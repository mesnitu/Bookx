<?php

if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

/*
 * This will update version 0.95. However, there are some file differences for zencart 156, starting on admin category listing
 * But maybe it works.
 * 
 * ZC 156 changed the default installation db CHARACTER to utf8mb4.
 * Not sure about the implications, so a option will later be added to install ( or update) to choose,
 * 
 */

if (('update' == $bookx_install) && (!empty($_POST))) {
    $update_095_msg = "<strong>Update Log</strong><br />";

    /**
     * Ensures that database indexes can be drop / altered or added.
     */
    function alter_indexes($table, $column_name, $new_key_name, $type = '') {
        global $db;
        $sql = "SHOW INDEX FROM " . $table . " ";
        $res = $db->Execute($sql);

        while (!$res->EOF) {
            $arr[trim($res->fields['Key_name'])] = trim($res->fields['Column_name']);
            if ($res->fields['Key_name'] !== "PRIMARY" && trim($res->fields['Column_name']) == $column_name) {
                $db->Execute("ALTER TABLE " . $table . " DROP INDEX " . $res->fields['Key_name'] . "");
            }
            $res->MoveNext();
        }
        if (!empty($new_key_name)) { // else just Drops
            $db->Execute("ALTER TABLE " . $table . " ADD " . $type . " INDEX " . $new_key_name . " (" . $column_name . " ASC);");
        }
    }

    // simple wrap msg in <li>
    $list_msg = function ($msg, $type = null) {

        $type = ($type == 'warning') ? 'glyphicon-exclamation-sign text-warning' : 'glyphicon-ok-circle text-success';
        return '<li><span class="glyphicon ' . $type . '" aria-hidden="true"></span>&nbsp;' . $msg . '</li>';
    };

    /**
     * Alter tables to use (int)'0' as default values
     * TABLE_PRODUCT_BOOKX_EXTRA
     */
    
    $db->Execute("ALTER TABLE {$const['TABLE_PRODUCT_BOOKX_EXTRA']} " . $table_character_set . ",  
            CHANGE COLUMN bookx_publisher_id bookx_publisher_id INT(11) NULL DEFAULT '0',
            CHANGE COLUMN bookx_series_id bookx_series_id INT(11) NULL DEFAULT '0',
            CHANGE COLUMN bookx_imprint_id bookx_imprint_id INT(11) NULL DEFAULT '0',
            CHANGE COLUMN bookx_binding_id bookx_binding_id INT(11) NULL DEFAULT '0',
            CHANGE COLUMN bookx_printing_id bookx_printing_id INT(11) NULL DEFAULT '0',
            CHANGE COLUMN bookx_condition_id bookx_condition_id INT(11) NULL DEFAULT '0';");

    alter_indexes(TABLE_PRODUCT_BOOKX_EXTRA, 'bookx_publisher_id', 'idx_bxe_publisher_id');
    alter_indexes(TABLE_PRODUCT_BOOKX_EXTRA, 'bookx_series_id', 'idx_bxe_series_id');
    alter_indexes(TABLE_PRODUCT_BOOKX_EXTRA, 'isbn', 'idx_bxe_isbn');

    $update_095_msg .= $list_msg("Added idx_bxe_publisher_id, idx_bxe_series_id, idx_bxe_isbn to TABLE_PRODUCT_BOOKX_EXTRA<br>");

    //Convert all NULL ids to int 0
    $db->Execute("UPDATE " . TABLE_PRODUCT_BOOKX_EXTRA . " SET
        bookx_publisher_id = IFNULL(bookx_publisher_id,0),
        bookx_series_id = IFNULL(bookx_series_id,0),
        bookx_imprint_id = IFNULL(bookx_imprint_id,0),
        bookx_binding_id = IFNULL(bookx_binding_id,0),
        bookx_printing_id = IFNULL(bookx_printing_id,0),
        bookx_condition_id = IFNULL(bookx_condition_id,0)");

    $update_095_msg .= $list_msg("Converted all NULL or Empty id's database fields to int(0) on TABLE_PRODUCT_BOOKX_EXTRA<br>");

    $db->Execute("UPDATE " . TABLE_PRODUCT_BOOKX_EXTRA . " SET
        publishing_date = CASE publishing_date WHEN '0000-00-00 00:00:00' THEN NULL ELSE publishing_date END,
        volume = CASE volume WHEN '' THEN NULL ELSE volume END,
        pages = CASE pages WHEN '' THEN NULL ELSE pages END,
        size = CASE size WHEN '' THEN NULL ELSE size END,
        isbn = CASE isbn WHEN '' THEN NULL ELSE isbn END;");

    $update_095_msg .= $list_msg("Converted all empty database fields to NULL on TABLE_PRODUCT_BOOKX_EXTRA<br>");

    $analyze = TABLE_PRODUCT_BOOKX_EXTRA;
    /**
     * TABLE_PRODUCT_BOOKX_EXTRA_DESCRIPTION
     */
    $db->Execute("ALTER TABLE {$const['TABLE_PRODUCT_BOOKX_EXTRA_DESCRIPTION']} " . $table_character_set . ",
            CHANGE COLUMN products_subtitle products_subtitle VARCHAR(191) " . $column_character_set . " NULL DEFAULT NULL;");
    $db->Execute("UPDATE " . TABLE_PRODUCT_BOOKX_EXTRA_DESCRIPTION . " SET
        products_subtitle = CASE products_subtitle WHEN '' THEN NULL ELSE products_subtitle END;");

    $update_095_msg .= $list_msg("Converted all empty database fields to NULL on TABLE_PRODUCT_BOOKX_EXTRA_DESCRIPTION<br>");
    $analyze .= ', ' . TABLE_PRODUCT_BOOKX_EXTRA_DESCRIPTION;

    /**
     * TABLE_PRODUCT_BOOKX_AUTHORS
     */
    $db->Execute("ALTER TABLE {$const['TABLE_PRODUCT_BOOKX_AUTHORS']} " . $table_character_set . ", 
              CHANGE COLUMN author_name author_name VARCHAR(64) " . $column_character_set . " NULL DEFAULT NULL,
              CHANGE COLUMN author_default_type author_default_type INT(11) NULL DEFAULT '0',
              CHANGE COLUMN author_sort_order author_sort_order INT(11) NULL DEFAULT '0',
              CHANGE COLUMN author_image_copyright author_image_copyright VARCHAR(64) " . $column_character_set . " NULL DEFAULT NULL;");

    $db->Execute("UPDATE " . TABLE_PRODUCT_BOOKX_AUTHORS . " SET
        author_name = CASE author_name WHEN '' THEN NULL ELSE author_name END,
        author_image = CASE author_image WHEN '' THEN NULL ELSE author_image END,
        author_image_copyright = CASE author_image_copyright WHEN '' THEN NULL ELSE author_image_copyright END,
        author_url = CASE author_url WHEN '' THEN NULL ELSE author_url END,
        last_modified = CASE last_modified WHEN '' THEN NULL ELSE last_modified END,
        date_added = CASE date_added WHEN '' THEN NULL ELSE date_added END;");

    $update_095_msg .= $list_msg("Converted all empty database fields to NULL on TABLE_PRODUCT_BOOKX_AUTHORS<br>");

    $db->Execute("UPDATE " . TABLE_PRODUCT_BOOKX_AUTHORS . " SET
        author_default_type = IFNULL(author_default_type,0),
        author_sort_order = IFNULL(author_sort_order,0)");

    $update_095_msg .= $list_msg("Converted all NULL or Empty id's database fields to int(0) on TABLE_PRODUCT_BOOKX_AUTHORS<br>");

    /**
     * TABLE_PRODUCT_BOOKX_AUTHORS_TO_PRODUCTS
     */
    $db->Execute("ALTER TABLE {$const['TABLE_PRODUCT_BOOKX_AUTHORS_TO_PRODUCTS']} " . $table_character_set . "");
    alter_indexes(TABLE_PRODUCT_BOOKX_AUTHORS_TO_PRODUCTS, 'bookx_author_id', 'idx_bxatp_author_id');
    alter_indexes(TABLE_PRODUCT_BOOKX_AUTHORS_TO_PRODUCTS, 'products_id', 'idx_bxatp_products_id');
    $update_095_msg .= $list_msg("Added INDEX idx_bxatp_author_id, dx_bxatp_products_id to TABLE_PRODUCT_BOOKX_AUTHORS_TO_PRODUCTS<br>");
    $analyze .= ', ' . TABLE_PRODUCT_BOOKX_AUTHORS_TO_PRODUCTS;

    /**
     * TABLE_PRODUCT_BOOKX_AUTHORS_DESCRIPTION
     */
    $db->Execute("ALTER TABLE {$const['TABLE_PRODUCT_BOOKX_AUTHORS_DESCRIPTION']} " . $table_character_set . ", 
               CHANGE COLUMN author_description author_description TEXT " . $column_character_set . " NULL DEFAULT NULL ;");
    $db->Execute("UPDATE " . TABLE_PRODUCT_BOOKX_AUTHORS_DESCRIPTION . " SET
        author_description = CASE author_description WHEN '' THEN NULL ELSE author_description END;");
    $update_095_msg .= $list_msg("Converted all empty database fields to NULL on TABLE_PRODUCT_BOOKX_AUTHORS_DESCRIPTION");

    /**
     * TABLE_PRODUCT_BOOKX_AUTHOR_TYPES
     */
    $db->Execute("ALTER TABLE {$const['TABLE_PRODUCT_BOOKX_AUTHOR_TYPES']} " . $table_character_set . ",
            CHANGE COLUMN type_sort_order type_sort_order INT(11) NULL DEFAULT '0';");
    alter_indexes(TABLE_PRODUCT_BOOKX_AUTHOR_TYPES, 'bookx_author_type_id', '');
    $update_095_msg .= $list_msg("DROP INDEX bookx_author_type_id - PRIMARY in use");
    $analyze .= ', ' . TABLE_PRODUCT_BOOKX_AUTHOR_TYPES;

    /**
     * TABLE_PRODUCT_BOOKX_AUTHOR_TYPES_DESCRIPTION
     */
    $db->Execute("ALTER TABLE {$const['TABLE_PRODUCT_BOOKX_AUTHOR_TYPES_DESCRIPTION']} " . $table_character_set . ";");
    $db->Execute("UPDATE " . TABLE_PRODUCT_BOOKX_AUTHOR_TYPES_DESCRIPTION . " SET
        type_image = CASE type_image WHEN '' THEN NULL ELSE type_image END,
        type_description = CASE type_description WHEN '' THEN NULL ELSE type_description END;");
    $update_095_msg .= $list_msg("Converted all empty database fields to NULL on TABLE_PRODUCT_BOOKX_AUTHOR_TYPES_DESCRIPTION");

    /**
     * TABLE_PRODUCT_BOOKX_BINDING
     */
    $db->Execute("ALTER TABLE {$const['TABLE_PRODUCT_BOOKX_BINDING']} " . $table_character_set . ",
            CHANGE COLUMN binding_sort_order binding_sort_order INT(11) NULL DEFAULT '0';");
    alter_indexes(TABLE_PRODUCT_BOOKX_BINDING, 'bookx_binding_id', '');
    $update_095_msg .= $list_msg("DROP INDEX bookx_binding_id - PRIMARY in use");
    $analyze .= ', ' . TABLE_PRODUCT_BOOKX_BINDING;

    /**
     * TABLE_PRODUCT_BOOKX_BINDING_DESCRIPTION
     */
    $db->Execute("ALTER TABLE {$const['TABLE_PRODUCT_BOOKX_BINDING_DESCRIPTION']} " . $table_character_set . ",
              CHANGE COLUMN binding_description binding_description VARCHAR(64) " . $column_character_set . " NULL DEFAULT NULL;");

    /**
     * TABLE_PRODUCT_BOOKX_CONDITIONS
     */
    $db->Execute("ALTER TABLE {$const['TABLE_PRODUCT_BOOKX_CONDITIONS']} " . $table_character_set . ",
            CHANGE COLUMN condition_sort_order condition_sort_order INT(11) NULL DEFAULT '0'");

    /**
     * TABLE_PRODUCT_BOOKX_CONDITIONS_DESCRIPTION
     */
    $db->Execute("ALTER TABLE {$const['TABLE_PRODUCT_BOOKX_CONDITIONS_DESCRIPTION']} " . $table_character_set . ",
               CHANGE COLUMN condition_description condition_description VARCHAR(64) " . $column_character_set . " NULL DEFAULT NULL;");

    /**
     * TABLE_PRODUCT_BOOKX_GENRES
     */
    $db->Execute("ALTER TABLE {$const['TABLE_PRODUCT_BOOKX_GENRES']} " . $table_character_set . ",
            CHANGE COLUMN genre_sort_order genre_sort_order INT(11) NULL DEFAULT '0';");
    $analyze .= ', ' . TABLE_PRODUCT_BOOKX_GENRES;

    /**
     * TABLE_PRODUCT_BOOKX_GENRES_TO_PRODUCTS
     */
    $db->Execute("ALTER TABLE {$const['TABLE_PRODUCT_BOOKX_GENRES_TO_PRODUCTS']} " . $table_character_set . ";");
    $analyze .= ', ' . TABLE_PRODUCT_BOOKX_GENRES_TO_PRODUCTS;

    /**
     * TABLE_PRODUCT_BOOKX_GENRES_DESCRIPTION
     */
    $db->Execute("ALTER TABLE {$const['TABLE_PRODUCT_BOOKX_GENRES_DESCRIPTION']} " . $table_character_set . ", 
               CHANGE COLUMN genre_description genre_description VARCHAR(64) " . $column_character_set . " NULL DEFAULT NULL;");
    //ADD UNIQUE INDEX idx_bxgd_genre_description (genre_description ASC) ;");
    alter_indexes(TABLE_PRODUCT_BOOKX_GENRES_DESCRIPTION, 'genre_description', 'idx_bxgd_genre_description');
    $update_095_msg .= $list_msg("Added INDEX idx_bxgd_genre_description to TABLE_PRODUCT_BOOKX_SERIES_DESCRIPTION<br>");
    $db->Execute("UPDATE " . TABLE_PRODUCT_BOOKX_GENRES_DESCRIPTION . " SET
        genre_description = CASE genre_description WHEN '' THEN NULL ELSE genre_description END,
        genre_image = CASE genre_image WHEN '' THEN NULL ELSE genre_image END;");
    $update_095_msg .= $list_msg("Converted all empty database fields to NULL on TABLE_PRODUCT_BOOKX_GENRES_DESCRIPTION");
    $analyze .= ', ' . TABLE_PRODUCT_BOOKX_GENRES_DESCRIPTION;

    /**
     * TABLE_PRODUCT_BOOKX_IMPRINTS
     */
    $db->Execute("ALTER TABLE {$const['TABLE_PRODUCT_BOOKX_IMPRINTS']} " . $table_character_set . ", 
            CHANGE COLUMN imprint_sort_order imprint_sort_order INT(11) NULL DEFAULT '0',
            CHANGE COLUMN imprint_name imprint_name VARCHAR(64) " . $column_character_set . " NULL DEFAULT NULL;");
    //ADD INDEX idx_bxi_imprint_name (imprint_name ASC),
    alter_indexes(TABLE_PRODUCT_BOOKX_IMPRINTS, 'imprint_name', 'idx_bxi_imprint_name');
    alter_indexes(TABLE_PRODUCT_BOOKX_IMPRINTS, 'bookx_imprint_id', '');
    $update_095_msg .= $list_msg("Added INDEX idx_bxi_imprint_name to TABLE_PRODUCT_BOOKX_IMPRINTS");
    $update_095_msg .= $list_msg("DROPPED INDEX bookx_imprint_id in TABLE_PRODUCT_BOOKX_IMPRINTS - PRIMARY in use");
    $analyze .= ', ' . TABLE_PRODUCT_BOOKX_IMPRINTS;

    /**
     * TABLE_PRODUCT_BOOKX_IMPRINTS_DESCRIPTION
     */
    $db->Execute("ALTER TABLE {$const['TABLE_PRODUCT_BOOKX_IMPRINTS_DESCRIPTION']} " . $table_character_set . ",
                CHANGE COLUMN imprint_description imprint_description TEXT " . $column_character_set . " NULL DEFAULT NULL ;");

    /**
     * TABLE_PRODUCT_BOOKX_PRINTING
     */
    $db->Execute("ALTER TABLE {$const['TABLE_PRODUCT_BOOKX_PRINTING']} " . $table_character_set . ",
            CHANGE COLUMN printing_sort_order printing_sort_order INT(11) NULL DEFAULT '0';");
    /**
     * TABLE_PRODUCT_BOOKX_PRINTING_DESCRIPTION
     */
    $db->Execute("ALTER TABLE {$const['TABLE_PRODUCT_BOOKX_PRINTING_DESCRIPTION']} " . $table_character_set . ",
                CHANGE COLUMN printing_description printing_description VARCHAR(64) " . $column_character_set . " NULL DEFAULT NULL;");

    /**
     * TABLE_PRODUCT_BOOKX_PUBLISHERS
     */
    $db->Execute("ALTER TABLE {$const['TABLE_PRODUCT_BOOKX_PUBLISHERS']} " . $table_character_set . ",
            CHANGE COLUMN publisher_sort_order publisher_sort_order INT(11) NULL DEFAULT '0',
            CHANGE COLUMN publisher_name publisher_name VARCHAR(64) " . $column_character_set . " NULL DEFAULT NULL;;");
    //ADD INDEX idx_bxp_publisher_name (publisher_name ASC);");
    alter_indexes(TABLE_PRODUCT_BOOKX_PUBLISHERS, 'publisher_name', 'idx_bxp_publisher_name');
    $update_095_msg .= $list_msg("Added INDEX idx_bxp_publisher_name to TABLE_PRODUCT_BOOKX_PUBLISHERS<br>");
    $db->Execute("UPDATE " . TABLE_PRODUCT_BOOKX_PUBLISHERS . " SET
        publisher_image = CASE publisher_image WHEN '' THEN NULL ELSE publisher_image END;");
    $update_095_msg .= $list_msg("Converted all empty database fields to NULL on TABLE_PRODUCT_BOOKX_PUBLISHERS");
    $analyze .= ', ' . TABLE_PRODUCT_BOOKX_PUBLISHERS;

    /**
     * TABLE_PRODUCT_BOOKX_PUBLISHERS_DESCRIPTION
     */
    $db->Execute("ALTER TABLE {$const['TABLE_PRODUCT_BOOKX_PUBLISHERS_DESCRIPTION']}
                CHANGE COLUMN publisher_description publisher_description TEXT " . $column_character_set . " NULL DEFAULT NULL,
                CHANGE COLUMN publisher_url publisher_url VARCHAR(191) NULL DEFAULT NULL;");

    $db->Execute("UPDATE " . TABLE_PRODUCT_BOOKX_PUBLISHERS_DESCRIPTION . " SET
        publisher_description = CASE publisher_description WHEN '' THEN NULL ELSE publisher_description END,
        publisher_url = CASE publisher_url WHEN '' THEN NULL ELSE publisher_url END;");
    $update_095_msg .= $list_msg("Converted all empty database fields to NULL on TABLE_PRODUCT_BOOKX_PUBLISHERS_DESCRIPTION");
    $analyze .= ', ' . TABLE_PRODUCT_BOOKX_PUBLISHERS_DESCRIPTION;

    /**
     * TABLE_PRODUCT_BOOKX_SERIES
     */
    $db->Execute("ALTER TABLE {$const['TABLE_PRODUCT_BOOKX_SERIES']} " . $table_character_set . ",
            CHANGE COLUMN series_sort_order series_sort_order INT(11) NULL DEFAULT '0';");

    /**
     * TABLE_PRODUCT_BOOKX_SERIES_DESCRIPTION
     */
    $db->Execute("ALTER TABLE {$const['TABLE_PRODUCT_BOOKX_SERIES_DESCRIPTION']} " . $table_character_set . ",
            CHANGE COLUMN series_name series_name VARCHAR(64) " . $column_character_set . " NULL DEFAULT NULL,
            CHANGE COLUMN series_description series_description TEXT " . $column_character_set . " NULL DEFAULT NULL;");
    //ADD INDEX idx_bxsd_series_name (series_name ASC);");
    alter_indexes(TABLE_PRODUCT_BOOKX_SERIES_DESCRIPTION, 'series_name', 'idx_bxsd_series_name');
    $update_095_msg .= $list_msg("Added INDEX idx_bxsd_series_name to TABLE_PRODUCT_BOOKX_SERIES_DESCRIPTION<br>");
    $db->Execute("UPDATE " . TABLE_PRODUCT_BOOKX_SERIES_DESCRIPTION . " SET
        series_image = CASE series_image WHEN '' THEN NULL ELSE series_image END,
        series_description = CASE series_description WHEN '' THEN NULL ELSE series_description END;");
    $update_095_msg .= $list_msg("Converted all empty database fields to NULL on TABLE_PRODUCT_BOOKX_SERIES_DESCRIPTION");
    $analyze .= ', ' . TABLE_PRODUCT_BOOKX_SERIES_DESCRIPTION;


    /**
     * @since v1.0.0
     */
    $db->Execute("CREATE TABLE IF NOT EXISTS  " . TABLE_PRODUCT_BOOKX_FAMILIES . " (
            bookx_family_id int(11) NOT NULL,
            bookx_family_name varchar(64) NOT NULL,
            bookx_family_discount float NOT NULL DEFAULT '0',
            bookx_family_stock_online tinyint(1) NOT NULL DEFAULT '0',
            PRIMARY KEY (bookx_family_id),
            UNIQUE KEY bookx_family_name (bookx_family_name),
            KEY idx_family_id (bookx_family_id, bookx_family_name)) 
            ENGINE=InnoDB DEFAULT CHARSET=" . $default_db_encoding . ";");
    $update_095_msg .= $list_msg("Added TABLE_PRODUCT_BOOKX_FAMILIES<br>");

    $db->Execute("CREATE TABLE IF NOT EXISTS  " . TABLE_PRODUCT_BOOKX_FAMILIES_TO_PRODUCTS . " (
            primary_id int(11) NOT NULL AUTO_INCREMENT,
            products_id int(11) NOT NULL,
            bookx_family_id int(11) NOT NULL,
            PRIMARY KEY (primary_id),
            KEY bookx_family_id (bookx_family_id) USING BTREE,
            CONSTRAINT fk_bxt_families_id FOREIGN KEY (bookx_family_id) 
            REFERENCES product_bookx_families (bookx_family_id) 
            ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=" . $default_db_encoding . ";");
    $update_095_msg .= $list_msg("Added TABLE_PRODUCT_BOOKX_FAMILIES_TO_PRODUCTS<br>");

    $sql = "CREATE TABLE ". TABLE_PRODUCT_BOOKX_SEARCH ." (
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
            ) ENGINE=InnoDB DEFAULT CHARSET=" . $default_db_encoding .";";
    $update_095_msg .= $list_msg("Added TABLE_PRODUCT_BOOKX_BOOKX_SEARCH<br>");
    
   
   
//zen_register_admin_page('bookxFamilies', 'BOX_CATALOG_PRODUCT_BOOKX_FAMILIES', 'FILENAME_BOOKX_FAMILIES', '', 'extras', 'Y', 20);

    $db->Execute("REPLACE INTO {$const['TABLE_CONFIGURATION']} 
            (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, 
            last_modified, date_added, use_function, set_function)
            VALUES (
            'BookX Version', 'BOOKX_VERSION_CHECK', '" . $bookx_version . "', 'BookX Version is stored but not editable', 0, 10000, NOW(), NOW(), NULL, NULL
             );");

    $db->Execute("ANALYZE TABLE " . $analyze . "");
    /**
     * Checks Table Product Relations. Doesn't delete them here. 
     */
    $update_095_msg .= "<stong>Table Product Relations:</strong> " . bookx_check_missing_product_relations(
            array(
            TABLE_PRODUCT_BOOKX_AUTHORS_TO_PRODUCTS => TABLE_PRODUCTS,
            TABLE_PRODUCT_BOOKX_GENRES_TO_PRODUCTS => TABLE_PRODUCTS
            ), 'products_id',false);

    $update_095_msg .= $list_msg("Bookx Updated to version " . $bookx_version);

    zen_register_admin_page('bookxFamilies', 'BOX_CATALOG_PRODUCT_BOOKX_FAMILIES', 'FILENAME_BOOKX_FAMILIES', '', 'extras', 'Y', 91);
    
    if (isset($_SESSION['bookx_install']) && $_SESSION['bookx_install'] == 'do_reset') {

        $messageStack->add_session('<ul style="line-height:1.5;">' . $update_095_msg . '</ul>', 'info');
        
        zen_redirect(FILENAME_BOOKX_TOOLS . '.php?action=bookx_reset_to_defaults');
    } else {
        /**
        * If its' a update with existing bookx tables but no pType and Version, no cf_gid is set.
        * On reset will add this
        */
        if ($bookx_uses_ceon == true) {
        $db->Execute("REPLACE INTO {$const['TABLE_CONFIGURATION']} 
            (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function)
            VALUES (
            'Use CEON URI Module', 'BOOKX_USES_CEON_URI_MODULE', '0', 
            'Enable Ceon Uri Module. Default 0', {$cf_gid}, '310', now(), now(), NULL,
            'zen_cfg_select_option(array(\"0\", \"1\"),');");

    }

    if ($bookx_uses_dinamic_metatags == true) {

        $db->Execute("REPLACE INTO {$const['TABLE_CONFIGURATION']} 
            (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function)
            VALUES (
            'Use Dinamic MetaTags', 'BOOKX_USES_DINAMIC_METATAGS', '0', 
            'Enable Dinamic MetaTags. Default 0', {$cf_gid}, '320', now(), now(), NULL,
            'zen_cfg_select_option(array(\"0\", \"1\"),');");
    }
        
        $messageStack->add('<ul style="line-height:1.5;">' . $update_095_msg . '</ul>', 'info');
        //zen_redirect(zen_href_link(FILENAME_BOOKX_TOOLS));
    }
   
} else {
    // Prevent update with no options.
    $install_incomplete = true; 
    zen_redirect(zen_href_link(FILENAME_BOOKX_TOOLS, 'action=bookx_install_options'));  
}