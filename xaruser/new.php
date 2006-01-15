<?php
/**
 * Add new item ITSP
 *
 * @package modules
 * @copyright (C) 2002-2006 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage ITSP Module
 * @link http://xaraya.com/index.php/release/572.html
 * @author ITSP Module Development Team
 */
/**
 * Add new ITSP
 *
 * This is a standard function that is called whenever an user
 * wishes to create a new module item
 *
 * @author ITSP module development team
 * @return array
 */
function itsp_user_new($args)
{
    extract($args);

    /* Get parameters from whatever input we need. All arguments to this
     */
    if (!xarVarFetch('userid',        'int:1:', $userid,        $userid,  XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('planid',        'int:1:', $planid,        $planid,  XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('itspstatus',    'str:1:', $itspstatus,    $itspstatus,    XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('datesubm',      'int:1:', $datesubm,      $datesubm,  XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('dateappr',      'int:1:', $dateappr,      $dateappr,  XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('datecertreq',   'int:1:', $datecertreq,   $datecertreq,  XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('datecertaward', 'int:1:', $datecertaward, $datecertaward,  XARVAR_NOT_REQUIRED)) return;

    if (!xarVarFetch('invalid', 'array',  $invalid, $invalid, XARVAR_NOT_REQUIRED)) return;
    /* Initialise the $data variable that will hold the data to be used in*/
    $data = xarModAPIFunc('itsp', 'user', 'menu');
    /* Security check - important to do this as early as possible to avoid
     * potential security holes or just too much wasted processing
     */
    if (!xarSecurityCheck('AddITSP')) return;

    /* Generate a one-time authorisation code for this operation */
    $data['authid'] = xarSecGenAuthKey();

    $plans = xarModApiFunc('itsp','user','getall_plans', array('enddate' => time()));
    $data['plans'] = $plans;

    $item = array();
    $item['module'] = 'itsp';
    $item['itemtype'] = 2;
    $hooks = xarModCallHooks('item', 'new', '', $item);

    if (empty($hooks)) {
        $data['hookoutput'] = array();
    } else {
        /* You can use the output from individual hooks in your template too, e.g. with
         * $hookoutput['categories'], $hookoutput['dynamicdata'], $hookoutput['keywords'] etc.
         */
        $data['hookoutput'] = $hooks;
    }
    $data['hooks'] = '';
    /* For E_ALL purposes, we need to check to make sure the vars are set.
     */
    if (empty($userid)) {
        $data['userid'] = '';
    } else {
        $data['userid'] = $userid;
    }

    if (empty($planid)) {
        $data['planid'] = '';
    } else {
        $data['planid'] = $planid;
    }

    if (empty($itspstatus)) {
        $data['itspstatus'] = '';
    } else {
        $data['itspstatus'] = $itspstatus;
    }
    if (empty($datesubm)) {
        $data['datesubm'] = '';
    } else {
        $data['datesubm'] = $datesubm;
    }
    if (empty($dateappr)) {
        $data['dateappr'] = '';
    } else {
        $data['dateappr'] = $dateappr;
    }
    if (empty($datecertreq)) {
        $data['datecertreq'] = '';
    } else {
        $data['datecertreq'] = $datecertreq;
    }
    if (empty($datecertaward)) {
        $data['datecertaward'] = '';
    } else {
        $data['datecertaward'] = $datecertaward;
    }

    /* Return the template variables defined in this function */
    return $data;
}
?>