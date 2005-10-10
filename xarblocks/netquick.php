<?php
function netquery_netquickblock_init()
{
    return true;
}
function netquery_netquickblock_info()
{
    return array('text_type' => 'netquick',
                 'module' => 'netquery',
                 'text_type_long' => 'Show Netquick Query Options',
                 'allow_multiple' => true,
                 'form_content' => false,
                 'form_refresh' => false,
                 'show_preview' => true);
}
function netquery_netquickblock_display($blockinfo)
{
    include_once "modules/netquery/xarincludes/nqSniff.class.php";
    if (!xarSecurityCheck('OverviewNetquery')) {
        return;
    }
    if (empty($blockinfo['content'])) {
        return '';
    }
    $vars = @unserialize($blockinfo['content']);
    if (!isset($vars['blockquery'])) {
        $vars['blockquery'] = 'whois';
    }
    $browserinfo =& new nqSniff();
    $geoip = xarModAPIFunc('netquery', 'user', 'getgeoip', array('ip' => $browserinfo->property('ip')));
    $topcountries_limit = xarModGetVar('netquery', 'topcountries_limit');
    $countries = xarModAPIFunc('netquery', 'user', 'getcountries', array('numitems' => $topcountries_limit));
    $whois_default = xarModGetVar('netquery', 'whois_default');
    $links = xarModAPIFunc('netquery','user','getlinks');
    $email = 'someone@'.gethostbyaddr($_SERVER['REMOTE_ADDR']);
    $httpurl = 'http://'.$_SERVER['SERVER_NAME'];
    $blockinfo['content'] = array('browserinfo'   => $browserinfo,
                                  'geoip'         => $geoip,
                                  'countries'     => $countries,
                                  'whois_default' => $whois_default,
                                  'links'         => $links,
                                  'email'         => $email,
                                  'httpurl'       => $httpurl,
                                  'vars'          => $vars);
    return $blockinfo;
}
?>