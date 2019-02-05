<?php
/**
 * This file is part of the ZenCart add-on Book X which
 * introduces a new product type for books to the Zen Cart
 * shop system. Tested for compatibility on ZC v. 1.56a
 *
 * @package initSystem
 * @author  mesnitu
 * @copyright Portions Copyright 2003-2011 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.gnu.org/licenses/gpl.txt GNU General Public License V2.0
 *
 * @version BookX V 1.0.0
 * @version $Id: [ZC INSTALLATION]/includes/classes/observers/class.bookx_observers_canonical.php 2019-01-30 mesnitu $
 */

class bookxCanonicalObserver extends base
{

    var $count_filters;
    var $useCeon = false;

    public function __construct()
    {
        global $zco_notifier;

        $zco_notifier->attach($this, array('NOTIFY_INIT_CANONICAL_PARAM_WHITELIST'
            , 'NOTIFY_INIT_CANONICAL_DEFAULT'));
    }

    function update(&$callingClass, $notifier, $paramsArray)
    {
        switch ($notifier) {
            case 'NOTIFY_INIT_CANONICAL_PARAM_WHITELIST':
                $this->updateNotifyInitCanonicalParamWhitelist($callingClass, $notifier, $paramsArray);
                break;
            case 'NOTIFY_INIT_CANONICAL_DEFAULT':
                $this->updateNotifyInitCanonicalDefault($callingClass, $notifier, $paramsArray);
                break;
        }
    }

    //$zco_notifier->notify ('NOTIFY_INIT_CANONICAL_PARAM_WHITELIST', $current_page, $excludeParams, $keepableParams, $includeCPath);

    function updateNotifyInitCanonicalParamWhitelist(&$callingClass, $notifier, $paramsArray)
    {
        global $keepableParams;

        $keepableParams[] = 'bookx_publisher_id';
        $keepableParams[] = 'bookx_genre_id';
        $keepableParams[] = 'bookx_author_id';
        $keepableParams[] = 'bookx_author_type_id';
        $keepableParams[] = 'bookx_author_type_id';
        $keepableParams[] = 'bookx_imprint_id';
        $keepableParams[] = 'bookx_series_id';
        $keepableParams[] = 'bookx_family_id';
    }

    //$zco_notifier->notify ('NOTIFY_INIT_CANONICAL_DEFAULT', $current_page, $excludeParams, $canonicalLink);
    function updateNotifyInitCanonicalDefault(&$callingClass, $notifier, $paramsArray)
    {
        global $db, $keepableParams, $current_page, $excludeParams, $canonicalLink;
        if ($current_page == FILENAME_DEFAULT && isset($_GET['typefilter']) && 'bookx' == $_GET['typefilter']) {
            $index = true;
        }
        if (class_exists('CeonURIMappingAdmin') && BOOKX_USES_CEON_URI_MODULE == 1) {
            $this->useCeon = true;
            $lang_id = $_SESSION['languages_id'];
            $lang_code = $_SESSION['languages_code'];
            require_once(DIR_FS_CATALOG . DIR_WS_CLASSES . 'class.CeonURIMappingAdmin.php');
            $handleUri = new CeonURIMappingAdmin();
            $this->count_filters();
        }

        if ($index) {

            switch (true) {

                case ($this->useCeon == false && (
                (isset($_GET['bookx_author_id']) && $_GET['bookx_publisher_id'] != '' ) ||
                (isset($_GET['bookx_author_type_id']) && $_GET['bookx_author_type_id'] != '' ) ||
                (isset($_GET['bookx_imprint_id']) && $_GET['bookx_imprint_id'] != '' ) ||
                (isset($_GET['bookx_series_id']) && $_GET['bookx_series_id'] != '' ) ||
                (isset($_GET['bookx_genre_id']) && $_GET['bookx_genre_id'] != '' ) ||
                (isset($_GET['bookx_publisher_id']) && $_GET['bookx_publisher_id'] != '' ))):
                unset($excludeParams[array_search('typefilter', $excludeParams)]);
                 $canonicalLink = zen_href_link($current_page, zen_get_all_get_params($excludeParams), 'NONSSL', false);
                    break;

                case ($this->useCeon == true && zen_not_null($_GET['bookx_publisher_id'])):
                    /**
                     * @todo waiting for ceon update fo zc156 to add more code to test it
                     */
                    unset($excludeParams[array_search('typefilter', $excludeParams)]);

                    if ($this->count_filters == 1) {

                        $sql = "SELECT publisher_name FROM " . TABLE_PRODUCT_BOOKX_PUBLISHERS . " WHERE bookx_publisher_id = :bookx_publisher_id:";
                        $sql = $db->bindVars($sql, ':bookx_publisher_id:', $_GET['bookx_publisher_id'], 'integer');
                        $result = $db->Execute($sql);
                        
                        $clean_url = $handleUri->_convertStringForURI($result->fields['publisher_name'], $lang_code);
                        if ($result->RecordCount() > 0) {
                            $handleUri->addURIMapping(PUBLISHER_URI_PREFIX . $clean_url, $lang_id, $current_page, 'typefilter=bookx&bookx_publisher_id=' . (int) $_GET['bookx_publisher_id'], '', '', 301, true);
                        }

                        $canonicalLink = zen_href_link($current_page, zen_get_all_get_params($clean_url), 'NONSSL', false);
                        break;
                    } else {
                        $this->publisher_name = $result->fields['publisher_name'];
                    }
            }
        }
    }

    private function count_filters()
    {
        //@TODO temp fucntion to get and display metatags title, etc...
        // This should be reviewd from the bottom up. Similar function is used in side boxes filter ( v096).
        if (isset($_GET['typefilter']) && 'bookx' == $_GET['typefilter']) {
            $active_filters = bookx_get_active_filter_ids();

            $this->count_filters = (int) $active_filters['active_filter_count'];
            array_pop($active_filters);
            if (!$this->active_filters && empty($this->active_filters)) {
                foreach ($active_filters as $key => $value) {
                    if (zen_not_null($value) && $value !== '') {
                        $this->active_filters[]['bookx_' . $key] = $value;
                    }
                }
            }
        }
    }

}
