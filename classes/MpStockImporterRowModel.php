<?php
/**
 * 2007-2018 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    Massimiliano Palermo <info@mpsoft.it>
 *  @copyright 2007-2018 Digital Solutions®
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

require_once _PS_MODULE_DIR_ . 'mpstock/classes/MpStockTools.php';

class MpStockImporterRowModel
{
    /** Parsed row values */
    public $ean13;
    public $reference;
    public $qty;
    public $price;
    public $wholesale_price;
    /** Table fields values */
    public $id_mp_stock;
    public $id_mp_stock_import;
    public $id_mp_stock_exchange;
    public $id_shop;
    public $id_product;
    public $id_product_attribute;
    public $name;
    public $id_mp_stock_type_movement;
    public $tax_rate;
    public $date_movement;
    public $sign;
    public $date_add;
    public $id_employee;
    public $snap;
    private $parent;

    public function __construct($row, $parent)
    {
        $this->module = new MpStock();
        $this->parent = $parent;
        $status = 5;

        foreach ($row as $key => $value) {
            switch ($key) {
                case 'ean13':
                    $status = $status - (int) $this->getEan13($value, $row);
                    break;
                case 'reference':
                    $status = $status - (int) $this->getReference($value);
                    break;
                case 'qty':
                    $status = $status - (int) $this->getQty($value);
                    break;
                case 'price':
                    $status = $status - (int) $this->getPrice($value);
                    break;
                case 'wholesale_price':
                    $status = $status - (int) $this->getWholesalePrice($value);
                    break;
                default:
                    $this->parent->addError(
                        sprintf(
                            $this->module->l('Column not defined: %s', get_class($this)),
                            $key
                        )
                    );
            }
        }

        if ($status != 0) {
            $this->parent->addError($this->module->l('Error: Parsing incomplete.', get_class($this)));
            $this->status = false;
        } else {
            $this->status = true;
        }
    }

    public function getEan13($value, $row = null)
    {
        if (empty($value) || Tools::strlen($value) != 13) {
            $this->parent->addError(
                sprintf(
                    $this->module->l('Ean13 not valid %s', get_class($this)),
                    $value
                )
            );
            if (isset($row) && isset($row['reference'])) {
                $this->parent->addError(
                    sprintf(
                        $this->module->l('Unable to find %s, Ean13 %s', get_class($this)),
                        $row['reference'],
                        $value
                    )
                );
            }
            return false;
        }
        $this->ean13 = $value;
        $db = Db::getInstance();
        $sql = "select count(*) from " . _DB_PREFIX_ . "product_attribute where ean13='" . pSQL($value) . "'";
        $count = (int) $db->getValue($sql);
        if (!$count) {
            $this->parent->addError(
                sprintf(
                    $this->module->l('Ean13 %s not found.', get_class($this)),
                    $value
                )
            );
            if (isset($row) && isset($row['reference'])) {
                $this->parent->addError(
                    sprintf(
                        $this->module->l('Unable to find %s, Ean13 %s', get_class($this)),
                        $row['reference'],
                        $value
                    )
                );
            }
            return false;
        }
        return true;
    }

    public function getReference($value)
    {
        if (empty($value)) {
            $this->parent->addError(
                sprintf(
                    $this->module->l('Reference not valid %s', get_class($this)),
                    $value
                )
            );
            return false;
        }
        $this->reference = $value;
        $db = Db::getInstance();
        $sql = "select count(*) from " . _DB_PREFIX_ . "product_attribute where reference='" . pSQL($value) . "'";
        $count = (int) $db->getValue($sql);
        if (!$count) {
            $this->parent->addError(
                sprintf(
                    $this->module->l('Reference %s not found.', get_class($this)),
                    $value
                )
            );
            return false;
        }
        return true;
    }

    public function getQty($value)
    {
        if ((int) $value == 0) {
            $this->parent->addError(
                sprintf(
                    $this->module->l('Quantity (%s) must be different from zero.', get_class($this)),
                    $value
                )
            );
            return false;
        }
        $this->qty = abs($value);
        return true;
    }

    public function getPrice($value)
    {
        $this->price = abs((float) $value);
        return true;
    }

    public function getWholesalePrice($value)
    {
        $this->wholesale_price = abs((float) $value);
        return true;
    }

    public function prepareRow(
        $id_mp_stock,
        $id_mp_stock_import,
        $id_mp_stock_exchange,
        $id_shop,
        $id_mp_stock_type_movement,
        $date_movement,
        $date_add,
        $id_employee
    ) {
        $this->id_mp_stock = (int) $id_mp_stock;
        $this->id_mp_stock_import = (int) $id_mp_stock_import;
        $this->id_mp_stock_exchange = (int) $id_mp_stock_exchange;
        $this->id_shop = (int) $id_shop;
        $this->id_mp_stock_type_movement = (int) $id_mp_stock_type_movement;
        $this->date_movement = $date_movement;
        $this->date_add = $date_add;
        $this->id_employee = (int) $id_employee;

        $db = Db::getInstance();
        $sql = "select sign from "
            . _DB_PREFIX_ . "mp_stock_type_movement "
            . "where id_mp_stock_type_movement=" . (int) $this->id_mp_stock_type_movement;
        $this->sign = (int) $db->getValue($sql);
        $product_values = MpStockTools::getProductValues($this->ean13, $this->reference);
        if (!$product_values) {
            $this->parent->addError(
                sprintf(
                    $this->module->l('Error getting product with Ean13 %s and Reference %s', get_class($this)),
                    $this->ean13,
                    $this->reference
                )
            );
            return false;
        }
        $this->id_product = (int) $product_values['id_product'];
        $this->id_product_attribute = (int) $product_values['id_product_attribute'];
        $this->name = MpStockTools::getProductCombinationName($this->id_product_attribute);
        $this->tax_rate = MpStockTools::getTaxRateFromIdProduct($this->id_product);
        $this->snap = MpStockTools::getSnap($this->id_product_attribute);
        $this->parent->addNotification(
            sprintf(
                $this->module->l('Found product %d-%d with ean13 %s and reference %s.', get_class($this)),
                (int) $this->id_product,
                (int) $this->id_product_attribute,
                $this->ean13,
                $this->reference
            )
        );
        return $this->insertRow();
    }

    private function insertRow()
    {
        $movement = new MpStockObjectModel($this->id_mp_stock);
        $movement->ean13 = $this->ean13;
        $movement->reference = $this->reference;
        $movement->qty = $this->qty;
        $movement->snap = $this->snap;
        $movement->price = $this->price;
        $movement->wholesale_price = $this->wholesale_price;
        $movement->id_mp_stock_import = $this->id_mp_stock_import;
        $movement->id_mp_stock_exchange = $this->id_mp_stock_exchange;
        $movement->id_shop = $this->id_shop;
        $movement->id_product = $this->id_product;
        $movement->id_product_attribute = $this->id_product_attribute;
        $movement->name = $this->name;
        $movement->id_mp_stock_type_movement = $this->id_mp_stock_type_movement;
        $movement->tax_rate = $this->tax_rate;
        $movement->date_movement = $this->date_movement;
        $movement->sign = $this->sign;
        $movement->date_add = $this->date_add;
        $movement->id_employee = $this->id_employee;
        $this->parent->addNotification('Inserting record ' . print_r($movement, 1));
        try {
            $result = $movement->save();
            if ($result) {
                $this->parent->addConfirmation(
                    array(
                        'row ' => $movement->reference . ' ' . $movement->name,
                        'result' => 'done',
                        'id' => (int) $movement->id,
                    )
                );
            } else {
                $this->parent->addError(
                    array(
                        'row ' => $movement->reference . ' ' . $movement->name,
                        'result' => 'fail',
                    )
                );
            }
            return $result;
        } catch (Exception $e) {
            $this->parent->addError(
                sprintf(
                    $this->module->l('Error %d while saving product movement: %s.', get_class($this)),
                    Db::getInstance()->getNumberError(),
                    Db::getInstance()->getMsgError()
                )
            );
            return false;
        }
    }
}