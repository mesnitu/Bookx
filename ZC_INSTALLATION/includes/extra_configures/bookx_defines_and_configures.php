<?php
/**
 * This file is part of the ZenCart add-on Book X which
 * introduces a new product type for books to the Zen Cart
 * shop system. Tested for compatibility on ZC v. 1.56a
 *
 * For latest version and support visit:
 * https://sourceforge.net/p/zencartbookx
 *
 * @package initSystem
 * @author  Philou
 * @copyright Copyright 2013
 * @copyright Portions Copyright 2003-2019 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.gnu.org/licenses/gpl.txt GNU General Public License V2.0
 *
 * @version BookX V 1.0.0
 * @version $Id: [ZC INSTALLATION]/includes/extra_configures/bookx_defines_and_configures.php 2019-02-15 mesnitu $
 */
/*
 * Extra Book X definitions & configures
 */
define('BOOKX_LAYOUT_FLAG_OPTION_DONT_DISPLAY', 0);
define('BOOKX_LAYOUT_FLAG_OPTION_DISPLAY_IF_NOT_EMPTY', 1);
define('BOOKX_LAYOUT_FLAG_OPTION_ALWAYS_DISPLAY', 2);

/**
 * @since v1.0.0
 */

/*
 * Some queries are full table scans such as tpl filter all ( authors, genres, etc). If you use zencart cache
 * set here the default cache time for BookX queries. If you need different cache time values, search for BOOKX_DEFAULT_SQL_CACHE_TIME in you editor.
 * To unset and not use bookx cache time, change the default config value 7200 (2hours) to false
 */
define('BOOKX_DEFAULT_SQL_CACHE_TIME', 7200); // 2 hours

define('BOOKX_TRUNCATE_DESCRIPTION_LENGHT', 350);
define('BOOKX_BOOKINFO_RELATED_AUTHOR_BOOKS_LIMIT', 3);

define('BOOKX_NOFOLLOW_LINK', ' rel="nofollow"'); 

define('BOOKX_FILTER_ALL_DISPLAY_OPTIONS', [
    'author_image' => true,
    'genre_image' => true,
    'publisher_image' => true,
    'imprint_image' => true,
    'serie_image' => true,
    'alpha_index' => true,
    'alpha_index_counter' => true,
]);

define('BOOKX_DEFAULT_IMAGE_FOR', [
    'author' => 'autores/author_noimage.jpg',
    'genre' => '',
    'publisher' => '',
    'imprint' => '',
    'serie' => '',
]);

/**
 * @todo stuff to be removed. Just placing it here to move along
 */
define('BOOKX_USES_CEON_URI_MODULE', false);
