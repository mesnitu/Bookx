<?php
/**
 * This file is part of the ZenCart add-on Book X which
 * introduces a new product type for books to the Zen Cart
 * shop system. Tested for compatibility on ZC v. 1.5
 *
 * For latest version and support visit:
 * https://sourceforge.net/p/zencartbookx
 *
 * @package initSystem
 * @author  Philou
 * @copyright Copyright 2013
 * @copyright Portions Copyright 2003-2011 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.gnu.org/licenses/gpl.txt GNU General Public License V2.0
 *
 * @version BookX V 0.9.4-revision8 BETA
 * @version $Id: [ZC INSTALLATION]/includes/extra_configures/bookx_defines_and_configures.php 2016-02-02 philou $
 */

/** 
 * Extra Book X definitions & configures
 */
  define('BOOKX_LAYOUT_FLAG_OPTION_DONT_DISPLAY', 0);
  define('BOOKX_LAYOUT_FLAG_OPTION_DISPLAY_IF_NOT_EMPTY', 1);
  define('BOOKX_LAYOUT_FLAG_OPTION_ALWAYS_DISPLAY', 2);
  
  /**
   * @since v1.0.0
   */
  define('TEXT_BOOKX_MORE_PRODUCT_INFO', 'See More');
  define('BOOKX_SHOW_ALPHAINDEX_ON_FILTER_ALL', true);
  define('BOOKX_TRUNCATE_DESCRIPTION_LENGHT', 350);
  define('BOOKX_BOOKINFO_RELATED_AUTHOR_BOOKS_LIMIT', 3);
  
  define('BOOKX_AUTHOR_DEFAULT_IMAGE', 'author_noimage.jpg');
  
  define('BOOKX_PUBLISHER_DEFAULT_IMAGE', PRODUCTS_IMAGE_NO_IMAGE); 
  define('BOOKX_GENRES_DEFAULT_IMAGE', PRODUCTS_IMAGE_NO_IMAGE);
  
  /**
   * @todo stuff to be removed. Just placing it here to move along
   */
  
  define('BOOKX_USES_CEON_URI_MODULE', false);