<?php

/**
 * add a hit for a specific item, and display the hitcount (= display hook)
 *
 * (use xarVarSetCached('Hooks.hitcount','save', 1) to tell hitcount *not*
 * to display the hit count, but to save it in 'Hooks.hitcount', 'value')
 *
 * @param $args['objectid'] ID of the item this hitcount is for
 * @param $args['extrainfo'] may contain itemtype
 * @returns output
 * @return output with hitcount information
 */
function hitcount_user_display($args)
{

    extract($args);

    // Load API
    if (!xarModAPILoad('hitcount', 'admin')) return;

    // Run API function
    $args['modname'] = xarModGetName();
    $args['itemtype'] = 0;
    if (isset($extrainfo) && is_array($extrainfo)) {
        if (isset($extrainfo['itemtype']) && is_numeric($extrainfo['itemtype'])) {
            $args['itemtype'] = $extrainfo['itemtype'];
        }
    }

    if (xarVarIsCached('Hooks.hitcount','nocount') ||
        (xarSecurityCheck('AdminPanel', 0) && xarModGetVar('hitcount', 'countadmin') == FALSE) ) {
        $hitcount = xarModAPIFunc('hitcount', 'user', 'get', $args);
    } else {
        $hitcount = xarModAPIFunc('hitcount', 'admin', 'update', $args);
    }

    if (isset($hitcount)) {
        // Display current hitcount or set the cached variable
        if (!xarVarIsCached('Hooks.hitcount','save') ||
            xarVarGetCached('Hooks.hitcount','save') == false ) {
            return '(' . $hitcount . ' ' . xarML('Reads') . ')';
        } else {
            xarVarSetCached('Hooks.hitcount','value',$hitcount);
        }
    }

    return '';
}

?>
