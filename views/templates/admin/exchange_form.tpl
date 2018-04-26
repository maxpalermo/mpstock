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
<form type="post" id='form_exchange'>
    <div class="panel">
        <input type="hidden" id='hidden_id_mp_stock_exchange' value='{if isset($id_mp_stock)}{$id_mp_stock}{/if}'>
            
        <div class='row'>
            <div class="col-md-6">
                <label for="input_exchange_product">{l s='Select a product' mod='mpstock'}</label>
                <input
                    type='text'
                    id='input_exchange_product'
                    value='{if isset($product_name)}{$product_name}{/if}'
                    class='input'
                    {if isset($product_name)}disabled{/if}
                    >
            </div>
            <div class='col-md-4'>
                <label for="input_select_exchange_combination">{l s='Select a combination' mod='mpstock'}</label>
                <select id='input_select_exchange_combination' class='select chosen' {if isset($product_name)}disabled{/if}>
                    {if isset($product_option)}
                        <option value='{$product_option.value}'>{$product_option.text}</option>
                    {/if}
                </select>
            </div>
            <div class="form-group" class='col-md-2'>
                <label for="input_exchange_product_qty">{l s='Insert quantity' mod='mpstock'}</label>
                <input type='text' id='input_exchange_product_qty' 
                       value='{if isset($product_qty)}{$product_qty}{else}0{/if}' 
                       class='input fixed-width-md text-right'>
            </div>
        </div>
            
        <div class='form-group'>    
            <button type='button' class='btn btn-success pull-right' id='btn_save_exchange' style='margin-left: 12px;'>
                <i class='icon process-icon-save'></i>&nbsp;
                {l s='Save' mod='mpstock'}
            </button>
            
            <button type='button' class='btn btn-default pull-right' id='btn_cancel_exchange'>
                <i class='icon process-icon-cancel'></i>&nbsp;
                {l s='Cancel' mod='mpstock'}
            </button>
        </div>
    </div>
</form>
<script type="text/javascript">
    $(document).ready(function(){
        $('#btn_save_exchange').on('click', function(){
            var id_product_attribute = $('#input_select_exchange_combination').val();
            var id_product_attribute_name = '';

            $('#input_select_exchange_combination option').each(function(){
                if(this.value === id_product_attribute) {
                    id_product_attribute_name = $(this).text();
                    return true;
                }
            });
            
            
            $.ajax({
                dataType: 'json',
                data:
                {
                    ajax: true,
                    action: 'addMovementExchange',
                    id: $('#hidden_id_mp_stock_exchange').val(),
                    id_product_attribute: id_product_attribute,
                    id_product_attribute_name: id_product_attribute_name,
                    qty: $('#input_exchange_product_qty').val()
                },
                success: function(data)
                {
                    if (data.result) {
                        $.growl.notice({
                            title: '{l s='Save exchange movement' mod='mpstock'}',
                            message: data.message
                        });
                        $('#form_exchange').closest('tr').remove();
                    } else {
                        $.growl.error({
                            title: '{l s='Save exchange movement' mod='mpstock'}',
                            message: data.message
                        });
                    }
                },
                error: function()
                {
                    $.growl.error({
                        title: '{l s='Ajax error' mod='mpstock'}',
                        message: '{l s='Server error during ajax call' mod='mpstock'}'
                    });
                }
            });
        });
        $('#btn_cancel_exchange').on('click', function(){
            var row = $(this).closest('tr');
            $(row).remove();
        });
        $('#input_exchange_product').autocomplete({
            source: function (request, response) {
                $.ajax({
                    dataType: 'json',
                    data: 
                    {
                        ajax: true,
                        action: 'autocompleteProduct',
                        term: request.term
                    },
                    success: function(data)
                    {
                        response(data);
                    }
                });
            },
            minLength: 3,
            select: function(event, ui)
            {
                event.preventDefault();
                //console.log("id: ", ui.item.id, "value:", ui.item.value);
                $.ajax({
                    dataType: 'json',
                    data: 
                    {
                        ajax: true,
                        action: 'showCombinationsForm',
                        id_product: ui.item.id,
                        name_product: ui.item.value
                    },
                    success: function(data)
                    {
                        $('#input_select_exchange_combination').html(data.options);
                    }
                });
            }
        });
    });
</script>
