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

Class MpStockListHelperObject
{
    private $id_product;
    private $helper;
    private $list;
    private $context;
    private $id_shop;
    private $id_lang;
    private $id_employee;
    private $table;
    private $fields_list;
    private $module;
    
    public function __construct($id_product, $id_employee = 0, $module = null) 
    {
        $this->module = $module;
        $this->id_product = $id_product;
        $this->context = Context::getContext();
        $this->id_shop = (int)$this->context->shop->id;
        $this->id_lang = (int)$this->context->language->id;
        if ($id_employee == 0) {
            $this->id_employee = (int)$this->context->employee->id;
        } else {
            $this->id_employee = $id_employee;
        }
        $this->table = 'mp_stock_list_movements';
        
        $this->fields_list = array(
            'image_url' => array(
                'title' => $this->l('Image'),
                'width' => 48,
                'type' => 'bool',
                'align' => 'text-center',
                'float' => true,
                'search' => false,
            ),
            'reference' => array(
                'title' => $this->l('Reference'),
                'width' => 'auto',
                'type' => 'text',
                'align' => 'text-left',
                'search' => false,
            ),
            'name' => array(
                'title' => $this->l('Name'),
                'width' => 'auto',
                'type' => 'text',
                'align' => 'text-left',
                'search' => false,
            ),
            'qty' => array(
                'title' => $this->l('Qty'),
                'width' => 64,
                'type' => 'bool',
                'float' => true,
                'align' => 'text-right',
                'search' => false,
            ),
            'type_movement' => array(
                'title' => $this->l('Movement'),
                'width' => 'auto',
                'type' => 'text',
                'align' => 'text-left',
                'search' => false,
            ),
            'date_add' => array(
                'title' => $this->l('Date'),
                'width' => 'auto',
                'type' => 'date',
                'align' => 'text-center',
                'search' => true,
            ),
            'employee' => array(
                'title' => $this->l('Employee'),
                'width' => 'auto',
                'type' => 'text',
                'align' => 'text-left',
                'search' => false,
            ),
        );
    }
    
    public function display()
    {
        return $this->displayForm() . $this->displayList();
    }
    
    public function displayForm()
    {
        if ($this->date_start || $this->date_end) {
            return '';
        }
        $smarty = Context::getContext()->smarty;
        return $smarty->fetch($this->module->getPath().'views/templates/admin/helperListForm.tpl');
    }
    
    public function displayList()
    {
        $this->helper = new HelperListCore();
        $this->helper->_default_pagination = Tools::getValue('selected_pagination', 100);
        $this->helper->_pagination = array(
            5,
            10,
            20,
            50,
            100,
            500,
            1000,
        );
        $this->helper->page = Tools::getValue('page', 1);
        $this->helper->shopLinkType = '';
        $this->helper->simple_header = false;
        $this->helper->identifier = 'id_mp_stock';
        $this->helper->show_toolbar = true;
        $this->helper->title = $this->l('Stock movements');
        $this->helper->table = $this->table;
        $this->helper->token = '';
        $this->helper->currentIndex = '';
        $this->helper->no_link = true;
        $this->helper->toolbar_btn = array(
            'terminal' => array(
                'href' => 'javascript:mpstock_printReport();',
                'desc' => $this->l('Print report'),
                'style' => 'color: #BB8888;'
            ),
            'stats' => array(
                'href' => 'javascript:mpstock_statistics();',
                'desc' => $this->l('Statistics'),
                'style' => 'color: #88BB88;'
            )
        );
        $this->list = $this->getList($this->helper->_default_pagination, $this->helper->page);
        $table = $this->helper->generateList($this->list, $this->fields_list);
        return $table;
    }
    
    public function getList($pagination, $page)
    {
        $movements = $this->prepareList(
            array_merge(
                $this->getListProductsStock($pagination, $page),
                $this->getListOrders($pagination, $page),
                $this->getListOrderSlip($pagination, $page)
            )
        );
        
        self::resetTable($this->id_employee);
        
        $db = Db::getInstance();
        $idx = 0;
        foreach ($movements as $movement) {
            $movement['idx'] = $idx;
            $movement['id_employee_bo'] = $this->id_employee;
            try {
                $db->insert(
                    $this->table,
                    $movement,
                    true,
                    false,
                    Db::INSERT_IGNORE);
            } catch (Exception $ex) {
                $movement['employee'] = pSQL($movement['employee']);
                $db->insert(
                    $this->table,
                    $movement,
                    true,
                    false,
                    Db::INSERT_IGNORE);
            }
                
            $idx++;
        }
        
        return $this->pagination();
    }
    
    public function pagination()
    {
        PrestaShopLoggerCore::addLog('$this->table='.$this->table);
        if (Tools::isSubmit('submitFilter'.$this->table)) {
            $this->helper->_default_pagination = (int)Tools::getValue($this->table.'_pagination', 20);
            $start = (int)Tools::getValue('submitFilter'.$this->table, 0) * $this->helper->_default_pagination;
        } else {
            $start = 0;
        }
        $end = $start + $this->helper->_default_pagination - 1;
        
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('st.*')
            ->from($this->table, 'st')
            ->where('id_employee_bo='.(int)$this->id_employee)
            ->where('idx between ' . $start . ' and ' . $end)
            ->orderBy('date_add DESC');
        PrestaShopLoggerCore::addLog($sql->__toString());
        $result = $db->executeS($sql);
        if ($result) {
            foreach($result as &$row) {
                $row['image_url'] = $this->img($row['image_url']);
                $row['qty'] = $this->QtyHtml($row['qty']);
            }
            $movements = $result;
        } else {
            $movements = array();
        }
        $this->helper->listTotal = $this->countMovements();
        return $movements;
    }
    
    public function countMovements()
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('count(*)')
            ->from('mp_stock_list_movements')
            ->where('id_employee_bo='.(int)$this->id_employee);
        return (int)$db->getValue($sql);
    }
    
    /**
     * Get all stock movements about current shop
     * @return array array of movements found
     * array (
     *   'id_mp_stock'
     *   'id_mp_stock_exchange'
     *   'id_shop'
     *   'id_product'
     *   'id_product_attribute'
     *   'id_mp_stock_type_movement'
     *   'qty'
     *   'price'
     *   'tax_rate'
     *   'date_add'
     *   'id_employee'
     *   'name'
     * )
     */
    public function getListProductsStock()
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('st.id_product')
            ->select('st.id_product_attribute')
            ->select('st.id_employee as id_employee_movement')
            ->select('st.qty')
            ->select('ts.name as type_movement')
            ->select('st.date_add')
            ->select("UPPER(CONCAT(e.firstname,' ',e.lastname)) as employee")
            ->from('mp_stock', 'st')
            ->innerJoin('mp_stock_type_movement', 'ts', 'ts.id_mp_stock_type_movement=st.id_mp_stock_type_movement')
            ->innerJoin('employee', 'e', 'e.id_employee=st.id_employee')
            ->where('st.id_shop='.(int)$this->id_shop)
            ->where('st.id_product='.(int)$this->id_product)
            ->orderBy('id_mp_stock DESC');
        
        if ($this->date_start && $this->date_end) {
            $sql->where(
                'st.date_add between \'' . pSQL($this->date_start) . '\' and \'' . pSQL($this->date_end) . '\''
            );
        }
        PrestaShopLoggerCore::addLog('SQL DATE: ' . $sql->__toString());
        $result = $db->executeS($sql);
        if ($result) {
            return $result;
        } else {
            return array();
        }
    }
    
    /**
     * Get all stock movements from orders
     * @return array array of movements found
     * array(
     'id_mp_stock'
     *   'id_order_detail'
     *   'id_order'
     *   'product_id'
     *   'product_attribute_id'
     *   'product_quantity' 
     *   'name' = 'ref order #order'
     *   'date_add' = #order_date_add
     * )
     */
    public function getListOrders($ids = null)
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select("'0' as id_employee_movement")
            ->select('od.product_id as id_product')
            ->select('od.product_attribute_id as id_product_attribute')
            ->select('o.date_add')
            ->select('UPPER(CONCAT(c.firstname,\' \',c.lastname)) as employee')
            ->select('UPPER(CONCAT(\'' . $this->l('Order reference') . '\', \' \', o.reference)) as `type_movement`')
            ->from('order_detail', 'od')
            ->innerJoin('orders', 'o', 'o.id_order=od.id_order')
            ->innerJoin('customer', 'c', 'c.id_customer=o.id_customer')
            ->where('od.product_id='.(int)$this->id_product)
            ->where('o.id_shop='.(int)$this->id_shop)
            ->orderBy('o.date_add DESC');
        if ($ids) {
            $sql->select('od.product_quantity as qty');
            $sql->innerJoin('order_slip_detail', 'osd', 'osd.id_order_detail=od.id_order_detail');
            $sql->where('od.id_order_detail in (' . implode(',', $ids) . ')');
        } else {
            $sql->select('CAST(-od.product_quantity as SIGNED) as qty');
        }
        if ($this->date_start && $this->date_end) {
            $sql->where(
                'o.date_add between \'' . pSQL($this->date_start) . '\' and \'' . pSQL($this->date_end) . '\''
            );
        }
        
        try {
            $result = $db->executeS($sql);
            if ($result) {
                return $result;
            } else {
                return array();
            }
        } catch (Exception $ex) {
            PrestaShopLoggerCore::addLog($ex->getMessage());
            return array();
        }
    }
    
    public function getListOrderSlip()
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select("od.id_order_detail")
            ->from('order_detail', 'od')
            ->innerJoin('order_slip_detail', 'osd', 'osd.id_order_detail=od.id_order_detail')
            ->where('od.product_id='.(int)$this->id_product);
        $result = $db->executeS($sql);
        $ids = array();
        foreach ($result as $row) {
            $ids[] = $row['id_order_detail'];
        }
        return $this->getListOrders($ids);
    }
    
    public function prepareList($list)
    {
        $idx = 0;
        foreach ($list as &$row) {
            $id_product = (int)$row['id_product'];
            $id_product_attribute = (int)$row['id_product_attribute'];
            $attributes = $this->getProductAttribute($id_product, $id_product_attribute);
            PrestaShopLoggerCore::addLog('attributes:' . print_r($attributes,1));
            $row['image_url'] = $this->getImageProductAttribute($id_product_attribute, $id_product);
            $row['reference'] = pSQL($attributes['reference']);
            $row['name'] = pSQL($attributes['name']);
            $row['idx'] = $idx++;
            $row['id_employee_bo'] = $this->id_employee;
            $row['employee'] = pSQL($row['employee']);
        }
        return $list;
    }
    
    public function getProductAttribute($id_product, $id_product_attribute)
    {
        $id_lang = (int) Context::getContext()->language->id;
        $db = Db::getInstance();
        
        //REFERENCE
        $sql = new DbQueryCore();
        $sql->select('reference')
            ->from('product_attribute')
            ->where('id_product_attribute='.(int)$id_product_attribute);
        $reference = $db->getValue($sql);
        
        //NAME
        $sql_name = new DbQueryCore();
        $sql_name->select('name')
            ->from('product_lang')
            ->where('id_product = ' . (int)$id_product)
            ->where('id_lang = ' . (int)$id_lang);
        $name = $db->getValue($sql_name);
      
        //ATTRIBUTES
        $sql_attr = new DbQueryCore();
        $sql_attr->select('id_attribute')
            ->from('product_attribute_combination')
            ->where('id_product_attribute = ' . (int)$id_product_attribute);
        $attributes = $db->executeS($sql_attr);
        foreach($attributes as $attribute) {
            $attr = new AttributeCore((int)$attribute['id_attribute']);
            $name .= ' ' . $attr->name[(int)$id_lang];
        }
        
        return array(
            'reference' => $reference,
            'name' => $name,
        );
    }
    
    public static function resetTable($id_employee)
    {
        $db = Db::getInstance();
        $db->delete(
            'mp_stock_list_movements',
            'id_employee_bo='.(int)$id_employee
        );
    }
    
    public static function getImageProduct($id_product)
    {
        $id_shop = (int)Context::getContext()->shop->id;
        $shop = new ShopCore($id_shop);
        if ((int)$id_product == 0) {
            PrestaShopLoggerCore::addLog('Invalid id product.');
            return $shop->getBaseURL(true) . 'img/404.gif';
        }
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('id_image')
            ->from('image')
            ->where('id_product='.(int)$id_product)
            ->where('cover IS NOT NULL');
        PrestaShopLoggerCore::addLog('sql==>' . $sql->__toString());
        $id_image = (int)$db->getValue($sql);
        if ((int)$id_image==0) {
            return $shop->getBaseURL(true) . 'img/404.gif';
        }
        $image = new ImageCore($id_image);
        $image_path = $shop->getBaseURL(true) . 'img/p/'. $image->getExistingImgPath() . '-small.jpg';
        return $image_path;
    }
    
    public static function getImageProductAttribute($id_product_attribute, $id_product)
    {
        $id_shop = (int)Context::getContext()->shop->id;
        $shop = new ShopCore($id_shop);
        if ((int)$id_product_attribute == 0) {
            PrestaShopLoggerCore::addLog('Invaid id product attribute.');
            return $shop->getBaseURL(true) . 'img/404.gif';
        }
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('id_image')
            ->from('product_attribute_image')
            ->where('id_product_attribute='.(int)$id_product_attribute);
        $id_image = (int)$db->getValue($sql);
        if ((int)$id_image==0) {
            PrestaShopLoggerCore::addLog('Invaid image attribute. get image product.');
            return self::getImageProduct($id_product);
        }
        $image = new ImageCore($id_image);
        $image_path = $shop->getBaseURL(true) . 'img/p/'. $image->getExistingImgPath() . '-small.jpg';
        return $image_path;
    }
    
    public static function getEmployeeName($id_employee)
    {
        if ((int)$id_employee == 0) {
            return '--';
        }
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('firstname')
            ->select('lastname')
            ->from('employee')
            ->where('id_employee = ' . (int)$id_employee);
        $row = $db->getRow($sql);
        if ($row) {
            return $row['firstname'] . ' ' . $row['lastname'];
        } else {
            return '--';
        }
    }
    
    public static function img($url) 
    {
        return "<img src='" . $url . "' style='max-width: 48px; max-height: 48px; object-fit: contain;'>";
    }
    
    public function findMovements($date_start, $date_end)
    {
        $this->date_start = $date_start . ' 00:00:00';
        $this->date_end = $date_end . ' 23:59:59';
        PrestaShopLoggerCore::addLog('start: ' . $this->date_start);
        PrestaShopLoggerCore::addLog('end: ' . $this->date_end);
        $html = $this->display();
        print Tools::jsonEncode(
            array(
                'result' => true,
                'html' => $html,
            )
        );
        exit();
    }
    
    public function exportCSV()
    {
        $list = $this->getListFromTable();
        foreach ($list as &$row) {
            unset($row['id_mp_stock_list_movements']);
            unset($row['image_url']);
            unset($row['id_employee_bo']);
            unset($row['id_employee_movement']);
            unset($row['idx']);
        }
        if (count($list)) {
            $header = array();
            foreach ($list[0] as $key=>$value) {
                $header[] = $key;
            }
            array_unshift($list, $header);
            $this->array_to_csv_download($list);
        }
            
        exit();
    }
    
    public function getListFromTable()
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('*')
            ->from($this->table)
            ->where('id_employee_bo='.(int)$this->id_employee)
            ->orderBy('idx');
        return $db->executeS($sql);
    }
    
    public function array_to_csv_download($array, $filename = "export.csv", $delimiter=";") 
    {
        // open raw memory as file so no temp files needed, you might run out of memory though
        $f = fopen('php://memory', 'w'); 
        // loop over the input array
        foreach ($array as $line) { 
            // generate csv lines from the inner arrays
            fputcsv($f, $line, $delimiter); 
        }
        // reset the file pointer to the start of the file
        fseek($f, 0);
        // tell the browser it's going to be a csv file
        header('Content-Type: application/csv');
        // tell the browser we want to save it instead of displaying it
        header('Content-Disposition: attachment; filename="'.$filename.'";');
        // make php send the generated csv lines to the browser
        fpassthru($f);
        exit();
    }
    
    public function QtyHtml($qty)
    {
        if ($qty>0) {
            return '<i class="icon-arrow-right" style="color: #1fc62d;"></i> <strong>'. abs($qty) . '</strong>';
        } else {
            return '<i class="icon-arrow-left" style="color: #c12020;"></i> <strong>'. abs($qty) . '</strong>';
        }
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