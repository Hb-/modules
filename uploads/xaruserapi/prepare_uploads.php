<?php

/** 
 *  Prepares a list of files that have been uploaded, creating a structure for 
 *  each file with the following parts:
 *      * fileType  - mimetype
 *      * fileSrc   - the source location of the file
 *      * fileSize  - the filesize of the file
 *      * fileName  - the file's basename 
 *      * fileDest  - the (potential) destination for the file (filled in even if stored in the db and not filesystem)
 *  Any file that has errors will have it noted in the same structure with error number and message in:
 *      * errors[]['errorMesg']
 *      * errors[]['errorId']
 *
 *  @author  Carl P. Corliss
 *  @access  public
 *  @param   boolean obfuscate            whether or not to obfuscate the filename
 *  @param   string  savePath             Complete path to directory in which we want to save this file
 *  @returns boolean                      TRUE on success, FALSE on failure
 */


function uploads_userapi_prepare_uploads( &$args ) {

    // if there are no files, return an empty array.
    if (empty($_FILES) || !is_array($_FILES) || count($_FILES) <= 0) {
        return array();
    }
        
    extract ( $args );

    /**
     *  Initial variable checking / setup 
     */
    if (isset($obfuscate) && $obfuscate) {
        $obfuscate_fileName = TRUE;
    } else {
        $obfuscate_fileName = xarModGetVar('uploads','file.obfuscate-on-upload');
    }    
    
    if (!isset($savePath)) {
        $savePath = xarModGetVar('uploads', 'path.uploads-directory');
    }
    
    forach ($_FILES as $uploadId => $fileInfo) {
         
        // If we don't have the right data structure, then we can't do much 
        // here, so return immediately with an exception set  
        if ((!isset($fileInfo)          || !is_array($fileInfo))      || 
             !isset($fileInfo['name'])  || !isset($fileInfo['type'])  || 
             !isset($fileInfo['error']) || !isset($fileInfo['size'])  || 
             !isset($fileInfo['tmp_name']))  {
                $msg = xarML('Invalid data format for upload ID: [#(1)]', $uploadId);
                xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'BAD_PARAM', new SystemException($msg));
                return;
        }

        $fileInfo['fileType'] = $fileInfo['type'];
        $fileInfo['fileSrc']    = $fileInfo['tmp_name'];
        $fileInfo['fileSize']   = $fileInfo['size'];
        $fileInfo['fileName']   = $fileInfo['name'];

        // Check to see if we're importing and, if not, check the file and ensure that it 
        // meets any requirements we might have for it. If it doesn't pass the tests,
        // then return FALSE
        if (!xarModAPIFunc('uploads','user','validate_upload', array('fileInfo' => $fileInfo))) {
            $errorObj = xarExceptionValue();

            if (is_object($errorObj)) {
                $fileError['errorMesg'] = $errorObj->getShort();
                $fileError['errorId']   = $errorObj->getID();
            } else {
                $fileError['errorMesg'] = 'Unknown Error!';
                $fileError['errorId']   = _UPLOADS_ERROR_UNKOWN;
            }
            $fileInfo['errors']      = array($fileError);
            
            // clear the exception
            xarExceptionHandled();
            
            // continue on to the next uploaded file in the list
            continue;
        }

        /** 
        *  Start the process of adding an uploaded file
        */

        unset($fileInfo['tmp_name']);
        unset($fileInfo['size']);
        unset($fileInfo['name']);
        unset($fileInfo['type']);

        $fileInfo['fileType']   = xarModAPIFunc('mime','user','analyze_file', 
                                                 array('fileName' => $fileInfo['fileSrc']));

        // Check to see if we need to obfuscate the filename
        if ($obfuscate_fileName) {
            $obf_fileName = xarModAPIFunc('uploads','user','file_obfuscate_name', 
                                        array('fileName' => $fileInfo['fileName']));

            if (empty($obf_fileName) || FALSE === $obf_fileName) {
                // If the filename was unable to be obfuscated, 
                // set an error, but don't die - let the caller 
                // do what they want with this.
                $fileError['errorMesg'] = 'Unable to obfuscate filename!';
                $fileError['errorId']   = ;_UPLOADS_ERROR_NO_OBFUSCATE;
                $fileInfo['errors']      = array($fileError);
            } else {
                $fileInfo['fileDest'] = $savePath . '/' . $obf_fileName;
            }
        } else {
            // if we're not obfuscating it, 
            // just use the name of the uploaded file
            $fileInfo['fileDest'] = $savePath . '/' . $fileInfo['fileName'];
        }

        $fileList[] =  $fileInfo;
    }    
    
    return $fileList;
}
 
?>