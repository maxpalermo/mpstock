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

<div class="panel-footer" id='section-table-footer'>
    <i class='icon icon-pagelines'></i>
    {$footer_title|upper}
    <span class="badge">{$tot_rows}</span>
    <span class="panel-heading-action">
        <table class="table-info">
            <tbody>
                <tr>
                    <td>{$footer_page}</td>
                    <td>
                        <select onchange="javascript:table_pagination(this.value, {$table_pagination})">
                        {for $page=1 to $footer_pages}
                            <option value="{$page}" {if $page==$footer_current_page}selected="selected"{/if}>{$page}</option>
                        {/for}
                        </select>
                    </td>
                    <td>/</td>
                    <td>
                        <span class="badge">{$footer_tot_pages}</span>
                    </td>
                    <td>
                        <select onchange="javascript:table_pagination(1,this.value)">
                        {foreach $footer_paginations as $page}
                            <option value="{$page}" {if $page==$footer_current_pagination}selected="selected"{/if}>{$page}</option>
                        {/foreach}   
                        </select>
                    </td>
                </tr>
            </tbody>
        </table>
    </span>    
</div>
        