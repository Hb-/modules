<?php

 /**
  *  Get the name of a mime type 
  *
  *  @author Carl P. Corliss
  *  @access public
  *  @param  integer    subtypeId   the subtypeID of the mime subtype to lookup (optional)
  *  @param  integer    subtypeName the Name of the mime sub type to lookup (optional)
  *  returns array      An array of (subtypeId, subtypeName) or an empty array
  */
  
function mime_userapi_get_mimetype( $args ) {

    extract($args);
    
    if (!isset($subtypeId) && !isset($subtypeName)) {
        $msg = xarML('No (usable) parameter to work with (#(1)::#(2)::#(3))', 'mime','userapi','get_subtype');
        xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'BAD_PARAM', new SystemException($msg));
    }
    
    // Get database setup
    list($dbconn) = xarDBGetConn();
    $xartable     = xarDBGetTables();
    
    $where = ' WHERE ';
    
    if (isset($subtypeId)) {
        $where .= ' xmstype.xar_mime_subtype_id = ' . $subtypeId;
    } else {
        $where .= " xmstype.xar_mime_subtype_name = '".strtolower($subtypeName)."'";
    }    
    
    // table and column definitions
    $subtype_table =& $xartable['mime_subtype'];
    $type_table    =& $xartable['mime_type'];

    $sql = "SELECT xar_mime_type_name AS mimetype,
                   xar_mime_subtype_name AS mimesubtype
              FROM $type_table AS xmtype, $subtype_table AS xmstype
            $where
               AND xmtype.xar_mime_type_id = xmstype.xar_mime_type_id";

    $result = $dbconn->Execute($sql);

    if (!$result || $result->EOF)  {
        return;
    }
    
    $row = $result->GetRowAssoc(false);
    
    return $row['mimetype'] . '/' . $row['mimesubtype'];
}    
    
?>
