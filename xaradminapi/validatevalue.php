<?php
/**
 * validate input values for uploads module (used in DD properties)
 *
 * @param  $args ['id'] string id of the upload field(s)
 * @param  $args ['value'] string the current value(s)
 * @param  $args ['format'] string format specifying 'fileupload', 'textupload' or 'upload' (future ?)
 * @param  $args ['multiple'] boolean allow multiple uploads or not
 * @param  $args ['maxsize'] integer maximum size for upload files
 * @param  $args ['methods'] array allowed methods 'trusted', 'external', 'stored' and/or 'upload'
 * @returns array
 * @return array of (result, value) with result true, false or NULL (= error)
 */
function uploads_adminapi_validatevalue($args)
{
    extract($args);
    if (empty($id)) {
        $id = null;
    }
    if (empty($value)) {
        $value = null;
    }
    if (empty($format)) {
        $format = 'fileupload';
    }
    if (empty($multiple)) {
        $multiple = false;
    } else {
        $multiple = true;
    }
    if (empty($maxsize)) {
        $maxsize = 1000000;
    }
    if (empty($methods)) {
        $methods = null;
    }

    // Check to see if an old value is present. Old values just file names
    // and do not start with a semicolon (our delimiter)
    if (xarModAPIFunc('uploads', 'admin', 'dd_value_needs_conversion', $value)) {
        $newValue = xarModAPIFunc('uploads', 'admin', 'dd_convert_value', array('value' =>$value));

        // if we were unable to convert the value, then go ahead and and return
        // an empty string instead of processing the value and bombing out
        if ($newValue == $value) {
            $value = null;
            unset($newValue);
        } else {
            $value = $newValue;
            unset($newValue);
        }
    }


    xarModAPILoad('uploads','user');

    if (isset($methods) && count($methods) > 0) {
        $typeCheck = 'enum:0:' . _UPLOADS_GET_STORED;
        $typeCheck .= (isset($methods['external']) && $methods['external'])  ? ':' . _UPLOADS_GET_EXTERNAL : '';
        $typeCheck .= (isset($methods['trusted']) && $methods['trusted']) ? ':' . _UPLOADS_GET_LOCAL : '';
        $typeCheck .= (isset($methods['upload']) && $methods['upload']) ? ':' . _UPLOADS_GET_UPLOAD : '';
        $typeCheck .= ':';
    } else {
        $typeCheck = 'enum:0:' . _UPLOADS_GET_STORED;
        $typeCheck .= (xarModGetVar('uploads', 'dd.fileupload.external') == TRUE) ? ':' . _UPLOADS_GET_EXTERNAL : '';
        $typeCheck .= (xarModGetVar('uploads', 'dd.fileupload.trusted') == TRUE) ? ':' . _UPLOADS_GET_LOCAL : '';
        $typeCheck .= (xarModGetVar('uploads', 'dd.fileupload.upload') == TRUE) ? ':' . _UPLOADS_GET_UPLOAD : '';
        $typeCheck .= ':';
    }

    xarVarFetch($id . '_attach_type', $typeCheck, $action, -2, XARVAR_NOT_REQUIRED);

    if (!isset($action)) {
        $action = -2;
    }


    $args['action']    = $action;
    switch ($action) {
        case _UPLOADS_GET_UPLOAD:

            $file_maxsize = xarModGetVar('uploads', 'file.maxsize');
            $file_maxsize = $file_maxsize > 0 ? $file_maxsize : $maxsize;

            if (!xarVarFetch('MAX_FILE_SIZE', "int::$file_maxsize", $maxsize)) return;

            if (!xarVarValidate('array:1:', $_FILES[$id . '_attach_upload'])) return;

            $upload         =& $_FILES[$id . '_attach_upload'];
            $args['upload'] =& $_FILES[$id . '_attach_upload'];
            break;
        case _UPLOADS_GET_EXTERNAL:
            // minimum external import link must be: ftp://a.ws  <-- 10 characters total

            if (!xarVarFetch($id . '_attach_external', 'regexp:/^([a-z]*).\/\/(.{7,})/', $import, 0, XARVAR_NOT_REQUIRED)) return;

            if (empty($import)) {
                return array(true,NULL);
            }

            $args['import'] = $import;
            break;
        case _UPLOADS_GET_LOCAL:

            if (!xarVarFetch($id . '_attach_trusted', 'list:regexp:/(?<!\.{2,2}\/)[\w\d]*/', $fileList)) return;

            $importDir = xarmodGetVar('uploads', 'path.imports-directory');
            foreach ($fileList as $file) {
                $file = str_replace('/trusted', $importDir, $file);
                $args['fileList']["$file"] = xarModAPIFunc('uploads', 'user', 'file_get_metadata',
                                                            array('fileLocation' => "$file"));
                $args['fileList']["$file"]['fileSize'] = $args['fileList']["$file"]['fileSize']['long'];
            }
            break;
        case _UPLOADS_GET_STORED:

            if (!xarVarFetch($id . '_attach_stored', 'list:int:1:', $fileList, 0, XARVAR_NOT_REQUIRED)) return;


            // If we've made it this far, then fileList was empty to start,
            // so don't complain about it being empty now
            if (empty($fileList) || !is_array($fileList)) {
                return array(true,NULL);
            }
            // We prepend a semicolon onto the list of fileId's so that
            // we can tell, in the future, that this is a list of fileIds
            // and not just a filename
            $value = ';' . implode(';', $fileList);

            return array(true,$value);
            break;
        case '-1':
            return array(true,$value);
        default:
            if (isset($value)) {
                if (strlen($value) && $value{0} == ';') {
                    return array(true,$value);
                } else {
                    return array(false,NULL);
                }
            } else {
                // If we have managed to get here then we have a NULL value
                // and $action was most likely either null or something unexpected
                // So let's keep things that way :-)
                return array(true,NULL);
            }
            break;
    }

    if (!empty($action)) {

        if (isset($storeType)) {
            $args['storeType'] = $storeType;
        }

        $list = xarModAPIFunc('uploads','user','process_files', $args);
        $storeList = array();
        foreach ($list as $file => $fileInfo) {
            if (!isset($fileInfo['errors'])) {
                $storeList[] = $fileInfo['fileId'];
            } else {
                $msg = xarML('Error Found: #(1)', $fileInfo['errors'][0]['errorMesg']);
                xarErrorSet(XAR_SYSTEM_EXCEPTION, 'UNKNOWN_ERROR', new SystemException($msg));

                return;
            }
        }
        if (is_array($storeList) && count($storeList)) {
            // We prepend a semicolon onto the list of fileId's so that
            // we can tell, in the future, that this is a list of fileIds
            // and not just a filename
            $value = ';' . implode(';', $storeList);
        } else {
            return array(false,NULL);
        }
    } else {
        return array(false,NULL);
    }

    return array(true,$value);
}

?>
