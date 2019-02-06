<?php

/**
 * This file is part of the ZenCart add-on Book X which
 * introduces a new product type for books to the Zen Cart
 * shop system. Tested for compatibility on ZC v. 1.5.6
 *
 * For latest version and support visit:
 * https://github.com/philoupin/bookx
 *
 * @package admin
 * @author  Philou
 * @copyright Copyright 2013
 * @copyright Copyright 2003-2019 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: DrByte 2019 Jan 04 Modified in v1.5.6a $
 *
 * @version BookX V 0.9.4-revision8 BETA
 * @version $Id: update_product.php 2018-12-28 mesnitu $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
if (isset($_GET['pID'])) {
  $products_id = zen_db_prepare_input($_GET['pID']);
}
if (isset($_POST['edit_x']) || isset($_POST['edit_y'])) {
  $action = 'new_product';
} elseif ((isset($_POST['products_model']) ? $_POST['products_model'] : '') . (isset($_POST['products_url']) ? implode('', $_POST['products_url']) : '') . (isset($_POST['products_name']) ? implode('', $_POST['products_name']) : '') . (isset($_POST['products_description']) ? implode('', $_POST['products_description']) : '') != '') {
  $products_date_available = zen_db_prepare_input($_POST['products_date_available']);
  $products_date_available = (date('Y-m-d') < $products_date_available) ? $products_date_available : 'null';

  // Data-cleaning to prevent data-type mismatch errors:
  $sql_data_array = array(
    'products_quantity' => convertToFloat($_POST['products_quantity']),
    'products_type' => (int)$_POST['product_type'],
    'products_model' => zen_db_prepare_input($_POST['products_model']),
    'products_price' => convertToFloat($_POST['products_price']),
    'products_date_available' => $products_date_available,
    'products_weight' => convertToFloat($_POST['products_weight']),
    'products_status' => (int)$_POST['products_status'],
    'products_virtual' => (int)$_POST['products_virtual'],
    'products_tax_class_id' => (int)$_POST['products_tax_class_id'],
    'manufacturers_id' => (int)$_POST['manufacturers_id'],
    'products_quantity_order_min' => convertToFloat($_POST['products_quantity_order_min']) == 0 ? 1 : convertToFloat($_POST['products_quantity_order_min']),
    'products_quantity_order_units' => convertToFloat($_POST['products_quantity_order_units']) == 0 ? 1 : convertToFloat($_POST['products_quantity_order_units']),
    'products_priced_by_attribute' => (int)$_POST['products_priced_by_attribute'],
    'product_is_free' => (int)$_POST['product_is_free'],
    'product_is_call' => (int)$_POST['product_is_call'],
    'products_quantity_mixed' => (int)$_POST['products_quantity_mixed'],
    'product_is_always_free_shipping' => (int)$_POST['product_is_always_free_shipping'],
    'products_qty_box_status' => (int)$_POST['products_qty_box_status'],
    'products_quantity_order_max' => convertToFloat($_POST['products_quantity_order_max']),
    'products_sort_order' => (int)$_POST['products_sort_order'],
    'products_discount_type' => (int)$_POST['products_discount_type'],
    'products_discount_type_from' => (int)$_POST['products_discount_type_from'],
    'products_price_sorter' => convertToFloat($_POST['products_price_sorter']),
  );

  $db_filename = zen_limit_image_filename($_POST['products_image'], TABLE_PRODUCTS, 'products_image');
  $sql_data_array['products_image'] = zen_db_prepare_input($db_filename);
  $new_image = 'true';

  // when set to none remove from database
  // is out dated for browsers use radio only
  if ($_POST['image_delete'] == 1) {
    $sql_data_array['products_image'] = '';
    $new_image = 'false';
  }

  if ($action == 'insert_product') {
    $sql_data_array['products_date_added'] = 'now()';
    $sql_data_array['master_categories_id'] = (int)$current_category_id;

    zen_db_perform(TABLE_PRODUCTS, $sql_data_array);
    $products_id = zen_db_insert_id();

    // reset products_price_sorter for searches etc.
    zen_update_products_price_sorter($products_id);

    $db->Execute("INSERT INTO " . TABLE_PRODUCTS_TO_CATEGORIES . " (products_id, categories_id)
                  VALUES ('" . (int)$products_id . "', '" . (int)$current_category_id . "')");

    zen_record_admin_activity('New product ' . (int)$products_id . ' added via admin console.', 'info');

    ///////////////////////////////////////////////////////
    //// INSERT PRODUCT-TYPE-SPECIFIC *INSERTS* HERE //////

        if(isset($_POST['publishing_date']) && zen_not_null($_POST['publishing_date'])) {
            if($_POST['date_format'] == 'MM yy') {
                $date = DateTime::createFromFormat('F Y', $_POST['publishing_date']);
                $publishing_date = $date->format('Y-m') . '-00';
            }
            elseif($_POST['date_format'] == 'yy' || strlen($_POST['publishing_date']) == 4) {
                $date = DateTime::createFromFormat('Y', $_POST['publishing_date']);
                $publishing_date = $_POST['publishing_date'] . '-00-00';
            }
            else {
                $publishing_date = $_POST['publishing_date'];
            }
        }


        $sql_data_array = array(
            'products_id' => (int) $products_id,
            'bookx_publisher_id' => (int) $_POST['bookx_publisher_id'],
            'bookx_series_id' => (int) $_POST['bookx_series_id'],
            'bookx_imprint_id' => (int) $_POST['bookx_imprint_id'],
            'bookx_binding_id' => (int) $_POST['bookx_binding_id'],
            'bookx_printing_id' => (int) $_POST['bookx_printing_id'],
            'bookx_condition_id' => (int) $_POST['bookx_condition_id'],
            'publishing_date' => bookx_null_check($publishing_date),
            'pages' => bookx_null_check($_POST['pages']),
            'volume' => bookx_null_check($_POST['volume']),
            'size' => bookx_null_check($_POST['size']),
            'isbn' => bookx_null_check(preg_replace('/[^0-9]/', '', $_POST['isbn']))
        );

        zen_db_perform(TABLE_PRODUCT_BOOKX_EXTRA, $sql_data_array);

        if(isset($_POST['bookx_genre_id']) && is_array($_POST['bookx_genre_id'])) {
            foreach ($_POST['bookx_genre_id'] as $array_key => $array_value) {
                $tmp_value = zen_db_prepare_input($array_value);
                $bookx_genre_id = (!zen_not_null($tmp_value) || $tmp_value == '' || $tmp_value === 0) ? null : $tmp_value;

                if($bookx_genre_id) {
                    $sql_data_array = array('products_id' => (int) $products_id,
                        'bookx_genre_id' => (int) $bookx_genre_id);
                    zen_db_perform(TABLE_PRODUCT_BOOKX_GENRES_TO_PRODUCTS, $sql_data_array);
                }
            }
        }

        if(isset($_POST['bookx_author_id']) && is_array($_POST['bookx_author_id'])) {
            foreach ($_POST['bookx_author_id'] as $array_key => $array_value) {
                $tmp_value = zen_db_prepare_input($array_value);
                $bookx_author_id = (!zen_not_null($tmp_value) || $tmp_value == '' || $tmp_value === 0) ? null : $tmp_value;

                $bookx_author_type_id = null;
                if(is_array($_POST['bookx_author_type_id']) && array_key_exists($array_key, $_POST['bookx_author_type_id'])) {
                    $tmp_value = zen_db_prepare_input($_POST['bookx_author_type_id'][$array_key]);
                    $bookx_author_type_id = (!zen_not_null($tmp_value) || $tmp_value == '' || $tmp_value === 0) ? null : (int) $tmp_value;
                }
                if($bookx_author_id) {
                    $sql_data_array = array(
                        'products_id' => (int) $products_id,
                        'bookx_author_id' => (int) $bookx_author_id,
                        'bookx_author_type_id' => (int) $bookx_author_type_id);
                    zen_db_perform(TABLE_PRODUCT_BOOKX_AUTHORS_TO_PRODUCTS, $sql_data_array);
                }
            }
        }

        $languages = zen_get_languages();
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
            $language_id = $languages[$i]['id'];

            $sql_data_array = array('products_subtitle' => bookx_null_check($_POST['products_subtitle'][$language_id])
            );

            if($action == 'insert_product') {
                $insert_sql_data = array('products_id' => (int) $products_id,
                    'languages_id' => (int) $language_id);

                $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

                zen_db_perform(TABLE_PRODUCT_BOOKX_EXTRA_DESCRIPTION, $sql_data_array);
            }
        }
        
        /*
         * @since v1.0.0
         */
        if (isset($_POST['bookx_family_id'])) {
            $objBookxFamily->BookxInsertFamilyProduct((int) $products_id);
        }
        if (BOOKX_APPLY_SPECIALS_UPDATE == true && $_POST['ignore_family_discount'] == 'off') {
            $objBookxFamily->applyFamilyDiscount();
        }


    ////    *END OF PRODUCT-TYPE-SPECIFIC INSERTS* ////////
    ///////////////////////////////////////////////////////
  } elseif ($action == 'update_product') {

        /*
         * @since v1.0.0
         */   
        if (isset($_POST['bookx_family_id']) && isset($_POST['ignore_family_discount'])) {
            $objBookxFamily->BookxUpdateFamilyProduct((int) $products_id);
        }
        if (BOOKX_APPLY_SPECIALS_UPDATE == true && $_POST['ignore_family_discount'] == 'off') {
            $objBookxFamily->applyFamilyDiscount();
        }
        
        
    $sql_data_array['products_last_modified'] = 'now()';
    $sql_data_array['master_categories_id'] = (!empty($_POST['master_category']) && (int)$_POST['master_category'] > 0 ? (int)$_POST['master_category'] : (int)$_POST['master_categories_id']);

    zen_db_perform(TABLE_PRODUCTS, $sql_data_array, 'update', "products_id = " . (int)$products_id);

    zen_record_admin_activity('Updated product ' . (int)$products_id . ' via admin console.', 'info');

    // reset products_price_sorter for searches etc.
    zen_update_products_price_sorter((int)$products_id);

    ///////////////////////////////////////////////////////
    //// INSERT PRODUCT-TYPE-SPECIFIC *UPDATES* HERE //////

        if(isset($_POST['publishing_date']) && zen_not_null($_POST['publishing_date'])) {
            if($_POST['date_format'] == 'MM yy') {
                $date = DateTime::createFromFormat('F Y', $_POST['publishing_date']);
                $publishing_date = $date->format('Y-m') . '-00';
            }
            elseif($_POST['date_format'] == 'yy') {
                $date = DateTime::createFromFormat('Y', $_POST['publishing_date']);
                $publishing_date = $_POST['publishing_date'] . '-00-00';
            }
            else {
                $publishing_date = $_POST['publishing_date'];
            }
        }

        $sql_data_array = array(
            'products_id' => (int) $products_id,
            'bookx_publisher_id' => (int) $_POST['bookx_publisher_id'],
            'bookx_series_id' => (int) $_POST['bookx_series_id'],
            'bookx_imprint_id' => (int) $_POST['bookx_imprint_id'],
            'bookx_binding_id' => (int) $_POST['bookx_binding_id'],
            'bookx_printing_id' => (int) $_POST['bookx_printing_id'],
            'bookx_condition_id' => (int) $_POST['bookx_condition_id'],
            'publishing_date' => bookx_null_check($publishing_date),
            'pages' => bookx_null_check($_POST['pages']),
            'volume' => bookx_null_check($_POST['volume']),
            'size' => bookx_null_check($_POST['size']),
            'isbn' => bookx_null_check(preg_replace('/[^0-9]/', '', $_POST['isbn']))
        );

        zen_db_perform(TABLE_PRODUCT_BOOKX_EXTRA, $sql_data_array, 'update', "products_id = '" . (int) $products_id . "'");

        if(isset($_POST['bookx_genre_id']) && is_array($_POST['bookx_genre_id'])) {
            foreach ($_POST['bookx_genre_id'] as $array_key => $array_value) {
                $tmp_value = zen_db_prepare_input($array_value);
                $bookx_genre_id = (!zen_not_null($tmp_value) || $tmp_value == '' || $tmp_value === 0) ? null : $tmp_value;

                $primary_id = null;
                if(isset($_POST['assigned_genre_db_id']) && is_array($_POST['assigned_genre_db_id']) && array_key_exists($array_key, $_POST['assigned_genre_db_id'])) {
                    $tmp_value = zen_db_prepare_input($_POST['assigned_genre_db_id'][$array_key]);
                    $primary_id = (!zen_not_null($tmp_value) || $tmp_value == '' || $tmp_value === 0) ? null : $tmp_value;
                }

                $gtp_action = 'insert';
                $where_clause = '';
                switch (true) {
                    case (null != $primary_id && null == $bookx_genre_id):
                        //this is an update to an existing genre_to_product table entry, but NO existing genre is assigned -> DELETE table entry
                        $db->Execute('DELETE FROM ' . TABLE_PRODUCT_BOOKX_GENRES_TO_PRODUCTS . ' WHERE primary_id = "' . (int) $primary_id . '" LIMIT 1');
                        break;

                    case (null != $primary_id && null != $bookx_genre_id):
                        //this is an update to an existing genre_to_product table entry and an existing genre is assigned -> UPDATE table entry
                        $gtp_action = 'update';
                        $where_clause = 'primary_id = "' . (int) $primary_id . '"';

                    // we don't break here!

                    case (null == $primary_id && null != $bookx_genre_id):
                        //this is a new genre_to_product table entry and an existing genre is assigned -> INSERT table entry
                        $sql_data_array = array('products_id' => (int) $products_id,
                            'bookx_genre_id' => $bookx_genre_id);

                        zen_db_perform(TABLE_PRODUCT_BOOKX_GENRES_TO_PRODUCTS, $sql_data_array, $gtp_action, $where_clause);
                        break;
                }
            }
        }

        if(isset($_POST['bookx_author_id']) && is_array($_POST['bookx_author_id'])) {
            foreach ($_POST['bookx_author_id'] as $array_key => $array_value) {
                $tmp_value = zen_db_prepare_input($array_value);
                $bookx_author_id = (!zen_not_null($tmp_value) || $tmp_value == '' || $tmp_value === 0) ? null : (int) $tmp_value;

                $bookx_author_type_id = null;
                if(isset($_POST['bookx_author_type_id']) && is_array($_POST['bookx_author_type_id']) && array_key_exists($array_key, $_POST['bookx_author_type_id'])) {
                    $tmp_value = zen_db_prepare_input($_POST['bookx_author_type_id'][$array_key]);
                    $bookx_author_type_id = (!zen_not_null($tmp_value) || $tmp_value == '' || $tmp_value === 0) ? null : (int) $tmp_value;
                }

                $primary_id = null;
                if(isset($_POST['assigned_author_db_id']) && is_array($_POST['assigned_author_db_id']) && array_key_exists($array_key, $_POST['assigned_author_db_id'])) {
                    $tmp_value = zen_db_prepare_input($_POST['assigned_author_db_id'][$array_key]);
                    $primary_id = (!zen_not_null($tmp_value) || $tmp_value == '' || $tmp_value === 0) ? null : $tmp_value;
                }

                $atp_action = 'insert';
                $where_clause = '';
                switch (true) {
                    case (null != $primary_id && null == $bookx_author_id):
                        //this is an update to an existing author_to_product table entry, but NO existing author is assigned -> DELETE table entry
                        $db->Execute('DELETE FROM ' . TABLE_PRODUCT_BOOKX_AUTHORS_TO_PRODUCTS . ' WHERE primary_id = "' . (int) $primary_id . '" LIMIT 1');
                        break;

                    case (null != $primary_id && null != $bookx_author_id):
                        //this is an update to an existing author_to_product table entry and an existing author is assigned -> UPDATE table entry
                        $atp_action = 'update';
                        $where_clause = 'primary_id = "' . (int) $primary_id . '"';

                    // we don't break here!

                    case (null == $primary_id && null != $bookx_author_id):
                        //this is a new author_to_product table entry and an existing author is assigned -> INSERT table entry
                        $sql_data_array = array('products_id' => (int) $products_id,
                            'bookx_author_id' => (int) $bookx_author_id,
                            'bookx_author_type_id' => (int) $bookx_author_type_id);

                        zen_db_perform(TABLE_PRODUCT_BOOKX_AUTHORS_TO_PRODUCTS, $sql_data_array, $atp_action, $where_clause);
                        break;
                }
            }
        }

        $languages = zen_get_languages();
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
            $language_id = $languages[$i]['id'];

            $sql_data_array = array(
                'products_subtitle' => bookx_null_check($_POST['products_subtitle'][$language_id])
            );
            if($action == 'insert_product' ||
                ($action == 'update_product' && null === bookx_get_products_subtitle($products_id, $language_id))) {
                $insert_sql_data = array('products_id' => (int) $products_id,
                    'languages_id' => (int) $language_id);

                $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

                zen_db_perform(TABLE_PRODUCT_BOOKX_EXTRA_DESCRIPTION, $sql_data_array);
            }
            elseif($action == 'update_product') {
                zen_db_perform(TABLE_PRODUCT_BOOKX_EXTRA_DESCRIPTION, $sql_data_array, 'update', "products_id = '" . (int) $products_id . "' and languages_id = '" . (int) $language_id . "'");
            }
        }
        
        


    ////    *END OF PRODUCT-TYPE-SPECIFIC UPDATES* ////////
    ///////////////////////////////////////////////////////
  }

    /**
     *  look for additional update_product*.php files and include now 
     */
    $incl_dir = @dir(DIR_FS_ADMIN . '/includes/modules/product_bookx');
    while ($file = $incl_dir->read()) {
        if('update_product_' == substr($file, 0, 15)) {
            include_once DIR_FS_ADMIN . '/includes/modules/product_bookx/' . $file; // This should handle any extra values collected in collect_info*.php
        }
    }
    $incl_dir->close();

  $languages = zen_get_languages();
  for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
    $language_id = $languages[$i]['id'];

    $sql_data_array = array(
      'products_name' => zen_db_prepare_input($_POST['products_name'][$language_id]),
      'products_description' => zen_db_prepare_input($_POST['products_description'][$language_id]),
      'products_url' => zen_db_prepare_input($_POST['products_url'][$language_id]));

    if ($action == 'insert_product') {
      $insert_sql_data = array(
        'products_id' => (int)$products_id,
        'language_id' => (int)$language_id);

      $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

      zen_db_perform(TABLE_PRODUCTS_DESCRIPTION, $sql_data_array);
    } elseif ($action == 'update_product') {
      zen_db_perform(TABLE_PRODUCTS_DESCRIPTION, $sql_data_array, 'update', "products_id = " . (int)$products_id . " and language_id = " . (int)$language_id);
    }
  }

  zen_redirect(zen_href_link(FILENAME_CATEGORY_PRODUCT_LISTING, 'cPath=' . $cPath . '&pID=' . $products_id . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '') . (isset($_POST['search']) ? '&search=' . $_POST['search'] : '')));
} else {
  $messageStack->add_session(ERROR_NO_DATA_TO_SAVE, 'error');
  zen_redirect(zen_href_link(FILENAME_CATEGORY_PRODUCT_LISTING, 'cPath=' . $cPath . '&pID=' . $products_id . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '') . (isset($_POST['search']) ? '&search=' . $_POST['search'] : '')));
}

/**
 * NOTE: THIS IS HERE FOR BACKWARD COMPATIBILITY. The function is properly declared in the functions files instead.
 * Convert value to a float -- mainly used for sanitizing and returning non-empty strings or nulls
 * @param int|float|string $input
 * @return float|int
 */
if (!function_exists('convertToFloat')) {

  function convertToFloat($input = 0) {
    if ($input === null) {
      return 0;
    }
    $val = preg_replace('/[^0-9,\.\-]/', '', $input);
    // do a non-strict compare here:
    if ($val == 0) {
      return 0;
    }

    return (float)$val;
  }

}
