<?php

if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

$sql = "UPDATE {$const['TABLE_GET_TERMS_TO_FILTER']} SET get_term_table = 'TABLE_PRODUCT_BOOKX_BINDING_DESCRIPTION' WHERE get_term_table = 'TABLE_PRODUCT_BOOKX_BINDING';";
$db->Execute($sql);

$sql = "UPDATE {$const['TABLE_GET_TERMS_TO_FILTER']} SET get_term_table = 'TABLE_PRODUCT_BOOKX_CONDITIONS_DESCRIPTION' WHERE get_term_table = 'TABLE_PRODUCT_BOOKX_CONDITIONS';";
$db->Execute($sql);

$sql = "UPDATE {$const['TABLE_GET_TERMS_TO_FILTER']} SET get_term_table = 'TABLE_PRODUCT_BOOKX_PRINTING_DESCRIPTION' WHERE get_term_table = 'TABLE_PRODUCT_BOOKX_PRINTING';";
$db->Execute($sql);

$sql = "ALTER TABLE {$const['TABLE_PRODUCT_BOOKX_GENRES_TO_PRODUCTS']} ADD INDEX ( products_id );";
$db->Execute($sql);

$sql = "ALTER TABLE {$const['TABLE_PRODUCT_BOOKX_GENRES_TO_PRODUCTS']} ADD INDEX ( bookx_genre_id );";
$db->Execute($sql);

if (!empty($cf_gid)) {
    $sql = <<<EOT
								REPLACE INTO {$const['TABLE_CONFIGURATION']} (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function)
					    			VALUES
							    	 	 ('Breadcrumbs: Use Bookx instead of ZC Categories', 'BOOKX_BREAD_USE_BOOKX_NO_CATEGORIES', '1', 'Let BookX fill the "Breadcrumb" navigation instead of letting ZenCart populate the "Breadcrumb" navigation with the category path. This only affects the product info page for BookX products or product listings resulting from applying a BookX filter.', {$cf_gid}, 240, NOW(), NOW(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED'))),")
                            		    ,('Breadcrumbs: Insert Publisher on Product Detail Page', 'BOOKX_BREAD_ADD_PUBLISHER', '10', 'If "Use Bookx instead of ZC Categories" is enabled, then the "Breadcrumb" navigation is filled automatically by BookX for a product info page, even if the user got there directly e.g. via a search. A value of "0" disables the inclusion of the Publisher and a number above zero determines the order in which the Publisher is inserted in the "Breadcrumb" navigation trail.', {$cf_gid}, 250, NOW(), NOW(), NULL, NULL)
                            		    ,('Breadcrumbs: Insert Imprint on Product Detail Page', 'BOOKX_BREAD_ADD_IMPRINT', '20', 'If "Use Bookx instead of ZC Categories" is enabled, then the "Breadcrumb" navigation is filled automatically by BookX for a product info page, even if the user got there directly e.g. via a search. A value of "0" disables the inclusion of the Imprint and a number above zero determines the order in which the Imprint is inserted in the "Breadcrumb" navigation trail.', {$cf_gid}, 260, NOW(), NOW(), NULL, NULL)
                            		    ,('Breadcrumbs: Insert Series on Product Detail Page', 'BOOKX_BREAD_ADD_SERIES', '30', 'If "Use Bookx instead of ZC Categories" is enabled, then the "Breadcrumb" navigation is filled automatically by BookX for a product info page, even if the user got there directly e.g. via a search. A value of "0" disables the inclusion of the Series and a number above zero determines the order in which the Series is inserted in the "Breadcrumb" navigation trail.', {$cf_gid}, 270, NOW(), NOW(), NULL, NULL)
                            		    ,('Breadcrumbs: Insert Genre on Product Detail Page', 'BOOKX_BREAD_ADD_GENRE', '0', 'If "Use Bookx instead of ZC Categories" is enabled, then the "Breadcrumb" navigation is filled automatically by BookX for a product info page, even if the user got there directly e.g. via a search. A value of "0" disables the inclusion of the Genre and a number above zero determines the order in which the Genre is inserted in the "Breadcrumb" navigation trail. ATTENTION: This may produce unexpected results when multiple Genres are assigned to a book, as only one Genre can be shown.', {$cf_gid}, 280, NOW(), NOW(), NULL, NULL)
                            		    ,('Breadcrumbs: Insert Author on Product Detail Page', 'BOOKX_BREAD_ADD_AUTHOR', '0', 'If "Use Bookx instead of ZC Categories" is enabled, then the "Breadcrumb" navigation is filled automatically by BookX for a product info page, even if the user got there directly e.g. via a search. A value of "0" disables the inclusion of the Author and a number above zero determines the order in which the Author is inserted in the "Breadcrumb" navigation trail. ATTENTION: This may produce unexpected results when multiple Authors are assigned to a book, as only one author can be show.', {$cf_gid}, 290, NOW(), NOW(), NULL, NULL)					    				    				
		                                ,('Product Info: "Previous"/"Next Buttons" based on active BookX Filter', 'BOOKX_NEXT_PREVIOUS_BASED_ON_FILTER', '1', 'If this feature is enabled, then the buttons "next", "previous", "back to listing" on the product info page will no longer navigate back an fourth in the ZC <strong>Category</b> containing the product, but rather navigate within the set of products as determined by the active BookX filter (e.g. Author).', {$cf_gid}, 300, NOW(), NOW(), NULL, NULL)
                            		    ;
EOT;

    $db->Execute($sql);

    if ($german_installed) {
        $sql = <<<EOT
		 							REPLACE INTO {$const['TABLE_CONFIGURATION_LANGUAGE']} (configuration_title, configuration_key, configuration_description, configuration_language_id)
							    	  VALUES
								    	 ('"Brotkrümel" Navigation: Ausfüllen durch BookX', 'BOOKX_BREAD_USE_BOOKX_NO_CATEGORIES', 'BookX soll die "Brotkrümel" Navigation ausfüllen und nicht Zen Cart mit den angelegten Produktkategorien. Dies betrifft nur die Artikeldetails-Seite für BookX-Produkte und Artikellisten die Ergebnisse eines BookX-Filters zeigen<br /><br />', 43)
                        			    ,('"Brotkrümel" Navigation: Verlag hinzufügen', 'BOOKX_BREAD_ADD_PUBLISHER', 'Wenn "Brotkrümel Navigation: Ausfüllen durch BookX" aktiviert ist, befüllt BookX automatisch den Krümelpfad, auch wenn der Kunde direkt zur Artikeldetailseite gelangt ist z.B. über die Suchfunktion. Bei einem Wert von "0" wird der Verlag dem Krümelpfad nicht hinzugefügt. Ein höherer Wert legt die Reihenfolge fest, in der der Verlag dem Krümelpfad hinzugefügt wird./><br />', 43)
                        			    ,('"Brotkrümel" Navigation: Label hinzufügen', 'BOOKX_BREAD_ADD_IMPRINT', 'Wenn "Brotkrümel Navigation: Ausfüllen durch BookX" aktiviert ist, befüllt BookX automatisch den Krümelpfad, auch wenn der Kunde direkt zur Artikeldetailseite gelangt ist z.B. über die Suchfunktion. Bei einem Wert von "0" wird das Label dem Krümelpfad nicht hinzugefügt. Ein höherer Wert legt die Reihenfolge fest, in der das Label dem Krümelpfad hinzugefügt wird./><br />', 43)
                        			    ,('"Brotkrümel" Navigation: Serie hinzufügen', 'BOOKX_BREAD_ADD_SERIES', 'Wenn "Brotkrümel Navigation: Ausfüllen durch BookX" aktiviert ist, befüllt BookX automatisch den Krümelpfad, auch wenn der Kunde direkt zur Artikeldetailseite gelangt ist z.B. über die Suchfunktion. Bei einem Wert von "0" wird die Serie dem Krümelpfad nicht hinzugefügt. Ein höherer Wert legt die Reihenfolge fest, in der die Serie dem Krümelpfad hinzugefügt wird./><br />', 43)
                        			    ,('"Brotkrümel" Navigation: Genre hinzufügen', 'BOOKX_BREAD_ADD_GENRE', 'Wenn "Brotkrümel Navigation: Ausfüllen durch BookX" aktiviert ist, befüllt BookX automatisch den Krümelpfad, auch wenn der Kunde direkt zur Artikeldetailseite gelangt ist z.B. über die Suchfunktion. Bei einem Wert von "0" wird das Genre dem Krümelpfad nicht hinzugefügt. Ein höherer Wert legt die Reihenfolge fest, in der das Genre dem Krümelpfad hinzugefügt wird. ACHTUNG: Wenn einem Buch mehrere Genres zugewiesen sind, kann leider nur eines im Krümelpfad gezeigt werden./><br />', 43)
                        			    ,('"Brotkrümel" Navigation: Autor hinzufügen', 'BOOKX_BREAD_ADD_AUTHOR', 'Wenn "Brotkrümel Navigation: Ausfüllen durch BookX" aktiviert ist, befüllt BookX automatisch den Krümelpfad, auch wenn der Kunde direkt zur Artikeldetailseite gelangt ist z.B. über die Suchfunktion. Bei einem Wert von "0" wird der Autor dem Krümelpfad nicht hinzugefügt. Ein höherer Wert legt die Reihenfolge fest, in der der Autor dem Krümelpfad hinzugefügt wird. ACHTUNG: Wenn einem Buch mehrere Autoren zugewiesen sind, kann leider nur einer im Krümelpfad gezeigt werden/><br />', 43)	
			                            ,('Artikeldetails: Buttons "vorheriger / nächster Artikel" navigiert in Bookx Kategorie', 'BOOKX_NEXT_PREVIOUS_BASED_ON_FILTER', 'Wenn diese Einstellung aktiviert ist, navigieren die Buttons "Nächster Artikel", "Vorheriger Artikel" und "Zurück zur Liste" nicht mehr hin und her zwischen den Artikeln in der gleichen ZC Kategorie, sondern vor und zurück in der Ergebnisliste eines aktiven Bookx Filters.<br /><br />', 43)	
		 							    ;
EOT;

        $db->Execute($sql);
    }
}
else {
    $messageStack->add(BOOKX_MS_CONFIG_TYPE_BOOKX_MISSING, 'error');
}