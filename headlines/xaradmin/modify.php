<?php
/**
 * modify an item
 * @param 'hid' the id of the headline to be modified
 */
function headlines_admin_modify($args)
{
    // Get parameters from whatever input we need
    if (!xarVarFetch('hid','int:1:',$hid)) return;
    if (!xarVarFetch('obid','str:1:',$obid,$hid,XARVAR_NOT_REQUIRED)) return;

    // Security Check
	if(!xarSecurityCheck('EditHeadlines')) return;
    $data = array();

    $data = xarModAPIFunc('headlines',
                          'user',
                          'get',
                          array('hid' => $hid));

    if ($data == false) return;

    $data['module']         = 'headlines';
    $data['itemtype']       = NULL; // forum
    $data['itemid']         = $hid;
    $hooks = xarModCallHooks('item','modify',$hid,$data);
    if (empty($hooks)) {
        $data['hooks']      = '';
    } elseif (is_array($hooks)) {
        $data['hooks']      = join('',$hooks);
    } else {
        $data['hooks']      = $hooks;
    }

    $data['submitlabel']    = xarML('Submit');
    $data['authid']         = xarSecGenAuthKey();

    return $data;
    
}
?>
