 <?php

/** 
 *  Change the status on a file, or group of files based on the file id(s) or filetype
 *
 *  @author  Carl P. Corliss
 *  @access  public
 * 
 *  @returns integer The number of affected rows on success, or FALSE on error
 */

function uploads_userapi_db_change_status( $args ) {
    extract($args);
    
    if (!isset($fileId) && !isset($fileType)) {
        $msg = xarML('Missing identifying parameter function [#(1)] in module [#(2)]', 
                     'db_change_status','uploads');
        xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'BAD_PARAM', new SystemException($msg));
        return FALSE;
    }
    
    if (!isset($newStatus)) {
        $msg = xarML('Missing parameter [#(1)] for function [#(2)] in module [#(3)]', 
                     'newStatus','db_change_status','uploads');
        xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'BAD_PARAM', new SystemException($msg));
        return FALSE;
    }
    
    if (isset($fileId)) {
        // Looks like we have an array of file ids, so change them all
        if (is_array($fileId)) {
            $where = " WHERE xar_fileEntry_id IN (" . implode(',', $fileId) .")";
        // Guess we're only changing one file id ...
        } else {
            $where = " WHERE xar_fileEntry_id = $fileId";
        }
    // Otherwise, we're changing based on MIME type
    } else {
        $where = " WHERE xar_mime_type LIKE '$fileType'";
    }
    
    if (isset($curStatus) && is_numeric($curStatus)) {
        $where .= " AND xar_status = $curStatus";
    }
        
    //add to uploads table
    // Get database setup
    $dbconn =& xarDBGetConn();
    $xartable =& xarDBGetTables();

    $fileEntry_table = $xartable['file_entry'];
    
    $sql             = "UPDATE $fileEntry_table 
                           SET xar_status = $newStatus
                        $where";
        
    $result          = &$dbconn->Execute($sql);

    if (!$result) {
        return FALSE;
    } else {
        return $dbconn->Affected_Rows();
    }

}

?>
