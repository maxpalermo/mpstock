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
    
    public static function getHtmlButtonCallBack($name, $icon, $callback='javascript:void(0);', $color='', $title='', $templatePath='')
    {
        /** Get Template path if not set **/
        if (empty($templatePath)) {
            $templatePath = _PS_MODULE_DIR_.'mpstock/views/templates/admin/';
        }
        
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
        /** Get Template path if not set **/
        if (empty($templatePath)) {
            $templatePath = _PS_MODULE_DIR_.'mpstock/views/templates/admin/';
        }
        
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
        /** Get Template path if not set **/
        if (empty($templatePath)) {
            $templatePath = _PS_MODULE_DIR_.'mpstock/views/templates/admin/';
        }
        
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
        /** Get Template path if not set **/
        if (empty($templatePath)) {
            $templatePath = _PS_MODULE_DIR_.'mpstock/views/templates/admin/';
        }
        
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
        /** Get Template path if not set **/
        if (empty($templatePath)) {
            $templatePath = _PS_MODULE_DIR_.'mpstock/views/templates/admin/';
        }
        
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
}
