<?php

/**
 * rate an item
 * @param $args['modname'] module name of the item to rate
 * @param $args['itemtype'] item type (optional)
 * @param $args['objectid'] ID of the item to rate
 * @param $args['rating'] actual rating
 * @returns int
 * @return the new rating for this item
 */
function ratings_userapi_rate($args)
{
    // Get arguments from argument array
    extract($args);

    // Argument check
    if ((!isset($modname)) ||
        (!isset($objectid)) ||
        (!isset($rating) || !is_numeric($rating) || $rating < 0 || $rating > 100)) {
        $msg = xarML('Invalid #(1) for #(2) function #(3)() in module #(4)',
                    xarML('value'), 'user', 'rate', 'ratings');
        xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'BAD_PARAM',
                       new SystemException($msg));
        return;
    }
    $modid = xarModGetIDFromName($modname);
    if (empty($modid)) {
        $msg = xarML('Invalid #(1) for #(2) function #(3)() in module #(4)',
                    xarML('module id'), 'user', 'rate', 'ratings');
        xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'BAD_PARAM',
                       new SystemException($msg));
        return;
    }

    if (!isset($itemtype)) {
        $itemtype = 0;
    }

    // Security Check
	if(!xarSecurityCheck('CommentRatings',1,'Item',"$modname:$itemtype:$objectid")) return;


    // Database information
    list($dbconn) = xarDBGetConn();
    $xartable = xarDBGetTables();
    $ratingstable = $xartable['ratings'];

    // Multipe rate check
    if (!empty($itemtype)) {
        $seclevel = xarModGetVar('ratings', "seclevel.$modname.$itemtype");
        if (!isset($seclevel)) {
            $seclevel = xarModGetVar('ratings', 'seclevel.'.$modname);
        }
    } else {
        $seclevel = xarModGetVar('ratings', 'seclevel.'.$modname);
    }
    if (!isset($seclevel)) {
        $seclevel = xarModGetVar('ratings', 'seclevel');
    }
    if ($seclevel == 'high') {
        if (xarUserIsLoggedIn()) {
            $rated = xarModGetUserVar('ratings',$modname.':'.$itemtype.':'.$objectid);
            if (!empty($rated)) {
                return;
            }
        } else {
            return;
        }
    } elseif ($seclevel == 'medium') {
        // Check to see if user has already voted
        if (xarUserIsLoggedIn()) {
            $rated = xarModGetUserVar('ratings',$modname.':'.$itemtype.':'.$objectid);
            if (!empty($rated) && $rated > time() - 24*60*60) {
                return;
            }
        } else {
            $rated = xarSessionGetVar('ratings:'.$modname.':'.$itemtype.':'.$objectid);
            if (!empty($rated) && $rated > time() - 24*60*60) {
                return;
            }
        }
    } // No check for low

    // Get current information on rating
    $query = "SELECT xar_rid,
                   xar_rating,
                   xar_numratings
            FROM $ratingstable
            WHERE xar_moduleid = '" . xarVarPrepForStore($modid) . "'
              AND xar_itemid = '" . xarVarPrepForStore($objectid) . "'
              AND xar_itemtype = '" . xarVarPrepForStore($itemtype) . "'";
    $result =& $dbconn->Execute($query);
    if (!$result) return;

    if (!$result->EOF) {
        // Update current rating
        list($rid, $currating, $numratings) = $result->fields;
        $result->close();

        // Calculate new rating
        $newnumratings = $numratings + 1;
        $newrating = (int)((($currating*$numratings) + $rating)/$newnumratings);

        // Insert new rating
        $query = "UPDATE $ratingstable
                SET xar_rating = " . xarVarPrepForStore($newrating) . ",
                    xar_numratings = $newnumratings
                WHERE xar_rid = $rid";
        $result =& $dbconn->Execute($query);
        if (!$result) return;

    } else {
        $result->close();

        // Get a new ratings ID
        $rid = $dbconn->GenId($ratingstable);
        // Create new rating
        $query = "INSERT INTO $ratingstable(xar_rid,
                                          xar_moduleid,
                                          xar_itemid,
                                          xar_itemtype,
                                          xar_rating,
                                          xar_numratings)
                VALUES ($rid,
                        '" . xarVarPrepForStore($modid) . "',
                        '" . xarVarPrepForStore($objectid) . "',
                        '" . xarVarPrepForStore($itemtype) . "',
                        " . xarVarPrepForStore($rating) . ",
                        1)";

        $result =& $dbconn->Execute($query);
        if (!$result) return;

        $newrating = $rating;
    }

    // Set note that user has rated this item if required
    if ($seclevel == 'high') {
        if (xarUserIsLoggedIn()) {
            xarModSetUserVar('ratings',$modname.':'.$itemtype.':'.$objectid,time());
        } else {
            // nope
        }
    } elseif ($seclevel == 'medium') {
        if (xarUserIsLoggedIn()) {
            xarModSetUserVar('ratings',$modname.':'.$itemtype.':'.$objectid,time());
        } else {
            xarSessionSetVar('ratings:'.$modname.':'.$itemtype.':'.$objectid,time());
        }
    }
    // CHECKME: find some cleaner way to update the page cache if necessary
    if (function_exists('xarPageFlushCached') &&
        xarModGetVar('xarcachemanager','FlushOnNewRating')) {
        $modinfo = xarModGetInfo($modid);
        // this may not be agressive enough flushing for all sites
        // we could flush "$modinfo[name]-" to remove all output cache associated with a module
        xarPageFlushCached("$modinfo[name]-user-display-");
    }    
    return $newrating;
}

?>
