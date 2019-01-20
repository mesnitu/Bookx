<?php

if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}


if (!empty($bookx_pType_id)) {
    $sql = <<<EOT
			    		REPLACE INTO {$const['TABLE_PRODUCT_TYPE_LAYOUT']} (configuration_title, configuration_key, configuration_value, configuration_description, product_type_id, sort_order, last_modified, date_added, use_function, set_function )
	                              VALUES
                             ('Product Listing: Group Products by availability', 'GROUP_PRODUCT_BOOKX_LISTING_BY_AVAILABILITY', '1', 'Group products in any product listing according to availability. Order: <br />1) Upcoming products <br />2) New products <br />3) Published / available products 4) Out of print <br /><br />Criteria for "new" and "upcoming" books are set in Admin -> Configuration -> BookX Configuration.', {$bookx_pType_id}, '150', now(), now(), NULL, "zen_cfg_select_drop_down(array(array('id'=>'1', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_ENABLED')), array('id'=>'0', 'text'=>constant('BOOKX_LAYOUT_SETTINGS_DISABLED'))),")
                             ;
EOT;
    $db->Execute($sql);

    if ($german_installed) {
        $sql = <<<EOT
	                        REPLACE INTO {$const['TABLE_PRODUCT_TYPE_LAYOUT_LANGUAGE']} (configuration_title, configuration_key, languages_id, configuration_description, last_modified, date_added)
	                              VALUES
	                        	('Artikelliste: Bücher nach Lieferbarkeit gruppieren', 'GROUP_PRODUCT_BOOKX_LISTING_BY_AVAILABILITY', 43, 'Gruppiere Bücher in Artikelliste nach Lieferbarkeit: Reihenfolge: <br />1) Noch nicht lieferbare Bücher <br />2) Neue Bücher <br />3) Lieferbare Bücher <br />4) Vergriffene Bücher  <br /><br />Kriterien für "Neue" und "noch nicht lieferbare" Bücher werden einegstellt unter Admin -> Konfiguration -> BookX.', now(), now())

	                        	;
EOT;
        $db->Execute($sql);
    }
}
else {
    $messageStack->add(BOOKX_MS_PRODUCT_TYPE_BOOKX_MISSING, 'error');
}
