<?php

/**
 * File: $Id$
 *
 * Generate translations information
 *
 * @package modules
 * @copyright (C) 2003 by the Xaraya Development Team.
 * @link http://www.xaraya.com
 *
 * @subpackage translations
 * @author Marco Canini
 * @author Marcel van der Boom <marcel@xaraya.com>
*/

function translations_admin_generate_trans_info()
{
    // Security Check
    if(!xarSecurityCheck('AdminTranslations')) return;

    $tplData['locales'] = xarConfigGetVar('Site.MLS.AllowedLocales');
    $tplData['release_locale'] = translations_release_locale();
    $tplData['archiver_path'] = xarModAPIFunc('translations','admin','archiver_path');

    $druidbar = translations_create_generate_trans_druidbar(INFO);
    $opbar = translations_create_opbar(GEN_TRANS);
    $tplData = array_merge($tplData, $druidbar, $opbar);

    return $tplData;
}

?>