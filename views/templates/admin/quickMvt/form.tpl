{*
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    Massimiliano Palermo <maxx.palermo@gmail.com>
 * @copyright Since 2016 Massimiliano Palermo
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 *}

<style>
    .btn-large {
        margin: 16px;
        padding-left: 12px;
        width: 100%;
        height: 72px;
    }

    .btn-large:hover {
        font-weight: bold;
        text-shadow: 2px 2px 4px #555555;
        box-shadow: 3px 3px 6px #555555;
    }

    .active-button {
        background-color: #72C279 !important;
        border-color: #60ba68 !important;
        color: #fcfcfc !important;
        font-weight: bold !important;
        font-size: 1.3em !important;
        text-shadow: 2px 2px 4px #555555 !important;
    }

    .active-button i {
        color: #fcfcfc !important;
    }

    .input-large {
        font-size: 2em !important;
        height: auto !important;
        padding: 12px !important;
    }

    .input-center {
        text-align: center !important;
        width: 75% !important;
        margin: 12px auto !important;
    }

    .bootstrap input[type="number"] {
        display: block;
        width: 100%;
        height: 31px;
        padding: 6px 8px;
        font-size: 12px;
        line-height: 1.42857;
        color: #555;
        background-color: #F5F8F9;
        background-image: none;
        border: 1px solid #C7D6DB;
        border-radius: 3px;
        -webkit-transition: border-color ease-in-out 0.15s, box-shadow ease-in-out 0.15s;
        -o-transition: border-color ease-in-out 0.15s, box-shadow ease-in-out 0.15s;
        transition: border-color ease-in-out 0.15s, box-shadow ease-in-out 0.15s;
    }

    .image {
        height: 100%;
        width: 100%;
        object-fit: contain;
        padding: 16px;
        border: 1px solid #C7D6DB;
        border-radius: 5px;
    }
</style>
<div class="panel" style="overflow: hidden;">
    <div class="col-md-12">
        <!-- TYPE MOVEMENT -->
        <div class="row text center" style="margin-bottom: 2rem;">
            <div class="col-md-6" class="text-right">
                <button type="button" class="btn btn-default btn-large" onclick="javascript:setMovementSign(1, event);">
                    <i class="icon icon-2x icon-upload"></i>
                    &nbsp;
                    {l s='CARICO' mod='mpstock'}
                </button>
            </div>
            <div class="col-md-6" class="text-right">
                <button type="button" class="btn btn-default btn-large"
                    onclick="javascript:setMovementSign(-1, event);">
                    <i class="icon icon-2x icon-download"></i>
                    &nbsp;
                    {l s='SCARICO' mod='mpstock'}
                </button>
            </div>
        </div>
        <!-- PRODUCT -->
        <div class="row text center">
            <div class="col-md-2">
                <div style="margin-left: 12px; margin-top: 24px;">
                    {include file="./no-image.tpl"}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group text-center">
                    <label style="font-size: 2rem;"><i class="icon icon-barcode"></i>
                        {l s='Inserisci EAN13' mod='mpstock'}</label>
                    <input type="text" class="form-control input-large input-center" id="input_text_ean13">
                </div>
                <div class="form-group text-center">
                    <label style="font-size: 2rem;">{l s='Quantità' mod='mpstock'}</label>
                    <input type="number" id="input_text_product_quantity" class="form-control input-large input-center"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '');" min="1">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group text-center">
                    <label style="font-size: 2rem;">{l s='Prodotto' mod='mpstock'}</label>
                    <input type="hidden" id="input_text_id_product">
                    <input type="hidden" id="input_text_id_product_attribute">
                    <label id="input_text_product_name" class="form-control"
                        style="font-size: 2rem; height: auto;">--</label>
                </div>
            </div>
        </div>
        <div class=" form-group text-center">
            <button type="button" class="btn btn-success btn-large"
                style="width: auto; padding-left: 2rem; padding-right: 2rem; margin-top: 16px; margin-bottom: 16px; height: auto;"
                onclick="javascript:submitQuickMovement(event);">
                <i class="icon icon-4x icon-save"></i>
                &nbsp;
                <span style="font-size: 2rem;">{l s='SALVA' mod='mpstock'}</span>
            </button>
        </div>
    </div>
</div>

<script type="text/javascript">
    var movement_sign = 0;
    $(function() {
        $("input").on('focus', function() {
            $(this).select();
        });
        $("#input_text_ean13").on('focusout', function() {
            getProductByEan13(this.value)
        });
    });

    function submitQuickMovement(event) {
        event.preventDefault();
        event.stopPropagation();
        if (movement_sign == 0) {
            $.growl.warning({
                'title': '{l s='Warning' mod='mpstock'}',
                'message': '{l s='Seleziona un tipo di movimento' mod='mpstock'}',
            });
            return false;
        }
        if ($('#input_text_ean13').val() == '') {
            $.growl.warning({
                'title': '{l s='Warning' mod='mpstock'}',
                'message': '{l s='Codice EAN13 non valido' mod='mpstock'}',
            });
            return false;
        }
        var data = {
            id_product: $('#input_text_id_product').val(),
            id_product_attribute: $('#input_text_id_product_attribute').val(),
            quantity: $('#input_text_product_quantity').val(),
            sign: movement_sign,
            ajax: true,
            action: 'addQuickMovement'
        };

        $.post( "{$ajax_controller}", data, function(response) 
        {
            if (response.success) {
                $.growl.notice({
                    'title': '{l s='Operazione eseguita' mod='mpstock'}',
                    'message': '{l s='Movimento salvato' mod='mpstock'}',
                });
            } else {
                $.growl.error({
                    'title': '{l s='Errore durante il salvataggio del movimento' mod='mpstock'}',
                    'message': response.message,
                });
            }
        });
    }

    function setMovementSign(sign, event) {
        movement_sign = sign;
        var button = document.activeElement;
        var
            buttons = $(button).closest(".row").find('button');
        if (movement_sign == 1) {
            $(buttons[0]).addClass('active-button');
            $(buttons[1]).removeClass('active-button');
        } else {
            $(buttons[1]).addClass('active-button');
            $(buttons[0]).removeClass('active-button');
        }
        $('#input_text_ean13').focus();
    }

    function getProductByEan13(ean13) {
        let data = {
            ajax: true,
            action: 'getProductByEan13' , ean13: ean13, }; $.post( "{$ajax_controller}" , data, function(response) {
            $('#input_text_id_product').val(response.id_product);
            $('#input_text_id_product_attribute').val(response.id_product_attribute);
            $('#input_text_product_name').html(response.name);
            $('#input_text_product_quantity').val(response.quantity);
            $('#product_image').attr("src", response.image);
            if ("class" in response) {
                $('#input_text_product_name').removeClass('^=text-').addClass('text-center text-bold')
                    .addClass(response.class);
            }
            return true;
        });
    }
</script>