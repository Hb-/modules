<?php

/**
 * Get total amount of site hits grouped by client operating system
 *
 * @param   $args['top10'] bool - true, if only top 10 operationg systems are needed
 * @param   $args['year'] optional year
 * @param   $args['month'] optional month in year
 * @param   $args['day'] optional day in month
 * @return  mixed - array of data, sum and maximum OR false
 */
function stats_userapi_getbyos($args)
{
    // get arguments from argument array
    extract($args);
    if (!isset($top10)) {
        $top10 = false;
    }
    
    // initialize variables
    $max = 0; $sum = 0;
    $data = array();

    // Get database setup
    $dbconn =& xarDBGetConn();
    $xartable     =& xarDBGetTables();
    $statstable   = $xartable['stats'];
    $sniffertable = $xartable['sniffer'];

    // create query
    // Excluded unknown OS
    $query = "SELECT b.xar_ua_osnam AS name, b.xar_ua_osver AS osver, SUM(a.xar_sta_hits) as sum
              FROM $statstable AS a, $sniffertable AS b
              WHERE a.xar_ua_id = b.xar_ua_id
              AND b.xar_ua_agver <> 0
              AND b.xar_ua_osnam <> 'Unknown' ";
    $bindvars = array();
    if (!empty($year) && is_numeric($year)) {
        $query .= "AND a.xar_sta_year = ? ";
        $bindvars[] = $year;
        if (!empty($month) && is_numeric($month)) {
            $query .= "AND a.xar_sta_month = ? ";
            $bindvars[] = $month;
            if (!empty($day) && is_numeric($day)) {
                $query .= "AND a.xar_sta_day = ? ";
                $bindvars[] = $day;
            }
        }
    }
    $query .= "GROUP BY name, osver
               ORDER BY sum DESC";

    if ($top10 == true) {
        $result =& $dbconn->SelectLimit($query,10,-1,$bindvars);
    } else {
        $result =& $dbconn->Execute($query,$bindvars);
    }

    // check for an error with the database code
    if (!$result) return;

    // generate the result array
    for (; !$result->EOF; $result->MoveNext()) {
        list($os, $osver, $hits) = $result->fields;
        if ($hits > $max) $max = $hits;
        $sum += $hits;
        $data[] = compact('os','osver','hits');
    }
    $result->Close();

    // return the items
    return array($data, $sum, $max);
}

?>
