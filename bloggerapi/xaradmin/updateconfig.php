<?php

/**
 * File: $Id$
 *
 * Update the administrative configuration
 *
 * @package modules
 * @copyright (C) 2003 by the Xaraya Development Team.
 * @link http://www.xaraya.com
 * 
 * @subpackage bloggerapi
 * @author Marcel van der Boom <marcel@xaraya.com>
*/

/**
 * modify bloggerapi configuration
 */
function bloggerapi_admin_updateconfig()
{
    if (!xarVarFetch('bloggerpubtype','int:1:',$bloggerPubType,'0')) return;
    if (!xarSecConfirmAuthKey()) return;
    if(!xarSecurityCheck('AdminBloggerAPI')) return;

    xarModSetVar('bloggerapi','bloggerpubtype',$bloggerPubType);

    // lets update status and display updated configuration
    xarResponseRedirect(xarModURL('bloggerapi', 'admin', 'modifyconfig'));

}
?>