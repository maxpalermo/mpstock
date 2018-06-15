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

class MpStockProductExtraHelperForm extends HelperFormCore
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
                'thousands_sep' => '.'
            );
        } else {
            $this->localeInfo = array(
                'decimal_point' => '.',
                'thousands_sep' => ','
            );
        }
    }
    
    public function display()
    {
        $this->table = $this->table_name;
        $this->default_form_language = (int) ConfigurationCore::get('PS_LANG_DEFAULT');
        $this->allow_employee_form_lang = (int) ConfigurationCore::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG');
        $this->submit_action = 'submitFormFindMovements';
        $this->currentIndex = $this->link->getAdminLink('AdminProducts', false)
            .'&key_tab=ModuleMpstock'
            .'&id_product='.Tools::getValue('id_product', 0)
            .'&updateproduct';
        $this->token = Tools::getAdminTokenLite('AdminProducts');
        $this->tpl_vars = array(
            'fields_value' => $this->getFieldsValue(),
            'languages' => $this->context->controller->getLanguages(),
        );
        return $this->generateForm($this->getFieldsForm()).$this->getScript();
    }
    
    private function getScript()
    {
        $smarty = Context::getContext()->smarty;
        return $smarty->fetch($this->module->getPath().'views/templates/front/extra_form.tpl');
    }

    private function getFieldsValue()
    {
        return array(
            'input_switch_search_in_orders' => Tools::getValue('input_switch_search_in_orders', 1),
            'input_switch_search_in_slips' => Tools::getValue('input_switch_search_in_slips', 1),
            'input_switch_search_in_movements' => Tools::getValue('input_switch_search_in_movements', 1),
            'input_select_combination' => Tools::getValue('input_select_combination', 0),
            'input_date_start' => Tools::getValue('input_date_start', ''),
            'input_date_end' => Tools::getValue('input_date_end', ''),
            'key_tab' => 'ModuleMpstock',
        );
    }
    
    private function getFieldsForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->module->l('Search movements', get_class($this)),
                    'icon' => 'icon-database',
                ),
                'input' => array(
                    array(
                        'type' => 'hidden',
                        'name' => 'key_tab',
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->module->l('Search in orders', get_class($this)),
                        'desc' => $this->module->l('If set, search movements in orders'),
                        'name' => 'input_switch_search_in_orders',
                        'values' => array(
                            array(
                                'id' => 'search_orders_on',
                                'value' => 1,
                                'label' => $this->module->l('Yes', get_class($this)),
                            ),
                            array(
                                'id' => 'search_orders_off',
                                'value' => 0,
                                'label' => $this->module->l('No', get_class($this)),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->module->l('Search in deliveries', get_class($this)),
                        'desc' => $this->module->l('If set, search movements in delivery slips'),
                        'name' => 'input_switch_search_in_slips',
                        'values' => array(
                            array(
                                'id' => 'search_slips_on',
                                'value' => 1,
                                'label' => $this->module->l('Yes', get_class($this)),
                            ),
                            array(
                                'id' => 'search_slips_off',
                                'value' => 0,
                                'label' => $this->module->l('No', get_class($this)),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->module->l('Search in movements', get_class($this)),
                        'desc' => $this->module->l('If set, search movements in stock'),
                        'name' => 'input_switch_search_in_movements',
                        'values' => array(
                            array(
                                'id' => 'search_movements_on',
                                'value' => 1,
                                'label' => $this->module->l('Yes', get_class($this)),
                            ),
                            array(
                                'id' => 'search_movements_off',
                                'value' => 0,
                                'label' => $this->module->l('No', get_class($this)),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->module->l('Select combination', get_class($this)),
                        'desc' => $this->module->l('Get a combination from list above.'),
                        'name' => 'input_select_combination',
                        'class' => 'chosen',
                        'options' => array(
                            'query' => $this->getCombinations(),
                            'id' => 'id_product_attribute',
                            'name' => 'combination',
                        ),
                    ),
                    array(
                        'type' => 'date',
                        'label' => $this->module->l('Date start', get_class($this)),
                        'desc' => $this->module->l('Movements will be searched from this date'),
                        'name' => 'input_date_start',
                        'class' => 'datepicker',
                    ),
                    array(
                        'type' => 'date',
                        'label' => $this->module->l('Date end', get_class($this)),
                        'desc' => $this->module->l('Movements will be searched until this date'),
                        'name' => 'input_date_end',
                        'class' => 'datepicker',
                    ),
                ),
                'submit' => array(
                    'title' => $this->module->l('Find', get_class($this)),
                    'confirm' => $this->module->l('Import selected file?', get_class($this)),
                    'icon' => 'process-icon-flag',
                ),
            )
        );
        return (array($fields_form));
    }
    
    private function getCombinations()
    {
        $id_product = (int)Tools::getValue('id_product', 0);
        if (!$id_product) {
            return array();
        }
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('id_product_attribute')
            ->from('product_attribute')
            ->where('id_product='.(int)$id_product);
        $result = $db->executeS($sql);
        if (!$result) {
            return array();
        }
        $combinations = array(
            array(
                'id_product_attribute' => 0,
                'combination' => $this->module->l('All', get_class($this)),
            )
        );
        foreach ($result as $row) {
            $combinations[] = array(
                'id_product_attribute' => $row['id_product_attribute'],
                'combination' => $this->getProductCombinationName($row['id_product_attribute']),
            );
        }
        return $combinations;
    }
    
    private function getProductCombinationName($id_product_attribute)
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('a.id_attribute')
            ->select('a.color')
            ->select('ag.position')
            ->select('al.name')
            ->from('attribute', 'a')
            ->innerJoin('attribute_group', 'ag', 'ag.id_attribute_group=a.id_attribute_group')
            ->innerJoin('attribute_lang', 'al', 'al.id_attribute=a.id_attribute')
            ->innerJoin('product_attribute_combination', 'pac', 'pac.id_attribute=a.id_attribute')
            ->where('al.id_lang='.(int)$this->id_lang)
            ->where('pac.id_product_attribute='.(int)$id_product_attribute)
            ->orderBy('ag.position');
        
        $name = array();
        $rows = $db->executeS($sql);
        if (!$rows) {
            $this->adminController->addError($db->getMsgError());
            $name = array();
        } else {
            foreach ($rows as $row) {
                $name[] = Tools::strtolower($row['name']);
            }
        }
        return Tools::strtoupper(implode(' - ', $name));
    }
}
