<?php

/**
 * get all links
 * TODO: allow fetched results to be ordered
 * @returns array
 * @param $args['enabled'] optional boolean: get only enabled or disabled links
 * @param $args['tid'] optional integer: get only given autolink type
 * @return array of links, or false on failure
 */
function autolinks_userapi_getall($args)
{
    extract($args);

    // Optional arguments
    if (!isset($startnum)) {
        $startnum = 1;
    }
    if (!isset($numitems)) {
        $numitems = -1;
    }

    // Security Check
    if(!xarSecurityCheck('ReadAutolinks')) {return;}

    list($dbconn) = xarDBGetConn();
    $xartable = xarDBGetTables();

    $autolinkstable = $xartable['autolinks'];
    $autolinkstypestable = $xartable['autolinks_types'];

    // Extra where-clause conditions.
    $where = array();

    // TODO: put where-clause stuff in a common function
    if (isset($enabled))
    {
        if (empty($enabled))
        {
            // Only return disabled links.
            $where[] = '(xar_enabled = 0 or xar_enabled is null)';
        } else {
            // Only return enabled links.
            $where[] = 'xar_enabled = 1';
        }
    }

    if (isset($tid) && is_numeric($tid))
    {
        $where[] = '(xar_type_tid = ' . $tid . ')';
    }

    $where = implode(' and ', $where);

    // Initialise.
    $links = array();

    // Get links.
    // Use a left join to return links without a valid type (we
    // don't want to lose them).
    $query = 'SELECT xar_lid,
                   xar_keyword,
                   xar_title,
                   xar_url,
                   xar_comment,
                   xar_enabled,
                   xar_match_re,
                   xar_cache_replace,
                   xar_sample,
                   xar_name,
                   xar_type_tid,
                   xar_dynamic_replace,
                   xar_template_name,
                   xar_type_name
            FROM ' . $autolinkstable
        . ' LEFT JOIN ' . $autolinkstypestable 
        . ' ON xar_tid = xar_type_tid'
        . (!empty($where) ? ' where ' . $where : '')
        . ' ORDER BY xar_keyword';

    $result =& $dbconn->SelectLimit($query, $numitems, $startnum-1);
    if (!$result) {return;}

    for (; !$result->EOF; $result->MoveNext()) {
        list(
            $lid, $keyword, $title, $url, $comment, $enabled, $match_re, $cache_replace, $sample, $name,
            $tid, $dynamic_replace, $template_name, $type_name
        ) = $result->fields;
    	if(xarSecurityCheck('ReadAutolinks', 0, 'All', $name.':'.$lid)) {
            $links[$lid] = array(
                'lid' => $lid,
                'keyword' => $keyword,
                'title' => $title,
                'url' => $url,
                'comment' => $comment,
                'enabled' => $enabled,
                'match_re' => $match_re,
                'cache_replace' => $cache_replace,
                'sample' => $sample,
                'name' => $name,
                'tid' => $tid,
                'dynamic_replace' => $dynamic_replace,
                'template_name' => $template_name,
                'type_name' => $type_name
            );
        }
    }

    $result->Close();

    return $links;
}

?>