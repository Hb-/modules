<?php

// FIXME: <rabbitt> only allow download of files that are -approved

function uploads_user_download()
{
    if (!xarSecurityCheck('ViewUploads')) return;
    
    if (!xarVarFetch('fileId', 'int:1:', $fileId)) return;

    $fileInfo = xarModAPIFunc('uploads','user','db_get_file', array('fileId' => $fileId));
    
    if (empty($fileInfo) || !count($fileInfo)) {
        $msg = xarML('Unable to retrieve information on file [#(1)]', $fileId);
        xarErrorSet(XAR_USER_EXCEPTION, 'UPLOADS_ERR_NO_FILE', new SystemException($msg));
        return;
    }
    
    // the file should be the first indice in the array
    $fileInfo = end($fileInfo);
    
    $instance[0] = $fileInfo['fileTypeInfo']['typeId'];
    $instance[1] = $fileInfo['fileTypeInfo']['subtypeId'];
    $instance[2] = xarSessionGetVar('uid');
    $instance[3] = $fileId;
    
    $instance = implode(':', $instance);

    // If you are an administrator OR the file is approved, continue
    if ($fileInfo['fileStatus'] != _UPLOADS_STATUS_APPROVED && !xarSecurityCheck('AdminUploads', false, 'File' . $instance)) {
        xarErrorHandled();
        $msg = xarML('You do not have the necessary permissions for this object.');
        xarErrorSet(XAR_SYSTEM_EXCEPTION, 'NO_PERMISSION', new DefaultUserException($msg));
        // No access - so return the exception
        return;
    }
        
    if (xarSecurityCheck('ViewUploads', 1, 'File', $instance)) {
        if ($fileInfo['storeType'] & _UPLOADS_STORE_FILESYSTEM || ($fileInfo['storeType'] == _UPLOADS_STORE_DB_ENTRY)) {
            if (!file_exists($fileInfo['fileLocation'])) {
                $msg = xarML('File [#(1)] does not exist in FileSystem.', $fileInfo['fileName']);
                xarErrorSet(XAR_SYSTEM_EXCEPTION, 'FILE_ERR_NO_FILE', new DefaultUserException($msg));
                return;
            }
        } elseif ($fileInfo['storeType'] & _UPLOADS_STORE_DB_FULL) {
            if (!xarModAPIFunc('uploads', 'user', 'db_count_data', array('fileId' => $fileInfo['fileId']))) {
                $msg = xarML('File [#(1)] does not exist in Database.', $fileInfo['fileName']);
                xarErrorSet(XAR_SYSTEM_EXCEPTION, 'FILE_ERR_NO_FILE', new DefaultUserException($msg));
                return;
            }
        }        
        
        $result = xarModAPIFunc('uploads', 'user', 'file_push', $fileInfo);
        
        if (!$result || xarCurrentErrorType() !== XAR_NO_EXCEPTION) {
            // now just return and let the error bubble up
            return FALSE;
        } 
    
    } else { 
        return FALSE;
    }
}
?>
