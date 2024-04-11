<?php
/**
 * 2017 mpSOFT
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
 *  @copyright 2018 Digital Solutions®
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of mpSOFT
 */

require_once _PS_MODULE_DIR_ . 'mpstock/classes/MpStockDocumentObjectModel.php';
require_once _PS_MODULE_DIR_ . 'mpstock/classes/MpStockProductObjectModel.php';

class HelperListAdminMpStockProduct extends HelperList
{
    public function __construct()
    {
        /**
         * Global Variables
         */
        $this->className = 'HelperListAdminMpStockProduct';
        $this->context = Context::getContext();
        $this->id_lang = (int) $this->context->language->id;
        $this->id_shop = (int) $this->context->shop->id;
        $this->id_employee = (int) $this->context->employee->id;
        $this->link = $this->context->link;
        $this->smarty = $this->context->smarty;
        $this->token = Tools::getAdminTokenLite('AdminMpStock');
        $this->module = $this->context->controller->module;
        $this->template = $this->module->getLocalPath() . 'views/templates/admin/';
        $this->cookie = $this->context->cookie;
        /**
         * INIT TABLE
         */
        $this->shopLinkType = '';
        $this->table = MpStockProductObjectModel::$definition['table'];
        $this->currentIndex = $this->context->link->getAdminLink('AdminMpStock', false);
        $this->token = $this->token;
        $this->no_link = true;
        $this->toolbar_btn = array(
            'plus' => array(
                'desc' => $this->module->l('add'),
                'href' => $this->currentIndex . '&add_document&token=' . $this->token,
            ),
        );
        $list_type = array();
        $list_type[] = 'order';
        $list_type[] = 'movement';
        $list_type[] = 'return';

        $this->fields_list = array(
            'prog' => array(
                'title' => '-',
                'type' => 'bool',
                'float' => true,
                'width' => 32,
                'align' => 'text-right',
                'search' => false,
            ),
            'id' => array(
                'title' => $this->module->l('id', $this->className),
                'type' => 'text',
                'width' => 32,
                'align' => 'text-right',
                'search' => true,
            ),
            'number_document' => array(
                'title' => $this->module->l('Number', $this->className),
                'type' => 'text',
                'width' => 'auto',
                'align' => 'text-left',
                'search' => false,
            ),
            'date_document' => array(
                'title' => $this->module->l('Date', $this->className),
                'type' => 'date',
                'width' => 'auto',
                'align' => 'text-left',
                'search' => true,
            ),
            'product_name' => array(
                'title' => $this->module->l('Product', $this->className),
                'type' => 'text',
                'width' => 'auto',
                'align' => 'text-left',
                'search' => true,
            ),
            'snap' => array(
                'title' => $this->module->l('Snap', $this->className),
                'type' => 'bool',
                'float' => true,
                'width' => 48,
                'align' => 'text-right',
                'search' => false,
            ),
            'qty' => array(
                'title' => $this->module->l('Qty', $this->className),
                'type' => 'bool',
                'float' => true,
                'width' => 48,
                'align' => 'text-right',
                'search' => false,
            ),
            'stock_quantity' => array(
                'title' => $this->module->l('Stock', $this->className),
                'type' => 'bool',
                'float' => true,
                'width' => 48,
                'align' => 'text-right',
                'search' => false,
            ),
            'price' => array(
                'title' => $this->module->l('Price', $this->className),
                'type' => 'price',
                'width' => 96,
                'align' => 'text-right',
                'search' => false,
            ),
            'w_price' => array(
                'title' => $this->module->l('W. Price', $this->className),
                'type' => 'price',
                'width' => 96,
                'align' => 'text-right',
                'search' => false,
            ),
            'type_row' => array(
                'title' => $this->module->l('Type mov', $this->className),
                'type' => 'text',
                'width' => 'auto',
                'list' => $list_type,
            ),
            'employee' => array(
                'title' => $this->module->l('Employee', $this->className),
                'type' => 'text',
                'width' => 'auto',
                'align' => 'text-left',
                'search' => true,
            ),
            'date_add' => array(
                'title' => $this->module->l('Date add', $this->className),
                'type' => 'date',
                'width' => 'auto',
                'align' => 'text-left',
                'search' => true,
            ),
        );
        $this->identifier = MpStockDocumentObjectModel::$definition['primary'];
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
            $order_field = 'id';
        } else {
            $order_field = $this->cookie->__get($this->table . '_order_field');
        }
        if (empty($this->cookie->__get($this->table . '_order_field'))) {
            $order_way = "DESC";
        } else {
            $order_way = $this->cookie->__get($this->table . '_order_way');
        }

        if (!Tools::isSubmit('submitReset' . $this->table)) {
            $filter_id_mpstock_movement = (int) Tools::getValue($this->table . 'Filter_id_mpstock_movement', 0);
            $filter_number_document = Tools::getValue($this->table . 'Filter_number_document', '');
            $filter_local_date_document = Tools::getValue('local_' . $this->table . 'Filter_date_document', array(0, 0));
            if (!empty($filter_local_date_document[0]) || !empty($filter_local_date_document[1])) {
                $filter_date_document = Tools::getValue($this->table . 'Filter_date_document', array(0, 0));
                $filter_date_document_start = $filter_date_document[0];
                $filter_date_document_end = $filter_date_document[1];
            } else {
                $filter_date_document_start = '';
                $filter_date_document_end = '';
            }
            $filter_product_name = Tools::getValue($this->table . 'Filter_product_name', '');
            $filter_stock_quantity_before = Tools::getValue($this->table . 'Filter_stock', '');
            $filter_stock_movement = Tools::getValue($this->table . 'Filter_quantity', '');
            $filter_employee = Tools::getValue($this->table . 'Filter_employee', '');
            $filter_date_add = Tools::getValue($this->table . 'Filter_date_add', array(0, 0));
            $filter_date_add_start = $filter_date_add[0];
            $filter_date_add_end = $filter_date_add[1];
        } else {
            $filter_id_mpstock_movement = null;
            $filter_number_document = null;
            $filter_date_document_start = null;
            $filter_date_document_end = null;
            $filter_product_name = null;
            $filter_stock_quantity_before = null;
            $filter_stock_movement = null;
            $filter_employee = null;
            $filter_date_add_start = null;
            $filter_date_add_end = null;
        }
        if (Tools::isSubmit($this->table . 'Orderby')) {
            $page = 1;
            $pagination = 20;
            $filter_id_mpstock_movement = (int) $this->cookie->__get($this->table . 'Filter_id_mpstock_movement');
            $filter_number_document = $this->cookie->__get($this->table . 'Filter_number_document');
            $filter_date_document_start = $this->cookie->__get($this->table . 'Filter_date_document_start');
            $filter_date_document_end = $this->cookie->__get($this->table . 'Filter_date_document_end');
            $filter_product_name = $this->cookie->__get($this->table . 'Filter_product_name');
            $filter_stock_quantity_before = $this->cookie->__get($this->table . 'Filter_stock');
            $filter_stock_movement = $this->cookie->__get($this->table . 'Filter_quantity');
            $filter_employee = $this->cookie->__get($this->table . 'Filter_employee');
            $filter_date_add_start = $this->cookie->__get($this->table . 'Filter_date_add_start');
            $filter_date_add_end = $this->cookie->__get($this->table . 'Filter_date_add_end');
            $order_field = Tools::getValue($this->table . 'Orderby', 'date_document');
            $order_way = Tools::getValue($this->table . 'Orderway', 'desc');
        }
        if ($order_field = 'id_mpstock_document') {
            $order_field = 'date_document';
        }

        $this->cookie->__set($this->table . '_page', $page);
        $this->cookie->__set($this->table . '_pagination', $pagination);
        $this->cookie->__set($this->table . 'Filter_id_mpstock_movement', $filter_id_mpstock_movement);
        $this->cookie->__set($this->table . 'Filter_number_document', $filter_number_document);
        $this->cookie->__set($this->table . 'Filter_date_document_start', $filter_date_document_start);
        $this->cookie->__set($this->table . 'Filter_date_document_end', $filter_date_document_end);
        $this->cookie->__set($this->table . 'Filter_product_name', $filter_product_name);
        $this->cookie->__set($this->table . 'Filter_stock', $filter_stock_quantity_before);
        $this->cookie->__set($this->table . 'Filter_quantity', $filter_stock_movement);
        $this->cookie->__set($this->table . 'Filter_employee', $filter_employee);
        $this->cookie->__set($this->table . 'Filter_date_add_start', $filter_date_add_start);
        $this->cookie->__set($this->table . 'Filter_date_add_end', $filter_date_add_end);
        $this->cookie->__set($this->table . '_order_field', $order_field);
        $this->cookie->__set($this->table . '_order_way', $order_way);

        $db = Db::getInstance();
        $sql1 = new DbQueryCore();
        $sql2 = new DbQueryCore();
        $sql3 = new DbQueryCore();
        $sql_where = array();
        $sql_orderBy = ' ORDER BY ' . $order_field . ' ' . Tools::strtoupper($order_way);

        $sql1->select('distinct p.id_mpstock_movement as `id`')
            ->select('p.id_mpstock_document')
            ->select('p.id_mpstock_mvt_reason')
            ->select('p.id_product')
            ->select('p.id_product_attribute')
            ->select('p.reference')
            ->select('p.ean13')
            ->select('p.upc')
            ->select('p.stock as `snap`')
            ->select('p.quantity as `qty`')
            ->select('p.price_te as `price`')
            ->select('p.wholesale_price_te as `w_price`')
            ->select('p.id_employee')
            ->select('p.date_add')
            ->select('p.date_upd')
            ->select('pl.name')
            ->select('doc.number_document')
            ->select('doc.date_document')
            ->select("'movement' as `type_row`")
            ->from('mpstock_movement', 'p')
            ->innerJoin('product_lang', 'pl', 'pl.id_product=p.id_product')
            ->leftJoin('mpstock_document', 'doc', 'doc.id_mpstock_document=p.id_mpstock_document')
            ->where('pl.id_lang=' . (int) $this->id_lang);

        $sql2->select('distinct p.id_order_detail')
            ->select('p.id_order')
            ->select("'-1' as id_mpstock_mvt_reason")
            ->select('p.product_id')
            ->select('p.product_attribute_id')
            ->select('p.product_reference')
            ->select('p.product_ean13')
            ->select('p.product_upc')
            ->select('p.product_quantity_in_stock')
            ->select('-p.product_quantity')
            ->select('p.unit_price_tax_incl')
            ->select('p.original_wholesale_price')
            ->select("o.id_customer as id_employee")
            ->select("o.date_add")
            ->select("o.date_upd")
            ->select("concat(p.product_reference,' ',p.product_ean13) as name")
            ->select('o.reference')
            ->select('o.date_add')
            ->select("'order' as `type_row`")
            ->from('order_detail', 'p')
            ->innerJoin('orders', 'o', 'o.id_order=p.id_order')
            ->where('o.id_shop=' . (int) $this->id_shop);

        $sql3->select('distinct ord.id_order_detail')
            ->select('ord.id_order_return')
            ->select("'-2' as id_mpstock_mvt_reason")
            ->select('p.product_id')
            ->select('p.product_attribute_id')
            ->select('p.product_reference')
            ->select('p.product_ean13')
            ->select('p.product_upc')
            ->select('p.product_quantity_in_stock')
            ->select('ord.product_quantity')
            ->select('p.unit_price_tax_incl')
            ->select('p.original_wholesale_price')
            ->select("ort.id_customer as id_employee")
            ->select("ort.date_add")
            ->select("ort.date_upd")
            ->select("concat(p.product_reference,' ',p.product_ean13) as name")
            ->select("'-return-' as reference")
            ->select('ort.date_add')
            ->select("'return' as `type_row`")
            ->from('order_return_detail', 'ord')
            ->innerJoin('order_detail', 'p', 'p.id_order_detail=ord.id_order_detail')
            ->innerJoin('order_return', 'ort', 'ort.id_order_return=ord.id_order_return')
            ->where('ort.state=-999');

        if ($filter_id_mpstock_movement) {
            $sql_where[] = ('id=' . (int) $filter_id_mpstock_movement);
        }

        if ($filter_product_name) {
            $querystring = Tools::substr($filter_product_name, 2);
            switch (Tools::substr($filter_product_name, 0, 1)) {
                case 'r'://byReference
                    $sql_where[] = ('reference LIKE \'%' . pSQL($querystring) . '%\'');
                    break;
                case 'e'://byEAN13
                    $sql_where[] = ('ean13 LIKE \'%' . pSQL($querystring) . '%\'');
                    break;
                case 'u'://byUPC
                    $sql_where[] = ('upc LIKE \'%' . pSQL($querystring) . '%\'');
                    break;
                case 's'://byPhisycalQty
                    $sql_where[] = ('snap = ' . (int) $querystring);
                    break;
                case 'q'://byUsableQuantity
                    $sql_where[] = ('qty = ' . (int) $querystring);
                    break;
                case 'p':
                    $sql_where[] = ('name LIKE \'%' . pSQL($querystring) . '%\'');
                    break;
                default:
                    $sql_where[] = ('name LIKE \'%' . pSQL($filter_product_name) . '%\'');
                    break;
            }
        }

        if ($filter_employee) {
            $sql_where[] = ('concat(e.firstname,\' \',e.lastname) like \'%' . pSQL($filter_employee) . '%\'');
        }
        if ($filter_date_add_start && $filter_date_add_end) {
            $sql_where[] = (
                'date_add between \''
                . pSQL($filter_date_add_start) . ' 00:00:00\' and \''
                . pSQL($filter_date_add_end) . ' 23:59:59\''
            );
        } elseif ($filter_date_add_start && !$filter_date_add_end) {
            $sql_where[] = (
                'date_add >= \''
                . pSQL($filter_date_add_start) . ' 00:00:00\''
            );
        } elseif (!$filter_date_add_start && $filter_date_add_end) {
            $sql_where[] = (
                'date_add <= \''
                . pSQL($filter_date_add_end) . ' 23:59:59\''
            );
        }
        if ($filter_date_document_start && $filter_date_document_end) {
            $sql_where[] = (
                'date_document between \''
                . pSQL($filter_date_document_start) . ' 00:00:00\' and \''
                . pSQL($filter_date_document_end) . ' 23:59:59\''
            );
        } elseif ($filter_date_document_start && !$filter_date_document_end) {
            $sql_where[] = (
                'date_document >= \''
                . pSQL($filter_date_document_start) . ' 00:00:00\''
            );
        } elseif (!$filter_date_document_start && $filter_date_document_end) {
            $sql_where[] = (
                'date_document <= \''
                . pSQL($filter_date_document_end) . ' 23:59:59\''
            );
        }

        if (Tools::isSubmit('mpstock_productFilter_type_row') && !empty(Tools::getValue('mpstock_productFilter_type_row'))) {
            $sql_where[] = "type_row = '" . pSQL(Tools::getValue('mpstock_productFilter_type_row')) . "'";
        }

        $query_movements = $sql1->build();
        $query_orders = $sql2->build();
        $query_returns = $sql3->build();
        $query_where = $sql_where ? ' WHERE ' . implode(' AND ', $sql_where) : '';
        $query = "SELECT * FROM ("
            . $query_movements . ' UNION ' . $query_orders . " UNION " . $query_returns
            . ") AS `T` " . $query_where;

        $result = $db->executeS($query);
        $this->listTotal = count($result);
        $this->orderBy = $order_field;
        $this->orderWay = Tools::strtoupper($order_way);
        $offset = ($page - 1) * $pagination;
        if ($offset < 0) {
            $offset = 0;
        }
        $query .= $sql_orderBy . ' LIMIT ' . $offset . ',' . $pagination;

        $result = $db->executeS($query);
        if ($result) {
            $i = 0;
            foreach ($result as &$row) {
                $document = $this->getDocument((int) $row['id_mpstock_document'], $row['type_row'], $row['date_add']);
                $i++;
                $row['number_document'] = $document['number_document'];
                $row['date_document'] = $document['date_document'];
                $row['employee'] = $this->getEmployee($row['id_employee'], $row['type_row']);
                $row['stock_quantity'] =
                    MpStockProductObjectModel::getStockQuantity(
                        $row['id_product_attribute'],
                        $row['id_product']
                    );
                $row['product_name'] =
                    MpStockProductObjectModel::getNameProductWithCombinations(
                        $row['id_product_attribute'],
                        $row['id_product']
                    )
                    . ' ' . $row['ean13'];
                $row['prog'] = $this->getBadge($i, $row['type_row']);
                $row['snap'] = $this->getBadgeQty($row['snap']);
                $row['qty'] = $this->getBadgeQty($row['qty']);
                $row['stock_quantity'] = $this->getBadgeQty($row['stock_quantity']);
            }
            return $result;
        } else {
            return array();
        }
    }

    public function getDocument($id_document, $type, $date_add)
    {
        $db = Db::getInstance();
        if ($type == 'movement') {
            $sql = "select number_document, date_document from "
                . _DB_PREFIX_ . "mpstock_document where id_mpstock_document=" . (int) $id_document;
        } elseif ($type == 'order') {
            $sql = "select reference as number_document, date_add as date_document from "
                . _DB_PREFIX_ . "orders where id_order=" . (int) $id_document;
        } elseif ($type == 'return') {
            $sql = "select id_order_return as number_document, date_add as date_document from "
                . _DB_PREFIX_ . "order_return where id_order_return=" . (int) $id_document;
        }

        $result = $db->getRow($sql);
        if ($result) {
            return $result;
        } else {
            return array(
                'number_document' => $this->module->l('Quick Movement', $this->className),
                'date_document' => $date_add,
            );
        }
    }

    public function getEmployee($id_employee, $type)
    {
        $db = Db::getInstance();
        if ($type == 'movement') {
            $sql = "select '**' as `prefix`, firstname, lastname from "
                . _DB_PREFIX_ . "employee where id_employee=" . (int) $id_employee;
        } elseif ($type == 'order') {
            $sql = "select '' as `prefix`, firstname, lastname from "
                . _DB_PREFIX_ . "customer where id_customer=" . (int) $id_employee;
        } elseif ($type == 'return') {
            $sql = "select '' as `prefix`, firstname, lastname from "
                . _DB_PREFIX_ . "customer where id_customer=" . (int) $id_employee;
        }

        $result = $db->getRow($sql);
        if ($result) {
            return $result['prefix'] . $result['firstname'] . ' ' . $result['lastname'];
        } else {
            return '--';
        }
    }

    public function getBadge($value, $type)
    {
        if ($type == 'movement') {
            $class = "badge-info";
        } elseif ($type == 'order') {
            $class = "badge-success";
        } elseif ($type == 'return') {
            $class = "badge-warning";
        }
        $this->smarty->assign(
            array(
                'element' => array(
                    'type' => 'badge',
                    'value' => $value,
                    'class' => $class,
                )
            )
        );
        return $this->smarty->fetch($this->template . 'html_elements.tpl');
    }

    public function getBadgeQty($qty)
    {
        if ($qty == 0) {
            $class = 'badge-white';
        } elseif ($qty < 0) {
            $class = 'badge-danger';
        } else {
            $class = 'badge-success';
        }
        $this->smarty->assign(
            array(
                'element' => array(
                    'type' => 'badge',
                    'value' => $qty,
                    'class' => $class,
                )
            )
        );
        return $this->smarty->fetch($this->template . 'html_elements.tpl');
    }
}
