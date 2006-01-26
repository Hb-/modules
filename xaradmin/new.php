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
 * display form for a new poll
 */
function polls_admin_new()
{

    if (!xarSecurityCheck('AddPolls')) {
        return;
    }

    $data = array();
    $data['authid'] = xarSecGenAuthKey();
    $data['optcount'] = xarModGetVar('polls', 'defaultopts');
    $data['start_date']= time();
    $data['end_date']= NULL;


    $item = array();
    $item['module'] = 'polls';
    $item['itemtype'] = 0;
    $hooks = xarModCallHooks('item', 'new', '', $item);

    if (empty($hooks)) {
        $data['hooks'] = '';
    } elseif (is_array($hooks)) {
        $data['hooks'] = join('', $hooks);
    } else {
        $data['hooks'] = $hooks;
    }

    return $data;
}

?>
