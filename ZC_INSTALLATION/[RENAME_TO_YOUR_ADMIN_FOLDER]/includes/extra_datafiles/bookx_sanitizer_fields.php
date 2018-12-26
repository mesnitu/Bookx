<?php
$sanitizer = AdminRequestSanitizer::getInstance();

$group = array(
    'author_description', 
    'publisher_description', 
    'series_description', 
    'imprint_description'
    );
$sanitizer->addSimpleSanitization('PRODUCT_DESC_REGEX', $group);

$group = array(
    'bookx_binding_id', 
    'bookx_author_id', 
    'bookx_publisher_id', 
    'bookx_series_id', 
    'bookx_author_type_id',
    'bookx_genre_id',
    'bookx_imprint_id',
    'bookx_printing_id',
    
);
$sanitizer->addSimpleSanitization('CONVERT_INT', $group);

$group = array(
    'bookx_publisher_name', 
    'bookx_author_name', 
    'bookx_author_type_name', 
    'bookx_publisher_name', 
    'bookx_genre_name',
    'products_subtitle',
    'pages',
    'isbn',
    'volume',
    'size'
);
$sanitizer->addSimpleSanitization('WORDS_AND_SYMBOLS_REGEX', $group);

