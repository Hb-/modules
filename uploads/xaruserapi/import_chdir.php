<?php

/**
 *  Change to the specified directory within the local imports sandbox directory
 *
 *  @author  Carl P. Corliss
 *  @access  public
 *  @param   string     dirName  The name of the directory (within the import sandbox) to change to
 *  @returns string           The complete path to the new Current Working Directory within the sandbox
 */

function uploads_userapi_import_chdir( $args ) 
{
    extract ( $args );

    if (!isset($dirName) || empty($dirName)) {
        $dirName = NULL;
    }

    $cwd = xarModGetUserVar('uploads', 'path.imports-cwd');
    $importDir = xarModGetVar('uploads', 'path.imports-directory');

    if (!empty($dirName)) {
        if ($dirName == '...') {
            if (stristr($cwd, $importDir) && strlen($cwd) > strlen($importDir)) {
                $cwd = dirname($cwd);
                xarModSetUserVar('uploads', 'path.imports-cwd', $cwd);
            }
        } else {
            if (file_exists("$cwd/$dirName") && is_dir("$cwd/$dirName")) {
                $cwd = "$cwd/$dirName";
                xarModSetUserVar('uploads', 'path.imports-cwd', $cwd);
            }
        }
    } else {
        // if dirName is empty, then reset the cwd to the top level directory
        $cwd = xarModGetVar('uploads', 'path.imports-directory');
        xarModSetUserVar('uploads', 'path.imports-cwd', $cwd);
    }

    if (!stristr($cwd, $importDir)) {
        $cwd = $importDir;
        xarModSetUserVar('uploads', 'path.imports-cwd', $importDir);
    }
    
    return $cwd;
} 
?>
