<?php
/*
 *
 * Keywords Module
 *
 * @package Xaraya eXtensible Management System
 * @copyright (C) 2003 by the Xaraya Development Team
 * @license GPL <http://www.gnu.org/licenses/gpl.html>
 * @link http://www.xaraya.com
 * @author mikespub
*/

/**
 * display keywords entry
 *
 * @param $args['itemid'] item id of the keywords entry
 * @returns bool
 * @return true on success, false on failure
 * @raise BAD_PARAM, NO_PERMISSION, DATABASE_ERROR
 */
function keywords_user_display($args)
{
    xarVarFetch('itemid','isset',$itemid,'', XARVAR_DONT_SET);
    extract($args);

    if (empty($itemid)) {
        return array();
    }
    $items = xarModAPIFunc('keywords','user','getitems',
                          array('id' => $itemid));
    if (!isset($items)) return;
    if (!isset($items[$itemid])) return array();

    $item = $items[$itemid];
    if (count($item) == 0 || empty($item['moduleid'])) return array();

    $modinfo = xarModGetInfo($item['moduleid']);
    if (!isset($modinfo) || empty($modinfo['name'])) return array();

    if (!empty($item['itemtype'])) {
        // Get the list of all item types for this module (if any)
        $mytypes = xarModAPIFunc($modinfo['name'],'user','getitemtypes',
                                 // don't throw an exception if this function doesn't exist
                                 array(), 0);
        if (isset($mytypes) && isset($mytypes[$item['itemtype']])) {
            $item['modname'] = $mytypes[$item['itemtype']]['label'];
        } else {
            $item['modname'] = ucwords($modinfo['name']);
        }
    } else {
        $item['modname'] = ucwords($modinfo['name']);
    }

    $itemlinks = xarModAPIFunc($modinfo['name'],'user','getitemlinks',
                               array('itemtype' => $item['itemtype'],
                                     'itemids' => array($item['itemid'])),
                               0);

    if (isset($itemlinks[$item['itemid']]) && !empty($itemlinks[$item['itemid']]['url'])) {
        // normally we should have url, title and label here
        foreach ($itemlinks[$item['itemid']] as $field => $value) {
            $item[$field] = $value;
        }
    } else {
        $item['url'] = xarModURL($modinfo['name'],'user','display',
                                 array('itemtype' => $item['itemtype'],
                                       'itemid' => $item['itemid']));
    }
    return $item;
}

?>
