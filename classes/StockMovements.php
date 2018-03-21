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

Class MpStockStockMovements
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
    private $smarty;
    private $module;
    private $db;
    
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
        $this->module = $module;
        
    }
    
    public function getMovements()
    {
        $query = "<pre>" . $this->prepareQuery() . "</pre>";
        $table = $this->smarty->fetch($this->module->getPath().'views/templates/admin/ProductExtraTableMovements.tpl');
        return $query . $table;
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
        
        $count = $this->db->executeS(implode(' UNION ', $sql_count));
        $totRows = 0;
        foreach ($count as $row) {
            $totRows+=$row['totrows'];
        }
        print "totrows: " . $totRows;
        
        $query = implode(' UNION ', $sqls) 
            . 'ORDER BY product_date_add DESC'
            . PHP_EOL
            . 'LIMIT 30';
        return $query;
    }
    
    public function getQueryOrders()
    {
        $sql = new DbQueryCore();
        $sql->select('od.product_attribute_id')
            ->select('od.product_quantity as product_qty')
            ->select('od.unit_price_tax_excl as product_price')
            ->select('od.id_tax_rules_group')
            ->select('\'0\' as product_tax_rate')
            ->select('o.date_add as product_date_add')
            ->select('o.id_customer as product_customer')
            ->select('\'0\' as product_employee')
            ->from('order_detail', 'od')
            ->innerJoin('orders', 'o', 'o.id_order=od.id_order')
            ->where('o.date_add between \''.$this->date_start.'\' and \''.$this->date_end.'\'')
            ->where('od.id_shop='.(int)$this->id_shop);
        return $sql->__toString();
    }
    
    public function getQuerySlips()
    {
        $sql = new DbQueryCore();
        $sql->select('od.product_attribute_id')
            ->select('osd.product_quantity as product_qty')
            ->select('osd.unit_price_tax_excl as product_price')
            ->select('od.id_tax_rules_group')
            ->select('\'0\' as product_tax_rate')
            ->select('os.date_add as product_date_add')
            ->select('os.id_customer as product_customer')
            ->select('\'0\' as product_employee')
            ->from('order_slip_detail', 'osd')
            ->innerJoin('order_detail', 'od', 'od.id_order_detail=osd.id_order_detail')
            ->innerJoin('order_slip', 'os', 'os.id_order_slip=osd.id_order_slip')
            ->where('os.date_add between \''.$this->date_start.'\' and \''.$this->date_end.'\'')
            ->where('od.id_shop='.(int)$this->id_shop);
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
            ->where('mps.date_add between \''.$this->date_start.'\' and \''.$this->date_end.'\'')
            ->where('mps.id_shop='.(int)$this->id_shop);
        return $sql->__toString();
    }
    
    public function getQueryCountOrders()
    {
        $sql = new DbQueryCore();
        $sql->select('count("*") as totrows')
            ->from('order_detail', 'od')
            ->innerJoin('orders', 'o', 'o.id_order=od.id_order')
            ->where('o.date_add between \''.$this->date_start.'\' and \''.$this->date_end.'\'')
            ->where('od.id_shop='.(int)$this->id_shop);
        return $sql->__toString();
    }
    
    public function getQueryCountSlips()
    {
        $sql = new DbQueryCore();
        $sql->select('count("*") as totrows')
            ->from('order_slip_detail', 'osd')
            ->innerJoin('order_detail', 'od', 'od.id_order_detail=osd.id_order_detail')
            ->innerJoin('order_slip', 'os', 'os.id_order_slip=osd.id_order_slip')
            ->where('os.date_add between \''.$this->date_start.'\' and \''.$this->date_end.'\'')
            ->where('od.id_shop='.(int)$this->id_shop);
        return $sql->__toString();
    }
    
    public function getQueryCountMovements()
    {
        $sql = new DbQueryCore();
        $sql->select('count("*") as totrows')
            ->from('mp_stock', 'mps')
            ->where('mps.date_add between \''.$this->date_start.'\' and \''.$this->date_end.'\'')
            ->where('mps.id_shop='.(int)$this->id_shop);
        return $sql->__toString();
    }
    
    /**
     * Non-static method which uses AdminController::translate()
     *
     * @param string  $string Term or expression in english
     * @param string|null $class Name of the class
     * @param bool $addslashes If set to true, the return value will pass through addslashes(). Otherwise, stripslashes().
     * @param bool $htmlentities If set to true(default), the return value will pass through htmlentities($string, ENT_QUOTES, 'utf-8')
     * @return string The translation if available, or the english default text.
     */
    protected function l($string, $class = null, $addslashes = false, $htmlentities = true)
    {
        if ($class === null || $class == 'AdminTab') {
            $class = substr(get_class($this), 0, -10);
        } elseif (strtolower(substr($class, -10)) == 'controller') {
            /* classname has changed, from AdminXXX to AdminXXXController, so we remove 10 characters and we keep same keys */
            $class = substr($class, 0, -10);
        }
        return Translate::getAdminTranslation($string, $class, $addslashes, $htmlentities);
    }
}