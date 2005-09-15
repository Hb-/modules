<?php
/**
    Creates the Ticket
    @author Brian McGilligan
    @param - all the ticket info 
    @return The new ticket id
*/
function helpdesk_userapi_create($args)
{
    extract($args);

    // Generate SQL code for Ticket entry
    $dbconn    =& xarDBGetConn();
    $xartable  =& xarDBGetTables();
    $db_table  = $xartable['helpdesk_tickets'];
    $time = date("Y-m-d H:i:s");

    if (empty($name)){ $name = xarUserGetVar('name', $whosubmit); }
    if (empty($email)){ $email = xarUserGetVar('email', $whosubmit); }
    if (empty($phone)){ $phone = ''; }

    // Get next ID inserted into table
    $nextid = $dbconn->GenID('xar_id');

    // Insert ticket
    $sql = "INSERT INTO $db_table  (xar_id,
                                    xar_domain,
                                    xar_statusid,
                                    xar_priorityid,
                                    xar_sourceid,
                                    xar_openedby,
                                    xar_subject,
                                    xar_date,
                                    xar_updated,
                                    xar_assignedto,
                                    xar_closedby,
                                    xar_name,
                                    xar_phone,
                                    xar_email
                                   ) 
                           VALUES  ( $nextid, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )";
    $bindvars = array($domain, $status, $priority, $source, $whosubmit, $subject, $time, $time, $assignedto, $closedby, $name, $phone, $email
    );
    $result = $dbconn->Execute($sql, $bindvars);

    // Check for an error
    if (!$result) return false;

    // Get the ID of the item that was inserted
    $nextid = $dbconn->PO_Insert_ID($db_table, 'xar_id');

    // To see their results, we redirect them to the Manage category page:
    return $nextid;
}
?>
