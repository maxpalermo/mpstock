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
    <table class="table" id='{$table.id}'>
        <thead>
            <tr class="nodrag nodrop">
                {if $table.checkbox}
                    <th class="center fixed-width-xs"><input type="checkbox" id='table-checkbox-toggle'></th>
                {/if}
                {foreach $table.header_columns as $col}
                    <th class='{$col.classname}' {if $col.width}style='width: {$col.width}'{/if}>
                        <span class='title_box'>
                            {$col.title}
                        </span>
                    </th>
                {/foreach}
            </tr>
            <tr class="nodrag nodrop filter row_hover">
                {if $table.checkbox}
                    <th class="center fixed-width-xs">--</th>
                {/if}
                {foreach $table.header_columns as $col}    
                    <th class='{$col.classname}' {if $col.width}style='width: {$col.width}'{/if}>
                        {if $col.search}
                            --
                        {else}
                            {if $col.type=='text' or $col.type=='price' or $col.type=='percentage'}
                                <input type="text" class="filter" name='table_filter[]' field="{$col.fieldname}" input_type="text" value="">
                            {elseif $col.type=='date'}
                                <div class="date_range row">
                                    <div class="input-group fixed-width-md center">
                                        <input type="text" class="filter datepicker date-input form-control hasDatepicker" name="table_filter[]" placeholder="{$table.table_content_filter_from}" input_type="date_from">
                                        <br>
                                        <input type="text" class="filter datepicker date-input form-control hasDatepicker" name="table_filter[]" placeholder="{$table.table_content_filter_to}" input_type="date_to">
                                    </div>
                                </div>
                            {elseif $col.type=='image' or $col.type=='html'}
                                --
                            {/if}
                        {/if}
                    </th>
                {/foreach}
                <th class="actions">
                    <span class="pull-right">
			<button type="button" id="submitFilterButtonmp_stock" class="btn btn-default" onclick='javascript:table_button_find_click();'>
                            <i class="icon icon-search"></i> {$table.table_content_filter_find}
			</button>
                    </span>
		</th>
            </tr>    
	</thead>
        <tbody>
        {foreach $table.rows as $row}
            {if $row.index is even} 
                {assign var='row_color' value=''}
            {else}
                {assign var='row_color' value='class="odd"'}
            {/if}
            <tr {$row_color}>
                {if $table.checkbox}
                    <td class="center fixed-width-xs"><input type="checkbox" name='table-checkbox-toggle-row[]'></td>
                {/if}
                {foreach $row as $key=>$column}
                    {assign var='col_type' value=$table.header_columns[$key]}
                <td>
                    {if $col_type=='text'}
                        {$column.value}
                    {elseif $col_type=='price'}
                        {displayPrice price=$column.value}
                    {elseif $col_type=='percentage'}
                        {$column.value} %
                    {elseif $col_type=='date'}
                        {$column.value|date_format:"%Y-%m-$d"}
                    {elseif $col_type=='image'}
                        <img src="{$column.value}" style="width: {$column.image.width}; height: {$column.image.height}; object-fit: {$column.image.fit};">
                    {elseif $col_type=='html'}
                        <div>
                            {$column.value}
                        </div>
                    {elseif $col_type=='button'}
                        <button type="button" id="{$column.button.id}" class="btn btn-default" onclick='javascript:{$column.button.onclick}();'>
                            <i class="icon {$column.button.icon}}" style="color: {$column.button.color};"></i> {$column.button.title}
			</button>
                    {/if}
                </td>
                {/foreach}
            </tr>
        {/foreach}
    </table>
</div>
        