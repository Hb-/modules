<?php
/*
 *
 * Polls Module
 *
 * @package Xaraya eXtensible Management System
 * @copyright (C) 2005 The Digital Development Foundation
 * @license GPL <http://www.gnu.org/licenses/gpl.html>
 * @link http://www.xaraya.com
 *
 * @subpackage polls
 * @author Jim McDonalds, dracos, mikespub et al.
 */

/**
 * show results
 * This is a standard function to provide detailed informtion on a single item
 * available from the module.
 */
function polls_user_results($args)
{
    // Get parameters
     if (!xarVarFetch('pid', 'id', $pid, XARVAR_DONT_SET)) return;

    extract($args);

    if(!xarSecurityCheck('ViewPolls')){
        return;
    }

    if (!isset($pid)) {
        $msg = xarML('Missing poll ID');
        xarErrorSet(XAR_USER_EXCEPTION,
                    'BAD_DATA',
                     new DefaultUserException($msg));
        return;
    }
    $canvote = xarModAPIFunc('polls', 'user', 'usercanvote', array('pid' => $pid));
    if(!xarModGetVar('polls', 'previewresults') && $canvote){
        xarResponseRedirect(xarModURL('polls', 'user', 'display',
                               array('pid' => $pid)));
    }


    $data = array();

    // Get item
    $poll = xarModAPIFunc('polls',
                           'user',
                           'get',
                           array('pid' => $pid));

    if (!$poll) {
        $msg = xarML('Error retrieving Poll data');
        xarErrorSet(XAR_USER_EXCEPTION,
                    'BAD_DATA',
                     new DefaultUserException($msg));
        return;
    }

    if ($canvote && !xarSecurityCheck('VotePolls',0,'Polls',"$poll[title]:$poll[type]")) {
        $canvote = 0;
    }

    $data['pid'] = $poll['pid'];
    $data['title'] = $poll['title'];
    $data['private'] = $poll['private'];
    $data['open'] = $poll['open'];

    // Number of participants
    $data['totalvotes'] = $poll['votes'];
    $data['options'] = array();
    $data['voteurl'] = xarModURL('polls', 'user', 'display',
                               array('pid' => $pid));
    $data['listurl'] = xarModURL('polls', 'user', 'list',
                               array('pid' => $pid));

    $data['canvote'] = $canvote;
    $barscale = xarModGetVar('polls', 'barscale');
    $imggraph = xarModGetVar('polls', 'imggraph');
    $data['imggraph'] = ($imggraph >= 2)?1:0;
    $data['showtotalvotes'] = xarModGetVar('polls', 'showtotalvotes');
    $voteinterval = xarModGetVar('polls', 'voteinterval');

    if($voteinterval == 86400){
        $data['votelimit'] = xarML('per day');
    }
    elseif($voteinterval == 604800){
        $data['votelimit'] = xarML('per week');
    }
    elseif($voteinterval == 2592000){
        $data['votelimit'] = xarML('per month');
    }
    else{
        $data['votelimit'] = xarML('per user');
    }

    // Poll information
    for ($i=1; $i<=$poll['opts']; $i++) {
        if ($poll['votes'] == 0) {
            $percentage = 0;
        } else {
            $percentage = (int)($poll['options'][$i]['votes']*1000/$poll['votes']);
            $percentage /= 10;
        }

        $row = array();
        $row['name'] = $poll['options'][$i]['name'];
        $row['votes'] = $poll['options'][$i]['votes'];
        $row['percentage'] = $percentage;
        $row['barwidth'] = (int)$percentage * $barscale;
        $data['options'][$i] = $row;
    }

    if ($poll['modid'] == xarModGetIDFromName('polls')) {
        // Let hooks know we're displaying a poll, so they can provide us with related stuff
        $item = $poll;
        $item['module'] = 'polls';
        $item['returnurl'] = xarModURL('polls','user', 'results', array('pid' => $poll['pid']));
        $hooks = xarModCallHooks('item','display', $poll['pid'], $item);

        $data['hookoutput'] = join('',$hooks);
    } else {
        $data['hookoutput'] = '';
    }

    // Return output
    return $data;
}

?>
