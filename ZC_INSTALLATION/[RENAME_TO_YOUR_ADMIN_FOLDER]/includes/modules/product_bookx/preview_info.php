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
 * @version BookX V 0.9.4-revision8 BETA
 * @version $Id: preview_info.php 2016-02-02 philou $
 */
if (!defined ('IS_ADMIN_FLAG')) {
    die ('Illegal Access');
}

$languages = zen_get_languages();

if (zen_not_null($_POST)) {
  
    $pInfo = new objectInfo($_POST);
   
    $products_name = $_POST['products_name'];
    $products_description = $_POST['products_description'];
    $products_url = $_POST['products_url'];

    /**
     *  BookX data which is not language specific
     */
    $pInfo->publisher = bookx_get_publisher_name($_POST['bookx_publisher_id']);
    $pInfo->imprint = bookx_get_imprint_name($_POST['bookx_imprint_id']);
    $pInfo->publishing_date = $_POST['publishing_date'];
    $pInfo->pages = $_POST['pages'];
    $pInfo->volume = $_POST['volume'];
    $pInfo->size = $_POST['size'];
    $pInfo->isbn_display = bookx_format_isbn_for_display($_POST['isbn']);
    
    /**
     * @since v1.0.0
     * This family obj is initiated in BookX admin observer -> bookx_notify_begin_admin_products
     */
    if(isset($_POST['bookx_family_id']) && $_POST['bookx_family_id'] !='' ) {
        $pInfo->bookx_family = $objBookxFamily->family_name;
    }

    /**
     * BookX data which has language sepcific names / descriptions to display
     */
    $pInfo->bookx_binding_id = $_POST['bookx_binding_id'];
    $pInfo->bookx_printing_id = $_POST['bookx_printing_id'];
    $pInfo->bookx_condition_id = $_POST['bookx_condition_id'];
    $pInfo->bookx_series_id = $_POST['bookx_series_id'];
    $products_subtitle = $_POST['products_subtitle'];

    $pInfo->genres_display = '';
    if (isset ($_POST['bookx_genre_id']) && is_array ($_POST['bookx_genre_id'])) {
        $bookx_genre_ids = $_POST['bookx_genre_id'];
        foreach ($bookx_genre_ids as $genre) {
            $pInfo->genres_display .= (!empty ($pInfo->genres_display) ? ' | ' : '') . bookx_get_genre_description ($genre, (int) $_SESSION['languages_id']);
        }
    }

    $pInfo->authors_display = '';

    if (isset ($_POST['bookx_author_id']) && is_array ($_POST['bookx_author_id'])) {
        $bookx_author_ids = $_POST['bookx_author_id'];
        $bookx_author_type_ids = (isset ($_POST['bookx_author_type_id']) && is_array ($_POST['bookx_author_type_id']) ? $_POST['bookx_author_type_id'] : null);

        foreach ($bookx_author_ids as $key => $author_id) {

            $pInfo->authors_display .= (!empty ($pInfo->authors_display) ? ' | ' : '') . ($bookx_author_type_ids ? bookx_get_author_type_description ($bookx_author_type_ids[$key], (int) $_SESSION['languages_id']) . ': ' : '') . bookx_get_author_name ($author_id);
        }
    }
}
else {
    /**
     * @see https://bit.ly/2ERef7c
     *  
     * This code at the present moment is not being called anywere. 
     */
    $product = $db->Execute ('SELECT p.products_id, pd.language_id, pd.products_name,
                          pd.products_description, pd.products_url, p.products_quantity,
                          p.products_model, p.products_image, p.products_price, p.products_virtual,
                          p.products_weight, p.products_date_added, p.products_last_modified,
                          p.products_date_available, p.products_status, p.manufacturers_id,
                          p.products_quantity_order_min, p.products_quantity_order_units, p.products_priced_by_attribute,
                          p.product_is_free, p.product_is_call, p.products_quantity_mixed,
                          p.product_is_always_free_shipping, p.products_qty_box_status, p.products_quantity_order_max,
                          p.products_sort_order,
                          be.bookx_publisher_id, be.bookx_series_id, be.bookx_imprint_id,
                          be.bookx_binding_id, be.bookx_printing_id, be.bookx_condition_id, be.publishing_date, be.pages, be.volume, be.size,
                          CONCAT_WS("-", SUBSTRING(be.isbn,1,3), SUBSTRING(be.isbn,4,1), SUBSTRING(be.isbn,5,6), SUBSTRING(be.isbn,11,2), SUBSTRING(be.isbn,13,1)) AS isbn_display,
                          bp.publisher_name AS publisher, bi.imprint_name AS imprint
                          FROM ' . TABLE_PRODUCTS . ' p
                          LEFT JOIN ' . TABLE_PRODUCTS_DESCRIPTION . ' pd ON p.products_id = pd.products_id
                          LEFT JOIN ' . TABLE_PRODUCT_BOOKX_EXTRA . ' be ON be.products_id = p.products_id
                          LEFT JOIN ' . TABLE_PRODUCT_BOOKX_EXTRA_DESCRIPTION . ' bed ON bed.products_id = p.products_id AND bed.languages_id = pd.language_id
                          LEFT JOIN ' . TABLE_PRODUCT_BOOKX_PUBLISHERS . ' bp ON bp.bookx_publisher_id = be.bookx_publisher_id
                          LEFT JOIN ' . TABLE_PRODUCT_BOOKX_IMPRINTS . ' bi ON bi.bookx_imprint_id = be.bookx_imprint_id
                          WHERE p.products_id = "' . (int) $_GET['pID'] . '"');

    $pInfo = new objectInfo ($product->fields);
    $products_image_name = $pInfo->products_image;

    $authors = $db->Execute ('SELECT ba.author_name, batd.type_description
                             FROM ' . TABLE_PRODUCT_BOOKX_AUTHORS_TO_PRODUCTS . ' bpta
    						 LEFT JOIN ' . TABLE_PRODUCT_BOOKX_AUTHORS . ' ba ON ba.bookx_author_id = bpta.bookx_author_id
    						 LEFT JOIN ' . TABLE_PRODUCT_BOOKX_AUTHOR_TYPES_DESCRIPTION . ' batd ON bpta.bookx_author_type_id = batd.bookx_author_type_id AND batd.languages_id = "' . (int) $_SESSION['languages_id'] . '"
    						 WHERE bpta.products_id = "' . (int) $_GET['pID'] . '"
    						 ORDER BY ba.author_sort_order, ba.author_name');

    $pInfo->authors_display = '';

    while (!$authors->EOF) {
        $pInfo->authors_display .= (!empty ($pInfo->authors_display) ? ' | ' : '') . $authors->fields['type_description'] . ': ' . $authors->fields['author_name'];
        $authors->MoveNext ();
    }

    $genres = $db->Execute ('SELECT gd.genre_description
                           FROM ' . TABLE_PRODUCT_BOOKX_GENRES_TO_PRODUCTS . ' gtp
    					   LEFT JOIN ' . TABLE_PRODUCT_BOOKX_GENRES_DESCRIPTION . ' gd ON gtp.bookx_genre_id = gd.bookx_genre_id AND gd.languages_id = "' . $_SESSION['languages_id'] . '"
    					   WHERE gtp.products_id = "' . (int) $_GET['pID'] . '"
    					   ORDER BY gd.genre_description');

    $pInfo->genres_display = '';
    while (!$genres->EOF) {
        $pInfo->genres_display .= (!empty ($pInfo->genres_display) ? ' | ' : '') . $genres->fields['genre_description'];
        $genres->MoveNext ();
    }
}
/**
 * look for additional preview_info*.php files and include now 
 */
$incl_dir = @dir(DIR_FS_ADMIN . '/includes/modules/product_bookx');
while ($file = $incl_dir->read ()) {
    if ('preview_info_' == substr ($file, 0, 13)) {
        /**
         * Pulling the extra files, will bring the metatags form buttons update/cancel
         * Placing this var $preview will prevent that behaviour on preview_info_meta_tags
         */
        $preview = true;
        include_once DIR_FS_ADMIN . '/includes/modules/product_bookx/' . $file; // This should handle any extra values collected in collect_info*.php
    }
}
$incl_dir->close ();

/**
 * @todo see usability
 * @since version 1.0.1 adding a notifier to include files ?
 */
$zco_notifier->notify ('NOTIFY_BOOKX_ADMIN_PRODUCT_PREVIEW_INFO', $pInfo);

$form_action = (isset ($_GET['pID'])) ? 'update_product' : 'insert_product';
?>
<style>
    li.list-group-item {
    display: flex;
    padding: 0;
    line-height: 2.5rem;
}
strong.display_preview_label {
    width: 25%;
    background: #eee;
    padding-left: 1rem;
    margin-right: 1rem;
    border-right: 2px solid #ddd;
    
}
</style>
<div class="container">
<?php 
if (!isset ($_GET['read']) || ($_GET['read'] !== 'only')) {
    echo zen_draw_form ($form_action, FILENAME_PRODUCT, 'cPath=' . $cPath . (isset ($_GET['product_type']) ? '&product_type=' . $_GET['product_type'] : '') . (isset ($_GET['pID']) ? '&pID=' . $_GET['pID'] : '') . '&action=' . $form_action . (isset ($_GET['page']) ? '&page=' . $_GET['page'] : ''), 'post', 'enctype="multipart/form-data"');
    }

include_once DIR_FS_CATALOG_LANGUAGES . $_SESSION['language'] . '/product_bookx_info.php';
include_once DIR_FS_CATALOG_LANGUAGES . $_SESSION['language'] . '/extra_definitions/product_bookx.php';

for ($i = 0, $n = sizeof ($languages); $i < $n; $i++) {
    /**
     * @todo I can't find the situation where GET read AND only occurs
     */
    if (isset ($_GET['read']) && ($_GET['read'] == 'only')) {
        /*
        $pInfo->products_name = zen_get_products_name ($pInfo->products_id, $languages[$i]['id']);
        $pInfo->products_description = zen_get_products_description ($pInfo->products_id, $languages[$i]['id']);
        $pInfo->products_url = zen_get_products_url ($pInfo->products_id, $languages[$i]['id']);

        $pInfo->products_subtitle = bookx_get_products_subtitle ($pInfo->products_id, $languages[$i]['id']);
        $pInfo->series = bookx_get_series_name ($pInfo->bookx_series_id, $languages[$i]['id']);
        $pInfo->binding = bookx_get_binding_description ($pInfo->bookx_binding_id, $languages[$i]['id']);
        $pInfo->printing = bookx_get_printing_description ($pInfo->bookx_printing_id, $languages[$i]['id']);
        $pInfo->condition = bookx_get_condition_description ($pInfo->bookx_condition_id, $languages[$i]['id']);

        $bookx_extra_attributes = (!empty ($pInfo->pages) ? sprintf (LABEL_BOOKX_PAGES, $pInfo->pages) : '');
        $bookx_extra_attributes .= (!empty ($pInfo->binding) ? (!empty ($bookx_extra_attributes) ? ' | ' : '') . $pInfo->binding : '');
        $bookx_extra_attributes .= (!empty ($pInfo->printing) ? (!empty ($bookx_extra_attributes) ? ' | ' : '') . $pInfo->printing : '');
        $bookx_extra_attributes .= (!empty ($pInfo->size) ? (!empty ($bookx_extra_attributes) ? ' | ' : '') . $pInfo->size : '');
        $bookx_extra_attributes .= (!empty ($pInfo->condition) ? (!empty ($bookx_extra_attributes) ? ' | ' : '') . $pInfo->condition : '');
        */
        
    } else {
        /**
         * @todo check if this code is still need it, since nothing will happen here. 
         */
        $pInfo->products_name = zen_db_prepare_input ($products_name[$languages[$i]['id']]);
        $pInfo->products_description = zen_db_prepare_input ($products_description[$languages[$i]['id']]);
        $pInfo->products_url = zen_db_prepare_input ($products_url[$languages[$i]['id']]);
        $pInfo->products_subtitle = zen_db_prepare_input ($products_subtitle[$languages[$i]['id']]);


        $pInfo->series = bookx_get_series_name ($pInfo->bookx_series_id, $languages[$i]['id']);
        $pInfo->binding = bookx_get_binding_description ($pInfo->bookx_binding_id, $languages[$i]['id']);
        $pInfo->printing = bookx_get_printing_description ($pInfo->bookx_printing_id, $languages[$i]['id']);
        $pInfo->condition = bookx_get_condition_description ($pInfo->bookx_condition_id, $languages[$i]['id']);

        $bookx_extra_attributes = (!empty ($pInfo->pages) ? sprintf (LABEL_BOOKX_PAGES, $pInfo->pages) : '');
        $bookx_extra_attributes .= (!empty ($pInfo->binding) ? (!empty ($bookx_extra_attributes) ? ' | ' : '') . $pInfo->binding : '');
        $bookx_extra_attributes .= (!empty ($pInfo->printing) ? (!empty ($bookx_extra_attributes) ? ' | ' : '') . $pInfo->printing : '');
        $bookx_extra_attributes .= (!empty ($pInfo->size) ? (!empty ($bookx_extra_attributes) ? ' | ' : '') . $pInfo->size : '');
        $bookx_extra_attributes .= (!empty ($pInfo->condition) ? (!empty ($bookx_extra_attributes) ? ' | ' : '') . $pInfo->condition : '');
        
        $pInfo->genres = '';
        if (!empty ($bookx_genre_ids) && is_array ($bookx_genre_ids)) {
            $pInfo->genres = array();
            foreach ($bookx_genre_ids as $genre_id) {
                $pInfo->genres[] = bookx_get_genre_description ($genre_id, $languages[$i]['id']);
            }
        }

        $pInfo->authors = '';
        if (!empty($bookx_author_ids) && is_array($bookx_author_ids)) {
            $pInfo->authors = array();

            foreach ($bookx_author_ids as $key => $author_id) {
                /*
                 * @todo check this line
                 */
                $pInfo->authors[] = array(bookx_get_author_name($author_id),
                    bookx_get_author_type_description ($bookx_author_type_ids[$key], $languages[$i]['id'])
                );
            }
        }
        }

        if (isset($_GET['pID'])) {
        $specials_price = zen_get_products_special_price($_GET['pID']);
            if (zen_not_null($specials_price)) {
                $specials_price = $currencies->format($specials_price);
                $css_price = 'style="text-decoration: line-through;"';
            }
        }   
    ?>	
    
    <div class="row">
        <div class="panel panel-default">
            <!-- Default panel contents -->
            <div class="panel-heading">
                <div class="row">
                    <div class="col-sm-6 pageHeading">
                    <?php $lng_icon = DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image']; ?>
                    <?php echo zen_image ($lng_icon, $languages[$i]['name']) . zen_output_string_protected ($pInfo->products_name); ?>
                        <small><?php echo (!empty ($pInfo->volume) ? '' . $pInfo->volume : '') . (!empty ($pInfo->products_subtitle) ? ' &ndash; ' . $pInfo->products_subtitle : ''); ?>
                        </small>
                    </div>
                    <div class="col-sm-6 text-right pageHeading">
                    
                    <span <?php echo $css_price; ?>><?php echo $currencies->format($pInfo->products_price); ?></span>
                    <?php
                    if ($specials_price) { ?>
                        <span class="text-danger"><?php echo $specials_price; ?></span>
                    <?php }
                    /**
                     * If $_POST['ignore_family_discount'] is not set don't show, don't apply any discounts
                     */
                    if(!isset($_POST['ignore_family_discount']) && $objBookxFamily->family_discount > 0 ) { 
                        $_POST['ignore_family_discount'] = 'off';
                        ?>
                        <span class="text-info">
                            <?php echo "W/Fam Disc[".$objBookxFamily->family_discount."%]" .$currencies->format($objBookxFamily->applyFamilyDiscount()); ?></span>
                    <?php } else {
                        //set this for update
                        $_POST['ignore_family_discount'] = 'on';
                    }
                   
                    ?>
                     
                    </div>
                    <div class="clearfix"></div>
                        <div class="row">
                            <div class="center-block">
                                <?php
                                $display_warnings = function ($var, $condition, $message) {
                                    if ($var == $condition) {
                                        return '<p class="clearfix"><span class="text-warning pull-right">' . $message . '</span></p>';
                                    }
                                };
                                echo $display_warnings($pInfo->products_virtual, 1, TEXT_VIRTUAL_PREVIEW);
                                echo $display_warnings($pInfo->product_is_always_free_shipping, 1, TEXT_FREE_SHIPPING_PREVIEW);
                                echo $display_warnings($pInfo->products_priced_by_attribute, 1, TEXT_PRODUCTS_PRICED_BY_ATTRIBUTES_PREVIEW);
                                echo $display_warnings($pInfo->product_is_free, 1, TEXT_PRODUCTS_IS_FREE_PREVIEW);
                                echo $display_warnings($pInfo->product_is_call, 1, TEXT_PRODUCTS_IS_CALL_PREVIEW);
                                echo $display_warnings($pInfo->products_qty_box_status, 0, TEXT_PRODUCTS_QTY_BOX_STATUS_PREVIEW);
                                if(isset ($_GET['pID']) && $pInfo->products_priced_by_attribute == 1) {
                                    echo zen_get_products_display_price ($_GET['pID']);
                                }
                                ?> 
                            </div>
                        </div>
                    </div>
                </div>
                <div class="panel-body">
                    <?php
                    //auto replace with defined missing image
                    if ($_POST['products_image_manual'] != '') {
                        $products_image_name = $_POST['img_dir'] . $_POST['products_image_manual'];
                        $pInfo->products_name = $products_image_name;
                    }
                    if ($_POST['image_delete'] == 1 || $products_image_name == '' && PRODUCTS_IMAGE_NO_IMAGE_STATUS == '1') {
                        echo zen_image (DIR_WS_CATALOG_IMAGES . PRODUCTS_IMAGE_NO_IMAGE, $pInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'class="img-responsive pull-right"');
                    }
                    else {
                        echo zen_image (DIR_WS_CATALOG_IMAGES . $products_image_name, $pInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'class="img-responsive pull-right"');
                    }
                    ?>
                    <p><?php echo $pInfo->products_description; ?></p>
                </div>
            
            <?php
            /**
             * display lamba
             */
            $display = function ($label, $param) {
                return '<strong class="display_preview_label">' . $label . ' </strong>' . (!empty($param) ? '' . $param : '---');
            };
            ?>
                <!-- List group -->
                <ul class="list-group">
                    <li class="list-group-item"><?php echo $display(TABLE_HEADING_BOOKX_DATE_PUBLISHED, $pInfo->publishing_date); ?></li>
                    <li class="list-group-item"><?php echo $display(LABEL_AUTHORS, $pInfo->authors_display); ?></li>
                    <li class="list-group-item"><?php echo $display(LABEL_BOOKX_GENRE ,$pInfo->genres_display); ?></li>
                    <li class="list-group-item"><?php echo $display(LABEL_EXTRA_ATTRIBUTES, $bookx_extra_attributes); ?></li>
                    <li class="list-group-item"><?php echo $display(LABEL_BOOKX_ISBN, $pInfo->isbn_display);?></li>
                    <li class="list-group-item"><?php echo $display(LABEL_SERIES, $pInfo->series);?></li>
                    <li class="list-group-item"><?php echo $display(LABEL_PUBLISHER, $pInfo->publisher); ?></li>
                    <li class="list-group-item"><?php echo $display(LABEL_IMPRINT, $pInfo->imprint); ?></li>
                    <li class="list-group-item"><?php echo $display(LABEL_BOOKX_FAMILY, $pInfo->bookx_family); ?></li>
                </ul>
            </div>

        </div>
        <?php
        if ($pInfo->products_url) {
            ?>
            <div class="row"><?php echo sprintf (TEXT_PRODUCT_MORE_INFORMATION, $pInfo->products_url); ?></div>
            <?php
        }
        ?>
        <div class="row"><?php echo zen_draw_separator ('pixel_trans.gif', '1', '10'); ?></div>
        <?php
        if ($pInfo->products_date_available > date ('Y-m-d')) {
            ?>
            <div class="row"><?php echo sprintf (TEXT_PRODUCT_DATE_AVAILABLE, zen_date_long ($pInfo->products_date_available)); ?></div>
            <?php
        }
        else {
            ?>
            <div class="row"><?php echo sprintf (TEXT_PRODUCT_DATE_ADDED, zen_date_long ($pInfo->products_date_added)); ?></div>
            <?php
        }
        ?>
        <div class="row"><?php echo zen_draw_separator ('pixel_trans.gif', '1', '10'); ?></div>
        <?php
    }

    if (isset ($_GET['read']) && ($_GET['read'] == 'only')) {
        if (isset ($_GET['origin'])) {
            $pos_params = strpos ($_GET['origin'], '?', 0);
            if ($pos_params != false) {
                $back_url = substr ($_GET['origin'], 0, $pos_params);
                $back_url_params = substr ($_GET['origin'], $pos_params + 1);
            }
            else {
                $back_url = $_GET['origin'];
                $back_url_params = '';
            }
        }
        else {
            $back_url = FILENAME_CATEGORY_PRODUCT_LISTING;
            $back_url_params = 'cPath=' . $cPath . '&pID=' . $pInfo->products_id;
        }
        ?>
        <div class="row text-right">
            <a href="<?php echo zen_href_link ($back_url, $back_url_params . (isset ($_POST['search']) ? '&search=' . $_POST['search'] : '')); ?>" class="btn btn-default" role="button"><?php echo IMAGE_BACK; ?></a>
        </div>
            <?php
        }
        else {
            ?>
        <div class="row text-right">
            <?php
            /* Re-Post all POST'ed variables */
            foreach ($_POST as $key => $value) {
                if (!is_array ($_POST[$key])) {
                    echo zen_draw_hidden_field ($key, htmlspecialchars (stripslashes ($value), ENT_COMPAT, CHARSET, TRUE));
                }
                else {
                    foreach ($_POST[$key] as $array_key => $array_value) {
                        echo zen_draw_hidden_field ($key . '[' . $array_key . ']', htmlspecialchars (stripslashes ($array_value)));
                    }
                }
            }

            for ($i = 0, $n = sizeof ($languages); $i < $n; $i++) {
                echo zen_draw_hidden_field ('products_name[' . $languages[$i]['id'] . ']', htmlspecialchars (stripslashes ($products_name[$languages[$i]['id']]), ENT_COMPAT, CHARSET, TRUE));
                echo zen_draw_hidden_field ('products_description[' . $languages[$i]['id'] . ']', htmlspecialchars (stripslashes ($products_description[$languages[$i]['id']]), ENT_COMPAT, CHARSET, TRUE));
                echo zen_draw_hidden_field ('products_url[' . $languages[$i]['id'] . ']', htmlspecialchars (stripslashes ($products_url[$languages[$i]['id']]), ENT_COMPAT, CHARSET, TRUE));
                echo zen_draw_hidden_field ('products_subtitle[' . $languages[$i]['id'] . ']', htmlspecialchars (stripslashes ($products_subtitle[$languages[$i]['id']]), ENT_COMPAT, CHARSET, TRUE));
            }
            echo zen_draw_hidden_field ('products_image', stripslashes ($products_image_name));
            echo ( (isset ($_GET['search']) && !empty ($_GET['search'])) ? zen_draw_hidden_field ('search', $_GET['search']) : '') . ( (isset ($_POST['search']) && !empty ($_POST['search']) && empty ($_GET['search'])) ? zen_draw_hidden_field ('search', $_POST['search']) : '');
            echo zen_image_submit ('button_back.gif', IMAGE_BACK, 'name="edit" class="btn btn-default"') . '&nbsp;&nbsp;';
            ?>
            <?php
            if (isset ($_GET['pID'])) {
                ?>
                <button type="submit" class="btn btn-primary"><?php echo IMAGE_UPDATE; ?></button>
                <?php
            }
            else {
                ?>
                <button type="submit" class="btn btn-primary"><?php echo IMAGE_INSERT; ?></button>
                <?php
            }
            ?>
            <a href="<?php echo zen_href_link (FILENAME_CATEGORY_PRODUCT_LISTING, 'cPath=' . $cPath . (isset ($_GET['pID']) ? '&pID=' . $_GET['pID'] : '') . (isset ($_GET['page']) ? '&page=' . $_GET['page'] : '') . (isset ($_GET['search']) ? '&search=' . $_GET['search'] : '')); ?>" class="btn btn-default" role="button"><?php echo IMAGE_CANCEL; ?></a>
        <?php
        if (!(isset ($_GET['read']) && ($_GET['read'] === 'only'))) {
            echo '</form>';
        }
        ?>
        </div>
    <?php
}
?>
</div>