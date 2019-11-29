<?php
/**
 * This file is part of the ZenCart add-on Book X which
 * introduces a new product type for books to the Zen Cart
 * shop system. Tested for compatibility on ZC v. 1.5.6
 *
 * For latest version and support visit:
 * https://sourceforge.net/p/zencartbookx
 *
 * @package admin
 * @author  Philou
 * @copyright Copyright 2013
 * @copyright Portions Copyright 2003-2019 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 *
 * @version BookX V 1.0.1
 * @version $Id: collect_info.php 2018-12-28 mesnitu $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}

//pr($_POST);
//pr($_GET);
//pr($objBookxFamily);

$sql = "SELECT configuration_group_id
        FROM " . TABLE_CONFIGURATION_GROUP . "
        WHERE configuration_group_title = 'BookX'";

$config_groups = $db->Execute($sql);

$boox_configuration_group_id = null;

foreach ($config_groups as $config_group) {
  $boox_configuration_group_id = $config_group['configuration_group_id'];
}

$sql = "SELECT configuration_id
        FROM " . TABLE_CONFIGURATION . "
        WHERE configuration_key = 'BOOKX_NEW_PRODUCTS_LOOK_BACK_NUMBER_OF_DAYS'";
$config = $db->Execute($sql);

$boox_configuration_pubdate_look_back_id = null;

foreach ($config as $item) {
  $boox_configuration_pubdate_look_back_id = $item['configuration_id'];
}

$sql = "SELECT configuration_id
        FROM " . TABLE_CONFIGURATION . "
        WHERE configuration_key = 'BOOKX_UPCOMING_PRODUCTS_LOOK_AHEAD_NUMBER_OF_DAYS'";
$config = $db->Execute($sql);

$boox_configuration_pubdate_look_ahead_id = null;

foreach ($config as $item) {
  $boox_configuration_pubdate_look_ahead_id = $item['configuration_id'];
}

/* @var $db queryFactory */
$parameters = array(
  'products_name' => '',
  'products_description' => '',
  'products_url' => '',
  'products_id' => '',
  'products_quantity' => '0',
  'products_model' => '',
  'products_image' => '',
  'products_price' => '0.0000',
  'products_virtual' => DEFAULT_PRODUCT_BOOKX_PRODUCTS_VIRTUAL,
  'products_weight' => '0',
  'products_date_added' => '',
  'products_last_modified' => '',
  'products_date_available' => '',
  'products_status' => '1',
  'products_tax_class_id' => DEFAULT_PRODUCT_BOOKX_TAX_CLASS_ID,
  'manufacturers_id' => '',
  'products_quantity_order_min' => '1',
  'products_quantity_order_units' => '1',
  'products_priced_by_attribute' => '0',
  'product_is_free' => '0',
  'product_is_call' => '0',
  'products_quantity_mixed' => '1',
  'product_is_always_free_shipping' => DEFAULT_PRODUCT_BOOKX_PRODUCTS_IS_ALWAYS_FREE_SHIPPING,
  'products_qty_box_status' => SHOW_PRODUCT_BOOKX_INFO_QUANTITY,
  'products_quantity_order_max' => '0',
  'products_sort_order' => '0',
  'products_discount_type' => '0',
  'products_discount_type_from' => '0',
  'products_price_sorter' => '0',
  'master_categories_id' => '',
  'products_subtitle' => '',
  'bookx_publisher_id' => '0',
  'bookx_series_id' => '0',
  'bookx_imprint_id' => '0',
  'bookx_binding_id' => '0',
  'bookx_condition_id' => '0',
  'publishing_date' => '',
  'pages' => '',
  'volume' => '',
  'size' => '',
  'isbn' => '',
  'isbn_display' => '',
  'bookx_family_id' => '0'
);

$pInfo = new objectInfo($parameters);

$product_assigned_authors = [];
$product_assigned_genres = [];

/**
 * @since v1.0.0
 * This familie obj is initiated in BookX admin observer -> bookx_notify_begin_admin_products.
 * setFamilies are set here for visualization, but they could be set on the observer.
 */
//$objBookxFamily->setFamilies_list();


if (isset($_GET['pID']) && empty($_POST)) { //" . DATE_FORMAT_SHORT . "
  $sql = "SELECT pd.products_name, pd.products_description, pd.products_url,
                 p.products_id, p.products_quantity, p.products_model, p.manufacturers_id,
                 p.products_image, p.products_price, p.products_virtual, p.products_weight,
                 p.products_date_added, p.products_last_modified,
                 DATE_FORMAT(p.products_date_available, '%Y-%m-%d') AS
                 products_date_available, p.products_status, p.products_tax_class_id, p.products_image,
                 DATE_FORMAT(p.products_date_available, '%Y-%m-%d') AS products_date_available, p.products_status,
                 p.products_tax_class_id, be.bookx_publisher_id, be.bookx_series_id, be.bookx_imprint_id,
                 be.bookx_binding_id, be.bookx_printing_id, be.bookx_condition_id,
                 DATE_FORMAT(be.publishing_date, '%Y-%m-%d') AS publishing_date, be.pages, be.volume, be.size, be.isbn,
                 CONCAT_WS('-', SUBSTRING(be.isbn,1,3), SUBSTRING(be.isbn,4,1), SUBSTRING(be.isbn,5,6), SUBSTRING(be.isbn,11,2), SUBSTRING(be.isbn,13,1)) AS isbn_display,
                 DATEDIFF('" . date('Y-m-d') . "',
                 CONCAT_WS('-', SUBSTRING(be.publishing_date, 1,4 ),
                 IF(SUBSTRING(be.publishing_date, 6,2 ) = '00', '01', SUBSTRING(be.publishing_date, 6,2 ) ),
                 IF(SUBSTRING(be.publishing_date, 9,2 ) = '00', '01', SUBSTRING(be.publishing_date, 9,2 )))) AS pub_date_diff,
                 bed.products_subtitle,
                 p.products_quantity_order_min, p.products_quantity_order_units, p.products_priced_by_attribute,
                 p.product_is_free, p.product_is_call, p.products_quantity_mixed,
                 p.product_is_always_free_shipping, p.products_qty_box_status, p.products_quantity_order_max,
                 p.products_sort_order,
                 p.products_discount_type, p.products_discount_type_from,
                 p.products_price_sorter, p.master_categories_id
          FROM " . TABLE_PRODUCTS . " p
          LEFT JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd ON p.products_id = pd.products_id
            AND pd.language_id = " . (int)$_SESSION['languages_id'] . "
          LEFT JOIN " . TABLE_PRODUCT_BOOKX_EXTRA . " be ON p.products_id = be.products_id
          LEFT JOIN " . TABLE_PRODUCT_BOOKX_EXTRA_DESCRIPTION . " bed ON be.products_id = bed.products_id
            AND bed.languages_id = " . (int)$_SESSION['languages_id'] . "
          WHERE p.products_id = " . (int)$_GET['pID'];
  $product = $db->Execute($sql);

  $pInfo->updateObjectInfo($product->fields);

  $assigned_authors = $db->Execute("SELECT *
                                    FROM " . TABLE_PRODUCT_BOOKX_AUTHORS_TO_PRODUCTS . "
                                    WHERE products_id = " . (int)$_GET['pID']);

  foreach ($assigned_authors as $assigned_author) {
    $product_assigned_authors[] = [
      'primary_id' => $assigned_author['primary_id'],
      'bookx_author_id' => $assigned_author['bookx_author_id'],
      'bookx_author_type_id' => $assigned_author['bookx_author_type_id']
    ];
  }

  $assigned_genres = $db->Execute("SELECT *
                                   FROM " . TABLE_PRODUCT_BOOKX_GENRES_TO_PRODUCTS . "
                                   WHERE products_id = " . (int)$_GET['pID']);

  foreach ($assigned_genres as $assigned_genre) {
    $product_assigned_genres[] = [
      'primary_id' => $assigned_genre['primary_id'],
      'bookx_genre_id' => $assigned_genre['bookx_genre_id']
    ];
  }

  /**
   * @since v1.0.0
   */
  $pInfo->bookx_family_id = $objBookxFamily->family_id;

  $assigned_bookx_family[] = [
    'bookx_family_name' => $objBookxFamily->family_name,
    'bookx_family_id' => $objBookxFamily->family_id];
} elseif (zen_not_null($_POST)) {

  $pInfo->updateObjectInfo($_POST);

  $products_name = isset($_POST['products_name']) ? $_POST['products_name'] : '';
  $products_description = isset($_POST['products_description']) ? $_POST['products_description'] : '';
  $products_url = isset($_POST['products_url']) ? $_POST['products_url'] : '';
  // bookx extra fields
  $products_subtitle = isset($_POST['products_subtitle']) ? $_POST['products_subtitle'] : '';

  if (isset($_POST['bookx_genre_id']) && is_array($_POST['bookx_genre_id']) && !empty($_POST['bookx_genre_id'])) {
    $bookx_genre_ids = $_POST['bookx_genre_id'];
    foreach ($bookx_genre_ids as $genre_id) {
      $product_assigned_genres[] = [
        'primary_id' => '',
        'bookx_genre_id' => $genre_id
      ];
    }
  }

  if (isset($_POST['bookx_author_id']) && is_array($_POST['bookx_author_id']) && !empty($_POST['bookx_author_id'])) {
    $bookx_author_ids = $_POST['bookx_author_id'];
    foreach ($bookx_author_ids as $key => $author_id) {
      $bookx_author_type_id = (isset($_POST['bookx_author_type_id']) &&
          is_array($_POST['bookx_author_type_id']) &&
          !empty($_POST['bookx_author_type_id']) ? $_POST['bookx_author_type_id'][$key] : '');
      $product_assigned_authors[] = [
        'primary_id' => '',
        'bookx_author_id' => $author_id,
        'bookx_author_type_id' => $bookx_author_type_id
      ];
    }
  }
}

//** look for additional custom collect_info*.php files and include now **//
$incl_dir = @dir(DIR_FS_ADMIN . '/includes/modules/product_bookx');
while ($file = $incl_dir->read()) {
  if ('collect_info_' == substr($file, 0, 13) && 'collect_info_metatags.php' != $file) {
    include_once DIR_FS_ADMIN . '/includes/modules/product_bookx/' . $file; // This should fill variable $extra_html which will be included below
  }
}
$incl_dir->close();

/* $pub_date_month_has_no_day = 'true';
  if (isset($pInfo->publishing_date) && '00' != substr($pInfo->publishing_date, 8, 2)) {
  $pub_date_month_has_no_day = 'false';
  } */

$authors_array = [
  [
    'id' => '',
    'text' => TEXT_NONE
    ]];

$authors = $db->Execute("SELECT bookx_author_id, author_name, author_default_type
                         FROM " . TABLE_PRODUCT_BOOKX_AUTHORS . "
                         ORDER BY author_name");

foreach ($authors as $author) {
  $authors_array[] = [
    'id' => $author['bookx_author_id'],
    'text' => $author['author_name'],
    'default_type' => $author['author_default_type']
  ];
}

$author_types_array = [
  [
    'id' => '',
    'text' => TEXT_NONE
    ]];
$author_types = $db->Execute("SELECT at.bookx_author_type_id, atd.type_description
                              FROM " . TABLE_PRODUCT_BOOKX_AUTHOR_TYPES . " at
                              LEFT JOIN " . TABLE_PRODUCT_BOOKX_AUTHOR_TYPES_DESCRIPTION . " atd ON at.bookx_author_type_id = atd.bookx_author_type_id
                                AND atd.languages_id = " . (int)$_SESSION['languages_id'] . "
                              ORDER BY at.type_sort_order, atd.type_description");

foreach ($author_types as $author_type) {
  $author_types_array[] = [
    'id' => $author_type['bookx_author_type_id'],
    'text' => $author_type['type_description']
  ];
}

$publisher_array = [
  [
    'id' => '',
    'text' => TEXT_NONE
    ]];
$publishers = $db->Execute("SELECT bookx_publisher_id, publisher_name
                            FROM " . TABLE_PRODUCT_BOOKX_PUBLISHERS . "
                            ORDER BY publisher_name ASC");
foreach ($publishers as $publisher) {
  $publisher_array[] = [
    'id' => $publisher['bookx_publisher_id'],
    'text' => $publisher['publisher_name']
  ];
}

$imprint_array = [
  [
    'id' => '',
    'text' => TEXT_NONE
    ]];
$imprints = $db->Execute("SELECT bookx_imprint_id, imprint_name
                          FROM " . TABLE_PRODUCT_BOOKX_IMPRINTS . "
                          ORDER BY imprint_name ASC");
foreach ($imprints as $imprint) {
  $imprint_array[] = [
    'id' => $imprint['bookx_imprint_id'],
    'text' => $imprint['imprint_name']
  ];
}

$genre_array = [
  [
    'id' => '',
    'text' => TEXT_NONE
    ]];
$genres = $db->Execute("SELECT g.bookx_genre_id, gd.genre_name
                       FROM " . TABLE_PRODUCT_BOOKX_GENRES . " g
                       LEFT JOIN " . TABLE_PRODUCT_BOOKX_GENRES_DESCRIPTION . " gd ON g.bookx_genre_id = gd.bookx_genre_id
                         AND gd.languages_id = " . (int)$_SESSION['languages_id'] . "
                       ORDER BY gd.genre_name ASC");

foreach ($genres as $genre) {
  $genre_array[] = [
    'id' => $genre['bookx_genre_id'],
    'text' => $genre['genre_name']
  ];
}

$series_array = [
  [
    'id' => '',
    'text' => TEXT_NONE
    ]];
$series = $db->Execute("SELECT s.bookx_series_id, sd.series_name
                        FROM " . TABLE_PRODUCT_BOOKX_SERIES . " s
                        LEFT JOIN " . TABLE_PRODUCT_BOOKX_SERIES_DESCRIPTION . " sd ON s.bookx_series_id = sd.bookx_series_id
                          AND sd.languages_id = " . (int)$_SESSION['languages_id'] . "
                        ORDER BY sd.series_name ASC");

foreach ($series as $serie) {
  $series_array[] = [
    'id' => $serie['bookx_series_id'],
    'text' => $serie['series_name']
  ];
}

$binding_array = [
  [
    'id' => '',
    'text' => TEXT_NONE
    ]];
$bindings = $db->Execute("SELECT b.bookx_binding_id, bd.binding_description
                         FROM " . TABLE_PRODUCT_BOOKX_BINDING . " b
                         LEFT JOIN " . TABLE_PRODUCT_BOOKX_BINDING_DESCRIPTION . " bd ON b.bookx_binding_id = bd.bookx_binding_id
                           AND bd.languages_id = " . (int)$_SESSION['languages_id'] . "
                         ORDER BY b.binding_sort_order, bd.binding_description");

foreach ($bindings as $binding) {
  $binding_array[] = [
    'id' => $binding['bookx_binding_id'],
    'text' => $binding['binding_description']
  ];
}

$printing_array = [
  [
    'id' => '',
    'text' => TEXT_NONE
    ]];
$printings = $db->Execute("SELECT p.bookx_printing_id, pd.printing_description
                           FROM " . TABLE_PRODUCT_BOOKX_PRINTING . " p
                           LEFT JOIN " . TABLE_PRODUCT_BOOKX_PRINTING_DESCRIPTION . " pd ON p.bookx_printing_id = pd.bookx_printing_id
                             AND pd.languages_id = " . (int)$_SESSION['languages_id'] . "
                           ORDER BY p.printing_sort_order, pd.printing_description");

foreach ($printings as $printing) {
  $printing_array[] = [
    'id' => $printing['bookx_printing_id'],
    'text' => $printing['printing_description']
  ];
}

$condition_array = [
  [
    'id' => '',
    'text' => TEXT_NONE
    ]];
$conditions = $db->Execute("SELECT c.bookx_condition_id, cd.condition_description
                            FROM " . TABLE_PRODUCT_BOOKX_CONDITIONS . " c
                            LEFT JOIN " . TABLE_PRODUCT_BOOKX_CONDITIONS_DESCRIPTION . " cd ON c.bookx_condition_id = cd.bookx_condition_id
                              AND cd.languages_id = " . (int)$_SESSION['languages_id'] . "
                            ORDER BY c.condition_sort_order, cd.condition_description");

foreach ($conditions as $condition) {
  $condition_array[] = [
    'id' => $condition['bookx_condition_id'],
    'text' => $condition['condition_description']
  ];
}

/**
 * @since v1.0.0
 */
if($objBookxFamily->use_families == true) {
    $families_array = [
  [
    'id' => '',
    'text' => TEXT_NONE
    ]];
foreach ($objBookxFamily->families_list as $family) {
  $families_array[] = [
    'id' => $family['bookx_family_id'],
    'text' => $family['bookx_family_name']
  ];
}
}

$category_lookup = $db->Execute("SELECT *
                                 FROM " . TABLE_CATEGORIES . " c,
                                      " . TABLE_CATEGORIES_DESCRIPTION . " cd
                                 WHERE c.categories_id = " . (int)$current_category_id . "
                                 AND c.categories_id = cd.categories_id
                                 AND cd.language_id = " . (int)$_SESSION['languages_id']);
if (!$category_lookup->EOF) {
  $cInfo = new objectInfo($category_lookup->fields);
} else {
  $cInfo = new objectInfo(array());
}

$manufacturers_array = array(array(
    'id' => '',
    'text' => TEXT_NONE));
$manufacturers = $db->Execute("SELECT manufacturers_id, manufacturers_name
                               FROM " . TABLE_MANUFACTURERS . "
                               ORDER BY manufacturers_name");
foreach ($manufacturers as $manufacturer) {
  $manufacturers_array[] = array(
    'id' => $manufacturer['manufacturers_id'],
    'text' => $manufacturer['manufacturers_name']
  );
}

$tax_class_array = array(array(
    'id' => '0',
    'text' => TEXT_NONE));
$tax_class = $db->Execute("SELECT tax_class_id, tax_class_title
                           FROM " . TABLE_TAX_CLASS . "
                           ORDER BY tax_class_title");
foreach ($tax_class as $item) {
  $tax_class_array[] = array(
    'id' => $item['tax_class_id'],
    'text' => $item['tax_class_title']);
}

$languages = zen_get_languages();

// set to out of stock if categories_status is off and new product or existing products_status is off
if (zen_get_categories_status($current_category_id) == 0 && $pInfo->products_status != 1) {
  $pInfo->products_status = 0;
}
?>

<style>
  .bookx-data {
      background-color: #ffdb94;
  }
  .bookx_article_status_explain {
      padding: 1rem;
      font-weight: bold;
  }
  #author_pulldowns select, #genre_pulldowns select {
      margin-bottom: 1rem;
  }
  .no-padding {
      padding:0;
  }
</style>

<script>

  var tax_rates = new Array();
<?php
for ($i = 0, $n = sizeof($tax_class_array); $i < $n; $i++) {
  if ($tax_class_array[$i]['id'] > 0) {
    echo 'tax_rates["' . $tax_class_array[$i]['id'] . '"] = ' . zen_get_tax_rate_value($tax_class_array[$i]['id']) . ';' . "\n";
  }
}
?>

  function doRound(x, places) {
      return Math.round(x * Math.pow(10, places)) / Math.pow(10, places);
  }

  function getTaxRate() {
      var selected_value = document.forms["new_product"].products_tax_class_id.selectedIndex;
      var parameterVal = document.forms["new_product"].products_tax_class_id[selected_value].value;

      if ((parameterVal > 0) && (tax_rates[parameterVal] > 0)) {
          return tax_rates[parameterVal];
      } else {
          return 0;
      }
  }

  function updateGross() {
      var taxRate = getTaxRate();
      var grossValue = document.forms["new_product"].products_price.value;

      if (taxRate > 0) {
          grossValue = grossValue * ((taxRate / 100) + 1);
      }

      document.forms["new_product"].products_price_gross.value = doRound(grossValue, 4);
  }

  function updateNet() {
      var taxRate = getTaxRate();
      var netValue = document.forms["new_product"].products_price_gross.value;

      if (taxRate > 0) {
          netValue = netValue / ((taxRate / 100) + 1);
      }

      document.forms["new_product"].products_price.value = doRound(netValue, 4);
  }

  var authorDefaulTypes = {
<?php
$divider = '';
foreach ($authors_array as $author) {
  if (array_key_exists('default_type', $author)) {
    echo $divider, $author['id'] . ': ' . ($author['default_type'] ? $author['default_type'] : 'null');
    if ('' == $divider) {
      $divider = ',';
    }
  }
}
?>
  }
//console.log(authorDefaulTypes);
  function setAuthorDefaultType(authorSelect) {

      var authorSelectId = authorSelect.getAttribute("id");
      var authorTypeSelectId = "selecttype" + authorSelectId.substring(6);
      var authorId = authorSelect.options[authorSelect.selectedIndex].value;

      if (authorId in authorDefaulTypes) {

          var authorTypeSelect = document.getElementById(authorTypeSelectId);
          var opts = authorTypeSelect.options.length;

          for (var i = 0; i < opts; i++) {
              if (authorTypeSelect.options[i].value == authorDefaulTypes[authorId]) {
                  authorTypeSelect.options[i].selected = true;
                  break;
              }
          }
      }
  }

  var authorCounter = null;
  function addAuthorPulldown(counter) {
      if (null != authorCounter) {
          counter = authorCounter + 1;
      }

      if (0 == counter) {
          counter = 1;
      }
      var br = document.createElement("div");
      br.setAttribute("class", "clearfix");
      // to float the select form
      var container = document.createElement("DIV");
      container.setAttribute("id", "container-" + counter);
      container.setAttribute("class", "col-sm-8 no-padding");

      var container1 = document.createElement("DIV");
      container1.setAttribute("id", "container1-" + counter);
      container1.setAttribute("class", "col-sm-4");

      var newAuthorSelect = document.getElementById("blank_bookx_author_id").cloneNode(true);
      newAuthorSelect.setAttribute("name", "bookx_author_id[" + counter + "]");
      newAuthorSelect.setAttribute("id", "select" + counter);
      newAuthorSelect.setAttribute("class", "form-control");

      var authorLabel = document.createElement("LABEL");
      var author_label_text = document.createTextNode(<?php echo '"' . TEXT_PRODUCTS_BOOKX_AUTHOR . ' "'; ?>);
      authorLabel.appendChild(author_label_text);
      authorLabel.setAttribute("for", "select" + counter);

      document.getElementById("author_pulldowns").appendChild(container);
      document.getElementById("container-" + counter).appendChild(authorLabel);
      document.getElementById("container-" + counter).appendChild(newAuthorSelect);

<?php
if (1 < count($author_types_array)) {
  //**** don't even include this code if there are zero author typed defined'
  ?>
        var newAuthorTypeSelect = document.getElementById("blank_bookx_author_type_id").cloneNode(true);

        if (newAuthorTypeSelect.options.length > 1) {

            newAuthorTypeSelect.setAttribute("name", "bookx_author_type_id[" + counter + "]");
            newAuthorTypeSelect.setAttribute("id", "selecttype" + counter);
            newAuthorTypeSelect.setAttribute("class", "form-control col-sm-3");

            var authorTypeLabel = document.createElement("LABEL");
            var authorType_label_text = document.createTextNode(<?php echo '"' . TEXT_PRODUCTS_BOOKX_AUTHOR_TYPE . ' "'; ?>);
            authorTypeLabel.setAttribute("for", "selecttype" + counter);
            authorTypeLabel.appendChild(authorType_label_text);

            document.getElementById("author_pulldowns").appendChild(container1);
            document.getElementById("container1-" + counter).appendChild(authorTypeLabel);
            document.getElementById("container1-" + counter).appendChild(newAuthorTypeSelect);
        }
<?php } ?>
      document.getElementById("author_pulldowns").appendChild(br);

      authorCounter = counter;
  }


  var genreCounter = null;

  function addGenrePulldown(counter) {
      if (null != genreCounter) {
          counter = genreCounter + 1;
      }

      if (0 == counter) {
          counter = 1;
      }

      var newGenreSelect = document.getElementById("blank_bookx_genre_id").cloneNode(true);

      newGenreSelect.setAttribute("name", "bookx_genre_id[" + counter + "]");
      newGenreSelect.setAttribute("id", "select" + counter);
      newGenreSelect.setAttribute("class", "form-control");

      document.getElementById("genre_pulldowns").appendChild(newGenreSelect);

      genreCounter = counter;
  }

  function checkISBN() {
      var isbnOrig = document.forms["new_product"].isbn.value;
      if (isbnOrig != "") {
          var isbnTXT = isbnOrig;
          while (isbnTXT.lastIndexOf("-") > 0) {
              isbnTXT = isbnTXT.replace(/-/, "");
          }
          isbnTXT = isbnTXT.substring(0, 13);
          document.forms["new_product"].isbn.value = isbnTXT;
          var checkDigit = isbnTXT.charAt(12);
          checkDigit = parseInt(checkDigit);
          var calculatedCheckDigit = 10 - ((parseInt(isbnTXT.charAt(0)) + parseInt(isbnTXT.charAt(1)) * 3 + parseInt(isbnTXT.charAt(2)) + parseInt(isbnTXT.charAt(3)) * 3 + parseInt(isbnTXT.charAt(4)) + parseInt(isbnTXT.charAt(5)) * 3 + parseInt(isbnTXT.charAt(6)) + parseInt(isbnTXT.charAt(7)) * 3 + parseInt(isbnTXT.charAt(8)) + parseInt(isbnTXT.charAt(9)) * 3 + parseInt(isbnTXT.charAt(10)) + parseInt(isbnTXT.charAt(11)) * 3) % 10);

          if (calculatedCheckDigit == "10") {
              calculatedCheckDigit = "0";
          }

          if (calculatedCheckDigit != checkDigit) {
              document.getElementById("isbn_display").innerHTML = <?php echo '"<span class=\"alert\">' . sprintf(TEXT_JAVASCRIPT_ISBN_WRONG_CHECKDIGIT, ' + isbnTXT + ', '+ calculatedCheckDigit +') . '</span>"'; ?>;
          }

          if (13 == isbnTXT.length) {
              // CONCAT_WS("-", SUBSTRING(pe.isbn,1,3), SUBSTRING(pe.isbn,4,1), SUBSTRING(pe.isbn,5,6), SUBSTRING(pe.isbn,11,2), SUBSTRING(pe.isbn,13,1)) AS isbn_display,
              document.getElementById("isbn_display").innerHTML = isbnTXT.substring(0, 3) + '-' + isbnTXT.substring(3, 4) + '-' + isbnTXT.substring(4, 10) + '-' + isbnTXT.substring(10, 12) + '-' + isbnTXT.substring(12);
          }
      }
  }

  /*
   var localeMonthNames = new Array();
   localeMonthNames[0] = "<?php echo strftime('%B', mktime(0, 0, 0, 1, 1)); ?>";
   localeMonthNames[1] = "<?php echo strftime('%B', mktime(0, 0, 0, 2, 1)); ?>";
   localeMonthNames[2] = "<?php echo strftime('%B', mktime(0, 0, 0, 3, 1)); ?>";
   localeMonthNames[3] = "<?php echo strftime('%B', mktime(0, 0, 0, 4, 1)); ?>";
   localeMonthNames[4] = "<?php echo strftime('%B', mktime(0, 0, 0, 5, 1)); ?>";
   localeMonthNames[5] = "<?php echo strftime('%B', mktime(0, 0, 0, 6, 1)); ?>";
   localeMonthNames[6] = "<?php echo strftime('%B', mktime(0, 0, 0, 7, 1)); ?>";
   localeMonthNames[7] = "<?php echo strftime('%B', mktime(0, 0, 0, 8, 1)); ?>";
   localeMonthNames[8] = "<?php echo strftime('%B', mktime(0, 0, 0, 9, 1)); ?>";
   localeMonthNames[9] = "<?php echo strftime('%B', mktime(0, 0, 0, 10, 1)); ?>";
   localeMonthNames[10] = "<?php echo strftime('%B', mktime(0, 0, 0, 11, 1)); ?>";
   localeMonthNames[11] = "<?php echo strftime('%B', mktime(0, 0, 0, 12, 1)); ?>";
   
   
   function previewDisplayDate() {
   var dateDisplayString = '';
   var dateFormatShort = '<?php echo DATE_FORMAT_SHORT; ?>';
   var dateFormatMonthAndYear = '<?php echo DATE_FORMAT_MONTH_AND_YEAR; ?>';
   
   var dateString = document.forms["new_product"].publishing_date.value;
   var yearString = dateString.substring(0, 4);
   var monthString = dateString.substring(5, 7);
   var dayString = dateString.substring(8, 10);
   
   var parsedDate = new Date(yearString, monthString, dayString);
   
   switch (true) {
   case ('00' == monthString):
   dateDisplayString = yearString;
   break;
   
   case ('00' == dayString):
   var mo = localeMonthNames[parsedDate.getMonth()];
   dateDisplayString = dateFormatMonthAndYear.replace('%Y', yearString).replace('%M', localeMonthNames[parsedDate.getMonth()]);
   break;
   
   default:
   dateDisplayString = dateFormatShort.replace('%Y', yearString).replace('%m', monthString).replace('%d', dayString);
   break;
   }
   document.getElementById("publishing_date_display").innerHTML = dateDisplayString;
   }
   
   function previewDisplayDate() {
   
   var dateDisplayString = document.forms["new_product"].publishing_date.value;
   document.getElementById("publishing_date_display").innerHTML = dateDisplayString;
   }
   */

  Date.daysBetween = function (date1, date2) {
      //Get 1 day in milliseconds
      var one_day = 1000 * 60 * 60 * 24;

      // Convert both dates to milliseconds
      var date1_ms = date1.getTime();
      var date2_ms = date2.getTime();

      // Calculate the difference in milliseconds
      var difference_ms = date2_ms - date1_ms;

      // Convert back to days and return
      return Math.round(difference_ms / one_day);
  }

  Date.addDays = function (date1, numOfDays) {
      var returnDate = new Date();

      // Add days and return
      returnDate.setDate(date1.getDate() + parseInt(numOfDays))
      return returnDate;

  }

  function formatDateYmd(d) {

      month = '' + (d.getMonth() + 1);
      day = '' + d.getDate();
      year = d.getFullYear();

      if (month.length < 2)
          month = '0' + month;
      if (day.length < 2)
          day = '0' + day;

      return [year, month, day].join('-');
  }

  function log(log) {
      console.log('log-> ' + log);
  }

  function determineBookxProductStatusMessage() {
      var statusMessage = '';

      var lookBackNoOfDays = <?php echo BOOKX_NEW_PRODUCTS_LOOK_BACK_NUMBER_OF_DAYS; ?>;
      var lookAheadNoOfDays = <?php echo BOOKX_UPCOMING_PRODUCTS_LOOK_AHEAD_NUMBER_OF_DAYS; ?>;

      var zcProductStatus = document.forms["new_product"].products_status.value;
      var zcDateAvailable = document.forms["new_product"].products_date_available.value;
      var zcProductsQuantity = document.forms["new_product"].products_quantity.value;

      var publishingDate = document.forms["new_product"].publishing_date.value;
      var publishingDateObject = null;
      var dateDiff = null;
      var dateToday = new Date();

      var upcomingCutoffDate = Date.addDays(dateToday, <?php echo BOOKX_UPCOMING_PRODUCTS_LOOK_AHEAD_NUMBER_OF_DAYS; ?>);

      if ('' != publishingDate && publishingDate.length == 10) {
          var publishingDateArray = [parseInt(publishingDate.substr(0, 4)), parseInt(publishingDate.substr(5, 2)) - 1, parseInt(publishingDate.substr(8, 2))];
          if (-1 == publishingDateArray[1])
              publishingDateArray[1] = 0; /* Javascript months go from 0 to 11 */
          if (0 == publishingDateArray[2])
              publishingDateArray[2] = 1;
          publishingDateObject = new Date(publishingDateArray[0], publishingDateArray[1], publishingDateArray[2], 1, 0, 0, 0);
          dateDiff = Date.daysBetween(new Date(), publishingDateObject);
      }

      switch (true) {
          case (1 != zcProductStatus):
              /* Product status is set to not display */
              statusMessage = '<span class="alert"><?php echo TEXT_PRODUCT_STATUS_NOT_DISPLAYED_DUE_TO_PRODUCT_STATUS; ?></span>';
              break;

          case (0 < zcProductsQuantity && '' != publishingDate && dateDiff !== null && dateDiff <= 0 && dateDiff > - 1 * lookBackNoOfDays):
              /**
               *  Product has a set Publishing Date and that date is in the past but within the range of a "new book".
               * It also has available stock, so we treat as "new"
               */
              statusMessage = '<span class="bookx_article_status_explain"><?php printf(TEXT_PRODUCT_STATUS_DISPLAYED_AS_NEW, BOOKX_NEW_PRODUCTS_LOOK_BACK_NUMBER_OF_DAYS); ?></span>';
              break;

          case (0 == zcProductsQuantity && '' != publishingDate && dateDiff !== null && dateDiff <= 0 && dateDiff > - 1 * lookBackNoOfDays):
              /**
               *  There's no available stock and Product has a set Publishing Date and that date is in the past within the range of a "new book"
               */
              if ('' != zcDateAvailable && zcDateAvailable > formatDateYmd(dateToday)) {
                  /**
                   *  Product has a "Date Available" which is in the future, so we show it as coming soon
                   */
                  statusMessage = '<span class="bookx_article_status_explain"><?php echo TEXT_PRODUCT_STATUS_CONSIDERED_TEMPORARILY_UNAVAILABLE; ?></span>';
              } else {
                  /**
                   *  We treat this book as still upcoming, since it is in the "new" range, but not yet in stock
                   */
                  statusMessage = '<span class="bookx_article_status_explain"><?php echo TEXT_PRODUCT_STATUS_CONSIDERED_NEW_BUT_UPCOMING_WITHOUT_STOCK; ?></span>';
              }
              break;

          case (0 == zcProductsQuantity && ('' == publishingDate || ('' != publishingDate && dateDiff !== null && dateDiff < - 1 * lookBackNoOfDays))):
              /**
               *  There's no available stock and Publishing Date is not set or older than range for "new" books
               */

              if ('' != zcDateAvailable && zcDateAvailable > formatDateYmd(dateToday)) {
                  /**
                   * Product has a "Date Available" which is in the future, so we show it as coming soon
                   */
                  statusMessage = '<span class="bookx_article_status_explain"><?php echo TEXT_PRODUCT_STATUS_CONSIDERED_TEMPORARILY_UNAVAILABLE; ?></span>';
              } else {
                  /**
                   *  We treat this book as permanently out of stock
                   */
                  statusMessage = '<span class="bookx_article_status_explain"><?php echo TEXT_PRODUCT_STATUS_CONSIDERED_OUT_OF_PRINT; ?></span>';
              }
              break;

          case (0 < zcProductsQuantity && ('' == publishingDate || ('' != publishingDate && dateDiff !== null && dateDiff < - 1 * lookBackNoOfDays))):
              /**
               *  This book is in stock and Publishing Date is not set or older than range for "new" books, so we treat it as "available"
               */
              statusMessage = '<span class="bookx_article_status_explain"><?php printf(TEXT_PRODUCT_STATUS_CONSIDERED_REGULAR_IN_STOCK, BOOKX_NEW_PRODUCTS_LOOK_BACK_NUMBER_OF_DAYS); ?></span>';
              break;

          case ('' != publishingDate && dateDiff !== null && 0 < dateDiff && dateDiff <= lookAheadNoOfDays):
              /* Product has a set Publishing Date and that date is in the future but within the range of an "upcoming book",
               * so we treat it as upcoming
               */
              statusMessage = '<?php printf(TEXT_PRODUCT_STATUS_DISPLAYED_AS_UPCOMING, zen_href_link(FILENAME_CONFIGURATION, 'gID=' . $boox_configuration_group_id . '&cID=' . $boox_configuration_pubdate_look_ahead_id), BOOKX_UPCOMING_PRODUCTS_LOOK_AHEAD_NUMBER_OF_DAYS); ?>';

              if (0 < zcProductsQuantity) {
                  /**
                   *  This book also has available stock, so it can be (pre-)ordered
                   */
                  statusMessage = statusMessage + '<?php echo TEXT_PRODUCT_STATUS_DISPLAYED_AS_UPCOMING_PREORDER_OPTION; ?>';
              }

              statusMessage = '<span class="bookx_article_status_explain">' + statusMessage + '</span>';
              break;

          case ('' != publishingDate && dateDiff !== null && dateDiff > lookAheadNoOfDays):
              /**
               * Product has a set Publishing Date and that date is in the future but beyond the range of an "upcoming book",
               * so Bookx will NOT display it
               */
              statusMessage = '<span class="bookx_article_status_explain"><?php printf(TEXT_PRODUCT_STATUS_NOT_DISPLAYED_SINCE_BEYOND_UPCOMING, BOOKX_UPCOMING_PRODUCTS_LOOK_AHEAD_NUMBER_OF_DAYS); ?>' + upcomingCutoffDate.toDateString() + '</span>';
              break;

          default:
              statusMessage = '<span class="bookx_article_status_explain"><?php echo TEXT_PRODUCT_STATUS_DEFAULT_CASE; ?></span>';
      }

      if (<?php echo SHOW_NEW_PRODUCTS_LIMIT; ?> !== 0) {
<?php
$sql_config_value = "SELECT *
                     FROM " . TABLE_CONFIGURATION . "
                     WHERE configuration_key = 'SHOW_NEW_PRODUCTS_LIMIT'";
$check_configure = $db->Execute($sql_config_value);
?>

          statusMessage += '<span class="bookx_article_status_explain">\n\
<?php printf(TEXT_ZC_NEW_PRODUCTS_LIMIT_WARNING, $check_configure->fields['configuration_title'], $check_configure->fields['configuration_value'], zen_href_link(FILENAME_CONFIGURATION, 'gID=' . $check_configure->fields['configuration_group_id'] . '&cID=' . $check_configure->fields['configuration_id'])); ?></span>';
      }

      document.getElementById("bookxProductStatusDisplay").innerHTML = statusMessage;
  }
</script>
<div class="container-fluid">
    <?php
    echo zen_draw_form('new_product', FILENAME_PRODUCT, 'cPath=' . $current_category_id . (isset($_GET['pID']) ? '&pID=' . $_GET['pID'] : '') . '&action=new_product_preview' . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '') . ((isset($_GET['search']) && !empty($_GET['search'])) ? '&search=' . $_GET['search'] : '') . ((isset($_POST['search']) && !empty($_POST['search']) && empty($_GET['search'])) ? '&search=' . $_POST['search'] : ''), 'post', 'enctype="multipart/form-data" class="form-horizontal"');
    if (isset($product_type)) {
      echo zen_draw_hidden_field('product_type', $product_type);
    }
    ?>
  <h3 class="col-sm-11"><?php echo sprintf(TEXT_NEW_PRODUCT, zen_output_generated_category_path($current_category_id)); ?></h3>
  <div class="col-sm-1">
      <?php echo zen_info_image($cInfo->categories_image, $cInfo->categories_name, HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?>
  </div>
   <!-- <span class="fa fa-3x fa-book" aria-hidden="true"></span> -->
  <div>
    <span class="floatButton text-right">
      <button type="submit" class="btn btn-primary"><?php echo IMAGE_PREVIEW; ?></button>&nbsp;&nbsp;<a href="<?php echo zen_href_link(FILENAME_CATEGORY_PRODUCT_LISTING, 'cPath=' . $current_category_id . (isset($_GET['pID']) ? '&pID=' . $_GET['pID'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '') . ((isset($_GET['search']) && !empty($_GET['search'])) ? '&search=' . $_GET['search'] : '') . ((isset($_POST['search']) && !empty($_POST['search']) && empty($_GET['search'])) ? '&search=' . $_POST['search'] : '')); ?>" class="btn btn-default" role="button"><?php echo IMAGE_CANCEL; ?></a>
    </span>
  </div>
  <div class="form-group">
      <?php
// show when product is linked
      if (isset($_GET['pID']) && zen_get_product_is_linked($_GET['pID']) == 'true' && $_GET['pID'] > 0) {
        ?>
        <?php echo zen_draw_label(TEXT_MASTER_CATEGORIES_ID, 'master_category', 'class="col-sm-3 control-label"'); ?>
      <div class="col-sm-9 col-md-6">
        <div class="input-group">
          <span class="input-group-addon">
              <?php
              echo zen_image(DIR_WS_IMAGES . 'icon_yellow_on.gif', IMAGE_ICON_LINKED) . '&nbsp;&nbsp;';
              ?>
          </span>
          <?php
          echo zen_draw_pull_down_menu('master_category', zen_get_master_categories_pulldown($_GET['pID']), $pInfo->master_categories_id, 'class="form-control"');
          ?>
        </div>
      </div>
    <?php } else { ?>
      <div class="col-sm-3 text-right">
        <strong>
            <?php echo TEXT_MASTER_CATEGORIES_ID; ?>
        </strong>
      </div>
      <div class="col-sm-9 col-md-6"><?php echo TEXT_INFO_ID . (isset($_GET['pID']) && $_GET['pID'] > 0 ? $pInfo->master_categories_id . ' ' . zen_get_category_name($pInfo->master_categories_id, $_SESSION['languages_id']) : $current_category_id . ' ' . zen_get_category_name($current_category_id, $_SESSION['languages_id'])); ?></div>
    <?php } ?>
  </div>
  <div class="form-group">
    <div class="col-sm-offset-3 col-sm-9 col-md-6">
        <?php echo TEXT_INFO_MASTER_CATEGORIES_ID; ?>
    </div>
  </div>
  <?php
// hidden fields not changeable on products page
  echo zen_draw_hidden_field('master_categories_id', $pInfo->master_categories_id);
  echo zen_draw_hidden_field('products_discount_type', $pInfo->products_discount_type);
  echo zen_draw_hidden_field('products_discount_type_from', $pInfo->products_discount_type_from);
  echo zen_draw_hidden_field('products_price_sorter', $pInfo->products_price_sorter);
  ?>
  <div class="col-sm-12 text-center"><?php echo (zen_get_categories_status($current_category_id) == '0' ? TEXT_CATEGORIES_STATUS_INFO_OFF : '') . (isset($out_status) && $out_status == true ? ' ' . TEXT_PRODUCTS_STATUS_INFO_OFF : ''); ?></div>
  <div class="form-group">
      <?php echo zen_draw_label(TEXT_PRODUCTS_STATUS, 'products_status', 'class="col-sm-3 control-label"'); ?>
    <div class="col-sm-9 col-md-6">
      <label class="radio-inline"><?php echo zen_draw_radio_field('products_status', '1', ($pInfo->products_status == 1)) . TEXT_PRODUCT_AVAILABLE; ?></label>
      <label class="radio-inline"><?php echo zen_draw_radio_field('products_status', '0', ($pInfo->products_status == 0)) . TEXT_PRODUCT_NOT_AVAILABLE; ?></label>
    </div>
  </div>
  <div class="form-group">
      <?php echo zen_draw_label(TEXT_PRODUCTS_DATE_AVAILABLE, 'products_date_available', 'class="col-sm-3 control-label"'); ?>
    <div class="col-sm-9 col-md-6">
      <div class="date input-group" id="datepicker">
        <span class="input-group-addon datepicker_icon">
          <i class="fa fa-calendar fa-lg"></i>
        </span>
        <?php echo zen_draw_input_field('products_date_available', $pInfo->products_date_available, 'class="form-control"'); ?>
      </div>
      <span class="help-block errorText">(YYYY-MM-DD)</span>
    </div>
  </div>
  <?php
  /**
   * extra include
   */
  if (isset($extra_html)) {
    echo $extra_html; // this was possibly filled by an included file above
  }
  ?>
  <div class="form-group">
      <?php echo zen_draw_label(TEXT_PRODUCTS_MANUFACTURER, 'manufacturers_id', 'class="col-sm-3 control-label"'); ?>
    <div class="col-sm-9 col-md-6">
        <?php echo zen_draw_pull_down_menu('manufacturers_id', $manufacturers_array, $pInfo->manufacturers_id, 'class="form-control"'); ?>
    </div>
  </div>
  <div class="form-group">
      <?php echo zen_draw_label(TEXT_PRODUCTS_NAME, 'products_name', 'class="col-sm-3 control-label"'); ?>
    <div class="col-sm-9 col-md-6">
        <?php
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
          ?>
        <div class="input-group">
          <span class="input-group-addon">
              <?php echo zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']); ?>
          </span>
          <?php echo zen_draw_input_field('products_name[' . $languages[$i]['id'] . ']', htmlspecialchars(isset($products_name[$languages[$i]['id']]) ? stripslashes($products_name[$languages[$i]['id']]) : zen_get_products_name($pInfo->products_id, $languages[$i]['id']), ENT_COMPAT, CHARSET, TRUE), zen_set_field_length(TABLE_PRODUCTS_DESCRIPTION, 'products_name') . ' class="form-control"'); ?>
        </div>
        <br>
        <?php
      }
      ?>
    </div>
  </div>
  <!-- Starts Bookx Data -->
  <div class="bookx-data well">

    <div id="bookxProductStatusDisplay" class="well well-sm col-md-6 col-md-offset-3">
        <?php
        /* $bookx_new_product_look_back_number_of_days = BOOKX_NEW_PRODUCTS_LOOK_BACK_NUMBER_OF_DAYS;
          $bookx_upcoming_products_look_ahead_number_of_days = BOOKX_UPCOMING_PRODUCTS_LOOK_AHEAD_NUMBER_OF_DAYS;

          switch (true) {
          case (empty($pInfo->products_status)):
          echo '<span class="alert">' . TEXT_PRODUCT_STATUS_NOT_DISPLAYED_DUE_TO_PRODUCT_STATUS . '</span>';
          break;

          case (0 == $pInfo->products_quantity && !empty($pInfo->publishing_date) && $pInfo->pub_date_diff >= -intval($bookx_upcoming_products_look_ahead_number_of_days)):
          echo '<span class="bookx_article_status_explain">' . sprintf(TEXT_PRODUCT_STATUS_DISPLAYED_AS_UPCOMING, $bookx_upcoming_products_look_ahead_number_of_days) . '</span>';
          break;

          case (0 == $pInfo->products_quantity && !empty($pInfo->products_date_available) && $pInfo->products_date_available > date('Y-m-d')):
          echo '<span class="bookx_article_status_explain">' . sprintf(TEXT_PRODUCT_STATUS_DISPLAYED_AS_UPCOMING_WITH_DATE_AVAILABLE) . '</span>';
          break;

          case (0 < $pInfo->products_quantity && !empty($pInfo->publishing_date) && 0 < $pInfo->pub_date_diff && abs($pInfo->pub_date_diff) <= intval($bookx_new_product_look_back_number_of_days)):
          echo '<span class="bookx_article_status_explain">' . sprintf(TEXT_PRODUCT_STATUS_DISPLAYED_AS_NEW, $bookx_new_product_look_back_number_of_days) . '</span>';
          break;

          case (0 == $pInfo->products_quantity && empty($pInfo->date_available) && (empty($pInfo->publishing_date) || (!empty($pInfo->publishing_date) && 0 < $pInfo->pub_date_diff && abs($pInfo->pub_date_diff) > intval($bookx_new_product_look_back_number_of_days) ))):
          echo '<span class="bookx_article_status_explain">' . TEXT_PRODUCT_STATUS_CONSIDERED_OUT_OF_PRINT . '</span>';
          break;

          case (0 < $pInfo->products_quantity):
          echo '<span class="bookx_article_status_explain">' . TEXT_PRODUCT_STATUS_CONSIDERED_REGULAR_IN_STOCK . '</span>';
          break;

          default:
          echo '<span class="bookx_article_status_explain">' . TEXT_PRODUCT_STATUS_DEFAULT_CASE . '</span>';
          break;
          } */
        ?>
    </div>

    <div class="clearfix"></div>

    <!-- *** Field "Subtitle" starts here *** -->
    <div class="form-group">
        <?php echo zen_draw_label(TEXT_PRODUCTS_BOOKX_SUBTITLE, 'products_subtitle', 'class="col-sm-3 control-label"'); ?>
      <div class="col-sm-9 col-md-6">
          <?php
          for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
            ?>
          <div class="input-group">
            <span class="input-group-addon">
                <?php echo zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']); ?>
            </span>
            <?php echo zen_draw_input_field('products_subtitle[' . $languages[$i]['id'] . ']', htmlspecialchars(isset($products_subtitle[$languages[$i]['id']]) ? stripslashes($products_subtitle[$languages[$i]['id']]) : bookx_get_products_subtitle($pInfo->products_id, $languages[$i]['id']), ENT_COMPAT, CHARSET, TRUE), zen_set_field_length(TABLE_PRODUCT_BOOKX_EXTRA_DESCRIPTION, 'products_subtitle') . ' class="form-control"'); ?>
          </div>
          <br />
          <?php
        }
        ?>
      </div>
    </div>

    <!-- *** Field "Volume" starts here *** -->
    <div class="form-group">
        <?php echo zen_draw_label(TEXT_PRODUCTS_BOOKX_VOLUME, 'volume', 'class="col-sm-3 control-label"'); ?>
      <div class="col-sm-9 col-md-6">
          <?php echo zen_draw_input_field('volume', $pInfo->volume, 'class="form-control"'); ?>
      </div>
    </div>


    <?php
    /**
     * @TODO In future, series, authors, could be inserted on product insert
     */
    if(!empty($objBookxFamily->use_families)) { // no families defined
      ?>
      <div class="form-group">
          <?php echo zen_draw_label(TEXT_PRODUCTS_BOOKX_FAMILY, 'bookx_family_id', 'class="col-sm-3 control-label"'); ?>
        <div class="col-sm-7 col-md-4">
          <?php echo zen_draw_pull_down_menu('bookx_family_id', $families_array, $pInfo->bookx_family_id, 'class="form-control"'); ?>         </div>
        <div class="checkbox col-sm-2">
          <label>
              <?php
              $checked = (isset($_POST['ignore_family_discount']) && $_POST['ignore_family_discount'] == 'on' ) ? true : false;
              echo zen_draw_checkbox_field('ignore_family_discount', '', $checked) . LABEL_BOOKX_IGNORE_FAMILY_DISCOUNT;
              ?>
          </label>
        </div>
      </div>

    <?php } // end if loop   ?>

    <!-- *** Field "Series" starts here *** -->
    <?php
    /**
     * @TODO In future, series, authors, could be inserted on product insert
     */
    if (1 < count($series_array)) { // no bindings defined
      ?>
      <div class="form-group">
          <?php echo zen_draw_label(TEXT_PRODUCTS_BOOKX_SERIES, 'bookx_series_id', 'class="col-sm-3 control-label"'); ?>
        <div class="col-sm-9 col-md-6">
          <?php echo zen_draw_pull_down_menu('bookx_series_id', $series_array, $pInfo->bookx_series_id, 'class="form-control"'); ?>         </div>
      </div>
    <?php } // end if loop  ?>


    <!-- *** Field "Authors" starts here *** -->
    <?php if (1 < count($authors_array)) { // no authors defined  ?>
      <div class="form-group">
          <?php echo zen_draw_label(TEXT_PRODUCTS_BOOKX_AUTHORS, 'blank_bookx_author_id', 'class="col-sm-3 control-label"'); ?>
        <div class="col-sm-9 col-md-6">
          <div style="display: none;">
              <?php
              echo zen_draw_pull_down_menu('blank_bookx_author_id', $authors_array, '', 'id="blank_bookx_author_id" onChange="setAuthorDefaultType(this);"');
              if (1 < count($author_types_array)) {
                echo zen_draw_pull_down_menu('blank_bookx_author_type_id', $author_types_array, '', 'id="blank_bookx_author_type_id"');
              }
              ?>
          </div>
          <div id="author_pulldowns" class="row">
              <?php
              $author_counter = 0;

              foreach ($product_assigned_authors as $product_assigned_author) {
                ?>
              <div class="drop_down_div">
                <div class="col-sm-8">
                    <?php
                    echo zen_draw_label(TEXT_PRODUCTS_BOOKX_AUTHOR, 'bookx_author_id[' . $author_counter . ']', '');
                    echo zen_draw_pull_down_menu('bookx_author_id[' . $author_counter . ']', $authors_array, $product_assigned_author['bookx_author_id'], 'class="form-control"');
                    echo zen_draw_hidden_field('assigned_author_db_id[' . $author_counter . ']', $product_assigned_author['primary_id']);
                    ?>
                </div>
                <?php if (1 < count($author_types_array)) { ?>
                  <div class="col-sm-4">
                      <?php
                      echo zen_draw_label(TEXT_PRODUCTS_BOOKX_AUTHOR_TYPE, 'bookx_author_type_id[' . $author_counter . ']');
                      echo zen_draw_pull_down_menu('bookx_author_type_id[' . $author_counter . ']', $author_types_array, $product_assigned_author['bookx_author_type_id'], 'class="form-control"');
                      ?>
                  </div>
                <?php }; ?>
              </div>
              <?php
              $author_counter++;
            }
            ?>
          </div>
          <a href="javascript:void(0);" onclick="addAuthorPulldown(<?php echo $author_counter; ?>);" class="btn btn-primary btn-sm"><?php echo TEXT_PRODUCTS_BOOKX_ADD_AUTHOR; ?></a>
        </div>
      </div>
    <?php } // end if loop   ?>
    <!-- *** Field "Authors" ends here *** -->

    <!-- *** Field "Publisher" starts here *** -->
    <?php if (1 < count($publisher_array)) { // no publishers defined    ?>
      <div class="form-group">
          <?php echo zen_draw_label(TEXT_PRODUCTS_BOOKX_PUBLISHER, 'bookx_publisher_id', 'class="col-sm-3 control-label"'); ?>
        <div class="col-sm-9 col-md-6">
            <?php echo zen_draw_pull_down_menu('bookx_publisher_id', $publisher_array, $pInfo->bookx_publisher_id, 'class="form-control"'); ?>
        </div>
      </div>
      <?php
    } // end if loop
    ?>

    <!-- *** Field "Imprint" starts here *** -->
    <?php if (1 < count($imprint_array)) { // no imprints defined    ?>
      <div class="form-group">
          <?php echo zen_draw_label(TEXT_PRODUCTS_BOOKX_IMPRINT, 'bookx_imprint_id', 'class="col-sm-3 control-label"'); ?>
        <div class="col-sm-9 col-md-6">
            <?php echo zen_draw_pull_down_menu('bookx_imprint_id', $imprint_array, $pInfo->bookx_imprint_id, 'class="form-control"'); ?>
        </div>
      </div>
    <?php } // end if loop    ?>

    <!-- *** Field "Publishing Date" starts here *** -->
    <div class="form-group">
        <?php echo zen_draw_label(TEXT_PRODUCTS_BOOKX_PUBLISHING_DATE . '', 'publishing_date', 'class="col-sm-3 control-label"'); ?>
      <div class="col-sm-9 col-md-6">
        <div class="date input-group">
          <span class="input-group-addon datepicker_icon">
            <i class="fa fa-calendar fa-lg"></i>
          </span>
          <?php echo zen_draw_input_field('publishing_date', $pInfo->publishing_date, 'id="datepicker1" class="form-control"'); ?>
        </div>
        <div class="input-group">
            <?php
            $date_format_options = [
              ['id' => 'yy-mm-dd',
                'text' => 'ISO 8601 - yy-mm-dd'],
              ['id' => 'MM yy',
                'text' => 'Month Year'],
              ['id' => 'yy',
                'text' => 'Year']
            ];
            echo zen_draw_label('Format Options', 'format_date', 'class="control-label"');
            //zen_draw_pull_down_menu($name, $values, $default = '', $parameters = '', $required = false)
            echo zen_draw_pull_down_menu('date_format', $date_format_options, $_POST['date_format'], 'id="format_date" class="form-control"');
            ?>
        </div>
        <span class="help-block">
            <?php
            $bookx_np_number_of_days_edit_url = '<a href="' . zen_href_link(FILENAME_CONFIGURATION, 'gID=' . $boox_configuration_group_id . '&cID=' . $boox_configuration_pubdate_look_back_id) . '" target="_admin_blank">' . TEXT_PRODUCTS_BOOKX_NEW_PRODUCTS_LOOK_BACKWARD_SETTING_LINK . '</a>';
            echo '&nbsp; ' . TEXT_PRODUCTS_BOOKX_ISBN_DISPLAY . ' <span id="publishing_date_display"></span><br />' . zen_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . TEXT_PRODUCTS_BOOKX_USE_PARTIAL_PUBLISHING_DATE . '<br />' . sprintf(TEXT_PRODUCTS_BOOKX_INFO_PUBLISHING_DATE_INFLUENCES_NEW_PRODUCT_DISPLAY, $bookx_new_product_look_back_number_of_days, $bookx_np_number_of_days_edit_url);
            ?>
        </span>
      </div>
    </div>
    <!-- *** Field "Publishing Date" ends here *** -->

    <!-- *** Field "ISBN" starts here *** -->
    <div class="form-group">
        <?php echo zen_draw_label(TEXT_PRODUCTS_BOOKX_ISBN, 'isbn', 'class="col-sm-3 control-label"'); ?>
      <div class="col-sm-9 col-md-6">
          <?php
          echo zen_draw_input_field('isbn', $pInfo->isbn, 'class="form-control" onchange="checkISBN()"');
          ?>
        <div class="help-block">
            <?php
            echo TEXT_PRODUCTS_BOOKX_ISBN_DISPLAY . '<span id="isbn_display">' . $pInfo->isbn_display . '</span>';
            ?>
        </div>
      </div>
    </div>

    <!-- *** Field "No. of pages" starts here *** -->
    <div class="form-group">
        <?php echo zen_draw_label(TEXT_PRODUCTS_BOOKX_PAGES, 'pages', 'class="col-sm-3 control-label"'); ?>
      <div class="col-sm-9 col-md-6">
          <?php echo zen_draw_input_field('pages', $pInfo->pages, 'class="form-control"'); ?>
      </div>
    </div>

    <!-- *** Field "size" starts here *** -->
    <div class="form-group">
        <?php echo zen_draw_label(TEXT_PRODUCTS_BOOKX_SIZE, 'size', 'class="col-sm-3 control-label"'); ?>
      <div class="col-sm-9 col-md-6">
          <?php echo zen_draw_input_field('size', $pInfo->size, 'class="form-control"'); ?>
      </div>
    </div>

    <!-- *** Field "Binding" starts here *** -->
    <?php if (1 < count($binding_array)) { // no bindings defined    ?>
      <div class="form-group">
          <?php echo zen_draw_label(TEXT_PRODUCTS_BOOKX_BINDING, 'bookx_binding_id', 'class="col-sm-3 control-label"'); ?>
        <div class="col-sm-9 col-md-6">
            <?php echo zen_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . zen_draw_pull_down_menu('bookx_binding_id', $binding_array, $pInfo->bookx_binding_id, 'class="form-control"'); ?>
        </div>
      </div>
    <?php } // end if loop     ?>

    <!-- *** Field "Printing" starts here *** -->
    <?php if (1 < count($printing_array)) { // no printings defined   ?>
      <div class="form-group">
          <?php echo zen_draw_label(TEXT_PRODUCTS_BOOKX_PRINTING, 'bookx_printing_id', 'class="col-sm-3 control-label"'); ?>
        <div class="col-sm-9 col-md-6">
            <?php echo zen_draw_pull_down_menu('bookx_printing_id', $printing_array, $pInfo->bookx_printing_id, 'class="form-control"'); ?>
        </div>
      </div>
    <?php } // end if loop    ?>
    <!-- *** Field "Printing" ends here *** -->

    <!-- *** Field "Condition" starts here *** -->
    <?php if (1 < count($condition_array)) { // no printings defined    ?>
      <div class="form-group">
          <?php echo zen_draw_label(TEXT_PRODUCTS_BOOKX_CONDITION, 'bookx_condition_id', 'class="col-sm-3 control-label"'); ?>
        <div class="col-sm-9 col-md-6">
            <?php echo zen_draw_pull_down_menu('bookx_condition_id', $condition_array, $pInfo->bookx_condition_id, 'class="form-control"'); ?>
        </div>
      </div>
    <?php } // end if loop    ?>

    <!-- *** Field "Genres" starts here *** -->
    <?php if (1 < count($genre_array)) { // no genres defined     ?>
      <div class="form-group">
          <?php echo zen_draw_label(TEXT_PRODUCTS_BOOKX_GENRES, 'blank_bookx_genre_id', 'class="col-sm-3 control-label"'); ?>
        <div class="col-sm-9 col-md-6">

          <div style="display: none;">
              <?php echo zen_draw_pull_down_menu('blank_bookx_genre_id', $genre_array, '', 'id="blank_bookx_genre_id"'); ?>
          </div>
          <div id="genre_pulldowns">
              <?php
              $genre_counter = 0;
              foreach ($product_assigned_genres as $product_assigned_genre) {
                echo '<div class="drop_down_div">';
                echo zen_draw_hidden_field('assigned_genre_db_id[' . $genre_counter . ']', $product_assigned_genre['primary_id']);
                echo zen_draw_pull_down_menu('bookx_genre_id[' . $genre_counter . ']', $genre_array, $product_assigned_genre['bookx_genre_id'], 'class="form-control"');
                echo '</div>';
                $genre_counter++;
              }
              ?>
          </div>
          <a href="javascript:void(0);" onclick="addGenrePulldown(<?php echo $genre_counter; ?>);" class="btn btn-primary btn-sm"><?php echo TEXT_PRODUCTS_BOOKX_ADD_GENRE; ?></a>
        </div>
      </div>
    <?php } // end if loop   ?>

  </div> <!-- Ends Bookx Data -->

  <div class="form-group">
      <?php echo zen_draw_label(TEXT_PRODUCT_IS_FREE, 'product_is_free', 'class="col-sm-3 control-label"'); ?>
    <div class="col-sm-9 col-md-6">
      <label class="radio-inline"><?php echo zen_draw_radio_field('product_is_free', '1', ($pInfo->product_is_free == 1)) . TEXT_YES; ?></label>
      <label class="radio-inline"><?php echo zen_draw_radio_field('product_is_free', '0', ($pInfo->product_is_free == 0)) . TEXT_NO; ?></label>
      <?php echo ($pInfo->product_is_free == 1 ? '<span class="help-block errorText">' . TEXT_PRODUCTS_IS_FREE_EDIT . '</span>' : ''); ?>
    </div>
  </div>
  <div class="form-group">
      <?php echo zen_draw_label(TEXT_PRODUCT_IS_CALL, 'product_is_call', 'class="col-sm-3 control-label"'); ?>
    <div class="col-sm-9 col-md-6">
      <label class="radio-inline"><?php echo zen_draw_radio_field('product_is_call', '1', ($pInfo->product_is_call == 1)) . TEXT_YES; ?></label>
      <label class="radio-inline"><?php echo zen_draw_radio_field('product_is_call', '0', ($pInfo->product_is_call == 0)) . TEXT_NO; ?></label>
      <?php echo ($pInfo->product_is_call == 1 ? '<span class="help-block errorText">' . TEXT_PRODUCTS_IS_CALL_EDIT . '</span>' : ''); ?>
    </div>
  </div>
  <div class="form-group">
      <?php echo zen_draw_label(TEXT_PRODUCTS_PRICED_BY_ATTRIBUTES, 'products_priced_by_attribute', 'class="col-sm-3 control-label"'); ?>
    <div class="col-sm-9 col-md-6">
      <label class="radio-inline"><?php echo zen_draw_radio_field('products_priced_by_attribute', '1', ($pInfo->products_priced_by_attribute == 1)) . TEXT_PRODUCT_IS_PRICED_BY_ATTRIBUTE; ?></label>
      <label class="radio-inline"><?php echo zen_draw_radio_field('products_priced_by_attribute', '0', ($pInfo->products_priced_by_attribute == 0)) . TEXT_PRODUCT_NOT_PRICED_BY_ATTRIBUTE; ?></label>
      <?php echo ($pInfo->products_priced_by_attribute == 1 ? '<span class="help-block errorText">' . TEXT_PRODUCTS_PRICED_BY_ATTRIBUTES_EDIT . '</span>' : ''); ?>
    </div>
  </div>
  <div class="well" style="color: #31708f;background-color: #d9edf7;border-color: #bce8f1;;padding: 10px 10px 0 0;">
    <div class="form-group">
        <?php echo zen_draw_label(TEXT_PRODUCTS_TAX_CLASS, 'products_tax_class_id', 'class="col-sm-3 control-label"'); ?>
      <div class="col-sm-9 col-md-6">
          <?php echo zen_draw_pull_down_menu('products_tax_class_id', $tax_class_array, $pInfo->products_tax_class_id, 'onchange="updateGross()" class="form-control"'); ?>
      </div>
    </div>
    <div class="form-group">
        <?php echo zen_draw_label(TEXT_PRODUCTS_PRICE_NET, 'products_price', 'class="col-sm-3 control-label"'); ?>
      <div class="col-sm-9 col-md-6">
          <?php echo zen_draw_input_field('products_price', $pInfo->products_price, 'onkeyup="updateGross()" class="form-control"'); ?>
      </div>
    </div>
    <div class="form-group">
        <?php echo zen_draw_label(TEXT_PRODUCTS_PRICE_GROSS, 'products_price_gross', 'class="col-sm-3 control-label"'); ?>
      <div class="col-sm-9 col-md-6">
          <?php echo zen_draw_input_field('products_price_gross', $pInfo->products_price, 'onkeyup="updateNet()" class="form-control"'); ?>
      </div>
    </div>
  </div>
  <script>
    updateGross();
  </script>
  <div class="form-group">
      <?php echo zen_draw_label(TEXT_PRODUCTS_VIRTUAL, 'products_virtual', 'class="col-sm-3 control-label"'); ?>
    <div class="col-sm-9 col-md-6">
      <label class="radio-inline"><?php echo zen_draw_radio_field('products_virtual', '1', ($pInfo->products_virtual == 1)) . TEXT_PRODUCT_IS_VIRTUAL; ?></label>
      <label class="radio-inline"><?php echo zen_draw_radio_field('products_virtual', '0', ($pInfo->products_virtual == 0)) . TEXT_PRODUCT_NOT_VIRTUAL; ?></label>
      <?php echo ($pInfo->products_virtual == 1 ? '<span class="help-block errorText">' . TEXT_VIRTUAL_EDIT . '</span>' : ''); ?>
    </div>
  </div>
  <div class="form-group">
      <?php echo zen_draw_label(TEXT_PRODUCTS_IS_ALWAYS_FREE_SHIPPING, 'product_is_always_free_shipping', 'class="col-sm-3 control-label"'); ?>
    <div class="col-sm-9 col-md-6">
      <label class="radio-inline"><?php echo zen_draw_radio_field('product_is_always_free_shipping', '1', ($pInfo->product_is_always_free_shipping == 1)) . TEXT_PRODUCT_IS_ALWAYS_FREE_SHIPPING; ?></label>
      <label class="radio-inline"><?php echo zen_draw_radio_field('product_is_always_free_shipping', '0', ($pInfo->product_is_always_free_shipping == 0)) . TEXT_PRODUCT_NOT_ALWAYS_FREE_SHIPPING; ?></label>
      <label class="radio-inline"><?php echo zen_draw_radio_field('product_is_always_free_shipping', '2', ($pInfo->product_is_always_free_shipping == 2)) . TEXT_PRODUCT_SPECIAL_ALWAYS_FREE_SHIPPING; ?></label>
      <?php echo ($pInfo->product_is_always_free_shipping == 1 ? '<span class="help-block errorText">' . TEXT_FREE_SHIPPING_EDIT . '</span>' : ''); ?>
    </div>
  </div>
  <div class="form-group">
      <?php echo zen_draw_label(TEXT_PRODUCTS_QTY_BOX_STATUS, 'products_qty_box_status', 'class="col-sm-3 control-label"'); ?>
    <div class="col-sm-9 col-md-6">
      <label class="radio-inline"><?php echo zen_draw_radio_field('products_qty_box_status', '1', ($pInfo->products_qty_box_status == 1 ? true : false)) . TEXT_PRODUCTS_QTY_BOX_STATUS_ON; ?></label>
      <label class="radio-inline"><?php echo zen_draw_radio_field('products_qty_box_status', '0', ($pInfo->products_qty_box_status == 0 ? true : false)) . TEXT_PRODUCTS_QTY_BOX_STATUS_OFF; ?></label>
      <?php echo ($pInfo->products_qty_box_status == 0 ? '<span class="help-block errorText">' . TEXT_PRODUCTS_QTY_BOX_STATUS_EDIT . '</span>' : ''); ?>
    </div>
  </div>
  <div class="form-group">
      <?php echo zen_draw_label(TEXT_PRODUCTS_QUANTITY_MIN_RETAIL, 'products_quantity_order_min', 'class="col-sm-3 control-label"'); ?>
    <div class="col-sm-9 col-md-6">
        <?php echo zen_draw_input_field('products_quantity_order_min', ($pInfo->products_quantity_order_min == 0 ? 1 : $pInfo->products_quantity_order_min), 'class="form-control"'); ?>
    </div>
  </div>
  <div class="form-group">
      <?php echo zen_draw_label(TEXT_PRODUCTS_QUANTITY_MAX_RETAIL, 'products_quantity_order_max', 'class="col-sm-3 control-label"'); ?>
    <div class="col-sm-9 col-md-6">
      <?php echo zen_draw_input_field('products_quantity_order_max', $pInfo->products_quantity_order_max, 'class="form-control"'); ?>&nbsp;&nbsp;<?php echo TEXT_PRODUCTS_QUANTITY_MAX_RETAIL_EDIT; ?>
    </div>
  </div>
  <div class="form-group">
      <?php echo zen_draw_label(TEXT_PRODUCTS_QUANTITY_UNITS_RETAIL, 'products_quantity_order_units', 'class="col-sm-3 control-label"'); ?>
    <div class="col-sm-9 col-md-6">
        <?php echo zen_draw_input_field('products_quantity_order_units', ($pInfo->products_quantity_order_units == 0 ? 1 : $pInfo->products_quantity_order_units), 'class="form-control"'); ?>
    </div>
  </div>
  <div class="form-group">
      <?php echo zen_draw_label(TEXT_PRODUCTS_MIXED, 'products_quantity_mixed', 'class="col-sm-3 control-label"'); ?>
    <div class="col-sm-9 col-md-6">
      <label class="radio-inline"><?php echo zen_draw_radio_field('products_quantity_mixed', '1', ($pInfo->products_quantity_mixed == 1)) . TEXT_YES; ?></label>
      <label class="radio-inline"><?php echo zen_draw_radio_field('products_quantity_mixed', '0', ($pInfo->products_quantity_mixed == 0)) . TEXT_NO; ?></label>
    </div>
  </div>
  <div class="form-group">
      <?php echo zen_draw_label(TEXT_PRODUCTS_DESCRIPTION, 'products_description', 'class="col-sm-3 control-label"'); ?>
    <div class="col-sm-9 col-md-6">
        <?php
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
          ?>
        <div class="input-group">
          <span class="input-group-addon">
              <?php echo zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']); ?>
          </span>
          <?php echo zen_draw_textarea_field('products_description[' . $languages[$i]['id'] . ']', 'soft', '100%', '30', htmlspecialchars((isset($products_description[$languages[$i]['id']])) ? stripslashes($products_description[$languages[$i]['id']]) : zen_get_products_description($pInfo->products_id, $languages[$i]['id']), ENT_COMPAT, CHARSET, TRUE), 'class="editorHook form-control"'); ?>
        </div>
        <br>
        <?php
      }
      ?>
    </div>
  </div>
  <div class="form-group">
      <?php echo zen_draw_label(TEXT_PRODUCTS_QUANTITY, 'products_quantity', 'class="col-sm-3 control-label"'); ?>
    <div id="product_quantity_field" class="col-sm-9 col-md-6">
        <?php echo zen_draw_input_field('products_quantity', $pInfo->products_quantity, 'class="form-control"'); ?>
    </div>
  </div>
  <div class="form-group">
      <?php echo zen_draw_label(TEXT_PRODUCTS_MODEL, 'products_model', 'class="col-sm-3 control-label"'); ?>
    <div class="col-sm-9 col-md-6">
        <?php echo zen_draw_input_field('products_model', htmlspecialchars(stripslashes($pInfo->products_model), ENT_COMPAT, CHARSET, TRUE), zen_set_field_length(TABLE_PRODUCTS, 'products_model') . ' class="form-control"'); ?>
    </div>
  </div>
  <?php
  $dir_info = zen_build_subdirectories_array(DIR_FS_CATALOG_IMAGES);
  $default_directory = substr($pInfo->products_image, 0, strpos($pInfo->products_image, '/') + 1);
  ?>

  <div class="form-group">
      <?php echo zen_draw_separator('pixel_black.gif', '100%', '3'); ?>
  </div>
  <div class="form-group">
      <?php echo zen_draw_label(TEXT_PRODUCTS_IMAGE, 'products_image', 'class="col-sm-3 control-label"'); ?>
    <div class="col-sm-9 col-md-9 col-lg-6">
      <div class="col-md-6">
        <div class="row">
            <?php echo zen_draw_file_field('products_image', '', 'class="form-control"'); ?>
        </div>
        <div class="row">&nbsp;</div>
        <div class="row">
            <?php echo zen_draw_label(TEXT_IMAGE_CURRENT, 'products_previous_image', 'class="conrol-label"') . '&nbsp;' . ($pInfo->products_image != '' ? $pInfo->products_image : NONE); ?>
            <?php echo zen_draw_hidden_field('products_previous_image', $pInfo->products_image); ?>
        </div>
        <div class="row">&nbsp;</div>
      </div>
      <div class="col-md-6">
        <div class="row">
          <?php echo zen_draw_label(TEXT_PRODUCTS_IMAGE_DIR, 'img_dir', 'class="control-label"'); ?>&nbsp;<?php echo zen_draw_pull_down_menu('img_dir', $dir_info, $default_directory, 'class="form-control"'); ?>
        </div>
        <div class="row">&nbsp;</div>
        <div class="row">
            <?php echo zen_draw_label(TEXT_IMAGES_DELETE, 'image_delete', 'class="control-label"'); ?>
          <label class="radio-inline"><?php echo zen_draw_radio_field('image_delete', '0', true) . TABLE_HEADING_NO; ?></label>
          <label class="radio-inline"><?php echo zen_draw_radio_field('image_delete', '1', false) . TABLE_HEADING_YES; ?></label>
        </div>
        <div class="row">&nbsp;</div>
        <div class="row">
            <?php echo zen_draw_label(TEXT_IMAGES_OVERWRITE, 'overwrite', 'class="control-label"'); ?>
          <label class="radio-inline"><?php echo zen_draw_radio_field('overwrite', '0', false) . TABLE_HEADING_NO; ?></label>
          <label class="radio-inline"><?php echo zen_draw_radio_field('overwrite', '1', true) . TABLE_HEADING_YES; ?></label>
        </div>
        <div class="row">&nbsp;</div>
        <div class="row">
            <?php echo zen_draw_label(TEXT_PRODUCTS_IMAGE_MANUAL, 'products_image_manual', 'class="control-label"') . zen_draw_input_field('products_image_manual', '', 'class="form-control"'); ?>
        </div>
      </div>
    </div>
  </div>
  <div class="form-group">
      <?php echo zen_draw_separator('pixel_black.gif', '100%', '3'); ?>
  </div>
  <div class="form-group">
    <div class="col-sm-3 control-label">
      <?php echo zen_draw_label(TEXT_PRODUCTS_URL, 'products_url'); ?><span class="help-block"><?php echo TEXT_PRODUCTS_URL_WITHOUT_HTTP; ?></span>
    </div>
    <div class="col-sm-9 col-md-6">
        <?php
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
          ?>
        <div class="input-group">
          <span class="input-group-addon">
              <?php echo zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']); ?>
          </span>
          <?php echo zen_draw_input_field('products_url[' . $languages[$i]['id'] . ']', htmlspecialchars(isset($products_url[$languages[$i]['id']]) ? $products_url[$languages[$i]['id']] : zen_get_products_url($pInfo->products_id, $languages[$i]['id']), ENT_COMPAT, CHARSET, TRUE), zen_set_field_length(TABLE_PRODUCTS_DESCRIPTION, 'products_url') . 'class="form-control"'); ?>
        </div>
        <br>
        <?php
      }
      ?>
    </div>
  </div>
  <div class="form-group">
      <?php echo zen_draw_label(TEXT_PRODUCTS_WEIGHT, 'products_weight', 'class="col-sm-3 control-label"'); ?>
    <div class="col-sm-9 col-md-6">
        <?php echo zen_draw_input_field('products_weight', $pInfo->products_weight, 'class="form-control"'); ?>
    </div>
  </div>
  <div class="form-group">
      <?php echo zen_draw_label(TEXT_PRODUCTS_SORT_ORDER, 'products_sort_order', 'class="col-sm-3 control-label"'); ?>
    <div class="col-sm-9 col-md-6">
      <?php echo zen_draw_input_field('products_sort_order', $pInfo->products_sort_order, 'class="form-control"'); ?>
    </div>
    <?php
    echo zen_draw_hidden_field('products_date_added', (zen_not_null($pInfo->products_date_added) ? $pInfo->products_date_added : date('Y-m-d')));
    echo ((isset($_GET['search']) && !empty($_GET['search'])) ? zen_draw_hidden_field('search', $_GET['search']) : '');
    echo ((isset($_POST['search']) && !empty($_POST['search']) && empty($_GET['search'])) ? zen_draw_hidden_field('search', $_POST['search']) : '');
    ?>
  </div>
  <?php echo '</form>'; ?>
</div>

<script>
  $(document).ready(function () {

      determineBookxProductStatusMessage();
      //previewDisplayDate();
      $("input[name='products_date_available']").change(function (e) {
          determineBookxProductStatusMessage()
      });

      $("input[name='publishing_date']").change(function (e) {
          determineBookxProductStatusMessage()
      });
      $("input[name='products_status']").change(function (e) {
          determineBookxProductStatusMessage()
      });
      $("input[name='products_quantity']").change(function (e) {
          determineBookxProductStatusMessage()
      });

      if ($("input[name='publishing_date']").val() !== '') {
          $("#publishing_date_display").text($("input[name='publishing_date']").val());
      }

      $("input[name=publishing_date]").on("change focus", function () {
          $("#publishing_date_display").text($("input[name='publishing_date']").val());
      });

      /*
       * Future Select2 or Choosen plugin for select search
       */
      /*
       $(".js-example-responsive").select2({
       placeholder: 'Select an option',
       tags: true,
       width: 'resolve' // need to override the changed default
       });
       
       
       $('.js-example-responsive').on("select2:selecting", function(e) {
       console.log(e);
       var author_id = e.params.args.data.id;
       console.log('Selecting: ' , author_id);
       if ($('.js-example-responsive').find("option[value='" + authorDefaulTypes[author_id] + "']")) {
       console.log('foud' + authorDefaulTypes[author_id]);
       }
       });
       */
      $("#datepicker1").datepicker({
          showButtonPanel: true
      });
      $("#format_date").on("change", function () {
          $("#datepicker1").datepicker("option", "dateFormat", $(this).val());
      });

//document.getElementById("publishing_date_display").innerHTML = dateDisplayString;


  });
</script>
