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
 * Class for handling calendars
 *
 * @author Ronny Hildebrandt <ronny.hildebrandt@avorium.de>
 */
class Calendars {
	
	/**
	 * Saves the given calendar in the database. The ID and name are extracted
	 * from the given JSON and stored in the table and the JSON itself is also
	 * stored in a table column. For new calendars the newly created ID is
	 * returned.
	 * 
	 * @param string $calendarjson JSON containing the calendar data
	 * @param string $userid Owner of the calendar.
	 * @return int ID of the saved calendar.
	 */
	static function saveCalendar($calendarjson, $userid) {
		$data = json_decode($calendarjson);
		$id = $data->id;
		unset($data->id);
		$value = Persistence::escape(json_encode($data));
		$tableprefix = $GLOBALS['tableprefix'];
		if ($id) {
			$query = 'update '.$tableprefix.'calendars set '.$tableprefix.'calendars_data = \''.$value.'\' where '.$tableprefix.'calendars_id = '.$id.' order by '.$tableprefix.'calendars_name asc';
			Persistence::query($query);
			return $id;
		} else {
			$query = 'insert into '.$tableprefix.'calendars ('.$tableprefix.'calendars_owner_users_id, '.$tableprefix.'calendars_data) values ('.$userid.', \''.$value.'\')';
			return Persistence::query($query);
		}
	}
	
	/**
	 * Retrieve the list of calendars where the given user is the owner and return
	 * their IDs, names, cover photo IDs and cover texts as json string.
	 * 
	 * @param string $userid ID of the user to get the calendar list for
	 * @return string JSON array containing the metadata of all calendars of the user.
	 */
	static function getCalendarList($userid) {
		$tableprefix = $GLOBALS['tableprefix'];
		$calendars = Persistence::query('select '.$tableprefix.'calendars_id, '.$tableprefix.'calendars_data from '.$tableprefix.'calendars where '.$tableprefix.'calendars_owner_users_id = '.$userid.' order by '.$tableprefix.'calendars_name asc');
		$result = [];
		for ($i = 0; $i < count($calendars); $i++) {
			$calendar = $calendars[$i];
			$data = json_decode($calendar[$tableprefix.'calendars_data']);
			$item = [
				'id' => $calendar[$tableprefix.'calendars_id'],
				'year' => $data->year,
				'image' => $data->months[0]->image
			];
			$result[] = $item;
		}
		return json_encode($result);
	}
	
	/**
	 * Retrieve the calendar data with the given ID and the given Owner user ID.
	 * When no matching calendar was found, null is returned. The result
	 * is returned as JSON string.
	 * 
	 * @param string $id ID of the calendar
	 * @param string $userid ID of the Owner user
	 * @return string Calendar data as JSON.
	 */
	static function getCalendar($id, $userid) {
		$tableprefix = $GLOBALS['tableprefix'];
		$calendars = Persistence::query('select '.$tableprefix.'calendars_id, '.$tableprefix.'calendars_data from '.$tableprefix.'calendars where '.$tableprefix.'calendars_owner_users_id = '.Persistence::escape($userid).' and '.$tableprefix.'calendars_id = '.Persistence::escape($id));
		$calendar = count($calendars) > 0 ? $calendars[0] : null;
		$data = json_decode($calendar[$tableprefix.'calendars_data']);
		$data->id = $calendar[$tableprefix.'calendars_id'];
		return json_encode($data);
	}

	/**
	 * Deletes a calendar with the given ID when the given user id the owner.
	 * 
	 * @param int $id ID of the calendar to delete.
	 * @param int $userid ID of the owner user of the calendar.
	 */
	static function deleteCalendar($id, $userid) {
		$escapedid = Persistence::escape($id);
		$tableprefix = $GLOBALS['tableprefix'];
		$query = '
			select '.$tableprefix.'calendars_id
			from '.$tableprefix.'calendars
			where '.$tableprefix.'calendars_id = '.$escapedid.'
			and '.$tableprefix.'calendars_owner_users_id = '.Persistence::escape($userid).'
			';
		$calendars = Persistence::query($query);
		if (count($calendars) < 1) {
			return;
		}
		// Delete the calendar
		$deletequery = '
			delete
			from '.$tableprefix.'calendars
			where '.$tableprefix.'calendars_id = '.$escapedid.'
			';
		Persistence::query($deletequery);
	}

}
