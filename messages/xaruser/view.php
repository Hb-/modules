<?php
function messages_user_view( $args ) {

    // Security check
    if (!xarSecurityCheck('ViewMessages', 0)) {
        return $data['error'] = xarML('You are not permitted to view messages.');
    }

    if (!xarVarFetch('mid', 'int:1:', $mid)) return;

    $messages = xarModAPIFunc('messages','user','get',array('mid' => $mid));

    if (!count($messages) || !is_array($messages)) {
        $data['error'] = xarML('Message ID nonexistant!');
        return $data;
    }

    if ($messages[0]['receipient_id'] != xarUserGetVar('uid') &&
        $messages[0]['sender_id'] != xaruserGetVar('uid')) {
            $data['error'] = xarML("You are NOT authorized to view someone else's mail!");
            return $data;
    }

    $read_messages = xarModGetUserVar('messages','read_messages');
    if (!empty($read_messages)) {
        $read_messages = unserialize($read_messages);
    } else {
        $read_messages = array();
    }


    /* 
     * if it's not already an array, then this must be
     * the first time we've looked at it
     * so let's make it an array :)
     */
    if (!is_array($read_messages)) {
        $read_messages = array();
    }

    $data['message'] = $messages[0];
    $data['action']  = 'view';
    /*
     * Add this message id to the list of 'seen' messages
     * if it's not already in there :)
     */
    if (!in_array($data['message']['mid'], $read_messages)) {
        array_push($read_messages, $data['message']['mid']);
        xarmodSetUserVar('messages','read_messages',serialize($read_messages));
    }

    return $data;
}

?>
