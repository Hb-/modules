<?php
function headlines_admin_create($args)
{
    if (!xarVarFetch('url','str:1:',$url)) return;
    extract($args);

    // Confirm authorisation code.
    if (!xarSecConfirmAuthKey()) return;

      if (!ereg("^http://|https://|ftp://", $url)) {
        $msg = xarML('Invalid Address for Feed');
        xarErrorSet(XAR_USER_EXCEPTION, 'MISSING_DATA', new DefaultUserException($msg));
        return;
      }

    // The API function is called
    $hid = xarModAPIFunc('headlines',
                         'admin',
                         'create',
                         array('url' => $url));

    if ($hid == false) return;   

    // Lets Create the Cache Right now to save processing later.

    // Require the xmlParser class
    require_once('modules/base/xarclass/xmlParser.php');

    // Require the feedParser class
    require_once('modules/base/xarclass/feedParser.php');

    $feedfile = $url;

    // Get the feed file (from cache or from the remote site)
    $feeddata = xarModAPIFunc('base', 'user', 'getfile',
                              array('url' => $feedfile,
                                    'cached' => true,
                                    'cachedir' => 'cache/rss',
                                    'refresh' => 3600,
                                    'extension' => '.xml'));
    if (!$feeddata) {
        return; // throw back
    }

    // Create a need feedParser object
    $p = new feedParser();

    // Tell feedParser to parse the data
    $info = $p->parseFeed($feeddata);

    if (!empty($info['warning'])){
        $msg = xarML('There is a problem with this feed : #(1)', $info['warning']);
        xarErrorSet(XAR_USER_EXCEPTION, 'MISSING_DATA', new DefaultUserException($msg));
        return;
    }

    xarResponseRedirect(xarModURL('headlines', 'admin', 'view'));

    // Return
    return true;
}
?>
