<?php
/**
  * Xaraya Referers
 * 
 * @package Xaraya eXtensible Management System
 * @copyright (C) 2002 by the Xaraya Development Team.
 * @license GPL <http://www.gnu.org/licenses/gpl.html>
 * @link http://www.xaraya.org
 * @subpackage Referer Module
 * @author John Cox et al. 
 */

function referer_xartables()
{ 
    // Initialise table array
    $xartable = array(); 
    $prefix = xarDBGetSiteTablePrefix();
    // Get the name for the autolinks item table
    $referertable = $prefix . '_referer'; 
    // Set the table name
    $xartable['referer'] = $referertable; 
    // Return the table information
    return $xartable;
} 

?>