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

<div class="list-group" style="overflow: hidden;">
    {assign var='row' value=$blank_row}
    {assign var='save_block' value=true}
    {assign var='counter' value=0}
    {assign var='info_class' value='list-group-item-info'}
    {include file=$template_row}
    {assign var='info_class' value=''}
    {assign var='save_block' value=false}
    {foreach $fill_rows as $row}
    	{assign var='counter' value=$counter+1}
        {include file=$template_row}
    {/foreach}
</div>