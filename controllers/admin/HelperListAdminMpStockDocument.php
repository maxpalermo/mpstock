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

class HelperListAdminMpStockDocument extends HelperList
{
    protected $smarty;

    public function __construct()
    {
        /**
         * Global Variables
         */
        $this->className = 'HelperListAdminMpStockDocument';
        $this->context = Context::getContext();
        $this->id_lang = (int) $this->context->language->id;
        $this->id_shop = (int) $this->context->shop->id;
        $this->id_employee = (int) $this->context->employee->id;
        $this->link = $this->context->link;
        $this->smarty = $this->context->smarty;
        $this->token = Tools::getAdminTokenLite('AdminMpStock');
        $this->module = $this->context->controller->module;
        $this->template = $this->module->getAdminTemplatePath();
        $this->cookie = $this->context->cookie;
        /**
         * INIT TABLE
         */
        $this->shopLinkType = '';
        $this->table = MpStockDocumentObjectModel::$definition['table'];
        $this->currentIndex = $this->context->link->getAdminLink('AdminMpStock', false);
        $this->token = $this->token;
        $this->no_link = true;
        $this->toolbar_btn = array(
            'plus' => array(
                'desc' => $this->module->l('add'),
                'href' => $this->currentIndex . '&add_document&token=' . $this->token,
            ),
            'download' => array(
                'desc' => $this->module->l('Import'),
                'href' => 'javascript:importDocument()',
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
            'id_mpstock_document' => array(
                'title' => $this->module->l('id', $this->className),
                'type' => 'text',
                'width' => 32,
                'align' => 'text-left',
                'search' => true,
                'hidden' => true,
            ),
            'id_mpstock_mvt_reason' => array(
                'title' => $this->module->l('Id Mov.', $this->className),
                'type' => 'text',
                'width' => 'auto',
                'align' => 'text-left',
                'search' => false,
            ),
            'movement' => array(
                'title' => $this->module->l('Movement', $this->className),
                'type' => 'text',
                'width' => 'auto',
                'align' => 'text-left',
                'search' => true,
            ),
            'number_document' => array(
                'title' => $this->module->l('Number', $this->className),
                'type' => 'text',
                'width' => 'auto',
                'align' => 'text-left',
                'search' => true,
            ),
            'date_document' => array(
                'title' => $this->module->l('Date', $this->className),
                'type' => 'date',
                'width' => 'auto',
                'align' => 'text-center',
                'search' => true,
            ),
            'supplier' => array(
                'title' => $this->module->l('Supplier', $this->className),
                'type' => 'text',
                'width' => 'auto',
                'align' => 'text-left',
                'search' => true,
            ),
            'tot_qty' => array(
                'title' => $this->module->l('Tot qty', $this->className),
                'type' => 'text',
                'width' => 'auto',
                'align' => 'text-right',
                'search' => false,
            ),
            'tot_document_te' => array(
                'title' => $this->module->l('Total t.e.', $this->className),
                'type' => 'price',
                'width' => 'auto',
                'align' => 'text-right',
                'search' => false,
            ),
            'tot_document_taxes' => array(
                'title' => $this->module->l('Total taxes', $this->className),
                'type' => 'price',
                'width' => 'auto',
                'align' => 'text-right',
                'search' => false,
            ),
            'tot_document_ti' => array(
                'title' => $this->module->l('Total t.i.', $this->className),
                'type' => 'price',
                'width' => 'auto',
                'align' => 'text-right',
                'search' => false,
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
                'align' => 'text-center',
                'search' => true,
            ),
            'tot_rows' => array(
                'title' => $this->l('Tot rows'),
                'type' => 'bool',
                'float' => true,
                'width' => 32,
                'align' => 'text-right',
                'search' => false,
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

    public function displayAddDocument()
    {
        $doc = new MpStockDocumentObjectModel();
        $this->smarty->assign('document', $doc);
        $this->smarty->assign('movement_reasons', $this->getMvtReasons());
        $this->smarty->assign('suppliers', Supplier::getSuppliers());
        $this->smarty->assign('back_url', $this->link->getAdminLink('AdminMpStock'));
        return $this->smarty->fetch($this->module->getAdminTemplatePath() . 'add_document.tpl');
    }

    public function displayImportForm()
    {
        return $this->generateFormImport();
    }

    private function processSubmit()
    {

    }

    public function getMvtReasons()
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('m.*')
            ->select('ml.name')
            ->from('mpstock_mvt_reason', 'm')
            ->innerJoin('mpstock_mvt_reason_lang', 'ml', 'ml.id_mpstock_mvt_reason=m.id_mpstock_mvt_reason')
            ->where('m.deleted=0')
            ->orderBy('ml.name');
        $result = $db->executeS($sql);
        return $result;
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
            $filter_id_mpstock_document = (int) Tools::getValue($this->table . 'Filter_id_mpstock_document', 0);
            $filter_movement = Tools::getValue($this->table . 'Filter_movement', '');
            $filter_number_document = Tools::getValue($this->table . 'Filter_number_document', '');
            $filter_date_document = Tools::getValue($this->table . 'Filter_date_document', array(0, 0));
            $filter_date_document_start = $filter_date_document[0];
            $filter_date_document_end = $filter_date_document[1];
            $filter_supplier = Tools::getValue($this->table . 'Filter_supplier', '');
            $filter_employee = Tools::getValue($this->table . 'Filter_employee', '');
            $filter_date = Tools::getValue($this->table . 'Filter_date_add', array(0, 0));
            $filter_date_add_start = $filter_date[0];
            $filter_date_add_end = $filter_date[1];
        } else {
            $filter_id_mpstock_document = null;
            $filter_movement = null;
            $filter_number_document = null;
            $filter_date_document_start = null;
            $filter_date_document_end = null;
            $filter_supplier = null;
            $filter_employee = null;
            $filter_date_add_start = null;
            $filter_date_add_end = null;
        }
        if (Tools::isSubmit($this->table . 'Orderby')) {
            $page = 1;
            $pagination = 20;
            $filter_id_mpstock_document = (int) $this->cookie->__get($this->table . 'Filter_id_mpstock_document');
            $filter_movement = $this->cookie->__get($this->table . 'Filter_movement');
            $filter_number_document = $this->cookie->__get($this->table . 'Filter_number_document');
            $filter_date_document_start = $this->cookie->__get($this->table . 'Filter_date_document_start');
            $filter_date_document_end = $this->cookie->__get($this->table . 'Filter_date_document_end');
            $filter_supplier = $this->cookie->__get($this->table . 'Filter_supplier');
            $filter_employee = $this->cookie->__get($this->table . 'Filter_employee');
            $filter_date_add_start = $this->cookie->__get($this->table . 'Filter_date_add_start');
            $filter_date_add_end = $this->cookie->__get($this->table . 'Filter_date_add_end');
            $order_field = Tools::getValue($this->table . 'Orderby', 'id_stock_mvt_reason');
            $order_way = Tools::getValue($this->table . 'Orderway', 'desc');
        }

        $this->cookie->__set($this->table . '_page', $page);
        $this->cookie->__set($this->table . '_pagination', $pagination);
        $this->cookie->__set($this->table . 'Filter_movement', $filter_movement);
        $this->cookie->__set($this->table . 'Filter_id_mpstock_document', $filter_id_mpstock_document);
        $this->cookie->__set($this->table . 'Filter_number_document', $filter_number_document);
        $this->cookie->__set($this->table . 'Filter_date_document_start', $filter_date_document_start);
        $this->cookie->__set($this->table . 'Filter_date_document_end', $filter_date_document_end);
        $this->cookie->__set($this->table . 'Filter_supplier', $filter_supplier);
        $this->cookie->__set($this->table . 'Filter_employee', $filter_employee);
        $this->cookie->__set($this->table . 'Filter_date_add_start', $filter_date_add_start);
        $this->cookie->__set($this->table . 'Filter_date_add_end', $filter_date_add_end);
        $this->cookie->__set($this->table . '_order_field', $order_field);
        $this->cookie->__set($this->table . '_order_way', $order_way);

        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql_count = new DbQueryCore();

        $sql->select('d.*')
            ->select('s.name as supplier')
            ->select('concat(e.firstname,\' \',e.lastname) as employee')
            ->select('ml.id_mpstock_mvt_reason')
            ->select('ml.name as movement')
            ->from('mpstock_document', 'd')
            ->leftJoin('supplier', 's', 's.id_supplier=d.id_supplier')
            ->innerJoin('employee', 'e', 'e.id_employee=d.id_employee')
            ->innerJoin('mpstock_mvt_reason_lang', 'ml', 'ml.id_mpstock_mvt_reason=d.id_mpstock_mvt_reason')
            ->where('id_shop=' . (int) $this->id_shop)
            ->where('ml.id_lang=' . (int) $this->id_lang)
            ->where('e.id_lang=' . (int) $this->id_lang);

        if ($filter_id_mpstock_document) {
            $sql->where('d.id_mpstock_document=' . (int) $filter_id_mpstock_document);
        }
        if ($filter_movement) {
            $sql->where('ml.name like \'' . pSQL($filter_movement) . '%\'');
        }
        if ($filter_number_document) {
            $sql->where('d.document_number=' . (int) $filter_number_document);
        }
        if ($filter_date_document_start && $filter_date_document_end) {
            $sql->where(
                'd.date_document between \''
                . pSQL($filter_date_document_start) . ' 00:00:00\' and \''
                . pSQL($filter_date_document_end) . ' 23:59:59\''
            );
        } elseif ($filter_date_document_start && !$filter_date_document_end) {
            $sql->where(
                'd.date_document >= \''
                . pSQL($filter_date_document_start) . ' 00:00:00\''
            );
        } elseif (!$filter_date_document_start && $filter_date_document_end) {
            $sql->where(
                'd.date_document <= \''
                . pSQL($filter_date_document_end) . ' 23:59:59\''
            );
        }
        if ($filter_supplier) {
            $sql->where('s.name like \'%' . pSQL($filter_supplier) . '%\'');
        }
        if ($filter_employee) {
            $sql->where('concat(e.firstname,\' \',e.lastname) like \'%' . pSQL($filter_employee) . '%\'');
        }
        if ($filter_date_add_start && $filter_date_add_end) {
            $sql->where(
                'd.date_add between \''
                . pSQL($filter_date_add_start) . ' 00:00:00\' and \''
                . pSQL($filter_date_add_end) . ' 23:59:59\''
            );
        } elseif ($filter_date_add_start && !$filter_date_add_end) {
            $sql->where(
                'd.date_add >= \''
                . pSQL($filter_date_add_start) . ' 00:00:00\''
            );
        } elseif (!$filter_date_add_start && $filter_date_add_end) {
            $sql->where(
                'd.date_add <= \''
                . pSQL($filter_date_add_end) . ' 23:59:59\''
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
                $row['tot_rows'] = $this->getBadge(
                    self::getTotRows($row['id_mpstock_document']),
                    'badge-white'
                );
            }
            return $result;
        } else {
            return array();
        }
    }


    public static function getTotRows($id_document)
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();

        $sql->select('count(*)')
            ->from('mpstock_movement')
            ->where('id_document=' . (int) $id_document);
        return (int) $db->getValue($sql);
    }

    public function getBadge($value, $class = '')
    {
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

    public function generateFormImport()
    {
        $form = new HelperForm();
        $form->table = 'mpstock_document';
        $form->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');
        $form->allow_employee_form_lang = (int) Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG');
        $form->submit_action = 'submitLoadXML';
        $form->currentIndex = $this->link->getAdminLink('AdminMpStock', false);
        $form->token = Tools::getAdminTokenLite('AdminMpStock');
        $form->tpl_vars = array(
            'fields_value' => array(
                'document_filename' => ''
            ),
            'languages' => $this->context->controller->getLanguages(),
        );
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Import document'),
                    'icon' => 'icon-download',
                ),
                'input' => array(
                    array(
                        'type' => 'file',
                        'label' => $this->l('Document'),
                        'name' => 'document_filename',
                        'display_image' => false,
                        'required' => true,
                        'desc' => $this->l('Upload your document')
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Load'),
                    'icon' => 'process-icon-upload',
                ),
            ),
        );
        return $form->generateForm(array($fields_form));
    }

    public function generateListImport($parsedfile)
    {
        $list = new HelperList();
        $list->shopLinkType = '';
        $list->table = 'mpstock_movement';
        $list->currentIndex = $this->context->link->getAdminLink('AdminMpStock', false);
        $list->token = $list->token;
        $list->no_link = true;
        $list->toolbar_btn = array(
            'ok' => array(
                'desc' => $this->l('Import'),
                'href' => 'javascript:importDocumentXML();'
            ),
        );
        $list->fields_list = array(
            'check' => array(
                'title' => $this->module->l('--', $this->className),
                'type' => 'bool',
                'float' => true,
                'width' => 48,
                'align' => 'text-center',
                'search' => false,
            ),
            'id_supplier' => array(
                'title' => $this->module->l('Id Supp.', $this->className),
                'type' => 'text',
                'width' => 96,
                'align' => 'text-right',
                'search' => false,
            ),
            'supplier' => array(
                'title' => $this->module->l('Supplier', $this->className),
                'type' => 'text',
                'width' => 'auto',
                'align' => 'text-left',
                'search' => false,
            ),
            'ean13' => array(
                'title' => $this->module->l('Ean13', $this->className),
                'type' => 'text',
                'width' => 'auto',
                'align' => 'text-left',
                'search' => false,
            ),
            'reference' => array(
                'title' => $this->module->l('Reference', $this->className),
                'type' => 'text',
                'width' => 'auto',
                'align' => 'text-left',
                'search' => false,
            ),
            'id_product' => array(
                'title' => $this->module->l('Id product', $this->className),
                'type' => 'text',
                'width' => 'auto',
                'align' => 'text-left',
                'search' => false,
            ),
            'id_product_attribute' => array(
                'title' => $this->module->l('Id attribute', $this->className),
                'type' => 'text',
                'width' => 'auto',
                'align' => 'text-left',
                'search' => false,
            ),
            'product' => array(
                'title' => $this->module->l('Product', $this->className),
                'type' => 'text',
                'width' => 'auto',
                'align' => 'text-left',
                'search' => false,
            ),
            'qty' => array(
                'title' => $this->module->l('Qty', $this->className),
                'type' => 'text',
                'width' => 96,
                'align' => 'text-right',
                'search' => false,
            ),
            'stock' => array(
                'title' => $this->module->l('Stock', $this->className),
                'type' => 'bool',
                'float' => true,
                'width' => 96,
                'align' => 'text-right',
                'search' => false,
            ),
            'exists' => array(
                'title' => $this->module->l('Exists', $this->className),
                'type' => 'bool',
                'float' => 'true',
                'width' => 48,
                'align' => 'text-center',
                'search' => false,
            ),
        );
        $list->identifier = 'id_mpstock_movement';
        $list->orderBy = $list->identifier;
        $list->orderWay = 'ASC';
        $list->title = $this->module->l('Products', $this->className);

        if (Tools::isSubmit('upload_file')) {
            $document = $this->LoadDocument($parsedfile);
        }

        if (!empty($document)) {
            $document['document']['number'] = $parsedfile['number'];
            $document['document']['date'] = $parsedfile['date'];
            $list->listTotal = count($document['rows']);
            $list->pagination = array(10, 20, 50, 100, 500, 1000);
            $list->_default_pagination = 1000;
            $list->page = 1;
            $html = $list->generateList($document['rows'], $list->fields_list);
        } else {
            $html = $list->generateList(array(), $list->fields_list);
        }
        $output = array(
            'document' => $document['document'],
            'rows' => $document['rows'],
            'html' => $html,
        );
        if (isset($document['errors'])) {
            $output['errors'] = $document['errors'];
        }
        return $output;
    }

    public static function existsTypeMovement($type)
    {
        $db = Db::getInstance();
        $sql = "select count(*) "
            . " from " . _DB_PREFIX_ . "mpstock_mvt_reason "
            . " where id_mpstock_mvt_reason=" . (int) $type;
        return (boolean) $db->getValue($sql);
    }

    public static function getMovement($type)
    {
        $id_lang = (int) Context::getContext()->language->id;
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('name')
            ->from('mpstock_mvt_reason_lang')
            ->where('id_lang=' . (int) $id_lang)
            ->where('id_mpstock_mvt_reason=' . (int) $type);
        return $db->getValue($sql);
    }

    public static function getSign($type)
    {
        $db = Db::getInstance();
        $sql = "select sign "
            . " from " . _DB_PREFIX_ . "mpstock_mvt_reason "
            . " where id_mpstock_mvt_reason=" . (int) $type;
        $sign = (int) $db->getValue($sql);
        if ($sign == 0) {
            $sign = 1;
        } else {
            $sign = -1;
        }
        return $sign;
    }

    public function LoadDocument($parsedFile)
    {
        $file = Tools::fileAttachment('file');
        if ($file['mime'] == 'text/xml' || $file['mime'] == 'text/csv') {
            return $this->parseFile($parsedFile, $file['content']);
        } else {
            return array(
                'document' => array(),
                'rows' => array(),
                'errors' => $this->l('Please select a valid xml file.')
            );
        }
    }

    public function parseFile($file, $content)
    {
        $xml = simplexml_load_string($content);

        /** Get date movement **/
        $date_movement = (string) $xml->movement_date;
        /** Get type movement **/
        $type_movement = (int) ((string) $xml->movement_type);
        /** Check if type moviment is correct **/
        $exist_movement = self::existsTypeMovement($type_movement);
        if (!$exist_movement) {
            return array(
                'document' => array(),
                'rows' => array(),
                'errors' => sprintf(
                    $this->l('Type movement id %d do not exists.'),
                    $type_movement
                ),
            );
        }
        /** Get sign **/
        $sign = self::getSign($type_movement);
        $parsedDocument = array(
            'number' => $file['number'],
            'date' => $file['date'],
            'type' => $type_movement,
            'movement' => self::getMovement($type_movement),
            'sign' => $sign,
        );
        /** Get XML rows **/
        $rows = $xml->rows;
        $parsedRows = $this->parseRows($rows, $sign);
        if ($parsedRows) {
            $parsedDocument['id_supplier'] = $parsedRows[0]['id_supplier'];
            $parsedDocument['supplier'] = $parsedRows[0]['supplier'];
        }
        return array(
            'document' => $parsedDocument,
            'rows' => $parsedRows,
        );
    }

    /** Prepare Array of products row for parsing */
    private function parseRows($rows, $sign)
    {
        /** Prepare array insertion **/
        $output = array();
        /** Parse rows **/
        foreach ($rows->children() as $row) {
            $ean13 = trim((string) $row->ean13);
            $reference = trim((string) $row->reference);
            $qty = (int) (((string) $row->qty) * (int) $sign);
            $price = (float) ((string) $row->price);
            $wholesale_price = (float) ((string) $row->wholesale_price);
            $product = MpStockProductObjectModel::getProduct($ean13, $reference);
            $supplier = MpStockProductObjectModel::getSupplier($product['id_product']);
            $product_fields = array(
                'check' => $this->createCheckButton($product['id_product']),
                'id_supplier' => $supplier['id_supplier'],
                'supplier' => $supplier['name'],
                'ean13' => $ean13,
                'reference' => $reference,
                'qty' => $qty,
                'stock' => self::formatQty(
                    MpStockProductObjectModel::getStockQuantity(
                        $product['id_product_attribute'],
                        $product['id_product']
                    )
                ),
                'price' => (float) $price,
                'wholesale_price' => (float) $wholesale_price,
                'exists' => $this->getStatus($product['id_product']),
                'id_product' => $product['id_product'],
                'id_product_attribute' => $product['id_product_attribute'],
                'product' => $product['name'],
            );
            $output[] = $product_fields;
        }
        return $output;
    }

    public static function formatQty($qty)
    {
        $smarty = Context::getContext()->smarty;
        $module = Context::getContext()->controller->module;
        if ($qty < 0) {
            $color = '#E08F95';
        } elseif ($qty == 0) {
            $color = '#555555';
        } else {
            $color = '#72C279';
        }
        $smarty->assign(
            array(
                'element' => array(
                    'type' => 'span',
                    'value' => (int) $qty,
                    'css' => array(
                        'color' => $color,
                        'font-weight' => 'bold',
                    ),
                ),
            )
        );
        return $smarty->fetch($module->getAdminTemplatePath() . 'html_elements.tpl');
    }

    private function createCheckButton($id_product)
    {
        $this->smarty->assign(
            array(
                'element' => array(
                    'type' => 'checkButton',
                    'value' => (int) $id_product,
                    'name' => 'chkProductImport'
                ),
            )
        );
        return $this->smarty->fetch($this->module->getAdminTemplatePath() . 'html_elements.tpl');
    }

    public function getStatus($status)
    {
        $this->smarty->assign(
            array(
                'element' => array(
                    'type' => 'status',
                    'value' => (int) $status,
                ),
            )
        );
        return $this->smarty->fetch($this->module->getAdminTemplatePath() . 'html_elements.tpl');
    }
}