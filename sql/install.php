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

$sql = array();

$sql[] = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."mp_stock` (
    `id_mp_stock` int(11) NOT NULL AUTO_INCREMENT,
    `id_mp_stock_exchange` int(11) NOT NULL,
    `id_shop` int(11) NOT NULL,
    `id_product` int(11) NOT NULL,
    `id_product_attribute` int(11) NOT NULL,
    `id_mp_stock_type_movement` int(11) NOT NULL,
    `qty` varchar(10) NOT NULL,
    `price` decimal(20,6) NOT NULL,
    `tax_rate` decimal(20,6) NOT NULL,
    `date_add` timestamp NOT NULL,
    `id_employee` int NOT NULL,
    PRIMARY KEY  (`id_mp_stock`)
) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8;";

$sql[] = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."mp_stock_type_movement` (
    `id_mp_stock_type_movement` int(11) NOT NULL AUTO_INCREMENT,
    `id_lang` int(11) NOT NULL,
    `id_shop` int(11) NOT NULL,
    `name` varchar(255) NOT NULL,
    `sign` enum('-1','1') NOT NULL DEFAULT '1',
    `exchange` boolean NOT NULL,
    PRIMARY KEY  (`id_mp_stock_type_movement`)
) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8;";

$sql[] = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."mp_stock_list_movements` (
    `id_mp_stock_list_movements` int(11) NOT NULL AUTO_INCREMENT,
    `id_employee_bo` int(11) NOT NULL,
    `id_product` int(11) NOT NULL,
    `id_product_attribute` int(11) NOT NULL,
    `idx` int(11) NOT NULL,
    `image_url` varchar(255) NULL,
    `reference` varchar(255) NULL,
    `name` varchar(255) NULL,
    `qty` int(11) NOT NULL,
    `type_movement` varchar(255) NOT NULL,
    `date_add` date NOT NULL,
    `id_employee_movement` int(11) NOT NULL,
    `employee` varchar(255) NULL,
    PRIMARY KEY  (`id_mp_stock_list_movements`)
) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8;";

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        $this->_errors[] = Db::getInstance()->getMsgError();
        return false;
    }
}
