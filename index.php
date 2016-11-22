<?php

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir. '/coursecatlib.php');

require_login();

$sort = optional_param('sort', '', PARAM_TEXT);
$reverse = optional_param('reverse', false, PARAM_BOOL);
$search = optional_param('search', '', PARAM_TEXT);
$errors = optional_param_array('errors', null, PARAM_TEXT);

$url = new moodle_url('/local/umnglobalmessage/index.php');
$PAGE->set_url($url);
$PAGE->requires->js_call_amd('local_umnglobalmessage/umnglobalmessage', 'settings');

if (is_siteadmin($USER->id)) {
    require_once($CFG->dirroot.'/local/umnglobalmessage/umnglobalmessage_admin.class.php');
    admin_externalpage_setup('local_umnglobalmessage');

    $gm = new umnglobalmessage_admin();
} else if (!empty(coursecat::make_categories_list('moodle/course:changecategory'))) {
    require_once($CFG->dirroot.'/local/umnglobalmessage/umnglobalmessage_category.class.php');
    // Cannot call above admin function as it requires admin rights, but we still need to prepare the page.

    $PAGE->set_context(null); // hack - set context to something, by default to system context

    $PAGE->set_pagelayout('admin');

    $PAGE->set_title(get_string('pluginname', 'local_umnglobalmessage'));
    $PAGE->set_heading($SITE->fullname);

    $gm = new umnglobalmessage_category();
} else {
    require_once($CFG->dirroot.'/local/umnglobalmessage/umnglobalmessage_student.class.php');
    $PAGE->set_context(context_system::instance());
    $PAGE->set_pagelayout('standard');
    $PAGE->set_title(get_string('pluginname', 'local_umnglobalmessage'));
    $path = $PAGE->navigation->add(get_string('pluginname', 'local_umnglobalmessage'));
    $path->make_active();
    $gm = new umnglobalmessage_student();
}
$html = $gm->show_page($sort, $reverse, $errors, $search);

echo $OUTPUT->header();
echo $html;
echo $OUTPUT->footer();
