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

Class MpStockTools
{
    const TYPE_BUTTON = 'button';
    const TYPE_TEXT = 'text';
    const TYPE_PRICE = 'price';
    const TYPE_INT = 'int';
    const TYPE_DATE = 'date';
    const TYPE_PERCENTAGE = 'percentage';
    const TYPE_IMAGE = 'image';
    const TYPE_HTML = 'html';
    const OBJECT_FIT_FILL = 'fill';
    const OBJECT_FIT_CONTAIN = 'contain';
    const OBJECT_FIT_COVER = 'cover';
    const OBJECT_FIT_NONE = 'none';
    const OBJECT_FIT_SCALE_DOWN = 'scale-down';
    
    /**
     * Get the default Template Path
     * @param  string templatePath
     * @return string templatePath, if empty returns the default path
     */
    public static function getDefaultTemplatePath($templatePath)
    {
        /** Get Template path if not set **/
        if (empty($templatePath)) {
            return _PS_MODULE_DIR_.'mpstock/views/templates/admin/';
        } else {
            return $templatePath;
        }
    }

    /**
     * Get Locale float values
     * @return array Array of decimal locale values ['decimal_point', 'thousands_sep']
     */
    public static function getLocaleInfo()
    {
        if (Context::getContext()->language->iso_code == 'it') {
            return array(
                'decimal_point' => ',',
                'thousands_sep' => '.'
            );
        } else {
            return array(
                'decimal_point' => '.',
                'thousands_sep' => ','
            );
        }
    }

    /**
     * Display an input element for price values
     * @param  float value Price value
     * @param  string name Name of the element
     * @param  string templatePath Optional Template path
     * @return string HTML Template Input element
     */
    public static function getHtmlPriceTextElement($value, $name, $templatePath = '')
    {
        $templatePath = self::getDefaultTemplatePath($templatePath);
        $smarty = Context::getContext()->smarty;
        $smarty->assign(
            array(
                'name' => $name,
                'id' => '',
                'class' => 'input text-right fixed-width-sm input-float',
                'value' => Tools::displayPrice($value),
            )
        );
        $input = $smarty->fetch($templatePath.'html_element_text.tpl');
        return $input;
    }

    /**
     * Display an input element for percentage values
     * @param  float $value Percentage value
     * @param  string $name The name of the text element
     * @param  string $templatePath Template path
     * @return string HTML Template input element
     */
    public static function getHtmlPercentTextElement($value, $name, $templatePath = '')
    {
        $templatePath = self::getDefaultTemplatePath($templatePath);
        $percentage = self::displayTaxRate($value);
        $smarty = Context::getContext()->smarty;
        $smarty->assign(
            array(
                'name' => $name,
                'id' => '',
                'class' => 'input text-right fixed-width-sm input-float',
                'value' => $percentage
            )
        );
        $input = $smarty->fetch($templatePath.'html_element_text.tpl');
        return $input;
    }

    /**
     * Display an input element for quantity values
     * @param  int $value Quantity value
     * @param  string $name Optional Name of the element
     * @param  string $templatePath Optional Template Path
     * @return string HTML Template input element
     */
    public static function getHtmlQuantityTextElement($value, $name = '', $templatePath = '')
    {
        $templatePath = self::getDefaultTemplatePath($templatePath);
        if (empty($name)) {
            $name = 'input_text_qty[]';
        }
        $smarty = Context::getContext()->smarty;
        $smarty->assign(
            array(
                'name' => $name,
                'id' => '',
                'class' => 'input text-right fixed-width-sm input-integer',
                'value' => $value,
                'color' => $value<0?'#BB6060':'#555555',
            )
        );
        $input = $smarty->fetch($templatePath.'html_element_text.tpl');
        return $input;
    }

    public static function getHtmlButtonCallBack($name, $icon, $callback='javascript:void(0);', $color='', $title='', $templatePath='')
    {
        $templatePath = self::getDefaultTemplatePath($templatePath);
        
        Context::getContext()->smarty->assign(
            array(
                'name' => $name,
                'icon' => $icon,
                'color' => $color,
                'title' => $title,
                'callback'=> $callback
            )
        );
        return Context::getContext()->smarty->fetch($templatePath.'html_element_button.tpl');
    }
    
    /**
     * Get HTML Template icon element
     * @param string $name Title of column, can be empty
     * @param string $icon Icon code [ex: icon-times, icon-pencil...]
     * @param string $color Color of icon
     * @param string $title Display title of icon
     * @return string HTML Template of icon element
     */
    public static function getHtmlIcon($name, $icon, $color = '', $title = '', $templatePath = '')
    {
        $templatePath = self::getDefaultTemplatePath($templatePath);
        
        Context::getContext()->smarty->assign(
            array(
                'name' => $name,
                'icon' => $icon,
                'color' => $color,
                'title' => $title,
            )
        );
        return Context::getContext()->smarty->fetch($templatePath.'html_element_icon.tpl');
    }
    
    /**
     * Get HTML Template of a default button with href
     * @param string $name
     * @param string $icon
     * @param string $href
     * @param string $target
     * @param string $color
     * @param string $title
     * @param string $templatePath
     * @return string HTML Template of a default button
     */
    public static function getHtmlHrefButton($name, $icon, $href='#', $target='_blank', $color='', $title='', $templatePath='')
    {
        $templatePath = self::getDefaultTemplatePath($templatePath);
        
        Context::getContext()->smarty->assign(
            array(
                'name' => $name,
                'icon' => $icon,
                'href' => $href,
                'target' => $target,
                'color' => $color,
                'title' => $title,
            )
        );
        return Context::getContext()->smarty->fetch($templatePath.'html_element_button_href.tpl');
    }
    
    /**
     * Get HTML Template list of select element options
     * @param array $list [value, name]
     * @param string $templatePath The admintemplate path
     * @return string HTML options list
     */
    public static function getOptionsCombination($list, $templatePath = '')
    {
        $templatePath = self::getDefaultTemplatePath($templatePath);
        
        Context::getContext()->smarty->assign(
            array(
                'rows' => $list,
            )
        );
        return Context::getContext()->smarty->fetch($templatePath.'html_element_options.tpl');
    }
    
    /**
     * Retirn HTML Template of product image
     * @param int $id_product id product
     * @param string $templatePath optional template path
     * @return string HTML template of image product
     */
    public static function getImageProduct($id_product, $templatePath = '')
    {
        $templatePath = self::getDefaultTemplatePath($templatePath);
        
        $id_shop = (int)Context::getContext()->shop->id;
        $shop = new ShopCore($id_shop);
        if ((int)$id_product == 0) {
            PrestaShopLoggerCore::addLog('Invalid id product for image display.');
            return $shop->getBaseURL(true) . 'img/404.gif';
        }
        $db = Db::getInstance();
        $sql = new DbQueryCore();
        $sql->select('id_image')
            ->from('image')
            ->where('id_product='.(int)$id_product)
            ->where('cover IS NOT NULL');
        
        $id_image = (int)$db->getValue($sql);
        if ((int)$id_image==0) {
            /** Image not found, display default 404.gif **/
            $image_path = $shop->getBaseURL(true) . 'img/404.gif';
        } else {
            $imageObj = new ImageCore($id_image);
            $image_path = $shop->getBaseURL(true) . 'img/p/'. $imageObj->getExistingImgPath() . '-small.jpg';
        }
        $image = array(
            'source' => $image_path,
            'width' => '48px',
        );
        $smarty = Context::getContext()->smarty;
        $smarty->assign('image', $image);
        return $smarty->fetch($templatePath.'html_element_img.tpl');
    }
    
    /**
     * Capitalize first letter of every word
     * @param string $str The string to be transformed
     * @return string The string processed
     */
    public static function ucFirst($str)
    {
        $str_lower = Tools::strtolower($str);
        $parts = explode(' ', $str_lower);
        foreach ($parts as &$part) {
            $part = Tools::ucfirst($part);
        }
        return implode(' ', $parts);
    }
    
    /**
     * Add a text element in table row
     * @param array $list byRef Collections of elements
     * @param string $title Title of column
     * @param string $key Key of column
     * @param string $width Width of column
     * @param string $alignment Text alignment of column [text-left. text-center, text-right]
     * @param boolean $search If true, a search field will be shown in table
     */
    public static function addText(&$list, $title, $key, $width, $alignment, $search = false)
    {
        $item = array(
            'title' => $title,
            'width' => $width,
            'align' => $alignment,
            'type' => 'text',
            'search' => $search,
        );

        $list[$key] = $item;
    }
    
    /**
     * Add a Date element in table row
     * @param array $list byRef Collections of elements
     * @param string $title Title of column
     * @param string $key Key of column
     * @param string $width Width of column
     * @param string $alignment Text alignment of column [text-left. text-center, text-right]
     * @param boolean $search If true, a search field will be shown in table
     */
    public static function addDate(&$list, $title, $key, $width, $alignment, $search = false)
    {
        $item = array(
            'title' => $title,
            'width' => $width,
            'align' => $alignment,
            'type' => 'date',
            'search' => $search,
        );

        $list[$key] = $item;
    }

    /**
     * Add a price element in table row
     * @param array $list byRef Collections of elements
     * @param string $title Title of column
     * @param string $key Key of column
     * @param string $width Width of column
     * @param string $alignment Text alignment of column [text-left. text-center, text-right]
     * @param boolean $search If true, a search field will be shown in table
     */
    public static function addPrice(&$list, $title, $key, $width, $alignment, $search = false)
    {
        $item = array(
            'title' => $title,
            'width' => $width,
            'align' => $alignment,
            'type' => 'price',
            'search' => $search,
        );

        $list[$key] = $item;
    }

    /**
     * Add an HTML element in table row
     * @param array $list byRef Collections of elements
     * @param string $title Title of column
     * @param string $key Key of column
     * @param string $width Width of column
     * @param string $alignment Text alignment of column [text-left. text-center, text-right]
     * @param boolean $search If true, a search field will be shown in table
     */
    public static function addHtml(&$list, $title, $key, $width, $alignment, $search = false)
    {
        $item = array(
            'title' => $title,
            'width' => $width,
            'align' => $alignment,
            'type' => 'bool',
            'float' => true,
            'search' => $search,
        );

        $list[$key] = $item;
    }

    /**
     * Display a float number into tax rate with percent symbol
     * @param  float Tax rate value
     * @return string Tax rate formatted value
     */
    public static function displayTaxRate($value)
    {
        $localeInfo = self::getLocaleInfo();
        $output =  number_format(
            $value,
            2,
            $localeInfo['decimal_point'],
            $localeInfo['thousands_sep']
        ) . ' %';

        return $output;
    }

    /**
     * Display a formatted price value
     * @param  int quantity value
     * @param  string optional templatePath
     * @return string HTML span element with formatted price
     */
    public static function displayPrice($value, $templatePath = '')
    {
        $templatePath = self::getDefaultTemplatePath($templatePath);

        $smarty = Context::getContext()->smarty;
        $smarty->assign(
            array(
                'style' => array(
                    'color' => '#555555',
                    'font-weight' => 'lighter',
                ),
                'value' => Tools::displayPrice($value),
            )
        );

        return $smarty->fetch($templatePath.'html_element_span.tpl');
    }

    /**
     * Display a quantity value into colored one
     * @param  int quantity value
     * @param  string optional templatePath
     * @return string HTML span element with formatted quantity
     */
    public static function displayQuantity($value, $templatePath = '')
    {
        $templatePath = self::getDefaultTemplatePath($templatePath);

        $smarty = Context::getContext()->smarty;
        if ($value>0) {
            $smarty->assign(
                array(
                    'style' => array(
                        'color' => '#50BB50',
                        'font-weight' => 'bold',
                    ),
                    'value' => $value,
                )
            );
        } else {
            $smarty->assign(
                array(
                    'style' => array(
                        'color' => '#BB5050',
                        'font-weight' => 'bold',
                    ),
                    'value' => $value,
                )
            );
        }

        return $smarty->fetch($templatePath.'html_element_span.tpl');
    }

    /**
     * Get all combinations of a specified product
     * @param type $id_product product id to search
     * @return array Array of combinations
     * ['id_product_attribute', 'reference', 'name', 'ean13', 'price', 'wholesale_price', 'tax_rate']
     */
    public static function getCombinations($id_product)
    {
        $db = Db::getInstance();
        /** Get id_product_attribute of specified id_product **/
        $sql_product_attribute = new DbQueryCore();
        $sql_product_attribute->select('id_product_attribute')
            ->select('reference')
            ->select('ean13')
            ->select('price')
            ->select('quantity')
            ->select('wholesale_price')
            ->select('quantity as stock')
            ->from('product_attribute')
            ->where('id_product='.(int)$id_product);
        $result_product_attribute = $db->executeS($sql_product_attribute);
        if (!$result_product_attribute) {
            return array();
        }
        $combinations = array();
        $tax_rate = self::getTaxRateFromIdProduct($id_product);
        foreach ($result_product_attribute as $row) {
            $sql_combination = new DbQueryCore();
            $sql_combination->select('distinct a.id_attribute')
                ->select('al.name')
                ->from('attribute', 'a')
                ->innerJoin('attribute_lang', 'al', 'al.id_attribute=a.id_attribute')
                ->innerJoin('attribute_group', 'ag', 'ag.id_attribute_group=a.id_attribute_group')
                ->innerJoin('product_attribute_combination', 'pac', 'pac.id_attribute=a.id_attribute')
                ->where('al.id_lang='.(int) Context::getContext()->language->id)
                ->where('pac.id_product_attribute='.(int)$row['id_product_attribute'])
                ->orderBy('ag.position')
                ->orderBy('al.name');
            $result_combination = $db->executeS($sql_combination);
            $name_combination = array();
            if ($result_combination) {
                foreach ($result_combination as $attribute) {
                    $name_combination[] = $attribute['name'];
                }
                $combination = implode(' - ', $name_combination);
            }
            $combinations[] = array(
                'id_product_attribute' => $row['id_product_attribute'],
                'reference' => $row['reference'],
                'ean13' => $row['ean13'],
                'wholesale_price' => $row['wholesale_price'],
                'price' => $row['price'],
                'tax_rate' => $tax_rate,
                'name' => $combination,
                'stock' => $row['stock'],
            );
        }
        usort($combinations, function($a, $b) {
            $a = $a['name'];
            $b = $b['name'];

            if ($a == $b) return 0;
            return ($a < $b) ? -1 : 1;
        });
        return $combinations;
    }

    public static function getTaxRateFromIdProduct($id_product)
    {
        if (!$id_product) {
            return 0;
        }
        $db = Db::getInstance();
        $sql_tax_group = new DbQueryCore();
        $sql_tax_group->select('id_tax_rules_group')
            ->from('product')
            ->where('id_product='.(int)$id_product);
        $id_tax_rules_group = (int)$db->getValue($sql_tax_group);

        $sql = new DbQueryCore();
        $sql->select('t.rate')
            ->from('tax', 't')
            ->innerJoin('tax_rule', 'tr', 'tr.id_tax=t.id_tax')
            ->where('tr.id_tax_rules_group='.(int)$id_tax_rules_group);
        $tax_rate = $db->getValue($sql);
        return (float)$tax_rate;
    }

}
