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
    $data['tinyshowpath'] = xarModGetVar('tinymce','tinyshowpath');
    $data['tinyencode'] = xarModGetVar('tinymce','tinyencode');
    $data['tinyplugins'] = xarModGetVar('tinymce','tinyplugins');
    $data['tinydate']=xarModGetVar('tinymce', 'tinydate');
    $data['tinytime']=xarModGetVar('tinymce', 'tinytime');
    $data['tinybr']=xarModGetVar('tinymce', 'tinybr');
    $data['tinypara'] = xarModGetVar('tinymce','tinypara');
    $data['tinyinvalid']=xarModGetVar('tinymce', 'tinyinvalid');
    $data['tinyadvformat']=xarModGetVar('tinymce', 'tinyadvformat');
    //$data['useibrowser']=xarModGetVar('tinymce', 'useibrowser');
    $data['editorcss']=xarModGetVar('tinymce', 'tinyeditorcss');
    $data['tinynowrap']=xarModGetVar('tinymce', 'tinynowrap');
    $data['tinyloadmode']=xarModGetVar('tinymce', 'tinyloadmode');
    $data['tinycustom']=xarModGetVar('tinymce', 'tinycustom');
    $data['jstext']=xarModGetVar('tinymce','jstext');
    $data['multiconfig'] = xarModGetVar('tinymce', 'multiconfig');
    $data['dousemulticonfig'] = xarModGetVar('tinymce', 'usemulticonfig');
    $data['usebutton'] = xarModGetVar('tinymce', 'usebutton');
    $data['tinybrowsers'] = xarModGetVar('tinymce', 'tinybrowsers');
    $data['tinytilemap'] = xarModGetVar('tinymce', 'tinytilemap');
    $data['tinyadvresize'] = xarModGetVar('tinymce', 'tinyadvresize');
    $data['tinyenablepath'] = xarModGetVar('tinymce', 'tinyenablepath');
    $data['tinyresizehorizontal'] = xarModGetVar('tinymce', 'tinyresizehorizontal');    
    if (!isset($data['tinybrowsers'])){
        $data['tinybrowsers']='msie,gecko,safari';
    }

    if (!isset($data['usebutton']) || ($data['tinymode']=='textareas')){
        $data['usebutton']=0;
    }
    
    $examplestring='tinyMCE.init({
        mode : "textareas",
        editor_selector : "MyEditorConfig",
        height: "100px",
        theme : "simple"
    });';
    //prepare multiconfig for display
    $data['multiconfig']=trim($data['multiconfig']);
    if ($data['dousemulticonfig']){
     $multiconfig=$data['multiconfig'];

    } else  {
      $data['multiconfig']=$examplestring;
      $multiconfig='';
    }
    if (!isset($data['usebutton'])) {
      $data['usebutton']=false;
    }
    //Prepare the display of current configuration
    $stringstart='tinyMCE.init({';
    $stringend=' });';

    $data['jsstrings']="";
    $jsstrings=explode('",',$data['jstext']);
    $data['jsstrings']=$stringstart;
     foreach ($jsstrings as $key => $value) {
       if (strlen($value) > 70){
        $data['jsstrings'].="\n".wordwrap($value, 75,"\n",1 ).'",';
       }elseif (substr(trim($value),-1)=='"'){
           $data['jsstrings'] .="\n ".$value;
       }else{
          $data['jsstrings'] .="\n ".$value.'",';
       }
     }
     $data['jsstrings'] .="\n".$stringend;

     $data['jsmultiple'] ="\n";
     //Add the multiconfig to the end of the jstext string if not empty
     if ($data['dousemulticonfig']) {
              $data['jsmultiple'] ="\n".$data['multiconfig'];
     }

   $data['jsstrings'] .=$data['jsmultiple'];

    if (strpos($data['tinyplugins'], 'insertdatetime')) {
        $data['dateplug']=1;
    } else {
        $data['dateplug']=0;
    }
    if (!isset($data['tab'])) {
        $data['tab']='basic';
    }
    if (!isset($data['tinycustom'])) {
        $data['tinycustom']='';
    }
     //get list of valid themes
    $tinythemepath="./modules/tinymce/xartemplates/includes/tinymce/jscripts/tiny_mce/themes";
    $themelist=array();
    $handle=opendir($tinythemepath);
    $skip_array = array('.','..','SCCS','CVS','index.htm','index.html','readme.txt');
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
