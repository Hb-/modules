<?php
 /**
 * Enroll student in course
 *
 * @package Xaraya eXtensible Management System
 * @copyright (C) 2002-2005 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage courses
 */

/**
 * Combine a teacher (Xar user) with a planned course and update database
 * @author Michel V.
 *
 * @param  $args an array of arguments (if called by other modules)
 * @param  $args an array of arguments (if called by other modules)
 * @param  $args ['userid'] the uid of the role to be treated as a teacher
 * @param  $args ['planningid'] the planned course ID that the user will enroll to
 */
function courses_admin_newteacher($args)
{

 extract($args);

  if (!xarVarFetch('planningid', 'id', $planningid, NULL, XARVAR_DONT_SET)) return;
  if (!xarVarFetch('userid',     'int::', $userid, NULL, XARVAR_DONT_SET)) return;
  // if (!xarVarFetch('extpid',       'isset', $extpid,       NULL, XARVAR_DONT_SET)) {return;}
    // Check to see if this user is already enrolled in this course
    $check = xarModAPIFunc('courses',
                           'admin',
                           'check_teacher',
                           array('userid' => $userid,
                                 'planningid' => $planningid));

    //if (!isset($courses) && xarCurrentErrorType() != XAR_NO_EXCEPTION) return; // throw back
    
    // Check if this teacher is already a teacher
    if (count($check)!=0) {
    $msg = xarML('This teacher has already been assigned to this course');
        xarErrorSet(XAR_USER_EXCEPTION, 'ALREADY_TEACHER',
            new SystemException(__FILE__ . '(' . __LINE__ . '): ' . $msg));
        return;
    }

        $item = xarModAPIFunc('courses',
        'user',
        'getplanned',
        array('planningid' => $planningid));
    if (!isset($item) && xarCurrentErrorType() != XAR_NO_EXCEPTION) return; // throw back
    
    // If user is not enrolled already go ahead and create the enrollment
    // Get status of student
    $type = 1;
    $tid = xarModAPIFunc('courses',
                          'admin',
                          'create_teacher',
                          array('userid'     => $userid,
                                'planningid' => $planningid,
                                'type' => $type));

    if (!isset($tid) && xarCurrentErrorType() != XAR_NO_EXCEPTION) return; // throw back
/*
    // Register an EDIT privilege for the newborn teacher
    // define the new instance
    $newinstance = array();
    $newinstance[] = empty($planningid) ? 'All' : $planningid;
    $newinstance[] = empty($uid) ? 'All' : $uid;
    $newinstance[] = empty($courseid) ? 'All' : $courseid;
    $extname = 'EditPlanning';
    $extrealm = 'All';
    $extmodule = 'courses';
    $extcomponent = 'Planning';
    $extlevel = '500';
    if (!empty($planningid)) {
        // create/update the privilege
        $pid = xarReturnPrivilege($extpid,$extname,$extrealm,$extmodule,$extcomponent,$newinstance,$extlevel);
        if (empty($pid)) {
            return;  // throw back
        }
    }
*/
    // This function generated no output, and so now it is complete we redirect
    // the user to an appropriate page for them to carry on their work
    xarResponseRedirect(xarModURL('courses', 'admin', 'teachers', array('planningid' => $planningid)));
    // Return
    return true;

}
?>
