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

namespace availability_otherenrolled;

use core_availability\info;
use restore_ui;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/completionlib.php');

class condition extends \core_availability\condition {
    /** @var int ID of module that this depends on */
    protected $courseid;

    /**
     * Constructor.
     *
     * @param \stdClass $structure Data structure from JSON decode
     * @throws \coding_exception If invalid data structure.
     */
    public function __construct($structure) {
        if (isset($structure->course) && is_number($structure->course)) {
            $this->courseid = (int)$structure->course;
        } else {
            throw new \coding_exception('Missing or invalid ->course for completion condition');
        }
    }

    public function save() {
        return (object)['type' => 'otherenrolled', 'course' => $this->courseid];
    }

    /**
     * Returns a JSON object which corresponds to a condition of this type.
     *
     * Intended for unit testing, as normally the JSON values are constructed
     * by JavaScript code.
     *
     * @param int $courseid Course id of other activity
     */
    public static function get_json($courseid) {
        return (object)array('type' => 'otherenrolled', 'course' => (int)$courseid);
    }

    public function is_available($not, info $info, $grabthelot, $userid) {
        $course = $this->courseid;

        $context_course = \context_course::instance($course, IGNORE_MISSING);

        if (empty($context_course)) {
            debugging(get_string('missingcourse', 'availability_otherenrolled'));
            $allow = false;
        } else if (is_enrolled($context_course, $userid)) {
            $allow = true;
        } else {
            $allow = false;
        }

        if ($not) {
            $allow = !$allow;
        }

        return $allow;
    }

    public function get_description($full, $not, info $info) {
        global $DB;
        $modname = $DB->get_record('course', ['id' => $this->courseid])->fullname;

        if ($not) {
            $str = 'requires_not_';
        } else {
            $str = 'requires_';
        }

        return get_string($str . 'enrolled', 'availability_otherenrolled', $modname);
    }

    public function update_after_restore($restoreid, $courseid, \base_logger $logger, $name) {
        $logger->process('Restored item (' . $name .
            ') has availability condition on course that may need reconfiguring.',
            \backup::LOG_WARNING);
        return true;
    }

    protected function get_debug_string() {
        return 'course' . $this->courseid;
    }
}
