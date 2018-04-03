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
ini_set('post_max_size', '64M');
ini_set('upload_max_filesize', '64M');

require_once _PS_MODULE_DIR_ . 'mpstock/classes/mpstock.class.php';

class AdminMpStockController extends ModuleAdminController
{
    public $link;
    protected $id_lang;
    protected $id_shop;
    protected $id_employee;
    protected $messages;
    protected $local_path;
    protected $parameters = array();
    protected $smarty;
    
    public function __construct()
    {   
        $this->bootstrap = true;
        $this->context = Context::getContext();
        $this->className = 'AdminMpStock';
        $this->token = Tools::getValue('token', Tools::getAdminTokenLite($this->className));
        parent::__construct();
        $this->id_lang = (int)ContextCore::getContext()->language->id;
        $this->id_shop = (int)ContextCore::getContext()->shop->id;
        $this->id_employee = (int)ContextCore::getContext()->employee->id;
        $this->link = new LinkCore();
        $this->smarty = Context::getContext()->smarty;
    }

    public function initContent()
    {
        $this->errors = array();
        $this->messages = array();
        $this->smarty = Context::getContext()->smarty;
        $this->link = new LinkCore();
        
        if (Tools::isSubmit('ajax')) {
            $action = 'ajaxProcess' . Tools::getValue('action');
            print $this->$action();
            exit();
        }
        
        if (Tools::isSubmit('mp_stockBox') || Tools::isSubmit('deletemp_stock')) {
            if (Tools::isSubmit('mp_stockBox')) {
                $id_movement = Tools::getValue('mp_stockBox');
            } else {
                $id_movement = (int)Tools::getValue('id_mp_stock');
            }
            MpStockClassObject::deleteMovement($id_movement);
            if (Db::getInstance()->getNumberError() == 0) {
                $this->messages = array(
                    $this->module->displayConfirmation($this->l('Selected movements deleted successfully'))
                );
            } else {
                $this->messages = array();
                $this->errors[] = sprintf($this->l('Error deleting movements: %s'), Db::getInstance()->getMsgError());
            }
        }
        
        if(Tools::isSubmit('submitNewMovement')) {
            $this->content = $this->initForm() . $this->initScript();
        } else {
            $this->content = implode('<br>', $this->messages) . $this->initList() . $this->initScript();
        }
        parent::initContent();
    }
    
    private function initForm()
    {
        $this->smarty->assign(
            array(
                'header_form' => $this->module->getPath().'views/templates/admin/AdminMpStockHeader.tpl',
                'content_form' => $this->module->getPath().'views/templates/admin/AdminMpStockContent.tpl',
                'footer_form' => $this->module->getPath().'views/templates/admin/AdminMpStockFooter.tpl',
                'transform_form' => $this->module->getPath().'views/templates/admin/AdminMpStockTransform.tpl',
                'tot_badge' => 0,
                'page' => 0,
                'pagination' => 0,
                'img_folder' => $this->module->getUrl().'views/img/',
                'select_stock_movements' => $this->getMovements(),
            )
        );
        
        return $this->smarty->fetch($this->module->getPath().'views/templates/admin/AdminMpStock.tpl');
    }
    
    private function initForm2($id_movement = 0)
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Stock movement'),
                    'icon' => 'icon-list',
                ),
                'input' => array(
                    array(
                        'required' => true,
                        'type' => 'text',
                        'name' => 'input_text_id',
                        'label' => $this->l('Id'),
                        'desc' => $this->l('Id movement. You can\'t edit this field'),
                        'prefix' => '<i class="icon-chevron-right"></i>',
                        'suffix' => '<i class="icon-cogs"></i>',
                        'class' => 'input fixed-width-sm',
                        'readonly' => true,
                    ),
                    array(
                        'required' => true,
                        'type' => 'select',
                        'name' => 'input_select_products',
                        'label' => $this->l('Product'),
                        'desc' => $this->l('Select one product from the list above'),
                        'options' => array(
                            'query' => $this->getProducts(),
                            'id' => 'id_product',
                            'name' => 'name',
                        ),
                        'class' => 'chosen fixed-width-300',
                        'multiple' => false,
                    ),
                    array(
                        'required' => true,
                        'type' => 'select',
                        'name' => 'input_select_product_attributes',
                        'label' => $this->l('Combinations'),
                        'desc' => $this->l('Select one combination from the list above'),
                        'options' => array(
                            'query' => array(),
                            'id' => 'id_product_attribute',
                            'name' => 'name',
                        ),
                        'class' => 'chosen fixed-width-300',
                        'multiple' => false,
                    ),
                    array(
                        'required' => false,
                        'type' => 'text',
                        'name' => 'input_text_reference',
                        'label' => $this->l('Reference'),
                        'desc' => $this->l('Product reference.'),
                        'prefix' => '<i class="icon-chevron-right"></i>',
                        'suffix' => '<i class="icon-pencil"></i>',
                        'class' => 'input fixed-width-xl',
                    ),
                    array(
                        'required' => false,
                        'type' => 'text',
                        'name' => 'input_text_ean13',
                        'label' => $this->l('EAN13'),
                        'desc' => $this->l('Product EAN13.'),
                        'prefix' => '<i class="icon-chevron-right"></i>',
                        'suffix' => '<i class="icon-barcode"></i>',
                        'class' => 'input fixed-width-xxl',
                    ),
                    array(
                        'required' => true,
                        'type' => 'select',
                        'name' => 'input_select_type_movements',
                        'label' => $this->l('Type movement'),
                        'desc' => $this->l('Select one movement from the list above'),
                        'prefix' => '<i class="icon-chevron-right"></i>',
                        'suffix' => '<i class="icon-pencil"></i>',
                        'options' => array(
                            'query' => $this->getMovements(),
                            'id' => 'id_mp_stock_type_movement',
                            'name' => 'name',
                        ),
                        'class' => 'chosen',
                        'multiple' => false,
                    ),
                    array(
                        'required' => true,
                        'type' => 'select',
                        'name' => 'input_select_products_exchange',
                        'label' => $this->l('Product'),
                        'desc' => $this->l('Select one product from the list above'),
                        'options' => array(
                            'query' => $this->getProducts(),
                            'id' => 'id_product',
                            'name' => 'name',
                        ),
                        'class' => 'chosen fixed-width-300',
                        'multiple' => false,
                    ),
                    array(
                        'required' => true,
                        'type' => 'select',
                        'name' => 'input_select_product_attributes_exchange',
                        'label' => $this->l('Combinations'),
                        'desc' => $this->l('Select one combination from the list above'),
                        'options' => array(
                            'query' => array(),
                            'id' => 'id_product_attribute',
                            'name' => 'name',
                        ),
                        'class' => 'chosen fixed-width-300',
                        'multiple' => false,
                    ),
                    array(
                        'required' => true,
                        'type' => 'text',
                        'name' => 'input_text_qty',
                        'label' => $this->l('Quantity'),
                        'desc' => $this->l('Insert stock quantity.'),
                        'prefix' => '<i class="icon-chevron-right"></i>',
                        'suffix' => '<i class="icon-pencil"></i>',
                        'class' => 'input fixed-width-md text-right',
                    ),
                    array(
                        'required' => true,
                        'type' => 'text',
                        'name' => 'input_text_price',
                        'label' => $this->l('Price (tax. excl.)'),
                        'desc' => $this->l('Insert Product price tax excluded.'),
                        'prefix' => '<i class="icon-chevron-right"></i>',
                        'suffix' => '<i class="icon-pencil"></i>',
                        'class' => 'input fixed-width-md text-right',
                    ),
                    array(
                        'required' => true,
                        'type' => 'text',
                        'name' => 'input_text_tax_rate',
                        'label' => $this->l('Tax rate'),
                        'desc' => $this->l('Insert Product tax rate.'),
                        'prefix' => '<i class="icon-chevron-right"></i>',
                        'suffix' => '<i class="icon-percent"></i>',
                        'class' => 'input fixed-width-md text-right',
                    ),
                    array(
                        'required' => true,
                        'type' => 'hidden',
                        'name' => 'input_hidden_sign',
                    ),
                    array(
                        'required' => true,
                        'type' => 'hidden',
                        'name' => 'input_hidden_transform',
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('SAVE'),
                ),
                'buttons' => array(
                    array(
                        'title' => $this->l('back'),
                        'name' => 'btn_module_back',
                        'icon' => 'process-icon-back',
                        'id' => 'btn_module_back',
                        'href' => $this->link->getAdminLink($this->className)
                    ),
                    array(
                        'title' => $this->l('Select all'),
                        'name' => 'btn_products_select_all',
                        'icon' => 'process-icon-toggle-on',
                        'id' => 'btn_products_select_all'
                    ),
                    array(
                        'title' => $this->l('Select none'),
                        'name' => 'btn_products_select_none',
                        'icon' => 'process-icon-toggle-off',
                        'id' => 'btn_products_select_none'
                    ),
                    array(
                        'title' => $this->l('Print Report'),
                        'name' => 'btn_products_print_report',
                        'icon' => 'process-icon-configure',
                        'id' => 'btn_products_print_report'
                    ),
                    array(
                        'title' => $this->l('Process Products'),
                        'name' => 'btn_products_print_report',
                        'icon' => 'process-icon-ok',
                        'id' => 'btn_products_process'
                    ),
                ),
            ),
        );
        
        $helper = new HelperFormCore();
        $helper->table = 'product';
        $helper->default_form_language = (int)$this->id_lang;
        $helper->allow_employee_form_lang = (int) Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANGUAGE');
        $helper->submit_action = 'submit_form';
        $helper->currentIndex = $this->link->getAdminLink($this->className, false);
        $helper->token = Tools::getAdminTokenLite($this->className);
        if (Tools::isSubmit('submit_form')) {
            $submit_values = Tools::getAllValues();
            $output = array();
            foreach($submit_values as $key=>$value) {
                if(is_array($value)) {
                    $output[$key.'[]'] = $value;
                } else {
                    $output[$key] = $value;
                }
            }
            $helper->tpl_vars = array(
                'fields_value' => $output,
                'languages' => $this->context->controller->getLanguages(),
            );
        } else {
            $tplVars = MpStockClassObject::getTplVars($id_movement);
            PrestaShopLoggerCore::addLog('TPL VARS: ' . print_r($tplVars,1));
            $helper->tpl_vars = array(
                'fields_value' => $tplVars,
                'languages' => $this->context->controller->getLanguages(),
            );  
        }
        return $helper->generateForm(array($fields_form));
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
                'title' => $this->l('Movement'),
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
        );
        
        $helper = new HelperListCore();
        $helper->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete'),
                'confirm' => $this->l('Are you sure you want to delete selected movements?'),
            ),
        );
        $helper->_default_pagination = Tools::getValue('selected_pagination', 20);
        $helper->_pagination = array(
            5,
            10,
            20,
            50,
            100,
            500,
            1000,
        );
        $helper->page = Tools::getValue('page', 1);
        $helper->shopLinkType = '';
        $helper->simple_header = false;
        $helper->identifier = 'id_mp_stock';
        $helper->show_toolbar = true;
        $helper->title = $this->l('Stock movements');
        $helper->table = 'mp_stock';
        $helper->token = Tools::getAdminTokenLite($this->className);
        $helper->currentIndex = $this->link->getAdminLink($this->className, false);
        $helper->no_link = true;
        $helper->actions = array('delete');
        $helper->toolbar_btn = array(
            'new' => array(
                'href' => '',
                'desc' => $this->l('New movement'),
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
        Context::getContext()->controller->addJqueryPlugin('growl');
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
        parent::setMedia();
        $this->addJqueryUI('ui.dialog');
        $this->addJqueryUI('ui.progressbar');
        $this->addJqueryUI('ui.draggable');
        $this->addJqueryUI('ui.effect');
        $this->addJqueryUI('ui.effect-slide');
        $this->addJqueryUI('ui.effect-fold');
        $this->addJqueryUI('ui.autocomplete');
    }
    
    public function getMovements()
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        
        $sql->select('CONCAT(id_mp_stock_type_movement,\'-\',exchange, \'-\', sign) as id')
            ->select('name as value')
            ->from('mp_stock_type_movement')
            ->where('id_lang='.(int)$this->id_lang)
            ->where('id_shop='.(int)$this->id_shop)
            ->orderBy('name');
        $result = $db->executeS($sql);
        if (!$result) {
            return array();
        }
        return $result;
    }
    
    public function getTypeMovement($id_movement)
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
                    'name' => $this->l('Select a feature value'),
                )
            );
            return array();
        }
        return $result;
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
                    $output['type'] = $this->getTypeMovement($row['id_mp_stock_type_movement']);
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
        $mpstock = new MpStockClassObject(null, $this->id_lang, $this->id_shop);
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
        $mpstock = new MpStockClassObject(null, $this->id_lang, $this->id_shop);
        print Tools::jsonEncode($mpstock->getProductAttributeValues($id_product_attribute));
        exit();
    }
    
    public function ajaxProcessGetTypeMovement()
    {
        $id_type_movement = (int)Tools::getValue('id_type_movement', 0);
        if ($id_type_movement == 0) {
            print Tools::jsonEncode(
                array(
                    'result' => false,
                    'error_msg' => $this->l('Movement type not valid.'),
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

    public function ajaxProcessUpdateMovement()
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
        $stock = new MpStockClassObject();
        $stock->id_mp_stock = 0;
        $stock->id_mp_stock_exchange = $row['exchange'];
        $stock->id_shop = $this->id_shop;
        $stock->id_product = $row['id_product'];
        $stock->id_product_attribute = $row['id_product_attribute'];
        $stock->id_mp_stock_type_movement = $row['type_movement'];
        $stock->qty = $row['qty'];
        $stock->price = $row['price'];
        $stock->tax_rate = $row['tax_rate'];
        $stock->date_add = date('Ymdhis');
        $stock->id_employee = $this->id_employee;
        
        try {
            if ($stock->save()) {
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
                        'msg_error' => Db::getInstance()->getMsgError(),
                    )
                );
            }
        } catch (Exception $ex) {
            print Tools::jsonEncode(
                array(
                    'result' => false,
                    'msg_error' => $ex->getMessage(),
                    'class' => print_r(
                        array(
                            'price' => $stock->price,
                        ),
                        1
                    ),
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
    public function insertMovement($id_mp_stock_exchange = 0, $id_product = null, $id_product_attribute = null)
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
        
        $stock = new MpStockClassObject();
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
            $id_stock_available = (int)MpStockClassObject::getIdStockAvailable($stock->id_product_attribute);
            MpStockClassObject::updateStock($id_stock_available, $stock->qty);
            if ($par['input_hidden_transform'] && $id_mp_stock_exchange == 0) {
                return $this->insertMovement(
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
}
