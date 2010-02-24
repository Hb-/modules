<?php
/**
 * Categories module
 *
 * @package modules
 * @copyright (C) 2002-2010 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage Categories Module
 * @link http://xaraya.com/index.php/release/147.html
 * @author Categories module development team
 * @author Jim McDonald, Fl?vio Botelho <nuncanada@xaraya.com>, mikespub <postnuke@mikespub.net>
 */
$modversion['name']         = 'categories';
$modversion['id']           = '147';
$modversion['version']      = '2.4.1';
$modversion['displayname']  = 'Categories';
$modversion['description']  = 'Categorised data utility';
$modversion['credits']      = 'xardocs/credits.txt';
$modversion['help']         = 'xardocs/help.txt';
$modversion['changelog']    = 'xardocs/changelog.txt';
$modversion['license']      = 'xardocs/license.txt';
$modversion['official']     = 1;
$modversion['author']       = 'Jim McDonald';
$modversion['contact']      = 'http://www.mcdee.net/';
$modversion['admin']        = 1;
$modversion['user']         = 0;
$modversion['class']        = 'Utility';
$modversion['category']     = 'Content';
$modversion['dependencyinfo']   = array(
                                    0 => array(
                                            'name' => 'core',
                                            'version_ge' => '1.2.0-b1'
                                         )
                                );

if (false) {
    xarML('Categories');
}
?>
