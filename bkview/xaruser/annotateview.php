<?php

/**
 * File: $Id$
 *
 * annotated view for a file 
 *
 * @package modules
 * @copyright (C) 2004 The Digital Development Foundation, Inc.
 * @link http://www.xaraya.com
 * 
 * @subpackage bkview
 * @author Marcel van der Boom <marcel@xaraya.com>
*/

function bkview_user_annotateview($args)
{
    xarVarFetch('repoid','id',$repoid);
    xarVarFetch('file','str::',$file,'ChangeSet');
    xarVarFetch('rev','str::',$rev,'1.0');
    extract($args);

    $item = xarModAPIFunc('bkview','user','get',array('repoid' => $repoid));
    if (!isset($item) && xarCurrentErrorType() != XAR_NO_EXCEPTION) return; // throw back
    $repo =& $item['repo'];

    $csetrev=$repo->bkChangeSet($file,$rev);
    $changeset=new bkChangeSet($repo,$csetrev);
    
    $delta = new bkDelta($changeset,$file,$rev);
    $annotate = $delta->bkAnnotate();
    $annolines =array();
    $data['annolines']=array();
    $counter=1;
    while (list(,$line) = each($annotate)) {
        $annolines[$counter]['annoline'] = htmlspecialchars("$line\n");
        $counter++;
    }
    
    // Return data to BL compiler
    $data['pageinfo']=xarML("Annotated listing of #(1)@#(2)",$file,$rev);
    $data['repoid']=$repoid;
    $data['name_value']=$item['reponame'];
    $data['annolines']=$annolines;
    return $data;
}

?>