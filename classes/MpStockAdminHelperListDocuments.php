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

class MpStockAdminHelperListDocuments extends HelperListCore
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
        $this->id_lang = (int) $this->context->language->id;
        $this->id_shop = (int) $this->context->shop->id;
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
        $this->no_link = true;
        $this->page = Tools::getValue('submitFilterconfiguration', 1);
        $this->_default_pagination = Tools::getValue('configuration_pagination', 20);
        $this->show_toolbar = true;
        $this->toolbar_btn = array(
            'plus' => array(
                'desc' => $this->module->l('Add new movement', get_class($this)),
                'href' => $this->link->getAdminLink($this->className) . '&addMovement',
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
        $this->shopLinkType = '';
        $this->simple_header = false;
        $this->token = Tools::getAdminTokenLite($this->className);
        $this->title = $this->module->l('Documents found', get_class($this));
        $this->table = 'mp_stock_import';

        $list = $this->getList();
        $fields_display = $this->getFields();

        return $this->generateList($list, $fields_display) . $this->initScript();
    }

    private function initScript()
    {
        $smarty = Context::getContext()->smarty;
        return $smarty->fetch($this->module->getAdminTemplatePath() . 'helper_list_docs_script.tpl');
    }

    protected function getFields()
    {
        $list = array();
        MpStockTools::addText(
            $list,
            $this->module->l('Id', get_class($this)),
            'id_mp_stock_import',
            48,
            'text-right'
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
        MpStockTools::addHtml(
            $list,
            $this->module->l('Products', get_class($this)),
            'rows',
            '64',
            'text-center'
        );
        MpStockTools::addDate(
            $list,
            $this->module->l('Date movement', get_class($this)),
            'date_movement',
            128,
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
            $this->module->l('Actions', get_class($this)),
            'actions',
            '48',
            'text-center'
        );
        return $list;
    }

    protected function getList()
    {
        $submit = 'submitFilter';
        $current_page_field = $submit . $this->table_name;
        $date_start = '';
        $date_end = '';
        if (Tools::isSubmit($submit)) {
            $current_page = (int) Tools::getValue($current_page_field, 1);
            $pagination = (int) Tools::getValue($this->table_name . '_pagination', 20);
            $this->page = $current_page;
            $this->_default_pagination = $pagination;
            $filterDate = $this->table_name . 'Filter' . '_date_movement';
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
            ->where('tm.id_lang=' . (int) $this->id_lang)
            ->where('tm.id_shop=' . (int) $this->id_shop);
        if (Tools::isSubmit('mp_stock_importOrderby')) {
            $sql->orderBy(
                Tools::getValue('mp_stock_importOrderby', 'date_movement')
                . ' '
                . Tools::getValue('mp_stock_importOrderway', 'desc')
            );
        } else {
            $sql->orderBy('s.date_movement DESC')
                ->orderBy('id_mp_stock_import DESC');
        }

        $sql_count = new DbQueryCore();
        $sql_count->select('count(*)')
            ->from('mp_stock_import', 's');

        if ($date_start) {
            $date_start .= ' 00:00:00';
            $sql->where('s.date_movement >= \'' . pSQL($date_start) . '\'');
            $sql_count->where('date_movement >= \'' . pSQL($date_start) . '\'');
        }
        if ($date_end) {
            $date_end .= ' 23:59:59';
            $sql->where('s.date_movement <= \'' . pSQL($date_end) . '\'');
            $sql_count->where('date_movement <= \'' . pSQL($date_end) . '\'');
        }


        $this->listTotal = (int) $db->getValue($sql_count);

        //Save query in cookies
        Context::getContext()->cookie->export_query = $sql->build();

        //Set Pagination
        $sql->limit($this->_default_pagination, ($this->page - 1) * $this->_default_pagination);

        //print "<pre>".$sql->build()."</pre>";

        $result = $db->executeS($sql);
        if ($result) {
            foreach ($result as &$row) {
                $row['rows'] = MpStockTools::getHtmlBadgeElement(
                    $this->countProducts($row['id_mp_stock_import'])
                );
                $row['actions'] =
                    MpStockTools::getHtmlButtonCallBack(
                        '',
                        'icon-times',
                        'javascript:deleteDocument(this);',
                        '#BB7070'
                    ) .
                    MpStockTools::getHtmlLinkButton(
                        '',
                        'icon-file-text',
                        $this->context->link->getAdminLink('AdminMpStock')
                        . '&updatemp_stock_import'
                        . '&id_mp_stock_import=' . $row['id_mp_stock_import'],
                        '#4040BB',
                        ''
                    );
            }
        }
        return $result;
    }

    private function countProducts($id)
    {
        $db = Db::getInstance();
        $sql = "select count(*) from " . _DB_PREFIX_ . 'mp_stock where id_mp_stock_import=' . (int) $id;
        return (int) $db->getValue($sql);
    }
}