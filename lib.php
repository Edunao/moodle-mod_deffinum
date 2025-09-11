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
 * @package   mod_deffinum
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/** DEFFINUM_TYPE_LOCAL = local */
define('DEFFINUM_TYPE_LOCAL', 'local');
/** DEFFINUM_TYPE_LOCALSYNC = localsync */
define('DEFFINUM_TYPE_LOCALSYNC', 'localsync');
/** DEFFINUM_TYPE_EXTERNAL = external */
define('DEFFINUM_TYPE_EXTERNAL', 'external');
/** DEFFINUM_TYPE_AICCURL = external AICC url */
define('DEFFINUM_TYPE_AICCURL', 'aiccurl');

define('DEFFINUM_TOC_SIDE', 0);
define('DEFFINUM_TOC_HIDDEN', 1);
define('DEFFINUM_TOC_POPUP', 2);
define('DEFFINUM_TOC_DISABLED', 3);

// Used to show/hide navigation buttons and set their position.
define('DEFFINUM_NAV_DISABLED', 0);
define('DEFFINUM_NAV_UNDER_CONTENT', 1);
define('DEFFINUM_NAV_FLOATING', 2);

// Used to check what DEFFINUM version is being used.
define('DEFFINUM_12', 1);
define('DEFFINUM_13', 2);
define('DEFFINUM_AICC', 3);

// List of possible attemptstatusdisplay options.
define('DEFFINUM_DISPLAY_ATTEMPTSTATUS_NO', 0);
define('DEFFINUM_DISPLAY_ATTEMPTSTATUS_ALL', 1);
define('DEFFINUM_DISPLAY_ATTEMPTSTATUS_MY', 2);
define('DEFFINUM_DISPLAY_ATTEMPTSTATUS_ENTRY', 3);

define('DEFFINUM_EVENT_TYPE_OPEN', 'open');
define('DEFFINUM_EVENT_TYPE_CLOSE', 'close');

// BEGIN DEFFINUM CUSTOMIZATION.
// DEFFINUM Custom types.
define('DEFFINUM_CUSTOMTYPE_360', '360');
define('DEFFINUM_CUSTOMTYPE_AUGMENTED_REALITY', 'augmented_reality');
define('DEFFINUM_CUSTOMTYPE_VIRTUAL_REALITY', 'virtual_reality');
define('DEFFINUM_CUSTOMTYPE_SERIOUS_GAME', 'serious_game');
// END DEFFINUM CUSTOMIZATION.

require_once(__DIR__ . '/deprecatedlib.php');

/**
 * Return an array of status options
 *
 * Optionally with translated strings
 *
 * @param   bool    $with_strings   (optional)
 * @return  array
 */
function deffinum_status_options($withstrings = false) {
    // Id's are important as they are bits.
    $options = array(
        2 => 'passed',
        4 => 'completed'
    );

    if ($withstrings) {
        foreach ($options as $key => $value) {
            $options[$key] = get_string('completionstatus_'.$value, 'deffinum');
        }
    }

    return $options;
}


/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @global stdClass
 * @global object
 * @uses CONTEXT_MODULE
 * @uses DEFFINUM_TYPE_LOCAL
 * @uses DEFFINUM_TYPE_LOCALSYNC
 * @uses DEFFINUM_TYPE_EXTERNAL
 * @param object $deffinum Form data
 * @param object $mform
 * @return int new instance id
 */
function deffinum_add_instance($deffinum, $mform=null) {
    global $CFG, $DB;

    require_once($CFG->dirroot.'/mod/deffinum/locallib.php');

    if (empty($deffinum->timeopen)) {
        $deffinum->timeopen = 0;
    }
    if (empty($deffinum->timeclose)) {
        $deffinum->timeclose = 0;
    }
    if (empty($deffinum->completionstatusallscos)) {
        $deffinum->completionstatusallscos = 0;
    }
    $cmid       = $deffinum->coursemodule;
    $cmidnumber = $deffinum->cmidnumber;
    $courseid   = $deffinum->course;

    $context = context_module::instance($cmid);

    $deffinum = deffinum_option2text($deffinum);
    $deffinum->width  = (int)str_replace('%', '', $deffinum->width);
    $deffinum->height = (int)str_replace('%', '', $deffinum->height);

    if (!isset($deffinum->whatgrade)) {
        $deffinum->whatgrade = 0;
    }

    $id = $DB->insert_record('deffinum', $deffinum);

    // Update course module record - from now on this instance properly exists and all function may be used.
    $DB->set_field('course_modules', 'instance', $id, array('id' => $cmid));

    // Reload deffinum instance.
    $record = $DB->get_record('deffinum', array('id' => $id));

    // BEGIN DEFFINUM CUSTOMIZATION.
    // Specific case for DEFFINUM_CUSTOMTYPE_AUGMENTED_REALITY.
    switch ($deffinum->customtype) {
        case DEFFINUM_CUSTOMTYPE_AUGMENTED_REALITY:
            // Store the resource and verify.
            if (!empty($deffinum->resourcefile)) {
                $fs = get_file_storage();
                $fs->delete_area_files($context->id, 'mod_deffinum', 'resource');
                file_save_draft_area_files($deffinum->resourcefile, $context->id, 'mod_deffinum', 'resource',
                        0, array('subdirs' => 0, 'maxfiles' => 1));
                // Get filename of zip that was uploaded.
                $files = $fs->get_area_files($context->id, 'mod_deffinum', 'resource', 0, '', false);
                $file = reset($files);
                $filename = $file->get_filename();
                if ($filename !== false) {
                    $record->reference = $filename;
                }
            }
            break;
    }
    // END DEFFINUM CUSTOMIZATION.

    // Save reference.
    $DB->update_record('deffinum', $record);

    // Extra fields required in grade related functions.
    $record->course     = $courseid;
    $record->cmidnumber = $cmidnumber;
    $record->cmid       = $cmid;

    deffinum_parse($record, true);

    deffinum_grade_item_update($record);
    deffinum_update_calendar($record, $cmid);
    if (!empty($deffinum->completionexpected)) {
        \core_completion\api::update_completion_date_event($cmid, 'deffinum', $record, $deffinum->completionexpected);
    }

    // BEGIN DEFFINUM CUSTOMIZATION.
    // Ensure there is a scorm_scoes entry in database for this record.
    if (!$DB->record_exists('deffinum_scoes', [
            'deffinum' => $id
    ])) {
        $DB->insert_record('deffinum_scoes', [
            'deffinum' => $id,
            'manifest' => "course_$courseid",
            'organization' => 'Default scoes organization',
            'parent' => '/',
            'identifier' => 'Default scoes identifier',
            'launch' => 'Default scoes launch',
            'deffinumtype' => '',
            'title' => 'Default scoes title',
            'sortorder' => '1',
        ]);
    }
    // END DEFFINUM CUSTOMIZATION.

    return $record->id;
}

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @global stdClass
 * @global object
 * @uses CONTEXT_MODULE
 * @uses DEFFINUM_TYPE_LOCAL
 * @uses DEFFINUM_TYPE_LOCALSYNC
 * @uses DEFFINUM_TYPE_EXTERNAL
 * @param object $deffinum Form data
 * @param object $mform
 * @return bool
 */
function deffinum_update_instance($deffinum, $mform=null) {
    global $CFG, $DB;

    require_once($CFG->dirroot.'/mod/deffinum/locallib.php');

    if (empty($deffinum->timeopen)) {
        $deffinum->timeopen = 0;
    }
    if (empty($deffinum->timeclose)) {
        $deffinum->timeclose = 0;
    }
    if (empty($deffinum->completionstatusallscos)) {
        $deffinum->completionstatusallscos = 0;
    }

    $cmid       = $deffinum->coursemodule;
    $cmidnumber = $deffinum->cmidnumber;
    $courseid   = $deffinum->course;

    $deffinum->id = $deffinum->instance;

    $context = context_module::instance($cmid);

    // BEGIN DEFFINUM CUSTOMIZATION.
    // Store the necessary information for this record based on its custom type.
    if (!empty($deffinum->resourcefile) && $deffinum->customtype === DEFFINUM_CUSTOMTYPE_AUGMENTED_REALITY) {
        $fs = get_file_storage();
        $fs->delete_area_files($context->id, 'mod_deffinum', 'resource');
        file_save_draft_area_files($deffinum->resourcefile, $context->id, 'mod_deffinum', 'resource',
            0, array('subdirs' => 0, 'maxfiles' => 1));
        // Get filename of zip that was uploaded.
        $files = $fs->get_area_files($context->id, 'mod_deffinum', 'resource', 0, '', false);
        $file = reset($files);
        $filename = $file->get_filename();
        if ($filename !== false) {
            $deffinum->reference = $filename;
        }
    }

    if (!empty($deffinum->vrurl) && $deffinum->customtype === DEFFINUM_CUSTOMTYPE_VIRTUAL_REALITY) {
        $deffinum->customdata = $deffinum->vrurl;
    }

    if (!empty($deffinum->visiturl) && $deffinum->customtype === DEFFINUM_CUSTOMTYPE_360) {
        $deffinum->customdata = $deffinum->visiturl;
    }
    // END DEFFINUM CUSTOMIZATION.

    $deffinum = deffinum_option2text($deffinum);
    $deffinum->width        = (int)str_replace('%', '', $deffinum->width);
    $deffinum->height       = (int)str_replace('%', '', $deffinum->height);
    $deffinum->timemodified = time();

    if (!isset($deffinum->whatgrade)) {
        $deffinum->whatgrade = 0;
    }

    $DB->update_record('deffinum', $deffinum);
    // We need to find this out before we blow away the form data.
    $completionexpected = (!empty($deffinum->completionexpected)) ? $deffinum->completionexpected : null;

    $deffinum = $DB->get_record('deffinum', array('id' => $deffinum->id));

    // Extra fields required in grade related functions.
    $deffinum->course   = $courseid;
    $deffinum->idnumber = $cmidnumber;
    $deffinum->cmid     = $cmid;

    deffinum_parse($deffinum, (bool)$deffinum->updatefreq);

    deffinum_grade_item_update($deffinum);
    deffinum_update_grades($deffinum);
    deffinum_update_calendar($deffinum, $cmid);
    \core_completion\api::update_completion_date_event($cmid, 'deffinum', $deffinum, $completionexpected);

    return true;
}

/**
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @global stdClass
 * @global object
 * @param int $id Deffinum instance id
 * @return boolean
 */
function deffinum_delete_instance($id) {
    global $CFG, $DB;

    if (! $deffinum = $DB->get_record('deffinum', array('id' => $id))) {
        return false;
    }

    $result = true;

    require_once($CFG->dirroot . '/mod/deffinum/locallib.php');
    // Delete any dependent records.
    deffinum_delete_tracks($deffinum->id);
    if ($scoes = $DB->get_records('deffinum_scoes', array('deffinum' => $deffinum->id))) {
        foreach ($scoes as $sco) {
            if (! $DB->delete_records('deffinum_scoes_data', array('scoid' => $sco->id))) {
                $result = false;
            }
        }
        $DB->delete_records('deffinum_scoes', array('deffinum' => $deffinum->id));
    }

    deffinum_grade_item_delete($deffinum);

    // We must delete the module record after we delete the grade item.
    if (! $DB->delete_records('deffinum', array('id' => $deffinum->id))) {
        $result = false;
    }

    /*if (! $DB->delete_records('deffinum_sequencing_controlmode', array('deffinumid'=>$deffinum->id))) {
        $result = false;
    }
    if (! $DB->delete_records('deffinum_sequencing_rolluprules', array('deffinumid'=>$deffinum->id))) {
        $result = false;
    }
    if (! $DB->delete_records('deffinum_sequencing_rolluprule', array('deffinumid'=>$deffinum->id))) {
        $result = false;
    }
    if (! $DB->delete_records('deffinum_sequencing_rollupruleconditions', array('deffinumid'=>$deffinum->id))) {
        $result = false;
    }
    if (! $DB->delete_records('deffinum_sequencing_rolluprulecondition', array('deffinumid'=>$deffinum->id))) {
        $result = false;
    }
    if (! $DB->delete_records('deffinum_sequencing_rulecondition', array('deffinumid'=>$deffinum->id))) {
        $result = false;
    }
    if (! $DB->delete_records('deffinum_sequencing_ruleconditions', array('deffinumid'=>$deffinum->id))) {
        $result = false;
    }*/

    return $result;
}

/**
 * Return a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 *
 * @param stdClass $course Course object
 * @param stdClass $user User
 * @param stdClass $mod
 * @param stdClass $deffinum The deffinum
 * @return mixed
 */
function deffinum_user_outline($course, $user, $mod, $deffinum) {
    global $CFG;
    require_once($CFG->dirroot.'/mod/deffinum/locallib.php');

    require_once("$CFG->libdir/gradelib.php");
    $grades = grade_get_grades($course->id, 'mod', 'deffinum', $deffinum->id, $user->id);
    if (!empty($grades->items[0]->grades)) {
        $grade = reset($grades->items[0]->grades);
        $result = (object) [
            'time' => grade_get_date_for_user_grade($grade, $user),
        ];
        if (!$grade->hidden || has_capability('moodle/grade:viewhidden', context_course::instance($course->id))) {
            $result->info = get_string('gradenoun') . ': '. $grade->str_long_grade;
        } else {
            $result->info = get_string('gradenoun') . ': ' . get_string('hidden', 'grades');
        }

        return $result;
    }
    return null;
}

/**
 * Print a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * @global stdClass
 * @global object
 * @param object $course
 * @param object $user
 * @param object $mod
 * @param object $deffinum
 * @return boolean
 */
function deffinum_user_complete($course, $user, $mod, $deffinum) {
    global $CFG, $DB, $OUTPUT;
    require_once("$CFG->libdir/gradelib.php");

    $liststyle = 'structlist';
    $now = time();
    $firstmodify = $now;
    $lastmodify = 0;
    $sometoreport = false;
    $report = '';

    // First Access and Last Access dates for SCOs.
    require_once($CFG->dirroot.'/mod/deffinum/locallib.php');
    $timetracks = deffinum_get_sco_runtime($deffinum->id, false, $user->id);
    $firstmodify = $timetracks->start;
    $lastmodify = $timetracks->finish;

    $grades = grade_get_grades($course->id, 'mod', 'deffinum', $deffinum->id, $user->id);
    if (!empty($grades->items[0]->grades)) {
        $grade = reset($grades->items[0]->grades);
        if (!$grade->hidden || has_capability('moodle/grade:viewhidden', context_course::instance($course->id))) {
            echo $OUTPUT->container(get_string('gradenoun').': '.$grade->str_long_grade);
            if ($grade->str_feedback) {
                echo $OUTPUT->container(get_string('feedback').': '.$grade->str_feedback);
            }
        } else {
            echo $OUTPUT->container(get_string('gradenoun') . ': ' . get_string('hidden', 'grades'));
        }
    }

    if ($orgs = $DB->get_records_select('deffinum_scoes', 'deffinum = ? AND '.
                                         $DB->sql_isempty('deffinum_scoes', 'launch', false, true).' AND '.
                                         $DB->sql_isempty('deffinum_scoes', 'organization', false, false),
                                         array($deffinum->id), 'sortorder, id', 'id, identifier, title')) {
        if (count($orgs) <= 1) {
            unset($orgs);
            $orgs = array();
            $org = new stdClass();
            $org->identifier = '';
            $orgs[] = $org;
        }
        $report .= html_writer::start_div('mod-deffinum');
        foreach ($orgs as $org) {
            $conditions = array();
            $currentorg = '';
            if (!empty($org->identifier)) {
                $report .= html_writer::div($org->title, 'orgtitle');
                $currentorg = $org->identifier;
                $conditions['organization'] = $currentorg;
            }
            $report .= html_writer::start_tag('ul', array('id' => '0', 'class' => $liststyle));
                $conditions['deffinum'] = $deffinum->id;
            if ($scoes = $DB->get_records('deffinum_scoes', $conditions, "sortorder, id")) {
                // Drop keys so that we can access array sequentially.
                $scoes = array_values($scoes);
                $level = 0;
                $sublist = 1;
                $parents[$level] = '/';
                foreach ($scoes as $pos => $sco) {
                    if ($parents[$level] != $sco->parent) {
                        if ($level > 0 && $parents[$level - 1] == $sco->parent) {
                            $report .= html_writer::end_tag('ul').html_writer::end_tag('li');
                            $level--;
                        } else {
                            $i = $level;
                            $closelist = '';
                            while (($i > 0) && ($parents[$level] != $sco->parent)) {
                                $closelist .= html_writer::end_tag('ul').html_writer::end_tag('li');
                                $i--;
                            }
                            if (($i == 0) && ($sco->parent != $currentorg)) {
                                $report .= html_writer::start_tag('li');
                                $report .= html_writer::start_tag('ul', array('id' => $sublist, 'class' => $liststyle));
                                $level++;
                            } else {
                                $report .= $closelist;
                                $level = $i;
                            }
                            $parents[$level] = $sco->parent;
                        }
                    }
                    $report .= html_writer::start_tag('li');
                    if (isset($scoes[$pos + 1])) {
                        $nextsco = $scoes[$pos + 1];
                    } else {
                        $nextsco = false;
                    }
                    if (($nextsco !== false) && ($sco->parent != $nextsco->parent) &&
                            (($level == 0) || (($level > 0) && ($nextsco->parent == $sco->identifier)))) {
                        $sublist++;
                    } else {
                        $report .= $OUTPUT->spacer(array("height" => "12", "width" => "13"));
                    }

                    if ($sco->launch) {
                        $score = '';
                        $totaltime = '';
                        if ($usertrack = deffinum_get_tracks($sco->id, $user->id)) {
                            if ($usertrack->status == '') {
                                $usertrack->status = 'notattempted';
                            }
                            $strstatus = get_string($usertrack->status, 'deffinum');
                            $report .= $OUTPUT->pix_icon($usertrack->status, $strstatus, 'deffinum');
                        } else {
                            if ($sco->deffinumtype == 'sco') {
                                $report .= $OUTPUT->pix_icon('notattempted', get_string('notattempted', 'deffinum'), 'deffinum');
                            } else {
                                $report .= $OUTPUT->pix_icon('asset', get_string('asset', 'deffinum'), 'deffinum');
                            }
                        }
                        $report .= "&nbsp;$sco->title $score$totaltime".html_writer::end_tag('li');
                        if ($usertrack !== false) {
                            $sometoreport = true;
                            $report .= html_writer::start_tag('li').html_writer::start_tag('ul', array('class' => $liststyle));
                            foreach ($usertrack as $element => $value) {
                                if (substr($element, 0, 3) == 'cmi') {
                                    $report .= html_writer::tag('li', s($element) . ' => ' . s($value));
                                }
                            }
                            $report .= html_writer::end_tag('ul').html_writer::end_tag('li');
                        }
                    } else {
                        $report .= "&nbsp;$sco->title".html_writer::end_tag('li');
                    }
                }
                for ($i = 0; $i < $level; $i++) {
                    $report .= html_writer::end_tag('ul').html_writer::end_tag('li');
                }
            }
            $report .= html_writer::end_tag('ul').html_writer::empty_tag('br');
        }
        $report .= html_writer::end_div();
    }
    if ($sometoreport) {
        if ($firstmodify < $now) {
            $timeago = format_time($now - $firstmodify);
            echo get_string('firstaccess', 'deffinum').': '.userdate($firstmodify).' ('.$timeago.")".html_writer::empty_tag('br');
        }
        if ($lastmodify > 0) {
            $timeago = format_time($now - $lastmodify);
            echo get_string('lastaccess', 'deffinum').': '.userdate($lastmodify).' ('.$timeago.")".html_writer::empty_tag('br');
        }
        echo get_string('report', 'deffinum').":".html_writer::empty_tag('br');
        echo $report;
    } else {
        print_string('noactivity', 'deffinum');
    }

    return true;
}

/**
 * Function to be run periodically according to the moodle Tasks API
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * @global stdClass
 * @global object
 * @return boolean
 */
function deffinum_cron_scheduled_task () {
    global $CFG, $DB;

    require_once($CFG->dirroot.'/mod/deffinum/locallib.php');

    $sitetimezone = core_date::get_server_timezone();
    // Now see if there are any deffinum updates to be done.

    if (!isset($CFG->deffinum_updatetimelast)) {    // To catch the first time.
        set_config('deffinum_updatetimelast', 0);
    }

    $timenow = time();
    $updatetime = usergetmidnight($timenow, $sitetimezone);

    if ($CFG->deffinum_updatetimelast < $updatetime and $timenow > $updatetime) {

        set_config('deffinum_updatetimelast', $timenow);

        mtrace('Updating deffinum packages which require daily update');// We are updating.

        $deffinumsupdate = $DB->get_records('deffinum', array('updatefreq' => DEFFINUM_UPDATE_EVERYDAY));
        foreach ($deffinumsupdate as $deffinumupdate) {
            deffinum_parse($deffinumupdate, true);
        }

        // Now clear out AICC session table with old session data.
        $cfgdeffinum = get_config('deffinum');
        if (!empty($cfgdeffinum->allowaicchacp)) {
            $expiretime = time() - ($cfgdeffinum->aicchacpkeepsessiondata * 24 * 60 * 60);
            $DB->delete_records_select('deffinum_aicc_session', 'timemodified < ?', array($expiretime));
        }
    }

    return true;
}

/**
 * Return grade for given user or all users.
 *
 * @global stdClass
 * @global object
 * @param int $deffinumid id of deffinum
 * @param int $userid optional user id, 0 means all users
 * @return array array of grades, false if none
 */
function deffinum_get_user_grades($deffinum, $userid=0) {
    global $CFG, $DB;
    require_once($CFG->dirroot.'/mod/deffinum/locallib.php');

    $grades = array();
    if (empty($userid)) {
        $sql = "SELECT DISTINCT userid
                  FROM {deffinum_attempt}
                 WHERE deffinumid = ?";
        $scousers = $DB->get_recordset_sql($sql, [$deffinum->id]);

        foreach ($scousers as $scouser) {
            $grades[$scouser->userid] = new stdClass();
            $grades[$scouser->userid]->id = $scouser->userid;
            $grades[$scouser->userid]->userid = $scouser->userid;
            $grades[$scouser->userid]->rawgrade = deffinum_grade_user($deffinum, $scouser->userid);
        }
        $scousers->close();
    } else {
        $preattempt = $DB->record_exists('deffinum_attempt', ['deffinumid' => $deffinum->id, 'userid' => $userid]);
        if (!$preattempt) {
            return false; // No attempt yet.
        }
        $grades[$userid] = new stdClass();
        $grades[$userid]->id = $userid;
        $grades[$userid]->userid = $userid;
        $grades[$userid]->rawgrade = deffinum_grade_user($deffinum, $userid);
    }

    if (empty($grades)) {
        return false;
    }

    return $grades;
}

/**
 * Update grades in central gradebook
 *
 * @category grade
 * @param object $deffinum
 * @param int $userid specific user only, 0 mean all
 * @param bool $nullifnone
 */
function deffinum_update_grades($deffinum, $userid=0, $nullifnone=true) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');
    require_once($CFG->libdir.'/completionlib.php');

    if ($grades = deffinum_get_user_grades($deffinum, $userid)) {
        deffinum_grade_item_update($deffinum, $grades);
        // Set complete.
        deffinum_set_completion($deffinum, $userid, COMPLETION_COMPLETE, $grades);
    } else if ($userid and $nullifnone) {
        $grade = new stdClass();
        $grade->userid   = $userid;
        $grade->rawgrade = null;
        deffinum_grade_item_update($deffinum, $grade);
        // Set incomplete.
        deffinum_set_completion($deffinum, $userid, COMPLETION_INCOMPLETE);
    } else {
        deffinum_grade_item_update($deffinum);
    }
}

/**
 * Update/create grade item for given deffinum
 *
 * @category grade
 * @uses GRADE_TYPE_VALUE
 * @uses GRADE_TYPE_NONE
 * @param object $deffinum object with extra cmidnumber
 * @param mixed $grades optional array/object of grade(s); 'reset' means reset grades in gradebook
 * @return object grade_item
 */
function deffinum_grade_item_update($deffinum, $grades=null) {
    global $CFG, $DB;
    require_once($CFG->dirroot.'/mod/deffinum/locallib.php');
    if (!function_exists('grade_update')) { // Workaround for buggy PHP versions.
        require_once($CFG->libdir.'/gradelib.php');
    }

    $params = array('itemname' => $deffinum->name);
    if (isset($deffinum->cmidnumber)) {
        $params['idnumber'] = $deffinum->cmidnumber;
    }

    if ($deffinum->grademethod == GRADESCOES) {
        $maxgrade = $DB->count_records_select('deffinum_scoes', 'deffinum = ? AND '.
                                                $DB->sql_isnotempty('deffinum_scoes', 'launch', false, true), array($deffinum->id));
        if ($maxgrade) {
            $params['gradetype'] = GRADE_TYPE_VALUE;
            $params['grademax']  = $maxgrade;
            $params['grademin']  = 0;
        } else {
            $params['gradetype'] = GRADE_TYPE_NONE;
        }
    } else {
        $params['gradetype'] = GRADE_TYPE_VALUE;
        $params['grademax']  = $deffinum->maxgrade;
        $params['grademin']  = 0;
    }

    if ($grades === 'reset') {
        $params['reset'] = true;
        $grades = null;
    }

    return grade_update('mod/deffinum', $deffinum->course, 'mod', 'deffinum', $deffinum->id, 0, $grades, $params);
}

/**
 * Delete grade item for given deffinum
 *
 * @category grade
 * @param object $deffinum object
 * @return object grade_item
 */
function deffinum_grade_item_delete($deffinum) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    return grade_update('mod/deffinum', $deffinum->course, 'mod', 'deffinum', $deffinum->id, 0, null, array('deleted' => 1));
}

/**
 * List the actions that correspond to a view of this module.
 * This is used by the participation report.
 *
 * Note: This is not used by new logging system. Event with
 *       crud = 'r' and edulevel = LEVEL_PARTICIPATING will
 *       be considered as view action.
 *
 * @return array
 */
function deffinum_get_view_actions() {
    return array('pre-view', 'view', 'view all', 'report');
}

/**
 * List the actions that correspond to a post of this module.
 * This is used by the participation report.
 *
 * Note: This is not used by new logging system. Event with
 *       crud = ('c' || 'u' || 'd') and edulevel = LEVEL_PARTICIPATING
 *       will be considered as post action.
 *
 * @return array
 */
function deffinum_get_post_actions() {
    return array();
}

/**
 * @param object $deffinum
 * @return object $deffinum
 */
function deffinum_option2text($deffinum) {
    $deffinumpopoupoptions = deffinum_get_popup_options_array();

    if (isset($deffinum->popup)) {
        if ($deffinum->popup == 1) {
            $optionlist = array();
            foreach ($deffinumpopoupoptions as $name => $option) {
                if (isset($deffinum->$name)) {
                    $optionlist[] = $name.'='.$deffinum->$name;
                } else {
                    $optionlist[] = $name.'=0';
                }
            }
            $deffinum->options = implode(',', $optionlist);
        } else {
            $deffinum->options = '';
        }
    } else {
        $deffinum->popup = 0;
        $deffinum->options = '';
    }
    return $deffinum;
}

/**
 * Implementation of the function for printing the form elements that control
 * whether the course reset functionality affects the deffinum.
 *
 * @param MoodleQuickForm $mform form passed by reference
 */
function deffinum_reset_course_form_definition(&$mform) {
    $mform->addElement('header', 'deffinumheader', get_string('modulenameplural', 'deffinum'));
    $mform->addElement('static', 'deffinumdelete', get_string('delete'));
    $mform->addElement('advcheckbox', 'reset_deffinum', get_string('deleteallattempts', 'deffinum'));
}

/**
 * Course reset form defaults.
 *
 * @return array
 */
function deffinum_reset_course_form_defaults($course) {
    return array('reset_deffinum' => 1);
}

/**
 * Removes all grades from gradebook
 *
 * @global stdClass
 * @global object
 * @param int $courseid
 * @param string optional type
 */
function deffinum_reset_gradebook($courseid, $type='') {
    global $CFG, $DB;

    $sql = "SELECT s.*, cm.idnumber as cmidnumber, s.course as courseid
              FROM {deffinum} s, {course_modules} cm, {modules} m
             WHERE m.name='deffinum' AND m.id=cm.module AND cm.instance=s.id AND s.course=?";

    if ($deffinums = $DB->get_records_sql($sql, array($courseid))) {
        foreach ($deffinums as $deffinum) {
            deffinum_grade_item_update($deffinum, 'reset');
        }
    }
}

/**
 * Actual implementation of the reset course functionality, delete all the
 * deffinum attempts for course $data->courseid.
 *
 * @global stdClass
 * @global object
 * @param object $data the data submitted from the reset course.
 * @return array status array
 */
function deffinum_reset_userdata($data) {
    global $DB, $CFG;
    require_once($CFG->dirroot.'/mod/deffinum/locallib.php');

    $componentstr = get_string('modulenameplural', 'deffinum');
    $status = [];

    if (!empty($data->reset_deffinum)) {

        $deffinums = $DB->get_recordset('deffinum', ['course' => $data->courseid]);
        foreach ($deffinums as $deffinum) {
            deffinum_delete_tracks($deffinum->id);
        }
        $deffinums->close();

        // Remove all grades from gradebook.
        if (empty($data->reset_gradebook_grades)) {
            deffinum_reset_gradebook($data->courseid);
        }

        $status[] = ['component' => $componentstr, 'item' => get_string('deleteallattempts', 'deffinum'), 'error' => false];
    }

    // Any changes to the list of dates that needs to be rolled should be same during course restore and course reset.
    // See MDL-9367.
    shift_course_mod_dates('deffinum', array('timeopen', 'timeclose'), $data->timeshift, $data->courseid);
    $status[] = ['component' => $componentstr, 'item' => get_string('date'), 'error' => false];

    return $status;
}

/**
 * Lists all file areas current user may browse
 *
 * @param object $course
 * @param object $cm
 * @param object $context
 * @return array
 */
function deffinum_get_file_areas($course, $cm, $context) {
    $areas = array();
    $areas['content'] = get_string('areacontent', 'deffinum');
    $areas['package'] = get_string('areapackage', 'deffinum');
    return $areas;
}

/**
 * File browsing support for DEFFINUM file areas
 *
 * @package  mod_deffinum
 * @category files
 * @param file_browser $browser file browser instance
 * @param array $areas file areas
 * @param stdClass $course course object
 * @param stdClass $cm course module object
 * @param stdClass $context context object
 * @param string $filearea file area
 * @param int $itemid item ID
 * @param string $filepath file path
 * @param string $filename file name
 * @return file_info instance or null if not found
 */
function deffinum_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    global $CFG;

    if (!has_capability('moodle/course:managefiles', $context)) {
        return null;
    }

    // No writing for now!

    $fs = get_file_storage();

    if ($filearea === 'content') {

        $filepath = is_null($filepath) ? '/' : $filepath;
        $filename = is_null($filename) ? '.' : $filename;

        $urlbase = $CFG->wwwroot.'/pluginfile.php';
        if (!$storedfile = $fs->get_file($context->id, 'mod_deffinum', 'content', 0, $filepath, $filename)) {
            if ($filepath === '/' and $filename === '.') {
                $storedfile = new virtual_root_file($context->id, 'mod_deffinum', 'content', 0);
            } else {
                // Not found.
                return null;
            }
        }
        require_once("$CFG->dirroot/mod/deffinum/locallib.php");
        return new deffinum_package_file_info($browser, $context, $storedfile, $urlbase, $areas[$filearea], true, true, false, false);

    } else if ($filearea === 'package') {
        $filepath = is_null($filepath) ? '/' : $filepath;
        $filename = is_null($filename) ? '.' : $filename;

        $urlbase = $CFG->wwwroot.'/pluginfile.php';
        if (!$storedfile = $fs->get_file($context->id, 'mod_deffinum', 'package', 0, $filepath, $filename)) {
            if ($filepath === '/' and $filename === '.') {
                $storedfile = new virtual_root_file($context->id, 'mod_deffinum', 'package', 0);
            } else {
                // Not found.
                return null;
            }
        }
        return new file_info_stored($browser, $context, $storedfile, $urlbase, $areas[$filearea], false, true, false, false);
    }

    // Deffinum_intro handled in file_browser.

    return false;
}

/**
 * Serves deffinum content, introduction images and packages. Implements needed access control ;-)
 *
 * @package  mod_deffinum
 * @category files
 * @param stdClass $course course object
 * @param stdClass $cm course module object
 * @param stdClass $context context object
 * @param string $filearea file area
 * @param array $args extra arguments
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @return bool false if file not found, does not return if found - just send the file
 */
function deffinum_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    global $CFG, $DB;

    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    require_login($course, true, $cm);

    $canmanageactivity = has_capability('moodle/course:manageactivities', $context);
    $lifetime = null;

    // Check DEFFINUM availability.
    if (!$canmanageactivity) {
        require_once($CFG->dirroot.'/mod/deffinum/locallib.php');

        $deffinum = $DB->get_record('deffinum', array('id' => $cm->instance), 'id, timeopen, timeclose', MUST_EXIST);
        list($available, $warnings) = deffinum_get_availability_status($deffinum);
        if (!$available) {
            return false;
        }
    }

    if ($filearea === 'content') {
        $revision = (int)array_shift($args); // Prevents caching problems - ignored here.
        $relativepath = implode('/', $args);
        $fullpath = "/$context->id/mod_deffinum/content/0/$relativepath";
        $options['immutable'] = true; // Add immutable option, $relativepath changes on file update.

    } else if ($filearea === 'package') {
        // Check if the global setting for disabling package downloads is enabled.
        $protectpackagedownloads = get_config('deffinum', 'protectpackagedownloads');
        if ($protectpackagedownloads and !$canmanageactivity) {
            return false;
        }
        $revision = (int)array_shift($args); // Prevents caching problems - ignored here.
        $relativepath = implode('/', $args);
        $fullpath = "/$context->id/mod_deffinum/package/0/$relativepath";
        $lifetime = 0; // No caching here.

    } else if ($filearea === 'imsmanifest') { // This isn't a real filearea, it's a url parameter for this type of package.
        $revision = (int)array_shift($args); // Prevents caching problems - ignored here.
        $relativepath = implode('/', $args);

        // Get imsmanifest file.
        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'mod_deffinum', 'package', 0, '', false);
        $file = reset($files);

        // Check that the package file is an imsmanifest.xml file - if not then this method is not allowed.
        $packagefilename = $file->get_filename();
        if (strtolower($packagefilename) !== 'imsmanifest.xml') {
            return false;
        }

        $file->send_relative_file($relativepath);
    // BEGIN DEFFINUM CUSTOMIZATION.
    // Allow serving "resource" files.
    } else if ($filearea === 'resource') {
        $revision = (int)array_shift($args); // Prevents caching problems - ignored here.
        $relativepath = implode('/', $args);
        $fullpath = "/$context->id/mod_deffinum/resource/0/$relativepath";
    // END DEFFINUM CUSTOMIZATION.
    } else {
        return false;
    }

    $fs = get_file_storage();
    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        if ($filearea === 'content') { // Return file not found straight away to improve performance.
            send_header_404();
            die;
        }
        return false;
    }

    // Allow SVG files to be loaded within DEFFINUM content, instead of forcing download.
    $options['dontforcesvgdownload'] = true;

    // Finally send the file.
    send_stored_file($file, $lifetime, 0, false, $options);
}

/**
 * @uses FEATURE_GROUPS
 * @uses FEATURE_GROUPINGS
 * @uses FEATURE_MOD_INTRO
 * @uses FEATURE_COMPLETION_TRACKS_VIEWS
 * @uses FEATURE_COMPLETION_HAS_RULES
 * @uses FEATURE_GRADE_HAS_GRADE
 * @uses FEATURE_GRADE_OUTCOMES
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, false if not, null if doesn't know or string for the module purpose.
 */
function deffinum_supports($feature) {
    switch($feature) {
        case FEATURE_GROUPS:                  return true;
        case FEATURE_GROUPINGS:               return true;
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_COMPLETION_HAS_RULES:    return true;
        case FEATURE_GRADE_HAS_GRADE:         return true;
        case FEATURE_GRADE_OUTCOMES:          return true;
        case FEATURE_BACKUP_MOODLE2:          return true;
        case FEATURE_SHOW_DESCRIPTION:        return true;
        case FEATURE_MOD_PURPOSE:
            return MOD_PURPOSE_INTERACTIVECONTENT;

        default: return null;
    }
}

/**
 * Get the filename for a temp log file
 *
 * @param string $type - type of log(aicc,deffinum12,deffinum13) used as prefix for filename
 * @param integer $scoid - scoid of object this log entry is for
 * @return string The filename as an absolute path
 */
function deffinum_debug_log_filename($type, $scoid) {
    global $CFG, $USER;

    $logpath = $CFG->tempdir.'/deffinumlogs';
    $logfile = $logpath.'/'.$type.'debug_'.$USER->id.'_'.$scoid.'.log';
    return $logfile;
}

/**
 * writes log output to a temp log file
 *
 * @param string $type - type of log(aicc,deffinum12,deffinum13) used as prefix for filename
 * @param string $text - text to be written to file.
 * @param integer $scoid - scoid of object this log entry is for.
 */
function deffinum_debug_log_write($type, $text, $scoid) {
    global $CFG;

    $debugenablelog = get_config('deffinum', 'allowapidebug');
    if (!$debugenablelog || empty($text)) {
        return;
    }
    if (make_temp_directory('deffinumlogs/')) {
        $logfile = deffinum_debug_log_filename($type, $scoid);
        @file_put_contents($logfile, date('Y/m/d H:i:s O')." DEBUG $text\r\n", FILE_APPEND);
        @chmod($logfile, $CFG->filepermissions);
    }
}

/**
 * Remove debug log file
 *
 * @param string $type - type of log(aicc,deffinum12,deffinum13) used as prefix for filename
 * @param integer $scoid - scoid of object this log entry is for
 * @return boolean True if the file is successfully deleted, false otherwise
 */
function deffinum_debug_log_remove($type, $scoid) {

    $debugenablelog = get_config('deffinum', 'allowapidebug');
    $logfile = deffinum_debug_log_filename($type, $scoid);
    if (!$debugenablelog || !file_exists($logfile)) {
        return false;
    }

    return @unlink($logfile);
}

/**
 * @deprecated since Moodle 3.3, when the block_course_overview block was removed.
 */
function deffinum_print_overview() {
    throw new coding_exception('deffinum_print_overview() can not be used any more and is obsolete.');
}

/**
 * Return a list of page types
 * @param string $pagetype current page type
 * @param stdClass $parentcontext Block's parent context
 * @param stdClass $currentcontext Current context of block
 */
function deffinum_page_type_list($pagetype, $parentcontext, $currentcontext) {
    $modulepagetype = array('mod-deffinum-*' => get_string('page-mod-deffinum-x', 'deffinum'));
    return $modulepagetype;
}

/**
 * Returns the DEFFINUM version used.
 * @param string $deffinumversion comes from $deffinum->version
 * @param string $version one of the defined vars DEFFINUM_12, DEFFINUM_13, DEFFINUM_AICC (or empty)
 * @return Deffinum version.
 */
function deffinum_version_check($deffinumversion, $version='') {
    $deffinumversion = trim(strtolower($deffinumversion));
    if (empty($version) || $version == DEFFINUM_12) {
        if ($deffinumversion == 'deffinum_12' || $deffinumversion == 'deffinum_1.2') {
            return DEFFINUM_12;
        }
        if (!empty($version)) {
            return false;
        }
    }
    if (empty($version) || $version == DEFFINUM_13) {
        if ($deffinumversion == 'deffinum_13' || $deffinumversion == 'deffinum_1.3') {
            return DEFFINUM_13;
        }
        if (!empty($version)) {
            return false;
        }
    }
    if (empty($version) || $version == DEFFINUM_AICC) {
        if (strpos($deffinumversion, 'aicc')) {
            return DEFFINUM_AICC;
        }
        if (!empty($version)) {
            return false;
        }
    }
    return false;
}

/**
 * Register the ability to handle drag and drop file uploads
 * @return array containing details of the files / types the mod can handle
 */
function deffinum_dndupload_register() {
    return array('files' => array(
        array('extension' => 'zip', 'message' => get_string('dnduploaddeffinum', 'deffinum'))
    ));
}

/**
 * Handle a file that has been uploaded
 * @param object $uploadinfo details of the file / content that has been uploaded
 * @return int instance id of the newly created mod
 */
function deffinum_dndupload_handle($uploadinfo) {

    $context = context_module::instance($uploadinfo->coursemodule);
    // BEGIN DEFFINUM CUSTOMIZATION.
    // Save the file in the resource filearea instead of package.
    file_save_draft_area_files($uploadinfo->draftitemid, $context->id, 'mod_deffinum', 'resource', 0);
    $fs = get_file_storage();
    $files = $fs->get_area_files($context->id, 'mod_deffinum', 'resource', 0, 'sortorder, itemid, filepath, filename', false);
    // END DEFFINUM CUSTOMIZATION.
    $file = reset($files);

    // Validate the file, make sure it's a valid DEFFINUM package!
    $errors = deffinum_validate_package($file);
    if (!empty($errors)) {
        return false;
    }
    // Create a default deffinum object to pass to deffinum_add_instance()!
    $deffinum = get_config('deffinum');
    $deffinum->course = $uploadinfo->course->id;
    $deffinum->coursemodule = $uploadinfo->coursemodule;
    $deffinum->cmidnumber = '';
    $deffinum->name = $uploadinfo->displayname;
    $deffinum->deffinumtype = DEFFINUM_TYPE_LOCAL;
    $deffinum->reference = $file->get_filename();
    $deffinum->intro = '';
    $deffinum->width = $deffinum->framewidth;
    $deffinum->height = $deffinum->frameheight;

    return deffinum_add_instance($deffinum, null);
}

/**
 * Sets activity completion state
 *
 * @param object $deffinum object
 * @param int $userid User ID
 * @param int $completionstate Completion state
 * @param array $grades grades array of users with grades - used when $userid = 0
 */
function deffinum_set_completion($deffinum, $userid, $completionstate = COMPLETION_COMPLETE, $grades = array()) {
    $course = new stdClass();
    $course->id = $deffinum->course;
    $completion = new completion_info($course);

    // Check if completion is enabled site-wide, or for the course.
    if (!$completion->is_enabled()) {
        return;
    }

    $cm = get_coursemodule_from_instance('deffinum', $deffinum->id, $deffinum->course);
    if (empty($cm) || !$completion->is_enabled($cm)) {
            return;
    }

    if (empty($userid)) { // We need to get all the relevant users from $grades param.
        foreach ($grades as $grade) {
            $completion->update_state($cm, $completionstate, $grade->userid);
        }
    } else {
        $completion->update_state($cm, $completionstate, $userid);
    }
}

/**
 * Check that a Zip file contains a valid DEFFINUM package
 *
 * @param $file stored_file a Zip file.
 * @return array empty if no issue is found. Array of error message otherwise
 */
function deffinum_validate_package($file) {
    $packer = get_file_packer('application/zip');
    $errors = array();
    if ($file->is_external_file()) { // Get zip file so we can check it is correct.
        $file->import_external_file_contents();
    }
    $filelist = $file->list_files($packer);

    if (!is_array($filelist)) {
        $errors['packagefile'] = get_string('badarchive', 'deffinum');
    } else {
        $aiccfound = false;
        $badmanifestpresent = false;
        foreach ($filelist as $info) {
            if ($info->pathname == 'imsmanifest.xml') {
                return array();
            } else if (strpos($info->pathname, 'imsmanifest.xml') !== false) {
                // This package has an imsmanifest file inside a folder of the package.
                $badmanifestpresent = true;
            }
            if (preg_match('/\.cst$/', $info->pathname)) {
                return array();
            }
        }
        if (!$aiccfound) {
            if ($badmanifestpresent) {
                $errors['packagefile'] = get_string('badimsmanifestlocation', 'deffinum');
            } else {
                $errors['packagefile'] = get_string('nomanifest', 'deffinum');
            }
        }
    }
    return $errors;
}

/**
 * Check and set the correct mode and attempt when entering a DEFFINUM package.
 *
 * @param object $deffinum object
 * @param string $newattempt should a new attempt be generated here.
 * @param int $attempt the attempt number this is for.
 * @param int $userid the userid of the user.
 * @param string $mode the current mode that has been selected.
 */
function deffinum_check_mode($deffinum, &$newattempt, &$attempt, $userid, &$mode) {
    global $DB;

    if (($mode == 'browse')) {
        if ($deffinum->hidebrowse == 1) {
            // Prevent Browse mode if hidebrowse is set.
            $mode = 'normal';
        } else {
            // We don't need to check attempts as browse mode is set.
            return;
        }
    }

    if ($deffinum->forcenewattempt == DEFFINUM_FORCEATTEMPT_ALWAYS) {
        // This DEFFINUM is configured to force a new attempt on every re-entry.
        $newattempt = 'on';
        $mode = 'normal';
        if ($attempt == 1) {
            // Check if the user has any existing data or if this is really the first attempt.
            $exists = $DB->record_exists('deffinum_attempt', ['userid' => $userid, 'deffinumid' => $deffinum->id]);
            if (!$exists) {
                // No records yet - Attempt should == 1.
                return;
            }
        }
        $attempt++;

        return;
    }
    // Check if the deffinum module is incomplete (used to validate user request to start a new attempt).
    $incomplete = true;

    // Note - in DEFFINUM_13 the cmi-core.lesson_status field was split into
    // 'cmi.completion_status' and 'cmi.success_status'.
    // 'cmi.completion_status' can only contain values 'completed', 'incomplete', 'not attempted' or 'unknown'.
    // This means the values 'passed' or 'failed' will never be reported for a track in DEFFINUM_13 and
    // the only status that will be treated as complete is 'completed'.

    $completionelements = array(
        DEFFINUM_12 => 'cmi.core.lesson_status',
        DEFFINUM_13 => 'cmi.completion_status',
        DEFFINUM_AICC => 'cmi.core.lesson_status'
    );
    $deffinumversion = deffinum_version_check($deffinum->version);
    if($deffinumversion===false) {
        $deffinumversion = DEFFINUM_12;
    }
    $completionelement = $completionelements[$deffinumversion];

    $sql = "SELECT sc.id, sub.value
              FROM {deffinum_scoes} sc
         LEFT JOIN (SELECT v.scoid, v.value
                      FROM {deffinum_attempt} a
                      JOIN {deffinum_scoes_value} v ON a.id = v.attemptid
                      JOIN {deffinum_element} e on e.id = v.elementid AND e.element = :element
                     WHERE a.userid = :userid AND a.attempt = :attempt AND a.deffinumid = :deffinumid) sub ON sub.scoid = sc.id
             WHERE sc.deffinumtype = 'sco' AND sc.deffinum = :deffinumid2";
    $tracks = $DB->get_recordset_sql($sql, ['userid' => $userid, 'attempt' => $attempt,
                                            'element' => $completionelement, 'deffinumid' => $deffinum->id,
                                            'deffinumid2' => $deffinum->id]);

    foreach ($tracks as $track) {
        if (($track->value == 'completed') || ($track->value == 'passed') || ($track->value == 'failed')) {
            $incomplete = false;
        } else {
            $incomplete = true;
            break; // Found an incomplete sco, so the result as a whole is incomplete.
        }
    }
    $tracks->close();

    // Validate user request to start a new attempt.
    if ($incomplete === true) {
        // The option to start a new attempt should never have been presented. Force false.
        $newattempt = 'off';
    } else if (!empty($deffinum->forcenewattempt)) {
        // A new attempt should be forced for already completed attempts.
        $newattempt = 'on';
    }

    if (($newattempt == 'on') && (($attempt < $deffinum->maxattempt) || ($deffinum->maxattempt == 0))) {
        $attempt++;
        $mode = 'normal';
    } else { // Check if review mode should be set.
        if ($incomplete === true) {
            $mode = 'normal';
        } else {
            $mode = 'review';
        }
    }
}

/**
 * Trigger the course_module_viewed event.
 *
 * @param  stdClass $deffinum        deffinum object
 * @param  stdClass $course     course object
 * @param  stdClass $cm         course module object
 * @param  stdClass $context    context object
 * @since Moodle 3.0
 */
function deffinum_view($deffinum, $course, $cm, $context) {

    // Trigger course_module_viewed event.
    $params = array(
        'context' => $context,
        'objectid' => $deffinum->id
    );

    $event = \mod_deffinum\event\course_module_viewed::create($params);
    $event->add_record_snapshot('course_modules', $cm);
    $event->add_record_snapshot('course', $course);
    $event->add_record_snapshot('deffinum', $deffinum);
    $event->trigger();
}

/**
 * Check if the module has any update that affects the current user since a given time.
 *
 * @param  cm_info $cm course module data
 * @param  int $from the time to check updates from
 * @param  array $filter  if we need to check only specific updates
 * @return stdClass an object with the different type of areas indicating if they were updated or not
 * @since Moodle 3.2
 */
function deffinum_check_updates_since(cm_info $cm, $from, $filter = array()) {
    global $DB, $USER, $CFG;
    require_once($CFG->dirroot . '/mod/deffinum/locallib.php');

    $deffinum = $DB->get_record($cm->modname, array('id' => $cm->instance), '*', MUST_EXIST);
    $updates = new stdClass();
    list($available, $warnings) = deffinum_get_availability_status($deffinum, true, $cm->context);
    if (!$available) {
        return $updates;
    }
    $updates = course_check_module_updates_since($cm, $from, array('package'), $filter);

    $updates->tracks = (object) array('updated' => false);
    $sql = "SELECT v.id
              FROM {deffinum_scoes_value} v
              JOIN {deffinum_attempt} a ON a.id = v.attemptid
             WHERE a.deffinumid = :deffinumid AND v.timemodified > :timemodified";
    $params = ['deffinumid' => $deffinum->id, 'timemodified' => $from, 'userid' => $USER->id];
    $tracks = $DB->get_records_sql($sql ." AND a.userid = :userid", $params);
    if (!empty($tracks)) {
        $updates->tracks->updated = true;
        $updates->tracks->itemids = array_keys($tracks);
    }

    // Now, teachers should see other students updates.
    if (has_capability('mod/deffinum:viewreport', $cm->context)) {
        $params = ['deffinumid' => $deffinum->id, 'timemodified' => $from];

        if (groups_get_activity_groupmode($cm) == SEPARATEGROUPS) {
            $groupusers = array_keys(groups_get_activity_shared_group_members($cm));
            if (empty($groupusers)) {
                return $updates;
            }
            list($insql, $inparams) = $DB->get_in_or_equal($groupusers, SQL_PARAMS_NAMED);
            $sql .= ' AND userid ' . $insql;
            $params = array_merge($params, $inparams);
        }

        $updates->usertracks = (object) array('updated' => false);

        $tracks = $DB->get_records_sql($sql, $params);
        if (!empty($tracks)) {
            $updates->usertracks->updated = true;
            $updates->usertracks->itemids = array_keys($tracks);
        }
    }
    return $updates;
}

/**
 * Get icon mapping for font-awesome.
 */
function mod_deffinum_get_fontawesome_icon_map() {
    return [
        'mod_deffinum:asset' => 'fa-regular fa-file-zipper',
        'mod_deffinum:assetc' => 'fa-regular fa-file-zipper',
        'mod_deffinum:browsed' => 'fa-book',
        'mod_deffinum:completed' => 'fa-regular fa-square-check',
        'mod_deffinum:failed' => 'fa-xmark',
        'mod_deffinum:incomplete' => 'fa-regular fa-pen-to-square',
        'mod_deffinum:minus' => 'fa-minus',
        'mod_deffinum:notattempted' => 'fa-regular fa-square',
        'mod_deffinum:passed' => 'fa-check',
        'mod_deffinum:plus' => 'fa-plus',
        'mod_deffinum:popdown' => 'fa-regular fa-rectangle-xmark',
        'mod_deffinum:popup' => 'fa-regular fa-window-restore',
        'mod_deffinum:suspend' => 'fa-pause',
        'mod_deffinum:wait' => 'fa-spinner fa-spin',
    ];
}

/**
 * This standard function will check all instances of this module
 * and make sure there are up-to-date events created for each of them.
 * If courseid = 0, then every deffinum event in the site is checked, else
 * only deffinum events belonging to the course specified are checked.
 *
 * @param int $courseid
 * @param int|stdClass $instance deffinum module instance or ID.
 * @param int|stdClass $cm Course module object or ID.
 * @return bool
 */
function deffinum_refresh_events($courseid = 0, $instance = null, $cm = null) {
    global $CFG, $DB;

    require_once($CFG->dirroot . '/mod/deffinum/locallib.php');

    // If we have instance information then we can just update the one event instead of updating all events.
    if (isset($instance)) {
        if (!is_object($instance)) {
            $instance = $DB->get_record('deffinum', array('id' => $instance), '*', MUST_EXIST);
        }
        if (isset($cm)) {
            if (!is_object($cm)) {
                $cm = (object)array('id' => $cm);
            }
        } else {
            $cm = get_coursemodule_from_instance('deffinum', $instance->id);
        }
        deffinum_update_calendar($instance, $cm->id);
        return true;
    }

    if ($courseid) {
        // Make sure that the course id is numeric.
        if (!is_numeric($courseid)) {
            return false;
        }
        if (!$deffinums = $DB->get_records('deffinum', array('course' => $courseid))) {
            return false;
        }
    } else {
        if (!$deffinums = $DB->get_records('deffinum')) {
            return false;
        }
    }

    foreach ($deffinums as $deffinum) {
        $cm = get_coursemodule_from_instance('deffinum', $deffinum->id);
        deffinum_update_calendar($deffinum, $cm->id);
    }

    return true;
}

/**
 * This function receives a calendar event and returns the action associated with it, or null if there is none.
 *
 * This is used by block_myoverview in order to display the event appropriately. If null is returned then the event
 * is not displayed on the block.
 *
 * @param calendar_event $event
 * @param \core_calendar\action_factory $factory
 * @param int $userid User id override
 * @return \core_calendar\local\event\entities\action_interface|null
 */
function mod_deffinum_core_calendar_provide_event_action(calendar_event $event,
                                                      \core_calendar\action_factory $factory, $userid = null) {
    global $CFG, $USER;

    require_once($CFG->dirroot . '/mod/deffinum/locallib.php');

    if (empty($userid)) {
        $userid = $USER->id;
    }

    $cm = get_fast_modinfo($event->courseid, $userid)->instances['deffinum'][$event->instance];

    if (has_capability('mod/deffinum:viewreport', $cm->context, $userid)) {
        // Teachers do not need to be reminded to complete a deffinum.
        return null;
    }

    $completion = new \completion_info($cm->get_course());

    $completiondata = $completion->get_data($cm, false, $userid);

    if ($completiondata->completionstate != COMPLETION_INCOMPLETE) {
        return null;
    }

    if (!empty($cm->customdata['timeclose']) && $cm->customdata['timeclose'] < time()) {
        // The deffinum has closed so the user can no longer submit anything.
        return null;
    }

    // Restore deffinum object from cached values in $cm, we only need id, timeclose and timeopen.
    $customdata = $cm->customdata ?: [];
    $customdata['id'] = $cm->instance;
    $deffinum = (object)($customdata + ['timeclose' => 0, 'timeopen' => 0]);

    // Check that the DEFFINUM activity is open.
    list($actionable, $warnings) = deffinum_get_availability_status($deffinum, false, null, $userid);

    return $factory->create_instance(
        get_string('enter', 'deffinum'),
        new \moodle_url('/mod/deffinum/view.php', array('id' => $cm->id)),
        1,
        $actionable
    );
}

/**
 * Add a get_coursemodule_info function in case any DEFFINUM type wants to add 'extra' information
 * for the course (see resource).
 *
 * Given a course_module object, this function returns any "extra" information that may be needed
 * when printing this activity in a course listing.  See get_array_of_activities() in course/lib.php.
 *
 * @param stdClass $coursemodule The coursemodule object (record).
 * @return cached_cm_info An object on information that the courses
 *                        will know about (most noticeably, an icon).
 */
function deffinum_get_coursemodule_info($coursemodule) {
    global $DB;

    $dbparams = ['id' => $coursemodule->instance];
    $fields = 'id, name, intro, introformat, completionstatusrequired, completionscorerequired, completionstatusallscos, '.
        'timeopen, timeclose';
    if (!$deffinum = $DB->get_record('deffinum', $dbparams, $fields)) {
        return false;
    }

    $result = new cached_cm_info();
    $result->name = $deffinum->name;

    if ($coursemodule->showdescription) {
        // Convert intro to html. Do not filter cached version, filters run at display time.
        $result->content = format_module_intro('deffinum', $deffinum, $coursemodule->id, false);
    }

    // Populate the custom completion rules as key => value pairs, but only if the completion mode is 'automatic'.
    if ($coursemodule->completion == COMPLETION_TRACKING_AUTOMATIC) {
        $result->customdata['customcompletionrules']['completionstatusrequired'] = $deffinum->completionstatusrequired;
        $result->customdata['customcompletionrules']['completionscorerequired'] = $deffinum->completionscorerequired;
        $result->customdata['customcompletionrules']['completionstatusallscos'] = $deffinum->completionstatusallscos;
    }
    // Populate some other values that can be used in calendar or on dashboard.
    if ($deffinum->timeopen) {
        $result->customdata['timeopen'] = $deffinum->timeopen;
    }
    if ($deffinum->timeclose) {
        $result->customdata['timeclose'] = $deffinum->timeclose;
    }

    return $result;
}

/**
 * Callback which returns human-readable strings describing the active completion custom rules for the module instance.
 *
 * @param cm_info|stdClass $cm object with fields ->completion and ->customdata['customcompletionrules']
 * @return array $descriptions the array of descriptions for the custom rules.
 */
function mod_deffinum_get_completion_active_rule_descriptions($cm) {
    // Values will be present in cm_info, and we assume these are up to date.
    if (empty($cm->customdata['customcompletionrules'])
        || $cm->completion != COMPLETION_TRACKING_AUTOMATIC) {
        return [];
    }

    $descriptions = [];
    foreach ($cm->customdata['customcompletionrules'] as $key => $val) {
        switch ($key) {
            case 'completionstatusrequired':
                if (!is_null($val)) {
                    // Determine the selected statuses using a bitwise operation.
                    $cvalues = array();
                    foreach (deffinum_status_options(true) as $bit => $string) {
                        if (($val & $bit) == $bit) {
                            $cvalues[] = $string;
                        }
                    }
                    $statusstring = implode(', ', $cvalues);
                    $descriptions[] = get_string('completionstatusrequireddesc', 'deffinum', $statusstring);
                }
                break;
            case 'completionscorerequired':
                if (!is_null($val)) {
                    $descriptions[] = get_string('completionscorerequireddesc', 'deffinum', $val);
                }
                break;
            case 'completionstatusallscos':
                if (!empty($val)) {
                    $descriptions[] = get_string('completionstatusallscos', 'deffinum');
                }
                break;
            default:
                break;
        }
    }
    return $descriptions;
}

/**
 * This function will update the deffinum module according to the
 * event that has been modified.
 *
 * It will set the timeopen or timeclose value of the deffinum instance
 * according to the type of event provided.
 *
 * @throws \moodle_exception
 * @param \calendar_event $event
 * @param stdClass $deffinum The module instance to get the range from
 */
function mod_deffinum_core_calendar_event_timestart_updated(\calendar_event $event, \stdClass $deffinum) {
    global $DB;

    if (empty($event->instance) || $event->modulename != 'deffinum') {
        return;
    }

    if ($event->instance != $deffinum->id) {
        return;
    }

    if (!in_array($event->eventtype, [DEFFINUM_EVENT_TYPE_OPEN, DEFFINUM_EVENT_TYPE_CLOSE])) {
        return;
    }

    $courseid = $event->courseid;
    $modulename = $event->modulename;
    $instanceid = $event->instance;
    $modified = false;

    $coursemodule = get_fast_modinfo($courseid)->instances[$modulename][$instanceid];
    $context = context_module::instance($coursemodule->id);

    // The user does not have the capability to modify this activity.
    if (!has_capability('moodle/course:manageactivities', $context)) {
        return;
    }

    if ($event->eventtype == DEFFINUM_EVENT_TYPE_OPEN) {
        // If the event is for the deffinum activity opening then we should
        // set the start time of the deffinum activity to be the new start
        // time of the event.
        if ($deffinum->timeopen != $event->timestart) {
            $deffinum->timeopen = $event->timestart;
            $deffinum->timemodified = time();
            $modified = true;
        }
    } else if ($event->eventtype == DEFFINUM_EVENT_TYPE_CLOSE) {
        // If the event is for the deffinum activity closing then we should
        // set the end time of the deffinum activity to be the new start
        // time of the event.
        if ($deffinum->timeclose != $event->timestart) {
            $deffinum->timeclose = $event->timestart;
            $modified = true;
        }
    }

    if ($modified) {
        $deffinum->timemodified = time();
        $DB->update_record('deffinum', $deffinum);
        $event = \core\event\course_module_updated::create_from_cm($coursemodule, $context);
        $event->trigger();
    }
}

/**
 * This function calculates the minimum and maximum cutoff values for the timestart of
 * the given event.
 *
 * It will return an array with two values, the first being the minimum cutoff value and
 * the second being the maximum cutoff value. Either or both values can be null, which
 * indicates there is no minimum or maximum, respectively.
 *
 * If a cutoff is required then the function must return an array containing the cutoff
 * timestamp and error string to display to the user if the cutoff value is violated.
 *
 * A minimum and maximum cutoff return value will look like:
 * [
 *     [1505704373, 'The date must be after this date'],
 *     [1506741172, 'The date must be before this date']
 * ]
 *
 * @param \calendar_event $event The calendar event to get the time range for
 * @param \stdClass $instance The module instance to get the range from
 * @return array Returns an array with min and max date.
 */
function mod_deffinum_core_calendar_get_valid_event_timestart_range(\calendar_event $event, \stdClass $instance) {
    $mindate = null;
    $maxdate = null;

    if ($event->eventtype == DEFFINUM_EVENT_TYPE_OPEN) {
        // The start time of the open event can't be equal to or after the
        // close time of the deffinum activity.
        if (!empty($instance->timeclose)) {
            $maxdate = [
                $instance->timeclose,
                get_string('openafterclose', 'deffinum')
            ];
        }
    } else if ($event->eventtype == DEFFINUM_EVENT_TYPE_CLOSE) {
        // The start time of the close event can't be equal to or earlier than the
        // open time of the deffinum activity.
        if (!empty($instance->timeopen)) {
            $mindate = [
                $instance->timeopen,
                get_string('closebeforeopen', 'deffinum')
            ];
        }
    }

    return [$mindate, $maxdate];
}

/**
 * Given an array with a file path, it returns the itemid and the filepath for the defined filearea.
 *
 * @param  string $filearea The filearea.
 * @param  array  $args The path (the part after the filearea and before the filename).
 * @return array The itemid and the filepath inside the $args path, for the defined filearea.
 */
function mod_deffinum_get_path_from_pluginfile(string $filearea, array $args): array {
    // DEFFINUM never has an itemid (the number represents the revision but it's not stored in database).
    array_shift($args);

    // Get the filepath.
    if (empty($args)) {
        $filepath = '/';
    } else {
        $filepath = '/' . implode('/', $args) . '/';
    }

    return [
        'itemid' => 0,
        'filepath' => $filepath,
    ];
}

/**
 * Callback to fetch the activity event type lang string.
 *
 * @param string $eventtype The event type.
 * @return lang_string The event type lang string.
 */
function mod_deffinum_core_calendar_get_event_action_string(string $eventtype): string {
    $modulename = get_string('modulename', 'deffinum');

    switch ($eventtype) {
        case DEFFINUM_EVENT_TYPE_OPEN:
            $identifier = 'calendarstart';
            break;
        case DEFFINUM_EVENT_TYPE_CLOSE:
            $identifier = 'calendarend';
            break;
        default:
            return get_string('requiresaction', 'calendar', $modulename);
    }

    return get_string($identifier, 'deffinum', $modulename);
}

/**
 * This function extends the settings navigation block for the site.
 *
 * It is safe to rely on PAGE here as we will only ever be within the module
 * context when this is called
 *
 * @param settings_navigation $settings navigation_node object.
 * @param navigation_node $deffinumnode navigation_node object.
 * @return void
 */
function deffinum_extend_settings_navigation(settings_navigation $settings, navigation_node $deffinumnode): void {
    if (has_capability('mod/deffinum:viewreport', $settings->get_page()->cm->context)) {
        $url = new moodle_url('/mod/deffinum/report.php', ['id' => $settings->get_page()->cm->id]);
        $deffinumnode->add(get_string("reports", "deffinum"), $url, navigation_node::TYPE_CUSTOM, null, 'deffinumreport');
    }
}

// BEGIN DEFFINUM CUSTOMIZATION.
// Functions added as part of the project.
/**
 * Generates a QR Code from a given URL and displays it as HTML div elements.
 * Each QR code pixel is represented as a small square div.
 *
 * @param string $url The URL to encode into the QR code.
 * @param int $pixelsize The size of each pixel square in the QR code.
 */
function generate_qrcode_from_url(string $url, int $pixelsize = 10): void {
    // Create a new QR code object from the core_qrcode class.
    $qrcode = new core_qrcode($url);

    // Get the array representation of the QR code.
    $barcode = $qrcode->getBarcodeArray();

    if (!empty($barcode)) {
        // Start a div container for the QR code with calculated width and height.
        echo '<div style="font-size:0;line-height:0;width:' . $barcode['num_cols'] * $pixelsize . 'px;height:' . $barcode['num_rows'] * $pixelsize . 'px;">';

        // Iterate over each line of the barcode.
        foreach ($barcode['bcode'] as $line) {
            foreach ($line as $char) {
                // Each '1' in the array represents a black square (pixel).
                if ($char === 1) {
                    echo '<div style="display:inline-block;width:' . $pixelsize . 'px;height:' . $pixelsize . 'px;background-color:black;"></div>';
                } else {
                    // Each '0' represents a white square (pixel).
                    echo '<div style="display:inline-block;width:' . $pixelsize . 'px;height:' . $pixelsize . 'px;background-color:white;"></div>';
                }
            }
            // New line after each line of barcode.
            echo '<br/>';
        }
        // Close the QR code div container.
        echo '</div>';
    }
}

/**
 * Get the list of allowed domains from configuration.
 *
 * @return array
 * @throws dml_exception
 */
function mod_deffinum_get_allowed_domains(): array {
    // Retrieve the allowed domains from the deffinum plugin configuration.
    $alloweddomains = get_config('deffinum', 'alloweddomains');

    // Return an empty array if no configuration is set.
    if (empty($alloweddomains)) {
        return [];
    }

    // Split the configuration by line breaks and trim whitespace.
    $alloweddomains = array_map('trim', explode("\n", $alloweddomains)) ?: [];

    // Filter out empty values and return the cleaned list of domains.
    return array_filter($alloweddomains);
}

/**
 * Check if a given domain is allowed based on the configured list.
 *
 * @param string $domain Domain to validate.
 * @return bool True if the domain is allowed, false otherwise.
 * @throws dml_exception
 */
function mod_deffinum_domain_is_allowed(string $domain): bool {
    // Get the list of allowed domains from configuration.
    $alloweddomains = mod_deffinum_get_allowed_domains();

    // Extract the host part from the given domain.
    $parseddomain = parse_url($domain, PHP_URL_HOST);
    if ($parseddomain === null) {
        // Return false if the given domain cannot be parsed.
        return false;
    }

    // Compare the parsed domain against each allowed domain.
    foreach ($alloweddomains as $allowed) {
        // Extract the host part of the allowed domain.
        $parsedalowed = parse_url($allowed, PHP_URL_HOST);

        // Return true if the parsed domain contains the allowed host.
        if ($parsedalowed && strpos($parseddomain, $parsedalowed) !== false) {
            return true;
        }
    }

    // Return false if no match is found.
    return false;
}

/**
 * Returns the name of the user preferences as well as the details this plugin uses.
 *
 * @uses core_user::is_current_user
 *
 * @return array[]
 */
function mod_deffinum_user_preferences(): array {
    // One preference by techno.
    $preferences['mod_deffinum_user_json_ra'] = array(
            'null' => NULL_ALLOWED,
            'default' => '',
            'type' => PARAM_RAW
    );
    $preferences['mod_deffinum_user_json_rv'] = array(
            'null' => NULL_ALLOWED,
            'default' => '',
            'type' => PARAM_RAW
    );
    $preferences['mod_deffinum_user_json_sg'] = array(
            'null' => NULL_ALLOWED,
            'default' => '',
            'type' => PARAM_RAW
    );
    $preferences['mod_deffinum_user_json_360'] = array(
            'null' => NULL_ALLOWED,
            'default' => '',
            'type' => PARAM_RAW
    );

    return $preferences;
}
// BEGIN DEFFINUM CUSTOMIZATION.
