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
 * File init_product_type_bookx.php
 * Project Path /[RENAME_TO_YOUR_ADMIN_FOLDER]/includes/init_includes/init_product_type_bookx.php
 * @version $Id: mesnitu  2019 Dec 08 in BookX v1.0.1 for Zen Cart 1.5.6c $
 * -----
 * HISTORY:
 * Date      	By   	Comments
 *
 * 2019 Dec 08	[mesnitu]	-> Version and file check are coming from
 * admin/includes/extra_datafiles/bookx/bookx_files.json
 *
 * 2019 Dec 08	[mesnitu] for historical reasons trying to maintain the update for v090
 * ----------	-----	--------------------------------------------------
 */

if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}
//
$bookx_development = false;

if (!isset($_SESSION['bookx'])) {
    $_SESSION['bookx'] = null;
}
//Get module version
$bookx_module_version = json_decode(file_get_contents(BOOKX_MODULE_FILES))->version;

define('PROJECT_BOOKX_VERSION', $bookx_module_version);

$login_page = false;
if (strpos($_SERVER['PHP_SELF'], 'login.php')) {
    $login_page = true;
}

// Test for existing installation
if (!$login_page) {
    // @todo remove this block
    if ($bookx_development == true) {
        //// UPDATE `configuration` SET `configuration_value` = '' WHERE `configuration`.`configuration_id` = 890;
        //bookx_backup_db_tables(BOOKX_DB_TABLES);
    }
    
    // if bookx_remove is requested, process it
    if (isset($_GET['action']) && ('bookx_remove' == $_GET['action'])) {
        require_once BOOKX_EXTRA_DATAFILES_FOLDER.'installers/bookx_install_v1.php';
    }
    //Find product_bookx type_handler id
    if (!$_SESSION['bookx']['ptypeID']) {

        $sql = "SELECT type_id FROM ".TABLE_PRODUCT_TYPES." WHERE type_handler = 'product_bookx'";
        $res = $db->Execute($sql);
        $bookx_ptypeID = $res->fields['type_id'] ?? false;

        $_SESSION['bookx']['ptypeID'] = $bookx_ptypeID;
    } else {
        $bookx_ptypeID = $_SESSION['bookx']['ptypeID'];
    }

    // if there is one ptype for bookx, find the installed version
    if ($bookx_ptypeID && !isset($_SESSION['bookx']['version'])) {
        
        $bookx_already_installed = true;
        
        $sql = "SELECT configuration_value AS version FROM ".TABLE_CONFIGURATION." 
        WHERE configuration_key = 'BOOKX_VERSION';";
        $res = $db->Execute($sql);
        // This could be false for v090.
        $bookx_installed_version = $res->fields['version'] ?? false;
    
        $_SESSION['bookx']['version'] = $bookx_installed_version;
    } else {
        $bookx_installed_version = $_SESSION['bookx']['version'];
        // there must be a ptype for bookx
        $bookx_already_installed = false;
    }
    
    // we can stop here if it's using current project version, else:
    if ($bookx_installed_version !== PROJECT_BOOKX_VERSION) {
        $tmp = 'update';
        $_SESSION['bookx']['update_from'] = $bookx_installed_version;
        // if $bookx_installed_version is false, checks for any related bookx tables in db 
        // it returns install or update 
        if ($_SESSION['bookx']['update_from'] !== '090') {
            if (bookx_check_db_tables(BOOKX_DB_TABLES) !== 'install') {
                $_SESSION['bookx']['update_from'] = '090';
            }else {
                $_SESSION['bookx']['install'] = true;
                $tmp = 'install';
            }
        }
        
        if (!strpos($_SERVER['PHP_SELF'], FILENAME_BOOKX_TOOLS)) {
            $msg = '<div style="display:inline-block;margin-left:1rem;line-height:2;">
            Welcome to Bookx v'.$bookx_module_version.' installation tool for Zen Cart 1.5.6<br />';

            $msg .= 'Proceed to <a id ="choice" href="'.zen_href_link(
                FILENAME_BOOKX_TOOLS,
                'action=bookx_install_options'
            ).' "class="btn btn-primary btn-xs">'.str_replace('_', ' ', FILENAME_BOOKX_TOOLS).'</a> to '.$tmp.' Bookx</div>';
            $messageStack->add($msg, "info");
            unset($tmp, $msg);
        }
    } else {
        $bookx_already_installed = true;
        unset($_SESSION['bookx']['update_from']);
    }
}
