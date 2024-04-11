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

if (!defined('_PS_VERSION_')) {
    exit;
}
require_once _PS_MODULE_DIR_ . 'mpstock/vendor/autoload.php';
require_once _PS_MODULE_DIR_ . 'mpstock/models/autoload.php';

require_once _PS_MODULE_DIR_ . 'mpstock/classes/MpStockHelperFormAddTypeMovement.php';
require_once _PS_MODULE_DIR_ . 'mpstock/classes/MpStockHelperListTypeMovement.php';
require_once _PS_MODULE_DIR_ . 'mpstock/classes/MpStockProductExtraHelperForm.php';
require_once _PS_MODULE_DIR_ . 'mpstock/classes/MpStockProductExtraHelperList.php';
require_once _PS_MODULE_DIR_ . 'mpstock/classes/MpStockTools.php';
require_once _PS_MODULE_DIR_ . 'mpstock/classes/MpStockProductExtraMovements.php';

class MpStock extends MpSoft\MpStock\Module\ModuleTemplate
{
    public const MENU_MAIN = 'AdminMpStockMain';
    public const MENU_MOVEMENTS = 'AdminMpStockMovements';
    public const MENU_STOCK = 'AdminMpStock';
    public const MENU_PRODUCT = 'AdminMpStockProduct';
    public const MENU_QUICK_MVT = 'AdminMpStockQuickMvt';
    public const MENU_AVAILABILITY = 'AdminMpStockAvailability';

    protected $config_form = false;
    protected $adminClassName = 'AdminMpStock';
    protected $id_lang;
    protected $id_shop;
    protected $mpMovement;
    public $link;
    public $smarty;
    private $errors = array();
    private $warnings = array();
    private $confirmations = array();

    public function __construct()
    {
        $this->name = 'mpstock';
        $this->tab = 'administration';
        $this->version = '1.1.0';
        $this->author = 'Massimiliano Palermo';
        $this->need_instance = 0;
        $this->bootstrap = true;
        /** CONSTRUCT **/
        parent::__construct();
        /** OTHER CONFIG **/
        $this->displayName = $this->l('MP Magazzino');
        $this->description = $this->l('Gestisce il magazzino.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
        $this->id_lang = (int) Context::getContext()->language->id;
        $this->id_shop = (int) Context::getContext()->shop->id;
        $this->link = new LinkCore();
        $this->smarty = Context::getContext()->smarty;
    }

    /**
     * Return the admin class name
     * @return string Admin class name
     */
    public function getAdminClassName()
    {
        return $this->adminClassName;
    }

    /**
     * Return the Admin Template Path
     * @return string The admin template path
     */
    public function getAdminTemplatePath()
    {
        return $this->getPath() . 'views/templates/admin/';
    }

    /**
     * Get the Id of current language
     * @return int id language
     */
    public function getIdLang()
    {
        return (int) $this->id_lang;
    }

    /**
     * Get the Id of current shop
     * @return int id shop
     */
    public function getIdShop()
    {
        return (int) $this->id_shop;
    }

    /**
     * Get The URL path of this module
     * @return string The URL of this module
     */
    public function getUrl()
    {
        return $this->_path;
    }

    /**
     * Return the physical path of this module
     * @return string The path of this module
     */
    public function getPath()
    {
        return $this->local_path;
    }

    /**
     * Add a message to Errors collection
     * @param string $message Message to add to collection
     */
    public function addError($message)
    {
        $this->errors[] = $message;
    }

    /**
     * Add a message to Warnings collection
     * @param string $message Message to add to collection
     */
    public function addWarning($message)
    {
        $this->warnings[] = $message;
    }

    /**
     * Add a message to Confirmations collection
     * @param string $message Message to add to collection
     */
    public function addConfirmation($message)
    {
        $this->confirmations[] = $message;
    }

    /**
     * Check if there is an Ajax call and execute it.
     */
    public function ajax()
    {
        if (Tools::isSubmit('ajax') && Tools::isSubmit('action')) {
            $action = 'ajaxProcess' . Tools::ucfirst(Tools::getValue('action'));
            $this->$action();
            exit();
        }
    }

    /**
     * Display Messages collections
     * @return string HTML messages
     */
    public function displayMessages()
    {
        $output = array();
        foreach ($this->errors as $msg) {
            $output[] = $this->displayError($msg);
        }
        foreach ($this->warnings as $msg) {
            $output[] = $this->displayWarning($msg);
        }
        foreach ($this->confirmations as $msg) {
            $output[] = $this->displayConfirmation($msg);
        }
        return implode("", $output);
    }

    public function install()
    {
        return parent::install() &&
            ModelMpStockMvtReason::createTable() &&
            ModelMpStockDocument::createTable() &&
            ModelMpStockMovement::createTable() &&
            $this->registerHooks(
                $this,
                [
                    'actionAdminControllerSetMedia',
                    'displayAdminProductsExtra',
                    'actionObjectOrderDetailAddAfter',
                    'actionObjectOrderDetailUpdateAfter',
                    'actionObjectOrderDetailDeleteAfter',

                ]
            ) &&
            $this->installMenu('Mp Magazzino', $this->name, 0, self::MENU_MAIN) &&
            $this->installMenu('Magazzino', $this->name, self::MENU_MAIN, self::MENU_STOCK) &&
            $this->installMenu('Movimenti', $this->name, self::MENU_MAIN, self::MENU_MOVEMENTS) &&
            $this->installMenu('Allinea', $this->name, self::MENU_MAIN, self::MENU_PRODUCT) &&
            $this->installMenu('Mov Veloce', $this->name, self::MENU_MAIN, self::MENU_QUICK_MVT) &&
            $this->installMenu('Disponibilità', $this->name, self::MENU_MAIN, self::MENU_AVAILABILITY);
    }

    public function uninstall()
    {
        return parent::uninstall() &&
            $this->uninstallMenu(self::MENU_AVAILABILITY) &&
            $this->uninstallMenu(self::MENU_QUICK_MVT) &&
            $this->uninstallMenu(self::MENU_PRODUCT) &&
            $this->uninstallMenu(self::MENU_MOVEMENTS) &&
            $this->uninstallMenu(self::MENU_STOCK) &&
            $this->uninstallMenu(self::MENU_MAIN);
    }

    public function hookActionAdminControllerSetMedia($params)
    {
        $ctrl = $this->context->controller;
        if ($ctrl instanceof AdminController && method_exists($ctrl, 'addCss')) {
            $ctrl->addCss($this->getLocalPath() . 'views/css/icon-menu.css');
        }
    }

    public function hookDisplayAdminProductsExtra()
    {
        //TODO: Visualizzare la pagina degli allineamenti
        $id_product = (int) Tools::getValue('id_product');
        $product = new Product($id_product);
        $combinations = $product->getAttributeCombinations($this->context->language->id);

        $variants = [];
        foreach ($combinations as $combination) {
            $variants[$combination['id_product_attribute']][] = [
                'id_product_attribute' => $combination['id_product_attribute'],
                'reference' => $combination['reference'],
                'ean13' => $combination['ean13'],
                'price' => $combination['price'] ? $combination['price'] + $product->price : $product->price,
                'quantity' => $combination['quantity'],
                'group_name' => $combination['group_name'],
                'attribute_name' => $combination['attribute_name'],
                'location' => $combination['location'],
                'default_on' => $combination['default_on'],
            ];
        }

        foreach ($variants as $key => $variant) {
            $comb_name = '';
            /** @var array */
            $cover = Image::getCover($product->id);
            if ($cover) {
                $folder = Image::getImgFolderStatic($cover['id_image']);
                $image = '/img/p/' . $folder . $cover['id_image'] . '.jpg';
            } else {
                $image = 'https://img.freepik.com/free-vector/oops-404-error-with-broken-robot-concept-illustration_114360-5529.jpg?w=826&t=st=1712664728~exp=1712665328~hmac=6db023dbbd90c5751ac79dceb73bf51675bfd9242c007b7e518b61ced6c023ee';
            }
            $first = $variants[$key][0];
            $variants[$key]['image'] = $image;
            $variants[$key]['id_product'] = $product->id;
            $variants[$key]['id_product_attribute'] = $first['id_product_attribute'];
            $variants[$key]['reference'] = $first['reference'];
            $variants[$key]['name'] = $product->name[$this->context->language->id];
            $variants[$key]['ean13'] = $first['ean13'];
            $variants[$key]['price'] = $first['price'];
            $variants[$key]['quantity'] = $first['quantity'];
            $variants[$key]['location'] = $first['quantity'];
            $variants[$key]['default_on'] = false;
            foreach ($variant as $attribute) {
                $comb_name .= $attribute['attribute_name'] . ', ';
                if ($attribute['default_on']) {
                    $variants[$key]['default_on'] = true;
                }
            }
            $variants[$key]['combination_name'] = rtrim($comb_name, ', ');
        }

        $tpl = $this->getLocalPath() . 'views/templates/admin/StockProducts/table.tpl';
        /** @var ModuleAdminController */
        $controller = $this->context->controller;
        $this->context->smarty->assign([
            'ajax_url' => $this->context->link->getAdminLink($controller->controller_name),
            'reference' => $product->reference,
            'variants' => $variants,
            'link' => $this->context->link,
        ]);


        return $this->context->smarty->fetch($tpl);
    }

    public function hookActionObjectOrderDetailAddAfter(&$params)
    {
        return $this->insertMovement($params['object'], 'add');
    }

    public function hookActionObjectOrderDetailUpdateAfter(&$params)
    {
        return $this->insertMovement($params['object'], 'update');
    }

    public function hookActionObjectOrderDetailDeleteAfter(&$params)
    {
        return $this->insertMovement($params['object'], 'delete');
    }

    protected function insertMovement($object, $action)
    {
        $id_movement = $this->getMovement($object->id);
        $id_movement_type = 133;
        $reason = $this->l('Vendita WEB');

        if ($action == 'delete') {
            $movement = new ModelMpStockMovement($id_movement);
            if (Validate::isLoadedObject($movement)) {
                return $movement->delete();
            }

            return true;
        }

        $movement = new ModelMpStockMovement();
        $movement->id_warehouse = null;
        $movement->id_document = null;
        $movement->id_order = $object->id_order;
        $movement->id_order_detail = $object->id;
        $movement->id_mpstock_mvt_reason = $id_movement_type;
        $movement->mvt_reason = $reason;
        $movement->id_product = $object->product_id;
        $movement->id_product_attribute = $object->product_attribute_id;
        $movement->reference = $object->product_reference;
        $movement->ean13 = $object->product_ean13;
        $movement->upc = $object->product_upc;
        $movement->stock_quantity_before = $object->product_quantity_in_stock;
        $movement->stock_movement = -$object->product_quantity;
        $movement->stock_quantity_after = $object->product_quantity_in_stock - $object->product_quantity;
        $movement->price_te = $object->product_price;
        $movement->wholesale_price_te = 0;
        $movement->id_employee = (int) Context::getContext()->employee->id;

        if ($id_movement) {
            $movement->id = $id_movement;
            $movement->date_upd = date('Y-m-d H:i:s');
            return $movement->update();
        } else {
            $movement->date_add = date('Y-m-d H:i:s');
            return $movement->add();
        }
    }

    protected function getMovement($id)
    {
        $db = Db::getInstance();
        $sql = 'SELECT id_mpstock_movement FROM ' . _DB_PREFIX_ . 'mpstock_movement WHERE id_order_detail = ' . (int) $id;
        $id_movement = (int) $db->getValue($sql);
        return $id_movement;
    }
}