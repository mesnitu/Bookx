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
 * @version BookX V 1.0.1
 * @version $Id: [admin]/bookx_authors.php 2018-12-28 mesnitu $
 */
/**
 * Product Type Book (BookX) Authors
 *
 * This file handles creating, editing and deleting
 * author infos
 *
 */
require('includes/application_top.php');

$action = (isset($_GET['action']) ? $_GET['action'] : '');
$sort_order = (isset($_GET['list_order'])) ? '&list_order=' . $_GET['list_order'] : '';
//pr($action);
//pr($_GET);
//pr($_POST);
//pr($sanitizer);

if (zen_not_null($action)) {
    
    switch ($action) {
        case 'insert':
            $sort_order = '&list_order=by_aID_desc';
            unset($_GET['list_order']);
        case 'save':
            
            if (isset($_GET['mID'])) {
                $bookx_author_id = zen_db_prepare_input($_GET['mID']);
            }

            $author_name = bookx_null_check($_POST['author_name']);
            $author_image_copyright = bookx_null_check($_POST['author_image_copyright']);
            $author_url = str_replace('http://', '', bookx_null_check($_POST['author_url']));

            $author_sort_order = bookx_null_check($_POST['author_sort_order']);
            $author_default_type = bookx_null_check($_POST['author_default_type']);

            $sql_data_array = array(
                'author_name' => $author_name,
                'author_sort_order' => (int) $author_sort_order,
                'author_url' => $author_url,
                'author_image_copyright' => $author_image_copyright,
                'author_default_type' => (int) $author_default_type,
                'last_modified' => 'now()');

            if ($action == 'insert') {
                $insert_sql_data = array('date_added' => 'now()');

                $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

                zen_db_perform(TABLE_PRODUCT_BOOKX_AUTHORS, $sql_data_array);

                $bookx_author_id = zen_db_insert_id();
                
            } elseif ($action == 'save') {
                /* $update_sql_data = array('last_modified' => 'now()');
                  $sql_data_array = array_merge($sql_data_array, $update_sql_data); */
                zen_db_perform(TABLE_PRODUCT_BOOKX_AUTHORS, $sql_data_array, 'update', "bookx_author_id = '" . (int) $bookx_author_id . "'");
            }

            if ($_POST['author_image_manual'] != '') {
                // add image manually
                $author_image_name = zen_db_input($_POST['img_dir'] . $_POST['author_image_manual']);
                $db->Execute("update " . TABLE_PRODUCT_BOOKX_AUTHORS . "
	                      set author_image = '" . $author_image_name . "'
	                      where bookx_author_id = '" . (int) $bookx_author_id . "'");
            } else {
                $author_image = new upload('author_image');
                $author_image->set_destination(DIR_FS_CATALOG_IMAGES . $_POST['img_dir']);
                if ($author_image->parse() && $author_image->save()) {
                    // remove image from database if none
                    if ($author_image->filename != 'none') {
                        // remove image from database if none
                        $db->Execute("update " . TABLE_PRODUCT_BOOKX_AUTHORS . "
	                          set author_image = '" . zen_db_input($_POST['img_dir'] . $author_image->filename) . "'
	                          where bookx_author_id = '" . (int) $bookx_author_id . "'");
                    } else {
                        $db->Execute("update " . TABLE_PRODUCT_BOOKX_AUTHORS . "
	                          set author_image = ''
	                          where bookx_author_id = '" . (int) $bookx_author_id . "'");
                    }
                }
            }

            $author_description_array = $_POST['author_description'];

            for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                $language_id = $languages[$i]['id'];

                $sql_data_array = array('author_description' => zen_db_prepare_input($author_description_array[$language_id]));

                if ($action == 'insert' ||
                    ($action == 'save' && null === bookx_get_author_description($bookx_author_id, $language_id))) {

                    $insert_sql_data = array(
                        'bookx_author_id' => $bookx_author_id,
                        'languages_id' => $language_id
                        );

                    $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

                    zen_db_perform(TABLE_PRODUCT_BOOKX_AUTHORS_DESCRIPTION, $sql_data_array);
                    
                } elseif ($action == 'save') {
                    zen_db_perform(TABLE_PRODUCT_BOOKX_AUTHORS_DESCRIPTION, $sql_data_array, 'update', "bookx_author_id = '" . (int) $bookx_author_id . "' and languages_id = '" . (int) $language_id . "'");
                }
            }

            //**** apply/remove multiple assignments
            if (isset($_POST['products_to_apply_author']) && !empty($_POST['products_to_apply_author']) && $bookx_author_id) {
                $selected_products = $_POST['products_to_apply_author'];
                $values = '';
                $delimiter = '';
                foreach ($selected_products as $product_id) {
                    $values .= $delimiter . '(' . $product_id . ',' . $bookx_author_id . ')';
                    $delimiter = ', ';
                }
                $query = 'REPLACE INTO ' . TABLE_PRODUCT_BOOKX_AUTHORS_TO_PRODUCTS . ' (products_id, bookx_author_id) VALUES ' . $values . ';';

                $db->Execute($query);
            }

            if (isset($_POST['products_to_remove_author']) && !empty($_POST['products_to_remove_author']) && $bookx_author_id) {
                $selected_products = $_POST['products_to_remove_author'];
                foreach ($selected_products as $product_id) {
                    $query = 'DELETE FROM ' . TABLE_PRODUCT_BOOKX_AUTHORS_TO_PRODUCTS . ' WHERE
                        products_id = ' . $product_id . ' AND bookx_author_id = ' . $bookx_author_id;
                    $db->Execute($query);
                }
            }

            if (isset($_POST['multiple_apply']) && $_POST['multiple_apply']) {
                // we are coming from the "multiple apply" part of the form, so we now continue with the "edit" part of the script
                $action = 'edit';
            } else {
                
                zen_redirect(zen_href_link(FILENAME_BOOKX_AUTHORS, (isset($_GET['page']) ? 'page=' . $_GET['page'] . '&' : '') . 'mID=' . $bookx_author_id . $sort_order . ((isset($_GET['search']) && !empty($_GET['search'])) ? '&search=' . $_GET['search'] : '')));
                
            }

            break;
        case 'deleteconfirm':
            // demo active test
            if (zen_admin_demo()) {
                $_GET['action'] = '';
                $messageStack->add_session(ERROR_ADMIN_DEMO, 'caution');
                zen_redirect(zen_href_link(FILENAME_BOOKX_AUTHORS, 'page=' . $_GET['page'] . ((isset($_GET['search']) && !empty($_GET['search'])) ? '&search=' . $_GET['search'] : '')));
            }
            $bookx_author_id = zen_db_prepare_input($_POST['mID']);

            if (isset($_POST['delete_image']) && ($_POST['delete_image'] == 'on')) {
                $author = $db->Execute("SELECT author_image
                                        FROM " . TABLE_PRODUCT_BOOKX_AUTHORS . "
                                        WHERE bookx_author_id = '" . (int) $bookx_author_id . "'");

                $image_location = DIR_FS_CATALOG_IMAGES . $author->fields['author_image'];

                if (file_exists($image_location))
                    @unlink($image_location);
            }
            
            $db->Execute("DELETE FROM " . TABLE_PRODUCT_BOOKX_AUTHORS . "
                      WHERE bookx_author_id = '" . (int) $bookx_author_id . "'");
            $db->Execute("DELETE FROM " . TABLE_PRODUCT_BOOKX_AUTHORS_DESCRIPTION . "
                      WHERE bookx_author_id = '" . (int) $bookx_author_id . "'");

            if (isset($_POST['delete_products']) && ($_POST['delete_products'] == 'on')) {
                $products = $db->Execute("SELECT p.products_id
                                            FROM " . TABLE_PRODUCT_BOOKX_EXTRA . " p
											LEFT JOIN " . TABLE_PRODUCT_BOOKX_AUTHORS_TO_PRODUCTS . " pta ON pta.products_id = p.products_id
                                            WHERE pta.bookx_author_id = '" . (int) $bookx_author_id . "'");

                while (!$products->EOF) {
                    bookx_delete_product((int) $products->fields['products_id']);
                    $products->MoveNext();
                }
            }

            $db->Execute("DELETE FROM " . TABLE_PRODUCT_BOOKX_AUTHORS_TO_PRODUCTS . "
                        WHERE bookx_author_id =  '" . (int) $bookx_author_id . "'");
            
            zen_redirect(zen_href_link(FILENAME_BOOKX_AUTHORS, 'page=' . $_GET['page'] . ((isset($_GET['search']) && !empty($_GET['search'])) ? '&search=' . $_GET['search'] : '')));
            
            break;
    }
}

/**
 * some style display options
 */
$col_left_css = 'col-xs-12 col-sm-12 col-md-8 configurationColumnLeft';
$col_right_css = 'col-xs-12 col-sm-12 col-md-4 configurationColumnRight';
$form_class = 'form-horizontal';
$form_control = ' class="form-control"';
/**
 * tpl wrap form labels and input fields to dispay 
 */
$wrap = function($label = array('name', 'for', 'style'), $input_field, $input_type = false) {
    $class = (empty($label[2])) ? 'col-sm-3' : $label[2];
    if ($input_type == 'checkbox') {
        $display = '<div class="checkbox"><label class="' . $class . '">' . $input_field . $label[0] . '</label></div>';
    } elseif ($input_type == 'textarea') {
        $display = zen_draw_label($label[0], $label[1], 'class="' . $class . '"');
        $display .= '<div class="col-sm-12">' . $input_field . '</div>';
    } else {
        $display = zen_draw_label($label[0], $label[1], 'class="' . $class . '"');
        $display .= '<div class="col-sm-9">' . $input_field . '</div>';
    }
    return $display;
};

if ($action == 'new' || $action == 'edit' || (isset($_POST['multiple_apply']) && $_POST['multiple_apply'])) {
    $col_left_css = "hide";
    $col_right_css = "col-xs-12 col-sm-12 col-md-8 col-md-offset-2";
}
?>
<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
    <head>
        <meta charset="<?php echo CHARSET; ?>">
        <title><?php echo TITLE; ?></title>
        <link rel="stylesheet" href="includes/stylesheet.css">
        <style>
            textArea {
                width:100%;
            }
            .glyphicon {
                font-size: 1.5rem;  
            }
            .glyphicon-edit:before, 
            .glyphicon-remove-sign:before, 
            .glyphicon-eye-open:before,
            .glyphicon-info-sign:before {
                padding: 4px 5px;
                border-radius: 3px;
            }
            .glyphicon-edit:before {
                color: #eee;
                background: #4CAF50;
            }
            .glyphicon-remove-sign:before {
                background: #FFCDD2;
                color: #D32F2F;
            }
            .glyphicon-info-sign:before {
                color: #1976D2;
            }
            .glyphicon-eye-open:before {
                color: #607D8B;
            }
        </style>
        <link rel="stylesheet" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
        <script src="includes/menu.js"></script>
        <script src="includes/general.js"></script>
    </head>
    <body onload="init()">
        <!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
        <!-- header_eof //-->
        <!-- body //-->
        <div class="container-fluid">

            <div class="row">
                <div class="col-xs-6"><h1 ><?php echo HEADING_TITLE; ?></h1></div>
                <div class="col-xs-6">
<?php echo zen_draw_form('search', FILENAME_BOOKX_AUTHORS, '', 'get', 'class="form-inline"'); ?>
                    <div class="form-group">
<?php
echo zen_draw_label(HEADING_TITLE_SEARCH_DETAIL, 'search', 'class="sr-only"');
echo zen_draw_input_field('search', '', 'class="form-control" placeholder="' . HEADING_TITLE_SEARCH_DETAIL . '"') . zen_hide_session_id();
// show reset search
//if (isset($_GET['search']) && zen_not_null($_GET['search'])) {
    echo '<a href="' . zen_href_link(FILENAME_BOOKX_AUTHORS) . '" class="btn btn-primary" role="button">Reset</a>';
//}
if (isset($_GET['search']) && zen_not_null($_GET['search'])) {
    $keywords = zen_db_input(zen_db_prepare_input($_GET['search']));
    ?>
                            <div class="alert alert-success" role="alert"><?php echo TEXT_INFO_SEARCH_DETAIL_FILTER . $keywords; ?></div>
                        <?php } ?>
                    </div>
                    </form>
                </div>
            </div> <!-- row-->

            <div class="row">
                <!-- configurationColumnLeft //-->
                <div class="<?php echo $col_left_css; ?>">
                    <table class="table table-hover">
                        <thead>
                            <tr class="dataTableHeadingRow">
                                <th class="dataTableHeadingContent">
<?php echo TABLE_HEADING_AUTHOR; ?>
                                    <a href="<?php echo zen_href_link(basename($PHP_SELF), zen_get_all_get_params(array('list_order', 'page')) . 'list_order=by_name_asc', 'NONSSL'); ?>"><?php echo ($_GET['list_order'] == 'by_name_asc' ? '<span class="SortOrderHeader">Asc</span>' : '<span class="SortOrderHeaderLink">Asc</span>'); ?></a>
                                    <a href="<?php echo zen_href_link(basename($PHP_SELF), zen_get_all_get_params(array('list_order', 'page')) . 'list_order=by_name_desc', 'NONSSL'); ?>"><?php echo ($_GET['list_order'] == 'by_name_desc' ? '<span class="SortOrderHeader">Desc</span>' : '<span class="SortOrderHeaderLink">Desc</span>'); ?></a>
                                </th>
                                <th class="dataTableHeadingContent">
<?php echo TABLE_HEADING_SORT_ORDER; ?>
                                    <a href="<?php echo zen_href_link(basename($PHP_SELF), zen_get_all_get_params(array('list_order', 'page')) . 'list_order=by_sort_order_asc', 'NONSSL'); ?>"><?php echo ($_GET['list_order'] == 'by_sort_order_asc' ? '<span class="SortOrderHeader">Asc</span>' : '<span class="SortOrderHeaderLink">Asc</span>'); ?></a>
                                    <a href="<?php echo zen_href_link(basename($PHP_SELF), zen_get_all_get_params(array('list_order', 'page')) . 'list_order=by_sort_order_desc', 'NONSSL'); ?>"><?php echo ($_GET['list_order'] == 'by_sort_order_desc' ? '<span class="SortOrderHeader">Desc</span>' : '<span class="SortOrderHeaderLink">Desc</span>'); ?></a>
                                </th>
                                <th class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</th>
                            </tr>
                        </thead>
                        <tbody>
<?php
$disp_order = "author_sort_order, author_name";

if (isset($_GET['search'])) {

    $search = zen_db_prepare_input($_GET['search']);
    $author_query_raw = "SELECT * FROM " . TABLE_PRODUCT_BOOKX_AUTHORS . " WHERE author_name LIKE '%" . zen_db_input($search) . "%' ORDER BY " . $disp_order . "";
    
} else {

    if (isset($_GET['list_order'])) {

        switch ($_GET['list_order']) {
            case 'by_sort_order_asc':
                $disp_order = "author_sort_order";
                break;
            case 'by_sort_order_desc':
                $disp_order = "author_sort_order DESC";
                break;
            case 'by_name_asc':
                $disp_order = "author_name";
                break;
            case 'by_name_desc':
                $disp_order = "author_name DESC";
                break;
            case 'by_aID_desc':
                $disp_order = "bookx_author_id DESC";
            default:
                break;
        }
    }

    $author_query_raw = "SELECT * FROM " . TABLE_PRODUCT_BOOKX_AUTHORS . " ORDER BY " . $disp_order . "";
}

$author_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $author_query_raw, $author_query_numrows);
$author = $db->Execute($author_query_raw);

$author_types_array = array(array('id' => '', 'text' => TEXT_NONE));
$author_types = $db->Execute('SELECT at.bookx_author_type_id, atd.type_description
                                FROM ' . TABLE_PRODUCT_BOOKX_AUTHOR_TYPES . ' at
    							LEFT JOIN ' . TABLE_PRODUCT_BOOKX_AUTHOR_TYPES_DESCRIPTION . ' atd ON at.bookx_author_type_id = atd.bookx_author_type_id AND atd.languages_id = ' . $_SESSION['languages_id'] . '
								ORDER BY at.type_sort_order, atd.type_description');

while (!$author_types->EOF) {
    $author_types_array[] = array(
        'id' => $author_types->fields['bookx_author_type_id'],
        'text' => $author_types->fields['type_description']
    );
    $author_types->MoveNext();
}

while (!$author->EOF) {

    if ((!isset($_GET['mID']) || (isset($_GET['mID']) && ($_GET['mID'] == $author->fields['bookx_author_id']))) && 
        !isset($aInfo) && (substr($action, 0, 3) != 'new')) {
        $author_products = $db->Execute("SELECT COUNT(DISTINCT p.products_id) AS products_count
                                             FROM " . TABLE_PRODUCT_BOOKX_EXTRA . " p
											 LEFT JOIN " . TABLE_PRODUCT_BOOKX_AUTHORS_TO_PRODUCTS . " pta ON pta.products_id = p.products_id
                                             WHERE pta.bookx_author_id = '" . (int) $author->fields['bookx_author_id'] . "'");
        //pr($author_products);
        $aInfo_array = array_merge($author->fields, $author_products->fields);

        $aInfo = new objectInfo($aInfo_array);
    }

    if (isset($aInfo) && is_object($aInfo) && ($author->fields['bookx_author_id'] == $aInfo->bookx_author_id)) {

        //echo '<tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link(FILENAME_BOOKX_AUTHORS, 'page=' . $_GET['page'] . $sort_order . '&mID=' . $author->fields['bookx_author_id'] . $sort_order . '&action=edit') . '\'">' . "\n";
    } else {
       // echo '<tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link(FILENAME_BOOKX_AUTHORS, 'page=' . $_GET['page'] . $sort_order . '&mID=' . $author->fields['bookx_author_id'] . '&action=edit') . '\'">' . "\n";
    }
    ?>
                            <td class="dataTableContent"><?php echo $author->fields['author_name']; ?></td>
                            <td class="dataTableContent"><?php echo $author->fields['author_sort_order']; ?></td>
                            <td class="dataTableContent" align="right">
                                <a href="<?php echo zen_href_link(FILENAME_BOOKX_AUTHORS, 'page=' . $_GET['page'] . $sort_order . '&mID=' . $author->fields['bookx_author_id'] . '&action=edit' . ((isset($_GET['search']) && !empty($_GET['search'])) ? '&search=' . $_GET['search'] : '')); ?>"> 
                                    <span class="glyphicon glyphicon-edit" aria-hidden="true"></span>
                                </a>
                                <a href="<?php echo zen_href_link(FILENAME_BOOKX_AUTHORS, 'page=' . $_GET['page'] . $sort_order . '&mID=' . $author->fields['bookx_author_id'] . '&action=delete' . ((isset($_GET['search']) && !empty($_GET['search'])) ? '&search=' . $_GET['search'] : '')); ?>">
                                    <span class="glyphicon glyphicon-remove-sign" aria-hidden="true"></span>
                                </a>

    <?php 
    if (isset($aInfo) && is_object($aInfo) && ($author->fields['bookx_author_id'] == $aInfo->bookx_author_id)) { ?>
                                    <span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span>
    <?php } else { ?>
                                    <a href="<?php echo zen_href_link(FILENAME_BOOKX_AUTHORS, zen_get_all_get_params(array('mID')) . 'mID=' . $author->fields['bookx_author_id']); ?>"><span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span></a>     
    <?php } ?>
                            </td>
                            </tr>
                                <?php
                                $author->MoveNext();
                            }
                            ?>
                    </tbody>
                    
                    </table>
                    <div class="center-block">

                        <?php
                        echo $author_split->display_count($author_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_AUTHORS);
                        echo $author_split->display_links($author_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']);

                        if (empty($action)) { ?>
                            <a href="<?php echo zen_href_link(FILENAME_BOOKX_AUTHORS, 'page=' . $_GET['page'] . $sort_order . '&action=new' . ((isset($_GET['search']) && !empty($_GET['search'])) ? '&search=' . $_GET['search'] : '')); ?>" class="btn btn-primary pull-right"><?php echo IMAGE_INSERT; ?></a>
                            <?php } ?>
                    </div>
                </div>
                <!-- configurationColumnRight //-->
                <div class="<?php echo $col_right_css; ?>">
<?php
$heading = array();
$contents = array();

switch ($action) {
    case 'new':
        $heading[] = array('text' => '<h4>' . TEXT_HEADING_NEW_AUTHOR . '</h4>');

        $contents = array('form' => zen_draw_form('author', FILENAME_BOOKX_AUTHORS, 'action=insert', 'post', 'class="form-horizontal" enctype="multipart/form-data"'));
        $contents[] = array('text' => '<p>' . TEXT_NEW_INTRO . '</p>');
        $contents[] = array(
            'text' => $wrap(array(
                TEXT_AUTHOR_NAME,
                'author_name'
                ), zen_draw_input_field('author_name', '', zen_set_field_length(TABLE_PRODUCT_BOOKX_AUTHORS, 'author_name') . $form_control))
        );
        $contents[] = array(
            'text' => $wrap(array(
                TEXT_AUTHOR_DEFAULT_TYPE,
                'author_default_type'
                ), zen_draw_pull_down_menu('author_default_type', $author_types_array, '', $form_control))
        );
        $contents[] = array(
            'text' => $wrap(array(
                TEXT_AUTHOR_IMAGE,
                'author_image'
                ), zen_draw_file_field('author_image', false, $form_control))
        );

        $dir = @dir(DIR_FS_CATALOG_IMAGES);
        $dir_info[] = array('id' => '', 'text' => "Main Directory");
        while ($file = $dir->read()) {
            if (is_dir(DIR_FS_CATALOG_IMAGES . $file) && strtoupper($file) != 'CVS' && $file != "." && $file != "..") {
                $dir_info[] = array('id' => $file . '/', 'text' => $file);
            }
        }
        $dir->close();

        $default_directory = 'bookx_authors/';

        $contents[] = array(
            'text' => $wrap(array(
                TEXT_AUTHOR_IMAGE_DIR,
                'img_dir'
                ), zen_draw_pull_down_menu('img_dir', $dir_info, $default_directory, $form_control))
        );
        $contents[] = array(
            'text' => $wrap(array(
                TEXT_AUTHOR_IMAGE_MANUAL,
                'author_image_manual'
                ), zen_draw_input_field('author_image_manual', '', $form_control))
        );
        $contents[] = array(
            'text' => $wrap(array(
                TEXT_AUTHOR_IMAGE_COPYRIGHT,
                'author_image_copyright'
                ), zen_draw_input_field('author_image_copyright', '', zen_set_field_length(TABLE_PRODUCT_BOOKX_AUTHORS, 'author_image_copyright') . $form_control))
        );
        $contents[] = array(
            'text' => $wrap(array(
                TEXT_AUTHOR_URL,
                'author_url'
                ), zen_draw_input_field('author_url', '', zen_set_field_length(TABLE_PRODUCT_BOOKX_AUTHORS, 'author_url') . $form_control))
        );

        $author_description_textarea = '';
        $languages = zen_get_languages();
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
            $author_description_textarea .= '<br>' . zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . zen_draw_textarea_field('author_description[' . $languages[$i]['id'] . ']', 'soft', '100%', '15', '');
        }

        $contents[] = array('text' =>
            $wrap(array(
                TEXT_AUTHOR_DESCRIPTION,
                'author_description',
                'block'
                ), $author_description_textarea, 'textarea')
        );

        $default_value = ($author_sort_order) ?? '0';
        $contents[] = array('text' =>
            $wrap(array(
                TEXT_AUTHOR_SORT_ORDER,
                'author_sort_order'
                ), zen_draw_input_field('author_sort_order', $default_value, $form_control))
        );

        $contents[] = array(
            'align' => 'center',
            'text' => '<button type="submit" class="btn btn-primary">' . IMAGE_SAVE . ' </button>&nbsp;<a href="' . zen_href_link(FILENAME_BOOKX_AUTHORS, 'page=' . $_GET['page'] . $sort_order ) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');

        break;

    case 'edit':

        $heading[] = array('text' => '<h4>' . TEXT_HEADING_EDIT_AUTHOR . '</h4>');

        $contents = array('form' => zen_draw_form('author', FILENAME_BOOKX_AUTHORS, 'page=' . $_GET['page'] . $sort_order . '&mID=' . $aInfo->bookx_author_id . '&action=save' . ((isset($_GET['search']) && !empty($_GET['search'])) ? '&search=' . $_GET['search'] : ''), 'post', 'class="form-horizontal" enctype="multipart/form-data"'));
        $contents[] = array('text' => TEXT_EDIT_INTRO);
        $contents[] = array(
            'text' => $wrap(array(
                TEXT_AUTHOR_NAME,
                'author_name'
                ), zen_draw_input_field('author_name', htmlspecialchars($aInfo->author_name, ENT_COMPAT, CHARSET, true), zen_set_field_length(TABLE_PRODUCT_BOOKX_AUTHORS, 'author_name') . $form_control))
        );
        $contents[] = array(
            'text' => $wrap(array(
                TEXT_AUTHOR_DEFAULT_TYPE, 
                'author_default_type'
                ), zen_draw_pull_down_menu('author_default_type', $author_types_array, $aInfo->author_default_type, $form_control))
        );
        $contents[] = array(
            'text' => (null != $aInfo->author_image && '' != $aInfo->author_image) ? $wrap(array('Current', 'img'), zen_image(DIR_WS_CATALOG_IMAGES . $aInfo->author_image, $aInfo->author_name, BOOKX_AUTHOR_LISTING_IMAGE_MAX_WIDTH, BOOKX_AUTHOR_LISTING_IMAGE_MAX_HEIGHT, 'class="img-responsive"')) : TEXT_AUTHOR_IMAGE_NOT_DEFINED);

        $contents[] = array(
            'text' => $wrap(array(
                TEXT_AUTHOR_IMAGE, 
                'author_image'
                ), zen_draw_file_field('author_image', false, $form_control) . $aInfo->author_image)
        );
        $dir = @dir(DIR_FS_CATALOG_IMAGES);
        $dir_info[] = array('id' => '', 'text' => "Main Directory");
        while ($file = $dir->read()) {
            if (is_dir(DIR_FS_CATALOG_IMAGES . $file) && strtoupper($file) != 'CVS' && $file != "." && $file != "..") {
                $dir_info[] = array('id' => $file . '/', 'text' => $file);
            }
        }
        $dir->close();
        sort($dir_info);
        $default_directory = substr($aInfo->author_image, 0, strpos($aInfo->author_image, '/') + 1);
        if ('' == $aInfo->author_image) {
            $default_directory = 'bookx_authors/';
        }

        $contents[] = array(
            'text' => $wrap(array(
                TEXT_AUTHOR_IMAGE_DIR, 
                'img_dir'
                ), zen_draw_pull_down_menu('img_dir', $dir_info, $default_directory, $form_control))
        );
        $contents[] = array(
            'text' => $wrap(array(
                TEXT_AUTHOR_IMAGE_MANUAL, 
                'author_image_manual'
                ), zen_draw_input_field('author_image_manual', '', $form_control))
        );
        $contents[] = array('text' =>
            $wrap(array(
                TEXT_AUTHOR_IMAGE_COPYRIGHT, 
                'author_image_copyright'
                ), zen_draw_input_field('author_image_copyright', $aInfo->author_image_copyright, zen_set_field_length(TABLE_PRODUCT_BOOKX_AUTHORS, 'author_image_copyright') . $form_control))
        );

        $contents[] = array('text' =>
            $wrap(array(TEXT_AUTHOR_URL, 'author_url'), zen_draw_input_field('author_url', $aInfo->author_url, zen_set_field_length(TABLE_PRODUCT_BOOKX_AUTHORS, 'author_url') . $form_control))
        );
        
        $author_description_textarea = '';
        $languages = zen_get_languages();
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
            $author_description_textarea .= zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . zen_draw_textarea_field('author_description[' . $languages[$i]['id'] . ']', 'soft', '100%', '15', htmlspecialchars(bookx_get_author_description($aInfo->bookx_author_id, $languages[$i]['id']), ENT_COMPAT, CHARSET, TRUE));
        }

        $contents[] = array(
            'text' => $wrap(array(
                TEXT_AUTHOR_DESCRIPTION,
                'author_description',
                'block'
                ), $author_description_textarea, 'textarea')
        );
        $contents[] = array(
            'text' => $wrap(array(
                TEXT_AUTHOR_SORT_ORDER,
                'author_sort_order'
                ), zen_draw_input_field('author_sort_order', $aInfo->author_sort_order, $form_control))
        );
        $contents[] = array(
            'align' => 'center', 
            'text' => '<button type="submit" class="btn btn-primary">' . IMAGE_SAVE . '</button><a href="' . zen_href_link(FILENAME_BOOKX_AUTHORS, 'page=' . $_GET['page'] . $sort_order . '&mID=' . $aInfo->bookx_author_id . ((isset($_GET['search']) && !empty($_GET['search'])) ? '&search=' . $_GET['search'] : '')) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');


        ///***** multiple apply / remove author
        $contents[] = array('text' => '<h4 class="infoBoxHeading">' . TEXT_APPLY_AUTHOR . '</h4>');

        $extra_fields = '';
        $extra_having_clause = '';
        if (isset($_POST['apply_authors_list_all_products']) && 'on' == $_POST['apply_authors_list_all_products'] &&
            isset($_POST['remove_authors_list_all_products']) && 'on' == $_POST['remove_authors_list_all_products']) {
            $flag_apply_authors_list_all_products = true;
        } else {
            $flag_apply_authors_list_all_products = false;
        }

        if (!$flag_apply_authors_list_all_products) {
            $extra_fields = ' , p.products_quantity,  p.products_date_available ';
            // $extra_in_stock_join_clause = ' LEFT JOIN ' . TABLE_PRODUCTS . ' p ON p.products_id = batp.products_id AND p.products_status > 0';
            $extra_having_clause = ' HAVING (products_quantity > 0 OR products_date_available >= "' . date('Y-m-d H:i:s', time() - (86400 * 60)) . '")'; // 86400 * 60 = 60 days
        }

        $contents[] = array('text' =>
            $wrap(array(
                TEXT_APPLY_AUTHOR_LIST_OUT_OF_STOCK, 
                'apply_authors_list_all_products'
                ), zen_draw_checkbox_field('apply_authors_list_all_products', true, $flag_apply_authors_list_all_products, '', 'id="apply_authors_list_all_products" onClick="document.getElementById(\'remove_authors_list_all_products\').checked=this.checked; document.getElementById(\'multiple_apply\').value=true; this.form.submit()"'), 'checkbox') . zen_draw_hidden_field('multiple_apply', false, 'id="multiple_apply"')
        );

        $select_string_products = '<select name="products_to_apply_author[]" size="10" multiple="multiple" multiple class="form-control">';

        $products = $db->Execute('SELECT DISTINCT p.products_id, p.products_model, 
                                           CONCAT_WS(""
											  ,pd.products_name
											  ,IF(NULLIF(be.volume, "") IS NOT NULL, CONCAT_WS("", " ", REPLACE("' . LABEL_BOOKX_VOLUME . '", "%s", be.volume), " - "), "")
											  ,IF(NULLIF(bed.products_subtitle, "") IS NOT NULL, bed.products_subtitle, "")
											  ) AS products_name '
            . $extra_fields .
            ' FROM ' . TABLE_PRODUCTS . ' p
            						LEFT JOIN ' . TABLE_PRODUCT_TYPES . ' pt ON p.products_type = pt.type_id
                                    LEFT JOIN ' . TABLE_PRODUCTS_DESCRIPTION . ' pd ON p.products_id = pd.products_id AND pd.language_id = "' . (int) $_SESSION['languages_id'] . '"
                                    LEFT JOIN ' . TABLE_PRODUCT_BOOKX_EXTRA . ' be ON p.products_id = be.products_id 
                                    LEFT JOIN ' . TABLE_PRODUCT_BOOKX_EXTRA_DESCRIPTION . ' bed ON p.products_id = bed.products_id AND bed.languages_id = "' . (int) $_SESSION['languages_id'] . '"
    				                WHERE pt.type_handler = "product_bookx" AND p.products_id NOT IN (SELECT products_id FROM ' . TABLE_PRODUCT_BOOKX_AUTHORS_TO_PRODUCTS . '  WHERE bookx_author_id = "' . $aInfo->bookx_author_id . '")'
            . $extra_having_clause .
            ' ORDER BY products_name');

        $product_array = array();

        while (!$products->EOF) {
            //$display_price = $products->fields['products_price']; // zen_get_products_display_price($product['products_id']);
            $select_string_products .= '<option value="' . $products->fields['products_id'] . '">';
            $select_string_products .= $products->fields['products_name'] . ' [' . $products->fields['products_model'] . '] - ID# ' . $products->fields['products_id'] . '</option>'; // (' . $display_price . ')

            $products->MoveNext();
        }

        $select_string_products .= '</select>';

        $contents[] = array('text' =>
            $select_string_products . zen_draw_hidden_field('bookx_author_id', $aInfo->bookx_author_id) . '<button class="btn btn-primary btn-sm" type="submit" onClick="document.getElementById(\'multiple_apply\').value=true; this.form.submit()"/>' . TEXT_BUTTON_SUBMIT_APPLY_AUTHOR . '</button>');

        //**** Show books to which author is already assigned ***//
        $contents[] = array('text' => '<h4 class="infoBoxHeading">' . TEXT_REMOVE_AUTHOR . '</h4>');

        $contents[] = array('text' =>
            $wrap(array(
                TEXT_APPLY_AUTHOR_LIST_OUT_OF_STOCK
                ), zen_draw_checkbox_field('remove_authors_list_all_products', true, $flag_apply_authors_list_all_products, '', 'id="remove_authors_list_all_products" onClick="document.getElementById(\'apply_authors_list_all_products\').checked=this.checked; document.getElementById(\'multiple_apply\').value=true; this.form.submit()"'), 'checkbox'));

        $select_string_products = '<select name="products_to_remove_author[]" size="10" multiple="multiple" multiple class="form-control">';

        $products = $db->Execute('SELECT DISTINCT p.products_id, p.products_model, 
                                           CONCAT_WS(""
											  ,pd.products_name
											  ,IF(NULLIF(be.volume, "") IS NOT NULL, CONCAT_WS("", " ", REPLACE("' . LABEL_BOOKX_VOLUME . '", "%s", be.volume), " - "), "")
											  ,IF(NULLIF(bed.products_subtitle, "") IS NOT NULL, bed.products_subtitle, "")
											  ) AS products_name '
            . $extra_fields .
            ' FROM ' . TABLE_PRODUCTS . ' p
            						LEFT JOIN ' . TABLE_PRODUCT_TYPES . ' pt ON p.products_type = pt.type_id
                                    LEFT JOIN ' . TABLE_PRODUCTS_DESCRIPTION . ' pd ON p.products_id = pd.products_id AND pd.language_id = "' . (int) $_SESSION['languages_id'] . '"
                                    LEFT JOIN ' . TABLE_PRODUCT_BOOKX_EXTRA . ' be ON p.products_id = be.products_id 
                                    LEFT JOIN ' . TABLE_PRODUCT_BOOKX_EXTRA_DESCRIPTION . ' bed ON p.products_id = bed.products_id AND bed.languages_id = "' . (int) $_SESSION['languages_id'] . '"
				                    WHERE pt.type_handler = "product_bookx" AND p.products_id IN (SELECT products_id FROM ' . TABLE_PRODUCT_BOOKX_AUTHORS_TO_PRODUCTS . '  WHERE bookx_author_id = "' . $aInfo->bookx_author_id . '")'
            . $extra_having_clause .
            ' ORDER BY products_name');

        $product_array = array();

        while (!$products->EOF) {
            //$display_price = $products->fields['products_price']; // zen_get_products_display_price($product['products_id']);
            $select_string_products .= '<option value="' . $products->fields['products_id'] . '">';
            $select_string_products .= $products->fields['products_name'] . ' [' . $products->fields['products_model'] . '] - ID# ' . $products->fields['products_id'] . '</option>';

            $products->MoveNext();
        }

        $select_string_products .= '</select>';

        $contents[] = array('text' => $select_string_products . zen_draw_hidden_field('bookx_author_id', $aInfo->bookx_author_id) . '<button type="submit" class="btn btn-primary btn-sm" onClick="document.getElementById(\'multiple_apply\').value=true; this.form.submit()"/>' . TEXT_BUTTON_SUBMIT_REMOVE_AUTHOR . '</button>');

        break;
    case 'delete':
        $heading[] = array('text' => '<h4>' . TEXT_HEADING_DELETE_AUTHOR . '</h4>');
        //function zen_draw_form($name, $action, $parameters = '', $method = 'post', $params = '', $usessl = 'false') {
        $contents = array('form' => zen_draw_form('author', FILENAME_BOOKX_AUTHORS, 'page=' . $_GET['page'] . $sort_order . '&action=deleteconfirm' . ((isset($_GET['search']) && !empty($_GET['search'])) ? '&search=' . $_GET['search'] : ''), 'post', 'class="form-horizontal"') . zen_draw_hidden_field('mID', $aInfo->bookx_author_id));
        $contents[] = array('text' => '<p>' . sprintf(TEXT_DELETE_INTRO, $aInfo->author_name) . '</p>');
        $contents[] = array(
            'text' => $aInfo->author_name . $wrap(array(
                TEXT_DELETE_IMAGE, 
                'delete_image'
                ), zen_draw_checkbox_field('delete_image', '', true), 'checkbox')
        );

        if ($aInfo->products_count > 0) {
            $contents[] = array('text' =>
                $wrap(array(
                    sprintf(TEXT_DELETE_PRODUCTS, $aInfo->author_name)
                    ), zen_draw_checkbox_field('delete_products'), 'checkbox'), 'checkbox');
            $contents[] = array('text' => '<p class="text-danger">' . sprintf(TEXT_DELETE_WARNING_PRODUCTS, $aInfo->products_count, $aInfo->author_name) . '</p>');
        }

        $contents[] = array(
            'align' => 'center', 
            'text' => '<button type="submit" class="btn btn-danger">'. IMAGE_DELETE . '</button><a href="' . zen_href_link(FILENAME_BOOKX_AUTHORS, 'page=' . $_GET['page'] . $sort_order . '&mID=' . $aInfo->bookx_author_id . ((isset($_GET['search']) && !empty($_GET['search'])) ? '&search=' . $_GET['search'] : '')) . '" class="btn btn-default" role="button">' . IMAGE_CANCEL . '</a>');
        break;
    default:
        if (isset($aInfo) && is_object($aInfo)) {
            $heading[] = array('text' => '<b>' . $aInfo->author_name . '</b>');

            $contents[] = array(
                'align' => 'center',
                'text' => '<a href="' . zen_href_link(FILENAME_BOOKX_AUTHORS, 'page=' . $_GET['page'] . $sort_order . '&mID=' . $aInfo->bookx_author_id . '&action=edit' . ((isset($_GET['search']) && !empty($_GET['search'])) ? '&search=' . $_GET['search'] : '')) . '" class="btn btn-primary" role="button">' . IMAGE_EDIT . '</a> <a href="' . zen_href_link(FILENAME_BOOKX_AUTHORS, 'page=' . $_GET['page'] . $sort_order . '&mID=' . $aInfo->bookx_author_id . '&action=delete') . '" class="btn btn-danger" role="button">' . IMAGE_DELETE . '</a>');
            $contents[] = array('text' => '<br>' . TEXT_DATE_ADDED . ' ' . zen_date_short($aInfo->date_added));
            if (zen_not_null($aInfo->last_modified))
                $contents[] = array('text' => TEXT_LAST_MODIFIED . ' ' . zen_date_short($aInfo->last_modified));
            //$contents[] = array('text' => '<br>' . zen_image(DIR_WS_CATALOG_IMAGES . $aInfo->author_image, $aInfo->author_name, , BOOKX_AUTHOR_LISTING_IMAGE_MAX_HEIGHT, 'class="img-responsive"'));
            $contents[] = array('text' => '<br>' . zen_image(DIR_WS_CATALOG_IMAGES . $aInfo->author_image, $aInfo->author_name,'' , '', 'class="img-responsive"'));
            $contents[] = array('text' => '<br>' . TEXT_PRODUCTS . ' ' . $aInfo->products_count);
        }
        break;
}

if ((zen_not_null($heading)) && (zen_not_null($contents))) {
    $box = new box;
    echo $box->infoBox($heading, $contents);
}
?>
                </div>

            </div><!-- row-->

        </div>
        <!-- body_eof //-->

        <!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
        <!-- footer_eof //-->
<?php if ($editor_handler != '') include_once ($editor_handler); ?>
    </body>
</html>
        <?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
