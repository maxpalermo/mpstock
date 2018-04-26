<?php
/**
* 2007-2017 PrestaShop
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
*  @copyright 2018 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

require_once(dirname(__FILE__).'../../../config/config.inc.php');
require_once(dirname(__FILE__).'../../../init.php');

$module_name = Tools::getValue('module_name', '');
$class_name = Tools::getValue('class_name', '');

if (!$module_name || !$class_name) {
    die("ERROR! No module selected");
}
require_once(dirname(__FILE__).'/' . $module_name . '.php');

$module = new $class_name();

if (Tools::isSubmit('ajax') && tools::isSubmit('action') && tools::isSubmit('token')) {
    if (Tools::getValue('token') != Tools::encrypt($module->name)) {
        print $module->displayError($module->l('INVALID TOKEN'));
        exit();
    }
    $action = 'ajaxProcess' . Tools::getValue('action');
    print $module->$action();
    exit();
} else {
        print Tools::jsonEncode(
            array(
                'result' => false,
                'msg_error' => $module->displayError(
                    $module->l('INVALID SUBMIT VALUES') . '<br>' .
                    "ajax=" . (int)Tools::getValue('ajax') . '<br>' .
                    "action=" . Tools::getValue('action') . '<br>' .
                    "token=" . Tools::getValue('token')
                )
            )
        );
    exit();
}
