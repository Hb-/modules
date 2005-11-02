<?php
/**
    Creates all security levels
    
    @param $args['modid']
    @param $args['itemtype']
    @param $args['itemid']
    @param $args['settings']
    
    @return boolean true if successful otherwise false
*/
function security_adminapi_create($args)
{
    extract($args);
    
    $dbconn =& xarDBGetConn();
    $xartable =& xarDBGetTables();
       
    $table = $xartable['security'];
    $groupLevelTable = $xartable['security_group_levels'];
   
    if( empty($settings) )
    {
        return false;
    }
    
    $query = "INSERT INTO $table (xar_modid, xar_itemtype, xar_itemid, xar_userlevel, xar_worldlevel)
              VALUES ( ?, ?, ?, ?, ? )
    ";
    $bindvars = array( $modid, $itemtype, $itemid, $settings['levels']['user'], $settings['levels']['world'] );
    $result = $dbconn->Execute($query, $bindvars);
    if( !$result ) return false;    
    
    foreach( $settings['levels']['groups'] as $gid => $group_level )
    {
        $query = "INSERT INTO $groupLevelTable (xar_modid, xar_itemtype, xar_itemid, xar_gid, xar_level)
                  VALUES ( ?, ?, ?, ?, ? )
        ";
        $bindvars = array( $modid, $itemtype, $itemid, $gid, $group_level );
        $result = $dbconn->Execute($query, $bindvars);
        if( !$result ) return false;
    }
    
    return true;
}
?>