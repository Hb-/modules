<?php

/**
 * This is a standard function that is called with the results of the
 * form supplied by events_admin_new() to create a new item
 * @param 'name' the name of the item to be created
 * @param 'number' the number of the item to be created
 */
function events_admin_create($args)
{
    // Get parameters from whatever input we need.  All arguments to this
    // function should be obtained from xarVarFetch(), getting them
    // from other places such as the environment is not allowed, as that makes
    // assumptions that will not hold in future versions of Xaraya
    if (!xarVarFetch('number', 'str:1:', $number, '',XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('name', 'str:1:', $name, '', XARVAR_NOT_REQUIRED)) return;
        
    // Admin functions of this type can be called by other modules.  If this
    // happens then the calling module will be able to pass in arguments to
    // this function through the $args parameter.  Hence we extract these
    // arguments *after* we have obtained any form-based input through
    // xarVarFetch().
    extract($args);

    // Argument check - make sure that all required arguments are present
    // and in the right format, if not then return to the add form with the
    // values that are there and a message with a session var.  If you perform
    // this check now, you could do away with the check in the API along with
    // the exception that comes with it.

    if ((!isset($number) || !is_numeric($number)) ||
        (!isset($name) || !is_string($name))){
        //Let us first clear any session vars that were once set.
        xarSessionDelVar('statusmsg');
        // Lets Explain why the form was sent back
        xarSessionSetVar('statusmsg', xarML('Error!'));
        xarResponseRedirect(xarModURL('events', 'admin', 'new', array('name' => $name, 'number' =>$number)));
    }

    // Confirm authorisation code.  This checks that the form had a valid
    // authorisation code attached to it.  If it did not then the function will
    // proceed no further as it is possible that this is an attempt at sending
    // in false data to the system
    if (!xarSecConfirmAuthKey()) return;

    // Notable by its absence there is no security check here.  This is because
    // the security check is carried out within the API function and as such we
    // do not duplicate the work here

    // The API function is called.  Note that the name of the API function and
    // the name of this function are identical, this helps a lot when
    // programming more complex modules.  The arguments to the function are
    // passed in as their own arguments array
    $exid = xarModAPIFunc('events',
                        'admin',
                        'create',
                        array('name' => $name,
                              'number' => $number));

    // The return value of the function is checked here, and if the function
    // suceeded then an appropriate message is posted.  Note that if the
    // function did not succeed then the API function should have already
    // posted a failure message so no action is required
    if (!isset($exid) && xarCurrentErrorType() != XAR_NO_EXCEPTION) return; // throw back

    // Success
    xarSessionSetVar('statusmsg', xarML('Event was successfully created'));

    // This function generated no output, and so now it is complete we redirect
    // the user to an appropriate page for them to carry on their work
    xarResponseRedirect(xarModURL('events', 'admin', 'view'));

    // Return
    return true;
}

?>