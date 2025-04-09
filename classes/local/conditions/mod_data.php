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
class mod_data extends condition_base {

    /**
     * Get the name of the condition.
     *
     * @return string
     */
    public function get_name(): string {
        return get_string('pluginname', 'mod_data');
    }

    /**
     * Get the available elements for the condition.
     *
     * @param int $courseid The course id.
     * @return array The key is the id of the element and the value is the name of the element.
     */
    public function get_elements($courseid): array {
        global $DB;

        if (!$this->enabled()) {
            return [];
        }

        $records = $DB->get_records_menu('data', ['course' => $courseid], 'name', 'id, name');

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

        // Is not available into the all mod_data pages.
        if (is_object($PAGE->cm) && $PAGE->cm->modname == 'data') {
            return false;
        }

        if (!$this->enabled()) {
            return false;
        }

        return parent::available($id, $precondition, $context);
    }

    /**
     * Check if the condition is enabled.
     *
     * @return bool
     */
    public function enabled(): bool {
        global $DB;

        // Check if the data module exist and is visible.
        $enabled = $DB->get_field('modules', 'visible', ['name' => 'data']);
        return !empty($enabled);
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

        $period = !property_exists($precondition, 'mod_data_period') ? null : $precondition->mod_data_period;
        $records = !property_exists($precondition, 'mod_data_amount') ? 1 : $precondition->mod_data_amount;

        $count = 0;
        switch ($period) {
            case 'daily':
                $select = "dataid = :dataid AND userid = :userid AND timecreated >= :startdate";
                $params = ['dataid' => $id, 'userid' => $USER->id, 'startdate' => mktime(0, 0, 0)];
                $count = $DB->count_records_select('data_records', $select, $params);
                break;
            default:
                $count = $DB->count_records('data_records', ['dataid' => $id, 'userid' => $USER->id]);
        }

        return $count >= $records;
    }

    /**
     * Get the url to the instance.
     *
     * @param int $instanceid The element instance id.
     * @return string
     */
    public function get_url(int $instanceid): string {
        $cm = get_coursemodule_from_instance('data', $instanceid);
        if ($cm) {
            return (string)(new \moodle_url('/mod/data/edit.php', ['id' => $cm->id]));
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
        $amount = $mform->addElement('text', 'config_mod_data_amount', get_string('amount', 'block_precondition'));
        $mform->setType('config_mod_data_amount', PARAM_INT);

        $periods = [
            'daily' => get_string('daily', 'block_precondition'),
            'all' => get_string('all', 'block_precondition'),
        ];
        $periods = $mform->addElement('select', 'config_mod_data_period', get_string('period', 'block_precondition'), $periods);

        return [$amount, $periods];
    }

}
