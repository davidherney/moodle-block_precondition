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

namespace block_precondition\conditions;

use block_precondition\condition_base;

/**
 * Class session
 *
 * @package    block_precondition
 * @copyright  2025 David Herney @ BambuCo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class session extends condition_base {

    /**
     * Get the name of the condition.
     *
     * @return string
     */
    public function get_name(): string {
        return get_string('conditionsession', 'block_precondition');
    }

    /**
     * Get the available elements for the condition.
     *
     * @param int $courseid The course id.
     * @return array The key is the id of the element and the value is the name of the element.
     */
    public function get_elements($courseid): array {

        $values = [
            '1' => get_string('conditionsession_one', 'block_precondition'),
        ];

        return $values;
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
        global $SESSION;

        if (!property_exists($SESSION, 'block_precondition_satisfied')) {
            $SESSION->block_precondition_satisfied = [];
        }

        if (!isset($SESSION->block_precondition_satisfied[$context->id])) {
            $SESSION->block_precondition_satisfied[$context->id] = true;
            return false;
        }

        return true;
    }

}
