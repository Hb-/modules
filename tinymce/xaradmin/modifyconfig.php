<?php
/**
 * File: $Id$
 * 
 * Realms configuration modification
 * 
 * @package Xaraya eXtensible Management System
 * @copyright (C) 2003 by the Xaraya Development Team.
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
  * @subpackage Realms
 * @author Jo Dalle Nogare <jojodee@xaraya.com>
 */
/**
 * This is a standard function to modify the configuration parameters of the
 * module
 */
function tinymce_admin_modifyconfig()
{
    if (!xarSecurityCheck('AdminTinyMCE')) return;
    if (!xarVarFetch('tab', 'str:1:100', $data['tab'], 'basic', XARVAR_NOT_REQUIRED)) return;
  //  $data = xarModAPIFunc('tinymce', 'admin', 'menu');

    $data['authid'] = xarSecGenAuthKey();
    // Specify some labels and values for display
    $data['tinytheme'] = xarModGetVar('tinymce', 'tinytheme');
    $data['tinylang'] = xarModGetVar('tinymce', 'tinylang');    
    $data['updatebutton'] = xarVarPrepForDisplay(xarML('Update Configuration'));
    $data['tinymode'] = xarModGetVar('tinymce', 'tinymode');
    $data['tinyinstances'] = xarModGetVar('tinymce', 'tinyinstances');
    $data['tinycsslist'] = xarModGetVar('tinymce', 'tinycsslist');
    $data['tinyask'] = xarModGetVar('tinymce', 'tinyask');
    $data['tinyextended'] = xarModGetVar('tinymce', 'tinyextended');
    $data['tinyexstyle'] = xarModGetVar('tinymce', 'tinyexstyle');
    $data['tinybuttons'] = xarModGetVar('tinymce', 'tinybuttons');
    $data['tinybuttons2'] = xarModGetVar('tinymce', 'tinybuttons2');
    $data['tinybuttons3'] = xarModGetVar('tinymce', 'tinybuttons3');
    $data['tinybuild1'] = xarModGetVar('tinymce', 'tinybuild1');
    $data['tinybuild2'] = xarModGetVar('tinymce', 'tinybuild2');
    $data['tinybuild3'] = xarModGetVar('tinymce', 'tinybuild3');
    $data['tinybuttonsremove'] = xarModGetVar('tinymce', 'tinybuttonsremove');                    
    $data['tinytoolbar'] = xarModGetVar('tinymce', 'tinytoolbar');
    $data['tinywidth'] = xarModGetVar('tinymce', 'tinywidth');
    $data['tinyheight'] = xarModGetVar('tinymce', 'tinyheight');
    $data['tinyinlinestyle'] = xarModGetVar('tinymce', 'tinyinlinestyle');
    $data['tinyundolevel'] = xarModGetVar('tinymce', 'tinyundolevel');
    $data['defaulteditor'] = xarModGetVar('base','editor');
    $data['tinydirection'] = xarModGetVar('tinymce','tinydirection');    
    $data['tinyencode'] = xarModGetVar('tinymce','tinyencode');
    $data['tinyplugins'] = xarModGetVar('tinymce','tinyplugins');
    $data['tinydate']=xarModGetVar('tinymce', 'tinydate');
    $data['tinytime']=xarModGetVar('tinymce', 'tinytime');
    $data['tinybr']=xarModGetVar('tinymce', 'tinybr');
    $data['tinyinvalid']=xarModGetVar('tinymce', 'tinyinvalid');    
    $data['tinyadvformat']=xarModGetVar('tinymce', 'tinyadvformat');     
    $data['useibrowser']=xarModGetVar('tinymce', 'useibrowser');              
    if (strpos($data['tinyplugins'], 'insertdatetime')) {
        $data['dateplug']=1;
    } else {
        $data['dateplug']=0;
    }
    if (!isset($data['tab'])) {
        $data['tab']='basic';
    }
     //get list of valid themes
    $tinythemepath="./modules/tinymce/xartemplates/includes/tinymce/jscripts/tiny_mce/themes";
    $themelist=array();
    $handle=opendir($tinythemepath);
    $skip_array = array('.','..','SCCS','CVS','index.htm','index.html');
    while (false !== ($file = readdir($handle))) {
        // check the skip array and add files in it to array
        if (!in_array($file,$skip_array)) {
            $themelist[]=$file;
        }
    }
    closedir($handle);
    //get list of valid languages
    $tinylangpath="./modules/tinymce/xartemplates/includes/tinymce/jscripts/tiny_mce/langs";
    $langlist=array();
    $handle=opendir($tinylangpath);
    while (false !== ($file = readdir($handle))) {
        // check the skip array and add files in it to array
        if (!in_array($file,$skip_array)) {
            $langlist[]=str_replace('.js', '', $file);
        }
    }
    closedir($handle);
    $data['themelist']=$themelist;
    $data['langlist']=$langlist;
    $data['ddflushurl']=xarModURL('dynamicdata','admin','modifyconfig');
    $hooks = xarModCallHooks('module', 'modifyconfig', 'tinymce',
        array('module' => 'tinymce'));
    if (empty($hooks)) {
        $data['hooks'] = '';
    } elseif (is_array($hooks)) {
        $data['hooks'] = join('', $hooks);
    } else {
        $data['hooks'] = $hooks;
    }
    // Return the template variables defined in this function
    return $data;
}

?>
