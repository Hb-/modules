<?php

/**
 * File: $Id$
 *
 * Admin view of all pages, in hierarchical format.
 *
 * @package Xaraya
 * @copyright (C) 2004 by Jason Judge
 * @license GPL <http://www.gnu.org/licenses/gpl.html>
 * @link http://www.academe.co.uk/
 * @author Jason Judge
 * @subpackage xarpages
 * @todo Support a pager of sorts, or allow display to be limited to specific sub-trees.
 */

function xarpages_admin_viewpages()
{
    // Security check
    if (!xarSecurityCheck('ModerateXarpagesPage', 1, 'Page', 'All')) {
        // No privilege for viewing pages.
        return false;
    }

    $data = xarModAPIFunc(
        'xarpages', 'user', 'getpagestree',
        array('key' => 'index', 'dd_flag' => false)
    );

    if (empty($data['pages'])) {
        // TODO: pass to template.
        return xarML('NO PAGES DEFINED');
    } else {
        $data['pages'] = xarModAPIfunc('xarpages', 'tree', 'array_maptree', $data['pages']);
    }

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

    // Check if the user is allowed to add pages.
    if (xarSecurityCheck('AddXarpagesPage', 0, 'Page', 'All')) {
        $data['add_allowed'] = true;
    }

    return $data;
}

?>