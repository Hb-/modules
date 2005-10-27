<?php
/**
 * Xaraya BBCode
 *
 * @package modules
 * @copyright (C) 2002-2005 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage BBCode Module
 * @link http://xaraya.com/index.php/release/778.html
 * @author John Cox
*/
/**
 * modify configuration
 */
function bbcode_admin_modifyconfig()
{
    // Security Check
    if (!xarSecurityCheck('EditBBCode')) return;
    if (!xarVarFetch('phase', 'str:1:100', $phase, 'modify', XARVAR_NOT_REQUIRED)) return;

    switch (strtolower($phase)) {
        case 'modify':
        default: 
            //Set Data Array
            $data                   = array();
            $data['authid']         = xarSecGenAuthKey();
            $data['submitlabel']    = xarML('Submit');

            // Call Modify Config Hooks
            $hooks = xarModCallHooks('module', 
                                     'modifyconfig', 
                                     'bbcode',
                                     array('module'     => 'bbcode',
                                           'itemtype'   => 0));

            if (empty($hooks)) {
                $hooks = array();
            }
            $data['hooks'] = $hooks;
            break;

        case 'update':
            if (!xarVarFetch('dolinebreak', 'int', $dolinebreak, 0)) return;
            if (!xarVarFetch('transformtype', 'int', $transformtype, 1)) return;
            if (!xarVarFetch('advancedbbcode', 'checkbox', $advancedbbcode, false, XARVAR_NOT_REQUIRED)) return;
            // Confirm authorisation code
            if (!xarSecConfirmAuthKey()) return; 
            // Update module variables
            xarModSetVar('bbcode', 'transformtype', $transformtype);
            xarModSetVar('bbcode', 'dolinebreak', $dolinebreak);
            xarModSetVar('bbcode', 'useadvanced', $advancedbbcode);

            // Call Update Config Hooks
            xarModCallHooks('module', 
                            'updateconfig', 
                            'bbcode',
                            array('module'      => 'bbcode', 
                                  'itemtype'    => 0));

            xarResponseRedirect(xarModURL('bbcode', 'admin', 'modifyconfig')); 
            // Return
            return true;
            break;
    } 
    return $data;
} 
?>