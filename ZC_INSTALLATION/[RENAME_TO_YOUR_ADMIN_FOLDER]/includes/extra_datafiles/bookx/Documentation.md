
# Important: This software is still in a beta testing stage!

>Note: with version 1.0.0 parts of this documentation are outdated. 

This is the documentation page for the ZenCart product type BookX version 0.9.5 Beta (September 29, 2017).

If you have questions not answered here, please post a comment in the "BookX" thread at the [ZenCart forum (english)](http://www.zen-cart.com/showthread.php?200560) or in german at [Zen Cart Pro](http://www.zen-cart-pro.at/forum/threads/10153-Neues-Modul-Artikeltyp-Buch).

If you have discovered a bug, please open a ticket on the [SourceForge BookX project page](http://sourceforge.net/p/zencartbookx).


## Description

This module introduces a new product type "BookX" into the Zencart shopping cart system. This product type is aimed a selling books. It allows the separate management of authors, publishers, imprints, series, genres, book conditions, binding type and printing type. These attributes can then be assigned to products of type "BookX". In the case of authors and genres, multiple assignments can be made to a single product. In addition there are fields for number of pages, volume number in a series and publishing date.

This module is only tested on ZenCart version 1.5 up to 1.5.5. It will most probably run as is on later versions, but certainly not on earlier versions of ZC.

## Features

* Separate database tables for publishers, authors, genres and some other book attributes, making these attributes are easier to maintain and allow for filtering of product lists in the shop
* Cross-referencing between products, such as "Other books by this author / in this genre etc.)
* Listings of all Authors, Series, (new as of 0.9.4:) Publishers, Imprints, Genres
* "Product Info" page template (i.e. info display for single product) which can be customized by settings in the admin and without modifying the PHP code
* Book X attributes are also available outside the "Product Info" page template, for example in the "Product Listing" template and the shopping cart without modifying ZC core files
* Conversion of existing products to type "BookX" and vice vers
* NEW in 0.9.3: Option to sort product listings according to books "releases soon" / "new" / "out of print"
* NEW in 0.9.4: Assign & remove Author / Publisher / Series / Genre / Imprint to multiple books at once

## Feedbackware

Since this project is hosted on Sourceforge it comes as no surprise that it is free, but this module is also _**Feedbackware**_: Please leave a message in the ZC forum if you are using this module and / or you have suggestions how it may be improved. Please report bugs.

## You appreciate the work done on BookX and would like to...

> The cat wants to eat caviar and the Mrs. wants to drive a Rolls. So, if you find this software useful and you would like to make a donation, those two will be very happy to spend it while I'm sitting in the garage programming :)
No amount is too small to be appreciated (if it pays me at least a beer) – Many thanks!

[![paypal](https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=B8AJYUXULQPSE)

---

## Documentation contents

### Install & Uninstall

### Operating BookX via ZC Admin

### Configuration

# Install & Uninstall

## Update

If you are updating from a previous version of BookX, then you should move all the files included in the archive to their respective directories in your Zen Cart installation. See "Install" below, if you need more instructions. 

> Attention: In version 0.9.2 some template files have moved to the  "template_default" directory to allow overrides in [YOUR-CURRENTLY-ACTIVE-TEMPLATE] folder. You need to manually remove  these template files with version 0.9.1 or lower from [YOUR-CURRENTLY-ACTIVE-TEMPLATE] folde in your ZC Shop installation.

Please exchange all the  files from one plugin version to the next. 
Once all files are in place, navigate to `Admin –> Tools –> BookX: Installation & Tools` and there you can click the link `Update BookX to new version`. 
If you have already performed an update and click this link, BookX will tell you that it is up to date.<br>

## Install

Move all the files included in the archive you downloaded to their respective directories in your Zen Cart installation. Folders which may be named differently on your system are indicated as `[YOUR-SHOP-URL] or [YOUR-ADMIN-FOLDER] or [YOUR-ACTIVE-TEMPLATE]`

Example:
The folder `[UNPACKED-MODULE_FOLDER]/includes/modules/pages/bookx_product_info`

should be placed in this folder on your system:
`[YOUR-ZEN-CART-FOLDER]/includes/modules/pages/`

**_During this entire process no existing file should be overwritten (unless you already have some "BookX" files ;-)._**

**_However, there are some files which may already exist on your system if you have modified (overridden) them yourself._**

**_If you have not modified these files yourself already, then you need copy them first before you can make the necessary manual changes:_**

copy 
`[YOUR-ZEN-CART-FOLDER]/includes/modules/new_products.php`

to 
`[YOUR-ZEN-CART-FOLDER]/includes/modules/[YOUR-ACTIVE-TEMPLATE]/new_products.php`

---

copy 
`[YOUR-ZEN-CART-FOLDER]/includes/modules/product_listing_alpha_sorter.php`

to 
`[YOUR-ZEN-CART-FOLDER]/includes/modules/[YOUR-ACTIVE-TEMPLATE]/product_listing_alpha_sorter.php`

---
copy 
`[YOUR-ZEN-CART-FOLDER]/includes/modules/upcoming_products.php`

to 
`[YOUR-ZEN-CART-FOLDER]/includes/modules/[YOUR-ACTIVE-TEMPLATE]/upcoming_products.php`

---
copy 
`[YOUR-ZEN-CART-FOLDER]/includes/templates/**template_default**/tpl_tabular_display.php`

to 
`[YOUR-ZEN-CART-FOLDER]/includes/templates/[YOUR-ACTIVE-TEMPLATE]/common/tpl_tabular_display.php`

---
copy 
`[YOUR-ZEN-CART-FOLDER]/includes/templates/template_default/templates/tpl_index_product_list.php`

to 
`[YOUR-ZEN-CART-FOLDER]/includes/templates/[YOUR-ACTIVE-TEMPLATE]/templates/tpl_index_product_list.php`

---
copy 
`[YOUR-ZEN-CART-FOLDER]/includes/templates/template_default/templates/tpl_modules_upcoming_products.php`

to 
`[YOUR-ZEN-CART-FOLDER]/includes/templates/[YOUR-ACTIVE-TEMPLATE]/templates/tpl_modules_upcoming_products.php`

You can use the files which are in this distribution as references. You will find them in the folder **[EDIT_MANUALLY]**. 
The manual changes inside the files are enclosed by comments, which also show the total number of manual changes inside that file. 
Example: 
///**** bof Bookx mod 2 of 3_ and _///**** eof Bookx mod 2 of 3_

For BookX to reach full functionality, the following edits have to be made manually.

**1.) At the top of file:**

`[YOUR-ZEN-CART-FOLDER]/[ADMIN]/includes/modules/category_product_listing.php`

before:
```php
// Split Page<br>
// reset page when page is unknown<br>
if (($_GET['page'] == '1' or $_GET['page'] == '') and $_GET['pID'] != '') {`
```
This line should be inserted:
```php
$zco_notifier->notify('NOTIFY_MODULE_ADMIN_CATEGORY_LISTING_QUERY_BUILT');
```

**2.) in the three files :**

`[YOUR-ZEN-CART-FOLDER]/includes/modules/[YOUR-ACTIVE-TEMPLATE]/new_products.php`

`[YOUR-ZEN-CART-FOLDER]/includes/modules/[YOUR-ACTIVE-TEMPLATE]/product_listing_alpha_sorter.php`

`[YOUR-ZEN-CART-FOLDER]/includes/modules/[YOUR-ACTIVE-TEMPLATE]/upcoming_products.php`

there are three lines to be inserted in each file. Please look inside the files included with this plugin to find the lines to add.

**3.) At the top of file:**

`[YOUR-ZEN-CART-FOLDER]/includes/templates/[YOUR-CURRENTLY-ACTIVE-TEMPLATE]/common/tpl_tabular_display.php`

before:
```php
//print_r($list_box_contents);`
```
the following code has to be inserted:
```php
$zco_notifier->notify('NOTIFY_TEMPLATE_PRODUCT_LISTING_TABULAR_DISPLAY_BEGIN');
```
This creates a notifier hook, so BookX can inject additional product information to be displayed.

**4.) At the top of file:**

`[YOUR-ZEN-CART-FOLDER]/includes/templates/[YOUR-CURRENTLY-ACTIVE-TEMPLATE]/templates/tpl_index_product_list.php`

after:
```php
<h1 id="productListHeading"><?php echo $breadcrumb->last(); ?></h1>
```
This line should be inserted:<br>
```php
<?php if (isset($extra_bookx_filter_term_info)) echo $extra_bookx_filter_term_info; ?>
```

**5.) the two files :**

`[YOUR-ZEN-CART-FOLDER]/includes/templates/[YOUR-ACTIVE-TEMPLATE]/templates/tpl_modules_upcoming_products.php`

**have been heavily modified** and you should use the files provided with this plugin. However, you may need to merge these files with any modifications you have done on your own.

**6.) At the top of file:**

`[YOUR-ZEN-CART-FOLDER]/includes/classes/shopping_cart.php`

around line 1084 inside the function get_products, replace:
```php
function get_products($check_for_valid_cart = false) {

global $db;
```
with:
```php
function get_products($check_for_valid_cart = false) {

global $db;

/**** begin addition bookx 1 of 1 ****/

global $products_array;

/**** end addition bookx 1 of 1 ****/
```

Note that the BookX template files are placed inside the template folder "template_default" which means they will be used regardless of which is `[YOUR-ACTIVE-TEMPLATE]`.  This means, that if you want to modify any BookX template, you can place a copy in directory `[YOUR-ACTIVE-TEMPLATE]` and the orginal BookX file is still around in case you need to go back to it.

Once all the files are in place, login to the ZenCart admin backend and paste the following URL into your browsers address field:
`http://[YOUR-SHOP-URL]/[YOUR-ADMIN-FOLDER]/?action=bookx_install`
an actual URL might look like this:
`http://www.mysupershop.com/admin/index.php?action=bookx_install`

## Conversion of existing products

It is  possible to convert existing products (e.g. of type "Product General") to product type BookX, so that existing product references remain useable (including orders made by customers etc.), and in addition the new BookX attributes can now be assigned. 
This feature can be accessed in `[Admin] -> Tools -> BookX: Installation & Tools -> Convert existing products`.
There are detailed on-screen instructions of how to proceed. 

> **NOTE: You should most definitely make a database backup before converting your products to BookX product type!**

## Uninstall

To remove the BookX module from your shop installation, you can go to Admin –> Tools –> BookX: Installation & Tools. There you have different options to remove BookX from the database:

**The first option** is to completely delete any products of type Book X in the database and all extra attributes like authors, publishers, genres etc.<br>

**The second option** involves first converting all existing BookX products to another product type (e.g. "Product General") keeping attributes such as price, name, weight which are present in BookX as well as the target product type. (***Currently this works with destination "Product General" but some more thinking needs to go into how to match attributes against another product type***) 
After the BookX products have been converted, you can choose the first option above to remove BookX, but now you have kept your products in the database. The product IDs remain unchanged, so existing references to these products (e.g. in orders) remain useable.

If you run into problems uninstalling BookX, but the `Tools –> Bookx` menu has already been removed, you can try to trigger the uninstall script again by pasting the following URL into your browser:

`http://[YOUR-SHOP-URL]/[YOUR-ADMIN-FOLDER]/?action=bookx_remove`

After you have uninstalled BookX as described above, you can delete the BookX PHP files from your system. 
Check the folder structure in this distribution to see where all these files are, or search for all files containing "bookx" in their filename inside the ZC installation.

# Operating BookX via ZC Admin

Operating BookX on the admin side means most of all inserting an editing data. When you first start using BookX you may in fact wish to first enter some data and then go on to set the configuration options, as it is hard to see the effect of some options without content. 
The BookX product data and attribute lists are not at all altered by BookX configuration settings, so you can insert data without having configured the module.

## Populating the Book X product attribute lists

The full strength of the Book X product type only will be put to use, if some the lists for extra product attributes are populated. These are:

* Authors
* Author Types (e.g. Writers / Illustrators / Photographers)
* Binding (e.g. Hardcover / Softcover)
* Conditions (e.g. new / used )
* Genres (e.g. Thriller / Fiction / Garden etc.)
* Imprints (e.g. Penguin "Classics")
* Printing (e.g. b&w / color / text with illustrations)
* Publishers
* Series (e.g. "Harry Potter" ;-p )

All these attributes can be set via menu items in `Admin –> Extras –> Bookx_` and have a combination of fields such as name, image, (long) description, URL. 
If you use more than one language in your shop, you will find that some of these fields allow for different values in multiple languages (e.g. name of genre) and some don't (e.g. name of author).

If you are trying to decide, whether you should use any/some of the attributes, then the main question should be whether you will want to filter products for some of these attributes or not.

## Inserting a new Book X product

The least you will have to do is to create a new product of type "Book X" via `Admin –> Catalog –> Categories & Products`. 
Inside a category you first need to choose `Product – BookX` in the popup next to the button "New product".

>  Hint: Unfortunately ZC does not currently support a "default" product type, so the popup has to be set each time you insert a new  product into the database. However, categories can be set to only use one product type, effectively making it the default product type for that category. This setting can be found when editing categories via the green "_ _e_" button in the category listing.

The new product of type BookX shows all the fields available for "Product – General" and the extra fields "publishing date", "no. of pages", "volume no." and "dimensions". 
If you have already inserted some authors, publishers, genres etc. (see below), you will see popups which allow you to choose from the list of authors, publishers, genres etc. you have created. **Note: If you have not created any entries for these (authors / publishers / genres etc.), you will not see a popup allowing you to choose from these (non-existent!) entries.** 
You are limited to the entries you have created for these lists and cannot manually add another option here. Please also note that a book can have multiple authors and genres, but only one publisher etc.

### Publishing date

The "publishing date" is differnet from the "date expected" which is also used normal ZCart products. The "date expected" should indicate the <u>availability in your shop</u>, whereas the publishing date is the date <u>when the book is first published on the market</u>. 
The publishing date is entered in this format for every country: YYYY-MM-DD with leading zeros, so September 8th 2014 is 2014-09-08. If the publishing date is not exact down to the day, you can set day or month to "00", to indicate that the exact day or even the exact month will not be displayed. Just "September 2014" would therefore be entered as "2014-09-00".

There are two settings  which also relate to the use of "publishiing date" in `ADMIN -> Configuration -> BookX: Configuration`. 
These settings are "**New Products: Base on Publication Date**" and "**Upcoming Products: Base on Publication Date**". 
They both default to "ON" with "90 days" which means that new and upcoming products are selected according to their "publishing date" as "new" or "upcoming".

### Linking a book to authors / series / imprint / publisher / genres

You can link a product you are editing to any  author / series / imprint / publisher / genre via their respective popups, but there may be situations where you would like to quickly assign e.g. a new genre to multiple books at once. 
This can be done by selecting the author / series / imprint / publisher / genre via `ADMIN -> Extras -> BookX: Genres -> Genre`. 
At the bottom of the edit screen there are two "multi-select" fields, where you can choose multiple books and assign the current genre. Inversely, you can use the bottom field to remove the assignment of the genre from multiple books at once.

## Filtering (searching for) BookX attributes

BookX provides some filtering functionality, which should be enough for many shops. Check out the "BookX Filters" sidebox, which will display popups for filtering based on settings in the admin backend (see above). 
The ZC system assumes that only one such filter is applied at a time, but BookX can handle setting multiple BookX filters at once. 
To use filters without the provided sidebox, you need to append at least two pieces of info to the URL. Firstly, you need to add "&typefilter=bookx" to let ZC know that you wish BookX to handle the filters and then you add the filter value, e.g. for an author "&bookx_author_id=6". 
If you play with the sidebox, you can see all filters and the name/value pairs to add to the URL. You can combine BookX filters, e.g. "&typefilter=bookx&bookx_author_id=5&bookx_publisher_id=12", but if more than one filter is applied. Bookx will no longer output the extra info above filter results, i.e. the aforementioned URL will <u>not</u> show author image and biography <u>nor</u> publisher description and logo etc.

# Configuration

## Configuration options for BookX via the shop Admin interface

The configuration options for BookX are spread over a few different locations in the ZC Admin menu:

### 1.) Catalog –> Product Types –> Products-BookX

Via the **_"edit"_** button you can modify some technical aspects of this product type, but it is recommended to leave these settings as they are except uploading a default image for the product type and setting the option to in-/exclude products of this type from the shopping cart.

More relevant is the button **_"edit layout"_** which allows customization of the display of most of the BookX attributes in the "product_listing" view and the "product_info" view. Most options accept plain language values which indicate what they do, or:

```
“0” = don’t display this attribute
“1” = show this attribute (and its description label)
```

At the bottom of this list of layout settings is another option "Filter results - show author / publisher etc.". 
When enabled, this means that if BookX filters are used, e.g. to show books by a certain author or publisher, some extra info is show above the search results, e.g. the foto of the author and his "description" (=biographical info), effectively transforming the filter results page into an "author" page or a "publisher" page etc.

As of version 0.9.3 there is a new option here to sort and group products according to availability dates.

### 2.) Configuration –> Book X: Configuration

Some settings can be found here which could possibly have been placed in other configuration menu options (such as "minimum values"), but which are grouped here for easier access. Some concern the display of the BookX Filters sidebox.

A new feature as of version 0.9.3 is the possibility to consider the BookX "publishing date" in addition to the "date available", when choosing which products to display in the modules "new products" and "upcoming products".

### 3.)Tools –> Layout Boxes Controller: BookX Filter sidebox

If you wish to use the provided sidebox for filtering products based on BookX attributes, you have to enable the sidebox in this menu. 
Please note that there are settings concerning which filters to show and whether they should be exclusive (i.e. setting one filter, rests all others) or if multiple filters are allowed. Note that this feature is set to _**OFF**_ by default, as it actually makes using the site quite complex for visitors, so you should evaluate carefully whether this feature is useful for your shop.

### 4.) Tools –> BookX Installation & Tools

This menu is really only relevant when (de-) installing or updating BookX and importing or exporting BookX products from/to another type (see section "Install and De-Install" above).

## Configuration options for BookX via language files

All texts and labels used by BookX are placed in language files which you can modify (rather than modifying the original files directly, it is recommended to override the language files by placing a copy with the same filename containing your edits in a folder named after your applied template next to the original file. Please check ZC documentation of the override mechanism, if you are not yet familiar with it).

Book X places language files in the following locations:
```
/includes/languages/english/product_bookx_info.php`
/includes/languages/english/extra_definitions/product_bookx_info.php
```

There are more language files used for the admin side, but seeing that these are not really critical for shop <-> customer interaction, I will not list them here. I suggest you refer to the folder structure of the original BookX distribution, where you can easily see which admin language files are in use.

## Further configuration / adaptation BookX inside the PHP templates / files

If all BookX elements you want are displayed and you just don't like the styling or the position, then it may be enough for you to edit 
`/includes/templates/[YOUR-TEMPLATE]/css/stylesheet_bookx.css`

to reach the desired result. BookX provides many hooks for CSS styling, so you should be able to get quite far without having to touch the PHP file. 
Check the HTML code e.g. with firebug to see what these CSS ids or classes are. If that is not working or you are not able to reach the desired behavior of BookX only with the configuration options described above, then you will need to modify the PHP files according to your needs. The lightest form of this adaptation is to modify the file `/includes/templates/[YOUR-TEMPLATE]/templates/tpl_product_bookx_info_display.php` which governs the display of one single product when it is of type BookX. 
There are some comments at the top of tpl_product_bookx_info_display.php in the distribution, listing BookX variables available inside that template.

If you have an existing template for "tpl_product_info_display.php", it may be quicker to copy some parts of the file above into your product info template and then save everything as "tpl_product_bookx_info_display.php".

If that still doesn't accomplish what you have in mind, then you will have to go deeper into the BookX files. Let us know in the BookX forum what you are doing and maybe even share the result. If it solves a problem more people are having it could be included in future BookX.

If you want to modify what and how extra BookX info is injected into "product_listing" and filter results, then look at the files:

`/includes/classes/observers/class.bookx_observers.php`
`/includes/modules/pages/product_bookx_info/*.php`

## Hints on how to speed up BookX on your website

In order to interfere the least possible with ZC core files, BookX does issue some queries a second time, which can increase loading time of your pages. If you want to reduce this and you know what you are doing, you can comment out the first queries made by ZC as follows:


