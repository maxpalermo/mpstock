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

Class MpStockHelperFormAddTypeMovement extends HelperFormCore
{
    public $context;
    public $values;
    public $id_lang;
    public $module;
    public $link;
    protected $cookie;
    protected $localeInfo;
    protected $table_name = 'mp_stock_type_movement';
    
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
        if (Tools::isSubmit('editMovement', 0)) {
            $movement = new MpStockObjectModelTypeMovement((int)Tools::getValue('editMovement', 0));
        } else {
            $movement = new MpStockObjectModelTypeMovement();
        }
        $this->table = $this->table_name;
        $this->default_form_language = (int) ConfigurationCore::get('PS_LANG_DEFAULT');
        $this->allow_employee_form_lang = (int) ConfigurationCore::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG');
        $this->submit_action = 'submitSaveMovement';
        $this->currentIndex = $this->link->getAdminLink('AdminModules', false)
            .'&configure=mpstock'
            .'&tab_module=administration'
            .'&module_name=mpstock';
        $this->token = Tools::getAdminTokenLite('AdminModules');
        $this->tpl_vars = array(
            'fields_value' => $movement->getArrayValues(),
            'languages' => $this->context->controller->getLanguages(),
        );
        return $this->generateForm($this->getFieldsForm()).$this->addScript();
    }
    
    private function getFieldsForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Movement type'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'required' => false,
                        'type' => 'text',
                        'name' => 'input_id_mp_stock_type_movement',
                        'label' => $this->l('Id'),
                        'desc' => $this->l('Id movement'),
                        'prefix' => '<i class="icon-chevron-right"></i>',
                        'suffix' => '<i class="icon-gear"></i>',
                        'class' => 'input fixed-width-sm text-right',
                    ),
                    array(
                        'required' => true,
                        'type' => 'text',
                        'name' => 'input_name',
                        'label' => $this->l('Name'),
                        'desc' => $this->l('Insert the name of the stock movement.'),
                        'prefix' => '<i class="icon-chevron-right"></i>',
                        'suffix' => '<i class="icon-list-ul"></i>',
                        'class' => 'input fixed-width-xxl',
                    ),
                    array(
                        'required' => true,
                        'type' => 'switch',
                        'name' => 'input_sign',
                        'label' => $this->l('Sign'),
                        'desc' => $this->l('Select the sign of the stock movement.'),
                        'values' => array(
                            array(
                                'id' => 'input_sign_on',
                                'value' => '1',
                                'label' => $this->l('+'),
                            ),
                            array(
                                'id' => 'input_sign_off',
                                'value' => '-1',
                                'label' => $this->l('-'),
                            ),
                        ),
                    ),
                    array(
                        'required' => true,
                        'type' => 'switch',
                        'name' => 'input_exchange',
                        'label' => $this->l('Exchange'),
                        'desc' => $this->l('If set stock will be charged with another product'),
                        'values' => array(
                            array(
                                'id' => 'id_switch_exchange_on',
                                'value' => '1',
                                'label' => $this->l('YES'),
                            ),
                            array(
                                'id' => 'id_switch_exchange_off',
                                'value' => '0',
                                'label' => $this->l('NO'),
                            ),
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->module->l('Save'),
                    'icon' => 'process-icon-save'
                ),
                'buttons' => array(
                    array(
                        'title' => $this->module->l('Back to movements', get_class($this)),
                        'icon' => 'process-icon-back',
                        'href' => $this->module->link->getAdminlink('AdminModules')
                            .'&configure=mpstock'
                            .'&module_name=mpstock'
                            .'&tab_module=administration',
                    )
                ),
            ),
        );  
        return (array($fields_form));
    }

    private function addScript()
    {
        $smarty = Context::getContext()->smarty;
        return $smarty->fetch($this->module->getAdminTemplatePath().'helper_form_type_movement.tpl');
    }
}
