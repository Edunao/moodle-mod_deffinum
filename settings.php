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

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    require_once($CFG->dirroot . '/mod/deffinum/locallib.php');
    $yesno = array(0 => get_string('no'),
                   1 => get_string('yes'));

    // Default display settings.
    $settings->add(new admin_setting_heading('deffinum/displaysettings', get_string('defaultdisplaysettings', 'deffinum'), ''));

    $settings->add(new admin_setting_configselect_with_advanced('deffinum/displaycoursestructure',
        get_string('displaycoursestructure', 'deffinum'), get_string('displaycoursestructuredesc', 'deffinum'),
        array('value' => 0, 'adv' => false), $yesno));

    $settings->add(new admin_setting_configselect_with_advanced('deffinum/popup',
        get_string('display', 'deffinum'), get_string('displaydesc', 'deffinum'),
        array('value' => 0, 'adv' => false), deffinum_get_popup_display_array()));

    $settings->add(new admin_setting_configtext_with_advanced('deffinum/framewidth',
        get_string('width', 'deffinum'), get_string('framewidth', 'deffinum'),
        array('value' => '100', 'adv' => true)));

    $settings->add(new admin_setting_configtext_with_advanced('deffinum/frameheight',
        get_string('height', 'deffinum'), get_string('frameheight', 'deffinum'),
        array('value' => '500', 'adv' => true)));

    $settings->add(new admin_setting_configcheckbox('deffinum/winoptgrp_adv',
         get_string('optionsadv', 'deffinum'), get_string('optionsadv_desc', 'deffinum'), 1));

    foreach (deffinum_get_popup_options_array() as $key => $value) {
        $settings->add(new admin_setting_configcheckbox('deffinum/'.$key,
            get_string($key, 'deffinum'), '', $value));
    }

    $settings->add(new admin_setting_configselect_with_advanced('deffinum/skipview',
        get_string('skipview', 'deffinum'), get_string('skipviewdesc', 'deffinum'),
        array('value' => 0, 'adv' => true), deffinum_get_skip_view_array()));

    $settings->add(new admin_setting_configselect_with_advanced('deffinum/hidebrowse',
        get_string('hidebrowse', 'deffinum'), get_string('hidebrowsedesc', 'deffinum'),
        array('value' => 0, 'adv' => true), $yesno));

    $settings->add(new admin_setting_configselect_with_advanced('deffinum/hidetoc',
        get_string('hidetoc', 'deffinum'), get_string('hidetocdesc', 'deffinum'),
        array('value' => 0, 'adv' => true), deffinum_get_hidetoc_array()));

    $settings->add(new admin_setting_configselect_with_advanced('deffinum/nav',
        get_string('nav', 'deffinum'), get_string('navdesc', 'deffinum'),
        array('value' => DEFFINUM_NAV_UNDER_CONTENT, 'adv' => true), deffinum_get_navigation_display_array()));

    $settings->add(new admin_setting_configtext_with_advanced('deffinum/navpositionleft',
        get_string('fromleft', 'deffinum'), get_string('navpositionleft', 'deffinum'),
        array('value' => -100, 'adv' => true)));

    $settings->add(new admin_setting_configtext_with_advanced('deffinum/navpositiontop',
        get_string('fromtop', 'deffinum'), get_string('navpositiontop', 'deffinum'),
        array('value' => -100, 'adv' => true)));

    $settings->add(new admin_setting_configtext_with_advanced('deffinum/collapsetocwinsize',
        get_string('collapsetocwinsize', 'deffinum'), get_string('collapsetocwinsizedesc', 'deffinum'),
        array('value' => 767, 'adv' => true)));

    $settings->add(new admin_setting_configselect_with_advanced('deffinum/displayattemptstatus',
        get_string('displayattemptstatus', 'deffinum'), get_string('displayattemptstatusdesc', 'deffinum'),
        array('value' => 1, 'adv' => false), deffinum_get_attemptstatus_array()));

    // Default grade settings.
    $settings->add(new admin_setting_heading('deffinum/gradesettings', get_string('defaultgradesettings', 'deffinum'), ''));
    $settings->add(new admin_setting_configselect('deffinum/grademethod',
        get_string('grademethod', 'deffinum'), get_string('grademethoddesc', 'deffinum'),
        GRADEHIGHEST, deffinum_get_grade_method_array()));

    for ($i = 0; $i <= 100; $i++) {
        $grades[$i] = "$i";
    }

    $settings->add(new admin_setting_configselect('deffinum/maxgrade',
        get_string('maximumgrade'), get_string('maximumgradedesc', 'deffinum'), 100, $grades));

    $settings->add(new admin_setting_heading('deffinum/othersettings', get_string('defaultothersettings', 'deffinum'), ''));

    // Default attempts settings.
    $settings->add(new admin_setting_configselect('deffinum/maxattempt',
        get_string('maximumattempts', 'deffinum'), '', '0', deffinum_get_attempts_array()));

    $settings->add(new admin_setting_configselect('deffinum/whatgrade',
        get_string('whatgrade', 'deffinum'), get_string('whatgradedesc', 'deffinum'), HIGHESTATTEMPT, deffinum_get_what_grade_array()));

    $settings->add(new admin_setting_configselect('deffinum/forcecompleted',
        get_string('forcecompleted', 'deffinum'), get_string('forcecompleteddesc', 'deffinum'), 0, $yesno));

    $forceattempts = deffinum_get_forceattempt_array();
    $settings->add(new admin_setting_configselect('deffinum/forcenewattempt',
        get_string('forcenewattempts', 'deffinum'), get_string('forcenewattempts_help', 'deffinum'), 0, $forceattempts));

    $settings->add(new admin_setting_configselect('deffinum/autocommit',
    get_string('autocommit', 'deffinum'), get_string('autocommitdesc', 'deffinum'), 0, $yesno));

    $settings->add(new admin_setting_configselect('deffinum/masteryoverride',
        get_string('masteryoverride', 'deffinum'), get_string('masteryoverridedesc', 'deffinum'), 1, $yesno));

    $settings->add(new admin_setting_configselect('deffinum/lastattemptlock',
        get_string('lastattemptlock', 'deffinum'), get_string('lastattemptlockdesc', 'deffinum'), 0, $yesno));

    $settings->add(new admin_setting_configselect('deffinum/auto',
        get_string('autocontinue', 'deffinum'), get_string('autocontinuedesc', 'deffinum'), 0, $yesno));

    $settings->add(new admin_setting_configselect('deffinum/updatefreq',
        get_string('updatefreq', 'deffinum'), get_string('updatefreqdesc', 'deffinum'), 0, deffinum_get_updatefreq_array()));

    // Admin level settings.
    $settings->add(new admin_setting_heading('deffinum/adminsettings', get_string('adminsettings', 'deffinum'), ''));

    // BEGIN DEFFINUM CUSTOMIZATION.
//    $settings->add(new admin_setting_configcheckbox('deffinum/deffinumstandard', get_string('deffinumstandard', 'deffinum'),
//                                                    get_string('deffinumstandarddesc', 'deffinum'), 0));
//
//    $settings->add(new admin_setting_configcheckbox('deffinum/allowtypeexternal', get_string('allowtypeexternal', 'deffinum'), '', 0));
//
//    $settings->add(new admin_setting_configcheckbox('deffinum/allowtypelocalsync', get_string('allowtypelocalsync', 'deffinum'), '', 0));
//
//    $settings->add(new admin_setting_configcheckbox('deffinum/allowtypeexternalaicc',
//        get_string('allowtypeexternalaicc', 'deffinum'), get_string('allowtypeexternalaicc_desc', 'deffinum'), 0));
//
//    $settings->add(new admin_setting_configcheckbox('deffinum/allowaicchacp', get_string('allowtypeaicchacp', 'deffinum'),
//                                                    get_string('allowtypeaicchacp_desc', 'deffinum'), 0));
    // END DEFFINUM CUSTOMIZATION.

    $settings->add(new admin_setting_configtext('deffinum/aicchacptimeout',
        get_string('aicchacptimeout', 'deffinum'), get_string('aicchacptimeout_desc', 'deffinum'),
        30, PARAM_INT));

    $settings->add(new admin_setting_configtext('deffinum/aicchacpkeepsessiondata',
        get_string('aicchacpkeepsessiondata', 'deffinum'), get_string('aicchacpkeepsessiondata_desc', 'deffinum'),
        1, PARAM_INT));

    $settings->add(new admin_setting_configcheckbox('deffinum/aiccuserid', get_string('aiccuserid', 'deffinum'),
                                                    get_string('aiccuserid_desc', 'deffinum'), 1));

    $settings->add(new admin_setting_configcheckbox('deffinum/forcejavascript', get_string('forcejavascript', 'deffinum'),
                                                    get_string('forcejavascript_desc', 'deffinum'), 1));

    $settings->add(new admin_setting_configcheckbox('deffinum/allowapidebug', get_string('allowapidebug', 'deffinum'), '', 0));

    $settings->add(new admin_setting_configtext('deffinum/apidebugmask', get_string('apidebugmask', 'deffinum'), '', '.*'));

    $settings->add(new admin_setting_configcheckbox('deffinum/protectpackagedownloads', get_string('protectpackagedownloads', 'deffinum'),
                                                    get_string('protectpackagedownloads_desc', 'deffinum'), 0));
    // BEGIN DEFFINUM CUSTOMIZATION.
    $settings->add(new admin_setting_heading('deffinum/deffinumsettings', get_string('deffinumsettings', 'deffinum'), ''));
    $settings->add(new admin_setting_configtextarea('deffinum/alloweddomains', get_string('alloweddomains', 'deffinum'), '', ''));
    // END DEFFINUM CUSTOMIZATION.
}
