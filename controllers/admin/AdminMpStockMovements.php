<?php
use MpSoft\MpStock\Helpers\DisplayImageThumbnail;

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

class AdminMpStockMovementsController extends ModuleAdminController
{
    protected $id_lang;
    protected $id_shop;
    protected $id_employee;
    protected $employees = [];

    public function __construct()
    {
        $this->bootstrap = true;
        $this->className = 'ModelMpStockMovement';
        $this->token = Tools::getAdminTokenLite('AdminMpStockMovements');

        parent::__construct();

        $this->id_lang = (int) Context::getContext()->language->id;
        $this->id_shop = (int) Context::getContext()->shop->id;
        $this->id_employee = (int) Context::getContext()->employee->id;
    }

    public function response($params)
    {
        header('Content-Type: application/json');
        exit(Tools::jsonEncode($params));
    }

    public function initProcess()
    {
        $this->table = 'mpstock_movement';
        $this->identifier = 'id_mpstock_movement';
        $this->list_id = $this->table;
        $this->explicitSelect = false;
        $this->list_no_link = true;
        $this->bulk_actions = [];
        $this->_defaultOrderWay = 'DESC';
        $this->_defaultOrderBy = 'a.date_add';
        $this->addRowAction('edit');
        $this->addRowAction('delete');

        $this->_select = ' a.id_product as id_image, a.id_product_attribute as variant_id, pl.name as product_name';
        $this->_join = ' INNER JOIN ' . _DB_PREFIX_ . 'product p ON a.id_product = p.id_product';
        $this->_join .= ' INNER JOIN ' . _DB_PREFIX_ . 'product_lang pl ON a.id_product = pl.id_product AND pl.id_lang=' . (int) $this->id_lang;
        $this->_join .= ' INNER JOIN ' . _DB_PREFIX_ . 'product_attribute pa ON a.id_product_attribute = pa.id_product_attribute';

        $this->fields_list = [
            'id_image' => [
                'title' => $this->module->l('Immagine', $this->controller_name),
                'align' => 'text-center',
                'width' => 'auto',
                'class' => 'fixed-width-sm',
                'search' => false,
                'float' => true,
                'callback' => 'displayImage',
            ],
            'id_mpstock_movement' => [
                'title' => $this->module->l('Id', $this->controller_name),
                'align' => 'text-right',
                'width' => 'auto',
                'class' => 'fixed-width-sm',
                'filter_key' => 'a!id_mpstock_movement',
                'filter_type' => 'int',
            ],
            'id_mpstock_mvt_reason' => [
                'title' => $this->module->l('Motivo', $this->controller_name),
                'type' => 'select',
                'list' => ModelMpStockMvtReason::getReasons($this->id_lang),
                'align' => 'text-left',
                'width' => 'auto',
                'class' => '',
                'filter_key' => 'a!id_mpstock_mvt_reason',
                'filter_type' => 'int',
                'callback' => 'getReasonName',
            ],
            'mvt_reason' => [
                'title' => $this->module->l('Descrizione', $this->controller_name),
                'type' => 'text',
                'width' => 'auto',
                'filter_key' => 'a!mvt_reason',
            ],
            'document_number' => [
                'title' => $this->module->l('Num Doc.', $this->controller_name),
                'type' => 'text',
                'width' => 'auto',
                'filter_key' => 'a!document_number',
            ],
            'document_date' => [
                'title' => $this->module->l('Data Doc.', $this->controller_name),
                'type' => 'date',
                'width' => 'auto',
                'filter_key' => 'a!document_date',
            ],
            'id_order' => [
                'title' => $this->module->l('Ord.', $this->controller_name),
                'align' => 'text-right',
                'width' => 'auto',
                'class' => 'fixed-width-sm',
                'float' => 'true',
                'filter_key' => 'a!id_order',
                'filter_type' => 'int',
            ],
            'id_product' => [
                'title' => $this->module->l('Prod.', $this->controller_name),
                'align' => 'text-right',
                'width' => 'auto',
                'class' => 'fixed-width-sm',
                'float' => 'true',
                'filter_key' => 'a!id_product',
                'filter_type' => 'int',
            ],
            'id_product_attribute' => [
                'title' => $this->module->l('Var.', $this->controller_name),
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
                'filter_key' => 'a!reference',
            ],
            'ean13' => [
                'title' => $this->module->l('Barcode', $this->controller_name),
                'align' => 'text-left',
                'width' => 'auto',
                'class' => '',
                'filter_key' => 'a!ean13',
            ],
            'product_name' => [
                'title' => $this->module->l('Nome', $this->controller_name),
                'align' => 'text-left',
                'width' => 'auto',
                'class' => '',
                'filter_key' => 'pl!name',
                'callback' => 'getVariantName',
            ],
            'stock_quantity_before' => [
                'title' => $this->module->l('Mag.', $this->controller_name),
                'type' => 'text',
                'align' => 'text-right',
                'width' => 'auto',
                'class' => 'fixed-width-md',
                'filter_key' => 'a!stock_quantity_before',
                'filter_type' => 'int',
                'callback' => 'displayQuantity',
                'float' => 'true',
            ],
            'stock_movement' => [
                'title' => $this->module->l('Mov', $this->controller_name),
                'type' => 'text',
                'align' => 'text-right',
                'width' => 'auto',
                'class' => 'fixed-width-md',
                'filter_key' => 'a!stock_movement',
                'filter_type' => 'int',
                'callback' => 'displayQuantity',
                'float' => 'true',
            ],
            'stock_quantity_after' => [
                'title' => $this->module->l('Qta', $this->controller_name),
                'type' => 'text',
                'align' => 'text-right',
                'width' => 'auto',
                'class' => 'fixed-width-md',
                'filter_key' => 'a!stock_quantity_after',
                'filter_type' => 'int',
                'callback' => 'displayQuantity',
                'float' => 'true',
            ],
            'id_employee' => [
                'title' => $this->module->l('Operatore', $this->controller_name),
                'align' => 'text-left',
                'type' => 'select',
                'list' => ModelEmployee::getEmployees(true, true),
                'width' => 'auto',
                'class' => '',
                'filter_key' => 'a!id_employee',
                'filter_type' => 'int',
                'callback' => 'getEmployeeName',
            ],
            'date_add' => [
                'title' => $this->module->l('Data', $this->controller_name),
                'type' => 'datetime',
                'align' => 'text-center',
                'width' => 'auto',
                'class' => 'fixed-width-lg',
                'filter_key' => 'a!date_add',
                'filter_type' => 'date',
            ],
            'date_upd' => [
                'title' => $this->module->l('Aggiornato', $this->controller_name),
                'type' => 'datetime',
                'align' => 'text-center',
                'width' => 'auto',
                'class' => 'fixed-width-lg',
                'filter_key' => 'a!date_upd',
                'filter_type' => 'date',
            ],
        ];

        parent::initProcess();
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
        parent::setMedia();

        $this->addCSS($this->module->getLocalPath() . 'views/css/panel-auto-width.css');
        $this->addCSS($this->module->getLocalPath() . 'views/css/dynamicSelect.css');
        $this->addJS($this->module->getLocalPath() . 'views/js/dynamicSelect.js');
        $this->addJqueryUI('ui.autocomplete');
    }

    public function getReasonName($value)
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('name')
            ->from(ModelMpStockMvtReason::$definition['table'] . '_lang')
            ->where('id_mpstock_mvt_reason=' . (int) $value . ' AND id_lang=' . (int) $this->id_lang);
        $name = Tools::strtoupper($db->getValue($sql));

        return $name;
    }

    public function getEmployeeName($value)
    {
        $value = (int) $value;

        if (!isset($this->employees[$value])) {
            $employee = new Employee($value);
            $this->employees[$value] = Tools::strtoupper($employee->firstname . ' ' . $employee->lastname);
        }

        if (!isset($this->employees[$value])) {
            return '--' . $value . '--';
        }

        return $this->employees[$value];
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

    public function getVariantName($value, $row)
    {
        return $value . ' ' . GetVariantName::get($row['variant_id']);
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

    public function processAddMovement()
    {
        if (Tools::isSubmit('saveMovementAndStay')) {
            if (Tools::getValue('id_mpstock_movement')) {
                $this->display = 'edit';
            } else {
                $this->display = 'add';
            }
        }

        $id_mpstock_movement = (int) Tools::getValue('id_mpstock_movement');
        $id_mpstock_mvt_reason = (int) Tools::getValue('id_mpstock_mvt_reason');
        $id_product = (int) Tools::getValue('product_id');
        $id_product_attribute = (int) Tools::getValue('id_product_attribute');
        $stock_quantity = abs((int) Tools::getValue('product_qty'));
        $wholesale_price = (float) Tools::getValue('product_wholesale_price_ti');
        $price = (float) Tools::getValue('product_price_ti');
        $id_order = (int) Tools::getValue('id_order');
        $mvt_reason = Tools::getValue('mvt_reason');

        $cookie = Context::getContext()->cookie;
        $document = [
            'document_number' => $cookie->document_number,
            'document_date' => $cookie->document_date,
            'document_supplier_id' => $cookie->document_supplier_id,
        ];

        if (!$id_mpstock_mvt_reason) {
            $this->errors[] = $this->module->l('Selezionare un tipo di movimento.', $this->controller_name);
        }
        if (!$id_product) {
            $this->errors[] = $this->module->l('Selezionare un prodotto.', $this->controller_name);
        }
        if (!$id_product_attribute) {
            $this->errors[] = $this->module->l('Selezionare una variante.', $this->controller_name);
        }
        if (!$stock_quantity) {
            $this->errors[] = $this->module->l('Inserire una quantità valida.', $this->controller_name);
        }

        if ($this->errors) {
            return false;
        }

        $product = new Product($id_product, false, $this->id_lang);
        if (!Validate::isLoadedObject($product)) {
            $this->errors[] = $this->module->l('Prodotto non trovato.', $this->controller_name);

            return false;
        }
        $pa = new Combination($id_product_attribute, $this->id_lang);
        if (!Validate::isLoadedObject($pa)) {
            $this->errors[] = $this->module->l('Variante non trovata.', $this->controller_name);

            return false;
        }
        $reason = ModelMpStockMvtReason::getReason($id_mpstock_mvt_reason, $this->id_lang);
        if ($reason === false) {
            $this->errors[] = $this->module->l('Tipo di movimento non trovato.', $this->controller_name);

            return false;
        }

        $id_stock_available = (int) StockAvailable::getStockAvailableIdByProductId($id_product, $id_product_attribute);
        $stock = new StockAvailable($id_stock_available, $this->id_lang);
        if (!Validate::isLoadedObject($stock)) {
            $this->errors[] = $this->module->l('Stock non trovato.', $this->controller_name);

            return false;
        }

        $sign = $reason->sign ? -1 : 1;
        $tax_rate = $product->getTaxesRate();
        $tax_rate = $tax_rate / 100 + 1;
        $wholesale_price = number_format($wholesale_price / $tax_rate, 6);
        $price = number_format($price / $tax_rate, 6);

        if ($id_mpstock_movement) {
            $mov = new ModelMpStockMovement($id_mpstock_movement, $this->id_lang);
            if (!Validate::isLoadedObject($mov)) {
                // New Movement
                $stock_quantity_before = $stock->quantity;
                $ref_movement = null;
                $stock_quantity = $stock_quantity * $sign;
                $stock_quantity_after = $stock_quantity_before + $stock_quantity;
                $delta_stock = $stock_quantity;
                $date_add = date('Y-m-d H:i:s');
                $date_upd = null;
            } else {
                // Update old movement
                $stock_quantity_before = $mov->stock_quantity_before;
                $ref_movement = $mov->id;
                $stock_quantity = $stock_quantity * $sign;
                $stock_quantity_after = $stock_quantity_before + $stock_quantity;
                $delta_stock = $stock_quantity_after - $mov->stock_quantity_after;
                $date_add = $mov->date_add;
                $date_upd = date('Y-m-d H:i:s');
            }
        } else {
            $stock_quantity_before = $stock->quantity;
            $ref_movement = null;
            $stock_quantity = $stock_quantity * $sign;
            $stock_quantity = $stock_quantity * $sign;
            $stock_quantity_after = $stock_quantity_before + $stock_quantity;
            $delta_stock = $stock_quantity;
            $date_add = date('Y-m-d H:i:s');
            $date_upd = null;
        }

        $movement = new ModelMpStockMovement();
        $movement->id = $ref_movement;
        $movement->id_warehouse = 0;
        $movement->document_number = $document['document_number'];
        $movement->document_date = $document['document_date'];
        $movement->id_supplier = $document['document_supplier_id'];
        $movement->id_mpstock_mvt_reason = $id_mpstock_mvt_reason;
        $movement->id_product = $id_product;
        $movement->id_product_attribute = $id_product_attribute;
        $movement->reference = $pa->reference ? $pa->reference : $product->reference;
        $movement->ean13 = $pa->ean13;
        $movement->upc = $pa->upc;
        $movement->wholesale_price_te = $wholesale_price;
        $movement->price_te = $price;
        $movement->id_employee = $this->id_employee;
        $movement->id_order = $id_order;
        $movement->id_order_detail = 0;
        $movement->mvt_reason = $mvt_reason;
        $movement->stock_quantity_before = $stock_quantity_before;
        $movement->stock_movement = $stock_quantity;
        $movement->stock_quantity_after = $stock_quantity_after;
        $movement->date_add = $date_add;
        $movement->date_upd = $date_upd;

        try {
            $res = $movement->add(false, true);
            if (!$res) {
                $this->errors[] = sprintf(
                    $this->module->l('Errore durante il salvataggio del movimento: %s.', $this->controller_name),
                    Db::getInstance()->getMsgError()
                );

                return false;
            }
            StockAvailable::updateQuantity($id_product, $id_product_attribute, $delta_stock);
        } catch (\Throwable $th) {
            $this->errors[] = $th->getMessage();

            return false;
        }

        $this->confirmations[] = $this->module->l('Movimento aggiunto.', $this->controller_name);
    }

    public function displayImage($value)
    {
        return DisplayImageThumbnail::displayImage($value);
    }

    public function renderForm()
    {
        if ($id_movement = Tools::getValue('id_mpstock_movement', 0)) {
            $movement = new ModelMpStockMovement($id_movement, $this->id_lang);
            if (Validate::isLoadedObject($movement)) {
                $tax_rate = (new Product($movement->id_product))->getTaxesRate();
                $tax_rate = $tax_rate / 100 + 1;
                $movement->price_ti = number_format($movement->price_te * $tax_rate, 2);
                $movement->wholesale_price_ti = number_format($movement->wholesale_price_te * $tax_rate, 2);
                $movement->product_name = Product::getProductName($movement->id_product);
                $variants = $this->processSearchProductAttribute($movement->id_product);

                $cookie = Context::getContext()->cookie;
                $cookie->document_number = $movement->document_number;
                $cookie->document_date = date('Y-m-d', strtotime($movement->document_date));
                $cookie->document_supplier_id = $movement->id_supplier;
                $cookie->write();
            }
        } else {
            if (!Tools::isSubmit('saveMovementAndStay') && !Tools::getValue('action') == 'setDocument') {
                $cookie = Context::getContext()->cookie;
                $cookie->document_number = '';
                $cookie->document_date = '';
                $cookie->document_supplier_id = 0;
                $cookie->write();
            }
        }

        $cookie = Context::getContext()->cookie;
        $document = [
            'document_number' => $cookie->document_number,
            'document_date' => $cookie->document_date,
            'document_supplier_id' => $cookie->document_supplier_id,
        ];

        if ($document['document_number'] && $document['document_date'] && $document['document_supplier_id']) {
            $query = 'SELECT a.id_mpstock_movement, a.id_product, a.id_product_attribute, a.reference, a.ean13, a.stock_movement, '
            . "'' as product_name, '' as product_variant "
            . 'FROM ' . _DB_PREFIX_ . 'mpstock_movement a '
            . "WHERE a.document_number='" . pSQL($document['document_number']) . "' "
            . "AND a.document_date='" . pSQL($document['document_date']) . "' "
            . 'AND a.id_supplier=' . (int) $document['document_supplier_id'] . ' '
            . 'ORDER BY a.id_mpstock_movement ASC';
            $db = Db::getInstance();
            $document_movements = $db->executeS($query);
            if ($document_movements) {
                foreach ($document_movements as &$row) {
                    $row['product_name'] = Product::getProductName($row['id_product']);
                    $row['product_variant'] = GetVariantName::get($row['id_product_attribute']);
                }
            } else {
                $document_movements = [];
            }
        } else {
            $document_movements = [];
        }

        $tpl = $this->module->getLocalPath() . 'views/templates/admin/movements/add.tpl';
        $this->context->smarty->assign([
            'id_shop' => $this->id_shop,
            'id_employee' => $this->id_employee,
            'id_lang' => $this->id_lang,
            'id_mpstock_movement' => (int) Tools::getValue('id_mpstock_movement'),
            'id_mpstock_mvt_reason' => (int) Tools::getValue('id_mpstock_mvt_reason'),
            'number_document' => Tools::getValue('number_document'),
            'date_document' => Tools::getValue('date_document'),
            'id_supplier' => (int) Tools::getValue('id_supplier'),
            'date_add' => date('Y-m-d H:i:s'),
            'reasons' => ModelMpStockMvtReason::getReasons($this->id_lang, 'rows'),
            'employees' => ModelEmployee::getEmployees(true, true),
            'suppliers' => Supplier::getSuppliers(false, $this->id_lang),
            'ajax_controller' => $this->context->link->getAdminLink('AdminMpStockMovements'),
            'back_url' => $this->context->link->getAdminLink('AdminMpStockMovements'),
            'movement' => isset($movement) && $movement->id ? $movement : null,
            'document' => $document,
            'document_movements' => $document_movements,
            'variants' => isset($variants) ? $variants : [],
        ]);

        return $this->context->smarty->fetch($tpl);
    }

    protected function getProducts()
    {
        $products = Product::getProducts($this->id_lang, 0, 0, 'id_product', 'ASC', false, true);
        foreach ($products as &$product) {
            $product['thumb'] = $this->getProductImage($product['id_product']);
        }
    }

    protected function getProductImage($id_product)
    {
        $cover = Product::getCover($id_product);
        $image = new Image($cover['id_image']);
        $path = $image->getImgPath();
        $folder = Image::getImgFolderStatic($image->id);
        $imagePath = $this->context->shop->getBaseURL(true) . 'img/p/' . $path . '-small_default.jpg';

        return $imagePath;
    }

    public function ajaxProcessSearchProduct()
    {
        $cookie = Context::getContext()->cookie;
        $id_supplier = (int) $cookie->document_supplier_id;
        $sql_supplier = isset($id_supplier) ? 'AND p.id_supplier=' . (int) $id_supplier . ' ' : '';

        $term = Tools::getValue('q');
        $query = 'SELECT p.id_product, pl.name, p.reference '
            . 'FROM ' . _DB_PREFIX_ . 'product p'
            . ' INNER JOIN ' . _DB_PREFIX_ . 'product_lang pl ON p.id_product = pl.id_product AND pl.id_lang=' . (int) $this->id_lang . ' '
            . "WHERE name LIKE '%" . pSQL($term) . "%' "
            . $sql_supplier
            . "OR reference LIKE '%" . pSQL($term) . "%' "
            . 'ORDER BY name ASC'
            . ' LIMIT 25';
        $db = Db::getInstance();
        $result = $db->executeS($query);

        foreach ($result as $row) {
            if (isset($row['id_product'])) {
                $row['id'] = $row['id_product'];
            }
            $image = $this->getProductImage($row['id_product']);
            $temp_array = [];
            $temp_array['id'] = $row['id_product'];
            $temp_array['value'] = $row['id_product'];
            $temp_array['reference'] = $row['reference'];
            $temp_array['label'] = $this->displayOption($row['id'], $row['name'], $row['reference'], $image);
            $temp_array['name'] = $row['name'];
            $output[] = $temp_array;
        }
        if (!$result) {
            $output = [
                [
                    'id' => 0,
                    'value' => '',
                    'reference' => '',
                    'label' => '<h5>Nessun record trovato.</h5>',
                    'name' => '',
                ],
            ];
        }

        $this->response($output);
    }

    public function processSetDocument()
    {
        if (Tools::isSubmit('submitAddDocument')) {
            $cookie = Context::getContext()->cookie;
            $cookie->document_number = Tools::getValue('document_number', '');
            $cookie->document_date = Tools::getValue('document_date', '');
            $cookie->document_supplier_id = Tools::getValue('id_supplier');
            $cookie->write();
            $this->display = 'add';
            $this->confirmations[] = $this->module->l('Documento impostato.', $this->controller_name);
        }

        if (Tools::isSubmit('submitRemoveDocument')) {
            $cookie = Context::getContext()->cookie;
            $cookie->document_number = '';
            $cookie->document_date = '';
            $cookie->document_supplier_id = 0;
            $cookie->write();
            $this->display = 'add';
            $this->confirmations[] = $this->module->l('Documento rimosso.', $this->controller_name);
        }
    }

    public function processSearchProductAttribute($id_product)
    {
        $product = new Product($id_product, false, $this->id_lang);
        $variants = $product->getAttributeCombinations($this->id_lang);
        $output = [];
        $temp_array = [];

        foreach ($variants as $variant) {
            $image = $this->getProductImage($id_product);
            $id = $variant['id_product_attribute'];
            $temp_array[$id]['id'] = $id;
            $temp_array[$id]['value'] = $variant['id_product_attribute'];
            $temp_array[$id]['reference'] = $variant['reference'];
            $temp_array[$id]['name'] = $product->name;
            $temp_array[$id]['image_src'] = $image;
            $temp_array[$id]['group_name'][] = $variant['group_name'];
            $temp_array[$id]['attribute_name'][] = $variant['attribute_name'];
        }

        foreach ($temp_array as &$value) {
            $value['variant'] = implode(' - ', $value['attribute_name']);
        }

        return $temp_array;
    }

    public function ajaxProcessSearchProductAttribute()
    {
        $id_product = (int) Tools::getValue('id_product');

        $this->response($this->processSearchProductAttribute($id_product));
    }

    protected function displayOption($id, $name, $reference, $image)
    {
        $html = '<div class="bootstrap ui-autocomplete-row">';
        $html .= '<a id="ui-id-' . $id . '" class="ui-corner-all" tabindex="-1">';
        $html .= '<div class="row">';
        $html .= '  <div class="pull-left mr-2">';
        $html .= '      <img src="' . $image . '" class="img-thumbnail" style="width: 64px; object-fit: contain;">';
        $html .= '  </div>';
        $html .= '  <div class="pull-left">';
        $html .= '      <strong>' . $name . '</strong><br>';
        $html .= '      <small>' . $reference . '</small>';
        $html .= '  </div>';
        $html .= '</a>';
        $html .= '</div>';

        return $html;
    }
}
