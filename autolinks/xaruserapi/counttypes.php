<?php

/**
 * count the number of links in the database
 * @returns integer
 * @returns number of link types in the database
 */
function autolinks_userapi_counttypes()
{
    list($dbconn) = xarDBGetConn();
    $xartable = xarDBGetTables();

    $autolinkstypestable = $xartable['autolinks_types'];

    $query = 'SELECT COUNT(1) FROM ' . $autolinkstypestable;
    $result =& $dbconn->Execute($query);
    if (!$result) {return;}

    list($numitems) = $result->fields;

    $result->Close();

    return (int)$numitems;
}

?>