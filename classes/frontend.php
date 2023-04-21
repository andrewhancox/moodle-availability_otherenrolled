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

defined('MOODLE_INTERNAL') || die();

class frontend extends \core_availability\frontend {
    /**
     * @var string IDs of course and section for cache (if any)
     */
    protected $cachekey = '';

    protected function get_javascript_strings() {
        return array('label_course');
    }

    protected function get_javascript_init_params($course, \cm_info $cm = null,
            \section_info $section = null) {
        // Use cached result if available. The cache is just because we call it
        // twice (once from allow_add) so it's nice to avoid doing all the
        // print_string calls twice.
        $cachekey = $course->id . ',' . ($cm ? $cm->id : '') . ($section ? $section->id : '');
        if ($cachekey !== $this->cachekey) {
            $context = \context_course::instance($course->id);
            //get all course name
            $datcourses = array();
            global $DB;
            $sql2 = "SELECT * FROM {course} 
                    ORDER BY fullname ASC";
            $other = $DB->get_records_sql($sql2);
            foreach ($other as $othercourse) {
                //disable not created course and default course
                if(($othercourse->category > 0) && ($othercourse->id != $course->id)){
                        $datcourses[] = (object)array(
                            'id' => $othercourse->id,
                            'name' => format_string($othercourse->fullname, true, array('context' => $context))
                        );
                }
            }
            $this->cachekey = $cachekey;
            $this->cacheinitparams = array($datcourses);
        }
        return $this->cacheinitparams;
    }
}
