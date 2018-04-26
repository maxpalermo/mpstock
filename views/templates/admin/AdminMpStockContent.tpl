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
        <label class="control-label col-lg-1 required text-right">{l s='Product' mod='mpstock'}</label>
	<div class="col-lg-9">
            <input type='text' name='input_id_product' id='input_id_product' class='input' style="width: 80%;">
            <p class="help-block">
                {l s='Insert product reference or product name' mod='mpstock'}
            </p>							
	</div>
    </div>
    <br>
    <div class="form-group" id='mpstock_transform'>
        {include file=$transform_form}
    </div>
    <br>
    <div class="form-group" id='div-table-content'>
        
    </div>
</div>