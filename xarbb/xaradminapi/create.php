<?php

/**
 * create a new forum
 * @param $args['fname'] name of forum
 * @param $args['fdesc'] description of forum
 * @returns int
 * @return autolink ID on success, false on failure
 */
function xarbb_adminapi_create($args)
{

    // Get arguments from argument array
    extract($args);

    // Argument check - make sure that all required arguments are present,
    // if not then set an appropriate error message and return
    if ((!isset($fname)) ||
        (!isset($fposter)) ||
        (!isset($fdesc))) {
        $msg = xarML('Invalid Parameter Count',
                    join(', ',$invalid), 'admin', 'create', 'xarbb');
        xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'BAD_PARAM',
                       new SystemException($msg));
        return;
    }

    // Security Check
    if(!xarSecurityCheck('AddxarBB',1,'Forum')) return;

    // Get datbase setup
    list($dbconn) = xarDBGetConn();
    $xartable = xarDBGetTables();

    $xbbforumstable = $xartable['xbbforums'];

    // Get next ID in table
    $nextId = $dbconn->GenId($xbbforumstable);

    // Get Time
    $time = date('Y-m-d G:i:s');

    // Add item
    $query = "INSERT INTO $xbbforumstable (
              xar_fid,
              xar_fname,
              xar_fdesc,
              xar_ftopics,
              xar_fposts,
              xar_fposter,
              xar_fpostid)
            VALUES (
              $nextId,
              '" . xarVarPrepForStore($fname) . "',
              '" . xarVarPrepForStore($fdesc) . "',
              '1',
              '1',
              '" . xarVarPrepForStore($fposter) . "',
              '$time')";
    $result =& $dbconn->Execute($query);
    if (!$result) return;

    // Get the ID of the item that we inserted
    $fid = $dbconn->PO_Insert_ID($xbbforumstable, 'xar_fid');

    // Let any hooks know that we have created a new forum
    $args['module'] = 'xarbb';
    $args['itemtype'] = 1; // forum
    $args['itemid'] = $fid;
    xarModCallHooks('item', 'create', $fid, $args);

    // Return the id of the newly created link to the calling process
    return $fid;
}

?>