<?php
// $Id: s.xarinit.php 1.2 02/12/01 14:28:07+01:00 marcel@hsdev.com $
// ----------------------------------------------------------------------
// POST-NUKE Content Management System
// Copyright (C) 2002 by the PostNuke Development Team.
// http://www.postnuke.com/
// ----------------------------------------------------------------------
// Based on:
// PHP-NUKE Web Portal System - http://phpnuke.org/
// Thatware - http://thatware.org/
// ----------------------------------------------------------------------
// LICENSE
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License (GPL)
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WIthOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// To read the license please visit http://www.gnu.org/copyleft/gpl.html
// ----------------------------------------------------------------------
// Original Author of file: Jim McDonald
// Current  Author of file: Curtis Nelson
// Purpose of file:  Initialisation functions for Dynamic Planning
// ----------------------------------------------------------------------

// initialise the module

function dynamic_planning_init()
{
    // Get datbase setup 
    
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    // create table
    $trackstable = $pntable['tracks'];
    $trackscolumn = &$pntable['tracks_column'];

    $sql = "CREATE TABLE $trackstable (
            $trackscolumn[trackid] int(11) NOT NULL auto_increment,
            $trackscolumn[trackname] text,
            $trackscolumn[tracklead] text,
	    $trackscolumn[tracktext] text,
	    $trackscolumn[trackstatus] text,
	    $trackscolumn[trackcat] int(11),
            PRIMARY KEY(pn_trackid))";
    $dbconn->Execute($sql);

    // Check for an error 
    if ($dbconn->ErrorNo() != 0) {
        pnSessionSetVar('errormsg', "$dbconn->ErrorNo()");
        return false;
    }

    // Create the table
    $taskstable = $pntable['tasks'];
    $taskscolumn = &$pntable['tasks_column'];

    $sql = "CREATE TABLE $taskstable (
            $taskscolumn[taskid] int(11) NOT NULL auto_increment,
            $taskscolumn[trackid] int(11) DEFAULT '0' NOT NULL,
            $taskscolumn[tasktitle] varchar(80),
            $taskscolumn[tasktext] text,
            $taskscolumn[taskstart] date,
            $taskscolumn[taskend] date,
	    $taskscolumn[tasklast] date,
            $taskscolumn[taskpercent] int(11) DEFAULT '0' NOT NULL,
            $taskscolumn[tasksteps] text,
            $taskscolumn[taskteam] text,
            PRIMARY KEY(pn_taskid))";
    $dbconn->Execute($sql);

    // Check for an error
    if ($dbconn->ErrorNo() != 0) {
        pnSessionSetVar('errormsg', xarML('Create tasks table failed'));
        return false;
    }


    // Set up an initial value for a module variable.  Note that all module
    // variables should be initialised with some value in this way rather
    // than just left blank, this helps the user-side code and means that
    // there doesn't need to be a check to see if the variable is set in
    // the rest of the code as it always will be
    pnModSetVar('dynamic_planning', 'bold', 0);
    pnModSetVar('dynamic_planning', 'itemsperpage', 10);

    // Initialisation successful
    return true;
}

/**
 * upgrade the template module from an old version
 * This function can be called multiple times
 */
function dynamic_planning_upgrade($oldversion)
{
    // Upgrade dependent on old version number
    switch($oldversion) {
        default:
            // No Upgrade path yet 
            break;
    }

    // Update successful
    return true;
}

/**
 * delete the template module
 * This function is only ever called once during the lifetime of a particular
 * module instance
 */
function dynamic_planning_delete()
{
    // Get datbase setup 
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();
    
    // Set table names
    $trackstable = $pntable['tracks'];
    $taskstable  = $pntable['tasks'];
    
    // Drop the table 
    $sql = "DROP TABLE $trackstable";
    $dbconn->Execute($sql);

    // Check for an error 
    if ($dbconn->ErrorNo() != 0) {
        // Report failed deletion attempt
	pnSessionSetVar('errormsg', xarML('Delete tracks table failed'));
        return false;
    }

    // Drop the table 
    $sql = "DROP TABLE $taskstable";
    $dbconn->Execute($sql);

    // Check for an error 
    if ($dbconn->ErrorNo() != 0) {
        // Report failed deletion attempt
	pnSessionSetVar('errormsg', xarML('Delete tasks table failed'));
        return false;
    }

    // Delete any module variables
    pnModDelVar('Dyanmic_Planning', 'itemsperpage');
    pnModDelVar('Dynamic_Planning', 'bold');

    // Deletion successful
    return true;
}

?>