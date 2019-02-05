<?php
/**
 * This file is part of the ZenCart add-on Book X which
 * introduces a new product type for books to the Zen Cart
 * shop system. Tested for compatibility on ZC v. 1.56a
 *
 * @package admin
 * @author  mesnitu
 * @copyright Portions Copyright 2003-2011 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.gnu.org/licenses/gpl.txt GNU General Public License V2.0
 *
 * @version BookX V 1.0.0
 * @version $Id: [admin]/bookx_families.php 2019-01-19 mesnitu $
 */

/**
 * Product Type Book (BookX) Families
 *
 * This file handles creating, editing and deleting
 * families by csv
 */

require('includes/application_top.php');


$action = (isset($_GET['action']) ? $_GET['action'] : '');
$fields_ok = '';

if ($action == 'replace' && isset($_FILES['file_csv'])) {

    $tmpName = $_FILES['file_csv']['tmp_name'];

    $csvAsArray = array_map('str_getcsv', file($tmpName));
    array_walk($csvAsArray, function(&$a) use ($csvAsArray) {
        $a = array_combine($csvAsArray[0], $a);
    });
    array_shift($csvAsArray); # remove column header
    
    
    foreach ($csvAsArray as $key => $fam) {
        if ($fam['family_id'] == '' || $fam['family_name'] == '') {
            $messageStack->add('invalid csv - empty family id or family name', 'error');
            $fields_ok = false;
            break;
        } else {
            $fields_ok = true;
        }
    } 
}

if ($fields_ok == true) {
    
    $updated_ids = [];
    $msg = '';
    foreach ($csvAsArray as $key => $fam) {
        
        $id = zen_db_prepare_input($fam['family_id']);
        $name = zen_db_prepare_input($fam['family_name']);
        $discount = zen_db_prepare_input($fam['family_discount']);
        $stock = zen_db_prepare_input($fam['family_stock_online']);
        
        $updated_ids[] = $id;
        
        $res = $db->Execute("SELECT bookx_family_id , bookx_family_name FROM " . TABLE_PRODUCT_BOOKX_FAMILIES . "
                WHERE bookx_family_id = '" . (int) $id . "' AND bookx_family_name = '".$name."';");

        if ($res->RecordCount() > 0) {

            $sql = "UPDATE " . TABLE_PRODUCT_BOOKX_FAMILIES . " SET 
            bookx_family_id = :bookx_family_id: , bookx_family_name = :bookx_family_name:, 
            bookx_family_discount = :bookx_family_discount:, bookx_family_stock_online = :bookx_family_stock_online: WHERE 
            bookx_family_id = :bookx_family_id:;";
            $sql = $db->bindVars($sql, ':bookx_family_id:', $id, 'integer');
            $sql = $db->bindVars($sql, ':bookx_family_name:', $name, 'string');
            $sql = $db->bindVars($sql, ':bookx_family_discount:', $discount, 'float');
            $sql = $db->bindVars($sql, ':bookx_family_stock_online:', $stock, 'integer');
            
            $msg .= "Updated fam[".$name."]<br />";
            
        } else {
            
            $sql = "INSERT INTO " . TABLE_PRODUCT_BOOKX_FAMILIES . " 
            (bookx_family_id, bookx_family_name, bookx_family_discount, bookx_family_stock_online)
            VALUES (:bookx_family_id:, :bookx_family_name:, :bookx_family_discount:, :bookx_family_stock_online:);";
            $sql = $db->bindVars($sql, ':bookx_family_id:', $id, 'integer');
            $sql = $db->bindVars($sql, ':bookx_family_name:', $name, 'string');
            $sql = $db->bindVars($sql, ':bookx_family_discount:', $discount, 'float');
            $sql = $db->bindVars($sql, ':bookx_family_stock_online:', $stock, 'integer');
            $msg .= "New fam[".$name."]<br />";
           
        }
        
        $db->Execute($sql);
    }

    unset($sql, $res);

    $q = "";
    
    if (!empty($updated_ids)) {
        
        foreach ($updated_ids as $fid) {
            $q .= " bookx_family_id != '" . (int) $fid . "' AND"; // construct the query with the id's
        }
    
    $q = substr($q, 0, -3);
    
    /**
     * if a special was set, the discount will remain.
     * Using ep4bookx on next stocks update the values are set correctly.
     * for this to work here, one has to query no existent ids to delete or put into status 0 the special price
     * 
     */
    $sql = "SELECT products_id FROM " . TABLE_PRODUCT_BOOKX_FAMILIES_TO_PRODUCTS . " "
        . "WHERE " . $q . ";";
    
    $res = $db->Execute($sql);

    while (!$res->EOF) {
        $sql = "DELETE FROM " . TABLE_SPECIALS . " WHERE products_id = :products_id:;";
        $sql = $db->bindVars($sql, ':products_id:', $res->fields['products_id'], 'integer');
        $db->Execute($sql);
        $msg .= "Updated specials [".$res->fields['products_id']."]<br />";
        $res->MoveNext();
    }
    // now delete families. families_to_prodducts will cascade on delete
    $delete = "DELETE FROM " . TABLE_PRODUCT_BOOKX_FAMILIES . " WHERE " . $q . ";";
    $db->Execute($delete);

    unset($_FILES, $delete, $res, $sql);
    $messageStack->add_session($msg, 'info');
    }
    
    zen_redirect(zen_href_link(FILENAME_BOOKX_FAMILIES));
}

$objBookxFamily = new \Bookx\BookxFamilies();
$objBookxFamily->setFamilies_list();

?>

<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <meta charset="<?php echo CHARSET; ?>">
    <title><?php echo TITLE; ?></title>
    <link rel="stylesheet" href="includes/stylesheet.css">
  </head>
  <body>
    <!-- header //-->
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
    <!-- header_eof //-->
    <!-- body //-->
    <div class="container">
        <h1><?php echo HEADING_TITLE; ?></h1>
        <p class="alert alert-info"><?php echo BOOKX_INFO_FAMLIES_ABOUT; ?></p>
        <div class="well">
            <?php 
            echo zen_draw_form('families_import', FILENAME_BOOKX_FAMILIES,'action=replace', 'post', 'enctype="multipart/form-data"'); ?>
            <div class="form-group">
            <label for="families_csv">File input</label>
            
            <?php echo zen_draw_file_field ('file_csv', true, 'id ="families_csv" class="form-control" required'); ?>
            <div class="help-block"><?php echo BOOKX_INFO_FAMLIES_EXPECTED_CSV_FIELDS; ?></div>
            </div>
            
            
            <button type="submit" class="btn btn-primary btn-sm"><?php echo BUTTON_SUBMIT_FAMILIES; ?></button>
        </div>
        <div class="table-responsive">
        <table class="table table-hover">
            <thead>
            <tr>
                <th>Family_ID</th>
                <th>Family Name</th>
                <th>Family discount</th>
                <th>Family Stock Online</th>
            </tr>
            </thead>
            <tbody>
            <?php 
            foreach ($objBookxFamily->families_list as $key => $value) { ?>
            <tr>
                <td><?php echo $value['bookx_family_id']; ?></td>
                <td><?php echo $value['bookx_family_name']; ?></td>
                <td><?php echo $value['bookx_family_discount']; ?></td>
                <td><?php echo $value['bookx_family_stock_online']; ?></td>
            </tr>    
            <?php } ?>
            </tbody>
        </table>
        </div>
        
        </div>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>