<?php
function comments_admin_delete( ) 
{
    if (!xarSecurityCheck('Comments-Admin')) return;
    if (!xarVarFetch('dtype', 'str:1:', $dtype)) return;
    $delete_args = array();

    if (!isset($dtype) || !eregi('^(all|module|object)$',$dtype)) {
        $msg = xarML('Invalid or Missing Parameter \'dtype\'');
        xarErrorSet(XAR_SYSTEM_EXCEPTION, 'BAD_PARAM', new SystemException($msg));
        return;
    } else {

        $delete_args['dtype'] = $dtype;
        $output['dtype'] = $dtype;

        switch (strtolower($dtype)) {
            case 'object':
                $objectid = xarVarCleanFromInput('objectid');

                if (!isset($objectid) || empty($objectid)) {
                    $msg = xarML('Invalid or Missing Parameter \'objectid\'');
                    xarErrorSet(XAR_SYSTEM_EXCEPTION, 'BAD_PARAM', new SystemException($msg));
                    return;
                }
                $output['objectid'] = $objectid;
                $delete_args['objectid'] = $objectid;

            // if dtype == object, then fall through to
            // the module section below cuz we need both
            // the module id and the object id
            case 'module':
                $modid = xarVarCleanFromInput('modid');

                if (!isset($modid) || empty($modid)) {
                    $msg = xarML('Invalid or Missing Parameter \'modid\'');
                    xarErrorSet(XAR_SYSTEM_EXCEPTION, 'BAD_PARAM', new SystemException($msg));
                    return;
                }
                $itemtype = xarVarCleanFromInput('itemtype');
                if (empty($itemtype)) {
                    $itemtype = 0;
                }
                $modinfo = xarModGetInfo($modid);
                $output['modname']    = $modinfo['name'];
                $delete_args['modid'] = $modid;
                $delete_args['itemtype'] = $itemtype;
                break;
            case 'all':
                $output['modname']    = '\'ALL MODULES\'';
                break;
            default:
                $msg = xarML('Invalid or Missing Parameter \'dtype\'');
                xarErrorSet(XAR_SYSTEM_EXCEPTION, 'BAD_PARAM', new SystemException($msg));
                return;
        }
    }

    $submitted = xarVarCleanFromInput('submitted');

    // if we're gathering submitted info form the delete
    // confirmation then we are ok to check delete choice,
    // then delete in the manner specified (or not) and
    // then redirect to the Comment's Statistics page
    if (isset($submitted) && !empty($submitted)) {

        // Confirm authorisation code
        if (!xarSecConfirmAuthKey())
            return;

        $choice = xarVarCleanFromInput('choice');

        // if choice isn't set or it has an incorrect value,
        // redirect back to the choice page
        if (!isset($choice) || !eregi('^(yes|no|true|false)$',$choice)) {
            xarResponseRedirect(xarModURL('comments','admin','delete',$delete_args));
        }

        if($choice == 'yes' || $choice == 'true') {

            if (!xarModAPILoad('comments','user')) {
                die ("COULDN'T LOAD API!!!");
            }
            $retval = TRUE;

            switch (strtolower($dtype)) {
                case 'module':
                    xarModAPIFunc('comments','admin','delete_module_nodes',
                                   array('modid'=>$modid,
                                         'itemtype' => $itemtype));
                    break;
                case 'object':
                    xarModAPIFunc('comments','admin','delete_object_nodes',
                                   array('modid'    => $modid,
                                         'itemtype' => $itemtype,
                                         'objectid' => $objectid));
                    break;
                case 'all':
                    $dbconn =& xarDBGetConn();
                    $xartable =& xarDBGetTables();

                    $ctable = &$xartable['comments_column'];

                    $sql = "DELETE
                              FROM  $xartable[comments]";

                    $result =& $dbconn->Execute($sql);

                    break;
                default:
                    $retval = FALSE;
            }

            if (!$retval) {
                $msg = xarML('Unable to delete comments!');
                xarErrorSet(XAR_SYSTEM_EXCEPTION, 'UNKNOWN', new SystemException($msg));
                return;
            }
        } else {
            if ( isset($modid) )  {
                xarResponseRedirect(xarModURL('comments','admin','module_stats',
                                              array('modid' => $modid,
                                                    'itemtype' => empty($itemtype) ? null : $itemtype)));
            } else {
                xarResponseRedirect(xarModURL('comments','admin','stats'));
            }
        }

        if (isset($modid) && strtolower($dtype) == 'object') {
            xarResponseRedirect(xarModURL('comments','admin','module_stats',
                                          array('modid' => $modid,
                                                'itemtype' => empty($itemtype) ? null : $itemtype)));
        } else {
            xarResponseRedirect(xarModURL('comments','admin','stats'));
        }
    }
    // If we're here, then we haven't received authorization
    // to delete any comments yet - so here we ask for confirmation.
    $output['authid'] = xarSecGenAuthKey();
    $output['delete_url'] = xarModURL('comments','admin','delete',$delete_args);

    return $output;
}
?>