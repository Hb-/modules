<?php

/**
 * count number of items depending on additional module criteria
 *
 * @param $args['cids'] array of cids that we are counting for (OR/AND)
 * @param $args['andcids'] true means AND-ing categories listed in cids
 *
 * @param $args['authorid'] the ID of the author
 * @param $args['ptid'] publication type ID (for news, sections, reviews, ...)
 * @param $args['status'] array of requested status(es) for the articles
 * @param $args['startdate'] articles published at startdate or later
 *                           (unix timestamp format)
 * @param $args['enddate'] articles published before enddate
 *                         (unix timestamp format)
 * @returns int
 * @return number of items
 */
function articles_userapi_countitems($args)
{
    // Database information
    list($dbconn) = xarDBGetConn();

    // Get the field names and LEFT JOIN ... ON ... parts from articles
    // By passing on the $args, we can let leftjoin() create the WHERE for
    // the articles-specific columns too now
    $articlesdef = xarModAPIFunc('articles','user','leftjoin',$args);

// TODO: make sure this is SQL standard
    // Start building the query
    $query = 'SELECT COUNT(DISTINCT ' . $articlesdef['field'] . ')';
    $query .= ' FROM ' . $articlesdef['table'];

    if (!isset($args['cids'])) {
        $args['cids'] = array();
    }
    if (!isset($args['andcids'])) {
        $args['andcids'] = false;
    }
    if (count($args['cids']) > 0) {
        // Load API
        if (!xarModAPILoad('categories', 'user')) return;

        // Get the LEFT JOIN ... ON ...  and WHERE (!) parts from categories
        $args['modid'] = xarModGetIDFromName('articles');
        $categoriesdef = xarModAPIFunc('categories','user','leftjoin',$args);

        $query .= ' LEFT JOIN ' . $categoriesdef['table'];
        $query .= ' ON ' . $categoriesdef['field'] . ' = '
                . $articlesdef['aid'];
        $query .= $categoriesdef['more'];
        $docid = 1;
    }

    // Create the WHERE part
    $where = array();
    // we rely on leftjoin() to create the necessary articles clauses now
    if (!empty($articlesdef['where'])) {
        $where[] = $articlesdef['where'];
    }
    if (!empty($docid)) {
        // we rely on leftjoin() to create the necessary categories clauses
        $where[] = $categoriesdef['where'];
    }
    if (count($where) > 0) {
        $query .= ' WHERE ' . join(' AND ', $where);
    }

    // Run the query - finally :-)
    $result =& $dbconn->Execute($query);
    if (!$result) return;

    if ($result->EOF) {
        return;
    }

    $num = $result->fields[0];
    $result->Close();

    return $num;
}

?>
