<?php
/**
 * Add a standard screen upon entry to the module.
 * 
 * @returns output
 * @return output with censor Menu information
 */
function censor_admin_main()
{ 
    // Security Check
    if (!xarSecurityCheck('EditCensor')) return; 
    // we only really need to show the default view (overview in this case)
    if (xarModGetVar('adminpanels', 'overview') == 0) {
        return array();
    } else {
        xarResponseRedirect(xarModURL('censor', 'admin', 'view'));
    } 
    // success
    return true;
} 


?>