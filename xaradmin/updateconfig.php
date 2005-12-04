<?php
/**
 * Update the config of this module
 *
 * @package modules
 * @copyright (C) 2002-2005 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage Userpoints Module
 * @link http://xaraya.com/index.php/release/782.html
 * @author Userpoints Module Development Team
 */
/**
 * Update configuration
 */
function userpoints_admin_updateconfig()
{
    // Get parameters
    if(!xarVarFetch('createscore',      'isset', $createscore,    10, XARVAR_NOT_REQUIRED)) {return;}
    if(!xarVarFetch('deletescore',      'isset', $deletescore,    10, XARVAR_NOT_REQUIRED)) {return;}
    if(!xarVarFetch('displayscore',     'isset', $displayscore,    10, XARVAR_NOT_REQUIRED)) {return;}
    if(!xarVarFetch('updatescore',      'isset', $updatescore,    10, XARVAR_NOT_REQUIRED)) {return;}
    if(!xarVarFetch('frontpagescore',   'isset', $frontpagescore,    10, XARVAR_NOT_REQUIRED)) {return;}
    if(!xarVarFetch('ranksperpage',     'int:1:', $ranksperpage, 10, XARVAR_NOT_REQUIRED)) return;
    if(!xarVarFetch('showadminscore',   'checkbox', $showadminscore, false, XARVAR_NOT_REQUIRED)) return;
    if(!xarVarFetch('showanonscore',    'checkbox', $showanonscore, false, XARVAR_NOT_REQUIRED)) return;
    if(!xarVarFetch('shorturls',        'checkbox', $shorturls, false, XARVAR_NOT_REQUIRED)) return;

    // Confirm authorisation code
    if (!xarSecConfirmAuthKey()) return;
    // Security Check
    if (!xarSecurityCheck('AdminUserpoints')) return;

    xarModSetVar('userpoints', 'ranksperpage', $ranksperpage);
    xarModSetVar('userpoints', 'showadminscore', $showadminscore);
    xarModSetVar('userpoints', 'showanonscore', $showanonscore);
    xarModSetVar('userpoints', 'SupportShortURLs', $shorturls);

    // Update default style
    if (!is_array($createscore)) {
        xarModSetVar('userpoints', 'defaultcreate', $createscore);
    } else {
        foreach ($createscore as $modname => $value) {
            if ($modname == 'default') {
                xarModSetVar('userpoints', 'defaultcreate', $value);
            } else {
                xarModSetVar('userpoints', 'createpoints.' . $modname, $value);

            }
        }
    }

    if (!is_array($deletescore)) {
        xarModSetVar('userpoints', 'defaultdelete', $deletescore);
    } else {
        foreach ($deletescore as $modname => $value) {
            if ($modname == 'default') {
                xarModSetVar('userpoints', 'defaultdelete', $value);
            } else {
                xarModSetVar('userpoints', 'deletepoints.' . $modname, $value);

            }
        }
    }
    if (!is_array($displayscore)) {
        xarModSetVar('userpoints', 'defaultdisplay', $displayscore);
    } else {
        foreach ($displayscore as $modname => $value) {
            if ($modname == 'default') {
                xarModSetVar('userpoints', 'defaultdisplay', $value);
            } else {
                xarModSetVar('userpoints', 'displaypoints.' . $modname, $value);

            }
        }
    }
    if (!is_array($updatescore)) {
        xarModSetVar('userpoints', 'defaultupdate', $updatescore);
    } else {
        foreach ($updatescore as $modname => $value) {
            if ($modname == 'default') {
                xarModSetVar('userpoints', 'defaultupdate', $value);
            } else {
                xarModSetVar('userpoints', 'updatepoints.' . $modname, $value);

            }
        }
    }
    if (!is_array($frontpagescore)) {
        xarModSetVar('userpoints', 'defaultfrontpage', $frontpagescore);
    } else {
        foreach ($frontpagescore as $modname => $value) {
            if ($modname == 'default') {
                xarModSetVar('userpoints', 'defaultfrontpage', $value);
            } else {
                xarModSetVar('userpoints', 'frontpagepoints.' . $modname, $value);

            }
        }
    }
    xarResponseRedirect(xarModURL('userpoints', 'admin', 'modifyconfig'));

    return true;
}

?>
