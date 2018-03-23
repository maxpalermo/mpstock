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
  .ui-autocomplete-loading 
  {
    background: white url("{$img_folder}ui-anim_basic_16x16.gif") right center no-repeat !important;
  }
  .ui-autocomplete 
  {
    max-height: 10em;
    overflow-y: auto;
    /* prevent horizontal scrollbar */
    overflow-x: hidden;
  }
</style>

<div id="growls" class="default">
    <div class="growl-message">Dati salvati</div>
</div>

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
    Number.prototype.formatMoney = function(c, d, t, cur){
    var n = this, 
        c = isNaN(c = Math.abs(c)) ? 2 : c, 
        d = d == undefined ? "." : d, 
        t = t == undefined ? "," : t, 
        s = n < 0 ? "-" : "", 
        i = String(parseInt(n = Math.abs(Number(n) || 0).toFixed(c))), 
        j = (j = i.length) > 3 ? j % 3 : 0;
        cur = cur == undefined ? "" : " " + cur;
        return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "") + cur;
    };
    var current_page = {$page};
    var pagination = {$pagination};
    $(document).ready(function(){
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
                mpstock_getProductCombination(ui.item.id);
            }
        });
        
        $('#mpstock_submit').on('click', function(){
            var date_start = $('#input_date_start').val();
            var date_end = $('#input_date_end').val();
            
            $("#mpstock_submit i").removeClass('icon-search').addClass('process-icon-loading');
            
            $.ajax({
            })
            .done(function(json){
                if (json.result === true) {
                    $('#helperlist-content').off('click').off('change');
                    $('#helperlist-content').html(json.html);
                    $('#mpstock_productextra').validate();
                    
                    $('#select_current_page').on('change', function(){
                        current_page = this.value;
                        console.log('change page to', current_page);
                        $('#mpstock_submit').click();
                    });

                    $('#select_current_pagination').on('change', function(){
                        current_page = 1;
                        pagination = this.value;
                        console.log('change pagination to', pagination);
                        $('#mpstock_submit').click();
                    });
                    
                    $('#helperlist-content').on('click', 'input[type="checkbox"]', function(){
                        if (this.id==='chkSelectRows') {
                            let checked = this.checked;
                            $('#helperlist-content table tbody input[type="checkbox"]').each(function(){
                                this.checked = checked;
                            });
                        }
                            
                    });
                    $("#mpstock_submit i").removeClass('process-icon-loading').addClass('icon-search');
                }
            })
            .fail(function(){
                jAlert("{l s='Error during getting values.' mod='mpstock'}", '{l s='FAIL' mod='mpstock'}');
                $("#mpstock_submit i").removeClass('process-icon-loading').addClass('icon-search');
            });
            
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
                id_product: id_product
            }
        })
        .success(function(data) {
            $('#div-table-content').html(data.html);
            $('#div-table-content').on('click', 'button', function(){
                let tr = $(this).closest('tr');
                let row = 
                    {
                        id_product_attribute: String($(tr).find('td:nth-child(1)').text()).trim(),
                        type_movement: $(tr).find('td:nth-child(2)').find('select').val(),
                        reference: $(tr).find('td:nth-child(4)').find('input').val(),
                        ean13: $(tr).find('td:nth-child(5)').find('input').val(),
                        qty: $(tr).find('td:nth-child(6)').find('input').val(),
                        price: $(tr).find('td:nth-child(7)').find('input').val(),
                        tax_rate: $(tr).find('td:nth-child(8)').find('input').val()
                    };
                jAlert(JSON.stringify(row));
            });
            $('#div-table-content').on('blur', 'input', function(){
                console.log("blur", this.name);
                if (this.name === 'input_price[]') {
                    this.value = Number(this.value).formatMoney(2, ',', '.', '€');
                } else if (this.name === 'input_tax_rate[]') {
                    this.value = Number(this.value).formatMoney(2, ',', '.', '%');
                }
            });
        })
        .fail(function(){
            jAlert('AJAX FAIL');
        });
    }
    
    function mpstock_process_row(row)
    {
        console.log("row: ", $(row).index(), row);
    }
</script>
    