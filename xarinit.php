<?php
/**
 *
 * Wizards initialization functions
 *
 * @package Xaraya eXtensible Management System
 * @copyright (C) 2003 by the Xaraya Development Team.
 * @license GPL <http://www.gnu.org/licenses/gpl.html>
 * @link http://www.xaraya.com
 * @subpackage installer
 * @author Marc Lutolf
 */


/**
 * Initialise the Wizards module
 *
 * @return bool
 */
function wizards_init()
{

// Register masks
    xarRegisterMask('ViewWizards','All','wizards','All','All:All:All','ACCESS_OVERVIEW');
    xarRegisterMask('ReadWizard','All','wizards','All','All:All:All','ACCESS_READ');
    xarRegisterMask('RunWizard','All','wizards','All','All:All:All','ACCESS_COMMENT');
    xarRegisterMask('EditWizard','All','wizards','All','All:All:All','ACCESS_EDIT');
    xarRegisterMask('AddWizard','All','wizards','All','All:All:All','ACCESS_ADD');
    xarRegisterMask('DeleteWizard','All','wizards','All','All:All:All','ACCESS_DELETE');
    xarRegisterMask('AdminWizard','All','wizards','All','All:All:All','ACCESS_ADMIN');

    xarRegisterPrivilege('RunScript','All','wizards','Item','All','ACCESS_COMMENT','Run wizard scripts');
    xarMakePrivilegeRoot('RunScript');

// Register modvars
    xarModSetVar('wizards','status',3);

    // Initialisation successful
    return true;
}

/**
 * Upgrade the Wizards module from an old version
 *
 * @param oldVersion
 * @returns bool
 */
function wizards_upgrade($oldVersion)
{
    switch($oldVersion) {
    case '0.1':
        // compatibility upgrade, nothing to be done
        break;
    }
    return true;
}

/**
 * Delete the Wizards module
 *
 * @param none
 * @returns bool
 */
function wizards_delete()
{
    xarRemoveMasks('wizards');
    xarRemovePrivileges('wizards');
    xarModDelVar('wizards','status');
  return true;
}

?>
