<?php

defined('MOODLE_INTERNAL') || die();

function xmldb_local_umnglobalmessage_upgrade ($oldversion=0) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2016041300) {
        // Define table local_umnglobalmessage to be created.
        $table = new xmldb_table('local_umnglobalmessage');

        // Adding fields to table local_umnglobalmessage.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('message', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('css', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('target', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('othertarget', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('version', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('dismissing', XMLDB_TYPE_INTEGER, '4', null, null, null, null);
        $table->add_field('enabled', XMLDB_TYPE_INTEGER, '4', null, null, null, null);
        $table->add_field('datestart', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('dateend', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('frequency', XMLDB_TYPE_INTEGER, '4', null, null, null, null);
        $table->add_field('hasend', XMLDB_TYPE_INTEGER, '4', null, null, null, null);
        $table->add_field('popup', XMLDB_TYPE_INTEGER, '4', null, null, null, null);

        // Adding keys to table local_umnglobalmessage.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for local_umnglobalmessage.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table local_umnglobalmessage_users to be created.
        $table = new xmldb_table('local_umnglobalmessage_users');

        // Adding fields to table local_umnglobalmessage_users.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('messageid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('messageversion', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table local_umnglobalmessage_users.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Adding indexes to table local_umnglobalmessage_users.
        $table->add_index('user_message', XMLDB_INDEX_UNIQUE, array('userid', 'messageid'));

        // Conditionally launch create table for local_umnglobalmessage_users.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Umnglobalmessage savepoint reached.
        upgrade_plugin_savepoint(true, 2016041300, 'local', 'umnglobalmessage');

        $DB->delete_records_select('config_plugins', "plugin='local_umnglobalmessage' and name!='version'");
    }
    if ($oldversion < 2016050600) {
        // Define field userrole to be added to local_umnglobalmessage.
        $table = new xmldb_table('local_umnglobalmessage_users');
        $field = new xmldb_field('notifyversion', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'messageversion');

        // Conditionally launch add field userrole.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Changing nullability of field messageversion on table local_umnglobalmessage_users to null.
        $table = new xmldb_table('local_umnglobalmessage_users');
        $field = new xmldb_field('messageversion', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'messageid');

        // Launch change of nullability for field messageversion.
        if ($dbman->field_exists($table, $field)) {
            $dbman->change_field_notnull($table, $field);
        }

        // Rename field messageversion on table local_umnglobalmessage_users to dismissversion.
        $table = new xmldb_table('local_umnglobalmessage_users');
        $field = new xmldb_field('messageversion', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'messageid');

        // Launch rename field messageversion.
        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'dismissversion');
        }

        // Define field userrole to be added to local_umnglobalmessage.  Temporarily allowing nulls
        // to allow column creation.  Later, we backpopulate with 0s and change to not nullable.
        $table = new xmldb_table('local_umnglobalmessage');
        $field = new xmldb_field('userrole', XMLDB_TYPE_INTEGER, '4', null, null, null, null, 'popup');

        // Conditionally launch add field userrole.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $DB->set_field('local_umnglobalmessage', 'userrole', 0);

        // Changing nullability of field userrole on table local_umnglobalmessage to not nullable.
        $table = new xmldb_table('local_umnglobalmessage');
        $field = new xmldb_field('userrole', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Launch change of nullability for field userrole.
        if ($dbman->field_exists($table, $field)) {
            $dbman->change_field_notnull($table, $field);
        }

        // Define field description to be added to local_umnglobalmessage.
        $table = new xmldb_table('local_umnglobalmessage');
        $field = new xmldb_field('description', XMLDB_TYPE_TEXT, null, null, null, null, null, 'userrole');

        // Conditionally launch add field description
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Umnglobalmessage savepoint reached.
        upgrade_plugin_savepoint(true, 2016050600, 'local', 'umnglobalmessage');
    }
    return true;
}
