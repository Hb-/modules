<?php

/**
 * get all example items
 *
 * @author the Example module development team
 * @param numitems the number of items to retrieve (default -1 = all)
 * @param startnum start with this item number (default 1)
 * @returns array
 * @return array of items, or false on failure
 * @raise BAD_PARAM, DATABASE_ERROR, NO_PERMISSION
 */
function contact_userapi_getcompany($args)
{
    // Get arguments from argument array - all arguments to this function
    // should be obtained from the $args array, getting them from other places
    // such as the environment is not allowed, as that makes assumptions that
    // will not hold in future versions of Xaraya
    extract($args);

    // Optional arguments.
    // FIXME: (!isset($startnum)) was ignoring $startnum as it contained a null value
    // replaced it with ($startnum == "") (thanks for the talk through Jim S.) NukeGeek 9/3/02
    //if (!isset($startnum)) {
    if ($startnum == "") {
        $startnum = 1;
    }
    if (!isset($numitems)) {
        $numitems = -1;
    }

    // Argument check - make sure that all required arguments are present and
    // in the right format, if not then set an appropriate error message
    // and return
    // Note : since we have several arguments we want to check here, we'll
    // report all those that are invalid at the same time...
    $invalid = array();
    if (!isset($startnum) || !is_numeric($startnum)) {
        $invalid[] = 'startnum';
    }
    if (!isset($numitems) || !is_numeric($numitems)) {
        $invalid[] = 'numitems';
    }
    if (count($invalid) > 0) {
        $msg = xarML('Invalid #(1) for #(2) function #(3)() in module #(4)',
                    join(', ',$invalid), 'admin', 'getcompany', 'contact');
        xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'BAD_PARAM',
                       new SystemException($msg));
        return;
    }

    $comp = array();

    // Security check - important to do this as early on as possible to
    // avoid potential security holes or just too much wasted processing
  //  if (!xarSecAuthAction(0, 'contact::', '::', ACCESS_OVERVIEW)) {
  //      xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'NO_PERMISSION');
 //       return;
 //   }
     if (!xarSecurityCheck('ContactRead')) return;

    // Get database setup - note that both xarDBGetConn() and xarDBGetTables()
    // return arrays but we handle them differently.  For xarDBGetConn() we
    // currently just want the first item, which is the official database
    // handle.  For xarDBGetTables() we want to keep the entire tables array
    // together for easy reference later on
    list($dbconn) = xarDBGetConn();
    $xartable = xarDBGetTables();

    // It's good practice to name the table and column definitions you are
    // getting - $table and $column don't cut it in more complex modules
    $contacttable = $xartable['contact_company'];

// TODO: how to select by cat ids (automatically) when needed ???

    // Get items - the formatting here is not mandatory, but it does make the
    // SQL statement relatively easy to read.  Also, separating out the sql
    // statement from the SelectLimit() command allows for simpler debug
    // operation if it is ever needed
    $query = "SELECT xar_id,
                   xar_name,
                   xar_address,
                   xar_address2,
                   xar_city,
                   xar_state,
                   xar_zip,
                   xar_country,
                   xar_phone,
                   xar_fax,
                   xar_mail,
                   xar_logo
                   FROM $contacttable
            ORDER BY xar_name";
    $result = $dbconn->SelectLimit($query, $numitems, $startnum-1);

    // Check for an error with the database code, adodb has already raised
    // the exception so we just return
    if (!$result) return;

    // Put items into result array.  Note that each item is checked
    // individually to ensure that the user is allowed *at least* OVERVIEW
    // access to it before it is added to the results array.
    // If more severe restrictions apply, e.g. for READ access to display
    // the details of the item, this *must* be verified by your function.
    for (; !$result->EOF; $result->MoveNext()) {
        list($id, $name, $address, $address2, $city, $state, $zip, $country, $phone, $fax, $mail, $companylogo) = $result->fields;
        if (xarSecurityCheck('ContactRead',0,'item',"$id:All:All")) {
            $items[] = array('id' => $id,
                             'name' => $name,
                             'address' => $address,
                             'address2' => $address2,
                             'city' => $city,
                             'state' => $state,
                             'zip' => $zip,
                             'country' => $country,
                             'phone' => $phone,
                             'fax' => $fax,
                             'mail' => $mail,
                             'companylogo' => $companylogo);

        }
    }

    // All successful database queries produce a result set, and that result
    // set should be closed when it has been finished with
    $result->Close();

    // Return the items
    return $items;
}

?>