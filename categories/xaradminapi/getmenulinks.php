<?php

/**
 * utility function pass individual menu items to the main menu
 *
 * @author the Example module development team
 * @returns array
 * @return array containing the menulinks for the main menu items.
 */
function categories_adminapi_getmenulinks()
{
    $menulinks = array();

// Security Check
    if (xarSecurityCheck('AddCategories',0)) {

        $menulinks[] = Array('url'   => xarModURL('categories',
                                                   'admin',
                                                   'modifycat'),
                              'title' => xarML('Add a new Category into the system'),
                              'label' => xarML('Add Category'));
    }

// Security Check
    if (xarSecurityCheck('EditCategories',0)) {

        $menulinks[] = Array('url'   => xarModURL('categories',
                                                   'admin',
                                                   'viewcats'),
                              'title' => xarML('View and Edit Categories'),
                              'label' => xarML('View Categories'));

        $menulinks[] = Array('url'   => xarModURL('categories',
                                                   'admin',
                                                   'stats'),
                              'title' => xarML('View category statistics per module'),
                              'label' => xarML('View Statistics'));

        $menulinks[] = Array('url'   => xarModURL('categories',
                                                   'admin',
                                                   'modifyconfig'),
                              'title' => xarML('Config the Categories module'),
                              'label' => xarML('Modify Config'));
    }

    return $menulinks;
}


?>
