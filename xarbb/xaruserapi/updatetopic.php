<?php
/**
 * File: $Id$
 * 
 * Update a topic
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
 * create a new forum
 * @param $args['fname'] name of forum
 * @param $args['fdesc'] description of forum
 * @param $args['tid'] topic id to update
 * @returns int
 * @return autolink ID on success, false on failure
 */
function xarbb_userapi_updatetopic($args)
{
    // Get arguments from argument array
    extract($args);

    if(!isset($tid))
        $invalid[] = "tid";

    // params in arg
    $params = array("fid"       => "xar_fid",
                    "ttitle"    => "xar_ttitle",
                    "tpost"     => "xar_tpost",
                    "tposter"   => "xar_tposter",
                    "time"      => "xar_ttime",
                    "tposter"   => "xar_tposter",
                    "treplies"  => "xar_treplies",
                    "treplier"  => "xar_treplier",
                    "tftime"    => "xar_tftime",
                    "tstatus"   => "xar_tstatus");
    foreach($params as $vvar => $dummy)    {
        if(isset($$vvar))    {
            $set = true;
            break;
        }
    }
    if(    !isset($set)   )
        $invalid[] = "at least one of these has to be set: ".join(",",array_keys($fields));

    // Argument check - make sure that at least on paramter is present
    // if not then set an appropriate error message and return
    if ( isset($invalid) ) {
        $msg = xarML('Invalid Parameter Count',
                    join(', ',$invalid), 'admin', 'create', 'xarbb');
        xarErrorSet(XAR_SYSTEM_EXCEPTION, 'BAD_PARAM',
                       new SystemException($msg));
        return;
    }

    // for sec check
    if(!$topic = xarModAPIFunc('xarbb','user','gettopic',array('tid' => $tid))) return;    

    // Security Check
    // it would have to be ModxarBB, but because posting results in an update, it has to be Post Permission
    if(!xarSecurityCheck('PostxarBB',1,'Forum',$topic['catid'].':'.$topic['fid'])) return;    // todo

    // Get datbase setup
    $dbconn =& xarDBGetConn();
    $xartable =& xarDBGetTables();
    $xbbtopicstable = $xartable['xbbtopics'];

    if (empty($time)){
        $time = time();
    }

    $update = array();
    $bindvars = array();
    foreach($params as $vvar => $field)    {
        if(isset($$vvar)) {
            $update[] = $field ."=?";
            $bindvars[] = $$vvar;
        }
    }

    // Update item
    $query = "UPDATE $xbbtopicstable SET ".join(",",$update)." WHERE xar_tid = ?";
    $bindvars[] = $tid;

    $result =& $dbconn->Execute($query, $bindvars);
    if (!$result) return;

    // check if the topic moved to another forum and if there are replies
    if (!empty($fid) && $fid != $topic['fid'] && !empty($topic['treplies'])) {
        // if so, adapt the itemtype for the comments too
        $commentstable = $xartable['comments'];
        $ctable = $xartable['comments_column'];
        $query = "UPDATE $commentstable
                  SET $ctable[itemtype] = ?
                  WHERE $ctable[modid] = ? AND $ctable[itemtype] = ? AND $ctable[objectid] = ?";
        $modid = xarModGetIDFromName('xarbb');
        $bindvars = array((int) $fid, (int) $modid, (int) $topic['fid'], (int) $tid);

        $result =& $dbconn->Execute($query, $bindvars);
        if (!$result) return;
    }

    $data = xarModAPIFunc('xarbb',
                          'user',
                          'gettopic',
                          array('tid' => $tid));

    if (!isset($nohooks)){
        // Let any hooks know that we have created a new topic
        $args['module'] = 'xarbb';
        $args['itemtype'] = $topic['fid']; // forum item type
        $args['itemid'] = $tid;
        xarModCallHooks('item', 'update', $tid, $args);
    }

    // Return the id of the newly created link to the calling process
    return true;
}
?>
