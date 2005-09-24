<?php
/**
 * @package Xaraya eXtensible Management System
 * @copyright (C) 2005 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage Sitetools
 * @author Jo Dalle Nogare <jojodee@xaraya.com>
 */
/**
 * Return SiteTools table names to xaraya
 *
 * This function is called internally by the core whenever the module is
 * loaded.  It is loaded by xarMod__loadDbInfo().
 * 
 * @access private 
 * @return array 
 */
function sitetools_xartables()
{ 
    /* Initialise table array */
    $xarTables = array(); 
    /* Get the name for the example item table.  This is not necessary
     * but helps in the following statements and keeps them readable
     */
    $sitetoolsTable = xarDBGetSiteTablePrefix() . '_sitetools';
    /* Set the table name */
    $xarTables['sitetools'] = $sitetoolsTable;

    $xarTables['sitetools_links'] = xarDBGetSiteTablePrefix() . '_sitetools_links';
    /* Return the table information */
    return $xarTables;
}

?>