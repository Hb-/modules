<?php

/**
 * File: $Id$
 *
 * Release core translations
 *
 * @package modules
 * @copyright (C) 2003 by the Xaraya Development Team.
 * @link http://www.xaraya.com
 *
 * @subpackage translations
 * @author Marco Canini
 * @author Marcel van der Boom <marcel@xaraya.com>
*/

function translations_adminapi_release_core_trans($args)
{
    // Get arguments
    extract($args);

    // Argument check
    assert('isset($locale)');

    if (!$bt = xarModAPIFunc('translations','admin','release_backend_type')) return;;
    // Security Check
    if(!xarSecurityCheck('AdminTranslations')) return;

    if ($bt != 'php') {
        $msg = xarML('Unsupported backend type \'#(1)\'. Don\'t know how to generate release package for that backend.', $bt);
        xarExceptionSet(XAR_USER_EXCEPTION, 'UnsupportedReleaseBackend', new DefaultUserException($msg));
        return;
    }

    $dirpath = "var/locales/$locale/php/core/";
    if (!file_exists($dirpath.'core.php')) {
        $msg = xarML('Before releasing translations package you must first generate translations.');
        $link = array(xarML('Click here to proceed.'), xarModURL('translations', 'admin', 'update_info', array('dntype' => 'core')));
        xarExceptionSet(XAR_USER_EXCEPTION, 'MissingTranslations', new DefaultUserException($msg, $link));
        return;
    }

    // return translations_make_package('xaraya', XARCORE_VERSION_NUM, $dirpath, $locale);
    $newargs['basefilename'] = 'xaraya';
    $newargs['version'] = XARCORE_VERSION_NUM;
    $newargs['dirpath'] = $dirpath;
    $newargs['locale'] = $locale;
    $backend = xarModAPIFunc('translations','admin','make_package',$newargs);
}

?>
