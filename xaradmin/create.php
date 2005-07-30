<?php
/**
 *  Create an item of item type
 *
 * @param $itemtype - type of item that is being created (required)
 * @param $itemid - item id  (optional)
 * @param $preview  - do a preview if set (optional)
 * @return true on success
 *         false on failure
 */
function courses_admin_create($args)
{
    // check the authorisation key
    if (!xarSecConfirmAuthKey()) return; // throw back

    // Security Check
    if (!xarSecurityCheck('AdminCourses')) return;

    extract($args);

    // Get parameters from whatever input we need.
    if (!xarVarFetch('itemtype', 'int:1:', $itemtype, null, XARVAR_GET_OR_POST)) return;
    if (!xarVarFetch('itemid',   'int:1:', $itemid,'', XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('preview',  'isset',  $preview,  null, XARVAR_NOT_REQUIRED)) return;

    if (empty($itemtype)) {
        $msg = xarML('Invalid #(1) for #(2) function #(3)() in module #(4)',
                    'item type', 'admin', 'create', 'courses');
        xarErrorSet(XAR_USER_EXCEPTION, 'BAD_PARAM',
                        new SystemException($msg));
        return $msg;
    }

    // get the Dynamic Object defined for this module (and itemtype, if relevant)
    $object = &xarModAPIFunc('dynamicdata','user','getobject',
                             array('module'   => 'courses',
                                   'itemtype' => $itemtype
                                  )
                            );
    if (!isset($object)) return;  // throw back

    // check the input values for this object
    // TODO: is this e a correct way of dealing with a check?
    $isvalid = $object->checkInput();

    // if we're in preview mode, or if there is some invalid input, show the form again
    if (!empty($preview) || !$isvalid) {
     // Get ready for a preview
     // $data['menu']      = xarModFunc('courses','admin','menu');
        $data['menutitle'] = xarModAPIFunc('courses','admin','menu');
        $data['itemtype'] = $itemtype;
        $data['object'] =& $object;
        $data['preview'] = $preview;

        // Deal with the Hooks
        $item = array();
        $item['module'] = 'courses';
        $item['itemtype'] = $itemtype;
        $hooks = xarModCallHooks('item','new','',$item);
        if (empty($hooks)) {
            $data['hooks'] = '';
        } elseif (is_array($hooks)) {
            $data['hooks'] = join('',$hooks);
        } else {
            $data['hooks'] = $hooks;
        }

        // Return the template variables defined in this function
        return xarTplModule('courses','admin','new', $data);
    }

    // create the item here
    $itemid = $object->createItem();
    if (empty($itemid)) return; // throw back

    // let's go back to the admin view
    xarResponseRedirect(xarModURL('courses', 'admin', 'view', array('itemtype' => $itemtype)));

    // Return
    return true;
}
?>
