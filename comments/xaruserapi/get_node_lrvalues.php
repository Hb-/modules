<?php

/**
 * Grab the left and right values for a particular node
 * (aka comment) in the database
 *
 * @author   Carl P. Corliss (aka rabbitt)
 * @access   public
 * @param    integer     $cid     id of the comment to lookup
 * @returns  array an array containing the left and right values or an
 *           empty array if the comment_id specified doesn't exist
 */
function comments_userapi_get_node_lrvalues( $args ) {

    extract( $args );
    
    if (empty($cid)) {
        $msg = xarML('Missing or Invalid parameter \'cid\'!!');
        xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'BAD_PARAM', new SystemException($msg));
        return;
    }

    list($dbconn) = xarDBGetConn();
    $xartable = xarDBGetTables();

    $ctable = &$xartable['comments_column'];

    $sql = "SELECT  $ctable[left], $ctable[right]
              FROM  $xartable[comments]
             WHERE  $ctable[cid]='$cid'";

    $result =& $dbconn->Execute($sql);

    if(!$result)
        return;

    if (!$result->EOF) {
        $lrvalues = $result->GetRowAssoc(false);
    } else {
        $lrvalues = array();
    }

    $result->Close();

    return $lrvalues;
}

?>