<?php
/**
 * Comments module - Allows users to post comments on items
 *
 * @package modules
 * @copyright (C) 2002-2007 The copyright-placeholder
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage comments
 * @link http://xaraya.com/index.php/release/14.html
 * @author Carl P. Corliss <rabbitt@xaraya.com>
 */
/**
 * get number of comments for all items or a list of items
 *
 * @param $args['modname'] name of the module you want items from, or
 * @param $args['modid'] module id you want items from
 * @param $args['itemtype'] item type (optional)
 * @param $args['itemids'] array of item IDs
 * @param $args['status'] optional status to count: ALL (minus root nodes), ACTIVE, INACTIVE
 * @param $args['numitems'] optional number of items to return
 * @param $args['startnum'] optional start at this number (1-based)
 * @returns array
 * @return $array[$itemid] = $numcomments;
 */
function comments_userapi_getitems($args)
{
    // Get arguments from argument array
    extract($args);

    // Argument check
    if (!isset($modname) && !isset($modid)) {
        $msg = xarML('Invalid #(1) for #(2) function #(3)() in module #(4)',
                    xarML('module name'), 'user', 'getitems', 'comments');
        throw new BadParameterException($msg);
    }
    if (!empty($modname)) {
        $modid = xarMod::getRegID($modname);
    }
    if (empty($modid)) {
        $msg = xarML('Invalid #(1) for #(2) function #(3)() in module #(4)',
                    xarML('module id'), 'user', 'getitems', 'comments');
        throw new BadParameterException($msg);
    }

    if (!isset($itemtype)) {
        $itemtype = 0;
    }

    if (empty($status)) {
        $status = 'all';
    }
    $status = strtolower($status);

    // Security check
    if (!isset($mask)){
        $mask = 'ReadComments';
    }
    if (!xarSecurityCheck($mask)) return;

    // Database information
    $dbconn = xarDB::getConn();
    $xartable = xarDB::getTables();
    $commentstable = $xartable['comments'];

    switch ($status) {
        case 'active':
            $where_status = "status = ". _COM_STATUS_ON;
            break;
        case 'inactive':
            $where_status = "status = ". _COM_STATUS_OFF;
            break;
        default:
        case 'all':
            $where_status = "status != ". _COM_STATUS_ROOT_NODE;
    }

    // Get items
    $bindvars = array();
    $query = "SELECT objectid, COUNT(*)
                FROM $commentstable
               WHERE modid = ?
                 AND itemtype = ?
                 AND $where_status ";
    $bindvars[] = (int) $modid; $bindvars[] = (int) $itemtype;
    if (isset($itemids) && count($itemids) > 0) {
        $bindmarkers = '?' . str_repeat(',?', count($itemids)-1);
        $bindvars = array_merge($bindvars, $itemids);
        $query .= " AND objectid IN ($bindmarkers)";
    }
    $query .= " GROUP BY objectid
                ORDER BY objectid";
//                ORDER BY (1 + objectid";
//
// CHECKME: dirty trick to try & force integer ordering (CAST and CONVERT are for MySQL 4.0.2 and higher
// <rabbitt> commented that line out because it won't work with PostgreSQL - not sure about others.

    if (!empty($numitems)) {
        if (empty($startnum)) {
            $startnum = 1;
        }
        $result = $dbconn->SelectLimit($query, $numitems, $startnum - 1,$bindvars);
    } else {
        $result = $dbconn->Execute($query,$bindvars);
    }
    if (!$result) return;

    $getitems = array();
    while (!$result->EOF) {
        list($id,$numcomments) = $result->fields;
        $getitems[$id] = $numcomments;
        $result->MoveNext();
    }
    $result->close();
    return $getitems;
}
?>
