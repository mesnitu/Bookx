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
    var $metaInfo = array();
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

    public function getInfo()
    {
        return $this->metaInfo;
    }

    public function setMetaInfo($info)
    {
        foreach ($info as $key => $value) {
            $this->metaInfo[$key] = $value;
        }
        return $this;
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
                (isset($_GET['bookx_author_type_id']) && $_GET['bookx_author_type_id'] != '') ||
                (isset($_GET['bookx_imprint_id']) && $_GET['bookx_imprint_id'] != '' ) ||
                (isset($_GET['bookx_series_id']) && $_GET['bookx_series_id'] != '' ) ||
                (isset($_GET['bookx_genre_id']) && $_GET['bookx_genre_id'] != '' ) ||
                (isset($_GET['bookx_publisher_id']) && $_GET['bookx_publisher_id'] != '' ) ||
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
    /*
     * This will set the defautl keywords, if you want to use them
     */
    //$zco_notifier->notify('NOTIFY_MODULE_META_TAGS_BUILDKEYWORDS', CUSTOM_KEYWORDS, $keywords_string_metatags);
    function updateNotifyModuleMetaTagsBuildkeywords($callingClass, $notifier, $paramsArray)
    {

        if (!empty($this->active_filters)) {
            global $keywords_string_metatags;
            $this->meta_keywords = $keywords_string_metatags;
        }
    }

    /* Note: keywords are no longer relevant to search engines. I'm still placing then here for whatever reason.
    * I'll build most of the information in the description.  
    * The info gather on $this->metaInfo can be however usefull to build related openGraph tags , etc.. 
    * @see https://yoast.com/meta-keywords/
    */
     //$zco_notifier->notify('NOTIFY_MODULE_META_TAGS_OVERRIDE', $metatag_page_name, $meta_tags_over_ride, $metatags_title, $metatags_description, $metatags_keywords);
    function updateNotifyModuleMetaTagsOverride(&$callingClass, $notifier, $paramsArray)
    {
        global $metatags_title, $metatags_description, $metatags_keywords;
        global $bookx_tpl_meta_info;
        if ($this->bookx_page == true) {
            
        } else {
            $this->metatags_title = BOOKX_META_MULTIPLE_FILTERS_PREFIX;
            $this->metatags_description = $this->metatags_title;
            $name = null;

            $wrap = function ($label, $name = null) {
                return $label . ' ' . $name . BOOKX_META_DIVIDER;
            };
        }

        if (!empty($this->active_filters['bookx_author_id'])) {
            if ($this->count_filters == 1) {
                global $author_meta_info;
                /*
                 * send $author_meta_info result as global to be used in main bookx observer
                 */
                $author_meta_info = $this->bookxGetMetaTagsInfo('author');
                /*
                 * send $bookx_tpl_meta_info result as global to be used in others scopes such as open_graph
                 */
                $bookx_tpl_meta_info = $this->metaInfo;

                $this->bookxSetMetaTags('author', $description = array(
                    $this->metaInfo['author_name'],
                    $this->metaInfo['author_books_names']
                    ), $this->metaInfo['author_books_names']
                );
            } else {
                $name = bookx_get_author_name($this->active_filters['bookx_author_id']);
                $this->metatags_title .= $wrap(LABEL_BOOKX_AUTHOR, $name);
                $this->metatags_description .= $wrap(sprintf(BOOKX_METATAGS_TITLE['author'],$name), '');
            }
        }
        if (!empty($this->active_filters['bookx_publisher_id'])) {
            if ($this->count_filters == 1) {
                global $publisher_meta_info;

                $publisher_meta_info = $this->bookxGetMetaTagsInfo('publisher');
                $bookx_tpl_meta_info = $this->metaInfo;
                $this->bookxSetMetaTags('publisher', '', $this->metaInfo['publisher_books_names']);
            } else {
                $name = bookx_get_publisher_name($this->active_filters['bookx_publisher_id']);
                $this->metatags_title .= $wrap(LABEL_BOOKX_PUBLISHER, $name);
                $this->metatags_description .= $wrap(sprintf(BOOKX_METATAGS_TITLE['publisher'],$name));
            }
        }
        if (!empty($this->active_filters['bookx_imprint_id'])) {
            if ($this->count_filters == 1) {
                global $imprint_meta_info;

                $imprint_meta_info = $this->bookxGetMetaTagsInfo('imprint');
                $bookx_tpl_meta_info = $this->metaInfo;
                $this->bookxSetMetaTags('imprint', $description = array(
                    $this->metaInfo['imprint_name']
                    ), $this->metaInfo['publisher_books_names']);
            } else {
                $name = bookx_get_imprint_name($this->active_filters['bookx_imprint_id']);
                $this->metatags_title .= $wrap(LABEL_BOOKX_IMPRINT . $name);
                $this->metatags_description .= $wrap(sprintf(BOOKX_METATAGS_TITLE['imprint'],$name));
            }
        }
        if (!empty($this->active_filters['bookx_series_id'])) {
            if ($this->count_filters == 1) {
                global $series_meta_info;

                $series_meta_info = $this->bookxGetMetaTagsInfo('series');
                $bookx_tpl_meta_info = $this->metaInfo;

                $this->bookxSetMetaTags('series', $description = array(
                    $this->metaInfo['series_name']
                    ), $this->metaInfo['series_books_names']);
            } else {
                $name = bookx_get_series_name($this->active_filters['bookx_series_id'], $_SESSION['languages_id']);
                $this->metatags_title .= $wrap(LABEL_BOOKX_SERIE, $name);
                $this->metatags_description .= $wrap(sprintf(BOOKX_METATAGS_TITLE['series'],$name));
            }
        }
        if (!empty($this->active_filters['bookx_genre_id'])) {
            if ($this->count_filters == 1) {
                global $genre_meta_info;

                $genre_meta_info = $this->bookxGetMetaTagsInfo('genre');
                $bookx_tpl_meta_info = $this->metaInfo;

                $this->bookxSetMetaTags('genre', $description = array(
                    $this->metaInfo['genre_name']
                    ), $this->metaInfo['genre_books_names']);
            } else {
                $name = bookx_get_genre_name($this->active_filters['bookx_genre_id'], $_SESSION['languages_id']);
                $this->metatags_title .= $wrap(LABEL_BOOKX_GENRE, $name);
                $this->metatags_description .= $wrap(sprintf(BOOKX_METATAGS_TITLE['publisher'],$name));
            }
        }

        $page = (!empty($this->pagination) ? ', page - ' . $this->pagination : '');
        $metatags_title = $this->metatags_title . $page;
        $metatags_description = $this->metatags_description . $page;
        $metatags_keywords = $this->metatags_keywords;
        pr($this);
    }

    /**
     * 
     * @param type $scope the filter scope 'author', 'genre', etc
     * @param type $title build with BOOKX_METATAGS_TITLE[$scope]
     * @param array $description an array to build the description. If empty it will use the BOOKX_META_FILTERS_EMPTY_DESCRIPTION[$scope]
     * @param type $keywords
     */
    private function bookxSetMetaTags($scope, $description = array(), $keywords = null)
    {
        
        $temp_desc = (!empty($description)) ? implode(', ', $description) . ',' : '';
        if (empty($this->metaInfo[$scope . '_description'])) {
            $build_desc = sprintf(BOOKX_META_FILTERS_EMPTY_DESCRIPTION[$scope], $this->metaInfo[$scope . '_name']) . $temp_desc;
        } else {
            $build_desc = $temp_desc . zen_truncate_paragraph(zen_clean_html($this->metaInfo[$scope . '_description']), MAX_META_TAG_DESCRIPTION_LENGTH) . ',';
        }
        
        $this->metatags_title = sprintf(BOOKX_METATAGS_TITLE[$scope], $this->metaInfo[$scope . '_name']);
        $this->metatags_description = $build_desc;
        $this->metatags_keywords = (!$keywords) ? $this->metatags_keywords : $keywords;
    }

    private function bookxGetMetaTagsInfo($param)
    {
        global $db;

        if ($param == 'author') {
            $sql = "SELECT batp.bookx_author_id, ba.author_name, ba.author_image, ba.author_url, bad.author_description,
                 GROUP_CONCAT(DISTINCT pd.products_name ORDER BY pd.products_id ASC SEPARATOR ',') AS author_books_names,
                 GROUP_CONCAT(DISTINCT pd.products_id ORDER BY pd.products_id ASC SEPARATOR ',') AS author_books_id,
                 GROUP_CONCAT(DISTINCT bgd.genre_name ORDER BY bgd.genre_name ASC SEPARATOR ',') AS author_books_genres 
                 FROM " . TABLE_PRODUCT_BOOKX_AUTHORS_TO_PRODUCTS . " batp
                 LEFT JOIN " . TABLE_PRODUCT_BOOKX_AUTHORS . " ba ON ba.bookx_author_id = batp.bookx_author_id 
                 LEFT JOIN " . TABLE_PRODUCT_BOOKX_AUTHORS_DESCRIPTION . " bad ON 
                     bad.bookx_author_id = batp.bookx_author_id AND bad.languages_id = :languages_id: 
                LEFT JOIN " . TABLE_PRODUCT_BOOKX_GENRES_TO_PRODUCTS . " bgtp ON bgtp.products_id = batp.products_id
                LEFT JOIN " . TABLE_PRODUCT_BOOKX_GENRES_DESCRIPTION . " bgd ON 
                    bgd.bookx_genre_id = bgtp.bookx_genre_id AND bgd.languages_id = :languages_id:
                
                LEFT JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd ON pd.products_id = batp.products_id AND pd.language_id = :languages_id:
                WHERE ba.bookx_author_id = :bookx_author_id:";
            $sql = $db->bindVars($sql, ':bookx_author_id:', $this->active_filters['bookx_author_id'], 'integer');
            $sql = $db->bindVars($sql, ':languages_id:', $_SESSION['languages_id'], 'integer');
            $author = $db->Execute($sql);
            
            $this->setMetaInfo($author->fields);
            
            return $author;
        }
                
        //if ($param == 'author_type') {}  // author_types filter are not working at present time 

        if ( $param == 'publisher' ) {
            
            $sql = "SELECT bep.bookx_publisher_id as publisher_id, bp.publisher_name, bp.publisher_image, bpd.publisher_description, bpd.publisher_url,
                 GROUP_CONCAT(DISTINCT pd.products_name ORDER BY pd.products_name ASC SEPARATOR ',') AS publisher_books_names
                 FROM " . TABLE_PRODUCT_BOOKX_PUBLISHERS . " bp
                 LEFT JOIN " . TABLE_PRODUCT_BOOKX_PUBLISHERS_DESCRIPTION . " bpd ON bpd.bookx_publisher_id = bp.bookx_publisher_id 
                 LEFT JOIN " . TABLE_PRODUCT_BOOKX_EXTRA . " bep ON bep.bookx_publisher_id = bp.bookx_publisher_id
                 LEFT JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd ON pd.products_id = bep.products_id
                 WHERE bpd.bookx_publisher_id = :bookx_publisher_id: AND bpd.languages_id = :languages_id: ";
            $sql = $db->bindVars($sql, ':bookx_publisher_id:', $this->active_filters['bookx_publisher_id'], 'integer');
            $sql = $db->bindVars($sql, ':languages_id:', $_SESSION['languages_id'], 'integer');
            $publisher = $db->Execute($sql);
            
            $this->setMetaInfo($publisher->fields);
            return $publisher;
        }
        
        if ($param == 'imprint') {
            $sql = 'SELECT i.imprint_name, i.imprint_image, id.imprint_description
					FROM ' . TABLE_PRODUCT_BOOKX_IMPRINTS . ' i
                    LEFT JOIN ' . TABLE_PRODUCT_BOOKX_IMPRINTS_DESCRIPTION . ' id ON id.bookx_imprint_id = i.bookx_imprint_id AND id.languages_id = "' . (int) $_SESSION['languages_id'] . '"
                    WHERE i.bookx_imprint_id = "' . (int)$this->active_filters['bookx_imprint_id'] . '"';
            $imprint = $db->Execute($sql);
            
            $this->setMetaInfo($imprint->fields);
            return $imprint;
        }
        
        if ($param == 'series') {
        $sql = "SELECT be.bookx_series_id, bsd.*,
                 GROUP_CONCAT(DISTINCT pd.products_name ORDER BY pd.products_name ASC SEPARATOR ',') AS series_books_names, 
                 GROUP_CONCAT(DISTINCT pd.products_id ORDER BY pd.products_id ASC SEPARATOR ',') AS series_books_id
                 FROM " . TABLE_PRODUCT_BOOKX_SERIES_DESCRIPTION . " bsd 
                 LEFT JOIN " . TABLE_PRODUCT_BOOKX_EXTRA . " be ON be.bookx_series_id = bsd.bookx_series_id
                 LEFT JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd ON pd.products_id = be.products_id
                 WHERE bsd.bookx_series_id = :bookx_series_id: AND bsd.languages_id = :languages_id: ";
            $sql = $db->bindVars($sql, ':bookx_series_id:', (int)$this->active_filters['bookx_series_id'], 'integer');
            $sql = $db->bindVars($sql, ':languages_id:', $_SESSION['languages_id'], 'integer');
            $series = $db->Execute($sql);
            
            $this->setMetaInfo($series->fields);
            return $series;
        }
        
        if ( $param == 'genre' ) {

            $sql = "SELECT bgd.genre_name as genre_name,  bgd.genre_image FROM " . TABLE_PRODUCT_BOOKX_GENRES_DESCRIPTION . " bgd
                 WHERE bgd.bookx_genre_id = :bookx_genre_id: AND bgd.languages_id = :languages_id: ";
            $sql = $db->bindVars($sql, ':bookx_genre_id:', (int)$this->active_filters['bookx_genre_id'], 'integer');
            $sql = $db->bindVars($sql, ':languages_id:', $_SESSION['languages_id'], 'integer');
            $genre = $db->Execute($sql);
            $this->setMetaInfo($genre->fields);
            return $genre;
        }
        
    }
    
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
