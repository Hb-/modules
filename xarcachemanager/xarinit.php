<?php
/**
 * File: $Id$
 *
 * xarCacheManager initialization functions
 *
 * @copyright (C) 2003 by the Xaraya Development Team.
 * @license GPL <http://www.gnu.org/licenses/gpl.html>
 * @link http://www.xaraya.com
 * @author jsb | mikespub
 */

/**
 * initialise the xarcachemanager module
 * This function is only ever called once during the lifetime of a particular
 * module instance
 */
function xarcachemanager_init()
{
    // set up the output cache directory
    $varCacheDir = xarCoreGetVarDirPath() . '/cache';

    if (is_writable($varCacheDir) || is_dir($varCacheDir.'/output')) {
        if (!is_dir($varCacheDir.'/output')) {
            // set up the output directorys
            $old_umask = umask(0);
            mkdir($varCacheDir.'/output', 0777);
            mkdir($varCacheDir.'/output/page', 0777);
            mkdir($varCacheDir.'/output/block', 0777);
            umask($old_umask);
        }
        if (!is_writable($varCacheDir.'/output')) {
            // tell them output dir needs to be writable
            $msg=xarML('The var/cache/output directory must be writable 
                       by the web server for output caching to work.  
                       The xarCacheManager module has not been installed, 
                       please make the var/cache/output directory 
                       writable by the web server before re-trying to 
                       install this module.');
            xarExceptionSet(XAR_SYSTEM_EXCEPTION,'FUNCTION_FAILED',
                            new SystemException($msg));
            return false;
        } else {
            $old_umask = umask(0);
            if (!is_dir($varCacheDir.'/output/page')) {
                mkdir($varCacheDir.'/output/page', 0777);
            }
            if (!is_dir($varCacheDir.'/output/block')) {
                mkdir($varCacheDir.'/output/block', 0777);
            }
            umask($old_umask);
        }
    } else {
        // tell them that cache needs to be writable or manually create output dir
        $msg=xarML('The var/cache directory must be writable 
                   by the web server for the install script to 
                   set up output caching for you.
                   The xarCacheManager module has not been installed, 
                   please make the var/cache directory 
                   writable by the web server before re-trying to 
                   install this module.  
                   Alternatively, you can manually create the 
                   var/cache/output directory and copy the 
                   xarcachemanager/config.caching.php.dist 
                   file to var/cache/config.caching.php - the output 
                   directory and the config.caching.php file must be 
                   writable by the web server for output caching to work.');
        xarExceptionSet(XAR_SYSTEM_EXCEPTION,'FUNCTION_FAILED',
                        new SystemException($msg));
        return false;
    }
    
    // avoid directory browsing
    if (!file_exists($varCacheDir.'/output/index.html')) {
        @touch($varCacheDir.'/output/index.html');
    }
    if (!file_exists($varCacheDir.'/output/page/index.html')) {
        @touch($varCacheDir.'/output/index.html');
    }
    if (!file_exists($varCacheDir.'/output/block/index.html')) {
        @touch($varCacheDir.'/output/index.html');
    }

    // set up the config file.
    $defaultConfigFile = 'modules/xarcachemanager/config.caching.php.dist';
    $cachingConfigFile = $varCacheDir .'/config.caching.php';
    if (!file_exists($defaultConfigFile)) {
        $msg=xarML('That is strange.  The default, distributed configuration 
                   file, normally #(1), seems to be 
                   missing.', $defaultConfigFile);
        xarExceptionSet(XAR_SYSTEM_EXCEPTION,'MODULE_FILE_NOT_EXIST',
                        new SystemException($msg));
        
        return false;
    }
    if (is_writable($varCacheDir) || is_writable($cachingConfigFile)) {
        $handle = fopen($defaultConfigFile, "rb");
        $defaultConfig = fread ($handle, filesize ($defaultConfigFile));
        $fp = @fopen($cachingConfigFile,"wb");
        fwrite($fp, $defaultConfig);
        fclose($fp);
    } else {
        // tell them that cache needs to be writable or manually create config file
        $msg=xarML('The var/cache directory must be writable 
                   by the web server for the install script to 
                   set up output caching for you.
                   The xarCacheManager module has not been installed, 
                   please make the var/cache directory 
                   writable by the web server before re-trying to 
                   install this module.  
                   Alternatively, you can manually copy the 
                   xarcachemanager/config.caching.php.dist 
                   file to var/cache/config.caching.php - the 
                   config.caching.php file must be writable by the
                   web server for output caching to be managed with
                   the xarcachemanager module.');
        xarExceptionSet(XAR_SYSTEM_EXCEPTION,'FUNCTION_FAILED',
                        new SystemException($msg));
        return false;
    }

    // Set up database tables
    $dbconn =& xarDBGetConn();
    $xartable =& xarDBGetTables();

    $cacheblockstable = $xartable['cache_blocks'];

    // Get a data dictionary object with item create methods.
    $datadict =& xarDBNewDataDict($dbconn, 'ALTERTABLE');

    // Table didn't exist, create table
    /*****************************************************************
    * CREATE TABLE xar_cache_blocks (
    * xar_bid int(11) NOT NULL default '0',
    * xar_nocache tinyint(4) NOT NULL default '0',
    * xar_page tinyint(4) NOT NULL default '0',
    * xar_user tinyint(4) NOT NULL default '0',
    * xar_expire int(11)
    * UNIQUE KEY `i_xar_cache_blocks_1` (`xar_bid`)
    * );
    *****************************************************************/
    
    $flds = "
        xar_bid             I           NotNull DEFAULT 0,
        xar_nocache         L           NotNull DEFAULT 0,
        xar_page            L           NotNull DEFAULT 0,
        xar_user            L           NotNull DEFAULT 0,
        xar_expire          I           Null
    ";
    
    // Create or alter the table as necessary.
    $result = $datadict->changeTable($cacheblockstable, $flds);    
    if (!$result) {return;}
    
    // Create a unique key on the xar_bid collumn
    $result = $datadict->createIndex('i_' . xarDBGetSiteTablePrefix() . '_cache_blocks_1',
                                     $cacheblockstable,
                                     'xar_bid',
                                     array('UNIQUE'));
    
    // Set up module variables
    xarModSetVar('xarcachemanager','FlushOnNewComment', 0);
    xarModSetVar('xarcachemanager','FlushOnNewRating', 0);
    xarModSetVar('xarcachemanager','FlushOnNewPollvote', 0);

    if (!xarModRegisterHook('item', 'create', 'API',
                            'xarcachemanager', 'admin', 'createhook')) {
        return false;
    }
    if (!xarModRegisterHook('item', 'update', 'API',
                            'xarcachemanager', 'admin', 'updatehook')) {
        return false;
    }
    if (!xarModRegisterHook('item', 'delete', 'API',
                            'xarcachemanager', 'admin', 'deletehook')) {
        return false;
    }
    if (!xarModRegisterHook('item', 'modify', 'GUI',
                            'xarcachemanager', 'admin', 'modifyhook')) {
        return false;
    }
    if (!xarModRegisterHook('module', 'updateconfig', 'API',
                            'xarcachemanager', 'admin', 'updateconfighook')) {
        return false;
    }

    // Enable xarcachemanager hooks for articles
    if (xarModIsAvailable('articles')) {
        xarModAPIFunc('modules','admin','enablehooks',
                      array('callerModName' => 'articles', 'hookModName' => 'xarcachemanager'));
    }
    // Enable xarcachemanager hooks for base
    if (xarModIsAvailable('base')) {
        xarModAPIFunc('modules','admin','enablehooks',
                      array('callerModName' => 'base', 'hookModName' => 'xarcachemanager'));
    }
    // Enable xarcachemanager hooks for blocks
    if (xarModIsAvailable('blocks')) {
        xarModAPIFunc('modules','admin','enablehooks',
                      array('callerModName' => 'blocks', 'hookModName' => 'xarcachemanager'));
    }
    // Enable xarcachemanager hooks for categories
    if (xarModIsAvailable('categories')) {
        xarModAPIFunc('modules','admin','enablehooks',
                      array('callerModName' => 'categories', 'hookModName' => 'xarcachemanager'));
    }
    // Enable xarcachemanager hooks for roles
    if (xarModIsAvailable('roles')) {
        xarModAPIFunc('modules','admin','enablehooks',
                      array('callerModName' => 'roles', 'hookModName' => 'xarcachemanager'));
    }
    // Enable xarcachemanager hooks for privileges
    if (xarModIsAvailable('privileges')) {
        xarModAPIFunc('modules','admin','enablehooks',
                      array('callerModName' => 'privileges', 'hookModName' => 'xarcachemanager'));
    }

    // set up permissions masks.
    xarRegisterMask('ReadXarCache', 'All', 'xarcachemanager', 'Item', 'All:All:All', 'ACCESS_READ');
    xarRegisterMask('AdminXarCache', 'All', 'xarcachemanager', 'Item', 'All:All:All', 'ACCESS_ADMIN');

    // Initialisation successful
    return true;
}

/**
 * upgrade the xarcachemanager module from an old version
 * This function can be called multiple times
 */
function xarcachemanager_upgrade($oldversion)
{
    $varCacheDir = xarCoreGetVarDirPath() . '/cache';
    $defaultConfigFile = 'modules/xarcachemanager/config.caching.php.dist';
    $cachingConfigFile = $varCacheDir . '/config.caching.php';

    // Upgrade dependent on old version number
    switch ($oldversion) {
        case 0.1:
            // Code to upgrade from the 0.1 version (base page level caching)
            // Do conversion of MB to bytes in config file
            include($cachingConfigFile);
            $cachesizelimit = $cachingConfiguration['Output.SizeLimit'] * 1048576;
            $cachingConfig = join('', file($cachingConfigFile));
            $cachingConfig = preg_replace('/\[\'Output.SizeLimit\'\]\s*=\s*(|\")(.*)\\1;/', "['Output.SizeLimit'] = $cachesizelimit;", $cachingConfig);
            $fp = fopen ($cachingConfigFile, 'wb');
            fwrite ($fp, $cachingConfig);
            fclose ($fp);
        case 0.2:
        case '0.2.0':
            // Code to upgrade from the 0.2 version (cleaned-up page level caching)
            // Bring the config file up to current version
            include($cachingConfigFile);
            $xarPage_cacheTime = $cachingConfiguration['Page.TimeExpiration'];
            $xarPage_cacheTheme = $cachingConfiguration['Page.DefaultTheme'];
            $xarPage_cacheDisplay = $cachingConfiguration['Page.DisplayView'];
            $xarPage_cacheShowTime = $cachingConfiguration['Page.ShowTime'];
            $xarOutput_cacheSizeLimit = $cachingConfiguration['Output.SizeLimit'];
            @unlink($cachingConfigFile);
            $handle = fopen($defaultConfigFile, "rb");
            $defaultConfig = fread ($handle, filesize ($defaultConfigFile));
            $fp = @fopen($cachingConfigFile,"wb");
            fwrite($fp, $defaultConfig);
            fclose($fp);
            $cachingConfig = join('', file($cachingConfigFile));
            $cachingConfig = preg_replace('/\[\'Output.DefaultTheme\'\]\s*=\s*(\'|\")(.*)\\1;/', "['Output.DefaultTheme'] = '$xarPage_cacheTheme';", $cachingConfig);
            $cachingConfig = preg_replace('/\[\'Output.SizeLimit\'\]\s*=\s*(|\")(.*)\\1;/', "['Output.SizeLimit'] = $xarOutput_cacheSizeLimit;", $cachingConfig);
            $cachingConfig = preg_replace('/\[\'Page.TimeExpiration\'\]\s*=\s*(|\")(.*)\\1;/', "['Page.TimeExpiration'] = $xarPage_cacheTime;", $cachingConfig);
            $cachingConfig = preg_replace('/\[\'Page.DisplayView\'\]\s*=\s*(|\")(.*)\\1;/', "['Page.DisplayView'] = $xarPage_cacheDisplay;", $cachingConfig);
            $cachingConfig = preg_replace('/\[\'Page.ShowTime\'\]\s*=\s*(|\")(.*)\\1;/', "['Page.ShowTime'] = $xarPage_cacheShowTime;", $cachingConfig);
            $fp = fopen ($cachingConfigFile, 'wb');
            fwrite ($fp, $cachingConfig);
            fclose ($fp);
            // Add new table to DB
            $dbconn =& xarDBGetConn();
            $xartable =& xarDBGetTables();
            $cacheblockstable = $xartable['cache_blocks'];
            $datadict =& xarDBNewDataDict($dbconn, 'ALTERTABLE');
            $flds = "
                    xar_bid             I           NotNull DEFAULT 0,
                    xar_nocache         L           NotNull DEFAULT 0,
                    xar_page            L           NotNull DEFAULT 0,
                    xar_user            L           NotNull DEFAULT 0,
                    xar_expire          I           Null
                    ";
            $result = $datadict->changeTable($cacheblockstable, $flds);    
            if (!$result) {return;}
            // Register new Admin Modify GUI Hook
            if (!xarModRegisterHook('item', 'modify', 'GUI',
                                    'xarcachemanager', 'admin', 'modifyhook')) {
                return false;
            }
        case '0.3.0':
            // Code to upgrade from the 0.3.0
            // Bring the config file up to current version            
            include($cachingConfigFile);
            $cachetheme = $cachingConfiguration['Output.DefaultTheme'];
            $cachesizelimit = $cachingConfiguration['Output.SizeLimit'];
            $pageexpiretime = $cachingConfiguration['Page.TimeExpiration'];
            $cachedisplayview = $cachingConfiguration['Page.DisplayView'];
            $cachetimestamp = $cachingConfiguration['Page.ShowTime'];
            $blockexpiretime = $cachingConfiguration['Block.TimeExpiration'];
            if(isset($cachingConfiguration['Page.CacheGroups'])) {
            	$cachegroups = $cachingConfiguration['Page.CacheGroups'];
            }
            @unlink($cachingConfigFile);
            $handle = fopen($defaultConfigFile, "rb");
            $defaultConfig = fread ($handle, filesize ($defaultConfigFile));
            $fp = @fopen($cachingConfigFile,"wb");
            fwrite($fp, $defaultConfig);
            fclose($fp);
            $cachingConfig = join('', file($cachingConfigFile));
            $cachingConfig = preg_replace('/\[\'Output.DefaultTheme\'\]\s*=\s*(\'|\")(.*)\\1;/', "['Output.DefaultTheme'] = '$cachetheme';", $cachingConfig);
            $cachingConfig = preg_replace('/\[\'Output.SizeLimit\'\]\s*=\s*(|\")(.*)\\1;/', "['Output.SizeLimit'] = $cachesizelimit;", $cachingConfig);
            $cachingConfig = preg_replace('/\[\'Page.TimeExpiration\'\]\s*=\s*(|\")(.*)\\1;/', "['Page.TimeExpiration'] = $pageexpiretime;", $cachingConfig);
            $cachingConfig = preg_replace('/\[\'Page.DisplayView\'\]\s*=\s*(|\")(.*)\\1;/', "['Page.DisplayView'] = $cachedisplayview;", $cachingConfig);
            $cachingConfig = preg_replace('/\[\'Page.ShowTime\'\]\s*=\s*(|\")(.*)\\1;/', "['Page.ShowTime'] = $cachetimestamp;", $cachingConfig);
            $cachingConfig = preg_replace('/\[\'Block.TimeExpiration\'\]\s*=\s*(|\")(.*)\\1;/', "['Block.TimeExpiration'] = $blockexpiretime;", $cachingConfig);
            if(isset($cachegroups)) {
            	$cachingConfig = preg_replace('/\[\'Page.CacheGroups\'\]\s*=\s*(\'|\")(.*)\\1;/', "['Page.CacheGroups'] = '$cachegroups';", $cachingConfig);
            }

            $fp = fopen ($cachingConfigFile, 'wb');
            fwrite ($fp, $cachingConfig);
            fclose ($fp);
            // Add the unique index that was added with this release
            $dbconn =& xarDBGetConn();
            $xartable =& xarDBGetTables();
            $datadict =& xarDBNewDataDict($dbconn, 'ALTERTABLE');
            $cacheblockstable = $xartable['cache_blocks'];
            $result = $datadict->createIndex('i_' . xarDBGetSiteTablePrefix() . '_cache_blocks_1',
                                             $cacheblockstable,
                                             'xar_bid',
                                             array('UNIQUE'));
            // switch to the file bashed block caching enabler
            if (xarModGetVar('xarcachemanager', 'CacheBlockOutput')) {
            	$outputCacheDir = $varCacheDir . '/output/';
            	if(!file_exists($outputCacheDir . 'cache.blocklevel')) {
            		touch($outputCacheDir . 'cache.blocklevel');
        		}
        		xarModDelVar('xarcachemanager', 'CacheBlockOutput');
        	}
        case '0.3.1':
            // Code to upgrade from the 0.3.1 version (base block level caching)
            // Bring the config file up to current version
            include($cachingConfigFile);
            $cachetheme = $cachingConfiguration['Output.DefaultTheme'];
            $cachesizelimit = $cachingConfiguration['Output.SizeLimit'];
            $pageexpiretime = $cachingConfiguration['Page.TimeExpiration'];
            $cachedisplayview = $cachingConfiguration['Page.DisplayView'];
            $cachetimestamp = $cachingConfiguration['Page.ShowTime'];
            $expireheader = $cachingConfiguration['Page.ExpireHeader'];
            $cachegroups = $cachingConfiguration['Page.CacheGroups'];
            $blockexpiretime = $cachingConfiguration['Block.TimeExpiration'];
            if(isset($cachingConfiguration['Page.SessionLess'])) {
                $sessionlessarray = $cachingConfiguration['Page.SessionLess'];
                $sessionlesslist = "'" . join("','", $sessionlessarray) . "'";
            }
            if(isset($cachingConfiguration['AutoCache.Period'])) {
                $autocacheperiod = $cachingConfiguration['AutoCache.Period'];
            }
            if(isset($cachingConfiguration['AutoCache.Period'])) {
                $autocachethreshold = $cachingConfiguration['AutoCache.Threshold'];
            }
            if(isset($cachingConfiguration['AutoCache.Period'])) {
                $autocachemaxpages = $cachingConfiguration['AutoCache.MaxPages'];
            }
            if(isset($cachingConfiguration['Page.SessionLess'])) {
                $includearray = $cachingConfiguration['AutoCache.Include'];
                $includelist = "'" . join("','", $includearray) . "'";
            }
            if(isset($cachingConfiguration['Page.SessionLess'])) {
                $excludearray = $cachingConfiguration['AutoCache.Exclude'];
                $excludelist = "'" . join("','", $excludearray) . "'";
            }
            @unlink($cachingConfigFile);
            $handle = fopen($defaultConfigFile, "rb");
            $defaultConfig = fread ($handle, filesize ($defaultConfigFile));
            $fp = @fopen($cachingConfigFile,"wb");
            fwrite($fp, $defaultConfig);
            fclose($fp);
            $cachingConfig = join('', file($cachingConfigFile));
            $cachingConfig = preg_replace('/\[\'Output.DefaultTheme\'\]\s*=\s*(\'|\")(.*)\\1;/', "['Output.DefaultTheme'] = '$cachetheme';", $cachingConfig);
            $cachingConfig = preg_replace('/\[\'Output.SizeLimit\'\]\s*=\s*(|\")(.*)\\1;/', "['Output.SizeLimit'] = $cachesizelimit;", $cachingConfig);
            $cachingConfig = preg_replace('/\[\'Page.TimeExpiration\'\]\s*=\s*(|\")(.*)\\1;/', "['Page.TimeExpiration'] = $pageexpiretime;", $cachingConfig);
            $cachingConfig = preg_replace('/\[\'Page.DisplayView\'\]\s*=\s*(|\")(.*)\\1;/', "['Page.DisplayView'] = $cachedisplayview;", $cachingConfig);
            $cachingConfig = preg_replace('/\[\'Page.ShowTime\'\]\s*=\s*(|\")(.*)\\1;/', "['Page.ShowTime'] = $cachetimestamp;", $cachingConfig);
            $cachingConfig = preg_replace('/\[\'Page.ExpireHeader\'\]\s*=\s*(|\")(.*)\\1;/', "['Page.ExpireHeader'] = $expireheader;", $cachingConfig);
            $cachingConfig = preg_replace('/\[\'Page.CacheGroups\'\]\s*=\s*(\'|\")(.*)\\1;/', "['Page.CacheGroups'] = '$cachegroups';", $cachingConfig);
            $cachingConfig = preg_replace('/\[\'Block.TimeExpiration\'\]\s*=\s*(|\")(.*)\\1;/', "['Block.TimeExpiration'] = $blockexpiretime;", $cachingConfig);
            if(isset($sessionlesslist)) {
                $cachingConfig = preg_replace('/\[\'Page.SessionLess\'\]\s*=\s*array\s*\((.*)\)\s*;/i', "['Page.SessionLess'] = array($sessionlesslist);", $cachingConfig);
            }
            if(isset($autocacheperiod)) {
                $cachingConfig = preg_replace('/\[\'AutoCache.Period\'\]\s*=\s*(.*?);/', "['AutoCache.Period'] = $autocacheperiod;", $cachingConfig);
            }
            if(isset($autocachethreshold)) {
                $cachingConfig = preg_replace('/\[\'AutoCache.Threshold\'\]\s*=\s*(.*?);/', "['AutoCache.Threshold'] = $autocachethreshold;", $cachingConfig);
            }
            if(isset($autocachemaxpages)) {
                $cachingConfig = preg_replace('/\[\'AutoCache.MaxPages\'\]\s*=\s*(.*?);/', "['AutoCache.MaxPages'] = $autocachemaxpages;", $cachingConfig);
            }
            if(isset($includelist)) {
                $cachingConfig = preg_replace('/\[\'AutoCache.Include\'\]\s*=\s*array\s*\((.*)\)\s*;/i', "['AutoCache.Include'] = array($includelist);", $cachingConfig);
            }
            if(isset($excludelist)) {
                $cachingConfig = preg_replace('/\[\'AutoCache.Exclude\'\]\s*=\s*array\s*\((.*)\)\s*;/i', "['AutoCache.Exclude'] = array($excludelist);", $cachingConfig);
            }

            $fp = fopen ($cachingConfigFile, 'wb');
            fwrite ($fp, $cachingConfig);
            fclose ($fp);
            
            // set up the new output sub-directorys
            $varCacheDir = xarCoreGetVarDirPath() . '/cache';
            $old_umask = umask(0);
            if (!is_dir($varCacheDir.'/output/page')) {
                mkdir($varCacheDir.'/output/page', 0777);
            }
            if (!is_dir($varCacheDir.'/output/block')) {
                mkdir($varCacheDir.'/output/block', 0777);
            }
            umask($old_umask);
            
            // since we've moved around where output will be cached, flush everything out
            if (!function_exists('xarOutputFlushCached')) {
                include_once('includes/xarCache.php');
                xarCache_init(array('cacheDir' => $varCacheDir . '/output'));
            }
            xarOutputFlushCached('');
            break;
        case '0.3.2':
            // Code to upgrade from the 0.3.1 version (base block level caching)
            break;
        case '0.4.0':
            // Code to upgrade from the 0.4.0 version (base module level caching)
            break;
        case '1.0.0':
            // Code to upgrade from version 1.0.0 goes here
            break;
        case '2.0.0':
            // Code to upgrade from version 2.0.0 goes here
            break;
    }
    // Update successful
    return true;
}

/**
 * delete the xarcachemanager module
 * This function is only ever called once during the lifetime of a particular
 * module instance
 */
function xarcachemanager_delete()
{
    //if still there, remove the cache.touch file, this turns everything off
    $varCacheDir = xarCoreGetVarDirPath() . '/cache';
    if (file_exists($varCacheDir . '/output') && is_dir($varCacheDir . '/output')) {
        if (file_exists($varCacheDir . '/output/cache.touch')) {
            @unlink($varCacheDir . '/output/cache.touch');
        }

        // clear out the cache
        if ($handle = @opendir($varCacheDir . '/output')) {
            while (($file = readdir($handle)) !== false) {
                $cache_file = $varCacheDir . '/output/' . $file;
                if (is_file($cache_file)) {
                    @unlink($cache_file);
                }
            }
            closedir($handle);
        }

        // remove the output cache directory
        @rmdir($varCacheDir . '/output');
    }

    // remove the caching config file
    if (file_exists($varCacheDir . '/config.caching.php')) {
        @unlink($varCacheDir . '/config.caching.php');
    }

    // Remove module hooks
    if (!xarModUnregisterHook('item', 'create', 'API',
                              'xarcachemanager', 'admin', 'createhook')) {
        return false;
    }
    if (!xarModUnregisterHook('item', 'update', 'API',
                              'xarcachemanager', 'admin', 'updatehook')) {
        return false;
    }
    if (!xarModUnregisterHook('item', 'delete', 'API',
                              'xarcachemanager', 'admin', 'deletehook')) {
        return false;
    }
    if (!xarModUnregisterHook('item', 'modify', 'GUI',
                              'xarcachemanager', 'admin', 'modifyhook')) {
        return false;
    }
    if (!xarModUnregisterHook('module', 'updateconfig', 'API',
                              'xarcachemanager', 'admin', 'updateconfighook')) {
        return false;
    }

    // Drop the tables
    $dbconn =& xarDBGetConn();
    $xartable =& xarDBGetTables();
    $datadict =& xarDBNewDataDict($dbconn, 'ALTERTABLE');
    
    $cacheblockstable = $xartable['cache_blocks'];
    $result = $datadict->dropTable($cacheblockstable);

    // Remove module variables
    xarModDelVar('xarcachemanager','FlushOnNewComment');
    xarModDelVar('xarcachemanager','FlushOnNewRating');
    xarModDelVar('xarcachemanager','FlushOnNewPollvote');


    // Remove Masks and Instances
    xarRemoveMasks('xarcachemanager');

    // Deletion successful
    return true;
} 

?>
