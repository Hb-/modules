<?php

/**
 * update an item
 */
function dyn_example_admin_update($args)
{
    list($itemid,
         $objectid,
         $preview) = xarVarCleanFromInput('itemid',
                                          'objectid',
                                          'preview');

    extract($args);

    if (!empty($objectid)) {
        $itemid = $objectid;
    }

    if (empty($itemid)) {
        $msg = xarML('Invalid #(1) for #(2) function #(3)() in module #(4)',
                    'item id', 'admin', 'update', 'dyn_example');
        xarExceptionSet(XAR_USER_EXCEPTION, 'BAD_PARAM',
                       new SystemException($msg));
        return $msg;
    }

    // get the Dynamic Object defined for this module (and itemtype, if relevant)
    $object =& xarModAPIFunc('dynamicdata','user','getobject',
                             array('module' => 'dyn_example',
                                   'itemid' => $itemid));
    if (!isset($object)) return;

    // get the values for this item
    $newid = $object->getItem();
    if (!isset($newid) || $newid != $itemid) return;

    // check the input values for this object
    $isvalid = $object->checkInput();

    // if we're in preview mode, or if there is some invalid input, show the form again
    if (!empty($preview) || !$isvalid) {
        $data = xarModAPIFunc('dyn_example','admin','menu');

        $data['object'] = & $object;
        $data['itemid'] = $itemid;

        $data['preview'] = $preview;

        $item = array();
        $item['module'] = 'dyn_example';
        $hooks = xarModCallHooks('item','modify',$itemid,$item);
        if (empty($hooks)) {
            $data['hooks'] = '';
        } elseif (is_array($hooks)) {
            $data['hooks'] = join('',$hooks);
        } else {
            $data['hooks'] = $hooks;
        }

        return xarTplModule('dyn_example','admin','modify', $data);
    }

    // update the item
    $itemid = $object->updateItem();

    if (empty($itemid)) return; // throw back

    // let's go back to the admin view
    xarResponseRedirect(xarModURL('dyn_example', 'admin', 'view'));

    // Return
    return true;
}

?>
