{*
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
*  @author    Massimiliano Palermo <contact@prestashop.com>
*  @copyright 2018 Digital Solutions®
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
{foreach $rows as $row}
<tr name="{$row.id_product}">
    <td><span name="product-attribute-row"><i class="icon icon-list-alt text-primary"></i></span></td>
    <td>{$row.id_product_attribute}</td>
    <td><i class='icon icon-barcode text-success'></i></td>
    <td><strong>{$row.ean13}</strong></td>   
    <td>{$row.name}</td>
    <td class="text-right">
        {if $row.quantity==0}{assign var=text_col value='text-primary'}{/if}
        {if $row.quantity<0}{assign var=text_col value='text-danger'}{/if}
        {if $row.quantity>0}{assign var=text_col value='text-success'}{/if}
        <strong class="{$text_col}">
            {$row.quantity}
        </strong>
    </td>
</tr>
{/foreach}
