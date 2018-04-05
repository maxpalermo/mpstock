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

Class MpStockClassObject extends ObjectModelCore
{
    /** @var bool if set, movement has another product */
    public $id_mp_stock_exchange;
    /** @var int product id */
    public $id_product;
    /** @var int product attribute id */
    public $id_product_attribute;
    /** @var int type movement from table mp_stock_type movements, 0 if movement has imported */
    public $id_mp_stock_type_movement;
    /** @var int product quantity */
    public $qty;
    /** @var float product price */
    public $price;
    /** @var float product tax rate */
    public $tax_rate;
    /** @var date if movement has imported, set the date of file xml */
    public $date_movement;
    /** @var int sign of inserted movement */
    public $sign;
    /** @var timestamp of inserted movement */
    public $date_add;
    /** @var int reference to employee */
    public $id_employee;
    /** @var int reference to shop */
    public $id_shop;
    /** @var int reference to language */
    public $id_lang;
    /** @var string errorMessage last error message */
    public $errorMessage;
    
    public static $definition = array(
        'table' => 'mp_stock',
        'primary' => 'id_mp_stock',
        'multilang' => false,
        'fields' => array(
            'id_mp_stock_exchange' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => 'true',
            ),
            'id_shop' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => 'true',
            ),
            'id_product' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => 'true',
            ),
            'id_product_attribute' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => 'true',
            ),
            'id_mp_stock_type_movement' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => 'true',
            ),
            'qty' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isInt',
                'required' => 'true',
            ),
            'price' => array(
                'type' => self::TYPE_FLOAT,
                'validate' => 'isPrice',
                'required' => 'true',
            ),
            'tax_rate' => array(
                'type' => self::TYPE_FLOAT,
                'validate' => 'isFloat',
                'required' => 'true',
            ),
            'date_movement' => array(
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'required' => 'false',
            ),
            'sign' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isInt',
                'required' => 'true',
            ),
            'date_add' => array(
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'required' => 'true',
            ),
            'id_employee' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => 'true',
            ),
        ),
    );
    
    public function __construct($id = null, $id_lang = null, $id_shop = null) {
        if (!$id_shop) {
            $this->id_shop = (int)Context::getContext()->shop->id;
        } else {
            $this->id_shop = (int)$id_shop;
        }
        if (!$id_lang) {
            $this->id_lang = Context::getContext()->language->id;
        } else {
            $this->id_lang = (int)$id_lang;
        }
        parent::__construct($id, $this->id_lang, $this->id_shop);
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
    protected function l($string, $class = null, $addslashes = false, $htmlentities = true)
    {
        if ($class === null || $class == 'AdminTab') {
            $class = substr(get_class($this), 0, -10);
        } elseif (strtolower(substr($class, -10)) == 'controller') {
            /* classname has changed, from AdminXXX to AdminXXXController, so we remove 10 characters and we keep same keys */
            $class = substr($class, 0, -10);
        }
        return Translate::getAdminTranslation($string, $class, $addslashes, $htmlentities);
    }
    
    public static function getIdMovementByExchangeId($id_stock_exchange)
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('id_mp_stock')
            ->from(self::$definition['table'])
            ->where('id_mp_stock_exchange='.(int)$id_stock_exchange);
        $value = (int)$db->getValue($sql);
        return $value;
    }
    
    public function getTaxRate($id_product)
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        
        $sql->select('t.rate')
            ->from('tax', 't')
            ->innerJoin('tax_rule', 'tr', 'tr.id_tax=t.id_tax')
            ->innerJoin('tax_rules_group', 'trl', 'trl.id_tax_rules_group=tr.id_tax_rules_group')
            ->innerJoin('product', 'p', 'p.id_tax_rules_group=trl.id_tax_rules_group')
            ->where('p.id_product='.(int)$id_product);
        
        return (float)$db->getValue($sql);
    }
    
    public function getProductAttributes($id_product)
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        
        $sql->select('pa.id_product_attribute')
            ->from('product_attribute', 'pa')
            ->where('pa.id_product='.(int)$id_product)
            ->orderBy('pa.id_product_attribute');
        
        $result = $db->executeS($sql);
        if ($result) {
            foreach ($result as &$row) {
                $sql_row = new DbQueryCore();
                $sql_row->select('al.name')
                    ->from('attribute_lang', 'al')
                    ->innerJoin('product_attribute_combination', 'pac', 'pac.id_attribute=al.id_attribute')
                    ->where('al.id_lang='.(int)$this->id_lang)
                    ->where('pac.id_product_attribute='.(int)$row['id_product_attribute'])
                    ->orderBy('al.id_attribute');
                PrestaShopLoggerCore::addLog($sql_row->__toString());
                $attributes = $db->executeS($sql_row);
                if ($attributes) {
                    $names = array();
                    foreach ($attributes as $attribute) {
                        $names[] = $attribute['name'];
                    }
                    $row['name'] = implode(' - ', $names);
                } else {
                    $row['name'] = '--';
                }
            }
            array_unshift(
                $result,
                array(
                    'id_product_attribute' => 0,
                    'name' => $this->l('Please select a combination.'),
                )
            );
            return $result;
        } else {
            return array();
        }
    }
    
    public function getProductAttributeValues($id_product_attribute)
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        
        $sql->select('pa.id_product_attribute')
            ->select('pa.reference')
            ->select('pa.ean13')
            ->select('pa.price')
            ->select('pa.unit_price_impact')
            ->select('p.price as product_price')
            ->from('product_attribute', 'pa')
            ->innerJoin('product', 'p', 'p.id_product=pa.id_product')
            ->where('pa.id_product_attribute='.(int)$id_product_attribute);
        
        $result = $db->getRow($sql);
        return $result;
    }
    
    public static function getMovement($id_movement, $exchange = false)
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();

        $sql->select('st.*')
            ->select('tp.*')
            ->select('p.reference')
            ->select('pa.ean13')
            ->from('mp_stock', 'st')
            ->innerJoin('mp_stock_type_movement', 'tp', 'tp.id_mp_stock_type_movement=st.id_mp_stock_type_movement')
            ->innerJoin('product', 'p', 'p.id_product=st.id_product')
            ->innerJoin('product_attribute', 'pa', 'pa.id_product_attribute=st.id_product_attribute');
            if ((bool)$exchange) {
                $sql->where('st.id_mp_stock_exchange='.(int)$id_movement);
            } else {
                $sql->where('st.id_mp_stock='.(int)$id_movement);
            }
        $row = $db->getRow($sql);
        return $row;
    }
    
    public static function getReference($id_product)
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('reference')
            ->from('product')
            ->where('id_product='.(int)$id_product);
        return (int)$db->getValue($sql);
    }
    
    public static function getExchangeId($id_movement)
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('id_mp_stock')
            ->from('mp_stock')
            ->where('id_mp_stock_exchange='.(int)$id_movement);
        return (int)$db->getValue($sql);
    }

    public static function getTplVars($id_movement)
    {
        if ((int)$id_movement == 0) {
            return array(
                'input_text_id' => 0,
                'input_select_products' => 0,
                'input_select_product_attributes' => 0,
                'input_text_reference' => '',
                'input_text_ean13' => '',
                'input_select_type_movements' => 0,
                'input_select_products_exchange' => 0,
                'input_select_product_attributes_exchange' => 0,
                'input_text_qty' => 0,
                'input_text_price' => Tools::displayPrice(0),
                'input_text_tax_rate' => '0.00 %',
                'input_hidden_sign' => '0',
                'input_hidden_transform' => '0',
            );
        }
        
        $row = self::getMovement($id_movement);
        if ($row) {
            PrestaShopLoggerCore::addLog('Row: ' . print_r($row, 1));
            if ((int)$row['exchange'] && (int)$row['id_mp_stock_exchange'] == 0) {
                $exchange = self::getTplVars((int)$row['id_mp_stock'], true);
            } else {
                $exchange = self::getTplVars(0);
            }
            PrestaShopLoggerCore::addLog('Exchange: ' . print_r($exchange, 1));
            return array(
                'input_text_id' => (int)$row['id_mp_stock'],
                'input_select_products' => (int)$row['id_product'],
                'input_select_product_attributes' => (int)$row['id_product_attribute'],
                'input_text_reference' => pSQL($row['reference']),
                'input_text_ean13' => pSQL($row['ean13']),
                'input_select_type_movements' => (int)$row['id_mp_stock_type_movement'],
                'input_select_products_exchange' => (int)$exchange['input_select_products_exchange'],
                'input_select_product_attributes_exchange' => (int)$exchange['input_select_product_attributes_exchange'],
                'input_text_qty' => (int)$row['qty'],
                'input_text_price' => Tools::displayPrice($row['price']),
                'input_text_tax_rate' => sprintf('%.2f', $row['tax_rate']),
                'input_hidden_sign' => (int)$row['sign'],
                'input_hidden_transform' => (int)$row['exchange'],
            );
        } else {
            return self::getTplVars(0);
        }
    }
    
    public static function updateStock($id_stock_available, $qty) {
        $stock = new StockAvailableCore($id_stock_available);
        $stock->quantity = $stock->quantity + $qty;
        try {
            $result =  $stock->update();
        } catch (Exception $ex) {
            $result = false;
            PrestaShopLoggerCore::addLog("Exception on updateStock(): " . $ex->getCode() . '-' . $ex->getMessage());
            $this->errorMessage = "Exception on updateStock(): " . $ex->getCode() . '-' . $ex->getMessage();
        }
        return $result;
    }
    
    public function getIdStockAvailable()
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('id_stock_available')
            ->from('stock_available')
            ->where('id_shop='.(int)$this->id_shop)
            ->where('id_product='.(int)$this->id_product)
            ->where('id_product_attribute='.(int)$this->id_product_attribute);
        $id_stock_available = (int)$db->getValue($sql);
        return $id_stock_available;
    }
    
    public function getQuantity($id_movement)
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('qty')
            ->from('mp_stock')
            ->where('id_mp_stock='.(int)$id_movement);
        $id_mp_stock = (int)$db->getValue($sql);
        return $id_mp_stock;
    }
    
    public function add($auto_date = true, $null_values = false) {
        try {
            $result = parent::add($auto_date, $null_values);
        } catch (Exception $ex) {
            $result = false;
            PrestaShopLoggerCore::addLog("Exception on updateStock(): " . $ex->getCode() . '-' . $ex->getMessage());
            $this->errorMessage = "Exception on add(): " . $ex->getCode() . '-' . $ex->getMessage();
        }
        
        if ($result) {
            $id_stock_available = $this->getIdStockAvailable();
            self::updateStock($id_stock_available, $this->qty);
        }
        return $result;
    }
    
    public function update($null_values = false) {
        $qty = $this->getQuantity($this->id);
        $result = parent::update($null_values);
        if ($result) {
            $id_stock_available = $this->getIdStockvailable();
            self::updateStock($id_stock_available, $this->qty - $qty);
        }
        return $result;
    }
    
    public function delete() {
        $result = parent::delete();
        if ($result) {
            $id_stock_available = $this->getIdStockAvailable();
            self::updateStock($id_stock_available, -$this->qty);
        }
        return $result;
    }
    
    public function deleteBulk($id_movements)
    {
        if (!is_array($id_movements)) {
            $id_movements = array($id_movements);
        }
        
        foreach($id_movements as $id_movement) {
            $movement = new MpStockClassObject($id_movement);
            if ($movement) {
                $this->id = $movement->id;
                $this->id_product = $movement->id_product;
                $this->id_product_attribute = $movement->id_product_attribute;
                $result = $this->delete();
                if (!$result) {
                    Context::getContext()->controller->errors[] = sprintf(
                        $this->l('Error deleting %s: %s'),
                        self::getReference($this->id_product),
                        Db::getInstance()->getMsgError()
                    );
                }
            }
        }  
    }
    
    public function toFLoat($value)
    {
        //$iso = Context::getContext()->language->language_code;
        //$curr = Context::getContext()->currency->iso_code;
        $sign = Context::getContext()->currency->sign;
        $num = str_replace(' %', '', $value);
        $num2 = str_replace(' '.$sign, '', $num);
        if ($sign == '€') {
            $num3 = str_replace(".", "", $num2);
            $num4 = str_replace(",", ".", $num3);
            $float = $num4;
        } else {
            $num3 = str_replace(",", "", $num2);
            $float = $num3;
        }
        return $float;
    }
}