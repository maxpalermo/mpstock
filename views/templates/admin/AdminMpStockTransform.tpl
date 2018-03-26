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
<br style='clear: both;'>
<br style='clear: both;'>
<div class="panel">
    <div class="panel-body">
        <div class="form-group">
            <label class="control-label col-lg-2 required text-right">{l s='Product exchange' mod='mpstock'}</label>
            <div class="col-lg-5">
                <input type='text' name='input_id_product_transform' id='input_id_product_transform' class='input' >
                <p class="help-block">
                    {l s='Insert product reference or product name for product transformation' mod='mpstock'}
                </p>							
            </div>
        </div>
        <br style="clear: both;">
        <div class="form-group">
            <label class="control-label col-lg-2 required text-right">{l s='Product Combination' mod='mpstock'}</label>
            <div class="col-lg-5">
                <select id="input_select_transform">
                    <!-- Ajax fill -->
                </select>
                <p class="help-block">
                    {l s='Insert product reference or product name for product transformation' mod='mpstock'}
                </p>							
            </div>
        </div>
        <br style="clear: both;">
        <div class="form-group">
            <label class="control-label col-lg-2 required text-right">{l s='Quantity' mod='mpstock'}</label>
            <div class="col-lg-5">
                <input type='text' name='input_id_product_transform_qty' id='input_id_product_transform_qty' class='input fixed-width-sm text-right' value='0'>
                <p class="help-block">
                    {l s='Insert quantity' mod='mpstock'}
                </p>							
            </div>
        </div>
    </div>
    <div class="panel-footer"style="height: 96px;">
        <button type="button" value="1" id="mpstock_submit_transform" class="btn btn-default pull-right" style="text-align: center; margin-bottom: 16px;">
            <i class="icon icon-2x icon-save" style="color: #88BB88; margin-bottom: 12px;"></i>
            <br>
            <span>Save</span>
        </button>
        <br>
    </div>
</div>
<br style="clear: both;">
<br style="clear: both;">