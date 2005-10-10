<?php
/**
 * Get all example items
 *
 * @package Xaraya eXtensible Management System
 * @copyright (C) 2002-2005 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage Example Module
 */

/**
 * Get all example items
 * 
 * @author the Example module development team 
 * @param numitems $ the number of items to retrieve (default -1 = all)
 * @param startnum $ start with this item number (default 1)
 * @returns array
 * @return array of items, or false on failure
 * @raise BAD_PARAM, DATABASE_ERROR, NO_PERMISSION
 */
function todolist_userapi_getallusers($args)
{ 
    /* Get arguments from argument array - all arguments to this function

/**
 * get all users
 * @returns array
 * @return array of items, or false on failure
    extract($args);

    if (!isset($startnum)) {
        $startnum = 1;
    }
    if (!isset($numitems)) {
        $numitems = -1;
    }

    if ((!isset($startnum)) || (!isset($numitems))) {
        pnSessionSetVar('errormsg', xarML('Error in API arguments'));
        return false;
    }

    $items = array();

    if (!pnSecAuthAction(0, 'todolist::', '::', ACCESS_READ)) {
        return $items;
    }

    $todolist_project_members_column = &$pntable['todolist_project_members_column'];    
    $sql = "SELECT DISTINCT $todolist_project_members_column[member_id] FROM $pntable[todolist_project_members]";
    $result = $dbconn->SelectLimit($sql, $numitems, $startnum-1);
    if ($dbconn->ErrorNo() != 0) {
        pnSessionSetVar('errormsg', xarML('Items load failed'));
        return false;
    }

    for (; !$result->EOF; $result->MoveNext()) {
        list($uid) = $result->fields;
        $userpref = xarModGetUserVar('todolist','userpref',$uid);
        list($unotify, $u1project, $umytasks, $ushowicons) = explode(';',$userpref);
        if (pnSecAuthAction(0, 'todolist::', "::$uid", ACCESS_READ)) {
            $items[] = array('user_id' => $uid,
                             'user_email_notify' => $unotify,
                             'user_primary_project' => $u1project,
                             'user_my_tasks' => $umytasks,
                             'user_show_icons' => $ushowicons);
        }
    }

    $result->Close();
}

     */
    extract($args);
    /* Optional arguments.
     * FIXME: (!isset($startnum)) was ignoring $startnum as it contained a null value
     * replaced it with ($startnum == "") (thanks for the talk through Jim S.) NukeGeek 9/3/02
     * if (!isset($startnum)) { */
    if (!isset($startnum)) {
        $startnum = 1;
    }
    if (!isset($numitems)) {
        $numitems = -1;
    }
    /* Argument check - make sure that all required arguments are present and
     * in the right format, if not then set an appropriate error message
     * and return
     * Note : since we have several arguments we want to check here, we'll
     * report all those that are invalid at the same time...
     */
    $invalid = array();
    if (!isset($startnum) || !is_numeric($startnum)) {
        $invalid[] = 'startnum';
    }
    if (!isset($numitems) || !is_numeric($numitems)) {
        $invalid[] = 'numitems';
    }
    if (count($invalid) > 0) {
        $msg = xarML('Invalid #(1) for #(2) function #(3)() in module #(4)',
            join(', ', $invalid), 'user', 'getall', 'Example');
        xarErrorSet(XAR_SYSTEM_EXCEPTION, 'BAD_PARAM',
            new SystemException($msg));
        return;
    }

    $items = array();
    /* Security check - important to do this as early on as possible to
     * avoid potential security holes or just too much wasted processing
     */
    if (!xarSecurityCheck('ViewExample')) return;
    /* Get database setup - note that both xarDBGetConn() and xarDBGetTables()
     * return arrays but we handle them differently.  For xarDBGetConn() we
     * currently just want the first item, which is the official database
     * handle.  For xarDBGetTables() we want to keep the entire tables array
     * together for easy reference later on
     */
    $dbconn =& xarDBGetConn();
    $xartable =& xarDBGetTables();
    /* It's good practice to name the table definitions you are
     * using - $table doesn't cut it in more complex modules
     */
    $exampletable = $xartable['example'];
    /* TODO: how to select by cat ids (automatically) when needed ???
     * Get items - the formatting here is not mandatory, but it does make the
     * SQL statement relatively easy to read.  Also, separating out the sql
     * statement from the SelectLimit() command allows for simpler debug
     * operation if it is ever needed
     */
    $query = "SELECT xar_exid,
                     xar_name,
                     xar_number
              FROM $exampletable
              ORDER BY xar_name";
    /* SelectLimit also supports bind variable, they get to be put in
     * as the last parameter in the function below. In this case we have no
     * bind variables, so we left the parameter out. We could have passed in an
     * empty array though.
     */
    $result = $dbconn->SelectLimit($query, $numitems, $startnum-1);
    /* Check for an error with the database code, adodb has already raised
     * the exception so we just return
     */
    if (!$result) return;
    /* Put items into result array.  Note that each item is checked
     * individually to ensure that the user is allowed *at least* OVERVIEW
     * access to it before it is added to the results array.
     * If more severe restrictions apply, e.g. for READ access to display
     * the details of the item, this *must* be verified by your function.
     */
    for (; !$result->EOF; $result->MoveNext()) {
        list($exid, $name, $number) = $result->fields;
        if (xarSecurityCheck('ViewExample', 0, 'Item', "$name:All:$exid")) {
            $items[] = array('exid'   => $exid,
                             'name'   => $name,
                             'number' => $number);
        }
    }
    /* All successful database queries produce a result set, and that result
     * set should be closed when it has been finished with
     */
    $result->Close(); 
    /* Return the items */
    return $items;
} 
?>