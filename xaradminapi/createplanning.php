<?php
/**
 * Create a new planned course
 *
 * @package Xaraya eXtensible Management System
 * @copyright (C) 2002-2005 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage courses
 * @author Courses module development team
 */
/**
 * create a new course planning
 *
 * @author the Courses module development team
 * @param  $args ['courseid'] ID of the course
 * @param  $args ['number'] number of the course etc
 * @returns int
 * @return planning ID on success, false on failure
 * @raise BAD_PARAM, NO_PERMISSION, DATABASE_ERROR
 */
function courses_adminapi_createplanning($args)
{
    // Get arguments from argument array
    extract($args);
    // Argument check TODO
    
    // Security check
    if (!xarSecurityCheck('EditCourses', 1, 'Course', "$courseid:All:All")) {
        return;
    }
    // Get database setup
    $dbconn =& xarDBGetConn();
    $xartable =& xarDBGetTables();
    $planningtable = $xartable['courses_planning'];
    // Get next ID in table
    $nextId = $dbconn->GenId($planningtable);
    // Add item
    $query = "INSERT INTO $planningtable (
                           xar_planningid,
                           xar_courseid,
                           xar_courseyear,
                           xar_credits,
                           xar_creditsmin,
                           xar_creditsmax,
                           xar_startdate,
                           xar_enddate,
                           xar_prerequisites,
                           xar_aim,
                           xar_method,
                           xar_longdesc,
                           xar_costs,
                           xar_committee,
                           xar_coordinators,
                           xar_lecturers,
                           xar_location,
                           xar_material,
                           xar_info,
                           xar_program,
                           xar_minparticipants,
                           xar_maxparticipants,
                           xar_closedate,
                           xar_hideplanning,
                           xar_last_modified)
              VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
            
    $bindvars = array($nextId, $courseid, $year, $credits, $creditsmin, $creditsmax, $startdate, $enddate, $prerequisites, $aim, $method, $longdesc,
     $costs, $committee, $coordinators, $lecturers, $location, $material, $info, $program, $minparticipants, $maxparticipants, $closedate, $hideplanning, $last_modified);
    $result = &$dbconn->Execute($query, $bindvars);
    // Check for an error with the database code, adodb has already raised
    // the exception so we just return
    if (!$result) return;
    // Get the ID of the item that we inserted.
    $planningid = $dbconn->PO_Insert_ID($planningtable, 'xar_planningid');
    // Let any hooks know that we have created a new item.
    // xarModCallHooks('item', 'create', $planningid, 'planningid');
    $item = $args;
    $item['module'] = 'courses';
    $item['itemid'] = $planningid;
    xarModCallHooks('item', 'create', $planningid, $item);
    // Return the id of the newly created item to the calling process
    return $planningid;
}

?>
