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

Class MpStockAdminHelperFormMovement extends HelperFormCore
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
        if (Context::getContext()->language->iso_code == 'it') {
            $this->localeInfo = array(
                'decimal_point' => ',',
                'thousands_sep' => '.',
                'currency_char' => Context::getContext()->currency->sign,
            );
        } else {
            $this->localeInfo = array(
                'decimal_point' => '.',
                'thousands_sep' => ',',
                'currency_char' => Context::getContext()->currency->sign,
            );
        }
    }
    
    public function display()
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
        return $this->generateForm($this->getFieldsForm());
    }
    
    protected function getFieldsValue()
    {
        return array(
            $this->table_name.'_current_index' => '',
            $this->table_name.'_token' => Tools::getAdminTokenLite($this->module->getAdminClassName()),
            $this->table_name.'_decimal_point' => $this->localeInfo['decimal_point'],
            $this->table_name.'_thousands_sep' => $this->localeInfo['thousands_sep'],
            $this->table_name.'_currency_char' => $this->localeInfo['currency_char'],
            'input_text_product' => '',
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
                        'type' => 'hidden',
                        'name' => $this->table_name.'_current_index',
                    ),
                    array(
                        'type' => 'hidden',
                        'name' => $this->table_name.'_token',
                    ),
                    array(
                        'type' => 'hidden',
                        'name' => $this->table_name.'_decimal_point',
                    ),
                    array(
                        'type' => 'hidden',
                        'name' => $this->table_name.'_thousands_sep',
                    ),
                    array(
                        'type' => 'hidden',
                        'name' => $this->table_name.'_currency_char',
                    ),
                    array(
                        'type' => 'text',
                        'autocomplete' => true,
                        'label' => $this->module->l('Product', get_class($this)),
                        'desc' => $this->module->l(
                            'Please, insert at least first three letters of reference or product name.'
                        ),
                        'name' => 'input_text_product',
                        'class' => 'autocomplete',
                    )
                ),
                'submit' => array(
                    'title' => $this->module->l('Combinations', get_class($this)),
                    'icon' => 'process-icon-duplicate',
                ),
                'buttons' => array(
                    'back' => array(
                        'title' => $this->module->l('Back', get_class($this)),
                        'icon' => 'process-icon-back',
                        'href' => $current_index,
                    ),
                ),
            )
        );
        
        return (array($fields_form));
    }
}
