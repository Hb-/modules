<?php

/**
 * modify article
 */
function articles_admin_modify($args)
{
    extract($args);

    // Get parameters
    if(!xarVarFetch('aid','isset', $aid, NULL, XARVAR_DONT_SET)) {return;}

    if (isset($aid) && empty($preview)) {
        $preview = 0;
        // Get article information
        $article = xarModAPIFunc('articles',
                                'user',
                                'get',
                                array('aid' => $aid,
                                      'withcids' => true));
    }
    if (!isset($article) || $article == false) {
        $msg = xarML('Unable to find #(1) item #(2)',
                    'Article', xarVarPrepForDisplay($aid));
        xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'NO_PERMISSION',
                       new SystemException($msg));
        return;
    }

    $ptid = $article['pubtypeid'];
    if (!isset($ptid)) {
       $ptid = '';
    }
    $data = array();
    $data['ptid'] = $ptid;
    $data['aid'] = $aid;

    // Use articles user GUI function (not API) for preview
    if (!xarModLoad('articles','user')) return;
    $data['preview'] = xarModFunc('articles', 'user', 'display',
                                  array('preview' => true, 'article' => $article));

    $pubtypes = xarModAPIFunc('articles','user','getpubtypes');

    // Security check
    $input = array();
    $input['article'] = $article;
    $input['mask'] = 'EditArticles';
    if (!xarModAPIFunc('articles','user','checksecurity',$input)) {
        $msg = xarML('You have no permission to modify #(1) item #(2)',
                     $pubtypes[$ptid]['descr'], xarVarPrepForDisplay($aid));
        xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'NO_PERMISSION',
                       new SystemException($msg));
        return;
    }
    unset($input);

    // preset some variables for hook modules
    $article['module'] = 'articles';
    $article['itemid'] = $aid;
    $article['itemtype'] = $ptid;

    $hooks = xarModCallHooks('item','modify',$aid,$article);
    if (empty($hooks)) {
        $hooks = '';
    }
    $data['hooks'] = $hooks;
    // Array containing the different labels

    // Array containing the different values (except the article fields)
    $values = array();

    // Show publication type
    $values['pubtype'] = $pubtypes[$ptid]['descr'];
    $data['values'] = $values;
    // TODO - language

// Note : this determines which fields are really shown in the template !!!
    // Show actual data fields
    $fields = array();
    $data['withupload'] = 0;
    // Get the labels from the pubtype configuration
// TODO: make order dependent on pubtype or not ?
//    foreach ($pubtypes[$ptid]['config'] as $field => $value) {}
    $pubfields = xarModAPIFunc('articles','user','getpubfields');
    foreach ($pubfields as $field => $dummy) {
        $value = $pubtypes[$ptid]['config'][$field];
        if (empty($value['label'])) {
            continue;
        }
        $input = array();
        $input['name'] = $field;
        $input['id'] = $field;
        $input['type'] = $value['format'];
        $input['value'] = $article[$field];

        if ($input['type'] == 'fileupload' || $input['type'] == 'textupload' ) {
            $data['withupload'] = 1;
        }
        // using new field tags here
        $fields[] = array('label' => $value['label'], 'id' => $field,
                          'definition' => $input);
    }
    unset($article);
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

    $formhooks = articles_user_formhooks();
    $data['formhooks'] = $formhooks;

    $data['previewlabel'] = xarML('Preview');
    $data['updatelabel'] = xarML('Update Article');
    $data['authid'] = xarSecGenAuthKey();

    if (!empty($ptid)) {
        $template = $pubtypes[$ptid]['name'];
    } else {
// TODO: allow templates per category ?
       $template = null;
    }

    return xarTplModule('articles', 'admin', 'modify', $data, $template);
}

?>
