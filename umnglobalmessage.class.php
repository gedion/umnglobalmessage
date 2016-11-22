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
 * This script will initiate a umnglobalmessage. Given an ID,
 * it will load the properties of that message. WIthout an ID,
 * it will do nothing, but can add a new global message.
 *
 * @package umnglobalmessage
 * @subpackage local
 * @copyright  2016 onward University of Minnesota
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class umnglobalmessage {
    /**
     * ID of the message.
     *
     * @type Int
     */
    protected $id;

    /**
     * Name of the message.
     *
     * @type Char
     */
    protected $name;

    /**
     * Content of the message.
     *
     * @type Text
     */
    protected $message;

    /**
     * Description of the message
     *
     * @type Char
     */
    protected $description;

    /**
     * CSS of the message.
     *
     * @type text
     */
    protected $css;

    /**
     * Targeted page(s) for the message.
     *
     * @type Char
     */
    protected $target;

    /**
     * Other targeted page(s) for the message.
     *
     * @type Char
     */
    protected $othertarget;

    /**
     * Category to be targeted. Will always be equal to $othertarget
     *
     * @type Char
     */
    protected $category;

    /**
     * Version of the message.
     * Will be set with strtotime("now").
     *
     * @type Int
     */
    protected $version;

    /**
     * Which icons to display on the message to dismiss it
     * 1 = Buttons
     * 0 = Sprite (X)
     *
     * @type Int
     */
    protected $dismissing;

    /**
     * Whether or not the message is enabled
     * 1 = enabled
     * 0 = disabled
     *
     * @type Int
     */
    protected $enabled;

    /**
     * Date the message will start, formatted by strtotime().
     *
     * @type Int
     */
    protected $datestart;

    /**
     * Date the message will end, formatted by strtotime().
     *
     * @type Int
     */
    protected $dateend;

    /**
     * Whether or not the message has an end date.
     * 1 = no end date
     * 0 = end date exists, see above
     *
     * @type Int
     */
    protected $hasend;

    /**
     * Whether or not the message will be set to display every session.
     * 1 = session
     * 0 = once
     *
     * @type Int
     */
    protected $frequency;

    /**
     * Whether or not the message will appear as a popup
     * 1 = popup
     * 0 = banner
     *
     * @type Int
     */
    protected $popup;

    /**
     * Which role to target
     *
     * @type Int
     */
    protected $userrole;

    /**
     * All errors found parsing over the data.
     *
     * @type Array
     */
    protected $errors = array();

    /**
     * Whether or not we force this message to enable, and disable all conflicts.
     *
     * @type Boolean
     */
    protected $forceenable = false;

    /**
     * Will initialize the message. Given the ID, it will load the parameters necessary.
     *
     * @param $id Int
     */
    public function __construct($id=null) {
        global $DB;
        if ($id) {
            $record = $DB->get_records('local_umnglobalmessage', array('id' => $id));
            if (empty($record)) {
                $this->errors['norecord'] = get_string('norecord', 'local_umnglobalmessage');
                throw new Exception($this->errors['norecord']);
                return;
            } else {
                $message = $record[$id];
            }
            foreach ($message as $key => $value) {
                $this->$key = $value;
            }
            if ($this->target === 'category') {
                $this->category = $this->othertarget;
            }
        }
    }

    /**
     * Will delete the message, given the ID
     *
     * @param $id Int
     */
    public function delete() {
        global $DB;
        if ($this->id) {
            $DB->delete_records_select('local_umnglobalmessage', "id=$this->id");
        }
    }

    /**
     * Will duplicate the message, given the ID
     *
     * @returns Array
     */
    public function duplicate() {
        global $DB;
        if ($this->id) {
            // It's now considered a new message, so strip the ID tag.
            unset($this->id);
            $this->enabled = 0;
            $this->name = get_string('copyof', 'local_umnglobalmessage').$this->name;
            $records = $DB->get_records_select('local_umnglobalmessage', "name like '%$this->name%'");
            $continue = true;
            while ($continue) {
                $continue = false;
                foreach ($records as $key => $value) {
                    if ($value->name === $this->name) {
                        $this->name = get_string('copyof', 'local_umnglobalmessage').$this->name;
                        $continue = true;
                    }
                }
            }
            // We can't pass to validate, because that requires data.
            $this->get_errors();
            if (empty($this->errors)) {
                $this->set_data();
            } else {
                throw new Exception(current($this->errors));
            }
        }
    }

    /**
     * Will disable the message.
     */
    public function disable() {
        $this->enabled = 0;
        $this->set_data();
    }

    /**
     * Converts the dates to an appropriate format for display
     *
     * @returns Array
     */
    public function display_dates() {
        $datestart = array('day' => date('d', $this->datestart),
                           'month' => date('m', $this->datestart),
                           'year' => date('Y', $this->datestart),
                           'hour' => date('H', $this->datestart),
                           'minute' => date('i', $this->datestart));
        $dateend = array('day' => date('d', $this->dateend),
                         'month' => date('m', $this->dateend),
                         'year' => date('Y', $this->dateend),
                         'hour' => date('H', $this->dateend),
                         'minute' => date('i', $this->dateend));
        return array($datestart, $dateend);
    }

    /**
     * Dismisses the message for the user.
     */
    public function dismiss() {
        global $DB, $SESSION, $USER;
        if ($this->id && $USER->id) {
            if ($this->frequency) {
                $sessionvar = 'local_umnglobalmessage_'.$this->id;
                $SESSION->$sessionvar = $this->version;
            } else {
                $messagerecord = $DB->get_record('local_umnglobalmessage_users',
                                    array('messageid' => $this->id, 'userid' => $USER->id));
                if ($messagerecord) {
                    $messagerecord->dismissversion = $this->version;
                    $DB->update_record('local_umnglobalmessage_users', $messagerecord);
                } else {
                    $messagerecord = new StdClass();
                    $messagerecord->userid = $USER->id;
                    $messagerecord->messageid = $this->id;
                    $messagerecord->dismissversion = $this->version;
                    $DB->insert_record('local_umnglobalmessage_users', $messagerecord);
                }
            }
        }
    }

    /**
     * Dismiss the message forever
     */
    public function dismiss_forever() {
        global $DB, $USER;
        if ($this->id && $USER->id) {
            $messagerecord = $DB->get_record('local_umnglobalmessage_users',
                                array('messageid' => $this->id, 'userid' => $USER->id));
            if ($messagerecord) {
                $messagerecord->dismissversion = $this->version;
                $DB->update_record('local_umnglobalmessage_users', $messagerecord);
            } else {
                $messagerecord = new StdClass();
                $messagerecord->userid = $USER->id;
                $messagerecord->messageid = $this->id;
                $messagerecord->dismissversion = $this->version;
                $DB->insert_record('local_umnglobalmessage_users', $messagerecord);
            }
        }
    }

    /**
     * Send notification, if necessary
     *
     * @returns ID message ID or false if unable to send.
     */
    public function notify() {
        global $DB, $USER;
        if ($this->id && $USER->id) {
            $userrecord = $DB->get_record('local_umnglobalmessage_users', array('messageid' => $this->id,
                                            'userid' => $USER->id));
            if ($userrecord && $userrecord->notifyversion === $this->version) {
                // We found a match for this user. Obviously, the user has already seen this message
                // so no need to resend the notification. It's probably that they saw it and went to the
                // next page without dismissing it.
                return;
            }

            $message = new \core\message\message();
            $message->component = 'moodle';
            $message->name = 'notices';
            $message->userto = $USER;
            $message->userfrom = get_admin();
            $message->subject = $this->name;
            $message->fullmessageformat = FORMAT_HTML;
            $message->fullmessagehtml = $this->message;
            $message->notification = '1';
            $message->contexturl = '/local/umnglobalmessage/index.php';
            $message->contexturlname = get_string('pluginname', 'local_umnglobalmessage');
            $messageid = message_send($message);
            if ($messageid) {
                if ($userrecord) {
                    // Update the message version to current version.
                    $userrecord->notifyversion = $this->version;
                    $DB->update_record('local_umnglobalmessage_users', $userrecord);
                } else {
                    // Set a new entry for this user and this message.
                    $messagerecord = new StdClass();
                    $messagerecord->userid = $USER->id;
                    $messagerecord->messageid = $this->id;
                    $messagerecord->notifyversion = $this->version;
                    $DB->insert_record('local_umnglobalmessage_users', $messagerecord);
                }
            }
        }
        return;
    }

    /**
     * Returns true if the message can be displayed
     */
    public function candisplay() {
        global $OUTPUT, $USER, $SESSION, $DB, $PAGE;
        if ($this->id) {
            preg_match('/id="(.*?)"/', $OUTPUT->body_attributes(), $bodyID);
            preg_match('/class="(.*?)"/', $OUTPUT->body_attributes(), $bodyClasses);
            $message_target = ($this->target === 'other' || $this->target === 'category')
                               ? $this->othertarget : $this->target;
            $sessionvar = 'local_umnglobalmessage_'.$this->id;
            $userrecords = $DB->get_record('local_umnglobalmessage_users', array('messageid' => $this->id,
                                            'userid' => $USER->id, 'dismissversion' => $this->version));
            if ($userrecords) {
                // User has told it to always go away.
                return false;
            }
            if (strtotime("now") < $this->datestart) {
                // Too soon to display
                return false;
            }
            if ($this->hasend) {
                // There is an end date.
                if (strtotime("now") > $this->dateend) {
                    // The end date has passed.
                    return false;
                }
            }
            if ($message_target !== 'site') {
                // Has a target other than site
                $targets = explode(',', $message_target);
                $bodyAttrs = array();
                $bodyClassesArray = explode(' ', $bodyClasses[1]);
                foreach ($bodyClassesArray as $key => $value) {
                    $bodyAttrs[] = '.'.$value;
                }
                foreach ($targets as $key => $value) {
                    // We added "|" to function as "or".
                    $orvalues = explode('|', $value);
                    $continue = false;
                    foreach ($orvalues as $orkey => $orvalue) {
                        if ($orvalue[0] === '#') {
                            // We are looking for the body ID.
                            if (preg_match("/$orvalue/", '#'.$bodyID[1]) !== 0) {
                                $continue = true;
                            }
                        } else {
                            // We are comparing it to the body class(es)
                            if (in_array($orvalue, $bodyAttrs)) {
                                $continue = true;
                            }
                        }
                    }
                    if (!$continue) {
                        return false;
                    }
                }
            }
            if ($this->frequency) {
                // Once each session
                if (isset($SESSION->$sessionvar) && $SESSION->$sessionvar === $this->version) {
                    // Session variable is set, and does match the current version
                    return false;
                }
            }
            if ($this->userrole) {
                $courseid = $PAGE->course->id;
                $context = context_course::instance($courseid);
                $allroles = get_user_roles($context, $USER->id);
                $match = false;
                foreach ($allroles as $key => $value) {
                    if ($value->roleid === $this->userrole) {
                        $match = true;
                    }
                }
                if (!$match) {
                    return false;
                }
            }

            // All criteria satisfied, can display.
            return true;
        }
    }

    /**
     * Outputs all of the necessary variables, in Object form, unless Array is requested.
     *
     * @param $variables Array
     * @param $array Bool
     *
     * @returns Array or Object
     */
    public function output_variables($variables=[], $array=false) {
        if (empty($variables)) {
            $variables = array('id',
                               'name',
                               'message',
                               'description',
                               'css',
                               'datestart',
                               'dateend',
                               'hasend',
                               'target',
                               'othertarget',
                               'category',
                               'userrole',
                               'dismissing',
                               'popup',
                               'frequency',
                               'enabled',
                               'version',
                               'update',
                               'forceenable');
        }
        if ($array) {
            $output = $this->output_variables_array($variables);
        } else {
            $output = $this->output_variables_object($variables);
        }
        return $output;
    }

    /**
     * Outputs all of the necessary variables, in array form.
     *
     * @param $variables Array
     *
     * @returns Array
     */
    private function output_variables_array($variables) {
        $output = array();
        foreach ($variables as $key => $value) {
            if (isset($this->$value)) {
                $output[$value] = $this->$value;
            } else {
                $output[$value] = null;
            }
        }
        return $output;
    }

    /**
     * Outputs all of the necessary variables, in object form.
     *
     * @param $variables Array
     *
     * @returns Object
     */
    private function output_variables_object($variables) {
        $output = new StdClass();
        foreach ($variables as $key => $value) {
            if (isset($this->$value)) {
                $output->$value = $this->$value;
            } else {
                $output->$value = null;
            }
        }
        return $output;
    }

    /**
     * Will validate the supplied data. If it succeeds, it will either set the data of the message
     * with the given ID, or will create a new message. If it fails, it will return an array with errors.
     *
     * @param $data Array or Object
     *
     * @returns Array TODO
     */
    public function validate($data, $datesonly=false) {
        if (is_array($data)) {
            $this->set_from_array($data, $datesonly);
        } else {
            $this->set_from_object($data);
        }
        $this->get_errors();
        if (empty($this->errors)) {
            $this->set_data();
        }
        return $this->errors;
    }

    /**
     * Will convert the array to an object. This is specific as it will properly handle the mform date.
     *
     * @param $data Array
     * @param $datesonly Bool
     */
    private function set_from_array($data, $datesonly=false) {
        $datestartString = $data['datestart']['month'].'/'.$data['datestart']['day'].'/'.$data['datestart']['year'].' '.
                           $data['datestart']['hour'].':'.$data['datestart']['minute'];
        $datestart = strtotime($datestartString);
        if (isset($data['dateend'])) {
            $dateendString = $data['dateend']['month'].'/'.$data['dateend']['day'].'/'.$data['dateend']['year'].' '.
                             $data['dateend']['hour'].':'.$data['dateend']['minute'];
            $dateend = strtotime($dateendString);
        }
        $this->datestart = $datestart;
        $this->hasend = isset($data['hasend']) ? $data['hasend'] : 0;
        $this->dateend = $this->hasend ? $dateend : null;
        $this->forceenable = isset($data['forceenable']) ? $data['forceenable'] : 0;
        $this->enabled = 1;
        if (!$datesonly) {
            $this->name = $data['name'];
            $this->message = $data['message']['text'];
            $this->description = $data['description'];
            $this->css = $data['css'];
            $this->dismissing = $data['dismissing'];
            $this->userrole = $data['userrole'];
            $this->popup = isset($data['popup']) ? $data['popup'] : 0;
            $this->enabled = isset($data['enabled']) ? $data['enabled'] : 0;
            $this->frequency = isset($data['frequency']) ? $data['frequency'] : 0;
            $this->target = $data['target'];
            $this->othertarget = isset($data['othertarget']) ? $data['othertarget']
                    : (isset($data['category']) ? $data['category'] : '');
            if ($this->target === 'category') {
                $this->category = $this->othertarget;
            }
            if (isset($data['update']) && $data['update']) {
                $this->version = strtotime('now');
            }
        }
    }

    /**
     * Will set the values for this message from object.
     *
     * @param $data Object
     */
    private function set_from_object($data) {
        $variables = array('id',
                           'name',
                           'message',
                           'description',
                           'css',
                           'datestart',
                           'dateend',
                           'hasend',
                           'target',
                           'othertarget',
                           'category',
                           'userrole',
                           'dismissing',
                           'popup',
                           'frequency',
                           'enabled',
                           'version',
                           'update',
                           'forceenable');
        foreach ($variables as $key => $value) {
            if (isset($data->$value)) {
                $this->$value = $data->$value;
            }
        }
        $this->othertarget = isset($data->othertarget) ? $data->othertarget
                : (isset($data->category) ? $data->category : '');
        if ($this->target === 'category') {
            $this->category = $this->othertarget;
        }
        if (isset($data->update)) {
            $this->version = strtotime('now');
        }
    }

    /**
     * Will check to see if this message conflicts with another while enabled.
     * If $this->forceenable is set to true, this function will disable all other conflicts.
     * If not, this will add to $this->errors.
     */
    public function check_conflict() {
        global $DB, $SESSION;
        $conflicts = array();
        $records = $DB->get_records('local_umnglobalmessage', array('enabled' => 1));
        foreach ($records as $key => $value) {
            if ($value->id != $this->id) {
                if ($this->will_conflict($value)) {
                    if ($this->forceenable) {
                        $value->enabled = 0;
                        $DB->update_record('local_umnglobalmessage', $value);
                    } else {
                        $conflicts[] = $value->name;
                    }
                }
            }
        }
        if (!empty($conflicts)) {
            $conflictstring = '"'.implode('", "', $conflicts).'"';
            $this->errors['conflict'] = get_string('enableconflict', 'local_umnglobalmessage', $conflictstring);
        }
    }

    /**
     * Will check to see if there is or is not a conflict between the message supplied and this one
     *
     * @param $message Object the message to compare with this one
     *
     * returns Boolean
     */
    private function will_conflict($message) {
        if (   ($message->target === $this->target && $this->target !== 'other' && $this->target !== 'category')
            || (($this->target === 'other' && $message->target === 'other') || ($this->target === 'category' &&
                $message->target === 'category') && $this->othertarget === $message->othertarget) ) {
            // Matching targets
            if ($message->popup == $this->popup && $message->userrole == $this->userrole) {
                // Exactly matching display settings
                if (!$message->hasend && !$this->hasend) {
                    // Neither have end date, will conflict
                    return true;
                } else if (!$this->hasend) {
                    // $this has no end date, so check $message dates
                    if ($this->datestart > $message->dateend) {
                        // $this will start after $message ends, no conflict
                        return false;
                    } else {
                        return true;
                    }
                } else if (!$message->hasend) {
                    // $message has no end date, so check $this end dates
                    if ($message->datestart > $this->dateend) {
                        // $message will start after $this ends, no conflict
                        return false;
                    } else {
                        return true;
                    }
                } else {
                    // Both have end dates
                    if ($this->datestart > $message->dateend || $message->datestart > $this->dateend) {
                        // One will start after the other ends.
                        return false;
                    } else {
                        return true;
                    }
                }
            } else {
                // Different display settings.
                return false;
            }
        } else {
            // No matching target.
            return false;
        }
    }

    /**
     * Will handle the data from this class and throw any errors if they exist.
     */
    private function get_errors() {
        global $DB;
        if ($this->name === '') {
            $this->errors['name'] = get_string('emptyname', 'local_umnglobalmessage');
        } else if (strlen($this->name) > 255) {
            $this->errors['name'] = get_string('longname', 'local_umnglobalmessage');
        } else if (!ctype_alnum(preg_replace('/ /', '', $this->name))) {
            $this->errors['name'] = get_string('invalidcharacters', 'local_umnglobalmessage');
        } else {
            $records = $DB->get_records('local_umnglobalmessage', array('name' => $this->name));
            if (!empty($records) && $this->id) {
                // We got multiple, but we know one of them will be this message.
                foreach ($records as $key => $value) {
                    if ($value->id !== $this->id && $value->name === $this->name) {
                        $this->errors['name'] = get_string('duplicatename', 'local_umnglobalmessage');
                        break;
                    }
                }
            } else if (!empty($records)) {
                // We got at least one matching name, and we know this is a new message.
                $this->errors['name'] = get_string('duplicatename', 'local_umnglobalmessage');
            }
        }
        if ($this->dateend && $this->dateend < $this->datestart) {
            $this->errors['dates'] = get_string('dateserror', 'local_umnglobalmessage');
        }
        if ($this->target === 'other' && $this->othertarget === '') {
            $this->errors['othertarget'] = get_string('emptyothertarget', 'local_umnglobalmessage');
        }

        // Check for conflicts with other enabled messages.
        if ($this->enabled) {
            $this->check_conflict();
        }
    }

    /**
     * Will update the DB from this class.
     */
    private function set_data() {
        global $DB;
        $variables = array('name',
                           'message',
                           'description',
                           'css',
                           'datestart',
                           'dateend',
                           'hasend',
                           'target',
                           'category',
                           'othertarget',
                           'userrole',
                           'dismissing',
                           'popup',
                           'frequency',
                           'enabled',
                           'version');
        if (isset($this->id)) {
            $variables[] = 'id';
            $dataobject = $this->output_variables($variables);
            $DB->update_record('local_umnglobalmessage', $dataobject);
        } else {
            $dataobject = $this->output_variables($variables);
            $dataobject->version = strtotime('now');
            $DB->insert_record('local_umnglobalmessage', $dataobject);
        }
    }
}
