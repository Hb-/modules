<?php

/**
// TODO: move this to some common place in Xaraya (base module ?)
 * Handle <xar:articles-field ...> form field tags
 * Format : <xar:articles-field definition="$definition" /> with $definition an array
 *                                             containing the type, name, value, ...
 *       or <xar:articles-field name="thisname" type="thattype" value="$val" ... />
 *
 * @param $args array containing the form field definition or the type, name, value, ...
 * @returns string
 * @return the PHP code needed to invoke showfield() in the BL template
 */
function articles_userapi_handleFieldTag($args)
{
    $out = "xarModAPILoad('articles','user');
echo xarModAPIFunc('articles',
                   'user',
                   'showfield',\n";
    if (isset($args['definition'])) {
        $out .= '                   '.$args['definition']."\n";
        $out .= '                  );';
    } else {
        $out .= "                   array(\n";
        foreach ($args as $key => $val) {
            if (is_numeric($val) || substr($val,0,1) == '$') {
                $out .= "                         '$key' => $val,\n";
            } else {
                $out .= "                         '$key' => '$val',\n";
            }
        }
        $out .= "                         ));";
    }
    return $out;
}

?>
