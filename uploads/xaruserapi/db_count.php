<?php

/**
 *  Retrieve the total count of files in the database based on the filters passed in
 * 
 * @author Carl P. Corliss
 * @author Micheal Cortez
 * @access public
 * @param  integer  file_id     (Optional) grab file with the specified file id
 * @param  string   fileName    (Optional) grab file(s) with the specified file name
 * @param  integer  status      (Optional) grab files with a specified status  (SUBMITTED, APPROVED, REJECTED)
 * @param  integer  user_id     (Optional) grab files uploaded by a particular user
 * @param  integer  store_type  (Optional) grab files with the specified store type (FILESYSTEM, DATABASE)
 * @param  integer  mime_type   (Optional) grab files with the specified mime type 
 *
 * @returns array   All of the metadata stored for the particular file
 */
 
function uploads_userapi_db_count( $args )  {
    
    extract($args);
    
    if (!isset($fileId) && !isset($fileName) && !isset($fileStatus) && 
        !isset($userId)  && !isset($fileType) && !isset($store_type)) {            
        $msg = xarML('Missing parameters for function [#(1)] in module [#(2)]', 'db_get_file', 'uploads');
        xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'BAD_PARAM', new SystemException($msg));
        return FALSE;
    }        
    
    $where = array();
    
    if (isset($fileId)) {
        if (is_array($fileId)) {
            $where[] = 'xar_fileEntry_id IN (' . implode(',', $fileIds) . ')';
        } elseif (!empty($fileId)) {
            $where[] = "xar_fileEntry_id = $fileId";
        }
    }
    
    if (isset($fileName) && !empty($fileName)) {
        $where[] = "(xar_filename LIKE '$fileName')";
    }

    if (isset($fileStatus) && !empty($fileStatus)) {
        $where[] = "(xar_status = $fileStatus)";
    }

    if (isset($userId) && !empty($userId)) {
        $where[] = "(xar_user_id = $userId)";
    } 

    if (isset($store_type) && !empty($store_type)) {
        $where[] = "(xar_store_type = $store_type)";
    }
    
    if (isseT($fileType) && !empty($fileType)) {
        $where[] = "(xar_mime_type LIKE '$fileType')";
    }

    if (count($where) > 1) {
        $where = implode(' AND ', $where);
    } else {
        $where = implode('', $where);
    }
    // Get database setup
    list($dbconn) = xarDBGetConn();
    $xartable     = xarDBGetTables();
        
        // table and column definitions
    $fileEntry_table = $xartable['file_entry'];
    
    $sql = "SELECT COUNT(xar_fileEntry_id) AS total
              FROM $fileEntry_table
             WHERE $where";
    
    $result = $dbconn->Execute($sql);

    if (!$result)  {
        return FALSE;
    }

    // if no record found, return an empty array        
    if ($result->EOF) {
        return (integer) 0;
    }
    
    $row = $result->GetRowAssoc(false);
    
    return $row['total'];
}

?>
