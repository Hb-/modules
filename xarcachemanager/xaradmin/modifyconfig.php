<?php

/**
 * Prep the configuration parameters of the module for the modification form
 * 
 * @author jsb | mikespub
 * @access public 
 * @param no $ parameters
 * @return $data (array of values for admin modify template) on success or false on failure
 * @throws MODULE_FILE_NOT_EXIST
 * @todo nothing
 */
function xarcachemanager_admin_modifyconfig()
{ 
    // Security Check
    if (!xarSecurityCheck('AdminXarCache')) return;

    $data = array();

    $varCacheDir = xarCoreGetVarDirPath() . '/cache';
    
    // is output caching enabled?
    if (file_exists($varCacheDir . '/output/cache.touch')) {
        $data['CachingEnabled'] = 1;
    } else {
        $data['CachingEnabled'] = 0;
    }

    // is page level output caching enbabled?
    if (file_exists($varCacheDir . '/output/cache.pagelevel')) {
        $data['pageCachingEnabled'] = 1;
    } else {
        $data['pageCachingEnabled'] = 0;
    }
    
    // is block level output caching enabled?
    if (file_exists($varCacheDir . '/output/cache.blocklevel')) {
        $data['blockCachingEnabled'] = 1;
    } else {
        $data['blockCachingEnabled'] = 0;
    }
    
    // get the caching config settings from the config file
    $data['settings'] = xarModAPIFunc('xarcachemanager', 'admin', 'get_cachingconfig',
                                         array('from' => 'file', 'tpl_prep' => TRUE));

    // set some default values
    if(!isset($data['settings']['OutputSizeLimit'])) {
        $data['settings']['OutputSizeLimit'] = 262144;
    }
    if(!isset($data['settings']['PageTimeExpiration'])) {
        $data['settings']['PageTimeExpiration'] = 1800;
    }
    if(!isset($data['settings']['PageDisplayView'])) {
        $data['settings']['PageDisplayView'] = 0;
    }
    if(!isset($data['settings']['PageViewTime'])) {
        $data['settings']['PageViewTime'] = 0;
    }
    if(!isset($data['settings']['PageExpireHeader'])) {
        $data['settings']['PageExpireHeader'] = 1;
    }
    if(!isset($data['settings']['BlockTimeExpiration'])) {
        $data['settings']['BlockTimeExpiration'] = 7200;
    }

    // convert the size limit from bytes to megabytes
    $data['settings']['OutputSizeLimit'] /= 1048576;

    // reformat seconds as hh:mm:ss
    $data['settings']['PageTimeExpiration'] = xarModAPIFunc( 'xarcachemanager', 'admin', 'convertseconds',
                                                             array('starttime' => $data['settings']['PageTimeExpiration'],
                                                                   'direction' => 'from'));
    $data['settings']['BlockTimeExpiration'] = xarModAPIFunc( 'xarcachemanager', 'admin', 'convertseconds',
                                                             array('starttime' => $data['settings']['BlockTimeExpiration'],
                                                                   'direction' => 'from'));

    // get the themes list
    $filter['Class'] = 2;
    $data['themes'] = xarModAPIFunc('themes',
        'admin',
        'getlist', $filter);

    $data['authid'] = xarSecGenAuthKey();
    return $data;
}

?>
