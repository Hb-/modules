<?php
/**
 * Photoshare by Jorn Lind-Nielsen (C) 2002.
 *
 * @copyright (C) 2003 by the Xaraya Development Team.
 * @license GPL <http://www.gnu.org/licenses/gpl.html>
 * @link http://www.xaraya.com
 * @subpackage photoshare
 * @author Jorn Lind-Nielsen / Chris van de Steeg
 */

function photoshare_user_deletefolder_confirmed()
{
	if(!xarVarFetch('fid', 'isset:int', $folderID,  NULL, XARVAR_GET_OR_POST)) {return;}
	if(!xarVarFetch('deletesubfolders', 'isset:bool', $deleteSubFolders,  NULL, XARVAR_GET_OR_POST)) {return;}

	// Confirm authorisation code
    if (!xarSecConfirmAuthKey()) return;

	if (!xarSecurityCheck('DeleteFolder')) return;

	$data = array();

	$folder = xarModAPIFunc('photoshare',
						'user',
						'getfolders',
						array( 'folderID' => $folderID ));

	if (!isset($folder)) return;

	if (!xarSecurityCheck('DeleteFolder', 1, 'folder', "$folder[id]:$folder[owner]:$folder[parentfolder]")) return;

	$ok = xarModAPIFunc('photoshare',
						'user',
						'deletefolder',
						array( 	'folderID' => $folder['id'],
								'deleteSubFolders' => $deleteSubFolders ));

	if (!isset($ok)) return;

	xarResponseRedirect(xarModURL('photoshare', 'user', 'view', array('fid' => $folder['parentfolder'])));
	return true;
}

?>
