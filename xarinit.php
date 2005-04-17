<?php

/**
 * File: $Id$
 *
 * init file for installing/upgrading Comments module
 *
 * @package modules
 * @copyright (C) 2002 by the Xaraya Development Team.
 * @link http://www.xaraya.com
 *
 * @subpackage comments
 * @author Carl P. Corliss <rabbitt@xaraya.com>
*/

/**
 * Comments API
 * @package Xaraya
 * @subpackage Comments_API
 */

include_once('modules/comments/xarincludes/defines.php');

/**
 * Comments Initialization Function
 *
 * @author Carl P. Corliss (aka Rabbitt)
 *
 */
function comments_init() 
{

    //Load Table Maintenance API
    xarDBLoadTableMaintenanceAPI();

    $dbconn =& xarDBGetConn();
    $xartable =& xarDBGetTables();

    // Create tables
    $ctable = $xartable['comments'];
    $cctable = &$xartable['comments_column'];

    $fields = array(
        'xar_cid'       => array('type'=>'integer',  'null'=>FALSE,  'increment'=>TRUE,'primary_key'=>TRUE),
        'xar_pid'       => array('type'=>'integer',  'null'=>FALSE),
        'xar_modid'     => array('type'=>'integer',  'null'=>TRUE),
        'xar_itemtype'  => array('type'=>'integer',  'null'=>false),
        'xar_objectid'  => array('type'=>'varchar',  'null'=>FALSE,  'size'=>255),
        'xar_date'      => array('type'=>'integer',  'null'=>FALSE),
        'xar_author'    => array('type'=>'integer',  'null'=>FALSE,  'size'=>'medium','default'=>1),
        'xar_title'     => array('type'=>'varchar',  'null'=>FALSE,  'size'=>100),
        'xar_hostname'  => array('type'=>'varchar',  'null'=>FALSE,  'size'=>255),
        'xar_text'      => array('type'=>'text',     'null'=>TRUE,   'size'=>'medium'),
        'xar_left'      => array('type'=>'integer',  'null'=>FALSE),
        'xar_right'     => array('type'=>'integer',  'null'=>FALSE),
        'xar_status'    => array('type'=>'integer',  'null'=>FALSE,  'size'=>'tiny'),
        'xar_anonpost'  => array('type'=>'integer',  'null'=>TRUE,   'size'=>'tiny', 'default'=>0),
    );

    $query = xarDBCreateTable($xartable['comments'], $fields);

    $result =& $dbconn->Execute($query);
    if (!$result)
        return;

    $index = array('name'      => 'i_' . xarDBGetSiteTablePrefix() . '_comments_left',
                   'fields'    => array('xar_left'),
                   'unique'    => FALSE);

    $query = xarDBCreateIndex($xartable['comments'],$index);

    $result =& $dbconn->Execute($query);
    if (!$result) return;

    $index = array('name'      => 'i_' . xarDBGetSiteTablePrefix() . '_comments_right',
                   'fields'    => array('xar_right'),
                   'unique'    => FALSE);

    $query = xarDBCreateIndex($xartable['comments'],$index);

    $result =& $dbconn->Execute($query);
    if (!$result) return;

    $index = array('name'      => 'i_' . xarDBGetSiteTablePrefix() . '_comments_pid',
                   'fields'    => array('xar_pid'),
                   'unique'    => FALSE);

    $query = xarDBCreateIndex($xartable['comments'],$index);

    $result =& $dbconn->Execute($query);
    if (!$result) return;

    $index = array('name'      => 'i_' . xarDBGetSiteTablePrefix() . '_comments_modid',
                   'fields'    => array('xar_modid'),
                   'unique'    => FALSE);

    $query = xarDBCreateIndex($xartable['comments'],$index);

    $result =& $dbconn->Execute($query);
    if (!$result) return;

    $index = array('name'      => 'i_' . xarDBGetSiteTablePrefix() . '_comments_itemtype',
                   'fields'    => array('xar_itemtype'),
                   'unique'    => FALSE);

    $query = xarDBCreateIndex($xartable['comments'],$index);

    $result =& $dbconn->Execute($query);
    if (!$result) return;

    $index = array('name'      => 'i_' . xarDBGetSiteTablePrefix() . '_comments_objectid',
                   'fields'    => array('xar_objectid'),
                   'unique'    => FALSE);

    $query = xarDBCreateIndex($xartable['comments'],$index);

    $result =& $dbconn->Execute($query);
    if (!$result) return;

    $index = array('name'      => 'i_' . xarDBGetSiteTablePrefix() . '_comments_status',
                   'fields'    => array('xar_status'),
                   'unique'    => FALSE);

    $query = xarDBCreateIndex($xartable['comments'],$index);

    $result =& $dbconn->Execute($query);
    if (!$result) return;

    $index = array('name'      => 'i_' . xarDBGetSiteTablePrefix() . '_comments_author',
                   'fields'    => array('xar_author'),
                   'unique'    => FALSE);

    $query = xarDBCreateIndex($xartable['comments'],$index);

    $result =& $dbconn->Execute($query);
    if (!$result) return;

    // Create blacklist tables
    $btable = $xartable['blacklist'];
    $bbtable = &$xartable['blacklist_column'];

    $fields = array(
        'xar_id'       => array('type'=>'integer',  'null'=>FALSE,  'increment'=> TRUE, 'primary_key'=>TRUE),
        'xar_domain'   => array('type'=>'varchar',  'null'=>FALSE,  'size'=>255)
    );

    $query = xarDBCreateTable($xartable['blacklist'], $fields);

    $result =& $dbconn->Execute($query);
    if (!$result)
        return;

    // fill blacklist table
    $file = file('modules/comments/xarincludes/blacklist.txt');
    for ($i=0; $i<count($file); $i++) {
        $data = $file[$i];
        $domain = "";
        for ($j=0; $j<strlen($data); $j++)  {
            if ($data[$j]==" " || $data[$j] == "#"){
                break;
            } else {
                $domain .= $data[$j];
                continue;
            }
        }
		if (strpos($domain, '[\w\-_.]')) {
			$domaim = str_replace('[\w\-_.]','[-\w\_.]', $domain);
		}
		$ps = strpos($domain, '/');
		while ($ps !== false) {
			if ($ps == 0) {
				$domain = '\\' + $domain;
			} else if (substr($domain, $ps-1, 1) != '\\') {
				$domain = substr_replace($domain, '\/', $ps, 1); 
			}
			$ps = strpos($domain, '/', $ps+2);
		}
        $domain = trim($domain);
        if ($domain != ""){
            $nextId = $dbconn->GenId($btable);
            $query = "INSERT INTO $btable(xar_id, 
                                          xar_domain)
                      VALUES (?,?)";
            $bindvars = array($nextId, $domain);
            $result =& $dbconn->Execute($query,$bindvars);
            if (!$result) return;
        }
    }
        
    // Set up module variables
    xarModSetVar('comments','render',_COM_VIEW_THREADED);
    xarModSetVar('comments','sortby',_COM_SORTBY_THREAD);
    xarModSetVar('comments','order',_COM_SORT_ASC);
    xarModSetVar('comments','depth', _COM_MAX_DEPTH);
    xarModSetVar('comments','AllowPostAsAnon',1);
    xarModSetVar('comments','AuthorizeComments',0);
    xarModSetVar('comments','AllowCollapsableThreads',1);
    xarModSetVar('comments','CollapsedBranches',serialize(array()));
    xarModSetVar('comments','editstamp',1);  
    // TODO: add delete hook

    // display hook
    if (!xarModRegisterHook('item', 'display', 'GUI','comments', 'user', 'display'))
        return false;

    // usermenu hook
    if (!xarModRegisterHook('item', 'usermenu', 'GUI','comments', 'user', 'usermenu'))
        return false;

    // search hook
    if (!xarModRegisterHook('item', 'search', 'GUI','comments', 'user', 'search'))
        return false;

    // module delete hook
    if (!xarModRegisterHook('module', 'remove', 'API','comments', 'admin', 'remove_module'))
        return false;


    /**
     * Define instances for this module
     * Format is
     * setInstance(Module, Type, ModuleTable, IDField, NameField,
     *             ApplicationVar, LevelTable, ChildIDField, ParentIDField)
     *
     */

    $query1 = "SELECT DISTINCT $xartable[modules].xar_name
                          FROM $ctable
                     LEFT JOIN $xartable[modules]
                            ON $cctable[modid] = $xartable[modules].xar_regid";

    $query2 = "SELECT DISTINCT $cctable[objectid]
                          FROM $ctable";

    $query3 = "SELECT DISTINCT $cctable[cid]
                          FROM $ctable
                         WHERE $cctable[status] != '"._COM_STATUS_ROOT_NODE."'";
    $instances = array(
                        array('header' => 'Module ID:',
                                'query' => $query1,
                                'limit' => 20
                            ),
                        array('header' => 'Module Page ID:',
                                'query' => $query2,
                                'limit' => 20
                            ),
                        array('header' => 'Comment ID:',
                                'query' => $query3,
                                'limit' => 20
                            )
                    );
    xarDefineInstance('comments','All',$instances);

    /*
     * Register the module components that are privileges objects
     * Format is
     * xarregisterMask(Name,Realm,Module,Component,Instance,Level,Description)
     *
     */

    xarRegisterMask('Comments-Read',     'All','comments',
                    'All','All:All:All','ACCESS_READ',      'See and Read Comments');
    xarRegisterMask('Comments-Post',     'All','comments',
                    'All','All:All:All','ACCESS_COMMENT',   'Post a new Comment');
    xarRegisterMask('Comments-Reply',    'All','comments',
                    'All','All:All:All','ACCESS_COMMENT',   'Reply to a Comment');
    xarRegisterMask('Comments-Edit',     'All','comments',
                    'All','All:All:All','ACCESS_EDIT',      'Edit Comments');
    xarRegisterMask('Comments-Delete',   'All','comments',
                    'All','All:All:All','ACCESS_DELETE',    'Delete a Comment or Comments');
    xarRegisterMask('Comments-Moderator','All','comments',
                    'All','All:All:All','ACCESS_MODERATE',  'Moderate Comments');
    xarRegisterMask('Comments-Admin',    'All','comments',
                    'All','All:All:All','ACCESS_ADMIN',     'Administrate Comments');


    // Register blocks
    if (!xarModAPIFunc('blocks', 'admin', 'register_block_type',
                       array('modName'  => 'comments',
                             'blockType'=> 'latestcomments'))) return;
    // TODO: define blocks mask & instances here, or re-use some common one ?

    // Initialisation successful
    return true;
}

function comments_delete()
{
    //Load Table Maintenance API
    xarDBLoadTableMaintenanceAPI();

    // Get database information
    $dbconn =& xarDBGetConn();
    $xartable =& xarDBGetTables();

    // Delete tables
    $query = xarDBDropTable($xartable['comments']);
    $result =& $dbconn->Execute($query);

    if(!$result)
        return;

    $query = xarDBDropTable($xartable['blacklist']);
    $result =& $dbconn->Execute($query);

    if(!$result)
        return;

    // Delete module variables
    xarModDelVar('comments','render');
    xarModDelVar('comments','sortby');
    xarModDelVar('comments','order');
    xarModDelVar('comments','depth');
    xarModDelVar('comments','AllowPostAsAnon');
    xarModDelVar('comments','AuthorizeComments');

    if (!xarModUnregisterHook('item', 'display', 'GUI',
                            'comments', 'user', 'display')) {
        return false;
    }

    // Remove Masks and Instances
    xarRemoveMasks('comments');
    xarRemoveInstances('comments');

    // UnRegister blocks
    if (!xarModAPIFunc('blocks', 'admin', 'unregister_block_type',
                       array('modName'  => 'comments',
                             'blockType'=> 'latestcomments'))) return;

    // Deletion successful
    return true;

}

/**
* upgrade the comments module from an old version
*/
function comments_upgrade($oldversion)
{
    // Upgrade dependent on old version number
    switch($oldversion) {
        case '1.0':
            // Code to upgrade from version 1.0 goes here
            // Register blocks
        if (!xarModAPIFunc('blocks', 'admin', 'block_type_exists',
                               array('modName'  => 'comments',
                                     'blockType'=> 'latestcomments'))) { 
                 if (!xarModAPIFunc('blocks', 'admin', 'register_block_type',
                               array('modName'  => 'comments',
                                     'blockType'=> 'latestcomments'))) return;
        }
            // fall through to the next upgrade
        case '1.1':
            // Code to upgrade from version 1.1 goes here
            if (xarModIsAvailable('articles')) {
                // load API for table definition etc.
                if (!xarModAPILoad('articles','user')) return;
            }

            // Get database information
            $dbconn =& xarDBGetConn();
            $xartable =& xarDBGetTables();
            $commentstable = $xartable['comments'];

            xarDBLoadTableMaintenanceAPI();

            // add the xar_itemtype column
            $query = xarDBAlterTable($commentstable,
                                     array('command' => 'add',
                                           'field' => 'xar_itemtype',
                                           'type' => 'integer',
                                           'null' => false,
                                           'default' => '0'));
            $result = &$dbconn->Execute($query);
            if (!$result) return;

            // make sure all current records have an itemtype 0 (just in case)
            $query = "UPDATE $commentstable SET xar_itemtype = 0";
            $result =& $dbconn->Execute($query);
            if (!$result) return;

            // update the itemtype field for all articles
            if (xarModIsAvailable('articles')) {
                $modid = xarModGetIDFromName('articles');
                $articlestable = $xartable['articles'];

                $query = "SELECT xar_aid, xar_pubtypeid FROM $articlestable";
                $result =& $dbconn->Execute($query);
                if (!$result) return;

                while (!$result->EOF) {
                    list($aid,$ptid) = $result->fields;
                    $update = "UPDATE $commentstable SET xar_itemtype = $ptid WHERE xar_objectid = '$aid' AND xar_modid = $modid";
                    $test =& $dbconn->Execute($update);
                    if (!$test) return;

                    $result->MoveNext();
                }
                $result->Close();
            }

// TODO: any other modules where we need to insert the right itemtype here ?

            // add an index for the xar_itemtype column
            $index = array('name'      => 'i_' . xarDBGetSiteTablePrefix() . '_comments_itemtype',
                           'fields'    => array('xar_itemtype'),
                           'unique'    => FALSE);
            $query = xarDBCreateIndex($commentstable,$index);
            $result =& $dbconn->Execute($query);
            if (!$result) return;

            // fall through to the next upgrade
        case '1.2':
        case '1.2.0':
            // Get database information
            $dbconn =& xarDBGetConn();
            $xartable =& xarDBGetTables();
            xarDBLoadTableMaintenanceAPI();
            // Create blacklist tables
            $btable = $xartable['blacklist'];
            $bbtable = &$xartable['blacklist_column'];

            $fields = array(
                'xar_id'       => array('type'=>'integer',  'null'=>FALSE,  'increment'=> TRUE, 'primary_key'=>TRUE),
                'xar_domain'   => array('type'=>'varchar',  'null'=>FALSE,  'size'=>255)
            );

            $query = xarDBCreateTable($xartable['blacklist'], $fields);

            $result =& $dbconn->Execute($query);
            if (!$result)
                return;

            $file = file('modules/comments/xarincludes/blacklist.txt');
            for ($i=0; $i<count($file); $i++) {
                $data = $file[$i];
                $domain = "";
                for ($j=0; $j<strlen($data); $j++)  {
                    if ($data[$j]==" " || $data[$j] == "#"){
                        break;
                    } else {
                        $domain .= $data[$j];
                        continue;
                    }
                }
                if (strpos($domain, '[\w\-_.]')) {
                    $domaim = str_replace('[\w\-_.]','[-\w\_.]', $domain);
                }
                $ps = strpos($domain, '/');
                while ($ps !== false) {
                    if ($ps == 0) {
                        $domain = '\\' + $domain;
                    } else if (substr($domain, $ps-1, 1) != '\\') {
                        $domain = substr_replace($domain, '\/', $ps, 1); 
                    }
                    $ps = strpos($domain, '/', $ps+2);
                }
                $domain = trim($domain);
                if ($domain != ""){
                    $nextId = $dbconn->GenId($btable);
                    $query = "INSERT INTO $btable(xar_id, 
                                                  xar_domain)
                              VALUES (?,?)";
                    $bindvars = array($nextId, $domain);
                    $result =& $dbconn->Execute($query,$bindvars);
                    if (!$result) return;
                }
            }
        case '2.0':
            // Code to upgrade from version 2.0 goes here
            // fall through to the next upgrade
        case '2.5':
            // Code to upgrade from version 2.5 goes here
            break;
    }
    return true;
}
?>