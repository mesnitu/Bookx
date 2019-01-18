# Changelog for Zencart module Product Type BookX

**Still to do / fix**

 - [ ] Buttons "previous / next" do not work as expected when looking e.g. at the books of a series. Current workaround is only to disable these buttons completely (for example via CSS, setting their "display: none")
 - [ ] display special BookX info for products during checkout process and on orders
 - [ ] Breadcrumbs do not yet give meaningful results, especially when using bookx filters

## v1.0.0 DEV

> Version 1.0.0 is a complete review for BookX to work with Zencart v156 with php v7.3.
> Due to the zencart update nature, it's not compatible with olders versions. (until futher notice)

### Admin Changes

 - Update for Zencart 156. A lot as change on admin side.
 - Change: admin/product_bookx.php is no longer needed
 - Change: admin/modules/copy_to_confirm product_bookx.php is no longer needed
 - Change Installation method 
 - Improved Bookx Tools Layout
 - Update: Add bookx families
 - Add 2 more tables for books families and search
 - Update: Add Configuration entry to support CEON URI module
 

## v0.9.5 BETA November, 2017

 - compatible with ZC 1.5.5 revisited calculation of upcoming, new
 - (pre-orderable) and out of stock products (see Admin -> product info
   page)
  - Buttons "next product" / "previous product" on product info page now can either navigate products in ZC category (default ZC behaviour) or navigate back and fourth between products selected by bookx filter (Setting in Admin -> Configuration -> BookX: Configuration)
 - page title "BookX Tools" no longer overwrites page title "Image Handler" in Admin -> Tools -> Image handler section

## v0.9.4revisions8 BETA February 2, 2016

 - fixed external links to "author URL" and "publisher URL"

## v0.9.4revisions7 BETA November 8, 2015
 - fixed MYSQL syntax error in installation script

## v0.9.4revisions6 BETA August 24, 2015
 - fixed MYSQL syntax error in installation script
  - removed inappropriate installation warnings for missing german language files for shops with other languages

## v0.9.4r5 BETA November 8, 2014

 - fixed a Fatal MySQL Error when using multiple BookX filters and combining "imprint" filter with "publisher" filter. Changed file *[YOUR_ZC_INSTALLATION] -> includes -> functions -> extra_functions -> functions_product_type_bookx.php*

 - fixed issue where update from 0.9.2 to 0.9.4 was not being executed completely. Changed file *[YOUR_ZC_INSTALLATION] -> [YOUR-ADMIN-FOLDER] -> includes -> init_includes -> init_product_type_bookx.php*

 - resetting a bookx filter now redirects to homepage when no more filters are set. Changed file  *[YOUR_ZC_INSTALLATION] -> includes -> infex_filters ->  bookx_filter.php*

## v0.9.4r4 BETA released November 1, 2014

 - fixed a Fatal MySQL Error when setting *ADMIN -> Configuration -> Bookx: Configuration -> Upcoming Products: Base on Publication Date* to "0". Changed file *[YOUR_ZC_INSTALLATION] -> includes -> classes -> observers -> class.bookx_observers.php*

 - fixed issue where sorting "Upcoming Products" by BookX publishing dates would not correctly list products which only had a "date available" specified.*

- fixed missing constants in files

- *[YOUR_ZC_INSTALLATION] -> [YOUR_ADMIN_FOLDER] -> includes -> languages -> english -> bookx_authors.php*

- *[YOUR_ZC_INSTALLATION] -> [YOUR_ADMIN_FOLDER] -> includes -> languages -> english -> bookx_author_types.php*

- *[YOUR_ZC_INSTALLATION] -> [YOUR_ADMIN_FOLDER] -> includes -> languages -> english -> extra_definitions -> product_bookx.php*

-  Now includes an option to "Reset BookX Settings" via *ADMIN -> Tools -> Bookx: Installation & Tools -> Reset BookX Settings*

## v0.9.4r3 BETA released September 8, 2014

- fixed a Fatal Error when filtering for publisher via sidebox filter. Only difference to 0.9.4r2 is the file "[YOUR_ZC_INSTALLATION]/includes/index_filters/bookx_filter.php"

## v0.9.4r2 BETA released September 6, 2014

- ATTENTION USERS UPDATING FROM PREVIOUS VERSIONS: some more file have to be modifed manually, please read section "Updating" in the docs

-  implement mechanism to assign or remove  genres / series / authors / publishers / imprints, to multiple books at once
-  added option setting in ADMIN -> Configuration -> BookX Configuration to show product images for upcoming products as well as product description. Defaults to "On".
-  added a copyright field to author for author image copyright info

- implemented "imprint listing",  "publisher listing" and "genre listing" like the already existing "author listing", "series listing" (Settings to be found in Admin -> Catalog -> Product types -> Product Type BookX -> edit layout)

-  implemented sidebox filter for "author types" (Settings to be found in Admin -> Catalog -> Product types -> Product Type BookX -> edit layout)

- Bookx filters can now be set to allow multiple selections to add up in filtering products. NOTE: This behaviour is quite complex and possibly difficult for users to grasp. Defaults to OFF. Settings to be found in Admin -> Catalog -> Product types -> Product Type BookX -> edit layout)

-  Bookx now can be combined with extra "alpha" filters, to filter product listing according to "manufacturer" or "titel starting with letter A-Z"

-  fixed issue where volume number of book is not displayed in product listing

-  fixed issue where conversion of products to and from product type BookX was not working

-  Using "Admin profiles" now also allows for editing BookX products

-  fixed some HTML problems in the "Product Info Display" page (tpl_product_bookx_info_display.php)

-  updated documentation to better explain the conversion of existing ZC products to product type "BookX"

-  performance improvements to database queries by adding indexes to BookX database tables

## v0.9.3 BETA released February 5, 2014

-  Product Listing: Added option to sort and group listing display according to "not yet published / upcoming" , "published / in stock" , "out of print / not in stock" (turn on / off in Menu "Catalog -> Product Types -> Product BookX -> edit layout)

-  Added option to list New products and Upcoming products according to "publishing_date" in combination with "date available". (Settings in menu Configuration -> BookX: Konfiguration)

-  Product Detail page: fixed a mistake in the product_info template which inserted a stray </div> tag and prevented product series from being displayed

-  Product Detail page: Fixed an error when adding a BookX product with "product attribute" to shopping cart

-  Product Detail page: hiding the "product model" via Admin  -> product types -> layout no longer hides ISBN as well

-  fixed issue where assigning multiple authors to a product would only record the last author added, not all of them (this only happend when the feature "author types" is not used in the shop)

-  fixed issue where BookX Series image path would become corrupted after editing BookX Series

-  fixed issue where adding multiple authors would only record one author per edit, discarding the other added authors

-  fixed error due to use of undefined variable "DIR_FS_CATALOG_LANGUAGES"

-   fixed error when using manufacturers in combination with BookX filters

-  fixed installation script error for shops without additional foreign languages besides english

## v0.9.2 BETA released August 2013

- ATTENTION: some templates have now moved to "template_default" to allow easier override by sys admins. Template files of previous versions by the same name should be removed manually!

-  BookX "Subtitle" now added to "new_products" and "upcoming_products"

-  BookX Subtitle and ISBN showing in Category list in ADMIN

-  ADMIN: Author, Publisher, Imprint and Series listings now have a search field

-  Related Books now split into "books by same authors team" and "books by single author" for books that have more than one author assigned

-  Heading for related books no longer showing when there are zero related books

-  Price and order button moved to top of page

-  possibility to exclude authors and series with books out of print from their respective listings (checkbox let's users override this)

-  product description now showing correctly again on product_info page

-  product URL showing correctly on product info page

## v0.9.1 BETA

-  BookX extra tables (authors, publishers etc.) can handle extra language installs in shop, after BookX install

-  added ISBN field to BookX product, so now "product model" fiedl can be used for something else.

-  check ISBN to be well-formed with a javascript

-  field "publishing date" now can have partial dates, e.g. only year or only year&month and the date display will adapt accordingly

-  added field "author_type_default" to table "authors". If used, this default value appears automatically as "author type" when adding an author to a BookX product.

-  fix an issue where BookX filters were not displaying correctly in shop (some data displayed twice)

-  HTML markup in the "collect_info.php" in case the field order in the admin form needs to be adapted to personal preferences

-  sort order now defaults to "0" for all BookX tables.

-  possibility to exclude certian author typed from the product listing, by setting a sort order "cutoff" above which authors are not displayed

-  any product listing view:

- "subtitle" and "volume" now displaying, if the corresponding flag is set in admin backend (paduct type -> edit layout)

- publishing date, condition and genre not showing in case of empty values, if corresponding flags are set

-  ZC Sidebox "Search" now also displays matches for serach terms in BookX attributes: subtitle, isbn, author name, publisher name, series name, genre

-  Admin "Product Preview" now also displays extra BookX data, after editing

-  Listings of all authors, publishers, labels and series

## v0.9 BETA

- first public release
