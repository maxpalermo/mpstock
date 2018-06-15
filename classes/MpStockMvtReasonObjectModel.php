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

class MpStockMvtReasonObjectModel extends ObjectModel
{
    public $id;
    public $id_lang;
    public $id_shop;
    public $id_stock_mvt_reason;
    public $sign;
    public $date_add;
    public $date_upd;
    public $deleted;
    public $transform;
    public $name;
    public $module;

    public static $definition = array(
        'table' => 'mpstock_mvt_reason',
        'primary' => 'id_mpstock_mvt_reason',
        'multilang' => true,
        'fields' => array(
            'sign' => array(
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => true,
            ),
            'date_add' => array(
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'datetime' => true,
                'required' => true,
            ),
            'date_upd' => array(
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'timestamp' => true,
                'required' => false,
            ),
            'deleted' => array(
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => true,
            ),
            'transform' => array(
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => true,
            ),
            'name' => array(
                'lang' => true,
                'type' => self::TYPE_STRING,
                'size' => 255,
                'validate' => 'isString',
                'required' => true,
            ),

        ),
    );

    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
        if (!$id_shop) {
            $this->id_shop = (int)Context::getContext()->shop->id;
        } else {
            $this->id_shop = (int)$id_shop;
        }
        if (!$id_lang) {
            $this->id_lang = Context::getContext()->language->id;
        } else {
            $this->id_lang = (int)$id_lang;
        }
        parent::__construct($id, $this->id_lang, $this->id_shop);
        $this->context = Context::getContext();
        $this->smarty = $this->context->smarty;
        $this->module = $this->context->controller->module;
    }

    public function delete()
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('count(*)')
            ->from('mpstock_mvt')
            ->where('id_mpstock_mvt_reason='.(int)$this->id);
        $count = (int)$db->getValue($sql);
        if ($count) {
            return false;
        }
        return parent::delete();
    }

    public static function dropTable()
    {
        Db::getInstance()->execute('DROP TABLE '._DB_PREFIX_.self::$definition['table']);
    }
    
    public function saveValues()
    {
        $shop = new Shop((int)$this->id_shop);
        $number_document = Tools::getValue('number_document');
        $date_document = Tools::getValue('date_document');
        $id_supplier = (int)Tools::getValue('id_supplier');
        $date_add = date('Y-m-d H:i:s');

        if (!$number_document) {
            return $this->l('Please select a valid number document');
        }
        if (!$date_document) {
            return $this->l('Please select a valid date document');
        }
        if (!$id_supplier) {
            return $this->l('Please select a valid supplier');
        }

        $this->id_shop = (int)$id_shop;
        $this->number_document = $number_document;
        $this->date_document = $date_document;
        $this->id_supplier = (int)$id_supplier;
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
            return (float)$number;
        } else {
            $swap = str_replace('.', '', $number);
            $swap = str_replace(',', '.', $swap);
            if (is_numeric($swap)) {
                return (float)$swap;
            } else {
                return 0;
            }
        }
    }
    
    public static function isEmpty()
    {
        $db = Db::getInstance();
        $sql = "select count(*) from "._DB_PREFIX_.self::$definition['table'];
        $count = (int)$db->getValue($sql);
        if ($count) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Get table definition and create a table in the database
     * @param  bool $execute_query Set the return type
     * @return mixed if $execute_query is true returns the query result, 
     *         otherwise returns the query string 
     */
    public static function install($execute_query = true)
    {
        $db = Db::getInstance();
        $sql = array();
        $primary = self::$definition['primary'];
        $sql_start = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_.self::$definition['table']."` (";
        $sql['primary'] =  "`".$primary."` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY";
        foreach (self::$definition['fields'] as $key=>$field) {
            if ($key == $primary) {
                continue;
            }
            if ($field['required']) {
                $null = " NOT NULL";
            } else {
                $null = " NULL";
            }
            switch ($field['type']) {
                case self::TYPE_INT:
                    $sql[$key] = "`".$key."` INT(11)".$null;
                    break;
                case self::TYPE_BOOL:
                    $sql[$key] = "`".$key."` TINYINT(1)".$null;
                    break;
                case self::TYPE_FLOAT:
                    if ($field['decimal']) {
                        $sql[$key] = "`".$key."` DECIMAL(".$field['size'].")".$null;
                    } else {
                        $sql[$key] = "`".$key."` FLOAT".$null;
                    }
                    break;
                case self::TYPE_STRING:
                    if($field['text']) {
                        $sql[$key] = "`".$key."` TEXT".$null;
                    } else {
                        $sql[$key] = "`".$key."` VARCHAR(".$field['size'].")".$null;    
                    }
                    break;
                case self::TYPE_DATE:
                    if($field['datetime']) {
                        $sql[$key] = "`".$key."` DATETIME".$null;
                    } elseif($field['timestamp']) {
                        $sql[$key] = "`".$key."` TIMESTAMP".$null;    
                    } else {
                        $sql[$key] = "`".$key."` DATE".$null;    
                    }
                    break;
                default:
                    break;
            }
        }
        $sql_end = ") ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8;";

        $query = $sql_start.implode(",", $sql).$sql_end;

        if (self::$definition['multilang']) {
            $query_multilang = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_.self::$definition['table']."_lang` ("
                .$sql['primary'].
                ', id_lang INT(11) NOT NULL';
            foreach (self::$definition['fields'] as $key=>$field) {
                if (isset($field['lang']) && $field['lang']) {
                    $field_row = ", `".$key."` VARCHAR(".$field['size'].") NOT NULL";
                    $query_multilang .= $field_row;
                }
            }
            $query_multilang .= $sql_end;
        } else {
            $query_multilang = '';
        }

        PrestaShopLoggerCore::addLog("multilang:".$query_multilang);

        if ($execute_query) {
            return Db::getInstance()->execute($query.$query_multilang);
        } else {
            return $query.$query_multilang;
        }
    }

    public static function alterTable()
    {
        if (!self::existsColumn('stock_mvt_reason', 'transform')) {
            $sql = "ALTER TABLE `"._DB_PREFIX_."stock_mvt_reason` ADD `transform` TINYINT NOT NULL;";
            return Db::getInstance()->execute($sql);
        }
    }

    public static function existsColumn($table, $column)
    {
        $sql = "SELECT count(*) "
            ."FROM information_schema.COLUMNS "
            ."WHERE "
            ."TABLE_SCHEMA = '"._DB_NAME_."' "
            ."AND TABLE_NAME = '"._DB_PREFIX_.$table."' "
            ."AND COLUMN_NAME = '".$column."';";
        return (int)Db::getInstance()->getValue($sql);
    }

    public static function getPath()
    {
        return _PS_MODULE_DIR_.'mpstock/';
    }

    public static function getURL()
    {
        $shop = new Shop(Context::getContext()->shop->id);
        $url = $shop->getBaseURI();
        return $url.'modules/mpstock/';
    }

    public function getEmployee()
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('firstname')
            ->select('lastname')
            ->from('employee')
            ->where('id_employee = ' . (int)$this->id_employee);
        $row = $db->getRow($sql);
        if ($row) {
            return $row['firstname'] . ' ' . $row['lastname'];
        } else {
            return "";
        }
    }
}
