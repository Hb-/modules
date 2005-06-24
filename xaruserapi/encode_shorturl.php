<?php
//$Id: encode_shorturl.php,v 1.3 2005/06/24 11:26:01 michelv01 Exp $

function julian_userapi_encode_shorturl($args) 
{
    // Get arguments from argument array
    extract($args); unset($args);
    // check if we have something to work with
    if (!isset($func)) { return; }
    
    // default path is empty -> no short URL
    $path = '';
    $extra = '';
    // we can't rely on xarModGetName() here (yet) !
    $module = 'julian';
    
    // specify some short URLs relevant to your module
    switch($func) {
        case 'main':
            // replace this with the default view when available
            // right now we'll just default to the month view
            $path = "/$module/month/";
            if(isset($cal_date) && !empty($cal_date)) $path .= xarVarPrepForDisplay($cal_date).'/';
            if(isset($cal_user) && !empty($cal_user)) $path .= xarVarPrepForDisplay($cal_user).'/';
            $path .= 'index.html';
            break;
        
        case 'day':
            $path = "/$module/day/";
            if(isset($cal_date) && !empty($cal_date)) $path .= xarVarPrepForDisplay($cal_date).'/';
            if(isset($cal_user) && !empty($cal_user)) $path .= xarVarPrepForDisplay($cal_user).'/';
            $path .= 'index.html';
            break;
            
        case 'week':
            $path = "/$module/week/";
            if(isset($cal_date) && !empty($cal_date)) $path .= xarVarPrepForDisplay($cal_date).'/';
            if(isset($cal_user) && !empty($cal_user)) $path .= xarVarPrepForDisplay($cal_user).'/';
            $path .= 'index.html';
            break;
            
        case 'month':
            $path = "/$module/month/";
            if(isset($cal_date) && !empty($cal_date)) $path .= xarVarPrepForDisplay($cal_date).'/';
            if(isset($cal_user) && !empty($cal_user)) $path .= xarVarPrepForDisplay($cal_user).'/';
            $path .= 'index.html';
            break;
            
        case 'year':
            $path = "/$module/year/";
            if(isset($cal_date) && !empty($cal_date)) $path .= xarVarPrepForDisplay($cal_date).'/';
            if(isset($cal_user) && !empty($cal_user)) $path .= xarVarPrepForDisplay($cal_user).'/';
            $path .= 'index.html';
            break;
            
        case 'add':
            $path = "/$module/add/";
            if(isset($cal_date) && !empty($cal_date)) $path .= xarVarPrepForDisplay($cal_date).'/';
            $path .= 'index.html';
            break;
            
        case 'edit':
            $path = "/$module/edit/";
            if(isset($cal_eid) && !empty($cal_eid)) $path .= xarVarPrepForDisplay($cal_eid).'.html/';
            break;
            
        case 'view':
            $path = "/$module/view/";
            if(isset($cal_vid) && !empty($cal_vid)) $path .= xarVarPrepForDisplay($cal_vid).'/';
            break;
    }
    
    if(!empty($path) && isset($cal_sdow)) {
        $join = empty($extra) ? '?' : '&amp;';
        $extra .= $join . 'cal_sdow=' . $cal_sdow;
    }
    
    if(!empty($path) && isset($cal_category)) {
        $join = empty($extra) ? '?' : '&amp;';
        $extra .= $join . 'cal_category=' . $cal_category;
    }
    
    if(!empty($path) && isset($cal_topic)) {
        $join = empty($extra) ? '?' : '&amp;';
        $extra .= $join . 'cal_topic=' . $cal_topic;
    }
    
    return $path.$extra;

}

?>
