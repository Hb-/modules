<?php

/** 
 *  Move a file from one location to another. Can (or will eventually be able to) grab a file from
 *  a remote site via ftp/http/etc and save it locally as well. Note: isUpload=TRUE implies isLocal=True
 *
 *  @author  Carl P. Corliss
 *  @access  public
 *  @param   string  fileSrc    Complete path to source file
 *  @param   string  fileDest   Complete path to destination 
 *  @param   boolean isUpload   Whether or not this file was uploaded (uses special checks on uploaded files)
 *  @param   boolean isLocal    Whether or not the file is a Local file or not (think: grabbing a web page)
 * 
 *  @returns boolean TRUE on success, FALSE otherwise
 */

function uploads_userapi_file_move( $args ) 
{ 

    extract ($args);
    
    if (!isset($force)) {
        $force = TRUE;
    }
    
    // if it wasn't specified, assume TRUE
    if (!isset($isUpload)) {
        $isUpload = FALSE;
    }
    
    if (!isset($fileSrc)) {
        $msg = xarML('Missing parameter [#(1)] for function [#(2)] in module [#(3)]',
                     'fileSrc','file_move','uploads');
        xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'BAD_PARAM', new SystemException($msg));
        return FALSE;
    }        

    if (!isset($fileDest)) {
        $msg = xarML('Missing parameter [#(1)] for function [(#(2)] in module [#(3)]',
                     'fileDest','file_move','uploads');
        xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'BAD_PARAM', new SystemException($msg));
        return FALSE;
    }
    
    if (!is_readable($fileSrc)) {
        $msg = xarML('Unable to move file - Source file [#(1)]is unreadable!', $fileSrc);
        xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'FILE_NO_READ', new SystemException($msg));
        return FALSE;
    }        
        
    if (!file_exists($fileSrc)) {
        $msg = xarML('Unable to move file - Source file [#(1)]does not exist!', $fileSrc);
        xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'FILE_NOT_EXIST', new SystemException($msg));
        return FALSE;
    }        
        
    if (!file_exists(dirname($fileDest)))  {
        $msg = xarML('Unable to move file - Destination directory does not exist!');
        xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'FILE_NOT_EXIST', new SystemException($msg));
        return FALSE;
    }        
        
    if (!is_writable(dirname($fileDest))) {
        $msg = xarML('Unable to move file - Destination directory is not writable!');
        xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'FILE_NO_WRITE', new SystemException($msg));
        return FALSE;
    }        
        
    if (disk_free_space(dirname($fileDest)) <= filesize($fileSrc)) {
        $msg = xarML('Unable to move file - Destination drive does not have enough free space!');
        xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'FILE_NO_SPACE', new SystemException($msg));
        return FALSE;
    }        
    
    if (file_exists($fileDest) && $force != TRUE) {
        $msg = xarML('Unable to move file - Destination file already exists!');
        xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'FILE_NO_OVERWRITE', new SystemException($msg));
        return FALSE;
    }
    
    if ($isUpload) {
        if (!move_uploaded_file($fileSrc, $fileDest)) {
            $msg = xarML('Unable to move file [#(1)] to destination [#(2)].',$fileSrc, $fileDest);
            xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'FILE_NO_MOVE', new SystemException($msg));
            return FALSE;
        }
    } else {
        if (!copy($fileSrc, $fileDest)) {
            $msg = xarML('Unable to move file [#(1)] to destination [#(2)].',$fileSrc, $fileDest);
            xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'FILE_NO_MOVE', new SystemException($msg));
            return FALSE;
        } 
        // Now remove the file :-)
        @unlink($fileSrc);
    }       
    
    return TRUE;
}

?>
