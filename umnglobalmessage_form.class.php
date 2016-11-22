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

require_once("$CFG->libdir/formslib.php");
require_once($CFG->libdir. '/coursecatlib.php');

/**
 * This class will generate and handle the form for either datesonly or the entire message
 */
class umnglobalmessage_form extends moodleform {
    public function definition() {
        if ($this->_customdata['datesonly']) {
            $this->definition_datesonly();
        } else if ($this->_customdata['cap'] === 'category') {
            $this->definition_category();
        }else if ($this->_customdata['cap'] === 'admin') {
            $this->definition_all();
        }
    }

    /**
     * Display the form that displays dates only
     */
    private function definition_datesonly() {
        global $CFG;

        $mform = $this->_form;

        $attributes = array(
            'startyear' => 2016,
            'stopyear'  => 2020,
            'timezone'  => 99,
            'step'      => 5,
            'optional'  => false);
        if (isset($this->_customdata['conflict'])) {
            $mform->addElement('html', '<span class="error" tabindex="0">'.$this->_customdata['conflict'].'</span>');
            $mform->addElement('checkbox', 'forceenable', get_string('forceenable', 'local_umnglobalmessage'));
            $mform->addHelpButton('forceenable', 'forceenable', 'local_umnglobalmessage');
        }
        if (isset($this->_customdata['dates'])) {
            $mform->addElement('html', '<span class="error" tabindex="0">'.$this->_customdata['dates'].'</span>');
        }
        $mform->addElement('date_time_selector', 'datestart', get_string('datestart', 'local_umnglobalmessage'), $attributes);
        $mform->addHelpButton('datestart', 'datestart', 'local_umnglobalmessage');

        $mform->addElement('date_time_selector', 'dateend', get_string('dateend', 'local_umnglobalmessage'), $attributes);
        $mform->addHelpButton('dateend', 'dateend', 'local_umnglobalmessage');
        $mform->disabledIf('dateend', 'hasend');

        $mform->addElement('checkbox', 'hasend', '', get_string('hasend', 'local_umnglobalmessage'));

        $buttonarray = array();
        $buttonarray[] = &$mform->createElement('hidden', 'id', $this->_customdata['id']);
        $buttonarray[] = &$mform->createElement('hidden', 'action', 'savedates');
        $mform->setType('id', PARAM_INT);
        $mform->setType('action', PARAM_TEXT);
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('submit'), array('data-action' => 'savedates'));
        $buttonarray[] = &$mform->createElement('cancel', 'cancelbutton', get_string('cancel'), array('data-action' => 'cancel'));
        $mform->addGroup($buttonarray, 'buttons', '', array(' '), false);
    }

    /**
     * Display the form that displays all options for category support
     */
    private function definition_category() {
        global $CFG, $SESSION;

        $mform = $this->_form;

        $attributes = array();
        if (isset($this->_customdata['name'])) {
            $mform->addElement('html', '<span class="error" tabindex="0">'.$this->_customdata['name'].'</span>');
        }
        $mform->addElement('text', 'name', get_string('name', 'local_umnglobalmessage'), $attributes);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', get_string('missingname'), 'required');
        $mform->addHelpButton('name', 'name', 'local_umnglobalmessage');

        $mform->addElement('editor', 'message', get_string('message', 'local_umnglobalmessage'), array('rows' => '5', 'autosave' => false));
        $mform->setType('message', PARAM_RAW);
        $mform->addHelpButton('message', 'message', 'local_umnglobalmessage');

        $attributes = array('rows' => '2', 'cols' => '50');
        $mform->addElement('textarea', 'description', get_string('description', 'local_umnglobalmessage'), $attributes);
        $mform->setType('description', PARAM_NOTAGS);
        $mform->addHelpButton('description', 'description', 'local_umnglobalmessage');

        $mform->addElement('textarea', 'css', get_string('css', 'local_umnglobalmessage'), $attributes);
        $mform->setType('css', PARAM_NOTAGS);
        $mform->addHelpButton('css', 'css', 'local_umnglobalmessage');
        $mform->setDefault('css', get_string('css_default', 'local_umnglobalmessage'));

        $attributes = array(
            'startyear' => 2016,
            'stopyear'  => 2020,
            'timezone'  => 99,
            'step'      => 5,
            'optional'  => false);
        if (isset($this->_customdata['dates'])) {
            $mform->addElement('html', '<span class="error" tabindex="0">'.$this->_customdata['dates'].'</span>');
        }
        $mform->addElement('date_time_selector', 'datestart', get_string('datestart', 'local_umnglobalmessage'), $attributes);
        $mform->addHelpButton('datestart', 'datestart', 'local_umnglobalmessage');

        $mform->addElement('date_time_selector', 'dateend', get_string('dateend', 'local_umnglobalmessage'), $attributes);
        $mform->addHelpButton('dateend', 'dateend', 'local_umnglobalmessage');
        $mform->disabledIf('dateend', 'hasend');

        $mform->addElement('checkbox', 'hasend', '', get_string('hasend', 'local_umnglobalmessage'));

        $options = array('category' => get_string('coursecategory'));
        $mform->addElement('select', 'target', get_string('target', 'local_umnglobalmessage'), $options);
        $mform->addHelpButton('target', 'target', 'local_umnglobalmessage');

        $attributes = array('disabled' => 'disabled');

        // This portion can get heavy on computations. We can safely assume that the categories have not chaged recently,
        // so we will set the options to a session variable if it does not exist, or simply use the variable if present.
        if (isset($SESSION->local_umnglobalmessage_categorytree)
            && is_array($SESSION->local_umnglobalmessage_categorytree)) {
            $options = $SESSION->local_umnglobalmessage_categorytree;
        } else {
            $displaylist = coursecat::make_categories_list('moodle/course:changecategory');
            $options = array();
            foreach ($displaylist as $key => $value) {
                $target = '.category-'.$key;
                // Get all children of this one category.
                $children = coursecat::get($key)->get_children();
                while (!empty($children)) {
                    $secondchildren = array();
                    foreach ($children as $childkey => $childvalue) {
                        // Add this child to the target.
                        $target .= '|.category-'.$childvalue->id;
                        // Find further children of this child category.
                        $child = coursecat::get($childvalue->id);
                        if ($child) {
                            $secondchildren = array_merge($secondchildren, $child->get_children());
                        }
                    }
                    $children = $secondchildren;
                }
                $options[$target] = $value;
            }
            $SESSION->local_umnglobalmessage_categorytree = $options;
        }
        $mform->addElement('select', 'category', get_string('coursecategory'), $options, $attributes);
        $mform->addHelpButton('category', 'category', 'local_umnglobalmessage');

        $systemcontext = context_system::instance();
        $roles = role_fix_names(get_all_roles(), $systemcontext, ROLENAME_ORIGINAL);
        $options = array('0' => get_string('allroles', 'local_umnglobalmessage'));
        foreach ($roles as $key => $value) {
            $options[$value->id] = $value->localname;
        }
        $mform->addElement('select', 'userrole', get_string('userrole', 'local_umnglobalmessage'), $options);
        $mform->addHelpButton('userrole', 'userrole', 'local_umnglobalmessage');

        $options = array(1 => get_string('buttons', 'local_umnglobalmessage'),
                         2 => get_string('x', 'local_umnglobalmessage'));
        $mform->addElement('select', 'dismissing', get_string('dismissing', 'local_umnglobalmessage'), $options);
        $mform->addHelpButton('dismissing', 'dismissing', 'local_umnglobalmessage');

        $mform->addElement('checkbox', 'popup', get_string('popup_form', 'local_umnglobalmessage'));
        $mform->addHelpButton('popup', 'popup_form', 'local_umnglobalmessage');

        $mform->addElement('checkbox', 'frequency', get_string('session', 'local_umnglobalmessage'));
        $mform->addHelpButton('frequency', 'session', 'local_umnglobalmessage');
        $mform->setDefault('frequency', 1);

        if (isset($this->_customdata['conflict'])) {
            $mform->addElement('html', '<span class="error" tabindex="0">'.$this->_customdata['conflict'].'</span>');
            $mform->addElement('checkbox', 'forceenable', get_string('forceenable', 'local_umnglobalmessage'));
            $mform->addHelpButton('forceenable', 'forceenable', 'local_umnglobalmessage');
        }
        $mform->addElement('checkbox', 'enabled', get_string('enablemessage', 'local_umnglobalmessage'));
        $mform->addHelpButton('enabled', 'enablemessage', 'local_umnglobalmessage');

        $buttonarray = array();
        if (isset($this->_customdata['id'])) {
            $mform->addElement('checkbox', 'update', get_string('forceupdate', 'local_umnglobalmessage'));
            $mform->addHelpButton('update', 'forceupdate', 'local_umnglobalmessage');
            $buttonarray[] = &$mform->createElement('hidden', 'id', $this->_customdata['id']);
            $mform->setType('id', PARAM_INT);
        }
        $buttonarray[] = &$mform->createElement('hidden', 'action', 'save');
        $mform->setType('action', PARAM_TEXT);
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('submit'), array('data-action' => 'save'));
        $buttonarray[] = &$mform->createElement('cancel', 'cancelbutton', get_string('cancel'), array('data-action' => 'cancel'));
        $mform->addGroup($buttonarray, 'buttons', '', array(' '), false);
    }

    /**
     * Display the form that displays all options
     */
    private function definition_all() {
        global $CFG, $SESSION;

        $mform = $this->_form;

        $attributes = array();
        if (isset($this->_customdata['name'])) {
            $mform->addElement('html', '<span class="error" tabindex="0">'.$this->_customdata['name'].'</span>');
        }
        $mform->addElement('text', 'name', get_string('name', 'local_umnglobalmessage'), $attributes);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', get_string('emptyname', 'local_umnglobalmessage'), 'required');
        $mform->addHelpButton('name', 'name', 'local_umnglobalmessage');

        $mform->addElement('editor', 'message', get_string('message', 'local_umnglobalmessage'), array('rows' => '5', 'autosave' => false));
        $mform->setType('message', PARAM_RAW);
        $mform->addHelpButton('message', 'message', 'local_umnglobalmessage');

        $attributes = array('rows' => '2', 'cols' => '50');
        $mform->addElement('textarea', 'description', get_string('description', 'local_umnglobalmessage'), $attributes);
        $mform->setType('description', PARAM_NOTAGS);
        $mform->addHelpButton('description', 'description', 'local_umnglobalmessage');

        $mform->addElement('textarea', 'css', get_string('css', 'local_umnglobalmessage'), $attributes);
        $mform->setType('css', PARAM_NOTAGS);
        $mform->addHelpButton('css', 'css', 'local_umnglobalmessage');
        $mform->setDefault('css', get_string('css_default', 'local_umnglobalmessage'));

        $attributes = array(
            'startyear' => 2016,
            'stopyear'  => 2020,
            'timezone'  => 99,
            'step'      => 5,
            'optional'  => false);
        if (isset($this->_customdata['dates'])) {
            $mform->addElement('html', '<span class="error" tabindex="0">'.$this->_customdata['dates'].'</span>');
        }
        $mform->addElement('date_time_selector', 'datestart', get_string('datestart', 'local_umnglobalmessage'), $attributes);
        $mform->addHelpButton('datestart', 'datestart', 'local_umnglobalmessage');

        $mform->addElement('date_time_selector', 'dateend', get_string('dateend', 'local_umnglobalmessage'), $attributes);
        $mform->addHelpButton('dateend', 'dateend', 'local_umnglobalmessage');
        $mform->disabledIf('dateend', 'hasend');

        $mform->addElement('checkbox', 'hasend', '', get_string('hasend', 'local_umnglobalmessage'));

        $options = array('site' => get_string('sitewide', 'local_umnglobalmessage'),
                         '#page-my-index' => get_string('myhome', 'local_umnglobalmessage'),
                         '#page-course' => get_string('course', 'local_umnglobalmessage'),
                         '#page-grade' => get_string('gradebook', 'local_umnglobalmessage'),
                         '#page-admin' => get_string('admin', 'local_umnglobalmessage'),
                         'category' => get_string('coursecategory'),
                         'other' => get_string('othertarget', 'local_umnglobalmessage'));
        $mform->addElement('select', 'target', get_string('target', 'local_umnglobalmessage'), $options);
        $mform->addHelpButton('target', 'target', 'local_umnglobalmessage');

        $attributes = array('disabled' => 'disabled');

        // This portion can get heavy on computations. We can safely assume that the categories have not chaged recently,
        // so we will set the options to a session variable if it does not exist, or simply use the variable if present.
        if (isset($SESSION->local_umnglobalmessage_categorytree)
            && is_array($SESSION->local_umnglobalmessage_categorytree)) {
            $options = $SESSION->local_umnglobalmessage_categorytree;
        } else {
            $displaylist = coursecat::make_categories_list('moodle/course:create');
            $options = array();
            foreach ($displaylist as $key => $value) {
                $target = '.category-'.$key;
                // Get all children of this one category.
                $children = coursecat::get($key)->get_children();
                while (!empty($children)) {
                    $secondchildren = array();
                    foreach ($children as $childkey => $childvalue) {
                        // Add this child to the target.
                        $target .= '|.category-'.$childvalue->id;
                        // Find further children of this child category.
                        $child = coursecat::get($childvalue->id);
                        if ($child) {
                            $secondchildren = array_merge($secondchildren, $child->get_children());
                        }
                    }
                    $children = $secondchildren;
                }
                $options[$target] = $value;
            }
            $SESSION->local_umnglobalmessage_categorytree = $options;
        }
        $mform->addElement('select', 'category', get_string('coursecategory'), $options, $attributes);
        $mform->addHelpButton('category', 'category', 'local_umnglobalmessage');
        $mform->disabledIf('category', 'target', 'neq', 'category');

        if (isset($this->_customdata['othertarget'])) {
            $mform->addElement('html', '<span class="error" tabindex="0">'.$this->_customdata['othertarget'].'</span>');
            $attributes = array();
        }
        $mform->addElement('text', 'othertarget', get_string('othertarget', 'local_umnglobalmessage'), $attributes);
        $mform->setType('othertarget', PARAM_TEXT);
        $mform->addHelpButton('othertarget', 'othertarget', 'local_umnglobalmessage');
        $mform->disabledIf('othertarget', 'target', 'neq', 'other');

        $systemcontext = context_system::instance();
        $roles = role_fix_names(get_all_roles(), $systemcontext, ROLENAME_ORIGINAL);
        $options = array('0' => get_string('allroles', 'local_umnglobalmessage'));
        foreach ($roles as $key => $value) {
            $options[$value->id] = $value->localname;
        }
        $mform->addElement('select', 'userrole', get_string('userrole', 'local_umnglobalmessage'), $options);
        $mform->addHelpButton('userrole', 'userrole', 'local_umnglobalmessage');

        $options = array(1 => get_string('buttons', 'local_umnglobalmessage'),
                         2 => get_string('x', 'local_umnglobalmessage'));
        $mform->addElement('select', 'dismissing', get_string('dismissing', 'local_umnglobalmessage'), $options);
        $mform->addHelpButton('dismissing', 'dismissing', 'local_umnglobalmessage');

        $mform->addElement('checkbox', 'popup', get_string('popup_form', 'local_umnglobalmessage'));
        $mform->addHelpButton('popup', 'popup_form', 'local_umnglobalmessage');

        $mform->addElement('checkbox', 'frequency', get_string('session', 'local_umnglobalmessage'));
        $mform->addHelpButton('frequency', 'session', 'local_umnglobalmessage');
        $mform->setDefault('frequency', 1);

        if (isset($this->_customdata['conflict'])) {
            $mform->addElement('html', '<span class="error" tabindex="0">'.$this->_customdata['conflict'].'</span>');
            $mform->addElement('checkbox', 'forceenable', get_string('forceenable', 'local_umnglobalmessage'));
            $mform->addHelpButton('forceenable', 'forceenable', 'local_umnglobalmessage');
        }
        $mform->addElement('checkbox', 'enabled', get_string('enablemessage', 'local_umnglobalmessage'));
        $mform->addHelpButton('enabled', 'enablemessage', 'local_umnglobalmessage');

        $buttonarray = array();
        if (isset($this->_customdata['id'])) {
            $mform->addElement('checkbox', 'update', get_string('forceupdate', 'local_umnglobalmessage'));
            $mform->addHelpButton('update', 'forceupdate', 'local_umnglobalmessage');
            $buttonarray[] = &$mform->createElement('hidden', 'id', $this->_customdata['id']);
        } else {
            $buttonarray[] = &$mform->createElement('hidden', 'id', 0);
        }
        $mform->setType('id', PARAM_INT);
        $buttonarray[] = &$mform->createElement('hidden', 'action', 'save');
        $mform->setType('action', PARAM_TEXT);
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('submit'), array('data-action' => 'save'));
        $buttonarray[] = &$mform->createElement('cancel', 'cancelbutton', get_string('cancel'), array('data-action' => 'cancel'));
        $mform->addGroup($buttonarray, 'buttons', '', array(' '), false);
    }
}
