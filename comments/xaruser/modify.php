<?php

/**
 * Modify a comment, dependant on the following criteria:
 * 1. user is the owner of the comment, or
 * 2. user has a minimum of moderator permissions for the
 *    specified comment
 *
 * @author Carl P. Corliss (aka rabbitt)
 * @access private
 * @returns mixed description of return
 */
function comments_user_modify() {

    $header                       = xarRequestGetVar('header');
    $package                      = xarRequestGetVar('package');
    $receipt                      = xarRequestGetVar('receipt');

    $receipt['post_url']          = xarModURL('comments','user','modify');
    $header['input-title']        = xarML('Modify Comment');

    if (!xarVarFetch('cid', 'int:1:', $cid, 0, XARVAR_NOT_REQUIRED)) return;
    if (!empty($cid)) {
        $header['cid'] = $cid;
    }

    $comments = xarModAPIFunc('comments','user','get_one', array('cid' => $header['cid']));
    $author_id = $comments[0]['xar_uid'];

    if ($author_id != xarUserGetVar('uid')) {
        if (!xarSecurityCheck('Comments-Edit'))
            return;
    }

    if (!isset($package['postanon'])) {
        $package['postanon'] = 0;
    }
    xarVarValidate('checkbox', $package['postanon']);
    if (!isset($header['itemtype'])) {
        $header['itemtype'] = 0;
    }

    $header['modid'] = $comments[0]['xar_modid'];
    $header['itemtype'] = $comments[0]['xar_itemtype'];
    $header['objectid'] = $comments[0]['xar_objectid'];

    if (empty($receipt['action'])) {
        $receipt['action'] = 'modify';
    }

    // get the title and link of the original object
    $modinfo = xarModGetInfo($header['modid']);
    $itemlinks = xarModAPIFunc($modinfo['name'],'user','getitemlinks',
                               array('itemtype' => $header['itemtype'],
                                     'itemids' => array($header['objectid'])),
                               // don't throw an exception if this function doesn't exist
                               0);
    if (!empty($itemlinks) && !empty($itemlinks[$header['objectid']])) {
        $url = $itemlinks[$header['objectid']]['url'];
        $header['objectlink'] = $itemlinks[$header['objectid']]['url'];
        $header['objecttitle'] = $itemlinks[$header['objectid']]['label'];
    } else {
        $url = xarModURL($modinfo['name'],'user','main');
    }
    if (empty($receipt['returnurl'])) {
        $receipt['returnurl'] = array('encoded' => rawurlencode($url),
                                      'decoded' => $url);
    }

    switch (strtolower($receipt['action'])) {
        case 'submit':
            if (empty($package['title'])) {
                $msg = xarML('Missing [#(1)] field on new #(2)','title','comment');
                xarExceptionSet(XAR_USER_EXCEPTION, 'MISSING_FIELD', new SystemException($msg));
                return;
            }

            if (empty($package['text'])) {
                $msg = xarML('Missing [#(1)] field on new #(2)','text','comment');
                xarExceptionSet(XAR_USER_EXCEPTION, 'MISSING_FIELD', new SystemException($msg));
                return;
            }
            xarModAPIFunc('comments','user','modify',
                                        array('cid'      => $header['cid'],
                                              'text'     => $package['text'],
                                              'title'    => $package['title'],
                                              'postanon' => $package['postanon']));

            xarResponseRedirect($receipt['returnurl']['decoded']);
            return true;
        case 'modify':
            list($comments[0]['transformed-text'],
                 $comments[0]['transformed-title']) =
                        xarModCallHooks('item',
                                        'transform',
                                         $header['cid'],
                                         array($comments[0]['xar_text'],
                                               $comments[0]['xar_title']));


            $package['comments']                = $comments;
            $package['title']                   = $comments[0]['xar_title'];
            $package['text']                    = $comments[0]['xar_text'];
            $package['comments'][0]['xar_cid']  = $header['cid'];
            $receipt['action']                  = 'modify';

            $output['header']                   = $header;
            $output['package']                  = $package;
            $output['receipt']                  = $receipt;

            break;
        case 'preview':
        default:
            list($package['transformed-text'],
                 $package['transformed-title']) = xarModCallHooks('item',
                                                                  'transform',
                                                                  $header['pid'],
                                                                  array($package['text'],
                                                                        $package['title']));

            $comments[0]['xar_text']     = $package['text'];
            $comments[0]['xar_title']    = $package['title'];
            $comments[0]['xar_modid']    = $header['modid'];
            $comments[0]['xar_itemtype'] = $header['itemtype'];
            $comments[0]['xar_objectid'] = $header['objectid'];
            $comments[0]['xar_pid']      = $header['pid'];
            $comments[0]['xar_author']   = ((xarUserIsLoggedIn() && !$package['postanon']) ? xarUserGetVar('name') : 'Anonymous');
            $comments[0]['xar_cid']      = 0;
            $comments[0]['xar_postanon'] = $package['postanon'];
            $comments[0]['xar_date']     = xarLocaleFormatDate("%d %b %Y %H:%M:%S %Z",time());

            $forwarded = xarServerGetVar('HTTP_X_FORWARDED_FOR');
            if (!empty($forwarded)) {
                $hostname = preg_replace('/,.*/', '', $forwarded);
            } else {
                $hostname = xarServerGetVar('REMOTE_ADDR');
            }

            $comments[0]['xar_hostname'] = $hostname;
            $package['comments']         = $comments;
            $receipt['action']           = 'modify';

            break;

    }

    $hooks = comments_user_formhooks();

    $output['hooks']              = $hooks;
    $output['header']             = $header;
    $output['package']            = $package;
    $output['package']['date']    = time();
    $output['package']['uid']     = ((xarUserIsLoggedIn() && !$package['postanon']) ? xarUserGetVar('uid') : 2);
    $output['package']['uname']   = ((xarUserIsLoggedIn() && !$package['postanon']) ? xarUserGetVar('uname') : 'anonymous');
    $output['package']['name']    = ((xarUserIsLoggedIn() && !$package['postanon']) ? xarUserGetVar('name') : 'Anonymous');
    $output['receipt']            = $receipt;
    return $output;

}

?>
