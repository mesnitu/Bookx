<?php

/**
 * This file is part of the ZenCart add-on Book X which
 * introduces a new product type for books to the Zen Cart
 * shop system. Tested for compatibility on ZC v. 1.5
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
 * @version BookX V 0.9.4-revision8 BETA
 * @version $Id: [ZC INSTALLATION]/includes/modules/pages/bookx_genres_list/header_php.php 2016-02-02 philou $
 */
require(DIR_WS_MODULES . zen_get_module_directory('require_languages.php'));

if (!defined('MAX_DISPLAY_BOOKX_GENRE_LISTING')) {
    define('MAX_DISPLAY_BOOKX_GENRE_LISTING', '20');
}

$extra_fields = '';
$extra_in_stock_join_clause = '';
$extra_having_clause = '';
$index_search = '';

$active_bx_filter_ids = bookx_get_active_filter_ids();

$extra_filter_query_parts = bookx_get_active_filter_query_parts($active_bx_filter_ids);

if (BOOKX_GENRE_LISTING_SHOW_ONLY_STOCKED && !(isset($_GET['la']) && $_GET['la'])) {
    $extra_fields = ' , MAX(p.products_quantity) AS quantity,  MAX(p.products_date_available) AS date_available, COUNT(p.products_id) AS books_in_stock ';
    $extra_in_stock_join_clause = ' LEFT JOIN ' . TABLE_PRODUCTS . ' p ON p.products_id = bgtp.products_id AND p.products_status > 0 ';
    $extra_having_clause = ' HAVING (quantity > 0 OR date_available >= "' . date('Y-m-d H:i:s', time() - (86400 * 60)) . '") '; // 86400 * 60 = 60 days
}

$sort_order_clause = '';
switch ((int)BOOKX_GENRE_LISTING_ORDER_BY) {
    case 1: // order by Name first
        $sort_order_clause = ' ORDER BY bgd.genre_name, bg.genre_sort_order';
        break;

    case 2: // order by sort order first
        $sort_order_clause = ' ORDER BY bg.genre_sort_order, bgd.genre_name';
        break;
}

//$index_search = " AND bgd.genre_name LIKE 'a%' ";
if (isset($_GET['q']) && !empty($_GET['q'])) {
    $index_search = " AND bgd.genre_name LIKE '" . $_GET['q'] . "%' ";
}

$sql = 'SELECT bg.bookx_genre_id, bgd.genre_image, bgd.genre_name '
    . $extra_fields
    . ' FROM ' . TABLE_PRODUCT_BOOKX_GENRES . ' bg
		LEFT JOIN ' . TABLE_PRODUCT_BOOKX_GENRES_DESCRIPTION . ' bgd ON '
    . 'bgd.bookx_genre_id = bg.bookx_genre_id AND bgd.languages_id = "' . (int)$_SESSION['languages_id'] . '"
		LEFT JOIN ' . TABLE_PRODUCT_BOOKX_GENRES_TO_PRODUCTS . ' bgtp ON bgtp.bookx_genre_id = bg.bookx_genre_id '
    . $extra_in_stock_join_clause
    . (!empty($extra_filter_query_parts['join_multi_filter']) ? $extra_filter_query_parts['join_multi_filter'] . ' ON be.products_id = bgtp.products_id ' : '')
    . bookx_assemble_filter_extra_join($extra_filter_query_parts['join'], array('genre'))
    . ' WHERE 1 ' . bookx_assemble_filter_extra_where($extra_filter_query_parts['where'], array('genre'))
    . $index_search
    . ' GROUP BY bg.bookx_genre_id '
    . $extra_having_clause
    . $sort_order_clause;

$bookx_genres_listing_split = new splitPageResults($sql, MAX_DISPLAY_BOOKX_GENRE_LISTING, 'bg.bookx_genre_id', 'page');
$bookx_genres_listing = $db->Execute($bookx_genres_listing_split->sql_query);

$bookx_genres_listing_split_array = [];

while (!$bookx_genres_listing->EOF) {

    $bookx_genres_listing_split_array [] = [
        'bookx_genre_id' => $bookx_genres_listing->fields['bookx_genre_id'],
        'genre_name' => $bookx_genres_listing->fields ['genre_name'],
        'genre_image' => (!empty($bookx_genres_listing->fields ['genre_image']) ? DIR_WS_IMAGES . $bookx_genres_listing->fields['genre_image'] : BOOKX_GENRES_DEFAULT_IMAGE)
    ];

    $bookx_genres_listing->MoveNext();
}

$bookx_alphafilter = tpl_bookx_alphafilter_all('genre_name', TABLE_PRODUCT_BOOKX_GENRES_DESCRIPTION, FILENAME_BOOKX_GENRES_LIST);
