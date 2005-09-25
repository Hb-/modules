<?php
/**
* Count all events.
*
* @package Xaraya eXtensible Management System
* @copyright (C) 2004 by Metrostat Technologies, Inc.
* @license GPL {@link http://www.gnu.org/licenses/gpl.html}
* @link http://www.metrostat.net
*
* @subpackage julian
* initial template: Roger Raymond
* @author Jodie Razdrh/John Kevlin/David St.Clair
*/


/**
 * Utility function to count the number of Events in the Calendar.
 *
 * @param $args an array of arguments
 * @param $args['event_id'] The ID of the Event
 * @param $args['external'] retrieve events marked external (1=true, 0=false) - ToDo:
 * @returns integer
 * @return number of items
 * @raise BAD_PARAM, DATABASE_ERROR, NO_PERMISSION
 */
function julian_userapi_countevents($args)
{
    // Security check
    if (!xarSecurityCheck('Viewjulian')) return;

    // Get arguments from argument array
    extract($args);

    // Set defaults
    if (!isset($event_id)) {
        $event_id = 0;
    }

/*  Haven't looked at Archives yet.
    if (!isset($external)) {
        $external = 0;
    }
*/

    // Establish a db connection.
    $dbconn =& xarDBGetConn();
    // Get db tables.
    $xartable = xarDBGetTables();
    // Set Events Table.
    $event_table = $xartable['julian_events'];

    // Create a query to select Events.
    $bindvars = array();
    if ($event_id) {
        // Get the list of Events.
        $query = "SELECT COUNT(1)
                  FROM  $event_table
                  WHERE $event_table.event_id = ? 
                  AND   $event_table.event_id != 0";
        $bindvars[] = array((int) $event_id);
    } else {
        // Get all Events
        $query = "SELECT COUNT(1) FROM $event_table
                  WHERE $event_table.event_id != 0";
    }

    // Check if we want to display external issues.  This is only
    // applicable to viewing issue archives.
/*    if ($external) {
        $query .= " AND $issuesTable.xar_external = 1";
    }
*/

    $result =& $dbconn->Execute($query, $bindvars);
    $noresult = 0;
    // Check for an error
    if (!$result) return $noresult;

    // Obtain the number of items
    list($numitems) = $result->fields;
    // Close result set
    $result->Close();
    // bug 4833: Turn result into Integer 
    $numitems = (INT)$numitems;
    // Return the number of items
    return $numitems;
}

?>
