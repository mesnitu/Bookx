<?php

if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

$sql = "SELECT IFNULL(column_name, '') FROM information_schema.columns WHERE table_name = '{$const['TABLE_PRODUCT_BOOKX_AUTHORS']}' AND column_name = 'author_default_type';";
	    		$result = $db->Execute($sql);

	    		if (0 == $result->RecordCount()) {
	    			$sql = "ALTER TABLE {$const['TABLE_PRODUCT_BOOKX_AUTHORS']} ADD author_default_type INT NULL DEFAULT NULL AFTER author_image;";
	    			$db->Execute($sql);
	    		}

	    		$sql = "SELECT IFNULL(column_name, '') FROM information_schema.columns WHERE table_name = '{$const['TABLE_PRODUCT_BOOKX_EXTRA']}' AND column_name = 'isbn';";
	    		$result = $db->Execute($sql);

	    		if (0 == $result->RecordCount()) {
	    			$sql = "ALTER TABLE {$const['TABLE_PRODUCT_BOOKX_EXTRA']} ADD isbn VARCHAR(13) NULL DEFAULT NULL AFTER size;";
	    		 	$db->Execute($sql);
	    		}


	    		//** fix default values for sort order fields
	    		$sql = "ALTER TABLE {$const['TABLE_PRODUCT_BOOKX_AUTHORS']} CHANGE author_sort_order author_sort_order INT( 11 ) NOT NULL DEFAULT '0';";
	    		$db->Execute($sql);

	    		$sql = "ALTER TABLE {$const['TABLE_PRODUCT_BOOKX_BINDING']} CHANGE binding_sort_order binding_sort_order INT( 11 ) NOT NULL DEFAULT '0';";
	    		$db->Execute($sql);

	    		$sql = "ALTER TABLE {$const['TABLE_PRODUCT_BOOKX_CONDITIONS']} CHANGE condition_sort_order condition_sort_order INT( 11 ) NOT NULL DEFAULT '0';";
	    		$db->Execute($sql);

	    		$sql = "ALTER TABLE {$const['TABLE_PRODUCT_BOOKX_GENRES']} CHANGE genre_sort_order genre_sort_order INT( 11 ) NOT NULL DEFAULT '0';";
	    		$db->Execute($sql);

	    		$sql = "ALTER TABLE {$const['TABLE_PRODUCT_BOOKX_IMPRINTS']} CHANGE imprint_sort_order imprint_sort_order INT( 11 ) NOT NULL DEFAULT '0';";
	    		$db->Execute($sql);

	    		$sql = "ALTER TABLE {$const['TABLE_PRODUCT_BOOKX_PRINTING']} CHANGE printing_sort_order printing_sort_order INT( 11 ) NOT NULL DEFAULT '0';";
	    		$db->Execute($sql);

	    		$sql = "ALTER TABLE {$const['TABLE_PRODUCT_BOOKX_PUBLISHERS']} CHANGE publisher_sort_order publisher_sort_order INT( 11 ) NOT NULL DEFAULT '0';";
	    		$db->Execute($sql);

	    		$sql = "ALTER TABLE {$const['TABLE_PRODUCT_BOOKX_SERIES']} CHANGE series_sort_order series_sort_order INT( 11 ) NOT NULL DEFAULT '0';";
	    		$db->Execute($sql);



	    		//** fix typos in set function which prevented editing of layout values
	    		$sql = "UPDATE {$const['TABLE_PRODUCT_TYPE_LAYOUT']} SET set_function = \"" . "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED')))," .
	    				'" WHERE set_function = "' . "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED'), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED')))" . '";' ;
	    		$db->Execute($sql);

	    		$sql = "UPDATE {$const['TABLE_PRODUCT_TYPE_LAYOUT']} SET set_function = \"" . "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')))," .
	    				'" WHERE set_function = "' . "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED'))" . '";' ;
	    		$db->Execute($sql);

	    		$sql = "UPDATE {$const['TABLE_PRODUCT_TYPE_LAYOUT']} SET set_function = \"" . "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')))," .
	    				'" WHERE set_function = "' . "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED'))" . '";' ;
	    		$db->Execute($sql);

	    		$sql = "UPDATE {$const['TABLE_PRODUCT_TYPE_LAYOUT']} SET set_function = \"" . "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ORDER_BY_NAME')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ORDER_BY_SORT_ORDER')))," .
	    				'" WHERE set_function = "' . "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ORDER_BY_NAME')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ORDER_BY_SORT_ORDER')))" . '";' ;
	    		$db->Execute($sql);

	    		$sql = "UPDATE {$const['TABLE_PRODUCT_TYPE_LAYOUT']} SET set_function = \"" . "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ORDER_BY_NAME')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ORDER_BY_SORT_ORDER')), array('id'=>'3', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ORDER_BY_TYPE_NAME')), array('id'=>'4', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ORDER_BY_TYPE_SORT_ORDER')))," .
	    				'" WHERE set_function = "' . "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ORDER_BY_NAME')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ORDER_BY_SORT_ORDER'), array('id'=>'3', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ORDER_BY_TYPE_NAME')), array('id'=>'4', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ORDER_BY_TYPE_SORT_ORDER')))" . '";' ;
	    		$db->Execute($sql);

	    		// update texts for BOOKX_AUTHOR_IMAGE_MAX_HEIGHT & WIDTH
	    		$sql = "UPDATE {$const['TABLE_CONFIGURATION']}
	    		SET configuration_title = 'Product Info Page Author Photo: Maximum Height',
	    		configuration_description = '<br />Maximum height in pixels for author photo on product info page. A value of 0 will show all images at their actual size without any scaling.'
	    		WHERE configuration_key = 'BOOKX_AUTHOR_IMAGE_MAX_HEIGHT';" ;
	    		$db->Execute($sql);

	    		$sql = "UPDATE {$const['TABLE_CONFIGURATION']}
	    		SET configuration_title = 'Product Info Page Author Photo: Maximum Width',
	    		configuration_description = '<br />Maximum width in pixels for author photo on product info page. A value of 0 will show all images at their actual size without any scaling.',
	    		configuration_key = 'BOOKX_AUTHOR_IMAGE_MAX_WIDTH'
	    		WHERE configuration_key = 'BOOKX_AUTHOR_IMAGE_WIDTH';" ;
	    		$db->Execute($sql);

	    		if ($german_installed && defined(TABLE_CONFIGURATION_LANGUAGE)) {
		    		$sql = "UPDATE {$const['TABLE_CONFIGURATION_LANGUAGE']}
		    		SET configuration_title = 'Autorenbild auf Seite Artikeldetails: Maximale Höhe',
		    		configuration_description = '<br />Maximale Höhe (in Pixeln) des Autorenbilds auf der Seite Artikeldetails. Bei einem Wert von 0 werden Bilder in voller Größe angezeigt und nicht skaliert.'
		    		WHERE configuration_key = 'BOOKX_AUTHOR_IMAGE_MAX_HEIGHT' AND configuration_language_id = '43';" ;
		    		$db->Execute($sql);

		    		$sql = "UPDATE {$const['TABLE_CONFIGURATION_LANGUAGE']}
		    		SET configuration_title = 'Autorenbild auf Seite Artikeldetails: Maximale Breite',
		    		configuration_description = '<br />Maximale Breite (in Pixeln) des Autorenbilds auf der Seite Artikeldetails. Bei einem Wert von 0 werden Bilder in voller Größe angezeigt und nicht skaliert.',
		    		configuration_key = 'BOOKX_AUTHOR_IMAGE_MAX_WIDTH'
		    		WHERE configuration_key = 'BOOKX_AUTHOR_IMAGE_WIDTH' AND configuration_language_id = '43';" ;
		    		$db->Execute($sql);
	    		}

	    		// update SHOW_PRODUCT_BOOKX_LISTING_MODEL
	    		$sql = "UPDATE {$const['TABLE_PRODUCT_TYPE_LAYOUT']}
	    		SET configuration_title = 'Product Listing: Show Model Number',
	    		configuration_description = 'Display Model Number on Product Listing.'
	    		WHERE configuration_key = 'SHOW_PRODUCT_BOOKX_LISTING_MODEL';" ;
	    		$db->Execute($sql);

	    		if ($german_installed && defined(TABLE_PRODUCT_TYPE_LAYOUT_LANGUAGE)) {
		    		$sql = "UPDATE {$const['TABLE_PRODUCT_TYPE_LAYOUT_LANGUAGE']}
		    		SET configuration_title = 'Artikelliste: Artikelnummer anzeigen',
		    		configuration_description = 'Artikelnummer in der Artikelliste anzeigen.'
		    		WHERE configuration_key = 'SHOW_PRODUCT_BOOKX_LISTING_MODEL' AND languages_id = '43';" ;
		    		$db->Execute($sql);
	    		}

	    		// update SHOW_PRODUCT_BOOKX_INFO_MODEL
	    		$sql = "UPDATE {$const['TABLE_PRODUCT_TYPE_LAYOUT']}
	    		SET configuration_title = 'Product Detail: Show Model Number',
	    		configuration_description = 'Display Model Number on Product Info.'
	    		WHERE configuration_key = 'SHOW_PRODUCT_BOOKX_INFO_MODEL';" ;
	    		$db->Execute($sql);

	    		if ($german_installed && defined(TABLE_PRODUCT_TYPE_LAYOUT_LANGUAGE)) {
		    		$sql = "UPDATE {$const['TABLE_PRODUCT_TYPE_LAYOUT_LANGUAGE']}
		    		SET configuration_title = 'Artikeldetails: Artikelnummer anzeigen',
		    		configuration_description = 'Soll auf der Produktseite die Artikelnummer anzeigt werden <br/> '
		    		WHERE configuration_key = 'SHOW_PRODUCT_BOOKX_INFO_MODEL' AND languages_id = '43';" ;
		    		$db->Execute($sql);
	    		}


	    		//*** these will be removed until it is clear what they are used for
	    		$sql = "DELETE FROM {$const['TABLE_PRODUCT_TYPE_LAYOUT']}
	    				WHERE configuration_key IN ('SHOW_PRODUCT_BOOKX_INFO_METATAGS_TITLE_STATUS'
	    									,'SHOW_PRODUCT_BOOKX_INFO_METATAGS_TITLE_TAGLINE_STATUS'
	    									,'SHOW_PRODUCT_BOOKX_INFO_METATAGS_PRODUCTS_TITLE_STATUS'
	    									,'SHOW_PRODUCT_BOOKX_INFO_METATAGS_PRODUCTS_SUBTITLE_STATUS'
	    									,'SHOW_PRODUCT_BOOKX_INFO_METATAGS_MODEL_STATUS'
	    									,'SHOW_PRODUCT_BOOKX_INFO_METATAGS_PRICE_STATUS'
	    									,'SHOW_PRODUCT_BOOKX_INFO_METATAGS_TITLE_AUTHOR_STATUS'
	    									,'SHOW_PRODUCT_BOOKX_INFO_METATAGS_TITLE_PUBLISHER_STATUS'
	    									,'SHOW_PRODUCT_BOOKX_INFO_METATAGS_TITLE_GENRE_STATUS'
	    									,'SHOW_PRODUCT_BOOKX_INFO_METATAGS_TITLE_SERIES_STATUS'
	    									,'SHOW_PRODUCT_BOOKX_INFO_METATAGS_TITLE_IMPRINT_STATUS'
	    									)";
	    		$db->Execute($sql);
                /**
                 * PHP Warning: Use of undefined constant TABLE_PRODUCT_TYPE_LAYOUT_LANGUAGE
                 * I guess this is related to the german zencart version. Adding var $german_installed to skip 
                 * this warning.
                 */
                if ($german_installed && defined(TABLE_PRODUCT_TYPE_LAYOUT_LANGUAGE)) {
    	    		$sql = "DELETE FROM {$const['TABLE_PRODUCT_TYPE_LAYOUT_LANGUAGE']}
    				    		WHERE configuration_key IN ('SHOW_PRODUCT_BOOKX_INFO_METATAGS_TITLE_STATUS'
    				    		,'SHOW_PRODUCT_BOOKX_INFO_METATAGS_TITLE_TAGLINE_STATUS'
    				    		,'SHOW_PRODUCT_BOOKX_INFO_METATAGS_PRODUCTS_TITLE_STATUS'
    				    		,'SHOW_PRODUCT_BOOKX_INFO_METATAGS_PRODUCTS_SUBTITLE_STATUS'
    				    		,'SHOW_PRODUCT_BOOKX_INFO_METATAGS_MODEL_STATUS'
    				    		,'SHOW_PRODUCT_BOOKX_INFO_METATAGS_PRICE_STATUS'
    				    		,'SHOW_PRODUCT_BOOKX_INFO_METATAGS_TITLE_AUTHOR_STATUS'
    				    		,'SHOW_PRODUCT_BOOKX_INFO_METATAGS_TITLE_PUBLISHER_STATUS'
    				    		,'SHOW_PRODUCT_BOOKX_INFO_METATAGS_TITLE_GENRE_STATUS'
    				    		,'SHOW_PRODUCT_BOOKX_INFO_METATAGS_TITLE_SERIES_STATUS'
    				    		,'SHOW_PRODUCT_BOOKX_INFO_METATAGS_TITLE_IMPRINT_STATUS'
    				    		)";
    	    		$db->Execute($sql);
                }

	    		/* not needed until use of meta tag options is clear

	    		 // update SHOW_PRODUCT_BOOKX_INFO_METATAGS_PRODUCTS_NAME_STATUS
	    		$sql = "UPDATE {$const['TABLE_PRODUCT_TYPE_LAYOUT']}
	 	    			SET configuration_title = 'Show Metatags Title Default - Product Title',
	 	    			    configuration_description = 'Display Book Title in Meta Tags Title.'
	    				WHERE configuration_key = 'SHOW_PRODUCT_BOOKX_INFO_METATAGS_PRODUCTS_NAME_STATUS';" ;
	    		$db->Execute($sql);

	    		$sql = "UPDATE {$const['TABLE_PRODUCT_TYPE_LAYOUT_LANGUAGE']}
	    		SET configuration_title = 'Metatag Titel Standardeinstellung - Buchtitel',
	    		configuration_description = 'Soll der Buchtitel im Metatag Titel angezeigt werden<br/>'
	    		WHERE configuration_key = 'SHOW_PRODUCT_BOOKX_INFO_METATAGS_PRODUCTS_NAME_STATUS' AND languages_id = '43';" ;
	    		$db->Execute($sql);

	    		// update SHOW_PRODUCT_BOOKX_INFO_METATAGS_TITLE_STATUS
	    		$sql = "UPDATE {$const['TABLE_PRODUCT_TYPE_LAYOUT']}
	    		SET configuration_title = 'Show Metatags Title Default - Website Title',
	    		configuration_description = 'Display Website Title in Meta Tags Title.'
	    		WHERE configuration_key = 'SHOW_PRODUCT_BOOKX_INFO_METATAGS_TITLE_STATUS';" ;
	    		$db->Execute($sql);

	    		$sql = "UPDATE {$const['TABLE_PRODUCT_TYPE_LAYOUT_LANGUAGE']}
	    		SET configuration_title = 'Metatag Titel Standardeinstellung - Webseitentitel',
	    		configuration_description = 'Soll der Titel der Webseite im Metatag Titel angezeigt werden<br/>'
	    		WHERE configuration_key = 'SHOW_PRODUCT_BOOKX_INFO_METATAGS_TITLE_STATUS' AND languages_id = '43';" ;
	    		$db->Execute($sql);

	    		// update SHOW_PRODUCT_BOOKX_INFO_METATAGS_TITLE_TAGLINE_STATUS
	    		$sql = "UPDATE {$const['TABLE_PRODUCT_TYPE_LAYOUT']}
	    		SET configuration_title = 'Show Metatags Title Default - Website Tagline'
	    		configuration_description = 'Display Website Tagline in Meta Tags Title.',
	    		sort_order = '505'
	    		WHERE configuration_key = 'SHOW_PRODUCT_BOOKX_INFO_METATAGS_TITLE_TAGLINE_STATUS';" ;
	    		$db->Execute($sql);

	    		$sql = "UPDATE {$const['TABLE_PRODUCT_TYPE_LAYOUT_LANGUAGE']}
	    		SET configuration_title = 'Metatag Titel Standardeinstellung - Webseiten-Tagline',
    			configuration_description = 'Soll die Tagline der Webseite im Metatag Titel angezeigt werden<br/>'
    			WHERE configuration_key = 'SHOW_PRODUCT_BOOKX_INFO_METATAGS_TITLE_TAGLINE_STATUS' AND languages_id = '43';" ;
    			$db->Execute($sql);*/

	    		// insert
    			if (!empty($bookx_pType_id)) {
		    		$sql = <<<EOT
		    		REPLACE INTO {$const['TABLE_PRODUCT_TYPE_LAYOUT']} (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, last_modified, date_added, use_function, set_function )
	                              VALUES
	                        ('Product Listing: Show ISBN', 'SHOW_PRODUCT_BOOKX_LISTING_ISBN', '1', 'Display ISBN on Product Listing.', {$bookx_pType_id}, '15', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),")
                            ,('Product Detail: Show ISBN', 'SHOW_PRODUCT_BOOKX_INFO_ISBN', '1', 'Display ISBN on Product Info.', {$bookx_pType_id}, '277', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),")
                            ,('Product Listing: Show only Authors with Type Sort Oder below', 'SHOW_PRODUCT_BOOKX_LISTING_AUTHORS_WITH_TYPE_BELOW_SORT_ORDER', '1000', 'Display only Authors on Product Listing which are of an Author Type with a Sort Order smaller than this value. Example: Default value of "1000" means that authors of type e.g. "Illustrator" will not be shown on product listing, if the author type "Illustrator" has a sort order of "1000" or greater. This way multiple authors can be given more or less "importance". If you enter a value "0" then this setting is ignored.', {$bookx_pType_id}, '122', now(), now(), NULL, NULL)
		                    ,('Filter Sidebox - Link to Author List', 'SHOW_PRODUCT_BOOKX_LINK_AUTHOR_LIST', '1', 'Show a link to display the list of all Authors in the Bookx Filter Sidebox.', {$bookx_pType_id}, '695', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED'))),")
		                    ,('Filter Sidebox - Link to Series List', 'SHOW_PRODUCT_BOOKX_LINK_SERIES_LIST', '1', 'Show a link to display the list of all Seies in the Bookx Filter Sidebox.', {$bookx_pType_id}, '696', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED'))),")

	                       #ignored ,('Show Metatags Title Default - Product Title', 'SHOW_PRODUCT_BOOKX_INFO_METATAGS_PRODUCTS_TITLE_STATUS', '1', 'Display Product Title in Meta Tags Title.', {$bookx_pType_id}, '510', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),")
	                       #ignored ,('Show Metatags Title Default - Product Subtitle', 'SHOW_PRODUCT_BOOKX_INFO_METATAGS_PRODUCTS_SUBTITLE_STATUS', '1', 'Display Product Subtitle in Meta Tags Title.', {$bookx_pType_id}, '515', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED_ONLY_IF_NOT_EMPTY')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED'))),")
							;
EOT;
		    		$db->Execute($sql);

		    		if ($german_installed) {
			    		$sql = <<<EOT
	                        REPLACE INTO {$const['TABLE_PRODUCT_TYPE_LAYOUT_LANGUAGE']} (configuration_title, configuration_key, languages_id, configuration_description, last_modified, date_added)
	                              VALUES
	                        	  ('Artikelliste: ISBN anzeigen', 'SHOW_PRODUCT_BOOKX_LISTING_ISBN', 43, 'ISBN in der Artikelliste anzeigen.', now(), now())
	                        	  ,('Artikeldetails: ISBN anzeigen', 'SHOW_PRODUCT_BOOKX_INFO_ISBN', 43, 'Soll auf der Produktseite die ISBN anzeigt werden <br/> ', now(), now())
	                        	  ,('Artikelliste: nur Autoren anzeigen mit Typ Sortierung unter', 'SHOW_PRODUCT_BOOKX_LISTING_AUTHORS_WITH_TYPE_BELOW_SORT_ORDER', 43, 'Nur solche Autoren auf der Artikelliste anzeigen, die einen Autorentyp haben, dessen Sortierung kleiner ist als der hier eingestellte Wert. Beispiel: Grundeinstellung "1000" bedeutet, dass ein Autor mit dem Typ "Illustrator" in der Artikelliste nicht angezeigt wird, wenn der Autorentyp eine Sortierung von "1000" oder mehr hat. So kann man die Autorentypen priorisieren, und z.B. nur einen "Hauptautor" in der Artikelliste anzeigen lassen. Bei einem Wert von "0" wird diese Einstellung ignoriert.', now(), now())
	                        	  ,('Filter Sidebox - Link zur Autorenliste', 'SHOW_PRODUCT_BOOKX_LINK_AUTHOR_LIST', '43', 'Link zur Liste aller Autoren in der Bookx Filter Sidebox anzeigen.', now(), now())
			                      ,('Filter Sidebox - Link zur Serienliste', 'SHOW_PRODUCT_BOOKX_LINK_SERIES_LIST', '43', 'Link zur Liste aller Serien in der Bookx Filter Sidebox anzeigen.', now(), now())

	                             #ignored ,('Metatag Titel Standardeinstellung - Buchtitel', 'SHOW_PRODUCT_BOOKX_INFO_METATAGS_PRODUCTS_TITLE_STATUS', 43, 'Soll der Buchtitel im Metatag Titel angezeigt werden<br/>', now(), now())
	                        	 #ignored ,('Metatag Titel Standardeinstellung - Untertitel', 'SHOW_PRODUCT_BOOKX_INFO_METATAGS_PRODUCTS_SUBTITLE_STATUS', 43, 'Soll der Untertitel im Metatag Titel angezeigt werden<br/>', now(), now())
	                        	  ;
EOT;
		    			$db->Execute($sql);
		    		}

    			} else {
    				$messageStack->add(BOOKX_MS_PRODUCT_TYPE_BOOKX_MISSING, 'error');
    			}

    			if (!empty($cf_gid)) {
		    		$sql = <<<EOT
				    	REPLACE INTO {$const['TABLE_CONFIGURATION']} (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function)
				    		VALUES
				    		 ('Author Listing: Max. number of Authors per page', 'MAX_DISPLAY_BOOKX_AUTHOR_LISTING', '30', '<br />Maximum number of listed authors on author listing. No value defaults to 20 rows per page.', {$cf_gid}, 145, NOW(), NOW(), NULL, NULL)
				    		,('Author Listing Photo: Maximum Height', 'BOOKX_AUTHOR_LISTING_IMAGE_MAX_HEIGHT', '90', '<br />Maximum height in pixels for author photo on author listing. A value of 0 will show all images at their actual size without any scaling.', {$cf_gid}, 150, NOW(), NOW(), NULL, NULL)
						    ,('Author Listing Photo: Maximum Width', 'BOOKX_AUTHOR_LISTING_IMAGE_MAX_WIDTH', '100', '<br />Maximum width in pixels for author photo on author listing. A value of 0 will show all images at their actual size without any scaling.', {$cf_gid}, 160, NOW(), NOW(), NULL, NULL)
						    ,('Series Listing: Max. number of Series per page', 'MAX_DISPLAY_BOOKX_SERIES_LISTING', '30', '<br />Maximum number of listed series on series listing. No value defaults to 20 rows per page.', {$cf_gid}, 170, NOW(), NOW(), NULL, NULL)
		    				,('Series Listing Image: Maximum Height', 'BOOKX_SERIES_LISTING_IMAGE_MAX_HEIGHT', '90', '<br />Maximum height in pixels for series image on series listing. A value of 0 will show all images at their actual size without any scaling.', {$cf_gid}, 180, NOW(), NOW(), NULL, NULL)
		    				,('Series Listing Image: Maximum Width', 'BOOKX_SERIES_LISTING_IMAGE_MAX_WIDTH', '100', '<br />Maximum width in pixels for series image on series listing. A value of 0 will show all images at their actual size without any scaling.', {$cf_gid}, 190, NOW(), NOW(), NULL, NULL)
						    ;
EOT;
		    		$db->Execute($sql);

		    		if ($german_installed) {
			    		$sql = <<<EOT
			    			INSERT INTO {$const['TABLE_CONFIGURATION_LANGUAGE']} (configuration_title, configuration_key, configuration_description, configuration_language_id)
			    				VALUES
								 ('Autorenliste: Anzahl Autoren pro Seite', 'MAX_DISPLAY_BOOKX_AUTHOR_LISTING', '<br />Maximale Anzahl von Autoren pro Seite in der Autorenliste. Bei "0" oder keinem Wert, werden 20 Autoren pro Seite angezeigt.', 43)
			    				,('Autorenbild in Autorenliste: Maximale Höhe', 'BOOKX_AUTHOR_LISTING_IMAGE_MAX_HEIGHT', '<br />Maximale Höhe (in Pixeln) des Autorenbilds in der Liste aller Autoren. Bei einem Wert von 0 werden Bilder in voller Größe angezeigt und nicht skaliert.', 43)
							    ,('Autorenbild in Autorenliste: Maximale Breite', 'BOOKX_AUTHOR_LISTING_IMAGE_MAX_WIDTH', '<br />Maximale Breite (in Pixeln) des Autorenbilds in der Liste aller Autoren. Bei einem Wert von 0 werden Bilder in voller Größe angezeigt und nicht skaliert.', 43)
							    ,('Serienliste: Anzahl Serien pro Seite', 'MAX_DISPLAY_BOOKX_SERIES_LISTING', '<br />Maximale Anzahl von Serien pro Seite in der Serienliste. Bei "0" oder keinem Wert, werden 20 Serien pro Seite angezeigt.', 43)
			    				,('Serienbild in Serienliste: Maximale Höhe', 'BOOKX_SERIES_LISTING_IMAGE_MAX_HEIGHT', '<br />Maximale Höhe (in Pixeln) des Serienbilds in der Liste aller Serien. Bei einem Wert von 0 werden Bilder in voller Größe angezeigt und nicht skaliert.', 43)
			    				,('Serienbild in Serienliste: Maximale Breite', 'BOOKX_SERIES_LISTING_IMAGE_MAX_WIDTH', '<br />Maximale Breite (in Pixeln) des Serienbilds in der Liste aller Serien. Bei einem Wert von 0 werden Bilder in voller Größe angezeigt und nicht skaliert.', 43)
			    				;
EOT;
		    			$db->Execute($sql);
		    		}
    			}