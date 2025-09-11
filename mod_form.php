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

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    //  It must be included from a Moodle page.
}

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/mod/deffinum/locallib.php');

class mod_deffinum_mod_form extends moodleform_mod {

    public function definition() {
        global $CFG, $COURSE, $OUTPUT;
        $cfgdeffinum = get_config('deffinum');

        $mform = $this->_form;

        if (!$CFG->slasharguments) {
            $mform->addElement('static', '', '', $OUTPUT->notification(get_string('slashargs', 'deffinum'), 'notifyproblem'));
        }

        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Name.
        $mform->addElement('text', 'name', get_string('name'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        // Summary.
        $this->standard_intro_elements();

        // BEGIN DEFFINUM CUSTOMIZATION.
//        // Package.
//        $mform->addElement('header', 'packagehdr', get_string('packagehdr', 'deffinum'));
//        $mform->setExpanded('packagehdr', true);
//
//        // Deffinum types.
//        $deffinumtypes = array(DEFFINUM_TYPE_LOCAL => get_string('typelocal', 'deffinum'));
//
//        if ($cfgdeffinum->allowtypeexternal) {
//            $deffinumtypes[DEFFINUM_TYPE_EXTERNAL] = get_string('typeexternal', 'deffinum');
//        }
//
//        if ($cfgdeffinum->allowtypelocalsync) {
//            $deffinumtypes[DEFFINUM_TYPE_LOCALSYNC] = get_string('typelocalsync', 'deffinum');
//        }
//
//        if ($cfgdeffinum->allowtypeexternalaicc) {
//            $deffinumtypes[DEFFINUM_TYPE_AICCURL] = get_string('typeaiccurl', 'deffinum');
//        }
//
//        // Reference.
//        if (count($deffinumtypes) > 1) {
//            $mform->addElement('select', 'deffinumtype', get_string('deffinumtype', 'deffinum'), $deffinumtypes);
//            $mform->setType('deffinumtype', PARAM_ALPHA);
//            $mform->addHelpButton('deffinumtype', 'deffinumtype', 'deffinum');
//            $mform->addElement('text', 'packageurl', get_string('packageurl', 'deffinum'), array('size' => 60));
//            $mform->setType('packageurl', PARAM_RAW);
//            $mform->addHelpButton('packageurl', 'packageurl', 'deffinum');
//            $mform->hideIf('packageurl', 'deffinumtype', 'eq', DEFFINUM_TYPE_LOCAL);
//        } else {
//            $mform->addElement('hidden', 'deffinumtype', DEFFINUM_TYPE_LOCAL);
//            $mform->setType('deffinumtype', PARAM_ALPHA);
//        }

        // Resource.
        $mform->addElement('header', 'resourcehdr', get_string('resourcehdr', 'deffinum'));
        $mform->setExpanded('resourcehdr', true);

        // New local package upload.
        $filemanageroptions = array();
//        $filemanageroptions['accepted_types'] = array('.zip', '.xml');
        $filemanageroptions['maxbytes'] = 0;
        $filemanageroptions['maxfiles'] = 1;
        $filemanageroptions['subdirs'] = 0;

//        $mform->addElement('filemanager', 'packagefile', get_string('package', 'deffinum'), null, $filemanageroptions);
//        $mform->addHelpButton('packagefile', 'package', 'deffinum');
//        $mform->hideIf('packagefile', 'deffinumtype', 'noteq', DEFFINUM_TYPE_LOCAL);

        // CUSTOMTYPE : 360, augmented_reality, virtual_reality or serious_game
        $mform->addElement('select', 'customtype', get_string('customtype', 'deffinum'), [
                DEFFINUM_CUSTOMTYPE_360 => get_string('customtype_360', 'deffinum'),
                DEFFINUM_CUSTOMTYPE_AUGMENTED_REALITY => get_string('customtype_augmented_reality', 'deffinum'),
                DEFFINUM_CUSTOMTYPE_VIRTUAL_REALITY => get_string('customtype_virtual_reality', 'deffinum'),
                //DEFFINUM_CUSTOMTYPE_SERIOUS_GAME => get_string('customtype_serious_game', 'deffinum'),
        ]);
        $mform->setType('customtype', PARAM_RAW);

        // For DEFFINUM_CUSTOMTYPE_AUGMENTED_REALITY only.
        $mform->addElement('filemanager', 'resourcefile', get_string('resource', 'deffinum'), null, $filemanageroptions);
        $mform->addHelpButton('resourcefile', 'resource', 'deffinum');
        $mform->hideIf('resourcefile', 'customtype', 'noteq', DEFFINUM_CUSTOMTYPE_AUGMENTED_REALITY);

        // For DEFFINUM_CUSTOMTYPE_360 only.
        $mform->addElement('url', 'visiturl', get_string('visiturl', 'deffinum'));
        $mform->setType('visiturl', PARAM_RAW);
        $mform->hideIf('visiturl', 'customtype', 'noteq', DEFFINUM_CUSTOMTYPE_360);

        // For DEFFINUM_CUSTOMTYPE_VIRTUAL_REALITY only.
        $mform->addElement('url', 'vrurl', get_string('vrurl', 'deffinum'));
        $mform->setType('vrurl', PARAM_RAW);
        $mform->hideIf('vrurl', 'customtype', 'noteq', DEFFINUM_CUSTOMTYPE_VIRTUAL_REALITY);
        // END DEFFINUM CUSTOMIZATION.

        // Update packages timing.
        $mform->addElement('select', 'updatefreq', get_string('updatefreq', 'deffinum'), deffinum_get_updatefreq_array());
        $mform->setType('updatefreq', PARAM_INT);
        $mform->setDefault('updatefreq', $cfgdeffinum->updatefreq);
        $mform->addHelpButton('updatefreq', 'updatefreq', 'deffinum');

        // Display Settings.
        $mform->addElement('header', 'displaysettings', get_string('appearance'));

        // Framed / Popup Window.
        $mform->addElement('select', 'popup', get_string('display', 'deffinum'), deffinum_get_popup_display_array());
        $mform->setDefault('popup', $cfgdeffinum->popup);
        $mform->setAdvanced('popup', $cfgdeffinum->popup_adv);

        // Width.
        $mform->addElement('text', 'width', get_string('width', 'deffinum'), 'maxlength="5" size="5"');
        $mform->setDefault('width', $cfgdeffinum->framewidth);
        $mform->setType('width', PARAM_INT);
        $mform->setAdvanced('width', $cfgdeffinum->framewidth_adv);
        $mform->hideIf('width', 'popup', 'eq', 0);

        // Height.
        $mform->addElement('text', 'height', get_string('height', 'deffinum'), 'maxlength="5" size="5"');
        $mform->setDefault('height', $cfgdeffinum->frameheight);
        $mform->setType('height', PARAM_INT);
        $mform->setAdvanced('height', $cfgdeffinum->frameheight_adv);
        $mform->hideIf('height', 'popup', 'eq', 0);

        // Window Options.
        $winoptgrp = array();
        foreach (deffinum_get_popup_options_array() as $key => $value) {
            $winoptgrp[] = &$mform->createElement('checkbox', $key, '', get_string($key, 'deffinum'));
            $mform->setDefault($key, $value);
        }
        $mform->addGroup($winoptgrp, 'winoptgrp', get_string('options', 'deffinum'), '<br />', false);
        $mform->hideIf('winoptgrp', 'popup', 'eq', 0);
        $mform->setAdvanced('winoptgrp', $cfgdeffinum->winoptgrp_adv);

        // Skip view page.
        $skipviewoptions = deffinum_get_skip_view_array();
        $mform->addElement('select', 'skipview', get_string('skipview', 'deffinum'), $skipviewoptions);
        $mform->addHelpButton('skipview', 'skipview', 'deffinum');
        $mform->setDefault('skipview', $cfgdeffinum->skipview);
        $mform->setAdvanced('skipview', $cfgdeffinum->skipview_adv);

        // Hide Browse.
        $mform->addElement('selectyesno', 'hidebrowse', get_string('hidebrowse', 'deffinum'));
        $mform->addHelpButton('hidebrowse', 'hidebrowse', 'deffinum');
        $mform->setDefault('hidebrowse', $cfgdeffinum->hidebrowse);
        $mform->setAdvanced('hidebrowse', $cfgdeffinum->hidebrowse_adv);

        // Display course structure.
        $mform->addElement('selectyesno', 'displaycoursestructure', get_string('displaycoursestructure', 'deffinum'));
        $mform->addHelpButton('displaycoursestructure', 'displaycoursestructure', 'deffinum');
        $mform->setDefault('displaycoursestructure', $cfgdeffinum->displaycoursestructure);
        $mform->setAdvanced('displaycoursestructure', $cfgdeffinum->displaycoursestructure_adv);

        // Toc display.
        $mform->addElement('select', 'hidetoc', get_string('hidetoc', 'deffinum'), deffinum_get_hidetoc_array());
        $mform->addHelpButton('hidetoc', 'hidetoc', 'deffinum');
        $mform->setDefault('hidetoc', $cfgdeffinum->hidetoc);
        $mform->setAdvanced('hidetoc', $cfgdeffinum->hidetoc_adv);
        $mform->disabledIf('hidetoc', 'deffinumtype', 'eq', DEFFINUM_TYPE_AICCURL);

        // Navigation panel display.
        $mform->addElement('select', 'nav', get_string('nav', 'deffinum'), deffinum_get_navigation_display_array());
        $mform->addHelpButton('nav', 'nav', 'deffinum');
        $mform->setDefault('nav', $cfgdeffinum->nav);
        $mform->setAdvanced('nav', $cfgdeffinum->nav_adv);
        $mform->hideIf('nav', 'hidetoc', 'noteq', DEFFINUM_TOC_SIDE);

        // Navigation panel position from left.
        $mform->addElement('text', 'navpositionleft', get_string('fromleft', 'deffinum'), 'maxlength="5" size="5"');
        $mform->setDefault('navpositionleft', $cfgdeffinum->navpositionleft);
        $mform->setType('navpositionleft', PARAM_INT);
        $mform->setAdvanced('navpositionleft', $cfgdeffinum->navpositionleft_adv);
        $mform->hideIf('navpositionleft', 'hidetoc', 'noteq', DEFFINUM_TOC_SIDE);
        $mform->hideIf('navpositionleft', 'nav', 'noteq', DEFFINUM_NAV_FLOATING);

        // Navigation panel position from top.
        $mform->addElement('text', 'navpositiontop', get_string('fromtop', 'deffinum'), 'maxlength="5" size="5"');
        $mform->setDefault('navpositiontop', $cfgdeffinum->navpositiontop);
        $mform->setType('navpositiontop', PARAM_INT);
        $mform->setAdvanced('navpositiontop', $cfgdeffinum->navpositiontop_adv);
        $mform->hideIf('navpositiontop', 'hidetoc', 'noteq', DEFFINUM_TOC_SIDE);
        $mform->hideIf('navpositiontop', 'nav', 'noteq', DEFFINUM_NAV_FLOATING);

        // Display attempt status.
        $mform->addElement('select', 'displayattemptstatus', get_string('displayattemptstatus', 'deffinum'),
                           deffinum_get_attemptstatus_array());
        $mform->addHelpButton('displayattemptstatus', 'displayattemptstatus', 'deffinum');
        $mform->setDefault('displayattemptstatus', $cfgdeffinum->displayattemptstatus);
        $mform->setAdvanced('displayattemptstatus', $cfgdeffinum->displayattemptstatus_adv);

        // Availability.
        $mform->addElement('header', 'availability', get_string('availability'));

        $mform->addElement('date_time_selector', 'timeopen', get_string("deffinumopen", "deffinum"), array('optional' => true));
        $mform->addElement('date_time_selector', 'timeclose', get_string("deffinumclose", "deffinum"), array('optional' => true));

        // Grade Settings.
        $mform->addElement('header', 'gradesettings', get_string('gradenoun'));

        // Grade Method.
        $mform->addElement('select', 'grademethod', get_string('grademethod', 'deffinum'), deffinum_get_grade_method_array());
        $mform->addHelpButton('grademethod', 'grademethod', 'deffinum');
        $mform->setDefault('grademethod', $cfgdeffinum->grademethod);

        // Maximum Grade.
        for ($i = 0; $i <= 100; $i++) {
            $grades[$i] = "$i";
        }
        $mform->addElement('select', 'maxgrade', get_string('maximumgrade'), $grades);
        $mform->setDefault('maxgrade', $cfgdeffinum->maxgrade);
        $mform->hideIf('maxgrade', 'grademethod', 'eq', GRADESCOES);

        // Attempts management.
        $mform->addElement('header', 'attemptsmanagementhdr', get_string('attemptsmanagement', 'deffinum'));

        // Max Attempts.
        $mform->addElement('select', 'maxattempt', get_string('maximumattempts', 'deffinum'), deffinum_get_attempts_array());
        $mform->addHelpButton('maxattempt', 'maximumattempts', 'deffinum');
        $mform->setDefault('maxattempt', $cfgdeffinum->maxattempt);

        // What Grade.
        $mform->addElement('select', 'whatgrade', get_string('whatgrade', 'deffinum'),  deffinum_get_what_grade_array());
        $mform->hideIf('whatgrade', 'maxattempt', 'eq', 1);
        $mform->addHelpButton('whatgrade', 'whatgrade', 'deffinum');
        $mform->setDefault('whatgrade', $cfgdeffinum->whatgrade);

        // Force new attempt.
        $newattemptselect = deffinum_get_forceattempt_array();
        $mform->addElement('select', 'forcenewattempt', get_string('forcenewattempts', 'deffinum'), $newattemptselect);
        $mform->addHelpButton('forcenewattempt', 'forcenewattempts', 'deffinum');
        $mform->setDefault('forcenewattempt', $cfgdeffinum->forcenewattempt);

        // Last attempt lock - lock the enter button after the last available attempt has been made.
        $mform->addElement('selectyesno', 'lastattemptlock', get_string('lastattemptlock', 'deffinum'));
        $mform->addHelpButton('lastattemptlock', 'lastattemptlock', 'deffinum');
        $mform->setDefault('lastattemptlock', $cfgdeffinum->lastattemptlock);

        // Compatibility settings.
        $mform->addElement('header', 'compatibilitysettingshdr', get_string('compatibilitysettings', 'deffinum'));

        // Force completed.
        $mform->addElement('selectyesno', 'forcecompleted', get_string('forcecompleted', 'deffinum'));
        $mform->addHelpButton('forcecompleted', 'forcecompleted', 'deffinum');
        $mform->setDefault('forcecompleted', $cfgdeffinum->forcecompleted);

        // Autocontinue.
        $mform->addElement('selectyesno', 'auto', get_string('autocontinue', 'deffinum'));
        $mform->addHelpButton('auto', 'autocontinue', 'deffinum');
        $mform->setDefault('auto', $cfgdeffinum->auto);

        // Autocommit.
        $mform->addElement('selectyesno', 'autocommit', get_string('autocommit', 'deffinum'));
        $mform->addHelpButton('autocommit', 'autocommit', 'deffinum');
        $mform->setDefault('autocommit', $cfgdeffinum->autocommit);

        // Mastery score overrides status.
        $mform->addElement('selectyesno', 'masteryoverride', get_string('masteryoverride', 'deffinum'));
        $mform->addHelpButton('masteryoverride', 'masteryoverride', 'deffinum');
        $mform->setDefault('masteryoverride', $cfgdeffinum->masteryoverride);

        // Hidden Settings.
        $mform->addElement('hidden', 'datadir', null);
        $mform->setType('datadir', PARAM_RAW);
        $mform->addElement('hidden', 'pkgtype', null);
        $mform->setType('pkgtype', PARAM_RAW);
        $mform->addElement('hidden', 'launch', null);
        $mform->setType('launch', PARAM_RAW);
        $mform->addElement('hidden', 'redirect', null);
        $mform->setType('redirect', PARAM_RAW);
        $mform->addElement('hidden', 'redirecturl', null);
        $mform->setType('redirecturl', PARAM_RAW);

        $this->standard_coursemodule_elements();

        // A DEFFINUM module should define this within itself and is not needed here.
        $suffix = $this->get_suffix();
        $completionpassgradeel = 'completionpassgrade' . $suffix;
        // The 'completionpassgrade' is a radio element with multiple options, so we should remove all of them.
        while ($mform->elementExists($completionpassgradeel)) {
            $mform->removeElement($completionpassgradeel);
        }

        // Buttons.
        $this->add_action_buttons();
    }

    public function data_preprocessing(&$defaultvalues) {
        global $CFG, $COURSE;

        if (isset($defaultvalues['popup']) && ($defaultvalues['popup'] == 1) && isset($defaultvalues['options'])) {
            if (!empty($defaultvalues['options'])) {
                $options = explode(',', $defaultvalues['options']);
                foreach ($options as $option) {
                    list($element, $value) = explode('=', $option);
                    $element = trim($element);
                    $defaultvalues[$element] = trim($value);
                }
            }
        }
        if (isset($defaultvalues['grademethod'])) {
            $defaultvalues['grademethod'] = intval($defaultvalues['grademethod']);
        }
        if (isset($defaultvalues['width']) && (strpos($defaultvalues['width'], '%') === false)
                                           && ($defaultvalues['width'] <= 100)) {
            $defaultvalues['width'] .= '%';
        }
        if (isset($defaultvalues['height']) && (strpos($defaultvalues['height'], '%') === false)
                                           && ($defaultvalues['height'] <= 100)) {
            $defaultvalues['height'] .= '%';
        }
        $deffinums = get_all_instances_in_course('deffinum', $COURSE);
        $coursedeffinum = current($deffinums);

        // BEGIN DEFFINUM CUSTOMIZATION.
//        $draftitemid = file_get_submitted_draft_itemid('packagefile');
//        file_prepare_draft_area($draftitemid, $this->context->id, 'mod_deffinum', 'package', 0,
//            array('subdirs' => 0, 'maxfiles' => 1));
//        $defaultvalues['packagefile'] = $draftitemid;
        $draftitemid = file_get_submitted_draft_itemid('resourcefile');
        file_prepare_draft_area($draftitemid, $this->context->id, 'mod_deffinum', 'resource', 0,
            array('subdirs' => 0, 'maxfiles' => 1));
        $defaultvalues['resourcefile'] = $draftitemid;
        // END DEFFINUM CUSTOMIZATION.

        if (($COURSE->format == 'singleactivity') && ((count($deffinums) == 0) || ($defaultvalues['instance'] == $coursedeffinum->id))) {
            $defaultvalues['redirect'] = 'yes';
            $defaultvalues['redirecturl'] = $CFG->wwwroot.'/course/view.php?id='.$defaultvalues['course'];
        } else {
            $defaultvalues['redirect'] = 'no';
            $defaultvalues['redirecturl'] = $CFG->wwwroot.'/mod/deffinum/view.php?id='.$defaultvalues['coursemodule'];
        }
        if (isset($defaultvalues['version'])) {
            $defaultvalues['pkgtype'] = (substr($defaultvalues['version'], 0, 5) == 'DEFFINUM') ? 'deffinum' : 'aicc';
        }
        if (isset($defaultvalues['instance'])) {
            $defaultvalues['datadir'] = $defaultvalues['instance'];
        }
        if (empty($defaultvalues['timeopen'])) {
            $defaultvalues['timeopen'] = 0;
        }
        if (empty($defaultvalues['timeclose'])) {
            $defaultvalues['timeclose'] = 0;
        }

        // Set some completion default data.
        $suffix = $this->get_suffix();
        $completionstatusrequiredel = 'completionstatusrequired' . $suffix;
        $cvalues = array();
        if (!empty($defaultvalues[$completionstatusrequiredel]) && !is_array($defaultvalues[$completionstatusrequiredel])) {
            // Unpack values.
            foreach (deffinum_status_options() as $key => $value) {
                if (($defaultvalues[$completionstatusrequiredel] & $key) == $key) {
                    $cvalues[$key] = 1;
                }
            }
        } else if (empty($this->_instance) && !array_key_exists($completionstatusrequiredel, $defaultvalues)) {
            // When in add mode, set a default completion rule that requires the DEFFINUM's status be set to "Completed".
            $cvalues[4] = 1;
        }

        if (!empty($cvalues)) {
            $defaultvalues[$completionstatusrequiredel] = $cvalues;
        }

        $completionscorerequiredel = 'completionscorerequired' . $suffix;
        if (isset($defaultvalues[$completionscorerequiredel])) {
            $completionscoreenabledel = 'completionscoreenabled' . $suffix;
            $defaultvalues[$completionscoreenabledel] = 1;
        }

        // BEGIN DEFFINUM CUSTOMIZATION.
        if (!empty($defaultvalues['customdata']) && $defaultvalues['customtype'] === DEFFINUM_CUSTOMTYPE_VIRTUAL_REALITY) {
            $defaultvalues['vrurl'] = $defaultvalues['customdata'];
        }

        if (!empty($defaultvalues['customdata']) && $defaultvalues['customtype'] === DEFFINUM_CUSTOMTYPE_360) {
            $defaultvalues['visiturl'] = $defaultvalues['customdata'];
        }
        // END DEFFINUM CUSTOMIZATION.
    }

    // BEGIN DEFFINUM CUSTOMIZATION.
//    public function validation($data, $files) {
//        global $CFG, $USER;
//        $errors = parent::validation($data, $files);
//
//        $type = $data['deffinumtype'];
//
//        if ($type === DEFFINUM_TYPE_LOCAL) {
//            if (empty($data['packagefile'])) {
//                $errors['packagefile'] = get_string('required');
//
//            } else {
//                $draftitemid = file_get_submitted_draft_itemid('packagefile');
//
//                file_prepare_draft_area($draftitemid, $this->context->id, 'mod_deffinum', 'packagefilecheck', null,
//                    array('subdirs' => 0, 'maxfiles' => 1));
//
//                // Get file from users draft area.
//                $usercontext = context_user::instance($USER->id);
//                $fs = get_file_storage();
//                $files = $fs->get_area_files($usercontext->id, 'user', 'draft', $draftitemid, 'id', false);
//
//                if (count($files) < 1) {
//                    $errors['packagefile'] = get_string('required');
//                    return $errors;
//                }
//                $file = reset($files);
//                if (!$file->is_external_file() && !empty($data['updatefreq'])) {
//                    // Make sure updatefreq is not set if using normal local file.
//                    $errors['updatefreq'] = get_string('updatefreq_error', 'mod_deffinum');
//                }
//                if (strtolower($file->get_filename()) == 'imsmanifest.xml') {
//                    if (!$file->is_external_file()) {
//                        $errors['packagefile'] = get_string('aliasonly', 'mod_deffinum');
//                    } else {
//                        $repository = repository::get_repository_by_id($file->get_repository_id(), context_system::instance());
//                        if (!$repository->supports_relative_file()) {
//                            $errors['packagefile'] = get_string('repositorynotsupported', 'mod_deffinum');
//                        }
//                    }
//                } else if (strtolower(substr($file->get_filename(), -3)) == 'xml') {
//                    $errors['packagefile'] = get_string('invalidmanifestname', 'mod_deffinum');
//                } else {
//                    // Validate this DEFFINUM package.
//                    $errors = array_merge($errors, deffinum_validate_package($file));
//                }
//            }
//
//        } else if ($type === DEFFINUM_TYPE_EXTERNAL) {
//            $reference = $data['packageurl'];
//            // Syntax check.
//            if (!preg_match('/(http:\/\/|https:\/\/|www).*\/imsmanifest.xml$/i', $reference)) {
//                $errors['packageurl'] = get_string('invalidurl', 'deffinum');
//            } else {
//                // Availability check.
//                $result = deffinum_check_url($reference);
//                if (is_string($result)) {
//                    $errors['packageurl'] = $result;
//                }
//            }
//
//        } else if ($type === 'packageurl') {
//            $reference = $data['reference'];
//            // Syntax check.
//            if (!preg_match('/(http:\/\/|https:\/\/|www).*(\.zip|\.pif)$/i', $reference)) {
//                $errors['packageurl'] = get_string('invalidurl', 'deffinum');
//            } else {
//                // Availability check.
//                $result = deffinum_check_url($reference);
//                if (is_string($result)) {
//                    $errors['packageurl'] = $result;
//                }
//            }
//
//        } else if ($type === DEFFINUM_TYPE_AICCURL) {
//            $reference = $data['packageurl'];
//            // Syntax check.
//            if (!preg_match('/(http:\/\/|https:\/\/|www).*/', $reference)) {
//                $errors['packageurl'] = get_string('invalidurl', 'deffinum');
//            } else {
//                // Availability check.
//                $result = deffinum_check_url($reference);
//                if (is_string($result)) {
//                    $errors['packageurl'] = $result;
//                }
//            }
//
//        }
//
//        // Validate availability dates.
//        if ($data['timeopen'] && $data['timeclose']) {
//            if ($data['timeopen'] > $data['timeclose']) {
//                $errors['timeclose'] = get_string('closebeforeopen', 'deffinum');
//            }
//        }
//        $suffix = $this->get_suffix();
//        $completionstatusallscosel = 'completionstatusallscos' . $suffix;
//        if (!empty($data[$completionstatusallscosel])) {
//            $completionstatusrequiredel = 'completionstatusrequired' . $suffix;
//            $requirestatus = false;
//            foreach (deffinum_status_options(true) as $key => $value) {
//                if (!empty($data[$completionstatusrequiredel][$key])) {
//                    $requirestatus = true;
//                }
//            }
//            if (!$requirestatus) {
//                $errors[$completionstatusallscosel] = get_string('youmustselectastatus', 'deffinum');
//            }
//        }
//
//        // Validate 'Require minimum score' value.
//        $completionscorerequiredel = 'completionscorerequired' . $this->get_suffix();
//        $completionscoreenabledel = 'completionscoreenabled' . $this->get_suffix();
//        if (array_key_exists($completionscoreenabledel, $data) &&
//            $data[$completionscoreenabledel] &&
//            array_key_exists($completionscorerequiredel, $data) &&
//            strlen($data[$completionscorerequiredel]) &&
//            $data[$completionscorerequiredel] <= 0
//        ) {
//            $errors['completionscoregroup' . $this->get_suffix()] = get_string('minimumscoregreater', 'deffinum');
//        }
//
//        return $errors;
//    }

    public function validation($data, $files) {
        global $CFG, $USER;
        $errors = parent::validation($data, $files);

        // Handle resource file in Augmented Reality.
        if ($data['customtype'] === DEFFINUM_CUSTOMTYPE_AUGMENTED_REALITY) {
            if (empty($data['resourcefile'])) {
                $errors['resourcefile'] = get_string('required');

            } else {
                $draftitemid = file_get_submitted_draft_itemid('resourcefile');

                file_prepare_draft_area($draftitemid, $this->context->id, 'mod_deffinum', 'resourcefilecheck', null,
                        array('subdirs' => 0, 'maxfiles' => 1));

                // Get file from users draft area.
                $usercontext = context_user::instance($USER->id);
                $fs = get_file_storage();
                $files = $fs->get_area_files($usercontext->id, 'user', 'draft', $draftitemid, 'id', false);

                if (count($files) < 1) {
                    $errors['resourcefile'] = get_string('required');
                    return $errors;
                }
                $file = reset($files);
                if (!$file->is_external_file() && !empty($data['updatefreq'])) {
                    // Make sure updatefreq is not set if using normal local file.
                    $errors['updatefreq'] = get_string('updatefreq_error', 'mod_deffinum');
                }
            }
        }

        // Handle visit URL in 360 type.
        if ($data['customtype'] === DEFFINUM_CUSTOMTYPE_360) {
            if (empty($data['visiturl'])) {
                $errors['visiturl'] = get_string('required');
            } elseif (!mod_deffinum_domain_is_allowed($data['visiturl'])) {
                $errors['visiturl'] = get_string('domainnotallowed', 'mod_deffinum');
            }
        }

        // Handle connexion URL in Virtual Reality type.
        if ($data['customtype'] === DEFFINUM_CUSTOMTYPE_VIRTUAL_REALITY) {
            if (empty($data['vrurl'])) {
                $errors['vrurl'] = get_string('required');
            } elseif (!mod_deffinum_domain_is_allowed($data['vrurl'])) {
                $errors['vrurl'] = get_string('domainnotallowed', 'mod_deffinum');
            }
        }


        // Validate availability dates.
        if ($data['timeopen'] && $data['timeclose']) {
            if ($data['timeopen'] > $data['timeclose']) {
                $errors['timeclose'] = get_string('closebeforeopen', 'deffinum');
            }
        }
        $suffix = $this->get_suffix();
        $completionstatusallscosel = 'completionstatusallscos' . $suffix;
        if (!empty($data[$completionstatusallscosel])) {
            $completionstatusrequiredel = 'completionstatusrequired' . $suffix;
            $requirestatus = false;
            foreach (deffinum_status_options(true) as $key => $value) {
                if (!empty($data[$completionstatusrequiredel][$key])) {
                    $requirestatus = true;
                }
            }
            if (!$requirestatus) {
                $errors[$completionstatusallscosel] = get_string('youmustselectastatus', 'deffinum');
            }
        }

        // Validate 'Require minimum score' value.
        $completionscorerequiredel = 'completionscorerequired' . $this->get_suffix();
        $completionscoreenabledel = 'completionscoreenabled' . $this->get_suffix();
        if (array_key_exists($completionscoreenabledel, $data) &&
                $data[$completionscoreenabledel] &&
                array_key_exists($completionscorerequiredel, $data) &&
                strlen($data[$completionscorerequiredel]) &&
                $data[$completionscorerequiredel] <= 0
        ) {
            $errors['completionscoregroup' . $this->get_suffix()] = get_string('minimumscoregreater', 'deffinum');
        }

        return $errors;
    }
    // END DEFFINUM CUSTOMIZATION.

    // Need to translate the "options" and "reference" field.
    public function set_data($defaultvalues) {
        // BEGIN DEFFINUM CUSTOMIZATION.
//        $defaultvalues = (array)$defaultvalues;
//
//        if (isset($defaultvalues['deffinumtype']) and isset($defaultvalues['reference'])) {
//            switch ($defaultvalues['deffinumtype']) {
//                case DEFFINUM_TYPE_LOCALSYNC :
//                case DEFFINUM_TYPE_EXTERNAL:
//                case DEFFINUM_TYPE_AICCURL:
//                    $defaultvalues['packageurl'] = $defaultvalues['reference'];
//            }
//        }
//        unset($defaultvalues['reference']);
//
//        if (!empty($defaultvalues['options'])) {
//            $options = explode(',', $defaultvalues['options']);
//            foreach ($options as $option) {
//                $opt = explode('=', $option);
//                if (isset($opt[1])) {
//                    $defaultvalues[$opt[0]] = $opt[1];
//                }
//            }
//        }
        // END DEFFINUM CUSTOMIZATION.
        parent::set_data($defaultvalues);
    }

    public function add_completion_rules() {
        $suffix = $this->get_suffix();
        $mform =& $this->_form;
        $items = [];

        // Require score.
        $group = [];
        $completionscorerequiredel = 'completionscorerequired' . $suffix;
        $completionscoreenabledel = 'completionscoreenabled' . $suffix;
        $group[] =& $mform->createElement(
            'checkbox',
            $completionscoreenabledel,
            null,
            get_string('completionscorerequired', 'deffinum')
        );
        $group[] =& $mform->createElement('text', $completionscorerequiredel, '', ['size' => 5]);
        $mform->setType($completionscorerequiredel, PARAM_INT);
        $completionscoregroupel = 'completionscoregroup' . $suffix;
        $mform->addGroup($group, $completionscoregroupel, '', '', false);
        $mform->hideIf($completionscorerequiredel, $completionscoreenabledel, 'notchecked');
        $mform->setDefault($completionscorerequiredel, 0);

        $items[] = $completionscoregroupel;

        // Require status.
        $completionstatusrequiredel = 'completionstatusrequired' . $suffix;
        foreach (deffinum_status_options(true) as $key => $value) {
            $key = $completionstatusrequiredel . '['.$key.']';
            $mform->addElement('checkbox', $key, '', $value);
            $mform->setType($key, PARAM_BOOL);
            $mform->hideIf($key, $completionstatusrequiredel, 'notchecked');
            $items[] = $key;
        }

        $completionstatusallscosel = 'completionstatusallscos' . $suffix;
        $mform->addElement('checkbox', $completionstatusallscosel, get_string('completionstatusallscos', 'deffinum'));
        $mform->setType($completionstatusallscosel, PARAM_BOOL);
        $mform->addHelpButton($completionstatusallscosel, 'completionstatusallscos', 'deffinum');
        $mform->setDefault($completionstatusallscosel, 0);
        $items[] = $completionstatusallscosel;

        return $items;
    }

    public function completion_rule_enabled($data) {
        $suffix = $this->get_suffix();
        $status = !empty($data['completionstatusrequired' . $suffix]);
        $score = !empty($data['completionscoreenabled' . $suffix]) &&
                strlen($data['completionscorerequired' . $suffix] && $data['completionscorerequired' . $suffix] > 0);

        return $status || $score;
    }

    /**
     * Allows module to modify the data returned by form get_data().
     * This method is also called in the bulk activity completion form.
     *
     * Only available on moodleform_mod.
     *
     * @param stdClass $data the form data to be modified.
     */
    public function data_postprocessing($data) {
        parent::data_postprocessing($data);
        // Convert completionstatusrequired to a proper integer, if any.
        $total = 0;
        $suffix = $this->get_suffix();
        if (isset($data->{'completionstatusrequired' . $suffix}) && is_array($data->{'completionstatusrequired' . $suffix})) {
            foreach ($data->{'completionstatusrequired' . $suffix} as $state => $value) {
                if ($value) {
                    $total |= $state;
                }
            }
            if (!$total) {
                $total  = null;
            }
            $data->{'completionstatusrequired' . $suffix} = $total;
        }

        if (!empty($data->completionunlocked)) {
            // Turn off completion settings if the checkboxes aren't ticked.
            $completion = $data->{'completion' . $suffix};
            $autocompletion = isset($completion) && $completion == COMPLETION_TRACKING_AUTOMATIC;

            if (!(isset($data->{'completionstatusrequired' . $suffix}) && $autocompletion)) {
                $data->{'completionstatusrequired' . $suffix} = null;
            }
            // Else do nothing: completionstatusrequired has been already converted into a correct integer representation.

            if (!(isset($data->{'completionscoreenabled' . $suffix}) && $autocompletion)) {
                $data->{'completionscorerequired' . $suffix} = null;
            }
        }
    }
}
