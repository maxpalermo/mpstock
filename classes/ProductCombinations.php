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

Class MpStockProductCombinations
{
    private $context;
    private $id_lang;
    private $id_shop;
    private $id_product;
    private $id_product_attribute;
    private $smarty;
    private $module;
    private $combinations;
    private $movements;
   
    public function __construct($module, $id_product, $movements)
    {
        $this->context = Context::getContext();
        $this->smarty = $this->context->smarty;
        $this->id_lang = (int)$this->context->language->id;
        $this->id_shop = (int)$this->context->shop->id;
        $this->id_product = (int)$id_product;
        $this->id_product_attribute= 0;
        $this->module = $module;
        $this->movements = $movements;
    }
    
    public function display()
    {
        $this->getCombinations($this->id_product);
        $rows = $this->prepareRows();
        $this->smarty->assign(
            array(
                'id_product' => $this->id_product,
                'mpstock_rows' => $rows,
            )
        );
        
        $form_path = $this->module->getPath().'/views/templates/admin/AdminMpStockTableCombinations.tpl';
        $form = $this->smarty->fetch($form_path);
        
        return $form;
    }
    
    public function prepareRows()
    {
        $db = Db::getInstance();
        $rows = array();
        foreach ($this->combinations as $combination) {
            $sql = new DbQueryCore();
            $sql->select('*')
                ->from('product_attribute')
                ->where('id_product_attribute='.(int)$combination['id']);
            $result = $db->executeS($sql);
            if ($result) {
                foreach ($result as $row) {
                    $out = array(
                        'id_product_attribute' => $combination['id'],
                        'select_movement' => $this->getSelectMovement($this->movements),
                        'name' => $combination['value'],
                        'input_reference' => $this->getInput('input_reference[]', $row['reference']),
                        'input_ean13' => $this->getInput('input_ean13[]', $row['ean13']),
                        'input_qty' => $this->getInput('input_qty[]', 0, 'right', 'sm'),
                        'input_price' => $this->getInput('input_price[]', Tools::displayPrice($row['price']), 'right', 'sm'),
                        'input_tax_rate' => $this->getInput('input_tax_rate[]', 0, 'right', 'sm'),
                    );
                }
                $rows[] = $out;
            }
        }
        return $rows;
    }
    
    public function getInput($name, $value, $align='left', $size='md' )
    {
        $this->smarty->assign(
            array(
                'input_name' => $name,
                'input_value' => $value,
                'input_text_align' => $align,
                'input_text_size' => $size,
            )
        );
        $path = $this->module->getPath().'views/templates/admin/AdminMpStockInputGeneric.tpl';
        return $this->smarty->fetch($path);
    }
    
    public function getSelectMovement($movements)
    {
        $this->smarty->assign(
            array(
                'select_stock_movements' => $movements
            )
        );
        $path = $this->module->getPath().'views/templates/admin/AdminMpStockSelectMovement.tpl';
        return $this->smarty->fetch($path);
    }
    
    public function getCombinations($id_product)
    {
        $product = new ProductCore($id_product);
        $combinations = $product->getAttributeCombinations($this->id_lang);
        $this->combinations = array();
        $id_product_attribute = null;
        $names = array();
        foreach ($combinations as $combination) {
            if (is_null($id_product_attribute)) {
                $id_product_attribute = $combination['id_product_attribute'];
                $names = array();
                $names[] = $combination['attribute_name'];
                continue;
            }
            if ($id_product_attribute == $combination['id_product_attribute']) {
                $names[] = $combination['attribute_name'];
            } else {
                $this->combinations[] = array(
                    'id' => $id_product_attribute,
                    'value' => implode(' - ', $names),
                );
                $names = array();
                $id_product_attribute = $combination['id_product_attribute'];
                $names[] = $combination['attribute_name'];
            }   
        }
        $this->combinations[] = array(
            'id' => $id_product_attribute,
            'value' => implode(' - ', $names),
        );
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
            $class = substr(get_class($this), 0, -10);
        } elseif (strtolower(substr($class, -10)) == 'controller') {
            /* classname has changed, from AdminXXX to AdminXXXController, so we remove 10 characters and we keep same keys */
            $class = substr($class, 0, -10);
        }
        return Translate::getAdminTranslation($string, $class, $addslashes, $htmlentities);
    }
}