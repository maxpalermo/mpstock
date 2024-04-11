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

ini_set('max_execution_time', 120); //120 seconds = 2 minutes
ini_set('post_max_size', '1024M');
ini_set('upload_max_filesize', '1024M');

require_once _PS_MODULE_DIR_ . 'mpstock/classes/MpStockAdminImport.php';
require_once _PS_MODULE_DIR_ . 'mpstock/classes/MpStockAdminImportXML.php';
require_once _PS_MODULE_DIR_ . 'mpstock/classes/MpStockAdminImportCSV.php';
require_once _PS_MODULE_DIR_ . 'mpstock/classes/MpStockAdminHelperForm.php';
require_once _PS_MODULE_DIR_ . 'mpstock/classes/MpStockAdminHelperListDocuments.php';
require_once _PS_MODULE_DIR_ . 'mpstock/classes/MpStockAdminHelperListMovements.php';
require_once _PS_MODULE_DIR_ . 'mpstock/classes/MpStockAdminHelperFormAddMovement.php';
require_once _PS_MODULE_DIR_ . 'mpstock/classes/MpStockAdminHelperListAddMovement.php';
require_once _PS_MODULE_DIR_ . 'mpstock/classes/MpStockAdminHelperListAddMovementExchange.php';
require_once _PS_MODULE_DIR_ . 'mpstock/classes/MpStockAdminHelperFormAddQuickMovement.php';
require_once _PS_MODULE_DIR_ . 'mpstock/classes/MpStockObjectModelImport.php';
require_once _PS_MODULE_DIR_ . 'mpstock/classes/MpStockObjectModel.php';
require_once _PS_MODULE_DIR_ . 'mpstock/classes/MpStockTools.php';

require_once _PS_MODULE_DIR_ . 'mpstock/controllers/admin/HelperListAdminMpStockDocument.php';
require_once _PS_MODULE_DIR_ . 'mpstock/controllers/admin/HelperListAdminMpStockProduct.php';
require_once _PS_MODULE_DIR_ . 'mpstock/controllers/admin/HelperListAdminMpStockConfig.php';
require_once _PS_MODULE_DIR_ . 'mpstock/controllers/admin/HelperFormAdminMpStockConfig.php';

class AdminMpStockController extends ModuleAdminController
{
    const TYPE_MESSAGE_ERROR = 'error';
    const TYPE_MESSAGE_CONFIRMATION = 'confirmation';
    const TYPE_MESSAGE_WARNING = 'warning';

    public $link;
    protected $id_lang;
    protected $id_shop;
    protected $id_employee;
    protected $messages;
    protected $local_path;
    protected $parameters = array();
    protected $smarty;

    /** PAGINATION **/
    protected $current_page = 1;
    protected $page = 1;
    protected $selected_pagination = 10;

    public function __construct()
    {
        $this->bootstrap = true;
        $this->className = 'AdminMpStock';
        $this->context = Context::getContext();
        $this->token = Tools::getValue('token', Tools::getAdminTokenLite($this->className));
        parent::__construct();
        $this->id_lang = (int) ContextCore::getContext()->language->id;
        $this->id_shop = (int) ContextCore::getContext()->shop->id;
        $this->id_employee = (int) ContextCore::getContext()->employee->id;
        $this->context = Context::getContext();
        $this->link = $this->context->link;
        $this->smarty = $this->context->smarty;
        $this->cookie = $this->context->cookie;
    }

    public function addError($message)
    {
        $this->errors[] = Tools::displayError($message);
    }

    public function addWarning($message)
    {
        $this->warnings[] = $this->displayWarning($message);
    }

    public function addConfirmation($message)
    {
        $this->confirmations[] = $message;
    }

    public function getLastId()
    {
        $db = Db::getInstance();
        $value = $db->getValue('SELECT max(`id_stock_mvt_reason`) FROM ' . _DB_PREFIX_ . 'stock_mvt_reason');
        return $value + 1;
    }

    public function checkSubmit()
    {
        if (Tools::isSubmit('submitMpStockConfig')) {
            $name = Tools::getValue('name', '');
            $sign = (int) Tools::getValue('sign');
            $transform = (int) Tools::getValue('transform');
            $deleted = 0;
            $date_add = date('Y-m-d H:i:s');

            if (empty($name)) {
                $this->addWarning($this->l('Please select a valid name.'));
                return false;
            }

            $obj = new ModelMpStockMvtReason();
            $obj->force_id = true;
            $obj->id = $this->getLastId();
            $obj->name[$this->id_lang] = $name;
            $obj->sign = $sign;
            $obj->transform = $transform;
            $obj->deleted = $deleted;
            $obj->date_add = $date_add;
            $result = $obj->add();
            if ($result) {
                $this->addConfirmation($this->l('Movement saved.'));
                return true;
            } else {
                $this->addError($this->l('Error saving movement.'));
                return false;
            }
        }
        if (Tools::isSubmit('deletestock_mvt_reason')) {
            $id = (int) Tools::getValue('id_stock_mvt_reason');
            $obj = new ModelMpStockMvtReason($id);
            $result = $obj->delete();
            if ($result) {
                $this->addConfirmation($this->l('Movement Deleted.'));
            } else {
                $this->addError($this->l('Unable to remove movement. probably there are documents associated.'));
            }
        }
    }

    public function initContent()
    {
        $helperListAdminMpStockDocument = new HelperListAdminMpStockDocument();
        $helperListAdminMpStockProduct = new HelperListAdminMpStockProduct();
        $helperListAdminMpStockConfig = new HelperListAdminMpStockConfig();
        $helperFormAdminMpStockConfig = new HelperFormAdminMpStockConfig();
        $product_quantities = $this->getStockAvailable();
        $active_tab = (int) $this->cookie->__get('admin_mp_stock_active_tab');

        $this->checkSubmit();

        if (Tools::isSubmit('add_mvt_reason')) {
            $html_config = $helperFormAdminMpStockConfig->display();
        } else {
            $html_config = $helperListAdminMpStockConfig->display();
        }

        if (Tools::isSubmit('add_document')) {
            $html_document = $helperListAdminMpStockDocument->displayAddDocument();
        } else {
            $html_document = $helperListAdminMpStockDocument->display();
        }

        if (Tools::isSubmit('submitLoadXML')) {
            print "<pre>" . print_r(Tools::fileAttachment('filename'), 1) . "</pre>";
        }

        $quick_movement_html = $this->smarty->fetch(
            $this->module->getAdminTemplatePath() . 'quick_movement.tpl'
        );

        $this->smarty->assign(
            array(
                'tab_document' => $html_document,
                'tab_product' => $helperListAdminMpStockProduct->display(),
                'tab_config' => $html_config,
                'tab_import' => $helperListAdminMpStockDocument->displayImportForm(),
                'tab_quick_movement' => $quick_movement_html,
                'product_quantities' => $product_quantities,
                'active_tab' => $active_tab,
                'img_loading' => $this->module->getURL() . 'views/img/loading.gif',
                'back_url' => $this->link->getAdminLink('AdminMpStock'),
                'stock_pagination' => (int) Tools::getValue('stock_pagination', 50),
                'stock_page' => (int) Tools::getValue('stock_page', 0),
                'stock_pages' => (int) $this->stock_pages,
                'stock_found' => (int) $this->stock_count,
            )
        );
        $this->content = $this->smarty->fetch($this->module->getAdminTemplatePath() . 'AdminMpStock.tpl');
        parent::initContent();
        return;

        $this->smarty = Context::getContext()->smarty;
        $this->link = new LinkCore();
        $list_content = '';
        $form_content = '';
        $textarea = '';
        /** Ajax call **/
        if (Tools::isSubmit('ajax') && !empty(Tools::getValue('action', ''))) {
            $action_name = Tools::ucfirst(Tools::getValue('action', ''));
            $action = 'ajaxProcess' . $action_name;
            print $this->$action();
            exit();
        }
        /** Import file **/
        if (Tools::isSubmit('submitFormImport')) {
            $importer = new MpStockAdminImport(Tools::fileAttachment('input_file_import'));

            if ($importer->status == false) {
                $this->addError($this->l('Error during import.'));
            } else {
                $this->addConfirmation($this->l('Import successfully done.'));
            }

            $smarty = Context::getContext()->smarty;
            $smarty->assign('content_area', $importer->getNotifications());
            $smarty->assign('content_total', $importer->getTotNotifications());
            $textarea = $smarty->fetch($this->module->getAdminTemplatePath() . 'html_element_textarea_import.tpl');
        }
        /** Submit Quick movement **/
        if (Tools::isSubmit('submitQuickMovement')) {
            $id_product = (int) Tools::getValue('input_text_id_product', 0);
            $id_product_attribute = (int) Tools::getValue('input_text_id_product_attribute', 0);
            $type_movement = (int) Tools::getValue('input_text_id_movement');

            $product = new ProductCore();

            $movement = new MpStockObjectModel($this->module);
            $movement->id_mp_stock_import = 0;
            $movement->id_mp_stock_exchange = 0;
            $movement->id_product = $id_product;
            $movement->id_product_attribute = $id_product_attribute;
            $movement->id_mp_stock_type_movement = $type_movement;
            $movement->qty = (int) Tools::getValue('input_text_quantity', 0);
            $movement->price = $product->price;
            $movement->wholesale_price = $product->wholesale_price;
            $movement->tax_rate = MpStockTools::getTaxRateFromIdProduct($id_product);
            $movement->date_movement = date('Y-m-d H:i:s');
            $movement->sign = MpStockTools::getSign($type_movement);
            $movement->date_add = date('Y-m-d H:i:s');
            $movement->id_employee = $this->id_employee;
            $movement->id_shop = $this->id_shop;
            $movement->id_lang = $this->id_lang;
            $movement->name = MpStockTools::getProductCombinationName($id_product_attribute);
            $movement->reference = $product->reference;
            $result = $movement->save();
            if ($result) {
                $this->addConfirmation($this->l('Quick movement saved.'));
            } else {
                $this->addError($this->l('Error saving Quick movement'));
            }
        }
        /** Get Stock movements **/
        if (Tools::isSubmit('id_mp_stock_import') && Tools::isSubmit('updatemp_stock_import')) {
            $list = new MpStockAdminHelperListMovements($this->module);
            $list_content = $list->display((int) Tools::getValue('id_mp_stock_import'));
        } else {
            /** Show default list **/
            $list = new MpStockAdminHelperListDocuments($this->module);
            $list_content = $list->display();
            $form = new MpStockAdminHelperForm($this->module);
            $form_content = $form->display();
        }
        /**Quick movement **/
        if (Tools::isSubmit('quick_movement')) {
            $form = new MpStockAdminHelperFormAddQuickMovement($this->module);
            $form_content = $form->display();
            $list_content = '';
        }
        /** Edit movement **/
        if (Tools::isSubmit('action') && Tools::getValue('action', '') == 'edit_movement') {
            $form = new MpStockAdminHelperFormMovement($this->module);
            $form_content = $form->display((int) Tools::getValue('id_movement'));
            $list_content = '';
        }
        /** Show documents **/
        if (Tools::isSubmit('show_documents')) {
            $list = new MpStockAdminHelperListDocuments($this->module);
            $list_content = $list->display();
            $form = new MpStockAdminHelperForm($this->module);
            $form_content = $form->display();
        }
        /** Show movements **/
        if (Tools::isSubmit('show_movements')) {
            $list = new MpStockAdminHelperListMovements($this->module);
            $list_content = $list->display();
            $form_content = '';
        }
        /** Add movement **/
        if (Tools::isSubmit('addMovement')) {
            $form = new MpStockAdminHelperFormAddMovement($this->module);
            $form_content = $form->display();
            $list_content = '';
        }
        $messages = $this->getMessages();
        $this->content = $messages . $form_content . $list_content . $textarea;

        parent::initContent();
    }

    private function getMessages()
    {
        $output = array();
        if ($this->errors) {
            array_merge($output, $this->errors);
        }
        if ($this->warnings) {
            array_merge($output, $this->warnings);
        }
        if ($this->confirmations) {
            array_merge($output, $this->confirmations);
        }
        return implode('<br>', $output);
    }

    private function processImportXML()
    {
        $importXML = new MpStockAdminImportXML($this->module);
        $importXML->import();
        $errors = $importXML->getImportErrors();
        if ($errors) {
            $smarty = Context::getContext()->smarty;
            $smarty->assign(
                array(
                    'import_errors' => $errors,
                )
            );
            $panel = $smarty->fetch($this->module->getPath() . 'views/templates/admin/import_errors.tpl');
            return $panel;
        }
        return true;
    }

    private function processImportCSV()
    {
        $importCSV = new MpStockAdminImportCSV($this->module);
        $importCSV->import();
        $errors = $importCSV->getImportErrors();
        if ($errors) {
            $smarty = Context::getContext()->smarty;
            $smarty->assign(
                array(
                    'import_errors' => $errors,
                )
            );
            $panel = $smarty->fetch($this->module->getPath() . 'views/templates/admin/import_errors.tpl');
            return $panel;
        }
        return true;
    }

    private function getLastFileName($folder)
    {
        $latest_ctime = 0;
        $latest_filename = '';
        $d = dir($folder);
        while (false !== ($entry = $d->read())) {
            $filepath = "{$folder}/{$entry}";
            //Check whether the entry is a file etc.:
            if (is_file($filepath) && filectime($filepath) > $latest_ctime) {
                $latest_ctime = filectime($filepath);
                $latest_filename = $entry;
            }//end if is file etc.
        }//end while going over files in excel_uploads dir.
        return $latest_filename;
    }

    public function ajaxProcessGetStockAvailable()
    {
        $smarty = Context::getContext()->smarty;
        $rows = $this->getStockAvailable();
        $smarty->assign(
            array(
                'product_quantities' => $rows,
                'stock_pagination' => (int) Tools::getValue('stock_pagination', 50),
                'stock_page' => (int) Tools::getValue('stock_page', 0),
                'stock_pages' => $this->stock_pages,
            )
        );

        $html = $smarty->fetch($this->module->getAdminTemplatePath() . 'table-stock.tpl');
        print Tools::jsonEncode(
            array(
                'html' => $html,
                'cur_rec' => $this->current_record,
                'last_rec' => $this->last_record,
            )
        );
        exit();
    }

    public function getStockAvailable()
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('sa.id_product')
            ->select('sa.id_product_attribute')
            ->select('p.reference')
            ->select('pl.name as product_name')
            ->select("'--' as product_attribute")
            ->select('sa.quantity as product_quantity')
            ->from('stock_available', 'sa')
            ->innerJoin('product', 'p', 'p.id_product=sa.id_product')
            ->innerJoin('product_lang', 'pl', 'pl.id_product=sa.id_product')
            ->where('sa.id_product_attribute=0')
            ->where('pl.id_lang=' . (int) $this->id_lang)
            ->where('pl.id_shop=' . (int) $this->id_shop)
            ->orderBy('pl.name');

        $result = $db->executeS($sql);
        if (!$result) {
            return array();
        }
        $tot = count($result);
        $this->stock_pagination = (int) Tools::getValue('stock_pagination', 50);
        $this->stock_page = (int) Tools::getValue('stock_page', 0);
        $this->stock_pages = (int) ceil(count($result) / $this->stock_pagination);
        $this->stock_count = count($result);
        $this->current_record = ((int) $this->stock_pagination * (int) $this->stock_page) + 1;
        $this->last_record = (int) $this->current_record + (int) $this->stock_pagination - 1;
        $output = array_slice($result, $this->stock_pagination * $this->stock_page, $this->stock_pagination);
        return $output;
    }

    public function ajaxProcessGetProductAttribute()
    {
        $id_product = (int) Tools::getValue('id_product');
        $smarty = Context::getContext()->smarty;
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('id_product')
            ->select('id_product_attribute')
            ->select('quantity')
            ->select('ean13')
            ->from('product_attribute')
            ->where('id_product=' . (int) $id_product);
        $rows = $db->executeS($sql);
        if ($rows) {
            foreach ($rows as &$row) {
                $row['name'] = $this->getProductAttributeName($row['id_product_attribute']);
            }
        } else {
            $rows = array();
        }
        $smarty->assign(
            array(
                'rows' => $rows,
            )
        );
        $html = $smarty->fetch($this->module->getAdminTemplatePath() . 'product-attribute.tpl');
        print Tools::jsonEncode(
            array(
                'html' => $html
            )
        );
        exit();
    }

    public function setDefaultProductAttribute($product, $id_product_attribute)
    {

        if (!Combination::isFeatureActive()) {
            return false;
        }

        if (Validate::isLoadedObject($product)) {
            $product->deleteDefaultAttributes();
            $product->setDefaultAttribute((int) $id_product_attribute);
        } else {
            return false;
        }
    }

    public function ajaxProcessSetDefaultCombination()
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('id_product')
            ->select('id_product_attribute')
            ->select('quantity')
            ->from('product_attribute')
            ->where('id_product_attribute != 0')
            ->groupBy('id_product')
            ->having('quantity = max(quantity)')
            ->orderBy('id_product');
        $rows = $db->executeS($sql);
        if ($rows) {
            foreach ($rows as $row) {
                $product = new Product($row['id_product']);
                $this->setDefaultProductAttribute($product, $row['id_product_attribute']);
            }
        }

        print Tools::jsonEncode(
            array(
                'result' => true,
            )
        );

        die();

        $db = Db::getInstance();
        $null = "update "
            . _DB_PREFIX_ . "product_attribute "
            . "set default_on=NULL";
        $db->execute($null);
        $null = "update "
            . _DB_PREFIX_ . "product_attribute_shop "
            . "set default_on=NULL "
            . "where id_shop=" . (int) $this->id_shop;
        $db->execute($null);

        $sql = new DbQuery();
        $sql->select('id_product')
            ->select('max(quantity) as max_qty')
            ->from('product_attribute')
            ->groupBy('id_product')
            ->orderBy('id_product');
        $result = $db->executeS($sql);

        if ($result) {
            foreach ($result as $row) {
                $update = "update ignore "
                    . _DB_PREFIX_ . "product_attribute "
                    . "set default_on=1 "
                    . "where "
                    . "id_product=" . (int) $row['id_product']
                    . " and "
                    . "quantity=" . (int) $row['max_qty'];
                $db->execute($update);
            }

            //UPDATE SHOP DEFAULT
            $sql = "select id_product_attribute "
                . "from " . _DB_PREFIX_ . "product_attribute "
                . "where default_on=1";
            $result = $db->executeS($sql);
            if ($result) {
                foreach ($result as $row) {
                    $update = "update ignore "
                        . _DB_PREFIX_ . "product_attribute_shop "
                        . "set default_on=1 "
                        . "where "
                        . "id_product_attribute=" . (int) $row['id_product_attribute'];
                    $db->execute($update);
                }
            }

            print Tools::jsonEncode(
                array(
                    'result' => true,
                )
            );
        } else {
            print Tools::jsonEncode(
                array(
                    'result' => false,
                    'query' => $sql->build(),
                    'error' => $db->getMsgError(),
                )
            );
        }
        exit();
    }

    public function setMedia()
    {
        if (Tools::getValue('controller') == $this->className) {
            parent::setMedia();
            $this->addCSS($this->module->getPath() . 'views/css/autocomplete.css');
            $this->addCSS($this->module->getPath() . 'views/css/jquery-confirm-min.css');
            $this->addJqueryUI('ui.dialog');
            $this->addJqueryUI('ui.progressbar');
            $this->addJqueryUI('ui.draggable');
            $this->addJqueryUI('ui.effect');
            $this->addJqueryUI('ui.effect-slide');
            $this->addJqueryUI('ui.effect-fold');
            $this->addJqueryUI('ui.autocomplete');
            $this->addJqueryUI('ui.datepicker');
            $this->addJqueryUI('ui.tabs');
            $this->addJqueryPlugin('growl');
            $this->addJS($this->module->getPath() . 'views/js/AdminMpStockAutocomplete.js');
            //$this->addJS($this->module->getPath().'views/js/AdminMpStockAddMovement.js');
            $this->addJS($this->module->getPath() . 'views/js/jquery-confirm-min.js');
        }
    }

    public function getExchangeForm($id = 0, $type_movement = 0)
    {
        $form = new MpStockAdminHelperListAddMovementExchange($this->module, $id, $type_movement);
        return $form->display();
    }

    public function getSmartyExchange($id, $type_movement)
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('*')
            ->from('mp_stock')
            ->where('id_mp_stock_exchange=' . (int) $id);
        $row = $db->getRow($sql);
        if ($row) {
            $product = new ProductCore($row["id_product"]);
            $assign = array(
                'id_mp_stock_exchange' => $id,
                'id_mp_stock_type_movement' => $type_movement,
                'product_name' => $product->name[$this->id_lang],
                'product_option' => array(
                    'value' => $row['id_product_attribute'],
                    'name' => $row['name'],
                ),
                'product_qty' => $row['qty'],
                'product_wholesale_price' => $row['wholesale_price'],
                'product_price' => $row['price'],
                'product_tax_rate' => $row['tax_rate']
            );
            return $assign;
        } else {
            return array(
                'id_mp_stock_exchange' => $id,
                'id_mp_stock_type_movement' => $type_movement,
            );
        }
    }

    public function getMovements()
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();

        $sql->select('id_mp_stock_type_movement')
            ->select('exchange')
            ->select('sign')
            ->select('name as value')
            ->from('mp_stock_type_movement')
            ->where('id_lang=' . (int) $this->id_lang)
            ->where('id_shop=' . (int) $this->id_shop)
            ->orderBy('name');
        $result = $db->executeS($sql);
        if (!$result) {
            return array();
        }
        foreach ($result as &$row) {
            $row['id'] = $row['id_mp_stock_type_movement'] . '_' . $row['exchange'] . '_' . $row['sign'];
        }
        return $result;
    }

    public function getTypemovement($id_movement)
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();

        $sql->select('name')
            ->from('mp_stock_type_movement')
            ->where('id_lang=' . (int) $this->id_lang)
            ->where('id_shop=' . (int) $this->id_shop)
            ->where('id_mp_stock_type_movement=' . (int) $id_movement);
        $result = $db->getValue($sql);
        return "" . $result;
    }

    public function getCategories($firstRow = false)
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('id_category')
            ->select('name')
            ->from('category_lang')
            ->where('id_shop = ' . (int) $this->id_shop)
            ->where('id_lang = ' . (int) $this->id_lang)
            ->orderBy('name');
        $result = $db->executeS($sql);
        if (!$result) {
            return array();
        }
        if ($firstRow) {
            array_unshift(
                $result,
                array(
                    'id_category' => 0,
                    'name' => $this->l('Select a category'),
                )
            );
        }
        return $result;
    }

    public function getManufacturers()
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('id_manufacturer')
            ->select('name')
            ->from('manufacturer')
            ->orderBy('name');
        $result = $db->executeS($sql);
        if (!$result) {
            return array();
        }
        return $result;
    }

    public function getSuppliers()
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('id_supplier')
            ->select('name')
            ->from('supplier')
            ->orderBy('name');
        $result = $db->executeS($sql);
        if (!$result) {
            return array();
        }
        return $result;
    }

    public function getProducts()
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('p.id_product')
            ->select('CONCAT(p.reference, " - ", pl.name) as name')
            ->from('product', 'p')
            ->innerJoin('product_lang', 'pl', 'p.id_product=pl.id_product')
            ->where('pl.id_shop=' . (int) $this->id_shop)
            ->where('pl.id_lang=' . (int) $this->id_lang)
            ->where('p.active=1')
            ->orderBy('p.reference');
        $result = $db->executeS($sql);
        if (!$result) {
            return array();
        }
        array_unshift(
            $result,
            array(
                'id_product' => 0,
                'name' => $this->l('Please select a product.'),
            )
        );
        return $result;
    }

    public function getFeatures()
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('id_feature')
            ->select('name')
            ->from('feature_lang')
            ->where('id_lang=' . (int) $this->id_lang)
            ->orderBy('name');
        $result = $db->executeS($sql);
        if (!$result) {
            return array();
        }
        array_unshift(
            $result,
            array(
                'id_feature' => 0,
                'name' => $this->l('Select a feature'),
            )
        );
        return $result;
    }

    public function getFeatureValues($id_feature)
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('fv.id_feature_value')
            ->select('fvl.value as name')
            ->from('feature_value', 'fv')
            ->innerJoin('feature_value_lang', 'fvl', 'fvl.id_feature_value=fv.id_feature_value')
            ->where('fvl.id_lang=' . (int) $this->id_lang)
            ->where('fv.id_feature=' . (int) $id_feature)
            ->orderBy('name');
        $result = $db->executeS($sql);
        if (!$result) {
            array_unshift(
                $result,
                array(
                    'id_feature_value' => 0,
                    'name' => $this->module->l('Select a feature value', get_class($this)),
                )
            );
            return array();
        }
        return $result;
    }

    public function getRows($pagination = 50, $page = 1)
    {
        $stock = new MpStockObjectModel($this->module);
        $rows = $stock->getRows($pagination, $page);
        return $rows;
    }

    public function getListProducts(HelperListCore &$helper, $order = 'DESC')
    {
        $this->id_lang = (int) Context::getContext()->language->id;
        $this->id_shop = (int) Context::getContext()->shop->id;

        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('*')
            ->from('mp_stock')
            //->where('date_add >= \'' . pSQL($date) . '\'')
            ->orderBy('id_mp_stock ' . $order);

        $result = $db->executeS($sql);
        $movements = array();
        if ($result) {
            $helper->listTotal = count($result);
            if (Tools::isSubmit('submitFiltermp_stock')) {
                $helper->_default_pagination = (int) Tools::getValue('mp_stock_pagination', 20);
                $offset = (int) Tools::getValue('submitFiltermp_stock', 0) * $helper->_default_pagination;
            } else {
                $offset = 0;
            }
            $result = array_splice($result, $offset, $helper->_default_pagination);

            foreach ($result as $row) {
                $id_product = (int) $row['id_product'];
                $id_product_attribute = (int) $row['id_product_attribute'];
                $id_employee = (int) $row['id_employee'];
                $id_stock = (int) $row['id_mp_stock'];
                if (!$id_product || !$id_product_attribute) {
                    $this->errors[] = sprintf($this->l('Unable to read product in stock line %d'), $id_stock);
                } else {
                    $output = $row;
                    $output['check'] = $this->chkBox('checkSelect[]', (int) $row['id_mp_stock']);
                    $output['reference'] = $this->getReferenceProduct($id_product);
                    $output['employee'] = $this->getEmployeeName($id_employee, $id_stock);
                    $output['name'] = $this->getAttributeProduct($id_product_attribute, $id_product, $id_stock);
                    $output['image_url'] = MpStockTools::getImageProduct($id_product);
                    $output['type'] = $this->getTypemovement($row['id_mp_stock_type_movement']);
                    if ($row['qty'] > 0) {
                        $output['qty'] = '<i class="icon-arrow-right" style="color: #1fc62d;"></i> <strong>' . abs($row['qty']) . '</strong>';
                    } else {
                        $output['qty'] = '<i class="icon-arrow-left" style="color: #c12020;"></i> <strong>' . abs($row['qty']) . '</strong>';
                    }
                    $movements[] = $output;
                }
            }
        } else {
            if ($db->getMsgError()) {
                $this->errors[] = $this->l('Error reading stock movements.');
                $this->errors[] = $db->getMsgError();
            }
            return array();
        }

        return $movements;
    }

    public function getDiscount($original_price, $discount_price)
    {
        if ($original_price != 0) {
            return (($original_price - $discount_price) * 100) / $original_price;
        } else {
            return 0;
        }
    }

    public function getImageProduct($id_product)
    {
        $shop = new ShopCore(Context::getContext()->shop->id);
        $product = new ProductCore((int) $id_product);
        $images = $product->getImages(Context::getContext()->language->id);

        foreach ($images as $obj_image) {
            $image = new ImageCore((int) $obj_image['id_image']);
            if ($image->cover) {
                return $shop->getBaseURL(true) . 'img/p/' . $image->getExistingImgPath() . '-small.jpg';
            }
        }
        return '';
    }

    public function perc($value)
    {
        return number_format($value, 2) . " %";
    }

    public function chkBox($name, $value)
    {
        return "<input type='checkbox' name='" . $name . "[]' value='" . $value . "'>";
    }

    public function active($id_product, $active)
    {
        if ($active) {
            $color = '#569117';
        } else {
            $color = '#992424';
        }

        return '<strong style="color: ' . $color . ';">' . $id_product . '</strong>';
    }

    public function getProductByEan13($ean13, $reference)
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('pa.id_product')
            ->select('pa.id_product_attribute')
            ->select('pa.ean13')
            ->select('p.reference')
            ->select('p.price')
            ->select('t.rate as tax_rate')
            ->from('product_attribute', 'pa')
            ->innerJoin('product', 'p', 'p.id_product=pa.id_product')
            ->innerJoin('tax_rule', 'tr', 'tr.id_tax_rules_group=p.id_tax_rules_group')
            ->innerJoin('tax', 't', 't.id_tax=tr.id_tax')
            ->where('pa.reference=\'' . pSQL($reference) . '\'')
            ->where('pa.ean13=\'' . pSQL($ean13) . '\'');

        $product = $db->getRow($sql);
        if (!$product) {
            return array();
        }

        $product['error'] = 0;
        $product['confirmation'] = $this->module->displayConfirmation(
            sprintf(
                "Product %s %s has been processed.",
                isset($product['reference']) ? $product['reference'] : '',
                isset($product['ean13']) ? $product['ean13'] : ''
            )
        );

        return $product;
    }

    public function displayMessage($params)
    {
        if ($params['type'] == self::TYPE_MESSAGE_ERROR) {
            $content = $this->module->displayError($params['message']);
            $error = true;
        } elseif ($params['type'] == self::TYPE_MESSAGE_WARNING) {
            $content = $this->module->displayWarning($params['message']);
            $error = true;
        } elseif ($params['type'] == self::TYPE_MESSAGE_CONFIRMATION) {
            $content = $this->module->displayConfirmation($params['message']);
            $error = false;
        } else {
            $content = $this->module->displayError($params['message']);
        }
        $params['message'] = $content;
        $params['error'] = $error;
    }

    public function getReferenceProduct($id_product)
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('reference')
            ->from('product')
            ->where('id_product = ' . (int) $id_product);
        $reference = $db->getValue($sql);

        return $reference;
    }

    public function getEmployeeName($id_employee, $id_stock)
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('firstname')
            ->select('lastname')
            ->from('employee')
            ->where('id_employee = ' . (int) $id_employee);
        $row = $db->getRow($sql);
        if ($row) {
            return $row['firstname'] . ' ' . $row['lastname'];
        } else {
            $this->errors[] = sprintf($this->l('Unable to read employee on stock line %d'), $id_stock);
        }
    }

    /**
     * Insert a new movement, if movement is a exchange movement, add a new movement for the exchanged product
     * @param type $id_mp_stock_exchange id movement reference
     * @param type $id_product id product
     * @param type $id_product_attribute id product attribute
     * @return type
     */
    public function insertmovement($id_mp_stock_exchange = 0, $id_product = null, $id_product_attribute = null)
    {
        $par = $this->getParameters();

        $sign = (int) $par['input_hidden_sign'];
        if (empty($id_product)) {
            $id_product = (int) $par['input_select_products'];
        }
        if (empty($id_product_attribute)) {
            $id_product_attribute = (int) $par['input_select_product_attributes'];
        }
        if ($id_mp_stock_exchange > 0) {
            $sign = (int) $par['input_hidden_sign'] * -1;
        }

        $stock = new MpStockObjectModel($this->module);
        $stock->id_mp_stock = (int) $par['input_text_id'];
        $stock->id_mp_stock_exchange = $id_mp_stock_exchange;
        $stock->id_shop = (int) $this->id_shop;
        $stock->id_product = $id_product;
        $stock->id_product_attribute = $id_product_attribute;
        $stock->id_mp_stock_type_movement = (int) $par['input_select_type_movements'];
        $stock->qty = ((int) $par['input_text_qty']) * $sign;
        $stock->price = (float) $par['input_text_price'];
        $stock->tax_rate = (float) $par['input_text_tax_rate'];
        $stock->date_add = date('Y-m-d h:i:s');
        $stock->id_employee = (int) $this->id_employee;

        if ($stock->id) {
            $insert = $stock->update();
        } else {
            $insert = $stock->add();
        }
        if ($insert) {
            //UPDATE PRESTASHOP STOCK AVAILABLE
            $id_stock_available = (int) MpStockObjectModel::getIdStockAvailable($stock->id_product_attribute);
            $stock->updateStock($id_stock_available, $stock->qty);
            if ($par['input_hidden_transform'] && $id_mp_stock_exchange == 0) {
                return $this->insertmovement(
                    $stock->id,
                    (int) $par['input_select_products_exchange'],
                    (int) $par['input_select_product_attributes_exchange']
                );
            }
            return array(
                'result' => true,
            );
        } else {
            return array(
                'result' => false,
                'error_msg' => Db::getInstance()->getMsgError(),
            );
        }
    }

    public function getParameters()
    {
        $parameters = Tools::getValue('parameters');
        $this->parameters = array();
        foreach ($parameters as $parameter) {
            $this->parameters[$parameter['name']] = $parameter['value'];
        }
        return $this->parameters;
    }

    public function createTable()
    {
        include $this->module->getPath() . 'classes/MpHelperTable.php';
        $helper = new MpHelperTable($this->module);
        $helper->header_title = $this->l('List Movements');
        $helper->header_icon = 'icon-list';
        $helper->header_color = '#5577BB';
        $helper->footer_title = $this->l('Total movements');
        $helper->footer_icon = 'icon-list';
        $helper->addImageDefinition($this->module->getUrl() . 'views/img/404.jpg');
        $helper->addToolbarButton(
            'addMovement',
            $this->link->getAdminLink($this->className) . '&addMovement',
            $this->l('Add new movement'),
            'process-icon-plus',
            '#3355BB'
        );
        $helper->addToolbarButton(
            'importMovement',
            'javascript:importXML();',
            $this->l('Import movements in XML format'),
            'process-icon-upload',
            '#55BB55'
        );
        $helper->addToolbarButton(
            'exportMovements',
            'javascript:'
            . 'exportMovements();',
            $this->l('Export movements'),
            'process-icon-download',
            '#88AABB'
        );
        $helper->addToolbarButton(
            'refreshMovements',
            'javascript:refreshMovements();',
            $this->l('Refresh table'),
            'process-icon-refresh'
        );
        $helper->addTableHeader(
            'col_image',
            'text-center',
            $this->l('Image'),
            MpHelperTable::TYPE_IMAGE,
            'image',
            false,
            '32px',
            'center'
        );
        $helper->addTableHeader(
            'col_reference',
            'text-center',
            $this->l('Reference'),
            MpHelperTable::TYPE_TEXT,
            'reference',
            true,
            'auto'
        );
        $helper->addTableHeader(
            'col_name',
            'text-center',
            $this->l('Name'),
            MpHelperTable::TYPE_TEXT,
            'name',
            true,
            'auto'
        );
        $helper->addTableHeader(
            'col_price',
            'text-center',
            $this->l('Price'),
            MpHelperTable::TYPE_PRICE,
            'price',
            false,
            'auto',
            'right'
        );
        $helper->addTableHeader(
            'col_tax_rate',
            'text-center',
            $this->l('Tax rate'),
            MpHelperTable::TYPE_PERCENTAGE,
            'tax_rate',
            false,
            'auto',
            'right'
        );
        $helper->addTableHeader(
            'col_qty',
            'text-center',
            $this->l('Qty'),
            MpHelperTable::TYPE_INT,
            'qty',
            false,
            'auto',
            'right'
        );
        $helper->addTableHeader(
            'col_movement',
            'text-center',
            $this->l('Movement'),
            MpHelperTable::TYPE_TEXT,
            'movement',
            true,
            'auto'
        );
        $helper->addTableHeader(
            'col_date',
            'text-center',
            $this->l('Date'),
            MpHelperTable::TYPE_DATE,
            'date',
            true,
            'auto',
            'center'
        );
        $helper->addTableHeader(
            'col_employee',
            'text-center',
            $this->l('Employee'),
            MpHelperTable::TYPE_TEXT,
            'employee',
            true,
            'auto'
        );
        $rows = $this->getRows();
        return $helper->generateTable($rows);
    }

    /**
     * Parse file name of imported xml file
     * @param  string $filename The file name to import
     * @return array  [number,date]
     */
    public function parseFilename($filename)
    {
        $pos1 = Tools::strpos($filename, "(");
        $pos2 = Tools::strpos($filename, "-", $pos1);
        $pos3 = Tools::strpos($filename, ")", $pos2);

        if ($pos1 === false || $pos2 === false || $pos3 === false) {
            return array(
                'number' => 0,
                'date' => '1970-01-01',
            );
        }

        $number = Tools::substr($filename, $pos1 + 1, $pos2 - $pos1 - 1);
        $date1 = Tools::substr($filename, $pos2 + 1, $pos3 - $pos2);
        $date2 = Tools::substr($date1, 0, 4)
            . '-'
            . Tools::substr($date1, 4, 2)
            . '-'
            . Tools::substr($date1, 6, 2);
        if (!$this->validateDate($date2, 'Y-m-d')) {
            $date2 = '1970-01-01';
        }
        return array(
            'number' => $number,
            'date' => $date2,
        );
    }

    function validateDate($date, $format = 'Y-m-d H:i:s')
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }

    public function ajaxProcessImportConfig()
    {
        $db = Db::getInstance();
        $db->execute('DELETE FROM ' . _DB_PREFIX_ . 'mpstock_mvt_reason');
        $db->execute('DELETE FROM ' . _DB_PREFIX_ . 'mpstock_mvt_reason_lang');
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'mp_stock_type_movement WHERE id_lang=' . (int) $this->id_lang;
        $result = $db->executeS($sql);
        if (!$result) {
            print Tools::jsonEncode(
                array(
                    'result' => true,
                    'message' => $this->l('Nothing to import.'),
                )
            );
            exit();
        }
        $output = array(
            'result' => true,
            'messages' => array(),
        );
        foreach ($result as $row) {
            $obj = new ModelMpStockMvtReason(
                (int) $row['id_mp_stock_type_movement'],
                (int) $this->id_lang
            );
            $obj->force_id = true;
            $obj->id = (int) $row['id_mp_stock_type_movement'];
            $obj->id_stock_mvt_reason = (int) $row['id_mp_stock_type_movement'];
            $obj->sign = (int) $row['sign'] == -1 ? true : false; //sign now is bool, true indicates sign presence for negative values
            $obj->deleted = 0;
            $obj->transform = (bool) $row['exchange'];
            $obj->date_add = date('Y-m-d H:i:s');
            $obj->name[$this->id_lang] = $row['name'];
            $result = $obj->add();
            if (!$result) {
                $output['result'] = $output['result'] && $result;
                $output['messages'][] = sprintf(
                    $this->l('Import failed for movement %s.'),
                    $row['name']
                );
            } else {
                $output['result'] = $output['result'] && $result;
                $output['messages'][] = sprintf(
                    $this->l('Imported movement %s.'),
                    $row['name']
                );
            }
        }
        print Tools::jsonEncode(
            array(
                'result' => $output['result'],
                'message' => implode(PHP_EOL, $output['messages']),
            )
        );
        exit();
    }

    function ajaxProcessSetActiveTab()
    {
        $id = (int) Tools::getValue('active_tab', 0);
        $this->cookie->__set('admin_mp_stock_active_tab', $id);
    }

    public function ajaxProcessRealignQuantities()
    {
        $db = Db::getInstance();
        $db->update(
            'stock_available',
            array(
                'quantity' => 0,
            ),
            'id_product_attribute=0'
        );
        $sql = new DbQuery();
        $sql->select("id_product")
            ->select("sum(quantity) as qty")
            ->from('stock_available')
            ->groupBy('id_product');
        $result = $db->executeS($sql);
        if ($result) {
            foreach ($result as $row) {
                $db->update(
                    'stock_available',
                    array(
                        'quantity' => (int) $row['qty'],
                    ),
                    'id_product=' . (int) $row['id_product'] . ' and id_product_attribute=0'
                );
            }
            print Tools::jsonEncode(
                array(
                    'result' => true,
                )
            );
        } else {
            print Tools::jsonEncode(
                array(
                    'result' => false,
                    'error' => $db_ > getMsgError()
                )
            );
        }
        exit();
    }

    function ajaxProcessToggle()
    {
        $db = Db::getInstance();
        $id = (int) Tools::getValue('id');
        $tablename = Tools::getValue('tablename');
        $field = Tools::getValue('field');
        if ($field == 'sign') {
            $positive = 'icon-plus-circle';
            $negative = 'icon-minus-circle';
        } else {
            $positive = 'icon-check';
            $negative = 'icon-times';
        }
        //before
        $sql = "SELECT `" . $field . "` FROM `" . _DB_PREFIX_ . $tablename . "` WHERE id_" . $tablename . "=" . (int) $id;
        //update        
        $sql = "UPDATE `" . _DB_PREFIX_ . $tablename . "` SET `" . $field . "` = 1-`" . $field . "` WHERE id_" . $tablename . "=" . (int) $id;
        $result = Db::getInstance()->execute($sql);
        //after
        $sql = "SELECT `" . $field . "` FROM `" . _DB_PREFIX_ . $tablename . "` WHERE id_" . $tablename . "=" . (int) $id;
        $value = (int) $db->getValue($sql);
        $output = array();
        if ((bool) $value === true) {
            $output = array(
                'addClass' => $positive,
                'removeClass' => $negative,
                'color' => '#72C279',
            );
        } else {
            $output = array(
                'addClass' => $negative,
                'removeClass' => $positive,
                'color' => '#E08F95',
            );
        }
        print Tools::jsonEncode($output);
        exit();

    }

    public function ajaxProcessGetDocumentRows()
    {
        $id_document = (int) Tools::getValue('id_document', 0);
        $empty_row = array(
            'id_mpstock_movement' => 0,
            'id_product' => 0,
            'id_product_attribute' => 0,
            'product' => '',
            'stock_quantity_before' => 0,
            'stock_movement' => 0,
            'price_te' => 0,
            'price_te_float' => 0,
            'wholesale_price_te' => 0,
            'wholesale_price_te_float' => 0,
            'tax_rate' => 0,
            'tax_rate_float' => 0,
        );
        $rows = $this->getProductsRows($id_document);

        $this->smarty->assign(
            array(
                'blank_row' => $empty_row,
                'fill_rows' => $rows,
                'id_document' => $id_document,
                'template_row' => $this->module->getAdminTemplatePath() . 'getDocumentRow.tpl',
            )
        );
        $html = $this->smarty->fetch($this->module->getAdminTemplatePath() . 'getDocumentRows.tpl');
        print Tools::jsonEncode(
            array(
                'htmlrow' => $html,
                'result' => print_r($rows, 1)
            )
        );
        exit();
    }
    public function ajaxProcessGetProductAutocomplete()
    {
        $term = Tools::getValue('term', '');

        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('pa.id_product_attribute')
            ->select('p.id_product')
            ->select('pa.ean13')
            ->select('p.reference')
            ->select('p.price')
            ->select('pl.name')
            ->from('product_attribute', 'pa')
            ->innerJoin('product', 'p', 'p.id_product=pa.id_product')
            ->innerJoin('product_lang', 'pl', 'p.id_product=pl.id_product')
            ->where('pl.id_shop=' . (int) $this->id_shop)
            ->where('pl.id_lang=' . (int) $this->id_lang)
            ->orderBy('p.id_product')
            ->orderBy('pl.name')
            ->limit(50);

        switch (Tools::substr($term, 0, 2)) {
            case ('e:')://byEan13
                $sql->where('pa.ean13 like \'%' . Tools::substr($term, 2) . '%\'');
                break;
            case ('r:')://byReference
                $sql->where('p.reference like \'%' . Tools::substr($term, 2) . '%\'');
                break;
            case ('p:');//byName
                $sql->where('pl.name like \'%' . Tools::substr($term, 2) . '%\'');
                break;
            case ('u:');//byUpc
                $sql->where('p.upc like \'%' . Tools::substr($term, 2) . '%\'');
                break;
            default://All of them
                $sql->where(
                    'pa.ean13 like \'%' . $term . '%\''
                    . ' OR ' .
                    'p.reference like \'%' . $term . '%\''
                    . ' OR ' .
                    'pl.name like \'%' . $term . '%\''
                    . ' OR ' .
                    'p.upc like \'%' . $term . '%\''
                );
                break;
        }
        $result = $db->executeS($sql);
        $output = array();
        foreach ($result as $row) {
            $other = MpStockProductObjectModel::getOtherAttributes($row['id_product_attribute']);
            $output[] = array(
                'id' => $row['id_product_attribute'],
                'id_product' => $row['id_product'],
                'id_product_attribute' => $row['id_product_attribute'],
                'label' => $row['reference'] . ' - ' . $other['name'],
                'value' => $other['name'],
                'price_te' => Tools::displayPrice($other['price_te']),
                'price_te_float' => $other['price_te'],
                'wholesale_price_te' => Tools::displayPrice($other['wholesale_price_te']),
                'wholesale_price_te_float' => $other['wholesale_price_te'],
                'tax_rate' => self::formatPercent($other['tax_rate']),
                'tax_rate_float' => $other['tax_rate'],
                'stock_quantity_before' => $other['stock_quantity_before'],
            );
        }
        print Tools::jsonEncode($output);
        exit();
    }

    public function getProductsRows($id_document)
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('p.*')
            ->from('mpstock_movement', 'p')
            ->innerJoin('mpstock_document', 'd', 'd.id_mpstock_document=p.id_document')
            ->where('d.id_shop=' . (int) $this->id_shop)
            ->where('d.id_mpstock_document=' . (int) $id_document)
            ->orderBy('p.id_mpstock_movement');
        $result = $db->executeS($sql);
        if ($result) {
            foreach ($result as &$row) {
                $row['id_mpstock_document'] = (int) $id_document;
                $row['product'] =
                    MpStockProductObjectModel::getAttributeProduct(
                        $row['id_product_attribute'],
                        $row['id_product']
                    );
                $row['price_te_float'] = $row['price_te'];
                $row['price_te'] = Tools::displayPrice($row['price_te']);
                $row['wholesale_price_te_float'] = $row['wholesale_price_te'];
                $row['wholesale_price_te'] = Tools::displayPrice($row['wholesale_price_te']);
                $row['tax_rate_float'] =
                    MpStockProductObjectModel::getProductTaxRate(
                        $row['id_product']
                    );
                $row['tax_rate'] = self::formatPercent($row['tax_rate_float']);
            }
            return $result;
        }
        return array();
    }

    public static function formatPercent($value)
    {
        $perc = Tools::displayPrice($value);
        $perc_value = preg_replace("/[^\d.,]/", "", $perc);
        return $perc_value . ' %';
    }

    /**
     * AJAX CALLS
     */

    public function ajaxProcessImportXML()
    {
        /** Check if user is logged **/
        $cookie = new CookieCore('psAdmin');
        /** COOKIE **/
        if (!$cookie->isLoggedBack()) {
            print Tools::jsonEncode(
                array(
                    array(
                        'reference' => $this->l('Session expired'),
                        'error' => $this->module->displayError(
                            $this->l('Your session has expired.')
                        ),
                    )
                )
            );
            exit();
        }

        $this->id_lang = $cookie->id_lang;
        $this->id_shop = (int) Context::getContext()->shop->id;
        $this->id_employee = $cookie->id_employee;

        $file = Tools::fileAttachment('inputFileXML');
        $filename = $file['name'];

        $importObj = new MpStockObjectModelImport();
        $importObj->filename = $filename;
        $importObj->id_employee = (int) $this->id_employee;
        $importObj->id_shop = (int) $this->id_shop;
        $importObj->date_add = date('Y-m-d H:i:s');
        try {
            $id_import = (int) $importObj->add();
        } catch (Exception $ex) {
            $json = array(
                array(
                    $this->displayMessage(
                        array(
                            'type' => self::TYPE_MESSAGE_ERROR,
                            'reference' => $this->l('Invalid reference'),
                            'message' => $ex->getMessage(),
                        )
                    ),
                )
            );

            print Tools::jsonEncode($json);
            exit();
        }

        if ($id_import) {
            $id_import = (int) Db::getInstance()->Insert_ID();
        }

        $output = array();
        $json = array();
        if ($file['content']) {
            $xml = simplexml_load_string($file['content']);
            $sign = (string) $xml->movement_type == 'load' ? 1 : -1;
            $date = (string) $xml->movement_date;
            $rows = $xml->rows;
            //$output['xml'] = $rows;
            foreach ($rows->children() as $row) {
                $ean13 = (string) $row->ean13;
                $reference = (string) $row->reference;
                $qty = (string) $row->qty * $sign;
                $date_movement = $date;
                $output[] = array(
                    'ean13' => $ean13,
                    'reference' => $reference,
                    'qty' => $qty,
                    'date_movement' => $date_movement,
                );
            }

            foreach ($output as $row) {
                $error_message = '';
                $error_db = '';
                $ean13 = trim($row['ean13']);
                $reference = trim($row['reference']);
                if (empty($ean13)) {
                    array_push(
                        $json,
                        array(
                            'reference' => $row['reference'],
                            'error' => $this->module->displayError(
                                $this->l('Ean13 not valid.')
                            ),
                        )
                    );
                    continue;
                } elseif (empty($reference)) {
                    array_push(
                        $json,
                        $this->displayMessage(
                            array(
                                'type' => self::TYPE_MESSAGE_ERROR,
                                'reference' => $this->l('Invalid reference'),
                                'message' => $this->l('Unable to find product.'),
                            )
                        )
                    );
                    continue;
                }
                $product = $this->getProductByEan13($ean13, $reference);
                if (!$product) {
                    array_push(
                        $json,
                        $json,
                        $this->displayMessage(
                            array(
                                'type' => self::TYPE_MESSAGE_ERROR,
                                'reference' => $this->l('Invalid reference'),
                                'message' => sprintf($this->l('Combination with ean13 %s not found.'), $ean13),
                            )
                        )
                    );
                    continue;
                }
                $stock = new MpStockObjectModel($this->module);
                $stock->id = 0;
                $stock->id_mp_stock_import = $id_import;
                $stock->id_mp_stock_type_movement = 0;
                $stock->id_mp_stock_exchange = 0;
                $stock->id_product = $product['id_product'];
                $stock->id_product_attribute = $product['id_product_attribute'];
                $stock->qty = abs((int) $row['qty']);
                $stock->price = $product['price'];
                $stock->tax_rate = $product['tax_rate'];
                $stock->id_lang = $this->id_lang;
                $stock->id_shop = $this->id_shop;
                $stock->id_employee = $this->id_employee;
                $stock->date_movement = $date;
                $stock->sign = $sign;
                $stock->date_add = date('Y-m-d H:i:s');
                try {
                    $add = $stock->save();
                } catch (Exception $ex) {
                    $add = false;
                    $error_message = "Exception: " . $ex->getMessage();
                    $error_db = "Database: " . Db::getInstance()->getMsgError();
                }
                if ((int) $add == 0) {
                    array_push(
                        $json,
                        array(
                            'reference' => $product['reference'],
                            'error' => $this->module->displayError(
                                sprintf(
                                    $this->l('Unable to add product. Error: %s, %s'),
                                    $error_message,
                                    $error_db
                                )
                            ),
                        )
                    );
                    continue;
                }
                array_push($json, $product);
            }
            print Tools::jsonEncode($json);
        } else {
            $this->displayMessage(
                array(
                    'type' => self::TYPE_MESSAGE_ERROR,
                    'title' => $this->l('Import XML'),
                    'message' => $this->l('File empty')
                )
            );
        }
        exit();
    }

    public function ajaxProcessGetFeatureValue()
    {
        $values = $this->getFeatureValues((int) Tools::getValue('id_feature'));
        print Tools::jsonEncode($values);
        exit();
    }

    public function ajaxProcessGetProduct()
    {
        $term = Tools::getValue('term', '');
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('p.id_product')
            ->select('p.reference')
            ->select('pl.name')
            ->from('product', '`p`')
            ->innerJoin('product_lang', '`pl`', 'p.id_product=pl.id_product')
            ->where('pl.id_lang=' . (int) $this->id_lang)
            ->where('p.reference like \'' . pSQL($term) . '%\' or pl.name like \'%' . pSQL($term) . '%\'')
            ->orderBy('pl.name');
        $result = $db->executeS($sql);
        if ($result) {
            $output = array();
            foreach ($result as $row) {
                $output[] = array(
                    'id' => $row['id_product'],
                    'label' => $row['reference'] . ' - ' . $row['name'],
                    'value' => $row['name'],
                );
            }
            print Tools::jsonEncode($output);
        } else {
            print Tools::jsonEncode(array());
        }
        exit();
    }

    public function ajaxProcessGetProductCombinations()
    {
        $id_product = (int) Tools::getValue('id_product', 0);
        $output_mode = Tools::getValue('output', 'table');
        if (!$id_product) {
            print Tools::jsonEncode(
                array(
                    'result' => false,
                    'html' => '',
                )
            );
        } else {
            require_once $this->module->getPath() . 'classes/ProductCombinations.php';
            $combinations = new MpStockProductCombinations($this->module, $id_product, $this->getMovements());
            $table = $combinations->display($output_mode);
            print Tools::jsonEncode(
                array(
                    'result' => true,
                    'html' => $table,
                )
            );
        }
        exit();
    }

    public function ajaxProcessGetProductAttributes()
    {
        $id_product = (int) Tools::getValue('id_product', 0);
        if (!$id_product) {
            print Tools::jsonEncode(
                array(
                    'id_product_attribute' => 0,
                    'name' => $this->l('Product attributes not found.'),
                )
            );
            exit();
        }
        $result = array();
        $mpstock = new MpStockObjectModel($this->module, null, $this->id_lang, $this->id_shop);
        $result['tax_rate'] = $mpstock->getTaxRate($id_product);
        $result['combinations'] = $mpstock->getProductAttributes($id_product);
        print Tools::jsonEncode($result);
        exit();
    }

    public function ajaxProcessGetProductAttributeValues()
    {
        $id_product_attribute = (int) Tools::getValue('id_product_attribute', 0);
        if (!$id_product_attribute) {
            print Tools::jsonEncode(
                array(
                    'id_product_attribute' => 0,
                    'name' => $this->l('Product attribute not found.'),
                    'ean13' => '',
                    'reference' => '',
                    'price' => 0,
                )
            );
            exit();
        }
        $mpstock = new MpStockObjectModel($this->module, null, $this->id_lang, $this->id_shop);
        print Tools::jsonEncode($mpstock->getProductAttributeValues($id_product_attribute));
        exit();
    }

    public function ajaxProcessGetTypemovement()
    {
        $id_type_movement = (int) Tools::getValue('id_type_movement', 0);
        if ($id_type_movement == 0) {
            print Tools::jsonEncode(
                array(
                    'result' => false,
                    'error_msg' => $this->module->l('movement type not valid.', get_class($this)),
                )
            );
            exit();
        }
        $db = Db::getInstance();
        $sql = new DbQueryCore();

        $sql->select('*')
            ->from('mp_stock_type_movement')
            ->where('id_mp_stock_type_movement=' . (int) $id_type_movement)
            ->where('id_shop=' . (int) $this->id_shop)
            ->where('id_lang=' . (int) $this->id_lang);
        $result = $db->getRow($sql);
        if ($result) {
            print Tools::jsonEncode(
                array(
                    'result' => true,
                    'id_movement' => (int) $result['id_mp_stock_type_movement'],
                    'name' => $result['name'],
                    'sign' => (int) $result['sign'],
                    'transform' => (int) $result['exchange'],
                )
            );
            exit();
        } else {
            print Tools::jsonEncode(
                array(
                    'result' => false,
                    'error_msg' => $db->getMsgError(),
                )
            );
            exit();
        }
    }

    public function ajaxProcessDeleteMovement()
    {
        $id_movement = (int) Tools::getValue('id_movement', 0);
        $mp_stock = new MpStockObjectModel($this->module, $id_movement);
        if ($mp_stock->delete()) {
            print Tools::jsonEncode(
                array(
                    'error' => false,
                    'message' => $this->l('Selected product has been deleted.'),
                    'title' => $this->l('Operation done')
                )
            );
            exit();
        } else {
            print Tools::jsonEncode(
                array(
                    'error' => true,
                    'message' => $this->module->displayError(
                        sprintf(
                            $this->l('Error deleting movement: %s'),
                            Db::getInstance()->getMsgError()
                        )
                    ),
                )
            );
        }
    }

    public function ajaxProcessGetCurrentStock()
    {
        $id_product_attribute = (int) Tools::getValue('id_product_attribute', 0);
        if (!$id_product_attribute) {
            return 0;
        }
        $db = Db::getInstance();
        $sql = "select id_product,quantity "
            . "from " . _DB_PREFIX_ . "product_attribute "
            . "where id_product_attribute=" . (int) $id_product_attribute;
        $row = $db->getRow($sql);
        if ($row) {
            $product = new ProductCore((int) $row['id_product']);
            $row['price'] = Tools::displayPrice($product->price);
            $row['wholesale_price'] = Tools::displayPrice($product->wholesale_price);
            $row['tax_rate'] = MpStockTools::getTaxRateFromIdProduct((int) $row['id_product'], true);
            $row['stock'] = (int) MpStockTools::getAvailableStock($id_product_attribute);
        } else {
            $row = array();
        }
        return Tools::jsonEncode($row);
    }

    public function ajaxProcessFillCombinationsOptions()
    {
        $id_product = (int) Tools::getValue('id_product', 0);
        if ($id_product == 0) {
            return '';
        }
        $mpstock = new MpStockObjectModel($this->module);
        $combinations = $mpstock->getProductAttributes($id_product);
        foreach ($combinations as &$comb) {
            $comb['value'] = $comb['id_product_attribute'];
        }
        $smarty = Context::getContext()->smarty;
        $smarty->assign(
            array(
                'rows' => $combinations,
            )
        );
        $options = $smarty->fetch($this->module->getAdminTemplatePath() . 'html_element_options.tpl');
        return Tools::jsonEncode(
            array(
                'options' => $options,
            )
        );
    }

    public function ajaxProcessUpdatemovement()
    {
        $this->errors = array();
        $row = Tools::getValue('row', null);
        if (empty($row)) {
            print Tools::jsonEncode(
                array(
                    'result' => false,
                    'msg_error' => $this->l('Invalid row'),
                )
            );
            exit();
        }
        $stock = new MpStockObjectModel($this->module);
        $stock->id_mp_stock_exchange = $row['exchange'];
        $stock->id_shop = $this->id_shop;
        $stock->id_product = $row['id_product'];
        $stock->id_product_attribute = $row['id_product_attribute'];
        $stock->id_mp_stock_type_movement = $row['type_movement'];
        $stock->qty = $row['qty'];
        $stock->price = $stock->toFloat($row['price']);
        $stock->tax_rate = $stock->toFloat($row['tax_rate']);
        $stock->date_add = date('Ymdhis');
        $stock->date_movement = $row['date_movement'] == 0 ? date('Y-m-d') : $row['date_movement'];
        $stock->sign = $row['sign'];
        $stock->id_employee = $this->id_employee;

        if ($stock->add()) {
            print Tools::jsonEncode(
                array(
                    'result' => true,
                    'row' => print_r($row, 1),
                )
            );
        } else {
            print Tools::jsonEncode(
                array(
                    'result' => false,
                    'msg_error' => $stock->errorMessage,
                )
            );
        }

        exit();
    }

    public function ajaxProcessFormatValue()
    {
        $value = Tools::getValue('value', '');
        $type = Tools::getValue('type', '');
        switch ($type) {
            case 'price':
                $output = MpStockTools::formatCurrency($value);
                break;
            case 'percent':
                $output = MpStockTools::formatPercent($value);
                break;
            default:
                $output = 0;
        }
        return Tools::jsonEncode(
            array(
                'value' => $output
            )
        );
    }

    public function ajaxProcessDelDocument()
    {
        $id = (int) Tools::getValue('id_document', 0);
        if (!$id) {
            print Tools::jsonEncode(
                array(
                    'result' => false,
                    'message' => $this->module->l('Select a valid document', get_class($this)),
                )
            );
            exit();
        }
        $stock = new MpStockObjectModelImport((int) $id);
        $id_movements = $stock->getIdMovements();
        $deleted = array();
        foreach ($id_movements as $id) {
            $obj = new MpStockObjectModel($this->module, $id);
            $result_obj = $obj->delete();
            $deleted[] = "delete movement $id: $result_obj";
        }
        $result = (int) $stock->delete();
        if ($result) {
            print Tools::jsonEncode(
                array(
                    'result' => true,
                    'message' => $this->module->l('Operation done.', get_class($this)),
                    'deleted' => $deleted,
                )
            );
        } else {
            print Tools::jsonEncode(
                array(
                    'result' => false,
                    'message' => sprintf(
                        $this->module->l('Error: %s', get_class($this)),
                        Db::getInstance()->getMsgError()
                    ),
                    'action' => (int) $result
                )
            );
        }
        exit();
    }

    public function ajaxProcessDelMovement()
    {
        $record = Tools::getValue('record');
        $id = (int) $record['id'];
        if (Tools::isSubmit('id_movement')) {
            $id = (int) Tools::getValue('id_movement', 0);
        }
        if (!$id) {
            print Tools::jsonEncode(
                array(
                    'result' => false,
                    'message' => $this->module->l('Select a valid movement', get_class($this)),
                    'record' => $record,
                )
            );
            exit();
        }
        $stock = new MpStockObjectModel($this->module, (int) $id);
        $result = (int) $stock->delete();
        if ($result) {
            print Tools::jsonEncode(
                array(
                    'result' => true,
                    'stock' => $stock->getCurrentStock(),
                    'message' => $this->module->l('Operation done.', get_class($this)),
                    'record' => $record,
                )
            );
        } else {
            print Tools::jsonEncode(
                array(
                    'result' => false,
                    'message' => sprintf(
                        $this->module->l('Error: %s', get_class($this)),
                        Db::getInstance()->getMsgError()
                    ),
                    'record' => $record,
                )
            );
        }
        exit();
    }

    public function ajaxProcessGetProductByEan13()
    {
        $ean13 = Tools::getValue('ean13', '');
        $db = Db::getInstance();
        $sql = "select * from " . _DB_PREFIX_ . "product_attribute where ean13='" . pSQL($ean13) . "'";
        $row = $db->getRow($sql);
        if ($row) {
            print Tools::jsonEncode(
                array(
                    'id_product' => $row['id_product'],
                    'id_product_attribute' => $row['id_product_attribute'],
                    'name' => MpStockTools::getProductName($row['id_product'])
                        . ' '
                        . MpStockTools::getProductCombinationName($row['id_product_attribute']),
                    'quantity' => 1,
                )
            );
        } else {
            print Tools::jsonEncode(
                array(
                    'id_product' => 0,
                    'id_product_attribute' => 0,
                    'name' => '',
                    'quantity' => 0,
                )
            );
        }
        exit();
    }

    public function ajaxProcessAddMovement()
    {
        $record = Tools::getValue('record');
        $stock = new MpStockObjectModel($this->module, $record['id']);
        $stock->id_mp_stock_import = 0;
        $stock->id_mp_stock_exchange = 0;
        $stock->id_shop = $this->id_shop;
        $stock->id_product_attribute = (int) $record['id_product_attribute'];
        $stock->id_product = $stock->getIdProductFromIdProductAttribute();
        $stock->name = $record['name'];
        $stock->id_mp_stock_type_movement = $record['movement'];
        $stock->qty = (int) $record['qty'];
        $stock->price = MpStockTools::parseValue($record['price']);
        $stock->wholesale_price = MpStockTools::parseValue($record['wholesale_price']);
        $stock->tax_rate = MpStockTools::parseValue($record['tax_rate']);
        $stock->date_movement = date('Y-m-d H:i:s');
        $stock->sign = $stock->getSign();
        $stock->date_add = $stock->date_movement;
        $stock->id_employee = (int) $this->id_employee;

        /** Check if is an exchange movement **/
        $exchange = (int) $stock->isExchangeMovement();
        /** Save record **/
        $result = $stock->save();
        /** Failed saving **/
        if (!$result) {
            print Tools::jsonEncode(
                array(
                    'result' => (int) $result,
                    'message' => sprintf(
                        $this->module->l('Error inserting record: %s'),
                        $stock->errorMessage
                    ),
                    'class' => $stock,
                )
            );
            exit();
        }
        /** Get current stock **/
        $current_stock = (int) $stock->getCurrentStock();
        /** Success **/
        print Tools::jsonEncode(
            array(
                'result' => true,
                'id' => $stock->id,
                'exchange' => (int) $exchange,
                'form' => $this->getExchangeForm($stock->id, $stock->id_mp_stock_type_movement),
                'stock' => (int) $current_stock,
                'message' => $this->module->l('Operation done.', get_class($this)),
                'record' => $record,
            )
        );
        exit();
    }

    public function ajaxProcessAddMovementExchange()
    {
        $movement = Tools::getValue('movement', array());
        if (!$movement) {
            print Tools::jsonEncode(
                array(
                    'result' => false,
                    'message' => $this->module->l('Input values not valid.', get_class($this)),
                )
            );
            exit();
        }

        $id_mp_stock_exchange = (int) $movement['id_exchange'];
        $id_mp_stock_type_movement = (int) $movement['id_movement'];
        $id_product_attribute = (int) $movement['id_product_attribute'];
        $id_product_attribute_name = $movement['id_product_attribute_name'];
        $qty = (int) $movement['qty'];
        $wholesale_price = MpStockTools::parseValue($movement['wholesale_price']);
        $price = MpStockTools::parseValue($movement['price']);
        $tax_rate = MpStockTools::parseValue($movement['tax_rate']);

        if ($id_mp_stock_exchange == 0 || $id_product_attribute == 0 || $qty == 0) {
            print Tools::jsonEncode(
                array(
                    'result' => false,
                    'message' => $this->module->l('Input values not valid.', get_class($this)),
                )
            );
            exit();
        }

        $id = MpStockObjectModel::getIdMovementByExchangeId($id_mp_stock_exchange);

        if ($id) {
            $stock = new MpStockObjectModel($this->module, $id);
            $stock->qty = $qty;
            $stock->wholesale_price = $wholesale_price;
            $stock->price = $price;
            $stock->tax_rate = $tax_rate;
        } else {
            $stock = new MpStockObjectModel($this->module);
            $stock->id_mp_stock_import = 0;
            $stock->id_mp_stock_exchange = $id_mp_stock_exchange;
            $stock->id_shop = $this->id_shop;
            $stock->id_product_attribute = (int) $id_product_attribute;
            $stock->id_product = $stock->getIdProductFromIdProductAttribute();
            $stock->name = $id_product_attribute_name;
            $stock->id_mp_stock_type_movement = (int) $id_mp_stock_type_movement;
            $stock->qty = $qty;
            $stock->price = $price;
            $stock->wholesale_price = $wholesale_price;
            $stock->tax_rate = $tax_rate;
            $stock->date_movement = date('Y-m-d H:i:s');
            $stock->sign = (int) (MpStockTools::getSign((int) $id_mp_stock_type_movement) * -1);
            $stock->date_add = $stock->date_movement;
            $stock->id_employee = (int) $this->id_employee;
        }

        $result = $stock->save();

        /** Failed saving **/
        if (!$result) {
            print Tools::jsonEncode(
                array(
                    'result' => (int) $result,
                    'message' => sprintf(
                        $this->module->l('Error inserting record: %s'),
                        $stock->errorMessage
                    ),
                    'class' => $stock,
                )
            );
            exit();
        }
        /** Get current stock **/
        $current_stock = (int) $stock->getCurrentStock();
        /** Success **/
        print Tools::jsonEncode(
            array(
                'result' => true,
                'id' => $stock->id,
                'exchange' => 0,
                'id_exchange' => $stock->id,
                'stock' => (int) $current_stock,
                'message' => $this->module->l('Operation done.', get_class($this)),
                'record' => null,
            )
        );
        exit();
    }

    public function ajaxProcessShowCombinationsForm()
    {
        $list = new MpStockAdminHelperListAddMovement($this->module);
        $content = array(
            'result' => true,
            'form' => $list->display(),
            'options' => $list->getOptionsCombination(),
        );
        print Tools::jsonEncode($content);
        exit();
    }

    public function ajaxProcessAlignStock()
    {
        $db = Db::getInstance();
        $sql = "update " . _DB_PREFIX_ . "product_attribute pa "
            . "inner join " . _DB_PREFIX_ . "stock_available sa on sa.id_product_attribute=pa.id_product_attribute "
            . "set pa.quantity=sa.quantity";
        $result = $db->execute($sql);
        if ($result) {
            return Tools::jsonEncode(
                array(
                    'result' => true,
                    'message' => $this->module->l('Align done.', get_class($this)),
                )
            );
        } else {
            return Tools::jsonEncode(
                array(
                    'result' => false,
                    'message' => sprintf(
                        $this->module->l('Error %s during stock align.', get_class($this)),
                        $db->getMsgError()
                    )
                )
            );
        }
    }

    public function ajaxProcessImportDocument()
    {
        $db = Db::getInstance();
        MpStockDocumentObjectModel::truncateTable();
        MpStockProductObjectModel::truncateTable();
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'mp_stock_import';
        $result = $db->executeS($sql);
        if (!$result) {
            print Tools::jsonEncode(
                array(
                    'result' => true,
                    'message' => $this->l('Nothing to import.'),
                )
            );
            exit();
        }
        $output = array(
            'result' => true,
            'messages' => array(),
        );
        foreach ($result as $row) {
            $obj = new MpStockDocumentObjectModel(
                (int) $row['id_mp_stock_import'],
                (int) $this->id_lang
            );
            $filename = $this->parseFilename($row['filename']);
            $output['messages'][] = 'Parsed filename ' . $row['filename'] . ': ' . print_r($filename, 1);
            $obj->force_id = true;
            $obj->id = (int) $row['id_mp_stock_import'];
            $obj->id_shop = (int) $row['id_shop'];
            $obj->number_document = $filename['number'];
            $obj->date_document = $filename['date'];
            $obj->id_mpstock_mvt_reason = (int) $row['id_type_document'];
            $obj->id_supplier = 0;
            $obj->tot_qty = 0;
            $obj->tot_document_te = 0;
            $obj->tot_document_taxes = 0;
            $obj->tot_document_ti = 0;
            $obj->id_employee = $this->id_employee;
            $obj->date_add = date('Y-m-d H:i:s');
            $result = $obj->add();
            $output['result'] = $output['result'] && $result;
            if (!$result) {
                $output['messages'][] = sprintf(
                    $this->l('Import failed for movement %s.'),
                    $row['name']
                );
            } else {
                $output['messages'][] = sprintf(
                    $this->l('Imported movement %s.'),
                    $row['filename']
                );
                $product = $this->processImportProducts($obj->id);
                $obj->tot_qty = $product['tot_qty'];
                $obj->tot_document_te = $product['tot_document_te'];
                $obj->tot_document_ti = $product['tot_document_ti'];
                $obj->tot_document_taxes = $product['tot_document_taxes'];
                $obj->id_supplier = $product['id_supplier'];
                $obj->save();
                array_merge($output['messages'], $product['messages']);
            }
        }
        array_merge($output['messages'], $this->processImportProductsOrphans());
        print Tools::jsonEncode(
            array(
                'result' => $output['result'],
                'message' => implode(PHP_EOL, $output['messages']),
            )
        );
        exit();
    }

    public function processImportProductsOrphans()
    {
        $db = Db::getInstance();
        $subsql = new DbQueryCore();
        $sql = new DbQueryCore();

        $subsql->select('id_mpstock_document')
            ->from('mpstock_document');

        $sql->select('*')
            ->from('mp_stock')
            ->where('id_mp_stock_import NOT IN (' . $subsql->build() . ')');

        $result = $db->executeS($sql);
        $messages = array();

        foreach ($result as $row) {
            $obj = new MpStockProductObjectModel();
            $obj->force_id = true;
            $obj->id = $row['id_mp_stock'];
            $obj->id_warehouse = 0;
            $obj->id_document = 0;
            $obj->id_mpstock_mvt_reason = $row['id_mp_stock_type_movement'];
            $obj->id_product = (int) $row['id_product'];
            $obj->id_product_attribute = (int) $row['id_product_attribute'];
            $other = $this->getOtherAttributes($obj->id_product_attribute);
            $obj->ean13 = $other['ean13'];
            $obj->upc = $other['upc'];
            $obj->reference = $other['reference'];
            $obj->stock_quantity_before = $row['snap'];
            $obj->stock_movement = $row['qty'];
            $obj->price_te = $row['price'];
            $obj->wholesale_price_te = $row['wholesale_price'];
            $obj->id_employee = $this->id_employee;
            $add = $obj->add();
            if ($add) {
                $messages[] = $this->module->l('Product inserted') . ': ' . $obj->reference;
            } else {
                $messages[] = $this->module->l('Product not inserted') . ': ' . $obj->reference;
            }
        }

        return $messages;
    }

    public function processImportProducts($id_document)
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $values = array(
            'id_supplier' => 0,
            'tot_qty' => 0,
            'tot_document_te' => 0,
            'tot_document_taxes' => 0,
            'tot_document_ti' => 0,
            'messages' => array(),
        );
        $sql->select('*')
            ->from('mp_stock')
            ->where('id_mp_stock_import=' . (int) $id_document);
        $result = $db->executeS($sql);

        foreach ($result as $row) {
            $obj = new MpStockProductObjectModel();
            $obj->force_id = true;
            $obj->id = $row['id_mp_stock'];
            $obj->id_warehouse = 0;
            $obj->id_document = (int) $id_document;
            $obj->id_mpstock_mvt_reason = $row['id_mp_stock_type_movement'];
            $obj->id_product = (int) $row['id_product'];
            $obj->id_product_attribute = (int) $row['id_product_attribute'];
            $other = MpStockProductObjectModel::getOtherAttributes($obj->id_product_attribute);
            $obj->ean13 = $other['ean13'];
            $obj->upc = $other['upc'];
            $obj->reference = $other['reference'];
            $obj->stock_quantity_before = $row['snap'];
            $obj->stock_movement = $row['qty'];
            $obj->price_te = $row['price'];
            $obj->wholesale_price_te = $row['wholesale_price'];
            $obj->id_employee = $this->id_employee;
            $values['tot_qty'] += $row['qty'];
            $values['tot_document_te'] += ($obj->stock_movement * $obj->price_te);
            $values['tot_document_ti'] += ($obj->stock_movement * $obj->price_te);
            $values['tot_document_taxes'] = $values['tot_document_ti'] - $values['tot_document_te'];
            $values['id_supplier'] = $other['id_supplier'];
            $add = $obj->add();
            if ($add) {
                $values['messages'][] = $this->module->l('Product inserted') . ': ' . $obj->reference;
            } else {
                $values['messages'][] = $this->module->l('Product not inserted') . ': ' . $obj->reference;
            }
        }

        return $values;
    }

    public function ajaxProcessLoadFile()
    {
        $attachment = Tools::fileAttachment('file', true);
        $file = $this->parseFilename($attachment['name']);
        $helper = new HelperListAdminMpStockDocument();
        $result = $helper->generateListImport($file);
        print Tools::jsonEncode($result);
        exit();
    }

    public function ajaxProcessImportFormattedDocumentXML()
    {
        $number = Tools::getValue('number');
        $date = Tools::getValue('date');
        $type_movement = (int) Tools::getValue('type');
        $id_supplier = (int) Tools::getValue('id_supplier');
        $rows = Tools::getValue('rows');

        $doc = new MpStockDocumentObjectModel();
        $doc->id_shop = (int) $this->id_shop;
        $doc->number_document = $number;
        $doc->date_document = $date;
        $doc->id_mpstock_mvt_reason = (int) $type_movement;
        $doc->id_supplier = (int) $id_supplier;
        $doc->tot_qty = 0;
        $doc->tot_document_te = 0;
        $doc->tot_document_taxes = 0;
        $doc->tot_document_ti = 0;
        $doc->id_employee = (int) $this->id_employee;
        $doc->date_add = date('Y-m-d H:i:s');
        $doc->date_upd = date('Y-m-d H:i:s');
        $result = (int) $doc->add();
        if ($result) {
            $result_rows = $this->ajaxProcessImportFormattedRows($rows, $doc->id, $type_movement);
            $doc->tot_qty = $result_rows['tot_qty'];
            $doc->tot_document_te = $result_rows['tot_document_te'];
            $doc->tot_document_taxes = $result_rows['tot_document_taxes'];
            $doc->tot_document_ti = $result_rows['tot_document_ti'];
            $result_doc = (int) $doc->save();
        }
        print Tools::jsonEncode(
            array(
                'result' => (int) $result_doc,
                'rows' => $result_rows,
            )
        );
        exit();
    }

    public function ajaxProcessImportFormattedRows($rows, $id_document, $type_movement)
    {
        $values = array(
            'tot_qty' => 0,
            'tot_document_te' => 0,
            'tot_document_ti' => 0,
            'tot_document_taxes' => 0,
            'id_supplier' => 0,
            'stock' => array(),
            'result' => array(),
        );
        foreach ($rows as $row) {
            $other =
                MpStockProductObjectModel::getOtherAttributes(
                    (int) $row['id_product_attribute']
                );
            $obj = new MpStockProductObjectModel();
            $obj->force_id = false;
            $obj->id_warehouse = 0;
            $obj->id_document = (int) $id_document;
            $obj->id_mpstock_mvt_reason = (int) $type_movement;
            $obj->id_product = (int) $row['id_product'];
            $obj->id_product_attribute = (int) $row['id_product_attribute'];
            $obj->ean13 = $other['ean13'];
            $obj->upc = $other['upc'];
            $obj->reference = $other['reference'];
            $obj->stock_quantity_before = $other['stock_quantity_before'];
            $obj->stock_movement = $row['qty'];
            $obj->price_te = $other['price_te'];
            $obj->wholesale_price_te = $other['wholesale_price_te'];
            $obj->id_employee = $this->id_employee;
            $values['tot_qty'] += $obj->stock_movement;
            $values['tot_document_te'] += ($obj->stock_movement * $obj->price_te * (int) $row['sign']);
            $values['tot_document_ti'] += ($obj->stock_movement * $obj->price_te * (int) $row['sign']);
            $values['tot_document_taxes'] = $values['tot_document_ti'] - $values['tot_document_te'];
            $values['id_supplier'] = $other['id_supplier'];
            $add = $obj->add();
            if ($add) {
                $current_stock = $obj->updateQty();
                $values['messages'][] = $this->module->l('Product inserted') . ': ' . $obj->reference;
            } else {
                $current_stock = 0;
                $values['messages'][] = $this->module->l('Product not inserted') . ': ' . $obj->reference;
            }
            $values['result'][] = (int) $add;
            $values['stock'][] = (int) $current_stock;
        }

        return $values;
    }

    public function ajaxProcessAddQuickMovement()
    {
        $product = Tools::getValue('product');
        $other = MpStockProductObjectModel::getOtherAttributes(
            (int) $product['id_product_attribute']
        );
        $obj = new MpStockProductObjectModel();
        $obj->force_id = false;
        $obj->id_warehouse = 0;
        $obj->id_document = 0;
        $obj->id_mpstock_mvt_reason = 0;
        $obj->id_product = (int) $product['id_product'];
        $obj->id_product_attribute = (int) $product['id_product_attribute'];
        $obj->ean13 = $other['ean13'];
        $obj->upc = '';
        $obj->reference = $other['reference'];
        $obj->stock_quantity_before = $other['stock_quantity_before'];
        $obj->stock_movement = $product['quantity'] * $product['sign'];
        $obj->price_te = $other['price_te'];
        $obj->wholesale_price_te = $other['wholesale_price_te'];
        $obj->id_employee = $this->id_employee;
        $result = $obj->add();
        if ($result) {
            $current_stock = $obj->updateQty();
        }
        print Tools::jsonEncode(
            array(
                'result' => (int) $result,
                'current_stock' => (int) $current_stock,
            )
        );
        exit();
    }

    public function ajaxProcessAddDocument()
    {
        $id = (int) Tools::getValue('id_document');
        $number = Tools::getValue('number');
        $date = Tools::getValue('date');
        $reason = Tools::getValue('reason');
        $sign = (int) Tools::getValue('sign');
        $supplier = (int) Tools::getValue('supplier');

        $doc = new MpStockDocumentObjectModel($id);
        $doc->number_document = $number;
        $doc->date_document = $date;
        $doc->id_mpstock_mvt_reason = (int) $reason;
        $doc->id_supplier = $supplier;
        $doc->tot_qty = 0;
        $doc->tot_document_te = 0;
        $doc->tot_document_ti = 0;
        $doc->tot_document_taxes = 0;
        $doc->id_employee = $this->id_employee;
        $result = $doc->add();
        print Tools::jsonEncode(
            array(
                'result' => (int) $result,
                'message' => Db::getInstance()->getMsgError(),
            )
        );
        exit();
    }

    public function ajaxProcessSaveDocumentRow()
    {
        $obj = Tools::getValue('row', array());
        $other = MpStockProductObjectModel::getOtherAttributes((int) $obj['id_product_attribute']);
        if ($obj) {
            $row = new MpStockProductObjectModel();
            $row->id_warehouse = 0;
            $row->id_mpstock_mvt_reason = (int) $obj['id_movement'];
            $row->id_document = (int) $obj['id_document'];
            $row->id_product = (int) $obj['id_product'];
            $row->id_product_attribute = (int) $obj['id_product_attribute'];
            $row->stock_movement = abs(
                (int) $obj['qty']
            ) *
                (int) MpStockProductObjectModel::getSign((int) $obj['id_movement']);
            $row->price_te = MpStockProductObjectModel::parseFloat($obj['price']);
            $row->wholesale_price_te = MpStockProductObjectModel::parseFloat($obj['wholesale_price']);
            $row->reference = $other['reference'];
            $row->ean13 = $other['ean13'];
            $row->upc = $other['upc'];
            $row->stock_quantity_before = $other['stock_quantity_before'];
            $row->id_employee = (int) $this->id_employee;
            $row->date_add = date('Y-m-d H:i:s');
            $row->date_upd = date('Y-m-d H:i:s');
            $result = $row->add();
            if ($result) {
                $row->updateQty();
                $tots = $row->updateDoc();
                print Tools::jsonEncode(
                    array(
                        'tot_document_ti' => Tools::displayPrice($tots['tot_document_ti']),
                        'tot_qty' => $tots['tot_qty'],
                    )
                );
            } else {
                print Tools::jsonEncode(array());
            }
            exit();
        }

        print Tools::jsonEncode(
            array(
                'tot_document_ti' => Tools::displayPrice(0),
                'tot_qty' => 0,
            )
        );
        exit();
    }

    public function getProductAttributeName($id_product_attribute)
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('id_attribute')
            ->from('product_attribute_combination')
            ->where('id_product_attribute=' . (int) $id_product_attribute);
        $attributes = $db->executeS($sql);
        if ($attributes) {
            return $this->getAttributes($attributes);
        } else {
            return array();
        }
    }

    public function getAttributes($attributes)
    {
        $db = Db::getInstance();
        $output = array();
        foreach ($attributes as $attr) {
            $sql = new DbQuery();
            $sql->select('al.name')
                ->select('ag.position')
                ->select('a.position as a_position')
                ->from('attribute_lang', 'al')
                ->innerJoin('attribute', 'a', 'a.id_attribute=al.id_attribute')
                ->innerJoin('attribute_group', 'ag', 'a.id_attribute_group=ag.id_attribute_group')
                ->where('al.id_attribute=' . (int) $attr['id_attribute']);
            $result = $db->getRow($sql);
            if ($result) {
                $output[] = array(
                    'group_position' => $result['position'],
                    'attr_position' => $result['a_position'],
                    'name' => Tools::strtoupper($result['name'])
                );
            }
        }
        if ($output) {
            array_multisort(
                array_column($output, 'group_position'),
                SORT_ASC,
                SORT_NUMERIC,
                array_column($output, 'attr_position'),
                SORT_ASC,
                SORT_NUMERIC,
                $output
            );
        } else {
            return array();
        }
        foreach ($output as $row) {
            $string[] = $row['name'];
        }
        return implode(" ", $string);
    }
}
