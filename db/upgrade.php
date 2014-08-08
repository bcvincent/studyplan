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
 * This file keeps track of upgrades to the studyplan module
 *
 * Sometimes, changes between versions involve alterations to database
 * structures and other major things that may break installations. The upgrade
 * function in this file will attempt to perform all the necessary actions to
 * upgrade your older installation to the current version. If there's something
 * it cannot do itself, it will tell you what you need to do.  The commands in
 * here will all be database-neutral, using the functions defined in DLL libraries.
 *
 * @package    mod_studyplan
 * @copyright  2011 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Execute studyplan upgrade from the given old version
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_studyplan_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager(); // loads ddl manager and xmldb classes
    
    if ($oldversion < 2014071400) {
        // Change field 'name' column to default null
        $table = new xmldb_table('studyplan');
        $field = new xmldb_field('name', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'quiz');

        // apply the change if the field is here to fix
        if ($dbman->field_exists($table, $field)) { $dbman->change_field_type($table, $field); }
        upgrade_mod_savepoint(true, 2014071400, 'studyplan');

    }
    
    if ($oldversion < 2014080700) {
    	
        // Create the studyplan_progress table
        if (!$dbman->table_exists('studyplan_progress')) {
			$table = new xmldb_table('studyplan_progress');
			$table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
			$table->add_field('studyplan', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'id');
			$table->add_field('user', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'studyplan');
			$table->add_field('percent', XMLDB_TYPE_NUMBER, '10,5', null, XMLDB_NOTNULL, null, '0', 'user');
			$table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'percent');
			$table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'timecreated');
			$table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
			$table->add_index('studyplan', XMLDB_INDEX_NOTUNIQUE, array('studyplan'));
			$table->add_index('user', XMLDB_INDEX_NOTUNIQUE, array('user'));
			$dbman->create_table($table);
		}
        upgrade_mod_savepoint(true, 2014080700, 'studyplan');
    }
    
    return true;
}
