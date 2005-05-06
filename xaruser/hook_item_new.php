<?php

function subitems_user_hook_item_new($args)
{
    extract($args);
    // extrainfo -> module,itemtype,itemid
    if (!isset($extrainfo['module'])) {
        $extrainfo['module'] = xarModGetName();
    }
    if (empty($extrainfo['itemtype'])) {
        $extrainfo['itemtype'] = 0;
    }

    // a object should be linked to this hook
    if(!$ddobjectlink = xarModAPIFunc('subitems','user','ddobjectlink_get',$extrainfo)) return '';
    // nothing to see here
    if (empty($ddobjectlink['objectid'])) return '';
    $objectid = $ddobjectlink['objectid'];

    // get the Dynamic Object defined for this module (and itemtype, if relevant)
    $object =& xarModAPIFunc('dynamicdata','user','getobject',
                             array('objectid' => $objectid,
                                     'status' => 1));

    $template = $object->name;
    if(!empty($ddobjectlink['template']))
        $template = $ddobjectlink['template'];

    $data['object'] = $object;

    return xarTplModule('subitems','user','hook_item_new',$data,$template);
}

?>
