<?php

/**
 * create item from xarModFunc('categories','admin','viewcat')
 */
function categories_admin_viewcatbases()
{
    // Get parameters
    // TODO: add pager
    if (!xarVarFetch('modid', 'id', $modid,  NULL, XARVAR_NOT_REQUIRED)) {return;}
    if (!xarVarFetch('itemtype', 'int', $itemtype,  NULL, XARVAR_NOT_REQUIRED)) {return;}

    // Security check
    if (!xarSecurityCheck('ReadCategories')) {return;}

    // These two variables define the scope of this screen.
    $data = array(
        'modid' => $modid,
        'itemtype' => $itemtype
    );

    // TODO: add pager
    $data['catbases'] = xarModAPIFunc(
        'categories', 'user', 'getallcatbases',
        array(
            'modid' => $modid,
            'itemtype' => $itemtype,
            'format' => 'flat'
        )
    );

    // Get itemtype names for all modules selected (where available).
    $itemtypes = array();
    if (!empty($data['catbases'])) {
        foreach ($data['catbases'] as $itemtypekey => $catbase) {
            if (empty($itemtypes[$catbase['modid']])) {
                $itemtypes[$catbase['modid']] = xarModAPIFunc(
                    $catbase['module'], 'user', 'getitemtypes',
                    array(), 0
                );
            }

            if (!empty($itemtypes[$catbase['modid']][$catbase['itemtype']])) {
                $data['catbases'][$itemtypekey]['itemtypename'] =  $itemtypes[$catbase['modid']][$catbase['itemtype']]['label'];
            }
        }
    }

    return $data;
}

?>
