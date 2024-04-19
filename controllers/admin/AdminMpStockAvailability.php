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
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'mpstock/models/autoload.php';

use MpSoft\MpStock\Helpers\GetVariantName;

class AdminMpStockAvailabilityController extends ModuleAdminController
{
    protected $id_lang;
    protected $id_shop;
    protected $id_employee;

    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'stock_available';
        $this->identifier = 'id_stock_available';
        $this->list_id = $this->identifier;
        $this->className = 'StockAvailable';
        $this->explicitSelect = true;
        $this->list_no_link = true;
        $this->bulk_actions = [];

        parent::__construct();

        $this->id_lang = (int) Context::getContext()->language->id;
        $this->id_shop = (int) Context::getContext()->shop->id;
        $this->id_employee = (int) Context::getContext()->employee->id;
    }

    public function init()
    {
        $this->fields_list = [
            'id_stock_available' => [
                'title' => $this->module->l('Id', $this->controller_name),
                'align' => 'text-right',
                'width' => 'auto',
                'class' => 'fixed-width-sm',
                'filter_key' => 'a!id_stock_available',
                'filter_type' => 'int',
            ],
            'id_product' => [
                'title' => $this->module->l('Id Prodotto', $this->controller_name),
                'align' => 'text-right',
                'width' => 'auto',
                'class' => 'fixed-width-sm',
                'float' => 'true',
                'filter_key' => 'a!id_product',
                'filter_type' => 'int',
            ],
            'id_product_attribute' => [
                'title' => $this->module->l('Id Variante', $this->controller_name),
                'align' => 'text-right',
                'width' => 'auto',
                'class' => 'fixed-width-sm',
                'float' => 'true',
                'filter_key' => 'a!id_product_attribute',
                'filter_type' => 'int',
            ],
            'reference' => [
                'title' => $this->module->l('Riferimento', $this->controller_name),
                'align' => 'text-left',
                'width' => 'auto',
                'class' => '',
                'filter_key' => 'p!reference',
            ],
            'barcode' => [
                'title' => $this->module->l('Barcode', $this->controller_name),
                'align' => 'text-left',
                'width' => 'auto',
                'class' => '',
                'filter_key' => 'pa!ean13',
            ],
            'product_name' => [
                'title' => $this->module->l('Prodotto', $this->controller_name),
                'align' => 'text-left',
                'width' => 'auto',
                'class' => '',
                'filter_key' => 'pl!name',
            ],
            'variant_name' => [
                'title' => $this->module->l('Variante', $this->controller_name),
                'align' => 'text-left',
                'width' => 'auto',
                'class' => '',
                'search' => false,
                'filter_key' => 'a!id_product_attribute',
                'filter_type' => 'int',
                'callback' => 'getVariantName',
            ],
            'quantity' => [
                'title' => $this->module->l('Quantità', $this->controller_name),
                'type' => 'text',
                'align' => 'text-right',
                'width' => 'auto',
                'class' => 'fixed-width-md',
                'filter_key' => 'a!quantity',
                'filter_type' => 'int',
                'callback' => 'displayQuantity',
                'float' => 'true',
            ],
            'out_of_stock' => [
                'title' => $this->module->l('Fuori magazzino', $this->controller_name),
                'type' => 'select',
                'list' => [
                    $this->module->l('--', $this->controller_name),
                    $this->module->l('Rifiuta', $this->controller_name),
                    $this->module->l('Accetta', $this->controller_name),
                    $this->module->l('Default', $this->controller_name),
                ],
                'align' => 'text-center',
                'width' => 'auto',
                'class' => 'fixed-width-md',
                'callback' => 'displayOutOfStock',
                'float' => 'true',
                'filter_key' => 'a!out_of_stock',
                'filter_type' => 'int',
            ],
            'default_on' => [
                'title' => $this->module->l('Default', $this->controller_name),
                'type' => 'select',
                'list' => [
                    $this->module->l('No', $this->controller_name),
                    $this->module->l('Si', $this->controller_name),
                ],
                'align' => 'text-center',
                'width' => 'auto',
                'class' => 'fixed-width-xs',
                'callback' => 'displayDefaultOn',
                'float' => 'true',
                'filter_key' => 'pa!default_on',
                'filter_type' => 'bool',
            ],
            'activated' => [
                'title' => $this->module->l('Attivo', $this->controller_name),
                'type' => 'bool',
                'align' => 'text-center',
                'width' => 'auto',
                'class' => 'fixed-width-sm',
                'callback' => 'dependsOnStock',
                'float' => 'true',
                'filter_key' => 'p!active',
                'filter_type' => 'bool',
            ],
        ];

        $this->_defaultOrderWay = 'ASC';
        $this->_defaultOrderBy = 'id_stock_available';

        $this->_select = 'p.active as `activated`';
        $this->_join = ' INNER JOIN ' . _DB_PREFIX_ . 'product p ON a.id_product = p.id_product';
        $this->_join .= ' INNER JOIN ' . _DB_PREFIX_ . 'product_lang pl ON a.id_product = pl.id_product AND pl.id_lang=' . (int) $this->id_lang;
        $this->_join .= ' INNER JOIN ' . _DB_PREFIX_ . 'product_attribute pa ON a.id_product_attribute = pa.id_product_attribute';

        parent::init();
    }

    public function initPageHeaderToolbar()
    {
        parent::initPageHeaderToolbar();

        $this->page_header_toolbar_btn['refresh'] = [
            'href' => $this->context->link->getAdminLink($this->controller_name, true) . '&action=sync',
            'desc' => $this->module->l('Allinea Magazzino', $this->controller_name),
            'icon' => 'process-icon-refresh',
        ];

        $this->page_header_toolbar_btn['edit'] = [
            'href' => $this->context->link->getAdminLink($this->controller_name, true) . '&action=default',
            'desc' => $this->module->l('Aggiorna combinazione di default', $this->controller_name),
            'icon' => 'process-icon-edit',
        ];
    }

    public function setMedia()
    {
        $this->addCSS($this->module->getLocalPath() . 'views/css/panel-auto-width.css');
        parent::setMedia();
    }

    public function getReasonName($value)
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('name')
            ->from(ModelMpStockMovementReason::$definition['table'] . '_lang')
            ->where('id_mpstock_mvt_reason=' . (int) $value . ' AND id_lang=' . (int) $this->id_lang);
        $name = Tools::strtoupper($db->getValue($sql));

        return $name;
    }

    public function displayQuantity($value)
    {
        if ($value < 0) {
            return '<span class="badge badge-pill badge-critical">' . $value . '</span>';
        } elseif ($value > 0) {
            return '<span class="badge badge-pill badge-success">' . $value . '</span>';
        } else {
            return '<span class="badge badge-pill badge-warning">' . $value . '</span>';
        }
    }

    public function displayDefaultOn($value)
    {
        if ($value) {
            return '<i class="icon-check text-success" style="margin: 12px;"></i>';
        }

        return '';
    }

    public function dependsOnStock($value)
    {
        if (!$value) {
            return '<i class="icon-times text-danger" style="margin: 12px;"></i>';
        }

        return '<i class="icon-check text-success" style="margin: 12px;"></i>';
    }

    public function displayOutOfStock($value)
    {
        switch ($value) {
            case 1: // Accetta
                return '<span class="badge badge-pill badge-danger">Rifiuta</span>';
            case 2: // Rifiuta
                return '<span class="badge badge-pill badge-success">Accetta</span>';
            case 3: // Default
                return '<span class="badge badge-pill badge-info">Default</span>';
        }
        return '<span class="badge badge-pill badge-warning"><i class="icon icon-question-circle"></i></span>';
    }

    public function getVariantName($value)
    {
        return GetVariantName::get($value);
    }

    public function processSync()
    {
        $products = [];

        $db = Db::getInstance();
        // Reset all product stock to 0
        $db->update(
            'stock_available',
            [
                'quantity' => 0,
            ],
            'id_product_attribute=0'
        );

        // Get all product stock by id_product_attribute
        $sql = new DbQuery();
        $sql->select('id_product')
            ->select('sum(quantity) as qty')
            ->from('stock_available')
            ->groupBy('id_product');
        $result = $db->executeS($sql);
        if ($result) {
            // Update stock by id_product
            foreach ($result as $row) {
                $res = $db->update(
                    'stock_available',
                    [
                        'quantity' => (int) $row['qty'],
                    ],
                    'id_product=' . (int) $row['id_product'] . ' and id_product_attribute=0'
                );

                if ($res) {
                    if (!isset($products[$row['id_product']])) {
                        $query = 'SELECT reference FROM ' . _DB_PREFIX_ . 'product WHERE id_product=' . (int) $row['id_product'];
                        $reference = $db->getValue($query);
                        $products[$row['id_product']] = $reference;
                    }

                    if ($row['qty'] > 0) {
                        $row['qty'] = '<span class="badge badge-pill badge-success">' . $row['qty'] . '</span>';
                    } elseif ($row['qty'] < 0) {
                        $row['qty'] = '<span class="badge badge-pill badge-danger">' . $row['qty'] . '</span>';
                    } else {
                        $row['qty'] = '<span class="badge badge-pill">' . $row['qty'] . '</span>';
                    }

                    $this->confirmations[] = sprintf(
                        $this->module->l('%sStock allineato per il prodotto (%s) %s: %s%s', $this->controller_name),
                        '<p>',
                        $row['id_product'],
                        '<strong>' . $products[$row['id_product']] . '</strong>',
                        $row['qty'],
                        '</p>'
                    );
                }
            }

            // Sync product_attribute with stock_available
            $res = $db->execute(
                'update ' . _DB_PREFIX_ . 'product_attribute pa, ' . _DB_PREFIX_ . 'stock_available sa ' .
                'set pa.quantity=sa.quantity where pa.id_product_attribute=sa.id_product_attribute ' .
                'and sa.id_shop=' . (int) Context::getContext()->shop->id
            );
            $tot_row = $db->numRows();
            $this->confirmations[] = sprintf(
                $this->module->l('%sAllineamento Tabella %s: %s righe modificate. %s', $this->controller_name),
                '<p>',
                '<strong>product_attribute</strong>',
                '<strong>' . $tot_row . '</strong>',
                '</p>'
            );
        } else {
            $this->warnings[] = $this->module->l('Nessun Prodotto da allineare.', $this->controller_name);
        }
    }

    public function processDefault()
    {
        if (!Combination::isFeatureActive()) {
            $this->errors[] = $this->module->l('La combinazione di prodotto non è attiva.', $this->controller_name);

            return false;
        }

        $table = _DB_PREFIX_ . 'product_attribute';

        $db = Db::getInstance();
        $db->execute("UPDATE {$table} SET default_on = null");

        /*
        UPDATE ps_product_attribute set default_on = null;

        UPDATE IGNORE ps_product_attribute a
        INNER JOIN (
            SELECT id_product, reference, max(quantity) as `quantity`
            FROM ps_product_attribute GROUP BY id_product ORDER BY id_product) b
        SET a.default_on = 1
        WHERE a.id_product=b.id_product AND a.quantity=b.quantity;
        */

        $subquery = "SELECT id_product, reference, max(quantity) as `quantity` FROM {$table} GROUP BY id_product ORDER BY id_product";
        $update = "UPDATE IGNORE {$table} a INNER JOIN ({$subquery}) b SET a.default_on = 1 WHERE a.id_product=b.id_product AND a.quantity=b.quantity";

        $res = $db->execute($update);
        if ($res) {
            $this->confirmations[] = sprintf(
                $this->module->l('Combinazioni di default aggiornate. Totale righe: %s', $this->controller_name),
                '<strong>' . $db->numRows() . '</strong>'
            );
        } else {
            $this->warnings[] = $this->module->l('Nessuna combinazione di default da aggiornare.', $this->controller_name);
        }
    }
}