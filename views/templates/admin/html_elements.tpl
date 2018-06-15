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
{if $element.type == 'badge'}
    <span 
        class="badge {if isset($element.class)}{$element.class}{/if}" 
        style="{if isset($element.background)}
                    background-color: {$background|escape:'htmlall':'UTF-8'};
                {/if}
                {if isset($element.color)}
                    color: {$element.color|escape:'htmlall':'UTF-8'};
                {/if}"
    >{$element.value}</span>
{/if}
{if $element.type == 'icon'}
    <i class="icon {$element.icon} {if isset($link)}link-pointer{/if}" 
        style="{if isset($element.color)} color: {$element.color};{/if}{if isset($element.background_color)} background-color: : {$element.background_color}{/if};"
        {if isset($element.link)}
            onclick="javascript:{$element.link};"
        {/if}        
    ></i>
{/if}
{if $element.type == 'status'}
    {if (int)$element.value}
        <i class="icon icon-check" style="color: #72C279;"></i>
    {else}
        <i class="icon icon-times" style="color: #E08F95;"></i>
    {/if}
{/if}
{if $element.type == 'checkButton'}
    {if (int)$element.value}
        {assign var='checked' value='checked'}
    {else}
        {assign var='checked' value=''}
    {/if}
    <input type="checkbox" name='{$element.name}[]' value='{$element.value}' {$checked}>
{/if}
{if $element.type == 'button'}{/if}
{if $element.type == 'link'}{/if}
{if $element.type == 'image'}{/if}
{if $element.type == 'option'}{/if}
{if $element.type == 'select'}{/if}
{if $element.type == 'span'}
    <span 
        {if isset($element.css)}
            style="{foreach $element.css as $key=>$value}{$key}: {$value};{/foreach}"
        {/if}
    >{$element.value}</span>
{/if}
{if $element.type == 'text'}{/if}
{if $element.type == 'textarea'}{/if}