<?php
/**
 * Create a doc
 *
 * @package Xaraya eXtensible Management System
 * @copyright (C) 2005 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage Release Module
 */
/**
 * Create a doc by user
 * 
 * Original Author of file: John Cox via phpMailer Team
 * @author Release module development team
 * @param rid, title, doc, type, approved
 */
function release_userapi_createdoc($args)
{
    // Get arguments
    extract($args);

    // Argument check
    if ((!isset($rid)) ||
        (!isset($title)) ||
        (!isset($doc)) ||
        (!isset($type)) ||
        (!isset($approved))) {

        $msg = xarML('Wrong arguments to release_userapi_createdoc.');
        xarErrorSet(XAR_SYSTEM_EXCEPTION,
                        'BAD_PARAM',
                        new SystemException($msg));
        return false;
    }

    // Get datbase setup
    $dbconn =& xarDBGetConn();
    $xartable =& xarDBGetTables();

    $releasetable = $xartable['release_docs'];

    if (empty($approved)){
        $approved = 1;
    }

    // Get next ID in table
    $nextId = $dbconn->GenId($releasetable);
    $time = time();
    $query = "INSERT INTO $releasetable (
              xar_rdid,
              xar_rid,
              xar_title,
              xar_docs,
              xar_type,
              xar_time,
              xar_approved
              )
            VALUES (?,?,?,?,?,?,?)";

    $bindvars = array($nextId,$rid,$title,$doc,$type,$time,$approved);
    $result =& $dbconn->Execute($query,$bindvars);
    if (!$result) return;

    // Get the ID of the item that we inserted
    $rdid = $dbconn->PO_Insert_ID($releasetable, 'xar_rdid');

    // Let any hooks know that we have created a new user.
    xarModCallHooks('item', 'create', $rdid, 'rdid');

    // Return the id of the newly created user to the calling process
    return $rdid;

}

?>
