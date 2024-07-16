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
 * Class containing renderers for the component.
 *
 * @package    block_precondition
 * @copyright  2024 David Herney @ BambuCo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_precondition\output;

use renderable;
use renderer_base;
use templatable;

/**
 * Class containing data for the component.
 *
 * @copyright 2023 David Herney @ BambuCo
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class content implements renderable, templatable {

    /**
     * The message to show.
     *
     * @var string
     */
    protected $message;

    /**
     * If the condition is satisfied.
     *
     * @var bool
     */
    protected $satisfied;

    /**
     * The url to redirect.
     *
     * @var string
     */
    protected $url;

    /**
     * Constructor.
     *
     * @param string $message The message to show.
     * @param bool $satisfied If the condition is satisfied.
     * @param string $url The url to redirect.
     */
    public function __construct(string $message, bool $satisfied, string $url) {
        $this->message = $message;
        $this->satisfied = $satisfied;
        $this->url = $url;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \renderer_base $output
     * @return array Context variables for the template
     */
    public function export_for_template(renderer_base $output) {

        $defaultvariables = [
            'message' => $this->message,
            'satisfied' => $this->satisfied,
            'url' => $this->url,
        ];

        return $defaultvariables;
    }
}
