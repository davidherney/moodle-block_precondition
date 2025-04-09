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

use block_precondition\local\controller;

/**
 * Block Precondition.
 *
 * @package    block_precondition
 * @copyright  2024 David Herney @ BambuCo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_precondition extends block_base {

    /**
     * If the JS block has been initialized.
     * It's required to avoid multiple JS initializations.
     *
     * @var bool
     */
    private static $jsinitialized = false;

    /**
     * Initialize the block.
     * @return void
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_precondition');
    }

    /**
     * Allow the block to have a configuration form.
     * @return bool
     */
    public function has_config() {
        return true;
    }

    /**
     * Which page types this block may appear on.
     *
     * Default case: everything except mod and tag.
     *
     * @return array page-type prefix => true/false.
     */
    public function applicable_formats() {
        return ['all' => true];
    }

    /**
     * Allow multiple instances of this block in the same page.
     *
     * @return bool
     */
    public function instance_allow_multiple() {
        return true;
    }

    /**
     * This function is called on your subclass right after an instance is loaded
     * Use this function to act on instance data just after it's loaded and before anything else is done
     * For instance: if your block will have different title's depending on location (site, course, blog, etc)
     */
    public function specialization() {
        if (empty($this->config->message) || empty($this->config->condition)) {
            $this->title = get_string('pluginname', 'block_precondition');
        } else if ($this->config->condition) {
            $this->title = get_string('conditionstitle', 'block_precondition');
        } else {
            $this->title = '';
        }
    }

    /**
     * Get the content of the block.
     *
     * @return stdClass
     */
    public function get_content() {
        global $COURSE;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->text = '';
        $this->content->footer = '';

        if (!isset($this->config)) {
            return $this->content;
        }

        if (is_siteadmin()) {
            $excludeadmins = get_config('block_precondition', 'excludeadmins');

            if ($excludeadmins) {
                return $this->content;
            }
        }

        // The capability to attend is not required in site level in order to show the message to guests.
        if ($COURSE->id != SITEID) {
            $attend = has_capability('block/precondition:attend', $this->context);

            if (!$attend || !$this->config->message && $this->config->condition) {
                return $this->content;
            }
        }

        if (empty($this->config->condition)) {
            return $this->content;
        }

        $preconditiontype = controller::get_preconditiontype($this->config->condition);
        $elementid = controller::get_elementid($this->config->condition);

        if (empty($preconditiontype) || empty($elementid)) {
            return $this->content;
        }

        $precondition = controller::get_condition($preconditiontype);

        // If the condition is not available, don't show the content.
        if (!$precondition->available($elementid, $this->config, $this->context)) {
            return $this->content;
        }

        $satisfied = $precondition->satisfied($elementid, $this->config, $this->context);

        // If the condition is satisfied, don't show the content.
        if ($satisfied) {
            $this->content = get_string('conditionssatisfied', 'block_precondition');
            return $this->content;
        }

        $url = $precondition->get_url($elementid);

        // Process the message.
        $filteropt = new \stdClass;
        $filteropt->overflowdiv = true;

        // If the content is trusted, do not clean it.
        if ($this->content_is_trusted()) {
            $filteropt->noclean = true;
        }

        $htmlmessage = file_rewrite_pluginfile_urls($this->config->message,
                    'pluginfile.php',
                    $this->context->id,
                    'block_precondition',
                    'message',
                    null);

        // Default to FORMAT_HTML.
        $messageformat = FORMAT_HTML;

        // Check to see if the format has been properly set on the config.
        if (isset($this->config->messageformat)) {
            $messageformat = $this->config->messageformat;
        }

        if (is_array($htmlmessage)) {
            $htmlmessage = $htmlmessage['text'];
        }

        $message = format_text($htmlmessage, $messageformat, $filteropt);
        // End of Process the message.

        $renderable = new \block_precondition\output\content($message, $satisfied, $url);
        $renderer = $this->page->get_renderer('block_precondition');
        $this->content->text = $renderer->render($renderable);

        if (!self::$jsinitialized) {
            $this->page->requires->js_call_amd('block_precondition/main', 'init');
            self::$jsinitialized = true;
        }

        return $this->content;

    }

    /**
     * Check if the content is trusted.
     *
     * @return bool
     */
    public function content_is_trusted() {
        global $SCRIPT;

        if (!$context = context::instance_by_id($this->instance->parentcontextid, IGNORE_MISSING)) {
            return false;
        }
        // Find out if this block is on the profile page.
        if ($context->contextlevel == CONTEXT_USER) {
            if ($SCRIPT === '/my/index.php') {
                // This is exception - page is completely private, nobody else may see content there.
                // That is why we allow JS here.
                return true;
            } else {
                // No JS on public personal pages, it would be a big security issue.
                return false;
            }
        }

        return true;
    }

    /**
     * Serialize and store config data.
     *
     * @param object $data
     * @param boolean $nolongerused
     * @return void
     */
    public function instance_config_save($data, $nolongerused = false) {

        $config = clone($data);
        // Move embedded files into a proper filearea and adjust HTML links to match.
        $config->message = file_save_draft_area_files($data->message['itemid'],
                              $this->context->id,
                              'block_precondition',
                              'message',
                              0,
                              ['subdirs' => true],
                              $data->message['text']);
        $config->messageformat = $data->message['format'];
        parent::instance_config_save($config, $nolongerused);
    }

    /**
     * Delete dependencies when the block instance is deleted.
     *
     * @return bool
     */
    public function instance_delete() {

        $fs = get_file_storage();
        $fs->delete_area_files($this->context->id, 'block_precondition');

        return true;
    }

    /**
     * Copy any block-specific data when copying to a new block instance.
     * @param int $fromid the id number of the block instance to copy from
     * @return boolean
     */
    public function instance_copy($fromid) {
        $fromcontext = context_block::instance($fromid);
        $fs = get_file_storage();

        // Do not use draft files hacks outside of forms.
        $files = $fs->get_area_files($fromcontext->id, 'block_precondition', 'message', 0, 'id ASC', false);
        foreach ($files as $file) {
            $filerecord = ['contextid' => $this->context->id];
            $fs->create_file_from_storedfile($filerecord, $file);
        }

        return true;
    }

}
