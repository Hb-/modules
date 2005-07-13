<?php
/**
 * File: $Id:
 * 
 * Delete an example item
 * 
 * @package Xaraya eXtensible Management System
 * @copyright (C) 2003 by the Xaraya Development Team.
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage example
 * @author Example module development team 
 */
/**
 * delete an example item
 * 
 * @author the Example module development team 
 * @param  $args ['exid'] ID of the item
 * @returns bool
 * @return true on success, false on failure
 * @raise BAD_PARAM, NO_PERMISSION, DATABASE_ERROR
 */
function courses_adminapi_delete($args)
{
    extract($args);
    if (!xarVarFetch('courseid', 'int:1:', $courseid)) return;

    if (!isset($courseid) || !is_numeric($courseid)) {
        $msg = xarML('Invalid #(1) for #(2) function #(3)() in module #(4)',
            'item ID', 'admin', 'delete', 'Courses');
        xarErrorSet(XAR_SYSTEM_EXCEPTION, 'BAD_PARAM',
            new SystemException($msg));
        return;
    }

    $item = xarModAPIFunc('courses',
        'user',
        'get',
        array('courseid' => $courseid));
    // Check for exceptions
    if (!isset($item) && xarCurrentErrorType() != XAR_NO_EXCEPTION) return; // throw back

    if (!xarSecurityCheck('DeleteCourses', 1, 'Course', "All:All:$courseid")) {
        return;
    }
    // Get database setup
    $dbconn =& xarDBGetConn();
    $xartable =& xarDBGetTables();
    $coursestable = $xartable['courses'];
    // Delete the item
    $query = "DELETE FROM $coursestable
            WHERE xar_courseid = ?";
    $result = &$dbconn->Execute($query, array((int)$courseid));
    // Check for an error with the database code, adodb has already raised
    // the exception so we just return
    if (!$result) return;
    // Let any hooks know that we have deleted an item.  As this is a
    // delete hook we're not passing any extra info
    // xarModCallHooks('item', 'delete', $exid, '');
    $item['module'] = 'courses';
    $item['itemid'] = $courseid;
    xarModCallHooks('item', 'delete', $courseid, $item);
    // Let the calling process know that we have finished successfully
    return true;
}

?>
