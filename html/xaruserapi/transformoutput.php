<?php
/**
 * File: $Id$
 *
 * Xaraya HTML Module
 *
 * @package Xaraya eXtensible Management System
 * @copyright (C) 2002 by the Xaraya Development Team.
 * @license GPL <http://www.gnu.org/licenses/gpl.html>
 * @link http://www.xaraya.org
 *
 * @subpackage HTML Module
 * @author John Cox
*/

/**
 * Transform text
 *
 * @public
 * @author John Cox 
 * @param $args['extrainfo'] string or array of text items
 * @returns string
 * @return string or array of transformed text items
 * @raise BAD_PARAM
 */
function html_userapi_transformoutput($args)
{
    // Get arguments from argument array
    extract($args);

    // Argument check
    if (!isset($extrainfo)) {
        $msg = xarML('Invalid Parameter #(1) for #(2) function #(3)() in module #(4)',
                     'extrainfo', 'userapi', 'transformoutput', 'html');
        xarExceptionSet(XAR_SYSTEM_EXCEPTION, 'BAD_PARAM', new SystemException($msg));
        return;
    }

    if (is_array($extrainfo)) {
        if (isset($extrainfo['transform']) && is_array($extrainfo['transform'])) {
            foreach ($extrainfo['transform'] as $key) {
                if (isset($extrainfo[$key])) {
                    $extrainfo[$key] = html_userapitransformoutput($extrainfo[$key]);
                }
            }
            return $extrainfo;
        }
        $transformed = array();
        foreach($extrainfo as $text) {
            $transformed[] = html_userapitransformoutput($text);
        }
    } else {
        $transformed = html_userapitransformoutput($text);
    }

    return $transformed;
}

/**
 * Transform text api
 *
 * @private
 * @author John Cox 
 */
function html_userapitransformoutput($text)
{
    $transformtype = xarModGetVar('html', 'transformtype');
    if ($transformtype == 1){
        $text = preg_replace("/\n/si","<br />",$text);
    } elseif ($transformtype == 2){
        $text = preg_replace("/\n/si","</p><p>",$text);
    } elseif ($transformtype == 3){
        $text = $text;
    } elseif ($transformtype == 4){
        // If the string contains end of line type tags, assume the user
        // wants to provide html markup manually
        if( strpos($text,"<b") OR strpos($text,"<p") )
        {
            $text = $text;
        } else {
            // No html tags for dealing with end-of-line, transform as Breaks <br />
            $text = preg_replace("/\n/si","<br />",$text);
        }
    }
    //$text = preg_replace("/(\015\012)|(\015)|(\012)/","</p><p>",$text); 
    // This call is what is driving the bugs because it is transforming more
    // than we want.  The problem without the call though, it the output from
    // this function is not xhtml compliant.
    //
    // So, a configuration in the html script will allow a replacement of
    // paragraphs or line breaks.  If paragraphs are used, the template must
    // open and close the paragraphs tags before and after the transformed output.
    //$text = "<p> " . $text . " </p>\n";
   $text = str_replace ("<p></p>", "", $text);
    return $text;
}

?>
