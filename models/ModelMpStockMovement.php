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
class ModelMpStockMovement extends AA_MpStockModelTemplate
{
    public $ref_movement;
    public $id_warehouse;
    public $id_supplier;
    public $document_number;
    public $document_date;
    public $id_order;
    public $id_order_detail;
    public $id_mpstock_mvt_reason;
    public $mvt_reason;
    public $id_product;
    public $id_product_attribute;
    public $reference;
    public $ean13;
    public $upc;
    public $stock_quantity_before;
    public $stock_movement;
    public $stock_quantity_after;
    public $price_te;
    public $wholesale_price_te;
    public $id_employee;
    public $date_add;
    public $date_upd;

    protected $context;
    protected $smarty;
    protected $module;

    public static $definition = [
        'table' => 'mpstock_movement',
        'primary' => 'id_mpstock_movement',
        'multilang' => false,
        'fields' => [
            'ref_movement' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => false,
            ],
            'id_warehouse' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => false,
            ],
            'id_supplier' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => false,
            ],
            'document_number' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
                'size' => 32,
                'required' => false,
            ],
            'document_date' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'required' => false,
                'datetime' => true,
            ],
            'id_order' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => false,
            ],
            'id_order_detail' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => false,
            ],
            'id_mpstock_mvt_reason' => [
                'type' => self::TYPE_INT,
                'validate' => 'isInt',
                'required' => true,
            ],
            'mvt_reason' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
                'size' => 255,
                'required' => false,
            ],
            'id_product' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => true,
            ],
            'id_product_attribute' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => true,
            ],
            'reference' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
                'size' => 32,
                'required' => false,
            ],
            'ean13' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
                'required' => false,
                'size' => 13,
            ],
            'upc' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
                'required' => false,
                'size' => 12,
            ],
            'stock_quantity_before' => [
                'type' => self::TYPE_INT,
                'validate' => 'isInt',
                'required' => true,
            ],
            'stock_movement' => [
                'type' => self::TYPE_INT,
                'validate' => 'isInt',
                'required' => true,
            ],
            'stock_quantity_after' => [
                'type' => self::TYPE_INT,
                'validate' => 'isInt',
                'required' => true,
            ],
            'price_te' => [
                'type' => self::TYPE_FLOAT,
                'validate' => 'isFLoat',
                'decimal' => true,
                'size' => '20,6',
                'required' => true,
            ],
            'wholesale_price_te' => [
                'type' => self::TYPE_FLOAT,
                'validate' => 'isFLoat',
                'decimal' => true,
                'size' => '20,6',
                'required' => true,
            ],
            'id_employee' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => true,
            ],
            'date_add' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'required' => true,
                'datetime' => true,
            ],
            'date_upd' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'required' => false,
                'timestamp' => true,
            ],
        ],
    ];

    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
        parent::__construct($id, $id_lang, $id_shop);

        $this->context = Context::getContext();
        $this->smarty = $this->context->smarty;
        $this->module = Module::getInstanceByName('mpstock');
    }

    public static function parseFloat($value)
    {
        $number = preg_replace('/[^\d\,\.\-]/', '', $value);
        if (is_numeric($number)) {
            return (float) $number;
        } else {
            $swap = str_replace('.', '', $number);
            $swap = str_replace(',', '.', $swap);
            if (is_numeric($swap)) {
                return (float) $swap;
            } else {
                return 0;
            }
        }
    }

    public static function isEmpty()
    {
        $db = Db::getInstance();
        $sql = 'select count(*) from ' . _DB_PREFIX_ . self::$definition['table'];
        $count = (int) $db->getValue($sql);
        if ($count) {
            return false;
        } else {
            return true;
        }
    }

    public function delete()
    {
        return false;
    }
}
