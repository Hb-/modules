<?php

function xartelnet_admin_modifyconfig()
{
    if(!xarSecurityCheck('AdminXarTelnet')) return;
    $data['authid'] = xarSecGenAuthKey();
    include_once 'modules/xartelnet/telnet.inc.php';
    $run =& new telnet;
    $run->set_defaults();
    $data['host'] = xarVarPrepForDisplay($run->host);
    $data['port'] = xarVarPrepForDisplay($run->port);
    $data['timeout'] = xarVarPrepForDisplay($run->timeout);
    $data['prompt'] = xarVarPrepForDisplay($run->prompt);
    $data['add_html_to_newline'] = false;
    $data['debug'] = false;
    if($run->add_html_to_newline == '1') $data['add_html_to_newline'] = true;
    if($run->debug == '1') $data['debug'] = true;
    return $data;
}
?>