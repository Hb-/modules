<?php

/**
 * File: $Id$
 *
 * Get information on a repository
 *
 * @package modules
 * @copyright (C) 2003 by the Xaraya Development Team.
 * @link http://www.xaraya.com
 * 
 * @subpackage bkview
 * @author Marcel van der Boom <marcel@xaraya.com>
*/

// This should be the only place where we include the class file
include_once("modules/bkview/xarincludes/bk.class.php");

/**
 * get a specific item
 *
 * @author the Bkview module development team
 * @param $args['exid'] id of bkview item to get
 * @returns array
 * @return item array, or false on failure
 * @raise BAD_PARAM, DATABASE_ERROR, NO_PERMISSION
 */
function bkview_userapi_get($args)
{
    if (!xarSecurityCheck('ViewAllRepositories')) return;

    extract($args);
    if (!isset($repoid) || !is_numeric($repoid)) {
        $msg = xarML('Invalid #(1) for #(2) function #(3)() in module #(4)',
                     'item ID', 'user', 'get', 'Bkview');
        xarErrorSet(XAR_SYSTEM_EXCEPTION, 'BAD_PARAM',
                        new SystemException($msg));
        return;
    }
    
    $dbconn =& xarDBGetConn();
    $xartable =& xarDBGetTables();
    $bkviewtable = $xartable['bkview'];
    $sql = "SELECT xar_repoid,
                   xar_name,
                   xar_path
            FROM $bkviewtable
            WHERE xar_repoid = ?";
    $result = $dbconn->Execute($sql,array($repoid));
    if(!$result) return;

    // Check for no rows found, and if so, close the result set and return an exception
    if ($result->EOF) {
        $result->Close();
        $msg = xarML('This item does not exists:').$sql;
        xarErrorSet(XAR_SYSTEM_EXCEPTION, 'ID_NOT_EXIST',
                       new SystemException(__FILE__.'('.__LINE__.'): '.$msg));
        return;
    }

    // Obtain the item information from the result set
    list($repoid, $reponame, $repopath) = $result->fields;

    // All successful database queries produce a result set, and that result
    // set should be closed when it has been finished with
    $result->Close();

    // Create the item array
    $repo = new bkRepo($repopath);
    $item = array('repoid' => $repoid,
                  'reponame' => $reponame,
                  'repopath' => $repopath,
                  'repo' => $repo);
    // Return the item 
    return $item;
}
?>