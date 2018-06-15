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
<script type="text/javascript">
    $(document).ready(function(){
        bind_values();
        var cell = $('input[name="input_text_id_product_exchange"]').closest('td');
        $(cell).append(
            "<input type='hidden' id='id_product_exchange' value='0'>"
        );
        $('input[name="input_text_id_product_exchange"]').autocomplete({
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
                    action: 'fillCombinationsOptions',
                    id_product: ui.item.id,
                    name_product: ui.item.value
                },
                success: function(data)
                {
                    $('#id_product_exchange').val(ui.item.id);
                    $('select[name="input_select_combination_exchange"]').html(data.options).focus();
                }
            });
        }
        });
        /**
         * Change combination
         */
        $('select[name="input_select_combination_exchange"]').on('change', function(){
            $.ajax({
                dataType: 'json',
                data: 
                {
                    ajax: true,
                    action: 'getCurrentStock',
                    id_product_attribute: $('select[name="input_select_combination_exchange"]').val()
                },
                success: function(data)
                {
                    var row = $('select[name="input_select_combination_exchange"]').closest('tr');
                    console.log("row", row);
                    $(row).find('td:nth-child(4)').text(data.stock);
                    $(row).find('td:nth-child(6)').find('input').val(data.wholesale_price);
                    $(row).find('td:nth-child(7)').find('input').val(data.price);
                    $(row).find('td:nth-child(8)').find('input').val(data.tax_rate);
                }
            });
        });
    });
    function saveCombinationExchange(button)
    {
        var row = $(button).closest('tr');
        var mov = {
            id : String($(row).find('td:nth-child(1)').text()).trim(),
            id_exchange : String($(row).closest('form').closest('td').closest('tr').prev().find('td:nth-child(1)').text()).trim(),
            id_product: $(row).find('td:nth-child(2)').find('#id_product_exchange').val(),
            id_product_attribute: $(row).find('td:nth-child(3)').find('select').val(),
            id_product_attribute_name: $(row).find('td:nth-child(3)').find('select option:selected').text(),
            qty: $(row).find('td:nth-child(5)').find('input').val(),
            wholesale_price: $(row).find('td:nth-child(6)').find('input').val(),
            price: $(row).find('td:nth-child(7)').find('input').val(),
            tax_rate: $(row).find('td:nth-child(8)').find('input').val(),
            id_movement: $(row)
                .closest('form')
                .closest('td')
                .closest('tr')
                .prev().find('td:nth-child(3)').find('select').val(),
        };
        $.ajax({
            dataType: 'json',
            data: 
            {
                ajax: true,
                action: 'addMovementExchange',
                movement: mov
            },
            success: function(data)
            {
                if (data.result) {
                    $.growl.notice({
                        message: '{l s='Movement saved successfully.' mod='mpstock'}'
                    });
                    $(row).find('td:nth-child(1)').text(data.id_exchange);
                    $(row).find('td:nth-child(4)').text(data.stock);
                } else {
                    $.growl.error({
                        message: data.message
                    });
                }
            }
        });
    }
    function cancelCombinationExchange(button)
    {
        var row = $(button).closest('tr');
        $(row).closest('form').closest('td').closest('tr').remove();
    }
</script>
