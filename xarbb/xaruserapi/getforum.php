<?php

/**
 * get a specific link
 * @poaram $args['lid'] id of link to get
 * @returns array
 * @return link array, or false on failure
 */
function xarbb_userapi_getforum($args)
{
    extract($args);

    if (empty($fid) && empty($fname)) {
        $msg = xarML('Invalid Parameter Count',
                    '', 'userapi', 'get', 'xarbb');
        xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'BAD_PARAM',
                       new SystemException($msg));
        return;
    }

    list($dbconn) = xarDBGetConn();
    $xartable = xarDBGetTables();

    $xbbforumstable = $xartable['xbbforums'];

    // Get link
    $categoriesdef = xarModAPIFunc('categories','user','leftjoin',
                                   array('cids' => array(),
                                        'modid' => xarModGetIDFromName('xarbb')));
// CHECKME: this won't work for forums that are assigned to more (or less) than 1 category
//          Do we want to support that in the future ?
    // Get links
    $query = "SELECT xar_fid,
                   xar_fname,
                   xar_fdesc,
                   xar_ftopics,
                   xar_fposts,
                   xar_fposter,
                   xar_fpostid,
                   {$categoriesdef['cid']}
            FROM $xbbforumstable
            LEFT JOIN {$categoriesdef['table']} ON {$categoriesdef['field']} = xar_fid
            {$categoriesdef['more']}
            WHERE {$categoriesdef['where']}";
    if (!empty($fid) && is_numeric($fid)) {
        $query .= " AND xar_fid = " . xarVarPrepForStore($fid);
    } else {
        $query .= " AND xar_fname = '" . xarVarPrepForStore($fname) . "'";
    }

    $result =& $dbconn->Execute($query);
    if (!$result) return;

    list($fid, $fname, $fdesc, $ftopics, $fposts, $fposter, $fpostid,$catid) = $result->fields;
    $result->Close();

    if (!xarSecurityCheck('ViewxarBB', 1, 'Forum',"$catid:$fid")) {
        return;
    }
    $forum = array('fid'     => $fid,
                   'fname'   => $fname,
                   'fdesc'   => $fdesc,
                   'ftopics' => $ftopics,
                   'fposts'  => $fposts,
                   'fposter' => $fposter,
                   'fpostid' => $fpostid,
                   'catid'   => $catid);

    return $forum;
}

?>
