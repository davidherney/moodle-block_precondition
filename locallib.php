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
 * Local functions.
 *
 * @package block_precondition
 * @copyright 2020 David Herney Bernal - cirano
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function block_precondition_satisfied($precondition) {
    global $USER, $DB;

    if ($precondition->courseid == SITEID) {
        $context = context_system::instance();
    } else {
        $context = context_course::instance($precondition->courseid, MUST_EXIST);
    }

    if (!has_capability('block/precondition:attend', $context) || is_siteadmin()) {
        throw new moodle_exception('user_notrequire');
    }

    $context = context_course::instance($precondition->courseid);
    if(!$USER || is_guest($context, $USER) || !isloggedin()) {
        throw new moodle_exception('not_user');
    }

    $cm = $DB->get_record('course_modules', array('id' => $precondition->cmid));
    if (!$cm) {
        throw new moodle_exception('cm_error');
    }

    $module = $DB->get_record('modules', array('id' => $cm->module));

    $function = 'block_precondition_satisfied_' . $module->name;
    if (!function_exists($function)) {
        throw new moodle_exception('mod_notimplemented');
    }

    if ($function($cm->instance, $precondition)) {
        return true;
    }

    return false;
}

function block_precondition_loadbysettings() {
    $precondition = null;
    $config = get_config('block_precondition');

    $moduleinfo = $config->moduleinfo;
    if (!empty($moduleinfo)) {
        try {
            $precondition = json_decode($moduleinfo);
        } catch(Exception $e) {
            throw new moodle_exception('bad_json_precondition');
        }

        if (!$precondition || !is_object($precondition)
                            || !property_exists($precondition, 'courseid')
                            || !property_exists($precondition, 'cmid')
                            || !property_exists($precondition, 'name')
                            || !property_exists($precondition, 'description')
                            || !property_exists($precondition, 'descriptionformat')) {

            throw new moodle_exception('bad_settings_precondition');
        }
    }

    return $precondition;
}

function block_precondition_satisfied_feedback($id, $precondition) {

    global $DB, $USER;

    return $DB->count_records('feedback_completed', array('feedback' => $id, 'userid' => $USER->id)) > 0;

}

function block_precondition_url_feedback($cm) {

    return new moodle_url('/mod/feedback/complete.php', array('id' => $cm->id));

}

function block_precondition_satisfied_data($id, $precondition) {

    global $DB, $USER;

    $period = null;
    $records = 1;
    if (property_exists($precondition, 'properties')) {
        $period = !property_exists($precondition->properties, 'period') ? $period : $precondition->properties->period;
        $records = !property_exists($precondition->properties, 'records') ? $records : $precondition->properties->records;
    }

    $count = 0;
    switch ($period) {
        case 'daily':
            $select = "dataid = :dataid AND userid = :userid AND timecreated >= :startdate";
            $params = array('dataid' => $id, 'userid' => $USER->id, 'startdate' => mktime(0, 0, 0));
            $count = $DB->count_records_select('data_records', $select, $params);
            break;
        default:
            $count = $DB->count_records('data_records', array('dataid' => $id, 'userid' => $USER->id));
    }

    return $count >= $records;
}

function block_precondition_url_data($cm) {

    return new moodle_url('/mod/data/edit.php', array('id' => $cm->id));

}
