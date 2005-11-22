<?php
/**
 * Get all events.
 *
 * @package Xaraya eXtensible Management System
 * @copyright (C) 2004 by Metrostat Technologies, Inc.
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.metrostat.net
 *
 * @subpackage Julian
 * initial template: Roger Raymond
 * @author Jodie Razdrh/John Kevlin/David St.Clair
 */

/**
 * Get all Julian Calendar Event items.
 *
 * This functions returns an array with a listing of events. The events
 * are not formatted for display. When a calendar oriented listing is needed, 
 * use xaruser-getall.php
 *
 * @param $args an array of arguments
 * @param $args['startnum'] start with this item number (default 1)
 * @param $args['numitems'] the number of items to retrieve (default -1 = all)
 * @param $args['sortby'] sort by 'date', 'eventName', 'eventCat', 'eventLocn', 'eventCont' or 'eventFee'
 * @param $args['external'] retrieve events marked external (1=true, 0=false) - ToDo:
 * @param $args['orderby'] order by 'ASC' or 'DESC' (default = ASC)
 * @param $args['catid'] int Category ID
 * @return array of items, or false on failure
 * @raise BAD_PARAM, DATABASE_ERROR, NO_PERMISSION
 */
function julian_userapi_getevents($args)
{
    // Get arguments
    extract($args);

    // Optional arguments.
    if(!isset($startnum)) {
        $startnum = 1;
    }

    if (!isset($numitems)) {
        $numitems = -1;
    }

    if (!isset($sortby)) {
        $sortby = 'eventDate';
    }

    if (!isset($orderby)) {
        $orderby = 'ASC';
    }

    if (!isset($startdate)) {
        $startdate = date('Ymd');
    }

    if (!isset($enddate)) {
        $yearend = (date('Y') + 1);
        $enddate = $yearend . date('md');
    }

    // Argument check.
    $invalid = array();
    if (!isset($startnum) || !is_numeric($startnum)) {
        $invalid[] = 'startnum';
    }
    if (!isset($numitems) || !is_numeric($numitems)) {
        $invalid[] = 'numitems';
    }

    if (count($invalid) > 0) {
        $msg = xarML('Invalid #(1) for #(2) function #(3)() in module #(4)',
                    join(', ',$invalid), 'userapi', 'getevents', 'julian');
        xarErrorSet(XAR_USER_EXCEPTION, 'MISSING_DATA', new DefaultUserException($msg));
        return;
    }

    $items = array();

    // Security check.
    if (!xarSecurityCheck('Viewjulian')) return;

    // Load categories API.
    // Needed?
    if (!xarModAPILoad('categories', 'user')) {
        $msg = xarML('Unable to load #(1) #(2) API','categories','user');
        xarErrorSet(XAR_SYSTEM_EXCEPTION, 'MODULE_DEPENDENCY', new SystemException($msg));
        return false;
    }

    // Get database setup.
    $dbconn =& xarDBGetConn();
    // Get database tables.
    $xartable =& xarDBGetTables();
    // Set Events Table and Column definitions.
    $event_table = $xartable['julian_events'];

    // Get items.
    $query = "SELECT DISTINCT $event_table.event_id,
                     $event_table.summary,
                     $event_table.description,
                     $event_table.street1,
                     $event_table.street2,
                     $event_table.city,
                     $event_table.state,
                     $event_table.zip,
                     $event_table.email,
                     $event_table.phone,
                     $event_table.location,
                     $event_table.url,
                     $event_table.contact,
                     $event_table.organizer,
                     $event_table.dtstart,
                     if($event_table.recur_until LIKE '0000%','',DATE_FORMAT($event_table.recur_until,'%Y:%m %d')),
                     $event_table.due,
                     $event_table.duration,
                     $event_table.rrule,
                     $event_table.isallday,
                     $event_table.fee";
              
    // Select on categories
    if (xarModIsHooked('categories','julian')) {
        // Get the LEFT JOIN ... ON ...  and WHERE parts from categories
        $categoriesdef = xarModAPIFunc('categories','user','leftjoin',
                                       array('modid' => 
                                              xarModGetIDFromName('julian'),
                                             'catid' => $catid));
        $query .= " FROM ( $event_table
                  LEFT JOIN $categoriesdef[table]
                  ON $categoriesdef[field] = event_id )
                  $categoriesdef[more]
                  WHERE $categoriesdef[where] ";
    } else {
        $query .= " FROM $event_table "; 
    }
    
    if (xarModIsHooked('categories','julian') && (!empty($startdate))&& (!empty($enddate))) {
        $query .= " AND ";
    }

    if ((!empty($startdate))&& (!empty($enddate))){
        $query .= " WHERE DATE_FORMAT($event_table.dtstart,'%Y%m%d') >= $startdate AND DATE_FORMAT($event_table.dtstart,'%Y%m%d') <= $enddate";
    }

    if (isset($sortby)) {
        switch ($sortby) {
            case 'eventDate':
                $query .= " ORDER BY $event_table.dtstart $orderby";
                break;
            case 'eventName':
                $query .= " ORDER BY $event_table.summary $orderby";
                break;
            case 'eventDesc':
                $query .= " ORDER BY $event_table.description $orderby";
                break;
            case 'eventLocn':
                $query .= " ORDER BY $event_table.location $orderby";
                break;
            case 'eventCont':
                $query .= " ORDER BY $event_table.contact $orderby";
                break;
            case 'eventFee':
                $query .= " ORDER BY $event_table.fee $orderby";
                break;
        }
    }

    $result =& $dbconn->SelectLimit($query, $numitems, $startnum-1);

    // Check for an error.
    if (!$result) return;

    // Check for no rows found.
    if ($result->EOF) {
        $result->Close();
        return;
    }

    // Put items into result array
    for (; !$result->EOF; $result->MoveNext()) {
        list($eID, 
             $eName,
             $eDescription,
             $eStreet1, 
             $eStreet2,
             $eCity,
             $eState,
             $eZip,
             $eEmail,
             $ePhone,
             $eLocation,
             $eUrl,
             $eContact,
             $eOrganizer,
             $eStart['timestamp'],
             $eRecur['timestamp'],
             $eDue['timestamp'],
             $eDuration,
             $eRrule,
             $eIsallday,
             $eFee) = $result->fields;

          // Change date formats from UNIX timestamp to something readable.
          if ($eStart['timestamp'] == 0) {
              $eStart['mon'] = "";
              $eStart['day'] = "";
              $eStart['year'] = "";
          } else {
              $eStart['linkdate'] = date("Ymd",strtotime($eStart['timestamp']));
              $eStart['viewdate'] = date("m-d-Y",strtotime($eStart['timestamp']));
          }
          if ($eRecur['timestamp'] == 0) {
              $eRecur['mon'] = "";
              $eRecur['day'] = "";
              $eRecur['year'] = "";
          } else {
              $eRecur['linkdate'] = date("Ymd",strtotime($eDue['timestamp']));
              $eRecur['viewdate'] = date("m-d-Y",strtotime($eDue['timestamp']));
          }
          if ($eDue['timestamp'] == 0) {
              $eDue['mon'] = "";
              $eDue['day'] = "";
              $eDue['year'] = "";
          } else {
              $eDue['linkdate'] = date("Ymd",strtotime($eDue['timestamp']));
              $eDue['viewdate'] = date("m-d-Y",strtotime($eDue['timestamp']));
          }

         $items[] = array('eID' => $eID,
                          'eName' => $eName,
                          'eDescription' => $eDescription,
                          'eStreet1' => $eStreet1,
                          'eStreet2' => $eStreet2,
                          'eCity' => $eCity,
                          'eState' => $eState,
                          'eZip' => $eZip,
                          'eEmail' => $eEmail,
                          'ePhone' => $ePhone,
                          'eLocation' => $eLocation,
                          'eUrl' => $eUrl,
                          'eContact' => $eContact,
                          'eOrganizer' => $eOrganizer,
                          'eStart' => $eStart,
                          'eRecur' => $eRecur,
                          'eDue' => $eDue,
                          'eDuration' => $eDuration,
                          'eRrule' => $eRrule,
                          'eIsallday' => $eIsallday,
                          'eFee' => $eFee,);
    }
/*
    // TODO: include linked events
    // Get the linked events
    
    $dbconn =& xarDBGetConn();
    $xartable =& xarDBGetTables();
    $event_linkage_table = $xartable['julian_events_linkage'];
    $query_linked = "SELECT DISTINCT event_id,
    					 hook_modid,
    					 hook_itemtype,
    					 hook_iid,
    					 dtstart,
    					 duration,
    					 isallday,
    					 rrule,
    					 recur_freq,
    					 recur_count,
    					 recur_until,
    					 if(recur_until LIKE '0000%','',recur_until) as fRecurUntil,
    					 recur_interval,
    					 if(isallday,'',DATE_FORMAT(dtstart,'%l:%i %p')) as fStartTime,
    					 DATE_FORMAT(dtstart,'%Y-%m-%d') as fStartDate
    			 FROM $event_linkage_table 
    			 WHERE (1) ".$condition."
    			 ORDER BY dtstart ASC;";
    $result_linked =& $dbconn->Execute($query_linked);
    if (!$result_linked) return;

    // Check for no rows found.
    if ($result->EOF) {
        $result->Close();
        return;
    }

    // Put items into result array
    for (; !$result->EOF; $result->MoveNext()) {
        list($eID, 
        //     $eName,
        //     $eDescription,
        //     $eStreet1, 
        //     $eStreet2,
        //     $eCity,
        //     $eState,
        //     $eZip,
        //     $eEmail,
        //     $ePhone,
        //     $eLocation,
        //     $eUrl,
        //     $eContact,
        //     $eOrganizer,
             $hook_modid,
             $hook_itemtype,
             $hook_iid,
             $eStart['timestamp'],
             $eDuration,
             $eIsallday,
             $eRrule,
             $eRecurFreq,
             $eRecurCount,
             $eRecurUntil
             ) = $result->fields;

          // Change date formats from UNIX timestamp to something readable.
          if ($eStart['timestamp'] == 0) {
              $eStart['mon'] = "";
              $eStart['day'] = "";
              $eStart['year'] = "";
          } else {
              $eStart['linkdate'] = date("Ymd",strtotime($eStart['timestamp']));
              $eStart['viewdate'] = date("m-d-Y",strtotime($eStart['timestamp']));
          }
          if ($eRecur['timestamp'] == 0) {
              $eRecur['mon'] = "";
              $eRecur['day'] = "";
              $eRecur['year'] = "";
          } else {
              $eRecur['linkdate'] = date("Ymd",strtotime($eDue['timestamp']));
              $eRecur['viewdate'] = date("m-d-Y",strtotime($eDue['timestamp']));
          }
          if ($eDue['timestamp'] == 0) {
              $eDue['mon'] = "";
              $eDue['day'] = "";
              $eDue['year'] = "";
          } else {
              $eDue['linkdate'] = date("Ymd",strtotime($eDue['timestamp']));
              $eDue['viewdate'] = date("m-d-Y",strtotime($eDue['timestamp']));
          }

         $items[] = array('eID' => $eID,
                          'eName' => $eName,
                          'eDescription' => $eDescription,
                          'eStreet1' => $eStreet1,
                          'eStreet2' => $eStreet2,
                          'eCity' => $eCity,
                          'eState' => $eState,
                          'eZip' => $eZip,
                          'eEmail' => $eEmail,
                          'ePhone' => $ePhone,
                          'eLocation' => $eLocation,
                          'eUrl' => $eUrl,
                          'eContact' => $eContact,
                          'eOrganizer' => $eOrganizer,
                          'eStart' => $eStart,
                          'eRecur' => $eRecur,
                          'eDue' => $eDue,
                          'eDuration' => $eDuration,
                          'eRrule' => $eRrule,
                          'eIsallday' => $eIsallday,
                          'eFee' => $eFee,);
    }
*/
    // Close result set
    $result->Close();

    // Return the items
    return $items;
}
?>
