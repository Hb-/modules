<?php

/**
 * File: $Id$
 *
 * init file for installing/upgrading MIME module
 *
 * @package modules
 * @copyright (C) 2002 by the Xaraya Development Team.
 * @link http://www.xaraya.com
 *
 * @subpackage mime
 * @author Carl P. Corliss <rabbitt@xaraya.com>
*/

/**
 * MIME API
 * @package Xaraya
 * @subpackage MIME_API
 */

/**
 * MIME Initialization Function
 *
 * @author Carl P. Corliss (aka Rabbitt)
 *
 */
function mime_init() {

    $error = false;
    
    //Load Table Maintenance API
    xarDBLoadTableMaintenanceAPI();
    
    list($dbconn) = xarDBGetConn();
    $xartable = xarDBGetTables();

    $fields['mime_type'] = array(
        'xar_mime_type_id'          => array('type'=>'integer',  'null'=>FALSE, 'increment'=>TRUE,'primary_key'=>TRUE),
        'xar_mime_type_name'        => array('type'=>'varchar',  'null'=>FALSE,  'size'=>255),
    );
    
    $fields['mime_subtype'] = array(
        'xar_mime_type_id'          => array('type'=>'integer',  'null'=>FALSE),
        'xar_mime_subtype_id'       => array('type'=>'integer',  'null'=>FALSE, 'increment'=>TRUE,'primary_key'=>TRUE),
        'xar_mime_subtype_name'     => array('type'=>'varchar',  'null'=>FALSE,  'size'=>255),
    );
    
    $fields['mime_extension'] = array(
        'xar_mime_subtype_id'       => array('type'=>'integer',  'null'=>FALSE),
        'xar_mime_extension_id'     => array('type'=>'integer',  'null'=>FALSE,  'increment'=>TRUE,'primary_key'=>TRUE),
        'xar_mime_extension_name'   => array('type'=>'varchar',  'null'=>FALSE,  'size'=>10)
    );
    
    $fields['mime_magic'] = array(
        'xar_mime_subtype_id'       => array('type'=>'integer',  'null'=>FALSE),
        'xar_mime_magic_id'         => array('type'=>'integer',  'null'=>FALSE, 'increment'=>TRUE,'primary_key'=>TRUE),
        'xar_mime_magic_value'      => array('type'=>'varchar',  'null'=>FALSE, 'size'=>256),
        'xar_mime_magic_length'     => array('type'=>'integer',  'null'=>FALSE),
        'xar_mime_magic_offset'     => array('type'=>'integer',  'null'=>FALSE)
    );
    
    // Create all the tables and, if there are errors
    // just make a note of them for now - we don't want
    // to return right away otherwise we could have
    // some tables created and some not.
    foreach ($fields as $table => $data) {
        $query = xarDBCreateTable($xartable[$table], $data);
        
        $result =& $dbconn->Execute($query);
        if (!$result) {
            $tables[$table] = false;
            $error |= true;
        } else {
            $tables[$table] = true;
            $error |= false;              
        }
    } 
    
    // if there were any errors during the 
    // table creation, make sure to remove any tables
    // that might have been created
    if ($error) {
        foreach ($tables as $table) {
            $query = xarDBDropTable($xartable[$table]);
            $result =& $dbconn->Execute($query);

            if(!$result)
                return;
        }
        return false;
    }
    
    // Initialisation successful
    return true;
}

/**
 *  Delete all tables, unregister hooks, remove
 *  priviledge instances, and anything else related to 
 *  this module
 */
              
              
              
    

function mime_delete()
{
    //Load Table Maintenance API
    xarDBLoadTableMaintenanceAPI();    

    // Get database information
    list($dbconn) = xarDBGetConn();
    $xartable = xarDBGetTables();

    // Delete tables
    $queries[0] = xarDBDropTable($xartable['mime_type']);
    $queries[1] = xarDBDropTable($xartable['mime_subtype']);
    $queries[2] = xarDBDropTable($xartable['mime_ext']);
    $queries[3] = xarDBDropTable($xartable['mime_magic']);
    
    foreach( $queries as $query) {
        $result =& $dbconn->Execute($query);
        if(!$result)
            return;
    }

    // Deletion successful
    return true;

}

/**
* upgrade the mime module from an old version
*/
function mime_upgrade($oldversion)
{
    // Upgrade dependent on old version number
    switch($oldversion) {
        case .1:
            mime_init();
            include_once "modules/mime/xarincludes/mime.magic.php";
            xarModAPIFunc('mime','user','import_mimelist', array('mimeList' => $mime_list));
            // fall through to the next upgrade
        case 1.1:
            // fall through to the next upgrade
        case 2.0:
            // Code to upgrade from version 2.0 goes here
            // fall through to the next upgrade
        case 2.5:
            // Code to upgrade from version 2.5 goes here
            break;
    }
    return true;
}

?>
