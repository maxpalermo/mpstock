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

class MpStockAdminImportCSV
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
    protected $mpStockImport;
    protected $importErrors = array();
    protected $id_mp_stock_type_movement;
    private $columns_header;


    public function __construct($module)
    {
        $this->module = $module;
        $this->context = Context::getContext();
        $this->link = new LinkCore();
        $this->id_lang = (int) $this->context->language->id;
        $this->id_shop = (int) Context::getContext()->shop->id;
        $this->id_employee = (int) Context::getContext()->employee->id;
        $this->cookie = Context::getContext()->cookie;
        $this->mpStockImport = new MpStockObjectModelImport();
        $this->localeInfo = MpStockTools::getLocaleInfo();
    }

    public function getImportErrors()
    {
        return $this->importErrors;
    }

    public function import()
    {
        /** Get attachment **/
        $file_upload = Tools::fileAttachment('input_file_import');
        /** Get filename **/
        $filename = Tools::strtolower($file_upload['name']);
        /** Get file content **/
        $content = $file_upload['content'];
        /** Split in rows  **/
        $rows = explode(PHP_EOL, $content);
        if (!$rows) {
            $this->importErrors[] = $this->module->l('No rows to parse, please check your CSV format.', get_class($this));
            return false;
        }
        /** Get header */
        $header = array_shift($rows);
        /** Get columns **/
        $this->columns_header = explode(';', Tools::strtolower($header));
        if (!$this->columns_header) {
            $this->importErrors[] = $this->module->l('No columns to parse, please check your CSV format.', get_class($this));
            return false;
        }
        foreach ($this->columns_header as &$col) {
            $col = trim(Tools::strtolower($col));
        }
        /** Check if there are all columns **/
        $headers = array(
            'movement_type',
            'movement_date',
            'ean13',
            'reference',
            'qty',
            'price',
            'wholesale_price',
        );
        if (array_diff($headers, $this->columns_header)) {
            $this->importErrors[] = $this->module->l('Columns headers do not match, please check your CSV format.', get_class($this));
            return false;
        }
        /** Prepare document */
        $document = array();
        foreach ($rows as $row) {
            $columns = explode(';', $row);
            $output_row = array();
            foreach ($columns as $key => $value) {
                $output_row[$this->columns_header[$key]] = $value;
            }
            $document[] = $output_row;
        }
        /** Import document **/
        /** Get movement from first row **/
        $row = $document[0];
        /** Get date movement **/
        if (!isset($row['movement_date'])) {
            return false;
        }
        $date_movement = (string) $row['movement_date'];
        /** Get type movement **/
        if (!isset($row['movement_type'])) {
            return false;
        }
        $type_movement = (int) $row['movement_type'];
        $movement = new MpStockObjectModelTypeMovement($type_movement);
        if (!$movement->id) {
            $this->importErrors[] = sprintf(
                $this->module->l('Invalid document type: %d', get_class($this)),
                $type_movement
            );
            Context::getContext()->controller->addError(
                sprintf(
                    $this->module->l('Invalid document type: %d', get_class($this)),
                    $type_movement
                )
            );
            return false;
        }
        /** Set id type movement */
        $this->id_mp_stock_type_movement = $type_movement;
        /** Get sign **/
        $sign = (int) $movement->sign;
        /** Insert file name in archive **/
        $this->insertMpStockImport($filename, $type_movement, $date_movement, $sign);

        foreach ($document as $row) {
            /** Get ean13 **/
            if (!isset($row['ean13'])) {
                continue;
            }
            $ean13 = (string) $row['ean13'];
            /** Get reference **/
            if (!isset($row['reference'])) {
                continue;
            }
            $reference = (string) $row['reference'];
            /** Get quantity */
            if (!isset($row['qty'])) {
                continue;
            }
            $qty = abs((int) $row['qty']);
            /** get Price **/
            if (!isset($row['price'])) {
                continue;
            }
            $price = (float) $row['price'];
            /** get Wholesale price **/
            if (!isset($row['wholesale_price'])) {
                continue;
            }
            $wholesale_price = (float) $row['wholesale_price'];
            /** Get Extra info **/
            $extra_info = $this->getExtraInfo($ean13, $reference);
            /** Create product array */
            $product = array(
                'ean13' => $ean13,
                'reference' => $reference,
                'qty' => $qty,
                'date_movement' => $date_movement,
                'id_product' => (int) $extra_info['id_product'],
                'id_product_attribute' => (int) $extra_info['id_product_attribute'],
                'tax_rate' => (float) $extra_info['tax_rate'],
                'name' => $extra_info['name'],
                'price' => (float) $price,
                'wholesale_price' => (float) $wholesale_price,
            );
            /** Insert Row **/
            $this->insertRow($product);
        }
        if ($this->importErrors) {
            $this->importErrors[] = $this->module->l('Errors during import.', get_class($this));
            return false;
        } else {
            return true;
        }
    }

    private function insertRow($row)
    {
        $flag_error = false;
        /** Get Object Model **/
        $stock = new MpStockObjectModel($this->module);
        $stock->id_mp_stock_import = (int) $this->mpStockImport->id;
        $stock->id_mp_stock_type_movement = $this->id_mp_stock_type_movement;
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
            $result = $stock->save();
            if (!$result) {
                $this->importErrors[] = sprintf(
                    $this->module->l('Error %s saving product (%d) %s.', get_class($this)),
                    Db::getInstance()->getMsgError(),
                    $row['id_product_attribute'],
                    $row['name']
                );
                $flag_error = true;
            }
        } catch (Exception $ex) {
            $this->importErrors[] = sprintf(
                $this->module->l('Error %s during import of product %s.', get_class($this)),
                $ex->getMessage(),
                $stock->name
            );
            $flag_error = true;
        }

        if ($flag_error) {
            $this->importErrors[] = $this->module->l('MpStockObjectModel error inserting product.', get_class($this));
            return false;
        } else {
            return true;
        }
    }

    private function getExtraInfo($ean13, $reference)
    {
        $product_attribute = $this->getProductAttribute($ean13, $reference);
        if (!$product_attribute) {
            $this->importErrors[] = sprintf(
                $this->module->l('Product %s with Ean13 %s not found.'),
                $reference,
                $ean13
            );
            return false;
        }
        $tax_rate = MpStockTools::getTaxRateFromIdProduct($product_attribute['id_product']);
        $name = MpStockTools::getProductCombinationName($product_attribute['id_product_attribute']);
        if (is_array($name)) {
            $this->importErrors[] = sprintf(
                $this->module->l('Product %s with Ean13 %s has no name. Error: %s', get_class($this)),
                $reference,
                $ean13,
                implode(',', $name['errors'])
            );
            return array(
                'id_product' => $product_attribute['id_product'],
                'id_product_attribute' => $product_attribute['id_product_attribute'],
                'tax_rate' => $tax_rate,
                'name' => $name['name'],
                'errors' => $name['errors'],
            );
        } else {
            return array(
                'id_product' => $product_attribute['id_product'],
                'id_product_attribute' => $product_attribute['id_product_attribute'],
                'tax_rate' => $tax_rate,
                'name' => $name,
                'errors' => array(),
            );
        }
    }

    private function getProductAttribute($ean13, $reference)
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('id_product')
            ->select('id_product_attribute')
            ->from('product_attribute')
            ->where('ean13 = \'' . pSQL($ean13) . '\'')
            ->where('reference = \'' . pSQL($reference) . '\'');
        $row = $db->getRow($sql);
        if (!$row) {
            $this->importErrors[] = $db->getMsgError();
            return false;
        }
        return $row;
    }

    private function insertMpStockImport($filename, $type_movement, $date_movement, $sign)
    {
        /** create object **/
        $this->mpStockImport = new MpStockObjectModelImport();
        $this->mpStockImport->id_type_document = (int) $type_movement;
        $this->mpStockImport->sign = (int) $sign;
        $this->mpStockImport->filename = $filename;
        $this->mpStockImport->id_employee = (int) $this->id_employee;
        $this->mpStockImport->id_shop = (int) $this->id_shop;
        $this->mpStockImport->date_movement = $date_movement;
        $this->mpStockImport->date_add = date('Y-m-d H:i:s');
        /** Try insert record **/
        try {
            $this->mpStockImport->add();
        } catch (Exception $ex) {
            $this->importErrors[] = sprintf(
                $this->module->l(
                    "Error %s during import of %s",
                    get_class($this)
                ),
                $ex->getMessage(),
                $filename
            );
            return false;
        }
        return (int) $this->mpStockImport->id;
    }
}