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

Class MpStockProductExtraForm
{
    private $search_in_orders = true;
    private $search_in_slips = true;
    private $search_in_movements = true;
    private $date_start = '';
    private $date_end = '';
    private $context;
    private $id_lang;
    private $id_shop;
    private $id_product;
    private $id_employee;
    private $smarty;
    private $module;
    private $module_token;
    
    public function __construct($module, $params)
    {
        $this->context = Context::getContext();
        $this->smarty = $this->context->smarty;
        $this->id_lang = (int)$this->context->language->id;
        $this->id_shop = (int)$this->context->shop->id;
        
        $this->search_in_orders = (int)$params['search_in_orders'];
        $this->search_in_slips = (int)$params['search_in_slips'];
        $this->search_in_movements = (int)$params['search_in_movements'];
        $this->date_start = $params['date_start'];
        $this->date_end = $params['date_end'];
        $this->id_product = (int)$params['id_product'];
        $this->id_employee = (int)$params['id_employee'];
        $this->module_token = $params['module_token'];
        
        $this->module = $module;
    }
    
    public function display()
    {
        $this->smarty->assign(
            array(
                'search_in_orders' => $this->search_in_orders,
                'search_in_slips' => $this->search_in_slips,
                'search_in_movements' => $this->search_in_movements,
                'date_start' => $this->date_start,
                'date_end' => $this->date_end,
                'tot_badge' => 0,
                'header_form' => $this->module->getPath().'views/templates/admin/ProductExtraFormHeader.tpl',
                'content_form' => $this->module->getPath().'views/templates/admin/ProductExtraFormContent.tpl',
                'footer_form' => $this->module->getPath().'views/templates/admin/ProductExtraFormFooter.tpl',
                'module_link' => $this->module->getUrl().'ajax.php',
                'id_product' => $this->id_product,
                'id_employee' => $this->id_employee,
                'module_token' => $this->module_token,
            )
        );
        
        $form_path = $this->module->getPath().'/views/templates/admin/ProductExtraForm.tpl';
        $form = $this->smarty->fetch($form_path);
        
        return $form;
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