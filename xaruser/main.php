<?php
/* * Xaraya Smilies
 *
 * @package Xaraya eXtensible Management System
 * @copyright (C) 2005 by the Xaraya Development Team.
 * @license GPL <http://www.gnu.org/licenses/gpl.html>
 * @link http://www.xaraya.org
 *
 * @subpackage Smilies Module
 * @author Jim McDonald, Mikespub, John Cox
*/

/**
 * Add a standard screen upon entry to the module.
 * @returns output
 * @return output with smilies Menu information
 */
function smilies_user_main()
{
    // Security Check
    if(!xarSecurityCheck('OverviewSmilies')) return;
    // Get parameters from whatever input we need
    if(!xarVarFetch('startnum', 'isset',    $startnum, 1,     XARVAR_NOT_REQUIRED)) {return;}

    // check to see if the print theme was called for documentation
    $theme = xarVarGetCached('Themes.name','CurrentTheme');
    if ($theme == 'print'){
        $print = true;
    }

    $data['items'] = array();
    // Specify some labels for display
    if (isset($print)){
        $data['pager'] = xarTplGetPager($startnum,
                                        xarModAPIFunc('smilies', 'user', 'countitems'),
                                        xarModURL('smilies', 'user', 'main', array('startnum' => '%%', 'theme' => 'print')),
                                        xarModGetVar('smilies', 'itemsperpage'));
    } else {
        $data['pager'] = xarTplGetPager($startnum,
                                        xarModAPIFunc('smilies', 'user', 'countitems'),
                                        xarModURL('smilies', 'user', 'main', array('startnum' => '%%')),
                                        xarModGetVar('smilies', 'itemsperpage'));
    }

    // The user API function is called
    $links = xarModAPIFunc('smilies',
                           'user',
                           'getall',
                           array('startnum' => $startnum,
                                 'numitems' => xarModGetVar('smilies',
                                                            'itemsperpage')));

    if (empty($links)) {
        $msg = xarML('There are no smilies registered');
        xarErrorSet(XAR_USER_EXCEPTION, 'MISSING_DATA', new DefaultUserException($msg));
        return;
    }
    // Add the array of items to the template variables
    $data['items'] = $links;
    // Return the template variables defined in this function
    return $data;
}
?>