<?php

function release_user_addnotes($args)
{

    // Security Check
    if(!xarSecurityCheck('OverviewRelease')) return;
    xarVarFetch('phase', 'enum:getmodule:start:getbasics:getdetails:preview:update',
                         $phase, 'getmodule', XARVAR_NOT_REQUIRED);

    if (empty($phase)){
        $phase = 'getmodule';
    }
             //Set the stateoptions array for rstate field
                $stateoptions=array();
                $stateoptions[0] = xarML('Planning');
                $stateoptions[1] = xarML('Alpha');
                $stateoptions[2] = xarML('Beta');
                $stateoptions[3] = xarML('Production/Stable');
                $stateoptions[4] = xarML('Mature');
                $stateoptions[5] = xarML('Inactive');
                $data['stateoptions']=$stateoptions;

    switch(strtolower($phase)) {
        case 'getmodule':
        default:
            // First we need to get the module that we are adding the release note to.
            // This will be done in several stages so that the information is accurate.

            $authid = xarSecGenAuthKey();
            $data = xarTplModule('release','user', 'addnote_getmodule', array('authid'    => $authid));

            break;

        case 'start':
            // First we need to get the module that we are adding the release note to.
            // This will be done in several stages so that the information is accurate.
            if (!xarVarFetch('rid', 'int:1:', $rid, null, XARVAR_NOT_REQUIRED)) {return;}

            // The user API function is called.
            $data = xarModAPIFunc('release',
                                  'user',
                                  'getid',
                                  array('rid' => $rid));

            
            $uid = xarUserGetVar('uid');

            if (($data['uid'] == $uid) or (xarSecurityCheck('EditRelease', 0))) {
                $message = '';
            } else {
                $message = xarML('You are not allowed to add a release notification to this module');               
            }

            //TODO FIX ME!!!
            if (empty($data['regname'])){
                $message = xarML('There is no assigned ID for your extension.');
            }

            xarTplSetPageTitle(xarVarPrepForDisplay($data['regname']));

            $authid = xarSecGenAuthKey();
            $data = xarTplModule('release','user', 'addnote_start', array('rid'       => $data['rid'],
                                                                          'regname'   => $data['regname'],
                                                                          'desc'      => $data['desc'],
                                                                          'message'   => $message,
                                                                          'authid'    => $authid));

            break;

        case 'getbasics':

           if (!xarVarFetch('rid', 'int:1:', $rid, null, XARVAR_NOT_REQUIRED)) {return;}
           if (!xarVarFetch('regname', 'str:1:', $regname, NULL, XARVAR_NOT_REQUIRED)) {return;};

           //if (!xarSecConfirmAuthKey()) return;
           $democheck=1;
           $supportcheck=1;
           $pricecheck=1;
            xarTplSetPageTitle(xarVarPrepForDisplay($regname));

           $authid = xarSecGenAuthKey();
           $data = xarTplModule('release','user', 'addnote_getbasics', array('rid'      => $rid,
                                                                             'regname'  => $regname,
                                                                             'authid'   => $authid,
                                                                             'democheck' => $democheck,
                                                                             'supportcheck' => $supportcheck,
                                                                             'pricecheck' => $pricecheck));
            break;

        case 'getdetails':
           if (!xarVarFetch('rid', 'int:1:', $rid, null, XARVAR_NOT_REQUIRED)) {return;}
           if (!xarVarFetch('regname', 'str:1:', $regname, NULL, XARVAR_NOT_REQUIRED)) {return;};
           if (!xarVarFetch('version', 'str:1:', $version, null, XARVAR_NOT_REQUIRED)) {return;}
           if (!xarVarFetch('pricecheck', 'int:1:2', $pricecheck, null, XARVAR_NOT_REQUIRED)) {return;}
           if (!xarVarFetch('supportcheck', 'int:1:2', $supportcheck, null, XARVAR_NOT_REQUIRED)) {return;}
           if (!xarVarFetch('democheck', 'int:1:2', $democheck, null, XARVAR_NOT_REQUIRED)) {return;}

           //if (!xarSecConfirmAuthKey()) return;

            xarTplSetPageTitle(xarVarPrepForDisplay($regname));

           $authid = xarSecGenAuthKey();
           $data = xarTplModule('release','user', 'addnote_getdetails', array('rid'          => $rid,
                                                                              'regname'      => $regname,
                                                                              'authid'       => $authid,
                                                                              'version'      => $version,
                                                                              'pricecheck'   => $pricecheck,
                                                                              'supportcheck' => $supportcheck,
                                                                              'democheck'    => $democheck,
                                                                              'stateoptions' => $stateoptions));

            break;
        
        case 'preview':
           if (!xarVarFetch('rid', 'int:1:', $rid, null, XARVAR_NOT_REQUIRED)) {return;}
           if (!xarVarFetch('regname', 'str:1:', $regname, NULL, XARVAR_NOT_REQUIRED)) {return;};
           if (!xarVarFetch('version', 'str:1:', $version, null, XARVAR_NOT_REQUIRED)) {return;}
           if (!xarVarFetch('pricecheck', 'int:1:2', $pricecheck, null, XARVAR_NOT_REQUIRED)) {return;}
           if (!xarVarFetch('supportcheck', 'int:1:2', $supportcheck, null, XARVAR_NOT_REQUIRED)) {return;}
           if (!xarVarFetch('democheck', 'int:1:2', $democheck, null, XARVAR_NOT_REQUIRED)) {return;}
           if (!xarVarFetch('dllink', 'str:1:', $dllink, '', XARVAR_NOT_REQUIRED)) {return;}
           if (!xarVarFetch('price', 'str', $price, '', XARVAR_NOT_REQUIRED)) {return;}
           if (!xarVarFetch('demolink', 'str:1:254', $demolink, '', XARVAR_NOT_REQUIRED)) {return;}
           if (!xarVarFetch('supportlink', 'str:1:254', $supportlink, '', XARVAR_NOT_REQUIRED)) {return;}
           if (!xarVarFetch('changelog', 'str', $changelog, '', XARVAR_NOT_REQUIRED)) {return;}
           if (!xarVarFetch('notes', 'str', $notes, '', XARVAR_NOT_REQUIRED)) {return;}
           if (!xarVarFetch('rstate', 'int:0:6', $rstate, null, XARVAR_NOT_REQUIRED)) {return;}

           //if (!xarSecConfirmAuthKey()) return;
           //Get some info for the extensions state
           foreach ($stateoptions as $key => $value) {
               if ($key==$rstate) {
                   $extstate=$stateoptions[$key];
               }
           }
           $notesf = nl2br($notes);
           $changelogf = nl2br($changelog);

            xarTplSetPageTitle(xarVarPrepForDisplay($regname));

           $authid = xarSecGenAuthKey();
           $data = xarTplModule('release','user', 'addnote_preview',    array('rid'         => $rid,
                                                                              'regname'     => $regname,
                                                                              'authid'      => $authid,
                                                                              'version'     => $version,
                                                                              'pricecheck'  => $pricecheck,
                                                                              'supportcheck'=> $supportcheck,
                                                                              'democheck'   => $democheck,
                                                                              'dllink'      => $dllink,
                                                                              'price'       => $price,
                                                                              'demolink'    => $demolink,
                                                                              'supportlink' => $supportlink,
                                                                              'changelog'   => $changelog,
                                                                              'changelogf'  => $changelogf,
                                                                              'notesf'      => $notesf,
                                                                              'notes'       => $notes,
                                                                              'rstate'      => $rstate,
                                                                              'stateoptions'=> $stateoptions,
                                                                              'extstate'     => $extstate));



            break;

        case 'update':
       if (!xarVarFetch('rid', 'int:1:', $rid, null, XARVAR_NOT_REQUIRED)) {return;}
           if (!xarVarFetch('regname', 'str:1:', $regname, NULL, XARVAR_NOT_REQUIRED)) {return;};
           if (!xarVarFetch('version', 'str:1:', $version, null, XARVAR_NOT_REQUIRED)) {return;}
           if (!xarVarFetch('pricecheck', 'int:1:2', $pricecheck, null, XARVAR_NOT_REQUIRED)) {return;}
           if (!xarVarFetch('supportcheck', 'int:1:2', $supportcheck, null, XARVAR_NOT_REQUIRED)) {return;}
           if (!xarVarFetch('democheck', 'int:1:2', $democheck, null, XARVAR_NOT_REQUIRED)) {return;}
           if (!xarVarFetch('dllink', 'str:1:', $dllink, '', XARVAR_NOT_REQUIRED)) {return;}
           if (!xarVarFetch('price', 'float', $price, 0, XARVAR_NOT_REQUIRED)) {return;}
           if (!xarVarFetch('demolink', 'str:1:254', $demolink, '', XARVAR_NOT_REQUIRED)) {return;}
           if (!xarVarFetch('supportlink', 'str:1:254', $supportlink, '', XARVAR_NOT_REQUIRED)) {return;}
           if (!xarVarFetch('changelog', 'str:1:', $changelog, '', XARVAR_NOT_REQUIRED)) {return;}
           if (!xarVarFetch('notes', 'str:1:', $notes, '', XARVAR_NOT_REQUIRED)) {return;}
           if (!xarVarFetch('rstate', 'int:0:6', $rstate, 0, XARVAR_NOT_REQUIRED)) {return;}

           //if (!xarSecConfirmAuthKey()) return;
            // The user API function is called.
            $data = xarModAPIFunc('release',
                                  'user',
                                  'getid',
                                  array('rid' => $rid));
            if ($data['type'] == 0) {
                $exttype='Module';
            } elseif ($data['type'] == 1)  {
                $exttype='Theme';
            } elseif ($data['type'] ==2) {
                $exttype='PubType';
            } elseif ($data['type']==3) {
                $exttype='Enhancement';
            }

            // The user API function is called.
            if (!xarModAPIFunc('release',
                               'user',
                               'createnote',
                                array('rid'         => $rid,
                                      'version'     => $version,
                                      'price'       => $pricecheck,
                                      'priceterms'  => $price,
                                      'supported'   => $supportcheck,
                                      'demo'        => $democheck,
                                      'dllink'      => $dllink,
                                      'demolink'    => $demolink,
                                      'supportlink' => $supportlink,
                                      'changelog'   => $changelog,
                                      'type'        => $exttype,
                                      'notes'       => $notes,
                                      'rstate'      => $rstate))) return;

            xarTplSetPageTitle(xarVarPrepForDisplay(xarML('Thank You')));

           $data = xarTplModule('release','user', 'addnote_thanks');

            break;
    }   
    
    return $data;
}

?>
