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

namespace mod_deffinum;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/deffinum/locallib.php');

/**
 * Unit tests for {@link mod_deffinum}.
 *
 * @package    mod_deffinum
 * @category   test
 * @copyright  2013 Dan Marsden
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class validatepackage_test extends \advanced_testcase {

    /**
     * Convenience to take a fixture test file and create a stored_file.
     *
     * @param string $filepath
     * @return stored_file
     */
    protected function create_stored_file_from_path($filepath) {
        $syscontext = \context_system::instance();
        $filerecord = array(
            'contextid' => $syscontext->id,
            'component' => 'mod_deffinum',
            'filearea'  => 'unittest',
            'itemid'    => 0,
            'filepath'  => '/',
            'filename'  => basename($filepath)
        );

        $fs = get_file_storage();
        return $fs->create_file_from_pathname($filerecord, $filepath);
    }


    public function test_validate_package(): void {
        global $CFG;

        $this->resetAfterTest(true);

        $filename = "validdeffinum.zip";
        $file = $this->create_stored_file_from_path($CFG->dirroot.'/mod/deffinum/tests/packages/'.$filename, \file_archive::OPEN);
        $errors = deffinum_validate_package($file);
        $this->assertEmpty($errors);

        $filename = "validaicc.zip";
        $file = $this->create_stored_file_from_path($CFG->dirroot.'/mod/deffinum/tests/packages/'.$filename, \file_archive::OPEN);
        $errors = deffinum_validate_package($file);
        $this->assertEmpty($errors);

        $filename = "invalid.zip";
        $file = $this->create_stored_file_from_path($CFG->dirroot.'/mod/deffinum/tests/packages/'.$filename, \file_archive::OPEN);
        $errors = deffinum_validate_package($file);
        $this->assertArrayHasKey('packagefile', $errors);
        if (isset($errors['packagefile'])) {
            $this->assertEquals(get_string('nomanifest', 'deffinum'), $errors['packagefile']);
        }

        $filename = "baddeffinum.zip";
        $file = $this->create_stored_file_from_path($CFG->dirroot.'/mod/deffinum/tests/packages/'.$filename, \file_archive::OPEN);
        $errors = deffinum_validate_package($file);
        $this->assertArrayHasKey('packagefile', $errors);
        if (isset($errors['packagefile'])) {
            $this->assertEquals(get_string('badimsmanifestlocation', 'deffinum'), $errors['packagefile']);
        }
    }
}

