<?php
/**
 * @package Xaraya eXtensible Management System
 * @copyright (C) 2005 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage SIGMAPersonnel Module
 */

/**
 * The main administration function
 *
 * @author MichelV michelv@xarayahosting.nl
 */
function sigmapersonnel_admin_main()
{ 

    if (!xarSecurityCheck('EditSIGMAPersonnel')) return;
    /* The admin system looks for a var to be set to skip the introduction
     * page altogether.
     */
    if (xarModGetVar('adminpanels', 'overview') == 0) {

        $data = xarModAPIFunc('sigmapersonnel', 'admin', 'menu');

        return $data;
    } else {
        xarResponseRedirect(xarModURL('sigmapersonnel', 'admin', 'view'));
    }
    /* success so return true */
    return true;
}
?>