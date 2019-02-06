<?php
/**
 * This file is part of the ZenCart add-on Book X which
 * introduces a new product type for books to the Zen Cart
 * shop system. Tested for compatibility on ZC v. 1.5
 *
 * For latest version and support visit:
 * https://sourceforge.net/p/zencartbookx
 *
 * @package admin
 * @author  Philou
 * @copyright Copyright 2013
 * @copyright Portions Copyright 2003-2011 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.gnu.org/licenses/gpl.txt GNU General Public License V2.0
 *
 * @version BookX V 0.9.4-revision8 BETA
 * @version $Id: [admin]/bookx_author_types.php 2016-02-02 philou $
 */
/**
 * Product Type Book (BookX) Author Types
 *
 * This file handles creating, editing and deleting
 * author type infos
 *
 */
require('includes/application_top.php');

$action = (isset($_GET['action']) ? $_GET['action'] : '');

$languages = zen_get_languages();

if (zen_not_null($action)) {
  switch ($action) {
    case 'insert':
    case 'save':
      if (isset($_GET['mID'])) {
        $type_id = zen_db_prepare_input($_GET['mID']);
      }
      $type_sort_order = zen_db_prepare_input($_POST['type_sort_order']);

      $sql_data_array = array('type_sort_order' => (int)$type_sort_order); // , 'last_modified' => 'now()'

      if ($action == 'insert') {
        /* $insert_sql_data = array('date_added' => 'now()');

          $sql_data_array = array_merge($sql_data_array, $insert_sql_data); */

        zen_db_perform(TABLE_PRODUCT_BOOKX_AUTHOR_TYPES, $sql_data_array);
        $type_id = $db->insert_ID();
      } elseif ($action == 'save') {
        /* $update_sql_data = array('last_modified' => 'now()');

          $sql_data_array = array_merge($sql_data_array, $update_sql_data); */

        zen_db_perform(TABLE_PRODUCT_BOOKX_AUTHOR_TYPES, $sql_data_array, 'update', "bookx_author_type_id = " . (int)$type_id);
      }

      $type_description_array = $_POST['type_description'];
      $type_image_manual_array = $_POST['type_image_manual'];
      for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
        $language_id = $languages[$i]['id'];
        $type_image_name = null;

        if ($type_image_manual_array[$language_id] != '') {
          // add image manually
          $type_image_name = zen_db_input($_POST['img_dir'] . $type_image_manual_array[$language_id]);
          /* $db->Execute("update " . TABLE_PRODUCT_BOOKX_AUTHOR_TYPES . "
            set type_image = '" .  $type_image_name . "'
            where bookx_author_type_id = '" . (int)$type_id . "'"); */
        } else {
          $type_image = new upload('type_image-' . $language_id);
          $type_image->set_destination(DIR_FS_CATALOG_IMAGES . $_POST['img_dir']);
          if ($type_image->parse() && $type_image->save()) {
            // remove image from database if none
            if ($type_image->filename != 'none') {
              // remove image from database if none
              $type_image_name = zen_db_input($_POST['img_dir'] . $type_image->filename);
            }
          }
        }


        $sql_data_array = [
          'type_description' => zen_db_prepare_input($type_description_array[$language_id]),
          'type_image' => $type_image_name];

        if ($action == 'insert' || ($action == 'save' && null === bookx_get_author_type_description($type_id, $language_id))) {
          $insert_sql_data = [
            'bookx_author_type_id' => $type_id,
            'languages_id' => $language_id];

          $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

          zen_db_perform(TABLE_PRODUCT_BOOKX_AUTHOR_TYPES_DESCRIPTION, $sql_data_array);
        } elseif ($action == 'save') {
          zen_db_perform(TABLE_PRODUCT_BOOKX_AUTHOR_TYPES_DESCRIPTION, $sql_data_array, 'update', "bookx_author_type_id = " . (int)$type_id . " and languages_id = " . (int)$language_id);
        }
      }

      zen_redirect(zen_href_link(FILENAME_BOOKX_AUTHOR_TYPES, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . 'mID=' . $type_id));
      break;
    case 'deleteconfirm':
      $type_id = zen_db_prepare_input($_POST['mID']);

      if (isset($_POST['delete_image']) && ($_POST['delete_image'] == 'on')) {
        $types = $db->Execute("SELECT type_image
                               FROM " . TABLE_PRODUCT_BOOKX_AUTHOR_TYPES_DESCRIPTION . "
                               WHERE bookx_author_type_id = " . (int)$type_id);

        foreach ($types as $type) {
          $image_location = DIR_FS_CATALOG_IMAGES . $type['type_image'];

          if (file_exists($image_location))
            @unlink($image_location);
        }
      }

      $db->Execute("DELETE FROM " . TABLE_PRODUCT_BOOKX_AUTHOR_TYPES . "
                    WHERE bookx_author_type_id = " . (int)$type_id);
      $db->Execute("DELETE FROM " . TABLE_PRODUCT_BOOKX_AUTHOR_TYPES_DESCRIPTION . "
                    WHERE bookx_author_type_id = " . (int)$type_id);

      $db->Execute("UPDATE " . TABLE_PRODUCT_BOOKX_AUTHORS . "
                    SET author_default_type = NULL
                    WHERE author_default_type = " . (int)$type_id);


      if (isset($_POST['delete_authortype_products']) && ($_POST['delete_authortype_products'] == 'on')) {
        $products = $db->Execute("SELECT p.products_id
                                  FROM " . TABLE_PRODUCT_BOOKX_EXTRA . " p
                                  LEFT JOIN " . TABLE_PRODUCT_BOOKX_AUTHORS_TO_PRODUCTS . " pta ON pta.products_id = p.products_id
                                  WHERE pta.bookx_author_type_id = " . (int)$type_id);

        foreach ($products as $product) {
          bookx_delete_product((int)$product['products_id']);
        }
      }

      if (isset($_POST['setnull_authortype_authors']) && ($_POST['setnull_authortype_authors'] == 'on')) {
        $db->Execute("DELETE FROM " . TABLE_PRODUCT_BOOKX_AUTHORS_TO_PRODUCTS . "
                      WHERE bookx_author_type_id = " . (int)$type_id);
      } else {
        $db->Execute("UPDATE " . TABLE_PRODUCT_BOOKX_AUTHORS_TO_PRODUCTS . " SET bookx_author_type_id = null
                      WHERE bookx_author_type_id = " . (int)$type_id);
      }

      zen_redirect(zen_href_link(FILENAME_BOOKX_AUTHOR_TYPES, 'page=' . $_GET['page']));
      break;
  }
}
?>
<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <meta charset="<?php echo CHARSET; ?>">
    <title><?php echo TITLE; ?></title>
    <link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
    <link rel="stylesheet" type="text/css" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
    <script language="javascript" src="includes/menu.js"></script>
    <script language="javascript" src="includes/general.js"></script>
    <script>
      function init() {
          cssjsmenu('navbar');
          if (document.getElementById) {
              var kill = document.getElementById('hoverJS');
              kill.disabled = true;
          }
      }
    </script>
    <?php
    if ($editor_handler != '') {
      include ($editor_handler);
    }
    ?>
  </head>
  <body onLoad="init()">
    <!-- header //-->
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
    <!-- header_eof //-->

    <!-- body //-->
    <div class="container-fluid" id="pageWrapper">

      <h1><?php echo HEADING_TITLE ?></h1>
      <div class="row">
        <!-- body_text //-->
        <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9 configurationColumnLeft">
          <table class="table table-striped table-hover">
            <thead>
              <tr class="dataTableHeadingRow">
                <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_AUTHOR_TYPE; ?></th>
                <th class="dataTableHeadingContent"><?php echo TABLE_HEADING_SORT_ORDER; ?></th>
                <th class="dataTableHeadingContent text-right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</th>
              </tr>
            </thead>
            <tbody>
                <?php
                $type_query_raw = "SELECT at.*, atd.type_description, atd.type_image
                                   FROM " . TABLE_PRODUCT_BOOKX_AUTHOR_TYPES . " at
                                   LEFT JOIN " . TABLE_PRODUCT_BOOKX_AUTHOR_TYPES_DESCRIPTION . " atd ON atd.bookx_author_type_id = at.bookx_author_type_id
                                     AND atd.languages_id = " . (int)$_SESSION['languages_id'] . "
                                   ORDER BY at.type_sort_order, atd.type_description";
                $type_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $type_query_raw, $type_query_numrows);
                $type = $db->Execute($type_query_raw);

                foreach ($type as $item) {

                  if ((!isset($_GET['mID']) || (isset($_GET['mID']) && ($_GET['mID'] == $item['bookx_author_type_id']))) && !isset($aInfo) && (substr($action, 0, 3) != 'new')) {
                    $type_products = $db->Execute("SELECT COUNT(p.products_id) AS products_of_atype_count
                                                   FROM " . TABLE_PRODUCT_BOOKX_EXTRA . " p
                                                   LEFT JOIN " . TABLE_PRODUCT_BOOKX_AUTHORS_TO_PRODUCTS . " pta ON pta.products_id = p.products_id
                                                   WHERE pta.bookx_author_type_id = " . (int)$item['bookx_author_type_id']);

                    $type_authors = $db->Execute("SELECT COUNT(DISTINCT(pta.bookx_author_id)) AS authors_of_atype_count
                                                  FROM " . TABLE_PRODUCT_BOOKX_EXTRA . " p
                                                  LEFT JOIN " . TABLE_PRODUCT_BOOKX_AUTHORS_TO_PRODUCTS . " pta ON pta.products_id = p.products_id
                                                  WHERE pta.bookx_author_type_id = " . (int)$item['bookx_author_type_id']);
                    $aInfo_array = array_merge($item, $type_products->fields);
                    $aInfo = new objectInfo($aInfo_array);
                  }

                  if (isset($aInfo) && is_object($aInfo) && ($item['bookx_author_type_id'] == $aInfo->bookx_author_type_id)) {
                    ?>
                  <tr id="defaultSelected" class="dataTableRowSelected" onclick="document.location.href = '<?php echo zen_href_link(FILENAME_BOOKX_AUTHOR_TYPES, 'page=' . $_GET['page'] . '&mID=' . $item['bookx_author_type_id'] . '&action=edit'); ?>'">
                    <?php } else { ?>
                  <tr class="dataTableRow" onclick="document.location.href = '<?php echo zen_href_link(FILENAME_BOOKX_AUTHOR_TYPES, 'page=' . $_GET['page'] . '&mID=' . $item['bookx_author_type_id'] . '&action=edit'); ?>'">
                    <?php } ?>
                  <td class="dataTableContent"><?php echo $item['type_description']; ?></td>
                  <td class="dataTableContent"><?php echo $item['type_sort_order']; ?></td>
                  <td class="dataTableContent text-right">
                    <a href="<?php echo zen_href_link(FILENAME_BOOKX_AUTHOR_TYPES, 'page=' . $_GET['page'] . '&mID=' . $item['bookx_author_type_id'] . '&action=edit'); ?>"><?php echo zen_image(DIR_WS_IMAGES . 'icon_edit.gif', ICON_EDIT); ?></a>
                    <a href="<?php echo zen_href_link(FILENAME_BOOKX_AUTHOR_TYPES, 'page=' . $_GET['page'] . '&mID=' . $item['bookx_author_type_id'] . '&action=delete'); ?>"><?php echo zen_image(DIR_WS_IMAGES . 'icon_delete.gif', ICON_DELETE); ?></a>
                    <?php if (isset($aInfo) && is_object($aInfo) && ($item['bookx_author_type_id'] == $aInfo->bookx_author_type_id)) { ?>
                      <?php echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); ?>
                    <?php } else { ?>
                      <a href="<?php echo zen_href_link(FILENAME_BOOKX_AUTHOR_TYPES, zen_get_all_get_params(array('mID')) . 'mID=' . $item['bookx_author_type_id']); ?>"><?php echo zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO); ?></a>
                    <?php } ?>
                  </td>
                </tr>
              <?php } ?>
          </table>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3 configurationColumnRight">
            <?php
            $heading = [];
            $contents = [];

            $flag_show_product_bookx_listing_authors_with_type_below_sort_order = bookx_get_show_product_switch('authors_with_type_below_sort_order', 'SHOW_', '_LISTING');

            $sql = "SELECT type_id
                    FROM " . TABLE_PRODUCT_TYPES . "
                    WHERE type_handler = 'product_bookx'";
            $product_type = $db->Execute($sql);

            if ($product_type->RecordCount() > 0) {
              $product_type_boox_id = $product_type->fields['type_id'];
            }

            switch ($action) {
              case 'new':
                $heading[] = array('text' => '<h4>' . TEXT_HEADING_NEW_AUTHOR_TYPE . '</h4>');

                $contents = array('form' => zen_draw_form('type', FILENAME_BOOKX_AUTHOR_TYPES, 'action=insert', 'post', 'enctype="multipart/form-data" class="form-horizontal"'));
                $contents[] = array('text' => TEXT_NEW_INTRO);
                $dir = @dir(DIR_FS_CATALOG_IMAGES);
                $dir_info[] = array('id' => '', 'text' => "Main Directory");
                while ($file = $dir->read()) {
                  if (is_dir(DIR_FS_CATALOG_IMAGES . $file) && strtoupper($file) != 'CVS' && $file != "." && $file != "..") {
                    $dir_info[] = array('id' => $file . '/', 'text' => $file);
                  }
                }
                $dir->close();

                $default_directory = 'bookx_types/';

                $type_image_fields = '';
                $type_manual_image_fields = '';
                $type_description_textareas = '';
                for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                  $language_image = zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']);
                  $type_image_fields .= '<div class="input-group"><span class="input-group-addon">' . $language_image . '</span>' . zen_draw_file_field('type_image-' . $languages[$i]['id'], '', 'class="form-control"') . '</div><br>';
                  $type_manual_image_fields .= '<div class="input-group"><span class="input-group-addon">' . $language_image . '</span>' . zen_draw_input_field('type_image_manual[' . $languages[$i]['id'] . ']', '','class="form-control" ' . zen_set_field_length(TABLE_PRODUCT_BOOKX_AUTHOR_TYPES_DESCRIPTION, 'type_image')) . '</div><br>';
                  $type_description_textareas .= '<div class="input-group"><span class="input-group-addon">' . $language_image . '</span>' . zen_draw_input_field('type_description[' . $languages[$i]['id'] . ']', '','class="form-control" ' . zen_set_field_length(TABLE_PRODUCT_BOOKX_AUTHOR_TYPES_DESCRIPTION, 'type_description')) . '</div><br>';
                }
                $contents[] = array('text' => '<br>' . TEXT_AUTHOR_TYPE_DESCRIPTION . $type_description_textareas);

                $contents[] = array('text' => '<br>' . TEXT_AUTHOR_TYPE_IMAGE_DIR . zen_draw_pull_down_menu('img_dir', $dir_info, $default_directory, 'class="form-control"'));

                $contents[] = array('text' => '<br>' . TEXT_AUTHOR_TYPE_IMAGE . '<br>' . $type_image_fields);
                $contents[] = array('text' => '<br>' . TEXT_AUTHOR_TYPE_IMAGE_MANUAL . '&nbsp;' . $type_manual_image_fields);

                $contents[] = array('text' => '<br>' . TEXT_AUTHOR_TYPE_SORT_ORDER . '<br>' . zen_draw_input_field('type_sort_order', '', 'class="form-control"')
                  . '<br>' . sprintf(TEXT_AUTHOR_TYPE_SORT_ORDER_INFLUENCES_DISPLAY, $flag_show_product_bookx_listing_authors_with_type_below_sort_order, zen_href_link(FILENAME_PRODUCT_TYPES, 'page=1&ptID=' . $product_type_boox_id . '&action=layout')));

                $contents[] = array('align' => 'text-center', 'text' => '<button type="submit" class="btn btn-primary">' . IMAGE_SAVE . '</button> <a href="' . zen_href_link(FILENAME_BOOKX_AUTHOR_TYPES, 'page=' . $_GET['page'] . '&mID=' . $_GET['mID']) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');
                break;
              case 'edit':
                $heading[] = array('text' => '<h4>' . TEXT_HEADING_EDIT_AUTHOR_TYPE . '</h4>');

                $contents = array('form' => zen_draw_form('type', FILENAME_BOOKX_AUTHOR_TYPES, 'page=' . $_GET['page'] . '&mID=' . $aInfo->bookx_author_type_id . '&action=save', 'post', 'enctype="multipart/form-data" class="form-horizontal"'));
                $contents[] = array('text' => TEXT_EDIT_INTRO);
                //$contents[] = array('text' => '<br>' . TEXT_AUTHOR_TYPE_IMAGE . '<br>' . zen_draw_file_field('type_image') . '<br>' . $aInfo->type_image);
                $dir = @dir(DIR_FS_CATALOG_IMAGES);
                $dir_info[] = array('id' => '', 'text' => "Main Directory");
                while ($file = $dir->read()) {
                  if (is_dir(DIR_FS_CATALOG_IMAGES . $file) && strtoupper($file) != 'CVS' && $file != "." && $file != "..") {
                    $dir_info[] = array('id' => $file . '/', 'text' => $file);
                  }
                }
                $dir->close();
                $default_directory = substr($type->fields['type_image'], 0, strpos($type->fields['type_image'], '/') + 1);
                if (!$default_directory) {
                  $default_directory = 'bookx_types/';
                }

                $type_image_fields = '';
                $type_manual_image_fields = '';
                $type_description_textareas = '';
                for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                  $language_image = zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']);
                  $type_description = bookx_get_author_type_description($aInfo->bookx_author_type_id, $languages[$i]['id']);
                  $type_image_fields .= '<div class="input-group"><span class="input-group-addon">' . $language_image . '</span>' . zen_draw_file_field('type_image-' . $languages[$i]['id'], '', 'class="form-control"') . '</div><br>';

                  $type_manual_image_url = bookx_get_author_type_image_url($aInfo->bookx_author_type_id, $languages[$i]['id']);
                  $type_manual_image_fields .= '<div class="input-group"><span class="input-group-addon">' . $language_image . '</span>' . zen_draw_input_field('type_image_manual[' . $languages[$i]['id'] . ']', $type_manual_image_url, 'class="form-control" ' . zen_set_field_length(TABLE_PRODUCT_BOOKX_AUTHOR_TYPES_DESCRIPTION, 'type_image')) . '</div><br>';
                  $type_manual_image_fields .= '<br>' . (null != $type_manual_image_url && '' != $type_manual_image_url ? zen_info_image($type_manual_image_url, $type_description) : TEXT_AUTHOR_TYPE_IMAGE_NOT_DEFINED);
                  $type_description_textareas .= '<div class="input-group"><span class="input-group-addon">' . $language_image . '</span>' . zen_draw_input_field('type_description[' . $languages[$i]['id'] . ']', $type_description, 'class="form-control" ' .  zen_set_field_length(TABLE_PRODUCT_BOOKX_AUTHOR_TYPES_DESCRIPTION, 'type_description')) . '</div><br>';
                }
                $contents[] = array('text' => '<br>' . TEXT_AUTHOR_TYPE_DESCRIPTION . $type_description_textareas);

                $contents[] = array('text' => '<br>' . TEXT_AUTHOR_TYPE_IMAGE_DIR . zen_draw_pull_down_menu('img_dir', $dir_info, $default_directory, 'class="form-control"'));
                $contents[] = array('text' => '<br>' . TEXT_AUTHOR_TYPE_IMAGE . '<br>' . $type_image_fields);
                $contents[] = array('text' => '<br>' . TEXT_AUTHOR_TYPE_IMAGE_MANUAL . '&nbsp;' . $type_manual_image_fields);
                // $contents[] = array('text' => '<br>' . (null != $aInfo->type_image && '' != $aInfo->type_image ? zen_info_image($aInfo->type_image, $aInfo->type_description) : TEXT_AUTHOR_TYPE_IMAGE_NOT_DEFINED));



                $contents[] = array('text' => '<br>' . TEXT_AUTHOR_TYPE_SORT_ORDER . '<br>' . zen_draw_input_field('type_sort_order', $aInfo->type_sort_order, 'class="form-control"')
                  . '<br>' . sprintf(TEXT_AUTHOR_TYPE_SORT_ORDER_INFLUENCES_DISPLAY, $flag_show_product_bookx_listing_authors_with_type_below_sort_order, zen_href_link(FILENAME_PRODUCT_TYPES, 'page=1&ptID=' . $product_type_boox_id . '&action=layout')));

                $contents[] = array('align' => 'text-center', 'text' => '<button type="submit" class="btn btn-primary">' . IMAGE_SAVE . '</button> <a href="' . zen_href_link(FILENAME_BOOKX_AUTHOR_TYPES, 'page=' . $_GET['page'] . '&mID=' . $aInfo->bookx_author_type_id) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');
                break;
              case 'delete':
                $heading[] = array('text' => '<h4>' . TEXT_HEADING_DELETE_AUTHOR_TYPE . '</h4>');

                $contents = array('form' => zen_draw_form('type', FILENAME_BOOKX_AUTHOR_TYPES, 'page=' . $_GET['page'] . '&action=deleteconfirm') . zen_draw_hidden_field('mID', $aInfo->bookx_author_type_id));
                $contents[] = array('text' => sprintf(TEXT_DELETE_INTRO, $aInfo->type_description));
                $contents[] = array('text' => '<br><b>' . $aInfo->type_description . '</b>');
                $contents[] = array('text' => '<div class="checkbox"><label>' . zen_draw_checkbox_field('delete_image', '', true) . TEXT_DELETE_IMAGE . '</label></div>');

                if ($aInfo->products_of_atype_count > 0) {
                  $contents[] = array('text' => '<br>' . sprintf(TEXT_DELETE_WARNING_PRODUCTS, $aInfo->products_of_atype_count, $aInfo->type_description));

                  $contents[] = array('text' => '<div class="checkbox"><label>' . zen_draw_checkbox_field('setnull_authortype_authors') . sprintf(TEXT_SETNULL_AUTHORTYPE_AUTHORS, $aInfo->type_description) . '</label></div>');
                  $contents[] = array('text' => '<div class="checkbox"><label>' . zen_draw_checkbox_field('delete_authortype_products') . sprintf(TEXT_DELETE_AUTHORTYPE_PRODUCTS, $aInfo->type_description) . '</label></div>');
                }

                $contents[] = array('align' => 'text-center', 'text' => '<button type="submit" class="btn btn-danger">' . IMAGE_DELETE . '</button> <a href="' . zen_href_link(FILENAME_BOOKX_AUTHOR_TYPES, 'page=' . $_GET['page'] . '&mID=' . $aInfo->bookx_author_type_id) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');
                break;
              default:
                if (isset($aInfo) && is_object($aInfo)) {
                  $heading[] = array('text' => '<h4>' . $aInfo->type_description . '</h4>');

                  $contents[] = array('align' => 'text-center', 'text' => '<a href="' . zen_href_link(FILENAME_BOOKX_AUTHOR_TYPES, 'page=' . $_GET['page'] . '&mID=' . $aInfo->bookx_author_type_id . '&action=edit') . '" class="btn btn-primary" role="button">' . IMAGE_EDIT . '</a> <a href="' . zen_href_link(FILENAME_BOOKX_AUTHOR_TYPES, 'page=' . $_GET['page'] . '&mID=' . $aInfo->bookx_author_type_id . '&action=delete') . '" class="btn btn-warning" role="button">' . IMAGE_DELETE . '</a>');
                  /* $contents[] = array('text' => '<br>' . TEXT_DATE_ADDED . ' ' . zen_date_short($aInfo->date_added));
                    if (zen_not_null($aInfo->last_modified)) $contents[] = array('text' => TEXT_LAST_MODIFIED . ' ' . zen_date_short($aInfo->last_modified)); */
                  $contents[] = array('text' => '<br>' . zen_info_image($aInfo->type_image, $aInfo->type_description));
                  $contents[] = array('text' => '<br>' . TEXT_PRODUCTS . ' ' . $aInfo->products_of_atype_count);
                }
                break;
            }

            if ((zen_not_null($heading)) && (zen_not_null($contents))) {
              $box = new box;
              echo $box->infoBox($heading, $contents);
            }
            ?>
        </div>
      </div>
      <div class="row">
        <table class="table">
          <tr>
            <td><?php echo $type_split->display_count($type_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_AUTHOR_TYPES); ?></td>
            <td class="text-right"><?php echo $type_split->display_links($type_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></td>
          </tr>
          <?php if (empty($action)) { ?>
            <tr>
              <td colspan="2" class="text-right"><a href="<?php echo zen_href_link(FILENAME_BOOKX_AUTHOR_TYPES, 'page=' . $_GET['page'] . '&mID=' . $aInfo->bookx_author_type_id . '&action=new'); ?>" class="btn btn-primary" role="button"><?php echo IMAGE_INSERT; ?></a></td>
            </tr>
          <?php } ?>
        </table>
      </div>
      <!-- body_text_eof //-->
    </div>
    <!-- body_eof //-->

    <!-- footer //-->
    <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
    <!-- footer_eof //-->
  </body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
