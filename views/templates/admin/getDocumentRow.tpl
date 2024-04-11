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
<a class="list-group-item {$info_class}" href='javascript:void(0);' style="overflow: hidden;">
    <span class='badge badge-white'>{$counter}</span>
    <div class="row" style="overflow: hidden;">
        <div class="col-md-1">
            <label>{l s='id' mod='mpstock'}</label>
            <input type='text' name='id_mpstock_movement' class="input" value='{$row.id_mpstock_movement}' disabled>
        </div>
        <div class="col-md-6">
            <input type='hidden' name='id_mpstock_document' value="{$id_document}">
            <input type='hidden' name='id_product' class="input" value='{$row.id_product}'>
            <input type='hidden' name='id_product_attribute' class="input" value='{$row.id_product_attribute}'>

            <label>{l s='Product' mod='mpstock'}</label>
            <input type='text' name='id_product_attribute_autocomplete' class="input input-autocomplete"
                value='{if isset($row.id_product)}{$row.id_product}{/if} {if isset($row.reference)}{$row.reference}{/if} {$row.product}'>
        </div>
        <div class="col-md-1">
            <label>{l s='Stock' mod='mpstock'}</label>
            <input type='text' name='stock_quantity_before' class="input text-right"
                value='{$row.stock_quantity_before}' disabled>
        </div>
        <div class="col-md-1">
            <label>{l s='Qty' mod='mpstock'}</label>
            <input type='text' name='stock_movement' class="input text-right" value='{$row.stock_movement}'>
        </div>
        <div class="col-md-1">
            <label>{l s='Price' mod='mpstock'}</label>
            <input type='text' name='price_te' class="input text-right" value='{$row.price_te}'>
            <input type='hidden' name='price_te_float' class="input text-right" value='{$row.price_te_float}'>
        </div>
        <div class="col-md-1">
            <label>{l s='Wholesale' mod='mpstock'}</label>
            <input type='text' name='wholesale_price_te' class="input text-right" value='{$row.wholesale_price_te}'>
            <input type='hidden' name='wholesale_price_te_float' class="input text-right"
                value='{$row.wholesale_price_te_float}'>
        </div>
        <div class="col-md-1">
            <label>{l s='Tax rate' mod='mpstock'}</label>
            <input type='text' name='tax_rate' class="input text-right" value='{$row.tax_rate}'>
            <input type='hidden' name='tax_rate_float' class="input text-right" value='{$row.tax_rate_float}'>
        </div>
        {if isset($save_block) && $save_block}
            <div class="col-md-12">
                <button type="button" class="btn btn-success pull-right" name='btn_save' style="margin-top: 12px;"
                    onclick="javascript:saveRow();">
                    <i class="icon icon-save"></i>
                    &nbsp;
                    {l s='Save' mod='mpstock'}
                </button>
            </div>
        {/if}
    </div>
</a>