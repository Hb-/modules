<?php
/**
 * Photoshare by Chris van de Steeg
 * based on Jorn Lind-Nielsen 's photoshare
 * module for PostNuke
 *
 * @package Xaraya eXtensible Management System
 * @copyright (C) 2002 by the Xaraya Development Team.
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 * @subpackage photoshare
 * @author Chris van de Steeg
 */

function photoshare_user_editfolder()
{
    if(!xarVarFetch('fid', 'int', $folderID,  NULL, XARVAR_GET_OR_POST)) {return;}
    if (!xarSecurityCheck('EditFolder')) return;

    $data = array();

    $data['folder'] = xarModAPIFunc('photoshare',
                                'user',
                                'getfolders',
                                array( 'folderID' => $folderID, 'prepareForDisplay' => true));
    if (!isset($data['folder'])) return;

    $data['folderID'] = $data['folder']['id'];
    $data['parentFolderID'] = $data['folder']['parentFolder'];

    $data['trail'] = xarModAPIFunc('photoshare',
                            'user',
                            'getfoldertrail',
                            array( 'folderID' => $folderID ));
    if (!isset($data['trail'])) return;

    if (!xarSecurityCheck('EditFolder', 0, 'folder', $data['folder']['id'].':'.$data['folder']['owner'].':'.$data['folder']['parentFolder']))
        return;

    // Add top menu
    $data['menuitems'] = xarModAPIFunc('photoshare', 'user', 'makemainmenu',
            array(    'gotoCurrentFolder' => true,
                    'menuHide' => false
                )
        );

    $data['actionUrl'] = xarModURL('photoshare', 'user', 'updatefolder', array('fid' => $folderID));
    $data['title'] = xarMl('Edit album');

    $templateName = xarModGetVar('photoshare', 'defaultTemplate');
    $data['viewTemplates'] = xarModAPIFunc(    'photoshare',
                                            'user',
                                            'gettemplates',
                                            array('currentTemplate' => $templateName) );

    return $data;
}

?>
