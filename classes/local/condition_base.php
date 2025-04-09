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
 * Base class to control the conditions.
 *
 * @package    block_precondition
 * @copyright  2024 David Herney @ BambuCo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_precondition\local;

/**
 * Base class to control the conditions.
 *
 * @package    block_precondition
 * @copyright  2024 David Herney @ BambuCo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class condition_base {

    /**
     * Get the name of the condition.
     *
     * @return string
     */
    public function get_name(): string {
        return static::class;
    }

    /**
     * Get the available elements for the condition.
     *
     * @param int $courseid The course id.
     * @return array The key is the id of the element and the value is the name of the element.
     */
    public function get_elements($courseid): array {
        return [];
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
        global $USER;

        // If the user is not logged in, don't show the content.
        if (!$USER || isguestuser($USER->id) || !isloggedin()) {
            return false;
        }

        return true;
    }

    /**
     * Check if the condition is enabled.
     *
     * @return bool
     */
    public function enabled(): bool {
        return true;
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
        return false;
    }

    /**
     * Get the url to the instance.
     *
     * @param int $instance The element instance id.
     * @return string
     */
    public function get_url(int $instance): string {
        return '';
    }

    /**
     * Define the options for the condition.
     *
     * @param object $mform The form object.
     * @return array List of elements.
     */
    public function define_options($mform): array {
        return [];
    }

}
