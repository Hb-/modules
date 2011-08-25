<?php
/**
 * Articles module
 *
 * @package modules
 * @copyright (C) 2002-2009 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage Articles Module
 * @link http://xaraya.com/index.php/release/151.html
 * @author mikespub
 */
/**
 * show some predefined form field in a template
 *
 * @param $args array containing the definition of the field (type, name, value, ...)
 * @return string containing the HTML (or other) text to output in the BL template
 * @TODO move this to some common place in Xaraya (base module ?)
 */
function articles_userapi_showfield($args)
{
    if (empty($args['type']) || $args['type'] != 'fieldtype') {
        if (isset($args['validation']) && !isset($args['configuration'])) {
            $args['configuration'] = $args['validation'];
            unset($args['validation']);
        }
        // TODO: check/fix display of username property in roles ?
        if ($args['type'] == 'username') {
            $property = & DataPropertyMaster::getProperty($args);
            $property->initialization_store_type = 'id';

            return $property->showOutput($args) . $property->showHidden($args);
        }
        // rename legacy type
        if ($args['type'] == 'textarea_small') {
            $args['type'] = 'textarea';
        }
        // quick hack to at least get a display
        if ($args['type'] == 'status') {
            $args['type'] = 'textbox';
        }
        // let DynamicData handle it
        return xarMod::apiFunc('dynamicdata','admin','showinput',$args);
    }

    extract($args);
    if (empty($name)) {
        return xarML('Missing \'name\' attribute in field tag or definition');
    }
    if (!isset($type)) {
        $type = 'text';
    }
    if (!isset($value)) {
        $value = '';
    }
    if (!isset($id)) {
        $id = '';
    } else {
        $id = ' id="'.$id.'"';
    }
    if (!isset($tabindex)) {
        $tabindex = '';
    } else {
        $tabindex = ' tabindex="'.$tabindex.'"';
    }

    // Note: if we want to re-use this for dynamic data, types are numeric there
    if (is_numeric($type)) {
        $fieldformatnums = xarMod::apiFunc('articles','user','getfieldformatnums');
        foreach ($fieldformatnums as $fname => $fid) {
            if ($fid == $type) {
                $type = $fname;
                break;
            }
        }
    }

    $output = '';
    switch ($type) {
    // yes, we auto-declare the allowed field types here too :-)
        case 'fieldtype':
            // Get the list of defined field formats
            $pubfieldformats = xarMod::apiFunc('articles','user','getpubfieldformats');

        // Note: if we want to re-use this for dynamic data, id's need to be numeric
            if (!empty($numeric_id)) {
                $fieldformatnums = xarMod::apiFunc('articles','user','getfieldformatnums');
            }

            $output .= '<select name="'.$name.'"'.$id.$tabindex.'>';
            foreach ($pubfieldformats as $fid => $fname) {
                if (!empty($numeric_id)) {
                    $numid = $fieldformatnums[$fid];
                    $output .= '<option value="'.$numid.'"';
                    if ($numid == $value) {
                        $output .= ' selected';
                    }
                } else {
                    $output .= '<option value="'.$fid.'"';
                    if ($fid == $value) {
                        $output .= ' selected';
                    }
                }
                $output .= '>'.$fname.'</option>';
            }
            $output .= '</select>';
            break;
        default:
            $output .= 'Unknown type '.$type;
            break;
    }
    return $output;
}

?>
