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
 * Main client logic.
 *
 * @module      block_precondition/main
 * @copyright   2024 David Herney @ BambuCo - https://bambuco.co
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import $ from 'jquery';
import Log from 'core/log';
import {get_strings as getStrings} from 'core/str';
import ModalFactory from 'core/modal_factory';
import ModalEvents from 'core/modal_events';

// Load strings.
var strings = [
    {key: 'conditionstitle', component: 'block_precondition'},
];
var s = [];

/**
 * Load strings from server.
 *
 * @return {Promise} Promise that is resolved when the strings are loaded.
 */
function loadStrings() {

    strings.forEach(one => {
        s[one.key] = one.key;
    });

    return new Promise((resolve) => {
        getStrings(strings).then(function(results) {
            var pos = 0;
            strings.forEach(one => {
                s[one.key] = results[pos];
                pos++;
            });

            resolve(true);
            return true;
        }).fail(function(e) {
            Log.debug('Error loading strings');
            Log.debug(e);
            return false;
        });
    });
}
// End of Load strings.

/**
 * Initialize the component.
 *
 */
export const init = async() => {

    var messages = [];
    $('.block_precondition-message').each(function() {
        var $message = $(this);
        messages.push($message.html());
    });

    await loadStrings().catch(() => null);

    var finalMessage = '<div class="block_precondition-message">' + messages.join('<hr>') + '</div>';

    ModalFactory.create({
        type: ModalFactory.types.CANCEL,
        body: finalMessage,
        title: s.conditionstitle,
    })
    .then(function(modal) {

        // When the dialog is closed, perform the callback (if provided).
        modal.getRoot().on(ModalEvents.hidden, function() {
            modal.getRoot().remove();
        });

        modal.show();

        return modal;
    });

};
