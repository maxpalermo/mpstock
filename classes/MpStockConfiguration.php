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

require_once _PS_MODULE_DIR_.'mpstock/mpstock.php';

class MpStockConfiguration extends ObjectModel
{
    public static $definition = array(
        'table' => 'mp_stock_type_movement',
        'primary' => 'id_mp_stock_type_movement',
        'multilang' => false,
        'fields' => array(
            'id_lang' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => 'true',
            ),
            'id_shop' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => 'true',
            ),
            'name' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isString',
                'required' => 'true',
            ),
            'sign' => array(
                'type' => self::TYPE_STRING,
                'validate' => 'isInt',
                'required' => 'true',
            ),
            'exchange' => array(
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => 'true',
            ),
        ),
    );
    
    /** @var int $id_mp_stock_type_movement **/
    public $id_mp_stock_type_movement;
    /** @var int $id_lang **/
    public $id_lang;
    /** @var int $id_shop **/
    public $id_shop;
    /** @var string $name **/
    public $name;
    /** @var int $sign **/
    public $sign;
    /** @var int $exchange **/
    public $exchange;
    /** @var boolen $record_exists **/
    public $record_exists;
    /** @var array errors Error Messages **/
    private $errors;
    /** @var MpStock module MpStock Module*/
    private $module;

    public function __construct($id =0, $id_lang = 0, $id_shop = 0)
    {
        $this->module = Context::getContext()->controller->module;
        if (!$id_lang) {
            $this->id_lang = Context::getContext()->language->id;
        }
        if (!$id_shop) {
            $this->id_shop = Context::getContext()->shop->id;
        }
        parent::__construct($id);
    }

    public static function installSQL()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."mp_stock_type_movement` (
            `id_mp_stock_type_movement` int(11) NOT NULL AUTO_INCREMENT,
            `id_lang` int(11) NOT NULL,
            `id_shop` int(11) NOT NULL,
            `name` varchar(255) NOT NULL,
            `sign` enum('-1','1') NOT NULL DEFAULT '1',
            `exchange` boolean NOT NULL,
            PRIMARY KEY  (`id_mp_stock_type_movement`)
        ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8;";
        $result = Db::getInstance()->execute($sql);
        if ($result) {
            return true;
        } else {
            $this->module->addError(Db::getInstance()->getMsgError());
            return false;
        }
    }

    public function getListMovements()
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('*')
            ->from('mp_stock_type_movement')
            ->where('id_shop='.(int)$this->id_shop)
            ->where('id_lang='.(int)$this->id_lang)
            ->orderBy('name');
        $result = $db->executeS($sql);
        $output = array();
        if ($result) {
            foreach ($result as $row) {
                $id = (int)$row['id_mp_stock_type_movement'];
                $line = array(
                    'id_mp_stock_type_movement' => $id,
                    'id_lang' => (int)$row['id_lang'],
                    'id_shop' => (int)$row['id_shop'],
                    'name' => $row['name'],
                    'sign' => $this->addSign($row['sign']),
                    'check' => $this->addCheckBox('checkSelect[]', $id),
                    'flag' => $this->addFlag((int)$row['id_lang']),
                    'actions' => 
                        MpStockTools::getHtmlLinkButton('editMovement[]', 'icon icon-pencil', 'javascript:void(0);', '#3030AA')
                    .   MpStockTools::getHtmlLinkButton('deleteMovement[]', 'icon icon-times', 'javascript:void(0);', '#BB4040'),
                    'exchange' => $this->addExchange($row['exchange']),
                );
                $output[] = $line;
            }
            return $output;
        } else {
            return array();
        }
    }
    
    public function addCheckBox($name, $value)
    {
        $template = $this->module->getAdminTemplatePath().'html_element_icon.tpl';
        $smarty = Context::getContext()->smarty;
        $smarty->assign(
            array(
                'name' => $name.$value,
                'icon' => 'icon-caret-right',
                'color' => '#7070BB',
                'title' => ''
            )
        );

        return $smarty->fetch($template);
    } 
    
    public function addSign($sign)
    {
        $template = $this->module->getAdminTemplatePath().'html_element_icon.tpl';
        $smarty = Context::getContext()->smarty;
        $smarty->assign(
            array(
                'name' => '',
                'icon' => (int)$sign>0?'icon-plus-circle':'icon-minus-circle',
                'color' => (int)$sign>0?'#db5e5e':'#629bbc',
                'title' => ''
            )
        );

        return $smarty->fetch($template);
    }

    public function addFlag($id_lang)
    {
        $shop = new ShopCore((int)$this->id_shop);
        $path =  $shop->physical_uri . 'img/l/' . $id_lang . '.jpg';
        $template = $this->module->getAdminTemplatePath().'html_element_img.tpl';
        $smarty = Context::getContext()->smarty;
        $smarty->assign(
            array(
                'image' => array(
                    'source' => $path,
                    'width' => 0,
                    'height' => 0,
                ),
            )
        );

        return $smarty->fetch($template);
    }
    
    public function addExchange($exchange)
    {
        $template = $this->module->getAdminTemplatePath().'html_element_icon.tpl';
        $smarty = Context::getContext()->smarty;
        $smarty->assign(
            array(
                'name' => '',
                'icon' => (int)$exchange>0?'icon-check':'',
                'color' => (int)$exchange>0?'#70BB70':'',
                'title' => ''
            )
        );

        return $smarty->fetch($template);
    }
    
    public function addActions($id_movement)
    {
        $url = Context::getContext()->link->getAdminLink('AdminModules')
            . '&configure=mpstock&tab_module=administration&module_name=mpstock';
        $buttons = array(
            'edit' => array(
                'name' => 'btn-edit-movement[]',
                'title' => $this->module->l('Edit', get_class($this)),
                'icon' => 'icon-edit',
                'color' => '#79e081',
                'href' => $url . '&editMovement=' . $id_movement,
                'onclick' => 'editMovement(this)',
                'value' => $id_movement,
            ),
            'delete' => array(
                'name' => 'btn-delete-movement[]',
                'title' => $this->module->l('Delete', get_class($this)),
                'icon' => 'icon-times',
                'color' => '#db5e5e',
                'href' => $url . '&deleteMovement=' . $id_movement,
                'onclick' => 'editMovement(this)',
                'value' => $id_movement,
            ),
        );
        $output = array();
        foreach ($buttons as $button)
        {
            $color = isset($button['color'])?' style="color: '. $button['color'] . ';"':'';
            $href = isset($button['href'])?' href="' . $button['href'] . '"':'';
            $output[] = '<a'
                . $href
                . ' value="' . $button['value'] 
                . '" name="' . $button['name'] 
                . '" class="btn btn-default">'
                . '<i class="icon ' . $button['icon'] . '"'
                . $color
                . '"></i> '
                . $button['title']
                . '</button>';
        }
        return implode('', $output);
    }

    public function delete()
    {
        /** check if there are movements with this type **/
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('count(*)')
            ->from('mp_stock')
            ->where('id_mp_stock_type_movement='.(int)$this->id);
        $result = (int)$db->getValue($sql);
        if ($result) {
            $this->errors[] = sprintf(
                $this->module->l('Unable to delete this movement type.', get_class($this))
                .$this->module->l('There are still %d stock movement(s) associated.', get_class($this)),
                $result
            );
            return false;
        }
        return parent::delete();
    }
    









    public $context;
    public $values;
    public $id_lang;
    public $module;
    public $link;
    protected $cookie;
    protected $className = 'AdminMpStock';
    protected $localeInfo;
    protected $table_name = 'mp_stock_import';
    
    public function _construct($module)
    {
        $this->module = $module;
        $this->context = Context::getContext();
        $this->link = new LinkCore();
        $this->values = array();
        $this->id_lang = (int)$this->context->language->id;
        $this->id_shop = (int)$this->context->shop->id;
        parent::__construct();
        $this->cookie = Context::getContext()->cookie;
        $this->localeInfo = MpStockTools::getLocaleInfo();
    }
    
    public function display()
    {
        $this->bootstrap = true;
        $this->currentIndex = "#";
        $this->identifier = '';
        $this->no_link = true;
        $this->page = Tools::getValue('submitFilterconfiguration', 1);
        $this->_default_pagination = Tools::getValue('configuration_pagination', 20);
        $this->show_toolbar = true;
        $this->toolbar_btn = array(
            'new' => array(
                'href' => $this->link->getAdminLink('AdminModules')
                    .'&configure=mpstock'
                    .'&module_name=mpstock'
                    .'&tab_module=administration'
                    .'&submitNewMovement',
                'desc' => $this->l('New movement'),
            ),
            'back' => array(
                'desc' => $this->module->l('Go to Stock Movements', get_class($this)),
                'href' => $this->module->link->getAdminlink('AdminMpStock'),
            ),
        );
        $this->shopLinkType='';
        $this->simple_header = false;
        $this->token = Tools::getAdminTokenLite('AdminModules');
        $this->title = $this->module->l('Documents found', get_class($this));
        $this->table = 'mp_stock_type_movement';
        
        $this->mpMovement = new MpStockObjectModelTypeMovement();
        $list = $this->mpMovement->getListMovements();
        $this->listTotal = count($list);
        $fields_display = $this->getFields();
        
        return $this->generateList($list, $fields_display).$this->getScript();
    }
    
    private function getScript()
    {
        $smarty = Context::getContext()->smarty;
        $smarty->assign(
            array(
                'module_url' => $this->module->link->getAdminlink('AdminModules')
                    .'&tab_module=administration'
                    .'&module_name=mpstock'
                    .'&configure=mpstock',
            )
        );
        return $smarty->fetch($this->module->getAdminTemplatePath().'helper_list_type_movement.tpl');
    }

    protected function getFields()
    {
        $list = array();
        MpStockTools::addHtml(
            $list,
            '',
            'check',
            '32',
            'text-center'
        );
        MpStockTools::addText(
            $list,
            $this->module->l('Id', get_class($this)),
            'id_mp_stock_type_movement',
            '48',
            'text-right'
        );
        MpStockTools::addHtml(
            $list,
            $this->module->l('Language', get_class($this)),
            'flag',
            '28',
            'text-center'
        );
        MpStockTools::addText(
            $list,
            $this->module->l('Name', get_class($this)),
            'name',
            'auto',
            'text-left'
        );
        MpStockTools::addHtml(
            $list,
            $this->module->l('Sign', get_class($this)),
            'sign',
            '32',
            'text-center'
        );
        MpStockTools::addHtml(
            $list,
            $this->module->l('Exchange', get_class($this)),
            'exchange',
            '32',
            'text-center'
        );
        MpStockTools::addHtml(
            $list,
            $this->module->l('Actions', get_class($this)),
            'actions',
            'auto',
            'text-center'
        );
        
        return $list;
    }
}
