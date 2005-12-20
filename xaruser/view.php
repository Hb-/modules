<?php
/**
 * View a list of items
 *
 * @package modules
 * @copyright (C) 2002-2005 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage ITSP Module
 * @link http://xaraya.com/index.php/release/572.html
 * @author ITSP Module Development Team
 */
/**
 * View a list of items: plans
 *
 * This is a standard function to provide an overview of all of the items
 * available from the module.
 *
 * @author the ITSP module development team
 */
function itsp_user_view()
{
    if (!xarVarFetch('startnum', 'str:1:', $startnum, '1', XARVAR_NOT_REQUIRED)) return;
    /* Initialise the $data variable that will hold the data to be used in
     * the blocklayout template, and get the common menu configuration - it
     * helps if all of the module pages have a standard menu at the top to
     * support easy navigation
     */
    $data = xarModAPIFunc('itsp', 'user', 'menu');
    /* Prepare the variable that will hold some status message if necessary */
    $data['status'] = '';
    /* Prepare the array variable that will hold all items for display */
    $data['items'] = array();
    /* Specify some other variables for use in the function template */
    $data['pager'] = '';
    /* Security check - important to do this as early as possible to avoid
     * potential security holes or just too much wasted processing
     */
    if (!xarSecurityCheck('ViewITSP')) return;
    /* Lets get the UID of the current user to check for overridden defaults */
    $uid = xarUserGetVar('uid');
    /* The API function is called.  The arguments to the function are passed in
     * as their own arguments array.
     * Security check 1 - the getall() function only returns items for which the
     * the user has at least OVERVIEW access.
     */
    $items = xarModAPIFunc('itsp',
        'user',
        'getall_plans',
        array('startnum' => $startnum,
              'numitems' => xarModGetUserVar('itsp','itemsperpage',$uid)));
    if (!isset($items) && xarCurrentErrorType() != XAR_NO_EXCEPTION) return; // throw back

    /* TODO: check for conflicts between transformation hook output and xarVarPrepForDisplay
     * Loop through each item and display it.
     */
    foreach ($items as $item) {
        // Add read link
        if (xarSecurityCheck('ReadITSPPlan', 0, 'Plan', "$planid:All:All")) {
            $item['link'] = xarModURL('itsp',
                'user',
                'display',
                array('planid' => $item['planid']));
            /* Security check 2 - else only display the item name (or whatever is
             * appropriate for your module)
             */
        } else {
            $item['link'] = '';
        }
        /* Clean up the item text before display */
        $item['planname'] = xarVarPrepForDisplay($item['planname']);
        /* Add this item to the list of items to be displayed */
        $data['items'][] = $item;
    }
    /* TODO: how to integrate cat ids in pager (automatically) when needed ???
     * Get the UID so we can see if there are any overridden defaults.
     */
    $uid = xarUserGetVar('uid');
    /* Call the xarTPL helper function to produce a pager in case of there
     * being many items to display.
     *
     * Note that this function includes another user API function.  The
     * function returns a simple count of the total number of items in the item
     * table so that the pager function can do its job properly
     */
    $data['pager'] = xarTplGetPager($startnum,
        xarModAPIFunc('itsp', 'user', 'countitems', array('itemtype'=>1)),
        xarModURL('itsp', 'user', 'view', array('startnum' => '%%')),
        xarModGetUserVar('itsp', 'itemsperpage', $uid));

    /* Same as above.  We are changing the name of the page to raise
     * better search engine compatibility.
     */
    xarTplSetPageTitle(xarVarPrepForDisplay(xarML('View our Educational plans')));
    /* Return the template variables defined in this function */
    return $data;

}
?>