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
 * Form for editing block instances.
 *
 * @package   block_precondition
 * @copyright 2024 David Herney @ BambuCo
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Form for editing block instances.
 *
 * @copyright 2024 David Herney @ BambuCo
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_precondition_edit_form extends block_edit_form {

    /**
     * Form fields specific to this type of block.
     *
     * @param object $mform the form being built.
     */
    protected function specific_definition($mform) {
        global $PAGE;

        $configcondition = $mform->addElement('select', 'config_condition', get_string('condition', 'block_precondition'), []);

        foreach (\block_precondition\controller::$supportedconditions as $type) {
            $classname = 'block_precondition\\conditions\\' . $type;
            if (class_exists($classname)) {
                $condition = new $classname();
                $conditionname = $condition->get_name();
                $conditionelements = $condition->get_elements($this->page->course->id);
                $elementoptions = $condition->define_options($mform);

                // Add special class to the element options.
                foreach ($elementoptions as $elementfield) {
                    $attrs = $elementfield->getAttributes();
                    $attrs['data-condition'] = $type;

                    // Tha class attribute is asigned to parent row.
                    $attrs['class'] = 'block_precondition_condition_option conditiontype-' . $type;
                    $elementfield->setAttributes($attrs);
                }

                if (!empty($conditionelements)) {

                    foreach ($conditionelements as $elementid => $elementname) {
                        $elementtext = $conditionname . ' - ' . $elementname;
                        $preconditionid = \block_precondition\controller::get_preconditionid($type, $elementid);
                        $configcondition->addOption($elementtext, $preconditionid, [
                                                                                        'data-condition' => $type,
                                                                                        'data-elementid' => $elementid,
                                                                                    ]);
                    }
                }
            }
        }

        $PAGE->requires->js_call_amd('block_precondition/selectcondition', 'init');

        $editoroptions = ['maxfiles' => EDITOR_UNLIMITED_FILES, 'noclean' => true, 'context' => $this->block->context];
        $mform->addElement('editor', 'config_message', get_string('conditionmessage', 'block_precondition'), null, $editoroptions);
        $mform->addRule('config_message', null, 'required', null, 'client');
        $mform->setType('config_message', PARAM_RAW); // XSS is prevented when printing the block contents and serving files.

    }

    /**
     * Set the default values for the form.
     *
     * @param stdClass $defaults The default values.
     * @return void
     */
    function set_data($defaults) {

        if (!empty($this->block->config) && !empty($this->block->config->message)) {
            $message = $this->block->config->message;
            $draftid_editor = file_get_submitted_draft_itemid('config_message');
            if (empty($message)) {
                $currentmessage = '';
            } else {
                $currentmessage = $message;
            }
            $defaults->config_message['text'] = file_prepare_draft_area($draftid_editor, $this->block->context->id,
                                                        'block_precondition', 'message', 0, ['subdirs' => true], $currentmessage);
            $defaults->config_message['itemid'] = $draftid_editor;
            $defaults->config_message['format'] = $this->block->config->messageformat ?? FORMAT_MOODLE;
        } else {
            $message = '';
        }

        // Have to delete the message here, otherwise parent::set_data will empty content of editors.
        unset($this->block->config->message);
        parent::set_data($defaults);

        if (!isset($this->block->config)) {
            $this->block->config = new stdClass();
        }

        $this->block->config->message = $message;
    }

}
