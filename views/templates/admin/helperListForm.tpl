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
*  @copyright 2018 Digital Solutions®
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<div class="panel">
    <div class="panel-heading">
        <i class="icon-cogs" style='color: #21a3d8; margin-right: 10px;'></i>
        {l s='Table options' mod='mpstock'}
        <span class='badge'>{$totList}</span>
        <span class='panel-heading-action' style='text-align: center;'>
            <a id="btn-date-between" class="list-toolbar-btn" href="javascript:void(0);" onclick='$("#div-date-between").toggle();'>
                <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="{l s='Find movements' mod='mpstock'}" data-html="true" data-placement="top">
                        <i class='icon-calendar' style='color: #2CA0FD;'></i>
                </span>
            </a>
            <a id="btn-statistics" class="list-toolbar-btn" href="javascript:void(0);" onclick='$("#div-date-between").toggle();'>
                <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="{l s='Statistics' mod='mpstock'}" data-html="true" data-placement="top">
                        <i class='icon-bar-chart' style='color: #46a546;'></i>
                </span>
            </a>
            <a id="btn-csv" class="list-toolbar-btn" href="javascript:void(0);" onclick='exportCSV();'>
                <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="{l s='Download CSV' mod='mpstock'}" data-html="true" data-placement="top">
                        <i class='icon-download-alt' style='color: #cdcdcd;'></i>
                </span>
            </a>
            <a id="btn-refresh" class="list-toolbar-btn" href="javascript:void(0);" onclick='resetTable();'>
                <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="{l s='Reset table' mod='mpstock'}" data-html="true" data-placement="top">
                        <i class='icon-refresh' style='color: #090;'></i>
                </span>
            </a>
        </span>
    </div>
    <div class='panel-body'>
        <div class='form-group'>
            <div style='display: block;'>
                <table style='margin: 0 auto; display: block;'>
                    <tbody>
                        <tr style='text-align: center;' colspan='3'>
                            <div id='div-date-between' style='display: none; margin-bottom: 16px; padding-bottom: 8px; border-bottom: 1px solid #cdcdcd;'>
                                <div style='display: inline-block;'>
                                    <label>{l s='Date from' mod='mpstock'}</label>
                                    <input class='input datepicker' name='date-from' id='date-from'>
                                </div>
                                <div style='display: inline-block;'>
                                    <label>{l s='Date to' mod='mpstock'}</label>
                                    <input class='input datepicker' name='date-to' id='date-to'>
                                </div>
                                <div style='display: inline-block'>
                                    <button type="button" id="submit-date-between" name="submitFilter" class="btn btn-default">
                                        <i class="icon-search"></i>{l s='Find' mod='mpstock'}
                                    </button>
                                </div>
                            </div>
                        </tr>
                        <tr>
                            <td style='padding-right: 8px; text-align: right;'>{l s='Show' mod='mpstock'}</td>
                            <td>
                                <select class='select-item' style='display: inline-block;'>
                                    <option value='5'>5</option>
                                    <option value='10'>10</option>
                                    <option value='20'>20</option>
                                    <option value='50'>50</option>
                                    <option value='100'>100</option>
                                    <option value='200'>200</option>
                                    <option value='500'>500</option>
                                    <option value='1000'>1000</option>
                                </select>
                            </td>
                            <td style='padding-left: 8px;'> {$totRecords} {l s='result' mod='mpstock'} </td>
                        </tr>
                        <tr>
                            <td style='text-align: center;' colspan='3'>
                                <ul class="pagination">
                                    <li class="disabled">
                                        <a href="javascript:void(0);" class="pagination-link" data-page="1" data-list-id="mp_stock_list_movements">
                                            <i class="icon-double-angle-left"></i>
                                        </a>
                                    </li>
                                    <li class="disabled">
                                        <a href="javascript:void(0);" class="pagination-link" data-page="0" data-list-id="mp_stock_list_movements">
                                            <i class="icon-angle-left"></i>
                                        </a>
                                    </li>
                                    <li class="active">
                                        <a href="javascript:void(0);" class="pagination-link" data-page="1" data-list-id="mp_stock_list_movements">1</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);" class="pagination-link" data-page="2" data-list-id="mp_stock_list_movements">2</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);" class="pagination-link" data-page="3" data-list-id="mp_stock_list_movements">3</a>
                                    </li>
                                    <li class="disabled">
                                        <a href="javascript:void(0);">…</a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);" class="pagination-link" data-page="2" data-list-id="mp_stock_list_movements">
                                            <i class="icon-angle-right"></i>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);" class="pagination-link" data-page="5" data-list-id="mp_stock_list_movements">
                                            <i class="icon-double-angle-right"></i>
                                        </a>
                                    </li>
                                </ul>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script type='text/javascript'>
    $(document).ready(function(){
        $('#date-from').datepicker(
            {
                dateFormat: "yy-mm-dd",
                showAnim: "slideDown"
            }
        );
        $('#date-to').datepicker(
            {
                dateFormat: "yy-mm-dd",
                showAnim: "slideDown"
            }
        );
        $('#submit-date-between').on('click', function(event){
            event.preventDefault();
            let date_start = $('#date-from').val();
            let date_end = $('#date-to').val();
            displayTable(date_start, date_end);
        });
    });
    
    function resetTable()
    {
        let date_start = '1970-01-01 00:00:00';
        let date_end = '2900-12-31 23:59:00';
        displayTable(date_start, date_end);
    }
    
    function displayTable(date_start, date_end)
    {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            useDefaultXhrHeader: false,
            url: '{$module_link}',
            data: 
            {
                ajax: true,
                action: 'FindMovements',
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
            }
        })
        .fail(function(){
            jAlert("{l s='Error during getting values.' mod='mpstock'}", '{l s='FAIL' mod='mpstock'}');
        });
    }
    
    function exportCSV()
    {
        $.ajax({
            type: 'POST',
            //dataType: 'json',
            useDefaultXhrHeader: false,
            url: '{$module_link}',
            data: 
            {
                ajax: true,
                action: 'ExportCSV',
                token: '{$module_token}',
                id_product: {$id_product},
                id_employee: {$id_employee}
            }
        })
        .done(function(result){
            download('export_product_{$id_product}.csv', result);
        })
        .fail(function(){
            jAlert("{l s='Error during getting values.' mod='mpstock'}", '{l s='FAIL' mod='mpstock'}');
        });
    }
    
    function download(filename, text) {
        var element = document.createElement('a');
        element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(text));
        element.setAttribute('download', filename);

        element.style.display = 'none';
        document.body.appendChild(element);

        element.click();

        document.body.removeChild(element);
    }
</script>
    