<?php
/**
 * New event hook
 *
 * @package julian
 * @copyright (C) 2005 by the Xaraya Development Team.
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage Julian
 * @link  link to information for the subpackage
 * @author Julian development Team
 */

/**
 * Provide GUI for new hook
 *
 * enter date/time for a new item - hook for ('item','new','GUI')
 *
 * @author  Julian Development Team, JornB MichelV. <michelv@xarayahosting.nl>
 * @access  public
 * @param   $extrainfo
 * @return  array tplinfo
 * @todo    MichelV. <#>
 */
function julian_user_newhook($args)
{
    extract($args);

    if (!isset($extrainfo)) {
        $extrainfo = array();
    }

    $data = array();

    $event_startdate = time();
    $event_enddate = time();

   $data['event_month'] = 1;
   $data['event_year'] = 1;
   $data['event_day'] = 1;

   $data['event_allday'] = true;

   list($data['event_year'],   $data['event_month'],   $data['event_day'])    = explode("-",date("Y-m-d",$event_startdate));
   list($data['event_endyear'],$data['event_endmonth'],$data['event_endday']) = explode("-",date("Y-m-d",$event_enddate));

   // Building start hour options (default = 12)
   $start_hour_options = '';
   for($i = 1;$i <= 12; $i++)
   {
     $j = str_pad($i,2,"0",STR_PAD_LEFT);
     $start_hour_options.='<option value="'.$i.'"';
     if ($i == 12) $start_hour_options.= " SELECTED";
      $start_hour_options.='>'.$j.'</option>';
   }
   $data['start_hour_options'] = $start_hour_options;

    // Building duration minute options
    // Get the interval
    $StartMinInterval = xarModGetVar('julian', 'StartMinInterval');
    if ($StartMinInterval == 1) {
        $sminend = 60;
    } elseif ($StartMinInterval == 5) {
        $sminend = 56;
    } elseif ($StartMinInterval == 10) {
        $sminend = 51;
    } elseif ($StartMinInterval == 15) {
        $sminend = 46;
    }

    $start_minute_options = '';
    for($i = 0;$i < $sminend; $i = $i + $StartMinInterval) {
        $j = str_pad($i,2,"0",STR_PAD_LEFT);
        $start_minute_options.='<option value="'.$j.'"';
        $start_minute_options.='>'.$j.'</option>';
    }
    $data['start_minute_options'] = $start_minute_options;

    // Building duration minute options
    // Get the interval
    $DurMinInterval = xarModGetVar('julian', 'DurMinInterval');
    if ($DurMinInterval == 1) {
        $minend = 60;
    } elseif ($DurMinInterval == 5) {
        $minend = 56;
    } elseif ($DurMinInterval == 10) {
        $minend = 51;
    } elseif ($DurMinInterval == 15) {
        $minend = 46;
    }

    $dur_minute_options = '';
    for($i = 0;$i < $minend; $i = $i + $DurMinInterval) {
        $j = str_pad($i,2,"0",STR_PAD_LEFT);
        $dur_minute_options.='<option value="'.$j.'"';
        $dur_minute_options.='>'.$j.'</option>';
    }
    $data['dur_minute_options'] = $dur_minute_options;

    // Start AM/PM (default = AM)
    $data['event_startampm'] = false;    // true=PM, false=AM

   // Building duration hour options (default = 1)
   $dur_hour_options = '';
   for($i = 0;$i <= 24; $i++)
   {
     $j = str_pad($i,2,"0",STR_PAD_LEFT);
     $dur_hour_options.='<option value="'.$i.'"';
     if ($i == 1) $dur_hour_options.= " selected";
     $dur_hour_options.='>'.$j.'</option>';
   }
   $data['dur_hour_options'] = $dur_hour_options;

   // Type of recurrence (0=none, 1=every, 2=on)
    $data['event_repeat'] = 0;

    // Repeat-every defaults.
    $data['event_repeat_every_type'] = 0;    // frequency unit (day=1, week=2, month=3, year=4)
    $data['event_repeat_every_freq'] = '';    // frequency (every x time units)

    // Repeat-on defaults
    $data['event_repeat_on_day'] = 0;    // day of the week
    $data['event_repeat_on_num'] = 0;    // instance within month (1st, 2nd, ..., last=5)
    $data['event_repeat_on_freq'] = '';    // frequency (every x months)

    return xarTplModule('julian','user','edithook',$data);
}

?>
