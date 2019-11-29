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
 * @version BookX V 1.0.0
 * @version $Id: [ZC INSTALLATION]/[ADMIN]/bookx_tools.php 2019-01-05 mesnitu $
 */

require_once('includes/application_top.php');

$action = (isset($_GET['action']) ? $_GET['action'] : '');

/**
 * Checks Install. 
 * 1 - Fresh install - Detects no pType, no Version, no DataBases.
 * 2 - update - Detects No version and ptype (v09) OR Version and pType found.
 * 3 - update - Detects DataBases and no pType, no Version.
 * 
 */
$sql = "SELECT type_id FROM " . TABLE_PRODUCT_TYPES . " WHERE type_handler = 'product_bookx'";
$product_type = $db->Execute($sql);

if (0 < $product_type->RecordCount()) {
    // we have a type. Assuming previous installation
    $already_installed = true;
    // set ptype id
    $bookx_pType_id = $product_type->fields['type_id'];

    if ($action == 'bookx_install_options') { 
        // if zc version < 56 display files warning
        if (str_replace('.', '', EXPECTED_DATABASE_VERSION_MINOR) < "56") {
            $msg = "Zencart 156 not found. This Bookx Version as not been fully tested with v" . PROJECT_VERSION_MINOR . ". While it can update database tables, some files and features were added. You have to compare files. Tested first in a dev enviroment";
            $messageStack->add($msg, 'caution');
        }
        
        // find version
        if (empty($installed_version)) {
            // Assuming that v09 is installed. Display a warning message with a confirm action
            if (isset($_GET['confirm_v09']) == true) {
                // if is confirmed insert version
                $messageStack->add('v090 confirmed', 'success');
                $installed_version = '0.9'; // some first BETA files had no version info
                $sql = 'REPLACE INTO ' . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function)
	    	VALUES ('BookX Version', 'BOOKX_VERSION', '0.9', 'BookX Version is stored but not editable', 0, 10000, NOW(), NOW(), NULL, NULL)";
                
                //$db->Execute($sql); 
            } else {
                //Bookx v095 tables could exist, but no configuration was imported or was lost.
                //Can't determinate if already install.
                $msg = "<p>A product type for BookX was found but <strong class=\"text-danger\">Could not determinate if Bookx is properly installed</strong> since no version was found (Assuming installed version 090)<br>. This message could be due to missing bookx configuration fields, such as Bookx Version identifier.<br>An Update and reset.</p>";
                $msg .= '<p>Please confirm that you have BookX v09 installed: <a href="' . zen_href_link(FILENAME_BOOKX_TOOLS, 'action=bookx_install_options&confirm_v09=1') . ' "class="btn btn-primary btn-xs">' . FILENAME_BOOKX_TOOLS . '</a></p>';
                $messageStack->add($msg, 'info');
            }
        } else {
            
            // we have a verion. compare versions
            /*
            $messageStack->add('info', 'info');
            if ($installed_version < $bookx_version && (!strpos($_SERVER['PHP_SELF'], FILENAME_BOOKX_TOOLS))) {
                $msg = 'Proceed to <a href="' . zen_href_link(FILENAME_BOOKX_TOOLS, 'action=bookx_install_options') . ' "class="btn btn-primary btn-xs">' . FILENAME_BOOKX_TOOLS . '</a> to update Bookx ' . $installed_version . ' to v' . $bookx_version . '</div>';
                $messageStack->add($msg, 'info');
            }
             */
        }
    }
} else {
    
    // check if any bookx table is present. If only bookx tables were imported, it's a update
    if (isset($_GET['confirm_update']) == true) {
        
        $_SESSION['bookx_install'] = 'do_reset';
        $already_installed = true;
        $installed_version = '0.9'; // some first BETA files had no version info
        $sql = 'REPLACE INTO ' . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function)
	    	VALUES ('BookX Version', 'BOOKX_VERSION', '0.9', 'BookX Version is stored but not editable', 0, 10000, NOW(), NOW(), NULL, NULL)";
        $db->Execute($sql);
        
        $sql = "REPLACE INTO " . TABLE_PRODUCT_TYPES . " (type_name, type_handler, type_master_type, allow_add_to_cart, date_added, last_modified)
	                      VALUES ( 'Products - Bookx', 'product_bookx', 1,  'Y', now(), now())";
	    $db->Execute($sql);
	        
        zen_redirect(FILENAME_BOOKX_TOOLS . '.php?action=bookx_install_options');
        
    
    } else {
    $already_installed = false;
    $bookx_db_tables = [
        TABLE_PRODUCT_BOOKX_AUTHORS,
        TABLE_PRODUCT_BOOKX_AUTHORS_DESCRIPTION,
        TABLE_PRODUCT_BOOKX_AUTHORS_TO_PRODUCTS,
        TABLE_PRODUCT_BOOKX_AUTHOR_TYPES,
        TABLE_PRODUCT_BOOKX_AUTHOR_TYPES_DESCRIPTION,
        TABLE_PRODUCT_BOOKX_BINDING,
        TABLE_PRODUCT_BOOKX_BINDING_DESCRIPTION,
        TABLE_PRODUCT_BOOKX_CONDITIONS,
        TABLE_PRODUCT_BOOKX_CONDITIONS_DESCRIPTION,
        TABLE_PRODUCT_BOOKX_EXTRA,
        TABLE_PRODUCT_BOOKX_EXTRA_DESCRIPTION,
        TABLE_PRODUCT_BOOKX_GENRES,
        TABLE_PRODUCT_BOOKX_GENRES_DESCRIPTION,
        TABLE_PRODUCT_BOOKX_GENRES_TO_PRODUCTS,
        TABLE_PRODUCT_BOOKX_IMPRINTS,
        TABLE_PRODUCT_BOOKX_IMPRINTS_DESCRIPTION,
        TABLE_PRODUCT_BOOKX_PRINTING,
        TABLE_PRODUCT_BOOKX_PRINTING_DESCRIPTION,
        TABLE_PRODUCT_BOOKX_PUBLISHERS,
        TABLE_PRODUCT_BOOKX_PUBLISHERS_DESCRIPTION,
        TABLE_PRODUCT_BOOKX_SERIES,
        TABLE_PRODUCT_BOOKX_SERIES_DESCRIPTION,
        TABLE_PRODUCT_BOOKX_FAMILIES,
        TABLE_PRODUCT_BOOKX_FAMILIES_TO_PRODUCTS,
        TABLE_PRODUCT_BOOKX_SEARCH
    ];
    
    $bookx_error = false;
    foreach ($bookx_db_tables as $value) {

        $sql = "SHOW TABLES LIKE '" . $value . "'";
        $res = $db->Execute($sql);

        if (!$res->EOF) {
            $bookx_error = true;
            break;
        }
    }
    
    if ($bookx_error) {
        $msg .= '<p>Found BookX tables in data base but no defined BookX Product Type Id. If your intent is to update, but haven\'t import BookX zencart configuration table, we can try to update. However probably you will have also to update your product table to the new product type ID. First, confirm BookX that this is a update : <a href="' . zen_href_link(FILENAME_BOOKX_TOOLS, 'action=bookx_install_options&confirm_update=1') . ' "class="btn btn-primary btn-xs">' . FILENAME_BOOKX_TOOLS . '</a></p>';
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
    require_once BOOKX_EXTRA_DATAFILES_FOLDER . 'installers/bookx_install_v1.php';
}

if (BOOKX_DISPLAY_GIT_RELEASES == true) {
    /**
     * Get git releases info from json file
     */
    $jsonStr = file_get_contents(BOOKX_EXTRA_DATAFILES_FOLDER . 'plugin_check.json');
    $objGit = json_decode($jsonStr);

    if (isset($_GET) && 'check_git_releases' == $_GET['action']) {
        $messageStack->add_session(bookx_update_plugin_release(), 'success');
        $_SESSION['bookx']['checked_date'] = true;
        zen_redirect(FILENAME_BOOKX_TOOLS . '.php');
    }

    //define('EP4BOOKX_VERSION', '0.9.9');
    $ep4bookx_exists = false;
    //pr(bookx_update_plugin_release());
    if ($action == 'update_git_repositories') {

        /**
         * Maybe the file was corrupted or no installed version is available. 
         * Check installed versions and update
         * Note: Until now, EP4 doesn't widely annouce is version. 
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
        file_put_contents(BOOKX_EXTRA_DATAFILES . '/plugin_check.json', $json);
        //EP4BOOKX_VERSION
        $messageStack->add_session(bookx_update_plugin_release(), 'info');
        zen_redirect(FILENAME_BOOKX_TOOLS . '.php');
    }
}

if (isset($_GET) && 'bookx_check_missing_product_relations' == $_GET['action']) {

    $messageStack->add_session(bookx_check_missing_product_relations(
        array(
            TABLE_PRODUCTS => TABLE_PRODUCTS_TO_CATEGORIES,
            TABLE_PRODUCT_BOOKX_EXTRA => TABLE_PRODUCTS,
            TABLE_PRODUCT_BOOKX_EXTRA_DESCRIPTION => TABLE_PRODUCT_BOOKX_EXTRA,
            TABLE_PRODUCT_BOOKX_AUTHORS_TO_PRODUCTS => TABLE_PRODUCTS,
            TABLE_PRODUCT_BOOKX_GENRES_TO_PRODUCTS => TABLE_PRODUCTS,
            TABLE_PRODUCT_BOOKX_FAMILIES_TO_PRODUCTS => TABLE_PRODUCT_BOOKX_EXTRA
            ), 'products_id', true), 'success');

    zen_redirect(FILENAME_BOOKX_TOOLS . '.php');
}

?>

<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
    <head>
        <meta charset="<?php echo CHARSET; ?>">
        <title><?php echo TITLE; ?></title>
        <link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
        <link rel="stylesheet" type="text/css" href="includes/extra_datafiles/bookx/libs/prism.css">
        <link rel="stylesheet" type="text/css" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
        <style>
            body {
                position: relative;
            }
            .affix {
                top: 20px;
                z-index: 9999 !important;
            }
            .container .nav-pills>li>a {
                font-weight: 700;
                background: #f5f5f5;
                color: black;
            }
            div.col-sm-9 div.panel {
                min-height: 200px;
            }
            .panel-title {
                font-weight: bold;
            }
            div.panel ul li {
                line-height: 1.5;
            }
            div.panel li span {
                display: inline;
                padding-right: 1rem;
            }
            .btn-sm {
                font-size: 1rem;
            }
            .wlabel {
                display: inline-table;
                border-radius: .25em;
                background: #757575;
                /* font-size: inherit; */
                padding: 0 0 0 .2em;
                color: white;
                font-weight: bold;
                margin-right: .5em;
            }
            .wlabel span {
                padding: .2em .5em;
                display: table-cell;
                vertical-align: middle;
            }
            .wlabel a  {
                display: table-cell;
                text-decoration: none;
                color: inherit;
            }
            .wlabel span:nth-child(2) {
                padding: 0 .5em;
                display: table-cell;
                font-size: inherit;
            }
            .container {
                line-height: 1.6;
            }
            .docs {
                height: 90vh;
                overflow: scroll;
                font-size: 12px;
                background-color: white;
                line-height: 1.45;
                color: #333;

            }
            .docs blockquote {
                font-size: 1.25em;
                background: #f5f5f5;
            }
            .docs p {margin-bottom: 1.25em;}
            .docs h1,.docs h2,.docs h3, .docs h4,.docs h5 {
                margin: 2.75rem 0 1rem;
                font-family: 'Poppins', sans-serif;
                font-weight: 400;
                line-height: 1.15;
            }
            .docs h1 {
                margin-top: 0;
                font-size: 3.052em;
            }
            .docs h2 {font-size: 2.441em;}

            .docs h3 {font-size: 1.953em;}

            .docs h4 {font-size: 1.563em;}

            .docs h5 {font-size: 1.25em;}
            
            @media screen and (max-width: 810px) {
                #section1, #section2, #section3, #section41, #section42  {
                    /*     margin-left: 150px;*/
                }     
            }
        </style>
        <script src="includes/general.js"></script>
        
    </head>
<body id="bookxtolls" data-spy="scroll" data-target="#myScrollspy" data-offset="20">
    
        <!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
        <!-- header_eof //-->

        <!-- body //-->
        <div class="container">
            <h1><?php echo HEADING_TITLE_BOOKX; ?></h1>

<?php
if ($action == 'bookx_install_options') {
    /**
     * Install options: 
     * - CEON SUPPORT
     * - Dinamic Metags ( IF CEON is enable, otherwise, other code is needed
     * - Database collation to mbutf8
     */
    
    /**
     * Loads a html block for install options
     */
    if (isset($_GET['action']) && ('bookx_install_options' == $_GET['action']) && ($install !==false)) {

        $title = ($already_installed) ? 'Update' : 'Install';
        $form_action = ($already_installed) ? 'action=bookx_update' : 'action=bookx_install';
        if ($already_installed && ($installed_version !== $bookx_version)) {
            /**
             * Show a warning about updating version
             */
            $update_msg = "Updating BookX from v$installed_version to v$bookx_version";
        }
        ?>

            <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title">BookX <?php echo $title; ?> Options<span class="glyphicon glyphicon-cog pull-right" aria-hidden="true"></span></h3>
                        </div>
                        <div class="panel-body">
                            <?php
                            echo zen_draw_form('bookx_fresh_install', FILENAME_BOOKX_TOOLS, $form_action, 'post', 'class="form-horizontal"');
                            ?>
                            <?php if ($update_msg) { ?>
                                <div class="alert alert-warning" role="alert"><?php echo $update_msg; ?></div>
                            <?php } ?>
                            <div class="form-group">
                                <?php echo zen_draw_label('Enable Ceon Module', 'bookx_ceon', 'class="col-sm-3 control-label"'); ?>
                                <div class="col-sm-9 col-md-6">
                                    <?php
                                    $ceon_options = array(
                                        array(
                                            'id' => 'enable_ceon',
                                            'text' => 'Enable'
                                        ),
                                        array(
                                            'id' => 'disable_ceon',
                                            'text' => 'Disable'
                                        )
                                    );

                                    if ($detect_ceon == true) {
                                        $msg = "Ceon Module Detected. Would you like to enable bookx support for it?";
                                        $ceon_option = true;
                                    } else {
                                        $msg = 'Ceon Module not Detected. If you intend to use it in the future, you can install Bookx Support. This will add a configuration Value';
                                        $ceon_option = false;
                                    }
                                    ?>
                                    <div class="alert alert-info alert-dismissible" role="alert">
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                        <?php echo $msg; ?>
                                    </div>
                                    <?php echo zen_draw_pull_down_menu('bookx_ceon', $ceon_options, ($ceon_option == true) ? $ceon_options[0]['id'] : $ceon_options[1]['id'], 'class="form-control"'); ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo zen_draw_label('Select DataBase Collations', 'bookx_db_charaset', 'class="col-sm-3 control-label"'); ?>
                                <div class="col-sm-9 col-md-6">
                                    <?php
                                    $db_options = array(
                                        array(
                                            'id' => 'utf8mb4',
                                            'text' => 'Install with utf8mb4'
                                        ),
                                        array(
                                            'id' => 'utf8',
                                            'text' => 'Install with utf8'
                                        )
                                    );

                                    if ($dbCharset == 'utf8mb4') {
                                        $msg = "Data Base Charaset utf8mb4 detect. If you want to use another collation please select";
                                        $db_option = true;
                                    } else {
                                        $msg = "Data Base Charaset ${dbCharsetutf} 8mb4 detect. If you want to use another collation please select";
                                        $db_option = false;
                                    }
                                    ?>
                                    <div class="alert alert-info alert-dismissible" role="alert">
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                        <?php echo $msg; ?>
                                    </div>
                                    <?php echo zen_draw_pull_down_menu('bookx_db_charaset', $db_options, ($db_option == true) ? $db_options[0]['id'] : $db_options[1]['id'], 'class="form-control"'); ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?php echo zen_draw_label('Use Dinamic Metatags', 'bookx_dinamic_metatags', 'class="col-sm-3 control-label"'); ?>
                                <div class="col-sm-9 col-md-6"> 
                                    <?php
                                    $bookx_dinamic_metatags = array(
                                        array(
                                            'id' => 'enable',
                                            'text' => 'Enable Dinamic MetaTags'
                                        ),
                                        array(
                                            'id' => 'disable',
                                            'text' => 'Don\'t! I have something else'
                                        )
                                    );
                                    $msg = "Use Dinamic Meta Tags on Front page.";
                                    ?>
                                    <div class="alert alert-info alert-dismissible" role="alert">
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                        <?php echo $msg; ?>
                                    </div>
                                    <?php echo zen_draw_pull_down_menu('bookx_dinamic_metatags', $bookx_dinamic_metatags, $bookx_dinamic_metatags[1]['id'], 'id="confMetaTags" class="form-control"'); ?>
                                </div>
                            </div>
                            <?php
                            //echo $result->html_url;
                            $ep4_info = "<p>You may use csv import for Bookx, with EP4 and EP4Bookx pluging.</p>";
                            $ep4_info .= "<p><strong>EasyPopulate</strong><br /> " . check_git_release_for("https://api.github.com/repos/mc12345678/EasyPopulate-4.0/releases", false) . "</p>";
                            $ep4_info .= "<p><strong>Ep4Bookx Info</strong> <br /> " . check_git_release_for("https://api.github.com/repos/mesnitu/EasyPopulate4BookX/releases", false) . "</p>";
                            ?>
                            <div class="form-group">
                                <?php echo zen_draw_label('CSV', 'ep4_download', 'class="col-sm-3 control-label"'); ?>
                                <div class="col-sm-9 col-md-6">
                                    <div class="alert alert-info" role="alert"><?php echo $ep4_info; ?></div>
                                    <?php
                                    echo zen_draw_checkbox_field('ep4_download', '0', false, '', 'placeholder="Disabled input here..." disabled') . '<span class="text-warning">Automatic Install not yet available</span>';
                                    ?>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-default float-rigth">Submit</button>
                            </form>
                        </div>
                    </div>
        <?php
    }
    /**
     * Ends the html block for install options
     */
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
                }
                ?>
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
                
                if ($already_installed == true) {
                    echo '<p>' . TEXT_BOOKX_STATUS_INSTALLED . '<span class="label label-success"> v' . $installed_version . '</span></p>';
                } else {
                    echo TEXT_BOOKX_STATUS_NOT_INSTALLED . '<br />';
                }
                ?>
                <ul class="list-group">
                    <li class="list-group-item">
                        <p><?php echo BOOKX_LINK_RESET_DESC; ?></p>
                        <a href="<?php echo zen_href_link(FILENAME_BOOKX_TOOLS, 'action=bookx_reset_to_defaults'); ?>" class="btn btn-danger btn-sm">
                <?php echo BOOKX_LINK_RESET; ?>
                        </a>
                    </li>
                <?php
                if (!$already_installed) {
                    echo '<li class="list-group-item"><a href="' . zen_href_link(FILENAME_BOOKX_TOOLS, 'action=bookx_install') . '">' . BOOKX_LINK_INSTALL . '</a></li>';
                }
                if ($installed_version < $bookx_version) {
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
                    echo zen_draw_hidden_field('action', 'bookx_remove');
                    ?>
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
                        <button type="submit" class="btn btn-danger btn-sm float-rigth">Submit</button>
                        <a href="<?php echo zen_href_link(FILENAME_BOOKX_TOOLS); ?>" class="btn btn-default btn-sm float-rigth">Cancel</a>
                        </form>
                    </div>
                <?php 
                } else { ?>
                    <li class="list-group-item">
                        <p><?php echo BOOKX_LINK_REMOVE_DESC; ?></p>
                         <a href="<?php echo zen_href_link(FILENAME_BOOKX_TOOLS, 'action=bookx_confirm_remove'); ?>" class="btn btn-danger btn-sm"><?php echo BOOKX_LINK_REMOVE; ?></a>
                    </li>
                <?php } ?>
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
                                    $type_array[] = array('id' => $product_types->fields['type_id'], 'text' => $product_types->fields['type_name']);
                                    $type_names[(int) $product_types->fields['type_id']] = $product_types->fields['type_name'];
                                    $product_types->MoveNext();
                                }

                                $select_string_from = false;
                                if ($choose_products_to_convert_from && isset($_POST['convert_from_type']) &&
                                    '' != $_POST['convert_from_type'] &&
                                    null != $bookx_pType_id) {

                                    $select_string_from = '<br /><select name="products_to_convert_from[]" size="10" multiple="multiple" class="form-control">';

                                    $products = $db->Execute("SELECT p.products_id, products_name, p.products_price, p.products_model
				                                FROM " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd
				                                where p.products_id = pd.products_id
				                                AND pd.language_id = '" . (int) $_SESSION['languages_id'] . "'
				                                AND p.products_type = '" . (int) $_POST['convert_from_type'] . "'
				                                ORDER BY products_name");

                                    $product_array = array();

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
                                    '' != $_POST['convert_to_type'] && null != $bookx_pType_id) {

                                    $select_string_to = '<br /><br /><select name="products_to_convert_to[]" size="10" multiple="multiple">';

                                    $products = $db->Execute("SELECT p.products_id, products_name, p.products_price, p.products_model
				                                FROM " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd
				                                where p.products_id = pd.products_id
				                                AND pd.language_id = '" . (int) $_SESSION['languages_id'] . "'
				                                AND p.products_type = '" . (int) $bookx_pType_id . "'
				                                ORDER BY products_name");

                                    $product_array = array();

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
                                echo ('' != $select_string_from ? $select_string_from : '');
                                echo '<button type="submit" class="btn btn-primary btn-sm float-rigth">Submit</button>';
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
                                echo ('' != $select_string_to ? $select_string_to : '');
                                echo '<button type="submit" class="btn btn-primary btn-sm float-rigth">Submit</button>';
                                echo '</form></div><!-- eof form migration_to_bookx -->';
                                echo '<a href="' . zen_href_link(FILENAME_BOOKX_TOOLS) . '" class="btn btn-default btn-sm float-rigth">Cancel</a>';
                            } elseif ('bookx_confirm_product_migration' == $action) {

                                switch (true) {
                                    case ($choose_products_to_convert_from && $product_selection_submitted && null != $bookx_pType_id): // convert some selected products from another article type to bookx
                                        $selected_products = implode(',', $_POST['products_to_convert_from']);
                                        $products_to_convert = $db->Execute('SELECT products_id FROM ' . TABLE_PRODUCTS . ' WHERE products_id IN (' . $selected_products . ') AND products_type = "' . (int) $_POST['convert_from_type'] . '"');
                                        while (!$products_to_convert->EOF) {
                                            bookx_convert_product_to_bookx_type($products_to_convert->fields['products_id']);

                                            $products_to_convert->MoveNext();
                                        }
                                        unset($_POST['products_to_convert_from']);


                                        break;

                                    case ($choose_products_to_convert_to && $product_selection_submitted && isset($_POST['convert_to_type']) && '' != $_POST['convert_to_type'] && null != $bookx_pType_id): // convert some selected products from bookx to another article type
                                        $selected_products = implode(',', $_POST['products_to_convert_to']);
                                        $products_to_convert = $db->Execute('SELECT products_id FROM ' . TABLE_PRODUCTS . ' WHERE products_id IN (' . $selected_products . ') AND products_type = "' . (int) $bookx_pType_id . '"');
                                        while (!$products_to_convert->EOF) {
                                            bookx_convert_product_from_bookx_to_type($products_to_convert->fields['products_id'], (int) $_POST['convert_to_type']);
                                            $products_to_convert->MoveNext();
                                        }
                                        $messageStack->add('Converted ' . $products_to_convert->Count() . 'products', 'success');
                                        unset($_POST);
                                        break;

                                    case (!$choose_products_to_convert_from && isset($_POST['convert_from_type']) && '' != $_POST['convert_from_type'] && null != $bookx_pType_id): // convert all products from another article type to bookx
                                        $products_to_convert = $db->Execute('SELECT products_id FROM ' . TABLE_PRODUCTS . ' WHERE products_type = "' . (int) $_POST['convert_from_type'] . '"');
                                        while (!$products_to_convert->EOF) {
                                            bookx_convert_product_to_bookx_type($products_to_convert->fields['products_id']);
                                            $products_to_convert->MoveNext();
                                        }
                                        $messageStack->add('Converted ' . $products_to_convert->Count() . 'products', 'success');
                                        unset($_POST);
                                        break;

                                    case (!$choose_products_to_convert_to && isset($_POST['convert_to_type']) && '' != $_POST['convert_to_type'] && null != $bookx_pType_id): // convert some products from another article type to bookx
                                        $products_to_convert = $db->Execute('SELECT products_id FROM ' . TABLE_PRODUCTS . ' WHERE products_type = "' . (int) $bookx_pType_id . '"');
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
                                if ($already_installed) {
                                    echo '<p>' . BOOKX_LINK_MANAGE_PRODUCT_MIGRATION_DESC . '</p><a href="' . zen_href_link(FILENAME_BOOKX_TOOLS, 'action=bookx_manage_product_migration#sectionConvertProduct') . '" class="btn btn-primary btn-sm">' . BOOKX_LINK_MANAGE_PRODUCT_MIGRATION . '</a>';
                                } else {
                                    echo TEXT_BOOKX_STATUS_NOT_INSTALLED;
                                }
                            }
                            tpl_panel('close');
                            ?>

                        <!-- Close Second Panel -->

                        <?php tpl_panel('open', 'sectionGitReleases', 'Git Relases Check'); ?>
                        <p>Info: <?php echo $objGit->ep4->installed; ?> You can trace your git fork updates. To do so, update git api url. 
                            <br />Note that this is not mandatory. You can just use the zencart download section.</p>
                        <?php if ($action == 'update_git_repo_url') { ?>
                            <?php
                            //function zen_draw_form($name, $action, $parameters = '', $method = 'post', $params = '', $usessl = 'false') {
                            echo zen_draw_form('git_repositories', FILENAME_BOOKX_TOOLS, 'action=update_git_repositories', 'post');
                            ?>
                            <div class="form-group">
                            <?php
                            echo zen_draw_label('Url for git Bookx', 'bookx');
                            echo zen_draw_input_field('bookx', $objGit->bookx->url, 'class="form-control" placeholder="' . $objGit->bookx->url . '" required', true, 'url');
                            ?>
                            </div>
                            <div class="form-group">
                            <?php
                            //zen_draw_input_field($name, $value = '~*~*#', $parameters = '', $required = false, $type = 'text', $reinsert_value = true) {
                            echo zen_draw_label('Url for git EP4', 'ep4');
                            echo zen_draw_input_field('ep4', '', 'class="form-control" placeholder="' . $objGit->ep4->url . '"', true, 'url');
                            ?>
                            </div>
                            <div class="form-group">
                                <?php
                                echo zen_draw_label('Url for git EP4Bookx', 'ep4bookx');
                                echo zen_draw_input_field('ep4bookx', '', 'class="form-control" placeholder="' . $objGit->ep4bookx->url . '"', true, 'url');
                                ?>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm float-rigth">Submit</button>
                            <a href="<?php echo zen_href_link(FILENAME_BOOKX_TOOLS); ?>" class="btn btn-default btn-sm">Cancel</a>
                            </form>

                                <?php
                            }

                            if (($action !== 'check_git_releases') && ($action !== 'update_git_repo_url')) {
                                ?>
                            <div>
                                <a href="<?php echo zen_href_link(FILENAME_BOOKX_TOOLS, 'action=check_git_releases'); ?>" class="btn btn-default btn-sm">Check Updates</a>
                                <a href="<?php echo zen_href_link(FILENAME_BOOKX_TOOLS, 'action=update_git_repo_url#sectionGitReleases'); ?>" class="btn btn-default btn-sm">Update Git Api Url</a></div>
    <?php } ?>
                        <?php tpl_panel('close'); ?>

                        <?php tpl_panel('open', 'sectionMissingRelations', 'Fix Missing Relations'); ?>
                        <p>Find and fix missing data base relations</p>
                        <a href="<?php echo zen_href_link(FILENAME_BOOKX_TOOLS, 'action=bookx_check_missing_product_relations'); ?>" class="btn btn-default btn-sm">Find and Fix</a>
    <?php tpl_panel('close'); ?>

                        <?php tpl_panel('open', 'section4', 'section 4'); ?>
                        <p>Try to scroll this section and look at the navigation list while scrolling!</p>
                        <?php tpl_panel('close'); ?>

                        <?php tpl_panel('open', 'sectionDocs', 'Documetation'); ?>
                        
                        <?php if ($action == 'loadDocumentation') {
                            echo '<div class="docs">';
                            require_once BOOKX_EXTRA_DATAFILES_FOLDER . 'libs/Parsedown.php';
                            $parsedown = new Parsedown();
                            $text = file_get_contents(BOOKX_EXTRA_DATAFILES_FOLDER . 'Documentation.md');
                            echo Parsedown::instance()
                                ->setUrlsLinked(true)
                                ->text($text);
                             echo '</div>';
                            } else { ?> 
                        <a href="<?php echo zen_href_link(FILENAME_BOOKX_TOOLS, 'action=loadDocumentation#sectionDocs'); ?>" class="btn btn-default btn-sm">Load DOcumetation</a> 
                            <?php } ?>
                        
                        <?php tpl_panel('close'); ?>
                    <?php } // ends bookx tools panels ?>
                </div><!-- right div_eof //--> 
            </div> <!-- row_eof //--> 
        </div><!-- container_eof //-->

        <!-- footer //-->
                    <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
        <!-- footer_eof //-->
       <script src="includes/extra_datafiles/bookx/libs/prism.js"></script>
        <script>
            $(document).ready(function () {

                $('.container ul.nav li a').bind('click', function (e) {
                    e.preventDefault();
                    $('html,body').animate({scrollTop: $(this.hash).offset().top});
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

function tpl_panel($param, $section_id = null, $title = null) {
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

require(DIR_WS_INCLUDES . 'application_bottom.php');
