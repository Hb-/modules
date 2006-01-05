<?php
/**
 * The main administration function
 *
 * @package modules
 * @copyright (C) 2002-2005 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage Example Module
 * @link http://xaraya.com/index.php/release/36.html
 * @author Example Module Development Team
 */

/**
 * The main administration function
 *
 * This function is the default function, and is called whenever the
 * module is initiated without defining arguments. As such it can
 * be used for a number of things, but most commonly it either just
 * shows the module menu and returns or calls whatever the module
 * designer feels should be the default function (often this is the
 * view() function)
 *
 * @author Example Module Development Team
 */
function example_admin_main()
{
    /* Security check - important to do this as early as possible to avoid
     * potential security holes or just too much wasted processing. For the
     * main function we want to check that the user has at least edit privilege
     * for some item within this component, or else they won't be able to do
     * anything and so we refuse access altogether. The lowest level of access
     * for administration depends on the particular module, but it is generally
     * either 'edit' or 'delete'
     */
    if (!xarSecurityCheck('EditExample')) return;
    /* The admin system looks for a var to be set to skip the introduction
     * page altogether. This allows you to add sparse documentation about the
     * module, and allow the site admins to turn it on and off as they see fit.
     */
    if (xarModGetVar('adminpanels', 'overview') == 0) {
        /* If you want to go directly to some default function, instead of
         * having a separate main function, you can simply call it here, and
         * use the same template for admin-main.xd as for admin-view.xd
         * return xarModFunc('example','admin','view');
         */

        /* Initialise the $data variable that will hold the data to be used in
         * the blocklayout template, and get the common menu configuration - it
         * helps if all of the module pages have a standard menu at the top to
         * support easy navigation
         */
        $data = xarModAPIFunc('example', 'admin', 'menu');

        /* You could specify some other variables to use in the blocklayout template
         *$data['welcome'] = xarML('Welcome to the administration part of this Example module...');
         * Return the template variables defined in this function
         */
        return $data;
        /* Note : instead of using the $data variable, you could also specify
         * the different template variables directly in your return statement :
         */

        /* return array('menutitle' => ...,
         * 'welcome' => ...,
         * ... => ...);
         */
    } else {
        /* If the Overview documentation is turned off, then we just return the view page,
         * or whatever function seems to be the most fitting.
         */
        xarResponseRedirect(xarModURL('example', 'admin', 'view'));
    }
    /* success so return true */
    return true;
}
?>