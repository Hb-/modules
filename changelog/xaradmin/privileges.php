<?php

/**
 * Manage definition of instances for privileges (unfinished)
 */
function changelog_admin_privileges($args)
{ 
    // Security Check
    if (!xarSecurityCheck('AdminChangeLog')) return;

    // fixed params
    list($moduleid,
         $itemtype,
         $itemid,
         $apply,
         $extpid,
         $extname,
         $extrealm,
         $extmodule,
         $extcomponent,
         $extinstance,
         $extlevel) = xarVarCleanFromInput('moduleid',
                                           'itemtype',
                                           'itemid',
                                           'apply',
                                           'extpid',
                                           'extname',
                                           'extrealm',
                                           'extmodule',
                                           'extcomponent',
                                           'extinstance',
                                           'extlevel');
    extract($args);

    if (!empty($extinstance)) {
        $parts = explode(':',$extinstance);
        if (count($parts) > 0 && !empty($parts[0])) $moduleid = $parts[0];
        if (count($parts) > 1 && !empty($parts[1])) $itemtype = $parts[1];
        if (count($parts) > 2 && !empty($parts[2])) $itemid = $parts[2];
    }

    // Get the list of all modules currently hooked to categories
    $hookedmodlist = xarModAPIFunc('modules','admin','gethookedmodules',
                                   array('hookModName' => 'changelog'));
    if (!isset($hookedmodlist)) {
        $hookedmodlist = array();
    }
    $modlist = array();
    foreach ($hookedmodlist as $modname => $val) {
        if (empty($modname)) continue;
        $modid = xarModGetIDFromName($modname);
        if (empty($modid)) continue;
        $modinfo = xarModGetInfo($modid);
        $modlist[$modid] = $modinfo['displayname'];
    }

    if (empty($moduleid) || $moduleid == 'All' || !is_numeric($moduleid)) {
        $moduleid = 0;
    }
    if (empty($itemtype) || $itemtype == 'All' || !is_numeric($itemtype)) {
        $itemtype = 0;
    }
    if (empty($itemid) || $itemid == 'All' || !is_numeric($itemid)) {
        $itemid = 0;
    }

    // define the new instance
    $newinstance = array();
    $newinstance[] = empty($moduleid) ? 'All' : $moduleid;
    $newinstance[] = empty($itemtype) ? 'All' : $itemtype;
    $newinstance[] = empty($itemid) ? 'All' : $itemid;

    if (!empty($apply)) {
        // create/update the privilege
        $pid = xarReturnPrivilege($extpid,$extname,$extrealm,$extmodule,$extcomponent,$newinstance,$extlevel);
        if (empty($pid)) {
            return; // throw back
        }

        // redirect to the privilege
        xarResponseRedirect(xarModURL('privileges', 'admin', 'modifyprivilege',
                                      array('pid' => $pid)));
        return true;
    }

/*
    if (!empty($moduleid)) {
        $numitems = xarModAPIFunc('categories','user','countitems',
                                  array('modid' => $moduleid,
                                        'cids'  => (empty($cid) ? null : array($cid))
                                       ));
    } else {
        $numitems = xarML('probably');
    }
*/
    $numitems = xarML('probably');

    $data = array(
                  'moduleid'     => $moduleid,
                  'itemtype'     => $itemtype,
                  'itemid'       => $itemid,
                  'modlist'      => $modlist,
                  'numitems'     => $numitems,
                  'extpid'       => $extpid,
                  'extname'      => $extname,
                  'extrealm'     => $extrealm,
                  'extmodule'    => $extmodule,
                  'extcomponent' => $extcomponent,
                  'extlevel'     => $extlevel,
                  'extinstance'  => xarVarPrepForDisplay(join(':',$newinstance)),
                 );

    $data['refreshlabel'] = xarML('Refresh');
    $data['applylabel'] = xarML('Finish and Apply to Privilege');

    return $data;
} 

?>
