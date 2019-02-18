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
 * @version BookX V 1.0.0
 * @version $Id: [ZC INSTALLATION]/includes/modules/pages/bookx_authors_list/header_php.php 2019-02-15 mesnitu $
 */
require(DIR_WS_MODULES . zen_get_module_directory('require_languages.php'));

if (!defined('MAX_DISPLAY_BOOKX_AUTHOR_LISTING')) {
    define('MAX_DISPLAY_BOOKX_AUTHOR_LISTING', '20');
}

$extra_fields = '';
$extra_in_stock_join_clause = '';
$extra_having_clause = '';
$index_search = '';
$active_bx_filter_ids = bookx_get_active_filter_ids();

$extra_filter_query_parts = bookx_get_active_filter_query_parts($active_bx_filter_ids);

if (BOOKX_AUTHOR_LISTING_SHOW_ONLY_STOCKED && !(isset($_GET['la']) && $_GET['la'])) {
    $extra_fields = ' , MAX(p.products_quantity) AS quantity,  MAX(p.products_date_available) AS date_available, COUNT(p.products_id) AS books_in_stock';
    $extra_in_stock_join_clause = ' LEFT JOIN ' . TABLE_PRODUCTS . ' p ON p.products_id = batp.products_id AND p.products_status > 0';
    $extra_having_clause = ' HAVING (quantity > 0 OR date_available >= "' . date('Y-m-d H:i:s', time() - (86400 * 60)) . '")'; // 86400 * 60 = 60 days
}

$sort_order_clause = '';
switch ((int)BOOKX_AUTHOR_LISTING_ORDER_BY) {
    case 1: // order by Name first
        $sort_order_clause = ' ORDER BY ba.author_name, ba.author_sort_order';
        break;

    case 2: // order by sort order first
        $sort_order_clause = ' ORDER BY ba.author_sort_order, ba.author_name';
        break;
}

$index_search = " AND ba.author_name LIKE 'a%' ";

if (isset($_GET['q']) && !empty($_GET['q'])) {
    $index_search = " AND  ba.author_name LIKE '" . $_GET['q'] . "%' ";
}

if ($active_bx_filter_ids['author_type_id']) {
    $author_type_filter_extra_where = ' AND batp.bookx_author_type_id ="' . $active_bx_filter_ids['author_type_id'] . '" ';
}

$sql = 'SELECT ba.bookx_author_id, ba.author_name, ba.author_image, ba.author_url, bad.author_description,
			GROUP_CONCAT(DISTINCT batd.type_description ORDER BY batd.type_description ASC SEPARATOR \', \') AS author_types '
    . $extra_fields
    . ' FROM ' . TABLE_PRODUCT_BOOKX_AUTHORS . ' ba
		    LEFT JOIN ' . TABLE_PRODUCT_BOOKX_AUTHORS_DESCRIPTION . ' bad ON bad.bookx_author_id = ba.bookx_author_id AND bad.languages_id = "' . (int)$_SESSION['languages_id'] . '"
		    LEFT JOIN ' . TABLE_PRODUCT_BOOKX_AUTHORS_TO_PRODUCTS . ' batp ON batp.bookx_author_id = ba.bookx_author_id
		    LEFT JOIN ' . TABLE_PRODUCT_BOOKX_AUTHOR_TYPES_DESCRIPTION . ' batd ON batd.bookx_author_type_id = batp.bookx_author_type_id AND batd.languages_id = "' . (int)$_SESSION['languages_id'] . '" '
    . (!empty($extra_filter_query_parts['join_multi_filter']) ? $extra_filter_query_parts['join_multi_filter'] . ' ON be.products_id = batp.products_id ' : '')
    . $extra_in_stock_join_clause
    . bookx_assemble_filter_extra_join($extra_filter_query_parts['join'], array('author', 'author_type'))
    . ' WHERE 1 ' . $author_type_filter_extra_where . bookx_assemble_filter_extra_where($extra_filter_query_parts['where'], array('author', 'author_type')) . $index_search
    . ' GROUP BY ba.bookx_author_id '
    . $extra_having_clause
    . $sort_order_clause;

$bookx_authors_listing_split = new splitPageResults($sql, MAX_DISPLAY_BOOKX_AUTHOR_LISTING, 'ba.bookx_author_id', 'page');
$bookx_authors_listing = $db->Execute($bookx_authors_listing_split->sql_query);

/*
 * @todo this should go somewhere more global, but where ? a init ? observer ?
 */
$authors_default_image = DIR_WS_IMAGES . BOOKX_AUTHOR_IMAGES_FOLDER . '/' . BOOKX_AUTHOR_DEFAULT_IMAGE;

$bookx_authors_listing_split_array = array();

while (!$bookx_authors_listing->EOF) {
    $bookx_authors_listing_split_array [] = array('bookx_author_id' => $bookx_authors_listing->fields ['bookx_author_id']
        , 'author_name' => $bookx_authors_listing->fields ['author_name']
        , 'author_types' => (!empty($bookx_authors_listing->fields ['author_types']) ? '(' . $bookx_authors_listing->fields ['author_types'] . ')' : '')
        , 'author_image' => (!empty($bookx_authors_listing->fields ['author_image']) ? DIR_WS_IMAGES . $bookx_authors_listing->fields ['author_image'] : $authors_default_image)
        , 'author_description' => $bookx_authors_listing->fields ['author_description']
        , 'author_url' => $bookx_authors_listing->fields ['author_url']
    );

    $bookx_authors_listing->MoveNext();
}

$bookx_alphafilter = tpl_bookx_alphafilter_all('author_name', TABLE_PRODUCT_BOOKX_AUTHORS, FILENAME_BOOKX_AUTHORS_LIST);
