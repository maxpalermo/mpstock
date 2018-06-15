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
    var decimal_point = $('#mp_stock_decimal_point').val();
    var thousands_sep = $('#mp_stock_thousands_sep').val();
    var currency_char = $('#mp_stock_currency_char').val();

    $(document).ready(function(){
        bind_values();
    });
    
    function bind_values()
    {
        $('.input-price').on('blur', function(){
            ajaxProcessFormat(this.value, 'price', this);
        });
        $('.input-percent').on('blur', function(){
            ajaxProcessFormat(this.value, 'percent', this);
        });
    }

    function ajaxProcessFormat(value, type, item)
    {
        $.ajax({
            dataType: 'json',
            data:
            {
                ajax: true,
                action: 'formatValue',
                value: value,
                type: type,
            },
            success: function(data)
            {
                
                item.value = data.value;
                return true;
            },
            error: function()
            {
                item.value = 0;
                return false;  
            }
        });
    }

    /**
     * Extract digits and decimal separator from a string
     * @param { type } str String to be parsed
     * @returns { Number } Parsed number
     */
    function extractNumbers(str)
    {
        var result = [];
        for (var i=0; i<str.length; i++) {
            var char = String(str).substr(i,1);
            if (/^[0-9,.]*$/.test(char)) {
                result.push(char);
            }
        }
        var number = result.join("");
        
        if (isNaN(number)) {
            var str = String(number).replace(".", "");
            var str2 = String(str).replace(",", ".");
        } else {
            return Number(number);
        }
        
        if (isNaN(str2)) {
            return 0;
        } else {
            return Number(str2)
        }
        
        return number;
    }
    function saveCombination(button)
    {
        var row = $(button).closest('tr');
        var record = {
            id: $(row).find('td:nth-child(1)').text().trim(),
            id_product_attribute: $(row).find('td:nth-child(2)').text().trim(),
            movement: $(row).find('td:nth-child(3)').find('select').val(),
            name: $(row).find('td:nth-child(4)').text().trim(),
            reference: $(row).find('td:nth-child(5)').text().trim(),
            ean13: $(row).find('td:nth-child(6)').text().trim(),
            qty: $(row).find('td:nth-child(8)').find('input').val(),
            wholesale_price: $(row).find('td:nth-child(9)').find('input').val(),
            price: $(row).find('td:nth-child(10)').find('input').val(),
            tax_rate: $(row).find('td:nth-child(11)').find('input').val()
        };
        console.log('movement: ', record.movement);
        if (record.movement == 0) {
            $.growl.warning({
                title: '{l s='WARNING' mod='mpstock'}',
                message: '{l s='Select a valid movement first' mod='mpstock'}'
            });
            return false;
        }
        console.log('qty: ', record.qty);
        if (record.qty == 0) {
            $.growl.warning({
                title: '{l s='WARNING' mod='mpstock'}',
                message: '{l s='Quantity must be greater than zero' mod='mpstock'}'
            });
            return false;
        }

        $.ajax({
            dataType: 'json',
            data:
            {
                ajax: true,
                action: 'addMovement',
                record: record
            },
            success: function(data)
            {
                console.log(data);
                if (data.result) {
                    $.growl.notice({
                        title: "{l s='Add new movement' mod='mpstock'}",
                        message: data.message
                    });
                    $(row).find('td:nth-child(1)').text(data.id);
                    $(row).find('td:nth-child(7)').text(data.stock);
                    $(row).find('td:nth-child(13)').find('i').removeClass('icon-edit').addClass('icon-ok-sign');
                    if (data.exchange) {
                        stockExchange(row, data.id, data.qty, data.form);
                    }
                } else {
                    $.growl.error({
                        title: "{l s='Add new movement' mod='mpstock'}",
                        message: data.message
                    });
                }
            }
        });
    }

    function stockExchange(row, id, qty, form)
    {
        console.log('EXCHANGE!', $(row).html());
        $(row).after('<tr><td colspan=13>'+form+'</td></tr>');
    }

    function deleteCombination(button)
    {
        if(confirm('{l s='Delete selected movement?' mod='mpstock'}') === false) {
            return false;
        }
        var row = $(button).closest('tr');
        var record = {
            id: $(row).find('td:nth-child(1)').text().trim()
        };
        $.ajax({
            dataType: 'json',
            data:
            {
                ajax: true,
                action: 'delMovement',
                record: record
            },
            success: function(data)
            {
                console.log(data);
                if (data.result) {
                    $.growl.notice({
                        title: "{l s='Delete movement' mod='mpstock'}",
                        message: data.message
                    });
                    $(row).find('td:nth-child(13)').find('i').removeClass('icon-ok-sign').addClass('icon-edit');
                    $(row).find('td:nth-child(7)').text(data.stock);
                    $(row).find('td:nth-child(1)').text(0);
                } else {
                    $.growl.error({
                        title: '{l s='Error' mod='mpstock'}',
                        message: data.message
                    });
                    return false;
                }
            }
        });
    }

</script>
