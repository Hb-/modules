<?php

/**
 * This is a standard function to modify the configuration parameters of the
 * module
 */
function subitems_admin_modifyconfig()
{
    $data = xarModAPIFunc('subitems', 'admin', 'menu');

    // Security check - important to do this as early as possible to avoid
    // potential security holes or just too much wasted processing
    if (!xarSecurityCheck('AdminSubitems')) return;

    // Generate a one-time authorisation code for this operation
    $data['authid'] = xarSecGenAuthKey();
    // Specify some labels and values for display
    $data['updatebutton'] = xarVarPrepForDisplay(xarML('Update Configuration'));
    $data['shorturlslabel'] = xarML('Enable short URLs?');
    $data['shorturlschecked'] = xarModGetVar('subitems', 'SupportShortURLs') ? true : false;
    //

    $hooks = xarModCallHooks('module', 'modifyconfig', 'subitems',
        array('module' => 'subitems','itemtype' => 1));
    
    if (empty($hooks)) {
        $data['hooks'] = '';
    } elseif (is_array($hooks)) {
        $data['hooks'] = join('', $hooks);
    } else {
        $data['hooks'] = $hooks;
    }
    // Return the template variables defined in this function
    return $data;
}

?>
