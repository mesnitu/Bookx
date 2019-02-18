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
 * @version $Id: [ZC INSTALLATION]/includes/modules/pages/bookx_publishers_list/header_php.php 2016-02-02 mesnitu $
 */
require(DIR_WS_MODULES . zen_get_module_directory('require_languages.php'));

if (!defined('MAX_DISPLAY_BOOKX_PUBLISHER_LISTING')) {
    define('MAX_DISPLAY_BOOKX_PUBLISHER_LISTING', '20');
}

$extra_fields = '';
$extra_in_stock_join_clause = '';
$extra_having_clause = '';

$active_bx_filter_ids = bookx_get_active_filter_ids();

$extra_filter_query_parts = bookx_get_active_filter_query_parts($active_bx_filter_ids);

if (BOOKX_PUBLISHER_LISTING_SHOW_ONLY_STOCKED && !(isset($_GET['la']) && $_GET['la'])) {
    $extra_fields = ' , MAX(p.products_quantity) AS quantity,  MAX(p.products_date_available) AS date_available, COUNT(p.products_id) AS books_in_stock';
    $extra_in_stock_join_clause = ' LEFT JOIN ' . TABLE_PRODUCT_BOOKX_EXTRA . ' be ON be.bookx_publisher_id = bp.bookx_publisher_id
	                                LEFT JOIN ' . TABLE_PRODUCTS . ' p ON p.products_id = be.products_id AND p.products_status > 0';
    $extra_having_clause = ' HAVING (quantity > 0 OR date_available >= "' . date('Y-m-d H:i:s', time() - (86400 * 60)) . '")'; // 86400 * 60 = 60 days
}

$sort_order_clause = '';
switch ((int)BOOKX_PUBLISHER_LISTING_ORDER_BY) {
    case 1: // order by Name first
        $sort_order_clause = ' ORDER BY bp.publisher_name, bp.publisher_sort_order';
        break;

    case 2: // order by sort order first
        $sort_order_clause = ' ORDER BY bp.publisher_sort_order, bp.publisher_name';
        break;
}

if (isset($_GET['q']) && !empty($_GET['q'])) {
    $index_search = " AND bp.publisher_name LIKE '" . $_GET['q'] . "%' ";
}

$sql = 'SELECT bp.bookx_publisher_id, bp.publisher_name, bp.publisher_image, bpd.publisher_description, bpd.publisher_url '
    . $extra_fields
    . ' FROM ' . TABLE_PRODUCT_BOOKX_PUBLISHERS . ' bp
		    LEFT JOIN ' . TABLE_PRODUCT_BOOKX_PUBLISHERS_DESCRIPTION . ' bpd ON bpd.bookx_publisher_id = bp.bookx_publisher_id AND bpd.languages_id = "' . (int)$_SESSION['languages_id'] . '" '
    . $extra_in_stock_join_clause
    . (!empty($extra_filter_query_parts['join_multi_filter']) && empty($extra_in_stock_join_clause) ? $extra_filter_query_parts['join_multi_filter'] . ' ON be.bookx_publisher_id = bp.bookx_publisher_id ' : '')
    . bookx_assemble_filter_extra_join($extra_filter_query_parts['join'], array('publisher'))
    . ' WHERE 1 ' . bookx_assemble_filter_extra_where($extra_filter_query_parts['where'], array('publisher'))
    . $index_search
    . ' GROUP BY bp.bookx_publisher_id '
    . $extra_having_clause
    . $sort_order_clause;

$bookx_publishers_listing_split = new splitPageResults($sql, MAX_DISPLAY_BOOKX_PUBLISHER_LISTING, 'bp.bookx_publisher_id', 'page');
$bookx_publishers_listing = $db->Execute($bookx_publishers_listing_split->sql_query);

$display_image = BOOKX_FILTER_ALL_DISPLAY_OPTIONS['publisher_image'];
$default_image = DIR_WS_IMAGES . BOOKX_DEFAULT_IMAGE_FOR['publisher'];

$bookx_publishers_listing_split_array = [];

while (!$bookx_publishers_listing->EOF) {

    $bookx_publishers_listing_split_array [] = [
        'bookx_publisher_id' => $bookx_publishers_listing->fields['bookx_publisher_id'],
        'publisher_name' => $bookx_publishers_listing->fields['publisher_name'],
        'publisher_image' => (!empty($bookx_publishers_listing->fields ['publisher_image']) ? DIR_WS_IMAGES . $bookx_publishers_listing->fields['publisher_image'] : $default_image),
        'publisher_description' => $bookx_publishers_listing->fields['publisher_description'],
        'publisher_url' => $bookx_publishers_listing->fields['publisher_url']
    ];

    $bookx_publishers_listing->MoveNext();
}

unset($default_image);
$bookx_alphafilter = tpl_bookx_alphafilter_all('publisher_name', TABLE_PRODUCT_BOOKX_PUBLISHERS, FILENAME_BOOKX_PUBLISHERS_LIST);
