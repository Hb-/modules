<?php
/*
 *
 * Polls Module
 *
 * @package Xaraya eXtensible Management System
 * @copyright (C) 2005 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage polls
 * @author Jim McDonalds, dracos, mikespub et al.
 */
/**
 * initialise block
 */
function polls_pollblock_init()
{
    // Security
    // xarSecAddSchema('Polls:Pollblock:', 'Block title::');
    return true;
}

/**
 * get information on block
 */
function polls_pollblock_info()
{
    // Values
    return array('text_type' => 'Poll',
                 'module' => 'polls',
                 'text_type_long' => 'Display poll',
                 'allow_multiple' => true,
                 'form_content' => false,
                 'form_refresh' => false,
                 'show_preview' => true);
}

/**
 * display block
 */
function polls_pollblock_display($blockinfo)
{
    // Security check

    if (!xarSecurityCheck('ViewPollBlock',0,'Pollblock',"$blockinfo[title]:$blockinfo[type]")) return;

    // Get variables from content block
    $vars = @unserialize($blockinfo['content']);

    // Get item
    if (isset($vars['pid']) && ($vars['pid'] > 0)) {
        $poll = xarModAPIFunc('polls', 'user', 'get', array('pid' => $vars['pid']));
    } else {
        $poll = xarModAPIFunc('polls', 'user', 'get', array('act' => 1));
    }

    if (!$poll) {
        // Poll didn't exist, or something.  We're only a block so
        // let's not worry about it too much
        return;
    }

    // Permissions check
    if (!xarSecurityCheck('ListPolls',0,'Polls',"$poll[title]:$poll[type]")) return;

    // Create output object
    $data = array();

    // Block content
    $data['title'] = $poll['title'];
    $data['type'] = $poll['type'];
    $data['open'] = $poll['open'];
    $data['start_date'] = $poll['start_date'];
    $data['buttonlabel'] = xarML('Vote');
    $data['previewresults'] = xarModGetVar('polls', 'previewresults');
    $data['showtotalvotes'] = xarModGetVar('polls', 'showtotalvotes');
    $data['canvote'] = xarModAPIFunc('polls', 'user', 'usercanvote', array('pid' => $poll['pid']));
    $data['bid'] = $blockinfo['bid'];

    // See if user is allowed to vote
    if ((xarSecurityCheck('VotePolls',0,'Polls',"$poll[title]:$poll[type]")) &&
                         ($data['canvote'])){
        // They have not voted yet, display voting options

        $data['authid'] = xarSecGenAuthKey('polls');
        $data['pid'] =  $poll['pid'];
        $data['returnurl'] = xarServerGetCurrentURL();
        $data['options'] = $poll['options'];
        $data['canvote'] = 1;
    } else {
        // They have voted, display current results
        $barscale = xarModGetVar('polls', 'barscale');
        $imggraph = xarModGetVar('polls', 'imggraph');
        $data['imggraph'] = ($imggraph == 1 || $imggraph == 3)?1:0;
        $data['canvote'] = 0;

        for ($i=1; $i<=$poll['opts']; $i++) {
            $row = array();
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
            $row['barwidth'] = (int)($percentage);

            $data['options'][$i] = $row;

        }
    }
    $data['resultsurl'] = xarModURL('polls',
                          'user',
                          'results',
                          array('pid' => $poll['pid']));
    $data['resultslabel'] = xarML('Results');

    // Return content
    $blockinfo['content'] = $data;
    return $blockinfo;
}

/**
 * modify block settings
 */
function polls_pollblock_modify($blockinfo)
{
    // Create output object
    $data = array();

    // Get current content
    $vars = unserialize($blockinfo['content']);

    // Defaults
    if (!isset($vars['pid'])) {
        $vars['pid'] = -1;
    }

    // Row
    $data['polls'] = array();
    $polls = xarModAPIFunc('polls', 'user', 'getall',
                           array('modid' => xarModGetIDFromName('polls')));
    $vars['polls'] = array();
    $vars['sel_pid'] = $vars['pid'];
    $vars['polls'][] = array('pid' => -1,
                             'name' => xarML('Latest Poll'));

    foreach ($polls as $poll) {
        $vars['polls'][] = array('pid' => xarVarPrepHTMLDisplay($poll['pid']),
                                'name' => xarVarPrepHTMLDisplay($poll['title']));
    }

    $vars['blockid'] = $blockinfo['bid'];
    $content = xarTplBlock('polls', 'pollAdmin', $vars);

    return $content;

}

/**
 * update block settings
 */
function polls_pollblock_update($blockinfo)
{
    xarVarFetch('pid', 'id', $vars['pid'], -1, XARVAR_DONT_SET);

    // Defaults
    /* if (empty($vars['pid'])) {
        $vars['pid'] = -1;
    }
*/

    $blockinfo['content'] = serialize($vars);

    return $blockinfo;
}

?>
