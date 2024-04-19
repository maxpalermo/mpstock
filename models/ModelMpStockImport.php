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

class ModelMpStockImport extends AA_MpStockModelTemplate
{
    public $filename;
    public $movement_date;
    public $movement_type;
    public $import_type;
    public $sign;
    public $ean13;
    public $reference;
    public $stock_before;
    public $quantity;

    public $stock_after;
    public $price;
    public $wholesale_price;
    public $loaded;
    public $id_employee;
    public $date_add;
    public $date_upd;

    public static $definition = [
        'table' => 'mpstock_import',
        'primary' => 'id_mpstock_import',
        'multilang' => false,
        'fields' => [
            'filename' => [
                'type' => self::TYPE_STRING,
                'size' => 255,
                'validate' => 'isString',
                'required' => true,
            ],
            'movement_date' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'required' => false,
                'timestamp' => true,
            ],
            'movement_type' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => false,
            ],
            'import_type' => [
                'type' => self::TYPE_STRING,
                'size' => 16,
                'validate' => 'isString',
                'required' => true,
            ],
            'sign' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => true,
                'default' => '0',
            ],
            'ean13' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
                'required' => false,
                'size' => 13,
            ],
            'reference' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
                'size' => 32,
                'required' => false,
            ],
            'stock_before' => [
                'type' => self::TYPE_INT,
                'validate' => 'isInt',
                'required' => false,
            ],
            'quantity' => [
                'type' => self::TYPE_INT,
                'validate' => 'isInt',
                'required' => true,
            ],
            'stock_after' => [
                'type' => self::TYPE_INT,
                'validate' => 'isInt',
                'required' => false,
            ],
            'price' => [
                'type' => self::TYPE_FLOAT,
                'validate' => 'isFLoat',
                'decimal' => true,
                'size' => '20,6',
                'required' => true,
            ],
            'wholesale_price' => [
                'type' => self::TYPE_FLOAT,
                'validate' => 'isFLoat',
                'decimal' => true,
                'size' => '20,6',
                'required' => true,
            ],
            'loaded' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => false,
                'default' => '0',
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
    }

    public static function getMovementsByFilename($filename)
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('*')
            ->from(self::$definition['table'])
            ->where('filename = \'' . pSQL($filename) . '\'')
            ->orderBy(self::$definition['primary']);
        $rows = $db->executeS($sql);
        if ($rows) {
            return $rows;
        }

        return [];
    }

    public static function getMovementsByDate($date)
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('*')
            ->from(self::$definition['table'])
            ->where('movement_date = ' . pSQL($date) . '\'')
            ->orderBy(self::$definition['primary']);
        $rows = $db->executeS($sql);
        if ($rows) {
            return $rows;
        }

        return [];
    }

    public static function getMovementsByType($type)
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('*')
            ->from(self::$definition['table'])
            ->where('movement_type = ' . (int) $type . '\'')
            ->orderBy(self::$definition['primary']);
        $rows = $db->executeS($sql);
        if ($rows) {
            return $rows;
        }

        return [];
    }

    public static function getMovementsByIdEmployee($sign)
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('*')
            ->from(self::$definition['table'])
            ->where('sign = ' . (int) $sign . '\'')
            ->orderBy(self::$definition['primary']);
        $rows = $db->executeS($sql);
        if ($rows) {
            return $rows;
        }

        return [];
    }

    public static function fileExists($filename)
    {
        return count(self::getMovementsByFilename($filename)) > 0;
    }

    public static function updateStock($id_mpstock_movement, $stock_before, $stock_after, $id_employee = 0)
    {
        if (!$id_employee) {
            $id_employee = (int) Context::getContext()->employee->id;
        }

        $sql = 'UPDATE `' . _DB_PREFIX_ . 'mpstock_import`'
            . ' SET `stock_before` = ' . (int) $stock_before . ','
            . '`stock_after` = ' . (int) $stock_after . ','
            . '`id_employee` = ' . (int) $stock_after . ','
            . '`loaded` = 1,'
            . '`date_upd` = \'' . date('Y-m-d H:i:s') . '\''
            . ' WHERE `id_mpstock_import` = ' . (int) $id_mpstock_movement;

        return Db::getInstance()->execute($sql);
    }
}