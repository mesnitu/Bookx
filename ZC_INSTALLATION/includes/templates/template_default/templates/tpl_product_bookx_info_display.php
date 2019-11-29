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
 * @version $Id: [ZC INSTALLATION]/includes/templates/[CURRENT_TEMPLATE]/templates/tpl_product_bookx_info_display.php 2016-02-02 philou $
 */

/**
 *
 * Loaded automatically by index.php?main_page=document_product_info.<br />
 * Displays template according to "document-product" product-type needs
 *
 */

// prepare Bookx HTML
/**
 * BookX $flag_variables can be 0 = don't show entry, 1 = only show entry when not empty, 2 = always show entry
 *
 * Available BookX flags in this file are:
 *   $flag_show_product_bookx_info_authors
 *   $flag_show_product_bookx_info_authors_as_link
 *
 *   $flag_show_product_bookx_info_authors_image
 *   $flag_show_product_bookx_info_authors_url
 *   $flag_show_product_bookx_info_authors_description
 *   $flag_show_product_bookx_info_authors_related_products
 *   $flag_show_product_bookx_info_authors_team_related_products // NEW
 *   $flag_show_product_bookx_info_author_type
 *   $flag_show_product_bookx_info_author_type_image
 *   $flag_order_product_bookx_info_authors_by
 *
 *   $flag_show_product_bookx_info_binding
 *   $flag_show_product_bookx_info_condition
 *
 *   $flag_show_product_bookx_info_genres
 *   $flag_show_product_bookx_info_genres_as_link
 *   $flag_show_product_bookx_info_genre_images
 *   $flag_order_product_bookx_info_genres_by
 *
 *   $flag_show_product_bookx_info_imprint
 *   $flag_show_product_bookx_info_imprint_as_link
 *   $flag_show_product_bookx_info_imprint_image
 *   $flag_show_product_bookx_info_imprint_description
 *
 *   $flag_show_product_bookx_info_isbn
 *
 *   $flag_show_product_bookx_info_pages
 *   $flag_show_product_bookx_info_printing
 *   $flag_show_product_bookx_info_publish_date
 *   $flag_show_product_bookx_info_publisher
 *   $flag_show_product_bookx_info_publisher_as_link
 *   $flag_show_product_bookx_info_publisher_image
 *   $flag_show_product_bookx_info_publisher_url
 *   $flag_show_product_bookx_info_publisher_description
 *   $flag_show_product_bookx_info_series
 *   $flag_show_product_bookx_info_series_as_link
 *   $flag_show_product_bookx_info_series_image
 *   $flag_show_product_bookx_info_series_description
 *   $flag_show_product_bookx_info_size
 *   $flag_show_product_bookx_info_subtitle
 *   $flag_show_product_bookx_info_volume
 *
 * Available BookX variables in this file are
 *
 * 	 $products_authors (array)
 * 	 $products_genres (array)
 *
 * 	 $products_subtitle
 * 	 $products_binding
 * 	 $products_printing
 * 	 $products_condition
 * 	 $products_pages
 * 	 $products_volume
 * 	 $products_size
 *   $products_isbn
 * 	 $products_publishing_date
 *
 * 	 $products_publisher_name
 * 	 $products_publisher_image
 * 	 $products_publisher_description
 * 	 $products_publisher_url
 * 	 $products_publisher_searchlink
 * 	 $products_publisher_image_searchlink
 *
 * 	 $products_series_name
 * 	 $products_series_searchlink
 * 	 $products_series_image
 * 	 $products_series_description
 * 	 $products_series_image_searchlink
 *
 * 	 $products_imprint_name
 * 	 $products_imprint_searchlink
 * 	 $products_imprint_image
 * 	 $products_imprint_description
 * 	 $products_imprint_image_searchlink
 *
 */

$zco_notifier->notify('NOTIFY_TPL_PRODUCT_BOOKX_INFO_DISPLAY_BEGIN', array('products_id' => $products_id_current));

//*** all authors
$authors_related_products_html = '';
$authors_related_products_team_html = '';
$authors_short_html = '<!-- bof bookx authors short -->
		<div class="bookxAuthorsShort">';

$authors_detail_html = '<!-- bof bookx authors detail -->
 		  <div class="bookxAuthorsDetail">';

if (!empty($products_authors)) {
	$authors_detail_html .= '<h3 id="bookxAuthorSectionHeading">' . LABEL_AUTHORS . '</h3>';
}

foreach ($products_authors as $author) {
	$single_author_detail_html = '';
	//*** author name
	if ($flag_show_product_bookx_info_authors &&
			(!empty($author['name']) || 2 == $flag_show_product_bookx_info_authors)) {

		$author_name_label = LABEL_AUTHOR;
		if ($flag_show_product_bookx_info_author_type &&
				(!empty($author['type']) || 2 == $flag_show_product_bookx_info_author_type)) {
			$author_name_label = $author['type'] . ': ';
		}

		if ($flag_show_product_bookx_info_author_type_image &&
				(!empty($author['type_image']) || 2 == $flag_show_product_bookx_info_author_type_image)) {
			$author_name_label = zen_image($author['type_image'], $author['type'] . ': ', BOOKX_AUTHOR_IMAGE_MAX_WIDTH, BOOKX_AUTHOR_IMAGE_MAX_HEIGHT);
		}


		//$authors_detail_html .= '<div class="bookxAuthor"><span class="bookxLabel">' . $author_name_label . '</span>';
		$authors_short_html .= '<div>'
            . '<span class="bookxLabel">' . $author_name_label . '</span>';

		if ($flag_show_product_bookx_info_authors_as_link && !empty($author['searchlink'])) {
			//$authors_detail_html .= ' ' . $author['searchlink'];
			$authors_short_html .= ' ' . $author['searchlink'];
		} else {
			//$authors_detail_html .= ' ' . $author['name'];
			$authors_short_html .= ' ' . $author['name'];
		}
		//$authors_detail_html .= '</div>';
		$authors_short_html .= '</div>';
	}

	//*** author image
	if ($flag_show_product_bookx_info_authors_image &&
			(!empty($author['image']) || 2 == $flag_show_product_bookx_info_authors_image)) {
        
		$single_author_detail_html .= '<div class="bookxAuthorImage">' .  zen_image($author['image'], $author['name'], BOOKX_AUTHOR_IMAGE_MAX_WIDTH, BOOKX_AUTHOR_IMAGE_MAX_HEIGHT);
		if (!empty($author['image_copyright'])) {
			$single_author_detail_html .= '<div class="bookxAuthorImageCopyright">' .  $author['image_copyright'] . '</div>';
		}
		$single_author_detail_html .= '</div>';
	}

	//*** author description
	if ($flag_show_product_bookx_info_authors_description &&
			(!empty($author['description']) || 2 == $flag_show_product_bookx_info_authors_description)) {

		$single_author_detail_html .= '<div class="bookxAuthorDescription">'
            . '<span class="bookxLabel">' . sprintf(LABEL_AUTHOR_DESCRIPTION, $author['name']) . '</span>' 
            . '<p>' . bookx_truncate_paragraph($author['description'], BOOKX_TRUNCATE_DESCRIPTION_LENGHT) . ' ' . $author['description_link'] 
            .'</p></div>';
	}
    
	if ( $flag_show_product_bookx_info_authors_related_products &&
        (!empty($author['related_products']) || 2 == $flag_show_product_bookx_info_authors_related_products) ) {
        $single_author_related_products_html = '<div class="bookxAuthorRelatedProducts">';
        
        $single_author_related_products_html .= '<h4 class="bookxAuthorRelatedProductsHeading">' . sprintf(HEADING_AUTHOR_RELATED_PRODUCTS, $author['name']) . '</h4>';

        $i = 0;
     
        foreach ( $author['related_products'] as $related_product ) {
            $i++;
            $single_author_related_products_html .= '<div class="bookxWrapAuthorRelatedBooks ' . (!empty($related_product['bookx_product_status']) ? 'status_' . $related_product['bookx_product_status'] : '') . '">';
            
            $single_author_related_products_html .= '<h5><a href="' . $related_product['products_link'] . '">' . $related_product['products_name'] . '</a>' . (!empty($related_product['volume']) ? '<small>Vol ' . $related_product['volume'] .'</small>': '');
            $single_author_related_products_html .= (!empty($related_product['products_subtitle']) ? ' &ndash;<small>' . $related_product['products_subtitle']. '</small>' : '');
            $single_author_related_products_html .= '</h5>';
            $single_author_related_products_html .= (!empty($related_product['bookx_product_status']) ? '<span class="labelBookStatus">' . constant('BOOKX_PRODUCT_STATUS_' . strtoupper($related_product['bookx_product_status'])) . '</span>' : '');
            $single_author_related_products_html .= '<div class="bookxAuthoRelatedBooks">'
                    . '<div class="related_book_image"><a href="' . $related_product['products_link'] . '">' . zen_image(DIR_WS_IMAGES . $related_product['author_related_book_image'], $related_product['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT)  . '</a></div>'
                    . '<div class="related_book_price">' . zen_get_products_display_price($related_product['products_id']) .'</div>'
                    . '</div>'
                . ( 1 < $author['related_books_as_author_type_count'] ? '<div class="booxAuthorRelatedType">' . (!empty($related_product['author_type_name']) ? ' (' . $related_product['author_type_name'] . ')' : '') . '</div>' : '')
                . '</div>';
            if ( $i == BOOKX_BOOKINFO_RELATED_AUTHOR_BOOKS_LIMIT ) {
                break;
            }
        }

        $single_author_related_products_html .= '</div>';
        ///**** this places the related products in the same div as the author. If not desired, remove  following line
        $single_author_detail_html .= $single_author_related_products_html;
    }
    
	//*** author url
	if ($flag_show_product_bookx_info_authors_url &&
			(!empty($author['url']) || 2 == $flag_show_product_bookx_info_authors_url)) {

		$single_author_detail_html .= '<p class="bookxAuthorUrl">' . sprintf(TEXT_AUTHOR_URL, $author['url']) . '</p>' ;
		// (30 < strlen($authors->fields['author_url']) ? substr($authors->fields['author_url'], 0, strpos($authors->fields['author_url'], '/', 7)) : $authors->fields['author_url'])
	}


	if (!empty($single_author_detail_html)) {
		$authors_detail_html .= '<div class="bookxSingleAuthor">' . $single_author_detail_html . ''
            . '</div><!-- eof single author -->';
	}
}

if (!empty($authors_related_products_html)) {
	$authors_related_products_html = '<!-- bof bookx related products --><div id="bookxRelatedProducts">' . $authors_related_products_html . '</div><!-- eof bookx related products-->';
}

$authors_detail_html .= '</div><!-- eof bookx authors detail -->';
$authors_short_html .= '</div><!-- eof bookx authors short -->';

if ($flag_show_product_bookx_info_authors_team_related_products && !empty($related_products_by_author_team) ) {
	$authors_related_products_team_html = '<!-- bof bookx related products team --><div id="bookxRelatedProductsTeam">';
	$authors_related_products_team_html .= '<h3 class="bookxTeamRelatedProductsHeading">' . sprintf(HEADING_AUTHOR_RELATED_PRODUCTS, $team_names_display) . '</h3>';
   
	foreach ($related_products_by_author_team as $related_team_product) {
        $authors_related_products_team_html .= '<!-- bof related product --><div class="bookxWrapRelatedProductTeam'
            . (!empty($related_team_product['bookx_product_status']) ? ' status_' . $related_team_product['bookx_product_status'] : '') . '">'
            . '<h5><a href="' . $related_team_product['products_link'] . '">' . $related_team_product['products_name']
            . (!empty($related_team_product['volume']) ? ' ' . $related_team_product['volume'] : '')
            . (!empty($related_team_product['products_subtitle']) ? ' &ndash; ' . $related_team_product['products_subtitle'] : '')
            . '</a></h5>'
            . (!empty($related_team_product['bookx_product_status']) ? '<span class="labelBookStatus">' . constant('BOOKX_PRODUCT_STATUS_' . strtoupper($related_team_product['bookx_product_status'])) . '</span>' : '')
            . '<div class="bookxAuthoRelatedBooks">'
            . '<div class="related_book_image"><a href="' . $related_team_product['products_link'] . '">' . zen_image(DIR_WS_IMAGES . $related_team_product['author_related_book_image'], $related_team_product['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '</a></div>'
            . '<div class="related_book_price">' . zen_get_products_display_price($related_team_product['products_id']) . '</div>'
            . ( 1 < $author['related_books_as_author_type_count'] ? '<div class="booxAuthorRelatedType">' . (!empty($related_team_product['author_type_name']) ? ' (' . $related_team_product['author_type_name'] . ')' : '') . '</div>' : '')
            . '</div></div><!-- eof related product -->';
    }
    $authors_related_products_team_html .= '</div><!-- eof bookx related products team -->';
}


//*** publisher name
$publisher_detail_html = '<!-- bof bookx publisher -->
			<div id="bookxPublisher">';
$publisher_short_html = '';
if ($flag_show_product_bookx_info_publisher &&
		(!empty($products_publisher_name) || 2 == $flag_show_product_bookx_info_publisher)) {

	$publisher_short_html .= '<div class="bookxPublisherName"><span class="bookxLabel">' . LABEL_PUBLISHER . '</span>';
    $publisher_detail_html .= '<h5 class="bookxPublisherName"><span class="bookxLabel">' . LABEL_PUBLISHER . '</span>';
	if ($flag_show_product_bookx_info_publisher_as_link && !empty($products_publisher_searchlink)) {
		$publisher_short_html .= $products_publisher_searchlink;
	} else {
		$publisher_short_html .= $products_publisher_name;
	}
    //$publisher_short_html .= '</div>';
	$publisher_detail_html .= '</h5>';
	
}

$publisher_short_html .= '</div>';

//*** publisher image
if ($flag_show_product_bookx_info_publisher_image &&
    (!empty($products_publisher_image) || 2 == $flag_show_product_bookx_info_publisher_image)) {

    $publisher_detail_html .= '<div class="bookxPublisherImage">';
    if ($flag_show_product_bookx_info_publisher_as_link && !empty($products_publisher_image_searchlink)) {
        if (!($flag_show_product_bookx_info_publisher && $flag_show_product_bookx_info_publisher_as_link && $products_publisher_image_searchlink == $products_publisher_searchlink)) {
            /* if no image assigned, then only show $products_publisher_image_searchlink if $products_publisher_searchlink not shown above
             * (if no image assigned, then $products_publisher_image_searchlink shows the publisher name, not an <IMG> tag,
             * and the name would therefore be displayed twice
             */
            $publisher_detail_html .= $products_publisher_image_searchlink;
        }
    } else {
        $publisher_detail_html .= zen_image($products_publisher_image, $products_publisher_name, BOOKX_ICONS_MAX_WIDTH, BOOKX_ICONS_MAX_HEIGHT);
    }
    $publisher_detail_html .= '</div>';
}

//*** publisher description
if ($flag_show_product_bookx_info_publisher_description &&
    (!empty($products_publisher_description) || 2 == $flag_show_product_bookx_info_publisher_description)) {
    $flag_publisher_has_detailed_info = true;
    $publisher_detail_html .= '<p class="bookxPublisherDescription"><span class="bookxLabel">' . LABEL_PUBLISHER_DESCRIPTION . '</span>' . $products_publisher_description . '</p>';
}

//*** publisher url
if ($flag_show_product_bookx_info_publisher_url &&
    (!empty($products_publisher_url) || 2 == $flag_show_product_bookx_info_publisher_url)) {

    $publisher_detail_html .= '<p class="bookxPublisherUrl">' . sprintf(TEXT_PUBLISHER_URL, $products_publisher_url) . '</p>';
}

if ($flag_publisher_has_detailed_info) {
    $publisher_detail_html .= '</div>
				<!-- eof bookx publisher -->';
} else {
    $publisher_detail_html = '<!-- bookx publisher description and URL empty -->';
}

//*** imprint info
$imprint_detail_html = '<!-- bof bookx imprint -->
			<div id="bookxImprint">';
$imprint_short_html = '<div id="bookxImprintShort">';

//*** imprint image
if ($flag_show_product_bookx_info_imprint_image &&
    (!empty($products_imprint_image) || 2 == $flag_show_product_bookx_info_imprint_image)) {

    $imprint_detail_html .= '<div class="bookxImprintImage">';
    if ($flag_show_product_bookx_info_imprint_as_link && !empty($products_imprint_image_searchlink)) {
        if (!($flag_show_product_bookx_info_imprint && $flag_show_product_bookx_info_imprint_as_link && $products_imprint_image_searchlink == $products_imprint_searchlink)) {
            //** See explanation of if condition above (publisher)
            $imprint_detail_html .= $products_imprint_image_searchlink;
        }
    } else {
        $imprint_detail_html .= zen_image($products_imprint_image, $products_imprint_name, BOOKX_ICONS_MAX_WIDTH, BOOKX_ICONS_MAX_HEIGHT);
    }
    $imprint_short_html = $imprint_detail_html;
    $imprint_detail_html .= '</div>';
} elseif ($flag_show_product_bookx_info_imprint &&
    (!empty($products_imprint_name) || 2 == $flag_show_product_bookx_info_imprint)) {
    //*** imprint name
    $imprint_detail_html .= '<h5 class="bookxImprintName"><span class="bookxLabel">' . LABEL_IMPRINT . '</span>';
    $imprint_short_html .= '<div class="bookxImprintName"><span class="bookxLabel">' . LABEL_IMPRINT . '</span>';
    if ($flag_show_product_bookx_info_imprint_as_link && !empty($products_imprint_searchlink)) {
        $imprint_detail_html .= $products_imprint_searchlink;
        $imprint_short_html .= $products_imprint_searchlink;
    } else {
        $imprint_detail_html .= $products_imprint_name;
        $imprint_short_html .= $products_imprint_name;
    }
    $imprint_detail_html .= '</div>';
    $imprint_short_html .= '</h5>';
}

//$imprint_short_html = $imprint_detail_html . '</div>';
//*** imprint description
if ($flag_show_product_bookx_info_imprint_description &&
    (!empty($products_imprint_description) || 2 == $flag_show_product_bookx_info_imprint_description)) {

    $imprint_detail_html .= '<p id="bookxImprintDescription"><span class="bookxLabel">' . LABEL_IMPRINT_DESCRIPTION . '</span>' . $products_imprint_description . '</p>';
    $imprint_detail_html .= '</div>
				<!-- eof bookx imprint -->';
} else {
    $imprint_detail_html = '<!-- bookx imprint description empty -->';
}

//*** publishing date
if ($flag_show_product_bookx_info_publish_date &&
    (!empty($products_publishing_date) || 2 == $flag_show_product_bookx_info_publish_date)) {

    $publishing_date_html = '<div id="bookxPublishingDate"><span class="bookxLabel">' . LABEL_BOOKX_PUBLISHING_DATE . '</span>' . $products_publishing_date . '</div>';
} else {
    $publishing_date_html = '';
}

//*** series
$series_detail_html = '<!-- bof bookx series -->
			<div id="bookxSeries">';
$series_detail_short = '';
//*** series name
if ($flag_show_product_bookx_info_series &&
    (!empty($products_series_name) || 2 == $flag_show_product_bookx_info_series)) {
    $series_detail_short .= '<div class="bookxSeriesName"><span class="bookxLabel">' . LABEL_SERIES . '</span>';
    $series_detail_html .= '<h5 class="bookxSeriesName"><span class="bookxLabel">' . LABEL_SERIES . '</span>';
    if ($flag_show_product_bookx_info_series_as_link && !empty($products_series_searchlink)) {
        $series_detail_html .= $products_series_searchlink;
        $series_detail_short .= $products_series_searchlink;
    } else {
        $series_detail_html .= $products_series_name;
        $series_detail_short .= $products_series_name;
    }
    $series_detail_html .= '</h5>';
    $series_detail_short .= '</div>';
}

//*** series image
if ($flag_show_product_bookx_info_series_image &&
    (!empty($products_series_image) || 2 == $flag_show_product_bookx_info_series_image)) {

    $series_detail_html .= '<div class="bookxSeriesImage">';
    if ($flag_show_product_bookx_info_series_as_link && !empty($products_series_image_searchlink)) {
        if (!($flag_show_product_bookx_info_series && $flag_show_product_bookx_info_series_as_link && $products_series_image_searchlink == $products_series_searchlink)) {
            //** See explanation of if condition above (publisher)
            $series_detail_html .= $products_series_image_searchlink;
        }
    } else {
        $series_detail_html .= zen_image($products_series_image, $products_series_name, BOOKX_ICONS_MAX_WIDTH, BOOKX_ICONS_MAX_HEIGHT);
    }
    $series_detail_html .= '</div>';
}

//*** series description
if ($flag_show_product_bookx_info_series_description &&
    (!empty($products_series_description) || 2 == $flag_show_product_bookx_info_series_description)) {

    $series_detail_html .= '<p class="bookxSeriesDescription"><span class="bookxLabel">' . LABEL_SERIES_DESCRIPTION . '</span>' . $products_series_description . '</p>';
}

$series_detail_html .= '</div>
 			<!-- eof bookx series -->';

$bookx_extra_attributes_html = '';
$bookx_extra_attributes = array();
if ($flag_show_product_bookx_info_pages && (!empty($products_pages) || 2 == $flag_show_product_bookx_info_pages))
    $bookx_extra_attributes[] = sprintf(LABEL_BOOKX_PAGES, $products_pages);
if ($flag_show_product_bookx_info_binding && (!empty($products_binding) || 2 == $flag_show_product_bookx_info_binding))
    $bookx_extra_attributes[] = $products_binding;
if ($flag_show_product_bookx_info_printing && (!empty($products_printing) || 2 == $flag_show_product_bookx_info_printing))
    $bookx_extra_attributes[] = $products_printing;
if ($flag_show_product_bookx_info_size && (!empty($products_size) || 2 == $flag_show_product_bookx_info_size))
    $bookx_extra_attributes[] = $products_size;

if (0 < count($bookx_extra_attributes)) {
    $bookx_extra_attributes_html .= '<div id="bookxExtraAttributes">' . implode(' | ', $bookx_extra_attributes) . '</div>';
}


$products_condition_html = '';
if ($flag_show_product_bookx_info_condition &&
    (!empty($products_condition) || 2 == $flag_show_product_bookx_info_condition)) {
    $products_condition_html = '<div class="bookxCondition">' . $products_condition . '</div>';
}

//*** genres
$genres_html = '<!-- bof bookx genres --><div class="bookxGenres">';
$genre_temp_html = '';
$first_genre = true;

foreach ($products_genres as $genre) {
    if (!$first_genre) {
        $genre_temp_html .= BOOKX_GENRE_SEPARATOR;
    } else {
        $first_genre = false;
    }
    $genre_temp_html .= '<!-- bof single genre --><div class="bookxSingleGenre">';
    //*** genre image
    if ($flag_show_product_bookx_info_genre_images &&
        (!empty($genre['image']) || 2 == $flag_show_product_bookx_info_genre_images)) {

        $genre_temp_html .= '<span class="bookxGenreImage">';

        if ($flag_show_product_bookx_info_genres_as_link && !empty($genre['image_searchlink'])) {
            if (!($flag_show_product_bookx_info_genre && $flag_show_product_bookx_info_genres_as_link && $genre['image_searchlink'] == $genre['searchlink'])) {
                $genre_temp_html .= $genre['image_searchlink'];
            }
        } else {
            $genre_temp_html .= zen_image($genre['image'], $genre['name'], BOOKX_ICONS_MAX_WIDTH, BOOKX_ICONS_MAX_HEIGHT);
        }
        $genre_temp_html .= '</span>';
    }

    //*** genre name
    if ($flag_show_product_bookx_info_genres &&
        (!empty($genre['name']) || 2 == $flag_show_product_bookx_info_genres)) {

        $genre_temp_html .= '<span class="bookxGenreName">';

        if ($flag_show_product_bookx_info_genres_as_link && !empty($genre['searchlink'])) {
            $genre_temp_html .= $genre['searchlink'];
        } else {
            $genre_temp_html .= $genre['name'];
        }
        $genre_temp_html .= '</span>';
    }

    $genre_temp_html .= '</div><!-- eof single genre -->';
}
if (!empty($genre_temp_html)) {
    $genre_temp_html = '<span class="bookxLabel">' . LABEL_BOOKX_GENRE . ':</span> ' . $genre_temp_html;
}
$genres_html .= $genre_temp_html . '</div><!-- eof bookx genres -->';
?>

<div class="centerColumn" id="docProductBookxDisplay">

<!--bof Form start-->
<?php echo zen_draw_form('cart_quantity', zen_href_link(zen_get_info_page($_GET['products_id']), zen_get_all_get_params(array('action')) . 'action=add_product', $request_type), 'post', 'enctype="multipart/form-data"') . "\n"; ?>
<!--eof Form start-->

<?php if ($messageStack->size('product_info') > 0) {
    echo $messageStack->output('product_info');
}
?>

<!--bof Category Icon -->
<?php if ($module_show_categories != 0) {
/**
 * display the category icons
 */
require($template->get_template_dir('/tpl_modules_category_icon_display.php',DIR_WS_TEMPLATE, $current_page_base,'templates'). '/tpl_modules_category_icon_display.php'); ?>
<?php } ?>
<!--eof Category Icon -->

<!--bof Prev/Next top position -->
<?php 
if (PRODUCT_INFO_PREVIOUS_NEXT == 1 or PRODUCT_INFO_PREVIOUS_NEXT == 3) {

    /**
     * display the product previous/next helper
     */
    require($template->get_template_dir('/tpl_products_next_previous.php', DIR_WS_TEMPLATE, $current_page_base, 'templates') . '/tpl_products_next_previous.php');
    
} ?>
<!--eof Prev/Next top position-->

<div id="prod-info-top">
<!--bof Product Name-->
<h1 id="productName" class="docProduct">
    <?php
    echo '<span class="bookxTitle">' . $products_name . '</span>'
    . (($flag_show_product_bookx_info_volume && !empty($products_volume)) ? " <span class='bookxProdVolume'>" . sprintf(LABEL_BOOKX_VOLUME, $products_volume) . "</span>" : '')
    . (($flag_show_product_bookx_info_volume && !empty($products_subtitle)) ? " - <span class='bookxProdSubtitle'>$products_subtitle</span>" : '');
    ?>
</h1>
<!--eof Product Name-->

<div id="pinfo-left" class="group">
<!--bof Main Product Image -->
<?php
if (zen_not_null($products_image)) {
    /**
     * display the main product image
     */
    require($template->get_template_dir('/tpl_modules_main_product_image.php', DIR_WS_TEMPLATE, $current_page_base, 'templates') . '/tpl_modules_main_product_image.php');
}
?>
<!--eof Main Product Image-->
<!--bof Additional Product Images -->
<?php
/**
 * display the products additional images
 */
  require($template->get_template_dir('/tpl_modules_additional_images.php',DIR_WS_TEMPLATE, $current_page_base,'templates'). '/tpl_modules_additional_images.php'); ?>
<!--eof Additional Product Images -->
</div>
<div id="pinfo-right" class="group grids">
    <!--bof Product Price block -->
<!--bof BookX Extra data items -->
<?php 
$zco_notifier->notify('NOTIFY_TPL_PRODUCT_BOOKX_INFO_DISPLAY_DOCPRODUCT_BEGIN');

if (($flag_show_product_info_model == 1 && $products_model != '')
    || ( $flag_show_product_info_weight == 1 and $products_weight != 0)
    || ( $flag_show_product_info_quantity == 1)
    || ( $flag_show_product_info_manufacturer == 1 && ! empty($manufacturers_name))
    || ( $flag_show_product_bookx_info_isbn == 1 && $products_isbn != '')) {
    echo '<!--bof Product details list  --><ul id="productDetailsList">';
    echo (($flag_show_product_bookx_info_isbn == 1 && $products_isbn != '') ? '<li><span class="bookxLabel">' . TEXT_PRODUCT_ISBN . '</span>'.$products_isbn . '</li>' : '') . "\n";
    echo '<li>' . $publisher_short_html . '</li>'. "\n";
    echo '<li>' . $imprint_short_html . '</li>'. "\n";
    echo '<li>' . $authors_short_html . '</li>'. "\n";
    echo '<li>' . $bookx_extra_attributes_html . '</li>'. "\n";
    echo '<li>' . $publishing_date_html . '</li>'. "\n";
    echo '<li>' . $genres_html . '</li>'. "\n";
    echo (($flag_show_product_info_model == 1 and $products_model != '') ? '<li>' . TEXT_PRODUCT_MODEL . $products_model . '</li>' : '') . "\n";
    echo (($flag_show_product_info_weight == 1 and $products_weight != 0) ? '<li>' . TEXT_PRODUCT_WEIGHT . $products_weight . TEXT_PRODUCT_WEIGHT_UNIT . '</li>' : '') . "\n";
    echo (($flag_show_product_info_quantity == 1) ? '<li>' . $products_quantity . TEXT_PRODUCT_QUANTITY . '</li>' : '') . "\n";
    echo (($flag_show_product_info_manufacturer == 1 and ! empty($manufacturers_name)) ? '<li>' . TEXT_PRODUCT_MANUFACTURER . $manufacturers_name . '</li>' : '') . "\n";
    echo '</ul><!--eof Product details list -->';
}

$zco_notifier->notify('NOTIFY_TPL_PRODUCT_BOOKX_INFO_DISPLAY_DOCPRODUCT_DETAILS_END', array('products_id' => $products_id_current));

?>
<!--eof Product details list -->

<!--bof free ship icon  -->
<?php if(zen_get_product_is_always_free_shipping($products_id_current) && $flag_show_product_info_free_shipping) { ?>
<div id="freeShippingIcon"><?php echo TEXT_PRODUCT_FREE_SHIPPING_ICON; ?></div>
<?php } ?>
<!--eof free ship icon  -->
</div>
<div id="cart-box" class="grids">
<h2 id="productPrices" class="productGeneral">
<?php
// base price
  if ($show_onetime_charges_description == 'true') {
    $one_time = '<span >' . TEXT_ONETIME_CHARGE_SYMBOL . TEXT_ONETIME_CHARGE_DESCRIPTION . '</span><br />';
  } else {
    $one_time = '';
  }
  echo $one_time . ((zen_has_product_attributes_values((int)$_GET['products_id']) and $flag_show_product_info_starting_at == 1) ? TEXT_BASE_PRICE : '') . zen_get_products_display_price((int)$_GET['products_id']);
?></h2>
<!--eof Product Price block -->

<!--bof Attributes Module -->
<!--bof Quantity Discounts table -->
<?php
  if ($products_discount_type != 0) { ?>
<?php
/**
 * display the products quantity discount
 */
 require($template->get_template_dir('/tpl_modules_products_quantity_discounts.php',DIR_WS_TEMPLATE, $current_page_base,'templates'). '/tpl_modules_products_quantity_discounts.php'); ?>
<?php
  }
?>
<!--eof Quantity Discounts table -->

<!--bof Add to Cart Box -->
<?php
if (CUSTOMERS_APPROVAL == 3 and TEXT_LOGIN_FOR_PRICE_BUTTON_REPLACE_SHOWROOM == '') {
  // do nothing
} else {
?>
<?php
    $display_qty = (($flag_show_product_info_in_cart_qty == 1 and $_SESSION['cart']->in_cart($_GET['products_id'])) ? '<p>' . PRODUCTS_ORDER_QTY_TEXT_IN_CART . $_SESSION['cart']->get_quantity($_GET['products_id']) . '</p>' : '');
    if ($products_qty_box_status == 0 or $products_quantity_order_max== 1) {
      // hide the quantity box and default to 1
      $the_button = '<input type="hidden" name="cart_quantity" value="1" />' . zen_draw_hidden_field('products_id', (int)$_GET['products_id']) . zen_image_submit(BUTTON_IMAGE_IN_CART, BUTTON_IN_CART_ALT);
    } else {
      // show the quantity box
      $the_button = '<div class="max-qty">' . zen_get_products_quantity_min_units_display((int)$_GET['products_id']) . '</div><span class="qty-text">' . PRODUCTS_ORDER_QTY_TEXT . '</span><input type="text" name="cart_quantity" value="' . $products_get_buy_now_qty . '" maxlength="6" size="4" />' . zen_draw_hidden_field('products_id', (int)$_GET['products_id']) . zen_image_submit(BUTTON_IMAGE_IN_CART, BUTTON_IN_CART_ALT);
    }
    $display_button = bookx_get_buy_now_button($_GET['products_id'], $the_button);
?>
<?php if ($display_qty != '' or $display_button != '') { ?>
    <div id="cartAdd">
    <?php
      echo $display_qty;
      echo $display_button;
?>
          </div>
<?php   } // display qty and button ?>
<?php } // CUSTOMERS_APPROVAL == 3 ?>
<!--eof Add to Cart Box-->
</div>
</div> 
<!-- eof prod-info-top -->

<!--bof Product description -->
<?php if ($products_description != '') { ?>
<div id="productDescription" class="productGeneral biggerText"><?php echo stripslashes($products_description); ?></div>
<?php } ?>
<!--eof Product description -->

<!--bof BookX Extra data items -->
<section id="bookxExtra" class="docProduct">
    <h1>Bookx Extras</h1>
<?php
$zco_notifier->notify('NOTIFY_TPL_PRODUCT_BOOKX_INFO_DISPLAY_DOCPRODUCT_DETAILS_BEGIN', array('products_id' => $products_id_current));

echo $publisher_detail_html;
echo $imprint_detail_html;
echo $series_detail_html;
echo $authors_detail_html;
echo $authors_related_products_team_html;
/* 
 * related products are placed in DIV with author. If undesired, uncomment the following line and comment above (see note next to it
 */

//echo $authors_related_products_html;
//*****
$zco_notifier->notify('NOTIFY_TPL_PRODUCT_BOOKX_INFO_DISPLAY_DOCPRODUCT_END', array('products_id' => $products_id_current));

?>
</section>
<!--eof BookX Extra data items -->

<!--bof Prev/Next bottom position -->
<?php if (PRODUCT_INFO_PREVIOUS_NEXT == 2 or PRODUCT_INFO_PREVIOUS_NEXT == 3) { ?>
<?php
/**
 * display the product previous/next helper
 */
 require($template->get_template_dir('/tpl_products_next_previous.php',DIR_WS_TEMPLATE, $current_page_base,'templates'). '/tpl_products_next_previous.php'); ?>
<?php } ?>
<!--eof Prev/Next bottom position -->

<!--bof Reviews button and count-->
<?php
  if ($flag_show_product_info_reviews == 1) {
    // if more than 0 reviews, then show reviews button; otherwise, show the "write review" button
    if ($reviews->fields['count'] > 0 ) { ?>
<div id="productReviewLink" class="buttonRow back"><?php echo '<a href="' . zen_href_link(FILENAME_PRODUCT_REVIEWS, zen_get_all_get_params()) . '">' . zen_image_button(BUTTON_IMAGE_REVIEWS, BUTTON_REVIEWS_ALT) . '</a>'; ?></div>
<br class="clearBoth" />
<p class="reviewCount"><?php echo ($flag_show_product_info_reviews_count == 1 ? TEXT_CURRENT_REVIEWS . ' ' . $reviews->fields['count'] : ''); ?></p>
<?php } else { ?>
<div id="productReviewLink" class="buttonRow back"><?php echo '<a href="' . zen_href_link(FILENAME_PRODUCT_REVIEWS_WRITE, zen_get_all_get_params(array())) . '">' . zen_image_button(BUTTON_IMAGE_WRITE_REVIEW, BUTTON_WRITE_REVIEW_ALT) . '</a>'; ?></div>
<br class="clearBoth" />
<?php
}
}
?>
<!--eof Reviews button and count -->

 <!--bof Product date added/available-->
<?php
  if ($products_date_available > date('Y-m-d H:i:s')) {
    if ($flag_show_product_info_date_available == 1) {
?>
  <p id="productDateAvailable" class="docProduct centeredContent"><?php echo sprintf(TEXT_DATE_AVAILABLE, zen_date_long($products_date_available)); ?></p>
<?php
    }
  } else {
    if ($flag_show_product_info_date_added  == 1) {
?>
      <p id="productDateAdded" class="docProduct centeredContent"><?php echo sprintf(TEXT_DATE_ADDED, zen_date_long($products_date_added)); ?></p>
<?php
    } // $flag_show_product_info_date_added
  }
?>
<!--eof Product date added/available -->

<!--bof Product URL -->
<?php
  if (zen_not_null($products_url)) {
    if ($flag_show_product_info_url == 1) {
?>
    <p id="productInfoLink" class="docProduct centeredContent"><?php echo sprintf(TEXT_MORE_INFORMATION, zen_href_link(FILENAME_REDIRECT, 'action=url&goto=' . urlencode($products_url), 'NONSSL', true, false)); ?></p>
<?php
    } // $flag_show_product_info_url
  }
?>
<!--eof Product URL -->

<!--bof also purchased products module-->
<?php require($template->get_template_dir('tpl_modules_also_purchased_products.php', DIR_WS_TEMPLATE, $current_page_base,'templates'). '/' . 'tpl_modules_also_purchased_products.php');?>
<!--eof also purchased products module-->

<!--bof Form close-->
</form>
<!--bof Form close-->
</div>
<?php $zco_notifier->notify('NOTIFY_TPL_PRODUCT_BOOKX_INFO_DISPLAY_END', array('products_id' => $products_id_current));?>