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
 * get list of keywords (from the existing assignments for now)
 *
 * @param $args['count'] if you want to count items per keyword
 */
function keywords_userapi_getlist($args)
{
    if (!xarSecurityCheck('ReadKeywords')) return;
    
    extract($args); 

    $dbconn =& xarDBGetConn();
    $xartable =& xarDBGetTables();
    $keywordstable = $xartable['keywords'];

    if (!isset($tab)){
        $tab='0';
    }
 
    if ($tab == '0'){ 
        $where = null;
    } elseif ($tab == '1'){
        $where = " WHERE ("
        ."'A' <= ".$dbconn->substr."(".$dbconn->upperCase."(xar_keyword),1,1) AND "
        .$dbconn->substr."(".$dbconn->upperCase."(xar_keyword),1,1) <= 'F')";
    } elseif ($tab == '2'){    
           $where = " WHERE ("
        ."'G' <= ".$dbconn->substr."(".$dbconn->upperCase."(xar_keyword),1,1) AND "
        .$dbconn->substr."(".$dbconn->upperCase."(xar_keyword),1,1) <= 'L')";
    } elseif ($tab == '3'){    
           $where = " WHERE ("
        ."'M' <= ".$dbconn->substr."(".$dbconn->upperCase."(xar_keyword),1,1) AND "
        .$dbconn->substr."(".$dbconn->upperCase."(xar_keyword),1,1) <= 'R')";
    } elseif ($tab == '4'){    
           $where = " WHERE ("
        ."'S' <= ".$dbconn->substr."(".$dbconn->upperCase."(xar_keyword),1,1) AND "
        .$dbconn->substr."(".$dbconn->upperCase."(xar_keyword),1,1) <= 'Z')";
    } elseif ($tab == '5'){    
          $where = " WHERE ("
        .$dbconn->substr."(".$dbconn->upperCase."(xar_keyword),1,1) < 'A' OR "
        .$dbconn->substr."(".$dbconn->upperCase."(xar_keyword),1,1) > 'Z')";
    }                
     

    // Get count per keyword from the database
    if (!empty($args['count'])) {
        $query = "SELECT xar_keyword, COUNT(xar_id)
                  FROM $keywordstable $where
                  GROUP BY xar_keyword
                  ORDER BY xar_keyword ASC";
        $result =& $dbconn->Execute($query);
        if (!$result) return;

        $items = array();
        if ($result->EOF) {
            $result->Close();
            return $items;
        }
        while (!$result->EOF) {
            list($word,$count) = $result->fields;
            $items[$word] = $count;
            $result->MoveNext();
        }
        $result->Close();
        return $items;
    }

    // Get distinct keywords from the database
    $query = "SELECT DISTINCT xar_keyword
              FROM $keywordstable  $where
              ORDER BY xar_keyword ASC";
    $result =& $dbconn->Execute($query);
    if (!$result) return;

    $items = array();
    $items[''] = '';
    if ($result->EOF) {
        $result->Close();
        return $items;
    }
    while (!$result->EOF) {
        list($word) = $result->fields;
        $items[$word] = $word;
        $result->MoveNext();
    }
    $result->Close();
    return $items;
}

?>
