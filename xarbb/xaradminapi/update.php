<?php

/**
 * update a forum
 * @param $args['fid'] the ID of the link
 * @param $args['fname'] the new keyword of the link
 * @param $args['fdesc'] the new title of the link
 */
function xarbb_adminapi_update($args)
{
    // Get arguments from argument array
    extract($args);

    // Argument check
    if ((!isset($fid)) ||
        (!isset($fname)) ||
        (!isset($fdesc))) {
        $msg = xarML('Invalid Parameter Count',
                    join(', ',$invalid), 'admin', 'update', 'xarbb');
        xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'BAD_PARAM',
                       new SystemException($msg));
        return;
    }

    // The user API function is called
    $link = xarModAPIFunc('xarbb',
                          'user',
                          'getforum',
                          array('fid' => $fid));

    if ($link == false) {
        $msg = xarML('No Such Forum Present',
                    'xarbb');
        xarExceptionSet(XAR_USER_EXCEPTION,
                    'MISSING_DATA',
                     new DefaultUserException($msg));
        return;
    }

    // Security Check
    if(!xarSecurityCheck('EditxarBB',1,'Forum',"$fid:All")) return;

    // Get datbase setup
    list($dbconn) = xarDBGetConn();
    $xartable = xarDBGetTables();

    $xbbforumstable = $xartable['xbbforums'];

    // Update the forum
    $query = "UPDATE $xbbforumstable
            SET xar_fname = '" . xarVarPrepForStore($fname) . "',
                xar_fdesc = '" . xarVarPrepForStore($fdesc) . "'
            WHERE xar_fid = " . xarVarPrepForStore($fid);
    $result =& $dbconn->Execute($query);
    if (!$result) return;

    // Let any hooks know that we have updated a forum
    $args['module'] = 'xarbb';
    $args['itemtype'] = 1; // forum
    xarModCallHooks('item', 'update', $fid, $args);

    // Let the calling process know that we have finished successfully
    return true;
}

?>