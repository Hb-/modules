<?php
/*
 * File: $Id: $
 *
 * Newsletter 
 *
 * @package Xaraya eXtensible Management System
 * @copyright (C) 2003-2004 by the Xaraya Development Team
 * @link http://www.xaraya.com
 *
 * @subpackage newsletter module
 * @author Richard Cave <rcave@xaraya.com>
*/

/**
 * Initialize the Newsletter module
 */
function newsletter_init()
{
    if(!xarModIsAvailable('categories')) {
        $msg=xarML('The categories module should be activated first');
        xarErrorSet(XAR_SYSTEM_EXCEPTION,'MODULE_DEPENDENCY',
                        new SystemException($msg));
        return;
    }

    // Get datbase setup
    $dbconn =& xarDBGetConn();
    $xartable =& xarDBGetTables();

    // Load table maintenance API
    xarDBLoadTableMaintenanceAPI();

    // Create the newsletter publication table and column definitions
    $nwsltrPublications = $xartable['nwsltrPublications'];
    $nwsltrPublicationsColumn = &$xartable['nwsltrPublications_column'];

    $fields = array(
        'xar_id'            => array('type'=>'integer','null'=>FALSE,'increment'=>TRUE,'primary_key'=>TRUE),
        'xar_cid'           => array('type'=>'integer','null'=>FALSE,'default'=>'0'),
        'xar_altcids'       => array('type'=>'varchar','size'=>255,'null'=>TRUE,'default'=>''),
        'xar_ownerid'       => array('type'=>'integer','null'=>FALSE,'default'=>'0'),
        'xar_template_html' => array('type'=>'varchar','size'=>50,'null'=>FALSE,'default'=>''),
        'xar_template_text' => array('type'=>'varchar','size'=>50,'null'=>FALSE,'default'=>''),
        'xar_title'         => array('type'=>'varchar','size'=>255,'null'=>FALSE),
        'xar_logo'          => array('type'=>'varchar','size'=>255,'null'=>TRUE,'default'=>''),
        'xar_linkexpiration'   => array('type'=>'integer','null'=>FALSE,'default'=>'0'),
        'xar_linkregistration' => array('type'=>'varchar','size'=>255,'null'=>TRUE,'default'=>''),
        'xar_description'   => array('type'=>'text','null'=>TRUE),
        'xar_disclaimerid'  => array('type'=>'integer','null'=>FALSE,'default'=>'0'),
        'xar_introduction'  => array('type'=>'text','null'=>TRUE),
        'xar_private'       => array('type'=>'integer','size'=>'tiny','null'=>FALSE,'default'=>'0'),
        'xar_subject'       => array('type'=>'integer','size'=>'tiny','null'=>FALSE,'default'=>'0')
    );
    
    // Create the table DDL
    $query = xarDBCreateTable($nwsltrPublications, $fields);
    if (empty($query)) return; // throw back

    // Pass the Table Create DDL to adodb to create the table
    $dbconn->Execute($query);

    // Check for an error with the database
    if ($dbconn->ErrorNo() != 0) {
        $msg = xarMLByKey('DATABASE_ERROR', $query);
        xarErrorSet(XAR_SYSTEM_EXCEPTION, 'DATABASE_ERROR',
                    new SystemException(__FILE__.'('.__LINE__.'): '.$msg));
        return;
    }

    // Create the issues table and column definitions
    $nwsltrIssues = $xartable['nwsltrIssues'];
    $nwsltrIssuesColumn = &$xartable['nwsltrIssues_column'];

    $fields = array(
        'xar_id'            => array('type'=>'integer','null'=>FALSE,'increment'=>TRUE,'primary_key'=>TRUE),
        'xar_pid'           => array('type'=>'integer','null'=>FALSE,'default'=>'0'),
        'xar_ownerid'       => array('type'=>'integer','null'=>FALSE,'default'=>'0'),
        'xar_title'         => array('type'=>'varchar','size'=>255,'null'=>FALSE),
        'xar_external'      => array('type'=>'integer','size'=>'tiny','null'=>FALSE,'default'=>'0'),
        'xar_editornote'    => array('type'=>'varchar','size'=>255,'null'=>TRUE),
        'xar_datepublished' => array('type'=>'integer','unsigned'=>TRUE,'null'=>TRUE,'default'=>'0')
    );
    
    // Create the table DDL
    $query = xarDBCreateTable($nwsltrIssues,$fields);
    if (empty($query)) return; // throw back

    // Pass the Table Create DDL to adodb to create the table
    $dbconn->Execute($query);

    // Check for an error with the database
    if ($dbconn->ErrorNo() != 0) {
        $msg = xarMLByKey('DATABASE_ERROR', $query);
        xarErrorSet(XAR_SYSTEM_EXCEPTION, 'DATABASE_ERROR',
                    new SystemException(__FILE__.'('.__LINE__.'): '.$msg));
        return;
    }

    // Create the topics table and column definitions
    $nwsltrTopics = $xartable['nwsltrTopics'];
    $nwsltrTopicsColumn = &$xartable['nwsltrTopics_column'];

    $fields = array(
        'xar_issueid'       => array('type'=>'integer','null'=>FALSE,'primary_key'=>TRUE),
        'xar_storyid'       => array('type'=>'integer','null'=>FALSE,'primary_key'=>TRUE),
        'xar_cid'           => array('type'=>'integer','default'=>'0','null'=>FALSE),
        'xar_order'         => array('type'=>'integer','size'=>'tiny','default'=>'0','null'=>FALSE)
    );

    // Create the table DDL
    $query = xarDBCreateTable($nwsltrTopics,$fields);
    if (empty($query)) return; // throw back

    // Pass the Table Create DDL to adodb to create the table
    $dbconn->Execute($query);

    // Check for an error with the database
    if ($dbconn->ErrorNo() != 0) {
        $msg = xarMLByKey('DATABASE_ERROR', $query);
        xarErrorSet(XAR_SYSTEM_EXCEPTION, 'DATABASE_ERROR',
                    new SystemException(__FILE__.'('.__LINE__.'): '.$msg));
        return;
    }

    // Create the owners table and column definitions
    $nwsltrOwners = $xartable['nwsltrOwners'];
    $nwsltrOwnersColumn = &$xartable['nwsltrOwners_column'];

    $fields = array(
        'xar_uid'            => array('type'=>'integer','null'=>FALSE,'increment'=>TRUE,'primary_key'=>TRUE),
        'xar_rid'            => array('type'=>'integer','null'=>FALSE),
        'xar_signature'      => array('type'=>'text','null'=>TRUE)
    );


    // Create the table DDL
    $query = xarDBCreateTable($nwsltrOwners,$fields);
    if (empty($query)) return; // throw back

    // Pass the Table Create DDL to adodb to create the table
    $dbconn->Execute($query);

    // Check for an error with the database
    if ($dbconn->ErrorNo() != 0) {
        $msg = xarMLByKey('DATABASE_ERROR', $query);
        xarErrorSet(XAR_SYSTEM_EXCEPTION, 'DATABASE_ERROR',
                    new SystemException(__FILE__.'('.__LINE__.'): '.$msg));
        return;
    }

    // Create the disclaimers table and column definitions
    $nwsltrDisclaimers = $xartable['nwsltrDisclaimers'];
    $nwsltrDisclaimersColumn = &$xartable['nwsltrDisclaimers_column'];

    $fields = array(
        'xar_id'            => array('type'=>'integer','null'=>FALSE,'increment'=>TRUE,'primary_key'=>TRUE),
        'xar_title'         => array('type'=>'varchar','size'=>255,'null'=>FALSE),
        'xar_text'          => array('type'=>'text','null'=>TRUE)
    );

    // Create the table DDL
    $query = xarDBCreateTable($nwsltrDisclaimers,$fields);
    if (empty($query)) return; // throw back

    // Pass the Table Create DDL to adodb to create the table
    $dbconn->Execute($query);

    // Check for an error with the database
    if ($dbconn->ErrorNo() != 0) {
        $msg = xarMLByKey('DATABASE_ERROR', $query);
        xarErrorSet(XAR_SYSTEM_EXCEPTION, 'DATABASE_ERROR',
                    new SystemException(__FILE__.'('.__LINE__.'): '.$msg));
        return;
    }

    // Create the stories table and column definitions
    $nwsltrStories = $xartable['nwsltrStories'];
    $nwsltrStoriesColumn = &$xartable['nwsltrStories_column'];

    $fields = array(
        'xar_id'            => array('type'=>'integer','null'=>FALSE,'increment'=>TRUE,'primary_key'=>TRUE),
        'xar_ownerid'       => array('type'=>'integer','null'=>FALSE,'default'=>'0'),
        'xar_pid'           => array('type'=>'integer','null'=>FALSE,'default'=>'0'),
        'xar_cid'           => array('type'=>'integer','null'=>FALSE,'default'=>'0'),
        'xar_title'         => array('type'=>'varchar','size'=>255,'null'=>FALSE),
        'xar_source'        => array('type'=>'varchar','size'=>255,'null'=>FALSE),
        'xar_content'       => array('type'=>'text','null'=>FALSE),
        'xar_priority'      => array('type'=>'integer','size'=>'tiny','default'=>'0','null'=>FALSE),
        'xar_storydate'     => array('type'=>'integer','unsigned'=>TRUE,'null'=>FALSE,'default'=>'0'),
        'xar_altdate'       => array('type'=>'varchar','size'=>255,'null'=>TRUE),
        'xar_datepublished' => array('type'=>'integer','unsigned'=>TRUE,'null'=>TRUE,'default'=>'0'),
        'xar_fulltextlink'  => array('type'=>'varchar','size'=>255,'null'=>TRUE),
        'xar_registerlink'  => array('type'=>'integer','size'=>'tiny','null'=>FALSE,'default'=>'0'),
        'xar_linkexpiration'  => array('type'=>'integer','null'=>FALSE,'default'=>'0'),
        'xar_commentary'    => array('type'=>'text','null'=>FALSE),
        'xar_commentarysrc' => array('type'=>'varchar','size'=>255,'null'=>TRUE)
    );


    // Create the table DDL
    $query = xarDBCreateTable($nwsltrStories,$fields);
    if (empty($query)) return; // throw back

    // Pass the Table Create DDL to adodb to create the table
    $dbconn->Execute($query);

    // Check for an error with the database
    if ($dbconn->ErrorNo() != 0) {
        $msg = xarMLByKey('DATABASE_ERROR', $query);
        xarErrorSet(XAR_SYSTEM_EXCEPTION, 'DATABASE_ERROR',
                    new SystemException(__FILE__.'('.__LINE__.'): '.$msg));
        return;
    }

    // Create the subscriptions table and column definitions
    $nwsltrSubscriptions = $xartable['nwsltrSubscriptions'];
    $nwsltrSubscriptionsColumn = &$xartable['nwsltrSubscriptions_column'];

    $fields = array(
        'xar_uid'      => array('type'=>'integer','null'=>FALSE,'primary_key'=>TRUE),
        'xar_pid'      => array('type'=>'integer','null'=>FALSE,'primary_key'=>TRUE),
        'xar_htmlmail' => array('type'=>'integer','size'=>'tiny','null'=>FALSE,'default'=>'0')
    );

    // Create the table DDL
    $query = xarDBCreateTable($nwsltrSubscriptions,$fields);
    if (empty($query)) return; // throw back

    // Pass the Table Create DDL to adodb to create the table
    $dbconn->Execute($query);

    // Check for an error with the database
    if ($dbconn->ErrorNo() != 0) {
        $msg = xarMLByKey('DATABASE_ERROR', $query);
        xarErrorSet(XAR_SYSTEM_EXCEPTION, 'DATABASE_ERROR',
                    new SystemException(__FILE__.'('.__LINE__.'): '.$msg));
        return;
    }

    // Create the alternative subscriptions table and column definitions
    $nwsltrAltSubscriptions = $xartable['nwsltrAltSubscriptions'];
    $nwsltrAltSubscriptionsColumn = &$xartable['nwsltrAltSubscriptions_column'];

    $fields = array(
        'xar_id'            => array('type'=>'integer','null'=>FALSE,'increment'=>TRUE,'primary_key'=>TRUE),
        'xar_name'     => array('type'=>'varchar','size'=>100,'null'=>TRUE,'default'=>''),
        'xar_email'    => array('type'=>'varchar','size'=>255,'null'=>FALSE,'default'=>''),
        'xar_pid'      => array('type'=>'integer','null'=>FALSE,'default'=>'0'),
        'xar_htmlmail' => array('type'=>'integer','size'=>'tiny','null'=>FALSE,'default'=>'0')
    );

    // Create the table DDL
    $query = xarDBCreateTable($nwsltrAltSubscriptions,$fields);
    if (empty($query)) return; // throw back

    // Pass the Table Create DDL to adodb to create the table
    $dbconn->Execute($query);

    // Check for an error with the database
    if ($dbconn->ErrorNo() != 0) {
        $msg = xarMLByKey('DATABASE_ERROR', $query);
        xarErrorSet(XAR_SYSTEM_EXCEPTION, 'DATABASE_ERROR',
                    new SystemException(__FILE__.'('.__LINE__.'): '.$msg));
        return;
    }


    // If Categories API loaded and available, generate proprietary
    // module master category cid and child subcids
    $mastercid = 0;
    $numcats = 0;
    if(xarModIsAvailable('categories') && xarModAPILoad('categories', 'admin'))
    {
        $mastercid = xarModAPIFunc('categories',
                                   'admin', 
                                   'create', 
                                   Array('name' => 'Newsletter',
                                         'description' => 'Newsletter Categories',
                                         'parent_id' => 0));
        if (isset($mastercid))
            $numcats = 1;

        // Just in case creation of category blew up, 
        // free all error exceptions
        xarExceptionFree();
    }

    // Set the category cid so we can delete on removal
    xarModSetVar('newsletter', 'number_of_categories', $numcats);
    xarModSetVar('newsletter', 'mastercid', $mastercid);

    // Set up module variables
    xarModSetVar('newsletter', 'creategroups', 0);
    xarModSetVar('newsletter', 'publishername', '');
    xarModSetVar('newsletter', 'information', '');
    xarModSetVar('newsletter', 'privacypolicy', '');
    xarModSetVar('newsletter', 'itemsperpage', '10');
    xarModSetVar('newsletter', 'categorysort', '0');
    xarModSetVar('newsletter', 'linkexpiration', '60');
    xarModSetVar('newsletter', 'linkregistration', 'You must register on the website before you can view this story');
    xarModSetVar('newsletter', 'previewbrowser', 1);
    xarModSetVar('newsletter', 'commentarysource', '');
    xarModSetVar('newsletter', 'SupportShortURLs', 0);
    xarModSetVar('newsletter', 'bulkemail', 1);

    // Set default roles and privileges
    xarModSetVar('newsletter', 'publisher', 'NewsletterPublisher');
    xarModSetVar('newsletter', 'editor', 'NewsletterEditor');
    xarModSetVar('newsletter', 'writer', 'NewsletterWriter');

    // Set default publication template
    xarModSetVar('newsletter', 'templateHTML', 'publication-template-html.xd');
    xarModSetVar('newsletter', 'templateText', 'publication-template-text.xd');

    // Register Block types (this *should* happen at activation/deactivation)
    if (!xarModAPIFunc('blocks',
                       'admin',
                       'register_block_type',
                       array('modName'  => 'newsletter',
                             'blockType'=> 'information'))) return;

    // Define mask definitions for security checks
    xarRegisterMask('OverviewNewsletter','All','newsletter','All','All','ACCESS_OVERVIEW');
    xarRegisterMask('ReadNewsletter','All','newsletter','All','All','ACCESS_READ');
    xarRegisterMask('CommentNewsletter','All','newsletter','All','All','ACCESS_COMMENT');
    xarRegisterMask('ModerateNewsletter','All','newsletter','All','All','ACCESS_MODERATE');
    xarRegisterMask('EditNewsletter','All','newsletter','All','All','ACCESS_EDIT');
    xarRegisterMask('AddNewsletter','All','newsletter','All','All','ACCESS_ADD');
    xarRegisterMask('DeleteNewsletter','All','newsletter','All','All','ACCESS_DELETE');
    xarRegisterMask('AdminNewsletter','All','newsletter','All','All','ACCESS_ADMIN');

    // Define base masks by group
    xarModSetVar('newsletter', 'nwsltrmask', 'OverviewNewsletter');
    xarModSetVar('newsletter', 'publishermask', 'AdminNewsletter');
    xarModSetVar('newsletter', 'editormask', 'DeleteNewsletter');
    xarModSetVar('newsletter', 'writermask', 'AddNewsletter');

    // Define security instance definitions
    $nwsltrOwners = $xartable['nwsltrOwners'];
    $query = "SELECT xar_title, xar_id FROM " . $nwsltrOwners; 

    // Initialisation successful
    return true;
}


/**
 * Upgrade the Newsletter module from an old version
 */
function newsletter_upgrade($oldversion)
{
    // Load Table Maintainance API
    xarDBLoadTableMaintenanceAPI();

    // Set up database tables
    $dbconn =& xarDBGetConn();
    $xartable =& xarDBGetTables();
    
    // Get the newsletter publication table
    $nwsltrPublications = $xartable['nwsltrPublications'];
    
    // Upgrade dependent on old version number
    switch($oldversion) {
        case '1.0.0':
            // Code to upgrade from version 1.0.0 goes here

            // Add the column 'xar_subject' to the publications table
            $query = xarDBAlterTable($nwsltrPublications,
                                     array('command' => 'add',
                                           'field' => 'xar_subject',
                                           'type' => 'integer',
                                           'size' => 'tiny',
                                           'null' => false,
                                           'default' => '0'));
                                           
            $result = & $dbconn->Execute($query);
            if (!$result) return;
            
            // Set current subject to 0
            $query = "UPDATE $nwsltrPublications 
                      SET xar_subject = 0";

            $result =& $dbconn->Execute($query);
            if (!$result) return;
            
            // fall through to the next upgrade

        case '1.1.0':
            // Code to upgrade from version 1.1.0 goes here
            break;
            
        default:
            // Couldn't find a previous version to upgrade
            return;
    }

    // Update successful
    return true;
}


/**
 * Delete the Newsletter module
 */
function newsletter_delete()
{
    // Get datbase setup
    $dbconn =& xarDBGetConn();
    $xartable =& xarDBGetTables();

    // Load table maintenance API
    xarDBLoadTableMaintenanceAPI();

    // Generate the SQL to drop the table using the API
    $query = xarDBDropTable($xartable['nwsltrPublications']);
    if (empty($query)) return; // throw back

    // Drop the table and send exception if returns false.
    $result =& $dbconn->Execute($query);
    if (!$result) return;

    // Generate the SQL to drop the table using the API
    $query = xarDBDropTable($xartable['nwsltrIssues']);
    if (empty($query)) return; // throw back

    // Drop the table and send exception if returns false.
    $result =& $dbconn->Execute($query);
    if (!$result) return;

    // Generate the SQL to drop the table using the API
    $query = xarDBDropTable($xartable['nwsltrTopics']);
    if (empty($query)) return; // throw back

    // Drop the table and send exception if returns false.
    $result =& $dbconn->Execute($query);
    if (!$result) return;

    // Generate the SQL to drop the table using the API
    $query = xarDBDropTable($xartable['nwsltrOwners']);
    if (empty($query)) return; // throw back

    // Drop the table and send exception if returns false.
    $result =& $dbconn->Execute($query);
    if (!$result) return;

    // Generate the SQL to drop the table using the API
    $query = xarDBDropTable($xartable['nwsltrDisclaimers']);
    if (empty($query)) return; // throw back

    // Drop the table and send exception if returns false.
    $result =& $dbconn->Execute($query);
    if (!$result) return;

    // Generate the SQL to drop the table using the API
    $query = xarDBDropTable($xartable['nwsltrStories']);
    if (empty($query)) return; // throw back

    // Drop the table and send exception if returns false.
    $result =& $dbconn->Execute($query);
    if (!$result) return;

    // Generate the SQL to drop the table using the API
    $query = xarDBDropTable($xartable['nwsltrSubscriptions']);
    if (empty($query)) return; // throw back

    // Drop the table and send exception if returns false.
    $result =& $dbconn->Execute($query);
    if (!$result) return;

    // Generate the SQL to drop the table using the API
    $query = xarDBDropTable($xartable['nwsltrAltSubscriptions']);
    if (empty($query)) return; // throw back

    // Drop the table and send exception if returns false.
    $result =& $dbconn->Execute($query);
    if (!$result) return;

    if(xarModIsAvailable('categories'))
    {
        // Set the category cid so we can delete on removal
        $mastercid = xarModGetVar('newsletter', 'mastercid');

        xarModAPIFunc('categories',
                      'admin', 
                      'deletecat', 
                      Array('cid' => $mastercid));

    }

    // Delete Block types
    if (!xarModAPIFunc('blocks',
                       'admin',
                       'unregister_block_type',
                       array('modName'  => 'newsletter',
                             'blockType'=> 'information'))) return;

    // Remove privileges, security masks and instances
    xarRemoveMasks('newsletter');
    xarRemoveInstances('newsletter');
    xarRemovePrivileges('newsletter');

    // Remove publisher role
    $publisherRole = xarModGetVar('newsletter', 'publisher');
    $userData = xarFindRole($publisherRole);
    if ($userData) {
        xarModAPIFunc('roles',
                      'admin',
                      'deletegroup',
                      array('uid'  => $userData->uid));
    }

    // Remove editor role
    $editorRole = xarModGetVar('newsletter', 'editor');
    $userData = xarFindRole($editorRole);
    if ($userData) {
        xarModAPIFunc('roles',
                      'admin',
                      'deletegroup',
                      array('uid'  => $userData->uid));
    }
    
    // Remove writer role
    $writerRole = xarModGetVar('newsletter', 'writer');
    $userData = xarFindRole($writerRole);
    if ($userData) {
        xarModAPIFunc('roles',
                      'admin',
                      'deletegroup',
                      array('uid'  => $userData->uid));
    }

    
    // Delete any module variables
    xarModDelVar('newsletter', 'number_of_categories');
    xarModDelVar('newsletter', 'mastercid');
    xarModDelVar('newsletter', 'creategroups');
    xarModDelVar('newsletter', 'publishername');
    xarModDelVar('newsletter', 'information');
    xarModDelVar('newsletter', 'privacypolicy');
    xarModDelVar('newsletter', 'itemsperpage');
    xarModDelVar('newsletter', 'categorysort');
    xarModDelVar('newsletter', 'linkexpiration');
    xarModDelVar('newsletter', 'linkregistration');
    xarModDelVar('newsletter', 'templateHTML');
    xarModDelVar('newsletter', 'templateText');
    xarModDelVar('newsletter', 'publisher');
    xarModDelVar('newsletter', 'editor');
    xarModDelVar('newsletter', 'writer');
    xarModDelVar('newsletter', 'nwsltrmask');
    xarModDelVar('newsletter', 'publishermask');
    xarModDelVar('newsletter', 'editormask');
    xarModDelVar('newsletter', 'writermask');
    xarModDelVar('newsletter', 'previewbrowser');
    xarModDelVar('newsletter', 'commentarysource');
    xarModDelVar('newsletter', 'SupportShortURLs');
    xarModDelVar('newsletter', 'bulkemail');

    // Deletion successful
    return true;
}

?>
