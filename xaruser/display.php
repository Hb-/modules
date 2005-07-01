<?php

/**
 * Displays a comment or set of comments
 *
 * @author   Carl P. Corliss (aka rabbitt)
 * @access   public
 * @param    integer    $args['modid']              the module id
 * @param    integer    $args['itemtype']           the item type
 * @param    string     $args['objectid']           the item id
 * @param    string     $args['returnurl']          the url to return to
 * @param    integer    [$args['selected_cid']]     optional: the cid of the comment to view (only for displaying single comments)
 * @param    integer    [$args['preview']]          optional: an array containing a single (preview) comment used with adding/editing comments
 * @returns  array      returns whatever needs to be parsed by the BlockLayout engine
 */
function comments_user_display($args) 
{

    if (!xarSecurityCheck('Comments-Read',0))
        return;

    // check if we're coming via a hook call
    if (isset($args['objectid'])) {
        $ishooked = 1;

    // if we're not coming via a hook call
    } else {
        $ishooked = 0;
        // then check for a 'cid' parameter
        if (!empty($args['cid'])) {
            $cid = $args['cid'];
        } else {
            xarVarFetch('cid','int:1:',$cid,0,XARVAR_NOT_REQUIRED);
        }
        // and set the selected cid to this one
        if (!empty($cid) && !isset($args['selected_cid'])) {
            $args['selected_cid'] = $cid;
        }
    }
// TODO: now clean up the rest :-)

    $header   = xarRequestGetVar('header');
    $package  = xarRequestGetVar('package');
    $receipt  = xarRequestGetVar('receipt');

    $package['settings'] = xarModAPIFunc('comments','user','getoptions');

    // FIXME: clean up return url handling

    $settings_uri = "&amp;depth={$package['settings']['depth']}"
                  . "&amp;order={$package['settings']['order']}"
                  . "&amp;sortby={$package['settings']['sortby']}"
                  . "&amp;render={$package['settings']['render']}";

    if (isset($args['modid'])) {
        $header['modid'] = $args['modid'];
    } elseif (isset($header['modid'])) {
        $args['modid'] = $header['modid'];
    } elseif (!empty($args['extrainfo']) && !empty($args['extrainfo']['module'])) {
        if (is_numeric($args['extrainfo']['module'])) {
            $modid = $args['extrainfo']['module'];
        } else {
            $modid = xarModGetIDFromName($args['extrainfo']['module']);
        }
        $args['modid'] = $modid;
        $header['modid'] = $modid;
    } else {
        xarVarFetch('modid','isset',$modid,NULL,XARVAR_NOT_REQUIRED);
        if (empty($modid)) {
            $modid = xarModGetIDFromName(xarModGetName());
        }
        $args['modid'] = $modid;
        $header['modid'] = $modid;
    }

    if (isset($args['itemtype'])) {
        $header['itemtype'] = $args['itemtype'];
    } elseif (isset($header['itemtype'])) {
        $args['itemtype'] = $header['itemtype'];
    } elseif (!empty($args['extrainfo']) && isset($args['extrainfo']['itemtype'])) {
        $args['itemtype'] = $args['extrainfo']['itemtype'];
        $header['itemtype'] = $args['extrainfo']['itemtype'];
    } else {
        xarVarFetch('itemtype','isset',$itemtype,NULL,XARVAR_NOT_REQUIRED);
        $args['itemtype'] = $itemtype;
        $header['itemtype'] = $itemtype;
    }

    if (isset($args['objectid'])) {
        $header['objectid'] = $args['objectid'];
    } elseif (isset($header['objectid'])) {
        $args['objectid'] = $header['objectid'];
    } else {
        xarVarFetch('objectid','isset',$objectid,NULL,XARVAR_NOT_REQUIRED);
        $args['objectid'] = $objectid;
        $header['objectid'] = $objectid;
    }

    if (isset($args['selected_cid'])) {
        $header['selected_cid'] = $args['selected_cid'];
    } elseif (isset($header['selected_cid'])) {
        $args['selected_cid'] = $header['selected_cid'];
    } else {
        xarVarFetch('selected_cid', 'isset', $selected_cid, NULL, XARVAR_NOT_REQUIRED);
        $args['selected_cid'] = $selected_cid;
        $header['selected_cid'] = $selected_cid;
    }

    if (!xarModLoad('comments','renderer')) {
        $msg = xarML('Unable to load #(1) #(2)','comments','renderer');
        xarErrorSet(XAR_SYSTEM_EXCEPTION, 'UNABLE_TO_LOAD',
            new SystemException(__FILE__.'('.__LINE__.'):  '.$msg));
        return;
    }

    if (!isset($header['selected_cid'])) {
        $package['comments'] = xarModAPIFunc('comments','user','get_multiple',$header);
        if (count($package['comments']) > 1) {
            $package['comments'] = comments_renderer_array_sort(
                                                                 $package['comments'],
                                                                 $package['settings']['sortby'],
                                                                 $package['settings']['order']
                                                               );
        }
    } else {
        $header['cid'] = $header['selected_cid'];
        $package['settings']['render'] = _COM_VIEW_FLAT;
        $package['comments'] = xarModAPIFunc('comments','user','get_one', $header);
        if (!empty($package['comments'][0])) {
            $header['modid'] = $package['comments'][0]['xar_modid'];
            $header['itemtype'] = $package['comments'][0]['xar_itemtype'];
            $header['objectid'] = $package['comments'][0]['xar_objectid'];
/*
            // Call display hooks for categories, dynamicdata etc. (only when displaying individual comments)
            $args['module'] = 'comments';
            $args['itemtype'] = 0;
            $args['itemid'] = $header['cid'];
            // pass along the current module & itemtype for pubsub (urgh)
        // FIXME: handle 2nd-level hook calls in a cleaner way - cfr. categories navigation, comments add etc.
            $args['cid'] = 0; // dummy category
            $modinfo = xarModGetInfo($header['modid']);
            $args['current_module'] = $modinfo['name'];
            $args['current_itemtype'] = $header['itemtype'];
            $args['current_itemid'] = $header['objectid'];
            $args['returnurl'] = xarModURL('comments','user','display',array('cid' => $header['cid']));
            $package['comments'][0]['hooks'] = xarModCallHooks('item', 'display', $header['cid'], $args);
*/
        }
    }

    $package['comments'] = comments_renderer_array_prune_excessdepth(
                            array('array_list'  => $package['comments'],
                                  'cutoff'      => $package['settings']['depth']));

    if ($package['settings']['render'] == _COM_VIEW_THREADED) {
        $package['comments'] = comments_renderer_array_maptree($package['comments']);
    }

    // run text and title through transform hooks
    if (!empty($package['comments'])) {
        foreach ($package['comments'] as $key => $comment) {
            $comment['xar_text'] = xarVarPrepHTMLDisplay($comment['xar_text']);
            $comment['xar_title'] = xarVarPrepForDisplay($comment['xar_title']);
            // say which pieces of text (array keys) you want to be transformed
            $comment['transform'] = array('xar_text');
            // call the item transform hooks
            // Note : we need to tell Xaraya explicitly that we want to invoke the hooks for 'comments' here (last argument)
            $package['comments'][$key] = xarModCallHooks('item', 'transform', $comment['xar_cid'], $comment, 'comments');
        }
    }
    $header['input-title']            = xarML('Post a new comment');

    $package['settings']['max_depth'] = _COM_MAX_DEPTH;
    $package['uid']                   = xarUserGetVar('uid');
    $package['uname']                 = xarUserGetVar('uname');
    $package['name']                  = xarUserGetVar('name');
    $package['new_title']             = xarVarPrepForDisplay(xarVarGetCached('Comments.title', 'title'));

    if (empty($ishooked) && empty($receipt['returnurl'])) {
        // get the title and link of the original object
        $modinfo = xarModGetInfo($header['modid']);
        $itemlinks = xarModAPIFunc($modinfo['name'],'user','getitemlinks',
                                   array('itemtype' => $header['itemtype'],
                                         'itemids' => array($header['objectid'])),
                                   // don't throw an exception if this function doesn't exist
                                   0);
        if (!empty($itemlinks) && !empty($itemlinks[$header['objectid']])) {
            $url = $itemlinks[$header['objectid']]['url'];
            if (!strstr($url,'?')) {
                $url .= '?';
            }
            $header['objectlink'] = $itemlinks[$header['objectid']]['url'];
            $header['objecttitle'] = $itemlinks[$header['objectid']]['label'];
        } else {
            $url = xarModURL($modinfo['name'],'user','main');
        }
        $receipt['returnurl'] = array('encoded' => rawurlencode($url),
                                      'decoded' => $url);
    } elseif (!isset($receipt['returnurl']['raw'])) {
        if (empty($args['extrainfo'])) {
            $modinfo = xarModGetInfo($args['modid']);
            $receipt['returnurl']['raw'] = xarModURL($modinfo['name'],'user','main');
        } elseif (is_array($args['extrainfo']) && isset($args['extrainfo']['returnurl'])) {
            $receipt['returnurl']['raw'] = $args['extrainfo']['returnurl'];
        } elseif (is_string($args['extrainfo'])) {
            $receipt['returnurl']['raw'] = $args['extrainfo'];
        }
        if (!stristr($receipt['returnurl']['raw'],'?')) {
            $receipt['returnurl']['raw'] .= '?';
        }
        $receipt['returnurl']['decoded'] = $receipt['returnurl']['raw'] . $settings_uri;
        $receipt['returnurl']['encoded'] = rawurlencode($receipt['returnurl']['decoded']);
    } else {
        if (!stristr($receipt['returnurl']['raw'],'?')) {
            $receipt['returnurl']['raw'] .= '?';
        }
        $receipt['returnurl']['encoded'] = rawurlencode($receipt['returnurl']['raw']);
        $receipt['returnurl']['decoded'] = $receipt['returnurl']['raw'] . $settings_uri;
    }

    $receipt['post_url']              = xarModURL('comments','user','reply');
    $receipt['action']                = 'display';

    $hooks = xarModAPIFunc('comments','user','formhooks'); 

    $output['hooks']   = $hooks;
    $output['header']  = $header;
    $output['package'] = $package;
    $output['receipt'] = $receipt;

    return $output;

}

?>
