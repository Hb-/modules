<?php
// ----------------------------------------------------------------------
// Copyright (C) 2004: Marc Lutolf (marcinmilan@xaraya.com)
// Purpose of file:  Configuration functions for commerce
// ----------------------------------------------------------------------
//  based on:
//  (c) 2003 XT-Commerce
//  (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
//  (c) 2002-2003 osCommerce (oscommerce.sql,v 1.83); www.oscommerce.com
//  (c) 2003  nextcommerce (nextcommerce.sql,v 1.76 2003/08/25); www.nextcommerce.org
// ----------------------------------------------------------------------

function commerce_admin_backup()
{


  if ($_GET['action']) {
    switch ($_GET['action']) {
      case 'forget':
        new xenQuery("delete from " . TABLE_CONFIGURATION . " where configuration_key = 'DB_LAST_RESTORE'");
        $messageStack->add_session(SUCCESS_LAST_RESTORE_CLEARED, 'success');
        xarRedirectResponse(xarModURL('commerce','admin',(FILENAME_BACKUP));
        break;
      case 'backupnow':
        @xtc_set_time_limit(0);
        $schema = '# osCommerce, Open Source E-Commerce Solutions' . "\n" .
                  '# http://www.oscommerce.com' . "\n" .
                  '#' . "\n" .
                  '# Database Backup For ' . STORE_NAME . "\n" .
                  '# Copyright (c) ' . date('Y') . ' ' . STORE_OWNER . "\n" .
                  '#' . "\n" .
                  '# Database: ' . DB_DATABASE . "\n" .
                  '# Database Server: ' . DB_SERVER . "\n" .
                  '#' . "\n" .
                  '# Backup Date: ' . date(PHP_DATE_TIME_FORMAT) . "\n\n";
        $tables_query = new xenQuery('show tables');
      $q = new xenQuery();
      $q->run();
        while ($tables = $q->output()) {
          list(,$table) = each($tables);
          $schema .= 'drop table if exists ' . $table . ';' . "\n" .
                     'create table ' . $table . ' (' . "\n";
          $table_list = array();
          $fields_query = new xenQuery("show fields from " . $table);
      $q = new xenQuery();
      $q->run();
          while ($fields = $q->output()) {
            $table_list[] = $fields['Field'];
            $schema .= '  ' . $fields['Field'] . ' ' . $fields['Type'];
            if (strlen($fields['Default']) > 0) $schema .= ' default \'' . $fields['Default'] . '\'';
            if ($fields['Null'] != 'YES') $schema .= ' not null';
            if (isset($fields['Extra'])) $schema .= ' ' . $fields['Extra'];
            $schema .= ',' . "\n";
          }
          $schema = ereg_replace(",\n$", '', $schema);

          // Add the keys
          $index = array();
          $keys_query = new xenQuery("show keys from " . $table);
      $q = new xenQuery();
      $q->run();
          while ($keys = $q->output()) {
            $kname = $keys['Key_name'];
            if (!isset($index[$kname])) {
              $index[$kname] = array('unique' => !$keys['Non_unique'],
                                     'columns' => array());
            }
            $index[$kname]['columns'][] = $keys['Column_name'];
          }
          while (list($kname, $info) = each($index)) {
            $schema .= ',' . "\n";
            $columns = implode($info['columns'], ', ');
            if ($kname == 'PRIMARY') {
              $schema .= '  PRIMARY KEY (' . $columns . ')';
            } elseif ($info['unique']) {
              $schema .= '  UNIQUE ' . $kname . ' (' . $columns . ')';
            } else {
              $schema .= '  KEY ' . $kname . ' (' . $columns . ')';
            }
          }
          $schema .= "\n" . ');' . "\n\n";

          // Dump the data
          $rows_query = new xenQuery("select " . implode(',', $table_list) . " from " . $table);
      $q = new xenQuery();
      $q->run();
          while ($rows = $q->output()) {
            $schema_insert = 'insert into ' . $table . ' (' . implode(', ', $table_list) . ') values (';
            reset($table_list);
            while (list(,$i) = each($table_list)) {
              if (!isset($rows[$i])) {
                $schema_insert .= 'NULL, ';
              } elseif ($rows[$i] != '') {
                $row = addslashes($rows[$i]);
                $row = ereg_replace("\n#", "\n".'\#', $row);
                $schema_insert .= '\'' . $row . '\', ';
              } else {
                $schema_insert .= '\'\', ';
              }
            }
            $schema_insert = ereg_replace(', $', '', $schema_insert) . ');' . "\n";
            $schema .= $schema_insert;
          }
          $schema .= "\n";
        }

        if ($_POST['download'] == 'yes') {
          $backup_file = 'db_' . DB_DATABASE . '-' . date('YmdHis') . '.sql';
          switch ($_POST['compress']) {
            case 'no':
              header('Content-type: application/x-octet-stream');
              header('Content-disposition: attachment; filename=' . $backup_file);
              echo $schema;
              exit;
              break;
            case 'gzip':
              if ($fp = fopen(DIR_FS_BACKUP . $backup_file, 'w')) {
                fputs($fp, $schema);
                fclose($fp);
                exec(LOCAL_EXE_GZIP . ' ' . DIR_FS_BACKUP . $backup_file);
                $backup_file .= '.gz';
              }
              if ($fp = fopen(DIR_FS_BACKUP . $backup_file, 'rb')) {
                $buffer = fread($fp, filesize(DIR_FS_BACKUP . $backup_file));
                fclose($fp);
                unlink(DIR_FS_BACKUP . $backup_file);
                header('Content-type: application/x-octet-stream');
                header('Content-disposition: attachment; filename=' . $backup_file);
                echo $buffer;
                exit;
              }
              break;
            case 'zip':
              if ($fp = fopen(DIR_FS_BACKUP . $backup_file, 'w')) {
                fputs($fp, $schema);
                fclose($fp);
                exec(LOCAL_EXE_ZIP . ' -j ' . DIR_FS_BACKUP . $backup_file . '.zip ' . DIR_FS_BACKUP . $backup_file);
                unlink(DIR_FS_BACKUP . $backup_file);
                $backup_file .= '.zip';
              }
              if ($fp = fopen(DIR_FS_BACKUP . $backup_file, 'rb')) {
                $buffer = fread($fp, filesize(DIR_FS_BACKUP . $backup_file));
                fclose($fp);
                unlink(DIR_FS_BACKUP . $backup_file);
                header('Content-type: application/x-octet-stream');
                header('Content-disposition: attachment; filename=' . $backup_file);
                echo $buffer;
                exit;
              }
          }
        } else {
          $backup_file = DIR_FS_BACKUP . 'db_' . DB_DATABASE . '-' . date('YmdHis') . '.sql';
          if ($fp = fopen($backup_file, 'w')) {
            fputs($fp, $schema);
            fclose($fp);
            switch ($_POST['compress']) {
              case 'gzip':
                exec(LOCAL_EXE_GZIP . ' ' . $backup_file);
                break;
              case 'zip':
                exec(LOCAL_EXE_ZIP . ' -j ' . $backup_file . '.zip ' . $backup_file);
                unlink($backup_file);
            }
          }
          $messageStack->add_session(SUCCESS_DATABASE_SAVED, 'success');
        }
        xarRedirectResponse(xarModURL('commerce','admin',(FILENAME_BACKUP));
        break;
      case 'restorenow':
      case 'restorelocalnow':
        @xtc_set_time_limit(0);

        if ($_GET['action'] == 'restorenow') {
          $read_from = $_GET['file'];
          if (file_exists(DIR_FS_BACKUP . $_GET['file'])) {
            $restore_file = DIR_FS_BACKUP . $_GET['file'];
            $extension = substr($_GET['file'], -3);
            if ( ($extension == 'sql') || ($extension == '.gz') || ($extension == 'zip') ) {
              switch ($extension) {
                case 'sql':
                  $restore_from = $restore_file;
                  $remove_raw = false;
                  break;
                case '.gz':
                  $restore_from = substr($restore_file, 0, -3);
                  exec(LOCAL_EXE_GUNZIP . ' ' . $restore_file . ' -c > ' . $restore_from);
                  $remove_raw = true;
                  break;
                case 'zip':
                  $restore_from = substr($restore_file, 0, -4);
                  exec(LOCAL_EXE_UNZIP . ' ' . $restore_file . ' -d ' . DIR_FS_BACKUP);
                  $remove_raw = true;
              }

              if ( ($restore_from) && (file_exists($restore_from)) && (filesize($restore_from) > 15000) ) {
                $fd = fopen($restore_from, 'rb');
                $restore_query = fread($fd, filesize($restore_from));
                fclose($fd);
              }
            }
          }
        } elseif ($_GET['action'] == 'restorelocalnow') {
          $sql_file = new upload('sql_file');

          if ($sql_file->parse() == true) {
            $restore_query = fread(fopen($sql_file->tmp_filename, 'r'), filesize($sql_file->tmp_filename));
            $read_from = $sql_file->filename;
          }
        }

        if ($restore_query) {
          $sql_array = array();
          $sql_length = strlen($restore_query);
          $pos = strpos($restore_query, ';');
          for ($i=$pos; $i<$sql_length; $i++) {
            if ($restore_query[0] == '#') {
              $restore_query = ltrim(substr($restore_query, strpos($restore_query, "\n")));
              $sql_length = strlen($restore_query);
              $i = strpos($restore_query, ';')-1;
              continue;
            }
            if ($restore_query[($i+1)] == "\n") {
              for ($j=($i+2); $j<$sql_length; $j++) {
                if (trim($restore_query[$j]) != '') {
                  $next = substr($restore_query, $j, 6);
                  if ($next[0] == '#') {
// find out where the break position is so we can remove this line (#comment line)
                    for ($k=$j; $k<$sql_length; $k++) {
                      if ($restore_query[$k] == "\n") break;
                    }
                    $query = substr($restore_query, 0, $i+1);
                    $restore_query = substr($restore_query, $k);
// join the query before the comment appeared, with the rest of the dump
                    $restore_query = $query . $restore_query;
                    $sql_length = strlen($restore_query);
                    $i = strpos($restore_query, ';')-1;
                    continue 2;
                  }
                  break;
                }
              }
              if ($next == '') { // get the last insert query
                $next = 'insert';
              }
              if ( (eregi('create', $next)) || (eregi('insert', $next)) || (eregi('drop t', $next)) ) {
                $next = '';
                $sql_array[] = substr($restore_query, 0, $i);
                $restore_query = ltrim(substr($restore_query, $i+1));
                $sql_length = strlen($restore_query);
                $i = strpos($restore_query, ';')-1;
              }
            }
          }

          new xenQuery("drop table if exists address_book, admin_access,banktransfer,content_manager,address_format, banners, banners_history, categories, categories_description, configuration, configuration_group, counter, counter_history, countries, currencies, customers, customers_basket, customers_basket_attributes, customers_info, languages, manufacturers, manufacturers_info, orders, orders_products, orders_status, orders_status_history, orders_products_attributes, orders_products_download, products, products_attributes, products_attributes_download, prodcts_description, products_options, products_options_values, products_options_values_to_products_options, products_to_categories, reviews, reviews_description, sessions, specials, tax_class, tax_rates, geo_zones, whos_online, zones, zones_to_geo_zones");
          for ($i = 0, $n = sizeof($sql_array); $i < $n; $i++) {
            new xenQuery($sql_array[$i]);
          }

          new xenQuery("delete from " . TABLE_CONFIGURATION . " where configuration_key = 'DB_LAST_RESTORE'");
      new xenQuery("insert into " . TABLE_CONFIGURATION . " (configuration_key,configuration_value,configuration_group_id) values ('DB_LAST_RESTORE', '" . $read_from . "','6')");

          if ($remove_raw) {
            unlink($restore_from);
          }
        }

        $messageStack->add_session(SUCCESS_DATABASE_RESTORED, 'success');
        xarRedirectResponse(xarModURL('commerce','admin',(FILENAME_BACKUP));
        break;
      case 'download':
        $extension = substr($_GET['file'], -3);
        if ( ($extension == 'zip') || ($extension == '.gz') || ($extension == 'sql') ) {
          if ($fp = fopen(DIR_FS_BACKUP . $_GET['file'], 'rb')) {
            $buffer = fread($fp, filesize(DIR_FS_BACKUP . $_GET['file']));
            fclose($fp);
            header('Content-type: application/x-octet-stream');
            header('Content-disposition: attachment; filename=' . $_GET['file']);
            echo $buffer;
            exit;
          }
        } else {
          $messageStack->add(ERROR_DOWNLOAD_LINK_NOT_ACCEPTABLE, 'error');
        }
        break;
      case 'deleteconfirm':
        if (strstr($_GET['file'], '..')) xarRedirectResponse(xarModURL('commerce','admin',(FILENAME_BACKUP));

        xtc_remove(DIR_FS_BACKUP . '/' . $_GET['file']);
        if (!$xtc_remove_error) {
          $messageStack->add_session(SUCCESS_BACKUP_DELETED, 'success');
          xarRedirectResponse(xarModURL('commerce','admin',(FILENAME_BACKUP));
        }
        break;
    }
  }

// check if the backup directory exists
  $dir_ok = false;
  if (is_dir(DIR_FS_BACKUP)) {
    $dir_ok = true;
    if (!is_writeable(DIR_FS_BACKUP)) $messageStack->add(ERROR_BACKUP_DIRECTORY_NOT_WRITEABLE, 'error');
  } else {
    $messageStack->add(ERROR_BACKUP_DIRECTORY_DOES_NOT_EXIST, 'error');
  }


  if ($dir_ok) {
    $dir = dir(DIR_FS_BACKUP);
    $contents = array();
    $exts = array("sql");
    while ($file = $dir->read()) {
      if (!is_dir(DIR_FS_BACKUP . $file)) {
      foreach ($exts as $value) {
      if (xtc_CheckExt($file, $value)) {

        $contents[] = $file;
        }
        }
      }
    }
    sort($contents);

    for ($files = 0, $count = sizeof($contents); $files < $count; $files++) {
      $entry = $contents[$files];

      $check = 0;

      if (((!$_GET['file']) || ($_GET['file'] == $entry)) && (!$buInfo) && ($_GET['action'] != 'backup') && ($_GET['action'] != 'restorelocal')) {
        $file_array['file'] = $entry;
        $file_array['date'] = date(PHP_DATE_TIME_FORMAT, filemtime(DIR_FS_BACKUP . $entry));
        $file_array['size'] = number_format(filesize(DIR_FS_BACKUP . $entry)) . ' bytes';
        switch (substr($entry, -3)) {
          case 'zip': $file_array['compression'] = 'ZIP'; break;
          case '.gz': $file_array['compression'] = 'GZIP'; break;
          default: $file_array['compression'] = TEXT_NO_EXTENSION; break;
        }

        $buInfo = new objectInfo($file_array);
      }

      if (is_object($buInfo) && ($entry == $buInfo->file)) {
        echo '              <tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'hand\'">' . "\n";
        $onclick_link = 'file=' . $buInfo->file . '&action=restore';
      } else {
        echo '              <tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'hand\'" onmouseout="this.className=\'dataTableRow\'">' . "\n";
        $onclick_link = 'file=' . $entry;
      }
?>
                <td class="dataTableContent" onclick="document.location.href='<?php echo xarModURL('commerce','admin',(FILENAME_BACKUP, $onclick_link); ?>'"><?php echo '<a href="' . xarModURL('commerce','admin',(FILENAME_BACKUP, 'action=download&file=' . $entry) . '">' . xtc_image(xarTplGetImage('icons/file_download.gif'), ICON_FILE_DOWNLOAD) . '</a>&nbsp;' . $entry; ?></td>
                <td class="dataTableContent" align="center" onclick="document.location.href='<?php echo xarModURL('commerce','admin',(FILENAME_BACKUP, $onclick_link); ?>'"><?php echo date(PHP_DATE_TIME_FORMAT, filemtime(DIR_FS_BACKUP . $entry)); ?></td>
                <td class="dataTableContent" align="right" onclick="document.location.href='<?php echo xarModURL('commerce','admin',(FILENAME_BACKUP, $onclick_link); ?>'"><?php echo number_format(filesize(DIR_FS_BACKUP . $entry)); ?> bytes</td>
                <td class="dataTableContent" align="right"><?php if ( (is_object($buInfo)) && ($entry == $buInfo->file) ) { echo xtc_image(xarTplGetImage(DIR_WS_IMAGES . 'icon_arrow_right.gif'), ''); } else { echo '<a href="' . xarModURL('commerce','admin',(FILENAME_BACKUP, 'file=' . $entry) . '">' . xtc_image(xarTplGetImage(DIR_WS_IMAGES . 'icon_info.gif'), IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
    }
    $dir->close();
  }
?>
              <tr>
                <td class="smallText" colspan="3"><?php echo TEXT_BACKUP_DIRECTORY . ' ' . DIR_FS_BACKUP; ?></td>
                <td align="right" class="smallText"><?php if ( ($_GET['action'] != 'backup') && ($dir) ) echo '<a href="' . xarModURL('commerce','admin',(FILENAME_BACKUP, 'action=backup') . '">' . xarModAPIFunc('commerce','user','image',array('src' => xarTplGetImage('buttons/' . xarSessionGetVar('language') . '/'.'button_backup.gif'),'alt' => IMAGE_BACKUP); . '</a>'; if ( ($_GET['action'] != 'restorelocal') && ($dir) ) echo '&nbsp;&nbsp;<a href="' . xarModURL('commerce','admin',(FILENAME_BACKUP, 'action=restorelocal') . '">' .
  xarModAPIFunc('commerce','user','image',array('src' => xarTplGetImage('buttons/' . xarSessionGetVar('language') . '/'.'button_restore.gif'),'alt' => IMAGE_RESTORE);
                </a>'; ?></td>
              </tr>
<?php
  if (defined('DB_LAST_RESTORE')) {
?>
              <tr>
                <td class="smallText" colspan="4"><?php echo TEXT_LAST_RESTORATION . ' ' . DB_LAST_RESTORE . ' <a href="' . xarModURL('commerce','admin',(FILENAME_BACKUP, 'action=forget') . '">' . TEXT_FORGET . '</a>'; ?></td>
              </tr>
<?php
  }
?>
            </table></td>
<?php
  $heading = array();
  $contents = array();
  switch ($_GET['action']) {
    case 'backup':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_NEW_BACKUP . '</b>');

      $contents = array('form' => xtc_draw_form('backup', FILENAME_BACKUP, 'action=backupnow'));
      $contents[] = array('text' => TEXT_INFO_NEW_BACKUP);

      if ($messageStack->size > 0) {
        $contents[] = array('text' => '<br>' . xtc_draw_radio_field('compress', 'no', true) . ' ' . TEXT_INFO_USE_NO_COMPRESSION);
        $contents[] = array('text' => '<br>' . xtc_draw_radio_field('download', 'yes', true) . ' ' . TEXT_INFO_DOWNLOAD_ONLY . '*<br><br>*' . TEXT_INFO_BEST_THROUGH_HTTPS);
      } else {
        $contents[] = array('text' => '<br>' . xtc_draw_radio_field('compress', 'gzip', true) . ' ' . TEXT_INFO_USE_GZIP);
        $contents[] = array('text' => xtc_draw_radio_field('compress', 'zip') . ' ' . TEXT_INFO_USE_ZIP);
        $contents[] = array('text' => xtc_draw_radio_field('compress', 'no') . ' ' . TEXT_INFO_USE_NO_COMPRESSION);
        $contents[] = array('text' => '<br>' . xtc_draw_checkbox_field('download', 'yes') . ' ' . TEXT_INFO_DOWNLOAD_ONLY . '*<br><br>*' . TEXT_INFO_BEST_THROUGH_HTTPS);
      }

      $contents[] = array('align' => 'center', 'text' => '<br>' .
    <input type="image" src="#xarTplGetImage('buttons/' . xarSessionGetVar('language') . '/'.'button_backup.gif')#" border="0" alt=IMAGE_BACKUP>
      . '&nbsp;<a href="' . xarModURL('commerce','admin',(FILENAME_BACKUP) . '">' . xarModAPIFunc('commerce','user','image',array('src' => xarTplGetImage('buttons/' . xarSessionGetVar('language') . '/'.'button_cancel.gif'),'alt' => IMAGE_CANCEL); . '</a>');
      break;
    case 'restore':
      $heading[] = array('text' => '<b>' . $buInfo->date . '</b>');

      $contents[] = array('text' => xarModAPIFunc('commerce','user','break_string',array('string' => sprintf(TEXT_INFO_RESTORE, DIR_FS_BACKUP . (($buInfo->compression != TEXT_NO_EXTENSION) ? substr($buInfo->file, 0, strrpos($buInfo->file, '.')) : $buInfo->file), ($buInfo->compression != TEXT_NO_EXTENSION) ? TEXT_INFO_UNPACK : ''),'length' => 35));
      $contents[] = array('align' => 'center', 'text' => '<br><a href="' . xarModURL('commerce','admin',(FILENAME_BACKUP, 'file=' . $buInfo->file . '&action=restorenow') . '">' .
  xarModAPIFunc('commerce','user','image',array('src' => xarTplGetImage('buttons/' . xarSessionGetVar('language') . '/'.'button_restore.gif'),'alt' => IMAGE_RESTORE);
      </a>&nbsp;<a href="' . xarModURL('commerce','admin',(FILENAME_BACKUP, 'file=' . $buInfo->file) . '">' . xarModAPIFunc('commerce','user','image',array('src' => xarTplGetImage('buttons/' . xarSessionGetVar('language') . '/'.'button_cancel.gif'),'alt' => IMAGE_CANCEL); . '</a>');
      break;
    case 'restorelocal':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_RESTORE_LOCAL . '</b>');

      $contents = array('form' => xtc_draw_form('restore', FILENAME_BACKUP, 'action=restorelocalnow', 'post', 'enctype="multipart/form-data"'));
      $contents[] = array('text' => TEXT_INFO_RESTORE_LOCAL . '<br><br>' . TEXT_INFO_BEST_THROUGH_HTTPS);
      $contents[] = array('text' => '<br>' . xtc_draw_file_field('sql_file'));
      $contents[] = array('text' => TEXT_INFO_RESTORE_LOCAL_RAW_FILE);
      $contents[] = array('align' => 'center', 'text' => '<br>' .
    <input type="image" src="#xarTplGetImage('buttons/' . xarSessionGetVar('language') . '/'.'button_restore.gif')#" border="0" alt=IMAGE_RESTORE>.
      &nbsp;<a href="' . xarModURL('commerce','admin',(FILENAME_BACKUP) . '">' . xarModAPIFunc('commerce','user','image',array('src' => xarTplGetImage('buttons/' . xarSessionGetVar('language') . '/'.'button_cancel.gif'),'alt' => IMAGE_CANCEL); . '</a>');
      break;
    case 'delete':
      $heading[] = array('text' => '<b>' . $buInfo->date . '</b>');

      $contents = array('form' => xtc_draw_form('delete', FILENAME_BACKUP, 'file=' . $buInfo->file . '&action=deleteconfirm'));
      $contents[] = array('text' => TEXT_DELETE_INTRO);
      $contents[] = array('text' => '<br><b>' . $buInfo->file . '</b>');
      $contents[] = array('align' => 'center', 'text' => '<br>' . <input type="image" src="#xarTplGetImage('buttons/' . xarSessionGetVar('language') . '/'.'button_delete.gif')#" border="0" alt=IMAGE_DELETE> . ' <a href="' . xarModURL('commerce','admin',(FILENAME_BACKUP, 'file=' . $buInfo->file) . '">' . xarModAPIFunc('commerce','user','image',array('src' => xarTplGetImage('buttons/' . xarSessionGetVar('language') . '/'.'button_cancel.gif'),'alt' => IMAGE_CANCEL); . '</a>');
      break;
    default:
      if (is_object($buInfo)) {
        $heading[] = array('text' => '<b>' . $buInfo->date . '</b>');

        $contents[] = array('align' => 'center', 'text' => '<a href="' . xarModURL('commerce','admin',(FILENAME_BACKUP, 'file=' . $buInfo->file . '&action=restore') . '">' . xarModAPIFunc('commerce','user','image',array('src' => xarTplGetImage('buttons/' . xarSessionGetVar('language') . '/'.'button_restore.gif'),'alt' => IMAGE_RESTORE) . '</a> <a href="' . xarModURL('commerce','admin',(FILENAME_BACKUP, 'file=' . $buInfo->file . '&action=delete') . '">' . xarModAPIFunc('commerce','user','image',array('src' => xarTplGetImage('buttons/' . xarSessionGetVar('language') . '/'.'button_delete.gif'),'alt' => IMAGE_DELETE); . '</a>');
        $contents[] = array('text' => '<br>' . TEXT_INFO_DATE . ' ' . $buInfo->date);
        $contents[] = array('text' => TEXT_INFO_SIZE . ' ' . $buInfo->size);
        $contents[] = array('text' => '<br>' . TEXT_INFO_COMPRESSION . ' ' . $buInfo->compression);
      }
      break;
  }

  if ( (xarModAPIFunc('commerce','user','not_null',array('arg' => $heading))) && (xarModAPIFunc('commerce','user','not_null',array('arg' => $contents))) ) {
    echo '            <td width="25%" valign="top">' . "\n";

    $box = new box;
    echo $box->infoBox($heading, $contents);

    echo '            </td>' . "\n";
  }
}
?>