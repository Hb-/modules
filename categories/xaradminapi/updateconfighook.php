<?php

/**
 * update configuration for a module - hook for ('module','updateconfig','API')
 * Needs $extrainfo['cids'] from arguments, or 'cids' from input
 *
 * @param $args['objectid'] ID of the object
 * @param $args['extrainfo'] extra information
 * @returns bool
 * @return true on success, false on failure
 * @raise BAD_PARAM, NO_PERMISSION, DATABASE_ERROR
 */
function categories_adminapi_updateconfighook($args)
{
    extract($args);

    if (!isset($extrainfo)) {
        $extrainfo = array();
    }

    // When called via hooks, the module name may be empty, so we get it from
    // the current module
    if (empty($extrainfo['module'])) {
        $modname = xarModGetName();
    } else {
        $modname = $extrainfo['module'];
    }

    $modid = xarModGetIDFromName($modname);
    if (empty($modid)) {
        $msg = xarML('Invalid #(1) for #(2) function #(3)() in module #(4)','module name', 'admin', 'updateconfighook', 'categories');
        xarExceptionSet(XAR_USER_EXCEPTION, 'BAD_PARAM', new SystemException($msg));
        return false;
    }

    // see what we have to do here (might be empty => we need to delete)
    if (empty($extrainfo['number_of_categories'])) {
        // try to get number of categories from input
        $numcats = (int) xarVarCleanFromInput('number_of_categories');
    } else {
        $numcats = $extrainfo['number_of_categories'];
    }
    if (empty($numcats) || !is_numeric($numcats)) {
        $numcats = 0;
    }
    if (!empty($extrainfo['itemtype'])) {
        xarModSetVar($modname,'number_of_categories.'.$extrainfo['itemtype'],$numcats);
    } else {
        xarModSetVar($modname,'number_of_categories',$numcats);
    }

    if (empty($extrainfo['cids']) || !is_array($extrainfo['cids'])) {
        // try to get cids from input
        $cids = xarVarCleanFromInput('cids');
        if (empty($cids) || !is_array($cids)) {
            $cids = array();
        }
    } else {
        $cids = $extrainfo['cids'];
    }
    // get all valid master cids for this module
    // Note : a module might have the same master cid twice (just in case...)
    $mastercids = array();
    foreach ($cids as $cid) {
        if (empty($cid) || !is_numeric($cid)) {
            continue;
        }
        $mastercids[] = $cid;
    }
    if (count($mastercids) > $numcats) {
        $mastercids = array_slice($mastercids,0,$numcats);
    }

    if ($numcats == 0 || count($mastercids) == 0) {
        if (!empty($extrainfo['itemtype'])) {
            xarModSetVar($modname,'mastercids.'.$extrainfo['itemtype'],'');
        } else {
            xarModSetVar($modname,'mastercids','');
        }
    } else {
        if (!empty($extrainfo['itemtype'])) {
            xarModSetVar($modname,'mastercids.'.$extrainfo['itemtype'],
                        join(';',$mastercids));
        } else {
            xarModSetVar($modname,'mastercids',join(';',$mastercids));
        }
    }

    // Return the extra info
    return $extrainfo;
}

?>