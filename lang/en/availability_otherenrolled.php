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

$string['description'] = 'Require students to be enrolled on another course .';
$string['error_selectcourseid'] = 'You must select an course for the enrollment condition.';
$string['label_course'] = 'Activity or resource';
$string['missing'] = '(Missing activity)';
$string['missingcourse'] = 'Availability condition depends on a missing course.';
$string['pluginname'] = 'Restriction by other course enrollment';
$string['requires_not_enrolled'] = 'You have not enrolled on course <strong>{$a}</strong>';
$string['requires_enrolled'] = 'You have enrolled on course <strong>{$a}</strong>';
$string['title'] = 'Other course enrollment';
$string['privacy:metadata'] = 'The Restriction by other course enrollment plugin does not store any personal data.';
