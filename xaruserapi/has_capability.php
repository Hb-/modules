<?php
/**
 * File: $Id$
 * 
 * AuthLDAP User API
 * 
 * @package authentication
 * @copyright (C) 2002 by the Xaraya Development Team.
 * @license GPL <http://www.gnu.org/licenses/gpl.html>
 * @link http://www.xaraya.com
 *
 * @subpackage authldap
 * @author Chris Dudley <miko@xaraya.com> | Richard Cave <rcave@xaraya.com>
*/

/**
 * check whether this module has a certain capability
 * @public
 * @param args['capability'] the capability to check for
 * @author Marco Canini
 * @returns bool
 */
function authldap_userapi_has_capability($args)
{
    extract($args);

    if (!isset($capability)) {
        $msg = xarML('Empty capability.');
        xarErrorSet(XAR_SYSTEM_EXCEPTION, 'BAD_PARAM',
                       new SystemException(__FILE__.'('.__LINE__.'): '.$msg));
        return;
    }

    switch($capability) {
        case XARUSER_AUTH_FAILED:
            return true;
            break;
        case XARUSER_AUTH_DYNAMIC_USER_DATA_HANDLER:
        case XARUSER_AUTH_USER_ENUMERABLE:
        case XARUSER_AUTH_PERMISSIONS_OVERRIDER:
        case XARUSER_AUTH_USER_CREATEABLE:
        case XARUSER_AUTH_USER_DELETEABLE:
            return false;
            break;
    }
    $msg = xarML('Unknown capability.');
    xarErrorSet(XAR_SYSTEM_EXCEPTION, 'BAD_PARAM',
                   new SystemException(__FILE__.'('.__LINE__.'): '.$msg));
    return;
}

?>
