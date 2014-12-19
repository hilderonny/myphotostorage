<?php

/*
 * The MIT License
 *
 * Copyright 2014 Ronny Hildebrandt <ronny.hildebrandt@avorium.de>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * Class for handling photos, upload, transformations and so on
 *
 * @author Ronny Hildebrandt <ronny.hildebrandt@avorium.de>
 */
class Photos {
	
	/**
	 * 
	 * @return string Returns the current media dir as absolute path with 
	 * trailing slash
	 */
	static function getMediaDir() {
		return dirname(__DIR__).'/data/media/';
	}
	
	/**
	 * Retrieve the list of photos where the given user is the owner and return
	 * their IDs as json list string.
	 * @param string $userid ID of the user to get the photo list for
	 * @return string JSON array containing the IDs of all photos of the user.
	 */
	static function getPhotoList($userid) {
		$medias = Persistence::query('select media_id, to_char(to_timestamp(media_date), \'YYYY-MM\') as month from media where media_owner_users_id = '.$userid.' order by media_date desc');
		$ids = [];
		for ($i = 0; $i < count($medias); $i++) {
			if (!isset($ids[$medias[$i]["month"]])) {
				$ids[$medias[$i]["month"]] = [];
			}
			$ids[$medias[$i]["month"]][] = $medias[$i]["media_id"];
		}
		return json_encode($ids);
	}
	
	/**
	 * Returns a photo with the given id when it is contained in a publicly
	 * visible album or when it is owned by the given user. When the photo does
	 * not exist or when the user cannot access it, null is returned.
	 * 
	 * @param int $photoId ID of the photo to get.
	 * @param int $userId ID of the requesting user. Can be false when the photo
	 * is in a public album.
	 * @return object Photo for the ID or null when the photo does not exist or is
	 * not accessible.
	 */
	static function getPhoto($photoId, $userId) {
		$query = '
			select
				media.*
			from media
			left join albummedia on albummedia.albummedia_media_id = media.media_id
			left join albums on albums.albums_id = albummedia.albummedia_albums_id
			where media.media_id = '.$photoId.'
			and (
				albums.albums_status = \'public\'
				'.($userId ? 'or media.media_owner_users_id = '.$userId : '').'
			)';
		$photos = Persistence::query($query);
		if (count($photos) < 1) {
			return null;
		}
		return $photos[0];
	}
	
	/**
	 * Copies the source image, resizes it and store the resized image into 
	 * the target file. The target file has the given maximum height, not 
	 * depending on the aspect ratio.
	 * 
	 * @param string $sourcefile Path of the source image file.
	 * @param string $targetfile Path where to put the resized image
	 * @param int $targetheight Height of the resized image
	 * @param boolean $issquare When True, the target image will be a square
	 * image. Used for list thumbnails.
	 */
	static function resizeImage($sourcefile, $targetfile, $targetheight, $issquare) {
		$dimensions = getimagesize($sourcefile);
		$sourcewidth = $dimensions[0];
		$sourceheight = $dimensions[1];
		$sourceimage = imagecreatefromjpeg($sourcefile);
		$exif = exif_read_data($sourcefile);
		// Rotate image if necessary, http://sylvana.net/jpegcrop/exif_orientation.html
		if (isset($exif['Orientation']) && in_array($exif['Orientation'], [6, 8])) {
			$sourceimage = imagerotate($sourceimage, $exif['Orientation'] === 6 ? -90 : 90, 0);
			$sourcewidth = $dimensions[1];
			$sourceheight = $dimensions[0];
		}
		$targetwidth = $issquare ? $targetheight : $sourcewidth * $targetheight / $sourceheight;
		$targetimage = imagecreatetruecolor($targetwidth, $targetheight);
		if ($issquare) {
			imagecopyresampled($targetimage, $sourceimage, 0, 0, $sourcewidth > $sourceheight ? ($sourcewidth - $sourceheight) / 2 : 0, $sourceheight > $sourcewidth ? ($sourceheight - $sourcewidth) / 2 : 0, $targetwidth, $targetheight, min([$sourcewidth, $sourceheight]), min([$sourcewidth, $sourceheight]));
		} else {
			imagecopyresampled($targetimage, $sourceimage, 0, 0, 0, 0, $targetwidth, $targetheight, $sourcewidth, $sourceheight);
		}
		imagejpeg($targetimage, $targetfile);
		// Rotate image depending on EXIF data and aspect ratio
		imagedestroy($sourceimage);
		imagedestroy($targetimage);
	}

	// From here: http://stackoverflow.com/a/2572991
	static function getGps($exifCoord, $hemi) {
		$degrees = count($exifCoord) > 0 ? self::gps2Num($exifCoord[0]) : 0;
		$minutes = count($exifCoord) > 1 ? self::gps2Num($exifCoord[1]) : 0;
		$seconds = count($exifCoord) > 2 ? self::gps2Num($exifCoord[2]) : 0;
		$flip = ($hemi == 'W' or $hemi == 'S') ? -1 : 1;
		return $flip * ($degrees + $minutes / 60 + $seconds / 3600);
	}
	static function gps2Num($coordPart) {
		$parts = explode('/', $coordPart);
		if (count($parts) <= 0) {
			return 0;
		}
		if (count($parts) == 1) {
			return $parts[0];
		}
		return floatval($parts[0]) / floatval($parts[1]);
	}
	
	/**
	 * Handles the upload of a photo, generates thumbnails and extracts
	 * meta information from the JFIF.
	 * 
	 * @param object $file File to upload
	 * @param int $userid ID of the owner of the uploaded photo
	 */
	static function uploadPhoto($file, $userid) {
		if ($file['type'] !== 'image/jpeg') {
			return;
		}
		// Extract meta data from JFIF and store it in the database
		$exif = exif_read_data($file['tmp_name']);
		$datetime = time();
		if (isset($exif['DateTimeOriginal'])) {
			$datetime = date_timestamp_get(date_create_from_format('Y:m:d H:i:s', $exif['DateTimeOriginal']));
		} else if (isset($exif['DateTime'])) {
			$datetime = date_timestamp_get(date_create_from_format('Y:m:d H:i:s', $exif['DateTime']));
		}
		$location = isset($exif['GPSLatitude']) ? sprintf('%f,%f', self::getGps($exif["GPSLatitude"], $exif['GPSLatitudeRef']), self::getGps($exif["GPSLongitude"], $exif['GPSLongitudeRef'])) : '';
		$query = 'insert into media (media_owner_users_id, media_mimetype, media_location, media_date) values ('.$userid.', \''.$file['type'].'\', \''.$location.'\', '.$datetime.') returning media_id';
		$result = Persistence::query($query);
		$id = $result[0]['media_id'];
		// Generate list thumbnail
		self::resizeImage($file['tmp_name'], self::getMediaDir().$id.'.thumb', 320, true);
		// Generate big preview image for browser view (max. 1024 pixels height)
		self::resizeImage($file['tmp_name'], self::getMediaDir().$id.'.preview', 640, false);
		// Move original image to media folder
		rename($file['tmp_name'], self::getMediaDir().$id);
	}
}
