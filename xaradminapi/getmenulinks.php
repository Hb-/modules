<?php 

/**
 * Locator Utilities
 *
 * @copyright   by Michael Cortez
 * @license     GPL (http://www.gnu.org/licenses/gpl.html)
 * @author      Michael Cortez
 * @link        
 *
 * @package     Xaraya eXtensible Management System
 * @subpackage  HookBridge Utility
 * @version     $Id$
 *
 */

/**
 * Pass individual menu items to the main menu
 *
 * @returns array
 * @return  array containing the menulinks for the main menu items
 */

function hookbridge_adminapi_getmenulinks ( $args ) 
{
    if (xarSecurityCheck('Adminhookbridge')) 
	{

        $menulinks[] = array(
            'url'       => xarModURL(
                'hookbridge'
                ,'admin'
                ,'main' )
            ,'title'    => 'Show informations'
            ,'label'    => 'Overview' );

        $menulinks[] = array(
            'url'       => xarModURL( 'hookbridge', 'admin', 'view')
            ,'title'    => 'Show the main page'
            ,'label'    => 'Main Page' );

        
        // The main menu will look for this array and return it for a tree
        // view of the module. We are just looking for three items in the
        // array, the url, which we need to use the xarModURL function, the
        // title of the link, which will display a tool tip for the module
        // url, in order to keep the label short, and finally the exact label
        // for the function that we are displaying.
        

        $menulinks[] = array(
								'url'       => xarModURL(
									'hookbridge'
									,'admin'
									,'config' )
								,'title'    => 'Modify the configuration'
								,'label'    => 'Modify Config' 
							);
    }


    if (empty($menulinks)){
        $menulinks = '';
    }

    // The final thing that we need to do in this function is return the values back
    // to the main menu for display.
    return $menulinks;

}

/*
 * END OF FILE
 */
?>
