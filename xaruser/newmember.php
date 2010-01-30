<?php
    function registration_user_newmember()
    {
        sys::import('modules.dynamicdata.class.objects.master');

        xarTplSetPageTitle(xarML('New Account'));
        if (!xarVarFetch('phase','str:1:100',$phase,'request',XARVAR_NOT_REQUIRED)) return;

        $regobjectname = xarModVars::get('registration', 'registrationobject');
        $object = DataObjectMaster::getObject(array('name' => $regobjectname));
        if(empty($object)) return;
        $authid = xarSecGenAuthKey();

       switch(strtolower($phase)) {

            case 'registerformcycle':
                $fieldvalues = xarSessionGetVar('Registration.UserInfo');
            case 'registerform':
            default:


                if (isset($fieldvalues)) {
                    $object->setFieldValues($fieldvalues);
                }

                /* Call hooks here, others than just dyn data
                 * We pass the phase in here to tell the hook it should check the data
                 */
                $item['module'] = 'registration';
                $item['itemid'] = '';
                $item['values'] = $object->getFieldValues();
                $item['phase']  = $phase;
                $hooks = xarModCallHooks('item', 'new', '', $item);

                if (empty($hooks)) {
                    $hookoutput = array();
                } else {
                    $hookoutput = $hooks;
                }

                $data = xarTplModule('registration','user', 'newmemberform',
                               array('authid'     => $authid,
                                     'object'    => $object,
                                     'properties'    => $object->getProperties(),
                                     'hookoutput' => $hookoutput));
                break;

            case 'checkregistration':

                $isvalid = $object->checkInput();

                /* Call hooks here, others than just dyn data
                 * We pass the phase in here to tell the hook it should check the data
                 */
                $item['module'] = 'registration';
                $item['itemid'] = '';
                $item['values'] = $object->getFieldValues(); // TODO: this includes the password. Do we want this?
                $item['phase']  = $phase;
                $hooks = xarModCallHooks('item', 'new','', $item);

                if (empty($hooks)) {
                    $hookoutput = array();
                } else {
                     $hookoutput = $hooks;
                }

                if (!$isvalid) {
                    $data = array('authid'     => $authid,
                                  'object'     => $object,
                                  'properties'    => $object->getProperties(),
                                  'hookoutput' => $hookoutput);
                    return xarTplModule('registration','user', 'newmemberform',$data);
                }

                // if a password exists, save it in a sessionvar for later retrieval
                foreach ($object->properties as $property) {
                    if (isset($property->password)) {
                        xarSession:: setVar('registration.password',$property->password);
                        break;
                    }
                }

                // invalid fields (we'll check this below)
                $invalid = array();

                $values = $object->getFieldValues();
                if (xarModVars::get('roles','uniqueemail')) {
                    $user = xarMod::apiFunc('roles','user', 'get', array('email' => $email));
                    if ($user) throw new DuplicateException(array('email',$email));
                }

                // Check password and set
                // @todo find a better way to turn choose own password on and off that works nicely with dd objects
                //$pass = '';
                if (xarModVars::get('registration', 'chooseownpassword')) {
                    /*$invalid['pass1'] = xarMod::apiFunc('registration','user','checkvar', array('type'=>'pass1', 'var'=>$pass1 ));
                    if (empty($invalid['pass1'])) {
                        $invalid['pass2'] = xarMod::apiFunc('registration','user','checkvar', array('type'=>'pass2', 'var'=>array($pass1,$pass2) ));
                    }
                    if (empty($invalid['pass1']) && empty($invalid['pass2']))   {
                        $pass = $pass1;
                    }*/
                }

                $count = 0;
                foreach ($invalid as $k => $v) if (!empty($v)) $count + 1;
                // @todo add preview?
                if (!$isvalid || ($count > 0)) {
                    $data = array();
                    $data['authid'] = $authid;
                    $data['object'] = & $object;
                    $data['invalid'] = $invalid;
                    //$data['preview'] = $preview;
                    $item = array();
                    $item['module'] = 'registration';
                    $item['phase'] = $phase;
                    $data['hookoutput'] = xarModCallHooks('item','new','',$item);
                    return xarTplModule('registration','user','newmemberform', $data);
                }

                xarSessionSetVar('Registration.UserInfo',$values);
                // everything seems OK -> go on to the next step
                $data = xarTplModule('registration','user', 'confirmnewmember',
                                     array('object'      => $object,
                                           'properties'  => $object->getProperties(),
                                           'password'    => xarSession:: getVar('registration.password'),
                                           'authid'      => $authid,
                                           'hookoutput'  => $hookoutput));

                break;

            case 'createuser':
//                if (!xarSecConfirmAuthKey()) return;
                $fieldvalues = xarSessionGetVar('Registration.UserInfo');

                // Do we need admin activation of the account?
                if (xarModVars::get('registration', 'explicitapproval')) {
                    $fieldvalues['state'] = xarRoles::ROLES_STATE_PENDING;
                }

                //Get the default auth module data
                //this 'authmodule' was introduced previously (1.1 merge ?)
                // - the column in roles re default auth module that this apparently used to refer to is redundant
                $defaultauthdata     = xarMod::apiFunc('roles', 'user', 'getdefaultauthdata');
                $defaultloginmodname = $defaultauthdata['defaultloginmodname'];
                $authmodule          = $defaultauthdata['defaultauthmodname'];

                //jojo - should just use authsystem now as we used to pre 1.1 merge
                $loginlink = xarModURL($defaultloginmodname,'user','main');

                //variables required for display of correct validation template to users, depending on registration options
                $data['loginlink'] = $loginlink;
                $data['pending']   = xarModVars::get('registration', 'explicitapproval');

                // Do we require user validation of account by email?
                if (xarModVars::get('registration', 'requirevalidation')) {
                    $fieldvalues['state'] = xarRoles::ROLES_STATE_NOTVALIDATED;

                    // Create confirmation code
                    $confcode = xarMod::apiFunc('roles', 'user', 'makepass');
                } else {
                    $confcode = '';
                }

                // Update the field values and create the user
                $object->setFieldValues($fieldvalues,1);

                // Create a password and add it if the user can't create one himself
                if (!xarModVars::get('registration', 'chooseownpassword')){
                    $pass = xarMod::apiFunc('roles', 'user', 'makepass');
                    $fieldvalues['password'] = $pass;
                    $object->setFieldValues($fieldvalues);
                }

                // Create the user, assigning it to a parent
                $id = $object->createItem(array('parentid' => xarModVars::get('registration','defaultgroup')));

                if (empty($id)) return;
                xarModVars::set('roles', 'lastuser', $id);

                // Make sure we have a name property for the next step(s)
                if (!isset($object->properties['name']))
                    throw new Exception(xarML('The object #(1) has no name property',$object->name));

                // Send a welcome email
                if (xarModVars::get('registration','sendnotice')) xarMod::apiFunc('registration','user','notifyuser',$object->getFieldValues());

                //Make sure the user email setting is off unless the user sets it
                xarModUserVars::set('roles','allowemail', false, $id);

                $hookdata = $fieldvalues;
                $hookdata['itemtype'] = xarRoles::ROLES_USERTYPE;
                $hookdata['module'] = 'registration';
                $hookdata['itemid'] = $id;
                xarModCallHooks('item', 'create', $id, $hookdata);

                // We allow "state" or "roles_state"
                if (!isset($fieldvalues['state'])) {
                    if (isset($fieldvalues['roles_state'])) {
                        $fieldvalues['state'] = $fieldvalues['roles_state'];
                    } else {
                        throw new Exception("Missing a 'state' property for the registration data");
                    }
                }

                // Retrieve the password and
                $data['password'] = xarSession:: getVar('registration.password');
//                if (!empty($data['password'])) xarSession::setVars('registration.password','');

                $data['object'] = $object;
                $data['properties'] = $object->getProperties();
                $data = xarTplModule('registration','user', 'newmembersignoff',$data);
                break;
        }
        return $data;
    }
?>