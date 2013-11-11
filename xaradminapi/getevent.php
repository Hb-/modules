<?php
/**
 * Pubsub Module
 *
 * @package modules
 * @subpackage pubsub module
 * @category Third Party Xaraya Module
 * @version 2.0.0
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://xaraya.com/index.php/release/181.html
 * @author Pubsub Module Development Team
 * @author Chris Dudley <miko@xaraya.com>
 * @author Garrett Hunter <garrett@blacktower.com>
 */
/**
 * get information about a pubsub event
 *
 * @param $args['eventid'] the event id for the event
 * @return array event information on success, false on failure
 * @throws BAD_PARAM, NO_PERMISSION, DATABASE_ERROR
 */
function pubsub_adminapi_getevent($args)
{
    // Get arguments from argument array
    extract($args);

    if (empty($eventid) || !is_numeric($eventid)) {
        $msg = xarML('Invalid #(1) for #(2) function #(3)() in module #(4)',
                     'event id', 'admin', 'getevent', 'Pubsub');
        throw new Exception($msg);
    }

    if (!xarModAPILoad('categories','user')) return;

    // Get datbase setup
    $dbconn =& xarDB::getConn();
    $xartable =& xarDB::getTables();
    $pubsubeventstable = $xartable['pubsub_events'];
    $modulestable = $xartable['modules'];
    $categoriestable = $xartable['categories'];

    $query = "SELECT $pubsubeventstable.xar_modid,
                     $modulestable.xar_name,
                     xar_itemtype,
                     $pubsubeventstable.xar_cid,
                     xar_extra,
                     xar_groupdescr,
                     $categoriestable.xar_name
                FROM $pubsubeventstable
           LEFT JOIN $modulestable
                  ON $pubsubeventstable.xar_modid = $modulestable.xar_regid
           LEFT JOIN $categoriestable
                  ON $pubsubeventstable.xar_cid = $categoriestable.xar_cid
               WHERE xar_eventid = ?";
    $result = $dbconn->Execute($query, array((int)$eventid));
    if (!$result) return;

    $info = array();

    if ($result->EOF) return false;

    list($info['modid'],
         $info['modname'],
         $info['itemtype'],
         $info['cid'],
         $info['extra'],
         $info['groupdescr'],
         $info['catname']) = $result->fields;

    return $info;
}

?>
