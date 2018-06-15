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
.btn-large
{
    margin: 16px;
    padding-left: 12px;
    width: 100%;
    height: 72px;
}
.btn-large:hover
{
    font-weight: bold;
    text-shadow: 2px 2px 4px #555555;
    box-shadow: 3px 3px 6px #555555;
}
.active-button
{
    background-color: #72C279 !important;
    border-color: #60ba68 !important;
    color: #fcfcfc !important;
    font-weight: bold !important;
    font-size: 1.3em !important;
    text-shadow: 2px 2px 4px #555555 !important;
}
.active-button i
{
    color: #fcfcfc !important;
}
</style>
<div class="panel">
    <div class="row">
        <div class="col-md-6" class="text-right">
            <button type="button" class="btn btn-default btn-large" onclick="javascript:setMovementSign(1, event);">
                <i class="icon icon-2x icon-upload"></i>
                &nbsp;
                {l s='LOAD MOVEMENT' mod='mpstock'}
            </button>
        </div>
        <div class="col-md-6" class="text-right">
            <button type="button" class="btn btn-default btn-large" onclick="javascript:setMovementSign(-1, event);">
                <i class="icon icon-2x icon-download"></i>
                &nbsp;
                {l s='UNLOAD MOVEMENT' mod='mpstock'}
            </button>
        </div>
    </div>
    <br>
    <div class="row">
        <div class="col-md-2" class="text-right">
            <label>{l s='Insert EAN13' mod='mpstock'}</label>
            <br>
            <i class="icon icon-4x icon-barcode"></i>
        </div>
        <div class="col-md-10">
            <input type="text" class="input" id="input_text_ean13" style="font-size: 3em; height: auto;">
        </div>
    </div>
    <br>
    <div class="row">
        <div class="col-md-2" class="text-right">
            <label>{l s='Product' mod='mpstock'}</label>
        </div>
        <div class="col-md-10">
            <input type="hidden" id="input_text_id_product">
            <input type="hidden" id="input_text_id_product_attribute">
            <input type="text" class="input" id="input_text_product_name" style="font-size: 1.5em; height: auto;" disabled>
        </div>
    </div>
    <br><div class="row">
        <div class="col-md-2" class="text-right">
            <label>{l s='Quantity' mod='mpstock'}</label>
        </div>
        <div class="col-md-10">
            <input type="text" class="input" id="input_text_product_quantity" style="font-size: 3em; width: 100%; height: auto;" oninput="this.value = this.value.replace(/[^0-9]/g, '');">
        </div>
    </div>
</div>


<div class="row">
    <div class="col-md-12">
        <button type="button" class="btn btn-success btn-large" onclick="javascript:submitQuickMovement(event);">
            <i class="icon icon-2x icon-save"></i>
            &nbsp;
            {l s='SAVE' mod='mpstock'}
        </button>
    </div>
</div>
<script type="text/javascript">
    var movement_sign = 0;
    $(document).ready(function(){
        $("#input_text_ean13").on('focusout', function(){
            getProductByEan13(this.value)
        });
    });
    function submitQuickMovement(event)
    {
        event.preventDefault();
        event.stopPropagation();
        if (movement_sign == 0) {
            $.growl.warning({
                'title': '{l s='Warning' mod='mpstock'}',
                'message': '{l s='Please select a movement type first' mod='mpstock'}',
            });
            return false;
        }
        if ($('#input_text_ean13').val() == '') {
            $.growl.warning({
                'title': '{l s='Warning' mod='mpstock'}',
                'message': '{l s='Please select a product first' mod='mpstock'}',
            });
            return false;
        }
        var product = {
            id_product: $('#input_text_id_product').val(),
            id_product_attribute: $('#input_text_id_product_attribute').val(),
            quantity: $('#input_text_product_quantity').val(),
            sign: movement_sign
        };
        $.ajax({
            type: 'post',
            dataType: 'json',
            data:
            {
                ajax: true,
                action: 'addQuickMovement',
                product: product
            },
            success: function(response){
                if (response.result) {
                    $.growl.notice({
                        'title': 'Save movement',
                        'message': 'Movement saved. new stock'+': '+response.current_stock
                    })
                }
            },
            error: function(response){
                console.log(response);
            }
        });
    }
    function only_num() {
        var keyCode = window.event.keyCode;
        console.log('key: ', keyCode, window.event);
        if ( keyCode >= 48 && keyCode <= 57) {
            return true;
        } else {
            return true;
        }
    }
    function setMovementSign(sign, event)
    {
        movement_sign = sign;
        var button = document.activeElement;  
        var buttons = $(button).closest(".row").find('button');
        
        if (movement_sign == 1) {
            $(buttons[0]).addClass('active-button');
            $(buttons[1]).removeClass('active-button');
        } else {
            $(buttons[1]).addClass('active-button');
            $(buttons[0]).removeClass('active-button');
        }

        $('#input_text_ean13').focus();
    }
    function getProductByEan13(ean13)
    {
        $.ajax({
            dataType: 'json',
            data:
            {
                ajax: true,
                action: 'getProductByEan13',
                ean13: ean13,
            },
            success: function(data)
            {
                $('#input_text_id_product').val(data.id_product);
                $('#input_text_id_product_attribute').val(data.id_product_attribute);
                $('#input_text_product_name').val(data.name);
                $('#input_text_product_quantity').val(data.quantity);
                return true;
            },
            error: function()
            {
                return false;  
            }
        });
    }
</script>
