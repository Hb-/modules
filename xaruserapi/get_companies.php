<?php
/**
    Gets the valid companies of the current user if any exist
*/
function helpdesk_userapi_get_companies($args)
{
    extract($args);
        
    /*
        Detects if 'Companies' group exists.  If it does not then just return false.
        It's not an exception because this is just an optional concept for those that need it.
    */
    $roles = new xarRoles();
    if( !$roles->findRole('Companies') ){ return false; }
    
    $groups = xarModAPIFunc('roles', 'user', 'getallgroups', 
        array(
            'parent' => 'Companies',
        )
    );    

    $companies = array();
    if( !xarSecurityCheck('edithelpdesk', 0) )
    {
        // Lose all groups the user is not in
        $user = $roles->getRole( xarUserGetVar('uid') );
        $parents = $user->getParents(); // AND parents and groups
        
        foreach( $parents as $parent )
        {
            foreach( $groups as $group )
            {
                if( $parent->uid == $group['uid'] )
                {
                    $companies[] = $group;
                    break;
                }
            }
        }        
    }
    else     
        $companies = $groups;
    
    return $companies;
}
?>