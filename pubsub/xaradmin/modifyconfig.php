<?php

/**
 * Update the configuration parameters of the module based on data from the modification form
 * 
 * @author mikespub
 * @access public 
 * @param no $ parameters
 * @return true on success or void on failure
 * @throws no exceptions
 */
function pubsub_admin_modifyconfig()
{ 
    // Security Check
    if (!xarSecurityCheck('AdminPubSub')) return;

    $data = array();

    $data['templates'] = array();
    $data['templates'][0] = xarML('not supported');

    // get the list of available templates
    $templates = xarModAPIFunc('pubsub','admin','getalltemplates');
    foreach ($templates as $id => $name) {
        $data['templates'][$id] = $name;
    }

    $data['settings'] = array();

    // get the list of hooked modules
    $hookedmodules = xarModAPIFunc('modules', 'admin', 'gethookedmodules',
                                   array('hookModName' => 'pubsub'));
    if (isset($hookedmodules) && is_array($hookedmodules)) {
        foreach ($hookedmodules as $modname => $value) {
            // Get the list of all item types for this module (if any)
            $mytypes = xarModAPIFunc($modname,'user','getitemtypes',
                                     // don't throw an exception if this function doesn't exist
                                     array(), 0);
            // we have hooks for individual item types here
            if (!isset($value[0])) {
                foreach ($value as $itemtype => $val) {
                    $create = xarModGetVar('pubsub', "$modname.$itemtype.create");
                    if (empty($create)) {
                        $create = 0;
                    }
                    $update = xarModGetVar('pubsub', "$modname.$itemtype.update");
                    if (empty($update)) {
                        $update = 0;
                    }
                    $delete = xarModGetVar('pubsub', "$modname.$itemtype.delete");
                    if (empty($delete)) {
                        $delete = 0;
                    }
                    if (isset($mytypes[$itemtype])) {
                        $type = $mytypes[$itemtype]['label'];
                        $link = $mytypes[$itemtype]['url'];
                    } else {
                        $type = xarML('type #(1)',$itemtype);
                        $link = xarModURL($modname,'user','view',array('itemtype' => $itemtype));
                    }
                    $data['settings']["$modname.$itemtype"] = array('label' => xarML('Configuration for #(1) module - <a href="#(2)">#(3)</a>', $modname, $link, $type),
                                                                    'create' => $create,
                                                                    'update' => $update,
                                                                    'delete' => $delete);
                }
            } else {
                $create = xarModGetVar('pubsub', "$modname.create");
                if (empty($create)) {
                    $create = 0;
                }
                $update = xarModGetVar('pubsub', "$modname.update");
                if (empty($update)) {
                    $update = 0;
                }
                $delete = xarModGetVar('pubsub', "$modname.delete");
                if (empty($delete)) {
                    $delete = 0;
                }
                $link = xarModURL($modname,'user','main');
                $data['settings'][$modname] = array('label' => xarML('Configuration for <a href="#(1)">#(2)</a> module', $link, $modname),
                                                    'create' => $create,
                                                    'update' => $update,
                                                    'delete' => $delete);
                if (!empty($mytypes) && count($mytypes) > 0) {
                    foreach ($mytypes as $itemtype => $mytype) {
                        $create = xarModGetVar('pubsub', "$modname.$itemtype.create");
                        if (empty($create)) {
                            $create = 0;
                        }
                        $update = xarModGetVar('pubsub', "$modname.$itemtype.update");
                        if (empty($update)) {
                            $update = 0;
                        }
                        $delete = xarModGetVar('pubsub', "$modname.$itemtype.delete");
                        if (empty($delete)) {
                            $delete = 0;
                        }
                        $type = $mytypes[$itemtype]['label'];
                        $link = $mytypes[$itemtype]['url'];
                        $data['settings']["$modname.$itemtype"] = array('label' => xarML('Configuration for #(1) module - <a href="#(2)">#(3)</a>', $modname, $link, $type),
                                                                        'create' => $create,
                                                                        'update' => $update,
                                                                        'delete' => $delete);
                    }
                }
            }
        }
    }
    $data['isalias'] = xarModGetVar('pubsub','SupportShortURLs');
    $data['numitems'] = xarModGetVar('pubsub','itemsperpage');
    if (empty($data['numitems'])) {
        $data['numitems'] = 20;
    }

    if (xarModIsAvailable('scheduler')) {
        $data['intervals'] = xarModAPIFunc('scheduler','user','intervals');
        // see if we have a scheduler job running to process the pubsub queue
        $job = xarModAPIFunc('scheduler','user','get',
                             array('module' => 'pubsub',
                                   'type' => 'admin',
                                   'func' => 'processq'));
        if (empty($job) || empty($job['interval'])) {
            $data['interval'] = '';
        } else {
            $data['interval'] = $job['interval'];
        }
    } else {
        $data['intervals'] = array();
        $data['interval'] = '';
    }

    $data['authid'] = xarSecGenAuthKey();
    return $data;
}

?>
