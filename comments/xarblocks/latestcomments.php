<?php 
// File: $Id: s.latestcomments.php 1.7 03/01/11 09:46:40-05:00 John.Cox@mcnabb. $
// ----------------------------------------------------------------------
// Xaraya eXtensible Management System
// Copyright (C) 2002 by the Xaraya Development Team.
// http://www.xaraya.org
// ----------------------------------------------------------------------
// Original Author of file: Andrea Moro
// Purpose of file: Show latest posted comments
// ----------------------------------------------------------------------

/**
 * initialise block
 */
function comments_latestcommentsblock_init()
{
    return true;
}

/**
 * get information on block
 */
function comments_latestcommentsblock_info()
{
    // Values
    return array('text_type' => 'latestcomments',
                 'module' => 'comments',
                 'text_type_long' => 'Show Latest Comments',
                 'allow_multiple' => true,
                 'form_content' => false,
                 'form_refresh' => false,
                 'show_preview' => true);
}

/**
 * display block
 */      
function comments_latestcommentsblock_display($blockinfo)
{
    // Security check
// TODO: use some blocks mask & instance for security check
    if (!xarSecurityCheck('Comments-Read')) {
        return;
    }

    if (empty($blockinfo['content'])) {
        return '';
    }

    // Get variables from content block
    $vars = @unserialize($blockinfo['content']);

    // Defaults
    if (empty($vars['howmany'])) {
        $vars['howmany'] = 5;
    }
    if (empty($vars['modid'])) {
        $vars['modid'] = Array('all');
    }  
    if (!isset($vars['addauthor'])) {   
        $vars['addauthor'] = '1';
    }
    if (empty($vars['addmodule'])) {
        $vars['addmodule'] = '0';
    }
    if (!isset($vars['addcomment'])) {
        $vars['addcomment'] = '20';
    }
    if (!isset($vars['addobject'])) {
        $vars['addobject'] = '1';
    }
    if (empty($vars['adddate'])) { 
        $vars['adddate'] = 'on';
    }
    if (empty($vars['adddaysep'])) { 
        $vars['adddaysep'] = 'on';
    }
    if (empty($vars['truncate'])) {
        $vars['truncate'] = 18;
    }
    if (empty($vars['addprevious'])) {
        $vars['addprevious'] = 'on';
    }

    $vars['block_is_calling']=1;
    $vars['first']=1;
    $vars['order']='DESC';


    $blockinfo['content']=xarModFunc('comments', 'user', 'displayall', $vars) ;

    return $blockinfo;
}


/**
 * modify block settings
 */
function comments_latestcommentsblock_modify($blockinfo)
{
    // Get current content
    $vars = @unserialize($blockinfo['content']);

    // Defaults
    if (empty($vars['howmany'])) {
        $vars['howmany'] = 5;
    }
    if (empty($vars['modid'])) {
        $vars['modid'] = Array('all');
    }
    if (!isset($vars['addauthor'])) {
        $vars['addauthor'] = '1';
    }
    if (empty($vars['addmodule'])) {
        $vars['addmodule'] = '0';
    }
    if (!isset($vars['addcomment'])) {
        $vars['addcomment'] = '20';
    }
    if (!isset($vars['addobject'])) {
        $vars['addobject'] = '1';
    }
    if (empty($vars['adddate'])) {
        $vars['adddate'] = 'on';
    }
    if (empty($vars['adddaysep'])) {
        $vars['adddaysep'] = 'on';
    }
    if (empty($vars['truncate'])) {
        $vars['truncate'] = 18;
    }
    if (empty($vars['addprevious'])) {
        $vars['addprevious'] = 'on';
    }

    // get the list of modules+itemtypes that comments is hooked to
    $hookedmodules = xarModAPIFunc('modules', 'admin', 'gethookedmodules',
                                   array('hookModName' => 'comments'));

    $modlist = array();
    $modlist['all'] = xarML('All');
    if (isset($hookedmodules) && is_array($hookedmodules)) {
        foreach ($hookedmodules as $modname => $value) {
            // we have hooks for individual item types here
            if (!isset($value[0])) {
                // Get the list of all item types for this module (if any)
                $mytypes = xarModAPIFunc($modname,'user','getitemtypes',
                                         // don't throw an exception if this function doesn't exist
                                         array(), 0);
                foreach ($value as $itemtype => $val) {
                    if (isset($mytypes[$itemtype])) {
                        $type = $mytypes[$itemtype]['label'];
                    } else {
                        $type = xarML('type #(1)',$itemtype);
                    }
                    $modlist["$modname.$itemtype"] = ucwords($modname) . ' - ' . $type;
                }
            } else {
                $modlist[$modname] = ucwords($modname);
            }
        }
    }

    // Send content to template
    $output = xarTplBlock('comments','latestcommentsblockadmin', 
                          array(
                                'howmany' => $vars['howmany'],
                                'modid' => $vars['modid'],
                                'modlist' => $modlist,
                                'addauthor' => $vars['addauthor'],
                                'addmodule' => $vars['addmodule'],
                                'addcomment' => $vars['addcomment'],
                                'addobject' => $vars['addobject'],
                                'adddate' => $vars['adddate'],
                                'adddaysep' => $vars['adddaysep'],
                                'truncate' => $vars['truncate'],
                                'addprevious' => $vars['addprevious']
                                ));

    // Return output
    return $output;
}

/**
 * update block settings
 */
function comments_latestcommentsblock_update($blockinfo)
{

    $vars['howmany'] = xarVarCleanFromInput('howmany');
    $vars['modid']   = xarVarCleanFromInput('modid');
    $vars['pubtypeid']   = xarVarCleanFromInput('pubtypeid');
    $vars['addauthor'] = xarVarCleanFromInput('addauthor');
    $vars['addmodule'] = xarVarCleanFromInput('addmodule');
    $vars['addcomment'] = xarVarCleanFromInput('addcomment');
    $vars['addobject'] = xarVarCleanFromInput('addobject');
    $vars['adddate'] = xarVarCleanFromInput('adddate');
    $vars['adddaysep'] = xarVarCleanFromInput('adddaysep');
    $vars['truncate'] = xarVarCleanFromInput('truncate');    
    $vars['addprevious'] = xarVarCleanFromInput('addprevious');
    
    $blockinfo['content'] = serialize($vars);
    

    return $blockinfo;
}

?>
