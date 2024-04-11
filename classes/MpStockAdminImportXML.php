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

require_once _PS_MODULE_DIR_ . 'mpstock/classes/MpStockImporterRowModel.php';


class MpStockAdminImportXML
{
    protected $context;
    protected $id_lang;
    protected $id_shop;
    protected $id_employee;
    protected $adminController;
    protected $link;
    protected $cookie;
    protected $localeInfo;
    protected $table_name_import = 'mp_stock_import';
    protected $table_name_movements = 'mp_stock';
    protected $locale_info = array();
    protected $rows = array();
    protected $mpStockImport;
    protected $importErrors = array();
    protected $id_mp_stock_type_movement;
    public $errors;
    private $module;
    private $content;
    private $filename;
    private $parent;
    private $sign;

    public function __construct($content, $filename, $parent)
    {
        $this->module = new MpStock();
        $this->parent = $parent;
        $this->content = $content;
        $this->filename = Tools::strtolower($filename);
        $this->errors = array();
        $this->context = Context::getContext();
        $this->link = new LinkCore();
        $this->id_lang = (int) $this->context->language->id;
        $this->id_shop = (int) Context::getContext()->shop->id;
        $this->id_employee = (int) Context::getContext()->employee->id;
        $this->cookie = Context::getContext()->cookie;
        $this->mpStockImport = new MpStockObjectModelImport();
        $this->localeInfo = MpStockTools::getLocaleInfo();
        $this->sign = 0;
    }

    public function parseContent()
    {
        /** Get XML **/
        $xml = simplexml_load_string($this->content);
        $this->xml = $xml;
        $this->parent->addNotification(
            array(
                'content xml' => print_r($xml, 1),
            )
        );
        /** Get date movement **/
        $date_movement = (string) $xml->movement_date;
        /** Get type movement **/
        $type_movement = (int) ((string) $xml->movement_type);
        /** Check if type moviment is correct **/
        $exist_movement = MpStockTools::existsTypeMovement($type_movement);
        if (!$exist_movement) {
            $this->parent->addError(
                sprintf(
                    $this->module->l('Type movement not valid %d.', get_class($this)),
                    $type_movement
                )
            );
            return false;
        }
        /** Get sign **/
        $sign = MpStockTools::getSign($type_movement);
        $this->sign = $sign;
        /** Get MpStockImport Class **/
        $parent = new MpStockObjectModelImport();
        $parent->id_type_document = $type_movement;
        $parent->sign = $sign;
        $parent->filename = $this->filename;
        $parent->date_movement = $date_movement;
        $parent->id_employee = $this->id_employee;
        try {
            $insert = $parent->save();
            $this->parent->addNotification(
                array(
                    'insert document' => (int) $insert,
                )
            );
        } catch (Exception $e) {
            $this->parent->addError(
                sprintf(
                    $this->module->l('Error %d while inserting document: %s.', get_class($this)),
                    $e->getCode(),
                    $e->getMessage()
                )
            );
            return false;
        }
        if (!$parent->id) {
            $this->errors[] = sprintf(
                $this->module->l('Error %d inserting document: %s', get_class($this)),
                Db::getInstance()->getNumberError(),
                Db::getInstance()->getMsgError()
            );
            return false;
        }
        /** Parse Rows **/
        $result = $this->parseRows();
        if ($result) {
            $rows = $this->rows;
        } else {
            $rows = array();
        }
        /**  Insert Rows **/
        foreach ($rows as $row) {
            $rowObj = new MpStockImporterRowModel($row, $this->parent);
            if ($rowObj->status) {
                $result = $rowObj->prepareRow(
                    0,
                    $parent->id,
                    0,
                    $this->id_shop,
                    $type_movement,
                    $date_movement,
                    date('Y-m-d H:i:s'),
                    $this->id_employee
                );
            }
        }
        if ($this->errors) {
            return false;
        } else {
            return true;
        }
    }

    /** Prepare Array of products row for parsing */
    private function parseRows()
    {
        /** Get XML rows **/
        $rows = $this->xml->rows;
        /** Prepare array insertion **/
        $output = array();
        /** Parse rows **/
        foreach ($rows->children() as $row) {
            $this->parent->addNotification('Parsing row:' . print_r($row, 1));
            $ean13 = trim((string) $row->ean13);
            $reference = trim((string) $row->reference);
            $qty = (int) (((string) $row->qty) * (int) $this->sign);
            $price = (float) ((string) $row->price);
            $wholesale_price = (float) ((string) $row->wholesale_price);
            $product = array(
                'ean13' => $ean13,
                'reference' => $reference,
                'qty' => $qty,
                'price' => (float) $price,
                'wholesale_price' => (float) $wholesale_price,
            );
            $output[] = $product;
        }
        $this->rows = $output;
        return true;
    }
}