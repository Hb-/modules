<?php

/**
 * search for scheduler API functions in modules/<module>/xarschedulerapi directories
 */
function scheduler_admin_search()
{ 
    if (!xarSecurityCheck('AdminScheduler')) return;

    $data = array();
    $data['found'] = array();

    $modules = realpath('modules');
    $dh = opendir($modules);
    if (empty($dh)) return $data;
    while (($dir = readdir($dh)) !== false) {
        if (is_dir($modules . '/' . $dir) && is_dir($modules . '/' . $dir . '/xarschedulerapi')) {
            $dh2 = opendir($modules . '/' . $dir . '/xarschedulerapi');
            if (empty($dh2)) continue;
            while (($file = readdir($dh2)) !== false) {
                if (preg_match('/^(\w+)\.php$/',$file,$matches)) {
                    $data['found'][] = array('module' => $dir, // not really, but let's not be difficult
                                             'type' => 'scheduler',
                                             'func' => $matches[1]);
                }
            }
            closedir($dh2);
        }
    }
    closedir($dh);
    return $data;
}

?>
