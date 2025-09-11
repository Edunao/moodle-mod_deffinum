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

// Prevent Caching Headers.
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Cache-Control: no-cache");
header("Pragma: no-cache");

require_once('../../config.php');
require_once($CFG->dirroot.'/mod/deffinum/lib.php');
require_once($CFG->dirroot.'/mod/deffinum/locallib.php');
require_once($CFG->dirroot.'/mod/deffinum/datamodels/aicclib.php');

foreach ($_POST as $key => $value) {
    $tempkey = strtolower($key);
    $_POST[$tempkey] = $value;
}

$command = required_param('command', PARAM_ALPHA);
$sessionid = required_param('session_id', PARAM_ALPHANUM);
$aiccdata = optional_param('aicc_data', '', PARAM_RAW);

$cfgdeffinum = get_config('deffinum');

$url = new moodle_url('/mod/deffinum/aicc.php', array('command' => $command, 'session_id' => $sessionid));
if ($aiccdata !== 0) {
    $url->param('aicc_data', $aiccdata);
}
$PAGE->set_url($url);

if (empty($cfgdeffinum->allowaicchacp)) {
    require_login();
    if (!confirm_sesskey($sessionid)) {
        throw new \moodle_exception('invalidsesskey');
    }
    $aiccuser = $USER;
    $deffinumsession = $SESSION->deffinum;
} else {
    $deffinumsession = deffinum_aicc_confirm_hacp_session($sessionid);
    if (empty($deffinumsession)) {
        throw new \moodle_exception('invalidhacpsession', 'deffinum');
    }
    $aiccuser = $DB->get_record('user', array('id' => $deffinumsession->userid), 'id,username,lastname,firstname', MUST_EXIST);
}

if (!empty($command)) {
    $command = strtolower($command);

    if (isset($deffinumsession->scoid)) {
        $scoid = $deffinumsession->scoid;
    } else {
        throw new \moodle_exception('cannotcallscript');
    }
    $mode = 'normal';
    if (isset($deffinumsession->deffinummode)) {
        $mode = $deffinumsession->deffinummode;
    }
    $status = 'Not Initialized';
    if (isset($deffinumsession->deffinumstatus)) {
        $status = $deffinumsession->deffinumstatus;
    }
    if (isset($deffinumsession->attempt)) {
        $attempt = $deffinumsession->attempt;
    } else {
        $attempt = 1;
    }
    $attemptobject = deffinum_get_attempt($aiccuser->id, $deffinumsession->deffinumid, $attempt);
    if ($sco = deffinum_get_sco($scoid, SCO_ONLY)) {
        if (!$deffinum = $DB->get_record('deffinum', array('id' => $sco->deffinum))) {
            throw new \moodle_exception('cannotcallscript');
        }
    } else {
        throw new \moodle_exception('cannotcallscript');
    }
    $aiccrequest = "MOODLE scoid: $scoid"
                 . "\r\nMOODLE mode: $mode"
                 . "\r\nMOODLE status: $status"
                 . "\r\nMOODLE attempt: $attempt"
                 . "\r\nAICC sessionid: $sessionid"
                 . "\r\nAICC command: $command"
                 . "\r\nAICC aiccdata:\r\n$aiccdata";
    deffinum_debug_log_write("aicc", "HACP Request:\r\n$aiccrequest", $scoid);
    ob_start();

    if ($deffinum = $DB->get_record('deffinum', array('id' => $sco->deffinum))) {
        switch ($command) {
            case 'getparam':
                if ($status == 'Not Initialized') {
                    $deffinumsession->deffinumstatus = 'Running';
                    $status = 'Running';
                }
                if ($status != 'Running') {
                    echo "error=101\r\nerror_text=Terminated\r\n";
                } else {
                    if ($usertrack = deffinum_get_tracks($scoid, $aiccuser->id, $attempt)) {
                        $userdata = $usertrack;
                    } else {
                        $userdata->status = '';
                        $userdata->score_raw = '';
                    }
                    $aiccuserid = get_config('deffinum', 'aiccuserid');
                    if (!empty($aiccuserid)) {
                        $userdata->student_id = $aiccuser->id;
                    } else {
                        $userdata->student_id = $aiccuser->username;
                    }
                    $userdata->student_name = $aiccuser->lastname .', '. $aiccuser->firstname;
                    $userdata->mode = $mode;
                    if ($userdata->mode == 'normal') {
                        $userdata->credit = 'credit';
                    } else {
                        $userdata->credit = 'no-credit';
                    }

                    if ($sco = deffinum_get_sco($scoid)) {
                        $userdata->course_id = $sco->identifier;
                        $userdata->datafromlms = isset($sco->datafromlms) ? $sco->datafromlms : '';
                        $userdata->mastery_score = isset($sco->mastery_score) && is_numeric($sco->mastery_score) ?
                                                        trim($sco->mastery_score) : '';
                        $userdata->max_time_allowed = isset($sco->max_time_allowed) ? $sco->max_time_allowed : '';
                        $userdata->time_limit_action = isset($sco->time_limit_action) ? $sco->time_limit_action : '';

                        echo "error=0\r\nerror_text=Successful\r\naicc_data=";
                        echo "[Core]\r\n";
                        echo 'Student_ID='.$userdata->student_id."\r\n";
                        echo 'Student_Name='.$userdata->student_name."\r\n";
                        if (isset($userdata->{'cmi.core.lesson_location'})) {
                            echo 'Lesson_Location='.$userdata->{'cmi.core.lesson_location'}."\r\n";
                        } else {
                            echo 'Lesson_Location='."\r\n";
                        }
                        echo 'Credit='.$userdata->credit."\r\n";
                        if (isset($userdata->status)) {
                            if ($userdata->status == '') {
                                $userdata->entry = ', ab-initio';
                            } else {
                                if (isset($userdata->{'cmi.core.exit'}) && ($userdata->{'cmi.core.exit'} == 'suspend')) {
                                    $userdata->entry = ', resume';
                                } else {
                                    $userdata->entry = '';
                                }
                            }
                        }
                        if (isset($userdata->{'cmi.core.lesson_status'})) {
                            echo 'Lesson_Status='.$userdata->{'cmi.core.lesson_status'}.$userdata->entry."\r\n";
                            $deffinumsession->deffinum_lessonstatus = $userdata->{'cmi.core.lesson_status'};
                        } else {
                            echo 'Lesson_Status=not attempted'.$userdata->entry."\r\n";
                            $deffinumsession->deffinum_lessonstatus = 'not attempted';
                        }
                        if (isset($userdata->{'cmi.core.score.raw'})) {
                            $max = '';
                            $min = '';
                            if (isset($userdata->{'cmi.core.score.max'}) && !empty($userdata->{'cmi.core.score.max'})) {
                                $max = ', '.$userdata->{'cmi.core.score.max'};
                                if (isset($userdata->{'cmi.core.score.min'}) && !empty($userdata->{'cmi.core.score.min'})) {
                                    $min = ', '.$userdata->{'cmi.core.score.min'};
                                }
                            }
                            echo 'Score='.$userdata->{'cmi.core.score.raw'}.$max.$min."\r\n";
                        } else {
                            echo 'Score='."\r\n";
                        }
                        if (isset($userdata->{'cmi.core.total_time'})) {
                            echo 'Time='.$userdata->{'cmi.core.total_time'}."\r\n";
                        } else {
                            echo 'Time='.'00:00:00'."\r\n";
                        }
                        echo 'Lesson_Mode='.$userdata->mode."\r\n";
                        if (isset($userdata->{'cmi.suspend_data'})) {
                            $decoded = rawurldecode($userdata->{'cmi.suspend_data'});
                            $header = "[Core_Lesson]\r\n";
                            if (stripos($decoded, $header) === 0) {
                                // The header may have been stored with the content. If it was, trim it off the front.
                                $decoded = core_text::substr($decoded, core_text::strlen($header));
                            }
                            echo "[Core_Lesson]\r\n".$decoded."\r\n";
                        } else {
                            echo "[Core_Lesson]\r\n";
                        }
                        echo "[Core_Vendor]\r\n".$userdata->datafromlms."\r\n";
                        echo "[Evaluation]\r\nCourse_ID = {".$userdata->course_id."}\r\n";
                        echo "[Student_Data]\r\n";
                        echo 'Mastery_Score='.$userdata->mastery_score."\r\n";
                        echo 'Max_Time_Allowed='.$userdata->max_time_allowed."\r\n";
                        echo 'Time_Limit_Action='.$userdata->time_limit_action."\r\n";
                    } else {
                        throw new \moodle_exception('cannotfindsco', 'deffinum');
                    }
                }
            break;
            case 'putparam':
                if ($status == 'Running') {
                    if (! $cm = get_coursemodule_from_instance("deffinum", $deffinum->id, $deffinum->course)) {
                        echo "error=1\r\nerror_text=Unknown\r\n"; // No one must see this error message if not hacked.
                    }
                    $savetrack = has_capability('mod/deffinum:savetrack', context_module::instance($cm->id), $aiccuser->id);
                    if (!empty($aiccdata) && $savetrack) {
                        $initlessonstatus = 'not attempted';
                        $lessonstatus = 'not attempted';
                        if (isset($deffinumsession->deffinum_lessonstatus)) {
                            $initlessonstatus = $deffinumsession->deffinum_lessonstatus;
                        }
                        $score = '';
                        $datamodel['lesson_location'] = 'cmi.core.lesson_location';
                        $datamodel['lesson_status'] = 'cmi.core.lesson_status';
                        $datamodel['score'] = 'cmi.core.score.raw';
                        $datamodel['time'] = 'cmi.core.session_time';
                        $datamodel['[core_lesson]'] = 'cmi.suspend_data';
                        $datamodel['[comments]'] = 'cmi.comments';
                        $datarows = explode("\r\n", $aiccdata);
                        reset($datarows);
                        $multirowvalue = '';
                        $multirowelement = '';
                        foreach ($datarows as $did => $datarow) {
                            $equal = strpos($datarow, '=');
                            if ($equal === false || !empty($multirowelement)) {
                                if (empty($multirowelement)) {
                                    if (isset($datamodel[strtolower(trim($datarow))])) {
                                        $multirowelement = $datamodel[strtolower(trim($datarow))];
                                    } else {
                                        // An element was passed by the external AICC package is not one we care about.
                                        continue;
                                    }
                                } else {
                                    $multirowvalue .= $datarow . "\r\n";
                                }
                                if (isset($datarows[$did + 1]) && substr($datarows[$did + 1], 0, 1) != '[') {
                                    // This is a multiline row, we haven't found the end yet.
                                    continue;
                                }
                                $value = rawurlencode($multirowvalue);
                                $id = deffinum_insert_track($aiccuser->id, $deffinum->id, $sco->id, $attemptobject,
                                                         $multirowelement, $value);
                                $multirowvalue = $multirowelement = '';
                            } else {
                                $element = strtolower(trim(substr($datarow, 0, $equal)));
                                $value = trim(substr($datarow, $equal + 1));
                                if (isset($datamodel[$element])) {
                                    $element = $datamodel[$element];
                                    switch ($element) {
                                        case 'cmi.core.lesson_location':
                                            $id = deffinum_insert_track($aiccuser->id, $deffinum->id, $sco->id,
                                                                     $attemptobject, $element, $value);
                                        break;
                                        case 'cmi.core.lesson_status':
                                            $statuses = array(
                                                       'passed' => 'passed',
                                                       'completed' => 'completed',
                                                       'failed' => 'failed',
                                                       'incomplete' => 'incomplete',
                                                       'browsed' => 'browsed',
                                                       'not attempted' => 'not attempted',
                                                       'p' => 'passed',
                                                       'c' => 'completed',
                                                       'f' => 'failed',
                                                       'i' => 'incomplete',
                                                       'b' => 'browsed',
                                                       'n' => 'not attempted'
                                                       );
                                            $exites = array(
                                                       'logout' => 'logout',
                                                       'time-out' => 'time-out',
                                                       'suspend' => 'suspend',
                                                       'l' => 'logout',
                                                       't' => 'time-out',
                                                       's' => 'suspend',
                                                       );
                                            $values = explode(',', $value);
                                            $value = '';
                                            if (count($values) > 1) {
                                                $value = trim(strtolower($values[1]));
                                                $value = $value[0];
                                                if (isset($exites[$value])) {
                                                    $value = $exites[$value];
                                                }
                                            }
                                            if (empty($value) || isset($exites[$value])) {
                                                $subelement = 'cmi.core.exit';
                                                $id = deffinum_insert_track($aiccuser->id, $deffinum->id, $sco->id,
                                                                         $attemptobject, $subelement, $value);
                                            }
                                            $value = trim(strtolower($values[0]));
                                            $value = $value[0];
                                            if (isset($statuses[$value]) && ($mode == 'normal')) {
                                                $value = $statuses[$value];
                                                $id = deffinum_insert_track($aiccuser->id, $deffinum->id, $sco->id,
                                                                         $attemptobject, $element, $value);
                                            }
                                            $lessonstatus = $value;
                                        break;
                                        case 'cmi.core.score.raw':
                                            $values = explode(',', $value);
                                            if ((count($values) > 1) && ($values[1] >= $values[0]) && is_numeric($values[1])) {
                                                $subelement = 'cmi.core.score.max';
                                                $value = trim($values[1]);
                                                $id = deffinum_insert_track($aiccuser->id, $deffinum->id, $sco->id,
                                                                         $attemptobject, $subelement, $value);
                                                if ((count($values) == 3) && ($values[2] <= $values[0]) && is_numeric($values[2])) {
                                                    $subelement = 'cmi.core.score.min';
                                                    $value = trim($values[2]);
                                                    $id = deffinum_insert_track($aiccuser->id, $deffinum->id, $sco->id,
                                                                             $attemptobject, $subelement, $value);
                                                }
                                            }

                                            $value = '';
                                            if (is_numeric($values[0])) {
                                                $value = trim($values[0]);
                                                $id = deffinum_insert_track($aiccuser->id, $deffinum->id, $sco->id,
                                                                         $attemptobject, $element, $value);
                                            }
                                            $score = $value;
                                        break;
                                        case 'cmi.core.session_time':
                                             $deffinumsession->sessiontime = $value;
                                        break;
                                    }
                                }
                            }
                        }
                        if (($mode == 'browse') && ($initlessonstatus == 'not attempted')) {
                            $lessonstatus = 'browsed';
                            $id = deffinum_insert_track($aiccuser->id, $deffinum->id, $sco->id,
                                                     $attemptobject, 'cmi.core.lesson_status', 'browsed');
                        }
                        if ($mode == 'normal') {
                            if ($sco = deffinum_get_sco($scoid)) {
                                if (isset($sco->mastery_score) && is_numeric($sco->mastery_score)) {
                                    if ($score != '') { // Score is correctly initialized w/ an empty string, see above.
                                        if ($score >= trim($sco->mastery_score)) {
                                            $lessonstatus = 'passed';
                                        } else {
                                            $lessonstatus = 'failed';
                                        }
                                    }
                                }
                                $id = deffinum_insert_track($aiccuser->id, $deffinum->id, $sco->id,
                                                         $attemptobject, 'cmi.core.lesson_status', $lessonstatus);
                            }
                        }
                    }
                    echo "error=0\r\nerror_text=Successful\r\n";
                } else if ($status == 'Terminated') {
                    echo "error=1\r\nerror_text=Terminated\r\n";
                } else {
                    echo "error=1\r\nerror_text=Not Initialized\r\n";
                }
            break;
            case 'putcomments':
                if ($status == 'Running') {
                    echo "error=0\r\nerror_text=Successful\r\n";
                } else if ($status == 'Terminated') {
                    echo "error=1\r\nerror_text=Terminated\r\n";
                } else {
                    echo "error=1\r\nerror_text=Not Initialized\r\n";
                }
            break;
            case 'putinteractions':
                if ($status == 'Running') {
                    echo "error=0\r\nerror_text=Successful\r\n";
                } else if ($status == 'Terminated') {
                    echo "error=1\r\nerror_text=Terminated\r\n";
                } else {
                    echo "error=1\r\nerror_text=Not Initialized\r\n";
                }
            break;
            case 'putobjectives':
                if ($status == 'Running') {
                    echo "error=0\r\nerror_text=Successful\r\n";
                } else if ($status == 'Terminated') {
                    echo "error=1\r\nerror_text=Terminated\r\n";
                } else {
                    echo "error=1\r\nerror_text=Not Initialized\r\n";
                }
            break;
            case 'putpath':
                if ($status == 'Running') {
                    echo "error=0\r\nerror_text=Successful\r\n";
                } else if ($status == 'Terminated') {
                    echo "error=1\r\nerror_text=Terminated\r\n";
                } else {
                    echo "error=1\r\nerror_text=Not Initialized\r\n";
                }
            break;
            case 'putperformance':
                if ($status == 'Running') {
                    echo "error=0\r\nerror_text=Successful\r\n";
                } else if ($status == 'Terminated') {
                    echo "error=1\r\nerror_text=Terminated\r\n";
                } else {
                    echo "error=1\r\nerror_text=Not Initialized\r\n";
                }
            break;
            case 'exitau':
                if ($status == 'Running') {
                    if (isset($deffinumsession->sessiontime) && ($deffinumsession->sessiontime != '')) {
                        $track = deffinum_get_sco_value($sco->id, $aiccuser->id, 'cmi.core.total_time', $attempt);
                        if (!empty($track)) {
                            // Add session_time to total_time.
                            $value = deffinum_add_time($track->value, $deffinumsession->sessiontime);
                            $v = new stdClass();
                            $v->id = $track->valueid;
                            $v->value = $value;
                            $v->timemodified = time();
                            $DB->update_record('deffinum_scoes_value', $v);
                        } else {
                            $track = new stdClass();
                            $track->scoid = $sco->id;
                            $track->elementid = deffinum_get_elementid('cmi.core.total_time');
                            $track->value = $deffinumsession->sessiontime;
                            $atobject = deffinum_get_attempt($aiccuser->id, $deffinumsession->deffinumid, $attempt);
                            $track->attemptid = $atobject->id;
                            $track->timemodified = time();
                            $id = $DB->insert_record('deffinum_scoes_value', $track);
                        }
                        deffinum_update_grades($deffinum, $aiccuser->id);
                    }
                    $deffinumsession->deffinumstatus = 'Terminated';
                    $deffinumsession->session_time = '';
                    echo "error=0\r\nerror_text=Successful\r\n";
                } else if ($status == 'Terminated') {
                    echo "error=1\r\nerror_text=Terminated\r\n";
                } else {
                    echo "error=1\r\nerror_text=Not Initialized\r\n";
                }
            break;
            default:
                echo "error=1\r\nerror_text=Invalid Command\r\n";
            break;
        }
    }
} else {
    if (empty($command)) {
        echo "error=1\r\nerror_text=Invalid Command\r\n";
    } else {
        echo "error=3\r\nerror_text=Invalid Session ID\r\n";
    }
}
if (empty($cfgdeffinum->allowaicchacp)) {
    $SESSION->deffinum = $deffinumsession;
} else {
    $deffinumsession->timemodified = time();
    $DB->update_record('deffinum_aicc_session', $deffinumsession);
}

$aiccresponse = ob_get_contents();
deffinum_debug_log_write("aicc", "HACP Response:\r\n$aiccresponse", $scoid);
ob_end_flush();
