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

class MpStockAdminHelperFormDisplayReport extends HelperFormCore
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
    
    public function display($content)
    {
        $this->table = $this->table_name;
        $this->default_form_language = (int) ConfigurationCore::get('PS_LANG_DEFAULT');
        $this->allow_employee_form_lang = (int) ConfigurationCore::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG');
        $this->submit_action = 'submitFormGetCombinations';
        $this->currentIndex = $this->link->getAdminLink($this->module->getAdminClassName(), false);
        $this->token = Tools::getAdminTokenLite($this->module->getAdminClassName());
        $this->tpl_vars = array(
            'fields_value' => $this->getFieldsValue(),
            'languages' => $this->context->controller->getLanguages(),
        );
        $this->content = $content;
        return $this->generateForm($this->getFieldsForm());
    }
    
    protected function getFieldsValue()
    {
        return array(
            'input_textarea_report' => $this->content,
        );
    }
    
    protected function getFieldsForm()
    {
        $link = new LinkCore();
        $current_index =  $link->getAdminLink($this->module->getAdminClassName());
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->module->l('Add Movement', get_class($this)),
                    'icon' => 'icon-plus',
                ),
                'input' => array(
                    array(
                        'type' => 'textarea',
                        'label' => $this->l('Report'),
                        'autoload_rte' => true,
                        'lang' => true,
                        'rows' => 10,
                        'cols' => 100,
                        'name' => 'input_textarea_report',
                    ),
                ),
                'buttons' => array(
                    'save' => array(
                        'title' => $this->module->l('Save Report', get_class($this)),
                        'icon' => 'process-icon-save',
                        'href' => 'javascript:SaveReport();',
                    ),
                ),
            )
        );
        
        return (array($fields_form));
    }
}
