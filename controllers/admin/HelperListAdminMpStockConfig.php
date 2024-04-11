<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    Massimiliano Palermo <maxx.palermo@gmail.com>
 * @copyright Since 2016 Massimiliano Palermo
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

class HelperListAdminMpStockConfig extends HelperList
{
    public $template;
    protected $smarty;
    protected $cookie;

    public function __construct()
    {
        /**
         * Global Variables
         */
        $this->className = 'HelperListAdminMpStockConfig';
        $this->context = Context::getContext();
        $this->module = Module::getInstanceByName('mpstock');
        $this->id_lang = (int) $this->context->language->id;
        $this->id_shop = (int) $this->context->shop->id;
        $this->id_employee = (int) $this->context->employee->id;
        $this->link = $this->context->link;
        $this->smarty = $this->context->smarty;
        $this->template = $this->module->getAdminTemplatePath();
        $this->cookie = $this->context->cookie;
        /**
         * INIT TABLE
         */
        $this->shopLinkType = '';
        $this->table = ModelMpStockMvtReason::$definition['table'];
        $this->currentIndex = $this->context->link->getAdminLink('AdminMpStock', false);
        $this->token = Tools::getAdminTokenLite('AdminMpStock');
        $this->no_link = true;
        $this->actions = array('view', 'delete');
        $this->toolbar_btn = array(
            'plus' => array(
                'desc' => $this->module->l('Add'),
                'href' => 'javascript:displayAddMvtReason();',
            ),
            'download' => array(
                'desc' => $this->module->l('Import'),
                'href' => 'javascript:importConfig()',
            ),
        );
        $this->fields_list = array(
            'prog' => array(
                'title' => '-',
                'type' => 'bool',
                'float' => true,
                'width' => 32,
                'align' => 'text-right',
                'search' => false,
            ),
            'id_mpstock_mvt_reason' => array(
                'title' => $this->module->l('id', $this->className),
                'type' => 'text',
                'width' => 32,
                'align' => 'text-right',
                'search' => true,
            ),
            'name' => array(
                'title' => $this->module->l('Name', $this->className),
                'type' => 'text',
                'width' => 'auto',
                'align' => 'text-left',
                'search' => true,
            ),
            'sign' => array(
                'title' => $this->module->l('Sign', $this->className),
                'type' => 'bool',
                'float' => true,
                'width' => 48,
                'align' => 'text-center',
                'search' => false,
            ),
            'transform' => array(
                'title' => $this->module->l('Transform', $this->className),
                'type' => 'bool',
                'float' => true,
                'width' => 48,
                'align' => 'text-center',
                'search' => false,
            ),
            'date_add' => array(
                'title' => $this->module->l('Date add', $this->className),
                'type' => 'date',
                'width' => 'auto',
                'align' => 'text-left',
                'search' => true,
            ),
            'date_upd' => array(
                'title' => $this->module->l('Date upd', $this->className),
                'type' => 'date',
                'width' => 'auto',
                'align' => 'text-left',
                'search' => true,
            ),
            'deleted' => array(
                'title' => $this->module->l('Deleted', $this->className),
                'type' => 'bool',
                'float' => true,
                'width' => 48,
                'align' => 'text-center',
                'search' => false,
            ),
        );
        $this->identifier = ModelMpStockMvtReason::$definition['primary'];
        $this->orderBy = $this->identifier;
        $this->orderWay = 'ASC';
        /** END HELPERLIST **/

        $this->bootstrap = true;
        parent::__construct();
    }

    public function display()
    {
        $this->processSubmit();
        return $this->generateList($this->getList(), $this->fields_list);
    }

    private function processSubmit()
    {

    }

    private function getList()
    {
        $page = (int) Tools::getValue('submitFilter' . $this->table, 1);
        $pagination = (int) Tools::getValue($this->table . '_pagination', 20);
        if (empty($this->cookie->__get($this->table . '_order_field'))) {
            $order_field = $this->identifier;
        } else {
            $order_field = $this->cookie->__get($this->table . '_order_field');
        }
        if (empty($this->cookie->__get($this->table . '_order_field'))) {
            $order_way = "DESC";
        } else {
            $order_way = $this->cookie->__get($this->table . '_order_way');
        }

        if (!Tools::isSubmit('submitReset' . $this->table)) {
            $filter_id_mpstock_mvt_reason = (int) Tools::getValue($this->table . 'Filter_id_mpstock_mvt_reason', 0);
            $filter_name = Tools::getValue($this->table . 'Filter_name', '');
            $filter_date_add_start = Tools::getValue($this->table . 'Filter_date_add[0]', '');
            $filter_date_add_end = Tools::getValue($this->table . 'Filter_date_add[1]', '');
            $filter_date_upd_start = Tools::getValue($this->table . 'Filter_date_upd[0]', '');
            $filter_date_upd_end = Tools::getValue($this->table . 'Filter_date_upd[1]', '');
        } else {
            $filter_id_mpstock_mvt_reason = null;
            $filter_name = null;
            $filter_date_add_start = null;
            $filter_date_add_end = null;
            $filter_date_upd_start = null;
            $filter_date_upd_end = null;
        }
        if (Tools::isSubmit($this->table . 'Orderby')) {
            $page = 1;
            $pagination = 20;
            $filter_id_mpstock_mvt_reason = (int) $this->cookie->__get($this->table . 'Filter_id_mpstock_mvt_reason');
            $filter_name = $this->cookie->__get($this->table . 'Filter_name');
            $filter_date_add_start = $this->cookie->__get($this->table . 'Filter_date_add_start');
            $filter_date_add_end = $this->cookie->__get($this->table . 'Filter_date_add_end');
            $filter_date_upd_start = $this->cookie->__get($this->table . 'Filter_date_upd_start');
            $filter_date_upd_end = $this->cookie->__get($this->table . 'Filter_date_upd_end');
            $order_field = Tools::getValue($this->table . 'Orderby', 'id_stock_mvt_reason');
            $order_way = Tools::getValue($this->table . 'Orderway', 'desc');
        }

        $this->cookie->__set($this->table . '_page', $page);
        $this->cookie->__set($this->table . '_pagination', $pagination);
        $this->cookie->__set($this->table . 'Filter_id_mpstock_mvt_reason', $filter_id_mpstock_mvt_reason);
        $this->cookie->__set($this->table . 'Filter_name', $filter_name);
        $this->cookie->__set($this->table . 'Filter_date_add_start', $filter_date_add_start);
        $this->cookie->__set($this->table . 'Filter_date_add_end', $filter_date_add_end);
        $this->cookie->__set($this->table . 'Filter_date_upd_start', $filter_date_upd_start);
        $this->cookie->__set($this->table . 'Filter_date_upd_end', $filter_date_upd_end);
        $this->cookie->__set($this->table . '_order_field', $order_field);
        $this->cookie->__set($this->table . '_order_way', $order_way);

        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql_count = new DbQueryCore();

        $sql->select('c.*')
            ->select('cl.name')
            ->from('mpstock_mvt_reason', 'c')
            ->innerJoin('mpstock_mvt_reason_lang', 'cl', 'c.id_mpstock_mvt_reason=cl.id_mpstock_mvt_reason')
            ->where('cl.id_lang=' . (int) $this->id_lang);

        if ($filter_id_mpstock_mvt_reason) {
            $sql->where('c.id_mpstock_mvt_reason=' . (int) $filter_id_mpstock_mvt_reason);
        }
        if ($filter_name) {
            $sql->where('cl.name like \'%' . pSQL($filter_name) . '%\'');
        }
        if ($filter_date_add_start && $filter_date_add_end) {
            $sql->where(
                'c.date_add between \''
                . pSQL($filter_date_add_start) . ' 00:00:00\' and \''
                . pSQL($filter_date_add_end) . ' 23:59:59\''
            );
        } elseif ($filter_date_add_start && !$filter_date_add_end) {
            $sql->where(
                'c.date_add >= \''
                . pSQL($filter_date_add_start) . ' 00:00:00\''
            );
        } elseif (!$filter_date_add_start && $filter_date_add_end) {
            $sql->where(
                'c.date_add <= \''
                . pSQL($filter_date_add_end) . ' 23:59:59\''
            );
        }
        if ($filter_date_upd_start && $filter_date_upd_end) {
            $sql->where(
                'c.date_upd between \''
                . pSQL($filter_date_upd_start) . ' 00:00:00\' and \''
                . pSQL($filter_date_upd_end) . ' 23:59:59\''
            );
        } elseif ($filter_date_upd_start && !$filter_date_upd_end) {
            $sql->where(
                'c.date_upd >= \''
                . pSQL($filter_date_upd_start) . ' 00:00:00\''
            );
        } elseif (!$filter_date_upd_start && $filter_date_upd_end) {
            $sql->where(
                'c.date_upd <= \''
                . pSQL($filter_date_upd_end) . ' 23:59:59\''
            );
        }


        $count = count($db->ExecuteS($sql));
        $this->listTotal = $count;
        $this->fields_list[$order_field]['orderby'] = true;
        $this->fields_list[$order_field]['orderway'] = $order_way;
        $this->orderBy = $order_field;
        $this->orderWay = Tools::strtoupper($order_way);
        $sql->orderBy($order_field . ' ' . $order_way);

        $sql->limit($pagination, ($page - 1) * $pagination);
        $result = $db->executeS($sql);
        if ($result) {
            $i = 0;
            foreach ($result as &$row) {
                $i++;
                $row['prog'] = $this->getBadge($i);
                $row['sign'] = $this->getSign($row['sign']);
                $row['transform'] = $this->getTransform($row['transform']);
                $row['deleted'] = $this->getDeleted($row['deleted']);
            }
            return $result;
        } else {
            return array();
        }
    }

    public function getBadge($value)
    {
        $this->smarty->assign(
            array(
                'element' => array(
                    'type' => 'badge',
                    'value' => $value,
                )
            )
        );
        return $this->smarty->fetch($this->template . 'html_elements.tpl');
    }

    public function getSign($value)
    {
        if ((int) $value == 0) {
            $icon = 'icon-plus-circle';
            $color = '#72C279';
        } else {
            $icon = 'icon-minus-circle';
            $color = '#E08F95';
        }
        $this->smarty->assign(
            array(
                'element' => array(
                    'type' => 'icon',
                    'icon' => $icon,
                    'color' => $color,
                    'link' => 'toggleSign(this)'
                )
            )
        );
        return $this->smarty->fetch($this->template . 'html_elements.tpl');
    }

    public function getTransform($value)
    {
        if ($value) {
            $icon = 'icon-check';
            $color = '#72C279';
        } else {
            $icon = 'icon-times';
            $color = '#E08F95';
        }
        $this->smarty->assign(
            array(
                'element' => array(
                    'type' => 'icon',
                    'icon' => $icon,
                    'color' => $color,
                    'link' => 'toggleTransform(this)'
                )
            )
        );
        return $this->smarty->fetch($this->template . 'html_elements.tpl');
    }

    public function getDeleted($value)
    {
        if ($value) {
            $icon = 'icon-check';
            $color = '#72C279';
        } else {
            $icon = 'icon-times';
            $color = '#E08F95';
        }
        $this->smarty->assign(
            array(
                'element' => array(
                    'type' => 'icon',
                    'icon' => $icon,
                    'color' => $color,
                    'link' => 'toggleDeleted(this)'
                )
            )
        );
        return $this->smarty->fetch($this->template . 'html_elements.tpl');
    }
}