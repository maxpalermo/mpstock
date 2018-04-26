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
<style>
    .mp-table-footer tfoot tr td
    {
        padding-left: 8px;
        padding-right: 8px;
    }
</style>
<div class="panel-footer" id='section-table-footer' style='text-align: center;'>
    <span class="panel-heading-action">
        <table class="table-info mp-table-footer" style='margin: 0 auto;'>
            <thead>
                
            </thead>
            <tbody>
                
            </tbody>
            <tfoot>
                <tr>
                    <td>
                        <i class='icon {$footer_icon}'></i>
                        {$footer_title|upper}
                    </td>
                    <td>
                        <span class="badge">{$tot_rows}</span>
                    </td>
                    <td>{$footer_title_page}</td>
                    <td>
                        <select id="table_footer_page" class='fixed-width-sm' onchange="javascript:table_pagination(this.value, {$footer_current_pagination})">
                        {for $page=1 to $footer_tot_pages}
                            <option value="{$page}" {if $page==$footer_current_page}selected="selected"{/if}>{$page}</option>
                        {/for}
                        </select>
                    </td>
                    <td>/</td>
                    <td>
                        <span class="badge">{$footer_tot_pages}</span>
                    </td>
                    <td style='padding-right: 4px;'>
                        {$footer_visualization_title} 
                    </td>    
                    <td style='padding-left: 0;'>
                        <select id="table_footer_pagination" onchange="table_pagination(1,this.value);">
                        {foreach $footer_paginations as $page}
                            <option value="{$page}" {if $page==$footer_current_pagination}selected="selected"{/if}>{$page}</option>
                        {/foreach}   
                        </select>
                    </td>
                </tr>
            </tfoot>
        </table>
    </span>    
</div>
        