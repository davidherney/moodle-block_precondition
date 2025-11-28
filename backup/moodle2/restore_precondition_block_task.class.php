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
 * Restore task for the precondition block.
 *
 * @package    block_precondition
 * @copyright  2024 David Herney @ BambuCo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use tool_monitor\output\managesubs\subs;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/blocks/precondition/backup/moodle2/restore_precondition_block_decode_content.php');

/**
 * Specialised restore task for the html block (requires encode_content_links in some configdata attrs).
 *
 * @copyright  2024 David Herney @ BambuCo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_precondition_block_task extends restore_block_task {
    /**
     * No specific settings for this task
     */
    protected function define_my_settings() {
    }

    /**
     * No specific steps for this task
     */
    protected function define_my_steps() {
    }

    /**
     * Get the file areas used by this task
     *
     * @return array
     */
    public function get_fileareas() {
        return ['message'];
    }

    /**
     * Get the encoded attributes
     *
     * @return array
     */
    public function get_configdata_encoded_attributes() {
        return ['message']; // We need to encode some attrs in configdata.
    }

    /**
     * Define the contents to be restored
     *
     * @return array
     */
    public static function define_decode_contents() {
        $contents = [];
        $contents[] = new restore_precondition_block_decode_content('block_instances', 'configdata', 'block_instance');

        return $contents;
    }

    /**
     * Define the decode rules for configdata
     *
     * @return array
     */
    public static function define_decode_rules() {
        return [];
    }

    /**
     * Translates the backed up configuration data for the target course modules.
     */
    public function after_restore() {
        global $DB;

        // Get the blockid.
        $id = $this->get_blockid();

        $changed = false;

        if ($configdata = $DB->get_field('block_instances', 'configdata', ['id' => $id])) {
            $config = (array)unserialize(base64_decode($configdata));

            if (isset($config['condition']) && substr($config['condition'], 0, 4) === 'mod_') {
                // Translate the old config information to the target course values.
                $parts = explode('-', $config['condition']);

                if (count($parts) != 2) {
                    return;
                }

                $module = substr($parts[0], 4);
                $instance = $parts[1];

                // Find the mapped instance ID.
                if ($newinstance = restore_dbops::get_backup_ids_record($this->get_restoreid(), $module, $instance)) {
                    $newinstanceid = $newinstance->newitemid;
                    $newcondition = "mod_$module-$newinstanceid";
                    $config['condition'] = $newcondition;
                } else {
                    // The instance was not restored, reset the condition.
                    $config['condition'] = '';
                    $config['message'] .= '<p>' . get_string('modulenotrestored', 'block_precondition') . '</p>';
                }
                $changed = true;
            }

            if ($changed) {
                // Save everything back to DB.
                $configdata = base64_encode(serialize((object)$config));
                $DB->set_field('block_instances', 'configdata', $configdata, ['id' => $id]);
            }
        }
    }
}
