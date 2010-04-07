<?php
/**
 * Dynamic Data Example Module - documented module template
 *
 * @package modules
 * @copyright (C) 2002-2007 The Digital Development Foundation
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage dyn_example
 * @link http://xaraya.com/index.php/release/66.html
 * @author mikespub <mikespub@xaraya.com>
 */
/**
 * Initialise the module
 *
 * This function is only ever called once during the lifetime of a particular
 * module instance
 * @return bool True on succes of init
 */
function dyn_example_init()
{
# --------------------------------------------------------
#
# Create DD objects
#
# The object XML files located in the xardata folder of the module.
# The file names have the form e.g.
#     dyn_example-def.xml
#     dyn_example-dat.xml
#
# The first is a definition file for the object, and needs to be present if you list dyn_example
# among the objects to be created in the array below.
#
# The second is a defintion file for the object's items, i.e. its data. This file can be omitted.
#
# You can create these files manually, for example by cutting and pasting from an existing example.
# The easier way is to create an object (and perhaps its items) using the user interface of the
# DynamicData module. Once you have an object (and items), you can export it into an XML file using the
# DD module's export facility.
#
# Note: the object(s) created below are automatically kept track of so that the module knows to remove them when
# you deinstall it.
#
    $module = 'dyn_example';
    $objects = array(
                'dyn_example',
                'dyn_example_module_settings',
                'dyn_example_user_settings',
                );

    if(!xarMod::apiFunc('modules','admin','standardinstall',array('module' => $module, 'objects' => $objects))) return;
# --------------------------------------------------------
#
# Set up configuration modvars (module specific)
#
# Since this modvar is used as storage in a DD object dyn_example_module_settings,
# we could also let Xaraya define it, but that would mean we wouldn't have it until
# we updated the modifyconfig page
#
    xarModVars::set('dyn_example','bold',true);

# --------------------------------------------------------
#
# Set up configuration modvars (general)
#
# The common settings use the module_settings dataobject. which is created when Xaraya is installed
# These next lines initialize the appropriate modvars that object uses for dyn_example, if they don't already exist.
# The lines below corresponding to the initializeation of the core modules are found in modules/installer/xaradmin.php.
# The module_settings dataobject itself is defined in the dynamicdata module.
#
        $module_settings = xarMod::apiFunc('base','admin','getmodulesettings',array('module' => 'dyn_example'));
        $module_settings->initialize();

# --------------------------------------------------------
#
# Register blocks
#
    if (!xarMod::apiFunc('blocks',
                       'admin',
                       'register_block_type',
                       array('modName' => 'dyn_example',
                             'blockType' => 'first'))) return;
# --------------------------------------------------------
#
# Create privilege instances if you don't use $ddobject->checkAccess() for security checks
#
    sys::import('modules.dynamicdata.class.objects.master');
    $object = DataObjectMaster::getObject(array('name' => 'dyn_example'));
    $objectid = $object->objectid;

    // Note: this query will retrieve a list of item ids for this object directly from the database
    // You could retrieve some more meaningful instances for security checks in your own module
    $xartable =& xarDB::getTables();
    $dynproptable = $xartable['dynamic_properties'];
    $dyndatatable = $xartable['dynamic_data'];
    $query = "SELECT DISTINCT $dyndatatable.item_id
	FROM $dynproptable
	LEFT JOIN $dyndatatable
                  ON $dyndatatable.property_id=$dynproptable.id
               WHERE $dynproptable.object_id=$objectid";

    // Note : we could add some other fields in here too, based on the properties we imported above
    $instances = array(
                        array('header' => 'Dynamic Example ID:',
                                'query' => $query,
                                'limit' => 20
                            )
                    );
    xarDefineInstance('dyn_example', 'Item', $instances);
# --------------------------------------------------------
#
# Register security masks
#
    // Check access to module items if you don't use $ddobject->checkAccess() for security checks
    xarRegisterMask('ViewDynExample',  'All','dyn_example','Item','All','ACCESS_OVERVIEW');
    xarRegisterMask('ReadDynExample',  'All','dyn_example','Item','All','ACCESS_READ');
    xarRegisterMask('EditDynExample',  'All','dyn_example','Item','All','ACCESS_EDIT');
    xarRegisterMask('AddDynExample',   'All','dyn_example','Item','All','ACCESS_ADD');
    xarRegisterMask('DeleteDynExample','All','dyn_example','Item','All','ACCESS_DELETE');
    xarRegisterMask('ConfigDynExample','All','dyn_example','Item','All','ACCESS_ADMIN');

    // Check access to the module administration if you don't use ???
    xarRegisterMask('AdminDynExample', 'All','dyn_example', 'All','All','ACCESS_ADMIN');

# --------------------------------------------------------
#
# Register hooks
#

    // Initialisation successful
    return true;
}

/**
 * Upgrade the module from an old version
 *
 * This function can be called multiple times
 */
function dyn_example_upgrade($oldversion)
{
    // Upgrade dependent on old version number
    switch($oldversion) {
        case '2.0.0':
            // Code to upgrade from version 2.0 goes here
            break;
    }

    // Update successful
    return true;
}

/**
 * Delete the module
 *
 * This function is only ever called once during the lifetime of a particular
 * module instance
 * @return bool true on success of deletion
 */
function dyn_example_delete()
{
    // UnRegister blocks
    if (!xarMod::apiFunc('blocks',
                       'admin',
                       'unregister_block_type',
                       array('modName' => 'dyn_example',
                             'blockType' => 'first'))) return;

# --------------------------------------------------------
#
# Uninstall the module
#
# The function below pretty much takes care of everything that needs to be removed
#
    $module = 'dyn_example';
    return xarMod::apiFunc('modules','admin','standarddeinstall',array('module' => $module));
}

?>
