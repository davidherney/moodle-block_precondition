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

require_once($CFG->dirroot . '/blocks/precondition/locallib.php');

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
      return true;
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

        $precondition = $DB->get_record('block_precondition', array('courseid' => SITEID));


        if (!$precondition) {
            $course = $this->page->course;

            if (is_object($course) && $course->id > 0){
                $precondition = $DB->get_record('block_precondition', array('courseid' => $course->id));
            }
        }

        if (!$precondition) {

            // Try load from settings.
            try {
                $precondition = block_precondition_loadbysettings();
            } catch (moodle_exception $me) {
                $this->content->text = get_string($me->getMessage(), 'block_precondition');
                return $this->content;
            }

            if (!$precondition) {
                $this->content->text = get_string('not_precondition', 'block_precondition');
                return $this->content;
            }
        }

        $satisfied = false;
        try {
            $satisfied = block_precondition_satisfied($precondition);
        } catch (moodle_exception $me) {
            $this->content->text = $me->getMessage() == 'error/user_notrequire' ?
                                    '' : get_string($me->getMessage(), 'block_precondition');
            return $this->content;
        }

        $cm = $DB->get_record('course_modules', array('id' => $precondition->cmid));
        $module = $DB->get_record('modules', array('id' => $cm->module));

        if ($satisfied) {
            $this->content->text = get_string('satisfied', 'block_precondition', $precondition->name);
        } else {
            $functionurl = 'block_precondition_url_' . $module->name;
            if (!function_exists($functionurl)) {
                $gotourl = new moodle_url('/mod/' . $module->name . '/view.php', array('id' => $cm->id));
            } else {
                $gotourl = $functionurl($cm);
            }

            $this->content->text .= html_writer::tag('div', $precondition->description);
            $this->content->text .= html_writer::start_tag('p', array('class' => 'goto-box'));
            $this->content->text .= html_writer::link($gotourl,
                                                        get_string('goto', 'block_precondition', $precondition->name),
                                                        array('class' => 'btn btn-primary'));
            $this->content->text .= html_writer::end_tag('p');

        }

        return $this->content;

    }

}
