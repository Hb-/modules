<?php

/**
 * Generate theme translations
 *
 * @package modules
 * @copyright (C) 2003 by the Xaraya Development Team.
 * @link http://www.xaraya.com
 *
 * @subpackage modules
 * @author Marco Canini
 * @author Marcel van der Boom <marcel@xaraya.com>
*/


/**
 * generate translations for the specified theme
 * @param $args['themeid'] module registry identifier
 * @param $args['locale'] locale name
 * @returns array
 * @return statistics on generation process
 */
function translations_adminapi_generate_theme_trans($args)
{
    // Get arguments
    extract($args);

    // Argument check
    assert('isset($themeid) && isset($locale)');

    if (!($modinfo = xarModGetInfo($themeid,'theme'))) return;
    $themename = $modinfo['name'];
    $themedir = $modinfo['osdirectory'];

    // Security Check
    if(!xarSecurityCheck('AdminTranslations')) return;

    $time = explode(' ', microtime());
    $startTime = $time[1] + $time[0];

    $l = xarLocaleGetInfo($locale);
//    if ($l['charset'] == 'utf-8') {
//        $ref_locale = $locale;
//    } else {
//        $l['charset'] = 'utf-8';
//        $ref_locale = xarLocaleGetString($l);
//    }
        $ref_locale = $locale;

    $backend = xarModAPIFunc('translations','admin','create_backend_instance',array('interface' => 'ReferencesBackend', 'locale' => $ref_locale));
    if (!isset($backend)) return;
    if (!$backend->bindDomain(XARMLS_DNTYPE_THEME, $themename)) {
        $msg = xarML('Before generating translations you must first generate skels.');
        $link = array(xarML('Click here to proceed.'), xarModURL('translations', 'admin', 'update_info', array('dntype' => 'theme')));
        xarExceptionSet(XAR_USER_EXCEPTION, 'MissingSkels', new DefaultUserException($msg, $link));
        return;
    }

    $gen = xarModAPIFunc('translations','admin','create_generator_instance',array('interface' => 'TranslationsGenerator', 'locale' => $locale));
    if (!isset($gen)) return;
    if (!$gen->bindDomain(XARMLS_DNTYPE_THEME, $themename)) return;

    $theme_contexts_list[] = 'themes:'.$themename.'::common';

    //$subnames = xarModAPIFunc('translations','admin','get_theme_files',array('themedir'=>$themedir));
    //foreach ($subnames as $subname) {
    //    $theme_contexts_list[] = 'themes:'.$themename.'::'.$subname;
    //}

    $dirnames = xarModAPIFunc('translations','admin','get_theme_dirs',array('themedir'=>$themedir));
    $pattern = '/^([a-z\-_]+)\.xt$/i';
    foreach ($dirnames as $dirname) {
        $subnames = xarModAPIFunc('translations','admin','get_theme_files',
                              array('themedir'=>"themes/$themedir/$dirname",'pattern'=>$pattern));
        foreach ($subnames as $subname) {
            $theme_contexts_list[] = 'themes:'.$themename.':'.$dirname.':'.$subname;
        }
    }

    foreach ($theme_contexts_list as $theme_context) {
        list ($dntype1, $dnname1, $ctxtype1, $ctxname1) = explode(':',$theme_context);
        $ctxType = 'themes:'.$ctxtype1;
        $ctxName = $ctxname1;

        if (!$backend->loadContext($ctxType, $ctxName)) return;
        if (!$gen->create($ctxType, $ctxName)) return;

        if ($ctxtype1 != '') $sName = $ctxtype1 . "::" . $ctxName;
        else $sName = $ctxName;

        $statistics[$sName] = array('entries'=>0, 'keyEntries'=>0);
        while (list($string, $translation) = $backend->enumTranslations()) {
            $statistics[$sName]['entries']++;
            $gen->addEntry($string, $translation);
        }
        while (list($key, $translation) = $backend->enumKeyTranslations()) {
            $statistics[$sName]['keyEntries']++;
            $gen->addKeyEntry($key, $translation);
        }
        $gen->close();
        $backend->clear();
    }

    $time = explode(' ', microtime());
    $endTime = $time[1] + $time[0];

    return array('time' => $endTime - $startTime, 'statistics' => $statistics);
}
?>