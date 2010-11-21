<?php
/**
 * Twitter Module
 *
 * @package modules
 * @copyright (C) 2009 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage Twitter Module
 * @link http://xaraya.com/index.php/release/991.html
 * @author Chris Powis (crisp@crispcreations.co.uk)
 */

/**
 * The main administration function
 *
 * @author Chris Powis (crisp@crispcreations.co.uk)
 * @access public
 * @return true
 */
function twitter_admin_main()
{
   /* Security Check */
    if (!xarSecurityCheck('AdminTwitter',0)) return;
   
    // get current module version for display
    $modname = 'twitter';
    $modid = xarMod::getRegID($modname);
    $modinfo = xarMod::getInfo($modid);
    $data['version'] = $modinfo['version'];
    return $data;

}
?>