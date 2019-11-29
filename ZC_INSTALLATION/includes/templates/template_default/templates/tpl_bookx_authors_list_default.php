<?php
/**
 * This file is part of the ZenCart add-on Book X which
 * introduces a new product type for books to the Zen Cart
 * shop system. Tested for compatibility on ZC v. 1.5
 *
 * For latest version and support visit:
 * https://sourceforge.net/p/zencartbookx
 *
 * @package templateSystem
 * @author  Philou
 * @copyright Copyright 2013
 * @copyright Portions Copyright 2003-2011 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.gnu.org/licenses/gpl.txt GNU General Public License V2.0
 *
 * @version BookX V 0.9.4-revision8 BETA
 * @version $Id: [ZC INSTALLATION]/includes/templates/[CURRENT_TEMPLATE]/templates/tpl_bookx_authors_list_default.php 2016-02-02 philou $
 */

/**
 * Loaded automatically by index.php?main_page=bookx_authors_list.
 */
?>

<div id="bookxAuthorListing">

<?php 
if ($bookx_authors_listing_split->number_of_rows > 0 && 
    $bookx_authors_listing_split->number_of_pages > 1 && 
    ((PREV_NEXT_BAR_LOCATION == '1') || (PREV_NEXT_BAR_LOCATION == '3'))) { ?>
	<div id="authorsListingTopNumber" class="navSplitPagesResult back"><?php echo $bookx_authors_listing_split->display_count(TEXT_DISPLAY_NUMBER_OF_AUTHORS); ?></div>
	<div id="authorsListingListingTopLinks" class="navSplitPagesLinks forward"><?php echo TEXT_RESULT_PAGE . ' ' . $bookx_authors_listing_split->display_links(MAX_DISPLAY_PAGE_LINKS, zen_get_all_get_params(array('page', 'info', 'x', 'y', 'main_page'))); ?></div>
	<br class="clearBoth" />
<?php 
}
if (BOOKX_AUTHOR_LISTING_SHOW_ONLY_STOCKED) { ?>
    <script type="text/javascript">
    function handleInStockOnlyCheckbox() {
        var n = window.location.href.indexOf('&la=');
        var listOutOfStock = authorsListOnlyStockedCheckbox.checked;
        var newGetParameter = (listOutOfStock ? '&la=true' : '');
        if (0 > n) {
            window.location.href = window.location.href + newGetParameter;
        } else {
            window.location.href = window.location.href.replace('&la=true', newGetParameter);
        }
    }
    </script>
    <div id="authorsListOnlyStockedCheckboxContainer">
        <label><input id="authorsListOnlyStockedCheckbox" type="checkbox" <?php echo ( isset($_GET['la']) && $_GET['la'] ? 'checked' : ''); ?> onClick="handleInStockOnlyCheckbox()" /> <?php echo TEXT_BOOKX_AUTHOR_LIST_STOCKCHECKBOX_LABEL; ?></label>
    </div>
<?php } ?>

<h1 id="authorListHeading"><?php echo TEXT_BOOKX_AUTHOR_LIST_TITLE; ?></h1>

<?php echo $bookx_alphafilter; ?>

<div id="bookxAuthorListingTable" class="bookxFilterListAll">
    <?php
    foreach ($bookx_authors_listing_split_array as $author) {
        
        if ($display_image == true) {
            $image = (!empty($author['author_image'])) ? zen_image($author['author_image'], $author['author_name'], BOOKX_AUTHOR_LISTING_IMAGE_MAX_WIDTH, BOOKX_AUTHOR_LISTING_IMAGE_MAX_HEIGHT, 'class="bookxAllListingImage"') : '<div class="placeHolder"></div>';
        }
        echo '<div class="row clearfix">' . $image;
        echo '<h3 class="bookxAllListingInfo">' . $author['author_name'] . '' . (!empty($author['author_types']) ? ' <span class="bookxAuthorType">' . $author['author_types'] . '<span>' : '') . '</h3>'
        . (!empty($author['author_description']) ? '<div class="bookxAllListingDescription">' . zen_html_entity_decode(bookx_truncate_paragraph($author['author_description'], BOOKX_TRUNCATE_DESCRIPTION_LENGHT, TEXT_BOOKX_MORE_PRODUCT_INFO , true)) . '</div>' : '')
        . (!empty($author['author_url']) ? '<div class="bookxAuthorUrl"><a href="http://' . $author['author_url'] . '" target="_blank" '.BOOKX_NOFOLLOW_LINK.'>' . BOOKX_URL_LINK_TEXT_AUTHOR . '</a></div>' : '')
        . ' <a href="' . zen_href_link(FILENAME_DEFAULT, '&typefilter=bookx&bookx_author_id=' . $author['bookx_author_id']) . '" class="bookx_searchlink">' . sprintf(TEXT_BOOKX_LIST_PRODUCTS_BY_AUTHOR, $author['author_name']) . '</a>';
        echo '</div>';
    }
    ?>
</div>

<?php if ( ($bookx_authors_listing_split->number_of_rows > 0) && $bookx_authors_listing_split->number_of_pages > 1 && ((PREV_NEXT_BAR_LOCATION == '2') || (PREV_NEXT_BAR_LOCATION == '3')) ) {
?>
<div id="authorsListingBottomNumber" class="navSplitPagesResult back"><?php echo $bookx_authors_listing_split->display_count(TEXT_DISPLAY_NUMBER_OF_AUTHORS); ?></div>
<div  id="authorsListingListingBottomLinks" class="navSplitPagesLinks forward"><?php echo TEXT_RESULT_PAGE . ' ' . $bookx_authors_listing_split->display_links(MAX_DISPLAY_PAGE_LINKS, zen_get_all_get_params(array('page', 'info', 'x', 'y'))); ?></div>
<br class="clearBoth" />
<?php
  }
?>
</div>