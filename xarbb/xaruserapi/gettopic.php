<?php

/**
 * get a specific link
 * @poaram $args['tid'] id of topic to get
 * @returns array
 * @return link array, or false on failure
 */
function xarbb_userapi_gettopic($args)
{
    extract($args);

    if (!isset($tid)) {
        $msg = xarML('Invalid Parameter Count',
                    join(', ',$invalid), 'userapi', 'get', 'xarbb');
        xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'BAD_PARAM',
                       new SystemException($msg));
        return;
    }

    list($dbconn) = xarDBGetConn();
    $xartable = xarDBGetTables();

    $xbbtopicstable = $xartable['xbbtopics'];
    $xbbforumstable = $xartable['xbbforums'];

    // Get link
    $categoriesdef = xarModAPIFunc('categories','user','leftjoin',
                                   array('cids' => array(),
                                        'modid' => xarModGetIDFromName('xarbb')));
    // make only one query to speed up
    // Get links
    $query = "SELECT xar_tid,
                     $xbbtopicstable.xar_fid,
                     xar_ttitle,
                     xar_tpost,
                     xar_tposter,
                     xar_ttime,
                     xar_treplies,
                     xar_treplier,
                     xar_tstatus,
                   	 xar_fname,
                     xar_fdesc,
                     xar_ftopics,
                     xar_fposts,
                     xar_fposter,
                     xar_fpostid,
                     {$categoriesdef['cid']}
            FROM $xbbtopicstable LEFT JOIN $xbbforumstable ON $xbbtopicstable.xar_fid = $xbbforumstable.xar_fid
            LEFT JOIN {$categoriesdef['table']} ON {$categoriesdef['field']} = $xbbforumstable.xar_fid
            {$categoriesdef['more']}
            WHERE {$categoriesdef['where']} AND xar_tid = " . xarVarPrepForStore($tid);

   /* // Get links
    $query = "SELECT xar_tid,
                     xar_fid,
                     xar_ttitle,
                     xar_tpost,
                     xar_tposter,
                     xar_ttime,
                     xar_treplies,
                     xar_tstatus
            FROM $xbbtopicstable
            WHERE xar_tid = " . xarVarPrepForStore($tid);   */
    $result =& $dbconn->Execute($query);
    if (!$result) return;

    if($result->EOF) 	{
         $msg = xarML('Topic with ID #(1) does not exist',$tid);
        xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'ID_NOT_EXIST',
            new SystemException(__FILE__ . '(' . __LINE__ . '): ' . $msg));
        return;
    }

    list($tid, $fid, $ttitle, $tpost, $tposter, $ttime, $treplies, $tstatus,$treplier,
    	$fname, $fdesc, $ftopics, $fposts, $fposter, $fpostid,$catid) = $result->fields;
    $result->Close();

    if (!xarSecurityCheck('ReadxarBB',1,'Forum',"$catid:$fid")) return;

    $topic = array('tid'     => $tid,
                   'fid'     => $fid,
                   'ttitle'  => $ttitle,
                   'tpost'   => $tpost,
                   'tposter' => $tposter,
                   'ttime'   => $ttime,
                   'treplies'=> $treplies,
                   'tstatus' => $tstatus,
                   'treplier' => $treplier,
                   'fname'   => $fname,
                   'fdesc'   => $fdesc,
                   'ftopics' => $ftopics,
                   'fposts'  => $fposts,
                   'fposter' => $fposter,
                   'fpostid' => $fpostid,
                   'catid'   => $catid);

    return $topic;
}

?>