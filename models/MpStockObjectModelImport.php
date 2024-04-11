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

class MpStockObjectModelImport extends ObjectModelCore
{
    public static $definition = array(
        'table' => 'mp_stock_import',
        'primary' => 'id_mp_stock_import',
        'multilang' => false,
        'fields' => array(
            'id_type_document' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => 'true',
            ),
            'sign' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isInt',
                'required' => 'true',
            ),
            'filename' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
                'required' => 'true',
            ),
            'id_shop' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => 'true',
            ),
            'id_employee' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => 'true',
            ),
            'date_movement' => array(
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'required' => 'true',
            ),
            'date_add' => array(
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'required' => 'true',
            ),
        ),
    );
    
    /**@var int Id table **/
    public $id_mp_stock_import;
    /**@var int Id type document **/
    public $id_type_document;
    /**@var int sign **/
    public $sign;
    /**@var string import file name **/
    public $filename;
    /**@var int Id language **/
    public $id_lang;
    /**@var int Id shop **/
    public $id_shop;
     /**@var int Id employee **/
    public $id_employee;
     /**@var date date creation **/
    public $date_movement;
     /**@var date date creation **/
    public $date_add;
    
    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
        if (!$id_lang) {
            $this->id_lang = (int)ContextCore::getContext()->language->id;
        }
        if (!$id_shop) {
            $this->id_shop = (int)ContextCore::getContext()->shop->id;
        }
        parent::__construct($id, $id_lang, $id_shop);
    }

    public function getIdMovements()
    {
        $db = Db::getInstance();
        $sql = "select id_mp_stock from "._DB_PREFIX_."mp_stock where id_mp_stock_import=".(int)$this->id;
        $result = $db->executeS($sql);
        $output = array();
        foreach ($result as $row) {
            $output[] = (int)$row['id_mp_stock'];
        }
        return $output;
    }

    public function getObjectMovements()
    {
        $id_movements = $this->getIdMovements();
        $output = array();
        foreach ($id_movements as $id) {
            $object = new MpStockObjectModel((int)$id);
            $output[] = $object;
        }
        return $output;
    }

    public function delete()
    {
        return parent::delete();
    }
}
