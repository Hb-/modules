<?php

/**
 * delete replies
 * @param $args['tid'] Topic id
 * @returns bool
 * @return true on success, false on failure
 */
function xarbb_adminapi_deleteallreplies($args)
{

    // BIG FIXME HERE
    // Get arguments from argument array
    extract($args);

    // Argument check
    if (!isset($tid))  {
        $msg = xarML('Invalid Parameter Count',
                    '', 'admin', 'delete', 'xarbb');
        xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'BAD_PARAM',
                       new SystemException($msg));
        return;
    }

    // get forum id
    if(!$topic = xarModAPIFunc('xarbb','user','gettopic',array('tid' => $tid))) return;    

    if(!xarSecurityCheck('ModxarBB',1,'Forum',$topic['catid'].':'.$topic['fid'])) return;

    $comments = xarModAPIFunc("comments","user","get_multiple",
    	array("modid" => xarModGetIdFromName('xarbb'),"objectid" => $tid));

    if(!isset($comments) || !is_array($comments))
    	return;

    if(count($comments) > 0)	{
    	if(!xarModAPIFunc("comments","admin","delete_object_nodes",
        	array("modid" => xarModGetIdFromName('xarbb'),"objectid" => $tid))) return;
	}

    // Hooks are called from comments module, aren't they?

    // Let the calling process know that we have finished successfully
    return true;
}

?>
