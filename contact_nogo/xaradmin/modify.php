<?php

/**
 * modify an item
 * This is a standard function that is called whenever an administrator
 * wishes to modify a current module item
 * @param 'exid' the id of the item to be modified
 */
function contact_admin_modify($args)
{
    // Get parameters from whatever input we need.  All arguments to this
    // function should be obtained from xarVarCleanFromInput(), getting them
    // from other places such as the environment is not allowed, as that makes
    // assumptions that will not hold in future versions of Xaraya
    list($id,
         $objectid)= xarVarCleanFromInput('id',
                                         'objectid');


    // Admin functions of this type can be called by other modules.  If this
    // happens then the calling module will be able to pass in arguments to
    // this function through the $args parameter.  Hence we extract these
    // arguments *after* we have obtained any form-based input through
    // xarVarCleanFromInput().
    extract($args);

    // At this stage we check to see if we have been passed $objectid, the
    // generic item identifier.  This could have been passed in by a hook or
    // through some other function calling this as part of a larger module, but
    // if it exists it overrides $exid
    //
    // Note that this module couuld just use $objectid everywhere to avoid all
    // of this munging of variables, but then the resultant code is less
    // descriptive, especially where multiple objects are being used.  The
    // decision of which of these ways to go is up to the module developer
    if (!empty($objectid)) {
        $id = $objectid;
    }

    // The user API function is called.  This takes the item ID which we
    // obtained from the input and gets us the information on the appropriate
    // item.  If the item does not exist we post an appropriate message and
    // return
    $edit = xarModAPIFunc('contact',
                         'user',
                         'get',
                         array('id' => $id));
    // Check for exceptions
    if (!isset($item) && xarExceptionMajor() != XAR_NO_EXCEPTION) return; // throw back

    // Security check - important to do this as early as possible to avoid
    // potential security holes or just too much wasted processing.  However,
    // in this case we had to wait until we could obtain the item name to
    // complete the instance information so this is the first chance we get to
    // do the check
    if (!xarSecurityCheck('ContactEdit',0,'Item',"$id:All:All")) return;


    // Get menu variables - it helps if all of the module pages have a standard
    // menu at their head to aid in navigation
    //$menu = xarModAPIFunc('contact','admin','menu','modify');

    $item['module'] = 'contact';
    $hooks = xarModCallHooks('item','modify',$id,$item);
    if (empty($hooks)) {
        $hooks = '';
    } elseif (is_array($hooks)) {
        $hooks = join('',$hooks);
    }

    // Return the template variables defined in this function
    return array('authid' => xarSecGenAuthKey(),
                 'namelabel' => xarVarPrepForDisplay(xarML('contactNAME')),
                 'numberlabel' => xarVarPrepForDisplay(xarML('contactNUMBER')),
                 'updatebutton' => xarVarPrepForDisplay(xarML('contactUPDATE')),
                 'hooks' => $hooks,
                 'item' => $item);
}

?>