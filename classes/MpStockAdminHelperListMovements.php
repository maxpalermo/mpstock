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

class MpStockAdminHelperListMovements extends HelperListCore
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
    protected $table_name = 'mp_stock';

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
        $this->smarty = Context::getContext()->smarty;
    }

    public function display($id_mp_stock_import = 0)
    {
        $this->bootstrap = true;
        $this->actions = array();
        $this->currentIndex = $this->context->link->getAdminLink('AdminMpStock', false)
            .'&id_mp_stock_import='.$id_mp_stock_import
            .'&updatemp_stock_import';
        $this->identifier = 'id_product';
        $this->no_link = true;
        $this->page = Tools::getValue('submitFilterconfiguration', 1);
        $this->_default_pagination = Tools::getValue('configuration_pagination', 20);
        $this->show_toolbar = true;
        $this->toolbar_btn = array(
            'back' => array(
                'desc' => $this->module->l('Back to documents', get_class($this)),
                'href' => $this->link->getAdminLink($this->className),
            ),
        );
        $this->shopLinkType='';
        $this->simple_header = false;
        $this->token = Tools::getAdminTokenLite('AdminMpStock');
        $this->title = $this->module->l('Movements found', get_class($this));
        $this->table = 'mp_stock';

        $list = $this->getList($id_mp_stock_import);
        $fields_display = $this->getFields();

        return $this->generateList($list, $fields_display).$this->getScript();
    }
    
    private function getScript()
    {
        $this->smarty->assign(
            array(
                'url_edit_movement' => $this->link->getAdminLink('AdminMpStock').'&action=edit_movement',
            )
        );
        return $this->smarty->fetch($this->module->getPath().'views/templates/admin/helper_list_movs_script.tpl');
    }
    
    private function getFields()
    {
        $list = array();
        MpStockTools::addText(
            $list,
            $this->module->l('Id', get_class($this)),
            'id_mp_stock',
            '48',
            'text-right'
        );
        MpStockTools::addHtml(
            $list,
            $this->module->l('Image', get_class($this)),
            'image',
            '48',
            'text-center'
        );
        MpStockTools::addText(
            $list,
            $this->module->l('Type movement', get_class($this)),
            'movement',
            'auto',
            'text-left'
        );
        MpStockTools::addText(
            $list,
            $this->module->l('Filename', get_class($this)),
            'filename',
            'auto',
            'text-left'
        );
        MpStockTools::addText(
            $list,
            $this->module->l('Reference', get_class($this)),
            'reference',
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
            $this->module->l('Stock', get_class($this)),
            'stock',
            '48',
            'text-right'
        );
        MpStockTools::addPrice(
            $list,
            $this->module->l('Wholesale Price', get_class($this)),
            'wholesale_price',
            'auto',
            'text-right'
        );
        MpStockTools::addPrice(
            $list,
            $this->module->l('Price', get_class($this)),
            'price',
            'auto',
            'text-right'
        );
        MpStockTools::addHtml(
            $list,
            $this->module->l('Tax rate', get_class($this)),
            'tax_rate',
            '128',
            'text-right'
        );
        MpStockTools::addHtml(
            $list,
            $this->module->l('Snap', get_class($this)),
            'snap',
            '48',
            'text-right'
        );
        MpStockTools::addHtml(
            $list,
            $this->module->l('Qty', get_class($this)),
            'qty',
            '48',
            'text-right'
        );
        MpStockTools::addDate(
            $list,
            $this->module->l('Date movement', get_class($this)),
            'date_movement',
            'auto',
            'text-center',
            true
        );
        MpStockTools::addText(
            $list,
            $this->module->l('Employee', get_class($this)),
            'employee',
            'auto',
            'text-left'
        );
        MpStockTools::addHtml(
            $list,
            $this->module->l('Action', get_class($this)),
            'action',
            'auto',
            'text-center'
        );

        return $list;
    }

    private function getList($id_mp_stock_import = 0)
    {
        $submit = 'submitFilter';
        $current_page_field = $submit.$this->table_name;
        $date_start = '';
        $date_end = '';
        if (Tools::isSubmit($current_page_field)) {
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
        $sql->select('distinct s.id_mp_stock')
            ->select('s.id_product')
            ->select('s.id_product_attribute')
            ->select('s.id_mp_stock_type_movement')
            ->select('pa.reference')
            ->select('s.tax_rate')
            ->select('s.snap')
            ->select('s.qty')
            ->select('s.date_movement')
            ->select('CONCAT(pl.name, \' - \', UPPER(s.name)) as `name`')
            ->select('s.wholesale_price')
            ->select('s.price')
            ->select('CONCAT(e.firstname, \' \', e.lastname) as employee')
            ->select('si.id_type_document')
            ->select('si.filename')
            ->select('tm.name as movement')
            ->from('mp_stock', 's')
            ->innerJoin('mp_stock_type_movement', 'tm', 'tm.id_mp_stock_type_movement=s.id_mp_stock_type_movement')
            ->leftJoin('mp_stock_import', 'si', 'si.id_mp_stock_import=s.id_mp_stock_import')
            ->innerJoin('product_attribute', 'pa', 'pa.id_product_attribute=s.id_product_attribute')
            ->innerJoin('product_lang', 'pl', 'pl.id_product=s.id_product')
            ->leftJoin('employee', 'e', 's.id_employee=e.id_employee')
            ->where('pl.id_lang='.(int)$this->id_lang)
            ->where('pl.id_shop='.(int)$this->id_shop);
        if (Tools::isSubmit('mp_stockOrderby') && Tools::getValue('mp_stockOrderby')!='action') {
            $sql->orderBy(
                Tools::getValue('mp_stockOrderby', 'id_mp_stock')
                .' '
                .Tools::getValue('mp_stockOrderway', 'desc')
            );
        } else {
            $sql->orderBy('s.id_mp_stock DESC')
                ->orderBy('s.date_movement DESC');
        }
            

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
                $row['image'] = MpStockTools::getImageProduct((int)$row['id_product']);
                $row['tax_rate'] = MpStockTools::displayTaxRate($row['tax_rate']);
                $row['qty'] = MpStockTools::displayQuantity($row['qty']);
                $row['stock'] = MpStockTools::displayQuantity(
                    MpStockTools::getAvailableStock($row['id_product_attribute'])
                );
                $row['action'] =
                    MpStockTools::getHtmlButtonCallBack(
                        '',
                        'icon-times',
                        'javascript:deleteMovement(this);',
                        '#BB4040',
                        ''
                    ).
                    MpStockTools::getHtmlLinkButton(
                        '',
                        'icon-file-text',
                        $this->context->link->getAdminLink('AdminProducts')
                        .'&updateproduct'
                        .'&id_product='.$row['id_product'],
                        '#4040BB',
                        ''
                    );
            }
        }

        return $result;
    }
}
