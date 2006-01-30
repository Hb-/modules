<?php
/**
 * Get a course name
 *
 * @package modules
 * @copyright (C) 2002-2006 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage Courses Module
 * @link http://xaraya.com/index.php/release/179.html
 * @author Courses module development team
 */
/**
 * get the name for a courses
 *
 * @author Michel V.
 * @author the Courses module development team
 * @param  $args ['courseid'] The ID of the coursename to get
 * @returns array
 * @return $name
 * @raise BAD_PARAM, DATABASE_ERROR, NO_PERMISSION
 */
function courses_userapi_getcoursename($args)
{
    extract($args);
    if (!isset($courseid) || !is_numeric($courseid)) {
        $msg = xarML('Invalid #(1) for #(2) function #(3)() in module #(4)',
            'item ID', 'user', 'getcoursename', 'courses');
        xarErrorSet(XAR_SYSTEM_EXCEPTION, 'BAD_PARAM',
            new SystemException($msg));
        return;
    }
    if (xarSecurityCheck('AdminCourses', 0)) {
    $where = "0, 1";
    } else {
    $where = "0";
    }

    if (!xarSecurityCheck('ViewCourses')) return;
    $item = array();
    $dbconn =& xarDBGetConn();
    $xartable =& xarDBGetTables();
    $coursestable = $xartable['courses'];
    $query = "SELECT xar_name
              FROM $coursestable
              WHERE xar_courseid = ? AND xar_hidecourse in ($where)";
    $result = &$dbconn->Execute($query, array($courseid));
    if (!$result) return;
    // Check for no rows found, and if so, close the result set and return an exception
    if ($result->EOF) {
        $result->Close();
        $msg = xarML('This course does not exists');
        xarErrorSet(XAR_SYSTEM_EXCEPTION, 'ID_NOT_EXIST',
            new SystemException(__FILE__ . '(' . __LINE__ . '): ' . $msg));
        return;
    }
    list($name) = $result->fields;
    $result->Close();
    return $name;
}

?>
