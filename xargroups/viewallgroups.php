<?php
/**
 * XProject Module - A simple project management module
 *
 * @package modules
 * @copyright (C) 2002-2005 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage XProject Module
 * @link http://xaraya.com/index.php/release/665.html
 * @author XProject Module Development Team
 */
/**
 * view groups
 */
function xproject_groups_viewallgroups()
{
    $output = new xarHTML();

    xarSessionSetVar('groupid',0);
    xarSessionDelVar('grouxarame');

    $output->SetInputMode(_XH_VERBATIMINPUT);
    $func = xarVarCleanFromInput('func');
    if($func == "viewallgroups") $output->Text(xarModAPIFunc('xproject','user','menu'));

    $groups = xarModAPIFunc('xproject',
               'groups',
               'getall');

    $tableHead = array(xarML('Team'), _OPTION);

    $output->TableStart('', $tableHead, 1);

    foreach($groups as $group) {

    $actions = array();
    $output->SetOutputMode(_XH_RETURNOUTPUT);

    if (xarSecAuthAction(0, 'Groups::', "$group[name]::$group[gid]", ACCESS_EDIT)) {
        $grouxaramedisplay = $output->URL(xarModURL('xproject',
                                               'groups',
                                               'viewgroup', array('gid'   => $group['gid'],
                                                      'gname' => $group['name'])), xarVarPrepForDisplay($group['name']));
    } else {
        $grouxaramedisplay = $output->Text(xarVarPrepForDisplay($group['name']));
    }

    if (xarSecAuthAction(0, 'Groups::', "$group[name]::$group[gid]", ACCESS_EDIT)) {
        $actions[] = $output->URL(xarModURL('xproject',
                           'groups',
                           'modifygroup', array('gid'   => $group['gid'],
                                    'gname' => $group['name'])), xarML('Rename group'));

    }
    if (xarSecAuthAction(0, 'Groups::', "$group[name]::$group[gid]", ACCESS_DELETE)) {
        $actions[] = $output->URL(xarModURL('xproject',
                           'groups',
                           'deletegroup', array('gid'    => $group['gid'],
                                    'gname'  => $group['name'],
                                    'authid' => xarSecGenAuthKey())), _DELETE);
    }
    $output->SetOutputMode(_XH_KEEPOUTPUT);

    $actions = join(' | ', $actions);

    $row = array($grouxaramedisplay,
             $actions);

    $output->SetInputMode(_XH_VERBATIMINPUT);
    $output->TableAddRow($row);
    $output->SetInputMode(_XH_PARSEINPUT);
    }
    $output->TableEnd();

    return $output->GetOutput();
}

/*
 * viewgroup - view a group
 */
?>