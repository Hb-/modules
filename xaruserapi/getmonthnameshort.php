<?php
// $Id: getmonthnameshort.php,v 1.2 2003/06/24 20:44:47 roger Exp $

function calendar_userapi_getMonthNameShort($args)
{
    extract($args); unset($args);
    if(!isset($month)) $month = date('m');
    
    // make sure we have a valid month value
    if(!xarVarValidate('int:1:12',$month)) {
        return;
    }
    $c = xarModAPIFunc('calendar','user','factory','calendar');
    return $c->MonthShort($month);
}

?>
