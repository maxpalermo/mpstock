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

Class MpStockAdminHelperListAddMovement extends HelperListCore
{
    public $context;
    public $values;
    public $id_lang;
    public $id_shop;
    public $module;
    public $link;
    protected $cookie;
    protected $className = 'AdminMpStock';
    protected static $localeInfo;
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
        if (Context::getContext()->language->iso_code == 'it') {
            self::$localeInfo = array(
                'decimal_point' => ',',
                'thousands_sep' => '.'
            );
        } else {
            self::$localeInfo = array(
                'decimal_point' => '.',
                'thousands_sep' => ','
            );
        }
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
    
    private function bindControls()
    {
        return $this->smarty->fetch($this->module->getPath().'views/templates/admin/bind_controls.tpl');
    }
    
    protected function getFields()
    {
        $list = array();
        $this->addText(
            $list,
            $this->module->l('Id', get_class($this)),
            'id_mp_stock',
            48,
            'text-right'
        );
        $this->addText(
            $list,
            $this->module->l('Attribute', get_class($this)),
            'id_product_attribute',
            48,
            'text-right'
        );
        $this->addHtml(
            $list,
            $this->module->l('Type movement', get_class($this)),
            'movement',
            'auto',
            'text-left'
        );
        $this->addText(
            $list,
            $this->module->l('Name', get_class($this)),
            'name',
            'auto',
            'text-left'
        );
        $this->addHtml(
            $list,
            $this->module->l('Reference', get_class($this)),
            'reference',
            'auto',
            'text-left'
        );
        $this->addHtml(
            $list,
            $this->module->l('EAN13', get_class($this)),
            'ean13',
            'auto',
            'text-left'
        );
        $this->addHtml(
            $list,
            $this->module->l('Qty', get_class($this)),
            'qty',
            'auto',
            'text-left'
        );
        $this->addHtml(
            $list,
            $this->module->l('Wholesale Price', get_class($this)),
            'wholesale_price',
            'auto',
            'text-left'
        );
        $this->addHtml(
            $list,
            $this->module->l('Price', get_class($this)),
            'price',
            'auto',
            'text-left'
        );
        $this->addHtml(
            $list,
            $this->module->l('Tax rate', get_class($this)),
            'tax_rate',
            'auto',
            'text-left'
        );
        $this->addHtml(
            $list,
            $this->module->l('Action', get_class($this)),
            'action',
            'auto',
            'text-center'
        );
        $this->addHtml(
            $list,
            $this->module->l('Status', get_class($this)),
            'status',
            'auto',
            'text-center'
        );
        
        return $list;
    }
    
    protected function addText(&$list, $title, $key, $width, $alignment, $search = false)
    {
        $item = array(
            'title' => $title,
            'width' => $width,
            'align' => $alignment,
            'type' => 'text',
            'search' => $search,
        );
        
        $list[$key] = $item;
    }
    
    protected function addDate(&$list, $title, $key, $width, $alignment, $search = false)
    {
        $item = array(
            'title' => $title,
            'width' => $width,
            'align' => $alignment,
            'type' => 'date',
            'search' => $search,
        );
        
        $list[$key] = $item;
    }
    
    protected function addPrice(&$list, $title, $key, $width, $alignment, $search = false)
    {
        $item = array(
            'title' => $title,
            'width' => $width,
            'align' => $alignment,
            'type' => 'price',
            'search' => $search,
        );
        
        $list[$key] = $item;
    }
    
    protected function addHtml(&$list, $title, $key, $width, $alignment, $search = false)
    {
        $item = array(
            'title' => $title,
            'width' => $width,
            'align' => $alignment,
            'type' => 'bool',
            'float' => true,
            'search' => $search,
        );
        
        $list[$key] = $item;
    }

    protected function addIcon($icon, $color, $title = '')
    {
        return "<i class='icon $icon' style='color: $color;'></i> ".$title;
    }
    
    protected function getList()
    {
        $output = array();
        $combinations = self::getCombinations($this->id_product);
        foreach ($combinations as $comb) {
            $row = array(
                'id_mp_stock' => 0,
                'id_product_attribute'=> $comb['id_product_attribute'],
                'movement' => $this->getMovements(),
                'name' => Tools::strtoupper($comb['name']),
                'reference' => $comb['reference'],
                'ean13' => $comb['ean13'],
                'qty' => $this->displayQuantity(0),
                'wholesale_price' => $this->displayPrice($comb['wholesale_price'], 'wholesale_price[]'),
                'price' => $this->displayPrice($comb['price'], 'price[]'),
                'tax_rate' => $this->displayPerc($comb['tax_rate'], 'input_tax_rate[]'),
                'action' => $this->displayButton('', 'icon icon-save', 'javascript:saveCombination(this);', '#3030AA')
                    .$this->displayButton('', 'icon icon-times', 'javascript:deleteCombination(this);', '#BB4040'),
                'status' => $this->displayIcon('icon_status[]', 'icon-edit', '#303090'),
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
    
    private function displayQuantity($value)
    {
        $this->smarty->assign(
            array(
                'name' => 'input_text_qty[]',
                'id' => '',
                'class' => 'input text-right fixed-width-sm input-quantity',
                'value' => $value,
            )
        );
        $input = $this->smarty->fetch($this->module->getPath().'views/templates/admin/html_element_text.tpl');
        return $input;
    }
    
    private function displayPrice($value, $name)
    {
        $this->smarty->assign(
            array(
                'name' => $name,
                'id' => '',
                'class' => 'input text-right fixed-width-sm input-price',
                'value' => Tools::displayPrice($value),
            )
        );
        $input = $this->smarty->fetch($this->module->getPath().'views/templates/admin/html_element_text.tpl');
        return $input;
    }
    
    private function displayPerc($value, $name)
    {
        $percentage = self::displayPercentage($value);
        $this->smarty->assign(
            array(
                'name' => $name,
                'id' => '',
                'class' => 'input text-right fixed-width-sm input-percent',
                'value' => $percentage
            )
        );
        $input = $this->smarty->fetch($this->module->getPath().'views/templates/admin/html_element_text.tpl');
        return $input;
    }
    
    private function displayButton($name, $icon, $callback, $color = '')
    {
        $this->smarty->assign(
            array(
                'name' => $name,
                'icon' => $icon,
                'callback' => $callback,
                'color' => $color,
            )
        );
        $input = $this->smarty->fetch($this->module->getPath().'views/templates/admin/html_element_button.tpl');
        return $input;
    }
    
    private function displayIcon($name, $icon, $color = '')
    {
        $this->smarty->assign(
            array(
                'name' => $name,
                'icon' => $icon,
                'color' => $color,
            )
        );
        $input = $this->smarty->fetch($this->module->getPath().'views/templates/admin/html_element_icon.tpl');
        return $input;
    }
    
    public static function displayPercentage($value)
    {
        return number_format(
            $value,
            2,
            self::$localeInfo['decimal_point'],
            self::$localeInfo['thousands_sep']
            ) . ' %';
    }
    
    /**
     * Get all combinations of a specified product
     * @param type $id_product product id to search
     * @return array Array of combinations 
     * ['id_product_attribute', 'reference', 'name', 'ean13', 'price', 'wholesale_price', 'tax_rate']
     */
    public static function getCombinations($id_product)
    {
        $db = Db::getInstance();
        /** Get id_product_attribute of specified id_product **/
        $sql_product_attribute = new DbQueryCore();
        $sql_product_attribute->select('id_product_attribute')
            ->select('reference')
            ->select('ean13')
            ->select('price')
            ->select('wholesale_price')
            ->from('product_attribute')
            ->where('id_product='.(int)$id_product);
        $result_product_attribute = $db->executeS($sql_product_attribute);
        if (!$result_product_attribute) {
            return array();
        }
        $combinations = array();
        $tax_rate = self::getTaxRateFromIdProduct($id_product);
        foreach ($result_product_attribute as $row) {
            $sql_combination = new DbQueryCore();
            $sql_combination->select('distinct a.id_attribute')
                ->select('al.name')
                ->from('attribute', 'a')
                ->innerJoin('attribute_lang', 'al', 'al.id_attribute=a.id_attribute')
                ->innerJoin('attribute_group', 'ag', 'ag.id_attribute_group=a.id_attribute_group')
                ->innerJoin('product_attribute_combination', 'pac', 'pac.id_attribute=a.id_attribute')
                ->where('al.id_lang='.(int) Context::getContext()->language->id)
                ->where('pac.id_product_attribute='.(int)$row['id_product_attribute'])
                ->orderBy('ag.position')
                ->orderBy('al.name');
            $result_combination = $db->executeS($sql_combination);
            $name_combination = array();
            if ($result_combination) {
                foreach ($result_combination as $attribute) {
                    $name_combination[] = $attribute['name'];
                }
                $combination = implode(' - ', $name_combination);
            }
            $combinations[] = array(
                'id_product_attribute' => $row['id_product_attribute'],
                'reference' => $row['reference'],
                'ean13' => $row['ean13'],
                'wholesale_price' => $row['wholesale_price'],
                'price' => $row['price'],
                'tax_rate' => $tax_rate,
                'name' => $combination,
            );
        }
        usort($combinations, function($a, $b) {
            $a = $a['name'];
            $b = $b['name'];

            if ($a == $b) return 0;
            return ($a < $b) ? -1 : 1;
        });
        return $combinations;
    }
    
    public static function getTaxRateFromIdProduct($id_product)
    {
        if (!$id_product) {
            return 0;
        }
        $db = Db::getInstance();
        $sql_tax_group = new DbQueryCore();
        $sql_tax_group->select('id_tax_rules_group')
            ->from('product')
            ->where('id_product='.(int)$id_product);
        $id_tax_rules_group = (int)$db->getValue($sql_tax_group);
        
        $sql = new DbQueryCore();
        $sql->select('t.rate')
            ->from('tax', 't')
            ->innerJoin('tax_rule', 'tr', 'tr.id_tax=t.id_tax')
            ->where('tr.id_tax_rules_group='.(int)$id_tax_rules_group);
        $tax_rate = $db->getValue($sql);
        return (float)$tax_rate;
    }
    
    public function addButton($link, $icon, $color = '#797979', $title = '', $newpage = true)
    {
        if ($newpage) {
            $newpage = '_blank';
        } else {
            $newpage = '';
        }
        $i = $this->addIcon($icon, $color, $title);
        $link = "<a class='btn btn-default $newpage' href='$link'>".$i."</a>";
        return $link;
    }
    
    public function addLink($link, $content)
    {
        $link = "<a href='$link'>".$content."</a>";
        return $link;
    }
    
    public function ucFirst($str)
    {
        $str_lower = Tools::strtolower($str);
        $parts = explode(' ', $str_lower);
        foreach ($parts as &$part) {
            $part = Tools::ucfirst($part);
        }
        return implode(' ', $parts);
    }
}