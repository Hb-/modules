<?php
/**
 * Main banner admin function
 *
 * @package modules
 * @copyright (C) 2002-2005 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage xarlinkme
 * @link http://xaraya.com/index.php/release/889.html
 * @author jojodee <jojodee@xaraya.com>
 */
/**
 * the main administration function
 */
function xarlinkme_admin_banneradmin()
{
    if (!xarSecurityCheck('AdminxarLinkMe')) return;


    $data=array();
    // success
    return $data;
} 

?>
