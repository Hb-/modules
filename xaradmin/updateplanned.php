<?php
/**
 * Standard function to update a current course
 * 
 * @package Xaraya eXtensible Management System
 * @copyright (C) 2005 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage courses
 * @author Courses module development team 
 */
/**
 * This is a standard function that is called with the results of the
 * form supplied by xarModFunc('courses','admin','modifycourse') to update a current item
 * 
 * @param  $ 'planningid' the id of the course to be updated
 * @param  $ 'name' the name of the course to be updated
 * @param  $ 'number' the number of the course to be updated
 */
function courses_admin_updateplanned($args)
{
    extract($args);

    // Get parameters from whatever input we need.
    if (!xarVarFetch('planningid', 'id', $planningid)) return;
    if (!xarVarFetch('objectid', 'id', $objectid, '', XARVAR_NOT_REQUIRED )) return;
    if (!xarVarFetch('name', 'str:1:', $name, '', XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('number', 'str:1:', $number, '',XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('coursetype', 'str:1:', $coursetype, '', XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('level', 'int:1:', $level, '', XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('year', 'int:1:', $year, '', XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('credits', 'int::', $credits, '', XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('creditsmin', 'int::', $creditsmin, '', XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('creditsmax', 'int::', $creditsmax, '', XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('longdesc', 'str:1:', $longdesc, '', XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('prerequisites', 'str:1:', $prerequisites, '', XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('program', 'str:1:', $program, '', XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('committee', 'str:1:', $committee, '', XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('coordinators', 'str:1:', $coordinators, '', XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('lecturers', 'str:1:', $lecturers, '', XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('aim', 'str:1:', $aim, '', XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('method', 'str:1:', $method, '', XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('language', 'str:1:', $language, '', XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('location', 'str:1:', $location, '', XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('costs', 'str:1:', $costs, '', XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('material', 'str:1:', $material, '', XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('startdate', 'str:1:', $startdate, '', XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('enddate', 'str:1:', $enddate, '', XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('info', 'str:1:', $info, '', XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('invalid', 'array::', $invalid, '', XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('minparticipants', 'int::', $minparticipants, '', XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('maxparticipants', 'int::', $maxparticipants, '', XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('closedate', 'str::', $closedate, '', XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('hideplanning', 'int:1:', $hideplanning, '', XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('last_modified', 'str::', $last_modified, '', XARVAR_NOT_REQUIRED)) return;
    // At this stage we check to see if we have been passed $objectid, the
    // generic item identifier.
    if (!empty($objectid)) {
        $planningid = $objectid;
    }

    // Confirm authorisation code.
    if (!xarSecConfirmAuthKey()) return;
    // We don't make an invalid here... so why need it?
    $invalid = array();
    // Check requirements
    if (isset($minparticipants) || isset($maxparticipants)) {
        if ($minparticipants > $maxparticipants) {
          $invalid['minparticipants'] = $minparticipants;
          }
    }
    
    if (empty($nameid)) {
        $data['name'] = '';
    } else {
        $data['name'] = $name;
    }

    if (empty($number)) {
        $data['number'] = '';
    } else {
        $data['number'] = $number;
    }

     if (empty($coursetype)) {
        $data['coursetype'] = '';
    } else {
        $data['coursetype'] = $coursetype;
    }

     if (empty($level)) {
        $data['level'] = '';
    } else {
        $data['level'] = $level;
    }

     if (empty($year)) {
        $data['year'] = '';
    } else {
        $data['year'] = $year;
    }

    if (empty($credits)) {
        $data['credits'] = '';
    } else {
        $data['credits'] = $credits;
    }
    if (empty($creditsmin)) {
        $data['creditsmin'] = '';
    } else {
        $data['creditsmin'] = $creditsmin;
    }
    if (empty($contact)) {
        $data['contact'] = '';
    } else {
        $data['contact'] = $contact;
    }
    if (empty($hideplanning)) {
        $data['hideplanning'] = '';
    } else {
        $data['hideplanning'] = $hideplanning;
    }

    // check if we have any errors
    if (count($invalid) > 0) {
        // call the admin_modifycourse function and return the template vars
        // (you need to copy admin-new.xd to admin-create.xd here)
        return xarModFunc('courses', 'admin', 'modifyplanned',
                          array('name' => $name,
                                'number' => $number,
                                'year' => $year,
                                'credits' => $credits,
                                'creditdsmin' => $creditsmin,
                                'creditsmax' => $creditsmax,
                                'startdate' => $startdate,
                                'enddate' => $enddate,
                                'prerequisites' => $prerequisites,
                                'aim' => $aim,
                                'method' => $method,
                                'longdesc' => $longdesc,
                                'costs' => $costs,
                                'committee' => $committee,
                                'coordinators' => $coordinators,
                                'lecturers' => $lecturers,
                                'location' => $location,
                                'material' => $material,
                                'info' => $info,
                                'program' => $program,
                                'minparticipants' => $minparticipants, 
                                'maxparticipants' => $maxparticipants,
                                'closedate' => $closedate,
                                'hideplanning' => $hideplanning,
                                'last_modified' => $last_modified,
                                'invalid' => $invalid));
    }
    $last_modified = '';
    $last_modified = date("Y-m-d H:i:s");
    // The API function is called.
    if (!xarModAPIFunc('courses',
                       'admin',
                       'updateplanned',
                       array(   'name' => $name,
                                'number' => $number,
                                'year' => $year,
                                'credits' => $credits,
                                'creditdsmin' => $creditsmin,
                                'creditsmax' => $creditsmax,
                                'startdate' => $startdate,
                                'enddate' => $enddate,
                                'prerequisites' => $prerequisites,
                                'aim' => $aim,
                                'method' => $method,
                                'longdesc' => $longdesc,
                                'costs' => $costs,
                                'committee' => $committee,
                                'coordinators' => $coordinators,
                                'lecturers' => $lecturers,
                                'location' => $location,
                                'material' => $material,
                                'info' => $info,
                                'program' => $program,
                                'minparticipants' => $minparticipants, 
                                'maxparticipants' => $maxparticipants,
                                'closedate' => $closedate,
                                'last_modified' => $last_modified,
                                'hideplanning' => $hideplanning))) {
        return; // throw back
    } 
    xarSessionSetVar('statusmsg', xarML('Planned Course Was Successfully Updated!'));
    // This function generated no output, and so now it is complete we redirect
    // the user to an appropriate page for them to carry on their work
    xarResponseRedirect(xarModURL('courses', 'admin', 'viewallplanned'));
    // Return
    return true;
}

?>
