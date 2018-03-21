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
    $('#form-mp_stock_list_movements').append('<input type="hidden" name="ajax" value="1">');
    $('#form-mp_stock_list_movements').append('<input type="hidden" name="action" value="SubmitTableForm">');
    bindAjaxReload();
});
function bindAjaxReload()
{
    var form = $('#form-mp_stock_list_movements');
    $(form).unbind();
    $(form).data('validator', null);
    $(form).validate();
    
    $('#form-mp_stock_list_movements').attr({ action: 'javascript:void(0);' }).on('submit', function(){
        var item = $(':focus');
        console.log(item);
    });
    
    $('#submitFilterButtonmp_stock_list_movements').on('click', function(event){
        event.preventDefault();
        let date_start = $('#local_mp_stock_list_movementsFilter_date_add_0').val();
        let date_end = $('#local_mp_stock_list_movementsFilter_date_add_1').val();
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
                $('#form-mp_stock_list_movements div').remove();
                $('#form-mp_stock_list_movements').append(json.html);
                bindAjaxReload();
            }
        })
        .fail(function(){
            jAlert("{l s='Error during getting values.' mod='mpstock'}", '{l s='FAIL' mod='mpstock'}');
        });
    });
    
    $('a .pagination-link').on('click', function(event){
        event.preventDefault();
        jAlert('Page: ' + this.value);
    });
    
    $('#mp_stock_list_movements-pagination-items-page').on('change', function(event){
        event.preventDefault();
        jAlert('Pagination: ' + this.value);
    });
}
</script>