<?php
// $Id:$

function timezone_user_list_timezones()
{
    $timezones = array();
    include('modules/timezone/tzdata.php');
    while(list($k,$v) = each($Zones)) {
        array_push($timezones,$k);
    }
    while(list($k,$v) = each($Links)) {
        array_push($timezones,$k);
    }
    // get rid of the large arrays from memory
    unset($Zones,$Rules,$Leaps,$Links);
    // sort the array by name
    sort($timezones);
    // return the sorted array for display
    return array('timezones'=>$timezones);
}

function mydump(&$var) 
{
    if(is_array($var)) {
        echo '<pre>'; print_r($var); echo '</pre>';
    } elseif(is_object($var)) {
        echo '<pre>'; print_r($var); echo '</pre>';
    } else {
        echo '<pre>'; echo $var; echo '</pre>';
    }
    echo "\n\n";
}

?>
