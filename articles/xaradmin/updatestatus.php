<?php

/**
 * update item from articles_admin_modify
 */
function articles_admin_updatestatus()
{
    // Get parameters
    if(!xarVarFetch('aids',   'isset', $aids,    NULL, XARVAR_DONT_SET)) {return;}
    if(!xarVarFetch('status', 'isset', $status,  NULL, XARVAR_DONT_SET)) {return;}
    if(!xarVarFetch('catid',  'isset', $catid,   NULL, XARVAR_DONT_SET)) {return;}
    if(!xarVarFetch('ptid',   'isset', $ptid,    NULL, XARVAR_DONT_SET)) {return;}


    // Confirm authorisation code
    if (!xarSecConfirmAuthKey()) return;

    if (!isset($aids) || count($aids) == 0) {
        $msg = xarML('No articles selected');
        xarExceptionSet(XAR_USER_EXCEPTION, 'MISSING_DATA',
                       new DefaultUserException($msg));
        return;
    }
    if (!isset($status) || !is_numeric($status) || $status < 0 || $status > 3) {
        $msg = xarML('Invalid status');
        xarExceptionSet(XAR_USER_EXCEPTION, 'BAD_DATA',
                       new DefaultUserException($msg));
        return;
    }

    $pubtypes = xarModAPIFunc('articles','user','getpubtypes');
    if (!empty($ptid)) {
        $descr = $pubtypes[$ptid]['descr'];
    } else {
        $descr = xarML('Articles');
        $ptid = null;
    }

    foreach ($aids as $aid => $val) {
        if ($val != 1) {
            continue;
        }
        // Get original article information
        $article = xarModAPIFunc('articles',
                                 'user',
                                 'get',
                                 array('aid' => $aid,
                                       'withcids' => 1));
        if (!isset($article) || !is_array($article)) {
            $msg = xarML('Unable to find #(1) item #(2)',
                         $descr, xarVarPrepForDisplay($aid));
            xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'BAD_PARAM',
                           new SystemException($msg));
            return;
        }
        $article['ptid'] = $article['pubtypeid'];
        // Security check
        $input = array();
        $input['article'] = $article;
        $input['mask'] = 'EditArticles';
        if (!xarModAPIFunc('articles','user','checksecurity',$input)) {
            $msg = xarML('You have no permission to modify #(1) item #(2)',
                         $descr, xarVarPrepForDisplay($aid));
            xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'NO_PERMISSION',
                           new SystemException($msg));
            return;
        }

        // Update the status now
        $article['status'] = $status;

        // Pass to API
        if (!xarModAPIFunc('articles', 'admin', 'update', $article)) {
            return; // throw back
        }
    }
    unset($article);

    // Success
    xarSessionSetVar('statusmsg', xarML('Article Status Updated'));

    if (empty($catid)) {
        $catid = null;
    }
    xarResponseRedirect(xarModURL('articles', 'admin', 'view',
                                  array('ptid' => $ptid, 'catid' => $catid)));

    return true;
}

?>
