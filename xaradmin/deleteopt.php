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
 * delete a poll option
 */
function polls_admin_deleteopt()
{
    // Start output
    $data = array();

    // Get parameters
    if (!xarVarFetch('pid', 'id', $pid, XARVAR_DONT_SET)) return;
    if (!xarVarFetch('opt', 'int:0:', $opt, XARVAR_DONT_SET)) return;
    if (!xarVarFetch('votes', 'int:0:', $votes, 0,  XARVAR_DONT_SET)) return;
    if (!xarVarFetch('confirm', 'isset', $confirm, '', XARVAR_NOT_REQUIRED)) return;

    if ((!isset($pid) || !isset($opt)) && xarCurrentErrorType() != XAR_NO_EXCEPTION) return; // throw back

    $poll = xarModAPIFunc('polls',
                           'user',
                           'get',
                           array('pid' => $pid));

    if (!isset($poll) && xarCurrentErrorType() != XAR_NO_EXCEPTION) return; // throw back


    if (!xarSecurityCheck('EditPolls',1,'Polls',"$poll[title]:$poll[type]")) {
        return;
    }

    // Check that option exists
    if (!isset($poll['options'][$opt])) {
        xarErrorSet(XAR_SYSTEM_EXCEPTION, 'BAD_DATA');
        return;
    }

    // Check for confirmation
    if (empty($confirm)) {
        // No confirmation yet - get one

        $data['polltitle'] = $poll['title'];
        $data['pid'] = $pid;
        $data['option'] = $poll['options'][$opt]['name'];
        $data['opt'] = $opt;
        $data['confirm'] = 1;
        $data['warning'] = '';
        $data['authid'] = xarSecGenAuthKey();

        if (($poll['type'] == 'single') &&
            ($poll['options'][$opt]['votes'] != 0)) {
            $data['warning'] = xarML('This option has votes.  Delete anyway?');
        }
        $data['cancelurl'] = xarModURL('polls', 'admin', 'modify', array('pid' => $pid));
        return $data;
    }

    // Confirm authorisation code
    if (!xarSecConfirmAuthKey()) return;

    // Pass to API
    if (xarModAPIFunc('polls',
                     'admin',
                     'deleteopt',
                     array('pid' => $pid,
                           'opt' => $opt,
                           'votes' => $votes))) {
        // Success
        xarSessionSetVar('statusmsg', xarML('Deleted option'));

    }

    xarResponseRedirect(xarModURL('polls', 'admin', 'modify', array('pid' => $pid)));

    return true;
}

?>
