<?php

/**
 * delete all hitcount items for a module - hook for ('module','remove','API')
 *
 * @param $args['objectid'] ID of the object (must be the module name here !!)
 * @param $args['extrainfo'] extra information
 * @returns bool
 * @return true on success, false on failure
 * @raise BAD_PARAM, NO_PERMISSION, DATABASE_ERROR
 */
function hitcount_adminapi_deleteall($args)
{
    extract($args);

    // When called via hooks, we should get the real module name from objectid
    // here, because the current module is probably going to be 'modules' !!!
    if (!isset($objectid) || !is_string($objectid)) {
        $msg = xarML('Invalid #(1) for #(2) function #(3)() in module #(4)',
                    'object ID (= module name)', 'admin', 'deleteall', 'Hitcount');
        xarExceptionSet(XAR_USER_EXCEPTION, 'BAD_PARAM',
                       new SystemException($msg));
        return false;
    }

    $modid = xarModGetIDFromName($objectid);
    if (empty($modid)) {
        $msg = xarML('Invalid #(1) for #(2) function #(3)() in module #(4)',
                    'module ID', 'admin', 'deleteall', 'Hitcount');
        xarExceptionSet(XAR_USER_EXCEPTION, 'BAD_PARAM',
                       new SystemException($msg));
        return false;
    }

// TODO: re-evaluate this for hook calls !!
    // Security check - important to do this as early on as possible to
    // avoid potential security holes or just too much wasted processing
	if(!xarSecurityCheck('DeleteHitcountItem',1,'Item',"All:All:$objectid")) return;

    $dbconn =& xarDBGetConn();
    $xartable =& xarDBGetTables();
    $hitcounttable = $xartable['hitcount'];

    $query = "DELETE FROM $hitcounttable
            WHERE xar_moduleid = '" . xarVarPrepForStore($modid) . "'";
    $result =& $dbconn->Execute($query);
    if (!$result) return;

    // hmmm, I think we'll skip calling more hooks here... :-)
    //xarModCallHooks('item', 'delete', '', '');

    // Return the extra info
    if (!isset($extrainfo)) {
        $extrainfo = array();
    }
    return $extrainfo;
}

?>
