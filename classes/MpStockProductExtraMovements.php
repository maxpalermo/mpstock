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

Class MpStockProductExtraMovements
{
    private $search_in_orders = true;
    private $search_in_slips = true;
    private $search_in_movements = true;
    private $date_start = '';
    private $date_end = '';
    private $context;
    private $id_lang;
    private $id_shop;
    private $id_product;
    private $id_employee;
    private $id_product_attribute;
    private $smarty;
    private $module;
    private $db;
    private $tot_rows;
    private $tot_pages;
    private $pagination;
    private $page;
    private $query;
    
    public function __construct($module, $params)
    {
        $this->context = Context::getContext();
        $this->smarty = $this->context->smarty;
        $this->id_lang = (int)$this->context->language->id;
        $this->id_shop = (int)$this->context->shop->id;
        $this->db = Db::getInstance();
        $this->search_in_orders = (int)$params['search_in_orders'];
        $this->search_in_slips = (int)$params['search_in_slips'];
        $this->search_in_movements = (int)$params['search_in_movements'];
        $this->date_start = $params['date_start'].' 00:00:00';
        $this->date_end = $params['date_end'].' 23:59:59';
        $this->id_product = (int)$params['id_product'];
        $this->id_employee = (int)$params['id_employee'];
        $this->id_product_attribute = (int)$params['id_product_attribute'];
        $this->pagination = (int)$params['pagination'];
        $this->page = (int)$params['page'];
        $this->module = $module;
    }
    
    public function getRows($sql)
    {
        $result = $this->db->executeS($sql);
        if ($result) {
            return $this->prepareRows($result);
        } else {
            return array();
        }
    }
    
    public function prepareRows($rows)
    {
        /**
           =>name
           product_attribute_id
           product_quantity
           product_price
           id_tax_rules_group
           =>product_tax_rate
           product_date_add
           product_customer
           product_employee
           =>referrer
         */
        foreach ($rows as &$row)
        {
            $row['product_name'] = $this->getNameProduct($row['product_attribute_id']);
            if ((int)$row['id_tax_rules_group']) {
                $row['product_tax_rate'] = $this->getProductTaxRate($row['id_tax_rules_group']);
            }
            if ((int)$row['product_customer']) {
                $row['referrer'] = $this->getCustomerName($row['product_customer']);
            }
            if ((int)$row['product_employee']) {
                $row['referrer'] = $this->getEmployeeName($row['product_employee']);
            }
            $row['product_total'] = $row['product_qty'] * $row['product_price'];
            $row['product_amount'] = $row['product_total'] * (100 + $row['product_tax_rate']) / 100;
        }
        
        return $rows;
    }
    
    public function getNameProduct($id_product_attribute)
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $id_lang = Context::getContext()->language->id;
        $sql->select('id_attribute')
            ->from('product_attribute_combination')
            ->where('id_product_attribute = ' . (int)$id_product_attribute);
        $name = '';
        $attributes = $db->executeS($sql);
        foreach ($attributes as $attribute) {
            $attr = new AttributeCore($attribute['id_attribute']);
            $name .= ' ' . $attr->name[(int)$id_lang];
        }
        
        return $name;
    }
    
    public function getProductTaxRate($id_tax_rules_group)
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        
        $sql->select('t.rate')
            ->from('tax', 't')
            ->innerJoin('tax_rule', 'tr', 'tr.id_tax=t.id_tax')
            ->innerJoin('tax_rules_group', 'trl', 'trl.id_tax_rules_group=tr.id_tax_rules_group')
            ->where('tr.id_tax_rules_group='.(int)$id_tax_rules_group);
        
        return (float)$db->getValue($sql);
    }
    
    public function getCustomerName($id_customer)
    {
        $customer = new CustomerCore($id_customer);
        return $customer->firstname.' '.$customer->lastname;
    }
    
    public function getEmployeeName($id_employee)
    {
        $employee = new EmployeeCore($id_employee);
        return $employee->firstname . ' ' . $employee->lastname;
    }
    
    public function prepareQuery()
    {
        $sqls = array();
        $sql_count = array();
        if ($this->search_in_orders) {
            $sqls[] = $this->getQueryOrders();
            $sql_count[] = $this->getQueryCountOrders();
        }
        
        if ($this->search_in_slips) {
            $sqls[] = $this->getQuerySlips();
            $sql_count[] = $this->getQueryCountSlips();
        }
        
        if ($this->search_in_movements) {
            $sqls[] = $this->getQueryMovements();
            $sql_count[] = $this->getQueryCountMovements();
        }
        
        $query = implode(' UNION ', $sql_count);
        
        $count = $this->db->executeS($query);
        $totRows = 0;
        foreach ($count as $row) {
            $totRows+=$row['totrows'];
        }
        $this->tot_rows = $totRows;
        $start = $this->pagination * ($this->page-1);
        $this->tot_pages = ceil($this->tot_rows / $this->pagination);
        
        $query = implode(' UNION ', $sqls) 
            . 'ORDER BY product_date_add DESC, product_attribute_id ASC'
            . PHP_EOL
            . 'LIMIT '.$this->pagination.' OFFSET '.$start;
        
        $this->query = $query;
        return $query;
    }
    
    public function getQueryOrders()
    {
        $sql = new DbQueryCore();
        $sql->select('od.product_attribute_id')
            ->select('-od.product_quantity as product_qty')
            ->select('od.unit_price_tax_excl as product_price')
            ->select('od.id_tax_rules_group')
            ->select('\'0\' as product_tax_rate')
            ->select('o.date_add as product_date_add')
            ->select('o.id_customer as product_customer')
            ->select('\'0\' as product_employee')
            ->from('order_detail', 'od')
            ->innerJoin('orders', 'o', 'o.id_order=od.id_order')
            ->where('od.id_shop='.(int)$this->id_shop)
            ->where('od.product_id='.(int)$this->id_product);
        
        if (ValidateCore::isDate($this->date_start) && ValidateCore::isDate($this->date_end)) {
            $sql->where('o.date_add between \''.$this->date_start.'\' and \''.$this->date_end.'\'');
        }
        
        if ($this->id_product_attribute>0) {
            $sql->where('od.product_attribute_id='.(int)$this->id_product_attribute);
        }
        
        return $sql->__toString();
    }
    
    public function getQuerySlips()
    {
        $sql = new DbQueryCore();
        $sql->select('od.product_attribute_id')
            ->select('-osd.product_quantity as product_qty')
            ->select('osd.unit_price_tax_excl as product_price')
            ->select('od.id_tax_rules_group')
            ->select('\'0\' as product_tax_rate')
            ->select('os.date_add as product_date_add')
            ->select('os.id_customer as product_customer')
            ->select('\'0\' as product_employee')
            ->from('order_slip_detail', 'osd')
            ->innerJoin('order_detail', 'od', 'od.id_order_detail=osd.id_order_detail')
            ->innerJoin('order_slip', 'os', 'os.id_order_slip=osd.id_order_slip')
            ->where('od.id_shop='.(int)$this->id_shop)
            ->where('od.product_id='.(int)$this->id_product);
        
        if (ValidateCore::isDate($this->date_start) && ValidateCore::isDate($this->date_end)) {
            $sql->where('os.date_add between \''.$this->date_start.'\' and \''.$this->date_end.'\'');
        }
        
        if ($this->id_product_attribute>0) {
            $sql->where('od.product_attribute_id='.(int)$this->id_product_attribute);
        }
        
        return $sql->__toString();
    }
    
    public function getQueryMovements()
    {
        $sql = new DbQueryCore();
        $sql->select('mps.id_product_attribute as product_attribute_id')
            ->select('mps.qty as product_qty')
            ->select('mps.price as product_price')
            ->select('\'0\' as id_tax_rules_group')
            ->select('mps.tax_rate as product_tax_rate')
            ->select('mps.date_add as product_date_add')
            ->select('\'0\' as product_customer')
            ->select('mps.id_employee as product_employee')
            ->from('mp_stock', 'mps')
            ->where('mps.id_shop='.(int)$this->id_shop)
            ->where('mps.id_product='.(int)$this->id_product);
        
        if (ValidateCore::isDate($this->date_start) && ValidateCore::isDate($this->date_end)) {
            $sql->where('mps.date_add between \''.$this->date_start.'\' and \''.$this->date_end.'\'');
        }
        
        if ($this->id_product_attribute>0) {
            $sql->where('mps.id_product_attribute='.(int)$this->id_product_attribute);
        }
        
        return $sql->__toString();
    }
    
    public function getQueryCountOrders()
    {
        $sql = new DbQueryCore();
        $sql->select('count("*") as totrows')
            ->from('order_detail', 'od')
            ->innerJoin('orders', 'o', 'o.id_order=od.id_order')
            ->where('od.id_shop='.(int)$this->id_shop)
            ->where('od.product_id='.(int)$this->id_product);
        
        if (ValidateCore::isDate($this->date_start) && ValidateCore::isDate($this->date_end)) {
            $sql->where('o.date_add between \''.$this->date_start.'\' and \''.$this->date_end.'\'');
        }
        
        if ($this->id_product_attribute>0) {
            $sql->where('od.product_attribute_id='.(int)$this->id_product_attribute);
        }
        
        return $sql->__toString();
    }
    
    public function getQueryCountSlips()
    {
        $sql = new DbQueryCore();
        $sql->select('count("*") as totrows')
            ->from('order_slip_detail', 'osd')
            ->innerJoin('order_detail', 'od', 'od.id_order_detail=osd.id_order_detail')
            ->innerJoin('order_slip', 'os', 'os.id_order_slip=osd.id_order_slip')
            ->where('od.id_shop='.(int)$this->id_shop)
            ->where('od.product_id='.(int)$this->id_product);
        
        if (ValidateCore::isDate($this->date_start) && ValidateCore::isDate($this->date_end)) {
            $sql->where('os.date_add between \''.$this->date_start.'\' and \''.$this->date_end.'\'');
        }
        
        if ($this->id_product_attribute>0) {
            $sql->where('od.product_attribute_id='.(int)$this->id_product_attribute);
        }
        
        return $sql->__toString();
    }
    
    public function getQueryCountMovements()
    {
        $sql = new DbQueryCore();
        $sql->select('count("*") as totrows')
            ->from('mp_stock', 'mps')
            ->where('mps.id_shop='.(int)$this->id_shop)
            ->where('mps.id_product='.(int)$this->id_product);
        
        if (ValidateCore::isDate($this->date_start) && ValidateCore::isDate($this->date_end)) {
            $sql->where('mps.date_add between \''.$this->date_start.'\' and \''.$this->date_end.'\'');
        }
        
        if ($this->id_product_attribute>0) {
            $sql->where('mps.id_product_attribute='.(int)$this->id_product_attribute);
        }
        
        return $sql->__toString();
    }
}