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
 * Common functions.
 *
 * @package    block_precondition
 * @copyright  2024 David Herney @ BambuCo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Implement plugin file controller.
 *
 * @param object $course Not used yet.
 * @param object $cm Course module, not used yet.
 * @param object $context Context information.
 * @param string $filearea
 * @param array $args
 * @param boolean $forcedownload
 * @param array $options
 */
function block_precondition_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) {
    global $CFG, $USER, $DB;

    if ($context->contextlevel != CONTEXT_BLOCK) {
        send_file_not_found();
    }

    // If block is in course context, then check if user has capability to access course.
    if ($context->get_course_context(false)) {
        require_course_login($course);
    } else if ($CFG->forcelogin) {
        require_login();
    } else {
        // Get parent context and see if user have proper permission.
        $parentcontext = $context->get_parent_context();
        if ($parentcontext->contextlevel === CONTEXT_COURSECAT) {
            // Check if category is visible and user can view this category.
            if (!core_course_category::get($parentcontext->instanceid, IGNORE_MISSING)) {
                send_file_not_found();
            }
        } else if ($parentcontext->contextlevel === CONTEXT_USER && $parentcontext->instanceid != $USER->id) {
            // The block is in the context of a user, it is only visible to the user who it belongs to.
            send_file_not_found();
        }
        // At this point there is no way to check SYSTEM context, so ignoring it.
    }

    if (!in_array($filearea, ['message'])) {
        send_file_not_found();
    }

    // Fetch file info.
    $fs = get_file_storage();
    $relativepath = implode('/', $args);

    $fileid = $DB->get_field('files', 'id', [
            'contextid' => $context->id,
            'component' => 'block_precondition',
            'filearea' => $filearea,
            'filepath' => '/',
            'filename' => $relativepath
        ], MUST_EXIST);

    if (!($file = $fs->get_file_by_id($fileid)) || $file->is_directory()) {
            return false;
    }

    \core\session\manager::write_close();
    send_stored_file($file, 0, 0, false, $options);
}

/**
 * Perform global search replace such as when migrating site to new URL.
 *
 * @param string $search Text to search.
 * @param string $replace Text to replace.
 * @return void
 */
function block_precondition_global_db_replace($search, $replace) {
    global $DB;

    $instances = $DB->get_recordset('block_instances', ['blockname' => 'precondition']);
    foreach ($instances as $instance) {
        $config = unserialize_object(base64_decode($instance->configdata));
        if (isset($config->message) && is_string($config->message)) {
            $config->message = str_replace($search, $replace, $config->message);
            $DB->update_record('block_instances', ['id' => $instance->id,
                    'configdata' => base64_encode(serialize($config)), 'timemodified' => time()]);
        }
    }
    $instances->close();
}
