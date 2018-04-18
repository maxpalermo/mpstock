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

ini_set('max_execution_time', 300); //300 seconds = 5 minutes
ini_set('post_max_size', '128M');
ini_set('upload_max_filesize', '128M');

require_once _PS_MODULE_DIR_ . 'mpstock/classes/MpStockAdminImportXML.php';
require_once _PS_MODULE_DIR_ . 'mpstock/classes/MpStockAdminHelperForm.php';
require_once _PS_MODULE_DIR_ . 'mpstock/classes/MpStockAdminHelperListDocuments.php';
require_once _PS_MODULE_DIR_ . 'mpstock/classes/MpStockAdminHelperListMovements.php';
require_once _PS_MODULE_DIR_ . 'mpstock/classes/MpStockImportObjectModel.php';
require_once _PS_MODULE_DIR_ . 'mpstock/classes/MpStockObjectModel.php';

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
        $this->id_lang = (int)ContextCore::getContext()->language->id;
        $this->id_shop = (int)ContextCore::getContext()->shop->id;
        $this->id_employee = (int)ContextCore::getContext()->employee->id;
        $this->link = new LinkCore();
        $this->smarty = Context::getContext()->smarty;
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
        $this->confirmations[] = $this->displayConfirmation($message);
    }
    
    public function initContent()
    {
        $this->smarty = Context::getContext()->smarty;
        $this->link = new LinkCore();
        $list_content = '';
        $form_content = '';
        /** Ajax call **/
        if (Tools::isSubmit('ajax')) {
            $action = 'ajaxProcess' . ucfirst(Tools::getValue('action'));
            print $this->$action();
            exit();
        }
        /** Import XML file **/
        if (Tools::isSubmit('submitFormImport')) {
            if ($this->processImportXML()) {
                $this->addConfirmation($this->l('Import from XML done.'));
            }
        }
        /** Get Stock movements **/
        if (Tools::isSubmit('id_mp_stock_import') && Tools::isSubmit('updatemp_stock_import')) {
            $list = new MpStockAdminHelperListMovements($this->module);
            $list_content = $list->display((int)Tools::getValue('id_mp_stock_import'));
        } else {
            /** Show deafult list **/
            $list = new MpStockAdminHelperListDocuments($this->module);
            $list_content = $list->display();
            $form = new MpStockAdminHelperForm($this->module);
            $form_content = $form->display();
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
            $form_content = $this->initForm() . $this->initScript();
        }
        
        $this->content = $form_content.$list_content;
        
        parent::initContent();
        return;
        
        
        /**
         * DISPLAY DEFAULT PAGE
         */
        $this->content = implode('<br>', $this->messages) . $this->createTable() . $this->initScript();
        parent::initContent();
        return;
        
        
        if (Tools::isSubmit('mp_stockBox') || Tools::isSubmit('deletemp_stock')) {
            if (Tools::isSubmit('mp_stockBox')) {
                $id_movement = Tools::getValue('mp_stockBox');
            } else {
                $id_movement = (int)Tools::getValue('id_mp_stock');
            }
            MpStockObjectModel::deletemovement($id_movement);
            if (Db::getInstance()->getNumberError() == 0) {
                $this->messages = array(
                    $this->module->displayConfirmation($this->l('Selected movements deleted successfully'))
                );
            } else {
                $this->messages = array();
                $this->errors[] = sprintf($this->l('Error deleting movements: %s'), Db::getInstance()->getMsgError());
            }
        }
        
        if(Tools::isSubmit('submitNewmovement')) {
            $this->content = $this->initForm() . $this->initScript();
        } else {
            $this->content = implode('<br>', $this->messages) . $this->initList() . $this->initScript();
        }
        
        $helperList = new MpStockAdminHelperList($this->module);
        $this->content = $helperList->display();
        
        parent::initContent();
    }
    
    protected function processImportXML()
    {
        $importXML = new MpStockAdminImportXML($this->module, $this);
        $result = $importXML->import();
    }
    
    private function initForm()
    {
        $this->smarty->assign(
            array(
                'header_form' => $this->module->getPath().'views/templates/admin/AdminMpStockHeader.tpl',
                'content_form' => $this->module->getPath().'views/templates/admin/AdminMpStockContent.tpl',
                'footer_form' => $this->module->getPath().'views/templates/admin/AdminMpStockFooter.tpl',
                'transform_form' => $this->module->getPath().'views/templates/admin/AdminMpStockTransform.tpl',
                'back_url' => $this->link->getAdminLink($this->className),
                'tot_badge' => 0,
                'page' => 0,
                'pagination' => 0,
                'img_folder' => $this->module->getUrl().'views/img/',
                'select_stock_movements' => $this->getMovements(),
            )
        );
        
        return $this->smarty->fetch($this->module->getPath().'views/templates/admin/AdminMpStock.tpl');
    }
    
    private function initList()
    {
        $fields_list = array(
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
                'align' => 'text-left'
            ),
            'name' => array(
                'title' => $this->l('Name'),
                'width' => 'auto',
                'type' => 'text',
                'align' => 'text-left'
            ),
            'price' => array(
                'title' => $this->l('Price'),
                'width' => 96,
                'type' => 'price',
                'align' => 'text-right',
                'search' => false,
            ),
            'tax_rate' => array(
                'title' => $this->l('Tax rate'),
                'width' => 96,
                'type' => 'float',
                'align' => 'text-right',
                'search' => false
            ),
            'qty' => array(
                'title' => $this->l('Qty'),
                'width' => 64,
                'type' => 'bool',
                'float' => true,
                'align' => 'text-right',
                'search' => false,
            ),
            'type' => array(
                'title' => $this->l('movement'),
                'width' => 'auto',
                'type' => 'text',
                'align' => 'text-left'
            ),
            'date_add' => array(
                'title' => $this->l('Date'),
                'width' => 'auto',
                'type' => 'date',
                'align' => 'text-center'
            ),
            'employee' => array(
                'title' => $this->l('Employee'),
                'width' => 'auto',
                'type' => 'text',
                'align' => 'text-left'
            ),
            'filename' => array(
                'title' => $this->l('Import filename'),
                'width' => 'auto',
                'type' => 'text',
                'align' => 'text-left'
            ),
        );
        
        $helper = new HelperListCore();
        $helper->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete'),
                'confirm' => $this->l('Are you sure you want to delete selected movements?'),
            ),
        );
        $helper->_default_pagination = $this->selected_pagination;
        $helper->_pagination = array(
            5,
            10,
            20,
            50,
            100,
            500,
            1000,
        );
        $helper->page = $this->current_page;
        $helper->shopLinkType = '';
        $helper->simple_header = false;
        $helper->identifier = 'id_mp_stock';
        $helper->show_toolbar = true;
        $helper->title = $this->l('Stock movements');
        $helper->table = 'mp_stock';
        $helper->token = Tools::getAdminTokenLite($this->className);
        $helper->currentIndex = $this->link->getAdminLink($this->className).'&token='.$helper->token.'&addMovement';
        $helper->no_link = true;
        $helper->actions = array('delete');
        $helper->toolbar_btn = array(
            'new' => array(
                'href' => $helper->currentIndex.'&token='.$helper->token.'&addMovement',
                'desc' => $this->l('New movement'),
            ),
            'upload' => array(
                'href' => 'javascript:importXML();',
                'desc' => $this->l('Import XML'),
            ),
            'terminal' => array(
                'href' => '',
                'desc' => $this->l('Print report'),
            ),
            'preview' => array(
                'href' => '',
                'desc' => $this->l('Find movements'),
            ),
            'stats' => array(
                'href' => '',
                'desc' => $this->l('Statistics')
            )
        );
        $list = $this->getListProducts($helper, 'DESC');
        
        $table = $helper->generateList($list, $fields_list);
        return $table;
    }
    
    private function initScript()
    {
        return;
        $this->smarty->assign(
            array(
                'token' => Tools::getAdminTokenLite($this->className),
                'loading_gif' => $this->module->getURL() . 'views/img/loading.gif',
                'url_main' => $this->link->getAdminLink($this->className),
            )
        );
        $script = $this->smarty->fetch($this->module->getPath() . 'views/templates/admin/script.tpl');
        return $script;
    }
    
    public function setMedia()
    {
        if (Tools::getValue('controller') == $this->className) {
            parent::setMedia();
            $this->addJqueryUI('ui.dialog');
            $this->addJqueryUI('ui.progressbar');
            $this->addJqueryUI('ui.draggable');
            $this->addJqueryUI('ui.effect');
            $this->addJqueryUI('ui.effect-slide');
            $this->addJqueryUI('ui.effect-fold');
            $this->addJqueryUI('ui.autocomplete');
            $this->addJqueryUI('ui.datepicker');
            $this->addJqueryPlugin('growl');
            $this->addJS($this->module->getPath().'views/js/back.js');
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
            ->where('id_lang='.(int)$this->id_lang)
            ->where('id_shop='.(int)$this->id_shop)
            ->orderBy('name');
        $result = $db->executeS($sql);
        if (!$result) {
            return array();
        }
        foreach ($result as &$row)
        {
            $row['id'] = $row['id_mp_stock_type_movement'].'_'.$row['exchange'].'_'.$row['sign'];
        }
        return $result;
    }
    
    public function getTypemovement($id_movement)
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        
        $sql->select('name')
            ->from('mp_stock_type_movement')
            ->where('id_lang='.(int)$this->id_lang)
            ->where('id_shop='.(int)$this->id_shop)
            ->where('id_mp_stock_type_movement='.(int)$id_movement);
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
                ->where('id_shop = ' . (int)$this->id_shop)
                ->where('id_lang = ' . (int)$this->id_lang)
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
            ->where('pl.id_shop='.(int)$this->id_shop)
            ->where('pl.id_lang='.(int)$this->id_lang)
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
                ->where('id_lang='.(int)$this->id_lang)
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
                ->where('fvl.id_lang='.(int)$this->id_lang)
                ->where('fv.id_feature='.(int)$id_feature)
                ->orderBy('name');
        $result = $db->executeS($sql);
        if (!$result) {
            array_unshift(
                $result,
                array(
                    'id_feature_value' => 0,
                    'name' => $this-l('Select a feature value'),
                )
            );
            return array();
        }
        return $result;
    }
    
    public function getRows($pagination = 50, $page = 1)
    {
        $stock = new MpStockObjectModel();
        $rows =  $stock->getRows($pagination, $page);
        return $rows;
    }
    
    public function getListProducts(HelperListCore &$helper, $order = 'DESC')
    {
        PrestaShopLoggerCore::addLog('Init getLIstProduct');
        $this->id_lang = (int)Context::getContext()->language->id;
        $this->id_shop = (int)Context::getContext()->shop->id;
        
        $date = date('Y-m-d') . ' 00:00:00';
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
                $helper->_default_pagination = (int)Tools::getValue('mp_stock_pagination', 20);
                $offset = (int)Tools::getValue('submitFiltermp_stock', 0) * $helper->_default_pagination;
            } else {
                $offset = 0;
            }
            PrestaShopLoggerCore::addLog('total: ' . $helper->listTotal . '\npagination: ' . $helper->_default_pagination . '\noffset: ' . $offset);
            $result = array_splice($result, $offset, $helper->_default_pagination);
            
            foreach ($result as $row) {
                $id_product = (int)$row['id_product'];
                $id_product_attribute = (int)$row['id_product_attribute'];
                $id_employee = (int)$row['id_employee'];
                $id_stock = (int)$row['id_mp_stock'];
                if (!$id_product || !$id_product_attribute) {
                    $this->errors[] = sprintf($this->l('Unable to read product in stock line %d'), $id_stock);
                } else {
                    $output = $row;
                    $output['check'] = $this->chkBox('checkSelect[]', (int)$row['id_mp_stock']);
                    $output['reference'] = $this->getReferenceProduct($id_product);
                    $output['employee'] = $this->getEmployeeName($id_employee, $id_stock);
                    $output['name'] = $this->getAttributeProduct($id_product_attribute, $id_product, $id_stock);
                    $output['image_url'] = $this->img($this->getImageProduct($id_product));
                    $output['type'] = $this->getTypemovement($row['id_mp_stock_type_movement']);
                    if ($row['qty']>0) {
                        $output['qty'] = '<i class="icon-arrow-right" style="color: #1fc62d;"></i> <strong>'. abs($row['qty']) . '</strong>';
                    } else {
                        $output['qty'] = '<i class="icon-arrow-left" style="color: #c12020;"></i> <strong>'. abs($row['qty']) . '</strong>';
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
            return (($original_price-$discount_price)*100)/$original_price;
        } else {
            return 0;
        }
    }
    
    public function getImageProduct($id_product)
    {
        $shop = new ShopCore(Context::getContext()->shop->id);
        $product = new ProductCore((int)$id_product);
        $images = $product->getImages(Context::getContext()->language->id);

        foreach ($images as $obj_image) {
            $image = new ImageCore((int)$obj_image['id_image']);
            if ($image->cover) {
                return $shop->getBaseURL(true) . 'img/p/'. $image->getExistingImgPath() . '-small.jpg';
            }
        }
        return '';
    }
    
    public function img($url) 
    {
        return "<img src='" . $url . "' style='max-width: 48px; max-height: 48px; object-fit: contain;'>";
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
            ->where('pa.reference=\''.pSQL($reference).'\'')
            ->where('pa.ean13=\''.pSQL($ean13).'\'');
        
        $product = $db->getRow($sql);
        if (!$product) {
            return array();
        }
        
        $product['error'] = 0;
        $product['confirmation'] = $this->module->displayConfirmation(
            sprintf(
                "Product %s %s has been processed.",
                isset($product['reference'])?$product['reference']:'',
                isset($product['ean13'])?$product['ean13']:''
            )
        );
        
        return $product;
    }
    
    public function displayMessage($params) {
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
    
    public function ajaxProcessImportXML()
    {
        /** Check if user is logged **/
        $cookie = new CookieCore('psAdmin');
        
        if (!$cookie->isLoggedBack()) {
            print Tools::jsonEncode(array(
                array(
                    'reference' => $this->l('Session expired'),
                    'error' =>$this->module->displayError(
                        $this->l('Your session has expired.')),
                ))
            );
            exit();
        }
        
        $this->id_lang = $cookie->id_lang;
        $this->id_shop = (int)Context::getContext()->shop->id;
        $this->id_employee = $cookie->id_employee;
        
        $file = Tools::fileAttachment('inputFileXML');
        $filename = $file['name'];
        
        $importObj = new MpStockImportObjectModel();
        $importObj->filename = $filename;
        $importObj->id_employee = (int)$this->id_employee;
        $importObj->id_shop = (int)$this->id_shop;
        $importObj->date_add = date('Y-m-d H:i:s');
        try {
            $id_import = (int)$importObj->add();
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
            $id_import = (int)Db::getInstance()->Insert_ID();
        }
        
        $output = array();
        $json = array();
        if ($file['content']) {
            $xml = simplexml_load_string($file['content']);
            $sign = (string)$xml->movement_type=='load'?1:-1;
            $date = (string)$xml->movement_date;
            $rows = $xml->rows;
            //$output['xml'] = $rows;
            foreach ($rows->children() as $row) {
                $ean13 = (string)$row->ean13;
                $reference= (string)$row->reference;
                $qty = (string)$row->qty * $sign;
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
                    array_push($json, array(
                        'reference' => $row['reference'],
                        'error' =>$this->module->displayError(
                            $this->l('Ean13 not valid.')),
                    ));
                    continue;
                } elseif (empty($reference)) {
                    array_push($json, $this->displayMessage(
                        array(
                            'type' => self::TYPE_MESSAGE_ERROR,
                            'reference' => $this->l('Invalid reference'),
                            'message' => $this->l('Unable to find product.'),
                        )
                    ));
                    continue;
                }
                $product = $this->getProductByEan13($ean13, $reference);
                PrestaShopLoggerCore::addLog('PRODUCT:\n'.print_r($product,1));
                if (!$product) {
                    array_push($json, $json, $this->displayMessage(
                        array(
                            'type' => self::TYPE_MESSAGE_ERROR,
                            'reference' => $this->l('Invalid reference'),
                            'message' => sprintf($this->l('Combination with ean13 %s not found.'), $ean13),
                        )
                    ));
                    continue;
                }
                $stock = new MpStockObjectModel();
                $stock->id = 0;
                $stock->id_mp_stock_import = $id_import;
                $stock->id_mp_stock_type_movement = 0;
                $stock->id_mp_stock_exchange = 0;
                $stock->id_product = $product['id_product'];
                $stock->id_product_attribute = $product['id_product_attribute'];
                $stock->qty = $row['qty'];
                $stock->price = $product['price'];
                $stock->tax_rate = $product['tax_rate'];
                $stock->id_lang = $this->id_lang;
                $stock->id_shop = $this->id_shop;
                $stock->id_employee = $this->id_employee;
                $stock->date_movement = $date;
                $stock->sign = $sign;
                $stock->date_add = date('Y-m-d H:i:s');
                try {
                    $add = $stock->add();
                    PrestaShopLoggerCore::addLog('Adding new stock:'.(int)$add);
                } catch (Exception $ex) {
                    $add = false;
                    $error_message = "Exception: " . $ex->getMessage();
                    $error_db = "Database: " . Db::getInstance()->getMsgError();
                }
                if ((int)$add == 0) {
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
            PrestaShopLoggerCore::addLog(print_r($json,1));
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
        $values = $this->getFeatureValues((int)Tools::getValue('id_feature'));        
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
            ->where('pl.id_lang='.(int)$this->id_lang)
            ->where('p.reference like \''.pSQL($term).'%\' or pl.name like \'%'.pSQL($term).'%\'')
            ->orderBy('pl.name');
        $result = $db->executeS($sql);
        if ($result) {
            $output = array();
            foreach ($result as $row) {
                $output[] = array(
                    'id' => $row['id_product'],
                    'label' => $row['reference'].' - '.$row['name'],
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
        $id_product = (int)Tools::getValue('id_product', 0);
        $output_mode = Tools::getValue('output', 'table');
        if (!$id_product) {
            print Tools::jsonEncode(
                array(
                    'result' => false,
                    'html' => '',
                )
            );
        } else {
            require_once $this->module->getPath().'classes/ProductCombinations.php';
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
        $id_product = (int)Tools::getValue('id_product', 0);
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
        $mpstock = new MpStockObjectModel(null, $this->id_lang, $this->id_shop);
        $result['tax_rate'] = $mpstock->getTaxRate($id_product);
        $result['combinations'] = $mpstock->getProductAttributes($id_product);
        print Tools::jsonEncode($result);
        exit();
    }
    
    public function ajaxProcessGetProductAttributeValues()
    {
        $id_product_attribute = (int)Tools::getValue('id_product_attribute', 0);
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
        $mpstock = new MpStockObjectModel(null, $this->id_lang, $this->id_shop);
        print Tools::jsonEncode($mpstock->getProductAttributeValues($id_product_attribute));
        exit();
    }
    
    public function ajaxProcessGetTypemovement()
    {
        $id_type_movement = (int)Tools::getValue('id_type_movement', 0);
        if ($id_type_movement == 0) {
            print Tools::jsonEncode(
                array(
                    'result' => false,
                    'error_msg' => $this-l('movement type not valid.'),
                )
            );
            exit();
        }
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        
        $sql->select('*')
            ->from('mp_stock_type_movement')
            ->where('id_mp_stock_type_movement='.(int)$id_type_movement)
            ->where('id_shop='.(int)$this->id_shop)
            ->where('id_lang='.(int)$this->id_lang);
        $result = $db->getRow($sql);
        if ($result) {
            print Tools::jsonEncode(
                array(
                    'result' => true,
                    'id_movement' => (int)$result['id_mp_stock_type_movement'],
                    'name' => $result['name'],
                    'sign' => (int)$result['sign'],
                    'transform' => (int)$result['exchange'],
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
    
    public function ajaxProcessRefreshTable()
    {
        $content = $this->initList() . $this->initScript();
        print Tools::jsonEncode(
            array(
                'result' => true,
                'content' => $content,
            )
        );
        exit();
    }
    
    public function ajaxProcessFilterTable()
    {
        $filters = tools::getValue('filters');
        $output = array();
        foreach($filters as $filter) {
            $output[] = array('field_name' => $filter['field'], 'field_value' => $filter['value']);
        }
        print Tools::jsonEncode($output);
        exit();
    }
    
    public function ajaxProcessDeleteMovement()
    {
        $id_movement = (int)Tools::getValue('id_movement', 0);
        $mp_stock = new MpStockObjectModel($id_movement);
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
        $stock = new MpStockObjectModel();
        $stock->id_mp_stock_exchange = $row['exchange'];
        $stock->id_shop = $this->id_shop;
        $stock->id_product = $row['id_product'];
        $stock->id_product_attribute = $row['id_product_attribute'];
        $stock->id_mp_stock_type_movement = $row['type_movement'];
        $stock->qty = $row['qty'];
        $stock->price = $stock->toFloat($row['price']);
        $stock->tax_rate = $stock->toFloat($row['tax_rate']);
        $stock->date_add = date('Ymdhis');
        $stock->date_movement = $row['date_movement']==0?date('Y-m-d'):$row['date_movement'];
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
    
    public function getNameProduct($id_product)
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('name')
            ->from('product_lang')
            ->where('id_product = ' . (int)$id_product)
            ->where('id_lang = ' . (int)$this->id_lang);
        $name = $db->getValue($sql);
        
        return $name;
    }
    
    public function getAttributeProduct($id_product_attribute, $id_product)
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
    
    public function getReferenceProduct($id_product)
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('reference')
            ->from('product')
            ->where('id_product = ' . (int)$id_product);
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
            ->where('id_employee = ' . (int)$id_employee);
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
        
        $sign = (int)$par['input_hidden_sign'];
        if (empty($id_product)) {
            $id_product = (int)$par['input_select_products'];
        }
        if (empty($id_product_attribute)) {
            $id_product_attribute = (int)$par['input_select_product_attributes'];
        }
        if ($id_mp_stock_exchange>0) {
            $sign = (int)$par['input_hidden_sign'] * -1;
        }
        
        $stock = new MpStockObjectModel();
        $stock->id_mp_stock = (int)$par['input_text_id'];
        $stock->id_mp_stock_exchange = $id_mp_stock_exchange;
        $stock->id_shop = (int)$this->id_shop;
        $stock->id_product = $id_product;
        $stock->id_product_attribute = $id_product_attribute;
        $stock->id_mp_stock_type_movement = (int)$par['input_select_type_movements'];
        $stock->qty = ((int)$par['input_text_qty'])*$sign;
        $stock->price = (float)$par['input_text_price'];
        $stock->tax_rate = (float)$par['input_text_tax_rate'];
        $stock->date_add = date('Y-m-d h:i:s');
        $stock->id_employee = (int)$this->id_employee;
        
        if ($stock->id) {
            $insert = $stock->update();
        } else {
            $insert = $stock->add();
        }
        if ($insert) {
            //UPDATE PRESTASHOP STOCK AVAILABLE
            $id_stock_available = (int)MpStockObjectModel::getIdStockAvailable($stock->id_product_attribute);
            MpStockObjectModel::updateStock($id_stock_available, $stock->qty);
            if ($par['input_hidden_transform'] && $id_mp_stock_exchange == 0) {
                return $this->insertmovement(
                    $stock->id,
                    (int)$par['input_select_products_exchange'],
                    (int)$par['input_select_product_attributes_exchange']
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
        foreach($parameters as $parameter) {
            $this->parameters[$parameter['name']] = $parameter['value'];
        }
        return $this->parameters;
    }
    
    public function createTable()
    {
        include $this->module->getPath().'classes/MpHelperTable.php';
        $helper = new MpHelperTable($this->module);
        $helper->header_title = $this->l('List Movements');
        $helper->header_icon = 'icon-list';
        $helper->header_color = '#5577BB';
        $helper->footer_title = $this->l('Total movements');
        $helper->footer_icon = 'icon-list';
        $helper->addImageDefinition($this->module->getUrl().'views/img/404.jpg');
        $helper->addToolbarButton(
            'addMovement',
            $this->link->getAdminLink($this->className).'&addMovement',
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
}
