<?php
/**
 * File: $Id$
 * 
 * View a forum topic and replies
 * 
 * @package Xaraya eXtensible Management System
 * @copyright (C) 2003 by the Xaraya Development Team.
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.org
 *
 * @subpackage  xarbb Module
 * @author John Cox
*/

function xarbb_user_viewtopic()
{
   // Get parameters from whatever input we need
    if(!xarVarFetch('startnum', 'id', $startnum,1, XARVAR_NOT_REQUIRED)) return;
    if(!xarVarFetch('tid', 'id', $tid)) return;
    if(!xarVarFetch('view', 'str', $view,'', XARVAR_NOT_REQUIRED)) return;

    // redirect to previous/next topic
    if (!empty($view)) {
        if ($view == 'next') {
            $nextid = xarModAPIFunc('xarbb','user','getnexttopicid',
                                    array('tid' => $tid));
            if (!isset($nextid)) return;
            if (!empty($nextid)) {
                xarResponseRedirect(xarModURL('xarbb','user','viewtopic',
                                              array('tid' => $nextid)));
                return true;
            }
        } elseif ($view == 'previous' || $view == 'prev') {
            $previousid = xarModAPIFunc('xarbb','user','getprevioustopicid',
                                        array('tid' => $tid));
            if (!isset($previousid)) return;
            if (!empty($previousid)) {
                xarResponseRedirect(xarModURL('xarbb','user','viewtopic',
                                              array('tid' => $previousid)));
                return true;
            }
        }
    }

    if(!$topic = xarModAPIFunc('xarbb','user','gettopic',array('tid' => $tid))) return;    

    if ($topic['fstatus'] == 1) {
        $msg = xarML('Forum -- #(1) -- all associated topics have been locked by administrator', $topic['fname']);
        xarExceptionSet(XAR_USER_EXCEPTION, 'LOCKED_FORUM', new SystemException($msg));
        return;
    }

    // Lets deal with the cookie in a more sane manner
    if (xarUserIsLoggedIn()){
        $time    = serialize(time());
        setcookie(xarModGetVar('xarbb', 'cookiename') . '_f_' . $topic['fid'], $time, time()+60*60*24*120, xarModGetVar('xarbb', 'cookiepath'), xarModGetVar('xarbb', 'cookiedomain'), 0);
        // Easier to set a cookie for the last visit than it is
        // roll through all the forums to check the time set.
        setcookie(xarModGetVar('xarbb', 'cookiename') . 'lastvisit', $time, time()+60*60*24*120, xarModGetVar('xarbb', 'cookiepath'), xarModGetVar('xarbb', 'cookiedomain'), 0);
    }

    $settings               = unserialize(xarModGetVar('xarbb', 'settings.'.$topic['fid']));
    $allowhtml              = $settings['allowhtml'];
    if (isset($settings['allowbbcode'])) {
        $allowbbcode  = $settings['allowbbcode'];
    } else {
        $allowbbcode = false;
    }
    $postperpage            = $settings['postsperpage'];

    // Security Check
    if(!xarSecurityCheck('ReadxarBB',1,'Forum',$topic['catid'].':'.$topic['fid'])) return;

    // The user API function is called and returns all forum and topic data
    //<jojodee> Do we need to call this again?
    $data=$topic; //to cover us for any use of $data

    $data['pager'] = '';

    // Need to get this working for new itemtypes
    list($data['transformedtext'],
         $data['transformedtitle']) = xarModCallHooks('item',
                                                      'transform',
                                                       $tid,
                                                 array($data['tpost'],
                                                       $data['ttitle']),
                                                       'xarbb',
                                                       $data['fid']);

    if ($allowhtml) {
        $data['tpost'] = xarVarPrepHTMLDisplay($data['tpost']);
        $data['ttitle'] = xarVarPrepHTMLDisplay($data['ttitle']);
    } else {
        $data['tpost'] = xarVarPrepForDisplay($data['tpost']);
        $data['ttitle'] = xarVarPrepForDisplay($data['ttitle']);
    }
    xarTplSetPageTitle(xarVarPrepForDisplay($data['ttitle']));
    // The user API function is called
    $posterdata = xarModAPIFunc('roles',
                                'user',
                                'get',
                                array('uid' => $data['tposter']));

    // The user API function is called
    $topiccount = xarModAPIFunc('xarbb',
                                'user',
                                'countposts',
                                array('uid' => $data['tposter']));

    // Build up the list of posters
    $isposter = array();
    $isposter[$data['tposter']] = 1;

    // Get the individual posts for the topic
    $header['modid']        = xarModGetIDFromName('xarbb');
    $header['objectid']     = $tid;

    $data['items'] = array();

    $comments = xarModAPIFunc('comments',
                              'user',
                              'get_multiple',
                              array('modid'       => $header['modid'],
                                    'objectid'    => $header['objectid'],
                                    'startnum' => $startnum,
                                    'numitems' => $postperpage));
/*
    $todolist = array();
    $todolist['transform'] = array();
*/
    $totalcomments=count($comments);
    for ($i = 0; $i < $totalcomments; $i++) {
        $comment = $comments[$i];

        if ($allowhtml){
            $comments[$i]['xar_text']=xarVarPrepHTMLDisplay($comments[$i]['xar_text']);
            $comments[$i]['xar_title']=xarVarPrepHTMLDisplay($comments[$i]['xar_title']);
        } else {
            $comments[$i]['xar_text']=xarVarPrepForDisplay($comments[$i]['xar_text']);
            $comments[$i]['xar_title']=xarVarPrepForDisplay($comments[$i]['xar_title']);
        }

/*
        $todolist['transform'][] = $i . 'xar_text';
        $todolist[$i.'xar_text'] =& $comments[$i]['xar_text'];
        $todolist['transform'][] = $i . 'xar_title';
        $todolist[$i.'xar_title'] =& $comments[$i]['xar_title'];
*/
        // This has to come after the html call.
        list($comments[$i]['xar_text'],
             $comments[$i]['xar_title']) = xarModCallHooks('item',
                                                           'transform',
                                                            $tid,
                                                            array($comment['xar_text'],
                                                                  $comment['xar_title']),
                                                            'xarbb',
                                                            $data['fid']);

// TODO: retrieve all post counts at once ?
        // The user API function is called
        $comments[$i]['usertopics'] = xarModAPIFunc('xarbb',
                                                    'user',
                                                    'countposts',
                                                    array('uid' => $comment['xar_uid']));

/*
// TODO: retrieve all user info at once ?
        // The user API function is called
        $comments[$i]['userdata'] = xarModAPIFunc('roles',
                                             'user',
                                             'get',
                                              array('uid' => $comment['xar_uid']));

        //format reply poster's registration date
        //$comments[$i]['commenterdate'] = xarLocaleFormatDate('%Y-%m-%d',$comments[$i]['userdata']['date_reg']);
        //Add datestamp so users can format in template, existing templates are still OK
        $comments[$i]['commenterdatestamp'] =$comments[$i]['userdata']['date_reg'];
*/

        $isposter[$comment['xar_uid']] = 1;

        //format the post reply date consistently with topic post date
        //$comments[$i]['xar_date']=xarLocaleFormatDate('%Y-%m-%d %H:%M:%S',$comments[$i]['xar_datetime']);
        //Add datestamp so users can format in template, existing templates are still OK
        $comments[$i]['xar_datestamp']=$comments[$i]['xar_datetime'];
    }

    $data['posterlist'] = array_keys($isposter);

/*
    $todolist = xarModCallHooks('item',
                                'transform',
                                $tid,
                                $todolist,
                                'xarbb',
                                $data['fid']);
*/

    if (count($data['posterlist']) > 0) {
/* the performance issue seems to be in comments author count, really, so this is not a solution
        $data['usertopics'] = xarModAPIFunc('xarbb','user','countpostslist',
                                            array('uidlist' => $data['posterlist']));
        // TODO: support of legacy templates - get rid of this later on
        for ($i = 0; $i < $totalcomments; $i++) {
            $uid = $comments[$i]['xar_uid'];
            if (isset($data['usertopics'][$uid])) {
                $comments[$i]['usertopics'] = $data['usertopics'][$uid];
            } else {
                $comments[$i]['usertopics'] = 0;
            }
        }
*/
        $data['userdata'] = xarModAPIFunc('roles','user','getall',
                                          array('order' => 'uid', 'uidlist' => $data['posterlist']));

        for ($i = 0; $i < $totalcomments; $i++) {
            $uid = $comments[$i]['xar_uid'];
            if (isset($data['userdata'][$uid])) {
                $comments[$i]['commenterdatestamp'] = $data['userdata'][$uid]['date_reg'];
            } else {
                $comments[$i]['commenterdatestamp'] = 0;
            }
        }
    }

    $data['items'] = $comments;

    // End individual Replies

    // adjust the display format
    // <jojodee> OK - rather than change each post reply time formats above
    // Let's bring this reg date into line with future locale use
    //$thisdate = new xarDate();
    //if(is_numeric($posterdata['date_reg'])) {
    //    $thisdate->setTimestamp($posterdata['date_reg']);
    // $regdate=xarLocaleFormatDate('%Y-%m-%d',$posterdata['date_reg']);
    //Add datestamp so users can format in template, existing templates are still OK
    $regdatestamp=$posterdata['date_reg'];
    //}
    // else {
    //     $thisdate->DBtoTS($posterdata['date_reg']);
    // }

    //Forum Name and Links
    // $data['fname']      = $forumdata['fname']; //No need to reassign here
    $data['postername'] = $posterdata['name'];
    // $data['posterdate'] = $regdate;
    $data['posterdatestamp'] = $regdatestamp;
    $data['usertopics'] = $topiccount;
    $data['xbbname']    = xarModGetVar('themes', 'SiteName');
    
    //Pager data - to prevent topic should on every additional pager page
    $data['startnum'] = $startnum;

    // Images
    // These are dependant on the time functions being changed
    $data['newtopic']    = '<img src="' . xarTplGetImage('new/post.gif') . '" alt="'.xarML('New topic').'" />';
    $data['newreply']    = '<img src="' . xarTplGetImage('new/reply.gif') . '" alt="'.xarML('New reply').'" />';
    $data['quoteimg']    = '<img src="' . xarTplGetImage('new/icon_quote.gif') . '" alt="'.xarML('Quote').'" />';
    $data['editimg']     = '<img src="' . xarTplGetImage('new/icon_edit.gif') . '" alt="'.xarML('Edit').'" />';
    $data['deleteimg']   = '<img src="' . xarTplGetImage('new/icon_delete.gif') . '" alt="'.xarML('Delete').'" />';
    $data['ipimg']       = '<img src="' . xarTplGetImage('new/icon_ip.gif') . '" alt="'.xarML('IP').'" />';
    $data['closed']      = '<img src="' . xarTplGetImage('new/reply-locked.gif') . '" alt="'.xarML('Closed Topic').'" />';

    $item = array();
    $item['module'] = 'xarbb';
    $item['itemtype'] = $data['fid']; // Forum Topics
    $item['itemid'] = $tid;
 

    // for display hooks, we need to pass a returnurl
    $item['returnurl'] = xarModURL('xarbb','user','viewtopic',
                                   array('tid' => $tid,
                                         'startnum'=>$startnum));

    $data['hooks'] = xarModCallHooks('item','display',$tid,$item);

    // Let's handle the changelog a little differently
    // and add a link in the topic itself.
    if (isset($data['hooks']['changelog'])){
        $data['changelog']  = true;
        $data['hooks']['changelog'] = '';
    }
    //pass the bbcodeswitch
    $data['allowbbcode'] = $allowbbcode;

    // Let's suppress the hitcount hook from showing.
    $data['hooks']['hitcount'] = '';

    // Generate authid only if the current user can delete topics and replies
    if (xarSecurityCheck('DeletexarBB',0)) {
        // Note : this make the page un-cacheable
        $data['authid'] = xarSecGenAuthKey('xarbb');
    } else {
        $data['authid'] = '';
    }

    // Call the xarTPL helper function to produce a pager in case of there
    // being many items to display.
    $data['pager'] = xarTplGetPager($startnum,
                                    xarModAPIFunc('comments', 'user', 'get_count',
                                    array('modid'       => $header['modid'],
                                          'objectid'    => $header['objectid'])),

                                    xarModURL('xarbb', 'user', 'viewtopic', array('startnum' => '%%',
                                                                                  'tid'          => $tid)),
                                    $postperpage);

    // Return the template variables defined in this function
    $categories = xarModAPIFunc('categories', 'user', 'getcatinfo', array('cid' => $data['catid']));
    $data['catname'] = $categories['name'];

    return $data;
}
?>
