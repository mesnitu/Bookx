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
 * @version BookX V 1.0.0
 * @version $Id: [admin]/includes/init_includes/init_product_type_bookx.php 2019-01-05 mesnitu $
 */
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}
/**
 * define public bookx version
 */
define('PROJECT_BOOKX_VERSION', '1.0.0');
// set version
$bookx_version = PROJECT_BOOKX_VERSION;

$login_page = false;
if (strpos($_SERVER['PHP_SELF'], 'login.php')) {
    $login_page = true;
}

// Test for existing installation
//pr($_SESSION);
//pr($_GET);
//pr($_POST);

if (!$login_page) {
    
    if (isset($_GET['action']) && ('bookx_remove' == $_GET['action'])) {
        require_once BOOKX_EXTRA_DATAFILES_FOLDER . 'installers/bookx_install_v1.php';
    }
    //Going for PROJECT_BOOKX_VERSION
    $sql = "SELECT configuration_value AS version FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'BOOKX_VERSION';";
    $res = $db->Execute($sql);
    $installed_version = $res->fields['version'];
    
    // we can stop here if it's using current project version, else:
    if ($installed_version !== PROJECT_BOOKX_VERSION) {

        $installed_version = $res->fields['version'];
        // found a version. Will update
        if ((!strpos($_SERVER['PHP_SELF'], FILENAME_BOOKX_TOOLS))) {
            $msg = "<div style=\"display:inline-block;margin-left:1rem;line-heigth:2;\">Welcome to Bookx v1.0.0 installation tool for Zencart 1.5.6<br />";
         
            //$msg .= 'Proceed to <a href="' . zen_href_link(FILENAME_BOOKX_TOOLS, 'action=bookx_install_options') . ' "class="btn btn-primary btn-xs">' . FILENAME_BOOKX_TOOLS . '</a> to Update Bookx ' . $installed_version . ' to v' . $bookx_version . '</div>';
            $msg .= 'Proceed to <a href="' . zen_href_link(FILENAME_BOOKX_TOOLS, 'action=bookx_install_options') . ' "class="btn btn-primary btn-xs">' . FILENAME_BOOKX_TOOLS . '</a> to Update Bookx</div>';
            $messageStack->add($msg, 'info');
        }
    }
}

