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

Class MpStockMovementObjectModel extends ObjectModelCore
{
    public static $definition = array(
        'table' => 'mp_stock_type_movement',
        'primary' => 'id_mp_stock_type_movement',
        'multilang' => false,
        'fields' => array(
            'id_mp_stock_type_movement' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => 'true',
            ),
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
                'validate' => 'isName',
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
    
    public $id_mp_stock_type_movement;
    public $id_lang;
    public $id_shop;
    public $name;
    public $sign;
    public $exchange;
    public $record_exists;
    
    public function __construct($id = null, $id_lang = null, $id_shop = null) {
        if (!$id_lang) {
            $this->id_lang = (int)ContextCore::getContext()->language->id;
        }
        if (!$id_shop) {
            $this->id_shop = (int)ContextCore::getContext()->shop->id;
        }
        parent::__construct($id, $id_lang, $id_shop);
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('count(*)')
            ->from('mp_stock_type_movement')
            ->where('id_mp_stock_type_movement='.(int)$this->id);
        $this->record_exists = (bool)$db->getValue($sql);
    }
    
    public function getListMovements()
    {
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('*')
            ->from('mp_stock_type_movement')
            ->where('id_shop='.(int)$this->id_shop)
            ->orderBy('id_lang')
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
                    'flag' => $this->getFlag((int)$row['id_lang']),
                    'actions' => $this->addActions($id),
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
        return "<input type='checkbox' name='" . $name . "[]' value='" . $value . "'>"; 
    } 
    
    public function addSign($sign)
    {
        if ((int)$sign>0) {
            $icon = 'icon-plus';
            $color = '#629bbc;';
        } elseif((int)$sign<0) {
            $icon = 'icon-minus';
            $color = '#629bbc;';
        } else {
            $icon = 'icon-times';
            $color = '#db5e5e;';
        }
         return '<i class="' . $icon . '" style="color: ' . $color . '"></i>';
    }
    
    public function addExchange($exchange)
    {
        if ((int)$exchange) {
            $icon = 'icon-flag';
            $color = '#79e081;';
        } else {
            $icon = 'icon-times';
            $color = '#db5e5e;';
        }
         return '<i class="' . $icon . '" style="color: ' . $color . '"></i>';
    }
    
    public function addActions($id_movement)
    {
        $url = Context::getContext()->link->getAdminLink('AdminModules')
            . '&configure=mpstock&tab_module=administration&module_name=mpstock';
        $buttons = array(
            'edit' => array(
                'name' => 'btn-edit-movement[]',
                'title' => $this->l('Edit'),
                'icon' => 'icon-edit',
                'color' => '#79e081',
                'href' => $url . '&editMovement=' . $id_movement,
                'onclick' => 'editMovement(this)',
                'value' => $id_movement,
            ),
            'delete' => array(
                'name' => 'btn-delete-movement[]',
                'title' => $this->l('Delete'),
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
    
    public function getFlag($id_lang) {
        $shop = new ShopCore((int)$this->id_shop);
        $lang = new LanguageCore((int)$id_lang);
        $path =  $shop->physical_uri . 'img/l/' . $id_lang . '.jpg';
        $img = '<img alt="'. $lang->name[(int)$id_lang] . '" src="' . $path . '">';
        return $img;
    }
    
    public function save($null_values = false, $auto_date = true) {
        $this->id_mpstock_type_movement = $this->id;
        return parent::save($null_values, $auto_date);
    }
    
    /**
     * Non-static method which uses AdminController::translate()
     *
     * @param string  $string Term or expression in english
     * @param string|null $class Name of the class
     * @param bool $addslashes If set to true, the return value will pass through addslashes(). Otherwise, stripslashes().
     * @param bool $htmlentities If set to true(default), the return value will pass through htmlentities($string, ENT_QUOTES, 'utf-8')
     * @return string The translation if available, or the english default text.
     */
    protected function l($string, $class = null, $addslashes = false, $htmlentities = true)
    {
        if ($class === null || $class == 'AdminTab') {
            $class = Tools::substr(get_class($this), 0, -10);
        } elseif (Tools::strtolower(Tools::substr($class, -10)) == 'controller') {
            /* classname has changed, from AdminXXX to AdminXXXController, 
             * so we remove 10 characters and we keep same keys */
            $class = Tools::substr($class, 0, -10);
        }
        return Translate::getAdminTranslation($string, $class, $addslashes, $htmlentities);
    }
}
