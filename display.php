<?php
defined('MOODLE_INTERNAL') || die;

require_once('umnglobalmessage_findmessage.function.php');
require_once('umnglobalmessage.class.php');

global $DB;

$umngm = '';
$allrecords = $DB->get_records('local_umnglobalmessage', array('enabled' => 1));
$records = array();
foreach ($allrecords as $key => $value) {
    $globalmessage = new umnglobalmessage($value->id);
    if ($globalmessage->candisplay()) {
        $records[] = $value;
    }
}
if (!empty($records)) {
    $messages = findmessage($records);
    foreach ($messages as $key => $value) {
        $message_content = $value->message;
        $message_css = $value->css;
        $message_frequency = $value->frequency;
        $message_popup = $value->popup ? 'popup' : '';
        $message_name = $value->name;
        preg_match('/id="(.*?)"/', $OUTPUT->body_attributes(), $bodyID);
        $url = new moodle_url('/local/umnglobalmessage/index.php');
        $viewcurrent = html_writer::tag('a', get_string('viewcurrent', 'local_umnglobalmessage'),
                                        array('href' => $url));
        if ($value->dismissing == 1) {
            $dismiss = html_writer::tag('button', get_string('dismiss', 'local_umnglobalmessage'),
                                        array('class' => 'umngm_close',
                                              'data-id' => $value->id,
                                              'data-action' => 'dismiss'));
            if ($message_frequency) {
                $dismiss .= html_writer::tag('button', get_string('dismissforever', 'local_umnglobalmessage'),
                                             array('class' => 'umngm_close',
                                                   'data-id' => $value->id,
                                                   'data-action' => 'dismissforever'));
            }
            $header = '<div class="local_umnglobalmessage_header">'.$message_name.'</div>';
            $close = '<div class="mdl-align">'.$dismiss.'</div><div class="mdl-align">'.$viewcurrent.'</div>';
            $umngm .= '<div id="local_umnglobalmessage_'.$value->id.'" class="umngm '.$message_popup.'">'
                      .$header.'<div class="local_umnglobalmessage_content" style="'.$message_css.'">'
                      .$message_content.$close.'</div></div>';
        } else {
            $close = '<div class="local_umnglobalmessage_header">'.$message_name
                     .'<div class="close umngm_close" data-id="'.$value->id.'" data-action="dismiss"></div>'
                     .'</div>';
            $umngm .= '<div id="local_umnglobalmessage_'.$value->id.'" class="umngm '.$message_popup.'">'
                      .$close.'<div class="local_umnglobalmessage_content" style="'.$message_css.'">'
                      .$message_content.'</div></div>';
        }
        $globalmessage = new umnglobalmessage($value->id);
        $globalmessage->notify();
    }
}
echo $umngm;
$PAGE->requires->js_call_amd('local_umnglobalmessage/umnglobalmessage', 'message');
