<?php

function release_user_adddocs()
{
    // Security Check
    if(!xarSecurityCheck('OverviewRelease')) return;

    list ($phase,
          $rid,
          $type)= xarVarCleanFromInput('phase',
                                       'rid',
                                       'type');

    $data['items'] = array();
    $data['rid'] = $rid;

    if (empty($phase)){
        $phase = 'getmodule';
    }

    switch(strtolower($phase)) {
        case 'getmodule':
        default:
            // First we need to get the module that we are adding the release note to.
            // This will be done in several stages so that the information is accurate.

            $authid = xarSecGenAuthKey();
            $data = xarTplModule('release','user', 'adddocs_getmodule', array('authid'    => $authid));

            xarTplSetPageTitle(xarVarPrepForDisplay(xarML('Documentation')));

            break;

        case 'start':
            // First we need to get the module that we are adding the release note to.
            // This will be done in several stages so that the information is accurate.

            $rid = xarVarCleanFromInput('rid');

            // The user API function is called.
            $data = xarModAPIFunc('release',
                                  'user',
                                  'getid',
                                  array('rid' => $rid));

            
            $uid = xarUserGetVar('uid');

            if (($data['uid'] == $uid) or (xarSecurityCheck('EditRelease', 0))) {
                $message = '';
            } else {
                $message = xarML('You are not allowed to add documentation to this module');               
            }

            //TODO FIX ME!!!
            if (empty($data['name'])){
                $message = xarML('There is no assigned ID for your module or theme.');
            }

            xarTplSetPageTitle(xarVarPrepForDisplay($data['name']));

            $authid = xarSecGenAuthKey();
            $data = xarTplModule('release','user', 'adddocs_start', array('rid'       => $data['rid'],
                                                                          'name'      => $data['name'],
                                                                          'desc'      => $data['desc'],
                                                                          'type'      => $data['type'],
                                                                          'message'   => $message,
                                                                          'authid'    => $authid));

            break;

        case 'module':

            $data['mtype'] = 'mgeneral';
            $data['return'] = 'module';
            // The user API function is called. 

            $items = xarModAPIFunc('release',
                                   'user',
                                   'getdocs',
                                    array('rid' => $rid,
                                          'type'=> $data['mtype']));

            if (empty($items)){
                $data['message'] = xarML('There is no general module documentation defined');
            }

            
            xarTplSetPageTitle(xarVarPrepForDisplay(xarML('General Information')));


            // Check individual permissions for Edit / Delete
            for ($i = 0; $i < count($items); $i++) {
                $item = $items[$i];

                $uid = xarUserGetVar('uid');
                $items[$i]['docsf'] = nl2br(xarVarPrepHTMLDisplay($item['docs']));
            }


            $data['items'] = $items;
            $data['authid'] = xarSecGenAuthKey();
        
            break;
        
        case 'theme':

            $data['mtype'] = 'tgeneral';
            $data['return'] = 'theme';
            // The user API function is called. 

            $items = xarModAPIFunc('release',
                                   'user',
                                   'getdocs',
                                    array('rid' => $rid,
                                          'type'=> $data['mtype']));

            if (empty($items)){
                $data['message'] = xarML('There is no general theme documentation defined');
            }

            
            xarTplSetPageTitle(xarVarPrepForDisplay(xarML('General Information')));


            // Check individual permissions for Edit / Delete
            for ($i = 0; $i < count($items); $i++) {
                $item = $items[$i];

                $uid = xarUserGetVar('uid');
                $items[$i]['docsf'] = nl2br(xarVarPrepHTMLDisplay($item['docs']));
            }


            $data['items'] = $items;
            $data['authid'] = xarSecGenAuthKey();

            break;

        case 'blockgroups':

            $data['mtype'] = 'bgroups';
            $data['return'] = 'blockgroups';
            // The user API function is called. 

            $items = xarModAPIFunc('release',
                                   'user',
                                   'getdocs',
                                    array('rid' => $rid,
                                          'type'=> $data['mtype']));

            if (empty($items)){
                $data['message'] = xarML('There is no block groups documentation defined');
            }

            
            xarTplSetPageTitle(xarVarPrepForDisplay(xarML('General Information')));


            // Check individual permissions for Edit / Delete
            for ($i = 0; $i < count($items); $i++) {
                $item = $items[$i];

                $uid = xarUserGetVar('uid');
                $items[$i]['docsf'] = nl2br(xarVarPrepHTMLDisplay($item['docs']));
            }


            $data['items'] = $items;
            $data['authid'] = xarSecGenAuthKey();

            break;
        
        case 'blocks':

            $data['mtype'] = 'mblocks';
            $data['return'] = 'blocks';
            // The user API function is called. 

            $items = xarModAPIFunc('release',
                                   'user',
                                   'getdocs',
                                    array('rid' => $rid,
                                          'type'=> $data['mtype']));

            if (empty($items)){
                $data['message'] = xarML('There is no blocks documentation defined');
            }

            
            xarTplSetPageTitle(xarVarPrepForDisplay(xarML('General Information')));


            // Check individual permissions for Edit / Delete
            for ($i = 0; $i < count($items); $i++) {
                $item = $items[$i];

                $uid = xarUserGetVar('uid');
                $items[$i]['docsf'] = nl2br(xarVarPrepHTMLDisplay($item['docs']));
            }


            $data['items'] = $items;
            $data['authid'] = xarSecGenAuthKey();


            break;

        case 'hooks':

            $data['mtype'] = 'mhooks';
            $data['return'] = 'hooks';
            // The user API function is called. 

            $items = xarModAPIFunc('release',
                                   'user',
                                   'getdocs',
                                    array('rid' => $rid,
                                          'type'=> $data['mtype']));

            if (empty($items)){
                $data['message'] = xarML('There is no hook documentation defined');
            }

            
            xarTplSetPageTitle(xarVarPrepForDisplay(xarML('General Information')));


            // Check individual permissions for Edit / Delete
            for ($i = 0; $i < count($items); $i++) {
                $item = $items[$i];

                $uid = xarUserGetVar('uid');
                $items[$i]['docsf'] = nl2br(xarVarPrepHTMLDisplay($item['docs']));
            }


            $data['items'] = $items;
            $data['authid'] = xarSecGenAuthKey();

            break;

        case 'update':
            list($rid,
                 $mtype,
                 $title,
                 $return,
                 $doc) = xarVarCleanFromInput('rid',
                                              'mtype',
                                              'title',
                                              'return',
                                              'doc');
            
           if (!xarSecConfirmAuthKey()) return;

           if (!xarSecurityCheck('EditRelease', 0)) {
               $approved = 1;
           } else {
               $approved = 2;
           }

            // The user API function is called. 
            if (!xarModAPIFunc('release',
                               'user',
                               'createdoc',
                                array('rid'         => $rid,
                                      'type'        => $mtype,
                                      'title'       => $title,
                                      'doc'         => $doc,
                                      'approved'    => $approved))) return;

            xarResponseRedirect(xarModURL('release', 'user', 'adddocs', array('phase' => $return, 
                                                                              'rid' => $rid)));

           $data = '';
            break;
    }   
    
    return $data;
}

?>