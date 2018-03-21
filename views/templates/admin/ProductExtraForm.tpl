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
<form method='POST'>
    <div class="panel">
        {include file=$header_form}
        {include file=$content_form}
        {include file=$footer_form}
    </div>
    <div id='helperlist-content'>
        
    </div>
</form>
    <script type='text/javascript'>
    $(document).ready(function(){
        $('#input_date_start').datepicker({ dateFormat: 'yy-mm-dd' });
        $('#input_date_end').datepicker({ dateFormat: 'yy-mm-dd' });
        
        $('#mpstock_submit').on('click', function(){
            var search_in_orders = $('input[name="input_switch_search_in_orders"]:checked').val();
            var search_in_slips = $('input[name="input_switch_search_in_slips"]:checked').val();
            var search_in_movements = $('input[name="input_switch_search_in_movements"]:checked').val();
            var date_start = $('#input_date_start').val();
            var date_end = $('#input_date_end').val();
            
            console.log(search_in_orders, search_in_slips, search_in_movements, date_start, date_end);
            
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
                    id_employee: {$id_employee},
                    date_start: date_start,
                    date_end: date_end
                }
            })
            .done(function(json){
                if (json.result === true) {
                    $('#helperlist-content').html(json.html);
                }
            })
            .fail(function(){
                jAlert("{l s='Error during getting values.' mod='mpstock'}", '{l s='FAIL' mod='mpstock'}');
            });
            
        });
    });
</script>
    