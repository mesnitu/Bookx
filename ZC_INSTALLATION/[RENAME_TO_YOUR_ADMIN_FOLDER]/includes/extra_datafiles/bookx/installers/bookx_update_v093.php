<?php

if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}


if (defined('TABLE_ADMIN_PAGES') && defined('TABLE_ADMIN_PAGES_TO_PROFILES')) {
    
    zen_deregister_admin_pages('bookxProduct');
    zen_register_admin_page('bookxProduct', 'BOX_CATALOG_PRODUCT_BOOKX', 'FILENAME_BOOKX_PRODUCT', '', 'catalog', 'Y', 2);

    $sql = "SELECT profile_id FROM {$const['TABLE_ADMIN_PAGES_TO_PROFILES']} WHERE page_key = 'product'";
    $profile_ids = $db->Execute($sql);

    while (!$profile_ids->EOF) {
        $db->Execute("REPLACE INTO {$const['TABLE_ADMIN_PAGES_TO_PROFILES']} (profile_id, page_key) VALUES ('{$profile_ids->fields['profile_id']}', 'bookxProduct')");
        $profile_ids->MoveNext();
    }
}

$sql = "REPLACE INTO {$const['TABLE_GET_TERMS_TO_FILTER']} (get_term_name, get_term_table, get_term_name_field) VALUES ('bookx_author_type_id', 'TABLE_PRODUCT_BOOKX_AUTHOR_TYPES_DESCRIPTION', 'type_description');";
$db->Execute($sql);

$sql = "SHOW COLUMNS FROM {$const['TABLE_PRODUCT_BOOKX_AUTHORS']} WHERE field = 'author_image_copyright';";
$res = $db->Execute($sql);
if ($res->RecordCount() == 0) {
    $sql = "ALTER TABLE {$const['TABLE_PRODUCT_BOOKX_AUTHORS']} ADD author_image_copyright VARCHAR(64) NULL DEFAULT NULL AFTER author_image;";
    $db->Execute($sql);
}

$sql = "CREATE INDEX bookx_author_id_index ON {$const['TABLE_PRODUCT_BOOKX_AUTHORS_TO_PRODUCTS']} (bookx_author_id)";
$db->Execute($sql);

$sql = "CREATE INDEX products_id_index ON {$const['TABLE_PRODUCT_BOOKX_AUTHORS_TO_PRODUCTS']} (products_id)";
$db->Execute($sql);

//*** correct meaningless date entries
$sql = "UPDATE {$const['TABLE_PRODUCTS']} SET products_date_available = null WHERE products_date_available = '0000-00-00 00:00:00'";
$db->Execute($sql);

//*** correct sort oder, make room for more settings
$sql = "UPDATE {$const['TABLE_CONFIGURATION']} SET sort_order = 178 WHERE configuration_key = 'MAX_DISPLAY_BOOKX_SERIES_LISTING'";
$db->Execute($sql);
$sql = "UPDATE {$const['TABLE_CONFIGURATION']} SET sort_order = 192 WHERE configuration_key = 'BOOKX_SERIES_LISTING_SHOW_ONLY_STOCKED'";
$db->Execute($sql);
$sql = "UPDATE {$const['TABLE_CONFIGURATION']} SET sort_order = 193 WHERE configuration_key = 'BOOKX_SERIES_LISTING_ORDER_BY'";
$db->Execute($sql);

if (!empty($cf_gid)) {
    $sql = <<<EOT
								REPLACE INTO {$const['TABLE_CONFIGURATION']} (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function)
					    			VALUES
							    	 	('New Products: Show Product Description', 'BOOKX_NEW_PRODUCTS_SHOW_PRODUCT_DESCRIPTION_NUMOFCHARS', '150', 'Show (part of) the product description in the "New Products" module. Enter the number of characters after which the description will be truncated. A value of "0" disables the display and a value of "-1" shows the entire description without truncating it.<br /><br />', {$cf_gid}, 201, NOW(), NOW(), NULL, NULL)
									   ,('Upcoming Products: Show Product Image', 'BOOKX_UPCOMING_PRODUCTS_SHOW_PRODUCT_IMAGE', '1', 'Show product image in "Upcoming Products" module', {$cf_gid}, 220, NOW(), NOW(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED'))),")
									   ,('Upcoming Products Image: Maximum Height', 'BOOKX_UPCOMING_PRODUCT_IMAGE_MAX_HEIGHT', '120', '<br />Maximum height in pixels for product images in upcoming products module. A value of 0 will not constrain the height of the image.', {$cf_gid}, 222, NOW(), NOW(), NULL, NULL)
									   ,('Upcoming Products Image: Maximum Width', 'BOOKX_UPCOMING_PRODUCT_IMAGE_MAX_WIDTH', '80', '<br />Maximum width in pixels for product images in upcoming products module. A value of 0 will not constrain the width of the image.', {$cf_gid}, 223, NOW(), NOW(), NULL, NULL)
									   ,('Upcoming Products: Show Product Description', 'BOOKX_UPCOMING_PRODUCTS_SHOW_PRODUCT_DESCRIPTION_NUMOFCHARS', '150', 'Show (part of) the product description in the "Upcoming Products" module. Enter the number of characters after which the description will be truncated. A value of "0" disables the display and a value of "-1" shows the entire description without truncating it.<br /><br />', {$cf_gid}, 230, NOW(), NOW(), NULL, NULL)
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
                            		    ,('Genre Listing: Max. number of genres per page', 'MAX_DISPLAY_BOOKX_GENRE_LISTING', '30', '<br />Maximum number of listed genres on genre listing. No value defaults to 20 rows per page.', {$cf_gid}, 195, NOW(), NOW(), NULL, NULL)
                            		    ,('Genre Listing Image: Maximum Height', 'BOOKX_GENRE_LISTING_IMAGE_MAX_HEIGHT', '90', '<br />Maximum height in pixels for genre image on genre listing. A value of 0 will show all images at their actual size without any scaling.', {$cf_gid}, 196, NOW(), NOW(), NULL, NULL)
                            		    ,('Genre Listing Image: Maximum Width', 'BOOKX_GENRE_LISTING_IMAGE_MAX_WIDTH', '100', '<br />Maximum width in pixels for genre image on genre listing. A value of 0 will show all images at their actual size without any scaling.', {$cf_gid}, 197, NOW(), NOW(), NULL, NULL)
                            		    ,('Genre Listing: Show only genres with books in stock', 'BOOKX_GENRE_LISTING_SHOW_ONLY_STOCKED', '1', '<br />Show only those genres in the genre listing, which have a book in the shop that is in stock (i.e. product is visible <u>and</u> stock is greater than "0"). If this setting is turned on, a checkbox is displayed on top of the genre listing, which allows users to override this setting. If this is not desired, set CSS "display: none" to hide it.', {$cf_gid}, 198,  now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED'))),")
                            		    ,('Genre Listing: Sort genres by', 'BOOKX_GENRE_LISTING_ORDER_BY', '1', '<br />Sort genres in genre listing by:', {$cf_gid}, 198,  now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ORDER_BY_NAME')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ORDER_BY_SORT_ORDER'))),")
                            		        
                            		        ;
EOT;
    $db->Execute($sql);

    if ($german_installed) {
        $sql = <<<EOT
		 							REPLACE INTO {$const['TABLE_CONFIGURATION_LANGUAGE']} (configuration_title, configuration_key, configuration_description, configuration_language_id)
							    	  VALUES
								    	 ('Neue Artikel: Artikelbeschreibung anzeigen', 'BOOKX_NEW_PRODUCTS_SHOW_PRODUCT_DESCRIPTION_NUMOFCHARS', 'Im Modul "Neue Artikel" soll die Artikelbeschreibung (teilweise) angezeigt werden. Anzahl der Zeichen nach denen die Beschreibung abgeschnitten wird. Bei einem Wert von "0" wird die Beschreibung nicht angezeigt und bei einem Wert von "-1" wird die gesamte Beschreibung ungekürzt angezeigt.<br /><br />', 43)
								    	,('Artikelankündigungen: Artikelbild anzeigen', 'BOOKX_UPCOMING_PRODUCTS_SHOW_PRODUCT_IMAGE', 'Im Modul "Artikelankündigungen" soll das Artikelbild angezeigt werden.<br /><br />', 43)
									    ,('Artikelankündigungen Artikelbild: Maximale Höhe', 'BOOKX_UPCOMING_PRODUCT_IMAGE_MAX_HEIGHT', '<br />Maximale Höhe (in Pixeln) der Artikelbilder im Modul „Artikelankündigungen”. Bei einem Wert von 0 wird die Höhe der Bilder nicht begrenzt.', 43)
									    ,('Artikelankündigungen Artikelbild: Maximale Breite', 'BOOKX_UPCOMING_PRODUCT_IMAGE_MAX_WIDTH', '<br />Maximale Breite (in Pixeln) der Artikelbilder in der Liste aller Serien. Bei einem Wert von 0 wird die Breite der Bilder nicht begrenzt.', 43)
		 								,('Artikelankündigungen: Artikelbeschreibung anzeigen', 'BOOKX_UPCOMING_PRODUCTS_SHOW_PRODUCT_DESCRIPTION_NUMOFCHARS', 'Im Modul "Artikelankündigungen" soll die Artikelbeschreibung (teilweise) angezeigt werden. Anzahl der Zeichen nach denen die Beschreibung abgeschnitten wird. Bei einem Wert von "0" wird die Beschreibung nicht angezeigt und bei einem Wert von "-1" wird die gesamte Beschreibung ungekürzt angezeigt.<br /><br />', 43)
                        			    ,('Unterlabelliste: Anzahl Unterlabel pro Seite', 'MAX_DISPLAY_BOOKX_IMPRINT_LISTING', '<br />Maximale Anzahl von Unterlabel pro Seite in der Unterlabelliste. Bei "0" oder keinem Wert, werden 20 Unterlabel pro Seite angezeigt.', 43)
                        			    ,('Unterlabellogo in Unterlabelliste: Maximale Höhe', 'BOOKX_IMPRINT_LISTING_IMAGE_MAX_HEIGHT', '<br />Maximale Höhe (in Pixeln) des Unterlabellogos in der Liste aller Unterlabel. Bei einem Wert von 0 werden Bilder in voller Größe angezeigt und nicht skaliert.', 43)
                        			    ,('Unterlabellogo in Unterlabelliste: Maximale Breite', 'BOOKX_IMPRINT_LISTING_IMAGE_MAX_WIDTH', '<br />Maximale Breite (in Pixeln) des Unterlabellogos in der Liste aller Unterlabel. Bei einem Wert von 0 werden Bilder in voller Größe angezeigt und nicht skaliert.', 43)
                        			    ,('Unterlabelliste: Nur lieferbare Bücher zeigen', 'BOOKX_IMPRINT_LISTING_SHOW_ONLY_STOCKED', '<br />In der Unterlabelliste nur Unterlabel anzeigen, für die ein lieferbares Buch im Shop existiert (d.h. der Artikel ist sichtbar <u>und</u> Bestand ist größer "0"). Wenn eingeschaltet, wird über der Unterlabelliste eine Checkbox angezeigt, die es Shopbesuchern erlaubt auch Unterlabel ohne lieferbare Bücher anzuzeigen. Ist diese Checkbox nicht gewünscht, kann diese via CSS "display: none" versteckt werden.', 43)
                        			    ,('Unterlabelliste: Unterlabel sortieren nach:', 'BOOKX_IMPRINT_LISTING_ORDER_BY', '<br />Unterlabel in der Unterlabelliste werden sortiert nach:', 43)
                        			    ,('Verlagsliste: Anzahl Verlage pro Seite', 'MAX_DISPLAY_BOOKX_PUBLISHER_LISTING', '<br />Maximale Anzahl von Verlagen pro Seite in der Verlagsliste. Bei "0" oder keinem Wert, werden 20 Verlage pro Seite angezeigt.', 43)
                        			    ,('Verlagslogo in Verlagsliste: Maximale Höhe', 'BOOKX_PUBLISHER_LISTING_IMAGE_MAX_HEIGHT', '<br />Maximale Höhe (in Pixeln) des Verlagslogos in der Liste aller Verlage. Bei einem Wert von 0 werden Bilder in voller Größe angezeigt und nicht skaliert.', 43)
                        			    ,('Verlagslogo in Verlagsliste: Maximale Breite', 'BOOKX_PUBLISHER_LISTING_IMAGE_MAX_WIDTH', '<br />Maximale Breite (in Pixeln) des Verlagslogos in der Liste aller Verlage. Bei einem Wert von 0 werden Bilder in voller Größe angezeigt und nicht skaliert.', 43)
                        			    ,('Verlagsliste: Nur lieferbare Bücher zeigen', 'BOOKX_PUBLISHER_LISTING_SHOW_ONLY_STOCKED', '<br />In der Verlagsliste nur Verlage anzeigen, für die ein lieferbares Buch im Shop existiert (d.h. der Artikel ist sichtbar <u>und</u> Bestand ist größer "0"). Wenn eingeschaltet, wird über der Verlagsliste eine Checkbox angezeigt, die es Shopbesuchern erlaubt auch Verlage ohne lieferbare Bücher anzuzeigen. Ist diese Checkbox nicht gewünscht, kann diese via CSS "display: none" versteckt werden.', 43)
                        			    ,('Verlagsliste: Verlage sortieren nach:', 'BOOKX_PUBLISHER_LISTING_ORDER_BY', '<br />Verlage in der Verlagsliste werden sortiert nach:', 43)
                        		        ,('Genreliste: Anzahl Genres pro Seite', 'MAX_DISPLAY_BOOKX_GENRE_LISTING', '<br />Maximale Anzahl von Genres pro Seite in der Genreliste. Bei "0" oder keinem Wert, werden 20 Genres pro Seite angezeigt.', 43)
                        			    ,('Genrelogo in Genreliste: Maximale Höhe', 'BOOKX_GENRE_LISTING_IMAGE_MAX_HEIGHT', '<br />Maximale Höhe (in Pixeln) des Genrelogos in der Liste aller Genres. Bei einem Wert von 0 werden Bilder in voller Größe angezeigt und nicht skaliert.', 43)
                        			    ,('Genrelogo in Genreliste: Maximale Breite', 'BOOKX_GENRE_LISTING_IMAGE_MAX_WIDTH', '<br />Maximale Breite (in Pixeln) des Genrelogos in der Liste aller Genres. Bei einem Wert von 0 werden Bilder in voller Größe angezeigt und nicht skaliert.', 43)
                        			    ,('Genreliste: Nur lieferbare Bücher zeigen', 'BOOKX_GENRE_LISTING_SHOW_ONLY_STOCKED', '<br />In der Genreliste nur Genres anzeigen, für die ein lieferbares Buch im Shop existiert (d.h. der Artikel ist sichtbar <u>und</u> Bestand ist größer "0"). Wenn eingeschaltet, wird über der Genreliste eine Checkbox angezeigt, die es Shopbesuchern erlaubt auch Genres ohne lieferbare Bücher anzuzeigen. Ist diese Checkbox nicht gewünscht, kann diese via CSS "display: none" versteckt werden.', 43)
                        			    ,('Genreliste: Genres sortieren nach:', 'BOOKX_GENRE_LISTING_ORDER_BY', '<br />Genres in der Genreliste werden sortiert nach:', 43)		 							    
		 							    ;
EOT;

        $db->Execute($sql);
    }
}
else {
    $messageStack->add(BOOKX_MS_CONFIG_TYPE_BOOKX_MISSING, 'error');
}

if (!empty($bookx_pType_id)) {
    if ($german_installed) {
        $sql = "UPDATE {$const['TABLE_PRODUCT_TYPE_LAYOUT_LANGUAGE']}
	    						SET configuration_title = 'Artikelliste: Bandnummer anzeigen',
	    						configuration_description = 'Bandnummer in der Artikelliste anzeigen.'
	    						WHERE configuration_key = 'SHOW_PRODUCT_BOOKX_LISTING_VOLUME' AND languages_id = '43';";
        $db->Execute($sql);
    }

    $sql = <<<EOT
			    				REPLACE INTO {$const['TABLE_PRODUCT_TYPE_LAYOUT']} (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, last_modified, date_added, use_function, set_function )
	                              VALUES
		                                ('Filter Sidebox - Filter Author Type', 'SHOW_PRODUCT_BOOKX_FILTER_AUTHOR_TYPE', '1', 'Display a filter for Author Type in the Bookx Filter Sidebox.', {$bookx_pType_id}, '655', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED'))),")
		                                ,('Filter Sidebox - Link to Imprint List', 'SHOW_PRODUCT_BOOKX_LINK_IMPRINT_LIST', '1', 'Show a link to display the list of all Imprints in the Bookx Filter Sidebox.', {$bookx_pType_id}, '695', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED'))),")
		                                ,('Filter Sidebox - Link to Publisher List', 'SHOW_PRODUCT_BOOKX_LINK_PUBLISHER_LIST', '1', 'Show a link to display the list of all Publishers in the Bookx Filter Sidebox.', {$bookx_pType_id}, '695', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED'))),")
		                                ,('Filter Sidebox - Link to Genres List', 'SHOW_PRODUCT_BOOKX_LINK_GENRES_LIST', '1', 'Show a link to display the list of all Genres in the Bookx Filter Sidebox.', {$bookx_pType_id}, '695', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED'))),")
		                                ,('Filter Sidebox - Allow multiple filters active', 'ALLOW_PRODUCT_BOOKX_FILTER_MULTIPLE', '0', 'Allow multiple filters to be active in the Bookx Filter Sidebox. Otherwise setting one filter will cancel the previous filter. EXCEPT: The combination of filters "Author" and "Author Type" is always enabled.', {$bookx_pType_id}, '699', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED'))),")
		                                    ;
EOT;
    $db->Execute($sql);

    if ($german_installed) {
        $sql = <<<EOT
	                        REPLACE INTO {$const['TABLE_PRODUCT_TYPE_LAYOUT_LANGUAGE']} (configuration_title, configuration_key, languages_id, configuration_description, last_modified, date_added)
	                              VALUES
								    	 ('Filter Sidebox - Link zur Autorenliste', 'SHOW_PRODUCT_BOOKX_LINK_AUTHOR_LIST', '43', 'Link zur Liste aller Autoren in der Bookx Filter Sidebox anzeigen.', now(), now())
	                                    ,('Filter Sidebox - Link zur Serienliste', 'SHOW_PRODUCT_BOOKX_LINK_SERIES_LIST', '43', 'Link zur Liste aller Serien in der Bookx Filter Sidebox anzeigen.', now(), now())
                                        ,('Filter Sidebox - Link zur Unterlabelliste', 'SHOW_PRODUCT_BOOKX_LINK_IMPRINT_LIST', '43', 'Link zur Liste aller Unterlabelliste in der Bookx Filter Sidebox anzeigen.', now(), now())
                                        ,('Filter Sidebox - Link zur Verlagsliste', 'SHOW_PRODUCT_BOOKX_LINK_PUBLISHER_LIST', '43', 'Link zur Liste aller Verlage in der Bookx Filter Sidebox anzeigen.', now(), now())
                           		        ,('Filter Sidebox - Link zur Genreliste', 'SHOW_PRODUCT_BOOKX_LINK_GENRES_LIST', '43', 'Link zur Liste aller Genres in der Bookx Filter Sidebox anzeigen.', now(), now())                            
                                        ,('Filter Sidebox - Mehrere Filter zulassen', 'ALLOW_PRODUCT_BOOKX_FILTER_MULTIPLE', '43', 'Erlaubt es, mehrere Filter in der Bookx Filter Sidebox zu setzen. Anderfalls ersetzt eine Filterauswahl einen ggf. vorher gesetzten Filter. AUSNAHME: Die Kombination der Filter "Autor" und "Autorentyp" ist immer erlaubt.', now(), now())
	                            ;
EOT;
        $db->Execute($sql);
    }
}
else {
    $messageStack->add(BOOKX_MS_PRODUCT_TYPE_BOOKX_MISSING, 'error');
}