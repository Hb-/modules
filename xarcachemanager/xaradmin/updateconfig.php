<?php

/**
 * Update the configuration parameters of the module based on data from the modification form
 */
function xarcachemanager_admin_updateconfig()
{ 
    // Get parameters
    if (!xarVarFetch('cacheenabled',     'isset', $cacheenabled,     0,  XARVAR_NOT_REQUIRED)) { return; }
    if (!xarVarFetch('cachetheme',       'str::24', $cachetheme,     '',  XARVAR_NOT_REQUIRED)) { return; }
    if (!xarVarFetch('cachesizelimit',   'float:0.1:', $cachesizelimit,  '',  XARVAR_NOT_REQUIRED)) { return; }
    if (!xarVarFetch('cachepages',       'isset', $cachepages,       0,  XARVAR_NOT_REQUIRED)) { return; }
    if (!xarVarFetch('pageexpiretime',   'str:1:9', $pageexpiretime,  '0',  XARVAR_NOT_REQUIRED)) { return; }
    if (!xarVarFetch('cachedisplayview', 'isset', $cachedisplayview, 0,  XARVAR_NOT_REQUIRED)) { return; }
    if (!xarVarFetch('cachetimestamp',   'isset', $cachetimestamp,   0,  XARVAR_NOT_REQUIRED)) { return; }
    if (!xarVarFetch('expireheader', 	 'isset', $expireheader,   0,  XARVAR_NOT_REQUIRED)) { return; }
    if (!xarVarFetch('cacheblocks', 	 'isset', $cacheblocks, 0, XARVAR_NOT_REQUIRED)) { return; }
    if (!xarVarFetch('blockexpiretime',  'str:1:9', $blockexpiretime, '0',  XARVAR_NOT_REQUIRED)) { return; }

    // Confirm authorisation code
    if (!xarSecConfirmAuthKey()) return; 
    // Security Check
    if (!xarSecurityCheck('AdminXarCache')) return;

    // set the cache dir
    $varCacheDir = xarCoreGetVarDirPath() . '/cache';
    $outputCacheDir = $varCacheDir . '/output';

    // turn output caching system on or off
    if(!empty($cacheenabled)) {
        if(!file_exists($outputCacheDir . '/cache.touch')) {
            touch($outputCacheDir . '/cache.touch');
        }
    } else {
        if(file_exists($outputCacheDir . '/cache.touch')) {
            unlink($outputCacheDir . '/cache.touch');
        }
    }

    // turn page level oupt caching on or off
    if(!empty($cachepages)) {
        if(!file_exists($outputCacheDir . '/cache.pagelevel')) {
            touch($outputCacheDir . '/cache.pagelevel');
        }
    } else {
        if(file_exists($outputCacheDir . '/cache.pagelevel')) {
            unlink($outputCacheDir . '/cache.pagelevel');
        }
        if(file_exists($outputCacheDir . '/autocache.start')) {
            unlink($outputCacheDir . '/autocache.start');
        }
        if(file_exists($outputCacheDir . '/autocache.log')) {
            unlink($outputCacheDir . '/autocache.log');
        }
    }

    // turn block level ouput caching on or off 
    if ($cacheblocks) {
        if(!file_exists($outputCacheDir . '/cache.blocklevel')) {
            touch($outputCacheDir . '/cache.blocklevel');
        }
        // flush adminpanels blocks to show new options if necessary
        if (!function_exists('xarOutputFlushCached')) {
            include_once('includes/xarCache.php');
            xarCache_init(array('cacheDir' => $outputCacheDir));
        }
        $cacheKey = "adminpanels-blockid";
        xarOutputFlushCached($cacheKey);
    } else {
        if(file_exists($outputCacheDir . '/cache.blocklevel')) {
            unlink($outputCacheDir . '/cache.blocklevel');
        }
    }

    $cachesizelimit *= 1048576;
    
    //turn hh:mm:ss back into seconds
    $pageexpiretime = xarModAPIFunc( 'xarcachemanager', 'admin', 'convertseconds',
                                 array('starttime' => $pageexpiretime,
                                        'direction' => 'to'));
    $blockexpiretime = xarModAPIFunc( 'xarcachemanager', 'admin', 'convertseconds',
                                 array('starttime' => $blockexpiretime,
                                       'direction' => 'to'));

    if(!empty($cachedisplayview)) {
        $cachedisplayview = 1;
    } else {
        $cachedisplayview = 0;
    }
    if(!empty($cachetimestamp)) {
        $cachetimestamp = 1;
    } else {
        $cachetimestamp = 0;
    }
    if(empty($cachesizelimit)) {
        $cachesizelimit = 262144;
    }
    
    // updated the config.caching settings
    $cachingConfigFile = $varCacheDir . '/config.caching.php';
    
    if (!is_writable($cachingConfigFile)) {
        $msg=xarML('The caching configuration file is not writable by the web server.  
                   #(1) must be writable by the web server for 
                   the output caching to be managed by xarCacheManager.', $cachingConfigFile);
        xarExceptionSet(XAR_SYSTEM_EXCEPTION,'FUNCTION_FAILED',
                        new SystemException($msg));
        return false;
    }

    $cachingConfig = join('', file($cachingConfigFile));

    $cachingConfig = preg_replace('/\[\'Output.DefaultTheme\'\]\s*=\s*(\'|\")(.*)\\1;/', "['Output.DefaultTheme'] = '$cachetheme';", $cachingConfig);
    $cachingConfig = preg_replace('/\[\'Output.SizeLimit\'\]\s*=\s*(|\")(.*)\\1;/', "['Output.SizeLimit'] = $cachesizelimit;", $cachingConfig);
    $cachingConfig = preg_replace('/\[\'Page.TimeExpiration\'\]\s*=\s*(|\")(.*)\\1;/', "['Page.TimeExpiration'] = $pageexpiretime;", $cachingConfig);
    $cachingConfig = preg_replace('/\[\'Page.DisplayView\'\]\s*=\s*(|\")(.*)\\1;/', "['Page.DisplayView'] = $cachedisplayview;", $cachingConfig);
    $cachingConfig = preg_replace('/\[\'Page.ShowTime\'\]\s*=\s*(|\")(.*)\\1;/', "['Page.ShowTime'] = $cachetimestamp;", $cachingConfig);
    $cachingConfig = preg_replace('/\[\'Page.ExpireHeader\'\]\s*=\s*(|\")(.*)\\1;/', "['Page.ExpireHeader'] = $expireheader;", $cachingConfig);
    $cachingConfig = preg_replace('/\[\'Block.TimeExpiration\'\]\s*=\s*(|\")(.*)\\1;/', "['Block.TimeExpiration'] = $blockexpiretime;", $cachingConfig);

    $fp = fopen ($cachingConfigFile, 'wb');
    fwrite ($fp, $cachingConfig);
    fclose ($fp);

    // see if we need to flush the cache when a new comment is added for some item
    xarVarFetch('cacheflushcomment','isset',$cacheflushcomment,0,XARVAR_NOT_REQUIRED);
    if ($cacheflushcomment && $cachedisplayview) {
        xarModSetVar('xarcachemanager','FlushOnNewComment', 1);
    } else {
        xarModSetVar('xarcachemanager','FlushOnNewComment', 0);
    }

    // see if we need to flush the cache when a new rating is added for some item
    xarVarFetch('cacheflushrating','isset',$cacheflushrating,0,XARVAR_NOT_REQUIRED);
    if ($cacheflushrating  && $cachedisplayview) {
        xarModSetVar('xarcachemanager','FlushOnNewRating', 1);
    } else {
        xarModSetVar('xarcachemanager','FlushOnNewRating', 0);
    }

    // see if we need to flush the cache when a new vote is cast on poll hooked to some item
    xarVarFetch('cacheflushpollvote','isset',$cacheflushpollvote,0,XARVAR_NOT_REQUIRED);
    if ($cacheflushpollvote && $cachedisplayview) {
        xarModSetVar('xarcachemanager','FlushOnNewPollvote', 1);
    } else {
        xarModSetVar('xarcachemanager','FlushOnNewPollvote', 0);
    }

    xarResponseRedirect(xarModURL('xarcachemanager', 'admin', 'modifyconfig'));

    return true;
}

?>
