<?php
/**
 * Add new planitem
 *
 * @package modules
 * @copyright (C) 2005-2006 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage ITSP Module
 * @link http://xaraya.com/index.php/release/572.html
 * @author ITSP Module Development Team
 */
/**
 * Add a new planitem
 *
 * This is a standard function that is called whenever an administrator
 * wishes to create a new module item
 *
 * @author ITSP module development team
 * @return array
 */
function itsp_admin_new_pitem($args)
{
    extract($args);


    // Get parameters from whatever input we need.
    if (!xarVarFetch('pitemname',   'str:1:', $pitemname,   $pitemname,  XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('pitemdesc',   'str:1:', $pitemdesc,   $pitemdesc,  XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('pitemrules',  'str:1:', $pitemrules,  $pitemrules, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('credits',    'int:1:', $credits,    $credits,   XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('mincredit',  'int:1:', $mincredit,  $mincredit, XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('dateopen',   'int:1:', $dateopen,   $dateopen,  XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('dateclose',  'int:1:', $dateclose,  $dateclose, XARVAR_NOT_REQUIRED)) return;

    if (!xarVarFetch('rule_cat',    'int:1:', $rule_cat,    $rule_cat,   XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('rule_type',   'str:1:', $rule_type,    $rule_type,   XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('rule_source', 'enum:internal:external:open', $rule_source,    $rule_source,   XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('rule_level',   'int:1:', $rule_level,    $rule_level,   XARVAR_NOT_REQUIRED)) return;

    if (!xarVarFetch('invalid', 'array',  $invalid, $invalid, XARVAR_NOT_REQUIRED)) return;
    // Add the admin menu
    $data = xarModAPIFunc('itsp', 'admin', 'menu');
    /* Security check - important to do this as early as possible to avoid
     * potential security holes or just too much wasted processing
     */
    if (!xarSecurityCheck('AddITSPPlan')) return;
    // get the levels in courses
    $data['levels'] = xarModAPIFunc('courses', 'user', 'gets',
                                      array('itemtype' => 3));


    /* Generate a one-time authorisation code for this operation */
    $data['authid'] = xarSecGenAuthKey();
    $data['invalid'] = $invalid;

    $item = array();
    $item['module'] = 'itsp';
    $item['itemtype'] = 3;
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
     * If they are not set, then we need to set them empty to surpress errors
     */
    if (empty($pitemname)) {
        $data['pitemname'] = '';
    } else {
        $data['pitemname'] = $pitemname;
    }

    if (empty($pitemdesc)) {
        $data['pitemdesc'] = '';
    } else {
        $data['pitemdesc'] = $pitemdesc;
    }
    if (empty($pitemrules)) {
        $data['pitemrules'] = '';
    } else {
        $data['pitemrules'] = $pitemrules;
    }
    if (empty($credits)) {
        $data['credits'] = '';
    } else {
        $data['credits'] = $credits;
    }
    if (empty($mincredit)) {
        $data['mincredit'] = '';
    } else {
        $data['mincredit'] = $mincredit;
    }
    if (empty($dateopen)) {
        $data['dateopen'] = '';
    } else {
        $data['dateopen'] = $dateopen;
    }
    if (empty($dateclose)) {
        $data['dateclose'] = '';
    } else {
        $data['dateclose'] = $dateclose;
    }

    if (empty($rule_type)) {
        $data['rule_type'] = '';
    } else {
        $data['rule_type'] = $rule_type;
    }
    if (empty($rule_source)) {
        $data['rule_source'] = '';
    } else {
        $data['rule_source'] = $rule_source;
    }
    if (empty($rule_level)) {
        $data['rule_level'] = '';
    } else {
        $data['rule_level'] = $rule_level;
    }
    if (empty($rule_cat)) {
        $data['rule_cat'] = '';
    } else {
        $data['rule_cat'] = $rule_cat;
    }

    /* Return the template variables defined in this function */
    return $data;
}
?>