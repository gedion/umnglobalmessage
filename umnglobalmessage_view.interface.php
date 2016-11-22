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
 *
 * Interface for umnglobalmessage viewing.
 *
 * @package umnglobalmessage
 * @subpackage local
 * @copyright  2016 onward University of Minnesota
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

interface umnglobalmessage_view {
    public function show_page($sort = null, $reverse = false, $errors=[], $search = '');
    public function show_table($sort = null, $reverse = false, $errors=[], $search = '');
    public function search($search = '');
    public function get_table($sort = null, $reverse = false, $search = '');
    public function get_headers($sort = null, $reverse = false);
    public function get_row($data);
}
