<?php
/**
 * Messages Module
 *
 * @package modules
 * @copyright (C) copyright-placeholder
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage Messages Module
 * @link http://xaraya.com/index.php/release/6.html
 * @author XarayaGeek
 */
function messages_admin_view($args)
{
    if (!xarVarFetch('itemtype', 'int', $itemtype, 0,XARVAR_NOT_REQUIRED)) return;

    switch( $itemtype ) {

        case 1:
            return xarMod::apiFunc('messages', 'admin', 'view' );


        default:
            return messages_admin_common('Main Page'); }
}

?>