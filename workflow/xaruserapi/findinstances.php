<?php

/**
 * find instances with a certain status, activityId and/or max. started date
 * 
 * @author mikespub
 * @access public 
 */
function workflow_userapi_findinstances($args)
{
// Common setup for Galaxia environment
    include('modules/workflow/tiki-setup.php');
    include_once (GALAXIA_LIBRARY.'/ProcessMonitor.php');

    extract($args);
    if (!isset($status)) {
        $status = 'active';
    }
    if (!isset($actstatus)) {
        $actstatus = 'running';
    }

    $where = '';
    $wheres = array();

    if (!empty($status)) {
        $wheres[] = "gi.status='" . $status . "'";
    }
    if (!empty($actstatus)) {
        $wheres[] = "gia.status='" . $actstatus . "'";
    }
    if (!empty($activityId)) {
        $wheres[] = "gia.activityId=" . $activityId;
    }
    if (!empty($max_started)) {
        $wheres[] = "gi.started <= " . $max_started;
    }

    $where = implode(' and ', $wheres);

    $items = $processMonitor->monitor_list_instances(0, -1, 'instanceId_asc', '', $where, array());

    if (isset($items) && isset($items['data'])) {
        return $items['data'];
    } else {
        return array();
    }
}

?>
