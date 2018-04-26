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

Class MpStockAdminHelperListDocuments extends HelperListCore
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
    
    public function display()
    {
        $this->bootstrap = true;
        $this->currentIndex = $this->context->link->getAdminLink($this->className, false);
        $this->identifier = 'id_mp_stock_import';
        $this->no_link = false;
        $this->page = Tools::getValue('submitFilterconfiguration', 1);
        $this->_default_pagination = Tools::getValue('configuration_pagination', 20);
        $this->show_toolbar = true;
        $this->toolbar_btn = array(
            'plus' => array(
                'desc' => $this->module->l('Add new movement', get_class($this)),
                'href' => $this->link->getAdminLink($this->className).'&addMovement',
            ),
            'upload' => array(
                'desc' => $this->module->l('Import from XML', get_class($this)),
                'href' => 'javascript:importXML();',
            ),
            'download' => array(
                'desc' => $this->module->l('Export to XML', get_class($this)),
                'href' => 'javascript:exportXML();',
            ),
        );
        $this->shopLinkType='';
        $this->simple_header = false;
        $this->token = Tools::getAdminTokenLite($this->className);
        $this->title = $this->module->l('Documents found', get_class($this));
        $this->table = 'mp_stock_import';
        
        $list = $this->getList();
        $fields_display = $this->getFields();
        
        return $this->generateList($list, $fields_display);
    }
    
    protected function getFields()
    {
        $list = array();
        $this->addText(
            $list,
            $this->module->l('Id', get_class($this)),
            'id_mp_stock_import',
            48,
            'text-right'
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
        $this->addDate(
            $list,
            $this->module->l('Date movement', get_class($this)),
            'date_movement',
            'auto',
            'text-center',
            true
        );
        $this->addText(
            $list,
            $this->module->l('Employee', get_class($this)),
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
    
    protected function getList()
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
        $sql->select('s.id_mp_stock_import')
            ->select('tm.name as movement')
            ->select('s.filename')
            ->select('s.date_movement')
            ->select('CONCAT(e.firstname, \' \', e.lastname) as employee')
            ->from('mp_stock_import', 's')
            ->leftJoin('mp_stock_type_movement', 'tm', 's.id_type_document=tm.id_mp_stock_type_movement')
            ->leftJoin('employee', 'e', 's.id_employee=e.id_employee')
            ->where('tm.id_lang='.(int)$this->id_lang)
            ->where('tm.id_shop='.(int)$this->id_shop)
            ->orderBy('s.date_movement DESC')
            ->orderBy('s.filename DESC');
        
        $sql_count = new DbQueryCore();
        $sql_count->select('count(*)')
            ->from('mp_stock_import', 's');
        
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
        
        
        $this->listTotal = (int)$db->getValue($sql_count);
        
        //Save query in cookies
        Context::getContext()->cookie->export_query = $sql->build();
        
        //Set Pagination
        $sql->limit($this->_default_pagination, ($this->page-1)*$this->_default_pagination);
        
        //print "<pre>".$sql->build()."</pre>";
        
        $result = $db->executeS($sql);
        return $result;
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