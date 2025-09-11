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

// This page prints a particular instance of aicc/deffinum package.

require_once('../../config.php');
require_once($CFG->dirroot.'/mod/deffinum/locallib.php');
require_once($CFG->libdir . '/completionlib.php');

$id = optional_param('cm', '', PARAM_INT);                          // Course Module ID, or
$a = optional_param('a', '', PARAM_INT);                            // deffinum ID
$scoid = required_param('scoid', PARAM_INT);                        // sco ID
$mode = optional_param('mode', 'normal', PARAM_ALPHA);              // navigation mode
$currentorg = optional_param('currentorg', '', PARAM_RAW);          // selected organization
$newattempt = optional_param('newattempt', 'off', PARAM_ALPHA);     // the user request to start a new attempt.
$displaymode = optional_param('display', '', PARAM_ALPHA);

if (!empty($id)) {
    if (! $cm = get_coursemodule_from_id('deffinum', $id, 0, true)) {
        throw new \moodle_exception('invalidcoursemodule');
    }
    if (! $course = $DB->get_record("course", array("id" => $cm->course))) {
        throw new \moodle_exception('coursemisconf');
    }
    if (! $deffinum = $DB->get_record("deffinum", array("id" => $cm->instance))) {
        throw new \moodle_exception('invalidcoursemodule');
    }
} else if (!empty($a)) {
    if (! $deffinum = $DB->get_record("deffinum", array("id" => $a))) {
        throw new \moodle_exception('invalidcoursemodule');
    }
    if (! $course = $DB->get_record("course", array("id" => $deffinum->course))) {
        throw new \moodle_exception('coursemisconf');
    }
    if (! $cm = get_coursemodule_from_instance("deffinum", $deffinum->id, $course->id, true)) {
        throw new \moodle_exception('invalidcoursemodule');
    }
} else {
    throw new \moodle_exception('missingparameter');
}

// PARAM_RAW is used for $currentorg, validate it against records stored in the table.
if (!empty($currentorg)) {
    if (!$DB->record_exists('deffinum_scoes', array('deffinum' => $deffinum->id, 'identifier' => $currentorg))) {
        $currentorg = '';
    }
}

// If new attempt is being triggered set normal mode and increment attempt number.
$attempt = deffinum_get_last_attempt($deffinum->id, $USER->id);

// Check mode is correct and set/validate mode/attempt/newattempt (uses pass by reference).
deffinum_check_mode($deffinum, $newattempt, $attempt, $USER->id, $mode);

if (!empty($scoid)) {
    $scoid = deffinum_check_launchable_sco($deffinum, $scoid);
}

$url = new moodle_url('/mod/deffinum/player.php', array('scoid' => $scoid, 'cm' => $cm->id));
if ($mode !== 'normal') {
    $url->param('mode', $mode);
}
if ($currentorg !== '') {
    $url->param('currentorg', $currentorg);
}
if ($newattempt !== 'off') {
    $url->param('newattempt', $newattempt);
}
if ($displaymode !== '') {
    $url->param('display', $displaymode);
}
$PAGE->set_url($url);
$PAGE->set_secondary_active_tab("modulepage");

$forcejs = get_config('deffinum', 'forcejavascript');
if (!empty($forcejs)) {
    $PAGE->add_body_class('forcejavascript');
}
$collapsetocwinsize = get_config('deffinum', 'collapsetocwinsize');
if (empty($collapsetocwinsize)) {
    // Set as default window size to collapse TOC.
    $collapsetocwinsize = 767;
} else {
    $collapsetocwinsize = intval($collapsetocwinsize);
}

require_login($course, false, $cm);

$strdeffinums = get_string('modulenameplural', 'deffinum');
$strdeffinum  = get_string('modulename', 'deffinum');
$strpopup = get_string('popup', 'deffinum');
$strexit = get_string('exitactivity', 'deffinum');

$coursecontext = context_course::instance($course->id);

if ($displaymode == 'popup') {
    $PAGE->set_pagelayout('embedded');
} else {
    $shortname = format_string($course->shortname, true, array('context' => $coursecontext));
    $pagetitle = strip_tags("$shortname: ".format_string($deffinum->name));
    $PAGE->set_title($pagetitle);
    $PAGE->set_heading($course->fullname);
}
if (!$cm->visible and !has_capability('moodle/course:viewhiddenactivities', context_module::instance($cm->id))) {
    echo $OUTPUT->header();
    notice(get_string("activityiscurrentlyhidden"));
    echo $OUTPUT->footer();
    die;
}

// Check if DEFFINUM available.
list($available, $warnings) = deffinum_get_availability_status($deffinum);
if (!$available) {
    $reason = current(array_keys($warnings));
    echo $OUTPUT->header();
    echo $OUTPUT->box(get_string($reason, "deffinum", $warnings[$reason]), "generalbox boxaligncenter");
    echo $OUTPUT->footer();
    die;
}

// TOC processing
$deffinum->version = strtolower(clean_param($deffinum->version, PARAM_SAFEDIR));   // Just to be safe.
if (!file_exists($CFG->dirroot.'/mod/deffinum/datamodels/'.$deffinum->version.'lib.php')) {
    $deffinum->version = 'deffinum_12';
}
require_once($CFG->dirroot.'/mod/deffinum/datamodels/'.$deffinum->version.'lib.php');

$result = deffinum_get_toc($USER, $deffinum, $cm->id, TOCJSLINK, $currentorg, $scoid, $mode, $attempt, true, true);
$sco = $result->sco;
if ($deffinum->lastattemptlock == 1 && $result->attemptleft == 0) {
    echo $OUTPUT->header();
    echo $OUTPUT->notification(get_string('exceededmaxattempts', 'deffinum'));
    echo $OUTPUT->footer();
    exit;
}

$scoidstr = '&amp;scoid='.$sco->id;
$modestr = '&amp;mode='.$mode;

$SESSION->deffinum = new stdClass();
$SESSION->deffinum->scoid = $sco->id;
$SESSION->deffinum->deffinumstatus = 'Not Initialized';
$SESSION->deffinum->deffinummode = $mode;
$SESSION->deffinum->attempt = $attempt;

// Mark module viewed.
$completion = new completion_info($course);
$completion->set_module_viewed($cm);

// Generate the exit button.
$exiturl = "";
if (empty($deffinum->popup) || $displaymode == 'popup') {
    if ($course->format == 'singleactivity' && $deffinum->skipview == DEFFINUM_SKIPVIEW_ALWAYS
        && !has_capability('mod/deffinum:viewreport', context_module::instance($cm->id))) {
        // Redirect students back to site home to avoid redirect loop.
        $exiturl = $CFG->wwwroot;
    } else {
        // Redirect back to the correct section if one section per page is being used.
        $exiturl = course_get_url($course, $cm->sectionnum)->out();
    }
}

// Print the page header.
$PAGE->requires->data_for_js('deffinumplayerdata', Array('launch' => false,
                                                       'currentorg' => '',
                                                       'sco' => 0,
                                                       'deffinum' => 0,
                                                       'courseid' => $deffinum->course,
                                                       'cwidth' => $deffinum->width,
                                                       'cheight' => $deffinum->height,
                                                       'popupoptions' => $deffinum->options), true);
$PAGE->requires->js('/mod/deffinum/request.js', true);
$PAGE->requires->js('/lib/cookies.js', true);

if (file_exists($CFG->dirroot.'/mod/deffinum/datamodels/'.$deffinum->version.'.js')) {
    $PAGE->requires->js('/mod/deffinum/datamodels/'.$deffinum->version.'.js', true);
} else {
    $PAGE->requires->js('/mod/deffinum/datamodels/deffinum_12.js', true);
}
$activityheader = $PAGE->activityheader;
$headerconfig = [
    'description' => '',
    'hidecompletion' => true
];

$activityheader->set_attrs($headerconfig);
echo $OUTPUT->header();

$PAGE->requires->string_for_js('navigation', 'deffinum');
$PAGE->requires->string_for_js('toc', 'deffinum');
$PAGE->requires->string_for_js('hide', 'moodle');
$PAGE->requires->string_for_js('show', 'moodle');
$PAGE->requires->string_for_js('popupsblocked', 'deffinum');

$name = false;

// Exit button should ONLY be displayed when in the current window.
if ($displaymode !== 'popup') {
    $renderer = $PAGE->get_renderer('mod_deffinum');
    echo $renderer->generate_exitbar($exiturl);
}

echo html_writer::start_div('', array('id' => 'deffinumpage'));
echo html_writer::start_div('', array('id' => 'tocbox'));
echo html_writer::div(html_writer::tag('script', '', array('id' => 'external-deffinumapi', 'type' => 'text/JavaScript')), '',
                        array('id' => 'deffinumapi-parent'));

if ($deffinum->hidetoc == DEFFINUM_TOC_POPUP or $mode == 'browse' or $mode == 'review') {
    echo html_writer::start_div('mb-3', array('id' => 'deffinumtop'));
    if ($mode == 'browse' || $mode == 'review') {
        echo html_writer::div(get_string("{$mode}mode", 'deffinum'), 'deffinum-left h3', ['id' => 'deffinummode']);
    }
    if ($deffinum->hidetoc == DEFFINUM_TOC_POPUP) {
        echo html_writer::div($result->tocmenu, 'deffinum-right', array('id' => 'deffinumnav'));
    }
    echo html_writer::end_div();
}

echo html_writer::start_div('', array('id' => 'toctree'));

if (empty($deffinum->popup) || $displaymode == 'popup') {
    echo $result->toc;
} else {
    // Added incase javascript popups are blocked we don't provide a direct link
    // to the pop-up as JS communication can fail - the user must disable their pop-up blocker.
    $linkcourse = html_writer::link($CFG->wwwroot.'/course/view.php?id='.
                    $deffinum->course, get_string('finishdeffinumlinkname', 'deffinum'));
    echo $OUTPUT->box(get_string('finishdeffinum', 'deffinum', $linkcourse), 'generalbox', 'altfinishlink');
}
echo html_writer::end_div(); // Toc tree ends.
echo html_writer::end_div(); // Toc box ends.
echo html_writer::tag('noscript', html_writer::div(get_string('noscriptnodeffinum', 'deffinum'), '', array('id' => 'noscript')));

if ($result->prerequisites) {
    if ($deffinum->popup != 0 && $displaymode !== 'popup') {
        // Clean the name for the window as IE is fussy.
        $name = preg_replace("/[^A-Za-z0-9]/", "", $deffinum->name);
        if (!$name) {
            $name = 'DefaultPlayerWindow';
        }
        $name = 'deffinum_'.$name;
        echo html_writer::script('', $CFG->wwwroot.'/mod/deffinum/player.js');
        $url = new moodle_url($PAGE->url, array('scoid' => $sco->id, 'display' => 'popup', 'mode' => $mode));
        echo html_writer::script(
            js_writer::function_call('deffinum_openpopup', Array($url->out(false),
                                                       $name, $deffinum->options,
                                                       $deffinum->width, $deffinum->height)));
        echo html_writer::tag('noscript', html_writer::tag('iframe', '', array('id' => 'main',
                                'class' => 'scoframe', 'name' => 'main', 'src' => 'loadSCO.php?id='.$cm->id.$scoidstr.$modestr)));
    }
} else {
    echo $OUTPUT->box(get_string('noprerequisites', 'deffinum'));
}
echo html_writer::end_div(); // Deffinum page ends.

$scoes = deffinum_get_toc_object($USER, $deffinum, $currentorg, $sco->id, $mode, $attempt);
$adlnav = deffinum_get_adlnav_json($scoes['scoes']);

if (empty($deffinum->popup) || $displaymode == 'popup') {
    if (!isset($result->toctitle)) {
        $result->toctitle = get_string('toc', 'deffinum');
    }
    $jsmodule = array(
        'name' => 'mod_deffinum',
        'fullpath' => '/mod/deffinum/module.js',
        'requires' => array('json'),
    );
    $deffinum->nav = intval($deffinum->nav);
    $PAGE->requires->js_init_call('M.mod_deffinum.init', array($deffinum->nav, $deffinum->navpositionleft, $deffinum->navpositiontop,
                            $deffinum->hidetoc, $collapsetocwinsize, $result->toctitle, $name, $sco->id, $adlnav), false, $jsmodule);
}
if (!empty($forcejs)) {
    $message = $OUTPUT->box(get_string("forcejavascriptmessage", "deffinum"), "generalbox boxaligncenter forcejavascriptmessage");
    echo html_writer::tag('noscript', $message);
}

if (file_exists($CFG->dirroot.'/mod/deffinum/datamodels/'.$deffinum->version.'.php')) {
    include_once($CFG->dirroot.'/mod/deffinum/datamodels/'.$deffinum->version.'.php');
} else {
    include_once($CFG->dirroot.'/mod/deffinum/datamodels/deffinum_12.php');
}

// Add the keepalive system to keep checking for a connection.
\core\session\manager::keepalive('networkdropped', 'mod_deffinum', 30, 10);

echo $OUTPUT->footer();

// Set the start time of this SCO.
deffinum_insert_track($USER->id, $deffinum->id, $scoid, $attempt, 'x.start.time', time());
