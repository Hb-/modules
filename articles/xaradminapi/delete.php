<?php

/**
 * Delete an article
 * Usage : if (xarModAPIFunc('articles', 'admin', 'delete', $article)) {...}
 *
 * @param $args['aid'] ID of the article
 * @param $args['ptid'] publication type ID for the item (*cough*)
 * @returns bool
 * @return true on success, false on failure
 */
function articles_adminapi_delete($args)
{
    // Get arguments from argument array
    extract($args);

    // Argument check
    if (!isset($aid)) {
        $msg = xarML('Invalid #(1) for #(2) function #(3)() in module #(4)',
                    'article ID', 'admin', 'delete',
                    'Articles');
        xarExceptionSet(XAR_USER_EXCEPTION, 'BAD_PARAM',
                       new SystemException($msg));
        return false;
    }

    // Security check
    if (!xarModAPILoad('articles', 'user')) return;

    $args['mask'] = 'DeleteArticles';
    if (!xarModAPIFunc('articles','user','checksecurity',$args)) {
        $msg = xarML('Not authorized to delete #(1) items',
                    'Article');
        xarExceptionSet(XAR_USER_EXCEPTION, 'NO_PERMISSION',
                       new SystemException($msg));
        return false;
    }

    // Call delete hooks for categories, hitcount etc.
    $args['module'] = 'articles';
    $args['itemid'] = $aid;
    if (isset($ptid)) {
        $args['itemtype'] = $ptid;
    }
    xarModCallHooks('item', 'delete', $aid, $args);

    // Get database setup
    $dbconn =& xarDBGetConn();
    $xartable =& xarDBGetTables();
    $articlestable = $xartable['articles'];

    // Delete item
    $query = "DELETE FROM $articlestable
            WHERE xar_aid = ?";
    $result =& $dbconn->Execute($query,array($aid));
    if (!$result) return;

    return true;
}

?>
