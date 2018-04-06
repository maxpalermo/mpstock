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

/**
 * TODO CLICK ON ROW
 * onclick="document.location = 'index.php?controller=AdminProducts&id_product=16&updateproduct&token=ec9df8557a49430bdd6f0a8010dd2f34'"
 */

Class MpHelperTable
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
    
    /** @var object module Object module **/
    protected $module;
    /** @var string $query Query SQL for fill table rows **/
    public $query;
    /** @var string $tablename Name of the table to search values **/
    public $tablename;
    /** @var int $checkbox if 1 shows checkbox button **/
    public $checkbox;
    /** @var string $header_icon Icon shown in title bar **/
    public $header_icon;
    /** @var string $header_color Color in HTML format for title bar **/
    public $header_color;
    /** @var string $header_title Title in title bar **/
    public $header_title;
    /** @var int $tot_rows Total rows shown **/
    public $tot_rows;
    /** 
     * @var array $header_action_buttons Array of buttons shown in title bar 
     * Button elements: .id : button id
     *                  .href: button href
     *                  .hint: button legend onmouseover
     *                  .icon: button icon
     *                  .color: button color in HTML format
     **/
    public $header_action_buttons;
    /** 
     * @var array $table Array of values that define table 
     * Table structure:
     *  - id : id of table
     *  - header_columns: array of header columns
     *  - - classname: column classname
     *  - - title: column title
     *  - - type: [button,text,price,date,percentage,image,html]
     *  - - fieldname: fieldname of the table for filtering
     *  - - search: 0/1 show search field
     *  - table_content_filter_from : translation of word from
     *  - table_content_filter_to : translation of word to
     *  - table_content_filter_find : translation of word find
     *  - rows: array of rows
     *  - - row: array of columns
     *  - - - column: array of values
     *  - - - - value: content of cell
     *  - - - - type: like header type
     *  - - - - image: array ov values
     *  - - - - - src: scr value
     *  - - - - - width: image width
     *  - - - - - height: image height
     *  - - - - - fit: [fill, contain, cover, none, scale-down]
     *  - - - - button: if type is button array of values
     *  - - - - - id: id of button
     *  - - - - - onclick: action when clicked
     *  - - - - - icon: button icon
     *  - - - - - title: button title
     *  - - - - - color: title color
     **/
    public $table;
    /** @var string $footer_title Title of footer panel **/
    public $footer_title;
    /** @var string $footer_icon Icon of footer panel **/
    public $footer_icon;
    /** @var array $footer_paginations Array of pagination values **/
    public $footer_paginations;
    /** @var int $footer_current_pagination currentpagination **/
    public $footer_current_pagination;
    /** @var string $footer_current_page current page **/
    public $footer_current_page;
    /** @var string $footer_tot_pages total pages to show **/
    public $footer_tot_pages;
    /** @var string $footer_title_page translation of word Page **/
    public $footer_title_page;
    /** @var string $footer_visualization_title translation of word Display **/
    public $footer_visualization_title;
    /** @var array $table_list Array of content values of table **/
    public $table_list;
    /** @var array $table_row_actions Array of button shown in each row **/
    public $table_row_actions;
    /** @var array $table_row_image Array of image definition **/
    public $table_row_image;
    
    public function __construct($module) {
        $this->module = $module;
        $this->query = '';
        $this->tablename = '';
        $this->checkbox = 1;
        $this->header_icon = 'icon-cogs';
        $this->header_color = '#5555BB';
        $this->header_title = $this->l('MP Helper List');
        $this->tot_rows = 0;
        $this->header_action_buttons = array();
        $this->table = array();
        $this->table['id'] = 'mp_table';
        $this->table['checkbox'] = $this->checkbox;
        $this->table['table_content_filter_from'] = $this->l('From');
        $this->table['table_content_filter_to'] = $this->l('To');
        $this->table['table_content_filter_find'] = $this->l('Find');
        $this->table['header_columns'] = array();
        $this->table['rows']= array();
        $this->footer_icon = 'icon-list';
        $this->footer_title = $this->l('MP Helper List');
        $this->footer_title_page = $this->l('Page');
        $this->footer_visualization_title = $this->l('Display');
        $this->footer_paginations = array(5, 10, 20, 50, 100, 200, 500, 1000);
        $this->footer_current_pagination = 50;
        $this->footer_current_page = 1;
        $this->footer_tot_pages = 1;
        $this->table_list = array();
        $this->table_row_actions = array();
    }
    
    public function addImageDefinition($src, $width = '48px', $height = '48px', $fit = self::OBJECT_FIT_CONTAIN)
    {
        $image = array(
            'src' => $src,
            'width' => $width,
            'height' => $height,
            'fit' => $fit,
        );
        $this->table_row_image = $image;
        return $image;
    }
    
    /**
     * Add a button in toolbar
     * @param string $id id of the button
     * @param string $href action when clicked
     * @param string $hint display suggestion on mouseover
     * @param string $icon icon to display
     * @param string $color Optional color in HTML format
     * @return array an Array of values definition for the button
     */
    public function addToolbarButton($id, $href, $hint, $icon, $color = "#555")
    {
        $button = array(
            'id' => $id,
            'href' => $href,
            'hint' => $hint,
            'icon' => $icon,
            'color' => $color,
        );
        $this->header_action_buttons[] = $button;
        return $button;
    }
    
    public function addRowButton($id, $onclick, $hint, $icon, $color = '#555')
    {
        $button = array(
            'id' => $id,
            'onclick' => $onclick,
            'hint' => $hint,
            'icon' => $icon,
            'color' => $color,
        );
        $this->table_row_actions[] = $button;
        return $button;
    }
    
    public function addTableHeader($key, $classname, $title, $type, $fieldname, $search = 0, $width='auto', $text_align = 'left', $negative = true)
    {
        $header = array(
            'key' => $key,
            'classname' => $classname,
            'title' => $title,
            'type' => $type,
            'fieldname' => $fieldname,
            'search' => $search,
            'width'=> $width,
            'text_align' => $text_align,
            'negative' => $negative,
        );
        $this->table['header_columns'][$fieldname] = $header;
        return $header;
    }
    
    public function setTableId($id)
    {
        $this->table['id'] = $id;
    }
    
    public function setQuery($query)
    {
        $this->query = $query;
    }
    
    public function setTableName($tablename)
    {
        $this->tablename = $tablename;
    }
    
    public function generateTable($rows)
    {
        $this->table['rows'] = $rows;
        $this->tot_rows = count($rows);
        $template = $this->module->getPath().'views/templates/admin/table/';
        $smarty = Context::getContext()->smarty;
        $smarty->assign(
            array(
                'template_header' => $template.'table_header.tpl',
                'template_content' => $template.'table_content.tpl',
                'template_footer' => $template.'table_footer.tpl',
                'header_icon' => $this->header_icon,
                'header_color' => $this->header_color,
                'header_title' => $this->header_title,
                'tot_rows' => $this->tot_rows,
                'header_action_buttons' => $this->header_action_buttons,
                'table_row_image' => $this->table_row_image,
                'table' => $this->table,
                'footer_title' => $this->footer_title,
                'footer_icon' => $this->footer_icon,
                'footer_title_page' => $this->footer_title_page,
                'footer_paginations' => $this->footer_paginations,
                'footer_current_pagination' => $this->footer_current_pagination,
                'footer_current_page' => $this->footer_current_page,
                'footer_tot_pages' => $this->footer_tot_pages,
                'footer_visualization_title' => $this->footer_visualization_title,
            )
        );
        $html = $smarty->fetch($this->module->getPath().'views/templates/admin/table/content.tpl');
        return $html;
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