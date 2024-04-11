<?php

class AdminMpStockProductController extends ModuleAdminController
{
    public const ALIGN_POSITIVE = 8080;
    public const ALIGN_NEGATIVE = 8090;

    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'product';
        $this->className = 'Product';
        $this->lang = false;
        $this->explicitSelect = true;
        $this->allow_export = true;
        $this->deleted = false;
        $this->context = Context::getContext();

        parent::__construct();
    }

    public function initContent()
    {
        $this->content = $this->getFormProductSelect() . $this->content;
        parent::initContent();
    }

    public function renderForm()
    {
        // Add your custom logic here

        return parent::renderForm();
    }

    public function getFormProductSelect()
    {
        $form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Select Product'),
                ],
                'input' => [
                    [
                        'type' => 'hidden',
                        'name' => 'id_product',
                        'required' => true,
                    ],
                    [
                        'type' => 'text',
                        'col' => 3,
                        'label' => $this->l('Reference'),
                        'name' => 'reference',
                        'required' => true,
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Search'),
                    'icon' => 'process-icon-search',
                ],
            ],
        ];

        $helper = new HelperForm();
        $helper->module = $this->module;
        $helper->name_controller = $this->controller_name;
        $helper->token = Tools::getAdminTokenLite('AdminMpStockProduct');
        $helper->currentIndex = Context::getContext()->link->getAdminLink('AdminMpStockProduct', false);
        $helper->title = $this->l('Seleziona un Prodotto');
        $helper->show_toolbar = false;
        $helper->toolbar_scroll = false;
        $helper->submit_action = 'submitProductSelect';
        $helper->fields_value = $this->fields_value;

        return $helper->generateForm([$form]);
    }

    protected function getProductTable()
    {
        $id_product = (int) isset($this->fields_value['id_product']) ? $this->fields_value['id_product'] : 0;
        if (!$id_product) {
            return '';
        }

        $product = new Product($id_product);
        $combinations = $product->getAttributeCombinations(Context::getContext()->language->id);
        $variants = [];
        foreach ($combinations as $combination) {
            $variants[$combination['id_product_attribute']][] = [
                'id_product_attribute' => $combination['id_product_attribute'],
                'reference' => $combination['reference'],
                'ean13' => $combination['ean13'],
                'price' => $combination['price'] ? $combination['price'] + $product->price : $product->price,
                'quantity' => $combination['quantity'],
                'group_name' => $combination['group_name'],
                'attribute_name' => $combination['attribute_name'],
                'location' => $combination['location'],
                'default_on' => $combination['default_on'],
            ];
        }

        foreach ($variants as $key => $variant) {
            $comb_name = '';
            $cover = Image::getCover($product->id);
            if ($cover) {
                $folder = Image::getImgFolderStatic($cover['id_image']);
                $image = '/img/p/' . $folder . $cover['id_image'] . '.jpg';
            } else {
                $image = 'https://img.freepik.com/free-vector/oops-404-error-with-broken-robot-concept-illustration_114360-5529.jpg?w=826&t=st=1712664728~exp=1712665328~hmac=6db023dbbd90c5751ac79dceb73bf51675bfd9242c007b7e518b61ced6c023ee';
            }
            $first = $variants[$key][0];
            $variants[$key]['image'] = $image;
            $variants[$key]['id_product'] = $product->id;
            $variants[$key]['id_product_attribute'] = $first['id_product_attribute'];
            $variants[$key]['reference'] = $first['reference'];
            $variants[$key]['name'] = $product->name[$this->context->language->id];
            $variants[$key]['ean13'] = $first['ean13'];
            $variants[$key]['price'] = $first['price'];
            $variants[$key]['quantity'] = $first['quantity'];
            $variants[$key]['location'] = $first['quantity'];
            $variants[$key]['default_on'] = false;
            foreach ($variant as $attribute) {
                $comb_name .= $attribute['attribute_name'] . ', ';
                if ($attribute['default_on']) {
                    $variants[$key]['default_on'] = true;
                }
            }
            $variants[$key]['combination_name'] = rtrim($comb_name, ', ');
        }

        $tpl = $this->module->getLocalPath() . 'views/templates/admin/StockProducts/table.tpl';
        $this->context->smarty->assign([
            'ajax_url' => $this->context->link->getAdminLink($this->controller_name),
            'reference' => $this->fields_value['reference'],
            'variants' => $variants,
            'link' => $this->context->link,
        ]);


        return $this->context->smarty->fetch($tpl);
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitProductSelect')) {
            $db = Db::getInstance();
            $sql = new DbQuery();

            $sql->select('id_product')
                ->from('product')
                ->where('reference = "' . pSQL(Tools::getValue('reference')) . '"');

            $id_product = (int) $db->getValue($sql);

            $this->fields_value = [
                'id_product' => $id_product,
                'reference' => Tools::getValue('reference'),
            ];
            $this->content .= $this->getProductTable();
        }

        return parent::postProcess();
    }

    public function processSubmitAdjustment()
    {
        $adjustments = Tools::getValue('variant_adjustment');
        $reasons = Tools::getValue('adjustment_reason');
        $variants = Tools::getValue('variants');

        foreach ($adjustments as $key => $adjustment) {
            /** @var Combination */
            $pa = new Combination($key);

            if (!Validate::isLoadedObject($pa)) {
                continue;
            }

            $stockAvailable = StockAvailable::getQuantityAvailableByProduct($pa->id_product, $pa->id);
            $id_product = $pa->id_product;
            $diff = $adjustment - $stockAvailable;

            if ($diff > 0) {
                $id_movement_type = self::ALIGN_POSITIVE;
            } elseif ($diff < 0) {
                $id_movement_type = self::ALIGN_NEGATIVE;
            } else {
                continue;
            }

            $stockQuantity = $stockAvailable + $diff;
            $reason = isset($reasons[$key]) ? $reasons[$key] : '--';
            $variant = isset($variants[$key]) ? $variants[$key] : '--';
            if (!$reason) {
                $this->warnings[] = sprintf(
                    $this->module->l('Allineamento prodotto %s %s: motivo non specificato', 'AdminMpStockProduct'),
                    $pa->reference,
                    $variant
                );
                $reason = '--';
            }

            $movement = new ModelMpStockMovement();
            $movement->id_mpstock_mvt_reason = $id_movement_type;
            $movement->mvt_reason = $reason;
            $movement->id_product = $pa->id_product;
            $movement->id_product_attribute = $pa->id;
            $movement->reference = $pa->reference;
            $movement->ean13 = $pa->ean13;
            $movement->upc = $pa->upc;
            $movement->stock_quantity_before = $stockAvailable;
            $movement->stock_movement = $diff;
            $movement->stock_quantity_after = $stockQuantity;
            $movement->price_te = $pa->price;
            $movement->wholesale_price_te = $pa->wholesale_price;
            $movement->id_employee = (int) Context::getContext()->employee->id;
            $movement->date_add = date('Y-m-d H:i:s');

            try {
                $res = $movement->add(true, true);
                if (!$res) {
                    $this->errors = Db::getInstance()->getMsgError();
                }

                $this->updateStock($pa->id_product, $pa->id, $diff);
                $this->setCheckProductQuantity($id_product, 1);

                $this->confirmations[] = '<p>' . sprintf(
                    $this->module->l('Allineamento prodotto %s %s: quantità %d', 'AdminMpStockProduct'),
                    $pa->reference,
                    $variant,
                    $diff
                ) . '</p>';
            } catch (\Throwable $th) {
                $this->errors[] = $th->getMessage();
            }
        }
    }

    protected function updateStock($id_product, $id_product_attribute, $delta)
    {
        return StockAvailable::updateQuantity($id_product, $id_product_attribute, $delta);
    }

    protected function setCheckProductQuantity($id_product, $value)
    {
        $db = Db::getInstance();
        $id_employee = (int) Context::getContext()->employee->id;
        $is_checked = (int) $value;
        $date = date('Y-m-d H:i:s');
        $id_employee = Context::getContext()->employee->id;

        try {
            $res = (int) $db->update(
                'product',
                array(
                    'id_employee' => $id_employee,
                    'is_checked' => $is_checked,
                    'date_checked' => $date,
                ),
                'id_product=' . (int) $id_product
            );
        } catch (\Throwable $th) {
            $this->errors[] = $th->getMessage();
            $res = false;
        }

        return $res;
    }
}