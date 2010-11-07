<?php
/**
 * xarCacheManager version information
 *
 * @package modules
 * @copyright (C) 2002-2009 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage xarCacheManager module
 * @link http://xaraya.com/index.php/release/1652.html
 * @author jsb | mikespub
 */
$modversion['name']           = 'xarcachemanager';
$modversion['id']             = '1652';
$modversion['version']        = '2.0.1';
$modversion['displayname']    = xarML('xarCacheManager');
$modversion['description']    = 'Manage the output cache system of Xaraya';
$modversion['credits']        = '';
$modversion['help']           = '';
$modversion['changelog']      = '';
$modversion['license']        = '';
$modversion['official']       = 1;
$modversion['author']         = 'jsb | mikespub';
$modversion['contact']        = 'http://www.xaraya.com/';
$modversion['admin']          = 1;
$modversion['user']           = 0;
$modversion['securityschema'] = array('xarCacheManager::' => '::');
$modversion['class']          = 'Utility';
$modversion['category']       = 'Miscellaneous';
$modversion['dependencyinfo'] = array(
                                    0 => array(
                                            'name' => 'Xaraya Core',
                                            'version_ge' => '2.1.0',
                                            'version_le' => '2.1.99',
                                         ),
                                      );
?>
