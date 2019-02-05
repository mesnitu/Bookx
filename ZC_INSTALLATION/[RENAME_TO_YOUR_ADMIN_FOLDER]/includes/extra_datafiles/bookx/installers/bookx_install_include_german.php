<?php
/**
 * German Language Configuration install. 
 * Can't test this, since it appears to be a diferent zencart ( TABLE_PRODUCT_TYPE_LAYOUT_LANGUAGE ) 
 */

if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}
if ($german_lng_layout == true) {
    
    $sql = <<<EOT
    REPLACE INTO {$const['TABLE_PRODUCT_TYPE_LAYOUT_LANGUAGE']} (configuration_title, configuration_key, languages_id, configuration_description, last_modified, date_added) VALUES
        # settings for product type bookx only
        ('Artikelliste: Artikelnummer anzeigen', 'SHOW_PRODUCT_BOOKX_LISTING_MODEL', 43, 'Artikelnummer in der Artikelliste anzeigen.', now(), now()),
        ('Artikelliste: ISBN anzeigen', 'SHOW_PRODUCT_BOOKX_LISTING_ISBN', 43, 'ISBN in der Artikelliste anzeigen.', now(), now()),
        ('Artikelliste: Untertitel anzeigen', 'SHOW_PRODUCT_BOOKX_LISTING_SUBTITLE', 43, 'Untertitel in der Artikelliste anzeigen.', now(), now()),
        ('Artikelliste: Seitenzahl anzeigen', 'SHOW_PRODUCT_BOOKX_LISTING_PAGES', 43, 'Seitenzahl in der Artikelliste anzeigen.', now(), now()),
        ('Artikelliste: Druck anzeigen', 'SHOW_PRODUCT_BOOKX_LISTING_PRINTING', 43, 'Druck in der Artikelliste anzeigen.', now(), now()),
        ('Artikelliste: Bindung anzeigen', 'SHOW_PRODUCT_BOOKX_LISTING_BINDING', 43, 'Druck in der Artikelliste anzeigen.', now(), now()),
        ('Artikelliste: Abmessungen anzeigen', 'SHOW_PRODUCT_BOOKX_LISTING_SIZE', 43, 'Abmessungen in der Artikelliste anzeigen.', now(), now()),
        ('Artikelliste: Bandnummer anzeigen', 'SHOW_PRODUCT_BOOKX_LISTING_VOLUME', 43, 'Bandnummer in der Artikelliste anzeigen.', now(), now()),
        ('Artikelliste: Erscheinungsdatum anzeigen', 'SHOW_PRODUCT_BOOKX_LISTING_PUBLISH_DATE', 43, 'Erscheinungsdatum in der Artikelliste anzeigen.', now(), now()),
        ('Artikelliste: Verlag anzeigen', 'SHOW_PRODUCT_BOOKX_LISTING_PUBLISHER', 43, 'Verlag in der Artikelliste anzeigen.', now(), now()),
        ('Artikelliste: Verlag als Link anzeigen', 'SHOW_PRODUCT_BOOKX_LISTING_PUBLISHER_AS_LINK', 43, 'Verlag in der Artikelliste als Link anzeigen. Klick auf den Link listet alle Bücher für diesen Verlag.', now(), now()),
        ('Artikelliste: Verlag Bild/Logo anzeigen', 'SHOW_PRODUCT_BOOKX_LISTING_PUBLISHER_IMAGE', 43, 'Verlag Bild/Logo in der Artikelliste anzeigen. Falls kein Bild vorhanden, wir der Name angezeigt.', now(), now()),
        ('Artikelliste: Verlag URL anzeigen', 'SHOW_PRODUCT_BOOKX_LISTING_PUBLISHER_URL', 43, 'Verlag URL in der Artikelliste anzeigen.', now(), now()),
        ('Artikelliste: Verlag Beschreibung anzeigen', 'SHOW_PRODUCT_BOOKX_LISTING_PUBLISHER_DESCRIPTION', 43, 'Verlag Beschreibung in der Artikelliste anzeigen.', now(), now()),
        ('Artikelliste: Unterlabel anzeigen', 'SHOW_PRODUCT_BOOKX_LISTING_IMPRINT', 43, 'Unterlabel in der Artikelliste anzeigen.', now(), now()),
        ('Artikelliste: Unterlabel als Link anzeigen', 'SHOW_PRODUCT_BOOKX_LISTING_IMPRINT_AS_LINK', 43, 'Unterlabel in der Artikelliste als Link anzeigen. Klick auf den Link listet alle Bücher für dieses Unterlabel.', now(), now()),
        ('Artikelliste: Unterlabel Bild anzeigen', 'SHOW_PRODUCT_BOOKX_LISTING_IMPRINT_IMAGE', 43, 'Unterlabel-Bild in der Artikelliste anzeigen. Falls kein Bild vorhanden, wir der Name angezeigt.', now(), now()),
        ('Artikelliste: Unterlabel Beschreibung anzeigen', 'SHOW_PRODUCT_BOOKX_LISTING_IMPRINT_DESCRIPTION', 43, 'Unterlabel Beschreibung in der Artikelliste anzeigen.', now(), now()),
        ('Artikelliste: Serie anzeigen', 'SHOW_PRODUCT_BOOKX_LISTING_SERIES', 43, 'Serie in der Artikelliste anzeigen.', now(), now()),
        ('Artikelliste: Serie als Link anzeigen', 'SHOW_PRODUCT_BOOKX_LISTING_SERIES_AS_LINK', 43, 'Serie in der Artikelliste als Link anzeigen. Klick auf den Link listet alle Bücher für diese Serie.', now(), now()),
        ('Artikelliste: Serienbild anzeigen', 'SHOW_PRODUCT_BOOKX_LISTING_SERIES_IMAGE', 43, 'Serienbild in der Artikelliste anzeigen. Falls kein Bild vorhanden, wir der Name angezeigt.', now(), now()),
        ('Artikelliste: Serienbeschreibung anzeigen', 'SHOW_PRODUCT_BOOKX_LISTING_SERIES_DESCRIPTION', 43, 'Serienbeschreibung in der Artikelliste anzeigen.', now(), now()),
        ('Artikelliste: Autoren anzeigen', 'SHOW_PRODUCT_BOOKX_LISTING_AUTHORS', 43, 'Autoren in der Artikelliste anzeigen.', now(), now()),
        ('Artikelliste: nur Autoren anzeigen mit Typ Sortierung unter', 'SHOW_PRODUCT_BOOKX_LISTING_AUTHORS_WITH_TYPE_BELOW_SORT_ORDER', 43, 'Nur solche Autoren auf der Artikelliste anzeigen, die einen Autorentyp haben, dessen Sortierung kleiner ist als der hier eingestellte Wert. Beispiel: Grundeinstellung "1000" bedeutet, dass ein Autor mit dem Typ "Illustrator" in der Artikelliste nicht angezeigt wird, wenn der Autorentyp eine Sortierung von "1000" oder mehr hat. So kann man die Autorentypen priorisieren, und z.B. nur einen "Hauptautor" in der Artikelliste anzeigen lassen. Bei einem Wert von "0" wird diese Einstellung ignoriert.', now(), now()),
        ('Artikelliste: Autoren als Link anzeigen', 'SHOW_PRODUCT_BOOKX_LISTING_AUTHORS_AS_LINK', 43, 'Autor in der Artikelliste als Link anzeigen. Klick auf den Link listet alle Bücher für diesen Autor.', now(), now()),
        ('Artikelliste: Autorenbild anzeigen', 'SHOW_PRODUCT_BOOKX_LISTING_AUTHORS_IMAGE', 43, 'Autorenbild in der Artikelliste anzeigen. Falls kein Bild vorhanden, wir der Name angezeigt.', now(), now()),
        ('Artikelliste: Autoren URL anzeigen', 'SHOW_PRODUCT_BOOKX_LISTING_AUTHORS_URL', 43, 'URLs der Autoren in der Artikelliste anzeigen.', now(), now()),
        ('Artikelliste: Autorenbeschreibung anzeigen', 'SHOW_PRODUCT_BOOKX_LISTING_AUTHORS_DESCRIPTION', 43, 'Autorenbeschreibung in der Artikelliste anzeigen.', now(), now()),
        ('Artikelliste: Autorentyp anzeigen', 'SHOW_PRODUCT_BOOKX_LISTING_AUTHOR_TYPE', 43, 'Autorentyp in der Artikelliste anzeigen.', now(), now()),
        ('Artikelliste: Autorentyp Bild anzeigen', 'SHOW_PRODUCT_BOOKX_LISTING_AUTHOR_TYPE_IMAGE', 43, 'Autorentyp Bild in der Artikelliste anzeigen.', now(), now()),
        ('Artikelliste: Genres anzeigen', 'SHOW_PRODUCT_BOOKX_LISTING_GENRES', 43, 'Genres in der Artikelliste anzeigen.', now(), now()),
        ('Artikelliste: Genres als Link anzeigen', 'SHOW_PRODUCT_BOOKX_LISTING_GENRES_AS_LINK', 43, 'Genres in der Artikelliste als Link anzeigen. Klick auf den Link listet alle Bücher für dieses Genre.', now(), now()),
        ('Artikelliste: Genre-Bild anzeigen', 'SHOW_PRODUCT_BOOKX_LISTING_GENRE_IMAGE', 43, 'Genre-Bild in der Artikelliste anzeigen. Falls kein Bild vorhanden, wir der Name angezeigt.', now(), now()),
        ('Artikelliste: Zustand anzeigen', 'SHOW_PRODUCT_BOOKX_LISTING_CONDITION', 43, 'Zustand in der Artikelliste anzeigen.', now(), now()),
        ('Artikelliste: Bücher nach Lieferbarkeit gruppieren', 'GROUP_PRODUCT_BOOKX_LISTING_BY_AVAILABILITY', 43, 'Gruppiere Bücher in Artikelliste nach Lieferbarkeit: Reihenfolge: <br />1) Noch nicht lieferbare Bücher <br />2) Neue Bücher <br />3) Lieferbare Bücher <br />4) Vergriffene Bücher <br /><br />Kriterien für "Neue" und "noch nicht lieferbare" Bücher werden einegstellt unter Admin -> Konfiguration -> BookX.', now(), now()),

        ('Artikeldetails: Untertitel anzeigen', 'SHOW_PRODUCT_BOOKX_INFO_SUBTITLE', 43, 'Untertitel auf der Artikeldetailseite anzeigen.', now(), now()),
        ('Artikeldetails: Seitenzahl anzeigen', 'SHOW_PRODUCT_BOOKX_INFO_PAGES', 43, 'Seitenzahl auf der Artikeldetailseite anzeigen.', now(), now()),
        ('Artikeldetails: Druck anzeigen', 'SHOW_PRODUCT_BOOKX_INFO_PRINTING', 43, 'Druck auf der Artikeldetailseite anzeigen.', now(), now()),
        ('Artikeldetails: Bindung anzeigen', 'SHOW_PRODUCT_BOOKX_INFO_BINDING', 43, 'Druck auf der Artikeldetailseite anzeigen.', now(), now()),
        ('Artikeldetails: Abmessungen anzeigen', 'SHOW_PRODUCT_BOOKX_INFO_SIZE', 43, 'Abmessungen auf der Artikeldetailseite anzeigen.', now(), now()),
        ('Artikeldetails: Reihennummer anzeigen', 'SHOW_PRODUCT_BOOKX_INFO_VOLUME', 43, 'Reihennummer auf der Artikeldetailseite anzeigen.', now(), now()),
        ('Artikeldetails: Erscheinungsdatum anzeigen', 'SHOW_PRODUCT_BOOKX_INFO_PUBLISH_DATE', 43, 'Erscheinungsdatum auf der Artikeldetailseite anzeigen.', now(), now()),
        ('Artikeldetails: Verlag anzeigen', 'SHOW_PRODUCT_BOOKX_INFO_PUBLISHER', 43, 'Verlag auf der Artikeldetailseite anzeigen.', now(), now()),
        ('Artikeldetails: Verlag also Link anzeigen', 'SHOW_PRODUCT_BOOKX_INFO_PUBLISHER_AS_LINK', 43, 'Verlag auf der Artikeldetailseite als Link anzeigen. Klick auf den Link listet alle Bücher für diesen Verlag.', now(), now()),
        ('Artikeldetails: Verlag Bild/Logo anzeigen', 'SHOW_PRODUCT_BOOKX_INFO_PUBLISHER_IMAGE', 43, 'Verlag auf der Artikeldetailseite anzeigen. Falls kein Bild vorhanden, wir der Name angezeigt.', now(), now()),
        ('Artikeldetails: Verlag URL anzeigen', 'SHOW_PRODUCT_BOOKX_INFO_PUBLISHER_URL', 43, 'Verlag auf der Artikeldetailseite anzeigen.', now(), now()),
        ('Artikeldetails: Verlag Beschreibung anzeigen', 'SHOW_PRODUCT_BOOKX_INFO_PUBLISHER_DESCRIPTION', 43, 'Verlag auf der Artikeldetailseite anzeigen.', now(), now()),
        ('Artikeldetails: Unterlabel anzeigen', 'SHOW_PRODUCT_BOOKX_INFO_IMPRINT', 43, 'Unterlabel auf der Artikeldetailseite anzeigen.', now(), now()),
        ('Artikeldetails: Unterlabel als Link anzeigen', 'SHOW_PRODUCT_BOOKX_INFO_IMPRINT_AS_LINK', 43, 'Unterlabel auf der Artikeldetailseite als Link anzeigen. Klick auf den Link listet alle Bücher für dieses Unterlabel.', now(), now()),
        ('Artikeldetails: Unterlabel-Bild anzeigen', 'SHOW_PRODUCT_BOOKX_INFO_IMPRINT_IMAGE', 43, 'Unterlabel-Bild auf der Artikeldetailseite anzeigen. Falls kein Bild vorhanden, wir der Name angezeigt.', now(), now()),
        ('Artikeldetails: Unterlabel Beschreibung anzeigen', 'SHOW_PRODUCT_BOOKX_INFO_IMPRINT_DESCRIPTION', 43, 'Unterlabel Beschreibung auf der Artikeldetailseite anzeigen.', now(), now()),
        ('Artikeldetails: Serie anzeigen', 'SHOW_PRODUCT_BOOKX_INFO_SERIES', 43, 'Serie auf der Artikeldetailseite anzeigen.', now(), now()),
        ('Artikeldetails: Serie als Link anzeigen', 'SHOW_PRODUCT_BOOKX_INFO_SERIES_AS_LINK', 43, 'Serie auf der Artikeldetailseite als Link anzeigen. Klick auf den Link listet alle Bücher für diese Serie.', now(), now()),
        ('Artikeldetails: Serienbild anzeigen', 'SHOW_PRODUCT_BOOKX_INFO_SERIES_IMAGE', 43, 'Serienbild auf der Artikeldetailseite anzeigen. Falls kein Bild vorhanden, wir der Name angezeigt.', now(), now()),
        ('Artikeldetails: Serienbeschreibung anzeigen', 'SHOW_PRODUCT_BOOKX_INFO_SERIES_DESCRIPTION', 43, 'Serienbeschreibung auf der Artikeldetailseite anzeigen.', now(), now()),
        ('Artikeldetails: Autoren anzeigen', 'SHOW_PRODUCT_BOOKX_INFO_AUTHORS', 43, 'Autoren auf der Artikeldetailseite anzeigen.', now(), now()),
        ('Artikeldetails: Autoren als Link anzeigen', 'SHOW_PRODUCT_BOOKX_INFO_AUTHORS_AS_LINK', 43, 'Autoren auf der Artikeldetailseite als Link anzeigen. Klick auf den Link listet alle Bücher für diesen Autor.', now(), now()),
        ('Artikeldetails: Autorenbild anzeigen', 'SHOW_PRODUCT_BOOKX_INFO_AUTHORS_IMAGE', 43, 'Autorenbild auf der Artikeldetailseite anzeigen. Falls kein Bild vorhanden, wir der Name angezeigt.', now(), now()),
        ('Artikeldetails: Autoren URL anzeigen', 'SHOW_PRODUCT_BOOKX_INFO_AUTHORS_URL', 43, 'URLs der Autoren auf der Artikeldetailseite anzeigen.', now(), now()),
        ('Artikeldetails: Autorenbeschreibung anzeigen', 'SHOW_PRODUCT_BOOKX_INFO_AUTHORS_DESCRIPTION', 43, 'Autorenbeschreibung auf der Artikeldetailseite anzeigen.', now(), now()),
        ('Artikeldetails: Autorentyp anzeigen', 'SHOW_PRODUCT_BOOKX_INFO_AUTHOR_TYPE', 43, 'Autorentyp auf der Artikeldetailseite anzeigen.', now(), now()),
        ('Artikeldetails: Autorentyp Bild anzeigen', 'SHOW_PRODUCT_BOOKX_INFO_AUTHOR_TYPE_IMAGE', 43, 'Autorentyp Bild auf der Artikeldetailseite anzeigen.', now(), now()),
        ('Artikeldetails: Autoren sortieren nach', 'ORDER_PRODUCT_BOOKX_INFO_AUTHORS', 43, 'Autoren auf der Artikeldetailseite sortieren nach: ', now(), now()),
        ('Artikeldetails: Weitere Bücher der Autoren anzeigen', 'SHOW_PRODUCT_BOOKX_INFO_AUTHORS_RELATED_PRODUCTS', 43, 'Weitere Bücher des selben Autors auf der Artikeldetailseite anzeigen.', now(), now()),
        ('Artikeldetails: Genres anzeigen', 'SHOW_PRODUCT_BOOKX_INFO_GENRES', 43, 'Genres auf der Artikeldetailseite anzeigen.', now(), now()),
        ('Artikeldetails: Genres als Link anzeigen', 'SHOW_PRODUCT_BOOKX_INFO_GENRES_AS_LINK', 43, 'Genres auf der Artikeldetailseite als Link anzeigen. Klick auf den Link listet alle Bücher für dieses Genre.', now(), now()),
        ('Artikeldetails: Genre-Bilder anzeigen', 'SHOW_PRODUCT_BOOKX_INFO_GENRE_IMAGES', 43, 'Genres auf der Artikeldetailseite anzeigen. Falls kein Bild vorhanden, wir der Name angezeigt.', now(), now()),
        ('Artikeldetails: Genres sortieren nach', 'ORDER_PRODUCT_BOOKX_INFO_GENRES', 43, 'Genres auf der Artikeldetailseite sortieren nach: ', now(), now()),
        ('Artikeldetails: Zustand anzeigen', 'SHOW_PRODUCT_BOOKX_INFO_CONDITION', 43, 'Zustand auf der Artikeldetailseite anzeigen.', now(), now()),

        # settings for all products
        ('Artikeldetails: Artikelnummer anzeigen', 'SHOW_PRODUCT_BOOKX_INFO_MODEL', 43, 'Soll auf der Produktseite die Artikelnummer anzeigt werden <br/> ', now(), now()),
        ('Artikeldetails: ISBN anzeigen', 'SHOW_PRODUCT_BOOKX_INFO_ISBN', 43, 'Soll auf der Produktseite die ISBN anzeigt werden <br/> ', now(), now()),
        ('Artikeldetails: Gewicht anzeigen', 'SHOW_PRODUCT_BOOKX_INFO_WEIGHT', 43, 'Soll das Gewicht auf der Artikeldetailseite angezeigt werden<br/> ', now(), now()),
        ('Artikeldetails: Attribut Gewicht anzeigen', 'SHOW_PRODUCT_BOOKX_INFO_WEIGHT_ATTRIBUTES', 43, 'Soll das Attribut Gewicht auf der Artikeldetailseite angezeigt werden?<br/> ', now(), now()),
        ('Artikeldetails: Hersteller anzeigen', 'SHOW_PRODUCT_BOOKX_INFO_MANUFACTURER', 43, 'Soll der Hersteller auf der Artikeldetailseite angezeigt werden?<br/> ', now(), now()),
        ('Artikeldetails: Menge im Warenkorb anzeigen', 'SHOW_PRODUCT_BOOKX_INFO_IN_CART_QTY', 43, 'Soll die bereits im Warenkorb vorhandene Menge diese Artikels auf der Artikeldetailseite angezeigt werden?<br/> ', now(), now()),
        ('Artikeldetails: Lagermenge anzeigen', 'SHOW_PRODUCT_BOOKX_INFO_QUANTITY', 43, 'Soll die aktuelle Lagermenge auf der Artikeldetailseite angezeigt werden<br/> ', now(), now()),
        ('Artikeldetails: Anzahl der Artikelbewertungen anzeigen', 'SHOW_PRODUCT_BOOKX_INFO_REVIEWS_COUNT', 43, 'Soll die Anzahl der Artikelbewertungen auf der Artikeldetailseite angezeigt werden?<br/> ', now(), now()),
        ('Artikeldetails: Button "Artikel bewerten" anzeigen', 'SHOW_PRODUCT_BOOKX_INFO_REVIEWS', 43, 'Soll der Button "Artikel bewerten" auf der Artikeldetailseite angezeigt werden?<br/> ', now(), now()),
        ('Artikeldetails: "Verfügbar am" anzeigen', 'SHOW_PRODUCT_BOOKX_INFO_DATE_AVAILABLE', 43, 'Soll auf der Artikeldetailseite "Verfügbar am" angezeigt werden?<br/> ', now(), now()),
        ('Artikeldetails: "Hinzugefügt am" anzeigen', 'SHOW_PRODUCT_BOOKX_INFO_DATE_ADDED', 43, 'Soll auf der Artikeldetailseite "Hinzugefügt am" angezeigt werden?<br/> ', now(), now()),
        ('Artikeldetails: Artikel URL anzeigen', 'SHOW_PRODUCT_BOOKX_INFO_URL', 43, 'Soll die Artikel URL auf der Artikeldetailseite angezeigt werden? ', now(), now()),
        ('Artikeldetails: Preis "ab.." anzeigen', 'SHOW_PRODUCT_BOOKX_INFO_STARTING_AT', 43, 'Soll bei Büchern mit Attributen die Preisanzeige mit "ab" beginnen?<br/> ', now(), now()),
        ('Artikeldetails: Button "Einem Freund empfehlen" anzeigen', 'SHOW_PRODUCT_BOOKX_INFO_TELL_A_FRIEND', 43, 'Soll der Button "Einem Freund empfehlen" auf der Artikeldetailseite angezeigt werden?<br /><br />HINWEIS: Das Deaktivieren dieser Einstellung hat keine Auswirkungen auf die entsprechende Sidebox. Das Deaktivieren der Sidebox deaktiviert nicht diesen Button<br />', now(), now()),
        ('Artikeldetails: Zusätzliche Artikelbilder anzeigen', 'SHOW_PRODUCT_BOOKX_INFO_ADDITIONAL_IMAGES', 43, 'Sollen auf der Artikeldetailseite zusätzliche Artikelbilder angezeigt werden?<br/> ', now(), now()),
        ('Artikeldetails: Bild "Versandkostenfreie Lieferung" anzeigen', 'SHOW_PRODUCT_BOOKX_INFO_ALWAYS_FREE_SHIPPING_IMAGE_SWITCH', 43, 'Soll das Bild bzw. der Text für "Versandkostenfreie Lieferung" im Shop angezeigt werden?', now(), now()),

        # settings for admin
        ('Artikelpreis Steuerklasse - Standardeinstellung', 'DEFAULT_PRODUCT_BOOKX_TAX_CLASS_ID', 43, 'Welche Steuerklasse soll jeder neu angelegte Artikel haben<br/>Bitte geben Sie die ID der Steuerklasse ein.', now(), now()),
        ('Artikel ist virtuell - Standardeinstellung', 'DEFAULT_PRODUCT_BOOKX_PRODUCTS_VIRTUAL', 43, 'Soll jeder neu angelegte Artikel ein virtueller sein?', now(), now()),
        ('Artikel "immer versandkostenfrei" - Standardeinstellung', 'DEFAULT_PRODUCT_BOOKX_PRODUCTS_IS_ALWAYS_FREE_SHIPPING', 43, 'Welche Einstellung soll beim Anlegen eines neuen Artikels standardmäßig aktiviert werden?<br />JA, Immer versandkostenfrei AN<br />NEIN, Immer versandkostenfrei AUS<br />Spezial, Artikel/Download benötigt Versand', now(), now()),

        #settings for image and meta tags
        # ('Metatag Titel Standardeinstellung - Webseitentitel', 'SHOW_PRODUCT_BOOKX_INFO_METATAGS_TITLE_STATUS', 43, 'Soll der Titel der Webseite im Metatag Titel angezeigt werden<br/>', now(), now()),
        # ('Metatag Titel Standardeinstellung - Webseiten-Tagline', 'SHOW_PRODUCT_BOOKX_INFO_METATAGS_TITLE_TAGLINE_STATUS', 43, 'Soll die Tagline der Webseite im Metatag Titel angezeigt werden<br/>', now(), now()),

        # ('Metatag Titel Standardeinstellung - Buchtitel', 'SHOW_PRODUCT_BOOKX_INFO_METATAGS_PRODUCTS_TITLE_STATUS', 43, 'Soll der Buchtitel im Metatag Titel angezeigt werden<br/>', now(), now()),
        # ('Metatag Titel Standardeinstellung - Untertitel', 'SHOW_PRODUCT_BOOKX_INFO_METATAGS_PRODUCTS_SUBTITLE_STATUS', 43, 'Soll der Untertitel im Metatag Titel angezeigt werden<br/>', now(), now()),
        # ('Metatag Titel Standardeinstellung - ISBN', 'SHOW_PRODUCT_BOOKX_INFO_METATAGS_MODEL_STATUS', 43, 'Soll die ISBN im Metatag Titel angezeigt werden<br/>', now(), now()),
        #  ('Metatag Titel Standardeinstellung - Artikelpreis', 'SHOW_PRODUCT_BOOKX_INFO_METATAGS_PRICE_STATUS', 43, 'Soll der Artikelpreis im Metatag Titel angezeigt werden<br/>', now(), now()),
        # ('Metatag Titel Standardeinstellung - Artikel Autor', 'SHOW_PRODUCT_BOOKX_INFO_METATAGS_TITLE_AUTHOR_STATUS', 43, 'Soll der Buchautor im Metatag Titel angezeigt werden<br/>', now(), now()),
        # ('Metatag Titel Standardeinstellung - Artikel Verlag', 'SHOW_PRODUCT_BOOKX_INFO_METATAGS_TITLE_PUBLISHER_STATUS', 43, 'Soll der Buchverlag im Metatag Titel angezeigt werden<br/>', now(), now()),
        # ('Metatag Titel Standardeinstellung - Artikel Genre', 'SHOW_PRODUCT_BOOKX_INFO_METATAGS_TITLE_GENRE_STATUS', 43, 'Soll das Buchgenre im Metatag Titel angezeigt werden<br/>', now(), now()),
        # ('Metatag Titel Standardeinstellung - Artikel Reihe', 'SHOW_PRODUCT_BOOKX_INFO_METATAGS_TITLE_SERIES_STATUS', 43, 'Soll die Buchreihe Tagline im Metatag Titel angezeigt werden<br/>', now(), now()),
        # ('Metatag Titel Standardeinstellung - Artikel Label', 'SHOW_PRODUCT_BOOKX_INFO_METATAGS_TITLE_IMPRINT_STATUS', 43, 'Soll das Buchlabel Tagline im Metatag Titel angezeigt werden<br/>', now(), now()),

        #settings show bookx filters in sidebox
        ('Filter Sidebox - Filter Autor', 'SHOW_PRODUCT_BOOKX_FILTER_AUTHOR', '43', 'Filter für Autor in der Bookx Filter Sidebox anzeigen.', now(), now()),
        ('Filter Sidebox - Filter Autorentyp', 'SHOW_PRODUCT_BOOKX_FILTER_AUTHOR_TYPE', '43', 'Filter für Autorentyp in der Bookx Filter Sidebox anzeigen.', now(), now()),
        ('Filter Sidebox - Filter Verlag', 'SHOW_PRODUCT_BOOKX_FILTER_PUBLISHER', '43', 'Filter für Verlag in der Bookx Filter Sidebox anzeigen.', now(), now()),
        ('Filter Sidebox - Filter Unterlabel', 'SHOW_PRODUCT_BOOKX_FILTER_IMPRINT', '43', 'Filter für Unterlabel in der Bookx Filter Sidebox anzeigen.', now(), now()),
        ('Filter Sidebox - Filter Reihe', 'SHOW_PRODUCT_BOOKX_FILTER_SERIES', '43', 'Filter für Reihe in der Bookx Filter Sidebox anzeigen.', now(), now()),
        ('Filter Sidebox - Filter Genre', 'SHOW_PRODUCT_BOOKX_FILTER_GENRE', '43', 'Filter für Genre in der Bookx Filter Sidebox anzeigen.', now(), now()),
        ('Filter Sidebox - Link zur Autorenliste', 'SHOW_PRODUCT_BOOKX_LINK_AUTHOR_LIST', '43', 'Link zur Liste aller Autoren in der Bookx Filter Sidebox anzeigen.', now(), now()),
        ('Filter Sidebox - Link zur Unterlabelliste', 'SHOW_PRODUCT_BOOKX_LINK_IMPRINT_LIST', '43', 'Link zur Liste aller Unterlabelliste in der Bookx Filter Sidebox anzeigen.', now(), now()),
        ('Filter Sidebox - Link zur Verlagsliste', 'SHOW_PRODUCT_BOOKX_LINK_PUBLISHER_LIST', '43', 'Link zur Liste aller Verlage in der Bookx Filter Sidebox anzeigen.', now(), now()),
        ('Filter Sidebox - Link zur Genreliste', 'SHOW_PRODUCT_BOOKX_LINK_GENRES_LIST', '43', 'Link zur Liste aller Genres in der Bookx Filter Sidebox anzeigen.', now(), now()),
        ('Filter Sidebox - Link zur Serienliste', 'SHOW_PRODUCT_BOOKX_LINK_SERIES_LIST', '43', 'Link zur Liste aller Serien in der Bookx Filter Sidebox anzeigen.', now(), now()),
        ('Filter Sidebox - Mehrere Filter zulassen', 'ALLOW_PRODUCT_BOOKX_FILTER_MULTIPLE', '43', 'Erlaubt es, mehrere Filter in der Bookx Filter Sidebox zu setzen. Anderfalls ersetzt eine Filterauswahl einen ggf. vorher gesetzten Filter. AUSNAHME: Die Kombination der Filter "Autor" und "Autorentyp" ist immer erlaubt.', now(), now()),

    #settings extra info on top of search results
    ('Filter Resultate - Autor: Beschreibung anzeigen', 'SHOW_PRODUCT_BOOKX_FILTER_EXTRA_INFO_AUTHOR', 43, 'Beschreibung für Autor oben auf der Resultate-Seite anzeigen, wenn der Filter „Autor” aktiviert ist?', now(), now()),
    ('Filter Resultate - Verlag: Beschreibung anzeigen', 'SHOW_PRODUCT_BOOKX_FILTER_EXTRA_INFO_PUBLISHER', 43, 'Beschreibung für Verlag oben auf der Resultate-Seite anzeigen, wenn der Filter „Autor” aktiviert ist?', now(), now()),
    ('Filter Resultate - Unterlabel: Beschreibung anzeigen', 'SHOW_PRODUCT_BOOKX_FILTER_EXTRA_INFO_IMPRINT', 43, 'Beschreibung für Unterlabel oben auf der Resultate-Seite anzeigen, wenn der Filter „Autor” aktiviert ist?', now(), now()),
    ('Filter Resultate - Reihe: Beschreibung anzeigen', 'SHOW_PRODUCT_BOOKX_FILTER_EXTRA_INFO_SERIES', 43, 'Beschreibung für Reihe oben auf der Resultate-Seite anzeigen, wenn der Filter „Autor” aktiviert ist?', now(), now()),
    ('Filter Resultate - Genre: Beschreibung anzeigen', 'SHOW_PRODUCT_BOOKX_FILTER_EXTRA_INFO_GENRE', 43, 'Beschreibung für Genre oben auf der Resultate-Seite anzeigen, wenn der Filter „Autor” aktiviert ist?', now(), now())

                        		;
EOT;
$db->Execute($sql);
    
}



///********   Add values for German admin  ******/////////
if ( $german_install_admin == true) {
    
    $sql = <<<EOT
        REPLACE INTO {$const['TABLE_CONFIGURATION_GROUP']} (configuration_group_id, language_id, configuration_group_title, configuration_group_description, sort_order, visible ) VALUES ({$cf_gid}, 43, 'BookX', 'BookX Einstellungen', '1', '1');
EOT;
	    	$db->Execute($sql);

	$sql = <<<EOT
        REPLACE INTO {$const['TABLE_CONFIGURATION_LANGUAGE']} (configuration_title, configuration_key, configuration_description, configuration_language_id) VALUES
        ('Filter Liste: Maximale Breite', 'BOOKX_MAX_DISPLAY_FILTER_DROPDOWN_LEN', '<br />Maximale Breite in Buchstaben für eine Optionen-Liste in der BookX Filter Sidebox.<br /><br /><b>Voreinstellung: 30', 43)
        ,('Filter Liste: Listenfeld Größe/Stil', 'BOOKX_MAX_SIZE_FILTER_LIST', '<br />Anzahl der Einträge, die im Listenfeld der Book X Filter Sidebox angezeigt werden sollen. Bei einer Eingabe von 0 oder 1 wird eine Dropdown Liste angezeigt.', 43)
        ,('BookX Piktogramme: Maximale Höhe', 'BOOKX_ICONS_MAX_HEIGHT', '<br />Maximale Höhe (in Pixeln) der Piktogramme/Bilder für Genre, Sublabel, Verlag, Serie und Autoren-<u>Typ</u>. Bei einer Eingabe von 0 werden Bilder in voller Größe angezeigt und nicht skaliert.', 43)
        ,('BookX Piktogramme: Maximale Breite', 'BOOKX_ICONS_MAX_WIDTH', '<br />Maximale Breite (in Pixeln) der Piktogramme/Bilder für Genre, Sublabel, Verlag, Serie und Autoren-<u>Typ</u>. Bei einer Eingabe von 0 werden Bilder in voller Größe angezeigt und nicht skaliert.', 43)
        ,('Autorenbild auf Seite Artikeldetails: Maximale Höhe', 'BOOKX_AUTHOR_IMAGE_MAX_HEIGHT', '<br />Maximale Höhe (in Pixeln) des Autorenbilds auf der Seite Artikeldetails. Bei einem Wert von 0 werden Bilder in voller Größe angezeigt und nicht skaliert.', 43)
        ,('Autorenbild auf Seite Artikeldetails: Maximale Breite', 'BOOKX_AUTHOR_IMAGE_MAX_WIDTH', '<br />Maximale Breite (in Pixeln) des Autorenbilds auf der Seite Artikeldetails. Bei einem Wert von 0 werden Bilder in voller Größe angezeigt und nicht skaliert.', 43)
        ,('Autorenliste: Anzahl Autoren pro Seite', 'MAX_DISPLAY_BOOKX_AUTHOR_LISTING', '<br />Maximale Anzahl von Autoren pro Seite in der Autorenliste. Bei "0" oder keinem Wert, werden 20 Autoren pro Seite angezeigt.', 43)
        ,('Autorenbild in Autorenliste: Maximale Höhe', 'BOOKX_AUTHOR_LISTING_IMAGE_MAX_HEIGHT', '<br />Maximale Höhe (in Pixeln) des Autorenbilds in der Liste aller Autoren. Bei einem Wert von 0 werden Bilder in voller Größe angezeigt und nicht skaliert.', 43)
        ,('Autorenbild in Autorenliste: Maximale Breite', 'BOOKX_AUTHOR_LISTING_IMAGE_MAX_WIDTH', '<br />Maximale Breite (in Pixeln) des Autorenbilds in der Liste aller Autoren. Bei einem Wert von 0 werden Bilder in voller Größe angezeigt und nicht skaliert.', 43)
        ,('Autorenliste: Nur lieferbare Bücher zeigen', 'BOOKX_AUTHOR_LISTING_SHOW_ONLY_STOCKED', '<br />In der Autorenliste nur Autoren anzeigen, für die ein lieferbares Buch im Shop existiert (d.h. der Artikel ist sichtbar <u>und</u> Bestand ist größer "0"). Wenn eingeschaltet, wird über der Autorenliste eine Checkbox angezeigt, die es Shopbesuchern erlaubt auch Autoren ohne lieferbare Bücher anzuzeigen. Ist diese Checkbox nicht gewünscht, kann diese via CSS "display: none" versteckt werden.', 43)
        ,('Autorenliste: Autoren sortieren nach:', 'BOOKX_AUTHOR_LISTING_ORDER_BY', '<br />Autoren in der Autorenliste werden sortiert nach:', 43)
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
        ,('Serienliste: Anzahl Serien pro Seite', 'MAX_DISPLAY_BOOKX_SERIES_LISTING', '<br />Maximale Anzahl von Serien pro Seite in der Serienliste. Bei "0" oder keinem Wert, werden 20 Serien pro Seite angezeigt.', 43)
        ,('Serienbild in Serienliste: Maximale Höhe', 'BOOKX_SERIES_LISTING_IMAGE_MAX_HEIGHT', '<br />Maximale Höhe (in Pixeln) des Serienbilds in der Liste aller Serien. Bei einem Wert von 0 wird die Höhe der Bilder nicht begrenzt.', 43)
        ,('Serienbild in Serienliste: Maximale Breite', 'BOOKX_SERIES_LISTING_IMAGE_MAX_WIDTH', '<br />Maximale Breite (in Pixeln) des Serienbilds in der Liste aller Serien. Bei einem Wert von 0 wird die Breite der Bilder nicht begrenzt.', 43)
        ,('Serienliste: Nur lieferbare Bücher zeigen', 'BOOKX_SERIES_LISTING_SHOW_ONLY_STOCKED', '<br />In der Liste aller Serien nur Serien anzeigen, für die ein lieferbares Buch im Shop existiert (d.h. der Artikel ist sichtbar <u>und</u> Bestand ist größer "0"). Wenn eingeschaltet, wird über der Autenliste eine Checkbox angezeigt, die es Shopbesuchern erlaubt auch Unterlabel ohne lieferbare Bücher anzuzeigen. Ist diese Checkbox nicht gewünscht, kann diese via CSS "display: none" versteckt werden.', 43)
        ,('Serienliste: Serien sortieren nach:', 'BOOKX_SERIES_LISTING_ORDER_BY', '<br />Serien in der Serienliste werden sortiert nach:', 43)
        ,('Genreliste: Anzahl Genres pro Seite', 'MAX_DISPLAY_BOOKX_GENRE_LISTING', '<br />Maximale Anzahl von Genres pro Seite in der Genreliste. Bei "0" oder keinem Wert, werden 20 Genres pro Seite angezeigt.', 43)
        ,('Genrelogo in Genreliste: Maximale Höhe', 'BOOKX_GENRE_LISTING_IMAGE_MAX_HEIGHT', '<br />Maximale Höhe (in Pixeln) des Genrelogos in der Liste aller Genres. Bei einem Wert von 0 werden Bilder in voller Größe angezeigt und nicht skaliert.', 43)
        ,('Genrelogo in Genreliste: Maximale Breite', 'BOOKX_GENRE_LISTING_IMAGE_MAX_WIDTH', '<br />Maximale Breite (in Pixeln) des Genrelogos in der Liste aller Genres. Bei einem Wert von 0 werden Bilder in voller Größe angezeigt und nicht skaliert.', 43)
        ,('Genreliste: Nur lieferbare Bücher zeigen', 'BOOKX_GENRE_LISTING_SHOW_ONLY_STOCKED', '<br />In der Genreliste nur Genres anzeigen, für die ein lieferbares Buch im Shop existiert (d.h. der Artikel ist sichtbar <u>und</u> Bestand ist größer "0"). Wenn eingeschaltet, wird über der Genreliste eine Checkbox angezeigt, die es Shopbesuchern erlaubt auch Genres ohne lieferbare Bücher anzuzeigen. Ist diese Checkbox nicht gewünscht, kann diese via CSS "display: none" versteckt werden.', 43)
        ,('Genreliste: Genres sortieren nach:', 'BOOKX_GENRE_LISTING_ORDER_BY', '<br />Genres in der Genreliste werden sortiert nach:', 43)
        ,('Neue Artikel: Auswahl durch Erscheinungsdatum', 'BOOKX_NEW_PRODUCTS_LOOK_BACK_NUMBER_OF_DAYS', '"Neue Artikel" werden nach Erscheinungsdatum ausgewählt. Ein Wert von "0" schaltet diese Option aus. Beispiel: Der Standardwert von "90" listet alle Bücher auf, deren Erscheinungsdatum innerhalb den letzten 90 Tage liegt. Achtung: Wenn unvollständige Erscheinungsdaten im Format "2013-04-00" verwendet werden, um nur den Erscheinungsmonat anzugeben, dann wird dieses Erscheinugsdatum am <u>Anfang</u> des Monats verortet.', 43)
        ,('Neue Artikel: Artikelbeschreibung anzeigen', 'BOOKX_NEW_PRODUCTS_SHOW_PRODUCT_DESCRIPTION_NUMOFCHARS', 'Im Modul "Neue Artikel" soll die Artikelbeschreibung (teilweise) angezeigt werden. Anzahl der Zeichen nach denen die Beschreibung abgeschnitten wird. Bei einem Wert von "0" wird die Beschreibung nicht angezeigt und bei einem Wert von "-1" wird die gesamte Beschreibung ungekürzt angezeigt.<br /><br />', 43)
        ,('Artikelankündigungen: Auswahl durch Erscheinungsdatum', 'BOOKX_UPCOMING_PRODUCTS_LOOK_AHEAD_NUMBER_OF_DAYS', '<br />"Artikelankündigungen" werden nach Erscheinungsdatum ausgewählt. Ein Wert von "0" schaltet diese Option aus. Beispiel: Der Standardwert von "180" listet alle Bücher auf, deren Erscheinungsdatum innerhalb der nächsten 180 Tage liegt. Achtung: Wenn unvollständige Erscheinungsdaten im Format "2013-04-00" verwendet werden, um nur den Erscheinungsmonat anzugeben, dann wird dieses Erscheinugsdatum am <u>Anfang</u> des Monats verortet.', 43)
        ,('Artikelankündigungen: Artikelbild anzeigen', 'BOOKX_UPCOMING_PRODUCTS_SHOW_PRODUCT_IMAGE', 'Im Modul "Artikelankündigungen" soll das Artikelbild angezeigt werden.<br /><br />', 43)
        ,('Artikelankündigungen Artikelbild: Maximale Höhe', 'BOOKX_UPCOMING_PRODUCT_IMAGE_MAX_HEIGHT', '<br />Maximale Höhe (in Pixeln) der Artikelbilder im Modul „Artikelankündigungen”. Bei einem Wert von 0 wird die Höhe der Bilder nicht begrenzt.', 43)
        ,('Artikelankündigungen Artikelbild: Maximale Breite', 'BOOKX_UPCOMING_PRODUCT_IMAGE_MAX_WIDTH', '<br />Maximale Breite (in Pixeln) der Artikelbilder in der Liste aller Serien. Bei einem Wert von 0 wird die Breite der Bilder nicht begrenzt.', 43)
        ,('Artikelankündigungen: Artikelbeschreibung anzeigen', 'BOOKX_UPCOMING_PRODUCTS_SHOW_PRODUCT_DESCRIPTION_NUMOFCHARS', 'Im Modul "Artikelankündigungen" soll die Artikelbeschreibung (teilweise) angezeigt werden. Anzahl der Zeichen nach denen die Beschreibung abgeschnitten wird. Bei einem Wert von "0" wird die Beschreibung nicht angezeigt und bei einem Wert von "-1" wird die gesamte Beschreibung ungekürzt angezeigt.<br /><br />', 43)
        ,('"Brotkrümel" Navigation: Ausfüllen durch BookX', 'BOOKX_BREAD_USE_BOOKX_NO_CATEGORIES', 'BookX soll die "Brotkrümel" Navigation ausfüllen und nicht Zen Cart mit den angelegten Produktkategorien. Dies betrifft nur die Artikeldetails-Seite für BookX-Produkte und Artikellisten die Ergebnisse eines BookX-Filters zeigen.<br /><br />', 43)
        ,('"Brotkrümel" Navigation: Verlag hinzufügen', 'BOOKX_BREAD_ADD_PUBLISHER', 'Wenn "Brotkrümel Navigation: Ausfüllen durch BookX" aktiviert ist, befüllt BookX automatisch den Krümelpfad, auch wenn der Kunde direkt zur Artikeldetailseite gelangt ist z.B. über die Suchfunktion. Bei einem Wert von "0" wird der Verlag dem Krümelpfad nicht hinzugefügt. Ein höherer Wert legt die Reihenfolge fest, in der der Verlag dem Krümelpfad hinzugefügt wird.<br /><br />', 43)
        ,('"Brotkrümel" Navigation: Label hinzufügen', 'BOOKX_BREAD_ADD_IMPRINT', 'Wenn "Brotkrümel Navigation: Ausfüllen durch BookX" aktiviert ist, befüllt BookX automatisch den Krümelpfad, auch wenn der Kunde direkt zur Artikeldetailseite gelangt ist z.B. über die Suchfunktion. Bei einem Wert von "0" wird das Label dem Krümelpfad nicht hinzugefügt. Ein höherer Wert legt die Reihenfolge fest, in der das Label dem Krümelpfad hinzugefügt wird.<br /><br />', 43)
        ,('"Brotkrümel" Navigation: Serie hinzufügen', 'BOOKX_BREAD_ADD_SERIES', 'Wenn "Brotkrümel Navigation: Ausfüllen durch BookX" aktiviert ist, befüllt BookX automatisch den Krümelpfad, auch wenn der Kunde direkt zur Artikeldetailseite gelangt ist z.B. über die Suchfunktion. Bei einem Wert von "0" wird die Serie dem Krümelpfad nicht hinzugefügt. Ein höherer Wert legt die Reihenfolge fest, in der die Serie dem Krümelpfad hinzugefügt wird.<br /><br />', 43)
        ,('"Brotkrümel" Navigation: Genre hinzufügen', 'BOOKX_BREAD_ADD_GENRE', 'Wenn "Brotkrümel Navigation: Ausfüllen durch BookX" aktiviert ist, befüllt BookX automatisch den Krümelpfad, auch wenn der Kunde direkt zur Artikeldetailseite gelangt ist z.B. über die Suchfunktion. Bei einem Wert von "0" wird das Genre dem Krümelpfad nicht hinzugefügt. Ein höherer Wert legt die Reihenfolge fest, in der das Genre dem Krümelpfad hinzugefügt wird. ACHTUNG: Wenn einem Buch mehrere Genres zugewiesen sind, kann leider nur eines im Krümelpfad gezeigt werden.<br /><br />', 43)
        ,('"Brotkrümel" Navigation: Autor hinzufügen', 'BOOKX_BREAD_ADD_AUTHOR', 'Wenn "Brotkrümel Navigation: Ausfüllen durch BookX" aktiviert ist, befüllt BookX automatisch den Krümelpfad, auch wenn der Kunde direkt zur Artikeldetailseite gelangt ist z.B. über die Suchfunktion. Bei einem Wert von "0" wird der Autor dem Krümelpfad nicht hinzugefügt. Ein höherer Wert legt die Reihenfolge fest, in der der Autor dem Krümelpfad hinzugefügt wird. ACHTUNG: Wenn einem Buch mehrere Autoren zugewiesen sind, kann leider nur einer im Krümelpfad gezeigt werden.<br /><br />', 43)	
        ,('Artikeldetails: Buttons "vorheriger / nächster Artikel" navigiert in Bookx Kategorie', 'BOOKX_NEXT_PREVIOUS_BASED_ON_FILTER', 'Wenn diese Einstellung aktiviert ist, navigieren die Buttons "Nächster Artikel", "Vorheriger Artikel" und "Zurück zur Liste" nicht mehr hin und her zwischen den Artikeln in der gleichen ZC Kategorie, sondern vor und zurück in der Ergebnisliste eines aktiven Bookx Filters.<br /><br />', 43)	
        ;

EOT;
 		   	$db->Execute($sql);
    
}

