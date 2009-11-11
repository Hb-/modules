<?php
/**
 * Articles module
 *
 * @package modules
 * @copyright (C) 2002-2009 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage Articles Module
 * @link http://xaraya.com/index.php/release/151.html
 * @author mikespub
 */
/**
 * update item from articles_admin_modify
 *
 * @param id     ptid       The publication Type ID for this new article
 * @param array  new_cids   An array with the category ids for this new article (OPTIONAL)
 * @param string preview    Are we gonna see a preview? (OPTIONAL)
 * @param string save       Call the save action (OPTIONAL)
 * @param string return_url The URL to return to (OPTIONAL)
 * @return  bool true on success, or mixed on failure
 */
function articles_admin_update()
{
    // Get parameters
    if(!xarVarFetch('aid',          'isset', $aid,       NULL, XARVAR_DONT_SET)) {return;}
    if(!xarVarFetch('ptid',         'isset', $ptid,      NULL, XARVAR_DONT_SET)) {return;}
    if(!xarVarFetch('modify_cids',  'isset', $cids,      NULL, XARVAR_DONT_SET)) {return;}
    if(!xarVarFetch('preview',      'isset', $preview,   NULL, XARVAR_DONT_SET)) {return;}
    if(!xarVarFetch('save',         'isset', $save,      NULL, XARVAR_DONT_SET)) {return;}
    if (!xarVarFetch('return_url',  'str:1', $return_url, NULL, XARVAR_NOT_REQUIRED)) {return;}
    // Confirm authorisation code
    try {
        $confirm = xarSecConfirmAuthKey();
        if (!$confirm) return;
    } catch (ForbiddenOperationException $e) {
        // Catch exception and fall back to preview
        $msg = $e->getMessage() . "<br />";
        $msg .= xarML('Article was <strong>NOT</strong> saved, please retry.');
        // Save the error message if we are not in preview
        if (!isset($preview)) {
            xarSession::setVar('statusmsg', $msg);
        }
        $preview = 1;
    }

    if (empty($aid) || !is_numeric($aid)) {
        $msg = xarML('Invalid #(1) for #(2) function #(3)() in module #(4)',
                     'item id', 'admin', 'update', 'Articles');
        throw new BadParameterException(null,$msg);
    }

    $pubtypes = xarMod::apiFunc('articles','user','getpubtypes');
    if (empty($ptid) || !isset($pubtypes[$ptid])) {
        $msg = xarML('Invalid #(1) for #(2) function #(3)() in module #(4)',
                     'publication type', 'admin', 'update', 'Articles');
        throw new BadParameterException(null,$msg);
    }

    // Get original article information
    $article = xarMod::apiFunc('articles', 'user', 'get',
                            array('aid' => $aid, 'withcids' => true)
    );
    if (!isset($article)) {
        $msg = xarML('Invalid #(1) for #(2) function #(3)() in module #(4)',
                     'article', 'admin', 'update', 'Articles');
        throw new BadParameterException(null, $msg);
    }

// TODO: switch to DD object style
    $invalid = array();
    if (xarModIsHooked('uploads', 'articles', $ptid)) {
        xarVarSetCached('Hooks.uploads','ishooked',1);
    }
    $modid = xarMod::getRegId('articles');
    $properties = array();
    foreach ($pubtypes[$ptid]['config'] as $field => $value) {
        if (!empty($value['label'])) {
            if (!isset($value['validation'])) {
                $value['validation'] = '';
            }
            $properties[$field] = xarMod::apiFunc('dynamicdata','user','getproperty',
                                                 array('name' => $field,
                                                       'type' => $value['format'],
                                                       'validation' => $value['validation'],
                                                       'value' => $article[$field],
                                                       // fake DD property from articles (for now)
                                                       '_moduleid' => $modid,
                                                       '_itemtype' => $ptid,
                                                       '_itemid'   => $aid));
            $check = $properties[$field]->checkInput($field);
            if (!$check) {
                if ($field == 'authorid') {
                    // re-assign article to Anonymous
                    $article[$field] = _XAR_ID_UNREGISTERED;
                } else {
                    $article[$field] = '';
                    $invalid[$field] = $properties[$field]->invalid;
                    $preview = 1;
                }
            } else {
                $article[$field] = $properties[$field]->value;
            }
        }
        if (!isset($article[$field])) {
            $article[$field] = '';
        }
      }

    $article['ptid'] = $ptid;

    // check that we have a title when we need one, or fill in a dummy one
    if (empty($article['title'])) {
        if (empty($pubtypes[$ptid]['config']['title']['label'])) {
            $article['title'] = ' ';
        } elseif (empty($invalid['title'])) {
            // show this to the user
            $invalid['title'] = xarML('This field is required');
        }
    }
    if (empty($article['pubdate'])) {
        $article['pubdate'] = 0;
    }

// TODO: make $status dependent on permissions ?
    if (empty($article['status'])) {
        if (empty($pubtypes[$ptid]['config']['status']['label'])) {
            $article['status'] = 2;
        } else {
            $article['status'] = 0;
        }
    }

    if (empty($article['language'])) {
        $article['language'] = xarMLSGetCurrentLocale();
    }

    if (!empty($cids) && count($cids) > 0) {
        $article['cids'] = array_values(preg_grep('/\d+/',$cids));
    } else {
        $article['cids'] = array();
    }

    // for preview
    $article['pubtypeid'] = $ptid;
    $article['aid'] = $aid;

    if ($preview || count($invalid) > 0) {
        $data = xarMod::guiFunc('articles','admin','modify',
                             array('preview' => true,
                                   'article' => $article,
                                   'return_url' => $return_url,
                                   'invalid' => $invalid));
        unset($article);
        if (is_array($data)) {
            return xarTplModule('articles','admin','modify',$data);
        } else {
            return $data;
        }
    }

    // call transform input hooks
    $article['transform'] = array('summary','body','notes');
    $article = xarModCallHooks('item', 'transform-input', $aid, $article,
                               'articles', $ptid);

    // Pass to API
    if (!xarMod::apiFunc('articles', 'admin', 'update', $article)) {
        return;
    }
    unset($article);

    // Success
    xarSession::setVar('statusmsg', xarML('Article Updated'));

    // Remove checkout info (even if it was from someone else)
    xarModItemVars::delete('articles','checkout_info', $aid);

    // Save and continue editing via feature request.
    if (isset($save) && xarSecurityCheck('EditArticles',0,'Article',$ptid.':All:All:All')) {
        xarResponse::Redirect(xarModURL('articles', 'admin', 'modify',
                                      array('aid' => $aid)));
        return true;
    }

    if (!empty($return_url)) {
        xarResponse::Redirect($return_url);
        return true;
    }

    // Return to the original admin view
    $lastview = xarSession::getVar('Articles.LastView');
    if (isset($lastview)) {
        $lastviewarray = unserialize($lastview);
        if (!empty($lastviewarray['ptid']) && $lastviewarray['ptid'] == $ptid) {
            extract($lastviewarray);
            xarResponse::Redirect(xarModURL('articles', 'admin', 'view',
                                          array('ptid' => $ptid,
                                                'catid' => $catid,
                                                'status' => $status,
                                                'startnum' => $startnum)));
            return true;
        }
    }

    // if we can edit articles, go to admin view, otherwise go to user view
    if (xarSecurityCheck('EditArticles',0,'Article',$ptid.':All:All:All')) {
        xarResponse::Redirect(xarModURL('articles', 'admin', 'view',
                                      array('ptid' => $ptid)));
    } else {
        xarResponse::Redirect(xarModURL('articles', 'user', 'view',
                                      array('ptid' => $ptid)));
    }

    return true;
}

?>
