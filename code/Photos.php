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
	 * Retrieve the list of photos where the given user is the owner and return
	 * their IDs as json list string.
	 * @param string $userid ID of the user to get the photo list for
	 * @return string JSON array containing the IDs of all photos of the user.
	 */
	static function getPhotoListJson($userid) {
		$medias = Persistence::query('select media_id from media where media_owner_users_id = '.$userid);
		return json_encode($medias);
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
}
