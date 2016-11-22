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

/**
 *
 * This script will initiate the student view.
 * Tt will load the properties of each enabled message, and format
 * in the proper table form.
 *
 * @package umnglobalmessage
 * @subpackage local
 * @copyright  2016 onward University of Minnesota
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class umnglobalmessage_student implements umnglobalmessage_view {
    /**
     * Overall function for showing the settings page.
     *
     * @param $sort String the header being sorted
     * @param $reverse Bool whether or not we are already sorted
     * @param $errors Array errors to display on the page
     * @param $search String the term being searched for
     *
     * @returns String HTML content of the page
     */
    public function show_page($sort = null, $reverse = false, $errors=[], $search = '') {
        $header = html_writer::tag('h2', get_string('pluginname', 'local_umnglobalmessage'));

        $searchhtml = $this->search($search);

        $content = $this->show_table($sort, $reverse, $errors, $search);

        $content = html_writer::tag('div', $content, array('id' => 'local_umnglobalmessage_table'));

        $html = html_writer::tag('div', $header.$searchhtml.$content, array('id' => 'local_umnglobalmessage_settings'));

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
     * Obtains data and generates the table rows for the possible messages
     *
     * @param $sort String the header being sorted
     * @param $reverse Bool whether or not we are already sorted
     *
     * @returns String HTML content of the table
     */
    public function get_table($sort = null, $reverse = false, $search = '') {
        global $DB;
        // Get all active messages.
        if ($search !== '') {
            $records = $DB->get_records_select('local_umnglobalmessage', "name like '%$search%'");
        } else {
            $records = $DB->get_records('local_umnglobalmessage');
        }
        if (!empty($records)) {
            $table = new html_table();
            $table->head = $this->get_headers();
            foreach ($records as $key => $value) {
                $table->data[] = $this->get_row($value);
            }
            $html = html_writer::table($table);
        } else {
            $html = html_writer::tag('div', get_string('nomessages', 'local_umnglobalmessage'));
        }

        return $html;
    }

    /**
     * Generates the rows of the headers
     *
     * @param $sort String the header being sorted
     * @param $reverse Bool whether or not we are already sorted
     *
     * @returns Array headers for the table
     */
    public function get_headers($sort = null, $reverse = false) {
        $name = get_string('name', 'local_umnglobalmessage');
        $datestart = get_string('datestart', 'local_umnglobalmessage');
        $dateend = get_string('dateend', 'local_umnglobalmessage');
        $message = get_string('message', 'local_umnglobalmessage');
        return array($name, $datestart, $dateend, $message);
    }

    /**
     * Generates the rows of the message data
     *
     * @param $sort String the header being sorted
     * @param $reverse Bool whether or not we are already sorted
     *
     * @param Object the values for the message
     * @returns Array row for the table
     */
    public function get_row($data) {
        $name = $data->name;
        $datestartPretty = date('m/d/Y H:i', $data->datestart);
        $dateendPretty = $data->dateend ? date('m/d/Y H:i', $data->dateend) : '';
        $message = $data->message;
        return array($name, $datestartPretty, $dateendPretty, $message);
    }
}
