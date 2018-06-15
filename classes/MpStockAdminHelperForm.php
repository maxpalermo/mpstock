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

Class MpStockAdminHelperForm extends HelperFormCore
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
        $this->localeInfo = MpStockTools::getLocaleInfo();
    }
    
    public function display()
    {
        $this->table = $this->table_name;
        $this->default_form_language = (int) ConfigurationCore::get('PS_LANG_DEFAULT');
        $this->allow_employee_form_lang = (int) ConfigurationCore::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG');
        $this->submit_action = 'submitFormImport';
        $this->currentIndex = $this->link->getAdminLink($this->module->getAdminClassName(), false);
        $this->token = Tools::getAdminTokenLite($this->module->getAdminClassName());
        $this->tpl_vars = array(
            'fields_value' => $this->getFieldsValue(),
            'languages' => $this->context->controller->getLanguages(),
        );
        return $this->generateForm($this->getFieldsForm()).$this->getScript();;
    }
    
    protected function getFieldsValue()
    {
        return array(
            $this->table_name.'_current_index' => '',
            $this->table_name.'_token' => Tools::getAdminTokenLite($this->module->getAdminClassName()),  
        );
    }
    
    protected function getFieldsForm()
    {
        $link = new LinkCore();
        $current_index =  $link->getAdminLink($this->module->getAdminClassName());
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->module->l('Import XML document', get_class($this)),
                    'icon' => 'icon-download',
                ),
                'input' => array(
                    array(
                        'type' => 'hidden',
                        'name' => $this->table_name.'_current_index',
                    ),
                    array(
                        'type' => 'hidden',
                        'name' => $this->table_name.'_token',
                    ),
                    array(
                        'type' => 'file',
                        'multiple' => false,
                        'label' => $this->module->l('Select xml file to import', get_class($this)),
                        'name' => 'input_file_import',
                        'accept' => '.xml'
                    )
                ),
                'submit' => array(
                    'title' => $this->module->l('Import', get_class($this)),
                    'confirm' => $this->module->l('Import selected file?', get_class($this)),
                    'icon' => 'process-icon-import',
                ),
                'buttons' => array(
                    'show_movements' => array(
                        'title' => $this->module->l('Show Movements', get_class($this)),
                        'icon' => 'process-icon-duplicate',
                        'href' => $current_index.'&show_movements',
                    ),
                    'config' => array(
                        'title' => $this->module->l('Configuration', get_class($this)),
                        'icon' => 'process-icon-cogs',
                        'href' => $this->link->getAdminLink('AdminModules')
                            .'&configure=mpstock'
                            .'&tab_module=administration'
                            .'&module_name=mpstock'
                    ),
                    'reset' => array(
                        'title' => $this->module->l('Align Stocks', get_class($this)),
                        'icon' => 'process-icon-reset',
                        'href' => 'javascript:alignStock();'
                    ),
                    'quick_load_movement' => array(
                        'title' => $this->module->l('Quick load', get_class($this)),
                        'icon' => 'process-icon-plus',
                        'href' => $this->link->getAdminLink('AdminMpStock')
                            .'&quick_movement=load'
                    ),
                    'quick_unload_movement' => array(
                        'title' => $this->module->l('Quick unload', get_class($this)),
                        'icon' => 'process-icon-minus',
                        'href' => $this->link->getAdminLink('AdminMpStock')
                            .'&quick_movement=unload'
                    ),

                ),
            )
        );
        return (array($fields_form));
    }

    public function getScript()
    {
        $smarty = Context::getContext()->smarty;
        return $smarty->fetch($this->module->getAdminTemplatePath().'helper_form_align_stock.tpl');
    }
}
