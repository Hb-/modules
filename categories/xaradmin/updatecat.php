<?php

/**
 * udpate item from categories_admin_modify
 */
function categories_admin_updatecat()
{
    // Get parameters

    //Checkbox work for submit buttons too
    if (!xarVarFetch('reassign', 'checkbox',  $reassign, false, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('repeat',   'int:1:100', $repeat,   1,     XARVAR_NOT_REQUIRED)) return;

    if ($reassign) {
        xarResponseRedirect(xarModUrl('categories','admin','modifycat',array('repeat' => $repeat)));
        return true;
    }

    if (!xarVarFetch('creating', 'bool', $creating)) return;

    if ($creating) {
        if (!xarVarFetch('cids', 'array', $cids)) return;
    } else {
        if (!xarVarFetch('cids', 'array', $cids)) return;
    }

    if (!xarVarFetch('name', 'list:str:0:255', $name)) return;
    if (!xarVarFetch('description', 'list:str:0:255', $description)) return;
    if (!xarVarFetch('image', 'array', $image)) return;


    if (!xarVarFetch('moving', 'list:bool', $moving)) return;
    if (!xarVarFetch('catexists', 'list:bool', $catexists)) return;
    if (!xarVarFetch('refcid', 'list:int:0', $refcid)) return;
    if (!xarVarFetch('position', 'list:enum:1:2:3:4', $position)) return;

    // Confirm authorisation code
    if (!xarSecConfirmAuthKey()) return;

    // Load API
    if (!xarModAPILoad('categories', 'admin')) return;

    foreach ($cids as $key => $cid) {
        //Empty -> Creating Cats (ALL OF THEM should have empty cids!)
        if (empty($cid)) {
            $cid = $key;
            $creating = true;
        }

        switch (intval($position[$cid])) {
            case 1: // above - same level
            default:
                $rightorleft = 'left';
                $inorout = 'out';
                break;
            case 2: // below - same level
                $rightorleft = 'right';
                $inorout = 'out';
                break;
            case 3: // below - child category
                $rightorleft = 'right';
                $inorout = 'in';
            case 4: // above - child category
                $rightorleft = 'left';
                $inorout = 'in';
                break;
        }

        // Pass to API
        if (!$creating) {
            if (!xarModAPIFunc('categories',
                             'admin',
                             'updatecat',
                             array('cid'         => $cid,
                                   'name'        => $name[$cid],
                                   'description' => $description[$cid],
                                   'image'       => $image[$cid],
                                   'moving'      => $moving[$cid],
                                   'refcid'      => $refcid[$cid],
                                   'inorout'     => $inorout,
                                   'rightorleft' => $rightorleft
                               ))) return;
        } else {
            // Pass to API
            if (!xarModAPIFunc('categories',
                              'admin',
                              'createcat',
                              array(
                                    'name'        => $name[$cid],
                                    'description' => $description[$cid],
                                    'image'       => $image[$cid],
                                    'catexists'   => $catexists[$cid],
                                    'refcid'      => $refcid[$cid],
                                    'inorout'     => $inorout,
                                    'rightorleft' => $rightorleft
                                   ))) return;
        }
    }

    if ($creating) {
        xarResponseRedirect(xarModUrl('categories','admin','modifycat',array()));
    } else {
        xarResponseRedirect(xarModUrl('categories','admin','viewcats',array()));
    }

    return true;
}

?>