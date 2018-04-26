/**
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
**/
$(document).ready(function(){
    $('.autocomplete').autocomplete({
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
                    action: 'showCombinationsForm',
                    id_product: ui.item.id,
                    name_product: ui.item.value
                },
                success: function(data)
                {
                    $('#form-mp_stock').remove();
                    $('#mp_stock_form_1').append(data.form);
                }
            });
        }
    });
});
function importXML()
{
    $('.module_confirmation').remove();
    $('body').append(
        $('<input/>')
            .attr('type', 'file')
            .attr('name', 'inputFileXML')
            .attr('accept', '.xml')
            .attr('id', 'input_file_import')
            .on('change', function(){
                var data = new FormData();
                data.append(
                    'inputFileXML',
                    this.files[0]
                );
                $('#form-mp_stock').submit();
                //ajaxImportXML(data);
            })
    );
    
    $('input[name="inputFileXML"]').click();
    
}

function ajaxImportXML(data)
{
    data.append('ajax', true);
    data.append('action', 'importXML');
    $.ajax({
        type: 'POST',
        dataType: 'json',
        useDefaultXhrHeader: false,
        processData: false,
        contentType: false,
        data: data
    })
    .done(function(result){
        result.forEach(function(item, index){
            if(item.error !== 0) {
                $('#section-messages').append(item.error);
            } else {
                $('#section-messages').append(item.confirmation);
            }   
        });
        location.reload();
        //$('input[name="inputFileXML"]').remove();
    })
    .fail(function(){
        jAlert("AJAX ERROR");
    });
}

function ajaxRefreshTable()
{
    $.ajax({
        type: 'POST',
        dataType: 'json',
        useDefaultXhrHeader: false,
        data: {
            ajax: true,
            action: 'refreshTable'
        }
    })
    .done(function(result){
        if (result.error === false) {
            $('#form-mp_stock').html(result.content);
        }
    })
    .fail(function(){
        jAlert("AJAX ERROR");
    });
}

function deleteMovement(id_movement, row)
{
    $.ajax({
        type: 'POST',
        dataType: 'json',
        useDefaultXhrHeader: false,
        data: 
        {
            ajax: true,
            action: 'deleteMovement',
            id_movement: id_movement
        }
    })
    .done(function(json){
        if(json.error !== 0) {
            $.growl.notice({
                    title: json.title,
                    size: "large",
                    message: json.message
            });
            row++;
            $('table.mp_stock>tbody>tr:nth-child('+row+')').remove();
        } else {
            $('#form-mp_stock').prepend(json.message);
        }
    })
    .fail(function(){
        jAlert("AJAX ERROR");
    });
}