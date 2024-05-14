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

use MpSoft\MpStock\Helpers\DisplayImageThumbnail;
use MpSoft\MpStock\Helpers\GetVariantName;
use MpSoft\MpStock\Helpers\ProductHelper;

class AdminMpStockConfigurationController extends ModuleAdminController
{
    protected $id_lang;
    protected $id_shop;
    protected $id_employee;

    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'configuration';
        $this->identifier = 'id_configuration';
        $this->list_id = $this->identifier;
        $this->className = 'Configuration';
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
        $tpl = $this->module->getLocalPath() . 'views/templates/admin/configuration/index.tpl';
        $ajax_controller = $this->context->link->getAdminLink($this->controller_name);
        $this->context->smarty->assign([
            'ajax_controller' => $ajax_controller,
            'module_enabled' => Module::isEnabled($this->module->name),
            'id_employee' => $this->id_employee,
            'id_lang' => $this->id_lang,
            'id_shop' => $this->id_shop,
            'image_404' => DisplayImageThumbnail::displayImage404('5x'),
            'order_states' => OrderState::getOrderStates($this->id_lang),
            'selected_order_states' => json_decode(Configuration::get('MPSTOCK_ORDER_STATES'), true),
            'movement_reasons' => ModelMpStockMvtReason::getMovementReasons($this->id_lang, false, 1),
            'order_detail_mvt_id' => (int) Configuration::get('MPSTOCK_ORDER_DETAIL_MOVEMENT_REASON_ID'),
        ]);
        $this->content = $this->context->smarty->fetch($tpl);

        parent::initContent();
    }

    public function setMedia()
    {
        $this->addCSS($this->module->getLocalPath() . 'views/css/panel-auto-width.css');
        parent::setMedia();
    }

    public function postProcess()
    {
        if (Tools::getValue('function')) {
            $func = 'func' . ucfirst(Tools::getValue('function'));
            if (method_exists($this, $func)) {
                exit($this->$func());
            }
        }

        return parent::postProcess();
    }

    public function processSaveConfig()
    {
        $states = Tools::getValue('order_states');
        $states = json_encode($states);
        Configuration::updateValue('MPSTOCK_ORDER_STATES', $states);

        $mvt_id = (int) Tools::getValue('order_detail_mvt_id');
        Configuration::updateValue('MPSTOCK_ORDER_DETAIL_MOVEMENT_REASON_ID', $mvt_id);

        $enable = (int) Tools::getValue('enable');
        if (!$enable) {
            if (Module::isEnabled($this->module->name)) {
                Module::disableByName($this->module->name);
            }
        } else {
            if (!Module::isEnabled($this->module->name)) {
                Module::enableByName($this->module->name);
            }
        }

        $this->confirmations[] = $this->l('Configurazione salvata correttamente.');
    }

    protected function response($params)
    {
        header('Content-Type: application/json; charset=utf-8');
        exit(json_encode($params));
    }

    public function getProductName($value)
    {
        return ProductHelper::getInstance()->getProductName($value);
    }

    public function getVariantName($value)
    {
        return GetVariantName::get($value);
    }

    public function ajaxProcessEditTables()
    {
        $step = (int) Tools::getValue('step');
        $action = 'processStep_' . $step;

        $response = $this->$action();
        $response = array_merge(
            [
                'step' => $step,
            ],
            $response
        );
        $this->response($response);
    }

    public function ajaxProcessInsertOrdersIntoMovements()
    {
        $this->response($this->insertOrderMovements());
    }

    protected function processStep_0()
    {
        /**
         * ELIMINO LE TABELLE NON UTILIZZATE
         */

        /**
         * @var array
         */
        $delete = [
            '',
            'import',
            'list_movements',
            'product_alignment',
            'type_movement',
        ];

        $delete = array_map(function ($item) {
            if (!$item) {
                return _DB_PREFIX_ . 'mp_stock';
            }

            return _DB_PREFIX_ . 'mp_stock_' . $item;
        }, $delete);

        $messages = [];
        foreach ($delete as $table) {
            $exists = AA_MpStockModelTemplate::existsTable($table);
            if ($exists) {
                $res = Db::getInstance()->execute('DROP TABLE ' . $table);
                if (!$res) {
                    $messages[] = 'Tabella ' . $table . ' not eliminata: ' . Db::getInstance()->getMsgError();
                }
            }
        }

        if (empty($messages)) {
            $messages[] = 'Tutte le tabelle sono state già eliminate.';
        }

        /**
         * EFFETTUO UN BACKUP DELLE TABELLE
         */
        $tables = [
            'mpstock_document',
            'mpstock_mvt_reason',
            'mpstock_mvt_reason_lang',
            'mpstock_product',
            'mpstock_movement',
        ];
        foreach ($tables as $table) {
            $exists = AA_MpStockModelTemplate::existsTable($table);
            if ($exists) {
                $res = AA_MpStockModelTemplate::clone($table, $table . '_bak_' . date('Ymd_His'));
                if (!$res) {
                    $messages[] = 'Backup della tabella ' . $table . ' fallito: ' . Db::getInstance()->getMsgError();
                }
            }
        }

        $response = [
            'status' => 'success',
            'message' => "Step 0 completo.\n" . implode("\n", $messages),
        ];

        return $response;
    }

    protected function processStep_1()
    {
        /**
         * MODIFICO LA TABELLA mpstock_mvt_reason
         */
        $source = 'mpstock_mvt_reason';
        $exists = AA_MpStockModelTemplate::existsTable($source);

        if (!$exists) {
            $res = ModelMpStockMvtReason::createTable();
            $response = [
                'status' => 'fail',
                'message' => $res ? 'Step 1 completato' : 'Step 1 fallito: ' . Db::getInstance()->getMsgError(),
            ];

            return $response;
        }

        $column = AA_MpStockModelTemplate::existsColumn($source, 'deleted');
        if ($column) {
            $res = AA_MpStockModelTemplate::addColumn($source, 'active', 'TINYINT(1)', false);
            if ($res) {
                $pfx = _DB_PREFIX_ . $source;
                $query = "UPDATE {$pfx} SET active = 1 WHERE deleted = 0";
                $res = Db::getInstance()->execute($query);
                if ($res) {
                    $delete = [
                        'deleted',
                        'transform',
                        'name',
                    ];
                    $res = AA_MpStockModelTemplate::dropColumns($source, $delete);

                    if ($res) {
                        $response = [
                            'status' => 'success',
                            'message' => 'Step 1 completato: Colonne ' . implode(',', $delete) . ' eliminate.',
                        ];

                        return $response;
                    }

                    $response = [
                        'status' => 'fail',
                        'message' => 'Step 1 fallito: ' . Db::getInstance()->getMsgError(),
                    ];

                    return $response;
                }
            }
        }

        $response = [
            'status' => 'fail',
            'message' => 'Step 1 completato: Nessuna operazione da effettuare.',
        ];

        return $response;
    }

    protected function processStep_2()
    {
        /**
         * MODIFICO LA TABELLA mpstock_product
         *  - Aggiungo le colonne
         *      - id_order
         *      - id_order_detail
         *      - document
         *      - mvt_reason
         *      - stock_quantity_before
         *      - stock_quantity_movement
         *      - stock_quantity_after
         *
         *  - Cambio physical_quantity in stock_quantity_before
         *  - Cambio usable_quantity_te in stock_movement
         *  - Rinomino la tabella in mpstock_movement
         *  - Rinomino id_mpstock_product in id_mpstock_movement
         *  - Inserisco le righe degli ordini nella tabella mpstock_movement
         */
        $source = 'mpstock_product';
        $exists = AA_MpStockModelTemplate::existsTable($source);

        if (!$exists) {
            ModelMpStockMovement::createTable();
            $response = [
                'status' => 'success',
                'message' => 'Step 2: La tabella ' . $source . ' non esiste. È stata creata.',
            ];

            return $response;
        }

        AA_MpStockModelTemplate::clone($source, 'mpstock_product_bak_' . date('Ymd_His'));

        $newColumns = [
            'document' => 'TEXT',
            'id_order' => 'INT(11)',
            'id_order_detail' => 'INT(11)',
            'mvt_reason' => 'VARCHAR(255)',
            'stock_quantity_before' => 'INT(11)',
            'stock_movement' => 'INT(11)',
            'stock_quantity_after' => 'INT(11)',
        ];
        foreach ($newColumns as $column => $type) {
            $exists = AA_MpStockModelTemplate::existsColumn($source, $column);
            if (!$exists) {
                $res = AA_MpStockModelTemplate::addColumn($source, $column, $type, true);
                if (!$res) {
                    $response = [
                        'status' => 'fail',
                        'message' => 'Step 2 fallito: ' . Db::getInstance()->getMsgError(),
                    ];

                    return $response;
                }
            }
        }

        $newIndexes = [
            'id_document',
            'id_mpstock_mvt_reason',
            'reference',
            'ean13',
            'id_order',
            'id_order_detail',
            'id_product',
            'id_product_attribute',
            'id_employee',
        ];
        foreach ($newIndexes as $index) {
            $exists = AA_MpStockModelTemplate::existsIndexByColumn($source, $index);
            if (!$exists) {
                $res = AA_MpStockModelTemplate::addIndex($source, $index);
                if (!$res) {
                    $response = [
                        'status' => 'fail',
                        'message' => 'Step 2 fallito: ' . Db::getInstance()->getMsgError(),
                    ];

                    return $response;
                }
            }
        }

        $pfx = _DB_PREFIX_ . $source;
        $update = [
            "UPDATE {$pfx} SET stock_quantity_before = physical_quantity",
            "UPDATE {$pfx} SET stock_movement = usable_quantity",
            "UPDATE {$pfx} SET stock_quantity_after = physical_quantity + usable_quantity",
        ];

        foreach ($update as $query) {
            $res = Db::getInstance()->execute($query);
            if (!$res) {
                $response = [
                    'status' => 'fail',
                    'message' => 'Step 2 fallito: ' . Db::getInstance()->getMsgError(),
                ];

                return $response;
            }
        }

        $delete = [
            'physical_quantity',
            'usable_quantity',
        ];
        $res = AA_MpStockModelTemplate::dropColumns($source, $delete);

        if ($res) {
            $res = AA_MpStockModelTemplate::clone($source, 'mpstock_movement');
            AA_MpStockModelTemplate::renameColumn('mpstock_movement', 'id_mpstock_product', 'id_mpstock_movement');
            AA_MpStockModelTemplate::drop($source);

            if (!$res) {
                $response = [
                    'status' => 'fail',
                    'message' => 'Step 2 fallito: ' . Db::getInstance()->getMsgError(),
                ];

                return $response;
            }

            $this->insertOrderMovements();

            $response = [
                'status' => 'success',
                'message' => 'Step 2 completato: Tabella ' . $source . ' modificata.',
            ];

            return $response;
        }

        $response = [
            'status' => 'fail',
            'message' => 'Step 2 completato: Nessuna operazione da effettuare.',
        ];

        return $response;
    }

    protected function insertOrderMovements()
    {
        $tbl_od = _DB_PREFIX_ . 'order_detail';
        $tbl_o = _DB_PREFIX_ . 'orders';
        $id_movement = (int) Configuration::get('MPSTOCK_ORDER_DETAIL_MOVEMENT_REASON_ID');
        $mvt = new ModelMpStockMvtReason($id_movement, $this->id_lang);
        $mvt_reason = $mvt->name;

        $query = "SELECT od.*, o.date_add, o.date_upd FROM {$tbl_od} od "
            . "INNER JOIN {$tbl_o} o ON o.id_order = od.id_order "
            . 'ORDER BY od.id_order_detail';
        $rows = Db::getInstance()->executeS($query);
        if ($rows) {
            $header = [
                'id_warehouse',
                'id_document',
                'id_order',
                'id_order_detail',
                'id_mpstock_mvt_reason',
                'mvt_reason',
                'id_product',
                'id_product_attribute',
                'reference',
                'ean13',
                'upc',
                'stock_quantity_before',
                'stock_movement',
                'stock_quantity_after',
                'price_te',
                'wholesale_price_te',
                'id_employee',
                'date_add',
                'date_upd',
            ];
            $header = array_map(function ($item) {
                return '`' . $item . '`';
            }, $header);

            $values = [];
            foreach ($rows as $row) {
                $data = [
                    'id_warehouse' => 0,
                    'id_document' => 0,
                    'id_order' => $row['id_order'],
                    'id_order_detail' => $row['id_order_detail'],
                    'id_mpstock_mvt_reason' => $id_movement,
                    'mvt_reason' => $mvt_reason,
                    'id_product' => $row['product_id'],
                    'id_product_attribute' => $row['product_attribute_id'],
                    'reference' => $row['product_reference'],
                    'ean13' => $row['product_ean13'],
                    'upc' => $row['product_upc'],
                    'stock_quantity_before' => $row['product_quantity_in_stock'],
                    'stock_movement' => -$row['product_quantity'],
                    'stock_quantity_after' => $row['product_quantity_in_stock'] - $row['product_quantity'],
                    'price_te' => $row['product_price'],
                    'wholesale_price_te' => 0,
                    'id_employee' => 0,
                    'date_add' => $row['date_add'],
                    'date_upd' => $row['date_upd'],
                ];
                $value = array_values($data);
                $values[] = '(' . implode(',', array_map(function ($item) {
                    return "'" . Db::getInstance()->escape($item, false, true) . "'";
                }, $value)) . ')';
            }

            $pfx = _DB_PREFIX_ . 'mpstock_movement';
            $fields = implode(',', $header);

            do {
                $chunk = array_splice($values, 0, 25000);
                $fields_values = implode(',', $chunk);
                $QUERY = "INSERT IGNORE INTO {$pfx} ({$fields}) VALUES {$fields_values};";

                try {
                    $res = Db::getInstance()->execute($QUERY);
                    if (!$res) {
                        $this->errors[] = Db::getInstance()->getMsgError();
                    }
                } catch (\Throwable $th) {
                    $this->errors[] = $th->getMessage();
                    $res = false;
                }
            } while ($values);

            if ($res) {
                return $this->updateOrderDescription();
            }

            return $res;
        }
    }

    protected function updateOrderDescription()
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sub = new DbQuery();

        $sub->select('id_document')
            ->from('mpstock_movement')
            ->where('id_document > 0');
        $sub = '(' . $sub->build() . ')';

        $sql->select('a.id_mpstock_document, a.number_document, a.date_document, b.name')
            ->from('mpstock_document', 'a')
            ->leftJoin('supplier', 'b', 'a.id_supplier = b.id_supplier AND a.id_supplier > 0')
            ->where('a.id_mpstock_document in ' . $sub);
        $sql = $sql->build();
        $rows = $db->executeS($sql);
        if (!$rows) {
            return true;
        }

        $queries = [];
        foreach ($rows as $row) {
            $id_document = (int) $row['id_mpstock_document'];
            $document = strtoupper($row['number_document']) . ' del ' . date('d/m/Y', strtotime($row['date_document']));
            if ($document == '0 del 01/01/1970') {
                continue;
            }
            $document .= ' - ' . strtoupper($row['name']);
            $queries[] = 'UPDATE ' . _DB_PREFIX_ . "mpstock_movement SET document = '{$document}' WHERE id_document = {$id_document};";
        }
        if ($queries) {
            try {
                $db->execute(implode("\n", $queries));
            } catch (\Throwable $th) {
                $this->errors[] = $th->getMessage();

                return false;
            }
        }

        return true;
    }

    protected function processStep_3()
    {
        /**
         * Inserisco nella tabella mpstock_movement tutti i movimenti degli ordini effettuati
         */
        $page = (int) Tools::getValue('page', 0);
        $pagination = 1000;

        $states = implode(',', json_decode(Configuration::get('MPSTOCK_ORDER_STATES'), true));
        $movement_id = (int) Configuration::get('MPSTOCK_ORDER_DETAIL_MOVEMENT_REASON_ID');
        if (!$movement_id) {
            $response = [
                'status' => 'fail',
                'message' => 'Step 3 fallito: Nessun movimento selezionato.',
            ];

            return $response;
        }
        $mvt = new ModelMpStockMvtReason($movement_id, $this->id_lang);
        $mvt_reason = $mvt->name;

        if ($page == 0) {
            try {
                AA_MpStockModelTemplate::renameColumn('mpstock_movement', 'id_mpstock_product', 'id_mpstock_movement');
            } catch (\Throwable $th) {
                // nothing
            }
            $delete = 'DELETE FROM ' . _DB_PREFIX_ . 'mpstock_movement WHERE id_order_detail > 0';
            $res = Db::getInstance()->execute($delete);
            if (!$res) {
                $response = [
                    'status' => 'fail',
                    'message' => 'Step 3 fallito: ' . Db::getInstance()->getMsgError(),
                ];

                return $response;
            }
        }

        $source = 'mpstock_movement';
        $query = 'SELECT od.*, o.date_add, o.date_upd FROM ' . _DB_PREFIX_ . 'order_detail od '
            . 'INNER JOIN ' . _DB_PREFIX_ . 'orders o ON o.id_order = od.id_order '
            . 'INNER JOIN ' . _DB_PREFIX_ . 'product p ON od.product_id = p.id_product '
            . 'WHERE o.current_state not in (' . $states . ') '
            // . 'AND od.id_order_detail NOT IN (SELECT id_order_detail FROM ' . _DB_PREFIX_ . 'mpstock_movement) '
            . 'ORDER BY od.id_order_detail '
            . 'LIMIT ' . $page * $pagination . ', ' . $pagination;
        $rows = Db::getInstance()->executeS($query);
        if ($rows) {
            $header = [
                'id_warehouse',
                'id_document',
                'id_order',
                'id_order_detail',
                'id_mpstock_mvt_reason',
                'mvt_reason',
                'id_product',
                'id_product_attribute',
                'reference',
                'ean13',
                'upc',
                'stock_quantity_before',
                'stock_movement',
                'stock_quantity_after',
                'price_te',
                'wholesale_price_te',
                'id_employee',
                'date_add',
                'date_upd',
            ];
            $header = array_map(function ($item) {
                return '`' . $item . '`';
            }, $header);

            $values = [];
            foreach ($rows as $row) {
                $data = [
                    'id_warehouse' => 0,
                    'id_document' => 0,
                    'id_order' => $row['id_order'],
                    'id_order_detail' => $row['id_order_detail'],
                    'id_mpstock_mvt_reason' => $movement_id,
                    'mvt_reason' => $mvt_reason,
                    'id_product' => $row['product_id'],
                    'id_product_attribute' => $row['product_attribute_id'],
                    'reference' => $row['product_reference'],
                    'ean13' => $row['product_ean13'],
                    'upc' => $row['product_upc'],
                    'stock_quantity_before' => $row['product_quantity_in_stock'],
                    'stock_movement' => -$row['product_quantity'],
                    'stock_quantity_after' => $row['product_quantity_in_stock'] - $row['product_quantity'],
                    'price_te' => $row['product_price'],
                    'wholesale_price_te' => 0,
                    'id_employee' => 0,
                    'date_add' => $row['date_add'],
                    'date_upd' => $row['date_upd'],
                ];
                $value = array_values($data);
                $values[] = '(' . implode(',', array_map(function ($item) {
                    return "'{$item}'";
                }, $value)) . ')';
            }

            $pfx = _DB_PREFIX_ . $source;
            $QUERY = "INSERT IGNORE INTO {$pfx} (" . implode(',', $header) . ') VALUES ' . implode(',', $values) . ';';
            $res = Db::getInstance()->execute($QUERY);

            // $res = Db::getInstance()->insert($source, $data, true, true, Db::INSERT_IGNORE);
            if (!$res) {
                $response = [
                    'status' => 'fail',
                    'message' => 'Step 3 fallito: ' . Db::getInstance()->getMsgError(),
                ];

                return $response;
            }

            $tot = ($page * $pagination) + count($rows);

            $response = [
                'status' => 'success',
                'message' => 'Step 3: ' . $tot . ' righe inserite.',
                'page' => $page + 1,
            ];

            return $response;
        }

        $response = [
            'status' => 'success',
            'message' => 'Step 3 completato: Dettagli ordini inseriti.',
        ];

        return $response;
    }

    public function funcUpdateOrderReferences()
    {
        $db = Db::getInstance();
        $query = 'SELECT b.id_mpstock_movement, a.id_mpstock_document, a.number_document, a.date_document, a.id_supplier '
            . 'FROM ' . _DB_PREFIX_ . 'mpstock_document a '
            . 'INNER JOIN ' . _DB_PREFIX_ . 'mpstock_movement b ON a.id_mpstock_document = b.id_document '
            . 'WHERE a.id_mpstock_document > 0 '
            . 'AND a.date_document != \'1970-01-01\' '
            . 'ORDER BY a.id_mpstock_document';
        $documents = $db->executeS($query);
        $updates = [];
        foreach ($documents as $doc) {
            $updates[] = 'UPDATE ' . _DB_PREFIX_ . 'mpstock_movement SET '
                . "document_number='{$doc['number_document']}', "
                . "document_date='{$doc['date_document']}', "
                . "id_supplier= {$doc['id_supplier']} "
                . "WHERE id_mpstock_movement = {$doc['id_mpstock_movement']};";
        }
        $query = implode("\n", $updates);
        $res = $db->execute($query);
        if ($res) {
            exit('RIGHE AGGIORNATE: ' . $db->Affected_Rows());
        }

        exit('ERRORE: ' . $db->getMsgError());
    }
}
