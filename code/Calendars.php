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
}
