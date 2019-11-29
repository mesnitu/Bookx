<?php

/**
 * This file is part of the ZenCart add-on Book X which
 * introduces a new product type for books to the Zen Cart
 * shop system. Tested for compatibility on ZC v. 1.56
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
 * @version BookX V 1.0.0
 * @version $Id: [ZC INSTALLATION]/includes/classes/observers/class.bookx_observers.php 2019-01-30 mesnitu $
 */
/* * *
 * Some observers for the product type bookx which insert variables into the script flow
 */
class productTypeFilterObserver extends base
{
    const NEW_BOOK_LOOK_BACK = BOOKX_NEW_PRODUCTS_LOOK_BACK_NUMBER_OF_DAYS;
    const UPCOMING_BOOK_LOOK_AHEAD = BOOKX_UPCOMING_PRODUCTS_LOOK_AHEAD_NUMBER_OF_DAYS;
    
    var $flag_group = array(
        'by_availability' => false);
    var $flag_show = array(
        'subtitle' => false,
        'pages' => false,
        'printing' => false,
        'binding' => false,
        'size' => false,
        'isbn' => false,
        'model' => false,
        'volume' => false,
        'publish_date' => false,
        'publisher' => false,
        'publisher_as_link' => false,
        'publisher_image' => false,
        'publisher_url' => false,
        'publisher_description' => false,
        'imprint' => false,
        'imprint_as_link' => false,
        'imprint_image' => false,
        'imprint_description' => false,
        'series' => false,
        'series_as_link' => false,
        'series_image' => false,
        'series__description' => false,
        'authors' => false,
        'authors_with_type_below_sort_order' => false,
        'authors_as_link' => false,
        'authors_image' => false,
        'authors_url' => false,
        'authors_description' => false,
        'author_type' => false,
        'author_type_image' => false,
        'author_type__description' => false,
        'genres' => false,
        'genres_as_link' => false,
        'genres_image' => false,
        'condition' => false
    );

    /* Unused ?
    var $flag_show_product_bookx_filter_author = false;
    var $flag_show_product_bookx_filter_autho_type = false;
    var $flag_show_product_bookx_filter_publisher = false;
    var $flag_show_product_bookx_filter_imprint = false;
    var $flag_show_product_bookx_filter_series = false;
    var $flag_show_product_bookx_filter_genre = false;
     */
    var $flag_show_product_bookx_filter_author_extra_info = false;
    var $flag_show_product_bookx_filter_author_type_extra_info = false;
    var $flag_show_product_bookx_filter_publisher_extra_info = false;
    var $flag_show_product_bookx_filter_imprint_extra_info = false;
    var $flag_show_product_bookx_filter_series_extra_info = false;
    var $flag_show_product_bookx_filter_genre_extra_info = false;
    
    var $bookx_filter_active = 0;
    var $filtered_author_id = null;
    var $filtered_author_type_id = null;
    var $filtered_publisher_id = null;
    var $filtered_imprint_id = null;
    var $filtered_series_id = null;
    var $filtered_genre_id = null;
    var $filtered_condition_id = null;
    var $filtered_printing_id = null;
    var $filtered_binding_id = null;
    var $filtered_values_loaded = false;
    
    function loadFilterValues()
    {
        // Bookx specific flags
        if (!$this->filtered_values_loaded) {
            foreach ($this->flag_show as $key => $value) {
                $this->flag_show[$key] = bookx_get_show_product_switch($key, 'SHOW_', '_LISTING');
            }
            // at the moment just one group value 
            $this->flag_group['by_availability'] = bookx_get_show_product_switch('by_availability', 'GROUP_', '_LISTING');
            $this->filtered_values_loaded = true;
        }
    }

    function __construct()
    {
        global $zco_notifier;
        
        $zco_notifier->attach($this, array(
            'NOTIFY_HEADER_INDEX_MAIN_TEMPLATE_VARS_RELEASE_PRODUCT_TYPE_VARS'
            , 'NOTIFY_MODULE_PRODUCT_LISTING_RESULTCOUNT'
            , 'NOTIFY_TPL_TABULAR_DISPLAY_START'
            /* ,'NOTIFY_TEMPLATE_PRODUCT_LISTING_COLUMNAR_DISPLAY_BEGIN' */
            , 'NOTIFY_HEADER_START_INDEX_MAIN_TEMPLATE_VARS'
            , 'NOTIFIER_CART_GET_PRODUCTS_END'
            , 'NOTIFY_HEADER_END_SHOPPING_CART'
            , 'NOTIFY_SEARCH_WHERE_STRING'
            , 'NOTIFY_MODULE_NEW_PRODUCTS_QUERY_BUILT'
            , 'NOTIFY_MODULE_NEW_PRODUCTS_END'
            , 'NOTIFY_MODULE_UPCOMING_PRODUCTS_QUERY_BUILT'
            , 'NOTIFIER_CART_GET_PRODUCTS_END'
            , 'NOTIFY_PRODUCT_LISTING_ALPHA_SORTER_SELECTLIST'
            )
        );
    }

    function update(&$callingClass, $notifier, $paramsArray)
    {
        switch ($notifier) {
            case 'NOTIFY_MODULE_PRODUCT_LISTING_RESULTCOUNT':
                $this->update_product_listing_with_bookx_attributes($callingClass, $notifier, $paramsArray);
                break;

            case 'NOTIFIER_CART_GET_PRODUCTS_END':
                $this->update_shopping_cart_with_bookx_attributes($callingClass, $notifier, $paramsArray);
                break;

            case 'NOTIFY_HEADER_INDEX_MAIN_TEMPLATE_VARS_RELEASE_PRODUCT_TYPE_VARS':
                $this->check_pType_filters_and_reset($callingClass, $notifier, $paramsArray);
                break;

            case 'NOTIFY_TPL_TABULAR_DISPLAY_START': // This notifier was added in ZC v.1.5.5
            case 'NOTIFY_TEMPLATE_PRODUCT_LISTING_COLUMNAR_DISPLAY_BEGIN':
                $this->insert_extra_bookx_attributes($callingClass, $notifier, $paramsArray);
                break;

            case 'NOTIFY_HEADER_START_INDEX_MAIN_TEMPLATE_VARS':
                $this->insert_search_term_special_bookx_info($callingClass, $notifier, $paramsArray);
                break;
            
            case 'NOTIFY_HEADER_END_SHOPPING_CART':
                $this->insert_extra_bookx_attributes_to_cart_display($callingClass, $notifier, $paramsArray);
                break;

            case 'NOTIFY_SEARCH_WHERE_STRING':
                $this->insert_bookx_attributes_into_search_query($callingClass, $notifier, $paramsArray);
                break;

            case 'NOTIFY_MODULE_NEW_PRODUCTS_QUERY_BUILT':
                $this->insert_bookx_attributes_into_new_products_query($callingClass, $notifier, $paramsArray);
                break;

            case 'NOTIFY_MODULE_NEW_PRODUCTS_END':
                $this->insert_bookx_attributes_into_new_products_listing($callingClass, $notifier, $paramsArray);
                break;

            case 'NOTIFY_MODULE_UPCOMING_PRODUCTS_QUERY_BUILT':
                $this->insert_bookx_attributes_into_upcoming_products_query($callingClass, $notifier, $paramsArray);
                break;

            case 'NOTIFY_PRODUCT_LISTING_ALPHA_SORTER_SELECTLIST':
                $this->insert_bookx_hidden_field_into_alpha_sorter($callingClass, $notifier, $paramsArray);
                break;
        }
    }

    /**
     * This function gets triggered by the file "includes/modules/product_listing.php"
     * and it replaces the splitPageResults with a query that also contains the extra
     * bookx fields based on Admin preference settings
     */
    function update_product_listing_with_bookx_attributes(&$callingClass, $notifier, $paramsArray)
    {
        /* $bookx_filter = false;
          if (isset($_GET['typefilter']) && 'bookx' == $_GET['typefilter']) {
          // we don't add anything to the listing_sql query since the bookx filter already did
          $bookx_filter = $_GET['bookxfilter'];
          $this->bookx_filter_active = true;
          $this->bookx_filter_type = $bookx_filter;
          $this->bookx_filter_value = $_GET[$this->bookx_filter_type];
          } */
        //$test = $_GET['typefilter'];
        //$test = $_GET['bookxfilter'];

        global $listing_split;
        global $listing_sql;

        $listing_sql_old = $listing_sql;

        $additional_fields = '';
        $additional_joins = '';
        $additional_where = '';

        $join_bx_extra = false;
        $join_bx_extra_desc = false;
        $join_author = false;
        $join_author_type = false;
        $join_genres = false;
        $join_publisher = false;
        $join_imprint = false;
        $join_series = false;

        /*
         * ('Product Listing: Show ISBN', 'SHOW_PRODUCT_BOOKX_LISTING_MODEL', '1', 'Display ISBN on Product Listing.', {$type_id}, '10', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED'), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))) "),

         */
        $this->loadFilterValues();

        if ($this->flag_show['subtitle']) {
            $additional_fields .= ', bed.products_subtitle';
            $join_bx_extra = true;
            $join_bx_extra_desc = true;
        }

        if ($this->flag_show['pages']) {
            $additional_fields .= ', be.pages';
            $join_bx_extra = true;
        }

        if ($this->flag_show['size']) {
            $additional_fields .= ', be.size';
            $join_bx_extra = true;
        }

        if ($this->flag_show['isbn']) {
            $additional_fields .= ', CONCAT_WS("-", SUBSTRING(be.isbn,1,3), SUBSTRING(be.isbn,4,1), SUBSTRING(be.isbn,5,6), SUBSTRING(be.isbn,11,2), SUBSTRING(be.isbn,13,1)) AS isbn_display';
            $join_bx_extra = true;
        }

        if ($this->flag_show['volume']) {
            $additional_fields .= ', be.volume';
            $join_bx_extra = true;
        }

        if ($this->flag_show['publish_date']) {
            //$additional_fields .= ', IF(DAYOFMONTH(be.publishing_date), DATE_FORMAT(be.publishing_date, "' . DATE_FORMAT_SHORT . '"), DATE_FORMAT(be.publishing_date, "' . DATE_FORMAT_MONTH_AND_YEAR . '")) AS publishing_date';
            $additional_fields .= ', CASE WHEN DAYOFMONTH(be.publishing_date) THEN DATE_FORMAT(be.publishing_date, "' . DATE_FORMAT_SHORT . '")
									WHEN MONTH(be.publishing_date) THEN DATE_FORMAT(be.publishing_date, "' . DATE_FORMAT_MONTH_AND_YEAR . '")
									ELSE YEAR(be.publishing_date)
									END AS publishing_date';
            $join_bx_extra = true;
        }

        if ($this->flag_show['printing']) {
            $additional_fields .= ', printd.printing_description';
            $additional_joins .= ' LEFT JOIN ' . TABLE_PRODUCT_BOOKX_PRINTING_DESCRIPTION . ' printd ON printd.bookx_printing_id = be.bookx_printing_id AND printd.languages_id = "' . (int) $_SESSION['languages_id'] . '"';
            $join_bx_extra = true;
        }

        if ($this->flag_show['binding']) {
            $additional_fields .= ', bindd.binding_description';
            $additional_joins .= ' LEFT JOIN ' . TABLE_PRODUCT_BOOKX_BINDING_DESCRIPTION . ' bindd ON bindd.bookx_binding_id = be.bookx_binding_id AND bindd.languages_id = "' . (int) $_SESSION['languages_id'] . '"';
            $join_bx_extra = true;
        }

        if ($this->flag_show['condition']) {
            $additional_fields .= ', conditd.condition_description';
            $additional_joins .= ' LEFT JOIN ' . TABLE_PRODUCT_BOOKX_CONDITIONS_DESCRIPTION . ' conditd ON conditd.bookx_condition_id = be.bookx_condition_id AND conditd.languages_id = "' . (int) $_SESSION['languages_id'] . '"';
            $join_bx_extra = true;
        }

        //** author
        $additional_author_join_condition = '';
        if ($this->flag_show['authors']) {
            if ($this->flag_show['author_type']) {
                $additional_fields .= ', GROUP_CONCAT(DISTINCT CONCAT_WS("", IF("" = IFNULL(batd.type_description,""), "", CONCAT_WS("", "<span class=\"bookxLabel\">", batd.type_description , ": </span>")), ba.author_name) ORDER BY bat.type_sort_order ASC SEPARATOR " &middot; ") AS authors';
                $join_author_type = true;
            } else {
                $additional_fields .= ', GROUP_CONCAT(DISTINCT ba.author_name ORDER BY ba.author_name ASC SEPARATOR " &middot; ") AS authors';
            }
            $join_author = true;
            $join_bx_extra = true;

            if ($this->flag_show['authors_with_type_below_sort_order']) {
                $join_author_type = true;
                $additional_author_join_condition = ' AND bat.type_sort_order < "' . $this->flag_show['authors_with_type_below_sort_order'] . '" ';
            }
        }

        if ($this->flag_show['authors_image']) {
            $additional_fields .= ', ba.author_image';
            $join_author = true;
            $join_bx_extra = true;
        }

        if ($this->flag_show['authors_url']) {
            $additional_fields .= ', ba.author_url';
            $join_author = true;
            $join_bx_extra = true;
        }

        if ($this->flag_show['authors_description']) {
            $additional_fields .= ', bad.author_description';
            $join_author = true;
            $join_bx_extra = true;
        }

        //**** publisher
       
        if ($this->flag_show['publisher']) {
            $additional_fields .= ', bp.publisher_name';
            $join_publisher = true;
            $join_bx_extra = true;
        }

        /*
          if ($this->flag_show['publisher_as_link']) {
          $additional_fields .= ', IF("" = IFNULL(bp.publisher_name,""), "", CONCAT_WS("","<a href=\���' . zen_href_link(FILENAME_DEFAULT, '&bookxfilter=bookx_publisher&bookx_bookx_publisher_id=') .'", bp.bookx_publisher_id, " class=\'bookx_searchlink\'>", bp.publisher_name, "</a>")) AS publisher_searchlink';
          $join_publisher = true;
          } */
        if ($this->flag_show['publisher_image']) {
            $additional_fields .= ', bp.publisher_image';
            $join_publisher = true;
            $join_bx_extra = true;
        }
        
        if ($this->flag_show['publisher_url']) {
            $additional_fields .= ', bpd.publisher_url';
            $join_publisher = true;
            $join_bx_extra = true;
        }

        if ($this->flag_show['publisher_description']) {
            $additional_fields .= ', bpd.publisher_description';
            $join_publisher = true;
            $join_bx_extra = true;
        }


        if ($this->flag_show['imprint']) {
            $additional_fields .= ', bi.imprint_name';
            $join_imprint = true;
            $join_bx_extra = true;
        }

        if ($this->flag_show['imprint_image']) {
            $additional_fields .= ', bi.imprint_image';
            $join_imprint = true;
            $join_bx_extra = true;
        }


        if ($this->flag_show['imprint_description']) {
            $additional_fields .= ', bid.imprint_description';
            $join_imprint = true;
            $join_bx_extra = true;
        }

        if ($this->flag_show['series']) {
            $additional_fields .= ', bsd.series_name';
            $join_series = true;
            $join_bx_extra = true;
        }

        if ($this->flag_show['series_image']) {
            $additional_fields .= ', bsd.series_image';
            $join_series = true;
            $join_bx_extra = true;
        }


        if ($this->flag_show['series_description']) {
            $additional_fields .= ', bsd.series_description';
            $join_series = true;
            $join_bx_extra = true;
        }

        //** genres
        if ($this->flag_show['genres']) {
            $additional_fields .= ', GROUP_CONCAT(DISTINCT bgd.genre_name ORDER BY bg.genre_sort_order ASC SEPARATOR "' . BOOKX_GENRE_SEPARATOR . '")  AS genres';
            if ($this->flag_show['genres_as_link']) {
                $genre_link_atag_firstpart = '<a href="' . zen_href_link(FILENAME_DEFAULT, '&typefilter=bookx&bookxfilter=bookx_genre_id&bookx_genre_id=');
                $genre_link_atag_middlepart = '" class="bookx_searchlink">';
                $additional_fields .= ", GROUP_CONCAT(DISTINCT CONCAT_WS('', '" . $genre_link_atag_firstpart . "', bgd.bookx_genre_id, '" . $genre_link_atag_middlepart . "', bgd.genre_name, '</a>') ORDER BY bg.genre_sort_order ASC SEPARATOR ' | ')  AS genres_as_links";
            }
            $join_genres = true;
            $join_bx_extra = true;
        }

        if ($this->flag_show['genre_image']) {
            $additional_fields .= ', bgd.genre_image';
            $join_genres = true;
            $join_bx_extra = true;
        }
        
        if ($this->flag_show['model']) {
            $additional_fields .= ', p.products_model ';
        }

        //**** extra joins

        if ($join_bx_extra_desc) {
            $additional_joins = ' LEFT JOIN ' . TABLE_PRODUCT_BOOKX_EXTRA_DESCRIPTION . ' bed ON bed.products_id = be.products_id AND bed.languages_id = "' . (int) $_SESSION['languages_id'] . '"' . $additional_joins;
        }

        //** this is always joined if any bookx attribute is flagged for listing. we test for an active bookx filter, since it would already be joined by the filter
        if (!$this->bookx_filter_active && $join_bx_extra) {
            /* the first join must be via WHERE clause, so we join "products" again and then use LEFT JOINS */
            $additional_joins = ', ' . TABLE_PRODUCTS . ' pbxjoin
								   LEFT JOIN ' . TABLE_PRODUCT_TYPES . ' prodt ON prodt.type_id = pbxjoin.products_type
								   LEFT JOIN ' . TABLE_PRODUCT_BOOKX_EXTRA . ' be ON be.products_id = pbxjoin.products_id ' . "\n" . $additional_joins;

            $additional_where .= ' AND pbxjoin.products_id = p.products_id ';

            $additional_fields .= ', prodt.type_handler AS product_type_handler, be.publishing_date AS flag_date ';
        }

        if ($join_author) { //&& empty($this->filtered_author_id // we don't check for this, since the author filter only filters products, not authors
            $additional_joins .= ' LEFT JOIN ' . TABLE_PRODUCT_BOOKX_AUTHORS_TO_PRODUCTS . ' batp ON batp.products_id = be.products_id
								   LEFT JOIN ' . TABLE_PRODUCT_BOOKX_AUTHORS . ' ba ON batp.bookx_author_id = ba.bookx_author_id
								   LEFT JOIN ' . TABLE_PRODUCT_BOOKX_AUTHORS_DESCRIPTION . ' bad ON bad.bookx_author_id = ba.bookx_author_id AND bad.languages_id = "' . (int) $_SESSION['languages_id'] . '" ';
        }

        if ($join_author_type) {
            $additional_joins .= ' LEFT JOIN ' . TABLE_PRODUCT_BOOKX_AUTHOR_TYPES . ' bat ON bat.bookx_author_type_id = batp.bookx_author_type_id ' . $additional_author_join_condition
                . '  LEFT JOIN ' . TABLE_PRODUCT_BOOKX_AUTHOR_TYPES_DESCRIPTION . ' batd ON batd.bookx_author_type_id = batp.bookx_author_type_id AND batd.languages_id = "' . (int) $_SESSION['languages_id'] . '" ';
        }

        if ($join_genres && empty($this->filtered_genre_id)) {
            $additional_joins .= ' LEFT JOIN ' . TABLE_PRODUCT_BOOKX_GENRES_TO_PRODUCTS . ' bgtp ON bgtp.products_id = be.products_id
								   LEFT JOIN ' . TABLE_PRODUCT_BOOKX_GENRES . ' bg ON bgtp.bookx_genre_id = bg.bookx_genre_id
								   LEFT JOIN ' . TABLE_PRODUCT_BOOKX_GENRES_DESCRIPTION . ' bgd ON bgd.bookx_genre_id = bgtp.bookx_genre_id AND bgd.languages_id = "' . (int) $_SESSION['languages_id'] . '" ';
        }

        if ($join_publisher && empty($this->filtered_publisher_id)) {
            $additional_joins .= ' LEFT JOIN ' . TABLE_PRODUCT_BOOKX_PUBLISHERS . ' bp ON bp.bookx_publisher_id = be.bookx_publisher_id
    							   LEFT JOIN ' . TABLE_PRODUCT_BOOKX_PUBLISHERS_DESCRIPTION . ' bpd ON bpd.bookx_publisher_id = be.bookx_publisher_id AND bpd.languages_id = "' . (int) $_SESSION['languages_id'] . '" ';
        }

        if ($join_imprint && empty($this->filtered_imprint_id)) {
            $additional_joins .= ' LEFT JOIN ' . TABLE_PRODUCT_BOOKX_IMPRINTS . ' bi ON bi.bookx_imprint_id = be.bookx_imprint_id
    							   LEFT JOIN ' . TABLE_PRODUCT_BOOKX_IMPRINTS_DESCRIPTION . ' bid ON bid.bookx_imprint_id = bi.bookx_imprint_id AND bid.languages_id = "' . (int) $_SESSION['languages_id'] . '" ';
        }

        if ($join_series && empty($this->filtered_series_id)) {
            $additional_joins .= ' LEFT JOIN ' . TABLE_PRODUCT_BOOKX_SERIES_DESCRIPTION . ' bsd ON bsd.bookx_series_id = be.bookx_series_id AND bsd.languages_id = "' . (int) $_SESSION['languages_id'] . '" ';
        }

        $listing_sql_new = preg_replace('/\b(' . preg_quote('order') . ')\b/i', $additional_where . ' GROUP BY p.products_id $1 ', $listing_sql_old);

        $listing_sql_new = preg_replace('/\b(' . preg_quote('from') . ')\b/i', $additional_fields . ' $1 ', $listing_sql_new);
        $listing_sql_new = preg_replace('/\b(' . preg_quote('where') . ')\b/i', $additional_joins . ' $1 ', $listing_sql_new);

        //$listing_sql_new = str_replace('products p', 'products p' . ' ' . $additional_joins . ' ', $listing_sql_new);

        $listing_split = new splitPageResults($listing_sql_new, MAX_DISPLAY_PRODUCTS_LISTING, 'p.products_id', 'page');
    }

    /**
     * This function gets triggered by the file "includes/classes/shopping_cart.php"
     * It adds some bookx specific data to the 'product_name' variable.
     */
    function update_shopping_cart_with_bookx_attributes(&$callingClass, $notifier, $paramsArray)
    {
        global $db, $products_array;
        //global $content, $productArray;
        //$const = get_defined_constants();
        include_once DIR_FS_CATALOG . 'includes/languages/' . $_SESSION['language'] . '/product_bookx_info.php';
        include_once DIR_FS_CATALOG . 'includes/languages/' . $_SESSION['language'] . '/extra_definitions/product_bookx.php';
       
        if (!empty($products_array)) {
            $ids = array();
            foreach ($products_array as $key => $product) {
                if (!empty($product['attributes'])) {
                    //*** this product with attributes, so ID needs to be cleaned
                    $id_parts = explode(':', $product['id']);
                    $ids[$id_parts[0]] = $key;
                } else {
                    $ids[$product['id']] = $key;
                }
            }

            $sql = 'SELECT be.products_id, be.volume, bed.products_subtitle
					FROM ' . TABLE_PRODUCT_BOOKX_EXTRA . ' be
					LEFT JOIN ' . TABLE_PRODUCT_BOOKX_EXTRA_DESCRIPTION . ' bed ON bed.products_id = be.products_id AND bed.languages_id = "' . (int) $_SESSION['languages_id'] . '"
					WHERE be.products_id IN (' . implode(',', array_keys($ids)) . ')
					GROUP BY be.products_id';

            $bookx_products_in_cart = $db->Execute($sql);

            while (!$bookx_products_in_cart->EOF) {
                $products_array[$ids[$bookx_products_in_cart->fields['products_id']]]['name'] .= zen_trunc_string(
                    (!empty($bookx_products_in_cart->fields['volume']) ? '&nbsp;' . $bookx_products_in_cart->fields['volume'] : '') .
                    (!empty($bookx_products_in_cart->fields['products_subtitle']) ? ' &ndash; ' . $bookx_products_in_cart->fields['products_subtitle'] : '')
                    , 50
                );
                // overkill ? . (strstr($bookx_products_in_cart->fields['authors'], '|') ? LABEL_BOOKX_AUTHORS : LABEL_BOOKX_AUTHOR) . ': ' .
                $bookx_products_in_cart->MoveNext();
            }
        }
    }

    /**
     * This function gets triggered by the file "includes/templates/[ACTIVE TEMPLATE or DEFAULT]/common/tpl_tabular_display.php"
     * and it adds some bookx specific data to the $list_box_contents array
     */
    function insert_extra_bookx_attributes(&$callingClass, $notifier, $paramsArray)
    {
        global $listing; /* @var $listing queryFactoryResult */
        
        if (isset($listing->fields['product_type_handler']) && 'product_bookx' == $listing->fields['product_type_handler']) {

            $process = true; // makes sure that this is a bookx pType
            
            global $list_box_contents, $column_list, $zco_notifier;

            //$zco_notifier->notify('NOTIFY_BOOKX_ADD_EXTRA_INFO_TO_PRODUCT_LISTING_TABULAR_DISPLAY_BEGIN');

            $upcoming_products_array = array();
            $new_products_array = array();

            $keywords = null;
            if (isset($_GET['keyword']) && !empty($_GET['keyword'])) {
                $keywords = explode(' ', trim($_GET['keyword']));
                if (!is_array($keywords)) {
                    $keywords = array($keywords);
                }
            }
        }

        if (!$listing->EOF || $listing->cursor && ($process == true)) {

            $product_name_column = null;
            $product_image_column = null;
            $product_manufacturer_column = null;
            $product_qty_column = null;
            $product_price_column = null;
            $product_weight_column = null;
          
            /**
             * The listing layout can change, so to replace the button, I guess one has to set the prodcut_list_price.
             * The others, like qty, weight, etc,.... maybe unset them, and place them together with the bookx fields.
             * However, I not sure about the layout positions.... It's not respecting the position, but I don't know if it's a template issue. 
             * (
              [0] => PRODUCT_LIST_MANUFACTURER
              [1] => PRODUCT_LIST_QUANTITY
              [2] => PRODUCT_LIST_IMAGE
              [3] => PRODUCT_LIST_NAME
              [4] => PRODUCT_LIST_PRICE
              )
             */
            
            for ($col = 0, $n = sizeof($column_list); $col < $n; $col++) {
                if ('PRODUCT_LIST_NAME' == $column_list[$col]) {
                    $product_name_column = $col;
                }
                if ('PRODUCT_LIST_IMAGE' == $column_list[$col]) {
                    $product_image_column = $col;
                }
                if ('PRODUCT_LIST_MANUFACTURER' == $column_list[$col]) {
                    $product_manufacturer_column = $col;
                }
                if ('PRODUCT_LIST_QUANTITY' == $column_list[$col]) {
                    $product_qty_column = $col;
                }
                if ('PRODUCT_LIST_WEIGHT' == $column_list[$col]) {
                    $product_weight_column = $col;
                }
                if ('PRODUCT_LIST_PRICE' == $column_list[$col]) {
                    $product_price_column = $col;
                }
            }

            if ($product_name_column) {
                
                $listing->rewind();

                if (1 <= intval(PROJECT_VERSION_MAJOR) && '5.5' > floatval(PROJECT_VERSION_MINOR)) {
                    
                    // don't understand why this is necessary, but without it shows the first entry twice ?!
                    // @mesnitu: ON zc156 this wont work 
                    $listing->cursor = 0;
                    $listing->MoveNext();
                }
                // eof ?!!?
                $rows = 0;
                $extra_row = 0;
                while (!$listing->EOF) {
                    
                    $rows++;
                   
                        $new_product_text = '';
                        //removed the wrap span on header title.
                        $products_name = bookx_highlight_search_terms($keywords, $listing->fields['products_name']);
                        $products_name .= ($this->flag_show['volume'] && !empty($listing->fields['volume'])) ? ' <span class="bookxProdVolume">' . sprintf(LABEL_BOOKX_VOLUME, $listing->fields['volume']) . '</span>' : '';
                        $products_name .= ($this->flag_show['subtitle'] && !empty($listing->fields['products_subtitle']) ? ' - <span class="bookxProdSubtitle">' . bookx_highlight_search_terms($keywords, $listing->fields['products_subtitle']) . '</span>' : '');
                            
                        $active_boox_get_filters = '';
                        /**
                         * @todo adding &typefilter=bookx or keywords to the url is necessary ? 
                         * This should simply link to the book page
                         * 
                         */
                        if (isset($_GET['typefilter']) && 'bookx' == $_GET['typefilter']) {
                            $active_boox_get_filters .= '&typefilter=bookx';

                            if (isset($_GET['bookx_author_id']) && zen_not_null($_GET['bookx_author_id'])) {
                                $active_boox_get_filters .= '&bookx_author_id=' . $_GET['bookx_author_id'];
                            }

                            if (isset($_GET['bookx_publisher_id']) && zen_not_null($_GET['bookx_publisher_id'])) {
                                $active_boox_get_filters .= '&bookx_publisher_id=' . $_GET['bookx_publisher_id'];
                            }

                            if (isset($_GET['bookx_imprint_id']) && zen_not_null($_GET['bookx_imprint_id'])) {
                                $active_boox_get_filters .= '&bookx_imprint_id=' . $_GET['bookx_imprint_id'];
                            }

                            if (isset($_GET['bookx_series_id']) && zen_not_null($_GET['bookx_series_id'])) {
                                $active_boox_get_filters .= '&bookx_series_id=' . $_GET['bookx_series_id'];
                            }

                            if (isset($_GET['bookx_genre_id']) && zen_not_null($_GET['bookx_genre_id'])) {
                                $active_boox_get_filters .= '&bookx_genre_id=' . $_GET['bookx_genre_id'];
                            }
                        }
                        
                        $url_cpath = (($_GET['manufacturers_id'] > 0 AND $_GET['filter_id'] > 0) ? zen_get_generated_category_path_rev($_GET['filter_id']) : ($_GET['cPath'] > 0 ? zen_get_generated_category_path_rev($_GET['cPath']) : zen_get_generated_category_path_rev($listing->fields['master_categories_id'])));
                        
                        $url_keywords = (isset($_GET['keyword']) ? '&keyword=' . $_GET['keyword'] : '')
                            . (isset($_GET['search_in_description']) ? '&search_in_description=' . $_GET['search_in_description'] : '')
                            . (isset($_GET['inc_subcat']) ? '&inc_subcat=' . $_GET['inc_subcat'] : '')
                            . (isset($_GET['sort']) ? '&sort=' . $_GET['sort'] : '');
                        /**
                         * Commeting here $active_boox_get_filters . $url_keywords from the url. 
                         * This could be optional, otherwise, it will mess CEON urls
                         * In tests. Also the $url_keywords highlight should be optional. 
                         */
                        $product_info_url = zen_href_link(zen_get_info_page($listing->fields['products_id']), 'cPath=' . $url_cpath . '&products_id=' . $listing->fields['products_id'] /*. $active_boox_get_filters . $url_keywords*/);

                        $new_product_text .= '<h3 class="itemTitle"><a href="' . $product_info_url . '" class="bookx_product_name">' . bookx_highlight_search_terms($keywords, $products_name) . '</a></h2>';

                        if ($this->flag_show['authors']) {
                            $new_product_text .= '<h4 class="bookxAuthors">' . bookx_highlight_search_terms($keywords, $listing->fields['authors']) . '</h4>';
                        }
                        
                        $bookx_extra_attributes = array();
                        if ($this->flag_show['pages'] &&
                            (!empty($listing->fields['pages']) || 2 == $this->flag_show['pages'])) {
                            $bookx_extra_attributes[] = sprintf(LABEL_BOOKX_PAGES, $listing->fields['pages']);
                        }
                        
                        if ($this->flag_show['binding'] &&
                            (!empty($listing->fields['binding_description']) || 2 == $this->flag_show['binding'])) {
                          $bookx_extra_attributes[] = $listing->fields['binding_description'];  
                        }
                            
                        if ($this->flag_show['printing'] &&
                            (!empty($listing->fields['printing_description']) || 2 == $this->flag_show['printing'])) {
                            $bookx_extra_attributes[] = $listing->fields['printing_description'];
                        }
                        
                        if ($this->flag_show['size'] &&
                            (!empty($listing->fields['size']) || 2 == $this->flag_show['size'])) {
                            $bookx_extra_attributes[] = $listing->fields['size'];
                        }                         
                        if (0 < count($bookx_extra_attributes)) {
                            $new_product_text .= '<div id="bookxExtraAttributes">' . implode(' | ', $bookx_extra_attributes) . '</div>';
                        }
                        if ($this->flag_show['isbn'] && 
                            (!empty($listing->fields['isbn_display']) || 2 == $this->flag_show['isbn'])) {
                           $new_product_text .= '<div class="bookxISBN"><span class="bookxLabel">' . LABEL_BOOKX_ISBN . ' </span>' . $listing->fields['isbn_display'] . '</div>'; 
                        }
                        /**
                         * @todo About model. If the listing_layout settings is set to display the model, this will be duplicated
                         */
                        if ($this->flag_show['model'] && 
                            (!empty($listing->fields['products_model']) || 2 == $this->flag_show['model'])) {
                            $new_product_text .= '<div class="bookxModel"><span class="bookxLabel">' . LABEL_BOOKX_MODEL . ' </span>' . bookx_highlight_search_terms($keywords, $listing->fields['products_model']) . '</div>';
                        }

                        if ($this->flag_show['publish_date'] && 
                            (!empty($listing->fields['publishing_date']) || 2 == $this->flag_show['publish_date'])) {
                           $new_product_text .= '<div class="bookxPublishingDate"><span class="bookxLabel">' . LABEL_BOOKX_PUBLISHING_DATE . '</span>' . $listing->fields['publishing_date'] . '</div>'; 
                        }
                        
                        if ($this->flag_show['condition'] && 
                            (!empty($listing->fields['condition_description']) || 2 == $this->flag_show['condition'])) {
                            $new_product_text .= '<div class="bookxCondition"><span class="bookxLabel">' . LABEL_BOOKX_CONDITION . ':</span> ' . $listing->fields['condition_description'] . '</div>';
                        }
                            
                        $new_product_text .= '<div class="listingDescription">' . bookx_highlight_search_terms($keywords, bookx_truncate_paragraph(zen_clean_html(stripslashes(zen_get_products_description($listing->fields['products_id'], $_SESSION['languages_id']))), PRODUCT_LIST_DESCRIPTION)) . '</div>';

                        if ($this->flag_show['genres'] && 
                            (!empty($listing->fields['genres']) || 2 == $this->flag_show['genres'])) {
                            $new_product_text .= '<div class="bookxGenres"><span class="bookxLabel">' . LABEL_BOOKX_GENRE . ':</span> ';
                            if ($this->flag_show['genres_as_link']) {
                                $new_product_text .= $listing->fields['genres_as_links'];
                            } else {
                                $new_product_text .= $listing->fields['genres'];
                            }
                            $new_product_text .= '</div>';
                        }
                        /**
                         * @todo Do we need manufaturer if it's set? 
                         */
                        unset($list_box_contents[$rows][$product_manufacturer_column]);
                        
                        
                    $publishing_date_flag = null;
                    $button = false;
                    $button_link = false;
                    /**
                     * @todo Why do we need the bookx_button: Mainly because a upcoming product is a pre-order book, so a customer can pre-order
                     * However, this is calculated here and after in the bookx_button function again...
                     * Wouldn't be better to set a common button and calculate and return from the function ? 
                     * 
                     */
                    
                    //pr($listing->fields['flag_date']);
                    //pr($list_box_contents[$rows][$product_price_column]['text'], 'top');
                    
					if(isset($listing->fields['flag_date']) && !empty($listing->fields['flag_date'])) {
						//$date_diff_days = (int)((strtotime($listing->fields['flag_date']) - time()) / 86400);
                        $date_diff_days = zen_date_diff($_SESSION['today_is'], $listing->fields['flag_date']);
                        if ($listing->fields['products_quantity'] <= 0 && $date_diff_days < 0) {
                            //$button_link = false;
                            //$button = zen_image_button(BUTTON_IMAGE_SOLD_OUT_SMALL, BUTTON_SOLD_OUT_SMALL_ALT, 'class="outofstock_product"');
                            //we don't break here
                            $list_box_contents[$rows]['params'] = str_replace('class="', 'class="sold-out ', $list_box_contents[$rows]['params']);
                        }
                        switch (true) {
                             
                            case $listing->fields['products_quantity'] <= 0 && $date_diff_days > 0: //case $date_diff_days >= 0 && $listing->fields['products_quantity'] < 1 : // publishing date today or in future and not yet in stock
                                
                                $button_link = true;
                                $button = zen_image_button(BUTTON_IMAGE_BOOKX_UPCOMING, BUTTON_IMAGE_BOOKX_UPCOMING_ALT, 'class="upcoming_product"');
                                if($this->flag_group['by_availability'] == true) {
                                    $publishing_date_flag = 'upcoming-product';
                                }

								break;

                            case $listing->fields['products_quantity'] > 0 && abs($date_diff_days) < self::NEW_BOOK_LOOK_BACK: // product in stock and publishing date within range of "new" products
                                $button_link = true;
                                $button = zen_image_button(BUTTON_IMAGE_BOOKX_NEW, BUTTON_IMAGE_BOOKX_NEW_ALT, 'class="new_product"');
                                if($this->flag_group['by_availability'] == true) {
                                   $publishing_date_flag = 'new-product'; 
                                }
								
								break;

							default:
								break;
						}
					}
                   
                        $list_box_contents[$rows]['date_flag'] = $publishing_date_flag;
                        $list_box_contents[$rows][$product_name_column]['text'] = $new_product_text;
                        /*
                         * If you want to add $url_keywords and $active_boox_get_filters params to the url uncomment the following line
                         */
                        //$list_box_contents[$rows][$product_image_column]['text'] = str_replace('&amp;products_id=', $active_boox_get_filters . $url_keywords . '&amp;products_id=', $list_box_contents[$rows][$product_image_column]['text']);
                       
                        /* 
                         * @todo this is a bit fragil solution since layouts can change as they did and is not working.
                         * <br /><br /> is no longer in responsive layout. Now there is a <br> coming from somewhere that is also wrong.
                         * For now trying to replace on $list_box_contents[$rows][$product_price_column]['text']
                         */
                        $new_content = '';
                        if ($button) {
                        $bsearch = strpos($list_box_contents[$rows][$product_price_column]['text'], '<span class="cssB');
                        $esearch = strpos($list_box_contents[$rows][$product_price_column]['text'], 'an>', $bsearch) - 4;
                        $len = $esearch - $bsearch;
                        $new_content = substr_replace($list_box_contents[$rows][$product_price_column]['text'], '', $bsearch, $len);

                        
                        $new_content .= ($button_link == true ? '<a href="' . zen_href_link($_GET['main_page'], zen_get_all_get_params(array('action')) . 'action=buy_now&products_id=' . $listing->fields['products_id']) . '">' : '') . bookx_get_buy_now_button($listing->fields['products_id'], $button) . ($button_link == true ? '</a>' : '');
                        $list_box_contents[$rows][$product_price_column]['text'] = $new_content;
                    }

                    switch ($publishing_date_flag) {
                        case 'upcoming-product':

                            $list_box_contents[$rows]['params'] = str_replace('class="', 'class="upcoming-product ', $list_box_contents[$rows]['params']);
                            $upcoming_products_array[] = $list_box_contents[$rows];
                            unset($list_box_contents[$rows]);
                            break;

                        case 'new-product':
                            $list_box_contents[$rows]['params'] = str_replace('class="', 'class="new-product ', $list_box_contents[$rows]['params']);

                            $new_products_array[] = $list_box_contents[$rows];
                            unset($list_box_contents[$rows]);

                            break;
                    }

                    $listing->MoveNext();
                }
            }
        }
        
        if ($process == true) {
            $heading_row = array();

        if (!empty($new_products_array) || !empty($upcoming_products_array)) {
            $heading_row[0] = $list_box_contents[0];
            unset($list_box_contents[0]);
            if (count($list_box_contents) > 0) {
                $published_prod_heading_row = array(
                    'params' => 'class="bookxList-publishedProductsHeading"', 
                    '0' => array(
                        'align' => 'left', 
                        'params' => 'colspan="3"', 
                        'text' => '<h3 class="bookxPublishedProduct"><label>' . TEXT_BOOKX_PUBLISHED_PRODUCTS_LABEL . '</label></h3>')
                    );
                array_unshift($list_box_contents, $published_prod_heading_row);
            }
        } elseif (count($list_box_contents) > 0) {
            $published_prod_heading_row = array(
                'params' => 'class="bookxList-publishedProductsHeading"', 
                '0' => array(
                    'align' => 'left', 
                    'params' => 'colspan="3"', 
                    'text' => '<h3 class="bookxPublishedProduct"><label>' . TEXT_BOOKX_PUBLISHED_PRODUCTS_LABEL . '</label></h3>')
                );
            array_unshift($list_box_contents, $published_prod_heading_row);
        }

        if (!empty($new_products_array)) {
            $new_prod_heading_row = array(
                'params' => 'class="bookxList-newProductsHeading"', 
                '0' => array(
                    'align' => 'left', 
                    'params' => 'colspan="3"', 
                    'text' => '<h3 class="bookxNewProduct"><label>' . TEXT_BOOKX_NEW_PRODUCTS_LABEL . '</label></h3>')
                );
            array_unshift($new_products_array, $new_prod_heading_row);
            $list_box_contents = array_merge($new_products_array, $list_box_contents);
        }

        if (!empty($upcoming_products_array)) {
            $upcoming_prod_heading_row = array(
                'params' => 'class="bookxList-upcomingProductsHeading"', 
                '0' => array(
                    'align' => 'left', 
                    'params' => 'colspan="3"', 
                    'text' => '<h3 class="bookxUpcomingProduct"><label>' . TEXT_BOOKX_UPCOMING_PRODUCTS_LABEL . '</label></h3>')
                );
            array_unshift($upcoming_products_array, $upcoming_prod_heading_row);
            $list_box_contents = array_merge($upcoming_products_array, $list_box_contents);
        }

        if (!empty($heading_row)) {
            $list_box_contents = array_merge($heading_row, $list_box_contents);
        }

        //$zco_notifier->notify('NOTIFY_BOOKX_ADD_EXTRA_INFO_TO_PRODUCT_LISTING_TABULAR_DISPLAY_END');
        }
        
    }
    

    /**
     * This function gets triggered at the end of the header.php inside modules/pages/shopping_cart.
     * It adds some bookx specific data to the 'product_name' variable.
     */
    function insert_extra_bookx_attributes_to_cart_display(&$callingClass, $notifier, $paramsArray)
    {
        global $productArray;
        global $db;

        //$const = get_defined_constants();
        include_once DIR_FS_CATALOG . 'includes/languages/' . $_SESSION['language'] . '/product_bookx_info.php';
        include_once DIR_FS_CATALOG . 'includes/languages/' . $_SESSION['language'] . '/extra_definitions/product_bookx.php';
        //include_once DIR_FS_CATALOG_LANGUAGES . $_SESSION['language'] . '/product_bookx_info.php';
        //include_once DIR_FS_CATALOG_LANGUAGES . $_SESSION['language'] . '/extra_definitions/product_bookx.php';

        if (!empty($productArray)) {
            $ids = array();
            foreach ($productArray as $key => $product) {
                if (!empty($product['attributes'])) {
                    //*** this product with attributes, so ID needs to be cleaned
                    $id_parts = explode(':', $product['id']);
                    $ids[$id_parts[0]] = $key;
                } else {
                    $ids[$product['id']] = $key;
                }
            }

            $sql = 'SELECT be.products_id, be.volume, bed.products_subtitle,
					CONCAT_WS("-", SUBSTRING(be.isbn,1,3), SUBSTRING(be.isbn,4,1), SUBSTRING(be.isbn,5,6), SUBSTRING(be.isbn,11,2), SUBSTRING(be.isbn,13,1)) AS isbn_display,
					GROUP_CONCAT(DISTINCT a.author_name ORDER BY a.author_name ASC SEPARATOR " | ") AS authors
					FROM ' . TABLE_PRODUCT_BOOKX_EXTRA . ' be
					LEFT JOIN ' . TABLE_PRODUCT_BOOKX_EXTRA_DESCRIPTION . ' bed ON bed.products_id = be.products_id AND bed.languages_id = "' . (int) $_SESSION['languages_id'] . '"
				    LEFT JOIN ' . TABLE_PRODUCT_BOOKX_AUTHORS_TO_PRODUCTS . ' atp ON atp.products_id = be.products_id
    				LEFT JOIN ' . TABLE_PRODUCT_BOOKX_AUTHORS . ' a ON atp.bookx_author_id = a.bookx_author_id
					WHERE be.products_id IN (' . implode(',', array_keys($ids)) . ')
					GROUP BY be.products_id';

            $bookx_products_in_cart = $db->Execute($sql);

            while (!$bookx_products_in_cart->EOF) {
                $productArray[$ids[$bookx_products_in_cart->fields['products_id']]]['productsName'] .= /* seems to already be included // (!empty($bookx_products_in_cart->fields['volume']) ? ' ' . $bookx_products_in_cart->fields['volume'] : '') . */
                    (!empty($bookx_products_in_cart->fields['products_subtitle']) ? ' &ndash; ' . $bookx_products_in_cart->fields['products_subtitle'] : '') .
                    (!empty($bookx_products_in_cart->fields['authors']) ? '<br />' . $bookx_products_in_cart->fields['authors'] : '') .
                    (!empty($bookx_products_in_cart->fields['isbn_display']) ? '<br />' . LABEL_BOOKX_ISBN . ': ' . $bookx_products_in_cart->fields['isbn_display'] : '');
                // overkill ? . (strstr($bookx_products_in_cart->fields['authors'], '|') ? LABEL_BOOKX_AUTHORS : LABEL_BOOKX_AUTHOR) . ': ' .

                $bookx_products_in_cart->MoveNext();
            }
        }
    }

    /**
     * This function gets triggered by the file "modules/pages/index/main_template_vars.php"
     * and checks to see if a bookx filter is active and whether we need to insert
     * any special bookx info
     */
    function insert_search_term_special_bookx_info(&$callingClass, $notifier, $paramsArray)
    {
        if (isset($_GET['typefilter']) && 'bookx' == $_GET['typefilter']) {
            /* $bookx_filter = $_GET['bookxfilter'];
              $this->bookx_filter_active = true;
              $this->bookx_filter_type = $bookx_filter;
              $this->bookx_filter_value = $_GET[$this->bookx_filter_type]; */

            global $extra_bookx_filter_term_info;
            global $db;

            $extra_show_only_stocked_html = '';

            if (BOOKX_AUTHOR_LISTING_SHOW_ONLY_STOCKED ||
                BOOKX_GENRE_LISTING_SHOW_ONLY_STOCKED ||
                BOOKX_IMPRINT_LISTING_SHOW_ONLY_STOCKED ||
                BOOKX_PUBLISHER_LISTING_SHOW_ONLY_STOCKED ||
                BOOKX_SERIES_LISTING_SHOW_ONLY_STOCKED) {

                $checked = ( isset($_GET['bookx_include_out_of_stock']) && $_GET['bookx_include_out_of_stock'] ? 'checked' : '');

                $extra_show_only_stocked_html = '
					<script type="text/javascript">
					<!--
					function handleInStockOnlyCheckbox() {
						var n = window.location.href.indexOf("&bookx_include_out_of_stock=");
						var listOutOfStock = bookxFilterOnlyStockedCheckbox.checked;
						var newGetParameter = (listOutOfStock ? "&bookx_include_out_of_stock=true" : "");
						if (0 > n) {
							window.location.href = window.location.href + newGetParameter;
						} else {
							window.location.href = window.location.href.replace("&bookx_include_out_of_stock=true", newGetParameter);
						}
					}
					-->
					</script>
					<div id="bookxFilterOnlyStockedCheckboxContainer">
						<label><input id="bookxFilterOnlyStockedCheckbox" type="checkbox" ' . $checked . ' onClick="handleInStockOnlyCheckbox()" /> ' . TEXT_BOOKX_FILTERS_STOCKCHECKBOX_LABEL . '</label>
					</div>';
            }

            $extra_html = '';

            if (isset($_GET['bookx_author_id']) && !empty($_GET['bookx_author_id'])) {

                /*
                 * Trying to  not duplicate necessary queries. To display proper metatags for authors, etc, the query must happen before to populate
                 * metatags, and then will happen here again. 
                 * Not sure what's the best way, maybe someone can have another way. 
                 * Because this file is getting big and hard to read, I created a new observer to deal with canonical and metatags (dinamic metatags)
                 * So this information is comming from that file on a global scope.
                 * Maybe latter this two observers files will be combined in to one. 
                 * 
                 */

                $this->bookx_filter_active ++;
                $this->filtered_author_id = (int) $_GET['bookx_author_id'];

                $this->flag_show_product_bookx_filter_author_extra_info = bookx_get_show_product_switch('author', 'SHOW_', '_FILTER_EXTRA_INFO');
                if (BOOKX_LAYOUT_FLAG_OPTION_DONT_DISPLAY < (int) $this->flag_show_product_bookx_filter_author_extra_info) {

                    global $author_meta_info; // this is comming from class.bookx_observers_canonical.php
                    $author = $author_meta_info; // assign author to author_meta_tags , so now everything is the same. 

                    if (!empty($author->fields['author_image']) || BOOKX_LAYOUT_FLAG_OPTION_ALWAYS_DISPLAY == (int) $this->flag_show_product_bookx_filter_author_extra_info) {
                        $extra_html .= '<div id="bookx_filter_author_image">' . zen_image(DIR_WS_IMAGES . $author->fields['author_image'], $author->fields['author_name'], BOOKX_AUTHOR_IMAGE_MAX_WIDTH, BOOKX_AUTHOR_IMAGE_MAX_HEIGHT) . '</div>';
                    }

                    if (!empty($author->fields['author_description']) || BOOKX_LAYOUT_FLAG_OPTION_ALWAYS_DISPLAY == (int) $this->flag_show_product_bookx_filter_author_extra_info) {
                        $extra_html .= '<div id="bookx_filter_author_description">' . zen_html_entity_decode($author->fields['author_description']) . '</div>';
                    }

                    if (!empty($author->fields['author_url']) || BOOKX_LAYOUT_FLAG_OPTION_ALWAYS_DISPLAY == (int) $this->flag_show_product_bookx_filter_author_extra_info) {
                        $author_url = strpos($author->fields['author_url'], 'http') ? $author->fields['author_url'] : 'http://' . $author->fields['author_url'];
                        $extra_html .= '<a id="bookx_filter_author_url" href="' . $author_url . '" target="_blank">' . BOOKX_URL_LINK_TEXT_AUTHOR . '</a>';
                    }
                }
            }

            if (isset($_GET['bookx_author_type_id']) && !empty($_GET['bookx_author_type_id'])) {
                $this->bookx_filter_active ++;
                $this->filtered_author_type_id = (int) $_GET['bookx_author_type_id'];

                $this->flag_show_product_bookx_filter_author_type_extra_info = bookx_get_show_product_switch('author_type', 'SHOW_', '_FILTER_EXTRA_INFO');
                if (BOOKX_LAYOUT_FLAG_OPTION_DONT_DISPLAY < (int) $this->flag_show_product_bookx_filter_author_type_extra_info) {
                    $sql = 'SELECT atd.type_description, atd.type_image
		                    FROM ' . TABLE_PRODUCT_BOOKX_AUTHOR_TYPES_DESCRIPTION . '
							WHERE atd.bookx_author_type_id = "' . (int) $this->filtered_author_type_id . '" AND atd.languages_id = "' . (int) $_SESSION['languages_id'] . '"';
                    $author_type = $db->Execute($sql);

                    if (!empty($author_type->fields['type_image']) || BOOKX_LAYOUT_FLAG_OPTION_ALWAYS_DISPLAY == (int) $this->flag_show_product_bookx_filter_author_type_extra_info) {
                        $extra_html .= '<div id="bookx_filter_author_type_image">' . zen_image(DIR_WS_IMAGES . $author_type->fields['type_image'], $author_type->fields['type_description'], BOOKX_ICONS_MAX_WIDTH, BOOKX_ICONS_MAX_HEIGHT) . '</div>';
                    }

                    if (!empty($author_type->fields['type_description']) || BOOKX_LAYOUT_FLAG_OPTION_ALWAYS_DISPLAY == (int) $this->flag_show_product_bookx_filter_author_type_extra_info) {
                        $extra_html .= '<div id="bookx_filter_author_type_description">' . zen_html_entity_decode($author_type->fields['type_description']) . '</div>';
                    }
                }
            }

            if (isset($_GET['bookx_publisher_id']) && !empty($_GET['bookx_publisher_id'])) {
                $this->bookx_filter_active ++;
                $this->filtered_publisher_id = (int) $_GET['bookx_publisher_id'];

                $this->flag_show_product_bookx_filter_publisher_extra_info = bookx_get_show_product_switch('publisher', 'SHOW_', '_FILTER_EXTRA_INFO');
                if (BOOKX_LAYOUT_FLAG_OPTION_DONT_DISPLAY < (int) $this->flag_show_product_bookx_filter_publisher_extra_info) {

                    global $publisher_meta_info;
                    $publisher = $publisher_meta_info; // same logic here and so on

                    if (!empty($publisher->fields['publisher_image']) || BOOKX_LAYOUT_FLAG_OPTION_ALWAYS_DISPLAY == (int)$this->flag_show_product_bookx_filter_publisher_extra_info) {
                        $extra_html .= '<div id="bookx_filter_publisher_image">' . zen_image(DIR_WS_IMAGES . $publisher->fields['publisher_image'], $publisher->fields['publisher_name'], BOOKX_ICONS_MAX_WIDTH, BOOKX_ICONS_MAX_HEIGHT) . '</div>';
                    }

                    if (!empty($publisher->fields['publisher_description']) || BOOKX_LAYOUT_FLAG_OPTION_ALWAYS_DISPLAY == (int) $this->flag_show_product_bookx_filter_publisher_extra_info) {
                        $extra_html .= '<div id="bookx_filter_publisher_description">' . zen_html_entity_decode($publisher->fields['publisher_description']) . '</div>';
                    }

                    if (!empty($publisher->fields['publisher_url']) || BOOKX_LAYOUT_FLAG_OPTION_ALWAYS_DISPLAY == (int) $this->flag_show_product_bookx_filter_publisher_extra_info) {
                        $publisher_url = strpos($publisher->fields['publisher_url'], 'http') ? $publisher->fields['publisher_url'] : 'http://' . $publisher->fields['publisher_url'];
                        $extra_html .= '<a id="bookx_filter_publisher_url" href="' . $publisher_url . '" target="_blank">' . BOOKX_URL_LINK_TEXT_PUBLISHER . '</a>';
                    }
                }
            }

            if (isset($_GET['bookx_imprint_id']) && !empty($_GET['bookx_imprint_id'])) {
                $this->bookx_filter_active ++;
                $this->filtered_imprint_id = (int) $_GET['bookx_imprint_id'];

                $this->flag_show_product_bookx_filter_imprint_extra_info = bookx_get_show_product_switch('imprint', 'SHOW_', '_FILTER_EXTRA_INFO');
                if (BOOKX_LAYOUT_FLAG_OPTION_DONT_DISPLAY < (int) $this->flag_show_product_bookx_filter_imprint_extra_info) {

                    global $imprint_meta_info;
                    $imprint = $imprint_meta_info;

                    if (!empty($imprint->fields['imprint_image']) || BOOKX_LAYOUT_FLAG_OPTION_ALWAYS_DISPLAY == (int) $this->flag_show_product_bookx_filter_imprint_extra_info) {
                        $extra_html .= '<div id="bookx_filter_imprint_image">' . zen_image(DIR_WS_IMAGES . $imprint->fields['imprint_image'], $imprint->fields['imprint_name'], BOOKX_ICONS_MAX_WIDTH, BOOKX_ICONS_MAX_HEIGHT) . '</div>';
                    }

                    if (!empty($imprint->fields['imprint_description']) || BOOKX_LAYOUT_FLAG_OPTION_ALWAYS_DISPLAY == (int) $this->flag_show_product_bookx_filter_imprint_extra_info) {
                        $extra_html .= '<div id="bookx_filter_imprint_description">' . zen_html_entity_decode($imprint->fields['imprint_description']) . '</div>';
                    }
                }
            }

            if (isset($_GET['bookx_series_id']) && !empty($_GET['bookx_series_id'])) {
                $this->bookx_filter_active ++;
                $this->filtered_series_id = (int) $_GET['bookx_series_id'];

                $this->flag_show_product_bookx_filter_series_extra_info = bookx_get_show_product_switch('series', 'SHOW_', '_FILTER_EXTRA_INFO');
                if (BOOKX_LAYOUT_FLAG_OPTION_DONT_DISPLAY < (int)$this->flag_show_product_bookx_filter_series_extra_info) {
                    global $series_meta_info;
                    $series = $series_meta_info;


                    if (!empty($series->fields['series_image']) ||
                        BOOKX_LAYOUT_FLAG_OPTION_ALWAYS_DISPLAY == (int) $this->flag_show_product_bookx_filter_series_extra_info) {
                        $extra_html .= '<div id="bookx_filter_series_image_container">' . zen_image(DIR_WS_IMAGES . $series->fields['series_image'], $series->fields['series_name'], '', '', 'id="bookx_filter_series_image"') . '</div>';
                    }

                    if (!empty($series->fields['series_description']) || 
                        BOOKX_LAYOUT_FLAG_OPTION_ALWAYS_DISPLAY == (int) $this->flag_show_product_bookx_filter_series_extra_info) {
                        $extra_html .= '<div id="bookx_filter_series_description">' . zen_html_entity_decode($series->fields['series_description']) . '</div>';
                    }
                }
            }

            if (isset($_GET['bookx_genre_id']) && !empty($_GET['bookx_genre_id'])) {
                $this->bookx_filter_active ++;
                $this->filtered_genre_id = (int) $_GET['bookx_genre_id'];

                $this->flag_show_product_bookx_filter_genre_extra_info = bookx_get_show_product_switch('genre', 'SHOW_', '_FILTER_EXTRA_INFO');
                if (BOOKX_LAYOUT_FLAG_OPTION_DONT_DISPLAY < (int) $this->flag_show_product_bookx_filter_genre_extra_info) {

                    global $genre_meta_info;
                    $genre = $genre_meta_info;

                    if (!empty($genre->fields['genre_image']) || BOOKX_LAYOUT_FLAG_OPTION_ALWAYS_DISPLAY == (int) $this->flag_show_product_bookx_filter_genre_extra_info) {
                        $extra_html .= '<div id="bookx_filter_genre_image">' . zen_image(DIR_WS_IMAGES . $genre->fields['genre_image'], $genre->fields['genre_name'], BOOKX_ICONS_MAX_WIDTH, BOOKX_ICONS_MAX_HEIGHT) . '</div>';
                    }

                    if (!empty($genre->fields['genre_name']) || BOOKX_LAYOUT_FLAG_OPTION_ALWAYS_DISPLAY == (int) $this->flag_show_product_bookx_filter_genre_extra_info) {
                        $extra_html .= '<div id="bookx_filter_binding_name">' . $genre->fields['genre_name'] . '</div>';
                    }
                }
            }

            if (1 == $this->bookx_filter_active) {
                //** only one filter is active, so it makes sense to insert filter specific info
                $extra_bookx_filter_term_info .= $extra_html;
            } else {
                $extra_bookx_filter_term_info = null;
            }

            $extra_bookx_filter_term_info .= $extra_show_only_stocked_html;
        }
    }

    function check_pType_filters_and_reset(&$callingClass, $notifier, $paramsArray)
    {
        $all_filters_blank = false;
        // release bookx_author_id when nothing is there so a blank filter is not setup.
        // this will result in the home page, if used
        if (isset($_GET ['bookx_author_id']) && empty($_GET ['bookx_author_id'])) {
            unset($_GET ['bookx_author_id']);
            unset($callingClass->bookx_author_id);
            $all_filters_blank = true;
        }

        // release bookx_author_type_id when nothing is there so a blank filter is not setup.
        // this will result in the home page, if used
        if (isset($_GET ['bookx_author_type_id']) && empty($_GET ['bookx_author_type_id'])) {
            unset($_GET ['bookx_author_type_id']);
            unset($callingClass->bookx_author_type_id);
        } else {
            $all_filters_blank = false;
        }

        // release bookx_author_type_id when nothing is there so a blank filter is not setup.
        // this will result in the home page, if used
        if (isset($_GET ['bookx_author_type_id']) && empty($_GET ['bookx_author_type_id'])) {
            unset($_GET ['bookx_author_type_id']);
            unset($callingClass->bookx_author_type_id);
        } else {
            $all_filters_blank = false;
        }

        // only release bookxfilter if all bookx filters are blank
        // this will result in the home page, if used
        /* if ($all_filters_blank) {
          unset ( $_GET ['bookxfilter'] );
          unset ( $callingClass->bookxfilter );
          } */
    }

    function insert_bookx_attributes_into_search_query(&$callingClass, $notifier, $paramsArray)
    {
        global $db, $from_str, $where_str, $keywords, $search_keywords;

        $extra_from = ' LEFT JOIN ' . TABLE_PRODUCT_BOOKX_EXTRA . ' srchbe ON srchbe.products_id = p.products_id
				        LEFT JOIN ' . TABLE_PRODUCT_BOOKX_EXTRA_DESCRIPTION . ' srchbed ON srchbed.products_id = p.products_id AND srchbed.languages_id = "' . (int) $_SESSION['languages_id'] . '"
				        LEFT JOIN ' . TABLE_PRODUCT_BOOKX_AUTHORS_TO_PRODUCTS . ' srchbatp ON srchbatp.products_id = p.products_id
				        LEFT JOIN ' . TABLE_PRODUCT_BOOKX_AUTHORS . ' srchba ON srchba.bookx_author_id = srchbatp.bookx_author_id
				        LEFT JOIN ' . TABLE_PRODUCT_BOOKX_IMPRINTS . ' srchbi ON srchbi.bookx_imprint_id = srchbe.bookx_imprint_id
				        LEFT JOIN ' . TABLE_PRODUCT_BOOKX_PUBLISHERS . ' srchbpub ON srchbpub.bookx_publisher_id = srchbe.bookx_publisher_id
				        LEFT JOIN ' . TABLE_PRODUCT_BOOKX_SERIES_DESCRIPTION . ' srchbsd ON srchbsd.bookx_series_id = srchbe.bookx_series_id
				     	LEFT JOIN ' . TABLE_PRODUCT_BOOKX_GENRES_TO_PRODUCTS . ' srchbgtp ON srchbgtp.products_id = srchbe.products_id
				     	LEFT JOIN ' . TABLE_PRODUCT_BOOKX_GENRES_DESCRIPTION . ' srchbgd ON srchbgd.bookx_genre_id = srchbgtp.bookx_genre_id ';

        $from_str .= $extra_from;

        for ($i = 0, $n = sizeof($search_keywords); $i < $n; $i++) {
            //$extra_where = '';
            switch ($search_keywords[$i]) {
                case '(':
                case ')':
                case 'and':
                case 'or':
                    //$extra_where .= " " . $search_keywords[$i] . " ";
                    break;
                default:

                    $extra_where = " OR srchbed.products_subtitle LIKE '%:keywords%'
									  OR srchba.author_name LIKE '%:keywords%'
									  OR srchbpub.publisher_name LIKE '%:keywords%'
									  OR srchbsd.series_name LIKE '%:keywords%'
									  OR srchbgd.genre_name LIKE '%:keywords%'
									  OR srchbi.imprint_name LIKE '%:keywords%'";

                    $isbn_test = str_replace('-', '', $search_keywords[$i]);
                    if (ctype_digit($isbn_test)) {
                        $extra_where .= " OR srchbe.isbn LIKE '%" . $isbn_test . "%'";
                    }

                    $extra_where = $db->bindVars($extra_where, ':keywords', $search_keywords[$i], 'noquotestring');

                    $where_str = str_replace("pd.products_name LIKE '%" . $search_keywords[$i] . "%'", "pd.products_name LIKE '%" . $search_keywords[$i] . "%'" . $extra_where, $where_str);
                    break;
            }
        }
    }

    /**
     * This function gets triggered by the file "includes/modules/[ACTIVE TEMPLATE]/new_products.php"
     * and it adds some bookx specific query items to the database query
     */
    function insert_bookx_attributes_into_new_products_query(&$callingClass, $notifier, $paramsArray)
    {
        global $db, $new_products_query, $new_products;

        // @TODO New stuff added by phill, check this
        if (!empty($new_products_query)) {
            $this->loadFilterValues();

            $extra_having = '';
            $extra_join_condition = '';
            $additional_bookx_fields = '';
            $extra_join = '';
            $group_by = '';
            
            if (!empty(self::NEW_BOOK_LOOK_BACK)) {
                $additional_bookx_fields .= ',p.products_quantity, be.publishing_date,p.products_date_available,
    		                                 DATEDIFF("' . date('Y-m-d') . '",
													  CONCAT_WS("-",
														        SUBSTRING(be.publishing_date, 1,4 ),
														        IF(SUBSTRING(be.publishing_date, 6,2 ) = "00", "01", SUBSTRING(be.publishing_date, 6,2 ) ),
														        IF(SUBSTRING(be.publishing_date, 9,2 )  = "00", "01", SUBSTRING(be.publishing_date, 9,2 ))
                                                               )
                                                      ) AS pubdate_diff_today';
                /* $extra_join_condition = ' AND (be.publishing_date IS NOT NULL) AND (DATEDIFF("' . date('Y-m-d') . '",
                  CONCAT_WS("-",
                  SUBSTRING(be.publishing_date, 1,4 ),
                  IF(SUBSTRING(be.publishing_date, 6,2 ) = "00", "01", SUBSTRING(be.publishing_date, 6,2 ) ),
                  IF(SUBSTRING(be.publishing_date, 9,2 )  = "00", "01", SUBSTRING(be.publishing_date, 9,2 ))
                  )
                  )
                  BETWEEN 0 AND ' . intval(self::NEW_BOOK_LOOK_BACK) . ') '; */
                //$extra_having .= ' AND (be.publishing_date IS NOT NULL) AND p.products_quantity > 0 ';
                $extra_having = ' HAVING (be.publishing_date IS NOT NULL)  /* we have a BookX publishing date enterd */
    		                      AND (   		                            
    		                             /* pub date is less than "number of days to look BACK" into the past (i.e. a "new" product) and stock is more than zero or date expected is set */
    		                             (( pubdate_diff_today BETWEEN 0 AND ' . intval(self::NEW_BOOK_LOOK_BACK) . ') AND (p.products_quantity > 0 OR (p.products_date_available IS NOT NULL AND p.products_date_available >= "' . date('Y-m-d') . '")))
    		                          )';
            }

            $extra_join = ' LEFT JOIN ' . TABLE_PRODUCT_BOOKX_EXTRA . ' be ON be.products_id = pd.products_id ' . $extra_join_condition
                . ' LEFT JOIN ' . TABLE_PRODUCT_BOOKX_EXTRA_DESCRIPTION . ' bed ON bed.products_id = pd.products_id AND bed.languages_id = "' . (int) $_SESSION['languages_id'] . '"';

            ///****** keep these commented JOINs here in case we want to implement more fields later

            /* LEFT JOIN ' . TABLE_PRODUCT_BOOKX_AUTHORS_TO_PRODUCTS . ' srchbatp ON srchbatp.products_id = p.products_id
              LEFT JOIN ' . TABLE_PRODUCT_BOOKX_AUTHORS . ' srchba ON srchba.bookx_author_id = srchbatp.bookx_author_id
              LEFT JOIN ' . TABLE_PRODUCT_BOOKX_IMPRINTS . ' srchbi ON srchbi.bookx_imprint_id = srchbe.bookx_imprint_id
              LEFT JOIN ' . TABLE_PRODUCT_BOOKX_PUBLISHERS . ' srchbpub ON srchbpub.bookx_publisher_id = srchbe.bookx_publisher_id
              LEFT JOIN ' . TABLE_PRODUCT_BOOKX_SERIES_DESCRIPTION . ' srchbsd ON srchbsd.bookx_series_id = srchbe.bookx_series_id
              LEFT JOIN ' . TABLE_PRODUCT_BOOKX_GENRES_TO_PRODUCTS . ' srchbgtp ON srchbgtp.products_id = srchbe.products_id
              LEFT JOIN ' . TABLE_PRODUCT_BOOKX_GENRES_DESCRIPTION . ' srchbgd ON srchbgd.bookx_genre_id = srchbgtp.bookx_genre_id '; */

            $name_replacement_field = 'pd.products_description, CONCAT_WS(""
    											  ,pd.products_name
    											  ,IF(NULLIF(be.volume, "") IS NOT NULL, CONCAT_WS("", " <span class=\'bookxProdVolume\'>", REPLACE("' . LABEL_BOOKX_VOLUME . '", "%s", be.volume), "</span>"), "")
    											  ,IF(NULLIF(bed.products_subtitle, "") IS NOT NULL, CONCAT_WS("", " &ndash; <span class=\'bookxProdSubtitle\'>", bed.products_subtitle, "</span>"), "")
    											  ) AS products_name';

            //** authors
            if ($this->flag_show['authors']) {
                if ($this->flag_show['authors_with_type_below_sort_order']) {
                    $additional_author_join_condition = ' AND bat.type_sort_order < "' . $this->flag_show['authors_with_type_below_sort_order'] . '" ';
                } else {
                    $additional_author_join_condition = '';
                }

                $extra_join .= ' LEFT JOIN ' . TABLE_PRODUCT_BOOKX_AUTHORS_TO_PRODUCTS . ' batp ON batp.products_id = be.products_id
    							 LEFT JOIN ' . TABLE_PRODUCT_BOOKX_AUTHORS . ' ba ON batp.bookx_author_id = ba.bookx_author_id ';
                /* .' LEFT JOIN ' . TABLE_PRODUCT_BOOKX_AUTHOR_TYPES . ' bat ON bat.bookx_author_type_id = batp.bookx_author_type_id ' . $additional_author_join_condition .
                  ' LEFT JOIN ' . TABLE_PRODUCT_BOOKX_AUTHOR_TYPES_DESCRIPTION . ' batd ON batd.bookx_author_type_id = batp.bookx_author_type_id AND batd.languages_id = "' . (int)$_SESSION['languages_id'] . '"'; */

                //	if ($this->flag_show['author_type'] ) {
                //		$additional_bookx_fields .= ', GROUP_CONCAT(DISTINCT CONCAT_WS("", IF("" = IFNULL(batd.type_description,""), "", CONCAT_WS("", "<span class=\"bookxLabel\">", batd.type_description , ": </span>")), ba.author_name) ORDER BY bat.type_sort_order ASC SEPARATOR " &middot; ") AS authors';
                //	} else {
                $additional_bookx_fields .= ', GROUP_CONCAT(DISTINCT ba.author_name ORDER BY ba.author_name ASC SEPARATOR " &middot; ") AS authors';
                //	}

                $group_by .= ' GROUP BY p.products_id ';
            }

            $new_products_query = str_replace('pd.products_name', $name_replacement_field . $additional_bookx_fields, $new_products_query);
            $new_products_query = str_replace(' WHERE ', $extra_join . ' WHERE ', $new_products_query);
            $new_products_query .= $group_by . $extra_having;
            $new_products_query .= ' ORDER BY be.publishing_date DESC, p.products_date_available DESC';
        }
    }

    /**
     * This function gets triggered by the file "includes/modules/[ACTIVE TEMPLATE]/new_products.php"
     * and it adds some bookx specific data to the $list_box_contents array
     */
    function insert_bookx_attributes_into_new_products_listing(&$callingClass, $notifier, $paramsArray)
    {
        global $list_box_contents, $productsInCategory, $title, $new_products_category_id;
        global $new_products; /* @var $new_products queryFactoryResult */


        if (!empty($new_products)) {
            $num_products_count = $new_products->RecordCount();

            // show only when 1 or more
            if ($num_products_count > 0) {

                if (isset($new_products_category_id) && $new_products_category_id != 0) {
                    $category_title = zen_get_categories_name((int) $new_products_category_id);
                    $title = '<h2 class="bookxNewProduct centerBoxHeading"><label>' . sprintf(TABLE_HEADING_NEW_PRODUCTS, strftime('%B')) . ($category_title != '' ? ' - ' . $category_title : '' ) . '</label></h2>';
                } else {
                    $title = '<h2 class="bookxNewProduct centerBoxHeading"><label>' . sprintf(TABLE_HEADING_NEW_PRODUCTS, strftime('%B')) . '</label></h2>';
                }

                if ($num_products_count < SHOW_PRODUCT_INFO_COLUMNS_NEW_PRODUCTS || SHOW_PRODUCT_INFO_COLUMNS_NEW_PRODUCTS == 0) {
                    $col_width = floor(100 / $num_products_count);
                } else {
                    $col_width = floor(100 / SHOW_PRODUCT_INFO_COLUMNS_NEW_PRODUCTS);
                }


                $new_products->rewind();

                // This seemed to be necessary in ZC Versions up to 1.5.3, but not anymore in 1.5.5
                if (1 <= intval(PROJECT_VERSION_MAJOR) && '5.5' > floatval(PROJECT_VERSION_MINOR)) {
                    // don't understand why this is necessary, but without it shows the first entry twice ?!
                    $new_products->cursor = 0;
                    $new_products->MoveNext();
                    // eof ?!!?
                }
                
                $row = 0;
                $col = 0;
                while (!$new_products->EOF) {
                                        
                    $products_price = zen_get_products_display_price($new_products->fields['products_id']); //zen_get_products_actual_price($products_id)
                    if (!isset($productsInCategory[$new_products->fields['products_id']]))
                        $productsInCategory[$new_products->fields['products_id']] = zen_get_generated_category_path_rev($new_products->fields['master_categories_id']);
                    // DO we need to add this to the url ? It messes if using url rewrite
                    $product_detail_url = zen_href_link(zen_get_info_page($new_products->fields['products_id']), 'cPath=' . $productsInCategory[$new_products->fields['products_id']] . '&products_id=' . $new_products->fields['products_id'] . '&typefilter=bookx&bookx_publishing_status=new');

                    $list_box_contents[$row][$col] = array('params' => 'class="centerBoxContentsNew centeredContent "' . ' ' . 'style="float: left; width:' . $col_width . '%;"',
                        'text' => (($new_products->fields['products_image'] == '' and PRODUCTS_IMAGE_NO_IMAGE_STATUS == 0) ? '' : '<a href="' . $product_detail_url . '" class="bookxProductImage">' . zen_image(DIR_WS_IMAGES . $new_products->fields['products_image'], $new_products->fields['products_name'], IMAGE_PRODUCT_NEW_WIDTH, IMAGE_PRODUCT_NEW_HEIGHT) . '</a>')
                        . '<a href="' . $product_detail_url . '" class="bookxProductName">' . $new_products->fields['products_name'] . '</a>'
                        . '<h6 class="bookxAuthors">' . $new_products->fields['authors'] . '</h6>'
                        . ('-1' == BOOKX_NEW_PRODUCTS_SHOW_PRODUCT_DESCRIPTION_NUMOFCHARS || '0' < BOOKX_NEW_PRODUCTS_SHOW_PRODUCT_DESCRIPTION_NUMOFCHARS ?
                        '<div class="newDescriptionCell">' . ( '-1' == BOOKX_NEW_PRODUCTS_SHOW_PRODUCT_DESCRIPTION_NUMOFCHARS ? $new_products->fields['products_description'] : bookx_truncate_paragraph($new_products->fields['products_description'], BOOKX_NEW_PRODUCTS_SHOW_PRODUCT_DESCRIPTION_NUMOFCHARS)) . ' <a href="' . $product_detail_url . '">' . TEXT_BOOKX_MORE_PRODUCT_INFO . '</a></div>' : '' )
                        . $products_price);

                    $col ++;
                    if ($col > (SHOW_PRODUCT_INFO_COLUMNS_NEW_PRODUCTS - 1)) {
                        $col = 0;
                        $row ++;
                    }

                    $new_products->MoveNextRandom();
                }
            }
        }
        
    }

    /**
     * This function gets triggered by the file "includes/modules/[ACTIVE TEMPLATE]/upcoming_products.php"
     * and it adds some bookx specific query items to the database query
     */
    function insert_bookx_attributes_into_upcoming_products_query(&$callingClass, $notifier, $paramsArray)
    {
        global $db, $expected_query, $expected;

        if (!empty($expected_query)) {
            $this->loadFilterValues();
            //pr($this);
            // @TODO new stuff added by phill. Check this

            $additional_bookx_fields = '';
            $extra_join = '';
            $extra_having = '';
            $extra_where_condition = '';
            $group_by = '';
            
            if (!empty(self::UPCOMING_BOOK_LOOK_AHEAD)) {
                //*** WHERE condition: publishing_date is set and with maximum days into the future and past as set by Admin values "look ahead" and "look back"

                $additional_bookx_fields .= ',p.products_quantity,
    		                                DATEDIFF("' . date('Y-m-d') . '",
											CONCAT_WS("-",
											SUBSTRING(be.publishing_date, 1,4 ),
											IF(SUBSTRING(be.publishing_date, 6,2 ) = "00", "01", SUBSTRING(be.publishing_date, 6,2 ) ),
											IF(SUBSTRING(be.publishing_date, 9,2 )  = "00", "01", SUBSTRING(be.publishing_date, 9,2 ))
                                            )
                                            ) AS pubdate_diff_today';
                /* $extra_where_condition = ' OR ((be.publishing_date IS NOT NULL) AND ((DATEDIFF("' . date('Y-m-d') . '",
                  CONCAT_WS("-",
                  SUBSTRING(be.publishing_date, 1,4 ),
                  IF(SUBSTRING(be.publishing_date, 6,2 ) = "00", "01", SUBSTRING(be.publishing_date, 6,2 ) ),
                  IF(SUBSTRING(be.publishing_date, 9,2 )  = "00", "01", SUBSTRING(be.publishing_date, 9,2 ))
                  )
                  )
                  BETWEEN -' . intval(self::UPCOMING_BOOK_LOOK_AHEAD) . ' AND ' . '0' . // replaced by '0' : intval(self::NEW_BOOK_LOOK_BACK)
                  ')
                  )
                  )) '; */

                //$extra_having = ' AND p.products_quantity < 1';
                $extra_having .= ' HAVING (be.publishing_date IS NOT NULL)  /* we have a BookX publishing date entered */
    		                    AND (
    		                    /* pub date is less than "number of days to look AHEAD" into the future */
    		                    (pubdate_diff_today BETWEEN -' . intval(self::UPCOMING_BOOK_LOOK_AHEAD) . ' AND 0)
    		                    OR
    		                    /* pub date is less than "number of days to look BACK" into the past (i.e. a "new" product) but stock is still zero and no date expected is set or in the past*/
    		                    (( pubdate_diff_today BETWEEN 0 AND ' . intval(self::NEW_BOOK_LOOK_BACK) . ') AND p.products_quantity < 1 AND (date_expected IS NULL OR date_expected < "' . date('Y-m-d') . '"))
    		                          )';
            }

            $extra_join .= ' LEFT JOIN ' . TABLE_PRODUCT_BOOKX_EXTRA . ' be ON be.products_id = pd.products_id
    						 LEFT JOIN ' . TABLE_PRODUCT_BOOKX_EXTRA_DESCRIPTION . ' bed ON bed.products_id = pd.products_id AND bed.languages_id = "' . (int)$_SESSION['languages_id'] . '"';

            $name_replacement_field = ' be.publishing_date, pd.products_description, p.products_image,
                CONCAT_WS(""
                ,pd.products_name
                ,IF(NULLIF(be.volume, "") IS NOT NULL, CONCAT_WS("", " <span class=\'bookxProdVolume\'>", REPLACE("' . LABEL_BOOKX_VOLUME . '", "%s", be.volume), "</span>"), "")
                ,IF(NULLIF(bed.products_subtitle, "") IS NOT NULL, CONCAT_WS("", " &ndash; <span class=\'bookxProdSubtitle\'>", bed.products_subtitle, "</span>"), "")
                ) AS products_name,
                CASE WHEN DAYOFMONTH(be.publishing_date) THEN DATE_FORMAT(be.publishing_date, "' . DATE_FORMAT_SHORT . '")
                WHEN MONTH(be.publishing_date) THEN DATE_FORMAT(be.publishing_date, "' . DATE_FORMAT_MONTH_AND_YEAR . '")
                ELSE YEAR(be.publishing_date)
                END AS formatted_publishing_date';
            /* $date_replacement_field = ' IF((p.products_date_available IS NULL) OR (p.products_date_available = "0000-00-00 00:00:00"), be.publishing_date, p.products_date_available) AS date_expected,
              CASE WHEN DAYOFMONTH(be.publishing_date) THEN DATE_FORMAT(be.publishing_date, "' . DATE_FORMAT_SHORT . '")
              WHEN MONTH(be.publishing_date) THEN DATE_FORMAT(be.publishing_date, "' . DATE_FORMAT_MONTH_AND_YEAR . '")
              ELSE YEAR(be.publishing_date)
              END AS formatted_publishing_date'; */

            if ($this->flag_show['authors']) {
                //$new_product_text = '<h2 class="bookxAuthors">' . $expectedItems[$i]['authors'] . '</h2>';
            }
            if ($this->flag_show['pages']) {
                $additional_bookx_fields .= ', be.pages ';
            }
            if ($this->flag_show['size']) {
                $additional_bookx_fields .= ', be.size ';
            }
            if ($this->flag_show['isbn']) {
                $additional_bookx_fields .= ', be.isbn AS isbn_display ';
            }
            if ($this->flag_show['printing']) {
                $additional_bookx_fields .= ', printd.printing_description ';
                $extra_join .= ' LEFT JOIN ' . TABLE_PRODUCT_BOOKX_PRINTING_DESCRIPTION . ' printd ON printd.bookx_printing_id = be.bookx_printing_id AND printd.languages_id = "' . (int)$_SESSION['languages_id'] . '"';
            }

            if ($this->flag_show['binding']) {
                $additional_bookx_fields .= ', bd.binding_description ';
                $extra_join .= ' LEFT JOIN ' . TABLE_PRODUCT_BOOKX_BINDING_DESCRIPTION . ' bd ON bd.bookx_binding_id = be.bookx_binding_id AND bd.languages_id = "' . (int)$_SESSION['languages_id'] . '"';
            }

            if ($this->flag_show['condition']) {
                $additional_bookx_fields .= ', cd.condition_description ';
                $extra_join .= ' LEFT JOIN ' . TABLE_PRODUCT_BOOKX_CONDITIONS_DESCRIPTION . ' cd ON cd.bookx_condition_id = be.bookx_condition_id AND cd.languages_id = "' . (int)$_SESSION['languages_id'] . '"';
            }

            if ($this->flag_show['model']) {
                $additional_bookx_fields .= ', p.products_model ';
            }
            //** authors
            if ($this->flag_show['authors']) {
                if ($this->flag_show['authors_with_type_below_sort_order']) {
                    $additional_author_join_condition = ' AND bat.type_sort_order < "' . $this->flag_show['authors_with_type_below_sort_order'] . '" ';
                } else {
                    $additional_author_join_condition = '';
                }

                $extra_join .= ' LEFT JOIN ' . TABLE_PRODUCT_BOOKX_AUTHORS_TO_PRODUCTS . ' batp ON batp.products_id = be.products_id
    							 LEFT JOIN ' . TABLE_PRODUCT_BOOKX_AUTHORS . ' ba ON batp.bookx_author_id = ba.bookx_author_id
    							 LEFT JOIN ' . TABLE_PRODUCT_BOOKX_AUTHOR_TYPES . ' bat ON bat.bookx_author_type_id = batp.bookx_author_type_id ' . $additional_author_join_condition .
                    ' LEFT JOIN ' . TABLE_PRODUCT_BOOKX_AUTHOR_TYPES_DESCRIPTION . ' batd ON batd.bookx_author_type_id = batp.bookx_author_type_id AND batd.languages_id = "' . (int)$_SESSION['languages_id'] . '"';

                //	LEFT JOIN ' . TABLE_PRODUCT_BOOKX_AUTHORS_DESCRIPTION . ' bad ON bad.bookx_author_id = ba.bookx_author_id AND bad.languages_id = "' . (int)$_SESSION['languages_id'] . '"


                if ($this->flag_show['author_type']) {
                    $additional_bookx_fields .= ', GROUP_CONCAT(DISTINCT CONCAT_WS("", IF("" = IFNULL(batd.type_description,""), "", CONCAT_WS("", "<span class=\"bookxLabel\">", batd.type_description , ": </span>")), ba.author_name) ORDER BY bat.type_sort_order ASC SEPARATOR \' &middot; \') AS authors';
                } else {
                    $additional_bookx_fields .= ', GROUP_CONCAT(ba.author_name ORDER BY ba.author_name ASC SEPARATOR " &middot; ") AS authors';
                }

                $group_by .= ' GROUP BY p.products_id ';
            }
            
            $expected_query = str_replace('pd.products_name', $name_replacement_field . $additional_bookx_fields, $expected_query);
            $expected_query = str_replace(' WHERE ', $extra_join . ' WHERE ', $expected_query);
            $date_available_clause = zen_get_upcoming_date_range();
            $expected_query = str_replace($date_available_clause, '', $expected_query);

            /* if(!empty($extra_where_condition)) {
              $expected_query = str_replace(' p.products_date_available ', ' (p.products_date_available ', $expected_query);
              } */
            //$expected_query = str_replace('products_date_available as date_expected', $date_replacement_field, $expected_query);
            //$expected_query = $extra_having;
            $expected_query = str_replace(' ORDER BY date_expected ', $extra_where_condition . $group_by . $extra_having . ' ORDER BY date_expected, be.publishing_date, p.products_date_available ', $expected_query);
            //pr($expected_query);
        }
    }

    /**
     * This function gets triggered by the file "includes/modules/[ACTIVE TEMPLATE]/product_listing_alpha_sorter.php"
     * and it adds a hidden field to the alpha sorter HTML form, so any active BookX filter will be kept active when sorting
     */
    function insert_bookx_hidden_field_into_alpha_sorter(&$callingClass, $notifier, $paramsArray)
    {

        if (isset($_GET['typefilter']) && 'bookx' == $_GET['typefilter']) {
            if (isset($_GET['bookx_author_id']) && '' != $_GET['bookx_author_id']) {
                echo zen_draw_hidden_field('bookx_author_id', $_GET['bookx_author_id']);
            }

            if (isset($_GET['bookx_publisher_id']) && '' != $_GET['bookx_publisher_id']) {
                echo zen_draw_hidden_field('bookx_publisher_id', $_GET['bookx_publisher_id']);
            }

            if (isset($_GET['bookx_imprint_id']) && '' != $_GET['bookx_imprint_id']) {
                echo zen_draw_hidden_field('bookx_imprint_id', $_GET['bookx_imprint_id']);
            }

            if (isset($_GET['bookx_series_id']) && '' != $_GET['bookx_series_id']) {
                echo zen_draw_hidden_field('bookx_series_id', $_GET['bookx_series_id']);
            }

            if (isset($_GET['bookx_genre_id']) && '' != $_GET['bookx_genre_id']) {
                echo zen_draw_hidden_field('bookx_genre_id', $_GET['bookx_genre_id']);
            }
            if (isset($_GET['bookx_publishing_status']) && '' != $_GET['bookx_publishing_status']) {
                echo zen_draw_hidden_field('bookx_publishing_status', $_GET['bookx_publishing_status']);
            }
        }
    }

}