<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    Massimiliano Palermo <maxx.palermo@gmail.com>
 * @copyright Since 2016 Massimiliano Palermo
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace MpSoft\MpStock\Helpers;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'mpstock/models/autoload.php';

class StockMovement
{
    protected $id_product;
    protected $id_product_attribute;
    protected $id_mpstock_import;
    protected $filename;
    protected $errors;
    protected $module;
    protected $class_name = 'StockMovement';

    protected $quantity_before = 0;
    protected $quantity_after = 0;

    public function __construct($id_product, $id_product_attribute, $id_mpstock_import = null, $filename = null)
    {
        $this->id_product = (int) $id_product;
        $this->id_product_attribute = (int) $id_product_attribute;
        $this->id_mpstock_import = (int) $id_mpstock_import;
        $this->filename = $filename;
        $this->errors = [];
        $this->module = \Module::getInstanceByName('mpstock');
        if ($this->id_product === 0) {
            $this->errors[] = $this->module->l('Id prodotto non valido', $this->class_name);
        }
        if (!$this->errors) {
            $this->quantity_before = (int) \StockAvailable::getQuantityAvailableByProduct($this->id_product, $this->id_product_attribute);
        }
    }

    /**
     * Generate Stock movement record
     *
     * @param int $id_mpstock_mvt_reason
     * @param int $quantity
     * @param float|null $price
     * @param float|null $wholesale_price
     *
     * @return \ModelMpStockProduct|false
     */
    public function generateMovement($id_mpstock_mvt_reason, $quantity, $price = null, $wholesale_price = null)
    {
        if ($quantity === 0) {
            $this->errors[] = $this->module->l('Quantity must be different from 0', $this->class_name);
        }

        if ($this->id_product === 0) {
            $this->errors[] = $this->module->l('Id prodotto non valido', $this->class_name);
        }

        if ($id_mpstock_mvt_reason === 0) {
            $this->errors[] = $this->module->l('Tipo movimento non valido', $this->class_name);
        }

        if ($this->errors) {
            return false;
        }

        $product = new \Product($this->id_product, false, \Context::getContext()->language->id);
        $combination = new \Combination($this->id_product_attribute, \Context::getContext()->language->id);

        if ($price === null) {
            $price = $product->price;
        }

        if ($wholesale_price === null) {
            $wholesale_price = $product->wholesale_price;
        }

        $record = new \ModelMpStockProduct();
        $record->id_warehouse = 0;
        $record->id_document = 0;
        $record->id_mpstock_mvt_reason = (int) $id_mpstock_mvt_reason;
        $record->id_product = (int) $this->id_product;
        $record->id_product_attribute = (int) $this->id_product_attribute;
        $record->reference = $product->reference;
        $record->ean13 = $combination->ean13;
        $record->upc = '';
        $record->stock_quantity_before = $this->quantity_before;
        $record->stock_movement = $quantity;
        $record->price_te = $price;
        $record->wholesale_price_te = $wholesale_price;
        $record->id_employee = (int) \Context::getContext()->employee->id;
        $record->id_mpstock_import = (int) $this->id_mpstock_import;
        $record->filename = $this->filename;
        $record->date_add = date('Y-m-d H:i:s');
        $record->date_upd = date('Y-m-d H:i:s');

        return $record;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function updateStock($id_product, $id_product_attribute, $quantity)
    {
        try {
            \StockAvailable::updateQuantity($id_product, $id_product_attribute, $quantity);
            \ModelProductAttribute::setStockQuantity($id_product, $id_product_attribute);
            $this->quantity_after = (int) \StockAvailable::getQuantityAvailableByProduct($id_product, $id_product_attribute);
        } catch (\Throwable $th) {
            $this->errors[] = $th->getMessage();

            return false;
        }

        return true;
    }

    public function updateImportMovementAfterStock($id_mpstock_import)
    {
        $stockQuantityAfter = (int) \StockAvailable::getQuantityAvailableByProduct($this->id_product, $this->id_product_attribute);
        $this->quantity_after = (int) $stockQuantityAfter;
        if ($id_mpstock_import) {
            try {
                \Db::getInstance()->update(
                    \ModelMpStockImport::$definition['table'],
                    ['loaded' => 1],
                    'id_mpstock_import=' . (int) $id_mpstock_import
                );

                \ModelMpStockImport::updateStock($id_mpstock_import, $this->quantity_before, $this->quantity_after);
            } catch (\Throwable $th) {
                $this->errors[] = $th->getMessage();

                return false;
            }
        }

        return true;
    }

    public function getStockQuantityBefore()
    {
        return (int) $this->quantity_before;
    }

    public function getStockQuantityAfter()
    {
        return (int) $this->quantity_after;
    }
}
