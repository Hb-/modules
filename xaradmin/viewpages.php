<?php

/**
 * File: $Id$
 *
 * Admin view of all pages, in hierarchical format.
 *
 * @package Xaraya
 * @copyright (C) 2004-2009 by Jason Judge
 * @license GPL <http://www.gnu.org/licenses/gpl.html>
 * @link http://www.academe.co.uk/
 * @author Jason Judge
 * @subpackage xarpages
 * @todo Support a pager of sorts, or allow display to be limited to specific sub-trees.
 */

function xarpages_admin_viewpages($args)
{
    extract($args);

    // Security check
    if (!xarSecurityCheck('ModerateXarpagesPage', 1, 'Page', 'All')) {
        // No privilege for viewing pages.
        return false;
    }

    // Accept a parameter to allow selection of a single tree.
    xarVarFetch('contains', 'id', $contains, 0, XARVAR_NOT_REQUIRED);

    $data = xarModAPIFunc(
        'xarpages', 'user', 'getpagestree',
        array('key' => 'index', 'dd_flag' => false, 'tree_contains_pid' => $contains)
    );

    if (empty($data['pages'])) {
        // TODO: pass to template.
        return $data; //xarML('NO PAGES DEFINED');
    } else {
        $data['pages'] = xarModAPIfunc('xarpages', 'tree', 'array_maptree', $data['pages']);
    }

    $data['contains'] = $contains;

    // Check modify and delete privileges on each page.
    // ModeratePage - allows overview
    // EditPage - allows basic changes, but no moving or renaming (good for sub-editors who manage content)
    // AddPage - new pages can be added (further checks may limit it to certain page types)
    // DeletePage - page can be renamed, moved and deleted
    if (!empty($data['pages'])) {
        foreach($data['pages'] as $key => $page) {
            if (xarSecurityCheck('ModerateXarpagesPage', 0, 'Page', $page['name'] . ':' . $page['pagetype']['name'])) {
                $data['pages'][$key]['moderate_allowed'] = true;
            }
            if (xarSecurityCheck('EditXarpagesPage', 0, 'Page', $page['name'] . ':' . $page['pagetype']['name'])) {
                $data['pages'][$key]['edit_allowed'] = true;
            }
            if (xarSecurityCheck('DeleteXarpagesPage', 0, 'Page', $page['name'] . ':' . $page['pagetype']['name'])) {
                $data['pages'][$key]['delete_allowed'] = true;
            }
        }
    }

    $pages = array_reverse($data['pages'], true);

    $pagestree = array();

    $parentid = 'x';
    $siblings = array();

    // convert the flat tree structure into a nested one for display
    foreach ($pages as $tmppage) {
        if ($tmppage['parent_pid'] !== $parentid) {
            if (count($siblings) > 0) {
                krsort($siblings);
                $tmppage['children'] = array_reverse($siblings);
            }
            $siblings = array();
            $parentid = $tmppage['parent_pid'];
            $siblings[$tmppage['left']] = $tmppage;
        } else {
            $siblings[$tmppage['left']] = $tmppage;
        }
        if ($tmppage['parent_pid'] == 0) {
            $pagestree[$tmppage['left']] = $tmppage;
            $siblings = array();
        }
    }
    ksort($pagestree);

    $data['pages'] = $pagestree;

    // Check if the user is allowed to add pages.
    if (xarSecurityCheck('AddXarpagesPage', 0, 'Page', 'All')) {
        $data['add_allowed'] = true;
    }

    return $data;
}

?>
