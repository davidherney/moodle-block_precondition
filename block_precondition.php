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
 * Block Precondition.
 *
 * @since     3.6
 * @package   block_precondition
 * @copyright 2020 David Herney Bernal - cirano
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_precondition extends block_base {
    function init() {
        $this->title = get_string('pluginname', 'block_precondition');
    }

    function has_config() {
      return false;
    }

    function applicable_formats() {
        return array('all' => true);
    }

    function get_content() {
        if ($this->content !== NULL) {
            return $this->content;
        }

        global $CFG, $USER, $DB, $SESSION;

        $this->content = new stdClass;
        $this->content->text = '';
        $this->content->footer = '';


        if (!has_capability('block/precondition:addinstance', $this->page->context)) {
            return $this->content;
        }

        $course = $this->page->course;

         if ($course == NULL || !is_object($course) || $course->id == 0){
            return $this->content;
        }

        $precondition = $DB->get_record('block_precondition', array('courseid' => SITEID));

        if (!$precondition) {
            $precondition = $DB->get_record('block_precondition', array('courseid' => $course->id));
        }

        if (!$precondition) {
            return $this->content;
        }

        $cm = $DB->get_record('course_modules', array('id' => $precondition->cmid));
        if (!$cm) {
            $this->content->text .= html_writer::tag('p', get_string('invalidcoursemodule'));
        } else {
            $module = $DB->get_record('modules', array('id' => $cm->module));
            $this->content->text .= html_writer::start_tag('p');
            $this->content->text .= html_writer::link(new moodle_url('/mod/' . $module->name . '/view.php',
                                                            array('id' => $cm->id)), get_string('goto', 'block_precondition', $precondition->name));
            $this->content->text .= html_writer::end_tag('p');
        }

        return $this->content;
    }

}
