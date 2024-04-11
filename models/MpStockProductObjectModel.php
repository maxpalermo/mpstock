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

class MpStockProductObjectModel extends ObjectModel
{
    public $id_warehouse;
    public $id_document;
    public $id_mpstock_mvt_reason;
    public $id_product;
    public $id_product_attribute;
    public $reference;
    public $ean13;
    public $upc;
    public $stock_quantity_before;
    public $stock_movement;
    public $price_te;
    public $wholesale_price_te;
    public $id_employee;
    public $date_add;
    public $date_upd;

    protected $context;
    protected $smarty;
    protected $module;

    public static $definition = array(
        'table' => 'mpstock_movement',
        'primary' => 'id_mpstock_movement',
        'multilang' => false,
        'fields' => array(
            'id_warehouse' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => false,
            ),
            'id_document' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => false,
            ),
            'id_mpstock_mvt_reason' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isInt',
                'required' => true,
            ),
            'id_product' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => true,
            ),
            'id_product_attribute' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => true,
            ),
            'reference' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
                'size' => 32,
                'required' => false,
            ),
            'ean13' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
                'required' => false,
                'size' => 13
            ),
            'upc' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
                'required' => false,
                'size' => 12
            ),
            'stock_quantity_before' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isInt',
                'required' => true,
            ),
            'stock_movement' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isInt',
                'required' => true,
            ),
            'price_te' => array(
                'type' => self::TYPE_FLOAT,
                'validate' => 'isFLoat',
                'decimal' => true,
                'size' => '20,6',
                'required' => true,
            ),
            'wholesale_price_te' => array(
                'type' => self::TYPE_FLOAT,
                'validate' => 'isFLoat',
                'decimal' => true,
                'size' => '20,6',
                'required' => true,
            ),
            'id_employee' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => true,
            ),
            'date_add' => array(
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'required' => true,
                'datetime' => true,
            ),
            'date_upd' => array(
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'required' => false,
                'timestamp' => true,
            ),
        ),
    );

    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
        if (!$id_shop) {
            $this->id_shop = (int) Context::getContext()->shop->id;
        } else {
            $this->id_shop = (int) $id_shop;
        }
        if (!$id_lang) {
            $this->id_lang = Context::getContext()->language->id;
        } else {
            $this->id_lang = (int) $id_lang;
        }
        parent::__construct($id, $this->id_lang, $this->id_shop);
        $this->context = Context::getContext();
        $this->smarty = $this->context->smarty;
        $this->module = Module::getInstanceByName('mpstock');
    }

    public function saveValues()
    {
        $id_shop = (int) $this->id_shop;
        $number_document = Tools::getValue('number_document');
        $date_document = Tools::getValue('date_document');
        $id_supplier = (int) Tools::getValue('id_supplier');
        $date_add = date('Y-m-d H:i:s');

        if (!$number_document) {
            return $this->module->l('Please select a valid number document');
        }
        if (!$date_document) {
            return $this->module->l('Please select a valid date document');
        }
        if (!$id_supplier) {
            return $this->module->l('Please select a valid supplier');
        }

        $this->id_shop = (int) $id_shop;
        $this->number_document = $number_document;
        $this->date_document = $date_document;
        $this->id_supplier = (int) $id_supplier;
        $this->tot_qty = 0;
        $this->tot_document_te = 0;
        $this->tot_document_taxes = 0;
        $this->tot_document_ti = 0;
        $this->date_add = $date_add;
        $result = $this->save();

        if ($result) {
            return true;
        } else {
            return Db::getInstance()->getMsgError();
        }
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
        $sql = "select count(*) from " . _DB_PREFIX_ . self::$definition['table'];
        $count = (int) $db->getValue($sql);
        if ($count) {
            return false;
        } else {
            return true;
        }
    }

    public static function dropTable()
    {
        Db::getInstance()->execute('DROP TABLE ' . _DB_PREFIX_ . self::$definition['table']);
    }

    public static function truncateTable()
    {
        Db::getInstance()->execute('TRUNCATE TABLE ' . _DB_PREFIX_ . self::$definition['table']);
    }

    /**
     * Get table definition and create a table in the database
     * @param  bool $execute_query Set the return type
     * @return mixed if $execute_query is true returns the query result, 
     *         otherwise returns the query string 
     */
    public static function install($execute_query = true)
    {
        $db = Db::getInstance();
        $sql = array();
        $primary = self::$definition['primary'];
        $sql_start = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . self::$definition['table'] . "` (";
        $sql['primary'] = "`" . $primary . "` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY";
        foreach (self::$definition['fields'] as $key => $field) {
            if ($key == $primary) {
                continue;
            }
            if ($field['required']) {
                $null = " NOT NULL";
            } else {
                $null = " NULL";
            }
            switch ($field['type']) {
                case self::TYPE_INT:
                    $sql[$key] = "`" . $key . "` INT(11)" . $null;
                    break;
                case self::TYPE_BOOL:
                    $sql[$key] = "`" . $key . "` TINYINT(1)" . $null;
                    break;
                case self::TYPE_FLOAT:
                    if ($field['decimal']) {
                        $sql[$key] = "`" . $key . "` DECIMAL(" . $field['size'] . ")" . $null;
                    } else {
                        $sql[$key] = "`" . $key . "` FLOAT" . $null;
                    }
                    break;
                case self::TYPE_STRING:
                    if ($field['text']) {
                        $sql[$key] = "`" . $key . "` TEXT" . $null;
                    } else {
                        $sql[$key] = "`" . $key . "` VARCHAR(" . $field['size'] . ")" . $null;
                    }
                    break;
                case self::TYPE_DATE:
                    if ($field['datetime']) {
                        $sql[$key] = "`" . $key . "` DATETIME" . $null;
                    } elseif ($field['timestamp']) {
                        $sql[$key] = "`" . $key . "` TIMESTAMP" . $null;
                    } else {
                        $sql[$key] = "`" . $key . "` DATE" . $null;
                    }
                    break;
                default:
                    break;
            }
        }
        $sql_end = ") ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=utf8;";

        $query = $sql_start . implode(",", $sql) . $sql_end;

        if ($execute_query) {
            return Db::getInstance()->execute($query);
        } else {
            return $query;
        }
    }

    public static function getPath()
    {
        return _PS_MODULE_DIR_ . 'mpstock/';
    }

    public static function getURL()
    {
        $shop = Context::getContext()->shop;
        $url = $shop->getBaseURI();
        return $url . 'modules/mpstock/';
    }

    public function getEmployee()
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('firstname')
            ->select('lastname')
            ->from('employee')
            ->where('id_employee = ' . (int) $this->id_employee);
        $row = $db->getRow($sql);
        if ($row) {
            return $row['firstname'] . ' ' . $row['lastname'];
        } else {
            return "";
        }
    }

    public static function getNameProductWithCombinations($id_product_attribute, $id_product)
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $id_lang = Context::getContext()->language->id;
        $sql->select('id_attribute')
            ->from('product_attribute_combination')
            ->where('id_product_attribute = ' . (int) $id_product_attribute);
        $name = self::getNameProduct($id_product);
        $attributes = $db->executeS($sql);
        foreach ($attributes as $attribute) {
            $attr = new AttributeCore($attribute['id_attribute']);
            $name .= ' ' . $attr->name[(int) $id_lang];
        }

        return $name;
    }

    public static function getStockQuantity($id_product_attribute, $id_product)
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $id_lang = Context::getContext()->language->id;
        $sql->select('quantity')
            ->from('stock_available')
            ->where('id_product_attribute = ' . (int) $id_product_attribute);

        return (int) $db->getValue($sql);
    }

    public static function getProductTaxRate($id_product)
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('t.rate')
            ->from('tax', 't')
            ->innerJoin('tax_rule', 'tr', 'tr.id_tax=t.id_tax')
            ->innerJoin('product', 'p', 'p.id_tax_rules_group=tr.id_tax_rules_group')
            ->where('p.id_product=' . (int) $id_product);
        return (float) $db->getValue($sql);
    }

    public static function getAttributeProduct($id_product_attribute, $id_product)
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $id_lang = Context::getContext()->language->id;
        $sql->select('id_attribute')
            ->from('product_attribute_combination')
            ->where('id_product_attribute = ' . (int) $id_product_attribute);
        $name = self::getNameProduct($id_product);
        $attributes = $db->executeS($sql);
        foreach ($attributes as $attribute) {
            $attr = new AttributeCore($attribute['id_attribute']);
            $name .= ' ' . $attr->name[(int) $id_lang];
        }

        return $name;
    }

    public static function getNameProduct($id_product)
    {
        $id_lang = (int) Context::getContext()->language->id;
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('name')
            ->from('product_lang')
            ->where('id_product = ' . (int) $id_product)
            ->where('id_lang = ' . (int) $id_lang);
        $name = $db->getValue($sql);

        return $name;
    }

    public static function getOtherAttributes($id_product_attribute)
    {
        $output = array(
            'ean13' => '',
            'upc' => '',
            'reference' => '',
            'id_supplier' => 0,
            'price_te' => 0,
            'wholesale_price_te' => 0,
            'tax_rate' => 0,
            'stock_quantity_before' => 0,
        );
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('pa.ean13 as pa_ean13')
            ->select('pa.upc as pa_upc')
            ->select('pa.reference as pa_reference')
            ->select('pa.id_product as id_other_product')
            ->select('p.ean13 as p_ean13')
            ->select('p.upc as p_upc')
            ->select('p.reference as p_reference')
            ->select('p.id_supplier')
            ->select('p.price')
            ->select('p.wholesale_price')
            ->select('sa.quantity as snap')
            ->from('product_attribute', 'pa')
            ->innerJoin('product', 'p', 'pa.id_product=p.id_product')
            ->innerJoin('stock_available', 'sa', 'sa.id_product_attribute=' . (int) $id_product_attribute)
            ->where('pa.id_product_attribute=' . (int) $id_product_attribute);
        $result = $db->getRow($sql);
        if ($result) {
            $output['ean13'] = !empty($result['pa_ean13']) ? $result['pa_ean13'] : $result['p_ean13'];
            $output['upc'] = !empty($result['pa_upc']) ? $result['pa_upc'] : $result['p_upc'];
            $output['reference'] = !empty($result['pa_reference']) ? $result['pa_reference'] : $result['p_reference'];
            $output['id_supplier'] = !empty($result['id_supplier']) ? $result['id_supplier'] : 0;
            $output['tax_rate'] = self::getProductTaxRate((int) $result['id_other_product']);
            $output['name'] = self::getAttributeProduct($id_product_attribute, $result['id_other_product']);
            $output['price_te'] = $result['price'];
            $output['wholesale_price_te'] = $result['wholesale_price'];
            $output['stock_quantity_before'] = (int) $result['snap']; //self::getStock($id_product_attribute);
        }
        return $output;
    }

    public static function getSign($id_movement)
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('sign')
            ->from('mpstock_mvt_reason')
            ->where('id_mpstock_mvt_reason=' . (int) $id_movement);
        $sign = (int) $db->getValue($sql);
        if ($sign) {
            return -1;
        } else {
            return 1;
        }
    }

    public static function getStock($id_product_attribute)
    {
        $db = Db::getInstance();
        $sql = "select quantity from " . _DB_PREFIX_ . "stock_available where id_product_attribute=" . (int) $id_product_attribute;
        return (int) $db->getValue($sql);
    }

    public static function getProduct($ean13, $reference)
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('id_product')
            ->select('id_product_attribute')
            ->from('product_attribute')
            ->where('`ean13` = \'' . pSQL($ean13) . '\' AND `reference`=\'' . pSQL($reference) . '\'');
        $prod = $db->getRow($sql);
        if (!$prod) {
            return array(
                'id_product' => 0,
                'id_product_attribute' => 0,
                'name' => '',
            );
        }
        $name = self::getAttributeProduct((int) $prod['id_product_attribute'], (int) $prod['id_product']);
        return array(
            'id_product' => (int) $prod['id_product'],
            'id_product_attribute' => (int) $prod['id_product_attribute'],
            'name' => $name,
        );
    }

    public static function getSupplier($id_product)
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('s.id_supplier')
            ->select('s.name')
            ->from('supplier', 's')
            ->innerJoin('product', 'p', 'p.id_supplier=s.id_supplier')
            ->where('p.id_product=' . (int) $id_product);
        return $db->getRow($sql);
    }

    public function updateDoc()
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('sum(stock_movement) as `tot_qty`')
            ->select('sum(price_te*stock_movement) as `tot_price`')
            ->from('mpstock_movement')
            ->where('id_document=' . (int) $this->id_document);
        $row = $db->getRow($sql);
        if ($row) {
            $db->update(
                'mpstock_document',
                array(
                    'tot_qty' => $row['tot_qty'],
                    'tot_document_te' => $row['tot_price'],
                    'tot_document_ti' => $row['tot_price'],
                    'tot_document_taxes' => 0,
                ),
                'id_mpstock_document=' . (int) $this->id_document
            );
            $sql = new DbQueryCore();
            $sql->select('tot_qty')
                ->select('tot_document_ti')
                ->from('mpstock_document')
                ->where('id_mpstock_document=' . (int) $this->id_document);
            $tots = $db->getRow($sql);
            return $tots;
        } else {
            return array();
        }
    }

    public function updateQty()
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();

        $stock = $this->getStockQuantity(
            $this->id_product_attribute,
            $this->id_product
        );
        $qty = $stock + $this->stock_movement;

        $db->update(
            'stock_available',
            array(
                'quantity' => (int) $qty,
            ),
            'id_product_attribute=' . (int) $this->id_product_attribute
        );
        $db->update(
            'product_attribute',
            array(
                'quantity' => (int) $qty,
            ),
            'id_product_attribute=' . (int) $this->id_product_attribute
        );
        $sql->select('sum(quantity)')
            ->from('stock_available')
            ->where('id_product=' . (int) $this->id_product)
            ->where('id_product_attribute!=0');
        $sum = (int) $db->getValue($sql);
        $db->update(
            'stock_available',
            array(
                'quantity' => $sum
            ),
            'id_product=' . (int) $this->id_product . ' and id_product_attribute=0'
        );
        return $this->getStockQuantity(
            $this->id_product_attribute,
            $this->id_product
        );
    }

    public function delete()
    {
        $this->stock_quantity_before = -$this->stock_quantity_before;
        $this->stock_movement = -$this->stock_movement;
        $this->updateQty();
        parent::delete();
    }
}