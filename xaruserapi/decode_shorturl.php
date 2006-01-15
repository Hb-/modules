<?php
/**
 * Decode short urls
 *
 * @package modules
 * @copyright (C) 2002-2005 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage SiteContact Module
 * @link http://xaraya.com/index.php/release/890.html
 * @author Jo Dalle Nogare <jojodee@xaraya.com>
 */

/**
 * extract function and arguments from short URLs for this module, and pass
 * them back to xarGetRequestInfo()
 *
 * @author Jo Dalle Nogare <jojodee@xaraya.com>
 * @param  $params array containing the different elements of the virtual path
 * @returns array
 * @return array containing func the function to be called and args the query
 *          string arguments, or empty if it failed
 */
function sitecontact_userapi_decode_shorturl($params)
{
    /* Initialise the argument list we will return */
    $args = array();
    $aliasisset = xarModGetVar('sitecontact', 'useModuleAlias');
    $aliasname = xarModGetVar('sitecontact','aliasname');

    if (($aliasisset) && isset($aliasname)) {
        $usealias   = true;
    } else{
        $usealias = false;
    }
    $module = 'sitecontact';
    if ($params[0] != $module) { /* it's possibly some type of alias */
        $aliasname = xarModGetVar('sitecontact','aliasname');
    }
    if ((strtolower($params[0]) == 'sitecontact') || (strtolower($params[0] == $aliasname))) {
        array_shift($params);
    }

    /* If no path components then return. */
   if (empty($params[0])) {
        /* nothing specified -> we'll go to the main function */
        return array('main', $args);
    } elseif (preg_match('/^index/i', $params[0])) {
        /* some search engine/someone tried using index.html (or similar)
         * -> we'll go to the main function
         */
        return array('main', $args);

    } elseif (preg_match('/^contactus/i', $params[0])) {
        /* something that starts with 'list' is probably for the view function
         * Note : make sure your encoding/decoding is consistent ! :-)
         */
        return array('contactus', $args);
    } elseif (preg_match('/^(\d+)/', $params[0], $matches)) {
         $messageid = $matches[0];
        $args['message'] = $messageid;
        return array('main', $args);
    } else {

    }
}
?>