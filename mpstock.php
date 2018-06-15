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
require_once _PS_MODULE_DIR_ . 'mpstock/classes/MpStockObjectModelTypeMovement.php';
require_once _PS_MODULE_DIR_ . 'mpstock/classes/MpStockHelperFormAddTypeMovement.php';
require_once _PS_MODULE_DIR_ . 'mpstock/classes/MpStockHelperListTypeMovement.php';
require_once _PS_MODULE_DIR_ . 'mpstock/classes/MpStockProductExtraHelperForm.php';
require_once _PS_MODULE_DIR_ . 'mpstock/classes/MpStockProductExtraHelperList.php';
require_once _PS_MODULE_DIR_ . 'mpstock/classes/MpStockTools.php';
require_once _PS_MODULE_DIR_ . 'mpstock/classes/MpStockProductExtraMovements.php';

require_once _PS_MODULE_DIR_ . 'mpstock/classes/MpStockMvtReasonObjectModel.php';
require_once _PS_MODULE_DIR_ . 'mpstock/classes/MpStockDocumentObjectModel.php';
require_once _PS_MODULE_DIR_ . 'mpstock/classes/MpStockProductObjectModel.php';

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
            MpStockMvtReasonObjectModel::install() &&
            MpStockDocumentObjectModel::install() &&
            MpStockProductObjectModel::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('displayBackOfficeHeader') &&
            $this->registerHook('displayAdminProductsExtra') &&
            $this->installTab('', $this->adminClassName, $this->l('MP Quick Stock'));
    }

    public function uninstall()
    {
        return parent::uninstall() &&
            $this->uninstallTab($this->adminClassName);
    }
    
    /**
     * Install a new menu
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
     * Uninstall a menu
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
    
    public function hookDisplayBackOfficeHeader()
    {
        $ctrl = $this->context->controller;
        if ($ctrl instanceof AdminController && method_exists($ctrl, 'addCss')) {
            $ctrl->addCss($this->_path . 'views/css/icon-menu.css');
        }
    }

    public function hookDisplayAdminProductsExtra()
    {
        return $this->smarty->fetch($this->getAdminTemplatePath().'hookDisplayAdminProductExtra.tpl');
    }
}

