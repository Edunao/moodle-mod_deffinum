<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.


/**
 * Sets up $userdata array and default values for DEFFINUM 1.2 .
 *
 * @param stdClass $userdata an empty stdClass variable that should be set up with user values
 * @param object $deffinum package record
 * @param string $scoid SCO Id
 * @param string $attempt attempt number for the user
 * @param string $mode deffinum display mode type
 * @return array The default values that should be used for DEFFINUM 1.2 package
 */
function get_deffinum_default (&$userdata, $deffinum, $scoid, $attempt, $mode) {
    global $USER;

    $userdata->student_id = $USER->username;
    if (empty(get_config('deffinum', 'deffinumstandard'))) {
        $userdata->student_name = fullname($USER);
    } else {
        $userdata->student_name = $USER->lastname .', '. $USER->firstname;
    }

    if ($usertrack = deffinum_get_tracks($scoid, $USER->id, $attempt)) {
        foreach ($usertrack as $key => $value) {
            $userdata->$key = $value;
        }
    } else {
        $userdata->status = '';
        $userdata->score_raw = '';
    }

    if ($scodatas = deffinum_get_sco($scoid, SCO_DATA)) {
        foreach ($scodatas as $key => $value) {
            $userdata->$key = $value;
        }
    } else {
        throw new \moodle_exception('cannotfindsco', 'deffinum');
    }
    if (!$sco = deffinum_get_sco($scoid)) {
        throw new \moodle_exception('cannotfindsco', 'deffinum');
    }

    if (isset($userdata->status)) {
        if ($userdata->status == '') {
            $userdata->entry = 'ab-initio';
        } else {
            if (isset($userdata->{'cmi.core.exit'}) && ($userdata->{'cmi.core.exit'} == 'suspend')) {
                $userdata->entry = 'resume';
            } else {
                $userdata->entry = '';
            }
        }
    }

    $userdata->mode = 'normal';
    if (!empty($mode)) {
        $userdata->mode = $mode;
    }
    if ($userdata->mode == 'normal') {
        $userdata->credit = 'credit';
    } else {
        $userdata->credit = 'no-credit';
    }

    $def = array();
    $def['cmi.core.student_id'] = $userdata->student_id;
    $def['cmi.core.student_name'] = $userdata->student_name;
    $def['cmi.core.credit'] = $userdata->credit;
    $def['cmi.core.entry'] = $userdata->entry;
    $def['cmi.core.lesson_mode'] = $userdata->mode;
    $def['cmi.launch_data'] = deffinum_isset($userdata, 'datafromlms');
    $def['cmi.student_data.mastery_score'] = deffinum_isset($userdata, 'masteryscore');
    $def['cmi.student_data.max_time_allowed'] = deffinum_isset($userdata, 'maxtimeallowed');
    $def['cmi.student_data.time_limit_action'] = deffinum_isset($userdata, 'timelimitaction');
    $def['cmi.core.total_time'] = deffinum_isset($userdata, 'cmi.core.total_time', '00:00:00');

    // Now handle standard userdata items.
    $def['cmi.core.lesson_location'] = deffinum_isset($userdata, 'cmi.core.lesson_location');
    $def['cmi.core.lesson_status'] = deffinum_isset($userdata, 'cmi.core.lesson_status');
    $def['cmi.core.score.raw'] = deffinum_isset($userdata, 'cmi.core.score.raw');
    $def['cmi.core.score.max'] = deffinum_isset($userdata, 'cmi.core.score.max');
    $def['cmi.core.score.min'] = deffinum_isset($userdata, 'cmi.core.score.min');
    $def['cmi.core.exit'] = deffinum_isset($userdata, 'cmi.core.exit');
    $def['cmi.suspend_data'] = deffinum_isset($userdata, 'cmi.suspend_data');
    $def['cmi.comments'] = deffinum_isset($userdata, 'cmi.comments');
    $def['cmi.student_preference.language'] = deffinum_isset($userdata, 'cmi.student_preference.language');
    $def['cmi.student_preference.audio'] = deffinum_isset($userdata, 'cmi.student_preference.audio', '0');
    $def['cmi.student_preference.speed'] = deffinum_isset($userdata, 'cmi.student_preference.speed', '0');
    $def['cmi.student_preference.text'] = deffinum_isset($userdata, 'cmi.student_preference.text', '0');
    return $def;
}
