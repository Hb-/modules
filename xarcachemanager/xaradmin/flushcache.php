<?php

/**
 * Flush cache files for a given cacheKey
 *
 * @package Xaraya eXtensible Management System
 * @copyright (C) 2003 Xaraya
 * @link http://www.xaraya.com
 *
 * @subpackage xarCacheManager module
 * @author jsb
*/

function xarcachemanager_admin_flushcache($args)
{
    // Security Check
    if (!xarSecurityCheck('AdminXarCache')) return;

    extract($args);

    if (!xarVarFetch('flushkey', 'str', $flushkey, '', XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('confirm', 'str:1:', $confirm, '', XARVAR_NOT_REQUIRED)) return;
    
    $outputCacheDir = xarCoreGetVarDirPath() . '/cache/output/';
    $outputDirs = xarModAPIFunc( 'xarcachemanager', 'admin', 'getcachedirs', $outputCacheDir);

    if (empty($confirm)) {

        $data = array();

        $data['message']    = false;
        $data['cachekeys'] = xarModAPIFunc( 'xarcachemanager', 'admin', 'getcachekeys', $outputCacheDir);
        $data['cachedirs'] = $outputDirs;

        if (!$data['cachekeys']) {
            $data['empty']  = true;
        } else {
            $data['empty']  = false;
        }

        $data['instructions'] = xarML("Please select a cache key to be flushed.");
        $data['instructionhelp'] = xarML("All cached files of output associated with this key will be deleted.");

        // Generate a one-time authorisation code for this operation
        $data['authid'] = xarSecGenAuthKey();

    } else {

        // Confirm authorisation code.
        if (!xarSecConfirmAuthKey()) return;
        
        //Make sure xarCache is included so you can delete cacheKeys even if caching is disabled
        if (!file_exists($outputCacheDir . 'cache.touch')) {
            include_once('includes/xarCache.php');
            xarCache_init(array('cacheDir' => $outputCacheDir));
        }

        //Make sure their is an authkey selected
        if ($flushkey == '-') {
            $data['notice'] = xarML("You must select a cache key to flush.  If there is no cache key to select the output cache is empty.");
        } else {
            if (!empty($flushkey) && !strpos($flushkey, '-')) {
                xarOutputFlushCached('', $flushkey);
            } else {
                xarOutputFlushCached($flushkey);
            }
            $data['notice'] = xarML("Cached #(1) files have been successfully flushed.", $flushkey);
        }
        
        $data['returnlink'] = Array('url'   => xarModURL('xarcachemanager',
                                                         'admin',
                                                         'flushcache'),
                                    'title' => xarML('Return to the cache key selector'),
                                    'label' => xarML('Back'));

        $data['message'] = true;
    }
    
    $data['cachesize'] = round(xarCacheGetDirSize($outputCacheDir) / 1048576, 2) . ' MB';
    
    foreach ($outputDirs as $dirname => $dirpath) {
        $data['outputdirs'][] = array('name' => $dirname,
                                      'size' => round(xarCacheGetDirSize($dirpath) / 1048576, 2) . ' MB'
                                      );
    }

    return $data;
}
?>
