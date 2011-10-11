<?php
/**
 * Shouter Module
 *
 * @package modules
 * @subpackage shouter module
 * @category Third Party Xaraya Module
 * @version 2.0.0
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.zwiggybo.com
 * @link http://xaraya.com/index.php/release/236.html
 * @author Neil Whittaker
 */

/**
 * Delete a shouts
 *
 * @return bool
 */
function shouter_admin_delete($args)
{
    extract($args);

    if (!xarVarFetch('shoutid', 'id', $shoutid)) return;
    if (!xarVarFetch('objectid', 'id', $objectid, NULL, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('confirm', 'str:1:', $confirm, '', XARVAR_NOT_REQUIRED)) return;

    if (!empty($objectid)) {
        $shoutid = $objectid;
    }

    $item = xarModAPIFunc('shouter', 'user', 'get',
                    array('shoutid' => $shoutid));

    if (!isset($item) && xarCurrentErrorType() != XAR_NO_EXCEPTION) return;

    if (!xarSecurityCheck('ManageShouter', 1, 'Item', "$item[name]:All:$shoutid")) {
        return;
    }
    // Check for confirmation.
    if (empty($confirm)) {
        // No confirmation yet
        $data = xarModAPIFunc('shouter', 'admin', 'menu');
        // Specify for which item you want confirmation
        $data['shoutid'] = $shoutid;

        $data['itemid'] = xarML('Item ID');

        $data['authid'] = xarSecGenAuthKey();
        return $data;
    }

    if (!xarSecConfirmAuthKey()) return;

    if (!xarModAPIFunc('shouter', 'admin', 'delete',
                 array('shoutid' => $shoutid))) {
        return;
    }

    xarController::redirect(xarModURL('shouter', 'admin', 'view'));
}
?>