<?php

/**
 * the main administration function
 * 
 * @author Jim McDonald 
 * @access public 
 * @param no $ parameters
 * @return true on success or void on falure
 * @throws XAR_SYSTEM_EXCEPTION, 'NO_PERMISSION'
 */
function ratings_admin_main()
{ 
    // Security Check
    if (!xarSecurityCheck('AdminRatings')) return;

    if (xarModGetVar('adminpanels', 'overview') == 0) {
        // Return the output
        return array();
    } else {
        xarResponseRedirect(xarModURL('ratings', 'admin', 'modifyconfig'));
    } 
    // success
    return true;
} 

?>