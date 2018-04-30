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

require_once dirname(__FILE__).'/MpStockObjectModel.php';

Class MpStockProductExtraHelperList extends HelperListCore
{
    public $context;
    public $values;
    public $id_lang;
    public $module;
    public $link;
    protected $cookie;
    protected $className = 'AdminMpStock';
    protected $localeInfo;
    protected $table_name = 'mp_stock';
    protected $current_pagination = 20;
    protected $current_page = 0;
    protected $id_product=0;
    
    public function __construct($module, $pagination = 20, $page = 0)
    {
        $this->module = $module;
        $this->context = Context::getContext();
        $this->link = new LinkCore();
        $this->values = array();
        $this->id_lang = (int)$this->context->language->id;
        parent::__construct();
        $this->cookie = Context::getContext()->cookie;
        if (Context::getContext()->language->iso_code == 'it') {
            $this->localeInfo = array(
                'decimal_point' => ',',
                'thousands_sep' => '.'
            );
        } else {
            $this->localeInfo = array(
                'decimal_point' => '.',
                'thousands_sep' => ','
            );
        }
        $this->current_pagination = $pagination;
        $this->current_page = $page;
    }
    
    public function display($id_mp_stock_import = 0)
    {
        $this->id_product = (int)Tools::getValue('id_product', 0);
        $this->bootstrap = true;
        $this->actions = array('edit');
        $this->currentIndex = $this->context->link->getAdminLink('AdminProducts', false)
            .'&key_tab=ModuleMpstock'
            .'&id_product='.$this->id_product
            .'&updateproduct'
            .'&show_movements';
        $this->identifier = 'id_mp_stock';
        $this->no_link = true;
        $this->page = Tools::getValue('submitFilterconfiguration', 1);
        $this->_default_pagination = Tools::getValue('configuration_pagination', 20);
        $this->show_toolbar = true;
        $this->toolbar_btn = array(
            'back' => array(
                'desc' => $this->module->l('Back to products', get_class($this)),
                'href' => 'javascript:void(0)'
            ),
        );
        $this->shopLinkType='';
        $this->simple_header = false;
        $this->token = Tools::getAdminTokenLite('AdminProducts');
        $this->title = $this->module->l('Movements found', get_class($this));
        $this->table = 'mp_stock';
        
        $list = $this->getList($this->id_product);
        $fields_display = $this->getFields();
        
        return $this->generateList($list, $fields_display).$this->displayScript();
    }
    
    private function displayScript()
    {
        return $this->module->smarty->fetch($this->module->getPath().'views/templates/front/form.tpl');
    }
    
    private function getFields()
    {
        $list = array();
        $this->addText(
            $list,
            $this->module->l('Id', get_class($this)),
            'id_mp_stock',
            48,
            'text-right'
        );
        $this->addHtml(
            $list,
            $this->module->l('Image', get_class($this)),
            'image',
            48,
            'text-center'
        );
        $this->addText(
            $list,
            $this->module->l('Type movement', get_class($this)),
            'movement',
            'auto',
            'text-left'
        );
        $this->addText(
            $list,
            $this->module->l('Filename', get_class($this)),
            'filename',
            'auto',
            'text-left'
        );
        $this->addText(
            $list,
            $this->module->l('Reference', get_class($this)),
            'reference',
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
        $this->addPrice(
            $list,
            $this->module->l('Wholesale Price', get_class($this)),
            'wholesale_price',
            'auto',
            'text-right'
        );
        $this->addPrice(
            $list,
            $this->module->l('Price', get_class($this)),
            'price',
            'auto',
            'text-right'
        );
        $this->addHtml(
            $list,
            $this->module->l('Tax rate', get_class($this)),
            'tax_rate',
            'auto',
            'text-right'
        );
        $this->addHtml(
            $list,
            $this->module->l('Qty', get_class($this)),
            'qty',
            48,
            'text-right'
        );
        $this->addDate(
            $list,
            $this->module->l('Date movement', get_class($this)),
            'date_add',
            'auto',
            'text-center',
            true
        );
        $this->addText(
            $list,
            $this->module->l('Customer', get_class($this)),
            'customer',
            'auto',
            'text-left'
        );
        
        return $list;
    }
    
    private function addText(&$list, $title, $key, $width, $alignment, $search = false)
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
    
    private function addDate(&$list, $title, $key, $width, $alignment, $search = false)
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
    
    private function addPrice(&$list, $title, $key, $width, $alignment, $search = false)
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
    
    private function addHtml(&$list, $title, $key, $width, $alignment, $search = false)
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

    private function addIcon($icon, $color, $title = '')
    {
        return "<i class='icon $icon' style='color: $color;'></i> ".$title;
    }
    
    private function getList($id_product = 0)
    {
        $submit = 'submitFilter';
        $current_page_field = $submit.$this->table_name;
        $date_start = '';
        $date_end = '';
        $search_in_orders = (int)ConfigurationCore::get('MP_STOCK_SEARCH_IN_ORDERS');
        $search_in_slips = (int)ConfigurationCore::get('MP_STOCK_SEARCH_IN_SLIPS');
        $search_in_movements = (int)ConfigurationCore::get('MP_STOCK_SEARCH_IN_MOVEMENTS');
        $this->page = $this->current_page;
        $this->_default_pagination = $this->current_pagination;
        $filterDate = $this->table_name.'Filter'.'_date_movement';
        $dates = Tools::getValue($filterDate, array());
        $record_count = 0;
        $sqls = array();
        if (isset($dates[0])) {
            $date_start = $dates[0] . ' 00:00:00';
        }
        if (isset($dates[1])) {
            $date_end = $dates[1] . ' 23:59:59';
        }
        
        $db = Db::getInstance();
        
        /** SEARCH IN ORDERS **/
        if ($search_in_orders) {
            $sql_count = new DbQueryCore();
            $sql_count->select('count(*)')
                ->from('order_detail', 'od')
                ->innerJoin('orders', 'o', 'o.id_order=od.id_order')
                ->where('o.id_shop='.(int)$this->module->getIdShop())
                ->where('od.product_id='.(int)$this->id_product);
            $sql = new DbQueryCore();
            $sql->select('od.id_order_detail as id')
                ->select('\'orders\' as tablename' )
                ->select('od.product_id as id_product')
                ->select('od.product_attribute_id as id_product_attribute')
                ->select('od.product_quantity as qty')
                ->select('od.product_ean13 as ean13')
                ->select('od.product_reference as reference')
                ->select('od.unit_price_tax_incl as price')
                ->select('od.original_wholesale_price as wholesale_price')
                ->select('o.id_customer')
                ->select('o.date_add')
                ->from('order_detail', 'od')
                ->innerJoin('orders', 'o', 'o.id_order=od.id_order')
                ->where('o.id_shop='.(int)$this->module->getIdShop())
                ->where('od.product_id='.(int)$this->id_product);
            if ($date_start) {
                $sql_count->where('o.date_add >= \''.pSQL($date_start).'\'');
                $sql->where('o.date_add >= \''.pSQL($date_start).'\'');
            }
            if ($date_end) {
                $sql_count->where('o.date_add <= \''.pSQL($date_end).'\'');
                $sql->where('o.date_add >= \''.pSQL($date_start).'\'');
            }
            $record_count += (int)$db->getValue($sql_count);
            $sqls[] = $sql->build();
        }
        /** SEARCH IN SLIPS **/
        if ($search_in_slips) {
            $sql_count = new DbQueryCore();
            $sql_count->select('count(*)')
                ->from('order_slip_detail', 'osd')
                ->innerJoin('order_detail', 'od', 'od.id_order_detail=osd.id_order_detail')
                ->innerJoin('orders', 'o', 'o.id_order=od.id_order')
                ->where('o.id_shop='.(int)$this->module->getIdShop())
                ->where('od.product_id='.(int)$this->id_product);
            $sql = new DbQueryCore();
            $sql->select('od.id_order_detail as id')
                ->select('\'slips\' as tablename' )
                ->select('od.product_id as id_product')
                ->select('od.product_attribute_id as id_product_attribute')
                ->select('osd.product_quantity as qty')
                ->select('\'\' as ean13')
                ->select('\'\' as reference')
                ->select('osd.unit_price_tax_incl as price')
                ->select('\'0\' as wholesale_price')
                ->select('o.id_customer')
                ->select('o.delivery_date as date_add')
                ->from('order_slip_detail', 'osd')
                ->innerJoin('order_detail', 'od', 'od.id_order_detail=osd.id_order_detail')
                ->innerJoin('orders', 'o', 'o.id_order=od.id_order')
                ->where('o.id_shop='.(int)$this->module->getIdShop())
                ->where('od.product_id='.(int)$this->id_product);
            if ($date_start) {
                $sql_count->where('o.delivery_date >= \''.pSQL($date_start).'\'');
                $sql->where('o.delivery_date >= \''.pSQL($date_start).'\'');
            }
            if ($date_end) {
                $sql_count->where('o.delivery_date <= \''.pSQL($date_end).'\'');
                $sql->where('o.delivery_date >= \''.pSQL($date_start).'\'');
            }
            $record_count += (int)$db->getValue($sql_count);
            $sqls[] = $sql->build();
        }
        /** SEARCH IN MOVEMENTS **/
        if ($search_in_movements) {
            $sql_count = new DbQueryCore();
            $sql_count->select('count(*)')
                ->from('mp_stock', 'st')
                ->where('st.id_shop='.(int)$this->module->getIdShop())
                ->where('st.id_product='.(int)$this->id_product);
            $sql = new DbQueryCore();
            $sql->select('st.id_mp_stock as id')
                ->select('\'movements\' as tablename')
                ->select('st.id_product')
                ->select('st.id_product_attribute')
                ->select('st.qty')
                ->select('\'\' as ean13')
                ->select('\'\' as reference')
                ->select('st.price')
                ->select('st.wholesale_price')
                ->select('st.id_employee id_customer')
                ->select('date_add')
                ->from('mp_stock', 'st')
                ->where('st.id_shop='.(int)$this->module->getIdShop())
                ->where('st.id_product='.(int)$this->id_product);
            if ($date_start) {
                $sql_count->where('o.delivery_date >= \''.pSQL($date_start).'\'');
                $sql->where('o.delivery_date >= \''.pSQL($date_start).'\'');
            }
            if ($date_end) {
                $sql_count->where('o.delivery_date <= \''.pSQL($date_end).'\'');
                $sql->where('o.delivery_date >= \''.pSQL($date_start).'\'');
            }
            $record_count += (int)$db->getValue($sql_count);
            $sqls[] = $sql->build();
        }
        
        $this->listTotal = (int)$record_count;
        $query = implode(' union ', $sqls);
        $query .= 'order by date_add DESC'
            . ' limit ' .(int)$this->current_pagination 
            . ' offset ' . (int)$this->current_pagination*(int)$this->current_page;
        PrestaShopLoggerCore::addLog('QUERY MOVEMENT: '.$query);
        
        //Save query in cookies
        Context::getContext()->cookie->export_query = $query;
        
        $result = $db->executeS($query);
        
        if ($result) {
            foreach ($result as &$row) {
                $row['image'] = MpStockTools::getImageProduct((int)$row['id_product']);
                $row['tax_rate'] = $this->getTaxRateFromIdProduct((int)$row['id_product']);
                $row['qty'] = $this->displayQuantity($row['qty']);
                $row['price'] = Tools::displayPrice($row['price']);
                $row['wholesale_price'] = Tools::displayPrice($row['wholesale_price']);
                $row['customer'] = $this->getCustomer($row['tablename'], $row['id_customer']);
                if ($row['tablename']=='movements') {
                    $row['movement'] = $this->getMovementType($row['id']);
                    $row['reference'] = $this->getReference($row['id']);
                    $row['name'] = $this->getName($row['id']);
                    $row['customer'] = $this->getCustomerName($row['id_customer'], true);
                } else {
                    $row['movement'] = Tools::strtoupper($row['tablename']);
                    $row['customer'] = $this->getCustomerName($row['id_customer'], false);
                }
            }
        }
        
        return $result;
    }
    
    public function getCustomerName($id_customer, $isEmplpoyee)
    {
        if ($isEmplpoyee) {
            $class = new EmployeeCore($id_customer);
        } else {
            $class = new CustomerCore($id_customer);
        }
        
        return MpStockTools::ucFirst($class->firstname . ' ' . $class->lastname);
    }
    
    public function getName($id_movement)
    {
        $movement = new MpStockObjectModel($id_movement);
        return $movement->name;
    }
    
    public function getReference($id_movement)
    {
        $movement = new MpStockObjectModel($id_movement);
        return $movement->reference;
    }
    
    public function getMovementType($id_movement)
    {
        $movement = new MpStockObjectModel($id_movement);
        return $movement->movement;
    }
    
    public function getTaxRateFromIdProduct($id_product)
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('t.rate')
            ->from('tax', 't')
            ->innerJoin('tax_rule', 'tr', 't.id_tax=tr.id_tax')
            ->innerJoin('product', 'p', 'p.id_tax_rules_group=tr.id_tax_rules_group')
            ->where('p.id_product='.(int)$id_product);
        return (float)$db->getValue($sql);
    }
    
    public function getCustomer($tablename, $id_customer)
    {
        return 'customer --';
    }
    
    public function displayTaxRate($value)
    {
        $output =  number_format(
            $value,
            2,
            $this->localeInfo['decimal_point'],
            $this->localeInfo['thousands_sep']
        ) . ' %';
        
        
        return $output;
    }
    
    public function displayQuantity($value)
    {
        $smarty = Context::getContext()->smarty;
        if ($value>0) {
            $smarty->assign(
                array(
                    'style' => array(
                        'color' => '#50BB50',
                        'font-weight' => 'bold',
                    ),
                    'value' => $value,
                )
            );
        } else {
            $smarty->assign(
                array(
                    'style' => array(
                        'color' => '#BB5050',
                        'font-weight' => 'bold',
                    ),
                    'value' => $value,
                )
            );
        }
        
        return $smarty->fetch($this->module->getPath().'views/templates/admin/html_element_span.tpl');
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
    
    public function getNameProduct($id_product)
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('name')
            ->from('product_lang')
            ->where('id_lang='.(int)$this->id_lang)
            ->where('id_product='.(int)$id_product);
        return $db->getvalue($sql);
    }
    
    public function getProductNameCombination($id_product, $id_product_attribute)
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $id_lang = Context::getContext()->language->id;
        $sql->select('id_attribute')
            ->from('product_attribute_combination')
            ->where('id_product_attribute = ' . (int)$id_product_attribute);
        $name = $this->getNameProduct($id_product);
        $attributes = $db->executeS($sql);
        foreach($attributes as $attribute) {
            $attr = new AttributeCore($attribute['id_attribute']);
            $name .= ' ' . $attr->name[(int)$id_lang];
        }
        
        return $name;
    }
}
