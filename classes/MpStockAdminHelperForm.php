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
        $this->submit_action = 'submitFormImport';
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
        );
    }
    
    protected function getFieldsForm()
    {
        $link = new LinkCore();
        $current_index =  $link->getAdminLink($this->module->getAdminClassName());
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Import XML document'),
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
                        'label' => $this->l('Select xml file to import'),
                        'name' => 'input_file_import',
                        'accept' => '.xml'
                    )
                ),
                'submit' => array(
                    'title' => $this->l('Import'),
                    'confirm' => $this->l('Import selected file?'),
                    'icon' => 'process-icon-upload',
                ),
                'buttons' => array(
                    'show_document' => array(
                        'title' => $this->l('Show Documents'),
                        'confirm' => $this->l('Import selected file?'),
                        'icon' => 'process-icon-compress',
                        'href' => $current_index.'&show_documents',
                    ),
                    'show_movements' => array(
                        'title' => $this->l('Show Movements'),
                        'confirm' => $this->l('Import selected file?'),
                        'icon' => 'process-icon-expand',
                        'href' => $current_index.'&show_movements',
                    ),
                ),
            )
        );
        
        return (array($fields_form));
    }
    
    /**
     * Non-static method which uses AdminController::translate()
     *
     * @param string  $string Term or expression in english
     * @param string|null $class Name of the class
     * @param bool $addslashes If set to true, the return value will pass through addslashes(). Otherwise, stripslashes().
     * @param bool $htmlentities If set to true(default), the return value will pass through htmlentities($string, ENT_QUOTES, 'utf-8')
     * @return string The translation if available, or the english default text.
     */
    protected function l($string, $class = null, $addslashes = false, $htmlentities = true)
    {
        if ($class === null || $class == 'AdminTab') {
            $class = substr(get_class($this), 0, -10);
        } elseif (strtolower(substr($class, -10)) == 'controller') {
            /* classname has changed, from AdminXXX to AdminXXXController, so we remove 10 characters and we keep same keys */
            $class = substr($class, 0, -10);
        }
        return Translate::getAdminTranslation($string, $class, $addslashes, $htmlentities);
    }
}