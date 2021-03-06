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
 * Defines the version of studyplan
 *
 * This code fragment is called by moodle_needs_upgrading() and
 * /admin/index.php
 *
 * @package    mod_studyplan
 * @copyright  2011 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$module->release   = 'v1.1.1';
$module->maturity  = MATURITY_STABLE;
$module->version   = 2015020200;      // The current module version (Date: YYYYMMDDXX)
$module->requires  = 2013050100;      // Requires this Moodle version
$module->cron      = 21600;               // Execute the cron command every 6 hours in (6*60*60) seconds
$module->component = 'mod_studyplan'; // To check on upgrade, that module sits in correct place
