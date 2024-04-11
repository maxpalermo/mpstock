<?php

class ModelMpStockProductAlignment extends AA_MpStockModelTemplate
{
    public $id;
    public $id_product;
    public $id_product_attribute;
    public $stock_before;
    public $stock_alignment;
    public $stock_quantity;
    public $reason;
    public $date_add;
    public $date_upd;

    public static $definition = [
        'table' => 'mp_stock_product_alignment',
        'primary' => 'id_mp_stock_product_alignment',
        'fields' => [
            'id_product' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => true
            ],
            'id_product_attribute' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => true
            ],
            'stock_before' => [
                'type' => self::TYPE_INT,
                'validate' => 'isInt',
                'required' => true
            ],
            'stock_alignment' => [
                'type' => self::TYPE_INT,
                'validate' => 'isInt',
                'required' => true
            ],
            'stock_quantity' => [
                'type' => self::TYPE_INT,
                'validate' => 'isInt',
                'required' => true
            ],
            'reason' => [
                'type' => self::TYPE_STRING,
                'size' => 1024 * 1024,
                'validate' => 'isString',
                'required' => true,
            ],
            'date_add' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'required' => true,
            ],
            'date_upd' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'required' => true,
            ]
        ],
    ];
}