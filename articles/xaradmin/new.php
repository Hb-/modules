<?php

/**
 * add new article
 */
function articles_admin_new($args)
{
    extract($args);

    // Get parameters
    if (!xarVarFetch('ptid',  'id', $ptid, NULL,  XARVAR_NOT_REQUIRED)) {return;}
    if (!xarVarFetch('catid', 'id', $catid, NULL, XARVAR_NOT_REQUIRED)) {return;}

    if (!empty($preview) && isset($article)) {
        $ptid = $article['ptid'];
    }
    $data = array();
    $data['ptid'] = $ptid;

    if (!isset($article)) {
        $article = array();
    }
    if (!isset($articles['cids']) && !empty($catid)) {
        $article['cids'] = preg_split('/[ +-]/',$catid);
    }

    $pubtypes = xarModAPIFunc('articles','user','getpubtypes');

    // Security check
    if (empty($ptid)) {
        $ptid = '';
    // TODO: check by category too ?
        if (!xarSecurityCheck('SubmitArticles',0)) {
            $msg = xarML('You have no permission to submit Articles');
            xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'NO_PERMISSION',
                            new SystemException($msg));
            return;
        }
    } else {
        if (isset($article['cids']) && count($article['cids']) > 0) {
            foreach ($article['cids'] as $cid) {
                if (!xarSecurityCheck('SubmitArticles',0,'Article',"$ptid:$cid:All:All")) {
                    $catinfo = xarModAPIFunc('categories', 'user', 'getcatinfo',
                                             array('cid' => $cid));
                    if (empty($catinfo['name'])) {
                        $catinfo['name'] = $cid;
                    }
                    $msg = xarML('You have no permission to submit #(1) in category #(2)',
                                 $pubtypes[$ptid]['descr'],$catinfo['name']);
                    xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'NO_PERMISSION',
                                    new SystemException($msg));
                    return;
                }
            }
        } else {
            if (!xarSecurityCheck('SubmitArticles',0,'Article',"$ptid:All:All:All")) {
                $msg = xarML('You have no permission to submit #(1)',
                             $pubtypes[$ptid]['descr']);
                xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'NO_PERMISSION',
                                new SystemException($msg));
                return;
            }
        }
        if (xarModIsHooked('uploads', 'articles', $ptid)) {
            xarVarSetCached('Hooks.uploads','ishooked',1);
        }
    }
    if (!empty($preview)) {
        // Use articles user GUI function (not API) for preview
        if (!xarModLoad('articles','user')) return;
        $preview = xarModFunc('articles', 'user', 'display',
                             array('preview' => true, 'article' => $article));
    } else {
        $preview = '';
    }
    $data['preview'] = $preview;

    if (!empty($ptid)) {
        // preset some variables for hook modules
        $article['module'] = 'articles';
        $article['itemid'] = 0;
        $article['itemtype'] = $ptid;

        $hooks = xarModCallHooks('item','new','',$article);
    }
    if (empty($hooks)) {
        $hooks = '';
    }
    $data['hooks'] = $hooks;

    // Array containing the different labels
    $labels = array();

    // Show publication type
    $pubfilters = array();
    foreach ($pubtypes as $id => $pubtype) {
        $pubitem = array();
        if ($id == $ptid) {
            $pubitem['plink'] = '';
        } else {
            if (!xarSecurityCheck('SubmitArticles',0,'Article',$id.':All:All:All')) {
                continue;
            }
            $pubitem['plink'] = xarModURL('articles','admin','new',
                                         array('ptid' => $id));
        }
        $pubitem['ptitle'] = $pubtype['descr'];
        $pubfilters[] = $pubitem;
    }
    $data['pubfilters'] = $pubfilters;

    // Array containing the different values (except the article fields)
    $values = array();

    // TODO - language

// Note : this determines which fields are really shown in the template !!!
    // Show actual data fields
    $fields = array();
    $data['withupload'] = 0;
    if (!empty($ptid)) {
    // TODO: make order dependent on pubtype or not ?
    //    foreach ($pubtypes[$ptid]['config'] as $field => $value) {}
        $pubfields = xarModAPIFunc('articles','user','getpubfields');
        foreach ($pubfields as $field => $dummy) {
            $value = $pubtypes[$ptid]['config'][$field];
            if (empty($value['label']) || empty($value['input'])) {
                continue;
            }
            $input = array();
            $input['name'] = $field;
            $input['type'] = $value['format'];
            $input['id'] = $field;
            if (!empty($preview) && isset($article[$field])) {
                $input['value'] = $article[$field];
            } elseif ($field == 'pubdate') {
                // default publication time is now
                $input['value'] = time();
            } else {
                $input['value'] = '';
            }
            if (isset($value['validation'])) {
                $input['validation'] = $value['validation'];
            }

            if ($input['type'] == 'fileupload' || $input['type'] == 'textupload' ) {
                $data['withupload'] = 1;
            }
            if (!empty($preview) && isset($invalid) && !empty($invalid[$field])) {
                $input['invalid'] = $invalid[$field];
            }
            $fields[] = array('label' => $value['label'], 'id' => $field,
                              'definition' => $input);
        }
    }
    $data['fields'] = $fields;

    if (!empty($ptid) && empty($data['withupload']) &&
        (xarVarIsCached('Hooks.dynamicdata','withupload') || xarModIsHooked('uploads', 'articles', $ptid)) ) {
        $data['withupload'] = 1;
    }

    // Show allowable HTML
    $data['allowedhtml'] = '';
    foreach (xarConfigGetVar('Site.Core.AllowableHTML') as $k=>$v) {
        if ($v) {
            $data['allowedhtml'] .= '&lt;' . $k . '&gt; ';
        }
    }

    if (!empty($ptid)) {
        $formhooks = articles_user_formhooks($ptid);
        $data['formhooks'] = $formhooks;
    }

    $data['previewlabel'] = xarVarPrepForDisplay(xarML('Preview'));
    $data['addlabel'] = xarVarPrepForDisplay(xarML('Add Article'));
    $data['authid'] = xarSecGenAuthKey();
    $data['values'] = $values;

    if (!empty($ptid)) {
        $template = $pubtypes[$ptid]['name'];
    } else {
// TODO: allow templates per category ?
       $template = null;
    }

    return xarTplModule('articles', 'admin', 'new', $data, $template);
}

?>
