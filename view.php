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

require_once("../../config.php");
require_once($CFG->dirroot.'/mod/deffinum/lib.php');
require_once($CFG->dirroot.'/mod/deffinum/locallib.php');
require_once($CFG->dirroot.'/course/lib.php');

$id = optional_param('id', '', PARAM_INT);       // Course Module ID, or
$a = optional_param('a', '', PARAM_INT);         // deffinum ID
$organization = optional_param('organization', '', PARAM_INT); // organization ID.
$action = optional_param('action', '', PARAM_ALPHA);
$preventskip = optional_param('preventskip', '', PARAM_INT); // Prevent Skip view, set by javascript redirects.

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

// BEGIN DEFFINUM CUSTOMIZATION.
// ---------------------------------------------------------------------------
// Handle the definition of the current attempt. -------------------------
// ---------------------------------------------------------------------------
$requestedattempt = optional_param('attempt', 0, PARAM_INT); // N >= 1
$currentattempts  = deffinum_get_attempt_count($USER->id, $deffinum);
$maxattempt       = (int)$deffinum->maxattempt; // 0 = unlimited

if ($requestedattempt
    && $requestedattempt == $currentattempts + 1
    && ($maxattempt == 0 || $requestedattempt <= $maxattempt)) {

    // Save the new attempt.
    $record = (object) [
        'deffinumid'   => $deffinum->id,
        'userid'       => $USER->id,
        'attempt'      => $requestedattempt,
    ];
    try {
        $DB->insert_record('deffinum_attempt', $record);
        $currentattempts = $requestedattempt;
    } catch (dml_exception $e) {
        // If another process has already created the line, we ignore the error, we will retrieve the number later.
    }
}

$currentattempts = deffinum_get_attempt_count($USER->id, $deffinum);
// END DEFFINUM CUSTOMIZATION.

$url = new moodle_url('/mod/deffinum/view.php', array('id' => $cm->id));
if ($organization !== '') {
    $url->param('organization', $organization);
}
$PAGE->set_url($url);
$forcejs = get_config('deffinum', 'forcejavascript');
if (!empty($forcejs)) {
    $PAGE->add_body_class('forcejavascript');
}

require_login($course, false, $cm);

$context = context_course::instance($course->id);
$contextmodule = context_module::instance($cm->id);

$launch = false; // Does this automatically trigger a launch based on skipview.
if (!empty($deffinum->popup)) {
    $scoid = 0;
    $orgidentifier = '';

    $result = deffinum_get_toc($USER, $deffinum, $cm->id, TOCFULLURL);
    // Set last incomplete sco to launch first.
    if (!empty($result->sco->id)) {
        $sco = $result->sco;
    } else {
        $sco = deffinum_get_sco($deffinum->launch, SCO_ONLY);
    }
    if (!empty($sco)) {
        $scoid = $sco->id;
        if (($sco->organization == '') && ($sco->launch == '')) {
            $orgidentifier = $sco->identifier;
        } else {
            $orgidentifier = $sco->organization;
        }
    }

    if (empty($preventskip) && $deffinum->skipview >= DEFFINUM_SKIPVIEW_FIRST &&
        has_capability('mod/deffinum:skipview', $contextmodule) &&
        !has_capability('mod/deffinum:viewreport', $contextmodule)) { // Don't skip users with the capability to view reports.

        // Do we launch immediately and redirect the parent back ?
        if ($deffinum->skipview == DEFFINUM_SKIPVIEW_ALWAYS || !deffinum_has_tracks($deffinum->id, $USER->id)) {
            $launch = true;
        }
    }
    // Redirect back to the section with one section per page ?

    $courseformat = course_get_format($course)->get_course();
    if ($courseformat->format == 'singleactivity') {
        $courseurl = $url->out(false, array('preventskip' => '1'));
    } else {
        $courseurl = course_get_url($course, $cm->sectionnum)->out(false);
    }
    $PAGE->requires->data_for_js('deffinumplayerdata', Array('launch' => $launch,
                                                           'currentorg' => $orgidentifier,
                                                           'sco' => $scoid,
                                                           'deffinum' => $deffinum->id,
                                                           'courseurl' => $courseurl,
                                                           'cwidth' => $deffinum->width,
                                                           'cheight' => $deffinum->height,
                                                           'popupoptions' => $deffinum->options), true);
    $PAGE->requires->string_for_js('popupsblocked', 'deffinum');
    $PAGE->requires->string_for_js('popuplaunched', 'deffinum');
    $PAGE->requires->js('/mod/deffinum/view.js', true);
}

if (isset($SESSION->deffinum)) {
    unset($SESSION->deffinum);
}

$strdeffinums = get_string("modulenameplural", "deffinum");
$strdeffinum  = get_string("modulename", "deffinum");

$shortname = format_string($course->shortname, true, array('context' => $context));
$pagetitle = strip_tags($shortname.': '.format_string($deffinum->name));

// Trigger module viewed event.
deffinum_view($deffinum, $course, $cm, $contextmodule);

if (empty($preventskip) && empty($launch) && (has_capability('mod/deffinum:skipview', $contextmodule))) {
    deffinum_simple_play($deffinum, $USER, $contextmodule, $cm->id);
}

// Print the page header.

$PAGE->set_title($pagetitle);
$PAGE->set_heading($course->fullname);
// Let the module handle the display.
if (!empty($action) && $action == 'delete' && confirm_sesskey() && has_capability('mod/deffinum:deleteownresponses', $contextmodule)) {
    $PAGE->activityheader->disable();
} else {
    $PAGE->activityheader->set_description('');
}

echo $OUTPUT->header();
if (!empty($action) && confirm_sesskey() && has_capability('mod/deffinum:deleteownresponses', $contextmodule)) {
    if ($action == 'delete') {
        $confirmurl = new moodle_url($PAGE->url, array('action' => 'deleteconfirm'));
        echo $OUTPUT->confirm(get_string('deleteuserattemptcheck', 'deffinum'), $confirmurl, $PAGE->url);
        echo $OUTPUT->footer();
        exit;
    } else if ($action == 'deleteconfirm') {
        // Delete this users attempts.
        deffinum_delete_tracks($deffinum->id, null, $USER->id);
        deffinum_update_grades($deffinum, $USER->id, true);
        echo $OUTPUT->notification(get_string('deffinumresponsedeleted', 'deffinum'), 'notifysuccess');
    }
}

// Print the main part of the page.
$attemptstatus = '';
if (empty($launch) && ($deffinum->displayattemptstatus == DEFFINUM_DISPLAY_ATTEMPTSTATUS_ALL ||
         $deffinum->displayattemptstatus == DEFFINUM_DISPLAY_ATTEMPTSTATUS_ENTRY)) {
    $attemptstatus = deffinum_get_attempt_status($USER, $deffinum, $cm);
}
echo $OUTPUT->box(format_module_intro('deffinum', $deffinum, $cm->id), '', 'intro');

// Check if DEFFINUM available. No need to display warnings because activity dates are displayed at the top of the page.
list($available, $warnings) = deffinum_get_availability_status($deffinum);

// BEGIN DEFFINUM CUSTOMIZATION.
// Prevent displaying the entry form and the table of contents.
//if ($available && empty($launch)) {
//    deffinum_print_launch($USER, $deffinum, 'view.php?id='.$cm->id, $cm);
//}
// END DEFFINUM CUSTOMIZATION.

echo $OUTPUT->box($attemptstatus);

if (!empty($forcejs)) {
    $message = $OUTPUT->box(get_string("forcejavascriptmessage", "deffinum"), "forcejavascriptmessage");
    echo html_writer::tag('noscript', $message);
}

if (!empty($deffinum->popup)) {
    $PAGE->requires->js_init_call('M.mod_deffinumform.init');
}

// BEGIN DEFFINUM CUSTOMIZATION.
// Add link to monitor logs.
$scoid = $DB->get_field('deffinum_scoes', 'id', ['deffinum' => $deffinum->id, 'parent' => '/', 'sortorder' => 1]);
if (has_capability('mod/deffinum:viewreport', $contextmodule)) {
    $links = [
        [
            'url' => new moodle_url('/mod/deffinum/monitor_logs.php', [
                'scoid' => $scoid,
                'cm' => $cm->id,
            ]),
            'text' => get_string('details', 'mod_deffinum'),
            'class' => 'btn btn-secondary custom-monitor-link-container mr-1',
        ],
        [
            'url' => new moodle_url('/mod/deffinum/report.php', [
                'id' => $cm->id,
                'mode' => 'detailed',
            ]),
            'text' => get_string('accessdetailedreports', 'deffinumreport_detailed'),
            'class' => 'btn btn-secondary custom-deffinum-report-detailed-link-container ml-1',
        ],
    ];

    foreach ($links as $link) {
        echo html_writer::link($link['url'], $link['text'], ['class' => $link['class']]);
    }

    echo '<hr>';
}

// Generate qr login key.
delete_user_key('tool_mobile/qrlogin', $USER->id);
$qrloginkey = create_user_key('tool_mobile/qrlogin', $USER->id);

// Display content depending on customtype.
switch ($deffinum->customtype) {
    // Handle augmented reality case and generate QR code for mobile access.
    case DEFFINUM_CUSTOMTYPE_AUGMENTED_REALITY:
        $target = $CFG->wwwroot . '/mod/deffinum/resource.php?id=' . $cm->id;
        $qrurl = 'moodlemobile://' . $CFG->wwwroot
                 . '?qrlogin=' . $qrloginkey
                 . '&userid=' . $USER->id
                 . '&scoid=' . $scoid
                 . '&linktoactivity=' . $target;
        generate_qrcode_from_url($qrurl, 7);
        break;

    // Handle 360 content, check allowed domain, embed iframe, and offer new attempt if available.
    case DEFFINUM_CUSTOMTYPE_360:
        $url = $deffinum->customdata;
        if (!mod_deffinum_domain_is_allowed($url)) {
            echo get_string('domainnotallowed', 'mod_deffinum');
            break;
        }
        $separator = (strpos($url, '?') !== false) ? '&' : '?';
        $qrurl = $url
                 . $separator . 'qrlogin=' . $qrloginkey
                 . '&userid=' . $USER->id
                 . '&scoid=' . $scoid
                 . '&host=' . urlencode($CFG->wwwroot)
                 . '&attempt=' . $currentattempts;

        // Display content inside an iframe.
        echo '<iframe src="' . $qrurl . '" width="100%" height="600px" allowfullscreen></iframe>';

        // Check if a new attempt can be started.
        $max       = (int)$deffinum->maxattempt;
        if ($max == 0 || $currentattempts < $max) {
            $nextattempt = $currentattempts + 1;

            $starturl = new moodle_url('/mod/deffinum/view.php', [
                'id'      => $cm->id,
                'attempt' => $nextattempt
            ]);

            echo html_writer::start_div('mt-3 mb-3');
            echo html_writer::link(
                $starturl,
                get_string('newattempt', 'deffinum'),
                ['class' => 'btn btn-primary']
            );
            echo html_writer::end_div();
        }

        break;

    // Handle virtual reality case, check allowed domain, and display a direct link.
    case DEFFINUM_CUSTOMTYPE_VIRTUAL_REALITY:
        $url = $deffinum->customdata;
        if (!mod_deffinum_domain_is_allowed($url)) {
            echo get_string('domainnotallowed', 'mod_deffinum');
            break;
        }
        $separator = (strpos($url, '?') !== false) ? '&' : '?';
        $qrurl = $url
                 . $separator . 'qrlogin=' . $qrloginkey
                 . '&userid=' . $USER->id
                 . '&scoid=' . $scoid
                 . '&host=' . urlencode($CFG->wwwroot);
        // Display a direct link to the VR resource.
        $downloadlink = '<a style="text-decoration: underline;" href="' . $qrurl . '">' . get_string('virtual_reality_instructions_link','mod_deffinum') . '</a>';
        echo '<p>' . get_string('virtual_reality_instructions', 'mod_deffinum', (object) [
            'downloadlink' => $downloadlink, 'scoid' => $scoid]). '</p>';
        break;
}
// END DEFFINUM CUSTOMIZATION.

echo $OUTPUT->footer();
