<?php

/**
 * This file is part of the ZenCart add-on Book X which
 * introduces a new product type for books to the Zen Cart
 * shop system. Tested for compatibility on ZC v. 1.56
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
 * @version BookX V 1.0.0
 * @version $Id: [admin]/includes/functions/extra_functions/product_type_bookx_functions.php 2019-02-02 mesnitu $
 */

/**
 * @since v1.0.0 Most of the functions that were as admin functions are also usefull for tpl display and quick access. 
 * A common functions file was created to share those functions.  
 */
require DIR_FS_CATALOG . 'includes/functions/extra_functions/functions_product_type_bookx_common.php';

function bookx_delete_product($product_id = null, $delete_linked = true)
{
  global $db;
  if (null != $product_id) {
    bookx_delete_bookx_specific_product_entries($product_id);

    zen_remove_product($product_id, $delete_linked);
  }
}

function bookx_delete_bookx_specific_product_entries($product_id = null, $delete_linked = true)
{
  global $db;
  if (null != $product_id) {
    $db->Execute("DELETE FROM " . TABLE_PRODUCT_BOOKX_EXTRA . "
                  WHERE products_id = " . (int)$product_id);

    $db->Execute("DELETE FROM " . TABLE_PRODUCT_BOOKX_EXTRA_DESCRIPTION . "
                  WHERE products_id = " . (int)$product_id);

    $db->Execute("DELETE FROM " . TABLE_PRODUCT_BOOKX_GENRES_TO_PRODUCTS . "
                  WHERE products_id = " . (int)$product_id);

    $db->Execute("DELETE FROM " . TABLE_PRODUCT_BOOKX_AUTHORS_TO_PRODUCTS . "
                  WHERE products_id = " . (int)$product_id);
  }
}

function bookx_convert_product_to_bookx_type($product_id = null)
{
  global $db;

  $sql = "SELECT *
          FROM " . TABLE_PRODUCT_TYPES . "
          WHERE type_handler = 'product_bookx'";

  $results = $db->Execute($sql); /* @var $result queryFactoryResult */
  foreach ($results as $result) {
    $bookx_type_id = $result['type_id'];
  }

  if (null != $product_id) {
    $db->Execute("UPDATE " . TABLE_PRODUCTS . "
                  SET products_type = " . (int)$bookx_type_id . "
                  WHERE products_id = " . (int)$product_id);

    $db->Execute("REPLACE INTO " . TABLE_PRODUCT_BOOKX_EXTRA . " (products_id)
                  VALUES (" . (int)$product_id . ")");
  }
}

function bookx_convert_product_from_bookx_to_type($product_id = null, $destination_type = null)
{
  global $db;

  if (null != $product_id && null != $destination_type) {
    bookx_delete_bookx_specific_product_entries($product_id);
    $db->Execute("UPDATE " . TABLE_PRODUCTS . "
                  SET products_type = " . (int)$destination_type . "
                  WHERE products_id = " . (int)$product_id);
  }
}

/*
 * This is just a slightly modified copy of zen_image_OLD in the CATALOG
 * since the ADMIN Zen Image does not scale with maintaining proportions
 */

function bookx_image($src, $alt = '', $width = '', $height = '', $parameters = '')
{

  if ((empty($src) || ($src == DIR_WS_IMAGES))) {
    return false;
  }

  // Convert width/height to int for proper validation.
  $width = empty($width) ? $width : intval($width);
  $height = empty($height) ? $height : intval($height);

  // alt is added to the img tag even if it is null to prevent browsers from outputting
  // the image filename as default
  $image = '<img src="' . zen_output_string($src) . '" alt="' . zen_output_string($alt) . '"';

  if (zen_not_null($alt)) {
    $image .= ' title=" ' . zen_output_string($alt) . ' "';
  }

  if ((CONFIG_CALCULATE_IMAGE_SIZE == 'true') && (empty($width) || empty($height))) {
    if ($image_size = @getimagesize($src)) {
      if (empty($width) && zen_not_null($height)) {
        $ratio = $height / $image_size[1];
        $width = $image_size[0] * $ratio;
      } elseif (zen_not_null($width) && empty($height)) {
        $ratio = $width / $image_size[0];
        $height = $image_size[1] * $ratio;
      } elseif (empty($width) && empty($height)) {
        $width = $image_size[0];
        $height = $image_size[1];
      }
    }
  }

  if (zen_not_null($width) && zen_not_null($height)) {
//      $image .= ' width="' . zen_output_string($width) . '" height="' . zen_output_string($height) . '"';
// proportional images
    $image_size = @getimagesize($src);
    // fix division by zero error
    $ratio = ($image_size[0] != 0 ? $width / $image_size[0] : 1);
    if ($image_size[1] * $ratio > $height) {
      $ratio = $height / $image_size[1];
      $width = $image_size[0] * $ratio;
    } else {
      $height = $image_size[1] * $ratio;
    }
// only use proportional image when image is larger than proportional size
    if ($image_size[0] < $width and $image_size[1] < $height) {
      $image .= ' width="' . $image_size[0] . '" height="' . intval($image_size[1]) . '"';
    } else {
      $image .= ' width="' . round($width) . '" height="' . round($height) . '"';
    }
  } else {
    // override on missing image to allow for proportional and required/not required
    if (IMAGE_REQUIRED == 'false') {
      return false;
    } else if (substr($src, 0, 4) != 'http') {
      $image .= ' width="' . intval(SMALL_IMAGE_WIDTH) . '" height="' . intval(SMALL_IMAGE_HEIGHT) . '"';
    }
  }

  if (zen_not_null($parameters)) {
    $image .= ' ' . $parameters;
  }

  $image .= ' />';

  return $image;
}

/**
 * @since v1.0.0
 * Insures that empty values are inserted Null in database
 * @param type $value The value received to insert in database
 * @return string
 */
function bookx_null_check($value)
{
  $value = zen_db_prepare_input($value);
  if (empty($value)) {
    return 'null';
  } else {
    return $value;
  }
}

/**
 * Checks missing relations between bookx tables_to_products and table products.
 * 
 * @category admin
 * @global type $db
 * @param array $bx_tables an array on tables to check
 * @param bool $delete default false
 * @return string $msg info
 * 
 */
function bookx_check_missing_product_relations($bx_tables, $field_id, $delete = false)
{
  global $db;
  $msg = '';
  if (is_array($bx_tables)) {

    foreach ($bx_tables as $table => $table2) {
      $check = $db->Execute("SELECT " . $field_id . "
                             FROM " . $table . "
                             WHERE " . $field_id . "
                             NOT IN (SELECT " . $field_id . " FROM " . $table2 . ");");

      $msg .= ($check->Count() > 0) ? "Found " . $check->Count() . " missing relations in table[" . $table . "]<br />" : $table . " all Good!<br />";
      if ($delete == true && $check->Count() > 0) {
        $msg .= ($check->Count() > 0) ? "Deleted " . $check->Count() . " in " . $table . "<br />" : "All Goodfff!";
        $db->Execute("DELETE FROM " . $table . "
                      WHERE " . $field_id . "
                      NOT IN (SELECT " . $field_id . " FROM " . $table2 . ");");
      }
    }
  }

  return $msg;
}

/**
 * 
 * @param type $url the git api release links
 * @param type $compare if <b>TRUE</b> returns an array. Else, display formated info (on install) 
 * @param type $install maybe future git install releases. 
 * @return type array
 */
function check_git_release_for($url, $compare = false, $install = null)
{
  //$download_folder = '';
  $cInit = curl_init();
  curl_setopt($cInit, CURLOPT_URL, $url);
  curl_setopt($cInit, CURLOPT_RETURNTRANSFER, 1); // 1 = TRUE
  curl_setopt($cInit, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
  curl_setopt($cInit, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

  $output = curl_exec($cInit);
  $response = curl_getinfo($cInit, CURLINFO_HTTP_CODE);

  if ($response == "200") {
    $result = json_decode($output, true);
  } else {
    $info = "No info found " . $response;
  }

  if ($compare == false) {
    $info = "Latest Release: " . $result[0]['name'] . " <br />Download: <a href=" . $result[0]['zipball_url'] . " rel=\"no-follow\" >" . $result[0]['name'] . "</a> <br />published: " . $result[0]['published_at'] . "\n";
  } else {
    $info = array(
      'tag_name' => $result[0]['tag_name'],
      'html_url' => $result[0]['html_url'],
      'zipball_url' => $result[0]['zipball_url'],
      'published_at' => $result[0]['published_at'],
      'body' => $result[0]['body'],
      'author' => $result[0]['author']['login']
    );
  }

  curl_close($cInit);

  return $info;
}

function download_img_from_url($url, $imageName)
{

  if (!file_exists($imageName)) {

    $ch = curl_init($url);
    $fp = fopen($imageName, 'wb');
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    //curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);
    fclose($fp);
  }
}

function cleanImageName($post_name, $type = null)
{

  $r = array(' ', '-', '.');

  if (class_exists('CeonURIMappingAdmin')) {

    require_once(DIR_FS_CATALOG . DIR_WS_CLASSES . 'class.CeonURIMappingAdmin.php');
    $handleUri = new CeonURIMappingAdmin();

    $lang_code = $_SESSION['languages_code'];

    $name = $handleUri->_convertStringForURI(trim($post_name), $lang_code);
    //some extra string checks

    if ($type == 'lower') {
      //for file names
      $post_name = str_replace($r, '_', strtolower($name));
      return $post_name;
    } else {
      // for Folders Name
      $post_name = str_replace($r, '', ucwords($name, '-'));
      return $post_name;
    }
  } elseif (extension_loaded('intl')) {

    $t = str_replace($r, '_', $post_name);
    return transliterator_transliterate('Any-Latin; NFD; [:Nonspacing Mark:] Remove; NFC; Lower();', $t);
  } else {
    return null;
  }
}

function bookx_update_plugin_release($now = true, $days = null)
{
  global $objGit;

  $file = DIR_FS_ADMIN . 'includes/exra_datafiles/bookx/plugin_check.json';
  $msg = '';
  $date = new DateTime(); //this returns the current date time
  $today = $date->format('Y-m-d');

//    if (zen_not_null($days) && zen_date_diff($today, $last_checked) <= -$conf_date) {
//        $last_checked = $read_file->last_check_date;
//        //@todo by days
//    }

  if ($now) {
    foreach ($objGit as $key => $plugin) {
      if ($key !== 'last_check_date') {
        $msg .= (empty($plugin->url)) ? '<span class="text-danger">No url found for ' . $key . '</span><br />' : '<span>Updated Info for ' . $key . '</span><br />';
        $check = check_git_release_for($plugin->url, true);
        if ($tag_name !== $plugin->installed) {
          $objGit->{$key}->last_release = $check['tag_name'];
          $objGit->{$key}->html_url = $check['html_url'];
        }
      }
    }
  }
  $objGit->last_check_date = $today;

  file_put_contents($file, json_encode($objGit, JSON_PRETTY_PRINT));
  return $msg;
}

function pr($v, $vn=null, $dedug = null, $die=null)
{
  echo '<pre>';
  echo $vn;
  print_r($v);
  if ($dedug) {
    debug_print_backtrace();
  }
  echo '</pre>';
  if ($die) die();
}

function vd($v, $n = null)
{
  echo "<pre>$n";
  var_dump($v);
  echo "</pre>";
}

function bookx_get_config($like) {
    global $db;
    $res = $db->Execute("SELECT * FROM ".TABLE_CONFIGURATION." WHERE configuration_key LIKE '%".$like."%'");
    
    $temp = [];
    while(!$res->EOF) {
        $temp[$res->fields['configuration_key']] = $res->fields['configuration_value'];
        $res->MoveNext();
    }
    pr($temp);
}
