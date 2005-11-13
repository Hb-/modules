<?php
/**
 * File: $Id:
 * 
 * Update an sigmapersonnel item
 * 
 * @package Xaraya eXtensible Management System
 * @copyright (C) 2002-2005 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage Sigmapersonnel Module
 * @author Michel V. 
 */
/**
 * update an sigmapersonnel item
 * 
 * @author the Michel V. 
 * @param  $args ['exid'] the ID of the item
 * @param  $args ['name'] the new name of the item
 * @param  $args ['number'] the new number of the item
 * @raise BAD_PARAM, NO_PERMISSION, DATABASE_ERROR
 */
function sigmapersonnel_adminapi_update($args)
{ 
    // Get arguments from argument array - all arguments to this function
    // should be obtained from the $args array, getting them from other
    // places such as the environment is not allowed, as that makes
    // assumptions that will not hold in future versions of Xaraya
    extract($args); 
    // Argument check - make sure that all required arguments are present
    // and in the right format, if not then set an appropriate error
    // message and return
    // Note : since we have several arguments we want to check here, we'll
    // report all those that are invalid at the same time...
    $invalid = array();
    if (!isset($exid) || !is_numeric($exid)) {
        $invalid[] = 'item ID';
    } 
    if (!isset($name) || !is_string($name)) {
        $invalid[] = 'name';
    } 
    if (!isset($number) || !is_numeric($number)) {
        $invalid[] = 'number';
    } 
    if (count($invalid) > 0) {
        $msg = xarML('Invalid #(1) for #(2) function #(3)() in module #(4)',
            join(', ', $invalid), 'admin', 'update', 'Example');
        xarErrorSet(XAR_SYSTEM_EXCEPTION, 'BAD_PARAM',
            new SystemException($msg));
        return;
    } 
    // The user API function is called.  This takes the item ID which
    // we obtained from the input and gets us the information on the
    // appropriate item.  If the item does not exist we post an appropriate
    // message and return
    $item = xarModAPIFunc('sigmapersonnel',
        'user',
        'get',
        array('exid' => $exid)); 
    // Check for exceptions
    if (!isset($item) && xarCurrentErrorType() != XAR_NO_EXCEPTION) return; // throw back
     
    // Security check - important to do this as early on as possible to
    // avoid potential security holes or just too much wasted processing.
    // However, in this case we had to wait until we could obtain the item
    // name to complete the instance information so this is the first
    // chance we get to do the check
    // Note that at this stage we have two sets of item information, the
    // pre-modification and the post-modification.  We need to check against
    // both of these to ensure that whoever is doing the modification has
    // suitable permissions to edit the item otherwise people can potentially
    // edit areas to which they do not have suitable access
    if (!xarSecurityCheck('EditExample', 1, 'Item', "$item[name]:All:$exid")) {
        return;
    } 
    if (!xarSecurityCheck('EditExample', 1, 'Item', "$name:All:$exid")) {
        return;
    } 
    // Get database setup - note that both xarDBGetConn() and xarDBGetTables()
    // return arrays but we handle them differently.  For xarDBGetConn()
    // we currently just want the first item, which is the official
    // database handle.  For xarDBGetTables() we want to keep the entire
    // tables array together for easy reference later on
    $dbconn = xarDBGetConn();
    $xartable = xarDBGetTables(); 
    // It's good practice to name the table and column definitions you
    // are getting - $table and $column don't cut it in more complex
    // modules
    $sigmapersonneltable = $xartable['sigmapersonnel']; 
    // Update the item - the formatting here is not mandatory, but it does
    // make the SQL statement relatively easy to read.  Also, separating
    // out the sql statement from the Execute() command allows for simpler
    // debug operation if it is ever needed
    $query = "UPDATE $sigmapersonneltable
            SET xar_name =?, xar_number = ?
            WHERE xar_exid = ?";
    $bindvars = array($name, $number, $exid);
    $result = &$dbconn->Execute($query,$bindvars); 
    // Check for an error with the database code, adodb has already raised
    // the exception so we just return
    if (!$result) return; 
    // Let any hooks know that we have updated an item.  As this is an
    // update hook we're passing the updated $item array as the extra info
    $item['module'] = 'sigmapersonnel';
    $item['itemid'] = $exid;
    $item['name'] = $name;
    $item['number'] = $number;
    xarModCallHooks('item', 'update', $exid, $item); 
    // Let the calling process know that we have finished successfully
    return true;
} 

?>
