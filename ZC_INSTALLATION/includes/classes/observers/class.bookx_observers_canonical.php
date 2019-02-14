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
    var $active_filters = array();
    var $info = array();
    var $pagination = false;
    var $bookx_page = false;
    var $use_ceon = false;

    public function __construct()
    {
        global $zco_notifier;
        
        if (isset($_GET['typefilter']) && 'bookx' == $_GET['typefilter']) {
            $this->count_filters();
        }      
        if(!empty($this->count_filters) && $_GET['page']) {
            $this->pagination = $_GET['page'];
        }
        if (isset($_GET['main_page']) && $_GET['main_page'] == 'product_bookx_info') {
            unset($this->count_filters);
            $this->bookx_page = true;
        }
       
        $zco_notifier->attach($this, array(
            'NOTIFY_INIT_CANONICAL_PARAM_WHITELIST', 
            'NOTIFY_INIT_CANONICAL_DEFAULT',
            'NOTIFY_MODULE_META_TAGS_OVERRIDE',
            'NOTIFY_MAIN_TEMPLATE_VARS_EXTRA_PRODUCT_BOOKX_INFO',
            'NOTIFY_MODULE_META_TAGS_BUILDKEYWORDS')
            );
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
            case 'NOTIFY_MODULE_META_TAGS_OVERRIDE':
                $this->updateNotifyModuleMetaTagsOverride($callingClass, $notifier, $paramsArray);
                break;
            case 'NOTIFY_MODULE_META_TAGS_BUILDKEYWORDS':
                $this->updateNotifyModuleMetaTagsBuildkeywords($callingClass, $notifier, $paramsArray);
                break;
            case 'NOTIFY_MAIN_TEMPLATE_VARS_EXTRA_PRODUCT_BOOKX_INFO':
                $this->updateNotifyProductTypeVarsProductBookxInfo($callingClass, $notifier, $paramsArray);
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
        $keepableParams[] = 'bookx_imprint_id';
        $keepableParams[] = 'bookx_series_id';
        $keepableParams[] = 'bookx_condition_id';
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

                case ($this->use_ceon == false && (
                (isset($_GET['bookx_author_id']) && $_GET['bookx_author_id'] != '' ) ||
                (isset($_GET['bookx_author_type_id']) && $_GET['bookx_author_type_id'] != '' ) ||
                (isset($_GET['bookx_imprint_id']) && $_GET['bookx_imprint_id'] != '' ) ||
                (isset($_GET['bookx_series_id']) && $_GET['bookx_series_id'] != '' ) ||
                (isset($_GET['bookx_genre_id']) && $_GET['bookx_genre_id'] != '' ) ||
                (isset($_GET['bookx_publisher_id']) && $_GET['bookx_publisher_id'] != '' )
                (isset($_GET['bookx_condition_id']) && $_GET['bookx_condition_id'] != '' )
                    )):
                    
                unset($excludeParams[array_search('typefilter', $excludeParams)]);
                $canonicalLink = zen_href_link($current_page, zen_get_all_get_params($excludeParams), 'NONSSL', false);
                
                break;

                case ($this->use_ceon == true && zen_not_null($_GET['bookx_publisher_id'])):
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
    
    
    /**
     * MetaTags
     */
    
    //$zco_notifier->notify('NOTIFY_MODULE_META_TAGS_BUILDKEYWORDS', CUSTOM_KEYWORDS, $keywords_string_metatags);
    function updateNotifyModuleMetaTagsBuildkeywords($callingClass, $notifier, $paramsArray) {
        
        global $keywords_string_metatags;
        
        $this->meta_keywords = $keywords_string_metatags;
            
    }
    
   
   /* Note: keywords are no longer relevant to search engines. I'm still placing then here for whatever reason.
    * I'll build most of the information in the description.  
    * The info gather on $this->info can be however usefull to build related openGraph tags , etc.. 
    * @see https://yoast.com/meta-keywords/
    */
     //$zco_notifier->notify('NOTIFY_MODULE_META_TAGS_OVERRIDE', $metatag_page_name, $meta_tags_over_ride, $metatags_title, $metatags_description, $metatags_keywords);
    function updateNotifyModuleMetaTagsOverride(&$callingClass, $notifier, $paramsArray)
    {
        global $db, $metatags_title, $metatags_description, $metatags_keywords;
        global $bookx_meta_info;
        
        $bookx_metatag_author_title = 'Books from Author %s at ' . STORE_NAME;
        $bookx_metatag_author_keywords = '';
        
        if ($this->count_filters == 1) {

            switch ($this->active_filters) {

                case !empty($this->active_filters['bookx_author_id']):
                    
                     global $author_meta_info;
                    /*
                     * send $author_meta_info result as global to be used in main bookx observer
                     */
                    $author_meta_info = $this->bookxSetMetaTags('author');
                    /*
                     * send $bookx_meta_info result as global to be used in others scopes such as open_graph
                     */
                    $bookx_meta_info = $this->info;

                    $metatags_title = sprintf($bookx_metatag_author_title, $this->info['author_name']);
                    $metatags_description = $metatags_title . METATAGS_DIVIDER 
                        . $this->info['author_books_names'] .METATAGS_DIVIDER 
                        . zen_truncate_paragraph(zen_clean_html($this->info['author_description']), MAX_META_TAG_DESCRIPTION_LENGTH);
                    $metatags_keywords = $this->info['author_name'] . METATAGS_DIVIDER
                       . $this->info['author_books_names'] . $this->info['author_books_genres'];
                    $bookx_metatag_author_keywords .= $this->info['author_books_genres'];   
                    break;

                default:
                    break;
            }
        }
    }

    private function bookxSetMetaTags($param)
    {
        global $db;

        if ($param == 'author') {
            $sql = "SELECT batp.bookx_author_id, ba.author_name, ba.author_image, ba.author_url, bad.author_description,   
                 GROUP_CONCAT(DISTINCT pd.products_name ORDER BY pd.products_name ASC SEPARATOR ',') AS author_books_names,
                 GROUP_CONCAT(DISTINCT pd.products_id ORDER BY pd.products_name ASC SEPARATOR ',') AS author_books_id,
                 GROUP_CONCAT(DISTINCT bgd.genre_description ORDER BY bgd.genre_description ASC SEPARATOR ',') AS author_books_genres 
                 FROM " . TABLE_PRODUCT_BOOKX_AUTHORS_TO_PRODUCTS . " batp
                 LEFT JOIN " . TABLE_PRODUCT_BOOKX_AUTHORS . " ba ON ba.bookx_author_id = batp.bookx_author_id 
                 LEFT JOIN " . TABLE_PRODUCT_BOOKX_AUTHORS_DESCRIPTION . " bad ON bad.bookx_author_id = batp.bookx_author_id AND bad.languages_id = :languages_id: 
                LEFT JOIN " . TABLE_PRODUCT_BOOKX_GENRES_TO_PRODUCTS . " bgtp ON bgtp.products_id = batp.products_id
                LEFT JOIN " . TABLE_PRODUCT_BOOKX_GENRES_DESCRIPTION . " bgd ON bgd.bookx_genre_id = bgtp.bookx_genre_id AND bgd.languages_id = :languages_id:
                 LEFT JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd ON pd.products_id = batp.products_id AND pd.language_id = :languages_id:
                 WHERE ba.bookx_author_id = :bookx_author_id:";
            $sql = $db->bindVars($sql, ':bookx_author_id:', $this->active_filters['bookx_author_id'], 'integer');
            $sql = $db->bindVars($sql, ':languages_id:', $_SESSION['languages_id'], 'integer');
            $author = $db->Execute($sql);

            foreach ($author->fields as $key => $value) {
                $this->info[$key] = $value;
            }
            return $author;
        }
        
        if ($param == 'author') {
            
        }
    }
    
    
    
    /*
     * This function will trigger in
     * C:\xampp\htdocs\vhosts\zencart\includes\modules\pages\product_bookx_info\main_template_vars_product_type.php 
     */
//    function updateNotifyProductTypeVarsProductBookxInfo($callingClass, $notifier, $paramsArray) {
//        
//        global $db, $products_id_current;
//        $products_id_current = $_GET['products_id'];
//        if ($this->bookx_page == true) {
//            global $bookx_extras;
//            pr("HERERER");
//            $sql = 'SELECT be.*, bed.products_subtitle,
//        CASE WHEN DAYOFMONTH(be.publishing_date) THEN DATE_FORMAT(be.publishing_date, "' . DATE_FORMAT_SHORT . '")
//        WHEN MONTH(be.publishing_date) THEN DATE_FORMAT(be.publishing_date, "' . DATE_FORMAT_MONTH_AND_YEAR . '")
//        ELSE YEAR(be.publishing_date)
//        END AS formatted_publishing_date,
//        CONCAT_WS("-", SUBSTRING(be.isbn,1,3), SUBSTRING(be.isbn,4,1), SUBSTRING(be.isbn,5,6), SUBSTRING(be.isbn,11,2), SUBSTRING(be.isbn,13,1))              AS isbn_display FROM ' . TABLE_PRODUCT_BOOKX_EXTRA . ' be
//        LEFT JOIN ' . TABLE_PRODUCT_BOOKX_EXTRA_DESCRIPTION . ' bed ON 
//        bed.products_id = be.products_id AND bed.languages_id = "' . (int) $_SESSION['languages_id'] . '"
//        WHERE be.products_id = "' . (int) $products_id_current . '"';
//
//// IF(DAYOFMONTH(be.publishing_date), DATE_FORMAT(be.publishing_date, "' . DATE_FORMAT_SHORT . '"), DATE_FORMAT(be.publishing_date, "' . DATE_FORMAT_MONTH_AND_YEAR . '")) AS formatted_publishing_date
//            $this->bookx_extras = $db->Execute($sql);
//            //$bookx_extras = $db->Execute($sql);
//            
//        }
//    }
    
    
    private function count_filters()
    {
        
        //if (isset($_GET['typefilter']) && 'bookx' == $_GET['typefilter']) {
            $active_filters = bookx_get_active_filter_ids();

            $this->count_filters = (int) $active_filters['active_filter_count'];
            array_pop($active_filters);
            if (!$this->active_filters && empty($this->active_filters)) {
                foreach ($active_filters as $key => $value) {
                    if (zen_not_null($value) && $value !== '') {
                        $this->active_filters['bookx_' . $key] = $value;
                    }
                }
            }
        //}
    }

}
