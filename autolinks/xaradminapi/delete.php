<?php

/**
 * delete an autolink
 * @param $args['lid'] ID of the link
 * @returns bool
 * @return true on success, false on failure
 */
function autolinks_adminapi_delete($args)
{
    // Get arguments from argument array
    extract($args);

    // Argument check
    if (!isset($lid)) {
        $msg = xarML('Invalid Parameter Count',
                    join(', ',$invalid), 'admin', 'delete', 'Autolinks');
        xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'BAD_PARAM',
                       new SystemException($msg));
        return;
    }

    // The user API function is called
    $link = xarModAPIFunc('autolinks',
                         'user',
                         'get',
                         array('lid' => $lid));

    if ($link == false) {
        $msg = xarML('No Such Link Present',
                    'autolinks');
        xarExceptionSet(XAR_USER_EXCEPTION,
                    'MISSING_DATA',
                     new DefaultUserException($msg));
        return;
    }

    // Security check
    if(!xarSecurityCheck('DeleteAutolinks')) return;

    // Get datbase setup
    list($dbconn) = xarDBGetConn();
    $xartable = xarDBGetTables();

    $autolinkstable = $xartable['autolinks'];

    // Delete the item
    $query = "DELETE FROM $autolinkstable
            WHERE xar_lid = " . xarVarPrepForStore($lid);
    $result =& $dbconn->Execute($query);
    if (!$result) return;

    // Let any hooks know that we have deleted a link
    xarModCallHooks('item', 'delete', $lid, '');

    // Let the calling process know that we have finished successfully
    return true;
}

?>