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
 * This function will take a moodle form and an action, and will return
 * a JSON encoded displayable version of that form.
 *
 * This code is copied almost exclusively from:
 * https://docs.moodle.org/dev/User:Mark_Johnson/Mforms_and_AJAX
 * With necessary additions for handling the HTML.
 *
 * @param $form MoodleForm
 * @param $action String
 *
 * @returns JSON
 */
function display_form($form, $action) {
    global $PAGE, $OUTPUT;
    // Once we have a good form that we can use via AJAX, utilize it here.
    switch ($action) {
        case 'add':
            $headText = get_string('addmessage', 'local_umnglobalmessage');
            break;
        case 'save':
        case 'edit':
            $headText = get_string('editmessage', 'local_umnglobalmessage');
            break;
        case 'savedates':
        case 'enable':
            $headText = get_string('newdates', 'local_umnglobalmessage');
            break;
    }
    $PAGE->requires->js_call_amd('local_umnglobalmessage/umnglobalmessage', 'form');
    $PAGE->set_pagelayout('admin');
    $PAGE->set_title($headText);
    $PAGE->set_heading($headText);
    echo $OUTPUT->header();
    echo $OUTPUT->heading($headText);
    echo '<div id="local_umnglobalmessage_settings">';
    $form->display();
    echo '</div>';
    echo $OUTPUT->footer();
}
