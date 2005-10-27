<?php
/**
 * Xaraya HTML Module
 *
 * @package modules
 * @copyright (C) 2002-2005 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage HTML Module
 * @link http://xaraya.com/index.php/release/779.html
 * @author John Cox
 */

/**
 * Get all tags
 *
 * @public
 * @author Richard Cave 
 * @param none 
 * @returns array
 * @return array of HTML tags, or false on failure
 * @raise none
 */
function html_userapi_getalltypes($args)
{
    // Extract arguments
    extract($args);

    // Set empty array
    $types = array();

    // Security Check
    if (!xarSecurityCheck('ReadHTML')) {
        return $types;
    }

    // Get database setup
    $dbconn =& xarDBGetConn();
    $xartable =& xarDBGetTables();

    // Set table names
    $htmltypestable = $xartable['htmltypes'];

    // Get HTML tags
    $query = "SELECT $htmltypestable.xar_id,
                     $htmltypestable.xar_type
              FROM $htmltypestable
              ORDER BY $htmltypestable.xar_type";

    $result =& $dbconn->Execute($query);

    // Check for an error
    if (!$result) return;

    // Put types into an array
    for (; !$result->EOF; $result->MoveNext()) {
        list($id, $type) = $result->fields;

         $types[] = array('id'        => $id,
                          'type'      => $type);
    }

    // Close result set
    $result->Close();

    return $types;
}

?>
