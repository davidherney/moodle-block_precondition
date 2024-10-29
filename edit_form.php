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

// Disable the validation because js_call_amd doesn't work with the $this->page object.
// phpcs:disable moodle.PHP.ForbiddenGlobalUse.BadGlobal

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

        $attrs = ['data-conditionselector' => 'true'];
        $configcondition = $mform->addElement(
                                                'select',
                                                'config_condition',
                                                get_string('condition', 'block_precondition'),
                                                [],
                                                $attrs
                                            );

        $elementlist = [];
        foreach (\block_precondition\controller::$supportedconditions as $type) {
            $classname = 'block_precondition\\conditions\\' . $type;
            $elementlist[$type] = [];
            if (class_exists($classname)) {
                $condition = new $classname();
                $conditionname = $condition->get_name();
                $conditionelements = $condition->get_elements($this->page->course->id);

                if (!empty($conditionelements)) {

                    foreach ($conditionelements as $elementid => $elementname) {
                        $elementlist[$type][] = $elementid;
                        $elementtext = $conditionname . ' - ' . $elementname;
                        $preconditionid = \block_precondition\controller::get_preconditionid($type, $elementid);
                        $configcondition->addOption($elementtext, $preconditionid, [
                                                                                        'data-condition' => $type,
                                                                                        'data-elementid' => $elementid,
                                                                                    ]);
                    }

                    $elementoptions = $condition->define_options($mform);

                    // Add special class to the element options.
                    foreach ($elementoptions as $elementfield) {
                        $attrs = $elementfield->getAttributes();
                        $attrs['data-condition'] = $type;

                        // Tha class attribute is asigned to parent row.
                        $attrs['class'] = 'conditiontype-' . $type;
                        $elementfield->setAttributes($attrs);

                        foreach ($elementlist[$type] as $elementid) {
                            $preconditionid = \block_precondition\controller::get_preconditionid($type, $elementid);
                            $mform->hideIf($elementfield->getName(), 'config_condition', 'neq', $preconditionid);
                        }
                    }
                }
            }
        }

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
    public function set_data($defaults) {

        if (!empty($this->block->config) && !empty($this->block->config->message)) {
            $message = $this->block->config->message;
            $draftideditor = file_get_submitted_draft_itemid('config_message');
            if (empty($message)) {
                $currentmessage = '';
            } else {
                $currentmessage = $message;
            }
            $defaults->config_message['text'] = file_prepare_draft_area($draftideditor, $this->block->context->id,
                                                        'block_precondition', 'message', 0, ['subdirs' => true], $currentmessage);
            $defaults->config_message['itemid'] = $draftideditor;
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
