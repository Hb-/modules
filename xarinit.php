<?php
/**
 *  Module Initialisation Function
 *  @version $Id: xarinit.php,v 1.7 2003/06/24 20:08:10 roger Exp $
 *  @author Roger Raymond, Andrea Moro
 *  @todo determine DB Table schema
 *  @todo determine all module vars
 *  @todo determine permissions masks
 *  @todo determine blocklayout tags
 */
function calendar_init()
{
    // Create tables
    // TODO: I assume this is ok -amoro
    calendar_upgrade('0.1.0');

    // Setting module vars

    // Location of the PEAR Calendar Classes
    // Use the PHP Include path for now
    xarModSetVar('calendar','pearcalendar_root','Calendar/');

    // get list of calendar ics files
    $data = xarModAPIFunc('calendar', 'admin', 'get_calendars');
    xarModSetVar('calendar','default_cal',serialize($data['icsfiles']));

    // Other variables from phpIcalendar config.inc.php
    xarModSetVar('calendar','default_view'           , 'week');
    xarModSetVar('calendar','minical_view'           , 'week');
    xarModSetVar('calendar','cal_sdow'               , 0);   // 0=sunday $week_start_day in phpIcalendar
    xarModSetVar('calendar','day_start'              , '0700');
    xarModSetVar('calendar','day_end'                , '2300');
    xarModSetVar('calendar','gridLength'             , 15);
    xarModSetVar('calendar','num_years'              , 1);
    xarModSetVar('calendar','month_event_lines'      , 1);
    xarModSetVar('calendar','tomorrows_events_lines' , 1);
    xarModSetVar('calendar','allday_week_lines'      , 1);
    xarModSetVar('calendar','week_events_lines'      , 1);
    xarModSetVar('calendar','second_offset'          , 0);
    xarModSetVar('calendar','bleed_time'             , 0);
    xarModSetVar('calendar','display_custom_goto'    , 0);
    xarModSetVar('calendar','display_ical_list'      , 1);
    xarModSetVar('calendar','allow_webcals'          , 0);
    xarModSetVar('calendar','this_months_events'     , 1);
    xarModSetVar('calendar','use_color_cals'         , 1);
    xarModSetVar('calendar','daysofweek_dayview'     , 0);
    xarModSetVar('calendar','enable_rss'             , 1);
    xarModSetVar('calendar','show_search'            , 1);
    xarModSetVar('calendar','allow_preferences'      , 1);
    xarModSetVar('calendar','printview_default'      , 0);
    xarModSetVar('calendar','show_todos'             , 1);
    xarModSetVar('calendar','show_completed'         , 0);
    xarModSetVar('calendar','allow_login'            , 0);

//TODO::Register the Module Variables
    //
    //xarModSetVar('calendar','allowUserCalendars',false);
    //xarModSetVar('calendar','eventsOpenNewWindow',false);
    //xarModSetVar('calendar','adminNotify',false);
    //xarModSetVar('calendar','adminEmail','none@none.org');

//TODO::Figure out all the permissions stuff
    // allow users to see the calendar w/ events
    xarRegisterMask('ViewCalendar','All','calendar','All','All','ACCESS_READ');
    // allow full admin of the calendar
    xarRegisterMask('AdminCalendar','All','calendar','All','All','ACCESS_ADMIN');

//TODO::Register our blocklayout tags to allow using Objects in the templates
//<xar:calendar-decorator object="$Month" decorator="Xaraya" name="$MonthURI" />
//<xar:calendar-build object="$Month" />
//<xar:set name="$Month">& $Year->fetch()</xar:set>

    xarModSetVar('calendar', 'SupportShortURLs', true);

    xarTplRegisterTag(
        'calendar', 'calendar-decorator', array(),
        'calendar_userapi_handledecoratortag'
    );

    return true;
}

/**
 *  Module Upgrade Function
 */
function calendar_upgrade($oldversion)
{

    switch ($oldversion) {
        case '0.1.0':
            // Start creating the tables
            $dbconn =& xarDBGetConn();
            $xartable =& xarDBGetTables();

            $caltable = $xartable['calendars'];
            xarDBLoadTableMaintenanceAPI();
            $fields = array(
                'xar_id' => array('type' => 'integer', 'unsigned' => true, 'null' => false, 'increment' => true, 'primary_key' => true),
                'xar_role_id' => array('type' => 'integer', 'unsigned' => true, 'null' => true),
                'xar_mod_id' => array('type' => 'integer', 'unsigned' => true, 'null' => true),
                'xar_name' => array('type' => 'varchar', 'size' => '255', 'null' => true)
                );
            $query = xarDBCreateTable($caltable, $fields);
            if (empty($query)) return;
            $result = &$dbconn->Execute($query);
            if (!$result) return;

            $calfilestable = $xartable['calendars_files'];
            xarDBLoadTableMaintenanceAPI();
            $fields = array(
                'xar_calendars_id' => array('type' => 'integer', 'unsigned' => true, 'null' => false, 'primary_key' => true),
                'xar_files_id' => array('type' => 'integer', 'unsigned' => true, 'null' => false, 'primary_key' => true)
                );
            $query = xarDBCreateTable($calfilestable, $fields);
            if (empty($query)) return;
            $result = &$dbconn->Execute($query);
            if (!$result) return;

            $filestable = $xartable['calfiles'];
            xarDBLoadTableMaintenanceAPI();
            $fields = array(
                'xar_id' => array('type' => 'integer', 'unsigned' => true, 'null' => false, 'increment' => true, 'primary_key' => true),
                'xar_path' => array('type' => 'varchar', 'size' => '255', 'null' => true)
                );
            $query = xarDBCreateTable($filestable, $fields);
            if (empty($query)) return;
            $result = &$dbconn->Execute($query);
            if (!$result) return;

            $index = array(
                'name'      => 'i_' . xarDBGetSiteTablePrefix() . '_calendars_files_calendars_id',
                'fields'    => array('xar_calendars_id'),
                'unique'    => false
            );
            $query = xarDBCreateIndex($calfilestable,$index);
            $result =& $dbconn->Execute($query);
            if (!$result) return;

            $index = array(
                'name'      => 'i_' . xarDBGetSiteTablePrefix() . '_calendars_files_files_id',
                'fields'    => array('xar_files_id'),
                'unique'    => false
            );
            $query = xarDBCreateIndex($calfilestable,$index);
            $result =& $dbconn->Execute($query);
            if (!$result) return;

            return calendar_upgrade('0.1.1');

       case '0.1.1':

            // Code to upgrade from version 0.1.1 goes here
            // Use the PHP Include path for pear classes
            xarModSetVar('calendar','pearcalendar_root','Calendar/');
            return calendar_upgrade('0.2.0');
            break;

        case '0.2.0':

            // Code to upgrade from version 0.2.0 goes here
            break;
    }
    return true;
}

/**
 *  Module Delete Function
 */
function calendar_delete()
{

    // Remove all tables (see example module for comments)
    $dbconn =& xarDBGetConn();
    $xartable =& xarDBGetTables();
    xarDBLoadTableMaintenanceAPI();

    $query = xarDBDropTable($xartable['calendars']);
    if (empty($query)) return;
    $result =& $dbconn->Execute($query);
    if (!$result) return;

    $query = xarDBDropTable($xartable['calendars_files']);
    if (empty($query)) return;
    $result =& $dbconn->Execute($query);
    if (!$result) return;

    $query = xarDBDropTable($xartable['calfiles']);
    if (empty($query)) return;
    $result =& $dbconn->Execute($query);
    if (!$result) return;

    // remove all module vars
    xarModDelAllVars('calendar');

    // Remove Masks and Instances
    xarRemoveMasks('calendar');
    xarRemoveInstances('calendar');

    // remove registered template tags
    xarTplUnregisterTag('calendar-decorator');

    return true;
}

?>
