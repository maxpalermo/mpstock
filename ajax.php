<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    Massimiliano Palermo <maxx.palermo@gmail.com>
 * @copyright Since 2016 Massimiliano Palermo
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

require_once (dirname(__FILE__) . '../../../config/config.inc.php');
require_once (dirname(__FILE__) . '../../../init.php');

$module_name = Tools::getValue('module_name', '');
$class_name = Tools::getValue('class_name', '');

if (!$module_name || !$class_name) {
    die("ERROR! No module selected");
}
require_once (dirname(__FILE__) . '/' . $module_name . '.php');

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
                "ajax=" . (int) Tools::getValue('ajax') . '<br>' .
                "action=" . Tools::getValue('action') . '<br>' .
                "token=" . Tools::getValue('token')
            )
        )
    );
    exit();
}