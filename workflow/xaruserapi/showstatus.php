<?php

/**
 * show the current status of "your" instances (called via <xar:workflow-status tag)
 * 
 * @author mikespub
 * @access public 
 */
function workflow_userapi_showstatus($args)
{
// TODO: keep track of instances from anonymous visitors via session ?
    if (!xarUserIsLoggedIn()) {
        return '';
    }

    // Security Check
    if (!xarSecurityCheck('ReadWorkflow',0)) {
        return '';
    }

// Common setup for Galaxia environment
    include('modules/workflow/tiki-setup.php');
    $tplData = array();

    include (GALAXIA_LIBRARY.'/ProcessMonitor.php');

    if (empty($user)) {
        $user = xarUserGetVar('uid');
    }

    // retrieve the instances for which you're the owner
    $where = "owner=$user";
    if (!empty($args['status'])) {
        $where .= " and gi.status='" . $args['status'] . "'";
    }
    if (!empty($args['actstatus'])) {
        $where .= " and gia.status='" . $args['actstatus'] . "'";
    }
    if (!empty($args['pId'])) {
        $where .= " and gp.pId='" . $args['pId'] . "'";
    }
    $items = $processMonitor->monitor_list_instances(0, -1, 'started_asc', '', $where, array());

    $seenlist = xarModGetUserVar('workflow','seenlist');
    if (!empty($seenlist)) {
        $seen = explode(';',$seenlist);
    } else {
        $seen = array();
    }
    $tplData['items'] = array();
    foreach ($items['data'] as $index => $info) {
        if (in_array($info['instanceId'],$seen)) continue;
        $items['data'][$index]['started'] = xarLocaleGetFormattedDate('medium',$info['started']) . ' '
                                            . xarLocaleGetFormattedTime('short',$info['started']);
        $items['data'][$index]['userId'] = $info['user'];
        if (is_numeric($info['user'])) {
            $items['data'][$index]['user'] = xarUserGetVar('name',$info['user']);
        }
        $tplData['items'][] = $items['data'][$index];
    }

    $tplData['userId'] = $user;

    if (!empty($args['layout'])) {
        $tplData['layout'] = $args['layout'];
    }

    // URL to return to if some action is taken - use urlencode() here
    $tplData['return_url'] = urlencode(xarServerGetCurrentURL());

    if (!empty($args['template'])) {
        return xarTplModule('workflow', 'user', 'showstatus', $tplData, $args['template']);
    } else {
        return xarTplModule('workflow', 'user', 'showstatus', $tplData);
    }
}

?>
