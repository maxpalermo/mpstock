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
<table class="table table-striped table-condensed">
    <thead>
        <tr>
            <th></th>
            <th>{l s='id_product' mod='mpstock'}</th>
            <th>{l s='reference' mod='mpstock'}</th>
            <th>{l s='name' mod='mpstock'}</th>
            <th>{l s='attributes' mod='mpstock'}</th>
            <th class="text-right">{l s='quantity' mod='mpstock'}</th>
        </tr>
    </thead>
    <tbody>
        {foreach $product_quantities as $row}
        <tr>
            <td><span name="expand-stock-row"><i class="icon icon-plus-circle"></i></span></td>
            <td>{$row.id_product}-{$row.id_product_attribute}</td>
            <td><strong>{$row.reference}</strong></td>
            <td>{$row.product_name}</td>
            <td>--</td>
            <td class="text-right">
                {if $row.product_quantity==0}{assign var=text_col value='text-primary'}{/if}
                {if $row.product_quantity<0}{assign var=text_col value='text-danger'}{/if}
                {if $row.product_quantity>0}{assign var=text_col value='text-success'}{/if}
                <strong class="{$text_col}">
                    {$row.product_quantity}
                </strong>
            </td>
        </tr>
        {/foreach}
    </tbody>
    <tfoot>
        <tr>
            <th colspan="5">
                <div class="row">
                    <div class="col-md-4">
                        <span class="badge">
                            <span>{l s='Products per page' mod='mpstock'}</span>
                            <select id="stock-pagination" style="width: auto; display: inline-block;">
                                <option value="50" {if $stock_pagination==50} selected{/if}>50</option>
                                <option value="100" {if $stock_pagination==100} selected{/if}>100</option>
                                <option value="200" {if $stock_pagination==200} selected{/if}>200</option>
                                <option value="500" {if $stock_pagination==500} selected{/if}>500</option>
                                <option value="1000" {if $stock_pagination==1000} selected{/if}>1000</option>
                                <option value="2000" {if $stock_pagination==1000} selected{/if}>2000</option>
                                <option value="5000" {if $stock_pagination==1000} selected{/if}>5000</option>
                                <option value="10000" {if $stock_pagination==1000} selected{/if}>10000</option>
                            </select>
                        </span>
                    </div>
                    <div class="col-md-4">
                        <span class="badge">
                            <span>{l s='Current page' mod='mpstock'}</span>
                            <select id="stock-page" style="width: auto; display: inline-block;">
                                {for $i=0 to $stock_pages-1}
                                    <option value="{$i}" {if $stock_page==$i} selected{/if}>{$i+1}</option>
                                {/for}
                            </select>
                        </span>
                    </div>
                </div>
            </th>
        </tr>
    </tfoot>
</table>
