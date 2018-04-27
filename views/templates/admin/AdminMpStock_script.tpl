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
    /** Bootstrap autocomplete **/
    .ui-autocomplete {
        max-height: 10em;
        overflow-y: auto;
        overflow-x: hidden;
        position: absolute;
        top: 100%;
        left: 0;
        z-index: 1000;
        float: left;
        display: none;
        min-width: 160px;
        _width: 160px;
        padding: 4px 0;
        margin: 2px 0 0 0;
        list-style: none;
        background-color: #ffffff;
        border-color: #ccc;
        border-color: rgba(0, 0, 0, 0.2);
        border-style: solid;
        border-width: 1px;
        box-shadow: 0 5px 10px rgba(0, 0, 0, 0.2);
        -webkit-background-clip: padding-box;
        -moz-background-clip: padding;
        background-clip: padding-box;
        *border-right-width: 2px;
        *border-bottom-width: 2px;
    }
    .ui-menu-item > a.ui-corner-all {
          display: block;
          padding: 3px 15px;
          clear: both;
          font-weight: normal;
          line-height: 18px;
          color: #555555;
          white-space: nowrap;
    }
    &.ui-state-hover, &.ui-state-active {
              color: #ffffff;
              text-decoration: none;
              background-color: #0088cc;
              border-radius: 0px !important;
              -webkit-border-radius: 0px !important;
              -moz-border-radius: 0px !important;
              background-image: none !important;
    }

    .ui-autocomplete-loading { background: white url("{$img_folder}ui-anim_basic_16x16.gif") right center no-repeat !important; }

    #mpstock_transform
    {
      display: none;
    }
</style>

<form method='POST' id="mpstock_admin">
    <div class="panel">
        {include file=$header_form}
        {include file=$content_form}
        {include file=$footer_form}
    </div>
    <div id='helperlist-content'>
        
    </div>
</form>
<script type='text/javascript'>
    var row = {};
    var tr = null;
    var current_page = {$page};
    var pagination = {$pagination};
    var current_id_product = 0;
    var current_id_product_attribute = 0;
    var current_id_product_transformation = 0;
    var current_id_product_attribute_transformation = 0;
    var current_id_product_ajax = 0;
    var current_id_product_attribute_ajax = 0;
    
    /**
     * Prototype function for currency format
     */
    String.prototype.formatMoney = function(c, d, t, cur){
    var n = this;
    if (String(n).indexOf('€')>-1 || String(n).indexOf('%')>-1 || String(n).indexOf('$')>-1)
    {
        n = String(n).slice(0, -2);
    }
    
    if (isNaN(Number(n))) {
        n = String(n).replace(',', '.');
    }
    
    var c = isNaN(c = Math.abs(c)) ? 2 : c, 
        d = d === undefined ? "." : d, 
        t = t === undefined ? "," : t, 
        s = n < 0 ? "-" : "", 
        i = String(parseInt(n = Math.abs(Number(n) || 0).toFixed(c))), 
        j = (j = i.length) > 3 ? j % 3 : 0;
        cur = cur === undefined ? "" : " " + cur;
        return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "") + cur;
    };
    
    $(document).ready(function(){
        /**
         * 
         * format input element
         */
        $('.input-float').on('blur', function(){
            var number = extractNumbers(this.value);
            this.value = Number(number).toFixed(2);
        });
        $('.input-integer').on('blur', function(){
            var number = extractNumbers(this.value);
            this.value = Number(number).toFixed(0);
        });
        /**
         * Looking for a product for transformation
         */
        $("#input_id_product_transform").autocomplete({
            source: function( request, response ) {
                $.ajax({
                    dataType: "json",
                    data: 
                    {
                        ajax: true,
                        action: 'GetProduct',
                        term: request.term
                    }
                })
                .success(function(data) {
                    response(data);
                })
                .fail(function(){
                    jAlert('AJAX FAIL');
                });
            },
            minLength: 2,
            select: function( event, ui ) {
                event.preventDefault();
                current_id_product_transformation = Number(ui.item.id);
                mpstock_getProductCombinationTransform(ui.item.id);
            }
        });
        /**
         * Save Stock transformation movement
         */
        $('#mpstock_submit_transform').on('click', function(){
            let status = mpstock_InsertStockMovementTransformation();
            
        });
        /**
         * Lookig for a product
         */
        $("#input_id_product").autocomplete({
            source: function( request, response ) {
                $.ajax({
                    dataType: "json",
                    data: 
                    {
                        ajax: true,
                        action: 'GetProduct',
                        term: request.term
                    }
                })
                .success(function(data) {
                    response(data);
                })
                .fail(function(){
                    jAlert('AJAX FAIL');
                });
            },
            minLength: 2,
            select: function( event, ui ) {
                event.preventDefault();
                current_id_product = Number(ui.item.id);
                mpstock_getProductCombination(ui.item.id);
            }
        });
    });
    
    function mpstock_getProductCombination(id_product)
    {
        $.ajax({
            dataType: "json",
            data: 
            {
                ajax: true,
                action: 'GetProductCombinations',
                id_product: id_product,
                output: 'table'
            }
        })
        .success(function(data) {
            $('#div-table-content').html(data.html);
            /**
             * SAVE MOVEMENT
             */
            $('#div-table-content').on('click', 'button', function(){
                tr = $(this).closest('tr');
                let id_movement = String($(tr).find('td:nth-child(2)').find('select').val()).split('_');
                current_id_product_attribute = String($(tr).find('td:nth-child(1)').text()).trim();
                row = 
                    {
                        index: $(tr).index(),
                        id_product: current_id_product,
                        id_product_attribute: current_id_product_attribute,
                        type_movement: id_movement[0],
                        exchange: id_movement[1],
                        sign: id_movement[2],
                        reference: $(tr).find('td:nth-child(4)').find('input').val(),
                        ean13: $(tr).find('td:nth-child(5)').find('input').val(),
                        qty: Number($(tr).find('td:nth-child(6)').find('input').val()),
                        price: $(tr).find('td:nth-child(7)').find('input').val(),
                        tax_rate: $(tr).find('td:nth-child(8)').find('input').val(),
                        date_movement: 0
                    };
                
                if (row.qty === 0) {
                    $.growl.error({
                        title: "",
                        size: "large",
                        message: "{l s='Invalid quantity.' mod='mpstock'}"
                    });
                    return false;
                }
                /**
                * SAVE PROCEDURE
                **/
                mpstock_InsertMovement();
            });
            $('#div-table-content').on('blur', 'input', function(){
                console.log("blur", this.name);
                if (this.name === 'input_price[]') {
                    this.value = String(this.value).formatMoney(2, ',', '.', '€');
                } else if (this.name === 'input_tax_rate[]') {
                    this.value = String(this.value).formatMoney(2, ',', '.', '%');
                } else if (this.name === 'input_qty[]') {
                    this.value = Number(this.value).valueOf();
                    if (isNaN(this.value)) {
                        this.value = 0;
                    }
                }
            });
        })
        .fail(function(){
            jAlert('AJAX FAIL');
        });
    }
    
    function mpstock_resultInsertTransformation(data)
    {
        var status = data.result;
        $('#mpstock_transform').fadeOut(300);
        if (status) {
            $.growl.notice({
                    title: "",
                    size: "large",
                    message: "{l s='Stock tranformation movement saved.' mod='mpstock'}"
            });
            $(tr).find('td:nth-child(10)').find('i').removeClass('icon-pencil-square-o').addClass('icon-ok-sign').css({ color: '#88BB88' });
        } else {
            $.growl.error({
                    title: "{l s='Error saving stock transformation movement.' mod='mpstock'}",
                    size: "large",
                    message: data.msg_error
            });
            $(tr).find('td:nth-child(10)').find('i').removeClass('icon-pencil-square-o').addClass('icon-times').css({ color: '#BB5555' });
        }
    }
    
    function mpstock_resultInsert(data)
    {
        var status = data.result;
        if (status === true) {
            $.growl.notice({
                title: "",
                size: "large",
                message: "{l s='Stock movement saved.' mod='mpstock'}"
            });

            if (Number(row.exchange) === 1) {
                current_id_product_transformation = 0;
                $('#input_id_product_transform').val('');
                $('#input_select_transform').html('');
                $('#input_id_product_transform_qty').val(row.qty);
                $("#mpstock_transform").fadeIn().find('input[name="input_id_product_transform"]').focus();
            } else {
                $(tr).find('td:nth-child(10)').find('i').removeClass('icon-pencil-square-o').addClass('icon-ok-sign').css({ color: '#88BB88' });
            }
        } else {
            $.growl.error({
                title: "{l s='Error saving stock movement.' mod='mpstock'}",
                size: "large",
                message: data.msg_error
            });
            $(tr).find('td:nth-child(10)').find('i').removeClass('icon-pencil-square-o').addClass('icon-times').css({ color: '#BB5555' });
        }
    }
    
    function mpstock_getProductCombinationTransform(id_product)
    {
        $.ajax({
            dataType: "json",
            data: 
            {
                ajax: true,
                action: 'GetProductCombinations',
                id_product: id_product,
                output: 'select'
            }
        })
        .success(function(data) {
            $('#input_select_transform').html(data.html);
        })
        .fail(function(){
            jAlert('AJAX FAIL');
        });
    }
    
    function mpstock_InsertMovement(transform = false)
    {
        $.ajax({
            type: "POST",
            dataType: "json",
            data: 
            {
                ajax: true,
                action: 'UpdateMovement',
                row: row
            }
        })
        .success(function(data) {
            if (transform) {
                mpstock_resultInsertTransformation(data);
            } else {
                mpstock_resultInsert(data);
            }           
        })
        .fail(function(){
            jAlert('AJAX FAIL');
            return false;
        });
    }
    
    function mpstock_InsertStockMovementTransformation()
    {
        let id_product = Number(current_id_product_transformation);
        let id_product_attribute = Number($('#input_select_transform').val());
        let qty = Number($('#input_id_product_transform_qty').val());

        if (isNaN(id_product) || id_product === 0) {
            $.growl.error({
                title: "",
                size: "large",
                message: "{l s='Product id is not valid.' mod='mpstock'}"
            });
            return false;
        }
        
        if (isNaN(id_product_attribute) || id_product_attribute === 0) {
            $.growl.error({
                title: "",
                size: "large",
                message: "{l s='Product attribute id is not valid.' mod='mpstock'}"
            });
            return false;
        }
        
        if (isNaN(qty) || qty === 0) {
            $.growl.error({
                title: "",
                size: "large",
                message: "{l s='Quantity is not valid.' mod='mpstock'}"
            });
            return false;
        }
        
        let id_movement = String($(tr).find('td:nth-child(2)').find('select').val()).split('_');
        
        row = 
            {
                index: $(tr).index(),
                id_product: id_product,
                id_product_attribute: id_product_attribute,
                type_movement: id_movement[0],
                exchange: id_movement[1],
                sign: -id_movement[2],
                reference: '',
                ean13: '',
                qty: qty,
                price: 0,
                tax_rate: 0,
                date_movement: 0
            };
            
        mpstock_InsertMovement(true);
    }
    
    function mpstock_process_row(row)
    {
        console.log("row: ", $(row).index(), row);
    }
</script>
    
