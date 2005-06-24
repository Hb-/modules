<?php
/*
 * Get the configuration of all users which categories the user wants to 
 * recieve alerts for.
 *
 * @package Xaraya eXtensible Management System
 * @copyright (C) 2004 by Metrostat Technologies, Inc.
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.metrostat.net
 *
 * @subpackage julian
 * initial template: Roger Raymond
 * @author Jodie Razdrh/John Kevlin/David St.Clair
 *
 * @return array array(uid=>subscriptions_array)
 */
function julian_userapi_getallsubscriptions()
{
    // TODO is there no function for all this? something like xarModGetAllUserVars()
    $dbconn =& xarDBGetConn();
    $tables =& xarDBGetTables();

    $modBaseInfo = xarMod_getBaseInfo('julian', 'module');
    if (!isset($modBaseInfo)) {
        return; // throw back
    }
    // Takes the right table basing on module mode
    if ($modBaseInfo['mode'] == XARMOD_MODE_SHARED) {
        $module_uservarstable = $tables['system/module_uservars'];
    } elseif ($modBaseInfo['mode'] == XARMOD_MODE_PER_SITE) {
        $module_uservarstable = $tables['site/module_uservars'];
    }
    $modvarid = xarModGetVarId('julian', 'alerts');
    if (!$modvarid) return;

    // it's all about this query
    $query = "SELECT xar_uid,xar_value FROM $module_uservarstable
                  WHERE xar_mvid = ?";

    $result =& $dbconn->Execute($query, array((int)$modvarid));
    $subscriptions = array();
    while(!$result->EOF) {
        // uid => subscriptions_array
        $subscriptions[$result->fields[0]] = unserialize($result->fields[1]);
        $result->MoveNext();
    }

    return $subscriptions;
}
?>