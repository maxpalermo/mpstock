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

require_once _PS_MODULE_DIR_.'mpstock/classes/MpStockMvtReasonObjectModel.php';

class HelperFormAdminMpStockConfig extends HelperForm
{
    public function __construct($id = 0)
    {
        /**
         * Global Variables
         */
        $this->id = (int)$id;
        $this->deleted = false;
        $this->className = 'HelperFormAdminMpStockConfig';
        $this->context = Context::getContext();
        $this->id_lang = (int)$this->context->language->id;
        $this->id_shop = (int)$this->context->shop->id;
        $this->id_employee = (int)$this->context->employee->id;
        $this->link = $this->context->link;
        $this->smarty = $this->context->smarty;
        $this->token = Tools::getAdminTokenLite('AdminMpStock');
        $this->module = $this->context->controller->module;
        $this->image_url = false;
        /**
         * INIT TABLE
         */
        $this->shopLinkType='';
        $this->table = MpStockMvtReasonObjectModel::$definition['table'];
        $this->default_form_language = (int)Configuration::get('PS_LANG_DEFAULT');
        $this->allow_employee_form_lang = (int)Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG');
        $this->submit_action = 'submitMpStockConfig';
        $this->currentIndex = $this->context->link->getAdminLink('AdminMpStock');
        $this->token = $this->token;
        $this->tpl_vars = array(
            'fields_value' => array(
                'id' => 0,
                'sign' => 0,
                'transform' => 1,
                'name' => '',
            ),
            'languages' => $this->context->controller->getLanguages(),
        );
        $this->identifier = MpStockMvtReasonObjectModel::$definition['primary'];
        /** END HELPERLIST **/

        $this->bootstrap = true;
        parent::__construct();
    }

    public function delete()
    {
        $deleted = true;
        if ((int)$this->id) {
            $obj = new MpStockMvtReasonObjectModel($this->id);
            $result = $obj->delete();
            return $result;
        } else {
            return $this->module->l('No record to delete');
        }
    }

    public function display()
    {
        $this->processSubmit();
        return $this->generateForm(array($this->fields_list));
    }

    private function processSubmit()
    {
        if ((int)$this->id) {
            $obj = new MpEmbroideryPositionObjectModel($this->id);
            $this->tpl_vars['fields_value'] = array(
                'id' => (int)$obj->id,
                'sign' => (int)$obj->sign,
                'transform' => $obj->transform,
                'name' => $obj->name[$this->id_lang],
            );
        } else {
            $this->tpl_vars['fields_value'] = array(
                'id' => 0,
                'sign' => 0,
                'transform' => 0,
                'name' => '',
            );
        }
        $this->setFields(); 
    }

    private function setFields()
    {
        $currentIndex = $this->context->link->getAdminLink('AdminMpStock')
            .'&'.$this->identifier.'='.(int)Tools::getValue($this->identifier);
        $this->fields_list = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->module->l('Edit Values', 'HelperFormAdminMpStockConfig'),
                    'icon' => 'icon-pencil',
                ),
                'input' => array(
                    array(
                        'label' => $this->module->l('id', 'HelperFormAdminMpStockConfig'),
                        'name' => 'id',
                        'type' => 'text',
                        'orderby' => true,
                        'disabled' => true,
                    ),
                    array(
                        'label' => $this->module->l('Name', 'HelperFormAdminMpStockConfig'),
                        'name' => 'name',
                        'type' => 'text',
                        'required' => true,
                    ),
                    array(
                        'label' => $this->module->l('Is load movement?', 'HelperFormAdminMpStockConfig'),
                        'name' => 'sign',
                        'type' => 'switch',
                        'required' => true,
                        'values' => array(
                            array(
                                'id' => 'load_on',
                                'value' => 1,
                                'label' => $this->module->l('YES', 'HelperFormAdminMpStockConfig'),
                            ),
                            array(
                                'id' => 'load_off',
                                'value' => 0,
                                'label' => $this->module->l('NO', 'HelperFormAdminMpStockConfig'),
                            )
                        )
                    ),
                    array(
                        'label' => $this->module->l('Transform', 'HelperFormAdminMpStockConfig'),
                        'name' => 'transform',
                        'type' => 'switch',
                        'required' => true,
                        'values' => array(
                            array(
                                'id' => 'transform_on',
                                'value' => 1,
                                'label' => $this->module->l('YES', 'HelperFormAdminMpStockConfig'),
                            ),
                            array(
                                'id' => 'transform_off',
                                'value' => 0,
                                'label' => $this->module->l('NO', 'HelperFormAdminMpStockConfig'),
                            )
                        )
                    ),
                ),
                'buttons' => array(
                    'back' => array(
                        'title' => $this->module->l('Back', 'HelperFormAdminMpStockConfig'),
                        'icon' => 'process-icon-back',
                        'href' => $this->currentIndex,
                    ),
                ),
                'submit' => array(
                    'title' => $this->module->l('Save', 'HelperFormAdminMpStockConfig'),
                ),
            ),
        );
    }

    private function getList()
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();

        $sql->select('*')
            ->from('mp_embroidery_position')
            ->where('id_lang='.(int)$this->id_lang)
            ->orderBy('name');

        $result = $db->executeS($sql);
        if ($result) {
            foreach ($result as &$row) {
                $row['logo'] = "<span class='badge' style='padding: 8px;'><img src='".$row['logo']."'></span>";
            }
            return $result;
        } else {
            return array();
        }
    }
}
