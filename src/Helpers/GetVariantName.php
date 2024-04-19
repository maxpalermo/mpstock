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
namespace MpSoft\MpStock\Helpers;

if (!defined('_PS_VERSION_')) {
    exit;
}

class GetVariantName
{
    public static function get($id_product_attribute, $id_lang = null)
    {
        if (!$id_lang) {
            $id_lang = (int) \Context::getContext()->language->id;
        }
        $db = \Db::getInstance();
        $sql = new \DbQuery();
        $sql->select('GROUP_CONCAT(al.name)')
            ->from('product_attribute', 'pa')
            ->innerJoin('product_attribute_combination', 'pac', 'pac.id_product_attribute = pa.id_product_attribute')
            ->innerJoin('attribute_lang', 'al', 'al.id_attribute = pac.id_attribute and al.id_lang=' . (int) $id_lang)
            ->where('pa.id_product_attribute=' . (int) $id_product_attribute);
        $name = \Tools::strtoupper($db->getValue($sql));

        return $name;
    }
}