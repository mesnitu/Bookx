<?php

namespace Bookx;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Bookx
 *
 * @author mesnitu
 */
class BookxFamilies
{

    var $pID;
    protected $method;
    var $families_list;
    var $family_name;
    var $family_id;
    var $family_discount;
    var $family_stock_online;

    function __construct($on_page = null)
    {
        global $db, $current_page;
       
        if ($current_page == 'product.php' && !$on_page) {
            $this->setFamilies_list();
            $this->pID = (isset($_POST['product_id'])) ? $_POST['product_id'] : $_GET['pID'];
            $set_family_id_by = (!empty($_POST['bookx_family_id'])) ? $_POST['bookx_family_id'] : $this->pID;
            
            $this->method = (empty($set_family_id_by)) ? 'new_product' : 'update_product';
            $this->setFamily_id($set_family_id_by);
            $this->setFamilyInfo();
            
        } elseif (!empty($on_page)) {
            if ( $current_page == $on_page) {
                //for copy product
                //$this->pID =$_POST['products_id'];
                $this->setFamily_id($_POST['products_id']);
                $this->setFamilyInfo();
            }
        }else {
            $this->method = 'csv';
            // @todo csv import / export
        }
       
    }

    function getFamilies_list()
    {
        return $this->families_list;
    }

    function getFamily_name()
    {
        return $this->family_name;
    }

    function getFamily_id()
    {
        return $this->family_id;
    }

    function getFamily_discount()
    {
        return $this->family_discount;
    }

    function setFamilies_list()
    {
        global $db;
        
        $sql = "SELECT * FROM " . TABLE_PRODUCT_BOOKX_FAMILIES . " ORDER BY bookx_family_id;";
        $res = $db->Execute($sql);
        while (!$res->EOF) {
            $this->families_list[] = array(
                'bookx_family_id' => $res->fields['bookx_family_id'],
                'bookx_family_name' => $res->fields['bookx_family_name'],
                'bookx_family_discount' => $res->fields['bookx_family_discount'],
                'bookx_family_stock_online' => $res->fields['bookx_family_stock_online']
            );
            $res->MoveNext();
        }

        return $this;
    }

    function setFamily_name($family_id, $pID = false)
    {
        global $db;
        $this->family_name = $this->searchOnFamiliesList('bookx_family_name');
        return $this;
    }

    function setFamily_id($set_family_id_by)
    {
        global $db;
            $res = $db->Execute("SELECT bookx_family_id FROM " . TABLE_PRODUCT_BOOKX_FAMILIES_TO_PRODUCTS . " WHERE products_id = " . (int) $set_family_id_by . ";");
            
            if ($res->RecordCount() > 0) {
                $this->family_id = $res->fields['bookx_family_id'];
            }else {
                $this->family_id = $this->searchOnFamiliesList('bookx_family_id', $set_family_id_by);
            }
        
        return $this;
    }

    function setFamily_discount($family_discount)
    {
        $this->family_discount = $this->searchOnFamiliesList('bookx_family_discount');
        return $this;
    }
    

    function BookxUpdateFamilyProduct($pID)
    {
        $this->sqlBookxFamilyProduct($pID, 'update');
    }

    function BookxInsertFamilyProduct($pID)
    {
        $this->pID = $pID; // setting new generated pID
        $this->sqlBookxFamilyProduct($pID, 'insert');
    }
    function BookxDeleteFamilyProduct($pID)
    {
        $this->sqlBookxFamilyProduct($pID, 'delete');
    }
    

    /**
     * Private Functions
     */
    function applyFamilyDiscount()
    {

        $item_price = $_POST['products_price'];
        $special_price = $item_price - (($item_price * $this->family_discount) / 100);
        if ($_GET['action'] == 'new_product_preview') {
            return $special_price;
        } else {
            global $db;
            $products_id = $this->pID;
            
            // Check if this product already has a special
            $sql = "SELECT products_id FROM " . TABLE_SPECIALS . " WHERE products_id = :products_id:";
            $sql = $db->bindVars($sql, ':products_id:', $products_id , 'integer');
            $res = $db->Execute($sql);
            if ($res->RecordCount() > 0) {
                $sql = "UPDATE " . TABLE_SPECIALS . " SET
              specials_new_products_price = :specials_price:,
              specials_last_modified    = now(),
              specials_date_available   = :specials_date_avail:,
              expires_date        = :specials_expires_date:,
              status            = '1'
              WHERE products_id     = :products_id:";
                
                $sql = $db->bindVars($sql, ':specials_price:', $special_price, 'float');
                $sql = $db->bindVars($sql, ':specials_date_avail:', 'DEFAULT', 'noquotestring');
                $sql = $db->bindVars($sql, ':specials_expires_date:', 'DEFAULT', 'noquotestring');
                $sql = $db->bindVars($sql, ':products_id:', $products_id, 'integer');
                $db->Execute($sql);
            } else {
                
                $sql = "INSERT INTO " . TABLE_SPECIALS . " (products_id, specials_new_products_price, specials_date_added,
                specials_date_available, expires_date, status) VALUES (
              :products_id:, :specials_price:, now(), :specials_date_avail:, :specials_expires_date:, '1')";
                $sql = $db->bindVars($sql, ':products_id:', $products_id, 'integer');
                $sql = $db->bindVars($sql, ':specials_price:', $special_price, 'float');
                $sql = $db->bindVars($sql, ':specials_date_avail:', 'DEFAULT', 'noquotestring');
                $sql = $db->bindVars($sql, ':specials_expires_date:', 'DEFAULT', 'noquotestring');
                $db->Execute($sql);
            }
        }
        zen_update_products_price_sorter((int)$products_id);
    }

    private function setFamilyInfo()
    {
        $this->setFamily_name($this->family_id);
        $this->setFamily_discount($this->families_list);
    }
    
    private function searchOnFamiliesList($this_field, $id = null)
    {

        foreach ($this->families_list as $value) {

            if (!empty($id) && ($id == $value['bookx_family_id'])) {
                $result = $value[$this_field];
                break;
            } else {
                //echo $value['bookx_family_id'] . 'value . ' . $this->family_id . '<br>';
                if ($value['bookx_family_id'] == $this->family_id) {
                    $this->families_list[$this_field];
                    $result = $value[$this_field];
                }
            }
        }
        return $result;
    }

    private function sqlBookxFamilyProduct($pID, $option)
    {
        global $db;

        $pID = (empty($this->pID)) ? $pID : $this->pID;
       
        $primary_id = $db->Execute("SELECT primary_id FROM " . TABLE_PRODUCT_BOOKX_FAMILIES_TO_PRODUCTS . " WHERE products_id = '" . (int) $pID . "';");
        
        if ($primary_id->RecordCount() == 0 && !empty($this->family_id)) {
            // check is it's a familiy insert on a product update with a family_id. Else it could mess a delete option
            $option = 'insert';
        }
       
        switch ($option) {
            case 'insert':
                $sql = "INSERT INTO " . TABLE_PRODUCT_BOOKX_FAMILIES_TO_PRODUCTS . " (products_id, bookx_family_id)
                VALUES (" . (int) $pID . ", " . (int) $this->family_id . ");";
                $sql = $db->bindVars($sql, ':products_id:', (int) $pID, 'integer');
                $sql = $db->bindVars($sql, ':bookx_family_id:', (int) $this->family_id , 'integer');
                $res = $db->Execute($sql);
                break;
            case 'update':
                $sql = "UPDATE " . TABLE_PRODUCT_BOOKX_FAMILIES_TO_PRODUCTS . " SET products_id = :products_id: , bookx_family_id= :bookx_family_id:
                WHERE primary_id = :primary_id:;";
                $sql = $db->bindVars($sql, ':products_id:', (int) $pID, 'integer');
                $sql = $db->bindVars($sql, ':bookx_family_id:', (int) $this->family_id , 'integer');
                $sql = $db->bindVars($sql, ':primary_id:', (int)$primary_id->fields['primary_id'] , 'integer'); 
                $res = $db->Execute($sql);
                break;
            case 'delete':
                
                $sql = "DELETE FROM " . TABLE_PRODUCT_BOOKX_FAMILIES_TO_PRODUCTS . " WHERE products_id = :products_id:;";
                $sql = $db->bindVars($sql, ':products_id:', (int) $pID , 'integer'); 
                $res = $db->Execute($sql);
                break;
            default:
                break;
        }
    }

    private function deleteFamily($fID)
    {
        
    }


}
