<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    Massimiliano Palermo <maxx.palermo@gmail.com>
 * @copyright Since 2016 Massimiliano Palermo
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

class HelperFormAdminMpStockConfig extends HelperForm
{
    public function __construct($id = 0)
    {
        /**
         * Global Variables
         */
        $this->id = (int) $id;
        $this->deleted = false;
        $this->className = 'HelperFormAdminMpStockConfig';
        $this->context = Context::getContext();
        $this->id_lang = (int) $this->context->language->id;
        $this->id_shop = (int) $this->context->shop->id;
        $this->id_employee = (int) $this->context->employee->id;
        $this->link = $this->context->link;
        $this->smarty = $this->context->smarty;
        $this->token = Tools::getAdminTokenLite('AdminMpStock');
        $this->module = $this->context->controller->module;
        $this->image_url = false;
        /**
         * INIT TABLE
         */
        $this->shopLinkType = '';
        $this->table = ModelMpStockMvtReason::$definition['table'];
        $this->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');
        $this->allow_employee_form_lang = (int) Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG');
        $this->submit_action = 'submitMpStockConfig';
        $this->currentIndex = $this->context->link->getAdminLink('AdminMpStock');
        $this->token = Tools::getAdminTokenLite('AdminMpStock');
        $this->tpl_vars = array(
            'fields_value' => array(
                'id' => 0,
                'sign' => 0,
                'transform' => 1,
                'name' => '',
            ),
            'languages' => $this->context->controller->getLanguages(),
        );
        $this->identifier = ModelMpStockMvtReason::$definition['primary'];
        /** END HELPERLIST **/

        $this->bootstrap = true;
        parent::__construct();
    }

    public function delete()
    {
        $deleted = true;
        if ((int) $this->id) {
            $obj = new ModelMpStockMvtReason($this->id);
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
        if ((int) $this->id) {
            $obj = new MpEmbroideryPositionObjectModel($this->id);
            $this->tpl_vars['fields_value'] = array(
                'id' => (int) $obj->id,
                'sign' => (int) $obj->sign,
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
            . '&' . $this->identifier . '=' . (int) Tools::getValue($this->identifier);
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
            ->where('id_lang=' . (int) $this->id_lang)
            ->orderBy('name');

        $result = $db->executeS($sql);
        if ($result) {
            foreach ($result as &$row) {
                $row['logo'] = "<span class='badge' style='padding: 8px;'><img src='" . $row['logo'] . "'></span>";
            }
            return $result;
        } else {
            return array();
        }
    }
}