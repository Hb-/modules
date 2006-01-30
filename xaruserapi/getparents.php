<?php
/**
 * Categories module
 *
 * @package modules
 * @copyright (C) 2002-2006 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage Categories Module
 * @link http://xaraya.com/index.php/release/147.html
 * @author Categories module development team
 */
/**
 * get parents of a specific (list of) category
 *
 * @param $args['cid'] id of category to get children for, or
 * @param $args['cids'] array of category ids to get children for
 * @param $args['return_itself'] =Boolean= return the cid itself (default true)
 * @returns array
 * @return array of category info arrays, false on failure
 */
function categories_userapi_getparents($args)
{
    $return_itself = true;
    extract($args);

    if (!isset($cid) && !isset($cids)) {
       xarSessionSetVar('errormsg', xarML('Bad arguments for API function'));
       return false;
    }
    $info = array();
    if (empty($cid)) {
        return $info;
    }

    $dbconn =& xarDBGetConn();
    $xartable =& xarDBGetTables();

    $categoriestable = $xartable['categories'];

// TODO : evaluate alternative with 2 queries
    $SQLquery = "SELECT
                        P1.xar_cid,
                        P1.xar_name,
                        P1.xar_description,
                        P1.xar_image,
                        P1.xar_parent,
                        P1.xar_left,
                        P1.xar_right
                   FROM $categoriestable AS P1,
                        $categoriestable AS P2
                  WHERE P2.xar_left
                     >= P1.xar_left
                    AND P2.xar_left
                     <= P1.xar_right";
/* this is terribly slow, at least for MySQL 3.23.49-nt
                  WHERE P2.xar_left
                BETWEEN P1.xar_left AND
                        P1.xar_right";
*/
    $SQLquery .= " AND P2.xar_cid = ?";
    $SQLquery .= " ORDER BY P1.xar_left";

    $result = $dbconn->Execute($SQLquery,array($cid));
    if (!$result) return;

    while (!$result->EOF) {
        list($pid, $name, $description, $image, $parent, $left, $right) = $result->fields;
        if (!xarSecurityCheck('ViewCategories',0,'Category',"$name:$cid")) {
             $result->MoveNext();
             continue;
        }

        if(($cid == $pid && $return_itself) || ($cid != $pid)) {
            $info[$pid] = Array(
                                "cid"         => $pid,
                                "name"        => $name,
                                "description" => $description,
                                "image"       => $image,
                                "parent"      => $parent,
                                "left"        => $left,
                                "right"       => $right
                                );
        }
        $result->MoveNext();
    }
    return $info;
}

?>
