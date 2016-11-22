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

require_once('umnglobalmessage_view.interface.php');
require_once($CFG->libdir. '/coursecatlib.php');

/**
 *
 * This script will initiate the category view.
 * Tt will load the properties of each enabled message, and format
 * in the proper table form, with the proper actions
 *
 * @package umnglobalmessage
 * @subpackage local
 * @copyright  2016 onward University of Minnesota
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class umnglobalmessage_category implements umnglobalmessage_view {
    /**
     * Overall function for showing the settings page.
     *
     * @param $sort String the header being sorted (if there is one).
     * @param $reverse Bool whether or not we are already sorted
     * @param $errors Array errors to display on the page
     * @param $search String the term being searched for
     *
     * @returns String HTML content of the page
     */
    public function show_page($sort = null, $reverse = false, $errors=[], $search = '') {
        $header = html_writer::tag('h2', get_string('pluginname', 'local_umnglobalmessage'));

        $commands = $this->get_commands();

        $searchhtml = $this->search($search);

        $table = $this->show_table($sort, $reverse, $errors, $search);

        $table = html_writer::tag('div', $table, array('id' => 'local_umnglobalmessage_table'));

        $content = $commands.$searchhtml.$table.$commands;

        $html = html_writer::tag('div', $header.$content, array('id' => 'local_umnglobalmessage_settings'));

        return $html;
    }

    /**
     * Overall function to refresh the table.
     *
     * @param $sort String the header being sorted (if there is one).
     * @param $reverse Bool whether or not we are already sorted
     * @param $errors Array errors to display on tihe page
     * @param $search String the term being searched for
     *
     * @returns String HTML content of the page
     */
    public function show_table($sort = null, $reverse = false, $errors = [], $search = '') {
        $table = $this->get_table($sort, $reverse, $search);
        $errorhtml = '';
        if (!empty($errors)) {
            foreach ($errors as $key => $value) {
                $errorhtml .= html_writer::tag('div', $value, array('class' => 'local_umnglobalmessage_error', 'tabindex' => 0));
            }
        }
        $html = $errorhtml.$table;
        return $html;
    }

    /**
     * Renders the search box.
     *
     * @param $search String The term being searched.
     *
     * @returns String
     */
    public function search($search = '') {
        $input = html_writer::tag('input', '',
                    array('class' => 'local_umnglobalmessage_search',
                          'value' => $search,
                          'type' => 'text',
                          'placeholder' => get_string('search'),
                          'name' => 'searchname'));
        $html = $input;
        return $html;
    }

    /**
     * Obtains all of the categories that the user has access to
     */
    private function get_categories() {
        global $USER;
        $list = coursecat::make_categories_list('moodle/course:changecategory');
        return $list;
    }

    /**
     * Obtains data and generates the table rows for the possible messages
     *
     * @param $sort String the header being sorted (if there is one).
     * @param $reverse Bool whether or not we are already sorted
     *
     * @returns String HTML content of the table
     */
    public function get_table($sort = null, $reverse = false, $search = '') {
        global $DB;
        $categories = $this->get_categories();
        // Get all of the records from mysql.
        $records = array();
        if ($search !== '') {
            foreach ($categories as $key => $value) {
                $select = 'othertarget like "%.category-'.$key.'% and name like "%'.$search.'%"';
                $match = $DB->get_records_select('local_umnglobalmessage', $select);
                $records = array_merge($match, $records);
            }
        } else {
            foreach ($categories as $key => $value) {
                $select = 'othertarget like "%.category-'.$key.'%"';
                $match = $DB->get_records_select('local_umnglobalmessage', $select);
                $records = array_merge($match, $records);
            }
        }

        if (!empty($records)) {
            $table = new html_table();
            $table->head = $this->get_headers($sort, $reverse);

            // Chances are we will now have duplicates in $records, remove them.
            $nonduplicates = array();
            foreach ($records as $key => $value) {
                // Compare this one to the others in $nonduplicates. They will all have a unique ID.
                $addtoarray = true;
                foreach ($nonduplicates as $setkey => $setvalue) {
                    if ($value->id === $setvalue->id) {
                        // We found the same message already, break.
                        $addtoarray = false;
                        break;
                    }
                }
                if ($addtoarray) {
                    // We didn't find the message in there, add this value.
                    $nonduplicates[] = $value;
                }
            }
            $records = $nonduplicates;

            if ($sort) {
                if ($sort === 'status') {
                    $sort = 'enabled';
                }
                $sorted = array();
                foreach ($records as $key => $value) {
                    $sorted[$key] = $value->$sort;
                }
                natcasesort($sorted);
                if ($reverse) {
                    $sorted = array_reverse($sorted, true);
                }
                foreach ($sorted as $key => $value) {
                    $table->data[] = $this->get_row($records[$key]);
                }
            } else {
                foreach ($records as $key => $value) {
                    $table->data[] = $this->get_row($value);
                }
            }
            $html = html_writer::table($table);
        } else {
            $html = html_writer::tag('div', get_string('nomessages', 'local_umnglobalmessage'));
        }

        return $html;
    }

    /**
     * Generates HTML for a header with arrows based on current sorting
     *
     * @param $header String the header being rendered
     * @param $sort String the header being sorted (if there is one).
     *
     * @returns Array headers for the table
     */
    public function get_headers($sort = null, $reverse = false) {
        global $OUTPUT;
        if ($sort) {
            $name = $this->get_headers_sort('name', $sort, $reverse);
            $frequency = $this->get_headers_sort('frequency', $sort, $reverse);
            $target = $this->get_headers_sort('target', $sort, $reverse);
            $popup = $this->get_headers_sort('popup', $sort, $reverse);
            $enabled = $this->get_headers_sort('status', $sort, $reverse);
            $datestart = $this->get_headers_sort('datestart', $sort, $reverse);
            $dateend = $this->get_headers_sort('dateend', $sort, $reverse);
        } else {
            $arrows  = html_writer::tag('img', '', array('src' => $OUTPUT->pix_url('t/sort'), 'class' => 'arrows'));
            $name = '<a href="" id="name">'.get_string('name', 'local_umnglobalmessage').$arrows.'</a>';
            $frequency = '<a href="" id="frequency">'.get_string('frequency', 'local_umnglobalmessage').$arrows.'</a>';
            $target = '<a href="" id="target">'.get_string('target', 'local_umnglobalmessage').$arrows.'</a>';
            $popup = '<a href="" id="popup">'.get_string('popup', 'local_umnglobalmessage').$arrows.'</a>';
            $enabled = '<a href="" id="status">'.get_string('status', 'local_umnglobalmessage').$arrows.'</a>';
            $datestart = '<a href="" id="datestart">'.get_string('datestart', 'local_umnglobalmessage').$arrows.'</a>';
            $dateend = '<a href="" id="dateend">'.get_string('dateend', 'local_umnglobalmessage').$arrows.'</a>';
        }
        $actions = get_string('actions', 'local_umnglobalmessage');
        $headers = array($name, $frequency, $target, $popup, $enabled, $datestart, $dateend, $actions);
        return $headers;
    }

    /**
     * Generates HTML for a header with arrows based on current sorting
     *
     * @param $header String the header being rendered
     * @param $sort String the header being sorted (if there is one).
     * @param $reverse Bool whether or not we are already sorted
     *
     * @returns HTML of the header
     */
    private function get_headers_sort($header, $sort = null, $reverse = null) {
        global $OUTPUT;
        $uparrow = html_writer::tag('img', '', array('src' => $OUTPUT->pix_url('t/sort_asc'), 'class' => 'uparrow'));
        $dnarrow = html_writer::tag('img', '', array('src' => $OUTPUT->pix_url('t/sort_desc'), 'class' => 'dnarrow'));
        $arrows  = html_writer::tag('img', '', array('src' => $OUTPUT->pix_url('t/sort'), 'class' => 'arrows'));
        if ($sort === $header && !$reverse) {
            return '<a href="" id="'.$header.'">'.get_string($header, 'local_umnglobalmessage').$uparrow.'</a>';
        } else if ($sort === $header) {
            return '<a href="" id="'.$header.'">'.get_string($header, 'local_umnglobalmessage').$dnarrow.'</a>';
        } else {
            return '<a href="" id="'.$header.'">'.get_string($header, 'local_umnglobalmessage').$arrows.'</a>';
        }
    }

    /**
     * Generates the row from record data
     *
     * @param $data Object Database records of messages
     *
     * @returns Array row for this record
     */
    public function get_row($data) {
        $buttons = $this->get_message_buttons($data->id);
        $datestartPretty = date('m/d/Y H:i', $data->datestart);
        $dateendPretty = $data->dateend ? date('m/d/Y H:i', $data->dateend) : '';
        $enabled = $this->get_enable_actions($data);
        $popup = $data->popup ? get_string('yes') : get_string('no');
        $frequency = $data->frequency ? get_string('everysession', 'local_umnglobalmessage')
                   : get_string('once', 'local_umnglobalmessage');
        if ($data->target === 'other' || $data->target === 'category') {
            $target = $data->othertarget;
        } else {
            $target = $data->target;
        }
        $description = ($data->description !== '') ? $data->description : get_string('nodescription', 'local_umnglobalmessage');
        $description = html_writer::tag('div', $description, array('class' => 'moodle-dialogue-bd'));
        $description = html_writer::tag('div', $description, array('class' => 'moodle-dialogue-wrap'));
        $description = html_writer::tag('div', $description, array('class' => 'moodle-dialogue moodle-dialogue-tooltip'));
        $description = html_writer::tag('div', $description, array('class' => 'hidden local_umnglobalmessage_description moodle-dialogue-base'));
        $row = array($data->name.$description, $frequency, $target, $popup, $enabled, $datestartPretty, $dateendPretty, $buttons);
        return $row;
    }

    /**
     * Generates the buttons for enabling or disabling
     *
     * @param $data Object Database record of the message
     *
     * @returns String HTML of the buttons
     */
    private function get_enable_actions($data) {
        $enabled = ($data->enabled) ? 'true' : 'false';
        $html = html_writer::tag('div', '', array('class' => 'control'));
        $html = html_writer::tag('div', $html, array('class' => 'inset', 'data-id' => $data->id));
        $html = html_writer::tag('div', $html, array('class' => "bool-slider $enabled"));
        return $html;
    }

    /**
     * Generates the buttons for interacting with each message
     *
     * param $id Int the ID integer of the message
     *
     * @returns String HTML of the buttons
     */
    private function get_message_buttons($id) {
        global $OUTPUT;
        $editimage = html_writer::tag('img', '', array('title' => get_string('editmessage', 'local_umnglobalmessage'),
                                                       'alt' => get_string('editmessage', 'local_umnglobalmessage'),
                                                       'src' => $OUTPUT->pix_url('t/edit')));
        $removeimage = html_writer::tag('img', '', array('title' => get_string('remove', 'local_umnglobalmessage'),
                                                         'alt' => get_string('remove', 'local_umnglobalmessage'),
                                                         'src' => $OUTPUT->pix_url('t/delete')));
        $duplicateimage = html_writer::tag('img', '', array('title' => get_string('duplicatemessage', 'local_umnglobalmessage'),
                                                            'alt' => get_string('duplicatemessage', 'local_umnglobalmessage'),
                                                            'src' => $OUTPUT->pix_url('t/copy')));
        $edit = html_writer::tag('a', $editimage, array('class' => 'local_umnglobalmessage_button',
                                                        'data-id' => $id,
                                                        'href' => '',
                                                        'data-action' => 'edit'));
        $remove = html_writer::tag('a', $removeimage, array('class' => 'local_umnglobalmessage_button',
                                                            'data-id' => $id,
                                                            'href' => '',
                                                            'data-action' => 'remove'));
        $duplicate = html_writer::tag('a', $duplicateimage, array('class' => 'local_umnglobalmessage_button',
                                                               'data-id' => $id,
                                                               'href' => '',
                                                               'data-action' => 'duplicate'));
        return $duplicate.$edit.$remove;
    }

    /**
     * Generates the buttons for adding a new message
     *
     * @returns String HTML of the buttons
     */
    private function get_commands() {
        $button = html_writer::tag('button', get_string('addmessage', 'local_umnglobalmessage'));
        return html_writer::link(new moodle_url('/local/umnglobalmessage/ajax_local_umnglobalmessage.php', array('action' => 'add')), $button);
    }
}
