<?php
/**
 * File: $Id:
 * 
 * Standard function to create a new module item
 * 
 * @package Xaraya eXtensible Management System
 * @copyright (C) 2002-2005 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage maxercalls
 * @author maxercalls module development team 
 */
/**
 * add new item
 * This is a standard function that is called whenever an administrator
 * wishes to create a new module item
 */
function maxercalls_user_new($args)
{ 
    extract($args);

    // Get parameters from whatever input we need.
	if (!xarVarFetch('owner', 'int:1:', $owner, $owner, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('calldate', 'str:1:', $calldate, $calldate, XARVAR_NOT_REQUIRED)) return;
	if (!xarVarFetch('calltime', 'str:1:', $calltime, $calltime, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('callhours','int::',$callhours,$callhours, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('callminutes','int::',$callminutes,$callminutes, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('callday', 'int:1:', $callday,$callday, XARVAR_NOT_REQUIRED)) return; 
    if (!xarVarFetch('callmonth', 'str:1:', $callmonth,$callmonth, XARVAR_NOT_REQUIRED)) return; 
    if (!xarVarFetch('callyear', 'int:1:', $callyear,$callyear, XARVAR_NOT_REQUIRED)) return; 
	if (!xarVarFetch('remarks', 'str:1:', $remarks, $remarks, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('invalid', 'str:1:', $invalid, $invalid, XARVAR_NOT_REQUIRED)) return; 
	
    // Initialise the $data variable that will hold the data to be used in
    // the blocklayout template, and get the common menu configuration - it
    // helps if all of the module pages have a standard menu at the top to
    // support easy navigation
    $data = xarModAPIFunc('maxercalls', 'user', 'menu'); 
    // Security check - important to do this as early as possible to avoid
    // potential security holes or just too much wasted processing
    if (!xarSecurityCheck('Addmaxercalls')) return; 
    // Generate a one-time authorisation code for this operation
    $data['authid'] = xarSecGenAuthKey();
    $data['invalid'] = $invalid; 
	
	// Specify some labels for display
    $data['calldatelabel'] = xarVarPrepForDisplay(xarML('Date of call'));
    $data['calltimelabel'] = xarVarPrepForDisplay(xarML('Time of call'));
    $data['calltextlabel'] = xarVarPrepForDisplay(xarML('Text of call'));
    $data['remarkslabel'] = xarVarPrepForDisplay(xarML('Other remarks'));
    $data['addbutton'] = xarVarPrepForDisplay(xarML('Add Maxercall'));
    $data['ownerlabel'] = xarVarPrepForDisplay(xarML('Owner of maxer'));
    $data['calltext'] = xarModAPIFunc('maxercalls', 'user', 'gets',
                                     array('itemtype' => 3));

	
	//Calendar option data
	$data['todays_month'] = date("n");
	$data['todays_year'] = date("Y");
	$data['todays_day'] = date("d");  
	//Call hooks and tell them of new item
    $item = array();
    $item['module'] = 'maxercalls';
    $hooks = xarModCallHooks('item', 'new', '', $item);

    if (empty($hooks)) {
        $data['hookoutput'] = array();
    } else {
        /* You can use the output from individual hooks in your template too, e.g. with
         * $hookoutput['categories'], $hookoutput['dynamicdata'], $hookoutput['keywords'] etc.
         */
        $data['hookoutput'] = $hooks;
    }
	
    // For E_ALL purposes, we need to check to make sure the vars are set.
    // If they are not set, then we need to set them empty to surpress errors
    if (empty($calldate)) {
        $data['calldate'] = '';
    } else {
        $data['calldate'] = $calldate;
    } 
    if (empty($calltime)) {
        $data['calltime'] = '';
    } else {
        $data['calltime'] = $calltime;
    } 
    if (empty($remarks)) {
        $data['remarks'] = '';
    } else {
        $data['remarks'] = $remarks;
    } 
    if (empty($owner)) {
        $data['owner'] = '';
    } else {
        $data['owner'] = $owner;
    } 
	
    // Return the template variables defined in this function
    return $data;
} 

?>
