function xar_filemanager_switchImport(sType, iTypeId, iObjectId) {

    switch ( sType ) {
        case 1: // turn on trusted import and off everything else
            document.getElementById(iObjectId + "_attach_trusted").style.display = "block";
            document.getElementById(iObjectId + "_attach_external").style.display = "none";
            document.getElementById(iObjectId + "_attach_upload").style.display = "none";
            document.getElementById(iObjectId + "_attach_stored").style.display = "none";

            document.getElementById(iObjectId + '_attach_external_id').value = '';
            document.getElementById(iObjectId + '_attach_type').value = iTypeId;
            break;
        case 2: // turn on external import and off everything else
            document.getElementById(iObjectId + "_attach_external").style.display = "block";
            document.getElementById(iObjectId + "_attach_trusted").style.display = "none";
            document.getElementById(iObjectId + "_attach_upload").style.display = "none";
            document.getElementById(iObjectId + "_attach_stored").style.display = "none";

            document.getElementById(iObjectId + '_attach_trusted_id').value = null;
            document.getElementById(iObjectId + '_attach_type').value = iTypeId;
            break;
        case 3: // turn on upload import and off everything else
            document.getElementById(iObjectId + "_attach_upload").style.display = "block";
            document.getElementById(iObjectId + "_attach_external").style.display = "none";
            document.getElementById(iObjectId + "_attach_trusted").style.display = "none";
            document.getElementById(iObjectId + "_attach_stored").style.display = "none";

            document.getElementById(iObjectId + '_attach_trusted_id').value = null;
            document.getElementById(iObjectId + '_attach_external_id').value = '';
            document.getElementById(iObjectId + '_attach_type').value = iTypeId;
            break;
        case 4: // turn on stored and off everything else
            document.getElementById(iObjectId + "_attach_stored").style.display = "block";
            document.getElementById(iObjectId + "_attach_upload").style.display = "none";
            document.getElementById(iObjectId + "_attach_external").style.display = "none";
            document.getElementById(iObjectId + "_attach_trusted").style.display = "none";

            document.getElementById(iObjectId + '_attach_stored_id').value = null;
            document.getElementById(iObjectId + '_attach_external_id').value = '';
            document.getElementById(iObjectId + '_attach_type').value = iTypeId;
            break;
        default: 
            document.getElementById(iObjectId + "_attach_stored").style.display = "none";
            document.getElementById(iObjectId + "_attach_upload").style.display = "none";
            document.getElementById(iObjectId + "_attach_external").style.display = "none";
            document.getElementById(iObjectId + "_attach_trusted").style.display = "none";
            
            document.getElementById(iObjectId + '_attach_type').value = -1;
            break;
    }

    return true;
}
