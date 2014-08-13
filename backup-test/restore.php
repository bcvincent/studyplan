<?php
 
define( 'CLI_SCRIPT', true );
 
chdir('/var/www/ae.ket.org');
require_once( 'config.php' );
require_once( $CFG->dirroot . '/backup/util/includes/restore_includes.php' );
 
// Transaction
$transaction = $DB->start_delegated_transaction( );
 
// Create new course
$folder                 = '9706ff11ee909cc426e66fff74926faa'; // as found in: $CFG->dataroot . '/temp/backup/' 
$categoryid             = 1; // e.g. 1 == Miscellaneous
$user_doing_the_restore = 4; // e.g. 2 == admin
$courseid               = restore_dbops::create_new_course( 'Z', 'Z', $categoryid );
 
// Restore backup into course
$controller = new restore_controller( $folder, $courseid, 
        backup::INTERACTIVE_NO, backup::MODE_SAMESITE, $user_doing_the_restore,
        backup::TARGET_NEW_COURSE );
$controller->execute_precheck( );
$controller->execute_plan( );
 
// Commit
$transaction->allow_commit( );