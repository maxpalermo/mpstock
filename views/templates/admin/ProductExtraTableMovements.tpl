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

<div class="panel col-lg-12">
    <div class="panel-heading">
	{l s='Stock movements' mod='mpstock'}
        <span class="badge">{$tot_rows}</span>
	<span class="panel-heading-action">
	    <a id="mpstock_list_export" class="list-toolbar-btn" href="javascript:mpstock_export_selected();">
	        <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="Export selected" data-html="true" data-placement="top">
                    <i class="process-icon-export"></i>
                </span>
            </a>
            <a id="desc-mp_stock-stats" class="list-toolbar-btn center" href="">
                <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="Statistics" data-html="true" data-placement="top">
                    <i class="process-icon-stats"></i>
		</span>
            </a>
            <a class="list-toolbar-btn" href="javascript:location.reload();">
		<span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="Aggiorna la lista" data-html="true" data-placement="top">
                    <i class="process-icon-refresh"></i>
		</span>
            </a>
        </span>
    </div>	
    <div class="table-responsive-row clearfix">
	<table class="table mp_stock">
            <thead>
		<tr class="nodrag nodrop">
                    <th class="center fixed-width-xs">
                        <input type='checkbox' id='chkSelectRows'>
                    </th>
                    <th class=" text-center">{l s='Name' mod='mpstock'}</th>
                    <th class=" text-center">{l s='Qty' mod='mpstock'}</th>
                    <th class=" text-center">{l s='Price' mod='mpstock'}</th>
                    <th class=" text-center">{l s='Total' mod='mpstock'}</th>
                    <th class=" text-center">{l s='Tax rate' mod='mpstock'}</th>
                    <th class=" text-center">{l s='Amount' mod='mpstock'}</th>
                    <th class=" text-center">{l s='Date add' mod='mpstock'}</th>
                    <th class=" text-center">{l s='Referrer' mod='mpstock'}</th>
                </tr>
            </thead>
            <tbody>
                {foreach $rows as $row}
                    <tr {if $smarty.foreach.foo.index is odd}class='odd'{/if}>
                        <td class='center'>
                            <input type='checkbox' name='chkSelectRow[]'> 
                        </td>
                        <td>{$row.product_name}</td>
                        <td class="text-right">
                            <span {if $row.product_qty<0}style="color: #BB6666;"{/if}>
                                {$row.product_qty}
                            </span>
                        </td>
                        <td class="text-right">{displayPrice price=$row.product_price}</td>
                        <td class="text-right">{displayPrice price=$row.product_total}</td>
                        <td class="text-right">{$row.product_tax_rate|string_format:"%.2f"}</td>
                        <td class="text-right">{displayPrice price=$row.product_amount}</td>
                        <td class="center">{dateFormat date=$row.product_date_add full=0}</td>
                        <td>
                            <span>
                                {if $row.product_employee>0}
                                    <i class="icon icon-user"></i>
                                {/if}
                                {$row.referrer}
                            </span>
                        </td>
                    </tr>
                {/foreach}	
            </tbody>
	</table>
</div>
    <div class="row" style='text-align: center;'>
        <div style='float: left'>
            <div style='display: inline-block'>
                {l s='Page' mod='mpstock'}&nbsp;&nbsp;
            </div>
            <div style='display: inline-block'>
                <select id='select_current_page'>
                    {for $cur_page=1 to $tot_pages}
                        <option value='{$cur_page}' {if $page==$cur_page}selected='selected'{/if}>{$cur_page}</option>
                    {/for}
                </select>
            </div>
            <div style='display: inline-block'>
                &nbsp;&nbsp;{l s='of' mod='mpstock'}&nbsp;&nbsp;{$tot_pages}
            </div>
        </div>
        <div style='float: right;'>
            <div style='display: inline-block'>
                {l s='records per page' mod='mpstock'}&nbsp;&nbsp;
            </div>
            <div style='display: inline-block'>
                <select id='select_current_pagination' style='display: inline-block;'>
                    <option value='5' {if $pagination==5}selected='selected'{/if}>5</option>
                    <option value='10' {if $pagination==10}selected='selected'{/if}>10</option>
                    <option value='20' {if $pagination==20}selected='selected'{/if}>20</option>
                    <option value='50' {if $pagination==50}selected='selected'{/if}>50</option>
                    <option value='100' {if $pagination==100}selected='selected'{/if}>100</option>
                    <option value='200' {if $pagination==200}selected='selected'{/if}>200</option>
                    <option value='500' {if $pagination==500}selected='selected'{/if}>500</option>
                    <option value='1000' {if $pagination==1000}selected='selected'{/if}>1000</option>
                    <option value='{$tot_rows}' {if $pagination==1000}selected='selected'{/if}>{$tot_rows}</option>
                </select>
            </div>
        </div>                  
    </div>
</div>
{if isset($debug) && $debug==1}
    <pre>
        {$query}
    </pre>
{/if}
