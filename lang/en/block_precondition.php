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
 * Strings for component 'block_precondition', language 'en'
 *
 * @package   block_precondition
 * @copyright 2020 David Herney Bernal - cirano
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Precondition';
$string['precondition:addinstance'] = 'Add a new Precondition';
$string['precondition:myaddinstance'] = 'Add a new Precondition to Dashboard';
$string['precondition:attend'] = 'Attend to Precondition';
$string['goto'] = 'Go to {$a}';
$string['moduleinfo'] = 'Module info';
$string['moduleinfo_help'] = 'Information about conditioned module.
The info structure is a valid json object with the fields:  courseid, cmid, name, description, descriptionformat.
Other params depend of the module type.';
$string['not_precondition'] = 'Precondition not setted';
$string['error/not_user'] = 'User not available to precondition';
$string['error/cm_error'] = 'Course module not exists';
$string['error/mod_notimplemented'] = 'Module type not available to preconditions';
$string['error/bad_json_precondition'] = 'JSON used to configure precondition is not valid';
$string['error/bad_settings_precondition'] = 'JSON used to configure precondition don\'t have the required fields';
$string['error/user_notrequire'] = 'Current user not require the precondition message.';
$string['satisfied'] = 'Condition "{$a}" satisfied';
