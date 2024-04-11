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

class MpStockObjectModel extends ObjectModelCore
{
    /** @var int contains id of import file */
    public $id_mp_stock_import;
    /** @var int containd id of main movement */
    public $id_mp_stock_exchange;
    /** @var int product id */
    public $id_product;
    /** @var int product attribute id */
    public $id_product_attribute;
    /** @var int type movement from table mp_stock_type movements, 0 if movement has imported */
    public $id_mp_stock_type_movement;
    /** @var int product quantity snap*/
    public $snap;
    /** @var int product quantity */
    public $qty;
    /** @var float product price */
    public $price;
    /** @var float product wholesale price */
    public $wholesale_price;
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
    /** @var string reference Product reference **/
    public $reference;
    /** @var string name Product name **/
    public $name;
    /** @var string employee Employee name **/
    public $employee;
    /** @var string image Product image src **/
    public $image;
    /** @var string movement Product movement **/
    public $movement;
    /** @var array importErrors Array of errors **/
    private $importErrors;
    /** @var MpStock module object class MpStock **/
    private $module;

    public static $definition = array(
        'table' => 'mp_stock',
        'primary' => 'id_mp_stock',
        'multilang' => false,
        'fields' => array(
            'id_mp_stock_import' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => 'false',
            ),
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
            'name' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
                'required' => 'true',
            ),
            'id_mp_stock_type_movement' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => 'true',
            ),
            'snap' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isInt',
                'required' => 'true',
            ),
            'qty' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isInt',
                'required' => 'true',
            ),
            'tax_rate' => array(
                'type' => self::TYPE_FLOAT,
                'validate' => 'isFloat',
                'required' => 'true',
            ),
            'price' => array(
                'type' => self::TYPE_FLOAT,
                'validate' => 'isPrice',
                'required' => 'true',
            ),
            'wholesale_price' => array(
                'type' => self::TYPE_FLOAT,
                'validate' => 'isPrice',
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

    public function __construct($module, $id = null, $id_lang = null, $id_shop = null)
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
        $this->importErrors = array();
        $this->module = $module;
        if ($id) {
            $this->reference = $this->getReference();
            $this->name = $this->getName();
            $this->image = $this->getImage();
            $this->employee = $this->getEmployee();
            $this->movement = $this->getMovement();
        }
    }

    public function cur2float($value)
    {
        $sign = Context::getContext()->currency->sign;
        $curr = trim(str_replace($sign, '', $value));
        return (float) $this->locale2float($curr);
    }

    public function perc2float($value)
    {
        $sign = '%';
        $numb = trim(str_replace($sign, '', $value));
        return (float) $this->locale2float($numb);
    }

    public function locale2float($value)
    {
        $iso_code = Context::getContext()->language->iso_code;
        $fmt = new NumberFormatter($iso_code, NumberFormatter::DECIMAL);
        return $fmt->parse($value);
    }

    public function getRows()
    {
        $collection = array();
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('id_mp_stock as id')
            ->from('mp_stock')
            ->orderBy('date_add DESC');
        $result = $db->executeS($sql);
        if ($result) {
            foreach ($result as $row) {
                $object = new MpStockObjectModel($this->module, $row['id']);
                $collection[] = array(
                    'id' => $object->id,
                    'image' => $object->image,
                    'reference' => $object->reference,
                    'name' => $object->name,
                    'price' => $object->price,
                    'tax_rate' => $object->tax_rate,
                    'qty' => $object->qty,
                    'movement' => $object->movement,
                    'date' => $object->date_add,
                    'employee' => $object->employee,
                );
            }
            return $collection;
        }
        return array();
    }

    public function getReference()
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('reference')
            ->from('product')
            ->where('id_product=' . (int) $this->id_product);
        return $db->getvalue($sql);
    }

    public function getNameProduct($id_product)
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('name')
            ->from('product_lang')
            ->where('id_lang=' . (int) $this->id_lang)
            ->where('id_product=' . (int) $id_product);
        return $db->getvalue($sql);
    }

    public function getIdProductFromIdProductAttribute($id_product_attribute = 0)
    {
        if ($id_product_attribute == 0) {
            $id_product_attribute = $this->id_product_attribute;
        }
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('id_product')
            ->from('product_attribute')
            ->where('id_product_attribute=' . (int) $id_product_attribute);
        $id_product = (int) $db->getValue($sql);
        return $id_product;
    }

    public function getName()
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $id_lang = Context::getContext()->language->id;
        $sql->select('id_attribute')
            ->from('product_attribute_combination')
            ->where('id_product_attribute = ' . (int) $this->id_product_attribute);
        $name = $this->getNameProduct($this->id_product);
        $attributes = $db->executeS($sql);
        foreach ($attributes as $attribute) {
            $attr = new AttributeCore($attribute['id_attribute']);
            $name .= ' ' . $attr->name[(int) $id_lang];
        }

        return $name;
    }

    public function getImage()
    {
        $shop = new ShopCore(Context::getContext()->shop->id);
        $product = new ProductCore((int) $this->id_product);
        $images = $product->getImages(Context::getContext()->language->id);

        foreach ($images as $obj_image) {
            $image = new ImageCore((int) $obj_image['id_image']);
            if ($image->cover) {
                return $shop->getBaseURL(true) . 'img/p/' . $image->getExistingImgPath() . '-small.jpg';
            }
        }
        return '';
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

    public function getMovement()
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('name')
            ->from('mp_stock_type_movement')
            ->where('id_lang = ' . (int) $this->id_lang)
            ->where('id_shop = ' . (int) $this->id_shop)
            ->where('id_mp_stock_type_movement = ' . (int) $this->id_mp_stock_type_movement);

        return $db->getValue($sql);
    }

    public static function getIdMovementByExchangeId($id_stock_exchange)
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('id_mp_stock')
            ->from(self::$definition['table'])
            ->where('id_mp_stock_exchange=' . (int) $id_stock_exchange);
        $value = (int) $db->getValue($sql);
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
            ->where('p.id_product=' . (int) $id_product);

        return (float) $db->getValue($sql);
    }

    public function getProductAttributes($id_product)
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();

        $sql->select('pa.id_product_attribute')
            ->from('product_attribute', 'pa')
            ->where('pa.id_product=' . (int) $id_product)
            ->orderBy('pa.id_product_attribute');

        $result = $db->executeS($sql);
        if ($result) {
            foreach ($result as &$row) {
                $sql_row = new DbQueryCore();
                $sql_row->select('al.name')
                    ->from('attribute_lang', 'al')
                    ->innerJoin('product_attribute_combination', 'pac', 'pac.id_attribute=al.id_attribute')
                    ->where('al.id_lang=' . (int) $this->id_lang)
                    ->where('pac.id_product_attribute=' . (int) $row['id_product_attribute'])
                    ->orderBy('al.id_attribute');

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
                    'name' => $this->module->l('Please select a combination.', get_class($this)),
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
            ->where('pa.id_product_attribute=' . (int) $id_product_attribute);

        $result = $db->getRow($sql);
        return $result;
    }

    public function getSign()
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('sign')
            ->from('mp_stock_type_movement')
            ->where('id_mp_stock_type_movement=' . (int) $this->id_mp_stock_type_movement);
        return (int) $db->getValue($sql);
    }

    public static function getMovementById($id_movement, $exchange = false)
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
        if ((bool) $exchange) {
            $sql->where('st.id_mp_stock_exchange=' . (int) $id_movement);
        } else {
            $sql->where('st.id_mp_stock=' . (int) $id_movement);
        }
        $row = $db->getRow($sql);
        return $row;
    }

    public static function getReferenceById($id_product)
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('reference')
            ->from('product')
            ->where('id_product=' . (int) $id_product);
        return (int) $db->getValue($sql);
    }

    public static function getReferenceByIdProductAttribute($id_product_attribute)
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('reference')
            ->from('product_attribute')
            ->where('id_product_attribute=' . (int) $id_product_attribute);
        return $db->getValue($sql);
    }

    public static function getEan13ByIdProductAttribute($id_product_attribute)
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('ean13')
            ->from('product_attribute')
            ->where('id_product_attribute=' . (int) $id_product_attribute);
        return $db->getValue($sql);
    }

    public static function getExchangeId($id_movement)
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('id_mp_stock')
            ->from('mp_stock')
            ->where('id_mp_stock_exchange=' . (int) $id_movement);
        return (int) $db->getValue($sql);
    }

    public static function getTplVars($id_movement)
    {
        if ((int) $id_movement == 0) {
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
            if ((int) $row['exchange'] && (int) $row['id_mp_stock_exchange'] == 0) {
                $exchange = self::getTplVars((int) $row['id_mp_stock'], true);
            } else {
                $exchange = self::getTplVars(0);
            }

            return array(
                'input_text_id' => (int) $row['id_mp_stock'],
                'input_select_products' => (int) $row['id_product'],
                'input_select_product_attributes' => (int) $row['id_product_attribute'],
                'input_text_reference' => pSQL($row['reference']),
                'input_text_ean13' => pSQL($row['ean13']),
                'input_select_type_movements' => (int) $row['id_mp_stock_type_movement'],
                'input_select_products_exchange' => (int) $exchange['input_select_products_exchange'],
                'input_select_product_attributes_exchange' =>
                    (int) $exchange['input_select_product_attributes_exchange'],
                'input_text_qty' => (int) $row['qty'],
                'input_text_price' => Tools::displayPrice($row['price']),
                'input_text_tax_rate' => sprintf('%.2f', $row['tax_rate']),
                'input_hidden_sign' => (int) $row['sign'],
                'input_hidden_transform' => (int) $row['exchange'],
            );
        } else {
            return self::getTplVars(0);
        }
    }

    public function isExchangeMovement()
    {
        $id_movement = (int) $this->id_mp_stock_type_movement;
        if (!$id_movement) {
            return false;
        }
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('exchange')
            ->from('mp_stock_type_movement')
            ->where('id_mp_stock_type_movement=' . (int) $id_movement);
        return (int) $db->getValue($sql);
    }

    public function getCurrentStock()
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('quantity')
            ->from('product_attribute')
            ->where('id_product_attribute=' . (int) $this->id_product_attribute);
        return (int) $db->getValue($sql);
    }

    public function updateStock($id_stock_available, $qty)
    {
        try {
            $stock = new StockAvailableCore((int) $id_stock_available);
            $stock->quantity = $stock->quantity + $qty;
            $result_stock = $stock->update();
            $combination = new CombinationCore((int) $stock->id_product_attribute);
            $combination->quantity = $stock->quantity;
            $result_comb = $combination->update();

            return $result_stock && $result_comb;
        } catch (Exception $e) {
            return true;
        }
    }

    public function getTotalStock()
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('sum(quantity)')
            ->from('stock_available')
            ->where('id_product=' . (int) $this->id_product)
            ->where('id_product_attribute!=0');
        return (int) $db->getValue($sql);
    }

    /**
     * Get Exchange movement
     * @return \MpStockObjectModel Movement class object or false
     */
    public function getExchangeMovement()
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('id_mp_stock')
            ->from('mp_stock')
            ->where('id_mp_stock_exchange=' . (int) $this->id);
        $value = (int) $db->getValue($sql);
        if ($value) {
            $class = new MpStockObjectModel($this->module, $value);
            return $class;
        } else {
            return false;
        }
    }

    public function getIdStockAvailable()
    {
        return (int) StockAvailable::getStockAvailableIdByProductId(
            $this->id_product,
            $this->id_product_attribute,
            $this->id_shop
        );
    }

    public static function getStaticIdStockAvailable($id_product, $id_pa, $id_shop = null)
    {
        if (!$id_shop) {
            $id_shop = (int) Context::getContext()->shop->id;
        }
        return (int) StockAvailable::getStockAvailableIdByProductId(
            $id_product,
            $id_pa,
            $id_shop
        );
    }

    public function getQuantity($id_movement)
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('qty')
            ->select('sign')
            ->from('mp_stock')
            ->where('id_mp_stock=' . (int) $id_movement);
        $row = $db->getRow($sql);
        $qty = abs((int) $row['qty']) * (int) $row['sign'];
        return $qty;
    }

    public function getImportErrors()
    {
        return $this->importErrors;
    }

    public function getOldQuantity()
    {
        $db = Db::getInstance();
        $sql = "select qty from " . _DB_PREFIX_ . "mp_stock where id_mp_stock=" . (int) $this->id;
        return (int) $db->getValue($sql);
    }

    public function save($null_values = false, $auto_date = true)
    {
        if (empty($this->date_add)) {
            $this->date_add = date('Y-m-d H:i:s');
        }
        if (empty($this->id_mp_stock_import)) {
            $this->id_mp_stock_import = 0;
        }
        $snap = $this->getSnap();
        $this->snap = $snap;
        /** Update Movement */
        if ((int) $this->id > 0) {
            /** Reset old quantity */
            $quantity = $this->getOldQuantity() * -1;
            $id_stock_available = $this->getIdStockAvailable();
            $this->updateStock($id_stock_available, $quantity);
            /** Save movement **/
            $this->qty = abs($this->qty) * $this->sign;
            $this->update($null_values);
        } else {
            /**Add new movement **/
            $this->qty = abs($this->qty) * $this->sign;
            $this->add($auto_date, $null_values);
        }
        /** Update Stock **/
        $id_stock_available_new = $this->getIdStockAvailable();
        $this->updateStock($id_stock_available_new, $this->qty);

        return true;
    }

    public function getSnap()
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('quantity')
            ->from('stock_available')
            ->where('id_product_attribute=' . (int) $this->id_product_attribute);
        return (int) $db->getValue($sql);
    }

    public function delete()
    {
        $id_stock_available = $this->getIdStockAvailable();
        $result = parent::delete();
        if ($result) {
            $this->updateStock($id_stock_available, $this->qty * -1);

            $class = $this->getExchangeMovement();
            if ($class) {
                $class->delete();
            }
        }
        return $result;
    }

    public function deleteBulk($id_movements)
    {
        if (!is_array($id_movements)) {
            $id_movements = array($id_movements);
        }

        foreach ($id_movements as $id_movement) {
            $movement = new MpStockObjectModel($this->module, $id_movement);
            if ($movement) {
                $this->id = $movement->id;
                $this->id_product = $movement->id_product;
                $this->id_product_attribute = $movement->id_product_attribute;
                $result = $this->delete();
                if (!$result) {
                    Context::getContext()->controller->errors[] = sprintf(
                        $this->module->l('Error deleting %s: %s', get_class($this)),
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
        $num2 = str_replace(' ' . $sign, '', $num);
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