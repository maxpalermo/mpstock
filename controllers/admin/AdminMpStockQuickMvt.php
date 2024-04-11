<?php

use MpSoft\MpStock\Helpers\ProductHelper;

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
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'mpstock/models/autoload.php';

class AdminMpStockQuickMvtController extends ModuleAdminController
{
    protected $id_lang;
    protected $id_shop;
    protected $id_employee;

    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'mpstock_movement';
        $this->identifier = 'id_mpstock_movement';
        $this->list_id = $this->identifier;
        $this->className = 'ModelMpStockProduct';
        $this->explicitSelect = true;
        $this->lang = true;

        parent::__construct();

        $this->id_lang = (int) Context::getContext()->language->id;
        $this->id_shop = (int) Context::getContext()->shop->id;
        $this->id_employee = (int) Context::getContext()->employee->id;
    }

    public function init()
    {
        $this->fields_list = [];
        $this->fields_form = [];

        parent::init();
    }

    public function initContent()
    {
        $tpl = $this->module->getLocalPath() . 'views/templates/admin/quickMvt/form.tpl';
        $ajax_controller = $this->context->link->getAdminLink($this->controller_name);
        $this->context->smarty->assign([
            'ajax_controller' => $ajax_controller,
            'id_employee' => $this->id_employee,
            'id_lang' => $this->id_lang,
            'id_shop' => $this->id_shop,
        ]);
        $this->content = $this->context->smarty->fetch($tpl);

        parent::initContent();
    }

    public function setMedia()
    {
        $this->addCSS($this->module->getLocalPath() . 'views/css/panel-auto-width.css');
        parent::setMedia();
    }

    protected function response($params)
    {
        header('Content-Type: application/json; charset=utf-8');
        exit(json_encode($params));
    }

    public function ajaxProcessGetProductByEan13()
    {
        $ean13 = trim(Tools::getValue('ean13', ''));
        if (!$ean13 || strlen($ean13) != 13 || !Validate::isEan13($ean13)) {
            $this->response(
                [
                    'id_product' => 0,
                    'id_product_attribute' => 0,
                    'name' => $this->module->l('Codice EAN13 non valido'),
                    'quantity' => 0,
                    'class' => 'text-danger',
                ]
            );
        }
        $db = Db::getInstance();
        $sql = 'select * from ' . _DB_PREFIX_ . "product_attribute where ean13='" . pSQL($ean13) . "'";
        $row = $db->getRow($sql);
        if ($row) {
            $this->response(
                [
                    'id_product' => $row['id_product'],
                    'id_product_attribute' => $row['id_product_attribute'],
                    'name' => $this->getProductName($row['id_product']) . ' ' . $this->getVariantName($row['id_product_attribute']),
                    'quantity' => 1,
                    'class' => 'text-success text-bold',
                    'image' => ProductHelper::getInstance()->getImage($row['id_product']),
                ]
            );
        } else {
            $this->response(
                [
                    'id_product' => 0,
                    'id_product_attribute' => 0,
                    'name' => $this->module->l('Codice EAN13 non trovato'),
                    'quantity' => 0,
                    'class' => 'text-danger',
                    'image' => ProductHelper::getInstance()->getImage(false),
                ]
            );
        }
    }

    public function ajaxProcessAddQuickMovement()
    {
        $id_product = (int) Tools::getValue('id_product', 0);
        $id_product_attribute = (int) Tools::getValue('id_product_attribute', 0);
        $quantity = (int) Tools::getValue('quantity', 0);
        $sign = (int) Tools::getValue('sign', 0);

        $filename = sprintf(
            $this->module->l('Movimento veloce del %s', $this->controller_name),
            date('Y-m-d H:i:s')
        );
        $delta_quantity = abs($quantity) * $sign;

        $movement = new MpSoft\MpStock\Helpers\StockMovement($id_product, $id_product_attribute, 0, $filename);
        $record = $movement->generateMovement(
            \MpSoft\MpStock\Config\MvtReasonConfig::getIdEan13Movement(),
            $delta_quantity
        );

        if ($record === false) {
            $this->response(['success' => false, 'message' => implode('<br>', $movement->getErrors())]);
        }

        try {
            $res = $record->add();
            if ($res) {
                $movement->updateStock($id_product, $id_product_attribute, $delta_quantity);
            } else {
                $this->response(['success' => false, 'message' => $this->module->l('Errore durante l\'inserimento del movimento veloce', $this->controller_name)]);
            }
            $this->response(['success' => true]);
        } catch (Exception $e) {
            $this->response(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function getProductName($value)
    {
        return ProductHelper::getInstance()->getProductName($value);
    }

    public function getVariantName($value)
    {
        return ProductHelper::getInstance()->getVariantName($value);
    }
}
