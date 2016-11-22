<?php

# Even though "ajax_" is in the name of the file, this is used for
# more than just ajax calls.  Eventually, we should clean this up
# so that it is clear what is for ajax and what is not.  The best
# way to tell right now is to see which $action cases end in an
# exit call.  Those are the actions that must be invoked through
# ajax.  The other cannot be, as currently implemented.

// Set up the graceful fail to format output for proper JSON encoding.
try {
    require_once('../../config.php');
    require_once('umnglobalmessage.class.php');
    require_once('umnglobalmessage_admin.class.php');
    require_once('umnglobalmessage_category.class.php');
    require_once('umnglobalmessage_student.class.php');
    require_once('umnglobalmessage_display_form.function.php');
    require_once('umnglobalmessage_form.class.php');
    require_once($CFG->libdir. '/coursecatlib.php');

    require_login();

    $context = context_system::instance();
    $PAGE->set_context($context);
    $url = new moodle_url('/local/umnglobalmessage/index.php');
    $PAGE->set_url($url);

    $action = required_param('action', PARAM_TEXT);
    $ajax = optional_param('ajax', false, PARAM_BOOL);
    $sort = optional_param('sort', null, PARAM_TEXT);
    $reverse = optional_param('reverse', false, PARAM_BOOL);
    $id = optional_param('id', null, PARAM_INT);
    $search = optional_param('search', '', PARAM_TEXT);

    $datesonly = false;
    if (is_siteadmin($USER->id)) {
        $cap = 'admin';
    } else if (!empty(coursecat::make_categories_list('moodle/course:changecategory'))) {
        $cap = 'category';
    } else {
        $cap = 'student';
    }

    if ($cap === 'student' && $action !== 'dismiss' && $action !== 'dismissforever' && $action !== 'search') {
        throw new Exception(get_string('invalidaccess', 'local_umnglobalmessage'));
    }
    switch ($action) {
        case 'search':
            $search = required_param('search', PARAM_TEXT);
            if ($cap === 'admin') {
                $gm = new umnglobalmessage_admin();
            } else if ($cap === 'category') {
                $gm = new umnglobalmessage_category();
            } else {
                $gm = new umnglobalmessage_student();
            }
            $html = $gm->show_table($sort, $reverse, null, $search);
            echo json_encode(array('html' => $html, 'type' => 'mainpage'));
            exit;
        case 'remove':
            $id = required_param('id', PARAM_INT);
            $gm = new umnglobalmessage($id);
            $gm->delete();
            // Intentionally fall through to load the page.
        case 'index':
            if ($cap === 'admin') {
                $gm = new umnglobalmessage_admin();
            } else if ($cap === 'category') {
                $gm = new umnglobalmessage_category();
            }
            $html = $gm->show_table($sort, $reverse, null, $search);
            echo json_encode(array('html' => $html, 'type' => 'mainpage'));
            exit;
        case 'duplicate':
            $id = required_param('id', PARAM_INT);
            $gm = new umnglobalmessage($id);
            $errors = $gm->duplicate();
            if ($cap === 'admin') {
                $gm = new umnglobalmessage_admin();
            } else if ($cap === 'category') {
                $gm = new umnglobalmessage_category();
            }
            $html = $gm->show_table($sort, $reverse, $errors, $search);
            echo json_encode(array('html' => $html, 'type' => 'mainpage'));
            exit;
        case 'dismiss':
            $id = required_param('id', PARAM_INT);
            $gm = new umnglobalmessage($id);
            $gm->dismiss();
            exit;
        case 'dismissforever':
            $id = required_param('id', PARAM_INT);
            $gm = new umnglobalmessage($id);
            $gm->dismiss_forever();
            exit;
        case 'sort':
            $sort = required_param('sort', PARAM_TEXT);
            if ($cap === 'admin') {
                $gm = new umnglobalmessage_admin();
            } else if ($cap === 'category') {
                $gm = new umnglobalmessage_category();
            }
            $html = $gm->show_table($sort, $reverse, null, $search);
            echo json_encode(array('html' => $html, 'type' => 'mainpage'));
            exit;
        case 'disable':
            $id = required_param('id', PARAM_INT);
            $gm = new umnglobalmessage($id);
            $gm->disable();
            if ($cap === 'admin') {
                $gm = new umnglobalmessage_admin();
            } else if ($cap === 'category') {
                $gm = new umnglobalmessage_category();
            }
            $html = $gm->show_table($sort, $reverse, null, $search);
            echo json_encode(array('html' => $html, 'type' => 'mainpage'));
            exit;
        case 'edit':
            $id = required_param('id', PARAM_INT);
            $gm = new umnglobalmessage($id);
            $record = $gm->output_variables();
            list($datestart, $dateend) = $gm->display_dates();
            $record->datestart = $datestart;
            $record->dateend = $dateend;
            $record->message = array('text' => $record->message, 'format' => '1');
            $form = new umnglobalmessage_form(null, array('datesonly' => $datesonly, 'id' => $id, 'cap' => $cap));
            $form->set_data($record);
            break;
        case 'enable':
            $id = required_param('id', PARAM_INT);
            $datesonly = true;
            $gm = new umnglobalmessage($id);
            $variables = array('id',
                               'hasend');
            $record = $gm->output_variables($variables);
            list($datestart, $dateend) = $gm->display_dates();
            $record->datestart = $datestart;
            $record->dateend = $dateend;
            $gm->check_conflict();
            $errors = $gm->output_variables(array('errors'), true)['errors'];
            $customdata = array_merge($errors, array('datesonly' => $datesonly, 'id' => $id, 'cap' => $cap));
            $form = new umnglobalmessage_form(null, $customdata);
            $form->set_data($record);
            break;
        case 'savedates':
            $id = required_param('id', PARAM_INT);
            $datesonly = true;
            $form = new umnglobalmessage_form(null, array('datesonly' => $datesonly, 'id' => $id, 'cap' => $cap));
            break;
        case 'save':
            // Do nothing, but has to be here to prevent the malformed $action error.
            break;
        case 'add':
            $form = new umnglobalmessage_form(null, array('datesonly' => $datesonly, 'id' => $id, 'cap' => $cap));
            break;
        default:
           $html = html_writer::tag('div', get_string('malformedaction', 'local_umnglobalmessage'));
           echo json_encode(array('html' => $html));
           die('Malformed action to ajax_local_umnglobalmessage: '.$action);
    }

    if ($_POST && !isset($_POST['cancelbutton'])) {
        $gm = new umnglobalmessage($id);
        $errors = $gm->validate($_POST, $datesonly);
        if (empty($errors)) {
            $url = new moodle_url('/local/umnglobalmessage/index.php', array('sort' => $sort, 'reverse' => $reverse, 'search' => $search));
            redirect($url);
        } else {
            $customdata = array_merge($errors, array('datesonly' => $datesonly, 'id' => $id, 'cap' => $cap));
            $form = new umnglobalmessage_form(null, $customdata);
            display_form($form, $action);
        }
    } else if ($_POST && isset($_POST['cancelbutton'])) {
        $url = new moodle_url('/local/umnglobalmessage/index.php', array('sort' => $sort, 'reverse' => $reverse, 'search' => $search));
        redirect($url);
        exit;
    } else {
        display_form($form, $action);
    }
} catch (Exception $e) {
    if ($ajax) {
        if ($cap === 'admin') {
            $gm = new umnglobalmessage_admin();
        } else if ($cap === 'category') {
            $gm = new umnglobalmessage_category();
        }
        $html = $gm->show_table($sort, $reverse, array($e->getMessage()), $search);
        echo json_encode(array('html' => $html, 'type' => 'mainpage'));
    } else {
        $errors = array($e->getMessage());
        $data = array('sort' => $sort, 'reverse' => $reverse, 'search' => $search, 'errors' => $errors);
        $url = new moodle_url('/local/umnglobalmessage/index.php?'.http_build_query($data));
        redirect($url);
    }
    exit;
}
