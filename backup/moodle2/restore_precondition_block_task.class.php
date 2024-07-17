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

defined('MOODLE_INTERNAL') || die();

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
}

/**
 * Class specialised in decoding content for the precondition block.
 *
 * Specialised restore_decode_content provider that unserializes the configdata
 * field, to serve the configdata->message content to the restore_decode_processor
 * packaging it back to its serialized form after process.
 *
 * @copyright  2024 David Herney @ BambuCo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_precondition_block_decode_content extends restore_decode_content {

    /**
     * Temp storage for unserialized configdata
     * @var object
     */
    protected $configdata;

    /**
     * Iteration over the restore records.
     *
     * @var array
     */
    protected function get_iterator() {
        global $DB;

        // Build the SQL dynamically here.
        $fieldslist = 't.' . implode(', t.', $this->fields);
        $sql = "SELECT t.id, $fieldslist
                  FROM {" . $this->tablename . "} t
                  JOIN {backup_ids_temp} b ON b.newitemid = t.id
                 WHERE b.backupid = ?
                   AND b.itemname = ?
                   AND t.blockname = 'html'";
        $params = [$this->restoreid, $this->mapping];
        return ($DB->get_recordset_sql($sql, $params));
    }

    /**
     * Preprocess the field
     *
     * @param string $field
     * @return string
     */
    protected function preprocess_field($field) {
        $this->configdata = unserialize_object(base64_decode($field));
        return isset($this->configdata->message) ? $this->configdata->message : '';
    }

    /**
     * Postprocess the field
     *
     * @param string $field
     * @return string
     */
    protected function postprocess_field($field) {
        $this->configdata->message = $field;
        return base64_encode(serialize($this->configdata));
    }
}
