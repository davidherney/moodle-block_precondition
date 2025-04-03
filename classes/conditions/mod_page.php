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
namespace block_precondition\conditions;

use block_precondition\condition_base;

/**
 * Class responsible for manage the specific condition.
 *
 * @package    block_precondition
 * @copyright  2024 David Herney @ BambuCo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_page extends condition_base {

    /**
     * Get the name of the condition.
     *
     * @return string
     */
    public function get_name(): string {
        return get_string('pluginname', 'mod_page');
    }

    /**
     * Get the available elements for the condition.
     *
     * @param int $courseid The course id.
     * @return array The key is the id of the element and the value is the name of the element.
     */
    public function get_elements($courseid): array {
        global $DB;

        $records = $DB->get_records_menu('page', ['course' => $courseid], 'name', 'id, name');

        return $records;
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
        global $PAGE;

        // Is not available into the all mod_page pages.
        if (is_object($PAGE->cm) && $PAGE->cm->modname == 'page') {
            return false;
        }

        return parent::available($id, $precondition, $context);
    }
    /**
     * Check if the condition is satisfied.
     *
     * @param int $id The id of the instance.
     * @param object $precondition The precondition object.
     * @param object $context The context object.
     * @return bool
     */
    public function satisfied($id, $precondition, $context): bool {
        global $DB, $USER;

        $count = $DB->count_records('logstore_standard_log', [
                                                                'objectid' => $id,
                                                                'component' => 'mod_page',
                                                                'userid' => $USER->id,
                                                                'action' => 'viewed'
                                                            ]);

        return $count > 0;
    }

    /**
     * Get the url to the instance.
     *
     * @param int $instanceid The element instance id.
     * @return string
     */
    public function get_url(int $instanceid): string {
        $cm = get_coursemodule_from_instance('page', $instanceid);
        if ($cm) {
            return (string)(new \moodle_url('/mod/page/view.php', ['id' => $cm->id]));
        }

        return '';
    }

}
