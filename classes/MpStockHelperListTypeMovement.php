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

class MpStockHelperListTypeMovement extends HelperListCore
{
    public $context;
    public $values;
    public $id_lang;
    public $module;
    public $link;
    protected $cookie;
    protected $className = 'AdminMpStock';
    protected $localeInfo;
    protected $table_name = 'mp_stock_import';
    
    public function __construct($module)
    {
        $this->module = $module;
        $this->context = Context::getContext();
        $this->link = new LinkCore();
        $this->values = array();
        $this->id_lang = (int)$this->context->language->id;
        $this->id_shop = (int)$this->context->shop->id;
        parent::__construct();
        $this->cookie = Context::getContext()->cookie;
        $this->localeInfo = MpStockTools::getLocaleInfo();
    }
    
    public function display()
    {
        $this->bootstrap = true;
        $this->currentIndex = "#";
        $this->identifier = '';
        $this->no_link = true;
        $this->page = Tools::getValue('submitFilterconfiguration', 1);
        $this->_default_pagination = Tools::getValue('configuration_pagination', 20);
        $this->show_toolbar = true;
        $this->toolbar_btn = array(
            'new' => array(
                'href' => $this->link->getAdminLink('AdminModules')
                    .'&configure=mpstock'
                    .'&module_name=mpstock'
                    .'&tab_module=administration'
                    .'&submitNewMovement',
                'desc' => $this->l('New movement'),
            ),
            'back' => array(
                'desc' => $this->module->l('Go to Stock Movements', get_class($this)),
                'href' => $this->module->link->getAdminlink('AdminMpStock'),
            ),
        );
        $this->shopLinkType='';
        $this->simple_header = false;
        $this->token = Tools::getAdminTokenLite('AdminModules');
        $this->title = $this->module->l('Documents found', get_class($this));
        $this->table = 'mp_stock_type_movement';
        
        $this->mpMovement = new MpStockObjectModelTypeMovement();
        $list = $this->mpMovement->getListMovements();
        $this->listTotal = count($list);
        $fields_display = $this->getFields();
        
        return $this->generateList($list, $fields_display).$this->getScript();
    }
    
    private function getScript()
    {
        $smarty = Context::getContext()->smarty;
        $smarty->assign(
            array(
                'module_url' => $this->module->link->getAdminlink('AdminModules')
                    .'&tab_module=administration'
                    .'&module_name=mpstock'
                    .'&configure=mpstock',
            )
        );
        return $smarty->fetch($this->module->getAdminTemplatePath().'helper_list_type_movement.tpl');
    }

    protected function getFields()
    {
        $list = array();
        MpStockTools::addHtml(
            $list,
            '',
            'check',
            '32',
            'text-center'
        );
        MpStockTools::addText(
            $list,
            $this->module->l('Id', get_class($this)),
            'id_mp_stock_type_movement',
            '48',
            'text-right'
        );
        MpStockTools::addHtml(
            $list,
            $this->module->l('Language', get_class($this)),
            'flag',
            '28',
            'text-center'
        );
        MpStockTools::addText(
            $list,
            $this->module->l('Name', get_class($this)),
            'name',
            'auto',
            'text-left'
        );
        MpStockTools::addHtml(
            $list,
            $this->module->l('Sign', get_class($this)),
            'sign',
            '32',
            'text-center'
        );
        MpStockTools::addHtml(
            $list,
            $this->module->l('Exchange', get_class($this)),
            'exchange',
            '32',
            'text-center'
        );
        MpStockTools::addHtml(
            $list,
            $this->module->l('Actions', get_class($this)),
            'actions',
            'auto',
            'text-center'
        );
        
        return $list;
    }
}
