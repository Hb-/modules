<?php
/** 
 * File: $Id$
 * 
 * View a list of topics in a forum
 * 
 * @package Xaraya eXtensible Management System
 * @copyright (C) 2003 by the Xaraya Development Team.
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.org
 *
 * @subpackage  xarbb Module
 * @author John Cox
*/
function xarbb_user_viewforum()
{
    // Get parameters from whatever input we need
    if(!xarVarFetch('startnum', 'id', $startnum, NULL, XARVAR_NOT_REQUIRED)) return;
    if(!xarVarFetch('fid', 'id', $fid)) return;
    if (!xarVarFetch('read', 'isset', $read, NULL, XARVAR_DONT_SET)) return;

    // The user API function is called.
    $data = xarModAPIFunc('xarbb',
                          'user',
                          'getforum',
                          array('fid' => $fid));

    if (empty($data)) return;
    if ($data['fstatus'] == 1) {
        $msg = xarML('Forum -- #(1) -- has been locked by administrator', $data['fname']);
        xarExceptionSet(XAR_USER_EXCEPTION, 'LOCKED_FORUM', new SystemException($msg));
        return;
    }
    // Security Check
    if(!xarSecurityCheck('ReadxarBB',1,'Forum',$data['catid'].':'.$data['fid'])) return;
    xarTplSetPageTitle(xarVarPrepForDisplay($data['fname']));


    // Lets deal with the cookie in a more sane manner
    if (xarUserIsLoggedIn()){
        xarSessionSetVar(xarModGetVar('xarbb', 'cookiename') . '_f_' . $fid, time());
        xarSessionSetVar(xarModGetVar('xarbb', 'cookiename') . 'lastvisit', time());
    }

    // Settings and disply
    $data['items']          = array();
    $settings               = unserialize(xarModGetVar('xarbb', 'settings.'.$fid));
    $data['showcats']       = $settings['showcats'];
    $data['xbbname']        = xarModGetVar('themes', 'SiteName');
    // Login
    $data['return_url']      = xarModURL('xarbb', 'user', 'viewforum', array('fid' => $data['fid']));
    $data['submitlabel']     = xarML('Submit');
    // TODO, should be a forum setting
    $hotTopic               = $settings['hottopic'];

    // The user API function is called
    $topics = xarModAPIFunc('xarbb',
                            'user',
                            'getalltopics',
                            array('fid' => $fid,
                                  'startnum' => $startnum,
                                  'numitems' => $settings['topicsperpage']));
    $totaltopics=count($topics);

    $topiclist = array();
    $isuser = array();
    for ($i = 0; $i < $totaltopics; $i++) {
        $topiclist[] = $topics[$i]['tid'];
        $isuser[$topics[$i]['tposter']] = 1;
        $isuser[$topics[$i]['treplier']] = 1;
    }
    $hits = xarModAPIFunc('hitcount',
                          'user',
                          'getitems',
                          array('modname' => 'xarbb',
                                'itemtype' => $fid,
                                'itemids' => $topiclist));
    $userlist = array_keys($isuser);
    $users = array();
    if (count($userlist) > 0) {
        $users = xarModAPIFunc('roles',
                               'user',
                               'getall',
                               array('uidlist' => $userlist));
    }
 
    for ($i = 0; $i < $totaltopics; $i++) {
        $topic = $topics[$i];
        list($topics[$i]['ttitle'],
             $topics[$i]['tpost']) = xarModCallHooks('item',
                                         'transform',
                                         $fid,
                                         array($topic['ttitle'],
                                               $topic['tpost']),
                                         'xarbb',
                                         $fid);

        if (!empty($read)){
            xarSessionSetVar(xarModGetVar('xarbb', 'cookiename') . '_t_' . $topic['tid'], time());
        }
        
        $read_topic = xarSessionGetVar(xarModGetVar('xarbb', 'cookiename') . '_t_' . $topic['tid']);

        if (isset($read_topic)){
            $cookie_time_compare = $read_topic;
        } else {
            $lastvisitsession = xarSessionGetVar(xarModGetVar('xarbb', 'cookiename') . 'lastvisit');
            if (isset($lastvisitsession)){
                $data['lastvisitdate'] = xarSessionGetVar(xarModGetVar('xarbb', 'cookiename') . 'lastvisit');
            } else {
                $data['lastvisitdate'] = time() - 60*60*24;
            }
            $cookie_time_compare = $data['lastvisitdate'];
        }

        $topics[$i]['tpost'] = xarVarPrepHTMLDisplay($topic['tpost']);
        $topics[$i]['comments'] = xarVarPrepHTMLDisplay($topic['treplies']);

        switch(strtolower($topic['tstatus'])) {
            // Just a regular old topic
            case '0':
            default:
                if (($cookie_time_compare > $topic['ttime']) || !xarUserIsLoggedIn()){
                    // More comments than our hottopic setting, therefore should be hot, but not new.
                    if ($topics[$i]['comments'] > $hotTopic){
                        $topics[$i]['timeimage'] = 1;
                    // Else should be a regular old boring topic
                    } else {
                        $topics[$i]['timeimage'] = 2;
                    }
                } else {
                    // OOF, look at this topic, hot and new.
                    if ($topics[$i]['comments'] > $hotTopic){
                        $topics[$i]['timeimage'] = 3;
                    // Else should be a regular old boring topic that has a new post
                    } else {
                        $topics[$i]['timeimage'] = 9;
                    }
                }
                break;
            // Announcement topic
            case '1':

                if (($cookie_time_compare > $topic['ttime']) || !xarUserIsLoggedIn()){
                    $topics[$i]['timeimage'] = 4;
                } else {
                    $topics[$i]['timeimage'] = 5;
                }

                break;
            // Sticky topic
            case '2':
                if (($cookie_time_compare > $topic['ttime']) || !xarUserIsLoggedIn()){
                    $topics[$i]['timeimage'] = 6;
                } else {
                    $topics[$i]['timeimage'] = 7;
                }
                break;
            // Locked
            case '3':
                $topics[$i]['timeimage'] = 8;
                break;
        }

        if (!empty($hits[$topic['tid']])) {
            $topics[$i]['hitcount'] = $hits[$topic['tid']];
        } else {
            $topics[$i]['hitcount'] = 0;
        }

        if (!$topics[$i]['hitcount']) {
            $topics[$i]['hitcount'] = '0';
        } elseif ($topics[$i]['hitcount'] == 1) {
            $topics[$i]['hitcount'] .= ' ';
        } else {
            $topics[$i]['hitcount'] .= ' ';
        }

        if (isset($users[$topic['tposter']])) {
            $topics[$i]['name'] = $users[$topic['tposter']]['name'];
        } else {
            $topics[$i]['name'] = '-';
        }

        // And we need to know who did the last reply

        if ($topics[$i]['comments'] == 0) {
            $topics[$i]['authorid'] = $topic['tposter'];
        } else {
            // TODO FIX THIS FROM COMMENTS
            $topics[$i]['authorid'] = $topic['treplier'];
        }

        if (isset($users[$topics[$i]['authorid']])) {
            $topics[$i]['replyname'] = $users[$topics[$i]['authorid']]['name'];
        } else {
            $topics[$i]['replyname'] = '-';
        }
    }
    sort_topics($topics);
    $data['items'] = $topics;

    // Call the xarTPL helper function to produce a pager in case of there
    // being many items to display.
    $data['pager'] = xarTplGetPager($startnum,
                                    xarModAPIFunc('xarbb', 'user', 'counttopics', array('fid' => $fid)),
                                    xarModURL('xarbb', 'user', 'viewforum', array('startnum' => '%%',
                                                                                  'fid'          => $fid)),
                                    $settings['topicsperpage']);
    $categories = xarModAPIFunc('categories', 'user', 'getcatinfo', array('cid' => $data['catid']));
    $data['catname'] = $categories['name'];

    // Forum Jump
    $data['forums'] = xarModAPIFunc('xarbb',
                                    'user',
                                    'getallforums');
    
    return $data;
}

/**
 *  Function to help sort the topics array by order of importance
 *  @params $topics array topics to be sorted passed in by reference
 *  @return null
 */
function sort_topics(&$topics)
{
    $normal = array();
    $sticky = array();
    $announcements = array();
    
    for($i=0, $max = count($topics); $i<$max; $i++) {
        switch($topics[$i]['tstatus']) {
            case '1':
                $announcements[] =& $topics[$i];
                break;
            case '2':
                $sticky[] =& $topics[$i];
                break;
            case '3':
                $normal[] =& $topics[$i];
                break;
            case '0':
            default:
                $normal[] =& $topics[$i];
                break;
        }
    }
    // merge the arrays and form the new topics array
    $topics = array_merge($announcements,$sticky,$normal);
    // get rid of these since we no longer need them in memory
    unset($announcements,$sticky,$normal);
}
?>
