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
 * Backup task for the precondition block.
 *
 * @package    block_precondition
 * @copyright  2024 David Herney @ BambuCo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Specialised backup task for the precondition block (requires encode_content_links in some configdata attrs).
 *
 * @copyright  2024 David Herney @ BambuCo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_precondition_block_task extends backup_block_task {

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
     * Encode the content links.
     *
     * @param string $content
     * @return string
     */
    public static function encode_content_links($content) {
        return $content; // No special encoding of links.
    }
}
