<?php
/**
 * Utility function to pass menu items to the main menu
 * 
 * @package Xaraya eXtensible Management System
 * @copyright (C) 2002-2005 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage Courses Module
 * @link http://xaraya.com/index.php/release/179.html
 * @author Courses module development team 
 */
/**
 * utility function pass individual menu items to the main menu
 * 
 * @author the Courses module development team 
 * @returns array
 * @return array containing the menulinks for the main menu items.
 */
function courses_adminapi_getmenulinks()
{
    if (xarSecurityCheck('EditCourses', 0)) {
        $menulinks[] = Array('url' => xarModURL('courses',
                'admin',
                'viewcourses'),
            'title' => xarML('View all courses that have been added.'),
            'label' => xarML('View Courses'));
    }
    if (xarSecurityCheck('AddCourses', 0)) {
        $menulinks[] = Array('url' => xarModURL('courses',
                'admin',
                'newcourse'),
            'title' => xarML('Adds a new course to system.'),
            'label' => xarML('Add Course'));
    }
    if (xarSecurityCheck('ReadCourses', 0)) {
        $menulinks[] = Array('url' => xarModURL('courses',
                'admin',
                'viewallplanned'),
            'title' => xarML('View all planned courses.'),
            'label' => xarML('Planning'));
    }
    if (xarSecurityCheck('AdminCourses', 0)) {
        $menulinks[] = Array('url' => xarModURL('courses',
                'admin',
                'view'),
            'title' => xarML('Modify the courses parameters'),
            'label' => xarML('Course parameters'));
    }
    if (xarSecurityCheck('AdminCourses', 0)) {
        $menulinks[] = Array('url' => xarModURL('courses',
                'admin',
                'modifyconfig'),
            'title' => xarML('Modify the configuration for the module'),
            'label' => xarML('Modify Config'));
    }
    // If we return nothing, then we need to tell PHP this, in order to avoid an ugly
    // E_ALL error.
    if (empty($menulinks)) {
        $menulinks = '';
    }
    return $menulinks;
}

?>
