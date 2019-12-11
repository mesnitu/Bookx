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
 * File bookx_extrafiles_folder.php
 * Project Path /[RENAME_TO_YOUR_ADMIN_FOLDER]/includes/extra_configures/bookx_extrafiles_folder.php
 * @version $Id: mesnitu  2019 Dec 10 in BookX v1.0.1 for Zen Cart 1.5.6c $
 * -----
 * HISTORY:
 * Date      	By   	Comments
 * ----------	-----	--------------------------------------------------
 */


define('BOOKX_EXTRA_DATAFILES_FOLDER', DIR_FS_ADMIN.'includes/extra_datafiles/bookx/');
define('BOOKX_VENDORS_FOLDER', DIR_WS_CLASSES.'vendors/bookx/vendor/');
define('BOOKX_MODULE_FILES', BOOKX_EXTRA_DATAFILES_FOLDER.'bookx_files.json');


/**
 * @todo temp constants that later will go to configuration... or not
 */
define('BOOKX_DISPLAY_GIT_RELEASES', true);

define('BOOKX_APPLY_SPECIALS_UPDATE', true);

define('BOOKX_TEMP_FOLDER', DIR_FS_CATALOG_IMAGES.'temp/');

define('BOOKX_RESIZE_IMAGES', true);

