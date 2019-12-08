<?php
/**
 * This file is part of the ZenCart add-on BookX which
 * introduces a new product type for books to the Zen Cart
 * shop system. Tested for compatibility on ZC v.1.56
 *
 * For latest version and support visit:
 * https://github.com/philoupin/bookx
 *
 * Project BookX v1.0.1
 * @package admin
 * @author  Philou
 * @copyright Copyright 2013
 * @copyright Copyright 2003-2019 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 *
 * File bookx_tools.php
 * Project Path /[RENAME_TO_YOUR_ADMIN_FOLDER]/bookx_tools.php
 *
 * @version $Id: mesnitu  2019 Dec 08 in BookX v1.0.1 for Zen Cart 1.5.6c $
 * -----
 * HISTORY:
 * Date      	By   	Comments
 * ----------	-----	--------------------------------------------------
*/

require_once 'includes/application_top.php';

$action = (isset($_GET['action']) ? $_GET['action'] : '');

/**
 * Checks Install.
 * 1 - Fresh install - Detects no pType, no Version, no DataBases.
 * 2 - update - Detects No version and ptype (v09) OR Version and pType found.
 * 3 - update - Detects DataBases and no pType, no Version.
 */

if ($action == 'bookx_install_options') {
    // if zc version < 56 display files warning
    if (str_replace('.', '', EXPECTED_DATABASE_VERSION_MINOR) < "56") {
        $msg = "Zen Cart 156 not found. This Bookx Version as not been tested with 
            v" . PROJECT_VERSION_MINOR . ". While it can update database tables, some files and features were added. 
            You have to compare files. Test first in a dev environment";
        $messageStack->add($msg, 'caution');
    }
    // No installed version, no databases found, no ptypeID: Install:
    if (empty($bookx_installed_version) && empty($bookx_already_installed) && empty($bookx_ptypeID)) {
        // bookx process install
    } else {
        //bookx update
        if (isset($_GET['confirm_v09']) == true) {
            //if is confirmed insert version
            $messageStack->add('Bookx v090 confirmed', 'success');
            $bookx_installed_version = '0.9'; // some first BETA files had no version info
            $sql = 'REPLACE INTO ' . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function)
            VALUES ('BookX Version', 'BOOKX_VERSION', '0.9', 'BookX Version is stored but not editable', 0, 10000, NOW(), NOW(), NULL, NULL)";

            //$db->Execute($sql);
        }
    }
    
    
    
    // find version
    if (empty($bookx_installed_version)) {
        // Assuming that v09 is installed. Display a warning message with a confirm action
        if (isset($_GET['confirm_v09']) == true) {
            //if is confirmed insert version
            $messageStack->add('Bookx v090 confirmed', 'success');
            $bookx_installed_version = '0.9'; // some first BETA files had no version info
            $sql = 'REPLACE INTO ' . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function)
            VALUES ('BookX Version', 'BOOKX_VERSION', '0.9', 'BookX Version is stored but not editable', 0, 10000, NOW(), NOW(), NULL, NULL)";

        //$db->Execute($sql);
        } else {
            //Bookx v095 tables could exist, but no configuration was imported or was lost.
            //Can't determinate if already install.
            $msg = "<p><strong class=\"text-danger\">Could not determinate if Bookx is properly installed</strong> since no version was found (Assuming installed version is 090)<br>. This message could be due to missing bookx configuration fields, such as Bookx Version identifier.<br>An Update and reset from 090 will be perform.</p>";
            $msg .= '<p>Please confirm that you have BookX v09 installed: <a id="choose" href="' . zen_href_link(FILENAME_BOOKX_TOOLS, 'action=bookx_install_options&confirm_v09=1') . ' "class="btn btn-primary btn-xs">' . FILENAME_BOOKX_TOOLS . '</a></p>';
            $messageStack->add($msg, 'info');
        }
    } else {
        // we have a version. compare versions
            /*
            $messageStack->add('info', 'info');
            if ($bookx_installed_version < $bookx_module_version && (!strpos($_SERVER['PHP_SELF'], FILENAME_BOOKX_TOOLS))) {
                $msg = 'Proceed to <a href="' . zen_href_link(FILENAME_BOOKX_TOOLS, 'action=bookx_install_options') . ' "class="btn btn-primary btn-xs">' . FILENAME_BOOKX_TOOLS . '</a> to update Bookx ' . $bookx_installed_version . ' to v' . $bookx_module_version . '</div>';
                $messageStack->add($msg, 'info');
            }
             */
    }
} else {

    // check if any bookx table is present. If only bookx tables were imported, it's a update
    if (isset($_GET['confirm_update']) == true) {
        $_SESSION['bookx_install'] = 'do_reset';
        $bookx_already_installed = true;
        $bookx_installed_version = '0.9'; // some first BETA files had no version info
        $sql = 'REPLACE INTO ' . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function)
	    	VALUES ('BookX Version', 'BOOKX_VERSION', '0.9', 'BookX Version is stored but not editable', 0, 10000, NOW(), NOW(), NULL, NULL)";
        $db->Execute($sql);

        $sql = "REPLACE INTO " . TABLE_PRODUCT_TYPES . " (type_name, type_handler, type_master_type, allow_add_to_cart, date_added, last_modified)
	                      VALUES ( 'Products - Bookx', 'product_bookx', 1,  'Y', now(), now())";
        $db->Execute($sql);

        zen_redirect(FILENAME_BOOKX_TOOLS . '.php?action=bookx_install_options');
    } else {
        if ($bookx_already_installed && !$bookx_installed_version) {
            $msg .= '<p>Found BookX tables in data base but no defined BookX Product Type Id. If your intent is to update, but haven\'t import BookX Zen Cart configuration table, we can try to update. However probably you will have also to update your product table to the new product type ID. First, confirm BookX that this is a update : <a href="' . zen_href_link(FILENAME_BOOKX_TOOLS, 'action=bookx_install_options&confirm_update=1') . ' "class="btn btn-primary btn-xs">' . FILENAME_BOOKX_TOOLS . '</a></p>';
            $messageStack->add($msg, 'alert');
            $install = false;
        } else {
            $messageStack->add('New BookX Installation.', 'info');
        }
    }
}

if (isset($_GET['action']) &&
    ('bookx_remove' == $_GET['action']) ||
    ('bookx_install' == $_GET['action']) ||
    ('bookx_update' == $_GET['action']) ||
    ('bookx_reset_to_defaults' == $_GET['action']) ||
    ('bookx_install_options' == $_GET['action'])
) {
    require_once BOOKX_EXTRA_DATA_FILES_FOLDER . 'installers/bookx_install_v1.php';
}

if (BOOKX_DISPLAY_GIT_RELEASES == true) {
    /**
     * Get git releases info from json file
     */
    $jsonStr = file_get_contents(BOOKX_EXTRA_DATA_FILES_FOLDER . 'plugin_check.json');
    $objGit = json_decode($jsonStr);

    if (isset($_GET) && 'check_git_releases' == $_GET['action']) {
        $messageStack->add_session(bookx_update_plugin_release(), 'success');
        $_SESSION['bookx']['checked_date'] = true;
        zen_redirect(FILENAME_BOOKX_TOOLS . '.php');
    }

    $ep4bookx_exists = false;
    //pr(bookx_update_plugin_release());
    if ($action == 'update_git_repositories') {
        /**
         * Maybe the file was corrupted or no installed version is available.
         * Check installed versions and update
         * Note: Until now, EP4 doesn't widely announce is version.
         */
        if ($ep4bookx_exists) {
            $objGit->ep4bookx->installed = '';
            $objGit->ep4bookx->url = $_POST['ep4bookx'];
        }
        if ($ep4_exists) {
            $objGit->ep4->installed = EP4BOOKX_VERSION;
            $objGit->ep4->url = $_POST['ep4'];
        }
        $json = json_encode($objGit, JSON_PRETTY_PRINT);
        file_put_contents(BOOKX_EXTRA_DATA_FILES . '/plugin_check.json', $json);
        //EP4BOOKX_VERSION
        $messageStack->add_session(bookx_update_plugin_release(), 'info');
        zen_redirect(FILENAME_BOOKX_TOOLS . '.php');
    }
}

if (isset($_GET) && 'bookx_check_missing_product_relations' == $_GET['action']) {
    $messageStack->add_session(bookx_check_missing_product_relations(
        [
            TABLE_PRODUCTS => TABLE_PRODUCTS_TO_CATEGORIES,
            TABLE_PRODUCT_BOOKX_EXTRA => TABLE_PRODUCTS,
            TABLE_PRODUCT_BOOKX_EXTRA_DESCRIPTION => TABLE_PRODUCT_BOOKX_EXTRA,
            TABLE_PRODUCT_BOOKX_AUTHORS_TO_PRODUCTS => TABLE_PRODUCTS,
            TABLE_PRODUCT_BOOKX_GENRES_TO_PRODUCTS => TABLE_PRODUCTS,
            TABLE_PRODUCT_BOOKX_FAMILIES_TO_PRODUCTS => TABLE_PRODUCT_BOOKX_EXTRA
        ],
        'products_id',
        true
    ), 'success');

    zen_redirect(FILENAME_BOOKX_TOOLS . '.php');
}

?>

<!doctype html>
<html <?php echo HTML_PARAMS; ?>>

<head>
    <meta charset="<?php echo CHARSET; ?>">
    <title><?php echo TITLE; ?>
    </title>
    <link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
    <link rel="stylesheet" type="text/css" href="includes/extra_datafiles/bookx/libs/prism.css">
    <link rel="stylesheet" type="text/css" href="includes/css/bookx_admin_stylesheet.css">
    <link rel="stylesheet" type="text/css" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
    <script src="includes/general.js"></script>
</head>

<body id="bookxTools" data-spy="scroll" data-target="#myScrollspy" data-offset="20">

    <!-- header //-->
    <?php require DIR_WS_INCLUDES . 'header.php'; ?>
    <!-- header_eof //-->

    <!-- body //-->
    <div class="container">
        <h1><?php echo HEADING_TITLE_BOOKX; ?>
        </h1>

        <?php
if ($action == 'bookx_install_options') {

    // call file
    $update_msg = '';

    // Loads a html block for install options
    if (isset($_GET['action']) && ('bookx_install_options' == $_GET['action']) &&
        ($bookx_already_installed !== false)) {
        require_once BOOKX_EXTRA_DATA_FILES_FOLDER . 'installers/bookx_install_include_options.php';
    }
    //Ends the html block for install options
} else { ?>
        <div class="row">
            <nav class="col-sm-3" id="myScrollspy">
                <ul class="nav nav-pills nav-stacked" data-spy="affix" data-offset-top="220" data-offset-bottom="100">
                    <li><a href="#sectionManagement">Bookx Management</a></li>
                    <li><a href="#sectionConvertProduct">Convert Tools</a></li>
                    <li><a href="#sectionGitReleases">Git Releases Check</a></li>
                    <li><a href="#sectionMissingRelations">Fix Missing Relations</a></li>
                    <li><a href="#section4">Section 4</a></li>
                    <li><a href="#sectionDocs">Documentation</a></li>
                    <!--
                    <li class="dropdown">
                      <a class="dropdown-toggle" data-toggle="dropdown" href="#">Docs<span class="caret"></span></a>
                      <ul class="dropdown-menu">
                        <li><a href="#section41">Section 4-1</a></li>
                        <li><a href="#section42">Section 4-2</a></li>
                      </ul>
                    </li>
                    <!-- //-->
                </ul>
            </nav>
            <div class="col-sm-9">
                <div style="margin-bottom:.5rem;">
                    <?php
                if (BOOKX_DISPLAY_GIT_RELEASES == true) { //@todo
                    $color = "success";
                    foreach ($objGit as $key => $value) {
                        if (($key !== 'last_check_date') && (!empty($objGit->{$key}->installed))) {
                            $check = $objGit->{$key}->last_release;
                            if (($objGit->{$key}->installed !== $objGit->{$key}->last_release)) {
                                $color = "warning";
                                $check .= '(New release)';
                                echo '<div class="wlabel"><a href="' . $objGit->{$key}->html_url . '"><span>' . $key . '</span><span class="label label-' . $color . '">' . $check . '</span></div></a>';
                            } else {
                                echo '<div class="wlabel"><span>' . $key . '</span><span class="label label-' . $color . '">' . $check . '</span></div>';
                            }
                        }
                    }
                } ?>
                </div>

                <?php
                /**
                 * @example section tpl_panels:
                 * <?php tpl_panel('open','section_id', 'Title');?>
                 *  ... content
                 * <?php tpl_panel('close');?>
                 */
                ?>
                <!-- Close First Panel -->
                <?php tpl_panel('open', 'sectionManagement', 'Bookx Management'); ?>
                <?php

                if ($bookx_already_installed == true) {
                    echo '<p>' . TEXT_BOOKX_STATUS_INSTALLED . '<span class="label label-success"> v' . $bookx_installed_version . '</span></p>';
                } else {
                    echo TEXT_BOOKX_STATUS_NOT_INSTALLED . '<br />';
                } ?>
                <ul class="list-group">
                    <li class="list-group-item">
                        <p><?php echo BOOKX_LINK_RESET_DESC; ?>
                        </p>
                        <a href="<?php echo zen_href_link(FILENAME_BOOKX_TOOLS, 'action=bookx_reset_to_defaults'); ?>"
                            class="btn btn-danger btn-sm">
                            <?php echo BOOKX_LINK_RESET; ?>
                        </a>
                    </li>
                    <?php
                if (!$bookx_already_installed) {
                    echo '<li class="list-group-item"><a href="' . zen_href_link(FILENAME_BOOKX_TOOLS, 'action=bookx_install') . '">' . BOOKX_LINK_INSTALL . '</a></li>';
                }
    if ($bookx_installed_version < $bookx_module_version) {
        echo '<li class="list-group-item"><a href="' . zen_href_link(FILENAME_BOOKX_TOOLS, 'action=bookx_install_options') . '">' . BOOKX_LINK_UPDATE . '</a></li>';
    }
    /*
     * Remove / COnfirm Remove
     */
    if ($action == 'bookx_confirm_remove') {
        ?>

                    <div class="form-group well warning">
                        <p>You are about to uninstall Bookx from your Data Base. Choose you options bellow.</p>
                        <?php
                    echo zen_draw_form('remove_form', 'index.php', '', 'get', 'class="class="form-horizontal""');
        echo zen_draw_hidden_field('action', 'bookx_remove'); ?>
                        <div class="radio">
                            <label>
                                <?php echo zen_draw_radio_field('convert_bookx_products', '1', '1') . '&nbsp;' . TEXT_CONVERT_BOOKX_PRODUCTS; ?>
                            </label>
                        </div>
                        <div class="radio">
                            <label>
                                <?php echo zen_draw_radio_field('convert_bookx_products', '0', '0') . '&nbsp;' . TEXT_DELETE_BOOKX_PRODUCTS; ?>
                            </label>
                        </div>
                        <?php echo BOOKX_CONFIRM_REMOVE . '&nbsp;'; ?>
                        <button type="submit" class="btn btn-danger btn-sm float-right">Submit</button>
                        <a href="<?php echo zen_href_link(FILENAME_BOOKX_TOOLS); ?>"
                            class="btn btn-default btn-sm float-right">Cancel</a>
                        </form>
                    </div>
                    <?php
    } else {
        ?>
                    <li class="list-group-item">
                        <p><?php echo BOOKX_LINK_REMOVE_DESC; ?>
                        </p>
                        <a href="<?php echo zen_href_link(FILENAME_BOOKX_TOOLS, 'action=bookx_confirm_remove'); ?>"
                            class="btn btn-danger btn-sm"><?php echo BOOKX_LINK_REMOVE; ?></a>
                    </li>
                    <?php
    } ?>
                </ul>
                <?php tpl_panel('close'); ?>

                <?php
                            /** open panel */
                            tpl_panel('open', 'sectionConvertProduct', 'Convert Tools');

    $product_selection_submitted = false;
    $choose_products_to_convert_from = false;
    $choose_products_to_convert_to = false;
    if (isset($_POST['choose_products_to_convert_from'])) {
        if ('1' == $_POST['choose_products_to_convert_from']) {
            $choose_products_to_convert_from = true;
            if (isset($_POST['products_to_convert_from']) &&
                                        is_array($_POST['products_to_convert_from']) &&
                                        0 < count($_POST['products_to_convert_from'])) {
                $product_selection_submitted = true;
            }
        }
    }

    if (isset($_POST['choose_products_to_convert_to'])) {
        if ('1' == $_POST['choose_products_to_convert_to']) {
            $choose_products_to_convert_to = true;
            if (isset($_POST['products_to_convert_to']) &&
                                        is_array($_POST['products_to_convert_to']) &&
                                        0 < count($_POST['products_to_convert_to'])) {
                $product_selection_submitted = true;
            }
        }
    }

    if ('bookx_manage_product_migration' == $action ||
                                ('bookx_confirm_product_migration' == $action &&
                                ($choose_products_to_convert_from || $choose_products_to_convert_to) &&
                                !$product_selection_submitted)) {
        global $currencies;

        $sql = "SELECT type_id, type_name FROM " . TABLE_PRODUCT_TYPES;
        $product_types = $db->Execute($sql);
        while (!$product_types->EOF) {
            $type_array[] = ['id' => $product_types->fields['type_id'], 'text' => $product_types->fields['type_name']];
            $type_names[(int) $product_types->fields['type_id']] = $product_types->fields['type_name'];
            $product_types->MoveNext();
        }

        $select_string_from = false;
        if ($choose_products_to_convert_from && isset($_POST['convert_from_type']) &&
                                    '' != $_POST['convert_from_type'] &&
                                    null != $bookx_ptypeID) {
            $select_string_from = '<br /><select name="products_to_convert_from[]" size="10" multiple="multiple" class="form-control">';

            $products = $db->Execute("SELECT p.products_id, products_name, p.products_price, p.products_model
				                                FROM " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd
				                                where p.products_id = pd.products_id
				                                AND pd.language_id = '" . (int) $_SESSION['languages_id'] . "'
				                                AND p.products_type = '" . (int) $_POST['convert_from_type'] . "'
				                                ORDER BY products_name");

            $product_array = [];

            while (!$products->EOF) {
                $display_price = $products->fields['products_price']; // zen_get_products_display_price($product['products_id']);
                $select_string_from .= '<option value="' . $products->fields['products_id'] . '">';
                $select_string_from .= $products->fields['products_name'] . ' [' . $products->fields['products_model'] . '] (' . $display_price . ') - ID# ' . $products->fields['products_id'] . '</option>';

                $products->MoveNext();
            }

            $select_string_from .= '</select>';
        }

        $select_string_to = false;
        if ($choose_products_to_convert_to && isset($_POST['convert_to_type']) &&
                                    '' != $_POST['convert_to_type'] && null != $bookx_ptypeID) {
            $select_string_to = '<br /><br /><select name="products_to_convert_to[]" size="10" multiple="multiple">';

            $products = $db->Execute("SELECT p.products_id, products_name, p.products_price, p.products_model
				                                FROM " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd
				                                where p.products_id = pd.products_id
				                                AND pd.language_id = '" . (int) $_SESSION['languages_id'] . "'
				                                AND p.products_type = '" . (int) $bookx_ptypeID . "'
				                                ORDER BY products_name");

            $product_array = [];

            while (!$products->EOF) {
                $display_price = $products->fields['products_price']; // zen_get_products_display_price($product['products_id']);
                $select_string_to .= '<option value="' . $products->fields['products_id'] . '">';
                $select_string_to .= $products->fields['products_name'] . ' [' . $products->fields['products_model'] . '] (' . $display_price . ') - ID# ' . $products->fields['products_id'] . '</option>';

                $products->MoveNext();
            }
            $select_string_to .= '</select>';
        }

        $form_action = (!$_POST['choose_products_to_convert_from']) ? '#sectionConvertProduct' : '';

        echo '<div class="form-group well">';
        echo zen_draw_form('migration_to_bookx', FILENAME_BOOKX_TOOLS, 'action=bookx_confirm_product_migration' . $form_action
                                    . '', 'post', 'enctype="multipart/form-data"');
        echo '<p>' . BOOKX_OPTION_IMPORT . '</p>';
        echo zen_draw_label(BOOKX_SELECT_PRODUCT_TYPE_SOURCE_FOR_MIGRATION, 'convert_from_type');
        if (isset($_POST['convert_from_type']) && '' != $_POST['convert_from_type']) {
            echo '<strong>' . $type_names[(int) $_POST['convert_from_type']] . '</strong>';
            echo zen_draw_hidden_field('convert_from_type', (int) $_POST['convert_from_type']);
        } else {
            echo zen_draw_pull_down_menu('convert_from_type', $type_array, '', 'class="form-control"');
        }
        echo '<div class="radio"><label>' . zen_draw_radio_field('choose_products_to_convert_from', '0', ($choose_products_to_convert_from ? '0' : '1')) . '&nbsp;' . BOOKX_OPTION_CONVERT_ALL_PRODUCTS . '</label></div>';

        echo '<div class="radio"><label>' . zen_draw_radio_field('choose_products_to_convert_from', '1', ($choose_products_to_convert_from ? '1' : '0')) . '&nbsp;' . BOOKX_OPTION_SELECT_PRODUCTS_TO_CONVERT . '</label></div>';
        echo('' != $select_string_from ? $select_string_from : '');
        echo '<button type="submit" class="btn btn-primary btn-sm float-right">Submit</button>';
        echo '</form></div><!-- eof form migration_to_bookx -->';
        /**
         *
         */
        echo '<div class="form-group well">';
        echo zen_draw_form('migration_from_bookx', FILENAME_BOOKX_TOOLS, 'action=bookx_confirm_product_migration', 'post', 'enctype="multipart/form-data"');
        echo '<p>' . BOOKX_OPTION_EXPORT . '</p>';
        echo zen_draw_label(BOOKX_SELECT_PRODUCT_TYPE_DESTINATION_FOR_MIGRATION, 'convert_to_type');
        if (isset($_POST['convert_to_type']) && '' != $_POST['convert_to_type']) {
            echo '<strong>' . $type_names[(int) $_POST['convert_to_type']] . '</strong>';
            echo zen_draw_hidden_field('convert_to_type', (int) $_POST['convert_to_type']);
        } else {
            echo zen_draw_pull_down_menu('convert_to_type', $type_array, '', 'class="form-control"');
        }
        echo '<div class="radio"><label>' . zen_draw_radio_field('choose_products_to_convert_to', '0', ($choose_products_to_convert_to ? '0' : '1')) . '&nbsp;' . BOOKX_OPTION_CONVERT_ALL_PRODUCTS . '</label></div>';
        echo '<div class="radio"><label>' . zen_draw_radio_field('choose_products_to_convert_to', '1', ($choose_products_to_convert_to ? '1' : '0')) . '&nbsp;' . BOOKX_OPTION_SELECT_PRODUCTS_TO_CONVERT . '</label></div>';
        echo('' != $select_string_to ? $select_string_to : '');
        echo '<button type="submit" class="btn btn-primary btn-sm float-right">Submit</button>';
        echo '</form></div><!-- eof form migration_to_bookx -->';
        echo '<a href="' . zen_href_link(FILENAME_BOOKX_TOOLS) . '" class="btn btn-default btn-sm float-right">Cancel</a>';
    } elseif ('bookx_confirm_product_migration' == $action) {
        switch (true) {
                                    case ($choose_products_to_convert_from && $product_selection_submitted && null != $bookx_ptypeID): // convert some selected products from another article type to bookx
                                        $selected_products = implode(',', $_POST['products_to_convert_from']);
                                        $products_to_convert = $db->Execute('SELECT products_id FROM ' . TABLE_PRODUCTS . ' WHERE products_id IN (' . $selected_products . ') AND products_type = "' . (int) $_POST['convert_from_type'] . '"');
                                        while (!$products_to_convert->EOF) {
                                            bookx_convert_product_to_bookx_type($products_to_convert->fields['products_id']);

                                            $products_to_convert->MoveNext();
                                        }
                                        unset($_POST['products_to_convert_from']);

                                        break;

                                    case ($choose_products_to_convert_to && $product_selection_submitted && isset($_POST['convert_to_type']) && '' != $_POST['convert_to_type'] && null != $bookx_ptypeID): // convert some selected products from bookx to another article type
                                        $selected_products = implode(',', $_POST['products_to_convert_to']);
                                        $products_to_convert = $db->Execute('SELECT products_id FROM ' . TABLE_PRODUCTS . ' WHERE products_id IN (' . $selected_products . ') AND products_type = "' . (int) $bookx_ptypeID . '"');
                                        while (!$products_to_convert->EOF) {
                                            bookx_convert_product_from_bookx_to_type($products_to_convert->fields['products_id'], (int) $_POST['convert_to_type']);
                                            $products_to_convert->MoveNext();
                                        }
                                        $messageStack->add('Converted ' . $products_to_convert->Count() . 'products', 'success');
                                        unset($_POST);
                                        break;

                                    case (!$choose_products_to_convert_from && isset($_POST['convert_from_type']) && '' != $_POST['convert_from_type'] && null != $bookx_ptypeID): // convert all products from another article type to bookx
                                        $products_to_convert = $db->Execute('SELECT products_id FROM ' . TABLE_PRODUCTS . ' WHERE products_type = "' . (int) $_POST['convert_from_type'] . '"');
                                        while (!$products_to_convert->EOF) {
                                            bookx_convert_product_to_bookx_type($products_to_convert->fields['products_id']);
                                            $products_to_convert->MoveNext();
                                        }
                                        $messageStack->add('Converted ' . $products_to_convert->Count() . 'products', 'success');
                                        unset($_POST);
                                        break;

                                    case (!$choose_products_to_convert_to && isset($_POST['convert_to_type']) && '' != $_POST['convert_to_type'] && null != $bookx_ptypeID): // convert some products from another article type to bookx
                                        $products_to_convert = $db->Execute('SELECT products_id FROM ' . TABLE_PRODUCTS . ' WHERE products_type = "' . (int) $bookx_ptypeID . '"');
                                        while (!$products_to_convert->EOF) {
                                            bookx_convert_product_from_bookx_to_type($products_to_convert->fields['products_id'], (int) $_POST['convert_to_type']);
                                            $products_to_convert->MoveNext();
                                        }
                                        $messageStack->add('Converted ' . $products_to_convert->Count() . 'products', 'success');
                                        zen_redirect(FILENAME_BOOKX_TOOLS . '.php');
                                        unset($_POST);
                                        break;
                                }
    } else {
        if ($bookx_already_installed) {
            echo '<p>' . BOOKX_LINK_MANAGE_PRODUCT_MIGRATION_DESC . '</p><a href="' . zen_href_link(FILENAME_BOOKX_TOOLS, 'action=bookx_manage_product_migration#sectionConvertProduct') . '" class="btn btn-primary btn-sm">' . BOOKX_LINK_MANAGE_PRODUCT_MIGRATION . '</a>';
        } else {
            echo TEXT_BOOKX_STATUS_NOT_INSTALLED;
        }
    }
    tpl_panel('close'); ?>

                <!-- Close Second Panel -->

                <?php tpl_panel('open', 'sectionGitReleases', 'Git Releases Check'); ?>
                <p>Info: <?php echo $objGit->ep4->installed; ?> You
                    can trace your git fork updates. To do so, update git api url.
                    <br />Note that this is not mandatory. You can just use the Zen Cart download section.</p>
                <?php if ($action == 'update_git_repo_url') {
        ?>
                <?php
                            //function zen_draw_form($name, $action, $parameters = '', $method = 'post', $params = '', $usessl = 'false') {
                            echo zen_draw_form('git_repositories', FILENAME_BOOKX_TOOLS, 'action=update_git_repositories', 'post'); ?>
                <div class="form-group">
                    <?php
                            echo zen_draw_label('Url for git Bookx', 'bookx');
        echo zen_draw_input_field('bookx', $objGit->bookx->url, 'class="form-control" placeholder="' . $objGit->bookx->url . '" required', true, 'url'); ?>
                </div>
                <div class="form-group">
                    <?php
                            //zen_draw_input_field($name, $value = '~*~*#', $parameters = '', $required = false, $type = 'text', $reinsert_value = true) {
                            echo zen_draw_label('Url for git EP4', 'ep4');
        echo zen_draw_input_field('ep4', '', 'class="form-control" placeholder="' . $objGit->ep4->url . '"', true, 'url'); ?>
                </div>
                <div class="form-group">
                    <?php
                                echo zen_draw_label('Url for git EP4Bookx', 'ep4bookx');
        echo zen_draw_input_field('ep4bookx', '', 'class="form-control" placeholder="' . $objGit->ep4bookx->url . '"', true, 'url'); ?>
                </div>
                <button type="submit" class="btn btn-primary btn-sm float-right">Submit</button>
                <a href="<?php echo zen_href_link(FILENAME_BOOKX_TOOLS); ?>"
                    class="btn btn-default btn-sm">Cancel</a>
                </form>

                <?php
    }

    if (($action !== 'check_git_releases') && ($action !== 'update_git_repo_url')) {
        ?>
                <div>
                    <a href="<?php echo zen_href_link(FILENAME_BOOKX_TOOLS, 'action=check_git_releases'); ?>"
                        class="btn btn-default btn-sm">Check Updates</a>
                    <a href="<?php echo zen_href_link(FILENAME_BOOKX_TOOLS, 'action=update_git_repo_url#sectionGitReleases'); ?>"
                        class="btn btn-default btn-sm">Update Git Api Url</a></div>
                <?php
    } ?>
                <?php tpl_panel('close'); ?>

                <?php tpl_panel('open', 'sectionMissingRelations', 'Fix Missing Relations'); ?>
                <p>Find and fix missing data base relations</p>
                <a href="<?php echo zen_href_link(FILENAME_BOOKX_TOOLS, 'action=bookx_check_missing_product_relations'); ?>"
                    class="btn btn-default btn-sm">Find and Fix</a>
                <?php tpl_panel('close'); ?>

                <?php tpl_panel('open', 'section4', 'section 4'); ?>
                <p>Try to scroll this section and look at the navigation list while scrolling!</p>
                <?php tpl_panel('close'); ?>

                <?php tpl_panel('open', 'sectionDocs', 'Documentation'); ?>

                <?php if ($action == 'loadDocumentation') {
        echo '<div class="docs">';
        require_once BOOKX_EXTRA_DATA_FILES_FOLDER . 'libs/Parsedown.php';
        $parsedown = new Parsedown();
        $text = file_get_contents(BOOKX_EXTRA_DATA_FILES_FOLDER . 'Documentation.md');
        echo Parsedown::instance()
                                ->setUrlsLinked(true)
                                ->text($text);
        echo '</div>';
    } else {
        ?>
                <a href="<?php echo zen_href_link(FILENAME_BOOKX_TOOLS, 'action=loadDocumentation#sectionDocs'); ?>"
                    class="btn btn-default btn-sm">Load Documentation</a>
                <?php
    } ?>

                <?php tpl_panel('close'); ?>
                <?php
} // ends bookx tools panels?>
            </div><!-- right div_eof //-->
        </div> <!-- row_eof //-->
    </div><!-- container_eof //-->

    <!-- footer //-->
    <?php require DIR_WS_INCLUDES . 'footer.php'; ?>
    <!-- footer_eof //-->
    <script src="includes/extra_datafiles/bookx/libs/prism.js"></script>
    <script>
        $(document).ready(function() {

            $('.container ul.nav li a').bind('click', function(e) {
                e.preventDefault();
                $('html,body').animate({
                    scrollTop: $(this.hash).offset().top
                });
            });

            /**    
             $(document.body).on('change',"#confMetaTags",function (e) { 
             var optVal= $("#confMetaTags option:selected").val();
             if (optVal == 'enable') {
             console.log(optVal);
             //$('[data-toggle="collapse"]').removeClass('hidden');
             $('#metatags').removeClass('hidden');
             //$('[data-toggle="collapse"]').removeClass('hidden');
             }
             if (optVal == 'disable') {
             $('#metatags').addClass('hidden');
             }
             });
             */
        });
    </script>

</body>

</html>
<?php
/*
 * Page Layout absolutely based on:
 * @see https://www.w3schools.com/bootstrap/bootstrap_affix.asp
 * and bootstrap panels
 */

function tpl_panel($param, $section_id = null, $title = null)
{
    $title = ($title) ? $title : 'Bookx Panel';
    if ($param == 'open' && $section_id !== '') {
        $param = '<!-- open panel ' . $section_id . ' //-->
            <div id="' . $section_id . '" class="panel panel-default">
            <div class="panel-heading">
            <h3 class="panel-title">' . $title . '</h3>
            </div>
            <div class="panel-body">';
        echo $param;
    } elseif ($param == 'close') {
        $param = '</div>
            </div><!-- close panel ' . $section_id . ' //-->';
        echo $param;
    }
    return;
}

require DIR_WS_INCLUDES . 'application_bottom.php';
