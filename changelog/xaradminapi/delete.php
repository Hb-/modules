<?php

/**
 * delete changelog entries
 *
 * @param $args['modid'] int module id, or
 * @param $args['modname'] name of the calling module
 * @param $args['itemtype'] optional item type for the item
 * @param $args['itemid'] int item id
 * @returns bool
 * @return true on success, false on failure
 * @raise BAD_PARAM, NO_PERMISSION, DATABASE_ERROR
 */
function changelog_adminapi_delete($args)
{
    extract($args);

    if (!xarSecurityCheck('AdminChangeLog')) return;

    // Database information
    $dbconn =& xarDBGetConn();
    $xartable =& xarDBGetTables();
    $changelogtable = $xartable['changelog'];

    $query = "DELETE FROM $changelogtable ";
    if (!empty($modid)) {
        if (!is_numeric($modid)) {
            $msg = xarML('Invalid #(1) for #(2) function #(3)() in module #(4)',
                         'module id', 'admin', 'delete', 'Hitcount');
            xarExceptionSet(XAR_USER_EXCEPTION, 'BAD_PARAM',
                            new SystemException($msg));
            return false;
        }
        if (empty($itemtype) || !is_numeric($itemtype)) {
            $itemtype = 0;
        }
        $query .= " WHERE xar_moduleid = ? 
                      AND xar_itemtype = ?
        $bindvars = array((int) $modid, (int) $itemtype);

        if (!empty($itemid)) {
            $query .= " AND xar_itemid = ?";
            $bindvars[] = (int) $itemid;
        }
    }

    $result =& $dbconn->Execute($query, $bindvars);
    if (!$result) return;

    return true;
}

?>
