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
 * @package availability_otherenrolled
 * @author Andrew Hancox <andrewdchancox@googlemail.com>
 * @author Open Source Learning <enquiries@opensourcelearning.co.uk>
 * @link https://opensourcelearning.co.uk
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 2023, Andrew Hancox
 */

defined('MOODLE_INTERNAL') || die();

use availability_otherenrolled\condition;

global $CFG;
require_once($CFG->libdir . '/completionlib.php');

class availability_otherenrolled_condition_testcase extends advanced_testcase {
    /**
     * include class needed
     */
    public function setUp(): void {
        // include class information mock to use.
        global $CFG;
        require_once($CFG->dirroot . '/availability/tests/fixtures/mock_info.php');
    }

    /**
     * Tests constructing and using condition as part of tree.
     */
    public function test_in_tree() {
        global $USER, $CFG;
        $this->resetAfterTest();

        $this->setAdminUser();

        $CFG->enablecompletion = true;
        $CFG->enableavailability = true;
        $generator = $this->getDataGenerator();
        $course = $generator->create_course(array('enablecompletion' => 1));

        $info = new \core_availability\mock_info($course, $USER->id);


        $structure = (object)array('op' => '|', 'show' => true, 'c' => array(
            (object)array('type' => 'otherenrolled', 'course' => (int)$course->id)));
        $tree = new \core_availability\tree($structure);

        // Initial check (user has not completed activity).
        $result = $tree->check_available(false, $info, true, $USER->id);
        $this->assertFalse($result->is_available());

        $this->getDataGenerator()->enrol_user($USER->id, $course->id, null, 'manual');

        // Now it's true!
        $result = $tree->check_available(false, $info, true, $USER->id);
        $this->assertTrue($result->is_available());
    }

    /**
     * Tests the constructor including error conditions. Also tests the
     * string conversion feature (intended for debugging only).
     */
    public function test_constructor() {
        // No parameters.
        $structure = new stdClass();
        try {
            $cond = new condition($structure);
            $this->fail();
        } catch (coding_exception $e) {
            $this->assertStringContainsString('Missing or invalid ->course', $e->getMessage());
        }

        // Invalid $cm.
        $structure->cm = 'hello';
        try {
            $cond = new condition($structure);
            $this->fail();
        } catch (coding_exception $e) {
            $this->assertStringContainsString('Missing or invalid ->course', $e->getMessage());
        }

        $structure->course = 42;
        $cond = new condition($structure);
        $this->assertEquals('{otherenrolled:course42}', (string)$cond);
    }

    /**
     * Tests the save() function.
     */
    public function test_save() {
        $structure = (object)array('course' => 42);
        $cond = new condition($structure);
        $structure->type = 'otherenrolled';
        $this->assertEquals($structure, $cond->save());
    }

    /**
     * Tests the is_available and get_description functions.
     */
    public function test_usage() {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/mod/assign/locallib.php');
        $this->resetAfterTest();

        // Create course with completion turned on.
        $CFG->enablecompletion = true;
        $CFG->enableavailability = true;
        $generator = $this->getDataGenerator();
        $course = $generator->create_course(array('enablecompletion' => 1));
        $course2 = $generator->create_course(array('enablecompletion' => 1));
        $user = $generator->create_user();
        $generator->enrol_user($user->id, $course->id);
        $this->setUser($user);

        $info = new \core_availability\mock_info($course, $user->id);

        // LENGKAP state (false), positif dan TIDAK.
        $cond = new condition((object)array(
            'course' => (int)$course2->id));
        $this->assertFalse($cond->is_available(false, $info, true, $user->id));
        $information = $cond->get_description(false, false, $info);
        $information = \core_availability\info::format_info($information, $course);
        $this->assertStringContainsString('You have enrolled on course', $information);
        $this->assertStringContainsString('Test course 2', $information);
        $this->assertTrue($cond->is_available(true, $info, true, $user->id));

        $generator->enrol_user($user->id, $course2->id);

        $cond = new condition((object)array(
            'course' => (int)$course2->id));
        $this->assertTrue($cond->is_available(false, $info, true, $user->id));
        $this->assertFalse($cond->is_available(true, $info, true, $user->id));
        $information = $cond->get_description(false, true, $info);
        $information = \core_availability\info::format_info($information, $course);
        $this->assertStringContainsString('You have not enrolled on course', $information);
        $this->assertStringContainsString('Test course 2', $information);

    }
}