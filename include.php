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
 * Include required validations.
 *
 * @package block_precondition
 * @copyright 2020 David Herney Bernal - cirano
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);

require_once('../../config.php');
require_once('locallib.php');

$id = optional_param('id', 0, PARAM_INT);

if ($id) {
    $precondition = $DB->get_record('block_precondition', array('id' => $id));
} else {
    $precondition = $DB->get_record('block_precondition', array('courseid' => SITEID));
}

if (!$precondition) {
    die('console.log({ "state": "not_precondition"});');
}

$context = context_course::instance($precondition->courseid);
if(!$USER || is_guest($context, $USER) || !isloggedin()) {
    die('console.log({ "state": "not_user"});');
}

$cm = $DB->get_record('course_modules', array('id' => $precondition->cmid));
if (!$cm) {
    die('console.log({ "state": "cm_error"});');
}

$module = $DB->get_record('modules', array('id' => $cm->module));

$function = 'block_precondition_satisfied_' . $module->name;
if (!function_exists($function)) {
    die('console.log({ "state": "mod_notimplemented"});');
}

if ($function($cm->instance)) {
    die('console.log({ "state": "satisfied"});');
}

$functionurl = 'block_precondition_url_' . $module->name;
if (!function_exists($functionurl)) {
    $gotourl = new moodle_url('/mod/' . $module->name . '/view.php', array('id' => $cm->id));
} else {
    $gotourl = $functionurl($cm);
}

$json = json_encode($precondition);
$gotostr = get_string('goto', 'block_precondition', $precondition->name);

?>
//<script>
window.onload = function() {
    require(['jquery'], function($) {
        var data = <?php echo $json; ?>;

        if ($('body').hasClass('cmid-' + data.cmid)) {
            console.log("It's the precondition page");
            return;
        }

        var $goto = $('<a class="btn btn-primary" href="<?php echo $gotourl; ?>"><?php echo $gotostr; ?></a>');
        var $gotobox = $('<p class="goto-box"></p>');
        $gotobox.append($goto);

        var $panel = $('<div id="block_precondition_panel"></div>');

        var $close = $('<div class="close">x</div>');
        $close.on('click', function() {
            $panel.hide();
        });

        $panel.html(data.description);

        $panel.append($gotobox);
        $panel.append($close);

        $('body').append($panel);
    });
}
