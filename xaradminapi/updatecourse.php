<?php
/**
 * File: $Id:
 * 
 * Update a course item
 * 
 * @package Xaraya eXtensible Management System
 * @copyright (C) 2005 by the Xaraya Development Team.
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage courses
 * @author Courses module development team 
 */
/**
 * update a course
 * 
 * @author the Course module development team 
 * @param  $args ['courseid'] the ID of the course
 * @param  $args ['name'] the new name of the item
 * @param  $args ['number'] the new number of the item
 * @param  $args all other course variables ;)
 * @raise BAD_PARAM, NO_PERMISSION, DATABASE_ERROR
 */
function courses_adminapi_updatecourse($args)
{
    extract($args);
    
    if (!xarVarFetch('courseid', 'int:1:', $courseid)) return;
    if (!xarVarFetch('objectid', 'int:1:', $objectid, '', XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('name', 'str:1:', $name)) return;
    if (!xarVarFetch('number', 'str:1:', $number)) return;
	/*
    if (!xarVarFetch('coursetype', 'str:1:', $coursetype, '', XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('level', 'int:1:', $level, '', XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('shortdesc', 'str:1:', $shortdesc, '', XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('language', 'str:1:', $language, '', XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('freq', 'str:1:', $freq, '', XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('contact', 'str:1:', $contact, '', XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('contactuid', 'int:1:', $contactuid,'', XARVAR_NOT_REQUIRED)) return;    
    if (!xarVarFetch('hidecourse', 'int:1:', $hidecourse, '', XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('last_modified', 'str:1:', $last_modified, '', XARVAR_NOT_REQUIRED)) return;
    */
    
    // Argument check
	// TODO: should these be in other place? Non-API?
    $invalid = array();
    if (!isset($courseid) || !is_numeric($courseid)) {
        $invalid[] = 'Course ID';
    }
    if (!isset($name) || !is_string($name)) {
        $invalid[] = 'Course name';
    }
    if (!isset($number) || !is_string($number)) {
        $invalid[] = 'Course number';
    }

    if (count($invalid) > 0) {
        $msg = xarML('Invalid #(1) for #(2) function #(3)() in module #(4)',
            join(', ', $invalid), 'adminapi', 'updatecourse', 'Courses');
        xarErrorSet(XAR_SYSTEM_EXCEPTION, 'BAD_PARAM', new SystemException($msg));
        return;
    }
    // The user API function is called.
    $item = xarModAPIFunc('courses',
        'user',
        'get',
        array('courseid' => $courseid));
    // Check for exceptions
    if (!isset($item) && xarCurrentErrorType() != XAR_NO_EXCEPTION) return; // throw back

    // Security check
    if (!xarSecurityCheck('EditCourses', 1, 'Course', "$courseid:All:All")) {
        echo "here";
    }
    // Get database setup
    $dbconn =& xarDBGetConn();
    $xartable =& xarDBGetTables();
    $coursestable = $xartable['courses'];
    // Update the item
    $query = "UPDATE $coursestable
              SET xar_name = ?,
                 xar_number = ?,
                 xar_type = ?,
                 xar_level = ?,
                 xar_shortdesc = ?,
                 xar_language = ?,
                 xar_freq = ?,
                 xar_contact = ?,
                 xar_contactuid =?,
                 xar_hidecourse = ?,
                 xar_last_modified = ?
              WHERE xar_courseid = ?";

    $bindvars = array($name, $number, $coursetype, $level, $shortdesc, $language, $freq, $contact, $contactuid,
                      $hidecourse, $last_modified, $courseid);
    $result = &$dbconn->Execute($query, $bindvars);
    // Check for an error with the database code, adodb has already raised
    // the exception so we just return
    if (!$result) {
		return false;
	}
    // Let any hooks know that we have updated an item.  As this is an
    // update hook we're passing the updated $item array as the extra info
    $item['module'] = 'courses';
    $item['itemid'] = $courseid;
    $item['name'] = $name;
    $item['number'] = $number;
    $item['level'] =$level;
    $item['shortdesc'] = $shortdesc;
    $item['freq'] = $freq;
    $item['contact'] = $contact;
    $item['contactuid'] = $contactuid;
    $item['hidecourse'] = $hidecourse;
    $item['last_modified'] =$last_modified;
    xarModCallHooks('item', 'update', $courseid, $item);
    // Let the calling process know that we have finished successfully
    return true;
}

?>
