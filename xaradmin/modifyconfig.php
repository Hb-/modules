<?php
/**
 * Main configuration page for the messages module
 *
 */

// Use this version of the modifyconfig file when the module is not a  utility module
    function messages_admin_modifyconfig()
    {
        // Security Check
        if (!xarSecurityCheck('AdminMessages')) return;
        if (!xarVarFetch('phase', 'str:1:100', $phase, 'modify', XARVAR_NOT_REQUIRED, XARVAR_PREP_FOR_DISPLAY)) return;
        if (!xarVarFetch('tab', 'str:1:100', $data['tab'], 'general', XARVAR_NOT_REQUIRED)) return;
        //if (!xarVarFetch('group',  'int',    $group, 1, XARVAR_NOT_REQUIRED)) return;
        //Psspl:Modifided the code for resolving group configuration update issue.
        if($phase == 'Update Messages Configuration'){
            $phase = 'update';
        }

        $data['groups'] = xarMod::apiFunc('roles', 'user', 'getallgroups');

        $data['module_settings'] = xarMod::apiFunc('base','admin','getmodulesettings',array('module' => 'messages'));
        $data['module_settings']->setFieldList('items_per_page, use_module_alias, enable_short_urls');
        $data['module_settings']->getItem();

        switch (strtolower($phase)) {
            case 'modify':
            default:
                switch ($data['tab']) {
                    case 'general':
                        break;
                    case 'tab2':
                        break;
                    case 'tab3':
                        break;
                    default:
                        break;
                }

                break;

            case 'update':
                // Confirm authorisation code
                if (!xarSecConfirmAuthKey()) return;
                switch ($data['tab']) {
                    case 'general':
                        if (!xarVarFetch('awaymsg', 'checkbox', $awaymsg,  xarModVars::get('messages', 'awaymsg'), XARVAR_NOT_REQUIRED)) return;
						if (!xarVarFetch('allowanonymous', 'checkbox', $allowanonymous,  xarModVars::get('messages', 'allowanonymous'), XARVAR_NOT_REQUIRED)) return;
                        if (!xarVarFetch('drafts', 'checkbox', $drafts,  xarModVars::get('messages', 'drafts'), XARVAR_NOT_REQUIRED)) return;

                        xarModVars::set('messages', 'awaymsg', $awaymsg);
                        xarModVars::set('messages', 'drafts', $drafts);
						xarModVars::set('messages', 'allowanonymous', $allowanonymous);

                        $isvalid = $data['module_settings']->checkInput();
                        if (!$isvalid) {
                            return xarTplModule('messages','admin','modifyconfig', $data);
                        } else {
                            $itemid = $data['module_settings']->updateItem();
                        }

                        //Psspl:Modifided the code for allowedsend to selected group configuration.

						
						foreach ($data['groups'] as $key => $value) {
							if (!xarVarFetch('roleid_'.$key,  'array',    $roleid_{$key}, 0, XARVAR_NOT_REQUIRED)) return; 
							xarModItemVars::set('messages', "allowedSendMessages", serialize($roleid_{$key}),$key);
						}
						

                        //if (!xarVarFetch('childgroupsimploded',  'str',    $childgroupsimploded, 0, XARVAR_NOT_REQUIRED)) return;  
                        //$selectedGroup = explode(",", $childgroupsimploded);
                        
                        break;
                    case 'tab2':
                        break;
                    case 'tab3':
                        break;
                    default:
                        break;
                }

                //xarResponse::redirect(xarModURL('messages', 'admin', 'modifyconfig',array('tab' => $data['tab'])));
                // Return
                //return true;
                //break;

        }
        $data['action']     = xarModURL('messages','admin','modifyconfig' );
        $data['authid'] = xarSecGenAuthKey();
        //$data['group'] = $group;
        //$data['selectedGroupStr'] = xarModAPIFunc('messages','admin','getconfig',array('group'=>$group));
        $data['supportshorturls']   = xarModVars::get('messages', 'SupportShortURLs');

        /*try {
            $data['selectedGroups'] = unserialize(xarModItemVars::get('messages', "allowedSendMessages", $group));
            $data['selectedGroupsStr'] = implode(",", $data['selectedGroups']);//convert array into comma seprated string.
        } catch(Exception $e) {
            $data['selectedGroups'] = array();
            $data['selectedGroupsStr'] = "";
        }*/
        return $data;
    }


/*
    $data['itemsperpage'] = xarModVars::get('messages', 'itemsperpage');
    $data['buddylist'] = xarModVars::get('messages', 'buddylist');
    $data['limitsaved'] = xarModVars::get('messages', 'limitsaved');
    $data['limitinbox'] = xarModVars::get('messages', 'limitinbox');
    $data['limitoutbox'] = xarModVars::get('messages', 'limitout');
    $data['smilies'] = xarModVars::get('messages', 'smilies');
    $data['allow_html'] = xarModVars::get('messages', 'allow_html');
    $data['allow_bbcode'] = xarModVars::get('messages', 'allow_bbcode');
    $data['mailsubject'] = xarModVars::get('messages', 'mailsubject');
    $data['fromname'] = xarModVars::get('messages', 'fromname');
    $data['from'] = xarModVars::get('messages', 'from');
    $data['inboxurl'] = xarModVars::get('messages', 'inboxurl');
    $data['serverpath'] = xarModVars::get('messages', 'serverpath');
    $data['updatebutton'] = xarVarPrepForDisplay(xarML('Update Configuration'));

    // Note : if you don't plan on providing encode/decode functions for
    // short URLs (see xaruserapi.php), you should remove these from your
    // admin-modifyconfig.xard template !
    $data['shorturls'] = xarModVars::get('messages','SupportShortURLs');

    $hooks = xarModCallHooks('module', 'modifyconfig', 'messages',
                            array('module' => 'messages'));
    if (empty($hooks)) {
        $data['hooks'] = '';
    } elseif (is_array($hooks)) {
        $data['hooks'] = join('',$hooks);
    } else {
        $data['hooks'] = $hooks;
    }

    // Return the template variables defined in this function
    return $data;
}
*/
?>
