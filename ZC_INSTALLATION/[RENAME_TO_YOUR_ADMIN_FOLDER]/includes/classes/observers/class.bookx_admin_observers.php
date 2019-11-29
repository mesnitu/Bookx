<?php

/**
 * This file is part of the ZenCart add-on Book X which
 * introduces a new product type for books to the Zen Cart
 * shop system. Tested for compatibility on ZC v. 1.5.6a
 *
 * For latest version and support visit:
 * https://sourceforge.net/p/zencartbookx
 *
 * 
 * @package admin
 * @author  Philou
 * @copyright Copyright 2013
 * @copyright Portions Copyright 2003-2011 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.gnu.org/licenses/gpl.txt GNU General Public License V2.0
 *
 * @version BookX V 1.0.0
 * @version $Id: [ZC INSTALLATION]/[ADMIN]/includes/classes/observers/class.bookx_admin_observers.php 2019-01-19 mesnit $
 */
class bookxAdminObserver extends base {

  function __construct()
  {
    global $zco_notifier;

    $zco_notifier->attach(
        $this, array('NOTIFY_MODULE_ADMIN_CATEGORY_LISTING_QUERY_BUILT',
      //'NOTIFIER_CART_ADD_CART_END',
      'NOTIFY_BEGIN_ADMIN_PRODUCTS',
      'NOTIFY_ADMIN_PROD_LISTING_ADD_THEADER',
      'NOTIFY_ADMIN_PROD_LISTING_ADD_BOOKX_INFO'
        )
    );
  }

  function update(&$callingClass, $notifier, &$paramsArray)
  {
    switch ($notifier) {
      case 'NOTIFY_MODULE_ADMIN_CATEGORY_LISTING_QUERY_BUILT':
        $this->insert_bookx_into_category_listing_query($callingClass, $notifier, $paramsArray);
        break;
      case 'NOTIFY_BEGIN_ADMIN_PRODUCTS':
        $this->bookx_notify_begin_admin_products($callingClass, $notifier, $paramsArray);
        break;
      case 'NOTIFY_ADMIN_PROD_LISTING_ADD_THEADER':
        $this->notify_admin_prod_listing_add_theader($callingClass, $notifier, $paramsArray);
        break;
      case 'NOTIFY_ADMIN_PROD_LISTING_ADD_BOOKX_INFO':
        $this->notify_admin_prod_listing_bookx_info($callingClass, $notifier, $paramsArray);
        break;
    }
  }

  function bookx_notify_begin_admin_products(&$class, $eventID, $paramsArray)
  {
    global $type_handler, $objBookxFamily;
    // @TODO: this is just a quick fix to have language files in collect_info. Not sure yet how zc is pulling lang files
    if ($type_handler == 'product_bookx.php') {
      /**
       * inistiate families class here
       */
      $objBookxFamily = new \Bookx\BookxFamilies();

      require(DIR_WS_LANGUAGES . $_SESSION['language'] . '/' . FILENAME_BOOKX_PRODUCT . '.php');
    }
  }

  /**
   * This is a optional way of showing book related fields in category listing
   */
  function notify_admin_prod_listing_add_theader(&$class, $eventID, $paramsArray)
  {
    if (isset($_GET['cPath'])) {
      echo '<th>Isbn</th>';
      echo '<th>Fam</th>';
    }
  }

  function notify_admin_prod_listing_bookx_info(&$class, $eventID, $paramsArray)
  {
    if (isset($_GET['cPath'])) {
      $product = $paramsArray;
      echo '<td class="hidden-md hidden-sm hidden-xs">' . bookx_get_isbn($product) . '</td>';
      echo '<td class="hidden-md hidden-sm hidden-xs">' . bookx_get_family_name($product) . '</td>';
    }
  }

  /**
   * Thsi will alter the category listing query so it lists some BookX-specific attributes, such as subtitle and ISBN
   */
  function insert_bookx_into_category_listing_query(&$shoppinCartClass, $notifier, $paramsArray)
  {
    global $db, $products_query_raw;

    $product_type = $db->Execute("SELECT type_id
                                  FROM " . TABLE_PRODUCT_TYPES . "
                                  WHERE type_handler = 'product_bookx'");

    $bookx_ptype_id = (0 < $product_type->RecordCount() ? $product_type->fields['type_id'] : null);
    if ($bookx_ptype_id) {
      $product_name_query = " IF(p.products_type = " . (int)$bookx_ptype_id . ",
                              CONCAT_WS(' - ', CONCAT_WS(' ', pd.products_name, be.volume), NULLIF(bed.products_subtitle, '') ),
                              pd.products_name) AS products_name";

      $product_model_query = " IF(p.products_type = " . (int)$bookx_ptype_id . ",
                               CONCAT_WS(' ', p.products_model,
                               CONCAT_WS('', ' / " . LABEL_BOOKX_ISBN . "',
                               CONCAT_WS('-', SUBSTRING(be.isbn,1,3), SUBSTRING(be.isbn,4,1), SUBSTRING(be.isbn,5,6), SUBSTRING(be.isbn,11,2), SUBSTRING(be.isbn,13,1))) ),
                               p.products_model) AS products_model";

      $additional_joins = ", " . TABLE_PRODUCTS . " pbxjoin
                          LEFT JOIN " . TABLE_PRODUCT_BOOKX_EXTRA . " be ON be.products_id = pbxjoin.products_id
                          LEFT JOIN " . TABLE_PRODUCT_BOOKX_EXTRA_DESCRIPTION . " bed ON bed.products_id = be.products_id
                            AND bed.languages_id = " . (int)$_SESSION['languages_id'];

      $additional_where = " pbxjoin.products_id = p.products_id AND ";

      $products_query_raw = str_replace(', pd.products_name,', ', ' . $product_name_query . ',', $products_query_raw);
      $products_query_raw = str_replace(', p.products_model,', ', ' . $product_model_query . ',', $products_query_raw);
      $products_query_raw = str_replace('WHERE ', $additional_joins . ' WHERE ' . $additional_where, $products_query_raw);
    }
  }
}
