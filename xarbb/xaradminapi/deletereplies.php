<?php

/**
 * delete replies
 * @param $args['cids'] Array( IDs ) of the forum   or $args['cid'] ID
 * @returns bool
 * @return true on success, false on failure
 */
function xarbb_adminapi_deletereplies($args)
{

    // BIG FIXME HERE
    // Get arguments from argument array
    extract($args);

    // Argument check
    if ( (!isset($cids) || !is_array($cids) || count($cids) <= 0) &&
		 (!isset($cid) || !($cid > 0) ) ) {
        $msg = xarML('Invalid Parameter Count',
                    join(', ',$invalid), 'admin', 'delete', 'xarbb');
        xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'BAD_PARAM',
                       new SystemException($msg));
        return;
    }

    if(!isset($cids))
    	$cids = Array($cid);

    // Security Check
    foreach($cids as $cid)	{
        // for update topics view
        if(!$comment = xarModAPIFunc('comments','user','get_one',array('cid' => $cid))) return;
        $tid = $comment[0]['xar_objectid'];

        if(!$topic = xarModAPIFunc('xarbb','user','gettopic',array('tid' => $tid))) return;
		$fid = $topic['fid'];

    	if(!xarSecurityCheck('ModxarBB',1,'Forum',"$fid:All")/* ||
        	!xarSecurityCheck('ModxarBB',1,'Topic',"$tid")*/) continue;  // TODO

        if(!xarModAPIFunc("comments","admin","delete_branch",array("node" => $cid)))
        	return;

        // update topics view, must do this here, because cids can contain different tids
	    if(!xarModAPIFunc('xarbb','user','updatetopicsview',array("tid" => $tid))) return;
    }


 // Hooks should be called from comments module

    // Let the calling process know that we have finished successfully
    return true;
}

?>