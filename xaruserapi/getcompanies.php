<?php
/**
 * File: $Id: getcompanies.php,v 1.3 2004/01/24 18:36:22 garrett Exp $
 *
 * AddressBook userapi getCompanies
 *
 * @package Xaraya eXtensible Management System
 * @copyright (C) 2003 by the Xaraya Development Team
 * @license GPL <http://www.gnu.org/licenses/gpl.html>
 * @link http://www.xaraya.com
 *
 * @subpackage AddressBook Module
 * @author Garrett Hunter <garrett@blacktower.com>
 * Based on pnAddressBook by Thomas Smiatek <thomas@smiatek.com>
 */

/**
 * getCompanies
 *
 * @param N/A
 * @return array of companies
 */
function addressbook_userapi_getCompanies() 
{
    $dbconn =& xarDBGetConn();
    $xarTables =& xarDBGetTables();
    $adr_table = $xarTables['addressbook_address'];

    $sql = "SELECT DISTINCT company
            FROM $adr_table
            ORDER BY company";
    $result =& $dbconn->Execute($sql);

    if($dbconn->ErrorNo() != 0) { return array(); }
    if(!isset($result)) { return array(); }

    $companies[] = array('id'=>'0','name'=>xarML('Enter new company name or select a company...'));
    for($i=1; !$result->EOF; $result->MoveNext()) {
        list($company) = $result->fields;
        $companies[]     = array('id'=>$company,'name'=>$company);
     }
    $result->Close();
    return $companies;

} // END getCompanies

?>