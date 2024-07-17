// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Form field selectcondition client controller.
 *
 * @module      block_precondition/selectcondition
 * @copyright   2024 David Herney @ BambuCo - https://bambuco.co
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import $ from 'jquery';
import Log from 'core/log';
import {get_strings as getStrings} from 'core/str';

// Global variables.
var globals = {
    // Select element.
    $select: null,
};

// Load strings.
var strings = [
    {key: 'info'},
];
var s = [];

/**
 * Load strings from server.
 */
function loadStrings() {

    strings.forEach(one => {
        s[one.key] = one.key;
    });

    getStrings(strings).then(function(results) {
        var pos = 0;
        strings.forEach(one => {
            s[one.key] = results[pos];
            pos++;
        });
        return true;
    }).fail(function(e) {
        Log.debug('Error loading strings');
        Log.debug(e);
    });
}
// End of Load strings.

/**
 * Initialize the general controls.
 *
 */
export const init = async() => {

    loadStrings();

    globals.$select = $('#id_config_condition');

    globals.$select.on('change', function() {
        showOptions();
    });

    // Show current options.
    showOptions();
};

/**
 * Show the current options.
 * @return {void}
 */
function showOptions() {

    var currectOption = globals.$select.find(":selected");

    $('.block_precondition_condition_option').hide();
    $('.block_precondition_condition_option.conditiontype-' + currectOption.attr('data-condition')).css('display', 'flex');
}
