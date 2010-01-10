<?php
/**
 * Articles module
 *
 * @package modules
 * @copyright (C) 2002-2010 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage Articles Module
 * @link http://xaraya.com/index.php/release/151.html
 * @author mikespub
 */
/**
 * get array of field formats for publication types
 * @TODO : move this to some common place in Xaraya (base module ?)
 * + replace with dynamic_propertytypes table
 *
 * + extend with other pre-defined formats
 * @return array('static'  => xarML('Static Text'),
                 'textbox' => xarML('Text Box'),
                 ...);
 */
function articles_userapi_getpubfieldformats($args)
{
    $fieldlist=array(
        'static'          => xarML('Static Text'),
        'textbox'         => xarML('Text Box'),
        'textarea_small'  => xarML('Text Area (small)'),
        'textarea_medium' => xarML('Text Area (medium)'),
        'textarea_large'  => xarML('Text Area (large)'),
        'dropdown'        => xarML('Dropdown List'),
        'textupload'      => xarML('Text Upload'),
        'fileupload'      => xarML('File Upload'),
        'url'             => xarML('URL'),
        'urltitle'        => xarML('URL + Title'),
        'image'           => xarML('Image'),
        'imagelist'       => xarML('Image List'),
        'calendar'        => xarML('Calendar'),
        'webpage'         => xarML('HTML Page'),
        'username'        => xarML('Username'),
        'userlist'        => xarML('User List'),
        'status'          => xarML('Status'),
        'language'        => xarML('Language List'),
    // TODO: add more property types after testing
   //other 'text' DD property types won't give significant performance hits
    );

    // Add  'text' dd properites that are dependent on module availability
    $extrafields=array();
    if (xarModIsAvailable('tinymce')) {
        $extrafields=array('xartinymce'=> xarML('xarTinyMCE GUI'));
        $fieldlist=array_merge($fieldlist,$extrafields);
    }
    asort($fieldlist);

    return $fieldlist;
}

?>
