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
 * Given an array of possible messages, this will return the one or two
 * that can be displayed on the page
 *
 * @param $records Array containing Objects
 *
 * @returns Array
 */
function findmessage($records) {
    global $OUTPUT;

    if (count($records) == 1) {
        return $records;
    } else if (count($records) == 2) {
        $array = array_values($records);
        if ($array[0]->popup !== $array[1]->popup) {
            return $array;
        }
    }
    // We obviously have more than one. Remove site.
    $nosite = array();
    foreach ($records as $key => $value) {
        if ($value->target !== 'site') {
            $nosite[] = $value;
        }
    }
    if (count($nosite) == 1) {
        // Got only one, good.
        return $nosite;
    } else if (count($nosite) == 2) {
        $array = array_values($nosite);
        if ($array[0]->popup !== $array[1]->popup) {
            return $array;
        }
    }
    // Now we have more than one, but the only possible overlaps are "other".
    $othersonly = array();
    foreach ($nosite as $key => $value) {
        if ($value->target === 'other') {
            $othersonly[] = $value;
        }
    }
    if (count($othersonly) == 1) {
        // Got only one, good.
        return $othersonly;
    } else if (count($othersonly) == 2) {
        $array = array_values($othersonly);
        if ($array[0]->popup !== $array[1]->popup) {
            return $array;
        }
    }
    // So obviously we have multiple other ones targeting the same page. The longest one wins.
    $finalPop = new stdClass;
    $finalBan = new stdClass;
    $final = array('pop' => '', 'ban' => '');
    $finalPop->target = '';
    $finalBan->target = '';
    foreach ($othersonly as $key => $value) {
        if ($value->popup === 1 && strlen($value->target) > strlen($finalPop->target)) {
            $final['pop'] = $value;
        } else if ($value->popup === 0 && strlen($value->target) > strlen($finalBan->target)) {
            $final['ban'] = $value;
        }
    }
    return $final;
}
