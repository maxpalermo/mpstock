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

class MpStockAdminHelperListAddMovement extends HelperListCore
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
        $this->show_toolbar = true;
        $this->toolbar_btn = array(
            'back' => array(
                'desc' => $this->module->l('Back', get_class($this)),
                'href' => $this->link->getAdminLink($this->className),
            ),
        );
        $this->shopLinkType='';
        $this->simple_header = true;
        $this->token = Tools::getAdminTokenLite($this->className);
        $this->title = sprintf(
            $this->module->l('Combinations: %s', get_class($this)),
            $this->name_product
        );
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
                'value'=> $comb['id_product_attribute'],
                'name' => Tools::strtoupper($comb['name']),
            );
            $list[] = $row;
        }
        
        return MpStockTools::getOptionsCombination($list);
    }
    
    private function bindControls()
    {
        return $this->smarty->fetch($this->module->getPath().'views/templates/admin/bind_controls.tpl');
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
        MpStockTools::addText(
            $list,
            $this->module->l('Attribute', get_class($this)),
            'id_product_attribute',
            48,
            'text-right'
        );
        MpStockTools::addHtml(
            $list,
            $this->module->l('Type movement', get_class($this)),
            'movement',
            'auto',
            'text-left'
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
            $this->module->l('Reference', get_class($this)),
            'reference',
            'auto',
            'text-left'
        );
        MpStockTools::addHtml(
            $list,
            $this->module->l('EAN13', get_class($this)),
            'ean13',
            'auto',
            'text-left'
        );
        MpStockTools::addHtml(
            $list,
            $this->module->l('Stock', get_class($this)),
            'stock',
            'auto',
            'text-right'
        );
        MpStockTools::addHtml(
            $list,
            $this->module->l('Qty', get_class($this)),
            'qty',
            'auto',
            'text-left'
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
        $output = array();
        $combinations = MpStockTools::getCombinations($this->id_product);
        foreach ($combinations as $comb) {
            $row = array(
                'id_mp_stock' => 0,
                'id_product_attribute'=> $comb['id_product_attribute'],
                'movement' => $this->getMovements(),
                'name' => Tools::strtoupper($comb['name']),
                'reference' => $comb['reference'],
                'ean13' => $comb['ean13'],
                'qty' => MpStockTools::getHtmlQuantityTextElement(0),
                'stock' => MpStockTools::displayQuantity(
                    MpStockTools::getAvailableStock($comb['id_product_attribute'])
                ),
                'wholesale_price' =>
                    MpStockTools::getHtmlPriceTextElement($comb['wholesale_price'], 'wholesale_price[]'),
                'price' => MpStockTools::getHtmlPriceTextElement($comb['price'], 'price[]'),
                'tax_rate' => MpStockTools::getHtmlPercentTextElement($comb['tax_rate'], 'input_tax_rate[]'),
                'action' =>
                    MpStockTools::getHtmlButtonCallBack(
                        '',
                        'icon icon-save',
                        'javascript:saveCombination(this);',
                        '#3030AA'
                    ).
                    MpStockTools::getHtmlButtonCallBack(
                        '',
                        'icon icon-times',
                        'javascript:deleteCombination(this);',
                        '#BB4040'
                    ),
                'status' => MpStockTools::getHtmlIcon('icon_status[]', 'icon-edit', '#303090'),
            );
            $output[] = $row;
        }
        return $output;
    }

    private function getMovements()
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('id_mp_stock_type_movement')
            ->select('name')
            ->from('mp_stock_type_movement')
            ->where('id_lang='.(int)$this->id_lang)
            ->where('id_shop='.(int)$this->id_shop)
            ->orderBy('name');
        $result = $db->executeS($sql);

        $this->smarty->assign(
            array(
                'name' => 'input_select_movement[]',
                'id' => '',
                'select_first' => $this->module->l('Select a movement', get_class($this)),
                'options' => array(
                    'query' => $result,
                    'key' => 'id_mp_stock_type_movement',
                    'value' => 'name'
                ),
                'multiple' => false,
                'chosen' => true,

            )
        );
        $select = $this->smarty->fetch($this->module->getPath().'views/templates/admin/html_element_select.tpl');
        return $select;
    }
}
