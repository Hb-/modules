<?php
/*
 *
 * Keywords Module
 *
 * @package Xaraya eXtensible Management System
 * @copyright (C) 2003 by the Xaraya Development Team
 * @license GPL <http://www.gnu.org/licenses/gpl.html>
 * @link http://www.xaraya.com
 * @author mikespub
*/

/**
 * get entries for a module item
 *
 * @param $args['modid'] module id
 * @returns array
 * @return array of keywords
 * @raise BAD_PARAM, NO_PERMISSION, DATABASE_ERROR

 */
function keywords_userapi_getwordslimited($args)
{
    if (!xarSecurityCheck('ReadKeywords')) return;

    extract($args);

    if (!isset($moduleid) || !is_numeric($moduleid)) {
        $msg = xarML('Invalid #(1) for #(2) function #(3)() in module #(4)',
                    'module id', 'user', 'getwordslimited', 'keywords');

        xarExceptionSet(XAR_USER_EXCEPTION, 'BAD_PARAM',
                       new SystemException($msg));
        return;
    }


    $dbconn =& xarDBGetConn();
    $xartable =& xarDBGetTables();
    $keywordstable = $xartable['keywords_restr'];

    // Get restricted keywords for this module item

    $query = "SELECT xar_id,
                     xar_keyword
             FROM $keywordstable ";
    if (isset($itemtype)) {
          $query .= " WHERE xar_moduleid = '0' OR ( xar_moduleid= " . xarVarPrepForStore($moduleid) ." AND  xar_itemtype = ". xarVarPrepForStore($itemtype) ." ) ORDER BY xar_keyword ASC";
       } else {
          $query .= " WHERE xar_moduleid = '0' OR  xar_moduleid= " . xarVarPrepForStore($moduleid) ." ORDER BY xar_keyword ASC";
    }


    $result =& $dbconn->Execute($query);
    if (!$result) return;
    if ($result->EOF) {
        $result->Close();
  /*      $query = "SELECT xar_id,
                     xar_keyword
              FROM $keywordstable
              WHERE xar_moduleid = " . xarVarPrepForStore($moduleid) . " ORDER BY xar_keyword ASC";
        $result =& $dbconn->Execute($query); */
    }
    
    $keywords = array();

    while (!$result->EOF) {
        list($id,
             $word) = $result->fields;
        $keywords[$id] = $word;
        $result->MoveNext();
    }
    $result->Close();

    return $keywords;
}


?>