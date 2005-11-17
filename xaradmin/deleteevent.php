<?php
/**
 * Delete an event
 *
 * @package Xaraya eXtensible Management System
 * @copyright (C) 2002-2005 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage Julian Module
 * @link http://xaraya.com/index.php/release/319.html
 * @author Julian Module Development Team
 */

/**
 * delete item
 * This is a standard function that is called whenever an administrator
 * wishes to delete a current module item.
 *
 * @param  id 'event_id' the id of the event to be deleted
 *
 */
function julian_admin_deleteevent($args)
{
    extract($args);

    if (!xarVarFetch('event_id', 'id',    $event_id)) return;
    if (!xarVarFetch('objectid', 'id',    $objectid, NULL, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('cal_date', 'isset', $cal_date)) return;

    if (!empty($objectid)) {
        $event_id = $objectid;
    }

    // Get item
    $item = xarModAPIFunc('julian',
        'user',
        'get',
        array('event_id' => $event_id));
    // Check for exceptions
    if (!isset($item) && xarCurrentErrorType() != XAR_NO_EXCEPTION) return; // throw back

    if (!xarSecurityCheck('Editjulian')) return;

    if (!xarSecConfirmAuthKey()) return;

    if (!xarModAPIFunc('julian',
            'admin',
            'deleteevent',
            array('event_id' => $event_id))) {
        return; // throw back
    }
    xarResponseRedirect(xarModURL('julian', 'user', 'month',array('cal_date'=>$cal_date)));
    // Return
    return true;
}
?>