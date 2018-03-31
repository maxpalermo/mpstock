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

require_once _PS_MODULE_DIR_ . 'mpstock/classes/mpstockmovement.class.php';
require_once _PS_MODULE_DIR_ . 'mpstock/classes/mpstocklist.class.php';

class MpStock extends Module
{
    protected $config_form = false;
    protected $adminClassName = 'AdminMpStock';
    protected $id_lang;
    protected $id_shop;
    protected $mpMovement;
    public $link;
    public $smarty;

    public function __construct()
    {
        $this->name = 'mpstock';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Digital Solutions®';
        $this->need_instance = 0;
        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;
        parent::__construct();
        $this->displayName = $this->l('MP Stock manager');
        $this->description = $this->l('With this module you can manage stock quantity in your shop.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
        $this->id_lang = (int) Context::getContext()->language->id;
        $this->id_shop = (int) Context::getContext()->shop->id;
        $this->link = new LinkCore();
        $this->smarty = Context::getContext()->smarty;
    }
    
    public function ajax()
    {
        if (Tools::isSubmit('ajax')) {
            $action = 'ajaxProcess' . Tools::getValue('action');
            $this->$action();
            exit();
        }
    }
    
    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
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

        $sql[] = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."mp_stock` (
            `id_mp_stock` int(11) NOT NULL AUTO_INCREMENT,
            `id_mp_stock_exchange` int(11) NOT NULL,
            `id_shop` int(11) NOT NULL,
            `id_product` int(11) NOT NULL,
            `id_product_attribute` int(11) NOT NULL,
            `id_mp_stock_type_movement` int(11) NOT NULL,
            `qty` varchar(10) NOT NULL,
            `price` decimal(20,6) NOT NULL,
            `tax_rate` decimal(20,6) NOT NULL,
            `date_movement` date NULL,
            `date_add` timestamp NOT NULL,
            `id_employee` int NOT NULL,
            PRIMARY KEY  (`id_mp_stock`)
        ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8;";
        
        $sql[] = "ALTER TABLE `"._DB_PREFIX_."ps_mp_stock` 
            ADD UNIQUE `idx_import_unique` (
            `id_product`, 
            `id_product_attribute`, 
            `date_movement`
        );";
        
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
            `date_add` date NOT NULL,
            `id_employee_movement` int(11) NOT NULL,
            `employee` varchar(255) NULL,
            PRIMARY KEY  (`id_mp_stock_list_movements`)
        ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8;";

        foreach ($sql as $query) {
            if (Db::getInstance()->execute($query) == false) {
                $this->_errors[] = Db::getInstance()->getMsgError();
                return false;
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
            $this->_errors[] = $this->l('Error during Tab install.');
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
    
    public function getContent()
    {
        if (Tools::isSubmit('ajax')) {
            $action = 'ajaxProcess' . Tools::getValue('action');
            $this->$action();
            exit();
        }
        if (Tools::isSubmit('submitNewMovement')) {
            return $this->initForm();
        } elseif (Tools::isSubmit('submitSaveMovement')) {
            if ($this->processSaveMovement()) {
                $message = $this->displayConfirmation($this->l('Movement type saved successfully.'));
            } else {
                $message = '';
            }
            return $message . $this->initList() . $this->initScript();
        } elseif (Tools::isSubmit('deleteMovement')) {
            if ($this->processDeleteMovement()) {
                $message = $this->displayConfirmation($this->l('Movement type deleted successfully.'));
            } else {
                $message = '';
            }
            return $message . $this->initList() . $this->initScript();
        } elseif (Tools::isSubmit('editMovement')) {
            return $this->initForm();
        } else {
            return $this->initList() . $this->initScript();
        }
    }
    
    public function initForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Movement type'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'required' => false,
                        'type' => 'text',
                        'name' => 'input_text_id_movement',
                        'label' => $this->l('Id'),
                        'desc' => $this->l('Id movement, you can\'t edit this field.'),
                        'prefix' => '<i class="icon-chevron-right"></i>',
                        'suffix' => '<i class="icon-gear"></i>',
                        'class' => 'input fixed-width-sm',
                        'readonly' => true,
                    ),
                    array(
                        'required' => true,
                        'type' => 'text',
                        'name' => 'input_text_name',
                        'label' => $this->l('Name'),
                        'desc' => $this->l('Insert the name of the stock movement.'),
                        'prefix' => '<i class="icon-chevron-right"></i>',
                        'suffix' => '<i class="icon-list-ul"></i>',
                        'class' => 'input fixed-width-xxl',
                    ),
                    array(
                        'required' => true,
                        'type' => 'select',
                        'name' => 'input_select_sign',
                        'label' => $this->l('Sign'),
                        'desc' => $this->l('Select the sign of the stock movement.'),
                        'prefix' => '<i class="icon-chevron-right"></i>',
                        'suffix' => '<i class="icon-list-ul"></i>',
                        'class' => 'input fixed-width-sm',
                        'options' => array(
                            'query' => array(
                                array(
                                    'id' => '1',
                                    'name' => '1',
                                ),
                                array(
                                    'id' => '-1',
                                    'name' => '-1',
                                )
                            ),
                            'id' => 'id',
                            'name' => 'name',
                        ),
                    ),
                    array(
                        'required' => true,
                        'type' => 'switch',
                        'name' => 'input_switch_exchange',
                        'label' => $this->l('Exchange'),
                        'desc' => $this->l('If set stock will be charged with another product'),
                        'values' => array(
                            array(
                                'id' => 'id_switch_exchange_on',
                                'value' => '1',
                                'label' => $this->l('YES'),
                            ),
                            array(
                                'id' => 'id_switch_exchange_off',
                                'value' => '0',
                                'label' => $this->l('NO'),
                            ),
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'icon' => 'process-icon-save'
                ),
            ),
        );
        
        $helper = new HelperFormCore();
        $helper->table = 'mp_stock_type_movement';
        $helper->default_form_language = (int)$this->id_lang;
        $helper->allow_employee_form_lang = (int) Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANGUAGE');
        $helper->submit_action = 'submitSaveMovement';
        $helper->currentIndex = $this->link->getAdminLink('AdminModules', false) 
            . '&configure=' . $this->name
            . '&tab_module=administration' 
            . '&module_name=mpstock';
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        if (Tools::isSubmit('submitEditMovement')) {
            $submit_values = Tools::getAllValues();
            $output = array();
            foreach($submit_values as $key=>$value) {
                if(is_array($value)) {
                    $output[$key.'[]'] = $value;
                } else {
                    $output[$key] = $value;
                }
            }
            $helper->tpl_vars = array(
                'fields_value' => $output,
                'languages' => $this->context->controller->getLanguages(),
            );
        } else {
            $helper->tpl_vars = array(
                'fields_value' => array(
                    'input_text_id_movement' => 0,
                    'input_text_name' => '',
                    'input_select_sign' => '1',
                    'input_switch_exchange' => 0,
                ),
                'languages' => $this->context->controller->getLanguages(),
            );
        }
        return $helper->generateForm(array($fields_form));
    }
    
    public function initList()
    {
        $fields_list = array(
            'check' => array(
                'title' => '',
                'width' => 16,
                'type' => 'bool',
                'align' => 'text-center',
                'float' => true,
                'search' => false,
            ),
            'id_mp_stock_type_movement' => array(
                'title' => $this->l('Id'),
                'width' => 16,
                'type' => 'bool',
                'align' => 'text-right',
                'float' => true,
                'search' => false,
            ),
            'flag' => array(
                'title' => $this->l('Language'),
                'width' => 24,
                'type' => 'bool',
                'align' => 'text-center',
                'float' => true,
                'search' => false,
            ),
            'name' => array(
                'title' => $this->l('Name'),
                'width' => 'auto',
                'type' => 'text',
                'align' => 'text-left',
                'search' => false,
            ),
            'sign' => array(
                'title' => $this->l('Sign'),
                'width' => 32,
                'type' => 'bool',
                'align' => 'text-center',
                'float' => true,
                'search' => false,
            ),
            'exchange' => array(
                'title' => $this->l('Exchange'),
                'width' => 32,
                'type' => 'bool',
                'align' => 'text-center',
                'float' => true,
                'search' => false,
            ),
            'actions' => array(
                'title' => $this->l('Actions'),
                'width' => 'auto',
                'type' => 'bool',
                'align' => 'text-center',
                'float' => true,
                'search' => false,
            ),
        );
        $this->mpMovement = new MpStockMovementClassObject();
        $list = $this->mpMovement->getListMovements();
        
        $helper = new HelperListCore();
        $helper->shopLinkType = '';
        $helper->simple_header = false;
        $helper->identifier = 'id_mp_stock';
        $helper->show_toolbar = true;
        $helper->title = $this->l('Stock movements type');
        $helper->table = 'mp_stock';
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = $this->link->getAdminLink('AdminModules', false);
        $helper->listTotal = count($list);
        $helper->no_link = true;
        $helper->toolbar_btn = array(
            'new' => array(
                'href' => '',
                'desc' => $this->l('New movement'),
            ),
            'preview' => array(
                'href' => '',
                'desc' => $this->l('Print report'),
            ),
            'dropdown' => array(
                'href' => '',
                'desc' => $this->l('Find movements'),
            )
        );
        
        return $helper->generateList($list, $fields_list);
    }
    
    private function initScript()
    {
        $this->smarty->assign(
            array(
                'token' => Tools::getAdminTokenLite('AdminModules'),
                'loading_gif' => $this->getURL() . 'views/img/loading.gif',
            )
        );
        $script = $this->smarty->fetch($this->getPath() . 'views/templates/admin/getContent.tpl');
        return $script;
    }
    
    public function processSaveMovement()
    {
        $movement = new MpStockMovementClassObject();
        $movement->id_mp_stock_type_movement = (int)Tools::getValue('input_text_id', 0);
        $movement->id_lang = (int)$this->id_lang;
        $movement->id_shop = (int)$this->id_shop;
        $movement->name = pSQL(Tools::getValue('input_text_name', ''));
        $movement->sign = (int)Tools::getValue('input_select_sign', '1');
        $movement->exchange = (int)Tools::getValue('input_switch_exchange');
        
        $result = $movement->save();
        PrestaShopLoggerCore::addLog('Save movement: ' . (int)$result);
        if (!$result) {
            $this->_errors[] = sprintf($this->l('Error saving movement: %s'), Db::getInstance()->getMsgError());
            return false;
        }
        return true;
    }
    
    public function processDeleteMovement()
    {
        $id = (int)Tools::getValue('deleteMovement', 0);
        $movement = new MpStockMovementClassObject($id);
        
        $result = $movement->delete();
        PrestaShopLoggerCore::addLog('Deleted movement: ' . (int)$result);
        if (!$result) {
            $this->_errors[] = sprintf($this->l('Error deleting movement: %s'), Db::getInstance()->getMsgError());
            return false;
        }
        return true;
    }
    
    public function hookDisplayAdminProductsExtra()
    {
        require_once $this->getPath().'/classes/ProductExtraForm.php';
        
        $id_product = (int)Tools::getValue('id_product');
        $form = new MpStockProductExtraForm(
            $this,
            array(
                'search_in_orders' => 1,
                'search_in_slips' => 1,
                'search_in_movements'=> 1,
                'date_start' => '',
                'date_end' => '',
                'id_product' => (int)$id_product,
                'id_employee' => (int) Context::getContext()->employee->id,
                'module_token' => Tools::encrypt($this->name),
            )
        );
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
        //$table = new MpStockListHelperObject($id_product, $id_employee, $this);
        //print $table->findMovements($date_start, $date_end);
    }
    
    public function ajaxProcessExportCSV()
    {
        $id_product = (int)Tools::getValue('id_product');
        $id_employee = (int)Tools::getValue('id_employee');
        $table = new MpStockListHelperObject($id_product, $id_employee, $this);
        print $table->exportCSV();
    }
}