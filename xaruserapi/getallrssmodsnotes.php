<?php

function release_userapi_getallrssmodsnotes($args)
{
    extract($args);

    $releaseinfo = array();

    // Security Check
    if(!xarSecurityCheck('OverviewRelease')) return;

    // Get database setup
    $dbconn =& xarDBGetConn();
    $xartable =& xarDBGetTables();

    $releasenotes = $xartable['release_notes'];

    $query = "SELECT xar_rnid,
                     xar_rid,
                     xar_version
            FROM $releasenotes
            WHERE xar_certified = ?
            AND xar_type = ?
            ORDER by xar_time DESC";

    $bindvars = array(2, $type);
    $result =& $dbconn->Execute($query, $bindvars);
    if (!$result) return;

    // Put users into result array
    for (; !$result->EOF; $result->MoveNext()) {
        list($rnid, $rid, $version) = $result->fields;
        if (xarSecurityCheck('OverviewRelease', 0)) {
            $releaseinfo[] = array('rnid'       => $rnid,
                                   'rid'        => $rid,
                                   'version'    => $version);
        }
    }

    $result->Close();

    // Return the users
    return $releaseinfo;
}

?>