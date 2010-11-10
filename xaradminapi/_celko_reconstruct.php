<?php
/**
 * Comments module - Allows users to post comments on items
 *
 * @package modules
 * @copyright (C) 2002-2007 The copyright-placeholder
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage comments
 * @link http://xaraya.com/index.php/release/14.html
 * @author Carl P. Corliss <rabbitt@xaraya.com>
 */
/**
 *  Reconstruct a corrupted celko based table
 *  using the parent id's
 *
 *  @author Carl P. Corliss
 *  @access public
 *  @param  void
 *  @returns boolean  FALSE on error, TRUE on success
 */
function comments_adminapi_celko_reconstruct()
{

    $dbconn = xarDB::getConn();
    $xartable = xarDB::getTables();

    // initialize the commentlist array
    $commentlist = array();

    // if the depth is zero then we
    // only want one comment
    $sql = "SELECT  id AS id,
                    pid AS pid,
                    left_id AS left_id,
                    right_id AS right_id
              FROM  $xartable[comments]
          ORDER BY  pid DESC";

    $result =& $dbconn->Execute($sql);
    if (!$result) return;

    // if we have nothing to return
    // we return nothing ;) duh? lol
    if ($result->EOF) {
        return TRUE;
    }

    // add it to the array we will return
    while (!$result->EOF) {
        $row = $result->GetRowAssoc(false);
        $tree[$row['id']] = $row;
        $result->MoveNext();
    }
    $result->Close();

    krsort($tree);

    foreach ($tree as $pid => $node) {
        $newNode = $tree[$node['id']];

        $tree[$node['pid']]['children'][$node['id']] = $newNode;
        if ($pid) {
            unset($tree[$node['id']]);
        }
    }

    krsort($tree);

    // reassign the each node a celko left/right value
    $tree = xarMod::apiFunc('comments','admin','celko_assign_slots', $tree);

    // run through each node and update it's entry in the db
    if (!xarMod::apiFunc('comments','admin','celko_update', $newtree)) {
        $msg = xarML('Unable to reconstruct the comments table!');
        throw new BadParameterException($msg);
    }

}

?>
