<?php

/**
 * Update the configuration parameters of the module based on data from the modification form
 * 
 * @author Jon Haworth | jsb
 * @access public 
 * @param $starttime (seconds or hh:mm:ss), $direction (from or to) 
 * @return $convertedtime (hh:mm:ss or seconds)
 * @throws nothing
 * @todo maybe add support for days?
 */
function xarcachemanager_adminapi_convertseconds($args)
{
    extract($args);

    if (!xarSecurityCheck('AdminXarCache')) return;

    $convertedtime = '';
    
    switch($direction) {
        case 'from':
            // convert to hours
            $hours = intval(intval($starttime) / 3600); 
            // add leading 0
            $convertedtime .= str_pad($hours, 2, '0', STR_PAD_LEFT). ':';
            // get the minutes
            $minutes = intval(($starttime / 60) % 60); 
            // then add to $hms (with a leading 0 if needed)
            $convertedtime .= str_pad($minutes, 2, '0', STR_PAD_LEFT). ':';
            // get the seconds
            $seconds = intval($starttime % 60); 
            // add to $hms, again with a leading 0 if needed
            $convertedtime .= str_pad($seconds, 2, '0', STR_PAD_LEFT);
            break;
        case 'to':
            // break apart the time elements
            $elements = explode(':', $starttime);
            // make sure it's all there
            $allelements = array_pad($elements, -3, 0);
            // calculate the total seconds
            $convertedtime = (($allelements[0] * 3600) + ($allelements[1] * 60) + $allelements[2]);
            break;
    }

    return $convertedtime;
    
}

?>
