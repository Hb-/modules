<?php
/**
 * File: $Id:
 * 
 * TinyMCE  main function
 * 
 * @package Xaraya eXtensible Management System
 * @copyright (C) 2003 by the Xaraya Development Team.
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage TinyMCE 
 * @author Jo Dalle Nogare <jojodee@xaraya.com>
 */
/**
 * The main administration function
 */
function tinymce_admin_main()
{
    if (!xarSecurityCheck('AdminTinyMCE')) return;
    if (xarModGetVar('adminpanels', 'overview') == 0) {
        $data = xarModAPIFunc('tinymce', 'admin', 'menu');
        // Specify some other variables used in the blocklayout template
        $data['welcome'] = xarML('Welcome to the administration part of this TinyMCE  module...');
        $data['ddflushurl']=xarModURL('dynamicdata','admin','modifyconfig');        
        // Return the template variables defined in this function
        return $data;
    } else {
        xarResponseRedirect(xarModURL('tinymce', 'admin', 'modifyconfig'));
    } 
    // success
    return true;
} 

?>
