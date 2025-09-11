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
 * Core Report class of detailed reporting plugin
 * @package    deffinumreport_detailed
 * @copyright  2025 Edunao SAS (contact@edunao.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace deffinumreport_detailed;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/csvlib.class.php');

/**
 * New detailed report for DEFFINUM project.
 */
class report extends \mod_deffinum\report {
    /**
     * displays the full report
     * @param \stdClass $deffinum full DEFFINUM object
     * @param \stdClass $cm - full course_module object
     * @param \stdClass $course - full course object
     * @param string $download - type of download being requested
     */
    public function display($deffinum, $cm, $course, $download) {
        global $CFG, $DB, $OUTPUT, $PAGE;

        $contextmodule = \context_module::instance($cm->id);
        $action = optional_param('action', '', PARAM_ALPHA);
        $attemptno = optional_param('attempt', 0, PARAM_INT);
        $userid     = optional_param('user',    0, PARAM_INT);
        $attemptsmode = optional_param('attemptsmode', DEFFINUM_REPORT_ATTEMPTS_ALL_STUDENTS, PARAM_INT);
        $PAGE->set_url(new \moodle_url($PAGE->url, ['attemptsmode' => $attemptsmode]));

        $view = optional_param('view', 'summary', PARAM_ALPHA);

        if ($view === 'detail' && $userid && $attemptno) {
            $this->render_detail($deffinum->id, $userid, $attemptno, $cm);
            return;
        }

        // Deffinum action bar for report.
        if ($download === '') {
            $actionbar = new \mod_deffinum\output\actionbar($cm->id, true, $attemptsmode);
            $renderer = $PAGE->get_renderer('mod_deffinum');
            echo $renderer->report_actionbar($actionbar);
        }

        // Find out current groups mode.
        $currentgroup = groups_get_activity_group($cm, true);

        // Detailed report.
        if (!$download) {
            echo '<style>#fitem_id_detailedrep {display: none}</style>';
        }
        $mform = new \mod_deffinum_report_settings($PAGE->url, compact('currentgroup'));
        if ($fromform = $mform->get_data()) {
            $pagesize = $fromform->pagesize;
            set_user_preference('deffinum_report_pagesize', $pagesize);
        } else {
            $pagesize = get_user_preferences('deffinum_report_pagesize', 0);
        }
        if ($pagesize < 1) {
            $pagesize = DEFFINUM_REPORT_DEFAULT_PAGE_SIZE;
        }

        // Select group menu.
        $displayoptions = [];
        $displayoptions['attemptsmode'] = $attemptsmode;
        if ($groupmode = groups_get_activity_groupmode($cm)) { // Groups are being used.
            if (!$download) {
                groups_print_activity_menu($cm, new \moodle_url($PAGE->url, $displayoptions));
            }
        }

        // Select the students.
        $nostudents = false;
        list($allowedlistsql, $params) = get_enrolled_sql($contextmodule, 'mod/deffinum:savetrack', (int) $currentgroup);
        if (empty($currentgroup)) {
            // All users who can attempt scoes.
            if (!$DB->record_exists_sql($allowedlistsql, $params)) {
                echo $OUTPUT->notification(get_string('nostudentsyet'));
                $nostudents = true;
            }
        } else {
            // All users who can attempt scoes and who are in the currently selected group.
            if (!$DB->record_exists_sql($allowedlistsql, $params)) {
                echo $OUTPUT->notification(get_string('nostudentsingroup'));
                $nostudents = true;
            }
        }

        if ( !$nostudents ) {
            // Now check if asked download of data.
            $coursecontext = \context_course::instance($course->id);
            if ($download) {
                $shortname = format_string($course->shortname, true, ['context' => $coursecontext]);
                $filename = clean_filename("$shortname ".format_string($deffinum->name, true));
            }

            // Define table columns.
            $columns = [];
            $headers = [];
            if (!$download && $CFG->grade_report_showuserimage) {
                $columns[] = 'picture';
                $headers[] = '';
            }
            $columns[] = 'fullname';
            $headers[] = get_string('name');
            // TODO Does not support custom user profile fields (MDL-70456).
            $extrafields = \core_user\fields::get_identity_fields($coursecontext, false);
            foreach ($extrafields as $field) {
                $columns[] = $field;
                $headers[] = \core_user\fields::get_display_name($field);
            }

            $columns[] = 'attempt';
            $headers[] = get_string('attempt', 'deffinum');
            $columns[] = 'start';
            $headers[] = get_string('started', 'deffinum');
            $columns[] = 'finish';
            $headers[] = get_string('last', 'deffinum');
            $columns[] = 'score';
            $headers[] = get_string('score', 'deffinum');
            $columns[] = 'completion';
            $headers[] = get_string('completion', 'deffinumreport_detailed');
            $columns[] = 'timespent';
            $headers[] = get_string('timespent',  'deffinumreport_detailed');
            $columns[] = 'progress';
            $headers[] = get_string('progressglobal', 'deffinumreport_detailed');

            if (!$download) {
                $table = new \flexible_table('mod-deffinum-report');

                $table->define_columns($columns);
                $table->define_headers($headers);
                $table->define_baseurl($PAGE->url);

                $table->sortable(true);
                $table->collapsible(true);

                // This is done to prevent redundant data, when a user has multiple attempts.
                $table->column_suppress('picture');
                $table->column_suppress('fullname');
                foreach ($extrafields as $field) {
                    $table->column_suppress($field);
                }

                $table->no_sorting('start');
                $table->no_sorting('finish');
                $table->no_sorting('score');
                $table->no_sorting('completion');
                $table->no_sorting('timespent');
                $table->no_sorting('progress');
                $table->no_sorting('checkbox');
                $table->no_sorting('picture');

                $table->column_class('picture', 'picture');
                $table->column_class('fullname', 'bold');
                $table->column_class('score', 'bold');

                $table->set_attribute('cellspacing', '0');
                $table->set_attribute('id', 'attempts');
                $table->set_attribute('class', 'generaltable generalbox');

                // Start working -- this is necessary as soon as the niceties are over.
                $table->setup();
            } else if ($download == 'ODS') {
                require_once("$CFG->libdir/odslib.class.php");

                $filename .= ".ods";
                // Creating a workbook.
                $workbook = new \MoodleODSWorkbook("-");
                // Sending HTTP headers.
                $workbook->send($filename);
                // Creating the first worksheet.
                $sheettitle = get_string('report', 'deffinum');
                $myxls = $workbook->add_worksheet($sheettitle);
                // Format types.
                $format = $workbook->add_format();
                $format->set_bold(0);
                $formatbc = $workbook->add_format();
                $formatbc->set_bold(1);
                $formatbc->set_align('center');
                $formatb = $workbook->add_format();
                $formatb->set_bold(1);
                $formaty = $workbook->add_format();
                $formaty->set_bg_color('yellow');
                $formatc = $workbook->add_format();
                $formatc->set_align('center');
                $formatr = $workbook->add_format();
                $formatr->set_bold(1);
                $formatr->set_color('red');
                $formatr->set_align('center');
                $formatg = $workbook->add_format();
                $formatg->set_bold(1);
                $formatg->set_color('green');
                $formatg->set_align('center');
                // Here starts workshhet headers.

                $colnum = 0;
                foreach ($headers as $item) {
                    $myxls->write(0, $colnum, $item, $formatbc);
                    $colnum++;
                }
                $rownum = 1;
            } else if ($download == 'Excel') {
                require_once("$CFG->libdir/excellib.class.php");

                $filename .= ".xls";
                // Creating a workbook.
                $workbook = new \MoodleExcelWorkbook("-");
                // Sending HTTP headers.
                $workbook->send($filename);
                // Creating the first worksheet.
                $sheettitle = get_string('report', 'deffinum');
                $myxls = $workbook->add_worksheet($sheettitle);
                // Format types.
                $format = $workbook->add_format();
                $format->set_bold(0);
                $formatbc = $workbook->add_format();
                $formatbc->set_bold(1);
                $formatbc->set_align('center');
                $formatb = $workbook->add_format();
                $formatb->set_bold(1);
                $formaty = $workbook->add_format();
                $formaty->set_bg_color('yellow');
                $formatc = $workbook->add_format();
                $formatc->set_align('center');
                $formatr = $workbook->add_format();
                $formatr->set_bold(1);
                $formatr->set_color('red');
                $formatr->set_align('center');
                $formatg = $workbook->add_format();
                $formatg->set_bold(1);
                $formatg->set_color('green');
                $formatg->set_align('center');

                $colnum = 0;
                foreach ($headers as $item) {
                    $myxls->write(0, $colnum, $item, $formatbc);
                    $colnum++;
                }
                $rownum = 1;
            } else if ($download == 'CSV') {
                $csvexport = new \csv_export_writer("tab");
                $csvexport->set_filename($filename, ".txt");
                $csvexport->add_data($headers);
            }
            // Construct the SQL.
            $select = 'SELECT DISTINCT '.$DB->sql_concat('u.id', '\'#\'', 'COALESCE(sa.attempt, 0)').' AS uniqueid, ';
            // TODO Does not support custom user profile fields (MDL-70456).
            $userfields = \core_user\fields::for_identity($coursecontext, false)->with_userpic()->including('idnumber');
            $selectfields = $userfields->get_sql('u', false, '', 'userid')->selects;
            $select .= 'sa.deffinumid AS deffinumid, sa.attempt AS attempt ' . $selectfields . ' ';

            // This part is the same for all cases - join users and user tracking tables.
            $from = 'FROM {user} u ';
            $from .= 'LEFT JOIN {deffinum_attempt} sa ON sa.userid = u.id AND sa.deffinumid = '.$deffinum->id;
            switch ($attemptsmode) {
                case DEFFINUM_REPORT_ATTEMPTS_STUDENTS_WITH:
                    // Show only students with attempts.
                    $where = " WHERE u.id IN ({$allowedlistsql}) AND sa.userid IS NOT NULL";
                    break;
                case DEFFINUM_REPORT_ATTEMPTS_STUDENTS_WITH_NO:
                    // Show only students without attempts.
                    $where = " WHERE u.id IN ({$allowedlistsql}) AND sa.userid IS NULL";
                    break;
                case DEFFINUM_REPORT_ATTEMPTS_ALL_STUDENTS:
                    // Show all students with or without attempts.
                    $where = " WHERE u.id IN ({$allowedlistsql}) AND (sa.userid IS NOT NULL OR sa.userid IS NULL)";
                    break;
            }

            $countsql = 'SELECT COUNT(DISTINCT('.$DB->sql_concat('u.id', '\'#\'', 'COALESCE(sa.attempt, 0)').')) AS nbresults, ';
            $countsql .= 'COUNT(DISTINCT('.$DB->sql_concat('u.id', '\'#\'', 'sa.attempt').')) AS nbattempts, ';
            $countsql .= 'COUNT(DISTINCT(u.id)) AS nbusers ';
            $countsql .= $from.$where;

            if (!$download) {
                $sort = $table->get_sql_sort();
            } else {
                $sort = '';
            }
            // Fix some wired sorting.
            if (empty($sort)) {
                $sort = ' ORDER BY uniqueid';
            } else {
                $sort = ' ORDER BY '.$sort;
            }

            if (!$download) {
                // Add extra limits due to initials bar.
                list($twhere, $tparams) = $table->get_sql_where();
                if ($twhere) {
                    $where .= ' AND '.$twhere; // Initial bar.
                    $params = array_merge($params, $tparams);
                }

                if (!empty($countsql)) {
                    $count = $DB->get_record_sql($countsql, $params);
                    $totalinitials = $count->nbresults;
                    if ($twhere) {
                        $countsql .= ' AND '.$twhere;
                    }
                    $count = $DB->get_record_sql($countsql, $params);
                    $total  = $count->nbresults;
                }

                $table->pagesize($pagesize, $total);

                echo \html_writer::start_div('deffinumattemptcounts');
                if ( $count->nbresults == $count->nbattempts ) {
                    echo get_string('reportcountattempts', 'deffinum', $count);
                } else if ( $count->nbattempts > 0 ) {
                    echo get_string('reportcountallattempts', 'deffinum', $count);
                } else {
                    echo $count->nbusers.' '.get_string('users');
                }
                echo \html_writer::end_div();
            }

            // Fetch the attempts.
            if (!$download) {
                $attempts = $DB->get_records_sql($select.$from.$where.$sort, $params,
                    $table->get_page_start(), $table->get_page_size());
                echo \html_writer::start_div('', ['id' => 'deffinumtablecontainer']);
                $table->initialbars($totalinitials > 20); // Build table rows.
            } else {
                $attempts = $DB->get_records_sql($select.$from.$where.$sort, $params);
            }

            if ($attempts) {
                foreach ($attempts as $scouser) {
                    $row = [];
                    if (!empty($scouser->attempt)) {
                        $timetracks = deffinum_get_sco_runtime($deffinum->id, false, $scouser->userid, $scouser->attempt);
                    } else {
                        $timetracks = '';
                    }
                    if (in_array('picture', $columns)) {
                        $user = new \stdClass();
                        $additionalfields = explode(',', implode(',', \core_user\fields::get_picture_fields()));
                        $user = username_load_fields_from_object($user, $scouser, null, $additionalfields);
                        $user->id = $scouser->userid;
                        $row[] = $OUTPUT->user_picture($user, ['courseid' => $course->id]);
                    }
                    if (!$download) {
                        $url = new \moodle_url('/user/view.php', ['id' => $scouser->userid, 'course' => $course->id]);
                        $row[] = \html_writer::link($url, fullname($scouser));
                    } else {
                        $row[] = fullname($scouser);
                    }
                    foreach ($extrafields as $field) {
                        $row[] = s($scouser->{$field});
                    }
                    if (empty($timetracks->start)) {
                        $row[] = '-';
                        $row[] = '-';
                        $row[] = '-';
                        $row[] = '-';
                        $row[] = '-';
                        $row[] = '-';
                        $row[] = '-';
                    } else {
                        if (!$download) {
                            $url = new \moodle_url('/mod/deffinum/report.php', [
                                'id' => $cm->id, 'user' => $scouser->userid,
                                'attempt' => $scouser->attempt, 'mode' => 'detailed',
                                'view' => 'detail',
                            ]);
                            $row[] = \html_writer::link($url, $scouser->attempt);
                        } else {
                            $row[] = $scouser->attempt;
                        }
                        if ($download == 'ODS' || $download == 'Excel' ) {
                            $row[] = userdate($timetracks->start, get_string("strftimedatetime", "langconfig"));
                        } else {
                            $row[] = userdate($timetracks->start);
                        }
                        if ($download == 'ODS' || $download == 'Excel' ) {
                            $row[] = userdate($timetracks->finish, get_string('strftimedatetime', 'langconfig'));
                        } else {
                            $row[] = userdate($timetracks->finish);
                        }
                        if (!empty($scouser->attempt)) {
                            // Récupère les 3 valeurs globales pour cette tentative.
                            $attemptglobals = $this->get_attempt_globals(
                                $deffinum->id,      // Activity id.
                                $scouser->userid,   // Learner id.
                                $scouser->attempt   // Attempt number (1-based).
                            );
                            if (!empty($attemptglobals->scoremax) && isset($attemptglobals->score)) {
                                $score = "$attemptglobals->score / $attemptglobals->scoremax";
                            } else if (isset($attemptglobals->score)) {
                                $score = $attemptglobals->score;
                            } else {
                                $score = '-';
                            }
                            $row[] = $score;
                            $row[] = $attemptglobals->completion ?? '-';
                            $row[] = $attemptglobals->timespent ?? '-';
                            $row[] = $attemptglobals->progress ?? '-';
                        } else {
                            // Aucune tentative : valeurs vides.
                            $row[] = '-';
                            $row[] = '-';
                            $row[] = '-';
                            $row[] = '-';
                        }
                    }

                    if (!$download) {
                        $table->add_data($row);
                    } else if ($download == 'Excel' || $download == 'ODS') {
                        $colnum = 0;
                        foreach ($row as $item) {
                            $myxls->write($rownum, $colnum, $item, $format);
                            $colnum++;
                        }
                        $rownum++;
                    } else if ($download == 'CSV') {
                        $csvexport->add_data($row);
                    }
                }
                if (!$download) {
                    $table->finish_output();
                }
            } else {
                echo \html_writer::end_div();
            }
            // Show preferences form irrespective of attempts are there to report or not.
            if (!$download) {
                $mform->set_data(compact('pagesize', 'attemptsmode'));
                $mform->display();
            }
            if ($download == 'Excel' || $download == 'ODS') {
                $workbook->close();
                exit;
            } else if ($download == 'CSV') {
                $csvexport->download_file();
                exit;
            }
        } else {
            echo $OUTPUT->notification(get_string('noactivity', 'deffinum'));
        }
    }// Function ends.

    /**
     * Render the detailed report for ONE attempt (user-level view).
     *
     * Contract
     * --------
     * • $deffinumid : primary-key of the DEFFINUM activity.
     * • $userid     : primary-key of the learner.
     * • $attemptno  : logical attempt number (1-based, {@link deffinum_attempt.attempt}).
     * • $cm         : course-module record (already fetched by caller).
     *
     * @throws \moodle_exception if the required data cannot be found.
     */
    private function render_detail(int $deffinumid,
                                   int $userid,
                                   int $attemptno,
                                   \stdClass $cm): void {
        global $DB, $OUTPUT, $PAGE;

        /* --------------------------------------------------------------------
         * 1.  Context & permission check
         * ------------------------------------------------------------------ */
        $context = \context_module::instance($cm->id);
        require_capability('mod/deffinum:viewreport', $context);

        /* --------------------------------------------------------------------
         * 2.  Retrieve core DB records
         * ------------------------------------------------------------------ */
        $attempt = $DB->get_record('deffinum_attempt', [
            'deffinumid' => $deffinumid,
            'userid'     => $userid,
            'attempt'    => $attemptno,
        ], '*', MUST_EXIST);

        $user      = $DB->get_record('user',     ['id' => $userid],     '*', MUST_EXIST);
        $deffinum  = $DB->get_record('deffinum', ['id' => $deffinumid], '*', MUST_EXIST);

        /* --------------------------------------------------------------------
         * 3.  Fetch SCORM values required by the template
         * ------------------------------------------------------------------ */
        $needed = [
            'detailed_progress',
            'cmi.completion_status', 'cmi.core.lesson_status',
            'cmi.score.raw', 'cmi.score.max',
            'cmi.session_time', 'cmi.learning_time',
            'cmi.progress_measure',
        ];
        $records = $this->fetch_attempt_scorm_values($deffinumid, $userid, $attemptno, $needed);

        if (!$records) {
            throw new \moodle_exception(
                'no attempt data found for attempt number '.$attemptno.
                " (user={$userid}, deffinum={$deffinumid})"
            );
        }
        if (empty($records['detailed_progress'])) {
            throw new \moodle_exception(
                'detailed_progress not found for attempt number '.$attemptno.
                " (user={$userid}, deffinum={$deffinumid})"
            );
        }

        /* --------------------------------------------------------------------
         * 4.  Build Mustache context
         * ------------------------------------------------------------------ */
        $templatecontext = json_parser::parse_from_string($records['detailed_progress']);

        // SCORM element  ➜ Mustache key inside $templatecontext['cmi'].
        $map = [
            'cmi.completion_status'  => 'completion_status',
            'cmi.core.lesson_status' => 'completion_status', // SCORM 1.2 fallback.
            'cmi.score.raw'          => 'score_raw',
            'cmi.score.max'          => 'score_max',
            'cmi.session_time'       => 'session_time',
            'cmi.learning_time'      => 'session_time',      // SCORM 1.2 fallback.
            'cmi.progress_measure'   => 'progress_measure',
        ];
        foreach ($map as $element => $key) {
            if (isset($records[$element])) {
                $templatecontext['cmi'][$key] = $records[$element];
            }
        }

        /* --------------------------------------------------------------------
         * 5.  Friendly decorations (badges, percentages, title, caps…)
         * ------------------------------------------------------------------ */
        // Completion badge flags (passed / incomplete / notattempted).
        if (!empty($templatecontext['cmi']['completion_status'])) {
            $raw = str_replace(' ', '', $templatecontext['cmi']['completion_status']);
            $templatecontext['cmi']['completion_status_raw'] = $raw;
            $templatecontext['cmi']['completion_status']     = get_string($raw, 'deffinumreport_detailed');
            $templatecontext['completion_'.$raw] = true;
        }

        // Progress measure 0–1  ➜ percentage.
        if (isset($templatecontext['cmi']['progress_measure'])) {
            $templatecontext['cmi']['progress_measure'] =
                round((float)$templatecontext['cmi']['progress_measure'] * 100, 2);
        }

        // Page title.
        $strattempt                = get_string('attempt', 'deffinumreport_detailed');
        $templatecontext['title']  = fullname($user).' – '.format_string($deffinum->name).
            " – $strattempt #{$attemptno}";

        // Capability flags for conditional blocks in template.
        foreach ([
                     'canviewcorrectorder',
                     'canviewlevel',
                     'canviewnbtry',
                     'canviewscore',
                     'canviewscoremax',
                     'canviewtimespent',
                 ] as $cap) {
            $templatecontext[$cap] = has_capability("deffinumreport/detailed:$cap", $context);
        }

        /* --------------------------------------------------------------------
         * 6.  UI chrome : action-bar, back button, PDF button
         * ------------------------------------------------------------------ */
        echo '<div id="detailed-report-header" class="row d-flex flex-wrap align-items-center col-lg-6 col-md-12">';

        // 6.1  “Back” button – returns to the summary list.
        $backurl = new \moodle_url('/mod/deffinum/report.php', [
            'id'   => $cm->id,
            'mode' => 'detailed',   // Stay in this plugin.
            // Omit user/attempt to fall back to summary.
        ]);
        echo $OUTPUT->single_button($backurl, get_string('back'), 'get');

        // 6.2  Action-bar (switch to other report plugins, keep SCORM consistency).
        $renderer   = $PAGE->get_renderer('mod_deffinum');
        $actionbar  = new \mod_deffinum\output\actionbar($cm->id, false, 0);
        echo $renderer->report_actionbar($actionbar);

        // 6.3  “Export PDF” button – triggers window.print().
        $pdfbuttonid = \html_writer::random_id('btnpdf');
        echo \html_writer::tag('button',
            get_string('print', 'deffinumreport_detailed'),
            ['type'  => 'button', 'id' => $pdfbuttonid,
                'class' => 'btn btn-primary']);

        // Attach the JS handler once.
        $PAGE->requires->js_init_code("
            document.getElementById('$pdfbuttonid')
                .addEventListener('click', () => window.print());
        ");
        echo '</div>';

        /* --------------------------------------------------------------------
         * 7.  Render template
         * ------------------------------------------------------------------ */
        $PAGE->set_title($templatecontext['title']);
        $PAGE->set_heading(fullname($user));

        echo $OUTPUT->render_from_template('deffinumreport_detailed/report', $templatecontext);
    }

    /**
     * Fetches the latest value for each requested SCORM element in a given
     * learner / attempt.
     *
     * @param int   $deffinumid  DEFFINUM activity ID.
     * @param int   $userid      Learner’s user ID.
     * @param int   $attemptno   Logical attempt number (1-based, as stored in
     *                           {@link mdl_deffinum_attempt.attempt}).
     * @param array $elements    List of SCORM element strings to retrieve
     *                           (e.g. `['cmi.session_time', …]`).
     * @return array             `[ element-name => value ]`, keeping only the most
     *                           recent value per element.
     */
    private function fetch_attempt_scorm_values(int $deffinumid, int $userid, int $attemptno, array $elements): array {
        global $DB;

        // Build the “IN (…)” clause safely.
        list($insql, $inparams) = $DB->get_in_or_equal($elements, SQL_PARAMS_NAMED);

        $sql = "
            SELECT e.element, v.value
              FROM {deffinum_scoes_value} v
              JOIN {deffinum_element}   e ON e.id = v.elementid
              JOIN {deffinum_attempt}   a ON a.id = v.attemptid
             WHERE a.deffinumid = :deffinumid
               AND a.userid     = :userid
               AND a.attempt    = :attemptno      -- logical attempt #
               AND e.element    $insql
             ORDER BY v.timemodified DESC         -- newest first
        ";

        $params = ['deffinumid' => $deffinumid, 'userid'     => $userid, 'attemptno'  => $attemptno] + $inparams;

        $records = $DB->get_records_sql($sql, $params);

        // Keep only the very first (i.e. most recent) entry for each element.
        $out = [];
        foreach ($records as $rec) {
            if (!isset($out[$rec->element])) {
                $out[$rec->element] = $rec->value;
            }
        }

        return $out;
    }

    /**
     * Return the 3 global SCORM values used in the attempt summary table.
     *
     * @param int $deffinumid  Activity id (mod_deffinum).
     * @param int $userid      Learner id.
     * @param int $attemptno   Logical attempt number (1-based).
     * @return \stdClass       → completion (string, localised)
     *                         → timespent (HH:MM:SS)
     *                         → progress  (string “85 %”) – unset when missing.
     */
    private function get_attempt_globals(int $deffinumid, int $userid, int $attemptno): \stdClass {

        // SCORM element  ➜ logical key used by the UI.
        $map = [
            'cmi.score.raw'          => 'score',
            'cmi.score.max'          => 'scoremax',
            'cmi.completion_status'  => 'completion',
            'cmi.core.lesson_status' => 'completion',   // SCORM 1.2 fallback.
            'cmi.session_time'       => 'timespent',
            'cmi.learning_time'      => 'timespent',    // SCORM 1.2 fallback.
            'cmi.progress_measure'   => 'progress',
        ];

        // Retrieve the *latest* value for each requested element.
        $vals = $this->fetch_attempt_scorm_values(
            $deffinumid,
            $userid,
            $attemptno,
            array_keys($map)
        );

        $out = new \stdClass();
        foreach ($vals as $element => $value) {
            $key = $map[$element];

            switch ($key) {
                case 'progress':
                    // Normalise 0–1 → percentage without decimals.
                    $out->progress = round((float) $value * 100) . '%';
                    break;

                case 'completion':
                    // Localise the completion status (passed / incomplete / notattempted).
                    $raw = str_replace(' ', '', $value);               // Example: "not attempted" → "notattempted".
                    $out->completion = get_string($raw, 'deffinumreport_detailed');
                    break;

                default: // Timespent.
                    $out->$key = $value;
            }
        }

        return $out;
    }

}
