<?php

/**
 * display item
 */
function articles_user_display($args)
{
    // Get parameters from user
    if(!xarVarFetch('aid',  'isset', $aid,   NULL, XARVAR_DONT_SET)) {return;}
    if(!xarVarFetch('page', 'isset', $page,  NULL, XARVAR_DONT_SET)) {return;}
// this is used to determine whether we come from a pubtype-based view or a
// categories-based navigation
    if(!xarVarFetch('ptid', 'isset', $ptid,  NULL, XARVAR_DONT_SET)) {return;}

    // Override if needed from argument array (e.g. preview)
    extract($args);

    // Defaults
    if (!isset($page)) {
        $page = 1;
    }
    if (!isset($preview)) {
        $preview = 0;
    }

// TODO: make configurable
    // show the number of articles for each publication type
    $showpubcount = 1;
    $showcatcount = 0;

    if ($preview) {
        if (!isset($article)) {
            return xarML('Invalid article');
        }
        $aid = $article['aid'];
    } elseif (!isset($aid) || !is_numeric($aid) || $aid < 1) {
        return xarML('Invalid article ID');
    }

    // Load API
    if (!xarModAPILoad('articles', 'user')) return;

    // Get article
    if (!$preview) {
        $article = xarModAPIFunc('articles',
                                'user',
                                'get',
                                array('aid' => $aid,
                                      'withcids' => true));
    }

    if (!is_array($article)) {
        return xarML('Failed to retrieve article');
    }

// keep original ptid (if any)
//    $ptid = $article['pubtypeid'];
    $pubtypeid = $article['pubtypeid'];
    $authorid = $article['authorid'];
    $cids = $article['cids'];

    // Get the article settings for this publication type
    if (empty($ptid)) {
        $settings = unserialize(xarModGetVar('articles', 'settings'));
    } else {
        $settings = unserialize(xarModGetVar('articles', 'settings.'.$ptid));
    }

    // Initialize the data array
    $data = array();
    $data['ptid'] = $ptid;

    // Security check for EDIT access
    $edit = true;
    if (isset($article['cids']) && count($article['cids']) > 0) {
// TODO: do we want all-or-nothing access here, or is one access enough ?
        foreach ($article['cids'] as $cid) {
            if (!xarSecurityCheck('EditArticles',0,'Article',"$article[pubtypeid]:$cid:$article[authorid]:$article[aid]")) {
                $edit = false;
                break;
            }
        }
    } else {
        if (!xarSecurityCheck('EditArticles',0,'Article',"$article[pubtypeid]:All:$article[authorid]:$article[aid]")) {
            $edit = false;
        }
    }
    if ($edit) {
        $data['editurl'] = xarModURL('articles',
                                    'admin',
                                    'modify',
                                    array('aid' => $article['aid']));
    // don't show unapproved articles to non-editors
    } elseif (!$preview && $article['status'] < 2) {
        return xarML('You have no permission to view this item');
    }
    $data['edittitle'] = xarML('Edit');

// TODO: improve the case where we have several icons :)
    $data['topic_icons'] = '';
    if (count($cids) > 0) {
        if (!xarModAPILoad('categories', 'user')) return;
        $catlist = xarModAPIFunc('categories',
                                'user',
                                'getcatinfo',
                                array('cids' => $cids));
        foreach ($catlist as $cat) {
            if (!empty($cat['image'])) {
                $link = xarModURL('articles','user','view',
                                 array(//'status' => array(3,2),
                                       'catid' => $cat['cid'],
                                       'ptid' => $ptid));
                $image = xarTplGetImage($cat['image'],'categories');
                $data['topic_icons'] .= '<a href="'. $link .'">'.
                                        '<img src="'. $image .
                                        '" border="0" alt="'. xarVarPrepForDisplay($cat['name']) .'" />'.
                                        '</a>';
                break;
            }
        }
    }

    // multi-page output for 'body' field (mostly for sections at the moment)
    $themeName = xarVarGetCached('Themes.name','CurrentTheme');
        if ($themeName != 'print'){
        if (strstr($article['body'],'<!--pagebreak-->')) {
            if ($preview) {
                $article['body'] = preg_replace('/<!--pagebreak-->/',
                                                '<hr/><div align="center">'.xarML('Page Break').'</div><hr/>',
                                                $article['body']);
                $data['previous'] = '';
                $data['next'] = '';
            } else {
                $pages = explode('<!--pagebreak-->',$article['body']);
                if (empty($page)) {
                    $page = 1;
                } elseif ($page > count($pages)) {
                    $page = count($pages);
                }
                $article['body'] = $pages[$page - 1];
                $numpages = count($pages);
                unset($pages);
            // TODO: use BL widget ?
                if ($page > 1) {
                    // only count first page hits
                    xarVarSetCached('Hooks.hitcount','nocount',1);

                    $data['previous'] = '<a href="' . xarModURL('articles','user','display',
                                                               array('aid' => $aid))
                                        . '">&lt;&lt; </a>&nbsp;&nbsp;';
                    if ($page > 2) {
                        $data['previous'] .= '<a href="' . xarModURL('articles','user','display',
                                                             array('aid' => $aid,
                                                                   'page' => $page - 1));
                    } else {
                        $data['previous'] .= '<a href="' . xarModURL('articles','user','display',
                                                             array('aid' => $aid));
                    }
                    $data['previous'] .= '">' . xarML('prev')
                                      . ' (' . ($page -1) . '/' . $numpages . ')</a>';
                } else {
                    $data['previous'] = '&nbsp;';
                }
                if ($page < $numpages) {
                    $data['next'] = '<a href="' . xarModURL('articles','user','display',
                                                             array('aid' => $aid,
                                                                   'page' => $page + 1))
                                      . '">' . xarML('next')
                                      . ' (' . ($page + 1) . '/' . $numpages . ')</a>&nbsp;&nbsp;';
                    $data['next'] .= '<a href="' . xarModURL('articles','user','display',
                                                             array('aid' => $aid,
                                                                   'page' => $numpages))
                                      . '"> &gt;&gt;</a>';
                } else {
                    $data['next'] = '&nbsp;';
                }
            }
        } else {
            $data['previous'] = '';
            $data['next'] = '';
        }
        } else {
                $article['body'] = preg_replace('/<!--pagebreak-->/',
                                                '',
                                                $article['body']);
        }

// TEST
    if (!empty($settings['prevnextart'])) {
        $prevart = xarModAPIFunc('articles','user','getprevious',
                                 array('aid' => $aid,
                                       'ptid' => $ptid,
                                       // unused
                                       //'sort' => $sort,
                                       'status' => array(3,2),
                                       'enddate' => time()));
        if (!empty($prevart['aid'])) {
            $data['prevart'] = xarModURL('articles','user','display',
                                         array('aid' => $prevart['aid'],
                                               'ptid' => $prevart['pubtypeid']));
        } else {
            $data['prevart'] = '';
        }
        $nextart = xarModAPIFunc('articles','user','getnext',
                                 array('aid' => $aid,
                                       'ptid' => $ptid,
                                       // unused
                                       //'sort' => $sort,
                                       'status' => array(3,2),
                                       'enddate' => time()));
        if (!empty($nextart['aid'])) {
            $data['nextart'] = xarModURL('articles','user','display',
                                         array('aid' => $nextart['aid'],
                                               'ptid' => $nextart['pubtypeid']));
        } else {
            $data['nextart'] = '';
        }
    } else {
        $data['prevart'] = '';
        $data['nextart'] = '';
    }

    // Display article

    // Get publication types
    $pubtypes = xarModAPIFunc('articles','user','getpubtypes');

    if (!empty($article['title'])) {
        xarTplSetPageTitle(xarVarPrepForDisplay($article['title']), xarVarPrepForDisplay($pubtypes[$pubtypeid]['descr']));
        // Save some variables to (temporary) cache for use in blocks etc.
        xarVarSetCached('Comments.title','title',$article['title']);
    }

    // Fill in the fields based on the pubtype configuration
    foreach ($pubtypes[$pubtypeid]['config'] as $field => $value) {
        if (empty($value['label'])) {
            $data[$field] = '';
            continue;
        }
        switch ($value['format']) {
            case 'username':
        // TODO: replace by authorid and sync with templates
                $data['author'] = xarUserGetVar('name', $article[$field]);
                if (empty($data['author'])) {
                    $data['author'] = xarUserGetVar('uname', $article[$field]);
                }
                if ($article[$field] > 1) {
                    $data['profile'] = xarModURL('roles','user','display',
                                                array('uid' => $article[$field]));
                }
                break;
            case 'status':
                $data[$field] = $article[$field];
                break;
            case 'calendar':
        // TODO: replace by pubdate and sync with templates
                if (!empty($article[$field])) {
                    $data[$field] = xarLocaleFormatDate('%a, %d %B %Y %H:%M:%S %Z', $article[$field]);
                } else {
                    $data[$field] = '';
                }
                if ($field == 'pubdate') {
                    $data['date'] = $data[$field];
                }
                break;
            case 'url':
                $data[$field] = xarVarPrepHTMLDisplay($article[$field]);
                if (!empty($article[$field]) && $article[$field] != 'http://') {
                    $data['redirect'] = xarModURL('articles','user','redirect',
                                                  array('aid' => $aid,
                                                        'ptid' => $ptid));
                } else {
                    $data['redirect'] = '';
                }
                break;
        // TEST ONLY
            case 'webpage':
                if (empty($value['validation'])) {
                    $value['validation'] = 'modules/articles';
                }
                $data[$field] = xarModAPIFunc('dynamicdata','user','showoutput',
                                              array('name' => $field,
                                                    'type' => 'webpage',
                                                    'validation' => $value['validation'],
                                                    'value' => $article[$field]));
                break;
            case 'imagelist':
                if (empty($value['validation'])) {
                    $value['validation'] = 'modules/articles/xarimages';
                }
                $data[$field] = xarModAPIFunc('dynamicdata','user','showoutput',
                                              array('name' => $field,
                                                    'type' => 'imagelist',
                                                    'validation' => $value['validation'],
                                                    'value' => $article[$field]));
                break;
            default:
                $data[$field] = xarVarPrepHTMLDisplay($article[$field]);
                //$data[$field] = $article[$field];
        }
    }
    unset($article);

    // temp. fix to include dynamic data fields without changing templates
    if (xarModIsHooked('dynamicdata','articles',$pubtypeid)) {
        list($properties) = xarModAPIFunc('dynamicdata','user','getitemfordisplay',
                                          array('module'   => 'articles',
                                                'itemtype' => $pubtypeid,
                                                'itemid'   => $aid,
                                                'preview'  => $preview));
        if (!empty($properties) && count($properties) > 0) {
            foreach (array_keys($properties) as $field) {
                $data[$field] = $properties[$field]->getValue();
            // TODO: clean up this temporary fix
                $data[$field.'_output'] = $properties[$field]->showOutput();
            }
        }
    }

    // Let any transformation hooks know that we want to transform some text.
    // You'll need to specify the item id, and an array containing all the
    // pieces of text that you want to transform (e.g. for autolinks, wiki,
    // smilies, bbcode, ...).
    $data['itemtype'] = $pubtypeid;
// TODO: what about transforming DD fields ?
    $data['transform'] = array('title','summary','body','notes');
    $data = xarModCallHooks('item', 'transform', $aid, $data, 'articles');

    // Navigation links
    $data['publabel'] = xarML('Publication');
    $data['publinks'] = xarModAPIFunc('articles','user','getpublinks',
                                     array('status' => array(3,2),
                                           'count' => $showpubcount));
    if (!empty($settings['showmap'])) {
        $data['maplabel'] = xarML('View Article Map');
        $data['maplink'] = xarModURL('articles','user','viewmap',
                                    array('ptid' => $ptid));
    }
    if (!empty($settings['showarchives'])) {
        $data['archivelabel'] = xarML('View Archives');
        $data['archivelink'] = xarModURL('articles','user','archive',
                                        array('ptid' => $ptid));
    }
    if (!empty($settings['showpublinks'])) {
        $data['showpublinks'] = 1;
    } else {
        $data['showpublinks'] = 0;
    }

    // Tell the hitcount hook not to display the hitcount, but to save it
    // in the variable cache.
    if (xarModIsHooked('hitcount','articles',$pubtypeid)) {
        xarVarSetCached('Hooks.hitcount','save',1);
        $dohits = 1;
    } else {
        $dohits = 0;
    }

    // Tell the ratings hook to save the rating in the variable cache.
    if (xarModIsHooked('ratings','articles',$pubtypeid)) {
        xarVarSetCached('Hooks.ratings','save',1);
        $dorating = 1;
    } else {
        $dorating = 0;
    }

    // Hooks
    if ($preview) {
        $data['hooks'] = '';
    } else {
        $data['hooks'] = xarModCallHooks('item', 'display', $aid,
                                         array('itemtype'  => $pubtypeid,
                                               'returnurl' => xarModURL('articles',
                                                                        'user',
                                                                        'display',
                                                                        array('aid' => $aid,
                                                                              'ptid' => $ptid))
                                              ),
                                         'articles'
                                        );
    }

    // Retrieve the current hitcount from the variable cache
    if ($dohits && xarVarIsCached('Hooks.hitcount','value')) {
        $data['counter'] = xarVarGetCached('Hooks.hitcount','value');
    } else {
        $data['counter'] = '';
    }

    // Retrieve the current rating from the variable cache
    if ($dorating && xarVarIsCached('Hooks.ratings','value')) {
        $data['rating'] = intval(xarVarGetCached('Hooks.ratings','value'));
    } else {
        $data['rating'] = '';
    }

    // Save some variables to (temporary) cache for use in blocks etc.
    xarVarSetCached('Blocks.articles','title',$data['title']);

    // Generating keywords from the API now instead of setting the entire
    // body into the cache.
    $keywords = xarModAPIFunc('articles',
                              'user',
                              'generatekeywords',
                              array('incomingkey' => $data['body']));

    xarVarSetCached('Blocks.articles','body',$keywords);
    xarVarSetCached('Blocks.articles','summary',$data['summary']);
    xarVarSetCached('Blocks.articles','aid',$aid);
    xarVarSetCached('Blocks.articles','ptid',$ptid);
    xarVarSetCached('Blocks.articles','cids',$cids);
    xarVarSetCached('Blocks.articles','authorid',$authorid);
    if (isset($data['author'])) {
        xarVarSetCached('Blocks.articles','author',$data['author']);
    }
// TODO: add this to articles configuration ?
//if ($shownavigation) {
    $data['aid'] = $aid;
    $data['cids'] = $cids;
    xarVarSetCached('Blocks.categories','module','articles');
    xarVarSetCached('Blocks.categories','itemtype',$ptid);
    xarVarSetCached('Blocks.categories','itemid',$aid);
    xarVarSetCached('Blocks.categories','cids',$cids);

    if (!empty($ptid) && !empty($pubtypes[$ptid]['descr'])) {
        xarVarSetCached('Blocks.categories','title',$pubtypes[$ptid]['descr']);
    }

    // optional category count
    if ($showcatcount && !empty($ptid)) {
        $pubcatcount = xarModAPIFunc('articles',
                                    'user',
                                    'getpubcatcount',
                                    // frontpage or approved
                                    array('status' => array(3,2),
                                          'ptid' => $ptid));
        if (!empty($pubcatcount[$ptid])) {
            xarVarSetCached('Blocks.categories','catcount',$pubcatcount[$ptid]);
        }
    } else {
    //    xarVarSetCached('Blocks.categories','catcount',array());
    }
//}

    // Module template depending on publication type
    $template = $pubtypes[$pubtypeid]['name'];

    // Page template depending on publication type (optional)
    if (empty($preview) && !empty($settings['page_template'])) {
        xarTplSetPageTemplateName($settings['page_template']);
    }

    // Specific layout within a template (optional)
    if (isset($layout)) {
        $data['layout'] = $layout;
    }

    // return template out
    return xarTplModule('articles', 'user', 'display', $data, $template);
}

?>
