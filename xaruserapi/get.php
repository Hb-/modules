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
 * get a specific headline
 * @poaram $args['hid'] id of headline to get
 * @returns array
 * @return link array, or false on failure
 */
function headlines_userapi_get($args)
{
    extract($args);

    if (empty($hid) || !is_numeric($hid)) {
        $msg = xarML('Invalid Headline ID');
        xarErrorSet(XAR_SYSTEM_EXCEPTION, 'BAD_PARAM', new SystemException($msg));
        return;
    }

    // Security Check
    if(!xarSecurityCheck('OverviewHeadlines')) return;

    $dbconn =& xarDBGetConn();
    $xartable =& xarDBGetTables();
    $headlinestable = $xartable['headlines'];

    // Get headline
    $query = "SELECT xar_hid,
                     xar_title,
                     xar_desc,
                     xar_url,
                     xar_order
            FROM $headlinestable
            WHERE xar_hid = ?";
    $bindvars = array($hid);
    $result =& $dbconn->Execute($query,$bindvars);
    if (!$result) return;

    list($hid, $title, $desc, $url, $order) = $result->fields;
    $result->Close();

    $link = array('hid'     => $hid,
                  'title'   => $title,
                  'desc'    => $desc,
                  'url'     => $url,
                  'order'   => $order);

    // Get categories (if any)
    if (xarModIsHooked('categories','headlines')) {
        $cids = xarModAPIFunc('categories','user','getlinks',
                              array('iids' => array($hid),
                                    //'itemtype' => 0, // not needed here
                                    'modid' => xarModGetIDFromName('headlines'),
                                    'reverse' => 1));
        if (isset($cids[$hid]) && is_array($cids[$hid])) {
            $link['cids'] = $cids[$hid];
            $link['catid'] = join('+',$cids[$hid]);
        }
    }
    return $link;
}
?>