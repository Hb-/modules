<?php

/**
 * return the field names and correct values for querying (or joining on)
 * the articles table
 * example 1 : SELECT ..., $title, $body,...
 *             FROM $table
 *             WHERE $title LIKE 'Hello world%'
 *                 AND $where
 *
 * example 2 : SELECT ..., $title, $body,...
 *             FROM ...
 *             LEFT JOIN $table
 *                 ON $field = <name of articleid field in your module>
 *             WHERE ...
 *                 AND $title LIKE 'Hello world%'
 *                 AND $where
 *
 * Note : the following arguments are all optional :
 *
 * @param $args['aids'] optional array of aids that we are selecting on
 * @param $args['authorid'] the ID of the author
 * @param $args['ptid'] publication type ID (for news, sections, reviews, ...)
 * @param $args['status'] array of requested status(es) for the articles
 * @param $args['search'] search text parameter(s)
 * @param $args['searchfields'] array of fields to search in
 * @param $args['searchtype'] start, end, like, eq, gt, ... (TODO)
 * @param $args['startdate'] articles published at startdate or later
 *                           (unix timestamp format)
 * @param $args['enddate'] articles published before enddate
 *                         (unix timestamp format)
 * @param $args['where'] additional where clauses (myfield gt 1234)
 * @returns array
 * @return array('table' => 'nuke_articles',
 *               'field' => 'nuke_articles.xar_aid',
 *               'where' => 'nuke_articles.xar_aid IN (...)',
 *               'title'  => 'nuke_articles.xar_title',
 *               ...
 *               'body'  => 'nuke_articles.xar_body')
 */
function articles_userapi_leftjoin($args)
{
    // Get arguments from argument array
    extract($args);

    // Optional argument
    if (!isset($aids)) {
        $aids = array();
    }

    // Note : no security checks here

    // Table definition
    $xartable =& xarDBGetTables();
    $dbconn   =& xarDBGetConn();
    $articlestable = $xartable['articles'];

    $leftjoin = array();

    // Add available columns in the articles table (for now)
    $columns = array('aid','title','summary','authorid','pubdate','pubtypeid',
                     'notes','status','body','language');
    foreach ($columns as $column) {
        $leftjoin[$column] = $articlestable . '.xar_' . $column;
    }

    // Specify LEFT JOIN ... ON ... [WHERE ...] parts
    $leftjoin['table'] = $articlestable;
    $leftjoin['field'] = $leftjoin['aid'];

    // Specify the WHERE part
    // FIXME: <mrb> someone better informed about this should replace
    // the xar-varprepforstore with qstr() method where appropriate
    $whereclauses = array();
    if (!empty($authorid) && is_numeric($authorid)) {
        $whereclauses[] = $leftjoin['authorid'] . ' = ' . $authorid;
    }
    if (!empty($ptid) && is_numeric($ptid)) {
        $whereclauses[] = $leftjoin['pubtypeid'] . ' = ' . $ptid;
    }
    if (!empty($status) && is_array($status)) {
        if (count($status) == 1 && is_numeric($status[0])) {
            $whereclauses[] = $leftjoin['status'] . ' = ' . $status[0];
        } elseif (count($status) > 1) {
            $allstatus = join(', ',$status);
            $whereclauses[] = $leftjoin['status'] . ' IN (' . $allstatus . ')';
        }
    }
    if (!empty($startdate) && is_numeric($startdate)) {
        $whereclauses[] = $leftjoin['pubdate'] . ' >= ' . $startdate;
    }
    if (!empty($enddate) && is_numeric($enddate)) {
        $whereclauses[] = $leftjoin['pubdate'] . ' < ' . $enddate;
    }
    if (!empty($language) && is_string($language)) {
        $whereclauses[] = $leftjoin['language'] . " = " . $dbconn->qstr($language);
    }
    if (count($aids) > 0) {
        $allaids = join(', ', $aids);
        $whereclauses[] = $articlestable . '.xar_aid IN (' . $allaids .')';
    }
    
    if (!empty($where)) {
        // find all single-quoted pieces of text and replace them first, to allow where clauses
        // like : title eq 'this and that' and body eq 'here or there'
        $idx = 0;
        $found = array();
        if (preg_match_all("/'(.*?)(?<!\\\)'/",$where,$matches)) {
            foreach ($matches[1] as $match) {
                $found[$idx] = $match;
                $match = preg_quote($match);

                $match = str_replace("#","\#",$match);

                $where = trim(preg_replace("#'$match'#","'~$idx~'",$where));
                $idx++;
            }
        }

        // cfr. BL compiler - adapt as needed (I don't think == and === are accepted in SQL)
        $findLogic      = array(' eq ', ' ne ', ' lt ', ' gt ', ' id ', ' nd ', ' le ', ' ge ');
        $replaceLogic   = array( ' = ', ' != ',  ' < ',  ' > ',  ' = ', ' != ', ' <= ', ' >= ');

        $where = str_replace($findLogic, $replaceLogic, $where);
        $parts = preg_split('/\s+(and|or)\s+/',$where,-1,PREG_SPLIT_DELIM_CAPTURE);
        $join = '';
        $mywhere = '';
        foreach ($parts as $part) {
            if ($part == 'and' || $part == 'or') {
                $join = $part;
                continue;
            }
            $pieces = preg_split('/\s+/',$part);
            $name = array_shift($pieces);
            // sanity check on SQL
            if (count($pieces) < 2) {
                continue;
            }
            if (isset($leftjoin[$name])) {
            // Note: this is a potential security hole, so don't allow end-users to
            // fill in the where clause without filtering quotes etc. !
                if (empty($idx)) {
                    $mywhere .= $join . ' ' . $leftjoin[$name] . ' ' . join(' ',$pieces) . ' ';
                } else {
                    $mywhere .= $join . ' ' . $leftjoin[$name] . ' ';
                    foreach ($pieces as $piece) {
                        // replace the pieces again if necessary
                        if (preg_match("#'~(\d+)~'#",$piece,$matches) && isset($found[$matches[1]])) {
                            $original = $found[$matches[1]];
                            $piece = preg_replace("#'~(\d+)~'#","'$original'",$piece);
                        }
                        $mywhere .= $piece . ' ';
                    }
                }
            }
        }
        if (!empty($mywhere)) {
            $whereclauses[] = '(' . $mywhere . ')';
        }
    }

    if (!empty($search)) {
        // TODO : improve + make use of full-text indexing for recent MySQL versions ?
        // 1. find quoted text
        $normal = array();
        if (preg_match_all('#"(.*?)"#',$search,$matches)) {
            foreach ($matches[1] as $match) {
                $normal[] = $match;
                $match = preg_quote($match);
                $search = trim(preg_replace("#\"$match\"#",'',$search));
            }
        }
        if (preg_match_all("/'(.*?)'/",$search,$matches)) {
            foreach ($matches[1] as $match) {
                $normal[] = $match;
                $match = preg_quote($match);
                $search = trim(preg_replace("#'$match'#",'',$search));
            }
        }
        // 2. find mandatory +text to include
        // 3. find mandatory -text to exclude
        // 4. find normal text
        $more = preg_split('/\s+/',$search,-1,PREG_SPLIT_NO_EMPTY);
        $normal = array_merge($normal,$more);

        if (empty($searchfields)) {
            $searchfields = array('title','summary','body');
        }
        $find = array();
        foreach ($normal as $text) {
            // TODO: use XARADODB to escape wildcards (and use portable ones) ??
            $text = str_replace('%','\%',$text);
            $text = str_replace('_','\_',$text);
            foreach ($searchfields as $field) {
                if (empty($leftjoin[$field])) continue;
                if (empty($searchtype) || $searchtype == 'like') {
                    $find[] = $leftjoin[$field] . " LIKE " . $dbconn->qstr('%' . $text . '%');
                } elseif ($searchtype == 'start') {
                    $find[] = $leftjoin[$field] . " LIKE " . $dbconn->qstr($text . '%');
                } elseif ($searchtype == 'end') {
                    $find[] = $leftjoin[$field] . " LIKE " . $dbconn->qstr('%' . $text);
                } elseif ($searchtype == 'eq') {
                    $find[] = $leftjoin[$field] . " = " . $dbconn->qstr($text);
                } else {
                // TODO: other search types ?
                    $find[] = $leftjoin[$field] . " LIKE " . $dbconn->qstr('%' . $text . '%');
                }
            }
        }
        $whereclauses[] = '(' . join(' OR ',$find) . ')';
    }
    if (count($whereclauses) > 0) {
        $leftjoin['where'] = join(' AND ', $whereclauses);
    } else {
        $leftjoin['where'] = '';
    }

    return $leftjoin;
}

?>