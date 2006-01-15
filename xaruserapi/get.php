<?php
/**
 * Get a specific course
 *
 * @package Xaraya eXtensible Management System
 * @copyright (C) 2002-2005 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage Courses Module
 * @link http://xaraya.com/index.php/release/179.html
 * @author Courses module development team
 */
/**
 * get a specific course
 *
 * @author the Courses module development team
 * @param  $args ['courseid'] id of course item to get
 * @returns array
 * @return item array, or false on failure
 * @raise BAD_PARAM, DATABASE_ERROR, NO_PERMISSION
 */
function courses_userapi_get($args)
{
    extract($args);

    if (!isset($courseid) || !is_numeric($courseid)) {
        $msg = xarML('Invalid #(1) for #(2) function #(3)() in module #(4)',
            'item ID', 'user', 'get', 'courses');
        xarErrorSet(XAR_SYSTEM_EXCEPTION, 'BAD_PARAM',
            new SystemException($msg));
        return;
    }
    // Check if user can see this course
    if (xarSecurityCheck('AdminCourses', 0)) {
    $where = "0, 1";
    } else {
    $where = "0";
    }

    /* Get database setup */
    $dbconn =& xarDBGetConn();
    $xartable =& xarDBGetTables();
    $coursestable = $xartable['courses'];
    /* Get the course */
    $query = "SELECT xar_name,
                   xar_number,
                   xar_type,
                   xar_level,
                   xar_shortdesc,
                   xar_intendedcredits,
                   xar_freq,
                   xar_contact,
                   xar_contactuid,
                   xar_hidecourse,
                   xar_last_modified
            FROM $coursestable
            WHERE xar_courseid = ? AND xar_hidecourse in ($where)";
    $result = &$dbconn->Execute($query, array((int)$courseid));
    if (!$result) return;
    // Check for no rows found, and if so, close the result set and return an exception
    if ($result->EOF) {
        $result->Close();
        $msg = xarML('This course does not exists');
        xarErrorSet(XAR_SYSTEM_EXCEPTION, 'ID_NOT_EXIST',
            new SystemException(__FILE__ . '(' . __LINE__ . '): ' . $msg));
        return;
    }
    // Extract fields
    list($name, $number, $coursetype, $level, $shortdesc, $intendedcredits, $freq, $contact, $contactuid, $hidecourse, $last_modified) = $result->fields;
    $result->Close();

    // Security checks
    // For this function, the user must *at least* have READ access to this item
    if (!xarSecurityCheck('ViewCourses', 1, 'Course', "$courseid:All:All")) {
        return;
        }
    $item = array('courseid'    => $courseid,
                'name'          => $name,
                'number'        => $number,
                'coursetype'    => $coursetype,
                'level'         => $level,
                'shortdesc'     => $shortdesc,
                'intendedcredits' => $intendedcredits,
                'freq'          => $freq,
                'contact'       => $contact,
                'contactuid'    => $contactuid,
                'hidecourse'    => $hidecourse,
                'last_modified' => $last_modified);
    // Return the item array
    return $item;
}
?>
