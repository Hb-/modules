<?php

function xarbb_user_deletereply()
{
    // Get parameters
    list($cid,
         $confirmation) = xarVarCleanFromInput('cid',
                                               'confirmation');

    // for sec check
    if(!$comment = xarModAPIFunc('comments','user','get_one',array('cid' => $cid))) return;
    $tid = $comment[0]['xar_objectid'];

    if(!$topic = xarModAPIFunc('xarbb','user','gettopic',array('tid' => $tid))) return;    

    // Security Check
    if(!xarSecurityCheck('ModxarBB',1,'Forum',$topic['catid'].':'.$topic['fid'])) return;

    // Check for confirmation.
    if (empty($confirmation)) {
        //Load Template
        $data['authid'] = xarSecGenAuthKey();
        $data['cid'] = $cid;
        return $data;
    }

    // If we get here it means that the user has confirmed the action

    // Confirm authorisation code.
    if (!xarSecConfirmAuthKey()) return;

    if (!xarModAPIFunc('xarbb',
                       'admin',
                       'deletereplies',
                        array('cid' => $cid))) return;

    // Redirect
    xarResponseRedirect(xarModURL('xarbb', 'user', 'viewtopic',array(
    	"tid" => $tid
        )
    ));

    // Return
    return true;
}

?>