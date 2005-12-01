<?php
/**
   The main helpdesk administration function
   This function is the default function, and is called whenever the
   module is initiated without defining arguments.

   @return template data
 */
function helpdesk_admin_main()
{
    if (!xarSecurityCheck('adminhelpdesk')) { return; }

    if (xarModGetVar('adminpanels', 'overview') == 0){
        xarResponseRedirect(xarModURL('helpdesk', 'admin', 'overview'));
    } else {
        // View the ticket object by default
        xarResponseRedirect(xarModURL('helpdesk', 'admin', 'view', array('itemtype' => 1)));
    }
    // success
    return true;

}
?>
