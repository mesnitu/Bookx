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
 * @copyright Portions Copyright 2003-2011 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.gnu.org/licenses/gpl.txt GNU General Public License V2.0
 *
 * @version BookX V 1.0.1
 * @version $Id: [admin]/includes/init_includes/init_product_type_bookx.php 2019-01-05 mesnitu $
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}
/**
 * Get bookx module version
 * admin/includes/extra_datafiles/bookx/bookx_files.json
 */
$bookx_module_version = json_decode(file_get_contents(BOOKX_MODULE_FILES))->version;

define('PROJECT_BOOKX_VERSION', $bookx_module_version);

$login_page = false;
if (strpos($_SERVER['PHP_SELF'], 'login.php')) {
    $login_page = true;
}

// Test for existing installation
if (!$login_page) {
    // if bookx_remove is requested, process it
    if (isset($_GET['action']) && ('bookx_remove' == $_GET['action'])) {
        require_once BOOKX_EXTRA_DATAFILES_FOLDER . 'installers/bookx_install_v1.php';
    }
    $bookx_installed_version = false;
    $bookx_already_installed = false;

    //Find product_bookx type_handler id
    $sql = "SELECT type_id FROM " . TABLE_PRODUCT_TYPES . " WHERE type_handler = 'product_bookx'";
    $res = $db->Execute($sql);
    $bookx_ptypeID = $res->fields['type_id'] ?? false;
    

    if ($bookx_ptypeID) {
        //Get the installed bookx version
        $sql = "SELECT configuration_value AS version FROM " . TABLE_CONFIGURATION . " 
        WHERE configuration_key = 'BOOKX_VERSION';";
        $res = $db->Execute($sql);
        $bookx_installed_version = $res->fields['version'];
        $bookx_already_installed = true;
    }

    // we can stop here if it's using current project version, else:
    if ($bookx_installed_version !== PROJECT_BOOKX_VERSION) {
        //$bookx_installed_version is false, check if any related bookx db is present
        if (bookx_db(BOOKX_DB_TABLES !== 'install')) {
            $bookx_already_installed = true;
        }
        // found a version. Will update
        if ((!strpos($_SERVER['PHP_SELF'], FILENAME_BOOKX_TOOLS))) {
            $msg = '<div style="display:inline-block;margin-left:1rem;line-heigth:2;">
            Welcome to Bookx v'.$bookx_module_version.' installation tool for Zencart 1.5.6<br />';

            $msg .= 'Proceed to <a id ="choice" href="' . zen_href_link(
                FILENAME_BOOKX_TOOLS,
                'action=bookx_install_options'
            ) . ' "class="btn btn-primary btn-xs">' . str_replace('_', ' ', FILENAME_BOOKX_TOOLS) . '</a> to Update Bookx</div>';
            $messageStack->add($msg, "info");
        }
    } else {
        $bookx_already_installed = true;
    }
}
