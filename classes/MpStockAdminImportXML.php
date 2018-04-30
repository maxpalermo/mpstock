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

require_once _PS_MODULE_DIR_.'mpstock/classes/MpStockImportObjectModel.php';
require_once _PS_MODULE_DIR_.'mpstock/classes/MpStockObjectModel.php';

Class MpStockAdminImportXML
{
    protected $context;
    protected $id_lang;
    protected $id_shop;
    protected $id_employee;
    protected $module;
    protected $adminController;
    protected $link;
    protected $cookie;
    protected $localeInfo;
    protected $table_name_import = 'mp_stock_import';
    protected $table_name_movements = 'mp_stock';
    protected $locale_info = array();
    protected $rows = array();
    protected $report = array();
    protected $mpStockImport;
    protected $importErrors = array();


    public function __construct($module, $adminController)
    {
        $this->module = $module;
        $this->adminController = $adminController;
        $this->context = Context::getContext();
        $this->link = new LinkCore();
        $this->id_lang = (int)$this->context->language->id;
        $this->id_shop = (int)Context::getContext()->shop->id;
        $this->id_employee = (int)Context::getContext()->employee->id;        
        $this->cookie = Context::getContext()->cookie;
        $this->mpStockImport = new MpStockImportObjectModel();
        if (Context::getContext()->language->iso_code == 'it') {
            $this->localeInfo = array(
                'decimal_point' => ',',
                'thousands_sep' => '.'
            );
        } else {
            $this->localeInfo = array(
                'decimal_point' => '.',
                'thousands_sep' => ','
            );
        }
    }
    
    public function import()
    {
        /** Get attachment **/
        $file_upload = Tools::fileAttachment('input_file_import');
        /** Get filename **/
        $filename = Tools::strtolower($file_upload['name']);
        /** Accept only file with XML extension **/
        if (pathinfo($filename, PATHINFO_EXTENSION) != 'xml') {
            $this->adminController->addError($this->l('Please selecta valid xml file.'));
            return false;
        }
        /** Get file content **/
        $content = $file_upload['content'];
        /** Get XML **/
        $xml = simplexml_load_string($content);
        /** Get date movement **/
        $date_movement = $date = (string)$xml->movement_date;
        /** Get type movement **/
        $type_movement = (int)((string)$xml->movement_type);
        /** Get movement **/
        $movement = new MpStockMovementObjectModel($type_movement);
        /** Check type movement **/
        if (!$movement->record_exists) {
            $this->importErrors[] = sprintf(
                $this->module->l('Invalid document type: %d', get_class($this)),
                $type_movement
            );
            $filename = $this->adminController->module->getPath()
                .'report/report_'
                .$filename
                .'.txt';
            $file = fopen(
                $filename,
                'w'
            );
            if ($file) {
                foreach ($this->importErrors as $error) {
                    fwrite($file, print_r($error,1));
                }
            }
            fclose($file);
            chmod($filename, 0777);
            Context::getContext()->controller->addError(
                sprintf(
                    $this->module->l('Invalid document type: %d'),
                    $type_movement
                )
            );
            return true;
        }
        /** Get sign **/
        $sign = (string)$movement->sign;
        /** Insert file name in archive **/
        $this->insertMpStockImport($filename, $type_movement, $date_movement, $sign);
        if ($this->mpStockImport->id) {
            /** Get rows informations **/
            $result = $this->parseRows($xml->rows);
            if ($result) {
                /** Insert rows in archive **/
                $this->insertRows();
            }
        }
        if ($this->importErrors) {
            $filename = $this->adminController->module->getPath()
                .'report/report_'
                .$filename
                .'.txt';
            $file = fopen(
                $filename,
                'w'
            );
            if ($file) {
                foreach ($this->importErrors as $error) {
                    fwrite($file, print_r($error,1));
                }
            }
            fclose($file);
            chmod($filename, 0777);
        }
        Context::getContext()->controller->addConfirmation(
            sprintf(
                $this->module->l('File %s imported successfully'),
                $file_upload['name']
            )
        );
        return true;
    }
    
    private function insertRows()
    {
        foreach ($this->rows as $row) {
            $stock = new MpStockObjectModel();
            $stock->id_mp_stock_import = (int)$this->mpStockImport->id;
            $stock->id_mp_stock_type_movement = 0;
            $stock->id_mp_stock_exchange = 0;
            $stock->id_product = $row['id_product'];
            $stock->id_product_attribute = $row['id_product_attribute'];
            $stock->qty = $row['qty'];
            $stock->price = $row['price'];
            $stock->wholesale_price = $row['wholesale_price'];
            $stock->tax_rate = $row['tax_rate'];
            $stock->name = $row['name'];
            $stock->id_lang = $this->id_lang;
            $stock->id_shop = $this->id_shop;
            $stock->id_employee = $this->id_employee;
            $stock->date_movement = $this->mpStockImport->date_movement;
            $stock->sign = $this->mpStockImport->sign;
            $stock->date_add = date('Y-m-d H:i:s');
            try {
                $stock->save();
            } catch (Exception $ex) {
                $this->addToReportError($stock, $ex->getCode(), $ex->getMessage());
                $this->adminController->addError(
                    sprintf(
                        $this->l('Error inserting %s %s: %d %s'),
                        $stock->reference,
                        $stock->name,
                        $ex->getCode(),
                        $ex->getMessage()
                    )
                );
            }
        }
    }
    
    private function addToReportError($stock, $code, $message)
    {
        $this->importErrors[] = array(
            'stock' => $stock,
            'error_code' => $code,
            'error_message' => $message,
        );
    }
    
    private function parseRows(SimpleXMLElement $rows)
    {
        /** Prepare array insertion **/
        $output = array();
        /** Parse rows **/
        foreach ($rows->children() as $row) {
            $ean13 = trim((string)$row->ean13);
            $reference= trim((string)$row->reference);
            $qty = (int)(((string)$row->qty) * (int)$this->mpStockImport->sign);
            $date_movement = $this->mpStockImport->date_add;
            $price = (float)((string)$row->price);
            $wholesale_price = (float)((string)$row->wholesale_price);
            $extra_info = $this->getExtraInfo($ean13, $reference);
            $output[] = array(
                'ean13' => $ean13,
                'reference' => $reference,
                'qty' => $qty,
                'date_movement' => $date_movement,
                'id_product' => (int)$extra_info['id_product'],
                'id_product_attribute' => (int)$extra_info['id_product_attribute'],
                'tax_rate' => (float)$extra_info['tax_rate'],
                'name' => $extra_info['name'],
                'price' => (float)$price,
                'wholesale_price' => (float)$wholesale_price,
            );
        }
        $this->rows = $output;
        return true;
    }
    
    private function getExtraInfo($ean13, $reference)
    {
        $product_attribute = $this->getProductAttribute($ean13, $reference);
        if (!$product_attribute) {
            return false;
        }
        $tax_rate = $this->getTaxRateFromIdProduct($product_attribute['id_product']);
        $name = $this->getProductCombinationName($product_attribute['id_product_attribute']);
        return array(
            'id_product' => $product_attribute['id_product'],
            'id_product_attribute' => $product_attribute['id_product_attribute'],
            'tax_rate' => $tax_rate,
            'name' => $name,
        );
    }
    
    private function getProductCombinationName($id_product_attribute)
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('a.id_attribute')
            ->select('a.color')
            ->select('ag.position')
            ->select('al.name')
            ->from('attribute', 'a')
            ->innerJoin('attribute_group', 'ag', 'ag.id_attribute_group=a.id_attribute_group')
            ->innerJoin('attribute_lang', 'al', 'al.id_attribute=a.id_attribute')
            ->innerJoin('product_attribute_combination', 'pac', 'pac.id_attribute=a.id_attribute')
            ->where('al.id_lang='.(int)$this->id_lang)
            ->where('pac.id_product_attribute='.(int)$id_product_attribute)
            ->orderBy('ag.position');
        
        $name = array();
        $rows = $db->executeS($sql);
        if (!$rows) {
            $this->adminController->addError($db->getMsgError());
            $name = array();
        } else {
            foreach ($rows as $row) {
                $name[] = Tools::strtolower($row['name']);
            }
        }
        $name_str = implode(' ', $name);
        return MpStockTools::ucFirst($name);
    }
    
    private function getTaxRateFromIdProduct($id_product)
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('t.rate')
            ->from('tax', 't')
            ->innerJoin('tax_rule', 'tr', 't.id_tax=tr.id_tax')
            ->innerJoin('product', 'p', 'p.id_tax_rules_group=tr.id_tax_rules_group')
            ->where('p.id_product='.(int)$id_product);
        return (float)$db->getValue($sql);
    }


    private function getProductAttribute($ean13, $reference)
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('id_product')
            ->select('id_product_attribute')
            ->from('product_attribute')
            ->where('ean13 = \''.pSQL($ean13).'\'')
            ->where('reference = \''.pSQL($reference).'\'');
        $row = $db->getRow($sql);
        if (!$row) {
            $this->adminController->addError($db->getMsgError());
            return false;
        }
        return $row;
    }
    
    private function insertMpStockImport($filename, $type_movement, $date_movement, $sign)
    {
        /** create object **/
        $this->mpStockImport = new MpStockImportObjectModel();
        $this->mpStockImport->id_type_document = (int)$type_movement;
        $this->mpStockImport->sign = (int)$sign;
        $this->mpStockImport->filename = $filename;
        $this->mpStockImport->id_employee = (int)$this->id_employee;
        $this->mpStockImport->id_shop = (int)$this->id_shop;
        $this->mpStockImport->date_movement = $date_movement;
        $this->mpStockImport->date_add = date('Y-m-d H:i:s');
        /** Try insert record **/
        try {
            $this->mpStockImport->add();
        } catch (Exception $ex) {
            $this->adminController->addError(
                sprintf(
                    $this->l('Error inserting filename: %s.'),
                    $ex->getMessage()
                )
            );
            return false;
        }
        return (int)$this->mpStockImport->id;
    }
    
    /**
     * Non-static method which uses AdminController::translate()
     *
     * @param string  $string Term or expression in english
     * @param string|null $class Name of the class
     * @param bool $addslashes If set to true, the return value will pass through addslashes(). Otherwise, stripslashes().
     * @param bool $htmlentities If set to true(default), the return value will pass through htmlentities($string, ENT_QUOTES, 'utf-8')
     * @return string The translation if available, or the english default text.
     */
    private function l($string, $class = null, $addslashes = false, $htmlentities = true)
    {
        if ($class === null || $class == 'AdminTab') {
            $class = substr(get_class($this), 0, -10);
        } elseif (strtolower(substr($class, -10)) == 'controller') {
            /* classname has changed, from AdminXXX to AdminXXXController, so we remove 10 characters and we keep same keys */
            $class = substr($class, 0, -10);
        }
        return Translate::getAdminTranslation($string, $class, $addslashes, $htmlentities);
    }
}
