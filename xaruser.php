<?php
/**
 * Short description of purpose of file
 *
 * @package Xaraya eXtensible Management System
 * @copyright (C) 2002 by the Xaraya Development Team.
 * @link http://www.xaraya.com
 *
 * @subpackage Commerce Module
 * @author Marc Lutolf
*/

/**
 * the main user function
 * This function is the default function, and is called whenever the module is
 * initiated without defining arguments.  Function decides if user is logged in
 * and returns user to correct location.
 *
*/
function commerce_user_main()
{
   // Security Check
//    if(!xarSecurityCheck('ViewCommerce')) return;

    xarSessionSetVar('commerce_statusmsg', xarML('Commerce Main Menu',
                    'commerce'));

    if (xarModGetVar('adminpanels', 'overview') == 0 && !isset($branch)) {
        return array();
    } else {

        switch(strtolower($branch)) {
            case 'start':
                xarResponseRedirect(xarModURL('commerce', 'user', 'start'));
                break;
        }
   }
}
?>