<?php

function navigator_userapi_nested_tree_create( $args )
{
    $tree = &$args['tree'];

    if (!isset($tree) || empty($tree)) {
        return;
    }

    $index = 1;
    $npid = 0;
    
    // FIXME: ugly hack to make the tree create work with 
    // trees that have been created and flattened already
    $test = current($tree);
    if (isset($test['indentation'])) {
        $sIndentName = 'indentation';
    } else {
        $sIndentName = 'indent';
    }
    
    // First thing we do is to reindex the array and
    // set up the elements that we will need
    foreach ($tree as $node) {
        $tmpTree[$index]['primary']  = (isset($node['primary']) ? $node['primary'] : NULL);
        $tmpTree[$index]['name']     = $node['name'];
        $tmpTree[$index]['cid']      = $node['cid'];
        $tmpTree[$index]['pid']      = (isset($node['parent']) ? $node['parent'] : $node['pid']);
        $tmpTree[$index]['children'] = array();

        if (!isset($rootIndent)) {
            // we base all indentation off the first element
            // in the array. This requires that the first element
            // has the lowest indent level
            $rootIndent = $node[$sIndentName];
        }

        // All other indents are in relation to the first element's
        // indent level - below is where we make that so.
        $tmpTree[$index]['indent'] = $node[$sIndentName] - $rootIndent;

        if ($index == 1) {
            $tmpTree[$index]['npid'] = $npid;
        } elseif ($index > 1) {
            $previous = $tmpTree[$index - 1]['indent'];
            $current  = $tmpTree[$index]['indent'];

            if ($current > $previous) {
                $npid = $index - 1;
                $tmpTree[$index]['npid'] = $npid;
            } elseif ($current < $previous) {
                $npid = $tmpTree[$index - 1]['npid'];
                while ($tmpTree[$npid]['indent'] != $current) {
                    $npid = $tmpTree[$npid]['npid'];
                }
                $tmpTree[$index]['npid'] = $tmpTree[$npid]['npid'];
            } else {
                $tmpTree[$index]['npid'] = $npid;
            }
        }
        $tmpTree[$index]['ncid']    = $index;
        $index++;
    }

    $tree = $tmpTree;

    krsort($tree);

    $list = array();

    foreach ($tree as $pid => $node) {
        if ($pid) {
            $tree[$node['npid']]['children'][$node['ncid']] =& $tree[$node['ncid']];
            unset($tree[$node['ncid']]);
        }
    }

    $tmpTree = $tree[0]['children'];
    xarModAPIFunc('navigator', 'user', 'nested_tree_sort', array('tree' => &$tmpTree));

    $tree = $tmpTree;
    unset($tmpTree);
}

?>