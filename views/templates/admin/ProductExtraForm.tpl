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
<form method='POST' id="mpstock_productextra">
    <div class="panel">
        {include file=$header_form}
        {include file=$content_form}
        {include file=$footer_form}
    </div>
    <div id='helperlist-content'>
        
    </div>
</form>
    <script type='text/javascript'>
    var current_page = {$page};
    var pagination = {$pagination};
    $(document).ready(function(){
        $('#mpstock_productextra').validate();
        $('#input_date_start').datepicker({ dateFormat: 'yy-mm-dd' });
        $('#input_date_end').datepicker({ dateFormat: 'yy-mm-dd' });
        
        $('#mpstock_submit').on('click', function(){
            var search_in_orders = $('input[name="input_switch_search_in_orders"]:checked').val();
            var search_in_slips = $('input[name="input_switch_search_in_slips"]:checked').val();
            var search_in_movements = $('input[name="input_switch_search_in_movements"]:checked').val();
            var date_start = $('#input_date_start').val();
            var date_end = $('#input_date_end').val();
            
            $("#mpstock_submit i").removeClass('icon-search').addClass('process-icon-loading');
            
            $.ajax({
                type: 'POST',
                dataType: 'json',
                useDefaultXhrHeader: false,
                url: '{$module_link}',
                data: 
                {
                    ajax: true,
                    action: 'FindMovements',
                    module_name: 'mpstock',
                    class_name: 'MpStock',
                    token: '{$module_token}',
                    id_product: {$id_product},
                    id_product_attribute: $('#select_combination').val(),
                    id_employee: {$id_employee},
                    date_start: date_start,
                    date_end: date_end,
                    pagination: pagination,
                    current_page: current_page
                }
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
    
    function mpstock_export_selected()
    {
        $('#helperlist-content table tbody input[type="checkbox"]:checked').each(function(){
            var processedRow = mpstock_process_row($(this).closest('tr'));
        });
    }
    
    function mpstock_process_row(row)
    {
        console.log("row: ", $(row).index(), row);
    }
</script>
    