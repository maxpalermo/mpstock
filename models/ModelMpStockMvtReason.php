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
class ModelMpStockMvtReason extends AA_MpStockModelTemplate
{
    public $id;
    public $id_lang;
    public $id_shop;
    public $sign;
    public $name;
    public $active;
    public $date_add;
    public $date_upd;
    protected $module;
    protected $context;

    public static $definition = [
        'table' => 'mpstock_mvt_reason',
        'primary' => 'id_mpstock_mvt_reason',
        'multilang' => true,
        'fields' => [
            'sign' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => true,
            ],
            'active' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => true,
            ],
            'date_add' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'datetime' => true,
                'required' => true,
            ],
            'date_upd' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'timestamp' => true,
                'required' => false,
            ],
            /** LANG FIELD **/
            'name' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'size' => 255,
                'lang' => true,
                'required' => true,
            ],
        ],
    ];

    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
        parent::__construct($id, $id_lang, $id_shop);
        $this->context = Context::getContext();
        $this->smarty = $this->context->smarty;
        $this->module = Module::getInstanceByName('mpstock');
    }

    public function delete()
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('count(*)')
            ->from('mpstock_mvt')
            ->where('id_mpstock_mvt_reason=' . (int) $this->id);
        $count = (int) $db->getValue($sql);
        if ($count) {
            return false;
        }

        return parent::delete();
    }

    public function saveValues()
    {
        $id_shop = (int) $this->id_shop;
        $number_document = Tools::getValue('number_document');
        $date_document = Tools::getValue('date_document');
        $id_supplier = (int) Tools::getValue('id_supplier');
        $date_add = date('Y-m-d H:i:s');

        if (!$number_document) {
            return $this->module->l('Please select a valid number document', 'ModelMpStockMvtReason');
        }
        if (!$date_document) {
            return $this->module->l('Please select a valid date document', 'ModelMpStockMvtReason');
        }
        if (!$id_supplier) {
            return $this->module->l('Please select a valid supplier', 'ModelMpStockMvtReason');
        }

        $this->id_shop = (int) $id_shop;
        $this->number_document = $number_document;
        $this->date_document = $date_document;
        $this->id_supplier = (int) $id_supplier;
        $this->tot_qty = 0;
        $this->tot_document_te = 0;
        $this->tot_document_taxes = 0;
        $this->tot_document_ti = 0;
        $this->date_add = $date_add;
        $result = $this->save();

        if ($result) {
            return true;
        } else {
            return Db::getInstance()->getMsgError();
        }
    }

    public static function parseFloat($value)
    {
        $number = preg_replace('/[^\d\,\.\-]/', '', $value);
        if (is_numeric($number)) {
            return (float) $number;
        } else {
            $swap = str_replace('.', '', $number);
            $swap = str_replace(',', '.', $swap);
            if (is_numeric($swap)) {
                return (float) $swap;
            } else {
                return 0;
            }
        }
    }

    public static function isEmpty()
    {
        $db = Db::getInstance();
        $sql = 'select count(*) from ' . _DB_PREFIX_ . self::$definition['table'];
        $count = (int) $db->getValue($sql);
        if ($count) {
            return false;
        } else {
            return true;
        }
    }

    public static function getPath()
    {
        return _PS_MODULE_DIR_ . 'mpstock/';
    }

    public static function getURL()
    {
        $shop = Context::getContext()->shop;
        $url = $shop->getBaseURI();

        return $url . 'modules/mpstock/';
    }

    public function getEmployee()
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('firstname')
            ->select('lastname')
            ->from('employee')
            ->where('id_employee = ' . (int) $this->context->employee->id);
        $row = $db->getRow($sql);
        if ($row) {
            return $row['firstname'] . ' ' . $row['lastname'];
        } else {
            return '';
        }
    }

    public static function getReasons($id_lang, $type = 'list')
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('id_mpstock_mvt_reason')
            ->select('name')
            ->from('mpstock_mvt_reason_lang')
            ->where('id_lang = ' . (int) $id_lang);
        $rows = $db->executeS($sql);
        if ($rows) {
            switch ($type) {
                case 'list':
                    $list = [];
                    foreach ($rows as $row) {
                        $list[$row['id_mpstock_mvt_reason']] = $row['name'];
                    }

                    return $list;
                case 'rows':
                    return $rows;
                case 'options':
                    $options = '';
                    foreach ($rows as $row) {
                        $options .= '<option value="' . $row['id_mpstock_mvt_reason'] . '">' . $row['name'] . '</option>';
                    }

                    return $options;
            }

            return $rows;
        } else {
            return [];
        }
    }

    public static function getMovementReasons($id_lang = null, $asSimpleArray = false, $sign = null)
    {
        if (!$id_lang) {
            $id_lang = Context::getContext()->language->id;
        }

        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('ml.id_mpstock_mvt_reason, ml.name')
            ->from(self::$definition['table'] . '_lang', 'ml')
            ->innerJoin(self::$definition['table'], 'm', 'm.' . self::$definition['primary'] . ' = ml.id_mpstock_mvt_reason')
            ->where('ml.id_lang=' . (int) $id_lang)
            ->orderBy(self::$definition['primary']);

        if ($sign) {
            $sql->where('m.sign=' . (int) $sign);
        }

        $rows = $db->executeS($sql);
        if ($rows) {
            if ($asSimpleArray) {
                $result = [];
                foreach ($rows as $row) {
                    $result[$row['id_mpstock_mvt_reason']] = $row['name'];
                }

                return $result;
            }

            return $rows;
        }

        return [];
    }

    public static function getReason($id_mpstock_mvt_reason, $id_lang)
    {
        $mov = new ModelMpStockMvtReason($id_mpstock_mvt_reason, $id_lang);
        if (!Validate::isLoadedObject($mov)) {
            return false;
        }

        return $mov;
    }
}
