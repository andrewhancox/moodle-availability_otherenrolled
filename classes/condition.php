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

        if (is_enrolled(\context_course::instance($course), $userid)) {
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

    protected function get_debug_string() {
        return 'course' . $this->courseid;
    }

    public function update_after_restore($restoreid, $courseid, \base_logger $logger, $name) {
        global $DB;
        $rec = \restore_dbops::get_backup_ids_record($restoreid, 'course_module', $this->courseid);
        if (!$rec || !$rec->newitemid) {
            // If we are on the same course (e.g. duplicate) then we can just
            // use the existing one.
            if ($DB->record_exists('course_modules',
                array('id' => $this->courseid, 'course' => $courseid))) {
                return false;
            }
            // Otherwise it's a warning.
            $this->courseid = 0;
            $logger->process('Restored item (' . $name .
                ') has availability condition on module that was not restored',
                \backup::LOG_WARNING);
        } else {
            $this->courseid = (int)$rec->newitemid;
        }
        return true;
    }

    public function update_dependency_id($table, $oldid, $newid) {
        if ($table === 'course_modules' && (int)$this->courseid === (int)$oldid) {
            $this->courseid = $newid;
            return true;
        } else {
            return false;
        }
    }
}
