<?php
/**
* File: $Id: modifycategories.php,v 1.3 2005/04/01 12:15:16 michelv01 Exp $
*
* This function manages the calendar categories.
*
* @package Xaraya eXtensible Management System
* @copyright (C) 2004 by Metrostat Technologies, Inc.
* @license GPL {@link http://www.gnu.org/licenses/gpl.html}
* @link http://www.metrostat.net
*
* @subpackage julian
* initial template: Roger Raymond
* @author Jodie Razdrh/John Kevlin/David St.Clair/Michel Vorenhout/Jorn Bruggeman
*/

function julian_admin_modifycategories()
{
    // Security Check
    if (!xarSecurityCheck('Adminjulian')) return;
    
    //get post/get vars
    if (!xarVarFetch('cal_date','int:0:8',$cal_date,date("Ymd"))) return;
    if (!xarVarFetch('color','str',$color,'')) return;

    if (!xarVarFetch('editaction','str',$editaction,'')) return;
    if (!xarVarFetch('addaction','str',$addaction,'')) return;
    if (!xarVarFetch('action','str',$action,'')) return;
    if (!xarVarFetch('cid','str',$cid,'')) return;
    if (strcmp($addaction,"Add")==0) {
        // If we are adding a new category-to-properties link, the category_id comes in
        // the format specified by categories_visual_makeselect. Convert this.
        xarVarFetch('new_cids', 'list:int:1:', $cids, NULL, XARVAR_NOT_REQUIRED);
        if (empty($cids) || !is_array($cids) || strcmp($cids[0],'')==0) {
          $msg = xarML('No valid category specified.');
          xarErrorSet(XAR_USER_EXCEPTION, 'BAD_PARAM', $msg);
          return;
        }
        $cid = $cids[0];
     }
    $data['edit_cond'] = $cid;
    
    // establish a db connection
    $dbconn =& xarDBGetConn();
    //get db tables
    $xartable = xarDBGetTables();
    $category_properties_table = $xartable['julian_category_properties'];

    //add and add cancel actions
    if (!strcmp($addaction,"Add")) {
        $query = "SELECT * FROM `$category_properties_table` WHERE `cid`='$cid'";
        $result = $dbconn->Execute($query);
        if (!$result->EOF) {
            // This category was already linked to properties. Throw an error.
            $msg = xarML('The category you specified has already a set of properties linked to it. You cannot add another set of properties (but you can edit the existing one).');
            xarErrorSet(XAR_USER_EXCEPTION, 'BAD_PARAM', $msg);
            return;
        }
    
        $query = "INSERT INTO " . $category_properties_table . " (`cid`,`color`) VALUES (?,?)";
        $bindvars = array($cid,$color);
        $result = $dbconn->Execute($query, $bindvars);
        xarResponseRedirect(xarModURL('julian', 'admin', 'modifycategories'));
    }

    else if (!strcmp($addaction,"Cancel"))
    {
        $back_link=xarSessionGetVar('lastview');
        xarResponseRedirect($back_link);
    }
    
    else if (!strcmp($action,"Delete"))
    {
        $query = "DELETE FROM ".$category_properties_table." WHERE `cid` = '".$cid."'";
        $result = $dbconn->Execute($query);
        xarResponseRedirect(xarModURL('julian', 'admin', 'modifycategories'));
    }    
    //Cancel and Modify edit actions
    else if (!strcmp($editaction,"Cancel"))
    {
        $data['edit_cond'] = '';
        xarResponseRedirect(xarModURL('julian', 'admin', 'modifycategories'));
    }
    else if (!strcmp($editaction,"Modify")){
        $query = "UPDATE `$category_properties_table` SET `color` = ? WHERE `cid` = '$cid'";
        $bindvars = array($color);
        $result = $dbconn->Execute($query, $bindvars);
        $data['edit_cond'] = '';
        xarResponseRedirect(xarModURL('julian', 'admin', 'modifycategories'));
    }
    
    // get categories
    $categories = xarModAPIFunc('julian','user','getcategories');
    $js_names  = '';
    $js_colors = '';
    foreach ($categories as $cid => $info) {
        $js_names .= '"'.$info['name'].'",';
        $js_colors .= '"'.$info['color'].'",';
    }
    $data['BulletForm'] = '&'.xarModGetVar('julian', 'BulletForm').';';
    $data['js_names_array'] = "var Names = new Array(".substr($js_names,0,-1).")\n";
    $data['js_colors_array'] = "var Colors = new Array(".substr($js_colors,0,-1).")\n";
    $data['categories'] = $categories;
    $data['cal_date'] = $cal_date;
    $data['category_select'] = xarModAPIFunc('categories', 'visual', 'makeselect',
                                                    array('cid' => explode(';',xarModGetVar('julian','mastercids')),
                                                            'multiple' => 0,
                                                            'name_prefix' => 'new_',
                                                            'return_itself' => true,
                                                            'select_itself' => true));
//                                                            'values' => &$seencid));
    
    return $data;
}
?>
