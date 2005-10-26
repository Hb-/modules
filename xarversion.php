<?php
/**
 *
 * XarLDAP Administration
 *
 * @package Xaraya eXtensible Management System
 * @copyright (C) 2005 The Digital Development Foundation
 * @license GPL <http://www.gnu.org/licenses/gpl.html>
 * @link http://www.xaraya.com
 *
 * @subpackage xarldap
 * @author Richard Cave <rcave@xaraya.com>
*/

$modversion['name']           = 'xarldap';
$modversion['id']             = '25';
$modversion['version']        = '1.0.0';
$modversion['displayname']    = xarML('xarLDAP');
$modversion['description']    = 'LDAP API for Xaraya';
$modversion['credits']        = 'xardocs/credits.txt';
$modversion['help']           = 'xardocs/help.txt';
$modversion['changelog']      = 'xardocs/changelog.txt';
$modversion['license']        = 'xardocs/license.txt';
$modversion['official']       = 0;
$modversion['author']         = 'Richard Cave';
$modversion['contact']        = 'rcave@xaraya.com';
$modversion['admin']          = 1;
$modversion['user']           = 0;
$modversion['securityschema'] = array('xarldap::all' => '::');
$modversion['class']          = 'Complete';
$modversion['category']       = 'Global';
// this module requires the ldap extension
$modversion['extensions']     = array('ldap');
?>
