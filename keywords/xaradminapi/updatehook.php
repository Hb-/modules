<?php

/**
 * update entry for a module item - hook for ('item','update','API')
 * Optional $extrainfo['keywords'] from arguments, or 'keywords' from input
 *
 * @param $args['objectid'] ID of the object
 * @param $args['extrainfo'] extra information
 * @returns bool
 * @return true on success, false on failure
 * @raise BAD_PARAM, NO_PERMISSION, DATABASE_ERROR
 */
function keywords_adminapi_updatehook($args)
{
    extract($args);

    if (!isset($objectid) || !is_numeric($objectid)) {
        $msg = xarML('Invalid #(1) for #(2) function #(3)() in module #(4)',
                    'object id', 'admin', 'updatehook', 'keywords');
        xarExceptionSet(XAR_USER_EXCEPTION, 'BAD_PARAM',
                       new SystemException($msg));
        // we *must* return $extrainfo for now, or the next hook will fail
        //return false;
        return $extrainfo;
    }
    if (!isset($extrainfo) || !is_array($extrainfo)) {
        $msg = xarML('Invalid #(1) for #(2) function #(3)() in module #(4)',
                    'extrainfo', 'admin', 'updatehook', 'keywords');
        xarExceptionSet(XAR_USER_EXCEPTION, 'BAD_PARAM',
                       new SystemException($msg));
        // we *must* return $extrainfo for now, or the next hook will fail
        //return false;
        return $extrainfo;
    }

    // When called via hooks, the module name may be empty, so we get it from
    // the current module
    if (empty($extrainfo['module'])) {
        $modname = xarModGetName();
    } else {
        $modname = $extrainfo['module'];
    }

    $modid = xarModGetIDFromName($modname);
    if (empty($modid)) {
        $msg = xarML('Invalid #(1) for #(2) function #(3)() in module #(4)',
                    'module name', 'admin', 'updatehook', 'keywords');
        xarExceptionSet(XAR_USER_EXCEPTION, 'BAD_PARAM',
                       new SystemException($msg));
        // we *must* return $extrainfo for now, or the next hook will fail
        //return false;
        return $extrainfo;
    }

    if (!empty($extrainfo['itemtype'])) {
        $itemtype = $extrainfo['itemtype'];
    } else {
        $itemtype = 0;
    }

    if (!empty($extrainfo['itemid'])) {
        $itemid = $extrainfo['itemid'];
    } else {
        $itemid = $objectid;
    }
    if (empty($itemid)) {
        $msg = xarML('Invalid #(1) for #(2) function #(3)() in module #(4)',
                    'item id', 'admin', 'updatehook', 'keywords');
        xarExceptionSet(XAR_USER_EXCEPTION, 'BAD_PARAM',
                       new SystemException($msg));
        // we *must* return $extrainfo for now, or the next hook will fail
        //return false;
        return $extrainfo;
    }

    // check if we need to save some keywords here
    $keywords = xarVarCleanFromInput('keywords');
    if (empty($keywords)) {
        $keywords = '';
    }

    // extract individual keywords from the input string (comma, semi-column or space separated)
    if (strstr($keywords,',')) {
        $words = explode(',',$keywords);
    } elseif (strstr($keywords,';')) {
        $words = explode(';',$keywords);
    } else {
        $words = explode(' ',$keywords);
    }
    $cleanwords = array();
    foreach ($words as $word) {
        $word = trim($word);
        if (empty($word)) continue;
        $cleanwords[] = $word;
    }

/* TODO: restrict to predefined keyword list
    $restricted = xarModGetVar('keywords','restricted');
    if (!empty($restricted)) {
        $wordlist = array();
        if (!empty($itemtype)) {
            $getlist = xarModGetVar('keywords',$modname.'.'.$itemtype);
        } else {
            $getlist = xarModGetVar('keywords',$modname);
        }
        if (!isset($getlist)) {
            $getlist = xarModGetVar('keywords','default');
        }
        if (!empty($getlist)) {
            $wordlist = split(',',$getlist);
        }
        if (count($wordlist) > 0) {
            $acceptedwords = array();
            foreach ($cleanwords as $word) {
                if (!in_array($word, $wordlist)) continue;
                $acceptedwords[] = $word;
            }
            $cleanwords = $acceptedwords;
        }
    }
*/

    // get the current keywords for this item
    $oldwords = xarModAPIFunc('keywords','user','getwords',
                              array('modid' => $modid,
                                    'itemtype' => $itemtype,
                                    'itemid' => $itemid));

    $delete = array();
    $keep = array();
    $new = array();
    // check what we need to delete, what we can keep, and what's new
    if (isset($oldwords) && count($oldwords) > 0) {
        foreach ($oldwords as $id => $word) {
            if (!in_array($word,$cleanwords)) {
                $delete[$id] = $word;
            } else {
                $keep[] = $word;
            }
        }
        foreach ($cleanwords as $word) {
            if (!in_array($word,$keep)) {
                $new[] = $word;
            }
        }
        if (count($delete) == 0 && count($new) == 0) {
            $extrainfo['keywords'] = join(' ',$cleanwords);

            return $extrainfo;
        }
    } else {
        $new = $cleanwords;
    }

    list($dbconn) = xarDBGetConn();
    $xartable = xarDBGetTables();
    $keywordstable = $xartable['keywords'];

    if (count($delete) > 0) {
        // Delete old words for this module item
        $idlist = array_keys($delete);
        $query = "DELETE FROM $keywordstable
                  WHERE xar_id IN (" . join(', ',$idlist) . ")";

        $result =& $dbconn->Execute($query);
        if (!$result) {
            // we *must* return $extrainfo for now, or the next hook will fail
            //return false;
            return $extrainfo;
        }
    }

    if (count($new) > 0) {
        foreach ($new as $word) {
            // Get a new keywords ID
            $nextId = $dbconn->GenId($keywordstable);
            // Create new keywords
            $query = "INSERT INTO $keywordstable (xar_id,
                                               xar_keyword,
                                               xar_moduleid,
                                               xar_itemtype,
                                               xar_itemid)
                    VALUES ($nextId,
                            '" . xarVarPrepForStore($word) . "',
                            '" . xarVarPrepForStore($modid) . "',
                            '" . xarVarPrepForStore($itemtype) . "',
                            '" . xarVarPrepForStore($objectid) . "')";

            $result =& $dbconn->Execute($query);
            if (!$result) {
                // we *must* return $extrainfo for now, or the next hook will fail
                //return false;
                return $extrainfo;
            }

            //$keywordsid = $dbconn->PO_Insert_ID($keywordstable, 'xar_id');
        }
    }

    $extrainfo['keywords'] = join(' ',$cleanwords);

    // Return the extra info
    return $extrainfo;
}


?>
