<?php

/**
 * This file is part of the ZenCart add-on Book X which
 * introduces a new product type for books to the Zen Cart
 * shop system. Tested for compatibility on ZC v. 1.6
 *
 * @package functions
 * @author  Philou
 * @copyright Copyright 2013
 * @copyright Portions Copyright 2003-2011 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.gnu.org/licenses/gpl.txt GNU General Public License V2.0
 *
 * @version BookX V 1.0.0
 * @version $Id: /includes/functions/extra_functions/product_type_bookx_functions.php 2016-02-02 mesnitu $
 */

/**
 * @since v1.0.0 Most of the functions that were as admin functions are also usefull for tpl display and quick access. 
 * This file was created to share those functions and called in admin also  
 */

function bookx_get_products_subtitle($products_id, $language_id)
{
  global $db;
  $product = $db->Execute("SELECT products_subtitle
                           FROM " . TABLE_PRODUCT_BOOKX_EXTRA_DESCRIPTION . "
                           WHERE products_id = " . (int)$products_id . "
                           AND languages_id = " . (int)$language_id);
  if (!$product->EOF) {
    return ($product->fields['products_subtitle'] ? $product->fields['products_subtitle'] : '');
  } else {
    return null;
  }
}

function bookx_get_isbn($products_id)
{
  global $db;
  $product = $db->Execute("SELECT isbn
                           FROM " . TABLE_PRODUCT_BOOKX_EXTRA . "
                           WHERE products_id = " . (int)$products_id);
  if (!$product->EOF) {
    return ($product->fields['isbn']) ?? '';
  }
}

function bookx_format_isbn_for_display($isbn = null)
{
  $isbn = preg_replace('/[^0-9]/', '', $isbn);
  if (!empty($isbn) && 13 == strlen($isbn)) {
    $isbn = substr($isbn, 0, 3) . '-' . substr($isbn, 3, 1) . '-' . substr($isbn, 4, 6) . '-' . substr($isbn, 10, 2) . '-' . substr($isbn, 12);
  }
  return $isbn;
}

function bookx_get_family_name($products_id)
{
  global $db;
  $product = $db->Execute("SELECT bookx_family_name
                           FROM " . TABLE_PRODUCT_BOOKX_FAMILIES . " bf LEFT JOIN
                                " . TABLE_PRODUCT_BOOKX_FAMILIES_TO_PRODUCTS . " bftp ON bf.bookx_family_id = bftp.bookx_family_id
                           WHERE bftp.products_id = " . (int)$products_id);
  if (!$product->EOF) {
    return ($product->fields['bookx_family_name']) ?? '';
  }
}

// Return the Authors URL
function bookx_get_author_url($bookx_author_id)
{
  global $db;
  $author = $db->Execute("SELECT author_url
                          FROM " . TABLE_PRODUCT_BOOKX_AUTHORS . "
                          WHERE bookx_author_id = " . (int)$bookx_author_id);
  if (!$author->EOF) {
    return $author->fields['author_url'];
  } else {
    return null;
  }
}

function bookx_get_author_name($bookx_author_id)
{
  global $db;
  $author = $db->Execute("SELECT author_name
                          FROM " . TABLE_PRODUCT_BOOKX_AUTHORS . "
                          WHERE bookx_author_id = " . (int)$bookx_author_id);

  if (!$author->EOF) {
    return ($author->fields['author_name'] ? $author->fields['author_name'] : '');
  } else {
    return null;
  }
}

function bookx_get_author_description($bookx_author_id, $language_id)
{
  global $db;
  $author = $db->Execute("SELECT author_description
                          FROM " . TABLE_PRODUCT_BOOKX_AUTHORS_DESCRIPTION . "
                          WHERE bookx_author_id = " . (int)$bookx_author_id . "
                          AND languages_id = " . (int)$language_id);

  if (!$author->EOF) {
    return ($author->fields['author_description'] ? $author->fields['author_description'] : '');
  } else {
    return null;
  }
}

function bookx_get_author_type_description($bookx_author_type_id, $language_id)
{
  global $db;
  $author_type = $db->Execute("SELECT type_description
                               FROM " . TABLE_PRODUCT_BOOKX_AUTHOR_TYPES_DESCRIPTION . "
                               WHERE bookx_author_type_id = " . (int)$bookx_author_type_id . "
                               AND languages_id = " . (int)$language_id );

  if (!$author_type->EOF) {
    return ($author_type->fields['type_description'] ? $author_type->fields['type_description'] : '');
  } else {
    return null;
  }
}

function bookx_get_author_type_image_url($bookx_author_type_id, $language_id)
{
  global $db;
  $author_type = $db->Execute("SELECT type_image
                               FROM " . TABLE_PRODUCT_BOOKX_AUTHOR_TYPES_DESCRIPTION . "
                               WHERE bookx_author_type_id = " . (int)$bookx_author_type_id . "
                               AND languages_id = " . (int)$language_id);

  if (!$author_type->EOF) {
    return ($author_type->fields['type_image'] ? $author_type->fields['type_image'] : '');
  } else {
    return null;
  }
}

function bookx_get_publisher_name($bookx_publisher_id)
{
  global $db;
  $publisher = $db->Execute("SELECT publisher_name
                             FROM " . TABLE_PRODUCT_BOOKX_PUBLISHERS . "
                             WHERE bookx_publisher_id = " . (int)$bookx_publisher_id);

  if (!$publisher->EOF) {
    return ($publisher->fields['publisher_name'] ? $publisher->fields['publisher_name'] : '');
  } else {
    return null;
  }
}

/*
 * Return the Publisher URL in the needed language
 *
 */
function bookx_get_publisher_url($bookx_publisher_id, $language_id)
{
  global $db;
  $publisher = $db->Execute("SELECT publisher_url
                             FROM " . TABLE_PRODUCT_BOOKX_PUBLISHERS_DESCRIPTION . "
                             WHERE bookx_publisher_id = " . (int)$bookx_publisher_id . "
                             AND languages_id = " . (int)$language_id);

  if (!$publisher->EOF) {
    return ($publisher->fields['publisher_url'] ? $publisher->fields['publisher_url'] : '');
  } else {
    return null;
  }
}

function bookx_get_publisher_description($bookx_publisher_id, $language_id)
{
  global $db;
  $publisher = $db->Execute("SELECT publisher_description
                             FROM " . TABLE_PRODUCT_BOOKX_PUBLISHERS_DESCRIPTION . "
                             WHERE bookx_publisher_id = " . (int)$bookx_publisher_id . "
                             AND languages_id = " . (int)$language_id);

  if (!$publisher->EOF) {
    return ($publisher->fields['publisher_description'] ? $publisher->fields['publisher_description'] : '');
  } else {
    return null;
  }
}

function bookx_get_imprint_name($bookx_imprint_id)
{
  global $db;
  $imprint = $db->Execute("SELECT imprint_name
                           FROM " . TABLE_PRODUCT_BOOKX_IMPRINTS . "
                           WHERE bookx_imprint_id = " . (int)$bookx_imprint_id);

  if (!$imprint->EOF) {
    return ($imprint->fields['imprint_name'] ? $imprint->fields['imprint_name'] : '');
  } else {
    return null;
  }
}

function bookx_get_imprint_description($bookx_imprint_id, $language_id)
{
  global $db;
  $imprint = $db->Execute("SELECT imprint_description
                           FROM " . TABLE_PRODUCT_BOOKX_IMPRINTS_DESCRIPTION . "
                           WHERE bookx_imprint_id = " . (int)$bookx_imprint_id . "
                           AND languages_id = " . (int)$language_id);

  if (!$imprint->EOF) {
    return ($imprint->fields['imprint_description'] ? $imprint->fields['imprint_description'] : '');
  } else {
    return null;
  }
}

function bookx_get_genre_name($bookx_genre_id, $language_id)
{
  global $db;
  $genre = $db->Execute("SELECT genre_name
                         FROM " . TABLE_PRODUCT_BOOKX_GENRES_DESCRIPTION . "
                         WHERE bookx_genre_id = " . (int)$bookx_genre_id . "
                         AND languages_id = " . (int)$language_id);

  if (!$genre->EOF) {
    return ($genre->fields['genre_name'] ? $genre->fields['genre_name'] : '');
  } else {
    return null;
  }
}

function bookx_get_genre_image_url($bookx_genre_id, $language_id)
{
  global $db;
  $genre = $db->Execute("SELECT genre_image
                         FROM " . TABLE_PRODUCT_BOOKX_GENRES_DESCRIPTION . "
                         WHERE bookx_genre_id = " . (int)$bookx_genre_id . "
                         AND languages_id = " . (int)$language_id);

  if (!$genre->EOF) {
    return ($genre->fields['genre_image'] ? $genre->fields['genre_image'] : '');
  } else {
    return null;
  }
}

function bookx_get_series_image_url($bookx_series_id, $language_id)
{
  global $db;
  $series = $db->Execute("SELECT series_image
                          FROM " . TABLE_PRODUCT_BOOKX_SERIES_DESCRIPTION . "
                          WHERE bookx_series_id = " . (int)$bookx_series_id . "
                          AND languages_id = " . (int)$language_id);

  if (!$series->EOF) {
    return ($series->fields['series_image'] ? $series->fields['series_image'] : '');
  } else {
    return null;
  }
}

function bookx_get_series_name($bookx_series_id, $language_id)
{
  global $db;
  $series = $db->Execute("SELECT series_name
                          FROM " . TABLE_PRODUCT_BOOKX_SERIES_DESCRIPTION . "
                          WHERE bookx_series_id = " . (int)$bookx_series_id . "
                          AND languages_id = " . (int)$language_id);

  if (!$series->EOF) {
    return ($series->fields['series_name'] ? $series->fields['series_name'] : '');
  } else {
    return null;
  }
}

function bookx_get_series_description($bookx_series_id, $language_id)
{
  global $db;
  $series = $db->Execute("SELECT series_description
                          FROM " . TABLE_PRODUCT_BOOKX_SERIES_DESCRIPTION . "
                          WHERE bookx_series_id = " . (int)$bookx_series_id . "
                          AND languages_id = " . (int)$language_id);

  if (!$series->EOF) {
    return ($series->fields['series_description'] ? $series->fields['series_description'] : '');
  } else {
    return null;
  }
}

function bookx_get_printing_description($bookx_printing_id, $language_id)
{
  global $db;
  $printing = $db->Execute("SELECT printing_description
                            FROM " . TABLE_PRODUCT_BOOKX_PRINTING_DESCRIPTION . "
                            WHERE bookx_printing_id = " . (int)$bookx_printing_id . "
                            AND languages_id = " . (int)$language_id);

  if (!$printing->EOF) {
    return ($printing->fields['printing_description'] ? $printing->fields['printing_description'] : '');
  } else {
    return null;
  }
}

function bookx_get_binding_description($bookx_binding_id, $language_id)
{
  global $db;
  $binding = $db->Execute("SELECT binding_description
                           FROM " . TABLE_PRODUCT_BOOKX_BINDING_DESCRIPTION . "
                           WHERE bookx_binding_id = " . (int)$bookx_binding_id . "
                           AND languages_id = " . (int)$language_id);

  if (!$binding->EOF) {
    return ($binding->fields['binding_description'] ? $binding->fields['binding_description'] : '');
  } else {
    return null;
  }
}

function bookx_get_condition_description($bookx_condition_id, $language_id)
{
  global $db;
  $condition = $db->Execute("SELECT condition_description
                             FROM " . TABLE_PRODUCT_BOOKX_CONDITIONS_DESCRIPTION . "
                             WHERE bookx_condition_id = " . (int)$bookx_condition_id . "
                             AND languages_id = " . (int)$language_id);

  if (!$condition->EOF) {
    return ($condition->fields['condition_description'] ? $condition->fields['condition_description'] : '');
  } else {
    return null;
  }
}

/*
 * Look up SHOW_XXX_INFO switch for product type bookx
*/

function bookx_get_show_product_switch($field, $suffix = 'SHOW_', $prefix = '_INFO', $field_prefix = '_', $field_suffix = '')
{
	global $db;

	$zv_key = strtoupper($suffix . 'PRODUCT_BOOKX' . $prefix . $field_prefix . $field . $field_suffix);

  $sql = "SELECT configuration_key, configuration_value
          FROM " . TABLE_PRODUCT_TYPE_LAYOUT . "
          WHERE configuration_key = '" . $zv_key . "'";
	$zv_key_value = $db->Execute($sql);
	if ($zv_key_value->RecordCount() > 0) {
		return $zv_key_value->fields['configuration_value'];
	} else {
    $sql = "SELECT configuration_key, configuration_value
            FROM " . TABLE_CONFIGURATION . "
            WHERE configuration_key = '" . $zv_key . "'";
		$zv_key_value = $db->Execute($sql);
		if ($zv_key_value->RecordCount() > 0) {
			return $zv_key_value->fields['configuration_value'];
		} else {
			return false;
		}
	}
}