<?php

/**
 * return the path for a short URL to xarModURL for this module
 * 
 * @author the Example module development team 
 * @param  $args the function and arguments passed to xarModURL
 * @returns string
 * @return path to be added to index.php for a short URL, or empty if failed
 */
function xlink_userapi_encode_shorturl($args)
{ 
    // Get arguments from argument array
    extract($args); 
    // Check if we have something to work with
    if (!isset($func)) {
        return;
    } 
    // Note : make sure you don't pass the following variables as arguments in
    // your module too - adapt here if necessary
    // default path is empty -> no short URL
    $path = ''; 
    // if we want to add some common arguments as URL parameters below
    $join = '?'; 
    // we can't rely on xarModGetName() here -> you must specify the modname !
    $module = 'xlink'; 
    // specify some short URLs relevant to your module
    if ($func == 'main') {
        if (!empty($base)) {
            $alias = xarModGetAlias($base);
            if ($alias == $module) {
                $module = $base;
                $base = '';
            }
        }
        $path = '/' . $module . '/'; 
        if (!empty($base)) {
            $path .= $base . '/';
        }
        if (!empty($id)) {
            $path .= $id;
        }

    } else {
        // anything else that you haven't defined a short URL equivalent for
        // -> don't create a path here
    } 
    return $path;
} 

?>
