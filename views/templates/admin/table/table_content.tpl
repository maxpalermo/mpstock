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
                        {if !$col.search}
                            --
                        {else}
                            {if $col.type=='text' or $col.type=='price' or $col.type=='percentage'}
                                <input type="text" class="filter" name='table_filter[]' field="{$col.fieldname}" input_type="text" value="">
                            {elseif $col.type=='date'}
                                <div class="date_range row">
                                    <div class="input-group fixed-width-md center">
                                        <input type="text" class="filter datepicker date-input form-control hasDatepicker" name="table_filter[]" placeholder="{$table.table_content_filter_from}" input_type="date_from">
                                        <span class="input-group-addon">
                                            <i class="icon-calendar"></i>
					</span>
                                    </div>
                                    <div class="input-group fixed-width-md center">
                                        <input type="text" class="filter datepicker date-input form-control hasDatepicker" name="table_filter[]" placeholder="{$table.table_content_filter_to}" input_type="date_to">
                                        <span class="input-group-addon">
                                            <i class="icon-calendar"></i>
					</span>
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
            <tr class='{cycle values="odd,even"}' value='{$row['id']}'>
                {if $table.checkbox}
                    <td class="center fixed-width-xs"><input type="checkbox" name='table-checkbox-toggle-row[]'></td>
                {/if}
                {foreach $row as $key=>$value}
                    {if $key!='id'}
                        {assign var='col_type' value=$table.header_columns[$key].type}
                        {assign var='text_align' value=$table.header_columns[$key].text_align}
                        {assign var='negative' value=$table.header_columns[$key].negative}
                        {if $col_type=='text' or $col_type=='price' or $col_type=='percentage' or $col_type=='date'}
                            {assign var='td_value' value="value='{$value}'"}
                        {else}
                            {assign var='td_value' value=""}
                        {/if}
                        <td style='text-align: {$text_align}' {$td_value}>
                            {if $col_type=='text'}
                                {$value}
                            {elseif $col_type=='price'}
                                {if $negative && $value<0}
                                    <span style='color: #BB2A2A;'>{displayPrice price=$value}</span>
                                {else}
                                    <span style='color: #555;'>{displayPrice price=$value}</span>
                                {/if}
                            {elseif $col_type=='percentage'}
                                {if $negative && $value<0}
                                    <span style='color: #BB2A2A;'>{$value|string_format:"%.2f"} %</span>
                                {else}
                                    <span style='color: #555;'>{$value|string_format:"%.2f"} %</span>
                                {/if}
                            {elseif $col_type=='int'}
                                {if $negative && $value<0}
                                    <span style='color: #BB2A2A;'>{$value}</span>
                                {else}
                                    <span style='color: #555;'>{$value}</span>
                                {/if}
                            {elseif $col_type=='date'}
                                {$value|date_format:"%Y-%m-%d"}
                            {elseif $col_type=='image'}
                                {if !$value}
                                    {assign var='img_src' value=$table_row_image.src}
                                {else}
                                    {assign var='img_src' value=$value}
                                {/if}
                                <img src="{$img_src}" style="width: {$table_row_image.width}; height: {$table_row_image.height}; object-fit: {$table_row_image.fit};">
                            {elseif $col_type=='html'}
                                <div>
                                    {$value}
                                </div>
                            {elseif $col_type=='button'}
                                <button type="button" id="{$column.button.id}" class="btn btn-default" onclick='javascript:{$column.button.onclick}();'>
                                    <i class="icon {$column.button.icon}}" style="color: {$column.button.color};"></i> {$column.button.title}
                                </button>
                            {/if}
                        </td>
                    {/if}
                {/foreach}
            </tr>
        {/foreach}
    </table>
</div>
        