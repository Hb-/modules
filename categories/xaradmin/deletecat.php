<?php

function categories_admin_deletecat()
{
    if (!xarVarFetch('cid','int:1:',$cid)) return;
    if (!xarVarFetch('confirm','str:1:',$confirm,'',XARVAR_NOT_REQUIRED)) return;

    // Security check
    if(!xarSecurityCheck('DeleteCategories',1,'category',"All:$cid")) return;

    // Check for confirmation
    if (empty($confirm)) {

        // Get category information
        $cat = xarModAPIFunc('categories',
                             'user',
                             'getcatinfo',
                              array('cid' => $cid));

        if ($cat == false) {
            $msg = xarML('The category to be deleted doesnt exist', 'categories');
            xarExceptionSet(XAR_USER_EXCEPTION, 'MISSING_DATA', new DefaultUserException($msg));
            return;
        }


        $data = Array('cid'=>$cid,'name'=>$cat['name']);
        $data['nolabel'] = xarML('No');
        $data['yeslabel'] = xarML('Yes');
        $data['authkey'] = xarSecGenAuthKey();

        // Return output
        return xarTplModule('categories','admin','delete',$data);
    }


    // Confirm Auth Key
    if (!xarSecConfirmAuthKey()) return;

    // Pass to API
    if (!xarModAPIFunc('categories',
                       'admin',
                       'deletecat',
                       array('cid' => $cid))) return;

    xarResponseRedirect(xarModURL('categories','admin','viewcats', array()));

    return true;
}

?>