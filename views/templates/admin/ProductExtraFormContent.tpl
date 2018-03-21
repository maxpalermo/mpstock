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

<div class="panel-body">
    <div class="form-group">
        <label class="control-label col-lg-3 required">{l s='Search in orders' mod='mpstock'}</label>
	<div class="col-lg-9">
            <span class="switch prestashop-switch fixed-width-lg">
                <input type="radio" name="input_switch_search_in_orders" id="input_switch_sio_on" value="1" {if !$search_in_orders}checked="checked"{/if}>
		<label for="input_switch_sio_on">{l s='YES' mod='mpstock'}</label>
                <input type="radio" name="input_switch_search_in_orders" id="input_switch_sio_off" value="0" {if !$search_in_orders}checked="checked"{/if}>
		<label for="input_switch_sio_off">{l s='NO' mod='mpstock'}</label>
                <a class="slide-button btn"></a>
            </span>
            <p class="help-block">
                {l s='If set, search stock movements in orders table' mod='mpstock'}
            </p>							
	</div>
    </div>
    <div class="form-group">
        <label class="control-label col-lg-3 required">{l s='Search in Order slips' mod='mpstock'}</label>
	<div class="col-lg-9">
            <span class="switch prestashop-switch fixed-width-lg">
                <input type="radio" name="input_switch_search_in_slips" id="input_switch_sis_on" value="1" {if $search_in_slips}checked='checked'{/if}>
		<label for="input_switch_sis_on">{l s='YES' mod='mpstock'}</label>
                <input type="radio" name="input_switch_search_in_slips" id="input_switch_sis_off" value="0" {if !$search_in_slips}checked="checked"{/if}>
		<label for="input_switch_sis_off">{l s='NO' mod='mpstock'}</label>
                <a class="slide-button btn"></a>
            </span>
            <p class="help-block">
                {l s='If set, search stock movements in orders table' mod='mpstock'}
            </p>							
	</div>
    </div>
    <div class="form-group">
        <label class="control-label col-lg-3 required">{l s='Search in Stock movements' mod='mpstock'}</label>
	<div class="col-lg-9">
            <span class="switch prestashop-switch fixed-width-lg">
                <input type="radio" name="input_switch_search_in_movements" id="input_switch_sim_on" value="1" {if $search_in_movements}checked='checked'{/if}>
		<label for="input_switch_sim_on">{l s='YES' mod='mpstock'}</label>
                <input type="radio" name="input_switch_search_in_movements" id="input_switch_sim_off" value="0" {if !$search_in_movements}checked="checked"{/if}>
		<label for="input_switch_sim_off">{l s='NO' mod='mpstock'}</label>
                <a class="slide-button btn"></a>
            </span>
            <p class="help-block">
                {l s='If set, search stock movements in orders table' mod='mpstock'}
            </p>							
	</div>
    </div>
    <div class="form-group">
	<label class="control-label col-lg-3 required">{l s='Start date' mod='mpstock'}</label>
        <div class="col-lg-8">
            <div class="row">
		<div class="input-group col-lg-4">
                    <input id="input_date_start" type="text" data-hex="true" class="datepicker" name="input_date_start" value="">
                    <span class="input-group-addon">
			<i class="icon-calendar-empty"></i>
                    </span>
		</div>
            </div>
	    <p class="help-block">
		{l s='Please insert start date' mod='mpstock'}
	    </p>
	</div>
    </div>
    <div class="form-group">
	<label class="control-label col-lg-3 required">{l s='End date' mod='mpstock'}</label>
        <div class="col-lg-8">
            <div class="row">
		<div class="input-group col-lg-4">
                    <input id="input_date_end" type="text" data-hex="true" class="datepicker" name="input_date_end" value="">
                    <span class="input-group-addon">
			<i class="icon-calendar-empty"></i>
                    </span>
		</div>
            </div>
	    <p class="help-block">
		{l s='Please insert end date' mod='mpstock'}
	    </p>
	</div>
    </div>
</div>