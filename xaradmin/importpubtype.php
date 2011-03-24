<?php
/**
 * Publications module
 *
 * @package modules
 * @copyright (C) copyright-placeholder
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage Publications Module
 
 * @author mikespub
 */
/**
 * Import an object definition or an object item from XML
 */
function publications_admin_importpubtype($args)
{
    if (!xarSecurityCheck('AdminPublications')) return;

    if(!xarVarFetch('import', 'isset', $import,  NULL, XARVAR_DONT_SET)) {return;}
    if(!xarVarFetch('xml', 'isset', $xml,  NULL, XARVAR_DONT_SET)) {return;}

    extract($args);

    $data = array();
    $data['menutitle'] = xarML('Dynamic Data Utilities');

    $data['warning'] = '';
    $data['options'] = array();

    $basedir = 'modules/publications';
    $filetype = 'xml';
    $files = xarModAPIFunc('dynamicdata','admin','browse',
                           array('basedir' => $basedir,
                                 'filetype' => $filetype));
    if (!isset($files) || count($files) < 1) {
        $files = array();
        $data['warning'] = xarML('There are currently no XML files available for import in "#(1)"',$basedir);
    }

    if (!empty($import) || !empty($xml)) {
        if (!xarSecConfirmAuthKey()) return;

        if (!empty($import)) {
            $found = '';
            foreach ($files as $file) {
                if ($file == $import) {
                    $found = $file;
                    break;
                }
            }
            if (empty($found) || !file_exists($basedir . '/' . $file)) {
                $msg = xarML('File not found');
                throw new BadParameterException(null,$msg);
            }
            $ptid = xarModAPIFunc('publications','admin','importpubtype',
                                  array('file' => $basedir . '/' . $file));
        } else {
            $ptid = xarModAPIFunc('publications','admin','importpubtype',
                                  array('xml' => $xml));
        }
        if (empty($ptid)) return;

        $data['warning'] = xarML('Publication type #(1) was successfully imported',$ptid);
    }

    natsort($files);
    array_unshift($files,'');
    foreach ($files as $file) {
         $data['options'][] = array('id' => $file,
                                    'name' => $file);
    }

    $data['authid'] = xarSecGenAuthKey();
    return $data;
}

?>