<?php
/**
 * @package Xaraya eXtensible Management System
 * @copyright (C) 2005 by the Xaraya Development Team.
 * @link http://www.xaraya.com
 *
 * @subpackage headlines module
 * @author John Cox
*/
/**
 * update an headline
 * @param $args['hid'] the ID of the link
 * @param $args['url'] the new url of the link
 */
function headlines_adminapi_update($args)
{
    // Get arguments from argument array
    extract($args);

    // Argument check
    if ((!isset($hid)) ||
        (!isset($url))) {
        $msg = xarML('Invalid Parameter Count');
        xarErrorSet(XAR_SYSTEM_EXCEPTION, 'BAD_PARAM', new SystemException($msg));
        return;
    }

    // The user API function is called
    $link = xarModAPIFunc('headlines',
                          'user',
                          'get',
                          array('hid' => $hid));

    if ($link == false) return;
    // Security Check
    if(!xarSecurityCheck('EditHeadlines')) return;
    // Get datbase setup
    $dbconn =& xarDBGetConn();
    $xartable =& xarDBGetTables();
    $headlinestable = $xartable['headlines'];

    // Update the link
    $query = "UPDATE $headlinestable
            SET xar_url = ?,
                xar_title = ?,
                xar_desc = ?,
                xar_order = ?
            WHERE xar_hid = ?";
    $bindvars = array($url, $title, $desc, $order, $hid);
    $result =& $dbconn->Execute($query,$bindvars);
    if (!$result) return;
    // Let the calling process know that we have finished successfully
    xarModCallHooks('item', 'update', $hid, '');
    return true;
}
?>
