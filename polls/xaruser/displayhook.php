<?php

/**
 * item display hook for polls
 * @param $args['objectid'] ID of the item this poll is for
 * @param $args['extrainfo'] optional item type and URL to return to if user chooses to vote
 */
function polls_user_displayhook($args)
{
    extract($args);

    if (empty($objectid)) {
        return '';
    }

    $data = array();
    $data['objectid'] = $objectid;

    $itemtype = 0;
    if (isset($extrainfo) && is_array($extrainfo)) {
        if (isset($extrainfo['itemtype']) && is_numeric($extrainfo['itemtype'])) {
            $itemtype = $extrainfo['itemtype'];
        }
        if (isset($extrainfo['returnurl']) && is_string($extrainfo['returnurl'])) {
            $data['returnurl'] = $extrainfo['returnurl'];
        }
        if (isset($extrainfo['module']) && is_string($extrainfo['module'])) {
            $modname = $extrainfo['module'];
        }
    } else {
        $data['returnurl'] = $extrainfo;
    }

    if (empty($modname)) {
        $modname = xarModGetName();
    }
    $args['modname'] = $modname;
    $args['itemtype'] = $itemtype;

    // Run API function
    $poll = xarModAPIFunc('polls',
                          'user',
                          'gethooked',
                          $args);

    if (!$poll) {
        return '';
    }
    $pid = $poll['pid'];

    $data['title'] = $poll['title'];
    if (empty($data['returnurl'])) {
        $data['returnurl'] = xarServerGetCurrentURL();
    }

    // See if user is allowed to vote
    if (xarSecurityCheck('VotePolls',0,'All',"$poll[title]:All:$poll[pid]")) {
        if ((xarModAPIFunc('polls', 'user', 'usercanvote', array('pid' => $pid)))) {
            // They have not voted yet, display voting options
            $data['canvote'] = 1;
            $data['type'] = $poll['type'];
            $data['private'] = $poll['private'];
            $data['resultsurl'] = xarModURL('polls',
                                  'user',
                                  'resultshook',
                                  array('pid' => $poll['pid']));
            $data['previewresults'] = xarModGetVar('polls', 'previewresults');
        
            $data['authid'] = xarSecGenAuthKey('polls');
            $data['pid'] =  $poll['pid'];
            $data['options'] = $poll['options'];
            $data['callingmod'] = $modname;
        }
        else {
            // They have voted, display current results
            return xarModFunc('polls',
                              'user',
                              'resultshook',
                              array('pid' => $pid,
                                    'returnurl' => $data['returnurl']));
        }
    }
    else {
        $data['canvote'] = 0;
    }

/* no hook calls inside hook calls :-) */

    $data['buttonlabel'] = xarML('Vote');

    // Return output
    return $data;
}

?>
