<?php
/**
 * File: $Id$
 * 
 * Get all topics in a forum
 * 
 * @package Xaraya eXtensible Management System
 * @copyright (C) 2003 by the Xaraya Development Team.
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.org
 *
 * @subpackage  xarbb Module
 * @author John Cox
*/
/**
 * get all topics
 *
 * @param $args['fid'] forum id, or
 * @param $args['tids'] array of topic ids
 * @returns array
 * @return array of links, or false on failure
 */
function xarbb_userapi_getalltopics($args)
{
    extract($args);

     // Optional argument
    if (!isset($startnum)) {
        $startnum = 1;
    }
    if (!isset($numitems)) {
        $numitems = -1;
    } 
    if (empty($cids)) {
        $cids = array();
    }

    if (empty($fid) && empty($tids)) {
        $msg = xarML('Invalid Parameter Count', '', 'userapi', 'get', 'xarbb');
        xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'BAD_PARAM', new SystemException($msg));
        return;
    }
 
    // Why do this look-up multiple times?  Return the cache instead.
    //<jojodee> Why here? I thought this was all topics for a given forum -not ALL topics
    //so we have to cache per forum
    /*if (xarVarIsCached('xarbb.topics', 'alltopicscache')){
        $topics = xarVarGetCached('xarbb.topics', 'alltopicscache');
        return $topics;
    }*/

    $dbconn =& xarDBGetConn();
    $xartable =& xarDBGetTables();
    $xbbtopicstable = $xartable['xbbtopics'];
    $xbbforumstable = $xartable['xbbforums'];
    if (!xarModAPILoad('categories', 'user')) return;
    // Get link
    $categoriesdef = xarModAPIFunc('categories','user','leftjoin',
                                   array('cids' => $cids,
                                        'modid' => xarModGetIDFromName('xarbb')));

    // CHECKME: this won't work for forums that are assigned to more (or less) than 1 category
    // Do we want to support that in the future ?
    // make only one query to speed up
    // Get links
    //Fix for duplicates listings of topics with topic itemtypes - select distinct - get bug #2335
    $query = "SELECT xar_tid,
                     $xbbtopicstable.xar_fid,
                     xar_ttitle,
                     xar_tpost,
                     xar_tposter,
                     xar_ttime,
                     xar_tftime,
                     xar_treplies,
                     xar_tstatus,
                     xar_treplier,
                     xar_fname,
                     xar_fdesc,
                     xar_ftopics, 
                     xar_fposts,
                     xar_fposter,
                     xar_fpostid,
                     {$categoriesdef['cid']}
            FROM $xbbtopicstable LEFT JOIN $xbbforumstable ON $xbbtopicstable.xar_fid = $xbbforumstable.xar_fid
            LEFT JOIN {$categoriesdef['table']} ON {$categoriesdef['field']} = $xbbforumstable.xar_fid
            {$categoriesdef['more']}
            WHERE {$categoriesdef['where']} ";
     if (isset($fid)) {
        $query .= "AND $xbbforumstable.xar_fid = " . xarVarPrepForStore($fid);
        //#bug 2335 - some older upgrades of xarbb seem to need the following to prevent duplicates
        $query .= " AND {$categoriesdef['itemtype']} = 0";
     } else {
        $query .= " AND xar_tid IN (" . join(', ', $tids) . ")";
    }
    // FIXME we should add possibility change sorting order
    $query .= " ORDER BY xar_ttime DESC";

    // Need to run the query and add $numitems to ensure pager works
    if (isset($numitems) && is_numeric($numitems)) {
        $result =& $dbconn->SelectLimit($query, $numitems, $startnum-1);
    } else {
        $result =& $dbconn->Execute($query);
    }
 
    $topics = array();
    for (; !$result->EOF; $result->MoveNext()) {
        list($tid, $fid, $ttitle, $tpost, $tposter, $ttime, $tftime, $treplies, $tstatus,$treplier,
    	$fname, $fdesc, $ftopics, $fposts, $fposter, $fpostid,$catid) = $result->fields;

	    if (xarSecurityCheck('ReadxarBB',0,'Forum',"$catid:$fid"))	{
            $topics[] = array('tid'     => $tid,
                   'fid'     => $fid,
                   'ttitle'  => $ttitle,
                   'tpost'   => $tpost,
                   'tposter' => $tposter,
                   'ttime'   => $ttime,
                   'tftime'  => $tftime,
                   'treplies'=> $treplies,
                   'tstatus' => $tstatus,
                   'treplier' => $treplier,
                   'fname'   => $fname,
                   'fdesc'   => $fdesc,
                   'ftopics' => $ftopics,
                   'fposts'  => $fposts,
                   'fposter' => $fposter,
                   'fpostid' => $fpostid,
                   'catid'   => $catid);
        }
    }
    $result->Close();
    // Save some variables to (temporary) cache for use in blocks etc.
    xarVarSetCached('xarbb.topics','alltopicscache',$topics);
    return $topics;
}
?>
