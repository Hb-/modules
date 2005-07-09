<?php

xarModAPILoad('uploads','user');

function uploads_admin_view( ) 
{
    //security check
    if (!xarSecurityCheck('AdminUploads')) return;
    
    /**
     *  Validate variables passed back
     */
     
    if (!xarVarFetch('mimetype',    'int:0:',     $mimetype,         NULL, XARVAR_DONT_SET)) return;
    if (!xarVarFetch('subtype',     'int:0:',     $subtype,          NULL, XARVAR_DONT_SET)) return;
    if (!xarVarFetch('status',      'int:0:',     $status,           NULL, XARVAR_DONT_SET)) return;
    if (!xarVarFetch('inverse',     'checkbox',   $inverse,          NULL, XARVAR_DONT_SET)) return;
    if (!xarVarFetch('fileId',      'list:int:1', $fileId,           NULL, XARVAR_DONT_SET)) return;
    if (!xarVarFetch('fileDo',      'str:5:',     $fileDo,           NULL, XARVAR_DONT_SET)) return;
    if (!xarVarFetch('action',      'int:0:',     $action,           NULL, XARVAR_DONT_SET)) return;
    
    /**
     *  Determine the filter settings to use for this view
     */
    if (!isset($mimetype) || !isset($subtype) || !isset($status) || !isset($inverse)) {
        // if the filter settings are empty, then 
        // grab the users last view filter
        $options  = unserialize(xarModGetUserVar('uploads','view.filter'));
        $data     = $options['data'];
        $filter   = $options['filter'];
        unset($options);
    } else {
        // otherwise, grab whatever filter options where passed in
        // and process them to create a filter
        $filters['mimetype'] = $mimetype;        
        $filters['subtype']  = $subtype;
        $filters['status']   = $status;
        $filters['inverse']  = $inverse;

        $options  =  xarModAPIFunc('uploads','user','process_filters', $filters);
        $data     = $options['data'];
        $filter   = $options['filter'];
        unset($options);
    }
    

    
    /**
     * Perform all actions
     */
    
    if (isset($action)) {
        
        if ($action > 0) {
            if (isset($fileDo)) {
                // If we got a signal to change status but no list of files to change,
                // then do nothing
                if (isset($fileId) && !empty($fileId)) {
                    $args['fileId']     = $fileId;
                } else {
                    $action = 0;
                }
            } else {
                $args['fileType']   = $filter['fileType'];
                $args['curStatus']  = $filter['fileStatus'];
            }
        }
        
        switch ($action) {
            case _UPLOADS_STATUS_APPROVED:
                    xarModAPIFunc('uploads','user','db_change_status', $args + array('newStatus'    => _UPLOADS_STATUS_APPROVED));
                    break;
            case _UPLOADS_STATUS_SUBMITTED:
                    xarModAPIFunc('uploads','user','db_change_status', $args + array('newStatus'    => _UPLOADS_STATUS_SUBMITTED));
                    break;
            case _UPLOADS_STATUS_REJECTED:
                xarModAPIFunc('uploads','user','db_change_status', $args + array('newStatus'   => _UPLOADS_STATUS_REJECTED));
                if (xarModGetVar('uploads', 'file.auto-purge')) {
                    if (xarModGetVar('uploads', 'file.delete-confirmation')) {
                        return xarModFunc('uploads', 'admin', 'purge_rejected', array('confirmation' => FALSE, 'authid' => xarSecGenAuthKey('uploads')));
                    } else {
                        return xarModFunc('uploads', 'admin', 'purge_rejected', array('confirmation' => TRUE, 'authid' => xarSecGenAuthKey('uploads')));
                    }
                }
                break;
            case 0: /* Change View or anything not defined */
            default:
                break;
        }
    }
    
    /**
     * Grab a list of files based on the defined filter 
     */
     
    if (!isset($filter) || count($filter) <= 0) {
        $items = xarModAPIFunc('uploads', 'user', 'db_getall_files');
    } else {
        $items = xarModAPIFunc('uploads', 'user', 'db_get_file', $filter);
    }
    
    if (xarSecurityCheck('EditUploads', 0)) {
    
        $data['diskUsage']['stored_size_filtered'] = xarModAPIFunc('uploads', 'user', 'db_diskusage', $filter);
        $data['diskUsage']['stored_size_total']    = xarModAPIFunc('uploads', 'user', 'db_diskusage');

        $data['diskUsage']['device_free']   = disk_free_space(xarModGetVar('uploads', 'path.uploads-directory'));
        $data['diskUsage']['device_total']  = disk_total_space(xarModGetVar('uploads', 'path.uploads-directory'));
        $data['diskUsage']['device_used']  = $data['diskUsage']['device_total'] - $data['diskUsage']['device_free'];

        foreach ($data['diskUsage'] as $key => $value) {
            $data['diskUsage'][$key] = xarModAPIFunc('uploads', 'user', 'normalize_filesize', $value);
        }

        $data['diskUsage']['numfiles_filtered']   = xarModAPIfunc('uploads', 'user', 'db_count', $filter);
        $data['diskUsage']['numfiles_total']      = xarModAPIFunc('uploads', 'user', 'db_count');
    }
    // now we check to see if the user has enough access to view 
    // each particular file - if not, we just silently remove it
    // from the view
    foreach ($items as $key => $fileInfo) {
        unset($instance);
        $instance[0] = $fileInfo['fileTypeInfo']['typeId'];
        $instance[1] = $fileInfo['fileTypeInfo']['subtypeId'];
        $instance[2] = xarSessionGetVar('uid');
        $instance[3] = $fileInfo['fileId'];

        if (is_array($instance)) {
            $instance = implode(':', $instance);
        } 
        if (!xarSecurityCheck('EditUploads', 0, 'File', $instance)) {
            unset($items[$key]);
        }
    }
    
        
    /**
     *  Check for exceptions
     */
    if (!isset($items) && xarCurrentErrorType() != XAR_NO_EXCEPTION) return; // throw back

    $data['items'] = $items;
    $data['authid'] = xarSecGenAuthKey();
    
    return $data;   
}

?>
