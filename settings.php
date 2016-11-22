<?php

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $pagename = new lang_string('pluginname', 'local_umnglobalmessage');
    $ADMIN->add('localplugins', new admin_externalpage('local_umnglobalmessage',
                                $pagename,
                                new moodle_url('/local/umnglobalmessage/index.php')));
}
