<?php

/**
 * run the job
 * @param $args['handlingid'] the process handling id
 * @param $args['pubsubid'] the subscription id
 * @param $args['objectid'] the specific object in the module
 * @returns bool
 * @return true on success, false on failure
 * @raise BAD_PARAM, DATABASE_ERROR
 */
function pubsub_adminapi_runjob($args)
{
    // Get arguments from argument array
    extract($args);

    // Argument check
    $invalid = array();
    if (!isset($handlingid) || !is_numeric($handlingid)) {
        $invalid[] = 'handlingid';
    }
    if (!isset($pubsubid) || !is_numeric($pubsubid)) {
        $invalid[] = 'pubsubid';
    }
    if (!isset($objectid) || !is_numeric($objectid)) {
        $invalid[] = 'objectid';
    }
    if (count($invalid) > 0) {
        $msg = xarML('Invalid #(1) function #(3)() in module #(4)',
                    join(', ',$invalid), 'runjob', 'Pubsub');
        xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'BAD_PARAM',
                       new SystemException($msg));
        return;
    }

    // Database information
    list($dbconn) = xarDBGetConn();
    $xartable = xarDBGetTables();
    $pubsubregtable = $xartable['pubsub_reg'];
    $pubsubeventstable = $xartable['pubsub_events'];

    // Get info on job to run
    $query = "SELECT xar_actionid,
                     xar_userid,
                     $pubsubregtable.xar_eventid,
                     xar_modid,
                     xar_itemtype,
              FROM $pubsubregtable
              LEFT JOIN $pubsubeventstable
              ON $pubsubregtable.xar_eventid = $pubsubeventstable.xar_eventid
              WHERE xar_pubsubid = " . xarVarPrepForStore($pubsubid);
    $result   = $dbconn->Execute($query);
    if (!$result) return;

    $actionid = $result->fields[0];
    $userid   = $result->fields[1];
    $eventid  = $result->fields[2];
    $modid    = $result->fields[3];
    $itemtype = $result->fields[4];
    $info = xarUserGetVar('email',$userid);
    $name = xarUserGetVar('uname',$userid);

    $modinfo = xarModGetInfo($modid);
    if (empty($modinfo['name'])) {
        $msg = xarML('Invalid #(1) function #(3)() in module #(4)',
                    join(', ',$invalid), 'runjob', 'Pubsub');
        xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'BAD_PARAM',
                       new SystemException($msg));
        return;
    } else {
        $modname = $modinfo['name'];
    }
    $templateid = xarModGetVar('pubsub',"$modname.$itemtype");

    if ($actionid == "mail" || $actionid == "htmlmail") {
        // Database information
        $pubsubtemplatestable = $xartable['pubsub_templates'];
        // Get the (compiled) template to use
        $query = "SELECT xar_compiled
                  FROM $pubsubtemplatestable
                  WHERE xar_templateid = " . xarVarPrepForStore($templateid);
        $result   = $dbconn->Execute($query);
        if (!$result) return;

        if ($result->EOF) {
            $msg = xarML('Invalid #(1) template',
                         'Pubsub');
            xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'BAD_PARAM',
                     new SystemException($msg));
            return;
        }

        $compiled = $result->fields[0];

        if (empty($compiled)) {
            $msg = xarML('Invalid #(1) template',
                         'Pubsub');
            xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'BAD_PARAM',
                     new SystemException($msg));
            return;
        }

        $tplData = array();
        $tplData['name'] = $name;
        $tplData['module'] = $modname;
        $tplData['itemtype'] = $itemtype;
        $tplData['itemid'] = $objectid;

        // (try to) retrieve a title and link for this item
        $itemlinks = xarModAPIFunc($modname,'user','getitemlinks',
                                   array('itemtype' => $itemtype,
                                         'itemids' => array($objectid)),
                                   0); // don't throw an exception here
        if (!empty($itemlinks) && !empty($itemlinks[$objectid])) {
            $tplData['title'] = $itemlinks[$objectid]['label'];
            $tplData['link'] =  $itemlinks[$objectid]['url'];
        } else {
            $tplData['title'] = xarML('Item #(1)', $objectid);
            $tplData['link'] =  xarModURL($modname,'user','main');
        }

        // *** TODO  ***
        // need to define some variables for user firstname and surname,etc.
        // might not be able to use the normal BL user vars as they would
        // probabaly expand to currently logged in user, not the user for
        // this event.
        // need to create the $tplData array with all the information in it

        // call BL with the (compiled) template to parse it and generate the HTML
        $html = xarTplString($compiled, $tplData);
        $plaintext = strip_tags($html);

        if ($action == "htmlmail") {
            $boundary = "b" . md5(uniqid(time()));
            $message = "From: xarConfigGetVar('adminmail')\r\nReply-to: xarConfigGetVar('adminmail')\r\n";
            $message .= "Content-type: multipart/mixed; ";
            $message .= "boundary = $boundary\r\n\r\n";
            $message .= "This is a MIME encoded message.\r\n\r\n";
            // first the plaintext message
            $message .= "--$boundary\r\n";
            $message .= "Content-type: text/plain\r\n";
            $message .= "Content-Transfer-Encoding: base64";
            $message .= "\r\n\r\n" . chunk_split(base64_encode($plaintext)) . "\r\n";
            // now the HTML version
            $message .= "--$boundary\r\n";
            $message .= "Content-type: text/html\r\n";
            $message .= "Content-Transfer-Encoding: base64";
            $message .= "\r\n\r\n" . chunk_split(base64_encode($html)) . "\r\n";
         } else {
            // plaintext mail
            $message=$plaintext;
         }
         // Send the mail using the mail module
         if (!xarModAPIFunc('mail',
                            'admin',
                            'sendmail',
                            array('info'     => $info,
                                  'name'     => $name,
                                  'subject'  => $subject,
                                  'message'  => $message,
                                  'from'     => $fmail,
                                  'fromname' => $fname))) return;

             // delete job from queue now it has run
             xarModAPIFunc('pubsub','admin','deljob',
                           array('handlingid' => $handlingid));
    } else {
        // invalid action - update queue accordingly
        xarModAPIFunc('pubsub','admin','updatejob',
                      array('handlingid' => $handlingid,
                            'pubsubid' => $pubsubid,
                            'objectid' => $objectid,
                            'status' => 'error'));
        $msg = xarML('Invalid #(1) action',
                     'Pubsub');
        xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'BAD_PARAM',
                 new SystemException($msg));
        return;
    }
    return true;
}

?>
