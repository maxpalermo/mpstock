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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2018 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<style>
    .modal {
        display:    none;
        position:   fixed;
        z-index:    1000;
        top:        0;
        left:       0;
        height:     100%;
        width:      100%;
        background: rgba( 255, 255, 255, .8 ) 
                    url('{$loading_gif}') 
                    50% 50% 
                    no-repeat;
    }
    body.loading .modal {
        overflow: hidden;   
    }
    body.loading .modal {
        display: block;
    }
    
</style>
<div class="modal"><!-- Place at bottom of page --></div>
<script type='text/javascript'>
    $body = $("body");
    $(document).on({
        ajaxStart: function() { $body.addClass("loading");    },
         ajaxStop: function() { $body.removeClass("loading"); }    
    });
    $(document).ready(function(){
        /**
         * MANAGE TABLE ORDER BUTTON
         */
        $('#table-tbl-products>thead>tr').first().find('a').on('click', function(event){
            event.preventDefault();
            var direction = 'asc';
            if($(this).index() == 1) {
                direction='desc';
            }
            var index = $(this).closest('th').index();
            console.log('index: ' + index + ', direction: ' + direction);
            sortTable(index, direction);
        });
        
        /**
         * CHECK ALL CHECKBOX
         */
        $('#btn_products_select_all').on('click', function(event){
            event.preventDefault();
            $('input[name="check_product[]"]').prop('checked', true);
        });
        /**
         * UNCHECK ALL CHECKBOX
         */
        $('#btn_products_select_none').on('click', function(event){
            event.preventDefault();
            $('input[name="check_product[]"]').prop('checked', false);
        });
        /**
         * NEW MOVEMENT TYPE
         */
        $('#desc-mp_stock-new').on('click',function(event){
            event.preventDefault();
            console.log('new movement');
            insertParam('submitNewMovement', '1');
        });
        /**
         * Edit selected movement
         */
        $('a[name="btn-edit-movement[]"]').on('click', function(event){
            event.preventDefault();
            var href = this.href;
            jConfirm('{l s ='Edit selected movement?' mod='mpstock'}', '{l s='Confirm' mod='mpstock'}', function(r){
                if(r) {
                    window.location.href=href;
                }
            });
        });
        /**
         * Delete selected movement
         */
        $('a[name="btn-delete-movement[]"]').on('click', function(event){
            event.preventDefault();
            var href = this.href;
            jConfirm('{l s ='Delete selected movement?' mod='mpstock'}', '{l s='Confirm' mod='mpstock'}', function(r){
                if(r) {
                    window.location.href=href;
                }
            });
        });
        
        
        /**
        * CHANGE FEATURE
        */
        $('#input_select_feature').chosen().change(function(event){
            event.preventDefault();
            $.ajax({
                type: 'POST',
                dataType: 'json',
                useDefaultXhrHeader: false,
                data: 
                {
                    ajax: true,
                    action: 'GetFeatureValue',
                    id_feature: this.value
                }
            })
            .done(function(result){
                console.log(result);
                $('#input_select_feature_value').html('');
                $(result).each(function(){
                    $('#input_select_feature_value').append("<option value='" + this['id_feature_value'] + "'>" + this['name'] + "</option>");
                });
                $('#input_select_feature_value').trigger("chosen:updated");
            })
            .fail(function(){
                jAlert("{l s='Error during getting values.' mod='mpmoveto'}", '{l s='FAIL' mod='mpmoveto'}');
            });
        });
        /**
         * Process Products
         */
        $('#btn_products_process').on('click', function(event){
            event.preventDefault();
            var id_products = getIdProducts();
            var parameters = {
                switch_category: Number($('input[name="input_switch_category"]:checked').val()),
                category: Number($('#input_select_category').val()),
                switch_feature: Number($('input[name="input_switch_feature"]:checked').val()),
                feature: Number($('#input_select_feature').val()),
                feature_value: Number($('#input_select_feature_value').val()),
                on_sale: Number($('input[name="input_switch_on_sale"]:checked').val()),
                id_products: id_products
            };
            if (parameters.switch_category === 1 && parameters.category === 0) {
                jAlert('{l s='Please select a category first.' mod='mpmoveto'}');
                return false;
            }
            if (parameters.switch_feature === 1 && parameters.feature === 0) {
                jAlert('{l s='Please select a feature first.' mod='mpmoveto'}');
                return false;
            }
            if (parameters.switch_feature === 1 && parameters.feature_value === 0) {
                jAlert('{l s='Please select a feature value first.' mod='mpmoveto'}');
                return false;
            }
            if (parameters.id_products.length === 0) {
                jAlert('{l s='Please select a product first.' mod='mpmoveto'}');
                return false;
            }
            
            jConfirm('{l s='Are you sure you want to update selected products?' mod='mpmoveto'}', '{l s='Confirm' mod='mpmoveto'}', function(r)
            {
                if(r) {
                    $.ajax({
                        type: 'POST',
                        dataType: 'json',
                        useDefaultXhrHeader: false,
                        data: 
                        {
                            ajax: true,
                            action: 'UpdateProducts',
                            parameters: parameters
                        }
                    })
                    .done(function(json){
                        if(json.result === false) {
                            var errors = json.errors.join('<br>');
                            jAlert("{l s='Errors during update.' mod='mpmoveto'}" + "<br>" + errors, '{l s='OPERATION DONE' mod='mpmoveto'}');
                        } else {
                            jAlert("{l s='Selected products have been updated.' mod='mpmoveto'}", '{l s='OPERATION DONE' mod='mpmoveto'}');
                        }
                        
                    })
                    .fail(function(){
                        jAlert("{l s='Error during getting values.' mod='mpmoveto'}", '{l s='FAIL' mod='mpmoveto'}');
                    });
                }
            });
        });
    });
    
    function getIdProducts()
    {
        var checked = $('input[name="check_product[]"]:checked');
        if (checked === undefined) {
            return [];
        }
        var products = new Array();
        $(checked).each(function(){
            products.push(this.value);
        });
        return products;
    }
    
    function sortTable(col, direction){
        console.log('SORTING');
        var rows = $('#table-tbl-products>tbody>tr').get();
        var typeCell = 'text';
        switch(col) {
            case 0:
                return false;
            case 1:
                typeCell='int';
                break;
            case 2:
                return false;
            case 3:
                typeCell='text';
                break;
            case 4:
                typeCell='text';
                break;
            case 5:
                typeCell='currency';
                break;
            case 6:
                typeCell='currency';
                break;
            case 7:
                typeCell='currency';
                break;
            case 8:
                typeCell='percent';
                break;
        }
        
        rows.sort(function(a, b) {
            var A = valueConvert($(a).children('td').eq(col).text().toUpperCase(), typeCell);
            var B = valueConvert($(b).children('td').eq(col).text().toUpperCase(), typeCell);
            
            var result = 0;

            if(A < B) {
                result = -1;
                if (direction === 'desc') {
                    return result * -1;
                }
                return result;
            }

            if(A > B) {
                result = 1;
                if (direction === 'desc') {
                    return result * -1;
                }
                return result;
            }

            return 0;
        });

        $.each(rows, function(index, row) {
          $('#table-tbl-products>tbody').append(row);
        });
    }
    
    function valueConvert(value, type)
    {
        value = String(value).trim();
        switch(type) {
            case 'text':
                return value;
            case 'int':
                return Number(value);
            case 'currency':
                var num = String(value).split(" ");
                if (isNaN(Number(num[0]))) {
                    num[0] = String(num[0]).replace(',','.');
                }
                
                return Number(num[0]);
            case 'percent':
                var num = String(value).split(" ");
                if (isNaN(Number(num[0]))) {
                    num[0] = String(num[0]).replace(',','.');
                }
                
                return Number(num[0]);
        }
    }
    
    function insertParam(key, value) 
    {
        key = escape(key); value = escape(value);

        var kvp = document.location.search.substr(1).split('&');
        if (kvp == '') {
            document.location.search = '?' + key + '=' + value;
        }
        else {

            var i = kvp.length; var x; while (i--) {
                x = kvp[i].split('=');

                if (x[0] == key) {
                    x[1] = value;
                    kvp[i] = x.join('=');
                    break;
                }
            }

            if (i < 0) { kvp[kvp.length] = [key, value].join('='); }

            //this will reload the page, it's likely better to store this until finished
            document.location.search = kvp.join('&');
        }
    }
</script>
