<?php

/**
 *  Retrieve the DATA (contents) stored for a particular file based on 
 *  the file id. This returns an array not unlike the php function
 *  'file()' wherby the contents of the file are in an ordered array.
 *  The contents can be put back together by doing: implode('', 
 * 
 * @author Carl P. Corliss
 * @author Micheal Cortez
 * @access public
 * @param  integer  fileId     The ID of the file we are are retrieving
 *
 * @returns array   All the (4K) blocks stored for this file 
 */
 
function uploads_userapi_db_get_file_data( $args )  {
    
    extract($args);
    
    if (!isset($fileId)) {
        $msg = xarML('Missing parameter [#(1)] for function [#(2)] in module [#(3)]', 
                     'fileId','db_get_file_data','uploads');
        xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'BAD_PARAM', new SystemException($msg));
        return FALSE;
    }
    
    // Get database setup
    list($dbconn) = xarDBGetConn();
    $xartable     = xarDBGetTables();
        
        // table and column definitions
    $fileData_table = $xartable['file_entry'];
    
    $sql = "SELECT xar_fileEntry_id,
                   xar_fileData_id, 
                   xar_fileData 
              FROM $fileData_table
             WHERE xar_fileData_id = $fileId,
          ORDER BY xar_fileData_id ASC";
    
    $result = $dbconn->Execute($sql);

    if (!$result)  {
        return;
    }

    // if no record found, return an empty array        
    if ($result->EOF) {
        return array();
    }
    
    while (!$result->EOF) {
        $row = $result->GetRowAssoc(false);
        $fileData[$row['xar_filedata_id']]       = $row['xar_filedata'];
        $result->MoveNext();
    }
    $result->Close();

                                                                    
   return $fileData;
}

?>
