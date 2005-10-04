<?php

/**
 *  Rename a file. (alias for file_move)
 *
 *  @author  Carl P. Corliss
 *  @access  public
 *  @param   <type>
 *  @returns <type>
 */

function filemanager_userapi_import_get_filelist( $args )
{


    extract($args);

    // Whether or not to descend into any directory
    // that is found while creating the list of files
    if (!isset($descend)) {
        $descend = FALSE;
    }

    // Whether or not to only add files that are -not- already
    // stored with entries in the database
    if (!isset($onlyNew)) {
        $onlyNew = FALSE;
    }

    if ((isset($search) && isset($exclude)) && $search == $exclude) {
        return array();
    }

    if (!isset($search)) {
        $search = '.*';
    }

    if (!isset($exclude)) {
        $exclude = NULL;
    }

    // if search and exclude are the same, we would get no results
    // so return no results.
    $fileList = array();

    if (!isset($fileLocation)) {
        $msg = xarML('Missing parameter [#(1)] for function [#(2)] in module [#(3)].',
                     'fileLocation', 'import_get_filelist', 'filemanager');
        xarErrorSet(XAR_SYSTEM_EXCEPTION, 'BAD_PARAM', new SystemException($msg));
        return;
    }

    if (!file_exists($fileLocation)) {
        $msg = xarML("Unable to acquire list of files to import - Location '#(1)' does not exist!",$fileLocation);
        xarErrorSet(XAR_USER_EXCEPTION, 'FILE_NO_EXIST', new DefaultUserException($msg));
        return;
    }

    if (is_file($fileLocation)) {
        $type = _INODE_TYPE_FILE;
    } elseif (is_dir($fileLocation)) {
        $type = _INODE_TYPE_DIRECTORY;
    } elseif (is_link($fileLocation)) {
        $linkLocation = readlink($fileLocation);

        while (is_link($linkLocation)) {
            $linkLocation = readlink($linkLocation);
        }

        $fileLocation = $linkLocation;

        if (is_dir($linkLocation)) {
            $type = _INODE_TYPE_FILE;
        } elseif (is_file($linkLocation)) {
            $type = _INODE_TYPE_DIRECTORY;
        } else {
            $type = -1;
        }

    } else {
        $type = -1;
    }

    switch ($type) {
        case _INODE_TYPE_FILE:
            if ($onlyNew) {

                $file = xarModAPIfunc('filemanager', 'user', 'db_get_file',
                                       array('fileLocation' => $fileLocation));
                if (count($file)) {
                    break;
                }
            }
            $fileName = $fileLocation;
            // if we are searching for specific files, then check and break if search doesn't match
            if ((isset($search) && preg_match("/$search/", $fileName)) &&
                (!isset($exclude) || !preg_match("/$exclude/", $fileName))) {
                    $fileList["$type:$fileName"] =
                        xarModAPIFunc('filemanager', 'fs', 'get_metadata',
                                       array('fileLocation' => $fileLocation));
            }
            break;
        case _INODE_TYPE_DIRECTORY:
            if ($fp = opendir($fileLocation)) {

                while (FALSE !== ($inode = readdir($fp))) {
                    if (is_dir($fileLocation . '/'. $inode) && !eregi('^([.]{1,2})$', $inode)) {
                        $type = _INODE_TYPE_DIRECTORY;
                    } elseif (is_file($fileLocation. '/' . $inode)) {
                        $type = _INODE_TYPE_FILE;
                    } elseif (is_link($fileLocation. '/' . $inode)) {
                        $linkLocation = readlink($fileLocation . '/' . $inode);

                        while (is_link($linkLocation)) {
                            $linkLocation = readlink($linkLocation);
                        }

                        if (is_dir($linkLocation) && !eregi('([.]{1,2}$', $linkLocation)) {
                            $type = _INODE_TYPE_DIRECTORY;
                        } elseif (is_file($linkLocation)) {
                            $type = _INODE_TYPE_FILE;
                        } else {
                            $type = -1;
                        }
                    } else {
                        $type = -1;
                    }


                    switch ($type) {
                        case _INODE_TYPE_FILE:
                            $fileName = $fileLocation . '/' . $inode;

                            if ($onlyNew) {
                                $file = xarModAPIfunc('filemanager', 'user', 'db_get_file',
                                                    array('fileLocation' => $fileName));
                                if (count($file)) {
                                    continue;
                                }
                            }
                            $file = xarModAPIFunc('filemanager', 'fs', 'get_metadata',
                                                array('fileLocation' => $fileName));

                            if ((!isset($search) || preg_match("/$search/", $fileName)) &&
                                (!isset($exclude) || !preg_match("/$exclude/", $fileName))) {
                                    $file = xarModAPIFunc('filemanager', 'fs', 'get_metadata',
                                                        array('fileLocation' => $fileName));
                                    $fileList["$file[inodeType]:$fileName"] = $file;
                            }
                            break;
                        case _INODE_TYPE_DIRECTORY:
                            $dirName = "$fileLocation/$inode";
                            if ($descend) {
                                $files = xarModAPIFunc('filemanager', 'user', 'import_get_filelist',
                                                        array('fileLocation' => $dirName,
                                                          'descend' => TRUE,
                                                          'exclude' => $exclude,
                                                          'search' => $search));
                                $fileList += $files;
                            } else {

                                if ((!isset($search) || preg_match("/$search/", $dirName)) &&
                                    (!isset($exclude) || !preg_match("/$exclude/", $dirName))) {
                                        $files = xarModAPIFunc('filemanager', 'fs', 'get_metadata',
                                                            array('fileLocation' => $dirName));
                                        $fileList["$files[inodeType]:$inode"] = $files;
                                }
                            }
                            break;
                        default:
                            break;
                    }

                    if (is_dir($fileLocation. '/' . $inode) && !eregi('^([.]{1,2})$', $inode)) {
                    }
                    if (is_file($fileLocation. '/' . $inode)) {
                    }
                }
            }
            closedir($fp);
            break;
        default:
            break;
    }

    if (is_array($fileList)) {
        ksort($fileList);
    }

    return $fileList;
}

?>
