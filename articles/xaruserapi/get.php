<?php

/**
 * get a specific article by aid, or by a combination of other fields
 *
 * @param $args['aid'] id of article to get, or
 * @param $args['pubtypeid'] pubtype id of article to get, and optional
 * @param $args['title'] title of article to get, and optional
 * @param $args['summary'] summary of article to get, and optional
 * @param $args['body'] body of article to get, and optional
 * @param $args['authorid'] notes of article to get, and optional
 * @param $args['pubdate'] pubdate of article to get, and optional
 * @param $args['notes'] notes of article to get, and optional
 * @param $args['status'] status of article to get, and optional
 * @param $args['language'] language of article to get
 * @param $args['withcids'] (optional) if we want the cids too (default false)
// TODO
 * @param $args['fields'] array with all the fields to return per article
 *                        Default list is : 'aid','title','summary','authorid',
 *                        'pubdate','pubtypeid','notes','status','body'
 *                        Optional fields : 'cids','author','counter','rating','dynamicdata'
 * @param $args['extra'] array with extra fields to return per article (in addition
 *                       to the default list). So you can EITHER specify *all* the
 *                       fields you want with 'fields', OR take all the default
 *                       ones and add some optional fields with 'extra'
 * @returns array
 * @return article array, or false on failure
 */
function articles_userapi_get($args)
{
    // Get arguments from argument array
    extract($args);

    // Argument check
    if (isset($aid) && (!is_numeric($aid) || $aid < 1)) {
        $msg = xarML('Invalid #(1) for #(2) function #(3)() in module #(4)',
                    'article ID', 'user', 'get',
                    'Articles');
        xarExceptionSet(XAR_USER_EXCEPTION, 'BAD_PARAM',
                       new SystemException($msg));
        return false;
    }

// TODO: put all this in dynamic data and retrieve everything via there (including hooked stuff)

    $bindvars = array();
    if (!empty($aid)) {
        $where = "WHERE xar_aid = ?";
        $bindvars[] = $aid;
    } else {
        $wherelist = array();
        $fieldlist = array('title','summary','authorid','pubdate','pubtypeid',
                           'notes','status','body','language');
        foreach ($fieldlist as $field) {
            if (isset($$field)) {
                $wherelist[] = "xar_$field = ?";
                $bindvars[] = $$field;
            }
        }
        if (count($wherelist) > 0) {
            $where = "WHERE " . join(' AND ',$wherelist);
        } else {
            $where = '';
        }
    }

    // Get database setup
    $dbconn =& xarDBGetConn();
    $xartable =& xarDBGetTables();
    $articlestable = $xartable['articles'];

    // Get item
    $query = "SELECT xar_aid,
                   xar_title,
                   xar_summary,
                   xar_body,
                   xar_authorid,
                   xar_pubdate,
                   xar_pubtypeid,
                   xar_notes,
                   xar_status,
                   xar_language
            FROM $articlestable
            $where";
    if (!empty($aid)) {
        $result =& $dbconn->Execute($query,$bindvars);
    } else {
        $result =& $dbconn->SelectLimit($query,1,0,$bindvars);
    }
    if (!$result) return;

    if ($result->EOF) {
        return false;
    }

    list($aid, $title, $summary, $body, $authorid, $pubdate, $pubtypeid, $notes,
         $status, $language) = $result->fields;

    $article = array('aid' => $aid,
                     'title' => $title,
                     'summary' => $summary,
                     'body' => $body,
                     'authorid' => $authorid,
                     'pubdate' => $pubdate,
                     'pubtypeid' => $pubtypeid,
                     'notes' => $notes,
                     'status' => $status,
                     'language' => $language);

    if (!empty($withcids)) {
        $article['cids'] = array();
        if (!xarModAPILoad('categories', 'user')) return;

        $articlecids = xarModAPIFunc('categories',
                                    'user',
                                    'getlinks',
                                    array('iids' => Array($aid),
                                          'itemtype' => $pubtypeid,
                                          'modid' =>
                                               xarModGetIDFromName('articles'),
                                          'reverse' => 0
                                         )
                                   );
        if (is_array($articlecids) && count($articlecids) > 0) {
            $article['cids'] = array_keys($articlecids);
        }
    }

    // Security check
    if (isset($article['cids']) && count($article['cids']) > 0) {
// TODO: do we want all-or-nothing access here, or is one access enough ?
        foreach ($article['cids'] as $cid) {
            if (!xarSecurityCheck('ReadArticles',0,'Article',"$pubtypeid:$cid:$authorid:$aid")) return;
        // TODO: combine with ViewCategoryLink check when we can combine module-specific
        // security checks with "parent" security checks transparently ?
            if (!xarSecurityCheck('ReadCategories',0,'Category',"All:$cid")) return;
        }
    } else {
        if (!xarSecurityCheck('ReadArticles',0,'Article',"$pubtypeid:All:$authorid:$aid")) return;
    }

/*
    if (xarModIsHooked('dynamicdata','articles')) {
        $values =& xarModAPIFunc('dynamicdata','user','getitem',
                                 array('module'   => 'articles',
                                       'itemtype' => $pubtypeid,
                                       'itemid'   => $aid));
        if (!empty($values) && count($values) > 0) {
        // TODO: compare with looping over $name => $value pairs
            $article = array_merge($article,$values);
        }
    }
*/
    return $article;
}

?>
