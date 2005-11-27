<?php
/**
 * View a list of items
 *
 * @package Xaraya eXtensible Management System
 * @copyright (C) 2002-2005 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage Sigmapersonnel Module
 * @author Michel V.
 */
/**
 * View a list of personnel
 * This is a standard function to provide an overview of all of the items
 * available from the module. The display of links depends on the privilege of the viewer
 * @param int $catid
 * @author MichelV
 * @return array
 * @todo everything
 */
function sigmapersonnel_user_view()
{
    if (!xarVarFetch('startnum', 'str:1:', $startnum, '1', XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('catid',    'int:1:', $catid,    '',  XARVAR_NOT_REQUIRED)) return;
    $data = xarModAPIFunc('sigmapersonnel', 'user', 'menu');
    // Prepare the variable that will hold some status message if necessary
    $data['status'] = '';
    // Prepare the array variable that will hold all items for display
    $data['items'] = array();
    // Specify some other variables for use in the function template

    $data['pager'] = '';
    // Security check - important to do this as early as possible to avoid
    // potential security holes or just too much wasted processing
    if (!xarSecurityCheck('ViewSIGMAPersonnel')) return;
    // Lets get the UID of the current user to check for overridden defaults
    $uid = xarUserGetVar('uid');
    // The API function is called.  The arguments to the function are passed in
    // as their own arguments array.
    // Security check 1 - the getall() function only returns items for which the
    // the user has at least OVERVIEW access.
    $items = xarModAPIFunc('sigmapersonnel',
                            'user',
                            'getall',
                            array('startnum' => $startnum,
                                  'numitems' => xarModGetUserVar('sigmapersonnel','itemsperpage',$uid),
                                  'catid'    => $catid));
    if (!isset($items) && xarCurrentErrorType() != XAR_NO_EXCEPTION) return; // throw back

    // TODO: check for conflicts between transformation hook output and xarVarPrepForDisplay
    // Loop through each item and display it.
    foreach ($items as $item) {
        // Let any transformation hooks know that we want to transform some text
        // You'll need to specify the item id, and an array containing all the
        // pieces of text that you want to transform (e.g. for autolinks, wiki,
        // smilies, bbcode, ...).
        // Note : for your module, you might not want to call transformation
        // hooks in this overview list, but only in the display of the details
        // in the display() function.
        // list($item['name']) = xarModCallHooks('item',
        // 'transform',
        // $item['exid'],
        // array($item['name']));
        // Security check 2 - if the user has read access to the item, show a
        // link to display the details of the item
        if (xarSecurityCheck('ReadSIGMAPersonnel', 0, 'PersonnelItem', "$item[personid]:All:$item[persstatus]")) {
            $item['link'] = xarModURL('sigmapersonnel',
                'user',
                'display',
                array('personid' => $item['personid']));
            // Security check 2 - else only display the item name (or whatever is
            // appropriate for your module)
        } else {
            $item['link'] = '';
        }
        // Clean up the item text before display
        $item['firstname'] = xarVarPrepForDisplay($item['firstname']);
        // Add this item to the list of items to be displayed
        $data['items'][] = $item;
    }
    // Call the xarTPL helper function to produce a pager in case of there
    // being many items to display.

    // Note that this function includes another user API function.  The
    // function returns a simple count of the total number of items in the item
    // table so that the pager function can do its job properly
    $data['pager'] = xarTplGetPager($startnum,
        xarModAPIFunc('sigmapersonnel', 'user', 'countitems'),
        xarModURL('sigmapersonnel', 'user', 'view', array('startnum' => '%%', 'catid' => $catid)),
        xarModGetUserVar('sigmapersonnel', 'itemsperpage', $uid));

    xarTplSetPageTitle(xarVarPrepForDisplay(xarML('View Personnel')));
    // Return the template variables defined in this function
    return $data;
}
?>
