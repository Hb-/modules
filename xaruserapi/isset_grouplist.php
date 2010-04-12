<?php
/**
 * Psspl:Adeded the function for selecting the 
 * group configuration for "send_allow_list"
 * @param unknown_type $args
 * @return array of groups selected if not configured return false
 */

sys::import('modules.messages.xarincludes.defines');

function messages_userapi_isset_grouplist( $args )
{

    extract($args);
    
        $users = xarModAPIFunc('roles', 'user',
                                        'getall',
                                        array('state'   => 3,
                                        'include_anonymous' => false,
                                        'include_myself' => false));
        $userid = xarUserGetVar('id');

        sys::import('xaraya.structures.query');

        $xartable = xarDB::getTables();
        $q = new Query('SELECT');
        $q->addtable($xartable['roles'], 'r');

        $q->eq('id', $userid);

        $q->addtable($xartable['rolemembers'],'rm');
        $q->join('r.id', 'rm.role_id');

        if(!$q->run()) return;
        $CurrentUser =  $q->output();
        
        $id=$CurrentUser[0]['parent_id'];
        $groupID=$CurrentUser[0]['parent_id'];
        
        $allowedSendMessages = unserialize(xarModItemVars::get('messages',"allowedSendMessages",$groupID));
        
        if(isset($allowedSendMessages)) {
            if(empty($allowedSendMessages[0])) {
                return false;
            }
            $data['users'] = xarModAPIFunc('messages','user','get_sendtousers');
            if(empty($data['users'])){
                return false;
            }
            return $allowedSendMessages;
        } else {
            return false;
        }
}
?>