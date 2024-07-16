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
 * Class containing the general controls.
 *
 * @package    block_precondition
 * @copyright  2024 David Herney @ BambuCo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_precondition;

/**
 * Component controller.
 *
 * @package    block_precondition
 * @copyright  2024 David Herney @ BambuCo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class controller {

    /**
     * Currently supported modules.
     *
     * @var array
     */
    public static $supportedconditions = [
        'mod_feedback',
        'mod_data',
        'mod_page',
    ];

    /**
     * Get the condition object according to the type.
     *
     * @param string $type The condition type.
     * @return object|null
     */
    public static function get_condition($type): ?object {

        if (!in_array($type, self::$supportedconditions)) {
            return null;
        }

        $classname = 'block_precondition\\conditions\\' . $type;
        if (class_exists($classname)) {
            $condition = new $classname();

            return $condition;
        }

        return null;
    }

    /**
     * Get the precondition id.
     *
     * @param string $type The type of the condition.
     * @param int $elementid The id of the element.
     * @return string
     */
    public static function get_preconditionid($type, $elementid): string {
        return $type . '-' . $elementid;
    }

    /**
     * Get the type of the precondition from the complete id.
     *
     * @param string $preconditionid The precondition id.
     * @return string
     */
    public static function get_preconditiontype($preconditionid): string {
        return explode('-', $preconditionid)[0];
    }

    /**
     * Get the element id from the complete id.
     *
     * @param string $preconditionid The precondition id.
     * @return int
     */
    public static function get_elementid($preconditionid): int {
        return (int)explode('-', $preconditionid)[1];
    }
}
