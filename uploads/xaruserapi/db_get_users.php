<?php

/**
 *  Retrieve a list of users who have submitted files
 * 
 * @author Carl P. Corliss
 * @author Micheal Cortez
 * @access public
 * @param  string  mime_type   (Optional) grab files with the specified mime type 
 *
 * @returns array   All of the metadata stored for the particular file
 */
 
function uploads_userapi_db_get_users( $args )  
{
    
    extract($args);
    
    if (isset($mimeType) && !empty($mimeType)) {
        $where = "WHERE (xar_mime_type LIKE '$mimeType')";
    } else {
        $where = '';
    }

    // Get database setup
    $dbconn =& xarDBGetConn();
    $xartable =& xarDBGetTables();
        
        // table and column definitions
    $fileEntry_table = $xartable['file_entry'];
    
    $sql = "SELECT DISTINCT xar_user_id
              FROM $fileEntry_table
            $where";
    
    $result = $dbconn->Execute($sql);
    
    if (!$result)  {
        return FALSE;
    }
        

    // if no record found, return an empty array        
    if ($result->EOF) {
        return array();
    }
    
    while (!$result->EOF) {
        $row = $result->GetRowAssoc(false);
        
        $userInfo['userId']   = $row['xar_user_id'];
        $userInfo['userName'] = xarUserGetVar('name', $row['xar_user_id']);
        
        $userList[$userInfo['userId']] = $userInfo;
        
        unset($userinfo);
        $result->MoveNext();
    }

    return $userList;
}

?>