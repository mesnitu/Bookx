<?php

if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

// insert
if (!empty($cf_gid)) {
    $sql = <<<EOT
					REPLACE INTO {$const['TABLE_CONFIGURATION']} (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function)
					    		VALUES
						    	 ('New Products: Base on Publication Date', 'BOOKX_NEW_PRODUCTS_LOOK_BACK_NUMBER_OF_DAYS', '90', 'Base "New Products" List on publication date. Enter number of days to look back in time for published books. A value of "0" turns off this option. Example: Default value of "90" will list all books with publication dates within the last 90 days. Note: If you use partial publication dates in the format "2013-04-00" to only indicate the month of publication, these dates are considered to be at the <u>beginning</u> of the month.<br /><br />', {$cf_gid}, 200, NOW(), NOW(), NULL, NULL)
							    ,('Upcoming Products: Base on Publication Date', 'BOOKX_UPCOMING_PRODUCTS_LOOK_AHEAD_NUMBER_OF_DAYS', '180', 'Base "Upcoming Products" List on publication date instead of date available. Enter number of days to look ahead in time for books to be published. A value of "0" turns off this option. Example: Default value of "180" will list all books with publication dates within the next 180 days. Note: If you use partial publication dates in the format "2013-04-00" to only indicate the month of publication, these dates are considered to be at the <u>beginning</u> of the month.<br /><br />', {$cf_gid}, 210, NOW(), NOW(), NULL, NULL)
		    					,('Author Listing: Show only authors of stocked books', 'BOOKX_AUTHOR_LISTING_SHOW_ONLY_STOCKED', '1', '<br />Show only those authors in the author listing, which have a book in the shop that is in stock (i.e. product is visible <u>and</u> stock is greater than "0"). If this setting is turned on, a checkbox is displayed on top of the author listing, which allows users to override this setting. If this is not desired, set CSS "display: none" to hide it.', {$cf_gid}, 165,  now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED'))),")
		    					,('Author Listing: Sort authors by', 'BOOKX_AUTHOR_LISTING_ORDER_BY', '1', '<br />Sort authors in listing by:', {$cf_gid}, 167,  now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ORDER_BY_NAME')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ORDER_BY_SORT_ORDER'))),")
		    					,('Series Listing: Show only series with stocked books', 'BOOKX_SERIES_LISTING_SHOW_ONLY_STOCKED', '1', '<br />Show only those series in the series listing, which have a book in the shop that is in stock (i.e. product is visible <u>and</u> stock is greater than "0"). If this setting is turned on, a checkbox is displayed on top of the series listing, which allows users to override this setting. If this is not desired, set CSS "display: none" to hide it.', {$cf_gid}, 195,  now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED'))),")
		    					,('Series Listing: Sort series by', 'BOOKX_SERIES_LISTING_ORDER_BY', '1', '<br />Sort series in series listing by:', {$cf_gid}, 197,  now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ORDER_BY_NAME')), array('id'=>'2', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ORDER_BY_SORT_ORDER'))),")
		    					;
EOT;
    $db->Execute($sql);

    if ($german_installed) {
        $sql = <<<EOT
	 					REPLACE INTO {$const['TABLE_CONFIGURATION_LANGUAGE']} (configuration_title, configuration_key, configuration_description, configuration_language_id)
						    		VALUES
						    		('Neue Artikel: Auswahl durch Erscheinungsdatum', 'BOOKX_NEW_PRODUCTS_LOOK_BACK_NUMBER_OF_DAYS', '"Neue Artikel" werden nach Erscheinungsdatum ausgewählt. Ein Wert von "0" schaltet diese Option aus. Beispiel: Der Standardwert von "90" listet alle Bücher auf, deren Erscheinungsdatum innerhalb den letzten 90 Tage liegt. Achtung: Wenn unvollständige Erscheinungsdaten im Format "2013-04-00" verwendet werden, um nur den Erscheinungsmonat anzugeben, dann wird dieses Erscheinugsdatum am <u>Anfang</u> des Monats verortet.', 43)
						    		,('Artikelankündigungen: Auswahl durch Erscheinungsdatum', 'BOOKX_UPCOMING_PRODUCTS_LOOK_AHEAD_NUMBER_OF_DAYS', '<br />"Artikelankündigungen" werden nach Erscheinungsdatum ausgewählt. Ein Wert von "0" schaltet diese Option aus. Beispiel: Der Standardwert von "180" listet alle Bücher auf, deren Erscheinungsdatum innerhalb der nächsten 180 Tage liegt. Achtung: Wenn unvollständige Erscheinungsdaten im Format "2013-04-00" verwendet werden, um nur den Erscheinungsmonat anzugeben, dann wird dieses Erscheinugsdatum am <u>Anfang</u> des Monats verortet.', 43)
			    					,('Autorenliste: Nur lieferbare Bücher zeigen', 'BOOKX_AUTHOR_LISTING_SHOW_ONLY_STOCKED', '<br />In der Autorenliste nur Autoren anzeigen, für die ein lieferbares Buch im Shop existiert (d.h. der Artikel ist sichtbar <u>und</u> Bestand ist größer "0"). Wenn eingeschaltet, wird über der Autenliste eine Checkbox angezeigt, die es Shopbesuchern erlaubt auch Autoren ohne lieferbare Bücher anzuzeigen. Ist diese Checkbox nicht gewünscht, kann diese via CSS "display: none" versteckt werden.', 43)
			    					,('Autorenliste: Autoren sortieren nach:', 'BOOKX_AUTHOR_LISTING_ORDER_BY', '<br />Autoren in der Autorenliste werden sortiert nach:', 43)
	 								,('Serienliste: Nur lieferbare Bücher zeigen', 'BOOKX_SERIES_LISTING_SHOW_ONLY_STOCKED', '<br />In der Liste aller Serien nur Serien anzeigen, für die ein lieferbares Buch im Shop existiert (d.h. der Artikel ist sichtbar <u>und</u> Bestand ist größer "0"). Wenn eingeschaltet, wird über der Autenliste eine Checkbox angezeigt, die es Shopbesuchern erlaubt auch Autoren ohne lieferbare Bücher anzuzeigen. Ist diese Checkbox nicht gewünscht, kann diese via CSS "display: none" versteckt werden.', 43)
			    					,('Serienliste: Serien sortieren nach:', 'BOOKX_SERIES_LISTING_ORDER_BY', '<br />Serien in der Serienliste werden sortiert nach:', 43)
	 							;
EOT;
        $db->Execute($sql);
    }
}
else {
    $messageStack->add(BOOKX_MS_PRODUCT_TYPE_BOOKX_MISSING, 'error');
}