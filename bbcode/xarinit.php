<?php
/**
 * File: $Id$
 * 
 * Xaraya BBCode
 * 
 * @package Xaraya eXtensible Management System
 * @copyright (C) 2002 by the Xaraya Development Team.
 * @license GPL <http://www.gnu.org/licenses/gpl.html>
 * @link http://www.xaraya.org
 *
 * @subpackage BBCode
 * @author larseneo, Hinrich Donner
*/

function bbcode_init() {

    // Set up module variables
    //

    // Set up module hooks
    if (!xarModRegisterHook('item',
                           'transform',
                           'API',
                           'bbcode',
                           'user',
                           'transform')) {
        $msg = xarML('Could not register hook');
        xarExceptionSet(XAR_USER_EXCEPTION, 'MISSING_DATA', new DefaultUserException($msg));
        return;
        
    }

    if (!xarModRegisterHook('item',
                           'formheader',
                           'GUI',
                           'bbcode',
                           'user',
                           'formheader')) {
        $msg = xarML('Could not register hook');
        xarExceptionSet(XAR_USER_EXCEPTION, 'MISSING_DATA', new DefaultUserException($msg));
        return;
        
    }

    if (!xarModRegisterHook('item',
                           'formaction',
                           'GUI',
                           'bbcode',
                           'user',
                           'formaction')) {
        $msg = xarML('Could not register hook');
        xarExceptionSet(XAR_USER_EXCEPTION, 'MISSING_DATA', new DefaultUserException($msg));
        return;
        
    }

    if (!xarModRegisterHook('item',
                           'formdisplay',
                           'GUI',
                           'bbcode',
                           'user',
                           'formdisplay')) {
        $msg = xarML('Could not register hook');
        xarExceptionSet(XAR_USER_EXCEPTION, 'MISSING_DATA', new DefaultUserException($msg));
        return;
        
    }

    if (!xarModRegisterHook('item',
                           'formarea',
                           'GUI',
                           'bbcode',
                           'user',
                           'formarea')) {
        $msg = xarML('Could not register hook');
        xarExceptionSet(XAR_USER_EXCEPTION, 'MISSING_DATA', new DefaultUserException($msg));
        return;
        
    }

    if (!xarModRegisterHook('item',
                           'formfooter',
                           'GUI',
                           'bbcode',
                           'user',
                           'formfooter')) {
        $msg = xarML('Could not register hook');
        xarExceptionSet(XAR_USER_EXCEPTION, 'MISSING_DATA', new DefaultUserException($msg));
        return;
        
    }

    // Initialisation successful
    return true;
}

function bbcode_upgrade($oldversion) {

    return true;
}

function bbcode_delete() {

    // Remove module hooks
    if (!xarModUnregisterHook('item',
                             'transform',
                             'API',
                             'bbcode',
                             'user',
                             'transform')) {
        $msg = xarML('Could not un-register hook');
        xarExceptionSet(XAR_USER_EXCEPTION, 'MISSING_DATA', new DefaultUserException($msg));
        return;
    }

    // Remove module hooks
    if (!xarModUnregisterHook('item',
                           'formaction',
                           'GUI',
                           'bbcode',
                           'user',
                           'formaction')) {
        $msg = xarML('Could not un-register hook');
        xarExceptionSet(XAR_USER_EXCEPTION, 'MISSING_DATA', new DefaultUserException($msg));
        return;
    }

    // Remove module hooks
    if (!xarModUnregisterHook('item',
                           'formdisplay',
                           'GUI',
                           'bbcode',
                           'user',
                           'formdisplay')) {
        $msg = xarML('Could not un-register hook');
        xarExceptionSet(XAR_USER_EXCEPTION, 'MISSING_DATA', new DefaultUserException($msg));
        return;
    }

    // Remove module hooks
    if (!xarModUnregisterHook('item',
                           'formarea',
                           'GUI',
                           'bbcode',
                           'user',
                           'formarea')) {
        $msg = xarML('Could not un-register hook');
        xarExceptionSet(XAR_USER_EXCEPTION, 'MISSING_DATA', new DefaultUserException($msg));
        return;
    }

    // Remove module hooks
    if (!xarModUnregisterHook('item',
                              'formfooter',
                              'GUI',
                              'bbcode',
                              'user',
                              'formfooter')) {
        $msg = xarML('Could not un-register hook');
        xarExceptionSet(XAR_USER_EXCEPTION, 'MISSING_DATA', new DefaultUserException($msg));
        return;
    }


    // Deletion successful
    return true;
}

?>