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
*  @author    Massimiliano Palermo <mpsoft.it>
*  @copyright 2018 Digital Solution®
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}
require_once _PS_MODULE_DIR_ . 'mpstock/classes/MpStockTypeMovementObjectModel.php';
require_once _PS_MODULE_DIR_ . 'mpstock/classes/MpStockHelperFormAddTypeMovement.php';
require_once _PS_MODULE_DIR_ . 'mpstock/classes/MpStockHelperListTypeMovement.php';
require_once _PS_MODULE_DIR_ . 'mpstock/classes/MpStockProductExtraHelperForm.php';
require_once _PS_MODULE_DIR_ . 'mpstock/classes/MpStockProductExtraHelperList.php';
require_once _PS_MODULE_DIR_ . 'mpstock/classes/MpStockTools.php';

class MpStock extends Module
{
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
        $this->version = '1.0.0';
        $this->author = 'Digital Solutions®';
        $this->need_instance = 0;
        $this->bootstrap = true;
        /** CONSTRUCT **/
        parent::__construct();
        /** OTHER CONFIG **/
        $this->displayName = $this->l('MP Stock manager');
        $this->description = $this->l('With this module you can manage stock quantity in your shop.');
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
        return $this->getPath().'views/templates/admin/';
    }
    
    /**
     * Get the Id of current language
     * @return int id language
     */
    public function getIdLang()
    {
        return (int)$this->id_lang;
    }
    
    /**
     * Get the Id of current shop
     * @return int id shop
     */
    public function getIdShop()
    {
        return (int)$this->id_shop;
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
        foreach($this->errors as $msg) {
            $output[] = $this->displayError($msg);
        }
        foreach($this->warnings as $msg) {
            $output[] = $this->displayWarning($msg);
        }
        foreach($this->confirmations as $msg) {
            $output[] = $this->displayConfirmation($msg);
        }
        return implode("", $output);
    }

    public function install()
    {
        return parent::install() &&
            $this->installSQL() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('displayAdminProductsExtra') &&
            $this->registerHook('displayBackOfficeHeader') &&
            $this->installTab('', $this->adminClassName, $this->l('MP Quick Store'));
    }

    public function uninstall()
    {
        return parent::uninstall() && 
            $this->uninstallTab($this->adminClassName);
    }
    
    public function installSQL()
    {
        $sql = array();
        $sql[] = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."mp_stock_import` (
            `id_mp_stock_import` int(11) NOT NULL AUTO_INCREMENT,
            `id_type_document` int(11) NOT NULL,
            `sign` int(11) NOT NULL,
            `filename` varchar(255) NOT NULL,
            `id_shop` int(11) NOT NULL,
            `id_employee` int(11) NOT NULL,
            `date_add` datetime NOT NULL,
            `date_add` datetime NOT NULL,
            `date_upd` timestamp NOT NULL,
            PRIMARY KEY  (`id_mp_stock_import`)
        ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8;";
        
        $sql[] = "ALTER TABLE `"._DB_PREFIX_."mp_stock_import` ADD UNIQUE `idx_unique_filename` (`filename`);";
        
        $sql[] = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."mp_stock` (
            `id_mp_stock` int(11) NOT NULL AUTO_INCREMENT,
            `id_mp_stock_import` int(11) NOT NULL DEFAULT 0,
            `id_mp_stock_exchange` int(11) NOT NULL,
            `id_shop` int(11) NOT NULL,
            `id_product` int(11) NOT NULL,
            `id_product_attribute` int(11) NOT NULL,
            `name` varchar(255) NOT NULL,
            `id_mp_stock_type_movement` int(11) NOT NULL,
            `qty` varchar(10) NOT NULL,
            `price` decimal(20,6) NOT NULL,
            `wholesale_price` decimal(20,6) NOT NULL,
            `tax_rate` decimal(20,6) NOT NULL,
            `date_movement` datetime NULL,
            `sign` int(11) NOT NULL,
            `date_add` timestamp NOT NULL,
            `id_employee` int NOT NULL,
            PRIMARY KEY  (`id_mp_stock`)
        ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8;";
        
        $sql[] = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."mp_stock_type_movement` (
            `id_mp_stock_type_movement` int(11) NOT NULL AUTO_INCREMENT,
            `id_lang` int(11) NOT NULL,
            `id_shop` int(11) NOT NULL,
            `name` varchar(255) NOT NULL,
            `sign` enum('-1','1') NOT NULL DEFAULT '1',
            `exchange` boolean NOT NULL,
            PRIMARY KEY  (`id_mp_stock_type_movement`)
        ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8;";
        $sql[] = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."mp_stock_list_movements` (
            `id_mp_stock_list_movements` int(11) NOT NULL AUTO_INCREMENT,
            `id_employee_bo` int(11) NOT NULL,
            `id_product` int(11) NOT NULL,
            `id_product_attribute` int(11) NOT NULL,
            `idx` int(11) NOT NULL,
            `image_url` varchar(255) NULL,
            `reference` varchar(255) NULL,
            `name` varchar(255) NULL,
            `qty` int(11) NOT NULL,
            `type_movement` varchar(255) NOT NULL,
            `date_add` datetime NOT NULL,
            `id_employee_movement` int(11) NOT NULL,
            `employee` varchar(255) NULL,
            PRIMARY KEY  (`id_mp_stock_list_movements`)
        ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8;";
        foreach ($sql as $query) {
            try {
                if (Db::getInstance()->execute($query) == false) {
                    $this->addError(Db::getInstance()->getMsgError());
                    return false;
                }
            } catch (Exception $ex) {
                PrestaShopLoggerCore::addLog('Install MPSTOCK: error '.$ex->getCode().' '.$ex->getMessage());
            }
        }
        return true;
    }
    
    /**
     * 
     * @param string $parent Parent tab name
     * @param type $class_name Class name of the module
     * @param type $name Display name of the module
     * @param type $active If true, Tab menu will be shown
     * @return boolean True if successfull, False otherwise
     */
    public function installTab($parent, $class_name, $name, $active = 1)
    {
        // Create new admin tab
        $tab = new Tab();
        
        $tab->id_parent = (int)Tab::getIdFromClassName($parent);
        $tab->name      = array();
        
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = $name;
        }
        
        $tab->class_name = $class_name;
        $tab->module     = $this->name;
        $tab->active     = $active;
        
        if (!$tab->add()) {
            $this->addError($this->l('Error during Tab install.'));
            return false;
        }
        return true;
    }
    
    /**
     * 
     * @param string pe $class_name Class name of the module
     * @return boolean True if successfull, False otherwise
     */
    public function uninstallTab($class_name)
    {
        $id_tab = (int)Tab::getIdFromClassName($class_name);
        if ($id_tab) {
            $tab = new Tab((int)$id_tab);
            return $tab->delete();
        }
    }
    
    public function getContent()
    {
        /** Check if there is an Ajax call **/
        $this->ajax();
        /** Sumbmit new movement  **/
        if (Tools::isSubmit('submitNewMovement')) {
            $form = new MpStockHelperFormAddTypeMovement($this);
            return $form->display();
        /** Submit edit movement */
        } elseif (Tools::isSubmit('editMovement')) {
            $id_movement = (int)Tools::getValue('editMovement', 0);
            if ($id_movement) {
                $form = new MpStockHelperFormAddTypeMovement($this);
                return $form->display();
            }
        /** Submit delete movement **/
        } elseif (Tools::isSubmit('deleteMovement')) {
            $id_movement = (int)Tools::getValue('deleteMovement', 0);
            if ($id_movement) {
                $movement = new MpStockTypeMovementObjectModel($id_movement);
                if ($movement->delete()) {
                    $this->addConfirmation($this->l('Movement type deleted successfully.'));
                } else {
                    $this->addError($movement->getErrorMessage());
                }
            }
        /** Submit save movement **/
        } elseif (Tools::isSubmit('submitSaveMovement')) {
            $this->processSaveMovement();
        }
        /** Display default list **/
        $list = new MpStockHelperListTypeMovement($this);
        return $this->displayMessages().$list->display();
    }
    
    public function processSaveMovement()
    {
        $id_mp_stock_type_movement = (int)Tools::getValue('input_id_mp_stock_type_movement', 0);
        $name = Tools::getValue('input_name', '');
        $sign = (int)Tools::getValue('input_sign', '1');
        $exchange = (int)Tools::getValue('input_exchange', 0);

        if (!$id_mp_stock_type_movement) {
            $this->addError($this->l('Please, insert a valid movement id.'));
            return false;
        }
        if (!$name) {
            $this->addError($this->l('Please, insert a valid name for this movement.'));
            return false;
        }
        $exists = (int)MpStockTypeMovementObjectModel::exists($id_mp_stock_type_movement);
        if ($exists) {
            $movement = new MpStockTypeMovementObjectModel($id_mp_stock_type_movement);
            $movement->id_lang = (int)$this->id_lang;
            $movement->id_shop = (int)$this->id_shop;
            $movement->name = $name;
            $movement->sign = $sign;
            $movement->exchange = $exchange;
            $result = $movement->update();
        } else {
            $movement = new MpStockTypeMovementObjectModel();
            $movement->force_id = true;
            $movement->id = $id_mp_stock_type_movement;
            $movement->id_lang = (int)$this->id_lang;
            $movement->id_shop = (int)$this->id_shop;
            $movement->name = $name;
            $movement->sign = $sign;
            $movement->exchange = $exchange;
            $result = $movement->add();
        }
        if (!$result) {
            $this->addError(sprintf($this->l('Error saving movement: %s'), Db::getInstance()->getMsgError()));
            return false;
        } else {
            $this->addConfirmation($this->l('Movement type saved successfully.'));
            return true;
        }
    }
    
    public function hookDisplayAdminProductsExtra()
    {
        /** Check pagination **/
        
        if (Tools::isSubmit('submitFiltermp_stock')) {
            $pagination = (int)Tools::getValue('mp_stock_pagination', 0);
            $page = (int)Tools::getValue('submitFiltermp_stock');
            $list = new MpStockProductExtraHelperList($this, $pagination, $page);
            return $list->display();
        }
        /** Check if has been submitted find button **/
        if (Tools::isSubmit('submitFormFindMovements')) {           
            /** Save configuration **/
            ConfigurationCore::updateValue(
                'MP_STOCK_SEARCH_IN_ORDERS',
                (int)Tools::getValue('input_switch_search_in_orders', 0)
            );
            ConfigurationCore::updateValue(
                'MP_STOCK_SEARCH_IN_SLIPS',
                (int)Tools::getValue('input_switch_search_in_slips', 0)
            );
            ConfigurationCore::updateValue(
                'MP_STOCK_SEARCH_IN_MOVEMENTS',
                (int)Tools::getValue('input_switch_search_in_movements', 0)
            );
            /** Get list movements **/
            $this->smarty->assign('key_tab', 'ModuleMpstock', 0);
            $list = new MpStockProductExtraHelperList($this);
            return $list->display();
        }
        
        /** Display default form **/
        $form = new MpStockProductExtraHelperForm($this);
        return $form->display();
    }
    
    public function hookDisplayBackOfficeHeader()
    {
        $ctrl = $this->context->controller;
        if ($ctrl instanceof AdminController && method_exists($ctrl, 'addCss')) {
            $ctrl->addCss($this->_path . 'views/css/icon-menu.css');
        }
    }
    
    public function ajaxProcessFindMovements()
    {
        $id_product = (int)Tools::getValue('id_product');
        $id_employee = (int)Tools::getValue('id_employee');
        $date_start = Tools::getValue('date_start');
        $date_end = Tools::getValue('date_end');
        $current_page = (int)Tools::getValue('current_page');
        $pagination = (int)Tools::getValue('pagination');
        $id_product_attribute = (int)Tools::getValue('id_product_attribute', 0);
        
        require_once $this->getPath().'classes/StockMovements.php';
        $stockMovements = new MpStockStockMovements(
            $this,
            array(
                'search_in_orders' => (int)Tools::getValue('search_in_orders', 1),
                'search_in_slips' => (int)Tools::getValue('search_in_slips', 1),
                'search_in_movements' => (int)Tools::getValue('search_in_movements', 1),
                'id_product' => $id_product,
                'id_product_attribute' => $id_product_attribute,
                'id_employee' => $id_employee,
                'date_start' => $date_start,
                'date_end' => $date_end,
                'pagination' => $pagination>0?$pagination:50,
                'page' => $current_page>0?$current_page:1,
            )
        );
        
        $html = $stockMovements->getMovements();
        
        return Tools::jsonEncode(
            array(
                'result' => true,
                'html' => $html,
            )
        );
    }
    
    public function ajaxProcessExportCSV()
    {
        $id_product = (int)Tools::getValue('id_product');
        $id_employee = (int)Tools::getValue('id_employee');
        $table = new MpStockListHelperObject($id_product, $id_employee, $this);
        print $table->exportCSV();
    }
}
