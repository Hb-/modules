<?php
/**
  Modify a ticket item
  
  @author Brian McGilligan
  @return Template data
*/
function helpdesk_user_modify($args)
{
    extract($args);
    
    xarVarFetch('tid',        'int:1:',  $tid,        null,  XARVAR_NOT_REQUIRED);
    xarVarFetch('confirm',    'isset',   $confirm,    null,  XARVAR_NOT_REQUIRED);
    xarVarFetch('itemtype',   'int',     $itemtype,   1,     XARVAR_NOT_REQUIRED);
    
    if (!xarModAPILoad('helpdesk', 'user')) { return false; }
    if (!xarModAPILoad('security', 'user')) { return false; }
         
    // If we have confirmation do the update
    if( !empty($confirm) )
    {
        $enforceauthkey = xarModGetVar('helpdesk', 'EnforceAuthKey');
        if ( $enforceauthkey && !xarSecConfirmAuthKey() ){ return false; }
        
        /*
            Security check to prevent un authorized users from modifying it
        */
        $has_security = xarModAPIFunc('security', 'user', 'check',
            array(
                'modid'     => xarModGetIDFromName('helpdesk'),
                'itemtype'  => $itemtype,
                'itemid'    => $tid,
                'level'     => SECURITY_WRITE
            )
        );
        if( !$has_security ){ return false; }
        
        if( !xarVarFetch('userid',     'str:1:',  $userid,  null,  XARVAR_NOT_REQUIRED) ){ return false; }
        if( !xarVarFetch('name',       'str:1:',  $name,  null,  XARVAR_NOT_REQUIRED) ){ return false; }
        if( !xarVarFetch('phone',      'str:1:',  $phone,  null,  XARVAR_NOT_REQUIRED) ){ return false; }
        if( !xarVarFetch('subject',    'str:1:',  $subject,  null,  XARVAR_NOT_REQUIRED) ){ return false; }
        if( !xarVarFetch('domain',     'str:1:',  $domain,  null,  XARVAR_NOT_REQUIRED) ){ return false; }
        if( !xarVarFetch('priority',   'str:1:',  $priority,  null,  XARVAR_NOT_REQUIRED) ){ return false; }
        if( !xarVarFetch('status',     'str:1:',  $statusid,  null,  XARVAR_NOT_REQUIRED) ){ return false; }
        if( !xarVarFetch('openedby',   'str:1:',  $openedby,  null,  XARVAR_NOT_REQUIRED) ){ return false; }
        if( !xarVarFetch('assignedto', 'str:1:',  $assignedto,  null,  XARVAR_NOT_REQUIRED) ){ return false; }
        if( !xarVarFetch('source',     'str:1:',  $source,  null,  XARVAR_NOT_REQUIRED) ){ return false; }
        if( !xarVarFetch('closedby',   'str:1:',  $closedby,  null,  XARVAR_NOT_REQUIRED) ){ return false; }

        $params = array(
            'tid'        => $tid,
            'userid'     => $userid,
            'name'       => $name,
            'subject'    => $subject,
            'domain'     => $domain,
            'priority'   => $priority,
            'statusid'   => $statusid,
            'openedby'   => $openedby,
            'assignedto' => $assignedto,
            'source'     => $source,
            'closedby'   => $closedby
        );        
        $result = xarModAPIFunc('helpdesk', 'user', 'update', $params);

        $item = array();
        $item['module'] = 'helpdesk';
        $item['itemtype'] = $itemtype;        
        $hooks = xarModCallHooks('item', 'update', $tid, $item);
                
        xarResponseRedirect(xarModURL('helpdesk', 'user', 'view',
            array(
                'tid'       => $tid,
                'selection' => 'MYALL' // MYALL includes assigned tickets now
            )
        ));
        
        return true;                           
    }    
    
    /*
        Get the ticket Data, if we can not get it then we must not have privs for it.
    */
    $data['ticketdata']   = xarModAPIFunc('helpdesk','user','getticket',
        array(
            'tid'            => $tid,
            'security_level' => SECURITY_WRITE
        )
    );
    if( empty($data['ticketdata']) )
    {
        $msg = xarML("You do not have the proper security clearance to view this ticket!");
        xarErrorSet(XAR_USER_EXCEPTION, 'NO_PRIVILEGES', $msg);
        return false;    
    }
    
    /*
        These funcs should be rethought once we get the rest working
    */
    $data['priority'] = xarModAPIFunc('helpdesk', 'user', 'gets', 
        array(
            'itemtype' => 2
        )
    );
    $data['status'] = xarModAPIFunc('helpdesk', 'user', 'gets', 
        array(
            'itemtype' => 3
        )
    );
    $data['sources'] = xarModAPIFunc('helpdesk', 'user', 'gets', 
        array(
            'itemtype' => 4
        )
    );
    $data['reps'] = xarModAPIFunc('helpdesk', 'user', 'gets', 
        array(
            'itemtype' => 10
        )
    );
        
    $data['users'] = xarModAPIFunc('roles', 'user', 'getall');                  
    
    $item = array();
    $item['module'] = 'helpdesk';
    $item['itemtype'] = $itemtype;
    $hooks = xarModCallHooks('item', 'modify', $tid, $item);
    if (empty($hooks)) {
        $data['hookoutput'] = array();
    }else {
        $data['hookoutput'] = $hooks;
    }
    
    $data['tid']            = $tid;    
    $data['menu']           = xarModFunc('helpdesk', 'user', 'menu');
    $data['EditAccess']     = xarSecurityCheck('edithelpdesk', 0);
    $data['UserLoggedIn']   = xarUserIsLoggedIn();
    $data['enforceauthkey'] = xarModGetVar('helpdesk', 'EnforceAuthKey');
    $data['enabledimages']  = xarModGetVar('helpdesk', 'Enable Images');    
    $data['summary']        = xarModFunc('helpdesk', 'user', 'summaryfooter');    
        
    return xarTplModule('helpdesk', 'user', 'modify', $data);    
}
?>
