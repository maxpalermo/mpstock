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

Class MpStockObjectModelTypeMovement extends ObjectModelCore
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
    
    public function __construct($id = null, $id_lang = null, $id_shop = null) {
        if (!$id_lang) {
            $this->id_lang = (int)ContextCore::getContext()->language->id;
        }
        if (!$id_shop) {
            $this->id_shop = (int)ContextCore::getContext()->shop->id;
        }
        $this->sign = 1;
        $this->exchange = 0;
        parent::__construct($id, $id_lang, $id_shop);
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('count(*)')
            ->from('mp_stock_type_movement')
            ->where('id_mp_stock_type_movement='.(int)$this->id);
        $this->record_exists = (bool)$db->getValue($sql);
        $this->errors = array();
        $this->module = new mpstock();
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

    public function getErrorMessage()
    {
        return implode(PHP_EOL, $this->errors);
    }

    public static function exists($id)
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('count(*)')
            ->from('mp_stock_type_movement')
            ->where('id_mp_stock_type_movement='.(int)$id);
        return (bool)$db->getValue($sql);
    }

    public function getArrayValues()
    {
        return array(
            'input_id_mp_stock_type_movement' => (int)$this->id_mp_stock_type_movement,
            'input_name' => $this->name,
            'input_sign' => (int)$this->sign,
            'input_exchange' => (int)$this->exchange,
        );
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
    
    public function save($null_values = false, $auto_date = true) {
        $this->id_mpstock_type_movement = $this->id;
        return parent::save($null_values, $auto_date);
    }
}
