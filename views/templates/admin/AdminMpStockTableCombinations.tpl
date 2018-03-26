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
*  @author    Massimiliano Palermo <info@mpsoft.it>
*  @copyright 2007-2018 Digital Solutions®
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<div class="table-responsive-row clearfix">
    <table class="table mp_stock">
        <thead>
            <tr class="nodrag nodrop">
                <th class="text-center">{l s='Id' mod='mpstock'}</th>
                <th class="text-center">{l s='Movement' mod='mpstock'}</th>
                <th class="text-center">{l s='Name' mod='mpstock'}</th>
                <th class="text-center">{l s='Reference' mod='mpstock'}</th>
                <th class="text-center">{l s='Ean13' mod='mpstock'}</th>
                <th class="text-center">{l s='Qty' mod='mpstock'}</th>
                <th class="text-center">{l s='Price' mod='mpstock'}</th>
                <th class="text-center">{l s='Tax rate' mod='mpstock'}</th>
                <th class="text-center">{l s='Action' mod='mpstock'}</th>
                <th class="text-center">{l s='Status' mod='mpstock'}</th>
            </tr>
        </thead>
        <tbody>
            {foreach $mpstock_rows as $row}
                <tr {if $smarty.foreach.foo.index is odd}class='odd'{/if}>
                    <td>{$row.id_product_attribute}</td>
                    <td class="text-center">{$row.select_movement}</td>
                    <td class="text-right">{$row.name}</td>
                    <td class="text-center">{$row.input_reference}</td>
                    <td class="text-center">{$row.input_ean13}</td>
                    <td class="text-center">{$row.input_qty}</td>
                    <td class="text-center">{$row.input_price}</td>
                    <td class="text-right">{$row.input_tax_rate}</td>
                    <td class="text-center">
                        <button class='btn btn-default' type='button' name='mpstock_save_movement[]'>
                            <i class='icon icon-save'></i>
                        </button>
                    </td>
                    <td class="text-center"><i class='icon icon-pencil-square-o'></i></td>
                </tr>
            {/foreach}	
        </tbody>
    </table>
</div>
