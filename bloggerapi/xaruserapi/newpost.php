<?php

/**
 * File: $Id$
 *
 * Create a new blog posting
 *
 * @package modules
 * @copyright (C) 2003 by the Xaraya Development Team.
 * @link http://www.xaraya.com
 * 
 * @subpackage bloggerapi
 * @author Marcel van der Boom <marcel@xaraya.com>
*/

include("modules/bloggerapi/xarincludes/common.php");

/**
* Create a new posting
* 
* Takes an xmlrpc enveloped message according to blogger api and
* uses it to create a new posting in Xaraya articles.
*
* @param  xmlrpcmsg   xml-rpc message with the parameters defined in blogger API
* @return xmlrpcresp  Returns an xmlrpc response message, which contains the 
*                     article-id on success or errormessage on failure
* @see    xmlrpc_userapi_call(), xmlrpcresp, xmlrpcmsg
*/
function bloggerapi_userapi_newpost($msg) {
    xarLogMessage("blogger api: newPost");
	setlocale(LC_TIME, xarConfigGetVar('locale'));
	// get the params, we skip appkey
	$sn1=$msg->getParam(1);  $category   = $sn1->scalarval();
	$sn2=$msg->getParam(2);  $username   = $sn2->scalarval();
	$sn3=$msg->getParam(3);  $password   = $sn3->scalarval();
	$sn4=$msg->getParam(4);  $content   = $sn4->scalarval();
	$sn5=$msg->getParam(5);  $publish   = $sn5->scalarval();
	xarLogVariable('publish', $publish);

    if (!xarUserLogin($username,$password)) {
      $err = xarML("Invalid user (#(1)) or wrong password while creating new post",$username);
    } else {
        // Fix for w.bloggar via marsel@phatcom.net (David Taylor)
        ereg("<title>(.*)</title>",$content, $title);
        $title = xarML($title[1]);
        $content = ereg_replace("<title>(.*)</title>","",$content);
        if (empty($title)){
	        $title = xarML("Post from #(1) on: #(2)",$username,date("Y-m-d"));
        }
        $summary = $content;
		$cids=array(); $cids[] = $category;
        $bodytype = ' ';
        $bodytext = $content;
        $language = ' ';
        $status=0; // Submitted
        if ($publish) {
          $status ='3'; // Frontpage
        }
        // FIXME: Test for exceptions
        $pubType= xarModGetVar('bloggerapi','bloggerpubtype');
        $postid = xarModAPIFunc('articles','admin','create',array('ptid'=>$pubType,
                                                                  'title'=>$title,'summary'=>$summary,
                                                                  'cids' => $cids,'bodytype'=>$bodytype, 'bodytext'=>$bodytext,
                                                                  'language'=>$language,
                                                                  'status' => $status));
        xarLogMessage("Created article $postid in category $category with status $status ($publish) ");
        if (!$postid) {
            xarExceptionFree();
            $err = xarML("Failed to create new post #(1) (permission problem?)",$postid);
        }
    }
	  
	if (!empty($err)) {
        $output = xarModAPIFunc('xmlrpcserver','user','faultresponse',array('errorstring' => $err));
	}	else {
        $data['postid'] = $postid;
        $output = _bloggerapi_createresponse('newpost',$data);
    }
    return $output;
} 
?>
