<?php

/**
 * Update an article
 * Usage : if (xarModAPIFunc('articles', 'admin', 'update', $article)) {...}
 *
 * @param $args['aid'] ID of the item (mandatory argument)
 * @param $args['title'] name of the item (mandatory argument)
 * @param $args['summary'] summary of the item
 * @param $args['body'] body of the item
 * @param $args['notes'] notes for the item
 * @param $args['status'] status of the item
 * @param $args['ptid'] publication type ID for the item (*cough*)
 * @param $args['pubdate'] publication date in unix time format
 * @param $args['authorid'] ID of the new author (*cough*)
 * @param $args['language'] language of the item
 * @param $args['cids'] category IDs this item belongs to
 * @returns bool
 * @return true on success, false on failure
 */
function articles_adminapi_update($args)
{
    // Get arguments from argument array
    extract($args);

    // Argument check
    if (empty($aid) || !is_numeric($aid)) {
        $msg = xarML('Invalid #(1) for #(2) function #(3)() in module #(4)',
                    'article ID', 'admin', 'update',
                    'Articles');
        xarExceptionSet(XAR_USER_EXCEPTION, 'BAD_PARAM',
                       new SystemException($msg));
        return false;
    } elseif (empty($title)) {
        $msg = xarML('Invalid #(1) for #(2) function #(3)() in module #(4)',
                    'title', 'admin', 'update',
                    'Articles');
        xarExceptionSet(XAR_USER_EXCEPTION, 'BAD_PARAM',
                       new SystemException($msg));
        return false;
    }

// Note : this will take care of checking against the current article values
//        too if nothing is passed as arguments except aid & title

    // Security check
    if (!xarModAPILoad('articles', 'user')) return;

    $args['mask'] = 'EditArticles';
    if (!xarModAPIFunc('articles','user','checksecurity',$args)) {
        $msg = xarML('Not authorized to update #(1) items',
                    'Article');
        xarExceptionSet(XAR_USER_EXCEPTION, 'NO_PERMISSION',
                       new SystemException($msg));
        return false;
    }

    // Get database setup
    $dbconn =& xarDBGetConn();
    $xartable =& xarDBGetTables();
    $articlestable = $xartable['articles'];
    $bindvars = array();
    // Update the item
    $query = "UPDATE $articlestable
            SET xar_title = ?";
    $bindvars[] = $title;
// Note : we use isset() here because we *do* care whether it's set to ''
//        or if it's not set at all

    if (isset($summary)) {
        $query .= ", xar_summary = ?";
        $bindvars[] = $summary;
    }

    if (isset($body)) {
        $query .= ", xar_body = ?";
        $bindvars[] = $body;
    }

    if (isset($notes)) {
        $query .= ", xar_notes = ?";
        $bindvars[] = $notes;
    }

    if (isset($status) && is_numeric($status)) {
        $query .= ", xar_status = ?";
        $bindvars[] = $status;
    }

    // not recommended
    if (isset($ptid) && is_numeric($ptid)) {
        $query .= ", xar_pubtypeid = ?";
        $bindvars[] = $ptid;
    }

    if (isset($pubdate) && is_numeric($pubdate)) {
        $query .= ", xar_pubdate = ?";
        $bindvars[] = $pubdate;
    }

    // not recommended
    if (isset($authorid) && is_numeric($authorid)) {
        $query .= ", xar_authorid = ?";
        $bindvars[] = $authorid;
    }

    if (isset($language)) {
        $query .= ", xar_language = ?";
        $bindvars[] = $language;
    }
    $query .= " WHERE xar_aid = ?";
    $bindvars[] =  $aid;
    $result =& $dbconn->Execute($query,$bindvars);
    if (!$result) return;

    if (empty($cids)) {
        $cids = array();
    }

    // Call update hooks for categories etc.
    // We need to tell some hooks that we are coming from the update status screen
    // and not the update the actual article screen.  Right now, the keywords vanish
    // into thin air.  Bug 1960 and 3161
    if (xarVarIsCached('Hooks.all','noupdate')){
        $args['statusflag'] = true; // legacy support for old method - remove later on
    }

    $args['module'] = 'articles';
    $args['itemtype'] = $ptid;
    $args['cids'] = $cids;
    xarModCallHooks('item', 'update', $aid, $args);

    return true;
}

?>
