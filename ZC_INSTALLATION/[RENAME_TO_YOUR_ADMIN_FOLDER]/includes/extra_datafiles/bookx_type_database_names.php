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
 * @version BookX v1.0.0
 * @version $Id: [admin]/includes/extra_datafiles/bookx_type_extra_database_names.php 2019-01-06 mesnitu $
 */

if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
/**
 * Database name defines
 */
define('TABLE_PRODUCT_BOOKX_AUTHORS', DB_PREFIX . 'product_bookx_authors');
define('TABLE_PRODUCT_BOOKX_AUTHORS_DESCRIPTION', DB_PREFIX . 'product_bookx_authors_description');
define('TABLE_PRODUCT_BOOKX_AUTHORS_TO_PRODUCTS', DB_PREFIX . 'product_bookx_authors_to_products');
define('TABLE_PRODUCT_BOOKX_AUTHOR_TYPES', DB_PREFIX . 'product_bookx_author_types');
define('TABLE_PRODUCT_BOOKX_AUTHOR_TYPES_DESCRIPTION', DB_PREFIX . 'product_bookx_author_types_description');
define('TABLE_PRODUCT_BOOKX_BINDING', DB_PREFIX . 'product_bookx_binding');
define('TABLE_PRODUCT_BOOKX_BINDING_DESCRIPTION', DB_PREFIX . 'product_bookx_binding_description');
define('TABLE_PRODUCT_BOOKX_CONDITIONS', DB_PREFIX . 'product_bookx_conditions');
define('TABLE_PRODUCT_BOOKX_CONDITIONS_DESCRIPTION', DB_PREFIX . 'product_bookx_condition_descriptions');
define('TABLE_PRODUCT_BOOKX_EXTRA', DB_PREFIX . 'product_bookx_extra');
define('TABLE_PRODUCT_BOOKX_EXTRA_DESCRIPTION', DB_PREFIX . 'product_bookx_extra_description');
define('TABLE_PRODUCT_BOOKX_GENRES', DB_PREFIX . 'product_bookx_genres');
define('TABLE_PRODUCT_BOOKX_GENRES_DESCRIPTION', DB_PREFIX . 'product_bookx_genres_description');
define('TABLE_PRODUCT_BOOKX_GENRES_TO_PRODUCTS', DB_PREFIX . 'product_bookx_genres_to_products');
define('TABLE_PRODUCT_BOOKX_IMPRINTS', DB_PREFIX . 'product_bookx_imprints');
define('TABLE_PRODUCT_BOOKX_IMPRINTS_DESCRIPTION', DB_PREFIX . 'product_bookx_imprints_description');
define('TABLE_PRODUCT_BOOKX_PRINTING', DB_PREFIX . 'product_bookx_printing');
define('TABLE_PRODUCT_BOOKX_PRINTING_DESCRIPTION', DB_PREFIX . 'product_bookx_printing_description');
define('TABLE_PRODUCT_BOOKX_PUBLISHERS', DB_PREFIX . 'product_bookx_publishers');
define('TABLE_PRODUCT_BOOKX_PUBLISHERS_DESCRIPTION', DB_PREFIX . 'product_bookx_publishers_description');
define('TABLE_PRODUCT_BOOKX_SERIES', DB_PREFIX . 'product_bookx_series');
define('TABLE_PRODUCT_BOOKX_SERIES_DESCRIPTION', DB_PREFIX . 'product_bookx_series_description');


/**
 * @since version bookx v1.0.0
 * 
 */
define('TABLE_PRODUCT_BOOKX_FAMILIES', DB_PREFIX . 'product_bookx_families');
define('TABLE_PRODUCT_BOOKX_FAMILIES_TO_PRODUCTS', DB_PREFIX . 'product_bookx_families_to_products');
define('TABLE_PRODUCT_BOOKX_SEARCH', DB_PREFIX . 'product_bookx_search');



