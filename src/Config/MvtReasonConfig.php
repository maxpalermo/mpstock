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

namespace MpSoft\MpStock\Config;

class MvtReasonConfig
{
    public static $adminMpStockParent = 'AdminMpStockParent';
    public static $adminMpStock = 'AdminMpStock';
    public static $adminMpStockDocuments = 'AdminMpStockDocuments';
    public static $adminMpStockMovements = 'AdminMpStockMovements';
    public static $adminMpStockConfig = 'AdminMpStockConfig';
    public static $adminMpStockImport = 'AdminMpStockImport';
    public static $adminMpStockQuickMvt = 'AdminMpStockQuickMvt';
    public static $adminMpStockAvailable = 'AdminMpStockAvailable';

    public static function setIdLoadMovement($id)
    {
        return \Configuration::updateValue('MPSTOCK_LOAD_MOVEMENT', (int) $id);
    }

    public static function getIdLoadMovement()
    {
        return (int) \Configuration::get('MPSTOCK_LOAD_MOVEMENT');
    }

    public static function setIdUnloadMovement($id)
    {
        return \Configuration::updateValue('MPSTOCK_UNLOAD_MOVEMENT', (int) $id);
    }

    public static function getIdUnloadMovement()
    {
        return (int) \Configuration::get('MPSTOCK_UNLOAD_MOVEMENT');
    }

    public static function setIdOrderMovement($id)
    {
        return \Configuration::updateValue('MPSTOCK_ORDER_MOVEMENT', (int) $id);
    }

    public static function getIdOrderMovement()
    {
        return (int) \Configuration::get('MPSTOCK_ORDER_MOVEMENT');
    }

    public static function setIdReturnMovement($id)
    {
        return \Configuration::updateValue('MPSTOCK_RETURN_MOVEMENT', (int) $id);
    }

    public static function getIdReturnMovement()
    {
        return (int) \Configuration::get('MPSTOCK_RETURN_MOVEMENT');
    }

    public static function setIdEan13Movement($id)
    {
        return \Configuration::updateValue('MPSTOCK_EAN13_MOVEMENT', (int) $id);
    }

    public static function getIdEan13Movement()
    {
        return (int) \Configuration::get('MPSTOCK_EAN13_MOVEMENT');
    }
}