<?php
/**
 * HTML Module
 *
 * @package modules
 * @subpackage html module
 * @category Third Party Xaraya Module
 * @version 1.5.0
 * @copyright see the html/credits.html file in this release
 * @link http://www.xaraya.com/index.php/release/779.html
 * @author John Cox
 */

/**
 * Get a specific tag
 *
 * @public
 * @author John Cox
 * @author Richard Cave
 * @param $args['cid'] id of tag to get
 * @return array link array, or false on failure
 * @throws BAD_PARAM
 */
function html_userapi_gettag($args)
{
    // Extract arguments
    extract($args);

    // Argument check
    if (!isset($cid) || !is_numeric($cid)) {
        $msg = xarML('Invalid Parameter #(1) for #(2) function #(3)() in module #(4)', 'cid', 'userapi', 'get', 'html');
        xarErrorSet(XAR_SYSTEM_EXCEPTION, 'BAD_PARAM', new SystemException($msg));
        return;
    }

    // Security Check
    if(!xarSecurityCheck('ReadHTML')) return;

    // Get database setup
    $dbconn = xarDB::getConn();
    $xartable =& xarDB::getTables();
    $htmltable = $xartable['html'];
    $htmltypestable = $xartable['htmltypes'];

    // Get tag
    $query = "SELECT $htmltable.xar_cid,
                     $htmltable.xar_tid,
                     $htmltypestable.xar_type,
                     $htmltable.xar_tag,
                     $htmltable.xar_allowed
              FROM $htmltable, $htmltypestable
              WHERE $htmltable.xar_cid = ?
              AND $htmltable.xar_tid = $htmltypestable.xar_id";

        $result =& $dbconn->Execute($query,array($cid));
    if (!$result) return;
    list($cid,
         $tid,
         $type,
         $tag,
         $allowed) = $result->fields;
    $result->Close();
    $tag = array('cid'      => $cid,
                 'tid'      => $tid,
                 'type'     => $type,
                 'tag'      => $tag,
                 'allowed'  => $allowed);
    return $tag;
}
?>