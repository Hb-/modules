<?php
function netquery_admin_config()
{
    if (!xarSecurityCheck('EditRole')) return;
    if (!xarVarFetch('phase', 'str:1:100', $phase, 'modify', XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('Submit', 'str:1:100', $Submit, 'Cancel', XARVAR_NOT_REQUIRED)) return;
    switch (strtolower($phase)) {
        case 'modify':
        default:
            $data = xarModAPIFunc('netquery', 'admin', 'configapi');
            break;
        case 'update':
            if ((!isset($Submit)) || ($Submit != 'Submit')) {
                xarResponseRedirect(xarModURL('netquery', 'admin', 'main'));
            }
            if (!xarVarFetch('querytype_default', 'str:1:10', $querytype_default, 'whois', XARVAR_NOT_REQUIRED)) return;
            if (!xarVarFetch('capture_log_enabled', 'checkbox', $capture_log_enabled, '0', XARVAR_NOT_REQUIRED)) return;
            if (!xarVarFetch('capture_log_filepath', 'str:1:100', $capture_log_filepath, 'var/logs/netquery.log', XARVAR_NOT_REQUIRED)) return;
            if (!xarVarFetch('capture_log_dtformat', 'str:1:100', $capture_log_dtformat, 'Y-m-d H:i:s', XARVAR_NOT_REQUIRED)) return;
            if (!xarVarFetch('clientinfo_enabled', 'checkbox', $clientinfo_enabled, '0', XARVAR_NOT_REQUIRED)) return;
            if (!xarVarFetch('whois_enabled', 'checkbox', $whois_enabled, '0', XARVAR_NOT_REQUIRED)) return;
            if (!xarVarFetch('whois_max_limit', 'int:1:10', $whois_max_limit, '3', XARVAR_NOT_REQUIRED)) return;
            if (!xarVarFetch('whois_default', 'str:1:20', $whois_default, '.com', XARVAR_NOT_REQUIRED)) return;
            if (!xarVarFetch('whoisip_enabled', 'checkbox', $whoisip_enabled, '0', XARVAR_NOT_REQUIRED)) return;
            if (!xarVarFetch('dns_lookup_enabled', 'checkbox', $dns_lookup_enabled, '0', XARVAR_NOT_REQUIRED)) return;
            if (!xarVarFetch('dns_dig_enabled', 'checkbox', $dns_dig_enabled, '0', XARVAR_NOT_REQUIRED)) return;
            if (!xarVarFetch('use_win_nslookup', 'checkbox', $use_win_nslookup, '0', XARVAR_NOT_REQUIRED)) return;
            if (!xarVarFetch('email_check_enabled', 'checkbox', $email_check_enabled, '0', XARVAR_NOT_REQUIRED)) return;
            if (!xarVarFetch('query_email_server', 'checkbox', $query_email_server, '0', XARVAR_NOT_REQUIRED)) return;
            if (!xarVarFetch('port_check_enabled', 'checkbox', $port_check_enabled, '0', XARVAR_NOT_REQUIRED)) return;
            if (!xarVarFetch('user_submissions', 'int:1:10', $user_submissions, '0', XARVAR_NOT_REQUIRED)) return;
            if (!xarVarFetch('http_req_enabled', 'checkbox', $http_req_enabled, '0', XARVAR_NOT_REQUIRED)) return;
            if (!xarVarFetch('ping_enabled', 'checkbox', $ping_enabled, '0', XARVAR_NOT_REQUIRED)) return;
            if (!xarVarFetch('pingexec_local', 'str:1:100', $pingexec_local, 'ping.exe', XARVAR_NOT_REQUIRED)) return;
            if (!xarVarFetch('ping_remote_enabled', 'checkbox', $ping_remote_enabled, '0', XARVAR_NOT_REQUIRED)) return;
            if (!xarVarFetch('pingexec_remote', 'str:1:100', $pingexec_remote, 'http://noc.thunderworx.net/cgi-bin/public/ping.pl', XARVAR_NOT_REQUIRED)) return;
            if (!xarVarFetch('pingexec_remote_t', 'str:1:10', $pingexec_remote_t, 'target', XARVAR_NOT_REQUIRED)) return;
            if (!xarVarFetch('trace_enabled', 'checkbox', $trace_enabled, '0', XARVAR_NOT_REQUIRED)) return;
            if (!xarVarFetch('traceexec_local', 'str:1:100', $traceexec_local, 'tracert.exe', XARVAR_NOT_REQUIRED)) return;
            if (!xarVarFetch('trace_remote_enabled', 'checkbox', $trace_remote_enabled, '0', XARVAR_NOT_REQUIRED)) return;
            if (!xarVarFetch('traceexec_remote', 'str:1:100', $traceexec_remote, 'http://noc.thunderworx.net/cgi-bin/public/traceroute.pl', XARVAR_NOT_REQUIRED)) return;
            if (!xarVarFetch('traceexec_remote_t', 'str:1:10', $traceexec_remote_t, 'target', XARVAR_NOT_REQUIRED)) return;
            if (!xarVarFetch('looking_glass_enabled', 'checkbox', $looking_glass_enabled, '0', XARVAR_NOT_REQUIRED)) return;
            if (!xarSecConfirmAuthKey()) return;
            xarModSetVar('netquery', 'querytype_default', $querytype_default);
            xarModSetVar('netquery', 'capture_log_enabled', $capture_log_enabled);
            xarModSetVar('netquery', 'capture_log_filepath', $capture_log_filepath);
            xarModSetVar('netquery', 'capture_log_dtformat', $capture_log_dtformat);
            xarModSetVar('netquery', 'clientinfo_enabled', $clientinfo_enabled);
            xarModSetVar('netquery', 'whois_enabled', $whois_enabled);
            xarModSetVar('netquery', 'whois_max_limit', $whois_max_limit);
            xarModSetVar('netquery', 'whois_default', $whois_default);
            xarModSetVar('netquery', 'whoisip_enabled', $whoisip_enabled);
            xarModSetVar('netquery', 'dns_lookup_enabled', $dns_lookup_enabled);
            xarModSetVar('netquery', 'dns_dig_enabled', $dns_dig_enabled);
            xarModSetVar('netquery', 'email_check_enabled', $email_check_enabled);
            xarModSetVar('netquery', 'query_email_server', $query_email_server);
            xarModSetVar('netquery', 'use_win_nslookup', $use_win_nslookup);
            xarModSetVar('netquery', 'port_check_enabled', $port_check_enabled);
            xarModSetVar('netquery', 'user_submissions', $user_submissions);
            xarModSetVar('netquery', 'http_req_enabled', $http_req_enabled);
            xarModSetVar('netquery', 'ping_enabled', $ping_enabled);
            xarModSetVar('netquery', 'pingexec_local', $pingexec_local);
            xarModSetVar('netquery', 'ping_remote_enabled', $ping_remote_enabled);
            xarModSetVar('netquery', 'pingexec_remote', $pingexec_remote);
            xarModSetVar('netquery', 'pingexec_remote_t', $pingexec_remote_t);
            xarModSetVar('netquery', 'trace_enabled', $trace_enabled);
            xarModSetVar('netquery', 'traceexec_local', $pingexec_local);
            xarModSetVar('netquery', 'trace_remote_enabled', $trace_remote_enabled);
            xarModSetVar('netquery', 'traceexec_remote', $traceexec_remote);
            xarModSetVar('netquery', 'traceexec_remote_t', $traceexec_remote_t);
            xarModSetVar('netquery', 'looking_glass_enabled', $looking_glass_enabled);
            xarResponseRedirect(xarModURL('netquery', 'admin', 'config'));
            return true;
            break;
    }
    return $data;
}
?>