<?php

/**
 * Delete a publication type
 *
 * @param $args['ptid'] ID of the publication type
 * @returns bool
 * @return true on success, false on failure
 */
function articles_adminapi_deletepubtype($args)
{
    // Get arguments from argument array
    extract($args);

    // Argument check - make sure that all required arguments are present
    // and in the right format, if not then set an appropriate error
    // message and return
    if (!isset($ptid) || !is_numeric($ptid) || $ptid < 1) {
        $msg = xarML('Invalid #(1) for #(2) function #(3)() in module #(4)',
                    'publication type ID', 'admin', 'deletepubtype',
                    'Articles');
        xarExceptionSet(XAR_USER_EXCEPTION, 'BAD_PARAM',
                       new SystemException($msg));
        return false;
    }

    // Security check - we require ADMIN rights here
    if (!xarSecurityCheck('AdminArticles',1,'Article',"$ptid:All:All:All")) return;

    // Load user API to obtain item information function
    if (!xarModAPILoad('articles', 'user')) return;

    // Get current publication types
    $pubtypes = xarModAPIFunc('articles','user','getpubtypes');
    if (!isset($pubtypes[$ptid])) {
        $msg = xarML('Invalid #(1) for #(2) function #(3)() in module #(4)',
                    'publication type ID', 'admin', 'deletepubtype',
                    'Articles');
        xarExceptionSet(XAR_USER_EXCEPTION, 'BAD_PARAM',
                       new SystemException($msg));
        return false;
    }

    // Get database setup
    $dbconn =& xarDBGetConn();
    $xartable =& xarDBGetTables();
    $pubtypestable = $xartable['publication_types'];

    // Delete the publication type
    $query = "DELETE FROM $pubtypestable
            WHERE xar_pubtypeid = " . xarVarPrepForStore($ptid);
    $result =& $dbconn->Execute($query);
    if (!$result) return;

    $articlestable = $xartable['articles'];

    // Delete all articles for this publication type
    $query = "DELETE FROM $articlestable
            WHERE xar_pubtypeid = " . xarVarPrepForStore($ptid);
    $result =& $dbconn->Execute($query);
    if (!$result) return;

// TODO: call some kind of itemtype delete hooks here, once we have those
    //xarModCallHooks('itemtype', 'delete', $ptid,
    //                array('module' => 'articles',
    //                      'itemtype' =>'ptid'));

    return true;
}

?>
