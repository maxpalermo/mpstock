<?php
use MpSoft\MpStock\Config\MvtReasonConfig;
use MpSoft\MpStock\Helpers\StockMovement;

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
use MpSoft\MpStock\Helpers\XmlParser;

class AdminMpStockImportController extends ModuleAdminController
{
    protected $id_lang;
    protected $id_shop;
    protected $id_employee;
    protected $upload_path;

    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'mpstock_import';
        $this->identifier = 'id_mpstock_import';
        $this->list_id = $this->identifier;
        $this->className = 'ModelMpStockImport';
        $this->explicitSelect = true;
        $this->lang = false;

        parent::__construct();

        $this->id_lang = (int) Context::getContext()->language->id;
        $this->id_shop = (int) Context::getContext()->shop->id;
        $this->id_employee = (int) Context::getContext()->employee->id;
        $this->upload_path = _PS_UPLOAD_DIR_ . 'mpstock/';
        if (!file_exists($this->upload_path)) {
            mkdir($this->upload_path, 0775, true);
        }
    }

    public function init()
    {
        $this->fields_list = [
            'id_mpstock_import' => [
                'title' => $this->module->l('Id', $this->controller_name),
                'align' => 'text-right',
                'width' => 'auto',
                'class' => 'fixed-width-sm',
            ],
            'filename' => [
                'title' => $this->module->l('Filename', $this->controller_name),
                'align' => 'text-left',
                'width' => 'auto',
                'class' => 'fixed-width-md',
                'lang' => false,
                'float' => 'true',
            ],
            'import_type' => [
                'title' => $this->module->l('Tipo importazione', $this->controller_name),
                'type' => 'select',
                'list' => [
                    'load' => 'Carico',
                    'unload' => 'Scarico',
                ],
                'align' => 'text-center',
                'width' => 'auto',
                'class' => 'fixed-width-md',
                'float' => 'true',
                'filter_key' => 'a!import_type',
                'filter_type' => 'string',
                'callback' => 'displayImportType',
            ],
            'movement_date' => [
                'title' => $this->module->l('Data movimento', $this->controller_name),
                'type' => 'date',
                'align' => 'text-center',
                'width' => 'auto',
                'class' => 'fixed-width-md',
                'filter_key' => 'a!date_upd',
            ],
            'movement_type' => [
                'title' => $this->module->l('Tipo movimento', $this->controller_name),
                'type' => 'select',
                'list' => ModelMpStockMvtReason::getMovementReasons($this->id_lang, true),
                'align' => 'text-center',
                'width' => 'auto',
                'class' => 'fixed-width-md',
                'filter_key' => 'a!movement_type',
                'callback' => 'getReasonName',
            ],
            'sign' => [
                'title' => $this->module->l('Segno', $this->controller_name),
                'type' => 'select',
                'list' => [
                    0 => 'Positivo',
                    1 => 'Negativo',
                ],
                'align' => 'text-center',
                'width' => 'auto',
                'class' => 'fixed-width-sm',
                'filter_key' => 'a!sign',
                'filter_type' => 'int',
                'callback' => 'displaySign',
                'float' => 'true',
            ],
            'ean13' => [
                'title' => $this->module->l('Ean13', $this->controller_name),
                'align' => 'text-left',
                'width' => 'auto',
                'class' => 'fixed-width-md',
                'lang' => false,
                'float' => 'true',
                'filter_key' => 'a!ean13',
                'filter_type' => 'string',
            ],
            'reference' => [
                'title' => $this->module->l('Riferimento', $this->controller_name),
                'align' => 'text-left',
                'width' => 'auto',
                'class' => 'fixed-width-md',
                'lang' => false,
                'float' => 'true',
                'filter_key' => 'pa!reference',
                'filter_type' => 'string',
            ],
            'product_name' => [
                'title' => $this->module->l('Prodotto', $this->controller_name),
                'align' => 'text-left',
                'width' => 'auto',
                'class' => 'fixed-width-md',
                'lang' => false,
                'float' => 'true',
                'filter_key' => 'pl!name',
                'filter_type' => 'string',
            ],
            'variant_name' => [
                'title' => $this->module->l('Variante', $this->controller_name),
                'align' => 'text-left',
                'width' => 'auto',
                'class' => 'fixed-width-md',
                'lang' => false,
                'float' => 'true',
                'filter_key' => 'pa!id_product_attribute',
                'filter_type' => 'int',
                'callback' => 'getVariantName',
            ],
            'stock_before' => [
                'title' => $this->module->l('Qta prima', $this->controller_name),
                'type' => 'int',
                'align' => 'text-right',
                'width' => 'auto',
                'class' => 'fixed-width-sm',
                'filter_key' => 'a!stock_before',
            ],
            'quantity' => [
                'title' => $this->module->l('Quantità', $this->controller_name),
                'type' => 'int',
                'align' => 'text-right',
                'width' => 'auto',
                'class' => 'fixed-width-sm',
                'filter_key' => 'a!quantity',
            ],
            'stock_after' => [
                'title' => $this->module->l('Qta dopo', $this->controller_name),
                'type' => 'int',
                'align' => 'text-right',
                'width' => 'auto',
                'class' => 'fixed-width-sm',
                'filter_key' => 'a!stock_after',
            ],
            'price' => [
                'title' => $this->module->l('Prezzo', $this->controller_name),
                'type' => 'price',
                'align' => 'text-right',
                'width' => 'auto',
                'class' => 'fixed-width-md',
                'filter_key' => 'a!price',
            ],
            'wholesale_price' => [
                'title' => $this->module->l('Acquisto', $this->controller_name),
                'type' => 'price',
                'align' => 'text-right',
                'width' => 'auto',
                'class' => 'fixed-width-md',
                'filter_key' => 'a!wholesale_price',
            ],
            'loaded' => [
                'title' => $this->module->l('Caricato', $this->controller_name),
                'type' => 'bool',
                'align' => 'text-center',
                'width' => 'auto',
                'class' => 'fixed-width-sm',
                'callback' => 'isLoaded',
                'float' => 'true',
                'filter_key' => 'a!loaded',
                'filter_type' => 'int',
            ],
            'id_employee' => [
                'title' => $this->module->l('Operatore', $this->controller_name),
                'type' => 'select',
                'list' => ModelEmployee::getEmployees(false, true),
                'align' => 'text-center',
                'width' => 'auto',
                'class' => 'fixed-width-sm',
                'callback' => 'displayEmployee',
                'float' => 'true',
                'filter_key' => 'a!id_employee',
                'filter_type' => 'int',
            ],
            'date_add' => [
                'title' => $this->module->l('Data inserimento', $this->controller_name),
                'type' => 'datetime',
                'align' => 'text-center',
                'width' => 'auto',
                'class' => 'fixed-width-md',
                'filter_key' => 'a!date_add',
            ],
            'date_upd' => [
                'title' => $this->module->l('Data aggiornamento', $this->controller_name),
                'type' => 'datetime',
                'align' => 'text-center',
                'width' => 'auto',
                'class' => 'fixed-width-md',
                'filter_key' => 'a!date_upd',
            ],
        ];

        // $this->addRowAction('import');
        // $this->addRowAction('delete');

        $this->_defaultOrderWay = 'DESC';
        $this->_defaultOrderBy = 'id_mpstock_import';

        $this->_join .= ' INNER JOIN ' . _DB_PREFIX_ . 'product_attribute pa ON a.ean13 = pa.ean13';
        $this->_join .= ' INNER JOIN ' . _DB_PREFIX_ . 'product_lang pl ON pa.id_product = pl.id_product AND pl.id_lang=' . (int) $this->id_lang;
        $this->_group = 'GROUP BY a.id_mpstock_import';

        parent::init();
    }

    public function displayImportLink($token = null, $id, $name = null)
    {
        // $token will contain token variable
        // $id will hold id_identifier value
        // $name will hold display name

        $tpl = $this->module->getLocalPath() . 'views/templates/admin/rowAction/import.tpl';
        $smarty = Context::getContext()->smarty;
        $smarty->assign([
            'token' => $token,
            'id' => $id,
            'name' => $name,
            'title' => $name,
            'href' => $this->context->link->getAdminLink($this->controller_name, true) . '&import' . $this->table . '&id_mpstock_mvt_reason=' . $id,
        ]);

        return $smarty->fetch($tpl);
    }

    public function initPageHeaderToolbar()
    {
        parent::initPageHeaderToolbar();

        $this->page_header_toolbar_btn['new'] = [
            'href' => $this->context->link->getAdminLink($this->controller_name, true) . '&add' . $this->table,
            'desc' => $this->module->l('Carica file', $this->controller_name),
            'icon' => 'process-icon-new',
        ];

        $this->page_header_toolbar_btn['upload'] = [
            'href' => $this->context->link->getAdminLink($this->controller_name, true) . '&action=import',
            'desc' => $this->module->l('Importa', $this->controller_name),
            'icon' => 'process-icon-upload',
        ];
    }

    public function initToolbar()
    {
        parent::initToolbar();

        $this->toolbar_btn['upload'] = [
            'href' => $this->context->link->getAdminLink($this->controller_name, true) . '&action=import',
            'desc' => $this->module->l('Importa', $this->controller_name),
            'imgclass' => 'upload',
        ];
    }

    public function initContent()
    {
        $this->content = $this->renderFormTypeImport();

        return parent::initContent();
    }

    public function setMedia()
    {
        $this->addCSS($this->module->getLocalPath() . 'views/css/panel-auto-width.css');
        parent::setMedia();
    }

    public function renderFormTypeImport()
    {
        $helper = new HelperForm();
        $helper->module = $this->module;
        $helper->name_controller = $this->controller_name;
        $helper->token = Tools::getAdminTokenLite($this->controller_name);
        $helper->currentIndex = AdminController::$currentIndex;
        $helper->submit_action = 'updateImportType';

        $helper->fields_value = $this->getImportFormValues();

        $form = $this->getImportFormFields();

        return $helper->generateForm([['form' => $form]]);
    }

    public function getImportFormFields()
    {
        $form = [
            'legend' => [
                'title' => $this->module->l('Impostazioni Importazione', $this->controller_name),
                'icon' => 'icon-cog',
            ],
            'input' => [
                [
                    'type' => 'select',
                    'label' => $this->module->l('Movimento di Carico', $this->controller_name),
                    'options' => [
                        'query' => ModelMpStockMvtReason::getMovementReasons(),
                        'id' => 'id_mpstock_mvt_reason',
                        'name' => 'name',
                    ],
                    'name' => 'id_mpstock_load_movement',
                    'suffix' => '<i class="icon-upload"></i>',
                    'class' => 'fixed-width-sm chosen',
                ],
                [
                    'type' => 'select',
                    'label' => $this->module->l('Movimento di Scarico', $this->controller_name),
                    'options' => [
                        'query' => ModelMpStockMvtReason::getMovementReasons(),
                        'id' => 'id_mpstock_mvt_reason',
                        'name' => 'name',
                    ],
                    'name' => 'id_mpstock_unload_movement',
                    'suffix' => '<i class="icon-download"></i>',
                    'class' => 'fixed-width-sm chosen',
                ],
                [
                    'type' => 'select',
                    'label' => $this->module->l('Movimento di Ordine', $this->controller_name),
                    'options' => [
                        'query' => ModelMpStockMvtReason::getMovementReasons(),
                        'id' => 'id_mpstock_mvt_reason',
                        'name' => 'name',
                    ],
                    'name' => 'id_mpstock_order_movement',
                    'suffix' => '<i class="icon-shopping-cart"></i>',
                    'class' => 'fixed-width-sm chosen',
                ],
                [
                    'type' => 'select',
                    'label' => $this->module->l('Movimento di Reso', $this->controller_name),
                    'options' => [
                        'query' => ModelMpStockMvtReason::getMovementReasons(),
                        'id' => 'id_mpstock_mvt_reason',
                        'name' => 'name',
                    ],
                    'name' => 'id_mpstock_return_movement',
                    'suffix' => '<i class="icon-undo"></i>',
                    'class' => 'fixed-width-sm chosen',
                ],
                [
                    'type' => 'select',
                    'label' => $this->module->l('Movimento tramite EAN13', $this->controller_name),
                    'options' => [
                        'query' => ModelMpStockMvtReason::getMovementReasons(),
                        'id' => 'id_mpstock_mvt_reason',
                        'name' => 'name',
                    ],
                    'name' => 'id_mpstock_ean13_movement',
                    'suffix' => '<i class="icon-undo"></i>',
                    'class' => 'fixed-width-sm chosen',
                ],
            ],
            'submit' => [
                'title' => $this->module->l('Save', $this->controller_name),
                'icon' => 'process-icon-save',
            ],
        ];

        return $form;
    }

    public function getImportFormValues()
    {
        $record = [
            'id_mpstock_load_movement' => MvtReasonConfig::getIdLoadMovement(),
            'id_mpstock_unload_movement' => MvtReasonConfig::getIdUnloadMovement(),
            'id_mpstock_order_movement' => MvtReasonConfig::getIdOrderMovement(),
            'id_mpstock_return_movement' => MvtReasonConfig::getIdReturnMovement(),
            'id_mpstock_ean13_movement' => MvtReasonConfig::getIdEan13Movement(),
            'id_employee' => $this->id_employee,
        ];

        return $record;
    }

    public function renderForm()
    {
        $helper = new HelperForm();
        $helper->module = $this->module;
        $helper->name_controller = $this->controller_name;
        $helper->token = Tools::getAdminTokenLite($this->controller_name);
        $helper->currentIndex = AdminController::$currentIndex;
        $helper->submit_action = 'uploadFile';

        $helper->fields_value = [
            'file_upload' => '',
        ];

        $form = [
            'legend' => [
                'title' => $this->module->l('Carica un file XML', $this->controller_name),
                'icon' => 'icon-download',
            ],
            'input' => [
                [
                    'type' => 'file',
                    'label' => $this->module->l('Carica File (.xml)', $this->controller_name),
                    'name' => 'file_upload',
                    'required' => true,
                ],
            ],
            'submit' => [
                'title' => $this->module->l('Carica', $this->controller_name),
                'icon' => 'process-icon-upload',
            ],
            'buttons' => [
                [
                    'href' => $this->context->link->getAdminLink($this->controller_name, true),
                    'title' => $this->module->l('Indietro', $this->controller_name),
                    'icon' => 'process-icon-back',
                ],
            ],
        ];

        return $helper->generateForm([['form' => $form]]);
    }

    public function getReasonName($value)
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('b.name')
            ->from(ModelMpStockMvtReason::$definition['table'], 'a')
            ->innerJoin(ModelMpStockMvtReason::$definition['table'] . '_lang', 'b')
            ->where('a.id_mpstock_mvt_reason=' . (int) $value)
            ->where('b.id_mpstock_mvt_reason=a.id_mpstock_mvt_reason')
            ->where('b.id_lang=' . (int) $this->id_lang);
        $name = Tools::strtoupper($db->getValue($sql));

        return $name;
    }

    public function getVariantName($value)
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('GROUP_CONCAT(al.name)')
            ->from('product_attribute', 'pa')
            ->innerJoin('product_attribute_combination', 'pac', 'pac.id_product_attribute = pa.id_product_attribute')
            ->innerJoin('attribute_lang', 'al', 'al.id_attribute = pac.id_attribute and al.id_lang=' . (int) $this->id_lang)
            ->where('pa.id_product_attribute=' . (int) $value);
        $name = Tools::strtoupper($db->getValue($sql));

        return $name;
    }

    public function displaySign($value)
    {
        if (!$value) {
            return '<i class="icon-plus text-success"></i>';
        }

        return '<i class="icon-minus text-danger"></i>';
    }

    public function displayImportType($value)
    {
        if ($value == 'load') {
            return '<span class="badge badge-pill badge-success">' . $this->module->l('Carico', $this->controller_name) . '</span>';
        } else {
            return '<span class="badge badge-pill badge-danger">' . $this->module->l('Scarico', $this->controller_name) . '</span>';
        }
    }

    public function displayEmployee($value)
    {
        $employee = new Employee($value);

        return $employee->firstname . ' ' . $employee->lastname;
    }

    public function displayDocuments($value)
    {
        return '<span class="badge badge-pill badge-primary">' . $value . '</span>';
    }

    public function isDeleted($value)
    {
        if ($value) {
            return '<i class="icon-times text-danger"></i>';
        }

        return '<i class="icon-check text-success"></i>';
    }

    public function isLoaded($value)
    {
        if ($value) {
            return '<i class="icon-check text-success"></i>';
        }

        return '<i class="icon-times text-danger"></i>';
    }

    public function postProcess()
    {
        if (Tools::isSubmit('uploadFile')) {
            $this->processUploadFile();
        }

        if (Tools::isSubmit('updateImportType')) {
            MvtReasonConfig::setIdLoadMovement((int) Tools::getValue('id_mpstock_load_movement'));
            MvtReasonConfig::setIdUnloadMovement((int) Tools::getValue('id_mpstock_unload_movement'));
            MvtReasonConfig::setIdOrderMovement((int) Tools::getValue('id_mpstock_order_movement'));
            MvtReasonConfig::setIdReturnMovement((int) Tools::getValue('id_mpstock_return_movement'));
            MvtReasonConfig::setIdEan13Movement((int) Tools::getValue('id_mpstock_ean13_movement'));
            $this->confirmations[] = $this->module->l('Impostazioni importazione salvate correttamente', $this->controller_name);

            return true;
        }

        return parent::postProcess();
    }

    public function processUploadFile()
    {
        $file = Tools::fileAttachment('file_upload', false);
        if ($file['error']) {
            $this->errors[] = $this->module->l('Errore durante il caricamento del file', $this->controller_name);

            return false;
        }

        if ($file['mime'] != 'text/xml') {
            $this->errors[] = $this->module->l('Il file caricato non è un file XML', $this->controller_name);

            return false;
        }

        $filename = Tools::strtolower($file['name']);
        if (!$this->parseFilename($filename)) {
            $this->errors[] = $this->module->l('Il nome del file non è valido.', $this->controller_name);

            return false;
        }

        $tmp_file = $file['tmp_name'];

        $move = move_uploaded_file($tmp_file, $this->upload_path . $filename);
        if ($move) {
            $parser = new XmlParser($filename);
            $res = $parser->parse();
            if ($res) {
                $this->confirmations[] = $this->module->l('File caricato correttamente', $this->controller_name);
            } else {
                return false;
            }
        } else {
            try {
                unlink($tmp_file);
            } catch (\Throwable $th) {
                $this->errors[] = $th->getMessage();
            }
            $this->errors[] = sprintf(
                $this->module->l('Errore durante il caricamento del file %s', $this->controller_name),
                $filename
            );
        }
    }

    public function processSave()
    {
        $languages = Language::getLanguages(false);
        $names = [];
        foreach ($languages as $lang) {
            $names[$lang['id_lang']] = Tools::getValue('name_' . $lang['id_lang']);
        }

        $id = (int) Tools::getValue('id_mpstock_mvt_reason');
        $sign = (int) Tools::getValue('sign');
        $active = (int) Tools::getValue('active');
        $alias = Tools::getValue('alias');

        $record = new ModelMpStockMvtReason($id);
        $record->alias = $alias;
        $record->name = $names;
        $record->sign = $sign;
        $record->active = $active;

        try {
            if (Validate::isLoadedObject($record)) {
                $res = $record->update();
            } else {
                $res = $record->add();
            }
        } catch (\Throwable $th) {
            $res = false;
            $this->errors[] = $th->getMessage();
        }

        if ($res) {
            $this->confirmations[] = $this->module->l('Record salvato correttamente', $this->controller_name);
        } else {
            $this->errors[] = $this->module->l('Errore durante il salvataggio del record', $this->controller_name);
        }

        return $res;
    }

    public function processDelete()
    {
        $id = (int) Tools::getValue('id_mpstock_mvt_reason');

        if ($tot_doc = $this->hasDocuments($id)) {
            $this->warnings[] = sprintf(
                $this->module->l('Impossibile eliminare il record, sono presenti %s documenti associati', $this->controller_name),
                "<strong>$tot_doc</strong>"
            );

            return false;
        }

        try {
            $record = new ModelMpStockMvtReason($id);
            if (Validate::isLoadedObject($record)) {
                $res = $record->delete();
            } else {
                $res = false;
            }
        } catch (\Throwable $th) {
            $this->errors[] = $th->getMessage();

            return false;
        }

        if ($res) {
            $this->confirmations[] = $this->module->l('Record eliminato correttamente', $this->controller_name);
        } else {
            $this->errors[] = $this->module->l('Errore durante la cancellazione del record', $this->controller_name);
        }

        return $res;
    }

    public function processImport()
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('*')
            ->from(ModelMpStockImport::$definition['table'])
            ->where('loaded=0')
            ->orderBy(ModelMpStockImport::$definition['primary'] . ' ASC');
        $rows = $db->executeS($sql);
        $skipped = 0;

        if ($rows) {
            foreach ($rows as $row) {
                if (!(int) $row['quantity']) {
                    $this->warnings[] = sprintf(
                        $this->module->l('La quantità del record %s %s è zero', $this->controller_name),
                        "<strong>{$row['ean13']}</strong>",
                        "<strong>{$row['reference']}</strong>"
                    );

                    continue;
                }

                if ($row['import_type'] == 'load') {
                    $quantity = (int) abs($row['quantity']);
                    $id_mvt_reason = MvtReasonConfig::getIdLoadMovement();
                } else {
                    $quantity = (int) abs($row['quantity']) * -1;
                    $id_mvt_reason = MvtReasonConfig::getIdUnloadMovement();
                }
                if (!$id_mvt_reason) {
                    $id_mvt_reason = ModelMpStockMvtReason::getIdByAlias($row['movement_type']);
                }

                $id_product = $this->getIdProduct($row['ean13'], $row['reference']);
                if (!$id_product) {
                    $this->errors[] = sprintf(
                        $this->module->l('Impossibile trovare il prodotto con ean13 %s e riferimento %s', $this->controller_name),
                        "<strong>{$row['ean13']}</strong>",
                        "<strong>{$row['reference']}</strong>"
                    );
                    $skipped++;

                    continue;
                }

                $id_product_attribute = $this->getIdProductAttribute($row['ean13'], $row['reference']);
                if (!$id_product_attribute) {
                    $this->errors[] = sprintf(
                        $this->module->l('Impossibile trovare la variante con ean13 %s e riferimento %s', $this->controller_name),
                        "<strong>{$row['ean13']}</strong>",
                        "<strong>{$row['reference']}</strong>"
                    );
                    $skipped++;

                    continue;
                }

                $stockMovement = new StockMovement($id_product, $id_product_attribute, (int) $row['id_mpstock_import'], $row['filename']);
                $record = $stockMovement->generateMovement($id_mvt_reason, $quantity, $row['price'], $row['wholesale_price']);

                if ($record === false) {
                    $this->errors[] = sprintf(
                        $this->module->l('Errore durante la creazione del movimento %s %s qta: %s %s', $this->controller_name),
                        "<strong>{$row['ean13']}</strong>",
                        "<strong>{$row['reference']}</strong>",
                        "<strong>{$quantity}</strong>",
                        '<p>' . implode('<br>', $stockMovement->getErrors()) . '</p>'
                    );
                    $skipped++;

                    continue;
                }

                /*
                $record = new ModelMpStockProduct();
                $record->id_warehouse = 0;
                $record->id_document = 0;
                $record->id_mpstock_mvt_reason = (int) $id_mvt_reason;
                $record->id_product = (int) $id_product;
                $record->id_product_attribute = (int) $id_product_attribute;
                $record->reference = $row['reference'];
                $record->ean13 = $row['ean13'];
                $record->upc = '';
                $record->physical_quantity = $stockQuantityBefore;
                $record->usable_quantity = $quantity;
                $record->price_te = $row['price'];
                $record->wholesale_price_te = $row['wholesale_price'];
                $record->id_employee = (int) Context::getContext()->employee->id;
                $record->id_mpstock_import = (int) $row['id_mpstock_import'];
                $record->filename = $row['filename'];
                $record->date_add = date('Y-m-d H:i:s');
                $record->date_upd = date('Y-m-d H:i:s');
                */

                try {
                    $id_mpstock_import = (int) $row['id_mpstock_import'];
                    $res = $record->add();
                    if ($res) {
                        if ($stockMovement->updateStock($id_product, $id_product_attribute, $quantity)) {
                            if ($stockMovement->updateImportMovementAfterStock($id_mpstock_import)) {
                                $stockQuantityBefore = $stockMovement->getStockQuantityBefore();
                                $stockQuantityAfter = $stockMovement->getStockQuantityAfter();

                                $this->confirmations[] = sprintf(
                                    $this->module->l('%sRecord salvato correttamente: %s %s prima: %s qta: %s stock: %s %s', $this->controller_name),
                                    '<p>',
                                    "<strong>{$record->ean13}</strong>",
                                    "<strong>{$record->reference}</strong>",
                                    "<strong>{$stockQuantityBefore}</strong>",
                                    "<strong>{$record->usable_quantity}</strong>",
                                    "<strong>{$stockQuantityAfter}</strong>",
                                    '</p>'
                                );
                            } else {
                                $this->errors[] = sprintf(
                                    $this->module->l('Errore durante l\'aggiornamento dello stock %s %s qta: %s %s', $this->controller_name),
                                    "<strong>{$row['ean13']}</strong>",
                                    "<strong>{$row['reference']}</strong>",
                                    "<strong>{$quantity}</strong>",
                                    '<p>' . implode('<br>', $stockMovement->getErrors()) . '</p>'
                                );
                                $skipped++;
                            }
                        } else {
                            $this->errors[] = sprintf(
                                $this->module->l('Errore durante l\'aggiornamento del movimento %s %s qta: %s %s', $this->controller_name),
                                "<strong>{$row['ean13']}</strong>",
                                "<strong>{$row['reference']}</strong>",
                                "<strong>{$quantity}</strong>",
                                '<p>' . implode('<br>', $stockMovement->getErrors()) . '</p>'
                            );
                            $skipped++;
                        }

                        /*
                        StockAvailable::updateQuantity($id_product, $id_product_attribute, $quantity);
                        ModelProductAttribute::setStockQuantity($id_product, $id_product_attribute);
                        $stockQuantityAfter = StockAvailable::getQuantityAvailableByProduct($id_product, $id_product_attribute);

                        Db::getInstance()->update(
                            ModelMpStockImport::$definition['table'],
                            ['loaded' => 1],
                            'id_mpstock_import=' . (int) $row['id_mpstock_import']
                        );

                        ModelMpStockImport::updateStock($row['id_mpstock_import'], $stockQuantityBefore, $stockQuantityAfter);
                        */
                    } else {
                        $this->errors[] = sprintf(
                            $this->module->l('Errore nel salvataggio del record %s %s qta: %s => %s', $this->controller_name),
                            "<strong>{$record->ean13}</strong>",
                            "<strong>{$record->reference}</strong>",
                            "<strong>{$record->physical_quantity}</strong>",
                            Db::getInstance()->getMsgError()
                        );
                        $skipped++;
                    }
                } catch (\Throwable $th) {
                    $res = false;
                    $this->errors[] = $th->getMessage();
                    $skipped++;
                }
            }

            $this->confirmations[] = sprintf(
                '<h1>' . $this->module->l('Importazione completata: %s righe su un totale di %s', $this->controller_name) . '</h1>',
                count($rows) - $skipped,
                count($rows)
            );
        } else {
            $this->warnings[] = $this->module->l('Nessun record da importare', $this->controller_name);
        }
    }

    public function getIdProductAttribute($ean13, $reference)
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('id_product_attribute')
            ->from('product_attribute')
            ->where('ean13=\'' . pSQL($ean13) . '\'')
            ->where('reference=\'' . pSQL($reference) . '\'');
        $id = (int) $db->getValue($sql);

        return $id;
    }

    public function getIdProduct($ean13, $reference)
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('id_product')
            ->from('product_attribute')
            ->where('ean13=\'' . pSQL($ean13) . '\'')
            ->where('reference=\'' . pSQL($reference) . '\'');
        $id = (int) $db->getValue($sql);

        return $id;
    }

    /**
     * Check if a movement id has associated documents
     *
     * @param int $id id of the movement
     *
     * @return int number of associated documents
     */
    public function hasDocuments($id)
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('count(id_mpstock_document)')
            ->from(ModelMpStockDocument::$definition['table'])
            ->where('id_mpstock_mvt_reason=' . (int) $id);
        $count = (int) $db->getValue($sql);

        return $count;
    }

    public function processRenumber()
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('id_mpstock_mvt_reason')
            ->from(ModelMpStockMvtReason::$definition['table'])
            ->orderBy('id_mpstock_mvt_reason');
        $rows = $db->executeS($sql);
        if ($rows) {
            $i = 1;
            foreach ($rows as $row) {
                $id = (int) $row['id_mpstock_mvt_reason'];

                if ($id == $i) {
                    $i++;

                    continue;
                }

                $db->update(
                    ModelMpStockMvtReason::$definition['table'],
                    ['id_mpstock_mvt_reason' => $i],
                    'id_mpstock_mvt_reason=' . $id
                );

                $db->update(
                    ModelMpStockMvtReason::$definition['table'] . '_lang',
                    ['id_mpstock_mvt_reason' => $i],
                    'id_mpstock_mvt_reason=' . $id
                );

                $db->update(
                    ModelMpStockDocument::$definition['table'],
                    ['id_mpstock_mvt_reason' => $i],
                    'id_mpstock_mvt_reason=' . $id
                );
                $tot_doc = $db->numRows();

                $db->update(
                    ModelMpStockProduct::$definition['table'],
                    ['id_mpstock_mvt_reason' => $i],
                    'id_mpstock_mvt_reason=' . $id
                );
                $tot_row = $db->numRows();

                $this->confirmations[] = sprintf(
                    $this->module->l('%sRecord renumerato correttamente da %s a %s: %s documenti riassegnati. %s prodotti riassegnati.%s', $this->controller_name),
                    '<p>',
                    "<strong>$id</strong>",
                    "<strong>$i</strong>",
                    "<strong>$tot_doc</strong>",
                    "<strong>$tot_row</strong>",
                    '</p>'
                );

                $i++;
            }
        }
    }

    public function parseFilename($filename)
    {
        // S(1400-20230309)171606.XML
        $matches = [];
        $match = preg_match('/^([s|c])\((.*)-(\d+)\)(\d+)\.xml$/', $filename, $matches);
        if ($match) {
            $type = $matches[1];
            $id = $matches[2];
            $date = $matches[3];
            $time = $matches[4];

            if ($type == 'S') {
                $type = 'unload';
            } else {
                $type = 'load';
            }

            return [
                'type' => $type,
                'id' => $id,
                'date' => $date,
                'time' => $time,
            ];
        } else {
            $this->errors[] = sprintf(
                $this->module->l('Il nome del file %s non è valido', $this->controller_name),
                $filename
            );

            return false;
        }
    }

    public function validateDate($date, $format = 'Y-m-d H:i:s')
    {
        $d = DateTime::createFromFormat($format, $date);

        return $d && $d->format($format) == $date;
    }
}
