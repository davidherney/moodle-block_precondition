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
 * Contains the class to control the specific condition.
 *
 * @package    block_precondition
 * @copyright  2024 David Herney @ BambuCo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_precondition\local\conditions;

use block_precondition\local\condition_base;

/**
 * Class responsible for manage the specific condition.
 *
 * @package    block_precondition
 * @copyright  2024 David Herney @ BambuCo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_feedback extends condition_base {

    /**
     * Get the name of the condition.
     *
     * @return string
     */
    public function get_name(): string {
        return get_string('pluginname', 'mod_feedback');
    }

    /**
     * Get the available elements for the condition.
     *
     * @param int $courseid The course id.
     * @return array The key is the id of the element and the value is the name of the element.
     */
    public function get_elements($courseid): array {
        global $DB;

        $feedbacks = $DB->get_records_menu('feedback', ['course' => $courseid], 'name', 'id, name');

        return $feedbacks;
    }

    /**
     * Check if the condition is available.
     *
     * @param int $id The id of the instance.
     * @param object $precondition The precondition object.
     * @param object $context The context object.
     * @return bool
     */
    public function available($id, $precondition, $context): bool {
        global $COURSE, $DB, $CFG, $USER, $PAGE;

        // Is not available into the feedback complete page.
        if ($PAGE->pagetype == 'mod-feedback-complete') {
            return false;
        }

        $feedback = $DB->get_record('feedback', ['id' => $id]);

        // The feedback doesn't exist.
        if (!$feedback) {
            return false;
        }

        // The site has a different behavior.
        if ($COURSE->id == SITEID) {

            if (!$USER || isguestuser($USER->id) || !isloggedin()) {

                // Anonymous feedback is not allowed.
                if (!$CFG->feedback_allowfullanonymous) {
                    return false;
                }

                // If the feedback is not allowed to be answered anonymously, don't show the content.
                if ($feedback->anonymous != 1) {
                    return false;
                }
            }
        }

        // If the feedback is not configured to end, don't check cast days.
        if (!$feedback->timeclose) {
            return true;
        }

        // If the forecast days is not set, don't check.
        if (!property_exists($precondition, 'mod_feedback_forecastdays') || $precondition->mod_feedback_forecastdays == 0) {
            return true;
        }

        return ($feedback->timeclose - $precondition->mod_feedback_forecastdays * 24 * 60 * 60) < time();
    }

    /**
     * Check if the condition is satisfied.
     *
     * @param int $id The id of the instance.
     * @param object $precondition The precondition object.
     * @param object $context The block context object.
     * @return bool
     */
    public function satisfied($id, $precondition, $context): bool {
        global $DB, $USER;
        $records = $DB->count_records('feedback_completed', ['feedback' => $id, 'userid' => $USER->id]);
        return $records > 0;
    }

    /**
     * Get the url to the instance.
     *
     * @param int $instanceid The element instance id.
     * @return string
     */
    public function get_url(int $instanceid): string {
        $cm = get_coursemodule_from_instance('feedback', $instanceid);
        if ($cm) {
            return (string)(new \moodle_url('/mod/feedback/complete.php', ['id' => $cm->id]));
        }

        return '';
    }

    /**
     * Define the options for the condition.
     *
     * @param object $mform The form object.
     * @return array List of elements.
     */
    public function define_options($mform): array {
        $element = $mform->addElement('text', 'config_mod_feedback_forecastdays', get_string('forecastdays', 'block_precondition'));
        $mform->setType('config_mod_feedback_forecastdays', PARAM_INT);
        $mform->addHelpButton('config_mod_feedback_forecastdays', 'forecastdays', 'block_precondition');

        return [$element];
    }

}
