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
    .input-fixed-300 {
        width: 300px;
    }
</style>
<div class="modal"><!-- Place at bottom of page --></div>
<script type='text/javascript'>
    $body = $("body");
    $(document).on({
        ajaxStart: function() { $body.addClass("loading"); },
         ajaxStop: function() { $body.removeClass("loading"); }    
    });
    $(document).ready(function(){
        /**
         * REPLACING DELETE BUTTON
         */
        $('table.mp_stock>tbody>tr').each(function(){
            var idx = $(this).index();
            var id_movement = $(this).find('td:nth-child(1) input').val();
            var td = $(this).find('td:nth-child(11)');
            var a = $(td).find('a');
            $(a).attr('href', 'javascript:deleteMovement('+id_movement+','+idx+')').removeAttr('onclick');
        });
        /**
         * RESIZING SELECT
         */
        $('.chosen-container').width(400);
        $('#input_select_type_movements').on('change', function(event){
            event.preventDefault();
        });
        /**
         * SELECT PRODUCT ATTRIBUTE ON CHANGE
         */
        $('#input_select_products').on('change', function(event){
            event.preventDefault();
            $.ajax({
                type: 'POST',
                dataType: 'json',
                useDefaultXhrHeader: false,
                data: 
                {
                    ajax: true,
                    action: 'GetProductAttributes',
                    id_product: this.value
                }
            })
            .done(function(result){
                $('#input_text_tax_rate').val(Number(result.tax_rate).toFixed(2));
                $('#input_select_product_attributes').empty();
                $(result.combinations).each(function(){
                    $('#input_select_product_attributes').append("<option value='" + this.id_product_attribute + "'>" + this.name + "</option>");
                });
                $('#input_select_product_attributes').trigger("chosen:updated");
            })
            .fail(function(){
                jAlert("{l s='Error during getting values.' mod='mpstock'}", '{l s='FAIL' mod='mpstock'}');
            });
        });
        /**
         * SELECT ATTRIBUTE PRODUCT ON CHANGE
         */
        $('#input_select_product_attributes').on('change', function(event){
            event.preventDefault();
            $.ajax({
                type: 'POST',
                dataType: 'json',
                useDefaultXhrHeader: false,
                data: 
                {
                    ajax: true,
                    action: 'GetProductAttributeValues',
                    id_product_attribute: this.value
                }
            })
            .done(function(result){
                console.log(result);
                if (result) {
                    $('#input_text_reference').val(result.reference);
                    $('#input_text_ean13').val(result.ean13);
                    $('#input_text_price').val(Number(Number(result.product_price)+Number(result.price)).toFixed(2));
                }
            })
            .fail(function(){
                jAlert("{l s='Error during getting values.' mod='mpstock'}", '{l s='FAIL' mod='mpstock'}');
            });
        });
        /**
         * HIDE SELECT PRODUCT EXCHANGE 
         */
        $('#input_select_products_exchange').closest('.form-group').hide();
        $('#input_select_product_attributes_exchange').closest('.form-group').hide();
        /**
         * SELECT PRODUCT ATTRIBUTE EXCHANGE ON CHANGE
         */
        $('#input_select_products_exchange').on('change', function(event){
            event.preventDefault();
            console.log('show');
            $.ajax({
                type: 'POST',
                dataType: 'json',
                useDefaultXhrHeader: false,
                data: 
                {
                    ajax: true,
                    action: 'GetProductAttributes',
                    id_product: this.value
                }
            })
            .done(function(result){
                $('#input_select_product_attributes_exchange').empty();
                $(result.combinations).each(function(){
                    $('#input_select_product_attributes_exchange').append("<option value='" + this.id_product_attribute + "'>" + this.name + "</option>");
                });
                $('#input_select_product_attributes_exchange').trigger("chosen:updated");
            })
            .fail(function(){
                jAlert("{l s='Error during getting values.' mod='mpstock'}", '{l s='FAIL' mod='mpstock'}');
            });
        });
        /**
         * CHANGE TYPE MOVEMENT
        **/
        $('#input_select_type_movements').on('change', function(){
            $.ajax({
                type: 'POST',
                dataType: 'json',
                useDefaultXhrHeader: false,
                data: 
                {
                    ajax: true,
                    action: 'GetTypeMovement',
                    id_type_movement: this.value
                }
            })
            .done(function(json){
                if (json.result === true) {
                    if(json.transform === 1) {
                        $('#input_hidden_transform').val('1');
                        $('#input_select_products_exchange').closest('.form-group').show();
                        $('#input_select_product_attributes_exchange').closest('.form-group').show();
                    } else {
                        $('#input_hidden_transform').val('0');
                        $('#input_select_products_exchange').closest('.form-group').hide();
                        $('#input_select_product_attributes_exchange').closest('.form-group').hide();
                    }
                    $('#input_hidden_sign').val(json.sign);
                } else {
                    jAlert(json.error_msg, '{l s='JSON ERROR' mod='mpstock'}');
                }
            })
            .fail(function(){
                jAlert("{l s='Error getting values.' mod='mpstock'}", '{l s='FAIL' mod='mpstock'}');
            });
        });
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
         * NEW MOMEMENT
         */
        $('#desc-mp_stock-new').on('click',function(event){
            event.preventDefault();
            insertParam('submitNewMovement', '1');
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
                $('#input_select_feature_value').html('');
                $(result).each(function(){
                    $('#input_select_feature_value').append("<option value='" + this['id_feature_value'] + "'>" + this['name'] + "</option>");
                });
                $('#input_select_feature_value').trigger("chosen:updated");
            })
            .fail(function(){
                jAlert("{l s='Error during getting values.' mod='mpstock'}", '{l s='FAIL' mod='mpstock'}');
            });
        });
        /***************
         * SUBMIT FORM *
         ***************/
        $('#product_form').on('submit', function(event){
            event.preventDefault();
            var parameters = $(this).serializeArray();
            if ($('#input_select_products').val()==='0') {
                jAlert('{l s='Please select a product first' mod='mpstock'}');
                return false;
            }
            if ($('#input_select_product_attributes').val()==='0') {
                jAlert('{l s='Please select a combination first' mod='mpstock'}');
                return false;
            }
            if ($('#input_select_type_movements').val()==='0') {
                jAlert('{l s='Please select a stock movement first' mod='mpstock'}');
                return false;
            }
            if ($('#input_select_products_exchange').closest('.form-group').is(':visible')) {
                if ($('#input_select_products_exchange').val()==='0') {
                    jAlert('{l s='Please select a product to load in stock.' mod='mpstock'}');
                    return false;
                }
                if ($('#input_select_product_attributes_exchange').val()==='0') {
                    jAlert('{l s='Please select a combination to load in stock.' mod='mpstock'}');
                    return false;
                }
            }
            if ($('#input_text_qty').val()==='0') {
                jAlert('{l s='Please select a stock quantity first' mod='mpstock'}');
                return false;
            }
            
            jConfirm('{l s='Are you sure you want to save selected stock movement?' mod='mpstock'}', '{l s='Confirm' mod='mpstock'}', function(r)
            {
                
                console.log(parameters);
                if(r) {
                    $.ajax({
                        type: 'POST',
                        dataType: 'json',
                        useDefaultXhrHeader: false,
                        data: 
                        {
                            ajax: true,
                            action: 'UpdateMovement',
                            parameters: parameters
                        }
                    })
                    .done(function(json){
                        if(json.result === false) {
                            jAlert("{l s='Errors during update.' mod='mpstock'}" + "<br>" + json.error_msg, '{l s='OPERATION DONE' mod='mpstock'}');
                        } else {
                            jAlert("{l s='Stock movement has been updated.' mod='mpstock'}", '{l s='OPERATION DONE' mod='mpstock'}');
                            window.location.href='{$url_main}';
                        }
                    })
                    .fail(function(){
                        jAlert("{l s='Error during getting values.' mod='mpstock'}", '{l s='FAIL' mod='mpstock'}');
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
