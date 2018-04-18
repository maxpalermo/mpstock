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

Class MpStockAdminHelperListMovements extends HelperListCore
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
    
    public function __construct($module)
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
    }
    
    public function display($id_mp_stock_import = 0)
    {
        $this->bootstrap = true;
        $this->actions = array('edit');
        $this->currentIndex = $this->context->link->getAdminLink($this->className, false)
            .'&id_mp_stock_import='.(int)$id_mp_stock_import
            .'&updatemp_stock_import';
        $this->identifier = 'id_mp_stock';
        $this->no_link = true;
        $this->page = Tools::getValue('submitFilterconfiguration', 1);
        $this->_default_pagination = Tools::getValue('configuration_pagination', 20);
        $this->show_toolbar = true;
        $this->toolbar_btn = array(
            'plus' => array(
                'desc' => $this->l('Add new movement'),
                'href' => $this->link->getAdminLink($this->className).'&addMovement',
            ),
            'upload' => array(
                'desc' => $this->l('Import from XML'),
                'href' => 'javascript:importXML();',
            ),
            'download' => array(
                'desc' => $this->l('Export to XML'),
                'href' => 'javascript:exportXML();',
            ),
            'back' => array(
                'desc' => $this->l('Back to documents'),
                'href' => $this->link->getAdminLink($this->className),
            ),
        );
        $this->shopLinkType='';
        $this->simple_header = false;
        $this->token = Tools::getAdminTokenLite($this->className);
        $this->title = $this->l('Movements found');
        $this->table = 'mp_stock';
        
        $list = $this->getList($id_mp_stock_import);
        $fields_display = $this->getFields();
        
        return $this->generateList($list, $fields_display);
    }
    
    protected function getFields()
    {
        $list = array();
        $this->addText(
            $list,
            $this->l('Id'),
            'id_mp_stock',
            48,
            'text-right'
        );
        $this->addHtml(
            $list,
            $this->l('Image'),
            'image',
            48,
            'text-center'
        );
        $this->addText(
            $list,
            $this->l('Type movement'),
            'movement',
            'auto',
            'text-left'
        );
        $this->addText(
            $list,
            $this->l('Filename'),
            'filename',
            'auto',
            'text-left'
        );
        $this->addText(
            $list,
            $this->l('Reference'),
            'reference',
            'auto',
            'text-left'
        );
        $this->addText(
            $list,
            $this->l('Name'),
            'name',
            'auto',
            'text-left'
        );
        $this->addPrice(
            $list,
            $this->l('Wholesale Price'),
            'wholesale_price',
            'auto',
            'text-right'
        );
        $this->addPrice(
            $list,
            $this->l('Price'),
            'price',
            'auto',
            'text-right'
        );
        $this->addHtml(
            $list,
            $this->l('Tax rate'),
            'tax_rate',
            'auto',
            'text-right'
        );
        $this->addHtml(
            $list,
            $this->l('Qty'),
            'qty',
            48,
            'text-right'
        );
        $this->addDate(
            $list,
            $this->l('Date movement'),
            'date_movement',
            'auto',
            'text-center',
            true
        );
        $this->addText(
            $list,
            $this->l('Employee'),
            'employee',
            'auto',
            'text-left'
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
    
    protected function getList($id_mp_stock_import = 0)
    {
        $submit = 'submitFilter';
        $current_page_field = $submit.$this->table_name;
        $date_start = '';
        $date_end = '';
        if (Tools::isSubmit($submit)) {
            $current_page = (int)Tools::getValue($current_page_field, 1);
            $pagination = (int)Tools::getValue($this->table_name.'_pagination', 20);
            $this->page = $current_page;
            $this->_default_pagination = $pagination;
            $filterDate = $this->table_name.'Filter'.'_date_movement';
            $dates = Tools::getValue($filterDate, array());
            if (isset($dates[0])) {
                $date_start = $dates[0];
            }
            if (isset($dates[1])) {
                $date_end = $dates[1];
            }
        } else {
            $date_start = '';
            $date_end = '';
        }
        
        $db = Db::getInstance();
        
        $sql = new DbQueryCore();
        $sql->select('s.id_mp_stock')
            ->select("'mp_stock' as `tablename`")
            ->select('s.id_product')
            ->select('s.id_product_attribute')
            ->select('pa.reference')
            ->select('s.price')
            ->select('s.tax_rate')
            ->select('s.qty')
            ->select('s.date_movement')
            ->select('s.name')
            ->select('s.wholesale_price')
            ->select('s.price')
            ->select('CONCAT(e.firstname, \' \', e.lastname) as employee')
            ->select('si.id_type_document')
            ->select('si.filename')
            ->from('mp_stock', 's')
            ->innerJoin('mp_stock_import', 'si', 'si.id_mp_stock_import=s.id_mp_stock_import')
            ->innerJoin('product_attribute', 'pa', 'pa.id_product_attribute=s.id_product_attribute')
            ->leftJoin('employee', 'e', 's.id_employee=e.id_employee')
            ->orderBy('s.date_movement DESC')
            ->orderBy('s.id_mp_stock_import DESC');
        
        $sql_count = new DbQueryCore();
        $sql_count->select('count(*)')
            ->from('mp_stock', 's');
        
        if ($date_start) {
            $date_start .= ' 00:00:00';
            $sql->where('s.date_movement >= \''.pSQL($date_start).'\'');
            $sql_count->where('date_movement >= \''.pSQL($date_start).'\'');
        }
        if ($date_end) {
            $date_end .= ' 23:59:59';
            $sql->where('s.date_movement <= \''.pSQL($date_end).'\'');
            $sql_count->where('date_movement <= \''.pSQL($date_end).'\'');
        }
        
        if ($id_mp_stock_import) {
            $sql->where('si.id_mp_stock_import='.(int)$id_mp_stock_import);
            $sql_count->innerJoin('mp_stock_import', 'si', 'si.id_mp_stock_import=s.id_mp_stock_import');
            $sql_count->where('si.id_mp_stock_import='.(int)$id_mp_stock_import);
        }
        
        $this->listTotal = (int)$db->getValue($sql_count);
        
        //Save query in cookies
        Context::getContext()->cookie->export_query = $sql->build();
        
        //Set Pagination
        $sql->limit($this->_default_pagination, ($this->page-1)*$this->_default_pagination);
        
        //print "<pre>".$sql->build()."</pre>";
        
        $result = $db->executeS($sql);
        
        if ($result) {
            foreach ($result as &$row) {
                $row['image'] = $this->getImageProduct($row['id_product'], true);
                $row['tax_rate'] = $this->displayTaxRate($row['tax_rate']);
                $row['qty'] = $this->displayQuantity($row['qty']);
            }
        }
        
        return $result;
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
    
    public function ucFirst($str)
    {
        $str_lower = Tools::strtolower($str);
        $parts = explode(' ', $str_lower);
        foreach ($parts as &$part) {
            $part = Tools::ucfirst($part);
        }
        return implode(' ', $parts);
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
    
    public function getImageProduct($id_product, $addImageTag = false)
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
        
        if ($addImageTag) {
            $image = array(
                'source' => $image_path,
                'width' => '48px',
            );
            $smarty = Context::getContext()->smarty;
            $smarty->assign('image', $image);
            return $smarty->fetch($this->module->getPath().'views/templates/admin/html_element_img.tpl');
        } else {
            return $image_path;
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