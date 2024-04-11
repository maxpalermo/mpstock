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

/**
 * TODO CLICK ON ROW
 * onclick="document.location = 'index.php?controller=AdminProducts&id_product=16&updateproduct&token=ec9df8557a49430bdd6f0a8010dd2f34'"
 */

Class MpStockAdminHelperListAddMovementExchange extends HelperListCore
{
    public $context;
    public $values;
    public $id_lang;
    public $id_shop;
    public $module;
    public $link;
    protected $cookie;
    protected $className = 'AdminMpStock';
    protected $localeInfo;
    protected $table_name = 'mp_stock_import';
    protected $id_product;
    protected $name_product;

    public function __construct($module, $id = 0, $type_movement = 0)
    {
        $this->module = $module;
        $this->context = Context::getContext();
        $this->link = new LinkCore();
        $this->values = array();
        $this->id_lang = (int)$this->context->language->id;
        $this->id_shop = (int)$this->context->shop->id;
        parent::__construct();
        $this->id = $id;
        $this->type_movement = $type_movement;
        $this->cookie = Context::getContext()->cookie;
        $this->localeInfo = MpStockTools::getLocaleInfo();
        $this->id_product = (int)Tools::getValue('id_product', 0);
        $this->name_product = Tools::getValue('name_product', '');
        $this->smarty = Context::getContext()->smarty;
    }

    public function display()
    {
        $this->bootstrap = true;
        $this->currentIndex = $this->context->link->getAdminLink($this->className, false);
        $this->identifier = 'id_mp_stock';
        $this->no_link = true;
        $this->page = Tools::getValue('submitFilterconfiguration', 1);
        $this->_default_pagination = Tools::getValue('configuration_pagination', 20);
        $this->show_toolbar = false;
        $this->shopLinkType='';
        $this->simple_header = true;
        $this->token = Tools::getAdminTokenLite($this->className);
        $this->title = $this->module->l('Exchange movement');
        $this->table = 'mp_stock';

        $list = $this->getList();
        $fields_display = $this->getFields();

        return $this->generateList($list, $fields_display)
            .$this->bindControls();
    }
    
    public function getOptionsCombination()
    {
        $list = array();
        $combinations = MpStockTools::getCombinations($this->id_product);
        foreach ($combinations as $comb) {
            $row = array(
                'key'=> $comb['id_product_attribute'],
                'value' => Tools::strtoupper($comb['name']),
            );
            $list[] = $row;
        }
        
        return array(
            'options' => $list,
        );
    }
    
    private function bindControls()
    {
        return $this->smarty->fetch($this->module->getPath().'views/templates/admin/bind_exchange_controls.tpl');
    }

    protected function getFields()
    {
        $list = array();
        MpStockTools::addText(
            $list,
            $this->module->l('Id', get_class($this)),
            'id_mp_stock',
            48,
            'text-right'
        );
        MpStockTools::addHtml(
            $list,
            $this->module->l('Product', get_class($this)),
            'id_product',
            48,
            'text-right'
        );
        MpStockTools::addHtml(
            $list,
            $this->module->l('Combination', get_class($this)),
            'id_product_attribute',
            'auto',
            'text-left'
        );
        MpStockTools::addHtml(
            $list,
            $this->module->l('Stock', get_class($this)),
            'stock',
            'auto',
            'text-center'
        );
        MpStockTools::addHtml(
            $list,
            $this->module->l('Qty', get_class($this)),
            'qty',
            'auto',
            'text-center'
        );
        MpStockTools::addHtml(
            $list,
            $this->module->l('Wholesale Price', get_class($this)),
            'wholesale_price',
            'auto',
            'text-left'
        );
        MpStockTools::addHtml(
            $list,
            $this->module->l('Price', get_class($this)),
            'price',
            'auto',
            'text-left'
        );
        MpStockTools::addHtml(
            $list,
            $this->module->l('Tax rate', get_class($this)),
            'tax_rate',
            '128px',
            'text-left'
        );
        MpStockTools::addHtml(
            $list,
            $this->module->l('Action', get_class($this)),
            'action',
            'auto',
            'text-center'
        );
        MpStockTools::addHtml(
            $list,
            $this->module->l('Status', get_class($this)),
            'status',
            'auto',
            'text-center'
        );

        return $list;
    }

    private function getList()
    {
        $row = array(
            'id_mp_stock' => 0,
            'id_product'=> MpStockTools::getHtmlTextElement('', 'input_text_id_product_exchange', 'fixed-width-xxl', 'text-left'),
            'id_product_attribute' => MpStockTools::getHtmlSelectEmptyElement('input_select_combination_exchange'),
            'stock' => MpStockTools::displayQuantity(0),
            'qty' => MpStockTools::getHtmlQuantityTextElement(0, 'input_text_qty_exchange'),
            'wholesale_price' => MpStockTools::getHtmlPriceTextElement(0, 'input_text_wholesale_price_exchange'),
            'price' => MpStockTools::getHtmlPriceTextElement(0, 'input_text_price_exchange'),
            'tax_rate' => MpStockTools::getHtmlPercentTextElement(0, 'input_text_tax_rate_exchange'),
            'action' => MpStockTools::getHtmlButtonCallBack('', 'icon icon-save', 'javascript:saveCombinationExchange(this);', '#3030AA')
                .MpStockTools::getHtmlButtonCallBack('', 'icon icon-times', 'javascript:cancelCombinationExchange(this);', '#BB4040'),
            'status' => MpStockTools::getHtmlIcon('icon_status[]', 'icon-edit', '#303090'),
        );
        return array($row);
    }
}
