<?php
/**
 * delete an ping
 * @param $args['id'] ID of the item
 * @returns bool
 * @return true on success, false on failure
 */
function ping_adminapi_delete($args)
{
    // Get arguments from argument array
    extract($args);
    // Argument check
    if (!isset($id)) {
        $msg = xarML('Invalid Parameter Count');
        xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'BAD_PARAM', new SystemException($msg));
        return;
    }
    // The user API function is called
    $link = xarModAPIFunc('ping',
                          'user',
                          'get',
                          array('id' => $id));

    if ($link == false) return;
    // Security Check
	if(!xarSecurityCheck('Adminping')) return;
    // Get datbase setup
    $dbconn =& xarDBGetConn();
    $xartable =& xarDBGetTables();
    $pingtable = $xartable['ping'];
    // Delete the item
    $query = "DELETE FROM $pingtable
              WHERE xar_id = " . xarVarPrepForStore($id);
    $result =& $dbconn->Execute($query);
    if (!$result) return;
    // Let any hooks know that we have deleted a link
    xarModCallHooks('item', 'delete', $id, '');
    // Let the calling process know that we have finished successfully
    return true;
}
?>