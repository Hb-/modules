<?php

/**
 * generate the common admin menu configuration
 */
function messages_adminapi_menu()
{
    // Initialise the array that will hold the menu configuration
    $menu = array();

    // Specify the menu title to be used in your blocklayout template
    $menu['menutitle'] = xarML('Messages Administration');

    // Specify the menu labels to be used in your blocklayout template
    $menu['menulabel_new'] = xarML('New Messages');
    $menu['menulabel_view'] = xarML('View Messages');
    $menu['menulabel_config'] = xarML('Modify Messages Config');

    // Preset some status variable
    $menu['status'] = '';

    // Note : you could also specify the menu links here, and pass them
    // on to the template as variables
    // $menu['menulink_view'] = xarModURL('messages','admin','view');

    // Note : you could also put all menu items in a $menu['menuitems'] array
    //
    // Initialise the array that will hold the different menu items
    // $menu['menuitems'] = array();
    //
    // Define a menu item
    // $item = array();
    // $item['menulabel'] = _messagesVIEW;
    // $item['menulink'] = xarModURL('messages','user','view');
    //
    // Add it to the array of menu items
    // $menu['menuitems'][] = $item;
    //
    // Add more menu items to the array
    // ...
    //
    // Then you can let the blocklayout template create the different
    // menu items *dynamically*, e.g. by using something like :
    //
    // <xart:loop name="menuitems">
    //    <td><a href="&xart-var-menulink;">&xart-var-menulabel;</a></td>
    // </xart:loop>
    //
    // in the templates of your module. Or you could even pass an argument
    // to the admin_menu() function to turn links on/off automatically
    // depending on which function is currently called...
    //
    // But most people will prefer to specify all this manually in each
    // blocklayout template anyway :-)

    // Return the array containing the menu configuration
    return $menu;
}

?>
