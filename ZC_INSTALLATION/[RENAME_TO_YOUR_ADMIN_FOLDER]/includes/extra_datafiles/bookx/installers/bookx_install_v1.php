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
 * @author  mesnitu
 * @copyright Copyright 2013
 * @copyright Copyright 2003-2019 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * 
 * File bookx_install_v1.php
 * Project Path /[RENAME_TO_YOUR_ADMIN_FOLDER]/includes/extra_datafiles/bookx/installers/bookx_install_v1.php
 * @version $Id: mesnitu  2019 Dec 10 in BookX v1.0.1 for Zen Cart 1.5.6c $
 */


if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

switch ($_GET['action']) {
    
    case 'bookx_remove':
        $bookx_install = 'uninstall';
        break;

    case 'bookx_install':
        $bookx_install = 'install';
        if (empty($_POST)) {
            zen_redirect(FILENAME_BOOKX_TOOLS.'php?action=bookx_install_options');
        }
        break;

    case 'bookx_update':
        $bookx_install = 'update';
        if (empty($_POST)) {
            zen_redirect(FILENAME_BOOKX_TOOLS.'php?action=bookx_install_options');
        }
        break;

    case 'bookx_reset_to_defaults':
        $bookx_install = 'reset';
        break;

    case 'bookx_install_options': // not in use
        $bookx_install = 'install_options';
        break;

    default:
        $bookx_install = false;
        break;
}

// get constants for use inside Heredoc block
$const = get_defined_constants();

$dbCharset = $const['DB_CHARSET'];
$zc_version = $const['PROJECT_VERSION_MINOR'];
$detect_ceon = ($const['TABLE_CEON_URI_MAPPINGS']) ? true : false;

// flags
$install_incomplete = false;
$no_template = false;

// find current template
try {
    if(!$template_dir) {
        throw new Exception(BOOKX_MS_TEMPLATE_NOTFOUND, 1);
    }
} catch (Exception $e) {
    $messageStack->add($e, 'warning');
} finally {
    $current_template = $template_dir;
    $install_incomplete = true;
    $no_template = true;
}
/****
 * I guess this is not necessary, because $template_dir has the template name
 * 
$sql = "SELECT template_dir FROM ".TABLE_TEMPLATE_SELECT." LIMIT 1";
$obj = $db->Execute($sql);
$current_template = $obj->fields['template_dir'] ?? null;
*/

$admin_page_keys = [
    'configBookXTools',
    'configProdTypeBookX',
    'bookxAuthors',
    'bookxAuthorTypes',
    'bookxBinding',
    'bookxConditions',
    'bookxGenres',
    'bookxImprints',
    'bookxPrinting',
    'bookxPublishers',
    'bookxSeries',
    'bookxFamilies'];


ob_start();
// necessary BookX files
$bookx_files_array = new ArrayObject(json_decode(file_get_contents(BOOKX_MODULE_FILES), true));
$it = $bookx_files_array->getIterator();

$msg = '';
$files = 0;
$files_issue = 0;
// Init arrays for lang verification. They are extracted from the bookx_files.json
$bookx_language_admin_files = [];
$bookx_language_catalog_files = [];

while ($it->valid()) {
    
    if ($it->key()=='admin_files' || $it->key()=='catalog_files' || 
    $it->key()=='edit_manually'){
        
        $temp= [];
        $bmsg = '<p class="text-success strong">'.$it->key().': OK.</p>';
        foreach ($it->current() as $f) {
            $files++;
            $f = str_replace("[YOUR-TEMPLATE]", $template_dir, $f);
            switch ($it->key()) {
                case 'edit_manually':
                    $f = str_replace("[RENAME_TO_YOUR_ADMIN_FOLDER]/", DIR_FS_ADMIN, $f);
                    $f = DIR_FS_CATALOG.str_replace("[EDIT_MANUALLY]/", '', $f);
                    break;
                case 'catalog_files':
                    $f = DIR_FS_CATALOG.$f;
                    //extract catalog languages files for later verification
                    if (strpos($f, '/languages/english/')!==false) {
                        $lf = explode('english/', $f)[1];
                        $bookx_language_catalog_files[] = $lf;
                    }
                    break;
                case 'admin_files':
                    $f = str_replace("[RENAME_TO_YOUR_ADMIN_FOLDER]/", DIR_FS_ADMIN, $f);
                    //extract admin languages files for later verification
                    if (strpos($f, '/languages/english/')!==false) {
                        $lf = explode('english/', $f)[1];
                        $bookx_language_admin_files[] = $lf;
                    }
                    break;
                default:
                    break;
            }
            if (!is_readable($f)) {
                $temp[]= '<p class="text-warning">'.$f.'</p>';
                $files_issue ++;
            }
        }
        if ($files_issue > 0) {
            $bmsg = '<p class="text-danger strong">'.$it->key().': Files Issues</p>';
        }
        $msg .= $bmsg.implode($temp, '');
    }
    $it->next();
}
/**
 * @since v1.0.1 
 * Bookx modules uses some vendor packages, They are not installed by default ( for now )
 * Check here if they are present
 */
try {
    if (!is_dir(BOOKX_VENDORS_FOLDER)) {
        //@todo add more explanation
        throw new Exception("No Vendors folder found", 1); 
    }
} catch (\Throwable $th) {
    throw $th;
}

$bookx_msg_required_files = <<<HEREDOC
<p>Number of Files: $files</p>
<p>Number of Files Issues: $files_issue</p>
<p>$msg</p>
HEREDOC;
$messageStack->add(BOOKX_MS_SOME_REQUIRED_FILES_MISSING.' '.$bookx_msg_required_files, 'warning');
ob_end_flush();


// possibly overriden BookX files
$template_default_overriden_files = [
    DIR_FS_CATALOG_TEMPLATES.$current_template.'/sideboxes/tpl_bookx_filters_select.php',
    DIR_FS_CATALOG_TEMPLATES.$current_template.'/templates/tpl_bookx_authors_list_default.php',
    DIR_FS_CATALOG_TEMPLATES.$current_template.'/templates/tpl_bookx_series_list_default.php',
    DIR_FS_CATALOG_TEMPLATES.$current_template.'/templates/tpl_product_bookx_info_display.php'
]; 

$overridden_files = [
    DIR_FS_CATALOG_TEMPLATES.$current_template.'/common/tpl_tabular_display.php',
    DIR_FS_CATALOG_TEMPLATES.$current_template.'/templates/tpl_index_product_list.php'
];


$bookx_available_languages = ['english', 'german'];
#$bookx_language_catalog_files = ['product_bookx_info.php','extra_definitions/product_bookx.php'];


//=======================================
// INSTALL CHECK
//=======================================

if ($login_page == false) {
    
    /** check that all files are where they should be
     * foreach ($required_files as $f) {
     * if (!is_readable($f)) {
     * $messageStack->add(BOOKX_MS_SOME_REQUIRED_FILES_MISSING.' '.$f, 'warning');
     * //$install_incomplete = true;
     * }
     * }
     */

    //check for overrides to template default
    foreach ($template_default_overriden_files as $f) {
        if (is_readable($f)) {
            $messageStack->add(BOOKX_MS_FILE_SHOULD_ONLY_BE_OVERRIDE.' '.$f, 'warning');
        }
    }

    ///***** check if multiple languages are installed and which
    $multilanguage = false;
    $german_installed = false;

    $installed_languages = zen_get_languages();
    if (sizeof($installed_languages)) {
        $multilanguage = true;
    }

    for ($i=0, $n=sizeof($installed_languages); $i<$n; $i++) {
        if ('de' == $installed_languages[$i]['code']) {
            $german_installed = true;
        }
    }
  
    foreach ($bookx_available_languages as $language) {
        $files_missing = [];
        switch (true) {
            case 'english' == $language:
            case 'german' == $language && $german_installed:
                foreach ($bookx_language_catalog_files as $f) {
                    $f = DIR_FS_CATALOG_LANGUAGES.$language.'/'.$f;
                    if (!is_readable($f)) {
                        $files_missing[] = $f;
                    }
                }
        
                foreach ($bookx_language_admin_files as $f) {
                    $f = DIR_FS_ADMIN.'includes/languages/'.$language.'/'.$f;
                    if (!is_readable($f)) {
                        $files_missing[] = $f;
                    }
                }
                  break;
        }
        if (!empty($files_missing)) {
            $messageStack->add(''.sprintf(BOOKX_MS_SOME_LANGUAGE_FILES_MISSING, $language).'<br />'.implode(', ', $files_missing), 'caution');
        }
    }
}

//=======================================
// INSTALL / UPDATE / UNINSTALL
//=======================================

if (isset($_POST) && (!empty($_POST))) {

    /*
     * Array(
      [securityToken] => 17f5519c35f2a6d83edbe08604064bae
      [bookx_ceon] => disable_ceon
      [bookx_db_charaset] => utf8mb4
      [bookx_dinamic_metagas] => disable
      )
     */
    /**
     * Checks the shops default encoding by ZC installation
     */
    // found this from Zencart Installation
    $default_db_encoding = zen_db_prepare_input($_POST['bookx_db_charaset']);

    $table_character_set = "CHARACTER SET = ".$default_db_encoding."";
    $column_character_set = "CHARACTER SET '".$default_db_encoding."'";
    
    if (isset($_POST['bookx_ceon']) && ($_POST['bookx_ceon'] == 'enable_ceon')) {
        $bookx_uses_ceon = true;
        //for update -> reset
        $_SESSION['bookx_install_ceon'] = true;
    }
    if (isset($_POST['bookx_dinamic_metatags']) && ($_POST['bookx_dinamic_metatags'] == 'enable')) {
        $bookx_uses_dinamic_metatags = true;
        //for update -> reset
        $_SESSION['bookx_install_metatags'] = true;
    }
}

switch (true) {
 
    case ($bookx_install == 'update' && $already_installed):
            
        $sql = "SELECT configuration_group_id FROM {$const['TABLE_CONFIGURATION_GROUP']} WHERE configuration_group_title = 'BookX';";

        $config_groups = $db->Execute($sql);
        $cf_gid = null;
        
        while (!$config_groups->EOF) {
            $cf_gid = $config_groups->fields['configuration_group_id'];
            $config_groups->MoveNext();
        }
        
        $sql = "SELECT type_id FROM {$const['TABLE_PRODUCT_TYPES']} WHERE type_handler= 'product_bookx'";

        $product_type = $db->Execute($sql);
        $bookx_ptypeID = null;

        while (!$product_type->EOF) {
            $bookx_ptypeID = $product_type->fields['type_id'];
            $product_type->MoveNext();
        }
        
        switch ($installed_version) {
            case '0.9':
                require_once BOOKX_EXTRA_DATAFILES_FOLDER.'installers/bookx_update_v09.php';
            // we don't break here

            // no break
            case '0.9.1':
                require_once BOOKX_EXTRA_DATAFILES_FOLDER.'installers/bookx_update_v091.php';
            // we don't break here

            // no break
            case '0.9.2':
                require_once BOOKX_EXTRA_DATAFILES_FOLDER.'installers/bookx_update_v092.php';
            // we don't break here

            // no break
            case '0.9.3':
                require_once BOOKX_EXTRA_DATAFILES_FOLDER.'installers/bookx_update_v093.php';
            // we don't break here

            // no break
            case '0.9.4':
                require_once BOOKX_EXTRA_DATAFILES_FOLDER.'installers/bookx_update_v094.php';
            // we don't break here
            // no break
            case '0.9.5':
                require_once BOOKX_EXTRA_DATAFILES_FOLDER.'installers/bookx_update_v095.php';
            break;
            
            case $bookx_module_version:
                $messageStack->add(sprintf(BOOKX_MS_VERSION_ALREADY_UP_TO_DATE, $bookx_module_version), 'warning');
                $install_incomplete = true;
                break;
        }

        if (!$install_incomplete) {
            $sql = 'UPDATE '.TABLE_CONFIGURATION.' SET configuration_value = "'.$bookx_module_version.'", last_modified="'.date('Y-m-d H:i:s').'" WHERE configuration_key = "BOOKX_VERSION";';
            
            $db->Execute($sql);
            $messageStack->add(''.BOOKX_MS_DB_UPDATE_SUCCESS.'', 'success');
        }

        break;

    case ('reset' == $bookx_install && !$login_page):
        
        
        $cf_gid = null;
        $sql = "SELECT configuration_group_id FROM {$const['TABLE_CONFIGURATION_GROUP']} WHERE configuration_group_title = 'BookX';";
         
        $config_groups = $db->Execute($sql);

        if ($config_groups->EOF) {
            $sql = "REPLACE INTO {$const['TABLE_CONFIGURATION_GROUP']} (configuration_group_title, configuration_group_description, sort_order, visible) VALUES
   				('BookX', 'Configure BookX Product Type settings', '1', '1')";
            $db->Execute($sql);
            
            $sql = "SELECT configuration_group_id FROM {$const['TABLE_CONFIGURATION_GROUP']} WHERE configuration_group_title = 'BookX';";
            
            $config_groups = $db->Execute($sql);
        }

        while (!$config_groups->EOF) {
            $cf_gid = $config_groups->fields['configuration_group_id'];
            $config_groups->MoveNext();
        }
        
        $bookx_ptypeID = null;
        $sql = "SELECT type_id FROM {$const['TABLE_PRODUCT_TYPES']} WHERE type_handler = 'product_bookx';";
        $product_type = $db->Execute($sql);
       
        if ($product_type->EOF) {
            $sql = "REPLACE INTO {$const['TABLE_PRODUCT_TYPES']} 
                (type_name, type_handler, type_master_type, allow_add_to_cart, date_added, last_modified)
	            VALUES ( 'Product - Bookx', 'product_bookx', 1,  'Y', now(), now())";
            $db->Execute($sql);
            
            $sql = "SELECT type_id FROM {$const['TABLE_PRODUCT_TYPES']} WHERE type_handler = 'product_bookx';";
            $product_type = $db->Execute($sql);
        }
        while (!$product_type->EOF) {
            $bookx_ptypeID = (int)$product_type->fields['type_id'];
            $product_type->MoveNext();
        }
        
        /**
         * @since v1.0.0
         * Check installation options
         */
        //unset($bookx_uses_ceon, $bookx_uses_dinamic_metatags);
        
        if (isset($_SESSION['bookx_install'])) {
            if ((isset($_SESSION['bookx_install_ceon'])) || isset($_SESSION['bookx_install_metatags'])) {
                $bookx_uses_ceon = $_SESSION['bookx_install_ceon'];
                $bookx_uses_dinamic_metatags = $_SESSION['bookx_install_ceon'];
            }
        }

        $sql = $db->Execute("SELECT configuration_value FROM ".TABLE_CONFIGURATION." WHERE configuration_key ='BOOKX_USES_CEON_URI_MODULE'");
        
        if ($sql->RecordCount() > 0) {
            $bookx_uses_ceon = true;
        }
        
        $sql = $db->Execute("SELECT configuration_value FROM ".TABLE_CONFIGURATION." WHERE configuration_key ='BOOKX_USES_DINAMIC_METATAGS'");
        if ($sql->RecordCount() > 0) {
            $bookx_uses_dinamic_metatags = true;
        }

        if (!empty($cf_gid)) {
            /* $sql = "DELETE FROM {$const['TABLE_CONFIGURATION_GROUP']} WHERE configuration_group_id = {$cf_gid}";
             $db->Execute($sql);*/ //we keep this an don't delete when resetting
        
            $sql = "DELETE FROM {$const['TABLE_CONFIGURATION']} WHERE configuration_group_id = {$cf_gid} AND configuration_group_id != 0";
            $db->Execute($sql);
        
            if (defined('TABLE_CONFIGURATION_LANGUAGE')) {
                $sql = "DELETE FROM {$const['TABLE_CONFIGURATION_LANGUAGE']} WHERE configuration_key LIKE '%BOOKX%'";
                $db->Execute($sql);
            }
        }
        
        // ======================================================
        //
        // remove Layout option descriptions
        //
        // ======================================================
        if (!empty($bookx_ptypeID)) {
            $sql = "DELETE FROM {$const['TABLE_PRODUCT_TYPE_LAYOUT']} WHERE product_type_id = $bookx_ptypeID";
            $db->Execute($sql);
        }

        //** This should not be necessary, but you never know
        $sql = "DELETE FROM {$const['TABLE_PRODUCT_TYPE_LAYOUT']} WHERE configuration_key LIKE '%BOOKX%'";
        $db->Execute($sql);
        //*** eof not necessary?
            
        $sql = "DELETE FROM {$const['TABLE_GET_TERMS_TO_FILTER']} WHERE get_term_table LIKE 'TABLE_PRODUCT_BOOKX%'";
        $db->Execute($sql);
            
        if (defined('TABLE_PRODUCT_TYPE_LAYOUT_LANGUAGE')) {
            $sql = "DELETE FROM {$const['TABLE_PRODUCT_TYPE_LAYOUT_LANGUAGE']} WHERE configuration_key LIKE '%BOOKX%'";
            $db->Execute($sql);
        }
        
        //if (defined('TABLE_ADMIN_PAGES')) zen_deregister_admin_pages($admin_page_keys);
        
        $sql = 'SELECT configuration_value AS version FROM '.TABLE_CONFIGURATION.' WHERE configuration_key = "BOOKX_VERSION";';
        $result = $db->Execute($sql); /* @var $result queryFactoryResult */
        
        if (!$result->EOF) { // someone may reset their BookX installation BEFORE updating, so we don't want to accidentally update the BookX version in the DB
            $version = $result->fields['version'];
        } else {
        }
         
        //******* we don't break here!
        
        // no break
    case ('install' == $bookx_install && (!$login_page) && (!$already_installed)):
       
        //=======================================
        // INSTALL
        //=======================================
        
        $sql = "REPLACE INTO {$const['TABLE_GET_TERMS_TO_FILTER']} (get_term_name, get_term_table, get_term_name_field) VALUES
			    	('bookx_author_id', 'TABLE_PRODUCT_BOOKX_AUTHORS', 'author_name'),
	    			('bookx_author_type_id', 'TABLE_PRODUCT_BOOKX_AUTHOR_TYPES_DESCRIPTION', 'type_description'),
	    			('bookx_binding_id', 'TABLE_PRODUCT_BOOKX_BINDING_DESCRIPTION', 'binding_description'),
	    			('bookx_condition_id', 'TABLE_PRODUCT_BOOKX_CONDITIONS_DESCRIPTION', 'condition_description'),
	    			('bookx_genre_id', 'TABLE_PRODUCT_BOOKX_GENRES_DESCRIPTION', 'genre_name'),
	    			('bookx_imprint_id', 'TABLE_PRODUCT_BOOKX_IMPRINTS', 'imprint_name'),
	    			('bookx_printing_id', 'TABLE_PRODUCT_BOOKX_PRINTING_DESCRIPTION', 'printing_description'),
	    			('bookx_publisher_id', 'TABLE_PRODUCT_BOOKX_PUBLISHERS', 'publisher_name'),
	    			('bookx_series_id', 'TABLE_PRODUCT_BOOKX_SERIES_DESCRIPTION', 'series_name')";
        $db->Execute($sql);
        
    if ('install' == $bookx_install) { // could also be "reset" ! ???

        $sql = "REPLACE INTO {$const['TABLE_PRODUCT_TYPES']} (type_name, type_handler, type_master_type, allow_add_to_cart, date_added, last_modified)
	                   VALUES ( 'Product - Bookx', 'product_bookx', 1,  'Y', now(), now())";
        $db->Execute($sql);
           
        

        $sql = <<<EOT
	                #DROP TABLE IF EXISTS {$const['TABLE_PRODUCT_BOOKX_EXTRA']};
                    CREATE TABLE {$const['TABLE_PRODUCT_BOOKX_EXTRA']} (
                    products_id int(11) NOT NULL default '0' PRIMARY KEY,
                    bookx_publisher_id int(11) default '0',
                    bookx_series_id int(11)  default '0',
                    bookx_imprint_id int(11) NULL default '0',
                    bookx_binding_id int(11) NULL default '0',
                    bookx_printing_id int(11) NULL default '0',
                    bookx_condition_id int(11) NULL default '0',
                    publishing_date datetime default NULL,
                    pages VARCHAR(16) DEFAULT NULL,
                    volume VARCHAR(16) DEFAULT NULL,
                    size VARCHAR(16) DEFAULT NULL,
                    isbn VARCHAR(13) DEFAULT NULL,
                    KEY idx_bxe_publisher_id (bookx_publisher_id ASC),
                    KEY idx_bxe_series_id (bookx_series_id ASC),
                    KEY idx_bxe_isbn (isbn ASC)
                   ) ENGINE=MyISAM DEFAULT CHARSET={$default_db_encoding};
EOT;
               
        $db->Execute($sql);

        $sql = <<<EOT

                # Table structure for table product_bookx_extra_description
                #DROP TABLE IF EXISTS {$const['TABLE_PRODUCT_BOOKX_EXTRA_DESCRIPTION']};
                CREATE TABLE {$const['TABLE_PRODUCT_BOOKX_EXTRA_DESCRIPTION']} (
                products_id int(11) NOT NULL DEFAULT 0,
                languages_id int(11) NOT NULL DEFAULT 0,
                products_subtitle VARCHAR(191) DEFAULT NULL,
                PRIMARY KEY  (products_id, languages_id)
                ) ENGINE=MyISAM DEFAULT CHARSET={$default_db_encoding};
EOT;
        $db->Execute($sql);

        $sql = <<<EOT
	               # Table structure for table product_bookx_authors
	               #DROP TABLE IF EXISTS {$const['TABLE_PRODUCT_BOOKX_AUTHORS']};
	               CREATE TABLE {$const['TABLE_PRODUCT_BOOKX_AUTHORS']} (
	                 bookx_author_id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	                 author_name VARCHAR(64) DEFAULT NULL,
	                 author_image VARCHAR(64) DEFAULT NULL,
	               	 author_image_copyright VARCHAR(64) DEFAULT NULL,
	               	 author_default_type int(11) DEFAULT 0,
	                 author_sort_order int(11) DEFAULT 0,
	               	 author_url VARCHAR(255) DEFAULT NULL,
	               	 date_added datetime DEFAULT NULL,
	               	 last_modified datetime DEFAULT NULL,
	                 KEY idx_bxa_author_name (author_name)
	               ) ENGINE=MyISAM DEFAULT CHARSET={$default_db_encoding};
EOT;
        $db->Execute($sql);

        $sql = <<<EOT

	               # Table structure for table product_bookx_authors_description
	               #DROP TABLE IF EXISTS {$const['TABLE_PRODUCT_BOOKX_AUTHORS_DESCRIPTION']};
	               CREATE TABLE {$const['TABLE_PRODUCT_BOOKX_AUTHORS_DESCRIPTION']} (
	                 bookx_author_id int(11) NOT NULL DEFAULT 0,
	                 languages_id int(11) NOT NULL DEFAULT 0,
	                 author_description TEXT,
	                 PRIMARY KEY  (bookx_author_id, languages_id)
	               ) ENGINE=MyISAM DEFAULT CHARSET={$default_db_encoding};
EOT;
        $db->Execute($sql);

        $sql = <<<EOT
	               # Table structure for table product_bookx_authors_to_products
	               #DROP TABLE IF EXISTS {$const['TABLE_PRODUCT_BOOKX_AUTHORS_TO_PRODUCTS']};
	               CREATE TABLE {$const['TABLE_PRODUCT_BOOKX_AUTHORS_TO_PRODUCTS']} (
	               	 primary_id int(11) NOT NULL AUTO_INCREMENT,
	                 bookx_author_id int(11) NOT NULL DEFAULT '0',
	                 products_id int(11) NOT NULL DEFAULT '0',
	                 bookx_author_type_id int(11) NOT NULL DEFAULT 0,
	                 PRIMARY KEY  (primary_id),
	                 KEY idx_bxatp_author_id (bookx_author_id),
                    KEY idx_bxatp_products_id (products_id)
	               ) ENGINE=MyISAM DEFAULT CHARSET={$default_db_encoding};
EOT;
        $db->Execute($sql);

        $sql = <<<EOT
               # Table structure for table product_bookx_author_types
               #DROP TABLE IF EXISTS {$const['TABLE_PRODUCT_BOOKX_AUTHOR_TYPES']};
               CREATE TABLE {$const['TABLE_PRODUCT_BOOKX_AUTHOR_TYPES']} (
                 bookx_author_type_id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                 type_sort_order int(11) DEFAULT '0',
                 KEY idx_bxat_author_type_id (bookx_author_type_id ASC)
               ) ENGINE=MyISAM DEFAULT CHARSET={$default_db_encoding};
EOT;
        $db->Execute($sql);

        $sql = <<<EOT

               # Table structure for table product_bookx_author_types_description
               #DROP TABLE IF EXISTS {$const['TABLE_PRODUCT_BOOKX_AUTHOR_TYPES_DESCRIPTION']};
               CREATE TABLE {$const['TABLE_PRODUCT_BOOKX_AUTHOR_TYPES_DESCRIPTION']} (
                 bookx_author_type_id int(11) NOT NULL DEFAULT 0,
                 languages_id int(11) NOT NULL DEFAULT 0,
                 type_description VARCHAR(64) DEFAULT NULL,
                 type_image VARCHAR(64) DEFAULT NULL,
                 PRIMARY KEY (bookx_author_type_id, languages_id)
               ) ENGINE=MyISAM DEFAULT CHARSET={$default_db_encoding};
EOT;
        $db->Execute($sql);

        $sql = <<<EOT

               # Table structure for table product_bookx_binding
               #DROP TABLE IF EXISTS {$const['TABLE_PRODUCT_BOOKX_BINDING']};
               CREATE TABLE {$const['TABLE_PRODUCT_BOOKX_BINDING']} (
                 bookx_binding_id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                 binding_sort_order int(11) DEFAULT 0,
                 KEY idx_bxb_binding_id (bookx_binding_id ASC)
               ) ENGINE=MyISAM DEFAULT CHARSET={$default_db_encoding};
EOT;
        $db->Execute($sql);

        $sql = <<<EOT

               # Table structure for table product_bookx_binding_description
               #DROP TABLE IF EXISTS {$const['TABLE_PRODUCT_BOOKX_BINDING_DESCRIPTION']};
               CREATE TABLE {$const['TABLE_PRODUCT_BOOKX_BINDING_DESCRIPTION']} (
                 bookx_binding_id int(11) NOT NULL DEFAULT 0,
                 languages_id int(11) NOT NULL DEFAULT 0,
                 binding_description VARCHAR(64) DEFAULT NULL,
                 PRIMARY KEY  (bookx_binding_id, languages_id)
               ) ENGINE=MyISAM DEFAULT CHARSET={$default_db_encoding};
EOT;
        $db->Execute($sql);


        $sql = <<<EOT

               # Table structure for table product_bookx_conditions
               #DROP TABLE IF EXISTS {$const['TABLE_PRODUCT_BOOKX_CONDITIONS']};
               CREATE TABLE {$const['TABLE_PRODUCT_BOOKX_CONDITIONS']} (
                 bookx_condition_id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                 condition_sort_order int(11) DEFAULT 0
               ) ENGINE=MyISAM DEFAULT CHARSET={$default_db_encoding};

EOT;
        $db->Execute($sql);

        $sql = <<<EOT

               # Table structure for table product_bookx_conditions_description
               #DROP TABLE IF EXISTS {$const['TABLE_PRODUCT_BOOKX_CONDITIONS_DESCRIPTION']};
               CREATE TABLE {$const['TABLE_PRODUCT_BOOKX_CONDITIONS_DESCRIPTION']} (
                 bookx_condition_id int(11) NOT NULL DEFAULT 0,
                 languages_id int(11) NOT NULL DEFAULT 0,
                 condition_description VARCHAR(64) DEFAULT NULL,
                 PRIMARY KEY  (bookx_condition_id, languages_id)
               ) ENGINE=MyISAM DEFAULT CHARSET={$default_db_encoding};

EOT;
        $db->Execute($sql);

        $sql = <<<EOT

               # Table structure for table product_bookx_genres
               #DROP TABLE IF EXISTS {$const['TABLE_PRODUCT_BOOKX_GENRES']};
               CREATE TABLE {$const['TABLE_PRODUCT_BOOKX_GENRES']} (
                 bookx_genre_id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                 genre_sort_order int(11) DEFAULT 0,
               	 date_added datetime DEFAULT NULL,
               	 last_modified datetime DEFAULT NULL,
               	 INDEX (bookx_genre_id)
               ) ENGINE=MyISAM DEFAULT CHARSET={$default_db_encoding};
EOT;
        $db->Execute($sql);

        $sql = <<<EOT

               # Table structure for table product_bookx_genres_description
               #DROP TABLE IF EXISTS {$const['TABLE_PRODUCT_BOOKX_GENRES_DESCRIPTION']};
               CREATE TABLE {$const['TABLE_PRODUCT_BOOKX_GENRES_DESCRIPTION']} (
                 bookx_genre_id int(11) NOT NULL DEFAULT 0,
                 languages_id int(11) NOT NULL DEFAULT 0,
                 genre_name VARCHAR(64) DEFAULT NULL,
                 genre_image VARCHAR(64) DEFAULT NULL,
                 PRIMARY KEY  (bookx_genre_id, languages_id),
                UNIQUE KEY idx_bxgd_genre_name (genre_name) 
               ) ENGINE=MyISAM DEFAULT CHARSET={$default_db_encoding};
EOT;
        $db->Execute($sql);


        $sql = <<<EOT

               # Table structure for table product_bookx_genres_to_products
               #DROP TABLE IF EXISTS {$const['TABLE_PRODUCT_BOOKX_GENRES_TO_PRODUCTS']};
               CREATE TABLE {$const['TABLE_PRODUCT_BOOKX_GENRES_TO_PRODUCTS']} (
               	 primary_id int(11) NOT NULL AUTO_INCREMENT,
                 bookx_genre_id int(11) NOT NULL DEFAULT 0,
                 products_id int(11) NOT NULL DEFAULT 0,
                 PRIMARY KEY  (primary_id),
                 INDEX (products_id, bookx_genre_id)
               ) ENGINE=MyISAM DEFAULT CHARSET={$default_db_encoding};
EOT;
        $db->Execute($sql);

        $sql = <<<EOT

               # Table structure for table product_bookx_imprints
               #DROP TABLE IF EXISTS {$const['TABLE_PRODUCT_BOOKX_IMPRINTS']};
               CREATE TABLE {$const['TABLE_PRODUCT_BOOKX_IMPRINTS']} (
                 bookx_imprint_id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
               	 imprint_name VARCHAR(64) DEFAULT NULL,
                 imprint_sort_order int(11) DEFAULT 0,
               	 imprint_image VARCHAR(64) DEFAULT NULL,
               	 date_added datetime DEFAULT NULL,
               	 last_modified datetime DEFAULT NULL,
                 KEY idx_bxi_imprint_name (imprint_name)
               ) ENGINE=MyISAM DEFAULT CHARSET={$default_db_encoding};
EOT;
        $db->Execute($sql);

        $sql = <<<EOT

               # Table structure for table product_bookx_imprints_description
               #DROP TABLE IF EXISTS {$const['TABLE_PRODUCT_BOOKX_IMPRINTS_DESCRIPTION']};
               CREATE TABLE {$const['TABLE_PRODUCT_BOOKX_IMPRINTS_DESCRIPTION']} (
                 bookx_imprint_id int(11) NOT NULL DEFAULT 0,
                 languages_id int(11) NOT NULL DEFAULT 0,
                 imprint_description TEXT,
                 PRIMARY KEY  (bookx_imprint_id, languages_id)
               ) ENGINE=MyISAM DEFAULT CHARSET={$default_db_encoding};
EOT;
        $db->Execute($sql);

        $sql = <<<EOT

               # Table structure for table product_bookx_printing
               #DROP TABLE IF EXISTS {$const['TABLE_PRODUCT_BOOKX_PRINTING']};
               CREATE TABLE {$const['TABLE_PRODUCT_BOOKX_PRINTING']} (
                 bookx_printing_id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                 printing_sort_order int(11) DEFAULT 0
               ) ENGINE=MyISAM DEFAULT CHARSET={$default_db_encoding};
EOT;
        $db->Execute($sql);

        $sql = <<<EOT

               # Table structure for table product_bookx_printing_description
               #DROP TABLE IF EXISTS {$const['TABLE_PRODUCT_BOOKX_PRINTING_DESCRIPTION']};
               CREATE TABLE {$const['TABLE_PRODUCT_BOOKX_PRINTING_DESCRIPTION']} (
                 bookx_printing_id int(11) NOT NULL DEFAULT 0,
                 languages_id int(11) NOT NULL DEFAULT 0,
                 printing_description VARCHAR(64) DEFAULT NULL,
                 PRIMARY KEY  (bookx_printing_id, languages_id)
               ) ENGINE=MyISAM DEFAULT CHARSET={$default_db_encoding};
EOT;
        $db->Execute($sql);

        $sql = <<<EOT

               # Table structure for table product_bookx_publishers
               #DROP TABLE IF EXISTS {$const['TABLE_PRODUCT_BOOKX_PUBLISHERS']};
               CREATE TABLE {$const['TABLE_PRODUCT_BOOKX_PUBLISHERS']} (
                 bookx_publisher_id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                 publisher_name VARCHAR(64) DEFAULT NULL,
                 publisher_image VARCHAR(64) DEFAULT NULL,
                 publisher_sort_order int(11) DEFAULT 0,
               	 date_added datetime DEFAULT NULL,
               	 last_modified datetime DEFAULT NULL,
               	 KEY idx_bxp_publisher_name (publisher_name)
               ) ENGINE=MyISAM DEFAULT CHARSET={$default_db_encoding};
EOT;
        $db->Execute($sql);

        $sql = <<<EOT

               # Table structure for table product_bookx_publishers_description
               #DROP TABLE IF EXISTS {$const['TABLE_PRODUCT_BOOKX_PUBLISHERS_DESCRIPTION']};
               CREATE TABLE {$const['TABLE_PRODUCT_BOOKX_PUBLISHERS_DESCRIPTION']} (
                 bookx_publisher_id int(11) NOT NULL DEFAULT 0,
                 languages_id int(11) NOT NULL DEFAULT 0,
                 publisher_url VARCHAR(255) DEFAULT NULL,
                 publisher_description TEXT,
                 PRIMARY KEY  (bookx_publisher_id, languages_id)
               ) ENGINE=MyISAM DEFAULT CHARSET={$default_db_encoding};
EOT;
        $db->Execute($sql);

        $sql = <<<EOT

               # Table structure for table product_bookx_series
               #DROP TABLE IF EXISTS {$const['TABLE_PRODUCT_BOOKX_SERIES']};
               CREATE TABLE {$const['TABLE_PRODUCT_BOOKX_SERIES']} (
                 bookx_series_id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                 series_sort_order int(11) DEFAULT 0,
               	 date_added datetime DEFAULT NULL,
               	 last_modified datetime DEFAULT NULL
               ) ENGINE=MyISAM DEFAULT CHARSET={$default_db_encoding};
EOT;
        $db->Execute($sql);

        $sql = <<<EOT

               # Table structure for table product_bookx_series_description lllll
               #DROP TABLE IF EXISTS {$const['TABLE_PRODUCT_BOOKX_SERIES_DESCRIPTION']};
               CREATE TABLE {$const['TABLE_PRODUCT_BOOKX_SERIES_DESCRIPTION']} (
                 bookx_series_id int(11) NOT NULL DEFAULT 0,
                 languages_id int(11) NOT NULL DEFAULT 0,
                 series_image VARCHAR(64) DEFAULT NULL,
                 series_name VARCHAR(64) DEFAULT NULL,
               	 series_description TEXT,
                 PRIMARY KEY  (bookx_series_id, languages_id),
                 KEY idx_bxsd_series_name (series_name ASC)
               ) ENGINE=MyISAM DEFAULT CHARSET={$default_db_encoding};
EOT;
        $db->Execute($sql);
   
        /**
         * @since v1.0.0
         */
        $db->Execute("DROP TABLE IF EXISTS {$const['TABLE_PRODUCT_BOOKX_FAMILIES']}");
        $sql = <<<EOT
        
        CREATE TABLE {$const['TABLE_PRODUCT_BOOKX_FAMILIES']} (
            bookx_family_id int(11) NOT NULL,
            bookx_family_name varchar(64) NOT NULL,
            bookx_family_discount float NOT NULL DEFAULT '0',
            bookx_family_stock_online tinyint(1) NOT NULL DEFAULT '0',
            PRIMARY KEY (bookx_family_id),
            UNIQUE KEY bookx_family_name (bookx_family_name),
            KEY idx_family_id (bookx_family_id, bookx_family_name)) 
            ENGINE=InnoDB DEFAULT CHARSET={$default_db_encoding};
        
EOT;
        $db->Execute($sql);
        $db->Execute("DROP TABLE IF EXISTS ".TABLE_PRODUCT_BOOKX_FAMILIES_TO_PRODUCTS.";");
        $sql = <<<EOT
         
         CREATE TABLE {$const['TABLE_PRODUCT_BOOKX_FAMILIES_TO_PRODUCTS']} (
            primary_id int(11) NOT NULL AUTO_INCREMENT,
            products_id int(11) NOT NULL,
            bookx_family_id int(11) NOT NULL,
            PRIMARY KEY (primary_id),
            KEY bookx_family_id (bookx_family_id),
            CONSTRAINT fk_bxt_families_id FOREIGN KEY (bookx_family_id) 
            REFERENCES product_bookx_families (bookx_family_id) 
            ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET={$default_db_encoding};
         
EOT;
        $db->Execute($sql);
        
        $db->Execute("DROP TABLE IF EXISTS ".TABLE_PRODUCT_BOOKX_SEARCH.";");
        
        $sql = "CREATE TABLE ".TABLE_PRODUCT_BOOKX_SEARCH." (
            search_index int(11) NOT NULL AUTO_INCREMENT,
            language_id int(11) NOT NULL,
            product_id int(11) NOT NULL,
            publisher_name varchar(64) DEFAULT NULL,
            series_name varchar(64) DEFAULT NULL,
            isbn varchar(13) DEFAULT NULL,
            products_subtitle varchar(128) DEFAULT NULL,
            author_name varchar(128) DEFAULT NULL,
            genre_name varchar(128) DEFAULT NULL,
            PRIMARY KEY (search_index, language_id),
            KEY idx_pbxs_product_id (product_id),
            KEY idx_pbxs_author_name (author_name),
            KEY idx_pbxs_publisher (publisher_name),
            KEY idx_pbxs_isbn (isbn),
            KEY idx_pbxs_series_name (series_name)
            ) ENGINE=InnoDB DEFAULT CHARSET=".$default_db_encoding.";";
        
        $db->Execute($sql);

        
        $messageStack->add(''.BOOKX_MS_DB_TABLES_SUCCESS.'', 'success');
    } // eof create DB tables
        
        
    //******* we don't break here!
      
    // no break
    case (('install' == $bookx_install) || ('reset' == $bookx_install) && (!$login_page)):
        
    // ======================================================
        //
        // register BookX in admin pages for Zen 1.5
        //
        // ======================================================
        
        if (defined('TABLE_ADMIN_PAGES') && defined('TABLE_ADMIN_PAGES_TO_PROFILES')) {
            zen_deregister_admin_pages($admin_page_keys);
             
            zen_register_admin_page('configBookXTools', 'TOOLS_MENU_PRODUCT_BOOKX', 'FILENAME_BOOKX_TOOLS', '', 'tools', 'Y', 20);
            zen_register_admin_page('bookxAuthors', 'BOX_CATALOG_PRODUCT_BOOKX_AUTHORS', 'FILENAME_BOOKX_AUTHORS', '', 'extras', 'Y', 20);
            zen_register_admin_page('bookxAuthorTypes', 'BOX_CATALOG_PRODUCT_BOOKX_AUTHOR_TYPES', 'FILENAME_BOOKX_AUTHOR_TYPES', '', 'extras', 'Y', 25);
            zen_register_admin_page('bookxBinding', 'BOX_CATALOG_PRODUCT_BOOKX_BINDING', 'FILENAME_BOOKX_BINDING', '', 'extras', 'Y', 30);
            zen_register_admin_page('bookxConditions', 'BOX_CATALOG_PRODUCT_BOOKX_CONDITIONS', 'FILENAME_BOOKX_CONDITIONS', '', 'extras', 'Y', 50);
            zen_register_admin_page('bookxGenres', 'BOX_CATALOG_PRODUCT_BOOKX_GENRES', 'FILENAME_BOOKX_GENRES', '', 'extras', 'Y', 60);
            zen_register_admin_page('bookxImprints', 'BOX_CATALOG_PRODUCT_BOOKX_IMPRINTS', 'FILENAME_BOOKX_IMPRINTS', '', 'extras', 'Y', 80);
            zen_register_admin_page('bookxPrinting', 'BOX_CATALOG_PRODUCT_BOOKX_PRINTING', 'FILENAME_BOOKX_PRINTING', '', 'extras', 'Y', 40);
            zen_register_admin_page('bookxPublishers', 'BOX_CATALOG_PRODUCT_BOOKX_PUBLISHERS', 'FILENAME_BOOKX_PUBLISHERS', '', 'extras', 'Y', 70);
            zen_register_admin_page('bookxSeries', 'BOX_CATALOG_PRODUCT_BOOKX_SERIES', 'FILENAME_BOOKX_SERIES', '', 'extras', 'Y', 90);
            zen_register_admin_page('bookxFamilies', 'BOX_CATALOG_PRODUCT_BOOKX_FAMILIES', 'FILENAME_BOOKX_FAMILIES', '', 'extras', 'Y', 91);
            //zen_register_admin_page('bookxProduct', 'BOX_CATALOG_PRODUCT_BOOKX', 'FILENAME_BOOKX_PRODUCT', '', 'catalog', 'Y', 2);
            echo BOX_CATALOG_PRODUCT_BOOKX_SERIES;
        /**
         * @todo Check this query, no "product" key is present in TABLE_ADMIN_PAGES_TO_PROFILES
         */
//	         $sql = "SELECT profile_id FROM {$const['TABLE_ADMIN_PAGES_TO_PROFILES']} WHERE page_key = 'product'";
//	         $profile_ids = $db->Execute($sql);
//
//	         while (!$profile_ids->EOF) {
//	         	$db->Execute("REPLACE INTO {$const['TABLE_ADMIN_PAGES_TO_PROFILES']} (profile_id, page_key) VALUES ('{$profile_ids->fields['profile_id']}', 'bookxProduct')");
//	         	$profile_ids->MoveNext();
//	         }
        } else {
            $messageStack->add_session(sprintf(BOOKX_MS_TABLE_DOESNT_EXIST, 'TABLE_ADMIN_PAGES'), 'warning');
        }
      
    $sql = "SELECT type_id FROM {$const['TABLE_PRODUCT_TYPES']} WHERE type_handler= 'product_bookx'";

    $product_type = $db->Execute($sql);
    $bookx_ptypeID = null;
    
    while (!$product_type->EOF) {
        $bookx_ptypeID = $product_type->fields['type_id'];
        $product_type->MoveNext();
    }
   
    if (!empty($bookx_ptypeID)) {
        $sql = <<<EOT

          REPLACE INTO {$const['TABLE_PRODUCT_TYPE_LAYOUT']} (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, last_modified, date_added, use_function, set_function )
                              VALUES
                               # settings for product type bookx only
                                         ('Product Listing: Show Model Number', 'SHOW_PRODUCT_BOOKX_LISTING_MODEL', '1', 'Display Model Number on Product Listing.', {$bookx_ptypeID}, '10', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
                                         ('Product Listing: Show ISBN', 'SHOW_PRODUCT_BOOKX_LISTING_ISBN', '1', 'Display ISBN on Product Listing.', {$bookx_ptypeID}, '15', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
                                         ('Product Listing: Show Subtitle', 'SHOW_PRODUCT_BOOKX_LISTING_SUBTITLE', '1', 'Display Subtitle on Product Listing.', {$bookx_ptypeID}, '20', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
                                         ('Product Listing: Show No. of Pages', 'SHOW_PRODUCT_BOOKX_LISTING_PAGES', '1', 'Display Number of Pages on Product Listing.', {$bookx_ptypeID}, '30', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
                                         ('Product Listing: Show Printing Type', 'SHOW_PRODUCT_BOOKX_LISTING_PRINTING', '1', 'Display Type of Printing on Product Listing.', {$bookx_ptypeID}, '40', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
                                         ('Product Listing: Show Binding Type', 'SHOW_PRODUCT_BOOKX_LISTING_BINDING', '1', 'Display Type of Binding on Product Listing.', {$bookx_ptypeID}, '50', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
                                         ('Product Listing: Show Size', 'SHOW_PRODUCT_BOOKX_LISTING_SIZE', '1', 'Display Size on Product Listing.', {$bookx_ptypeID}, '60', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
                                         ('Product Listing: Show Volume No.', 'SHOW_PRODUCT_BOOKX_LISTING_VOLUME', '1', 'Display Volume Number on Product Listing.', {$bookx_ptypeID}, '70', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
                                         ('Product Listing: Show Publishing Date', 'SHOW_PRODUCT_BOOKX_LISTING_PUBLISH_DATE', '1', 'Display Publishing Date on Product Listing.', {$bookx_ptypeID}, '80', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
                                         ('Product Listing: Show Publisher', 'SHOW_PRODUCT_BOOKX_LISTING_PUBLISHER', '1', 'Display Publisher on Product Listing.', {$bookx_ptypeID}, '90', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
                                         ('Product Listing: Show Publisher as Link', 'SHOW_PRODUCT_BOOKX_LISTING_PUBLISHER_AS_LINK', '1', 'Display Publisher on Product Listing as clickable link, which will list all products for this publisher.', {$bookx_ptypeID}, '90', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED'))),"),
                                         ('Product Listing: Show Publisher Image/Logo', 'SHOW_PRODUCT_BOOKX_LISTING_PUBLISHER_IMAGE', '1', 'Display Publisher Image on Product Listing. In case of an undefined Image, the Publishers Name will be shown.', {$bookx_ptypeID}, '91', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
                                         ('Product Listing: Show Publisher Url', 'SHOW_PRODUCT_BOOKX_LISTING_PUBLISHER_URL', '0', 'Display Publisher URL on Product Listing.', {$bookx_ptypeID}, '92', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
                                         ('Product Listing: Show Publisher Description', 'SHOW_PRODUCT_BOOKX_LISTING_PUBLISHER_DESCRIPTION', '0', 'Display Publisher Description on Product Listing.', {$bookx_ptypeID}, '93', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
                                         ('Product Listing: Show Imprint', 'SHOW_PRODUCT_BOOKX_LISTING_IMPRINT', '1', 'Display Imprint/Sublabel on Product Listing.', {$bookx_ptypeID}, '100', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
                                        ('Product Listing: Show Imprint as Link', 'SHOW_PRODUCT_BOOKX_LISTING_IMPRINT_AS_LINK', '1', 'Display Imprint on Product Listing as clickable link, which will list all products for this Imprint.', {$bookx_ptypeID}, '90', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED'))),"),
                                         ('Product Listing: Show Imprint Image', 'SHOW_PRODUCT_BOOKX_LISTING_IMPRINT_IMAGE', '1', 'Display Imprint/Sublabel Image on Product Listing. In case of an undefined image, the name will be shown.', {$bookx_ptypeID}, '101', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
                                         ('Product Listing: Show Imprint Description', 'SHOW_PRODUCT_BOOKX_LISTING_IMPRINT_DESCRIPTION', '0', 'Display Imprint/Sublabel Description on Product Listing.', {$bookx_ptypeID}, '102', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
                                         ('Product Listing: Show Series', 'SHOW_PRODUCT_BOOKX_LISTING_SERIES', '1', 'Display Series on Product Listing.', {$bookx_ptypeID}, '110', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
                                        ('Product Listing: Show Series as Link', 'SHOW_PRODUCT_BOOKX_LISTING_SERIES_AS_LINK', '1', 'Display Series on Product Listing as clickable link, which will list all products for this Series.', {$bookx_ptypeID}, '90', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED'))),"),

                                         ('Product Listing: Show Series Image', 'SHOW_PRODUCT_BOOKX_LISTING_SERIES_IMAGE', '1', 'Display Series Image on Product Listing. In case of an undefined image, the name will be shown.', {$bookx_ptypeID}, '111', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
                                         ('Product Listing: Show Series Description', 'SHOW_PRODUCT_BOOKX_LISTING_SERIES_DESCRIPTION', '0', 'Display Series Description on Product Listing.', {$bookx_ptypeID}, '112', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
                                         ('Product Listing: Show Authors', 'SHOW_PRODUCT_BOOKX_LISTING_AUTHORS', '1', 'Display Authors on Product Listing.', {$bookx_ptypeID}, '117', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
                                         ('Product Listing: Show only Authors with Type Sort Oder below', 'SHOW_PRODUCT_BOOKX_LISTING_AUTHORS_WITH_TYPE_BELOW_SORT_ORDER', '1000', 'Display only Authors on Product Listing which are of an Author Type with a Sort Order smaller than this value. Example: Default value of "1000" means that authors of type e.g. "Illustrator" will not be shown on product listing, if the author type "Illustrator" has a sort order of "1000" or greater. This way multiple authors can be given more or less "importance". If you enter a value "0" then this setting is ignored.', {$bookx_ptypeID}, '122', now(), now(), NULL, NULL),
                                         ('Product Listing: Show Authors as Link', 'SHOW_PRODUCT_BOOKX_LISTING_AUTHORS_AS_LINK', '1', 'Display Authors on Product Listing as clickable link, which will list all products for this Author.', {$bookx_ptypeID}, '120', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED'))),"),
                                         ('Product Listing: Show Authors Image', 'SHOW_PRODUCT_BOOKX_LISTING_AUTHORS_IMAGE', '0', 'Display Authors image on Product Listing. In case of an undefined Image, the Authors Name will be shown.', {$bookx_ptypeID}, '121', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
                                         ('Product Listing: Show Authors Url', 'SHOW_PRODUCT_BOOKX_LISTING_AUTHORS_URL', '0', 'Display Authors URL on Product Listing.', {$bookx_ptypeID}, '122', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
                                         ('Product Listing: Show Authors Description', 'SHOW_PRODUCT_BOOKX_LISTING_AUTHORS_DESCRIPTION', '0', 'Display Authors description on Product Listing.', {$bookx_ptypeID}, '123', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
                                         ('Product Listing: Show Author Type', 'SHOW_PRODUCT_BOOKX_LISTING_AUTHOR_TYPE', '1', 'Display Author Type on Product Listing.', {$bookx_ptypeID}, '124', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
                                         ('Product Listing: Show Author Type Image', 'SHOW_PRODUCT_BOOKX_LISTING_AUTHOR_TYPE_IMAGE', '1', 'Display Author Type Image on Product Listing.', {$bookx_ptypeID}, '125', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
                                         ('Product Listing: Show Genres', 'SHOW_PRODUCT_BOOKX_LISTING_GENRES', '1', 'Display Genres on Product Listing.', {$bookx_ptypeID}, '130', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
                                        ('Product Listing: Show Genres as Link', 'SHOW_PRODUCT_BOOKX_LISTING_GENRES_AS_LINK', '1', 'Display Genres on Product Listing as clickable link, which will list all products for this Genre.', {$bookx_ptypeID}, '90', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED'))),"),
                                         ('Product Listing: Show Genre Image', 'SHOW_PRODUCT_BOOKX_LISTING_GENRE_IMAGE', '1', 'Display Genre Image on Product Listing. In case of an undefined image, the name will be shown.', {$bookx_ptypeID}, '131', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
                                         ('Product Listing: Show Condition', 'SHOW_PRODUCT_BOOKX_LISTING_CONDITION', '1', 'Display Book Condition on Product Listing.', {$bookx_ptypeID}, '140', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
                                         ('Product Listing: Group Products by availability', 'GROUP_PRODUCT_BOOKX_LISTING_BY_AVAILABILITY', '1', 'Group products in any product listing according to availability. Order: <br />1) Upcoming products <br />2) New products <br />3) Published / available products 4) Out of print <br /><br />Criteria for "new" and "upcoming" books are set in Admin -> Configuration -> BookX Configuration.', {$bookx_ptypeID}, '150', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED'))),"),

                                         ('Product Detail: Show Subtitle', 'SHOW_PRODUCT_BOOKX_INFO_SUBTITLE', '1', 'Display Subtitle on Product Info.', {$bookx_ptypeID}, '150', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
                                         ('Product Detail: Show No. of Pages', 'SHOW_PRODUCT_BOOKX_INFO_PAGES', '1', 'Display Number of Pages on Product Info.', {$bookx_ptypeID}, '160', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
                                         ('Product Detail: Show Printing Type', 'SHOW_PRODUCT_BOOKX_INFO_PRINTING', '1', 'Display Type of Printing on Product Info.', {$bookx_ptypeID}, '170', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
                                         ('Product Detail: Show Binding Type', 'SHOW_PRODUCT_BOOKX_INFO_BINDING', '1', 'Display Type of Binding on Product Info.', {$bookx_ptypeID}, '180', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
                                         ('Product Detail: Show Size', 'SHOW_PRODUCT_BOOKX_INFO_SIZE', '1', 'Display Size on Product Info.', {$bookx_ptypeID}, '190', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
                                         ('Product Detail: Show Volume No.', 'SHOW_PRODUCT_BOOKX_INFO_VOLUME', '1', 'Display Volume Number on Product Info.', {$bookx_ptypeID}, '200', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
                                         ('Product Detail: Show Publishing Date', 'SHOW_PRODUCT_BOOKX_INFO_PUBLISH_DATE', '1', 'Display Publishing Date on Product Info.', {$bookx_ptypeID}, '210', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
                                         ('Product Detail: Show Publisher', 'SHOW_PRODUCT_BOOKX_INFO_PUBLISHER', '1', 'Display Publisher on Product Info.', {$bookx_ptypeID}, '220', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
                                         ('Product Detail: Show Publisher as Link', 'SHOW_PRODUCT_BOOKX_INFO_PUBLISHER_AS_LINK', '1', 'Display Publisher on Product Info as clickable link, which will list all products for this publisher.', {$bookx_ptypeID}, '221', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED'))),"),
                                         ('Product Detail: Show Publisher Image/Logo', 'SHOW_PRODUCT_BOOKX_INFO_PUBLISHER_IMAGE', '1', 'Display Publisher Image/Logo on Product Info. In case of an undefined image, the name will be shown.', {$bookx_ptypeID}, '222', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
                                         ('Product Detail: Show Publisher URL', 'SHOW_PRODUCT_BOOKX_INFO_PUBLISHER_URL', '1', 'Display Publisher URL on Product Info.', {$bookx_ptypeID}, '223', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
                                         ('Product Detail: Show Publisher Description', 'SHOW_PRODUCT_BOOKX_INFO_PUBLISHER_DESCRIPTION', '1', 'Display Publisher Description on Product Info.', {$bookx_ptypeID}, '224', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
                                         ('Product Detail: Show Imprint', 'SHOW_PRODUCT_BOOKX_INFO_IMPRINT', '1', 'Display Imprint/Sublabel on Product Info.', {$bookx_ptypeID}, '230', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
                                         ('Product Detail: Show Imprint as Link', 'SHOW_PRODUCT_BOOKX_INFO_IMPRINT_AS_LINK', '1', 'Display Imprint on Product Info as clickable link, which will list all products for this Imprint.', {$bookx_ptypeID}, '231', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED'))),"),
                                         ('Product Detail: Show Imprint Image', 'SHOW_PRODUCT_BOOKX_INFO_IMPRINT_IMAGE', '1', 'Display Imprint/Sublabel Image on Product Info. In case of an undefined image, the name will be shown.', {$bookx_ptypeID}, '232', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
                                         ('Product Detail: Show Imprint Description', 'SHOW_PRODUCT_BOOKX_INFO_IMPRINT_DESCRIPTION', '1', 'Display Imprint/Sublabel Description  on Product Info.', {$bookx_ptypeID}, '233', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
                                         ('Product Detail: Show Series', 'SHOW_PRODUCT_BOOKX_INFO_SERIES', '1', 'Display Series on Product Info.', {$bookx_ptypeID}, '240', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
                                         ('Product Detail: Show Series as Link', 'SHOW_PRODUCT_BOOKX_INFO_SERIES_AS_LINK', '1', 'Display Series on Product Info as clickable link, which will list all products for this Series.', {$bookx_ptypeID}, '241', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED'))),"),
                                         ('Product Detail: Show Series Image', 'SHOW_PRODUCT_BOOKX_INFO_SERIES_IMAGE', '1', 'Display Series Image Product Info. In case of an undefined image, the name will be shown.', {$bookx_ptypeID}, '242', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
                                         ('Product Detail: Show Series Description', 'SHOW_PRODUCT_BOOKX_INFO_SERIES_DESCRIPTION', '1', 'Display Series Descriptionon Product Info.', {$bookx_ptypeID}, '243', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
                                         ('Product Detail: Show Authors', 'SHOW_PRODUCT_BOOKX_INFO_AUTHORS', '1', 'Display Authors on Product Info.', {$bookx_ptypeID}, '250', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
                                         ('Product Detail: Show Authors as Link', 'SHOW_PRODUCT_BOOKX_INFO_AUTHORS_AS_LINK', '1', 'Display Authors on Product Info as clickable link, which will list all products for this Author.', {$bookx_ptypeID}, '251', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED'))),"),
                                         ('Product Detail: Show Authors Image', 'SHOW_PRODUCT_BOOKX_INFO_AUTHORS_IMAGE', '1', 'Display Authors image on Product Info. In case of an undefined image, the name will be shown.', {$bookx_ptypeID}, '252', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
                                         ('Product Detail: Show Authors Url', 'SHOW_PRODUCT_BOOKX_INFO_AUTHORS_URL', '1', 'Display Authors URL on Product Info.', {$bookx_ptypeID}, '253', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
                                         ('Product Detail: Show Authors Description', 'SHOW_PRODUCT_BOOKX_INFO_AUTHORS_DESCRIPTION', '1', 'Display Authors Description on Product Info.', {$bookx_ptypeID}, '254', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
                                         ('Product Detail: Show Author Type', 'SHOW_PRODUCT_BOOKX_INFO_AUTHOR_TYPE', '1', 'Display Authors Type  on Product Info.', {$bookx_ptypeID}, '255', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
                                         ('Product Detail: Show Authors Type Image', 'SHOW_PRODUCT_BOOKX_INFO_AUTHOR_TYPE_IMAGE', '1', 'Display Authors Type Image on Product Info.', {$bookx_ptypeID}, '256', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
                                         ('Product Detail: Show Authors related books', 'SHOW_PRODUCT_BOOKX_INFO_AUTHORS_RELATED_PRODUCTS', '1', 'Display other books by the same Author on Product Info.', {$bookx_ptypeID}, '257', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
                                         ('Product Detail: Order Authors by', 'ORDER_PRODUCT_BOOKX_INFO_AUTHORS', '1', 'Order Authors on Product Info page by: ', {$bookx_ptypeID}, '258', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ORDER_BY_NAME')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ORDER_BY_SORT_ORDER'), array('id'=>'3', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ORDER_BY_TYPE_NAME')), array('id'=>'4', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ORDER_BY_TYPE_SORT_ORDER'))), "),
                                         ('Product Detail: Show Genres', 'SHOW_PRODUCT_BOOKX_INFO_GENRES', '1', 'Display Genres on Product Info.', {$bookx_ptypeID}, '260', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
                                         ('Product Detail: Show Genres as Link', 'SHOW_PRODUCT_BOOKX_INFO_GENRES_AS_LINK', '1', 'Display Genres on Product Info as clickable link, which will list all products for this Genre.', {$bookx_ptypeID}, '261', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED'))),"),
                                         ('Product Detail: Show Genre Image', 'SHOW_PRODUCT_BOOKX_INFO_GENRE_IMAGES', '1', 'Display Genre Images on Product Info. In case of an undefined image, the name will be shown.', {$bookx_ptypeID}, '262', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
                                         ('Product Detail: Order Genres by', 'ORDER_PRODUCT_BOOKX_INFO_GENRES', '1', 'Order Genres on Product Info page by: ', {$bookx_ptypeID}, '263', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ORDER_BY_NAME')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ORDER_BY_SORT_ORDER'))),"),
                                         ('Product Detail: Show Condition', 'SHOW_PRODUCT_BOOKX_INFO_CONDITION', '1', 'Display Book Condition on Product Info.', {$bookx_ptypeID}, '270', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),


                               # settings for all products
                                        ('Product Detail: Show Model Number', 'SHOW_PRODUCT_BOOKX_INFO_MODEL', '1', 'Display Model Number on Product Info.', {$bookx_ptypeID}, '275', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
                                         ('Product Detail: Show ISBN', 'SHOW_PRODUCT_BOOKX_INFO_ISBN', '1', 'Display ISBN on Product Info.', {$bookx_ptypeID}, '277', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),

                                         ('Product Detail: Show Weight', 'SHOW_PRODUCT_BOOKX_INFO_WEIGHT', '1', 'Display Weight on Product Info.', {$bookx_ptypeID}, '280', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
		                                ('Product Detail: Show Attribute Weight', 'SHOW_PRODUCT_BOOKX_INFO_WEIGHT_ATTRIBUTES', '1', 'Display Attribute Weight on Product Info.', {$bookx_ptypeID}, '290', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
		                                ('Product Detail: Show Manufacturer', 'SHOW_PRODUCT_BOOKX_INFO_MANUFACTURER', '1', 'Display Manufacturer Name on Product Info.', {$bookx_ptypeID}, '300', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
		                                ('Product Detail: Show Quantity in Shopping Cart', 'SHOW_PRODUCT_BOOKX_INFO_IN_CART_QTY', '1', 'Display Quantity in Current Shopping Cart on Product Info.', {$bookx_ptypeID}, '310', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
		                                ('Product Detail: Show Quantity in Stock', 'SHOW_PRODUCT_BOOKX_INFO_QUANTITY', '1', 'Display Quantity in Stock on Product Info.', {$bookx_ptypeID}, '320', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
		                                ('Product Detail: Show Product Reviews Count', 'SHOW_PRODUCT_BOOKX_INFO_REVIEWS_COUNT', '1', 'Display Product Reviews Count on Product Info.', {$bookx_ptypeID}, '330', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
		                                ('Product Detail: Show Product Reviews Button', 'SHOW_PRODUCT_BOOKX_INFO_REVIEWS', '1', 'Display Product Reviews Button on Product Info.', {$bookx_ptypeID}, '340', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
		                                ('Product Detail: Show Date Available', 'SHOW_PRODUCT_BOOKX_INFO_DATE_AVAILABLE', '1', 'Display Date Available on Product Info.', {$bookx_ptypeID}, '350', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
		                                ('Product Detail: Show Date Added', 'SHOW_PRODUCT_BOOKX_INFO_DATE_ADDED', '1', 'Display Date Added on Product Info.', {$bookx_ptypeID}, '360', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
		                                ('Product Detail: Show Product URL', 'SHOW_PRODUCT_BOOKX_INFO_URL', '1', 'Display URL on Product Info.', {$bookx_ptypeID}, '370', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
                                         ('Product Detail: Show Starting At text on Price', 'SHOW_PRODUCT_BOOKX_INFO_STARTING_AT', '1', 'Display Starting At text on products with attributes Product Info.', {$bookx_ptypeID}, '380', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
		                                ('Product Detail: Show Product Tell a Friend button', 'SHOW_PRODUCT_BOOKX_INFO_TELL_A_FRIEND', '1', 'Display the Tell a Friend button on Product Info<br /><br />Note: Turning this setting off does not affect the Tell a Friend box in the columns and turning off the Tell a Friend box does not affect the button<br />0= off 1= on', {$bookx_ptypeID}, '390', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
		                                ('Product Detail: Show Product Additional Images', 'SHOW_PRODUCT_BOOKX_INFO_ADDITIONAL_IMAGES', '1', 'Display Additional Images on Product Info.', {$bookx_ptypeID}, '395', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
		                                ('Product Detail: Product Free Shipping Image Status - Catalog', 'SHOW_PRODUCT_BOOKX_INFO_ALWAYS_FREE_SHIPPING_IMAGE_SWITCH', '0', 'Show the Free Shipping image/text in the catalog?', {$bookx_ptypeID}, '400', now(), now(), NULL, 'zen_cfg_select_drop_down(array(array(''id''=>''1'', ''text''=>''Yes''), array(''id''=>''0'', ''text''=>''No'')), '),

                              # settings for admin
		                                ('Product Price Tax Class Default - When adding new products?', 'DEFAULT_PRODUCT_BOOKX_TAX_CLASS_ID', '0', 'What should the Product Price Tax Class Default ID be when adding new products?', {$bookx_ptypeID}, '410', now(), now(), NULL, ''),
		                                ('Product Virtual Default Status - Skip Shipping Address - When adding new products?', 'DEFAULT_PRODUCT_BOOKX_PRODUCTS_VIRTUAL', '0', 'Default Virtual Product status to be ON when adding new products?', {$bookx_ptypeID}, '420', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
		                                ('Product Free Shipping Default Status - Normal Shipping Rules - When adding new products?', 'DEFAULT_PRODUCT_BOOKX_PRODUCTS_IS_ALWAYS_FREE_SHIPPING', '0', 'What should the Default Free Shipping status be when adding new products?', {$bookx_ptypeID}, '430', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),

		                        #settings for meta tags
		                               # ('Show Metatags Title Default - Website Title', 'SHOW_PRODUCT_BOOKX_INFO_METATAGS_TITLE_STATUS', '1', 'Display Website Title in Meta Tags Title.', {$bookx_ptypeID}, '500', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
		                               # ('Show Metatags Title Default - Website Tagline', 'SHOW_PRODUCT_BOOKX_INFO_METATAGS_TITLE_TAGLINE_STATUS', '1', 'Display Website Tagline in Meta Tags Title.', {$bookx_ptypeID}, '505', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),

		                               # ('Show Metatags Title Default - Product Title', 'SHOW_PRODUCT_BOOKX_INFO_METATAGS_PRODUCTS_TITLE_STATUS', '1', 'Display Product Title in Meta Tags Title.', {$bookx_ptypeID}, '510', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
		                               # ('Show Metatags Title Default - Product Subtitle', 'SHOW_PRODUCT_BOOKX_INFO_METATAGS_PRODUCTS_SUBTITLE_STATUS', '1', 'Display Product Subtitle in Meta Tags Title.', {$bookx_ptypeID}, '515', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
		                               # ('Show Metatags Title Default - Product ISBN', 'SHOW_PRODUCT_BOOKX_INFO_METATAGS_MODEL_STATUS', '1', 'Display Book ISBN in Meta Tags Title.', {$bookx_ptypeID}, '520', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
		                               # ('Show Metatags Title Default - Product Price', 'SHOW_PRODUCT_BOOKX_INFO_METATAGS_PRICE_STATUS', '1', 'Display Book Price in Meta Tags Title.', {$bookx_ptypeID}, '530', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
		                               # ('Show Metatags Title Default - Product Author', 'SHOW_PRODUCT_BOOKX_INFO_METATAGS_TITLE_AUTHOR_STATUS', '1', 'Display Book Author in Meta Tags Title.', {$bookx_ptypeID}, '550', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
		                               # ('Show Metatags Title Default - Product Publisher', 'SHOW_PRODUCT_BOOKX_INFO_METATAGS_TITLE_PUBLISHER_STATUS', '1', 'Display Book Publisher in Meta Tags Title.', {$bookx_ptypeID}, '560', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
		                               # ('Show Metatags Title Default - Product Genre', 'SHOW_PRODUCT_BOOKX_INFO_METATAGS_TITLE_GENRE_STATUS', '1', 'Display Book Genre in Meta Tags Title.', {$bookx_ptypeID}, '570', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
		                               # ('Show Metatags Title Default - Product Series', 'SHOW_PRODUCT_BOOKX_INFO_METATAGS_TITLE_SERIES_STATUS', '1', 'Display Book Series in Meta Tags Title.', {$bookx_ptypeID}, '580', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
		                               # ('Show Metatags Title Default - Product Imprint', 'SHOW_PRODUCT_BOOKX_INFO_METATAGS_TITLE_IMPRINT_STATUS', '1', 'Display Book Imprint in Meta Tags Title.', {$bookx_ptypeID}, '590', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),

		                       #settings show bookx filters in sidebox
		                                ('Filter Sidebox - Filter Author', 'SHOW_PRODUCT_BOOKX_FILTER_AUTHOR', '1', 'Display a filter for Author in the Bookx Filter Sidebox.', {$bookx_ptypeID}, '650', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED'))),"),
		                                ('Filter Sidebox - Filter Author Type', 'SHOW_PRODUCT_BOOKX_FILTER_AUTHOR_TYPE', '1', 'Display a filter for Author Type in the Bookx Filter Sidebox.', {$bookx_ptypeID}, '655', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED'))),"),
		                                ('Filter Sidebox - Filter Publisher', 'SHOW_PRODUCT_BOOKX_FILTER_PUBLISHER', '1', 'Display a filter for Publisher in the Bookx Filter Sidebox.', {$bookx_ptypeID}, '660', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED'))),"),
		                                ('Filter Sidebox - Filter Imprint', 'SHOW_PRODUCT_BOOKX_FILTER_IMPRINT', '1', 'Display a filter for Imprint in the Bookx Filter Sidebox.', {$bookx_ptypeID}, '670', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED'))),"),
		                                ('Filter Sidebox - Filter Genre', 'SHOW_PRODUCT_BOOKX_FILTER_GENRE', '1', 'Display a filter for Genre in the Bookx Filter Sidebox.', {$bookx_ptypeID}, '660', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED'))),"),
		                                ('Filter Sidebox - Filter Series', 'SHOW_PRODUCT_BOOKX_FILTER_SERIES', '1', 'Display a filter for Series in the Bookx Filter Sidebox.', {$bookx_ptypeID}, '690', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED'))),"),
		                                ('Filter Sidebox - Link to Authors List', 'SHOW_PRODUCT_BOOKX_LINK_AUTHOR_LIST', '1', 'Show a link to display the list of all Authors in the Bookx Filter Sidebox.', {$bookx_ptypeID}, '695', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED'))),"),
		                                ('Filter Sidebox - Link to Imprint List', 'SHOW_PRODUCT_BOOKX_LINK_IMPRINT_LIST', '1', 'Show a link to display the list of all Imprints in the Bookx Filter Sidebox.', {$bookx_ptypeID}, '695', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED'))),"),
		                                ('Filter Sidebox - Link to Publisher List', 'SHOW_PRODUCT_BOOKX_LINK_PUBLISHER_LIST', '1', 'Show a link to display the list of all Publishers in the Bookx Filter Sidebox.', {$bookx_ptypeID}, '695', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED'))),"),
		                                ('Filter Sidebox - Link to Genres List', 'SHOW_PRODUCT_BOOKX_LINK_GENRES_LIST', '1', 'Show a link to display the list of all Genres in the Bookx Filter Sidebox.', {$bookx_ptypeID}, '695', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED'))),"),
		                                ('Filter Sidebox - Link to Series List', 'SHOW_PRODUCT_BOOKX_LINK_SERIES_LIST', '1', 'Show a link to display the list of all Seies in the Bookx Filter Sidebox.', {$bookx_ptypeID}, '696', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED'))),"),
		                                ('Filter Sidebox - Allow multiple filters active', 'ALLOW_PRODUCT_BOOKX_FILTER_MULTIPLE', '0', 'Allow multiple filters to be active in the Bookx Filter Sidebox. Otherwise setting one filter will cancel the previous filter. EXCEPT: The combination of filters "Author" and "Author Type" is always enabled.', {$bookx_ptypeID}, '699', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED'))),"),

		                        #settings extra info on top of search results
		                                ('Filter Results - Author: Show extra Info', 'SHOW_PRODUCT_BOOKX_FILTER_EXTRA_INFO_AUTHOR', '1', 'Display extra info for Author on top of search results when Filter active.', {$bookx_ptypeID}, '700', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
		                                ('Filter Results - Publisher: Show extra Info', 'SHOW_PRODUCT_BOOKX_FILTER_EXTRA_INFO_PUBLISHER', '1', 'Display extra info for Publisher on top of search results when Filter active.', {$bookx_ptypeID}, '710', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
		                                ('Filter Results - Imprint: Show extra Info', 'SHOW_PRODUCT_BOOKX_FILTER_EXTRA_INFO_IMPRINT', '1', 'Display extra info for Imprint on top of search results when Filter active.', {$bookx_ptypeID}, '720', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
		                                ('Filter Results - Series: Show extra Info', 'SHOW_PRODUCT_BOOKX_FILTER_EXTRA_INFO_SERIES', '1', 'Display extra info for Series on top of search results when Filter active.', {$bookx_ptypeID}, '730', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),"),
		                                ('Filter Results - Genre: Show extra Info', 'SHOW_PRODUCT_BOOKX_FILTER_EXTRA_INFO_GENRE', '1', 'Display extra info for Genre on top of search results when Filter active.', {$bookx_ptypeID}, '740', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),")
		                                ;
EOT;
        $db->Execute($sql);

        if ($german_installed) {
            $german_lng_layout = true;
            require_once DIR_FS_ADMIN.'includes/installers/bookx/bookx_install_include_german.php';
        }
    } else {
        $messageStack->add_session(''.BOOKX_MS_PRODUCT_LAYOUT_CONFIGS_NOT_INSTALLED.'', 'error');
    }

    //*********** Menu item for Config menu ********//////////
    if ('install' == $bookx_install) { // could also be "reset" !
        $sql = "REPLACE INTO {$const['TABLE_CONFIGURATION_GROUP']} (configuration_group_title, configuration_group_description, sort_order, visible) VALUES
   				('BookX', 'Configure BookX Product Type settings', '1', '1')";
        $db->Execute($sql);
    }

    $sql = "SELECT configuration_group_id FROM {$const['TABLE_CONFIGURATION_GROUP']} WHERE configuration_group_title = 'BookX';";

    $config_groups = $db->Execute($sql);
    $cf_gid = null;

    while (!$config_groups->EOF) {
        $cf_gid = $config_groups->fields['configuration_group_id'];
        $config_groups->MoveNext();
    }

    if (!empty($cf_gid)) {
        ///*********  Register for Admin Access Control ********////
        zen_deregister_admin_pages('configProdTypeBookX');
        zen_register_admin_page('configProdTypeBookX', 'CONFIG_MENU_PRODUCT_BOOKX', 'FILENAME_CONFIGURATION', 'gID='.$cf_gid, 'configuration', 'Y', $cf_gid);

        $sql = <<<EOT
		    UPDATE {$const['TABLE_CONFIGURATION_GROUP']} SET sort_order = {$cf_gid} WHERE configuration_group_id = {$cf_gid};
EOT;
        $db->Execute($sql);

        $sql = <<<EOT
    	REPLACE INTO {$const['TABLE_CONFIGURATION']} (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function)
    		VALUES
    		('BookX Version', 'BOOKX_VERSION', '{$bookx_module_version}', 'BookX Version is stored but not editable', 0, 10000, NOW(), NOW(), NULL, NULL)
		    ,('Filter list: Maximum width', 'BOOKX_MAX_DISPLAY_FILTER_DROPDOWN_LEN', '30', '<br />Sets the maximum width for an option list in the Book X filter sidebox.<br /><br /><b>Default: 30</b><br />', {$cf_gid}, 90, NOW(), NOW(), NULL, NULL)
		    ,('Filter list: Size/Style', 'BOOKX_MAX_SIZE_FILTER_LIST', '0', '<br />Sets the maximum length for an option list in the Book X filter sidebox. Settings this value to 0 or 1 will display a dropdown list.', {$cf_gid}, 100, NOW(), NOW(), NULL, NULL)
		    ,('BookX Icons: Maximum Height', 'BOOKX_ICONS_MAX_HEIGHT', '32', '<br />Maximum height in pixels for icons used for genre, publisher (logo), imprint, series, author <u>type</u>. A value of 0 will show all icons at their actual size without any scaling.', {$cf_gid}, 110, NOW(), NOW(), NULL, NULL)
		    ,('BookX Icons: Maximum Width', 'BOOKX_ICONS_MAX_WIDTH', '120', '<br />Maximum width in pixels for icons used for genre, publisher (logo), imprint, series, author <u>type</u>. A value of 0 will show all icons at their actual size without any scaling.', {$cf_gid}, 120, NOW(), NOW(), NULL, NULL)
		    ,('Product Info Page Author Photo: Maximum Height', 'BOOKX_AUTHOR_IMAGE_MAX_HEIGHT', '180', '<br />Maximum height in pixels for author photo on product info page. A value of 0 will show all images at their actual size without any scaling.', {$cf_gid}, 130, NOW(), NOW(), NULL, NULL)
		    ,('Product Info Page Author Photo: Maximum Width', 'BOOKX_AUTHOR_IMAGE_MAX_WIDTH', '150', '<br />Maximum width in pixels for author photo on product info page. A value of 0 will show all images at their actual size without any scaling.', {$cf_gid}, 140, NOW(), NOW(), NULL, NULL)
		    ,('Author Listing: Max. number of Authors per page', 'MAX_DISPLAY_BOOKX_AUTHOR_LISTING', '30', '<br />Maximum number of listed authors on author listing. No value defaults to 20 rows per page.', {$cf_gid}, 145, NOW(), NOW(), NULL, NULL)
		    ,('Author Listing Photo: Maximum Height', 'BOOKX_AUTHOR_LISTING_IMAGE_MAX_HEIGHT', '90', '<br />Maximum height in pixels for author photo on author listing. A value of 0 will show all images at their actual size without any scaling.', {$cf_gid}, 150, NOW(), NOW(), NULL, NULL)
		    ,('Author Listing Photo: Maximum Width', 'BOOKX_AUTHOR_LISTING_IMAGE_MAX_WIDTH', '100', '<br />Maximum width in pixels for author photo on author listing. A value of 0 will show all images at their actual size without any scaling.', {$cf_gid}, 160, NOW(), NOW(), NULL, NULL)
            ,('Author Image Folder Name', 'BOOKX_AUTHOR_IMAGES_FOLDER', 'authors', '<br />Where to save the authors Images. This will look for the folder name inside the images directory.', {$cf_gid}, 161, NOW(), NOW(), NULL, NULL)
		    ,('Author Listing: Show only authors of stocked books', 'BOOKX_AUTHOR_LISTING_SHOW_ONLY_STOCKED', '1', '<br />Show only those authors in the author listing, which have a book in the shop that is in stock (i.e. product is visible <u>and</u> stock is greater than "0"). If this setting is turned on, a checkbox is displayed on top of the author listing, which allows users to override this setting. If this is not desired, set CSS "display: none" to hide it.', {$cf_gid}, 165,  now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED'))),")
		    ,('Author Listing: Sort authors by', 'BOOKX_AUTHOR_LISTING_ORDER_BY', '1', '<br />Sort authors in author listing by:', {$cf_gid}, 167,  now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ORDER_BY_NAME')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ORDER_BY_SORT_ORDER'))),")
		    ,('Imprint Listing: Max. number of imprints per page', 'MAX_DISPLAY_BOOKX_IMPRINT_LISTING', '30', '<br />Maximum number of listed imprints on imprint listing. No value defaults to 20 rows per page.', {$cf_gid}, 168, NOW(), NOW(), NULL, NULL)
		    ,('Imprint Listing Logo: Maximum Height', 'BOOKX_IMPRINT_LISTING_IMAGE_MAX_HEIGHT', '90', '<br />Maximum height in pixels for imprint logo on imprint listing. A value of 0 will show all images at their actual size without any scaling.', {$cf_gid}, 169, NOW(), NOW(), NULL, NULL)
		    ,('Imprint Listing Logo: Maximum Width', 'BOOKX_IMPRINT_LISTING_IMAGE_MAX_WIDTH', '100', '<br />Maximum width in pixels for imprint logo on imprint listing. A value of 0 will show all images at their actual size without any scaling.', {$cf_gid}, 170, NOW(), NOW(), NULL, NULL)
		    ,('Imprint Listing: Show only imprints with books in stock', 'BOOKX_IMPRINT_LISTING_SHOW_ONLY_STOCKED', '1', '<br />Show only those imprints in the imprint listing, which have a book in the shop that is in stock (i.e. product is visible <u>and</u> stock is greater than "0"). If this setting is turned on, a checkbox is displayed on top of the imprint listing, which allows users to override this setting. If this is not desired, set CSS "display: none" to hide it.', {$cf_gid}, 171,  now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED'))),")
		    ,('Imprint Listing: Sort imprints by', 'BOOKX_IMPRINT_LISTING_ORDER_BY', '1', '<br />Sort imprints in imprint listing by:', {$cf_gid}, 172,  now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ORDER_BY_NAME')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ORDER_BY_SORT_ORDER'))),")
		    ,('Publisher Listing: Max. number of publishers per page', 'MAX_DISPLAY_BOOKX_PUBLISHER_LISTING', '30', '<br />Maximum number of listed publishers on publisher listing. No value defaults to 20 rows per page.', {$cf_gid}, 173, NOW(), NOW(), NULL, NULL)
		    ,('Publisher Listing Logo: Maximum Height', 'BOOKX_PUBLISHER_LISTING_IMAGE_MAX_HEIGHT', '90', '<br />Maximum height in pixels for publisher logo on publisher listing. A value of 0 will show all images at their actual size without any scaling.', {$cf_gid}, 174, NOW(), NOW(), NULL, NULL)
		    ,('Publisher Listing Logo: Maximum Width', 'BOOKX_PUBLISHER_LISTING_IMAGE_MAX_WIDTH', '100', '<br />Maximum width in pixels for publisher logo on publisher listing. A value of 0 will show all images at their actual size without any scaling.', {$cf_gid}, 175, NOW(), NOW(), NULL, NULL)
		    ,('Publisher Listing: Show only publishers with books in stock', 'BOOKX_PUBLISHER_LISTING_SHOW_ONLY_STOCKED', '1', '<br />Show only those publishers in the publisher listing, which have a book in the shop that is in stock (i.e. product is visible <u>and</u> stock is greater than "0"). If this setting is turned on, a checkbox is displayed on top of the publisher listing, which allows users to override this setting. If this is not desired, set CSS "display: none" to hide it.', {$cf_gid}, 176,  now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED'))),")
		    ,('Publisher Listing: Sort publishers by', 'BOOKX_PUBLISHER_LISTING_ORDER_BY', '1', '<br />Sort publishers in publisher listing by:', {$cf_gid}, 177,  now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ORDER_BY_NAME')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ORDER_BY_SORT_ORDER'))),")
		    ,('Series Listing: Max. number of Series per page', 'MAX_DISPLAY_BOOKX_SERIES_LISTING', '30', '<br />Maximum number of listed series on series listing. No value defaults to 20 rows per page.', {$cf_gid}, 178, NOW(), NOW(), NULL, NULL)
		    ,('Series Listing Image: Maximum Height', 'BOOKX_SERIES_LISTING_IMAGE_MAX_HEIGHT', '90', '<br />Maximum height in pixels for series image on series listing. A value of 0 will show all images at their actual size without any scaling.', {$cf_gid}, 180, NOW(), NOW(), NULL, NULL)
		    ,('Series Listing Image: Maximum Width', 'BOOKX_SERIES_LISTING_IMAGE_MAX_WIDTH', '100', '<br />Maximum width in pixels for series image on series listing. A value of 0 will show all images at their actual size without any scaling.', {$cf_gid}, 190, NOW(), NOW(), NULL, NULL)
		    ,('Series Listing: Show only series with stocked books', 'BOOKX_SERIES_LISTING_SHOW_ONLY_STOCKED', '1', '<br />Show only those series in the series listing, which have a book in the shop that is in stock (i.e. product is visible <u>and</u> stock is greater than "0"). If this setting is turned on, a checkbox is displayed on top of the series listing, which allows users to override this setting. If this is not desired, set CSS "display: none" to hide it.', {$cf_gid}, 192,  now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED'))),")
		    ,('Series Listing: Sort series by', 'BOOKX_SERIES_LISTING_ORDER_BY', '1', '<br />Sort series in series listing by:', {$cf_gid}, 193,  now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ORDER_BY_NAME')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ORDER_BY_SORT_ORDER'))),")
		    ,('Genre Listing: Max. number of genres per page', 'MAX_DISPLAY_BOOKX_GENRE_LISTING', '30', '<br />Maximum number of listed genres on genre listing. No value defaults to 20 rows per page.', {$cf_gid}, 195, NOW(), NOW(), NULL, NULL)
		    ,('Genre Listing Image: Maximum Height', 'BOOKX_GENRE_LISTING_IMAGE_MAX_HEIGHT', '90', '<br />Maximum height in pixels for genre image on genre listing. A value of 0 will show all images at their actual size without any scaling.', {$cf_gid}, 196, NOW(), NOW(), NULL, NULL)
		    ,('Genre Listing Image: Maximum Width', 'BOOKX_GENRE_LISTING_IMAGE_MAX_WIDTH', '100', '<br />Maximum width in pixels for genre image on genre listing. A value of 0 will show all images at their actual size without any scaling.', {$cf_gid}, 197, NOW(), NOW(), NULL, NULL)
		    ,('Genre Listing: Show only genres with books in stock', 'BOOKX_GENRE_LISTING_SHOW_ONLY_STOCKED', '1', '<br />Show only those genres in the genre listing, which have a book in the shop that is in stock (i.e. product is visible <u>and</u> stock is greater than "0"). If this setting is turned on, a checkbox is displayed on top of the genre listing, which allows users to override this setting. If this is not desired, set CSS "display: none" to hide it.', {$cf_gid}, 198,  now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED'))),")
		    ,('Genre Listing: Sort genres by', 'BOOKX_GENRE_LISTING_ORDER_BY', '1', '<br />Sort genres in genre listing by:', {$cf_gid}, 198,  now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ORDER_BY_NAME')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ORDER_BY_SORT_ORDER'))),")
		    ,('New Products: Base on Publication Date', 'BOOKX_NEW_PRODUCTS_LOOK_BACK_NUMBER_OF_DAYS', '90', 'Base "New Products" List on publication date. Enter number of days to look back in time for published books. A value of "0" turns off this option. Example: Default value of "90" will list all books with publication dates within the last 90 days. Note: If you use partial publication dates in the format "2013-04-00" to only indicate the month of publication, these dates are considered to be at the <u>beginning</u> of the month.<br /><br />', {$cf_gid}, 200, NOW(), NOW(), NULL, NULL)
		    ,('New Products: Show Product Description', 'BOOKX_NEW_PRODUCTS_SHOW_PRODUCT_DESCRIPTION_NUMOFCHARS', '150', 'Show (part of) the product description in the "New Products" module. Enter the number of characters after which the description will be truncated. A value of "0" disables the display and a value of "-1" shows the entire description without truncating it.<br /><br />', {$cf_gid}, 201, NOW(), NOW(), NULL, NULL)
		    ,('Upcoming Products: Base on Publication Date', 'BOOKX_UPCOMING_PRODUCTS_LOOK_AHEAD_NUMBER_OF_DAYS', '180', 'Base "Upcoming Products" List on publication date instead of date available. Enter number of days to look ahead in time for books to be published. A value of "0" turns off this option. Example: Default value of "180" will list all books with publication dates within the next 180 days. Note: If you use partial publication dates in the format "2013-04-00" to only indicate the month of publication, these dates are considered to be at the <u>beginning</u> of the month.<br /><br />', {$cf_gid}, 210, NOW(), NOW(), NULL, NULL)
		    ,('Upcoming Products: Show Product Image', 'BOOKX_UPCOMING_PRODUCTS_SHOW_PRODUCT_IMAGE', '1', 'Show product image in "Upcoming Products" module', {$cf_gid}, 220, NOW(), NOW(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED'))),")
		    ,('Upcoming Products Image: Maximum Height', 'BOOKX_UPCOMING_PRODUCT_IMAGE_MAX_HEIGHT', '120', '<br />Maximum height in pixels for product images in upcoming products module. A value of 0 will not constrain the height of the image.', {$cf_gid}, 222, NOW(), NOW(), NULL, NULL)
		    ,('Upcoming Products Image: Maximum Width', 'BOOKX_UPCOMING_PRODUCT_IMAGE_MAX_WIDTH', '80', '<br />Maximum width in pixels for product images in upcoming products module. A value of 0 will not constrain the width of the image.', {$cf_gid}, 223, NOW(), NOW(), NULL, NULL)
		    ,('Upcoming Products: Show Product Description', 'BOOKX_UPCOMING_PRODUCTS_SHOW_PRODUCT_DESCRIPTION_NUMOFCHARS', '150', 'Show (part of) the product description in the "Upcoming Products" module. Enter the number of characters after which the description will be truncated. A value of "0" disables the display and a value of "-1" shows the entire description without truncating it.<br /><br />', {$cf_gid}, 230, NOW(), NOW(), NULL, NULL)
		    ,('Breadcrumbs: Use Bookx instead of ZC Categories', 'BOOKX_BREAD_USE_BOOKX_NO_CATEGORIES', '1', 'Let BookX fill the "Breadcrumb" navigation instead of letting ZenCart populate the "Breadcrumb" navigation with the category path. This only affects the product info page for BookX products or product listings resulting from applying a BookX filter.', {$cf_gid}, 240, NOW(), NOW(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED'))),")
		    ,('Breadcrumbs: Insert Publisher on Product Detail Page', 'BOOKX_BREAD_ADD_PUBLISHER', '10', 'If "Use Bookx instead of ZC Categories" is enabled, then the "Breadcrumb" navigation is filled automatically by BookX for a product info page, even if the user got there directly e.g. via a search. A value of "0" disables the inclusion of the Publisher and a number above zero determines the order in which the Publisher is inserted in the "Breadcrumb" navigation trail.', {$cf_gid}, 250, NOW(), NOW(), NULL, NULL)
		    ,('Breadcrumbs: Insert Imprint on Product Detail Page', 'BOOKX_BREAD_ADD_IMPRINT', '20', 'If "Use Bookx instead of ZC Categories" is enabled, then the "Breadcrumb" navigation is filled automatically by BookX for a product info page, even if the user got there directly e.g. via a search. A value of "0" disables the inclusion of the Imprint and a number above zero determines the order in which the Imprint is inserted in the "Breadcrumb" navigation trail.', {$cf_gid}, 260, NOW(), NOW(), NULL, NULL)
		    ,('Breadcrumbs: Insert Series on Product Detail Page', 'BOOKX_BREAD_ADD_SERIES', '30', 'If "Use Bookx instead of ZC Categories" is enabled, then the "Breadcrumb" navigation is filled automatically by BookX for a product info page, even if the user got there directly e.g. via a search. A value of "0" disables the inclusion of the Series and a number above zero determines the order in which the Series is inserted in the "Breadcrumb" navigation trail.', {$cf_gid}, 270, NOW(), NOW(), NULL, NULL)
		    ,('Breadcrumbs: Insert Genre on Product Detail Page', 'BOOKX_BREAD_ADD_GENRE', '0', 'If "Use Bookx instead of ZC Categories" is enabled, then the "Breadcrumb" navigation is filled automatically by BookX for a product info page, even if the user got there directly e.g. via a search. A value of "0" disables the inclusion of the Genre and a number above zero determines the order in which the Genre is inserted in the "Breadcrumb" navigation trail. ATTENTION: This may produce unexpected results when multiple Genres are assigned to a book, as only one Genre can be shown.', {$cf_gid}, 280, NOW(), NOW(), NULL, NULL)
		    ,('Breadcrumbs: Insert Author on Product Detail Page', 'BOOKX_BREAD_ADD_AUTHOR', '0', 'If "Use Bookx instead of ZC Categories" is enabled, then the "Breadcrumb" navigation is filled automatically by BookX for a product info page, even if the user got there directly e.g. via a search. A value of "0" disables the inclusion of the Author and a number above zero determines the order in which the Author is inserted in the "Breadcrumb" navigation trail. ATTENTION: This may produce unexpected results when multiple Authors are assigned to a book, as only one author can be show.', {$cf_gid}, 290, NOW(), NOW(), NULL, NULL)
		    ,('Product Info: "Previous"/"Next Buttons" based on active BookX Filter', 'BOOKX_NEXT_PREVIOUS_BASED_ON_FILTER', '1', 'If this feature is enabled, then the buttons "next", "previous", "back to listing" on the product info page will no longer navigate back an fourth in the ZC <strong>Category</b> containing the product, but rather navigate within the set of products as determined by the active BookX filter (e.g. Author).', {$cf_gid}, 300, NOW(), NOW(), NULL, NULL);
EOT;
        $db->Execute($sql);

        ///********   Add values for German admin  ******/////////
        if ($german_installed) {
            $german_install_admin == true;
            require_once DIR_FS_ADMIN.'includes/installers/bookx/bookx_install_include_german.php';
        }
        
        /**
         * @since v1.0.0
         * New configuration values For v1.0.0. Leaving them here separated, for developement purposes and to add them later to german language
         */
        
        if ($bookx_uses_ceon == true) {
            $sql ="REPLACE INTO ".TABLE_CONFIGURATION."
            (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function)
            VALUES (
            'Use CEON URI Module', 'BOOKX_USES_CEON_URI_MODULE', '1', 
            'Enable Ceon Uri Module. Default 0', '{$cf_gid}', '310', now(), now(), NULL,
            'zen_cfg_select_option(array(\"0\", \"1\"),');";
            
            $db->Execute($sql);
        }
        
        if ($bookx_uses_dinamic_metatags == true) {
            $sql = "REPLACE INTO {$const['TABLE_CONFIGURATION']} 
            (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function)
            VALUES (
            'Use Dinamic MetaTags', 'BOOKX_USES_DINAMIC_METATAGS', '1', 
            'Enable Dinamic MetaTags. Default 0', '{$cf_gid}', '320', now(), now(), NULL,
            'zen_cfg_select_option(array(\"0\", \"1\"),');";
            $db->Execute($sql);
        }
    } else {
        $messageStack->add_session(''.BOOKX_MS_ADMIN_CONFIG_MENU_NOT_INSTALLED.'', 'error');
    }


    if ('reset' == $bookx_install) {
        $messageStack->add_session(''.BOOKX_MS_RESET_SUCCESS.'', 'success');
//        if (isset($_SESSION['bookx_install']) && $_SESSION['bookx_install'] == 'do_reset') {
//
//        }
    } else {
        $messageStack->add_session(''.BOOKX_MS_SUCCESS.'', 'success');
    }
    $db->Execute("
            REPLACE INTO {$const['TABLE_CONFIGURATION']} 
            (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order,last_modified, date_added, use_function, set_function) VALUES (
            'BookX Version', 'BOOKX_VERSION', '".$bookx_module_version."', 'BookX Version is stored but not editable', 0, 10000, NOW(), NOW(), NULL, NULL);");
    
             unset(
            $_SESSION['bookx_install'],
            $_SESSION['bookx_install_ceon'],
            $_SESSION['bookx_install_metatags']
            );
    zen_redirect(FILENAME_BOOKX_TOOLS.'.php');
    break; // install and reset


    case ($bookx_install == 'uninstall' and !$login_page):

// ======================================================
//
// Uninstall
//
// ======================================================

    // ======================================================
    //
    // remove the menu items
    //
    // ====================================================
    
    // let's see what we should do with the existing products

    $sql = "SELECT type_id FROM {$const['TABLE_PRODUCT_TYPES']} WHERE type_handler = 'product_bookx';";
    $product_type = $db->Execute($sql);
    $bookx_ptypeID = null;
   
    while (!$product_type->EOF) {
        $bookx_ptypeID = (int)$product_type->fields['type_id'];
        $product_type->MoveNext();
    }
    
    if (isset($_GET['convert_bookx_products']) && '1' == $_GET['convert_bookx_products']) {
        $sql = "SELECT type_id FROM {$const['TABLE_PRODUCT_TYPES']} WHERE type_handler = 'product';";
        $product_general_type = $db->Execute($sql);
        $general_type_id = null;

        while (!$product_general_type->EOF) {
            $general_type_id = (int)$product_general_type->fields['type_id'];
            $product_general_type->MoveNext();
        }

        if (!empty($bookx_ptypeID) && !empty($general_type_id)) {
            $languages = zen_get_languages();
            for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
                $sql = <<<EOT
	   						SELECT p.products_id, pd.products_name, bed.products_subtitle, pd.language_id FROM {$const['TABLE_PRODUCTS']} p
	   						LEFT JOIN {$const['TABLE_PRODUCTS_DESCRIPTION']} pd ON pd.products_id = pd.products_id AND pd.language_id = "{$languages[$i]['id']}"
	   						LEFT JOIN {$const['TABLE_PRODUCT_BOOKX_EXTRA_DESCRIPTION']} bed ON bed.products_id = p.products_id AND bed.languages_id = "{$languages[$i]['id']}"
	   						WHERE p.products_type = "$bookx_ptypeID";
EOT;
                $bookx_products_extra_descriptions = $db->Execute($sql);

                while (!$bookx_products_extra_descriptions->EOF) {
                    if (!empty($bookx_products_extra_descriptions->fields['products_subtitle']) && !empty($bookx_products_extra_descriptions->fields['products_name'])) {
                        $divider = ' - ';
                    } else {
                        $divider = '';
                    }

                    $new_products_name = $bookx_products_extra_descriptions->fields['products_name'].$divider.$bookx_products_extra_descriptions->fields['products_subtitle'];

                    $sql = "UPDATE {$const['TABLE_PRODUCTS_DESCRIPTION']} SET products_name = '".zen_db_input($new_products_name)."'
   							WHERE products_id = {$bookx_products_extra_descriptions->fields['products_id']} AND language_id = {$bookx_products_extra_descriptions->fields['language_id']};";
                    $db->Execute($sql);
                    $bookx_products_extra_descriptions->MoveNext();
                }
            }

            $sql = "UPDATE {$const['TABLE_PRODUCTS']} SET products_type = $general_type_id WHERE products_type = $bookx_ptypeID;";
            $db->Execute($sql);
        }

        $convert_products_to_general = true;
    } elseif (!empty($bookx_ptypeID)) {
        $sql = "SELECT products_id FROM {$const['TABLE_PRODUCTS']} WHERE products_type = $bookx_ptypeID;";
        $products_bookx = $db->Execute($sql);

        while (!$products_bookx->EOF) {
            bookx_delete_product($products_bookx->fields['products_id']);
            $products_bookx->MoveNext();
        }
        $convert_products_to_general = false;
    }

    if (defined('TABLE_ADMIN_PAGES')) {
        zen_deregister_admin_pages($admin_page_keys);
    }

    /////// *********** Remove Configuration menu items   *********** //////////
    $sql = "SELECT configuration_group_id FROM {$const['TABLE_CONFIGURATION_GROUP']} WHERE configuration_group_title = 'BookX';";

    $config_groups = $db->Execute($sql);
    $cf_gid = null;

    while (!$config_groups->EOF) {
        $cf_gid = $config_groups->fields['configuration_group_id'];
        $config_groups->MoveNext();
    }

    if (!empty($cf_gid)) {
        $sql = <<<EOT
		    DELETE FROM {$const['TABLE_CONFIGURATION_GROUP']} WHERE configuration_group_id = {$cf_gid};
EOT;
        $db->Execute($sql);

        $sql = <<<EOT
		    DELETE FROM {$const['TABLE_CONFIGURATION']} WHERE configuration_group_id = {$cf_gid} AND configuration_group_id != 0;
EOT;
        $db->Execute($sql);
        
        $sql = <<<EOT
		    DELETE FROM {$const['TABLE_CONFIGURATION']} WHERE configuration_key = 'BOOKX_VERSION';
EOT;
        $db->Execute($sql);

        if (defined('TABLE_CONFIGURATION_LANGUAGE')) {
            $sql = "DELETE FROM {$const['TABLE_CONFIGURATION_LANGUAGE']} WHERE configuration_key LIKE '%BOOKX%'";
            $db->Execute($sql);
        }
    }

    // ======================================================
    //
    // remove Layout option descriptions
    //
    // ======================================================
    if (!empty($bookx_ptypeID)) {
        $sql = <<<EOT
		    DELETE FROM {$const['TABLE_PRODUCT_TYPE_LAYOUT']} WHERE product_type_id = $bookx_ptypeID;
EOT;
        $db->Execute($sql);
    }

    //** This should not be necessary, but you never know
    $sql = <<<EOT
		    DELETE FROM {$const['TABLE_PRODUCT_TYPE_LAYOUT']} WHERE configuration_key LIKE '%BOOKX%';
EOT;
    $db->Execute($sql);
    //*** eof not necessary?

    $sql = <<<EOT
            DELETE FROM {$const['TABLE_GET_TERMS_TO_FILTER']}
                WHERE get_term_table LIKE 'TABLE_PRODUCT_BOOKX%';
EOT;
                $db->Execute($sql);

    if (defined('TABLE_PRODUCT_TYPE_LAYOUT_LANGUAGE')) {
        $sql = "DELETE FROM {$const['TABLE_PRODUCT_TYPE_LAYOUT_LANGUAGE']} WHERE configuration_key LIKE '%BOOKX%'";
        $db->Execute($sql);
    }

      $sql = "DELETE FROM {$const['TABLE_PRODUCT_TYPES']} WHERE type_handler = 'product_bookx';";
      $db->Execute($sql);

        $sql = "DROP TABLE IF EXISTS {$const['TABLE_PRODUCT_BOOKX_AUTHORS']};";
      $db->Execute($sql);

        $sql = "DROP TABLE IF EXISTS {$const['TABLE_PRODUCT_BOOKX_AUTHORS_DESCRIPTION']};";
      $db->Execute($sql);

        $sql = "DROP TABLE IF EXISTS {$const['TABLE_PRODUCT_BOOKX_AUTHORS_TO_PRODUCTS']};";
      $db->Execute($sql);

        $sql = "DROP TABLE IF EXISTS {$const['TABLE_PRODUCT_BOOKX_AUTHOR_TYPES']};";
      $db->Execute($sql);

        $sql = "DROP TABLE IF EXISTS {$const['TABLE_PRODUCT_BOOKX_AUTHOR_TYPES_DESCRIPTION']};";
      $db->Execute($sql);

        $sql = "DROP TABLE IF EXISTS {$const['TABLE_PRODUCT_BOOKX_BINDING']};";
      $db->Execute($sql);

        $sql = "DROP TABLE IF EXISTS {$const['TABLE_PRODUCT_BOOKX_BINDING_DESCRIPTION']};";
      $db->Execute($sql);

        $sql = "DROP TABLE IF EXISTS {$const['TABLE_PRODUCT_BOOKX_CONDITIONS']};";
      $db->Execute($sql);

        $sql = "DROP TABLE IF EXISTS {$const['TABLE_PRODUCT_BOOKX_CONDITIONS_DESCRIPTION']};";
      $db->Execute($sql);

        $sql = "DROP TABLE IF EXISTS {$const['TABLE_PRODUCT_BOOKX_EXTRA']};";
      $db->Execute($sql);

      $sql = "DROP TABLE IF EXISTS {$const['TABLE_PRODUCT_BOOKX_EXTRA_DESCRIPTION']};";
      $db->Execute($sql);

        $sql = "DROP TABLE IF EXISTS {$const['TABLE_PRODUCT_BOOKX_GENRES']};";
      $db->Execute($sql);

        $sql = "DROP TABLE IF EXISTS {$const['TABLE_PRODUCT_BOOKX_GENRES_DESCRIPTION']};";
      $db->Execute($sql);

        $sql = "DROP TABLE IF EXISTS {$const['TABLE_PRODUCT_BOOKX_GENRES_TO_PRODUCTS']};";
      $db->Execute($sql);

        $sql = "DROP TABLE IF EXISTS {$const['TABLE_PRODUCT_BOOKX_IMPRINTS']};";
      $db->Execute($sql);

        $sql = "DROP TABLE IF EXISTS {$const['TABLE_PRODUCT_BOOKX_IMPRINTS_DESCRIPTION']};";
      $db->Execute($sql);

        $sql = "DROP TABLE IF EXISTS {$const['TABLE_PRODUCT_BOOKX_PRINTING']};";
      $db->Execute($sql);

        $sql = "DROP TABLE IF EXISTS {$const['TABLE_PRODUCT_BOOKX_PRINTING_DESCRIPTION']};";
      $db->Execute($sql);

        $sql = "DROP TABLE IF EXISTS {$const['TABLE_PRODUCT_BOOKX_PUBLISHERS']};";
      $db->Execute($sql);

        $sql = "DROP TABLE IF EXISTS {$const['TABLE_PRODUCT_BOOKX_PUBLISHERS_DESCRIPTION']};";
      $db->Execute($sql);

        $sql = "DROP TABLE IF EXISTS {$const['TABLE_PRODUCT_BOOKX_SERIES']};";
      $db->Execute($sql);

        $sql = "DROP TABLE IF EXISTS {$const['TABLE_PRODUCT_BOOKX_SERIES_DESCRIPTION']};";

      $db->Execute($sql);
      
      /**
       * @since v1.0.0
       */
      $db->Execute("DROP TABLE IF EXISTS ".TABLE_PRODUCT_BOOKX_FAMILIES_TO_PRODUCTS.";");
      $db->Execute("DROP TABLE IF EXISTS ".TABLE_PRODUCT_BOOKX_FAMILIES.";");
      $db->Execute("DROP TABLE IF EXISTS ".TABLE_PRODUCT_BOOKX_SEARCH.";");
      

      //@TODO delete the sample catagory BookX if emtpy

    // ======================================================
    //
    // rollback corefiles to default versions
    //
    // ======================================================

   /* foreach($rollback_files as $cf)
    {
        if(xxxx_install_replace($cf[0],$cf[1]))
        {
                if($message_type=='session'){
                    $messageStack->add_session('ROLLBACK  : '.$cf[0].' ' . BOOKX_MS_ROLLBACK_OK . '', 'success');
                }else{
                    $messageStack->add('ROLLBACK  : '.$cf[0].' ' . BOOKX_MS_ROLLBACK_OK . '', 'success');
                }
          @unlink($cf[1]);

        }else{
                if($message_type=='session'){
                    $messageStack->add_session('ROLLBACK : '.$cf[0].' ' . BOOKX_MS_ROLLBACK_NOT_OK . ' ', 'warning');
                }else{
                    $messageStack->add('ROLLBACK : '.$cf[0].' ' . BOOKX_MS_ROLLBACK_NOT_OK . ' ', 'warning');
                }
        }
    }


    // delete the non-core files
    foreach($files as $f)
    {
        if(file_exists($f))
        {
            if(unlink($f))
            {
            //$messageStack->add_session('deleted - '.$f,'success');
            }else{
                if($message_type=='session'){
                    $messageStack->add_session('not deleted - '.$f,'error');
                }else{
                    $messageStack->add('not deleted - '.$f,'error');
                }
            }
        }
    }

    // delete the template files
    foreach($template_files as $f)
    {
        if(file_exists($f[0]))
        {
            if(unlink($f[0]))
            {
            //$messageStack->add_session('deleted - '.$f[0],'success');
            }else{
                if($message_type=='session'){
                    $messageStack->add_session('not deleted - '.$f[1],'error');
                }else{
                    $messageStack->add('not deleted - '.$f[1],'error');
                }
            }
        }

        if(file_exists($f[1])) // may not need to do this but what the heck.
        {
            if(unlink($f[1]))
            {
            //$messageStack->add_session('deleted - '.$f[1],'success');
            }else{
                if($message_type=='session'){
                    $messageStack->add_session('not deleted - '.$f[1],'error');
                }else{
                    $messageStack->add('not deleted - '.$f[1],'error');
                }
            }
        }

    }*/

     if (isset($message_type) && $message_type=='session') {
         $messageStack->add_session(''.BOOKX_MS_UNINSTALL_OK.'', 'success');
     //$messageStack->add_session('' . BOOKX_MS_BACKUP_INFO . '', 'warning');
     } else {
         $messageStack->add_session(''.BOOKX_MS_UNINSTALL_OK.'', 'success');
         $_SESSION['bookx_uninstall'] = true;
         //$messageStack->add('' . BOOKX_MS_BACKUP_INFO . '', 'warning');
     }
     //zen_redirect(FILENAME_DEFAULT.'.php');
     break;

}



/*
 * @ todo This is a temporary db update, to use 0 as default db values in id's a sort orders.
 * This will probably reduce the verification code on NULL or '0' or empty.
 * On mysql 5.7 the bookx fields ids are inserted as '0', to insert then as NULL other verifications had to be made.
 * This is set to update if zencart is 1.5.6.
 * This update will go somewhere else.
 *
 */

    if ('install' == $bookx_install) {

        /**
         * @todo change, add or remove this message
         */
        $update_message = "Remove files admin/product_bookx.php";
        $messageStack->add('Bookx Updated to version '.$bookx_module_version, 'success');
        $messageStack->add($update_message, 'waning');
    }
