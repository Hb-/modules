<?php
/**
 * Example Block
 * 
 * @package Xaraya eXtensible Management System
 * @copyright (C) 2005 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage courses
 * @author Courses module development team 
 */

/**
 * modify block settings for "new"
 */
function courses_newblock_modify($blockinfo)
{
    // Get current content
    $vars = @unserialize($blockinfo['content']);
    // Defaults
    if (empty($vars['numitems'])) {
        $vars['numitems'] = 5;
    }
    // Send content to template
    $output = xarTplBlock('courses', 'newAdmin', array('numitems' => $vars['numitems'], 'blockid' => $blockinfo['bid']));
    // Return output
    return $output;
}

/**
 * update block settings
 */
function courses_newblock_update($blockinfo)
{
    if (!xarVarFetch('numitems', 'isset', $vars['numitems'], NULL, XARVAR_DONT_SET)) return;

    $blockinfo['content'] = serialize($vars);

    return $blockinfo;
}

?>