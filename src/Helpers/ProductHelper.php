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

class ProductHelper
{
    public static $instance;
    protected $id_lang;
    protected $id_employee;
    protected $currency;
    protected $module;

    private function __construct()
    {
        $this->id_lang = \Context::getContext()->language->id;
        $this->id_employee = \Context::getContext()->employee->id;
        $this->currency = \Context::getContext()->currency->id;
        $this->module = \Module::getInstanceByName('mpstock');
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getReasonName($value)
    {
        $db = \Db::getInstance();
        $sql = new \DbQuery();
        $sql->select('name')
            ->from(ModelMpStockMovementReason::$definition['table'] . '_lang')
            ->where('id_mpstock_mvt_reason=' . (int) $value . ' AND id_lang=' . (int) $this->id_lang);
        $name = \Tools::strtoupper($db->getValue($sql));

        return $name;
    }

    public function getSupplierName($value)
    {
        $db = \Db::getInstance();
        $sql = new \DbQuery();
        $sql->select('name')
            ->from(ModelSupplier::$definition['table'])
            ->where('id_supplier=' . (int) $value);
        $name = \Tools::strtoupper($db->getValue($sql));

        return $name;
    }

    public function getEmployeeName($value)
    {
        $db = \Db::getInstance();
        $sql = new \DbQuery();
        $sql->select('CONCAT(firstname, \' \', lastname)')
            ->from(ModelEmployee::$definition['table'])
            ->where('id_employee=' . (int) $value);
        $name = \Tools::strtoupper($db->getValue($sql));

        return $name;
    }

    public function addVat($value, $row)
    {
        $id_product = (int) $row['id_product'];
        $product = new \Product($id_product, false);
        $vat = (float) $product->getTaxesRate();
        $price = $value;
        $price_ti = $price + ($price * $vat / 100);

        return \Tools::displayPrice($price_ti, $this->currency->id);
    }

    public function getVariantName($id_product_attribute)
    {
        $db = \Db::getInstance();
        $sql = new \DbQuery();
        $sql->select('GROUP_CONCAT(al.name)')
            ->from('product_attribute', 'pa')
            ->innerJoin('product_attribute_combination', 'pac', 'pac.id_product_attribute = pa.id_product_attribute')
            ->innerJoin('attribute_lang', 'al', 'al.id_attribute = pac.id_attribute and al.id_lang=' . (int) $this->id_lang)
            ->where('pa.id_product_attribute=' . (int) $id_product_attribute);
        $name = \Tools::strtoupper($db->getValue($sql));

        return $name;
    }

    public function getProductName($value)
    {
        $db = \Db::getInstance();
        $sql = new \DbQuery();
        $sql->select('name')
            ->from('product_lang')
            ->where('id_product=' . (int) $value)
            ->where('id_lang=' . (int) $this->id_lang);
        $name = \Tools::strtoupper($db->getValue($sql));

        return $name;
    }

    public function getIdProductAttribute($ean13, $reference)
    {
        $db = \Db::getInstance();
        $sql = new \DbQuery();
        $sql->select('id_product_attribute')
            ->from('product_attribute')
            ->where('ean13=\'' . pSQL($ean13) . '\'')
            ->where('reference=\'' . pSQL($reference) . '\'');
        $id = (int) $db->getValue($sql);

        return $id;
    }

    public function getIdProduct($ean13, $reference)
    {
        $db = \Db::getInstance();
        $sql = new \DbQuery();
        $sql->select('id_product')
            ->from('product_attribute')
            ->where('ean13=\'' . pSQL($ean13) . '\'')
            ->where('reference=\'' . pSQL($reference) . '\'');
        $id = (int) $db->getValue($sql);

        return $id;
    }

    public function getImage($id_product)
    {
        return DisplayImageThumbnail::displayImage($id_product);
    }
}