<?php
/**
 * This file is part of the ZenCart add-on Book X which
 * introduces a new product type for books to the Zen Cart
 * shop system. Tested for compatibility on ZC v. 1.56a
 *
 * For latest version and support visit:
 * https://sourceforge.net/p/zencartbookx
 *
 * @package page
 * @author  Philou
 * @copyright Copyright 2013
 * @copyright Portions Copyright 2003-2011 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.gnu.org/licenses/gpl.txt GNU General Public License V2.0
 *
 * @version BookX V 1.0.0
 * @version $Id: [ZC INSTALLATION]/includes/modules/pages/product_bookx_info/header_php.php 2019-02-02 mesnitu $
 */

/**
 * product_info header_php.php
 *
 */

  // This should be first line of the script:
  $zco_notifier->notify('NOTIFY_HEADER_START_PRODUCT_INFO');
  
  require(DIR_WS_MODULES . zen_get_module_directory('require_languages.php'));
  
  $page_not_found = false;
  
// if specified product_id is disabled or doesn't exist, ensure that metatags and breadcrumbs don't share inappropriate information
  $sql = "select count(*) as total
          from " . TABLE_PRODUCTS . " p, " .
                   TABLE_PRODUCTS_DESCRIPTION . " pd
          where    p.products_status = '1'
          and      p.products_id = '" . (int)$_GET['products_id'] . "'
          and      pd.products_id = p.products_id
          and      pd.language_id = '" . (int)$_SESSION['languages_id'] . "'";
  $res = $db->Execute($sql);
  if ( $res->fields['total'] < 1 ) {
    unset($_GET['products_id']);
    unset($breadcrumb->_trail[sizeof($breadcrumb->_trail)-1]['title']);
    $robotsNoIndex = true;
    // passing this var $page_not_found because the same query is repeted in main_template_vars
    $page_not_found = true;
    header('HTTP/1.1 404 Not Found');
    
  }
  
  // ensure navigation snapshot in case must-be-logged-in-for-price is enabled
  if (!$_SESSION['customer_id']) {
    $_SESSION['navigation']->set_snapshot();
  }

  // This should be last line of the script:
  $zco_notifier->notify('NOTIFY_HEADER_END_PRODUCT_INFO');
