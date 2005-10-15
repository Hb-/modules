<?php
/**
 * File: $Id$
 *
 * Messages Block
 *
 * @package Xaraya eXtensible Management System
 * @copyright (C) 2003 by the Xaraya Development Team.
 * @link http://www.xaraya.com
 *
 * @subpackage Messages module
 * @author Scot Gardner
*/


function messages_newmessagesblock_init()
{
     return true;
}

function messages_newmessagesblock_info()
{
    // Values
    return array('text_type' => 'Messages',
                 'module' => 'messages',
                 'text_type_long' => 'My Messages',
                 'allow_multiple' => true,
                 'form_content' => false,
                 'form_refresh' => false,
                 'show_preview' => false);
}


function messages_newmessagesblock_display($blockinfo)
{
       // Security check
    if(!xarSecurityCheck('ReadMessagesBlock', 0)) return;

     // Get variables from content block
    $vars = @unserialize($blockinfo['content']);

    $data = array();
    $itemtype=1;

    // Get Logged in Users ID
    $uid = xarUserGetVar('uid');

    // Count total Messages
     $numitems = xarModAPIFunc(
        'messages'
        ,'user'
        ,'count_total'
        ,array(
            'module'     => 'messages'
            ,'itemtype'  => $itemtype
        ));
     $data['totalin'] = $numitems;
    // Count Unread Messages

   $numitems = xarModAPIFunc(
        'messages'
        ,'user'
        ,'count_unread'
        ,array(
            'module'     => 'messages'
            ,'itemtype'  => $itemtype
        ));
    $data['unread'] = $numitems;

    // No messages return emptymessage
    if (empty($numitems)){
        $data['emptymessage'] = xarML('No Unread messages in mailbox');
        $data['content'] = 'No new Messages';
        if (empty($data['title'])){
            $data['title'] = xarML('My Messages');
        }

        $blockinfo['content'] = $data;
        return $blockinfo;
    }


//pulling unread messages will go here
// $read_messages = unserialize(xarModGetUserVar('messages','read_messages'));
//if (!in_array($data['message']['mid'], $read_messages)) {
// add to unread list
// }

   $data['emptymessage'] = '';
   $data['numitems'] = $numitems;
   $blockinfo['content'] = $data ;

    if (empty($blockinfo['title'])){
        $blockinfo['title'] = xarML('My Messages');
    }

    return $blockinfo;
}

?>