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
    .link-pointer:hover {
        cursor: pointer;
        font-weight: bold;
        font-size: 1.2em;
    }

    .tr-pointer:hover {
        cursor: pointer;
    }

    .badge-white {
        background-color: white !important;
        color: #222 !important;
        border: 1px solid #999 !important;
    }

    .ui-autocomplete-loading {
        background: white url("{$img_loading}") right center no-repeat !important;
    }

    #progress-wrp {
        border: 1px solid #0099CC;
        padding: 1px;
        position: relative;
        height: 30px;
        border-radius: 3px;
        margin: 10px;
        text-align: left;
        background: #fff;
        box-shadow: inset 1px 3px 6px rgba(0, 0, 0, 0.12);
    }

    #progress-wrp .progress-bar {
        height: 100%;
        border-radius: 3px;
        background-color: #72C279;
        width: 0;
        box-shadow: inset 1px 1px 10px rgba(0, 0, 0, 0.11);
    }

    #progress-wrp .status {
        top: 3px;
        left: 50%;
        position: absolute;
        display: inline-block;
        color: #000000;
    }

    .modal-dialog-centered {
        display: flex;
        align-items: center;
        min-height: calc(100% - (0.5rem * 2));
    }

    .modal-content {
        width: 100% !important;
    }

    .modal-header button.close {
        position: absolute;
        right: 12px;
        top: 12px;
    }
</style>

{include file="./modals/modalMvtReason.tpl"}

<div class="row" id="ps_addresses" style="display:none;">
    <!--Nav content-->
    <ul class='nav nav-tabs' id="tabPaneMenu">
        <li class="active">
            <a href="#tab-pane-1" onclick='javascript:activatePane();'>
                <i class="icon icon-truck"></i>&nbsp;{l s='Delivery address'}
            </a>
        </li>
        <li>
            <a href="#tab-pane-2" onclick='javascript:activatePane();'>
                <i class="icon icon-truck"></i>&nbsp;{l s='Delivery address'}
            </a>
        </li>
        <li>
            <a href="#tab-pane-3" onclick='javascript:activatePane();'>
                <i class="icon icon-truck"></i>&nbsp;{l s='Delivery address'}
            </a>
        </li>
        <li>
            <a href="#tab-pane-4" onclick='javascript:activatePane();'>
                <i class="icon icon-truck"></i>&nbsp;{l s='Delivery address'}
            </a>
        </li>
        <li>
            <a href="#tab-pane-5" onclick='javascript:activatePane();'>
                <i class="icon icon-list"></i>&nbsp;{l s='Invoice address'}
            </a>
        </li>
    </ul>
    <!--Tab content-->
    <div class="tab-content panel">
        <!--Tabs-->
        <div class="tab-pane active" id="#tab-pane-1">

        </div>
        <div class="tab-pane" id="#tab-pane-2">

        </div>
        <div class="tab-pane" id="#tab-pane-3">

        </div>
        <div class="tab-pane" id="#tab-pane-4">

        </div>
        <div class="tab-pane" id="#tab-pane-5">

        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="panel">
            <div class="panel-header">

            </div>
            <div class="panel-body">
                <div id="tabs">
                    <ul>
                        <li><a href="#tabs-1"><i
                                    class="icon icon-list-ul"></i>&nbsp;{l s='Lista Documenti' mod='mpstock'}</a></li>
                        <li><a href="#tabs-2"><i
                                    class="icon icon-list-ul"></i>&nbsp;{l s='Lista Movimenti' mod='mpstock'}</a></li>
                        <li><a href="#tabs-3"><i
                                    class="icon icon-download"></i>&nbsp;{l s='Importa documenti' mod='mpstock'}</a>
                        </li>
                        <li><a href="#tabs-4"><i
                                    class="icon icon-barcode"></i>&nbsp;{l s='Movimento veloce' mod='mpstock'}</a></li>
                        <li><a href="#tabs-5"><i
                                    class="icon icon-cogs"></i>&nbsp;{l s='Movimenti di magazzino' mod='mpstock'}</a>
                        </li>
                    </ul>
                    <div id="tabs-1">
                        {$tab_document}
                    </div>
                    <div id="tabs-2">
                        {$tab_product}
                    </div>
                    <div id="tabs-3">
                        {$tab_import}
                        <div id="progress-wrp">
                            <div class="progress-bar"></div>
                            <div class="status">0%</div>
                        </div>
                        <div class='panel'>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="list-group">
                                        <div class="list-group-item list-group-item-text">
                                            <div class="row">
                                                <div class="col-md-3 text-right">
                                                    <label>{l s="DOCUMENT NUMBER" mod='mpstock'}:</label>
                                                </div>
                                                <div class="col-md-3">
                                                    <input type="text" id="number_document" value="0" disabled>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="list-group-item list-group-item-text">
                                            <div class="row">
                                                <div class="col-md-3 text-right">
                                                    <label>{l s="DOCUMENT DATE" mod='mpstock'}:</label>
                                                </div>
                                                <div class="col-md-3">
                                                    <input type="text" id="date_document" value="0" disabled>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="list-group-item list-group-item-text">
                                            <div class="row">
                                                <div class="col-md-3 text-right">
                                                    <label>{l s="SUPPLIER" mod='mpstock'}:</label>
                                                </div>
                                                <div class="col-md-3">
                                                    <input type="hidden" id="id_supplier" value="0">
                                                    <input type="text" id="supplier" value="" disabled>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="list-group-item list-group-item-text">
                                            <div class="row">
                                                <div class="col-md-3 text-right">
                                                    <label>{l s="MOVEMENT" mod='mpstock'}:</label>
                                                </div>
                                                <div class="col-md-3">
                                                    <input type="hidden" id="id_mpstock_mvt_reason" value="0">
                                                    <input type="text" id="mvt_reason" value="" disabled>
                                                </div>
                                                <div class="col-md-1">
                                                    <input type="text" id="mvt_sign" value="" disabled>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id='section-list' class='row'>
                        </div>
                        <div class="row" style="display:none;" id="info-report-row">
                            <div class="col-md-12">
                                <div class="panel">
                                    <div class="panel-header">
                                        <i class='icon icon-info'></i>
                                        &nbsp;
                                        {l s='Info report' mod='mpstock'}
                                        <span class="badge pull-right"
                                            onclick="javascript:$('#info-report-row').toggle();">
                                            <i class="icon-times"></i>
                                        </span>
                                    </div>
                                    <div class="panel-body">
                                        <textarea id="info-report" rows="10" class="col-md-12" disabled></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="tabs-4">
                        {$tab_quick_movement}
                    </div>
                    <div id="tabs-5">
                        {$tab_config}
                    </div>
                </div>
            </div>
            <div class="panel-footer">

            </div>
        </div>
    </div>
</div>


<script type="text/javascript">
    /*****************
     * UPLOAD HANDLER *
     *****************/
    var Upload = function(file) {
        this.file = file;
    };

    Upload.prototype.getType = function() {
        return this.file.type;
    };
    Upload.prototype.getSize = function() {
        return this.file.size;
    };
    Upload.prototype.getName = function() {
        return this.file.name;
    };
    Upload.prototype.doUpload = function() {
        $('#section-list').html('');
        var that = this;
        var formData = new FormData();

        // add assoc key values, this will be posts values
        formData.append("file", this.file, this.getName());
        formData.append("upload_file", true);
        formData.append("ajax", true);
        formData.append("action", 'loadFile');

        $.ajax({
            type: "POST",
            dataType: 'json',
            xhr: function() {
                var myXhr = $.ajaxSettings.xhr();
                if (myXhr.upload) {
                    myXhr.upload.addEventListener('progress', that.progressHandling, false);
                }
                return myXhr;
            },
            success: function(response) {
                if ('errors' in response) {
                    $.growl.error({
                        'title': '{l s='ERROR' mod='mpstock'}',
                        'message': response.errors
                    });
                } else {
                    $('#number_document').val(response.document.number);
                    $('#date_document').val(response.document.date);
                    $('#id_mpstock_mvt_reason').val(response.document.type);
                    $('#mvt_reason').val(response.document.movement);
                    $('#mvt_sign').val(response.document.sign);
                    $('#id_supplier').val(response.document.id_supplier);
                    $('#supplier').val(response.document.supplier);
                    $('#section-list').html(response.html);
                }
            },
            error: function(error) {
                // handle error
            },
            async: true,
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            timeout: 60000
        });
    };

    Upload.prototype.progressHandling = function(event) {
        var percent = 0;
        var position = event.loaded || event.position;
        var total = event.total;
        var progress_bar_id = "#progress-wrp";
        if (event.lengthComputable) {
            percent = Math.ceil(position / total * 100);
        }
        // update progressbars classes so it fits your code
        $(progress_bar_id + " .progress-bar").css("width", +percent + "%");
        $(progress_bar_id + " .status").text(percent + "%");
    };

    var total_products = 0;

    function displayAddMvtReason(id = 0) {
        if (id) {
            let data = {
                ajax: true,
                action: 'getMovement',
                id: id
            };

            $.post( "{$ajax_url}", data, function(response)
            {
                if (response.success) {
                    $('#mvt_reason_id').val(response.movement.id).attr('readonly', true);
                    $('#mvt_reason_name').val(response.movement.name);
                    $('#mvt_reason_sign').val(response.movement.sign);

                    $('#ModalMvtReason').modal('show');
                    return true;
                } else {
                    $.growl.error({
                        title: '{l s='Errore.' mod='mpstock'}',
                        message: response.message,
                        duration: 5000,
                        size: 'large'
                    });
                }
            });
    } else {
        $('#mvt_reason_id').val("0").attr('readonly', false);
        $('#mvt_reason_name').val("");
        $('#mvt_reason_sign').val(0);
        $('#ModalMvtReason').modal('show');
    }
    }

    function saveMovement() {
        let id = $('#mvt_reason_id').val();
        let name = $('#mvt_reason_name').val();
        let sign = $('#mvt_reason_sign').val();

        let data = {
            ajax: true,
            action: 'saveMovement',
            movement: {
                id: id,
                name: name,
                sign: sign
            }
        };
        $.post( "{$ajax_url}", data, function(response) {
        if (response.success) {
            $.growl.notice({
                title: '{l s='Movimento di magazzino' mod='mpstock'}',
                message: response.message,
                duration: 5000,
                size: 'large'
            });
        } else {
            $.growl.error({
                title: '{l s='Errore.' mod='mpstock'}',
                message: response.message,
                duration: 5000,
                size: 'large'
            });
        }
    });
    }

    $(document).ready(function() {
            $(document).on("keypress", "form", function(event) {
                return event.keyCode != 13;
            });

            $(".btn-group a").on("click", function(evt) {
                evt.preventDefault();
                evt.stopImmediatePropagation();

                var href = $(this).attr("href");
                if (href.includes("id_mpstock_mvt_reason")) {
                    let regId = new RegExp(/id_mpstock_mvt_reason=(\d+)/);
                    id = regId.exec(href)[1];
                    console.log("ID:", id);
                    displayAddMvtReason(id);
                }
                return false;
            });

            $('#tabs').tabs({
                'active': {$active_tab},
                'activate': function() {
                    var selected = $("#tabs .ui-tabs-panel:visible").index() - 1;
                    $.ajax({
                        type: 'post',
                        dataType: 'json',
                        data: {
                            active_tab: selected,
                            ajax: true,
                            action: 'setActiveTab'
                        }
                    });
                }
            });
            $('#form-mpstock_document table tbody tr').each(function() {
                $(this)
                    .attr('onclick', 'javascript:toggleDocumentRow(this);')
                    .addClass('tr-pointer');
            });
            $('#form-mpstock_document table td')
                .css('font-size', '0.8em')
                .css('white-space', 'nowrap');
            $('#form-mpstock_document table th:nth-child(9)').css('display', 'none');
            $('#form-mpstock_document table th:nth-child(10)').css('display', 'none');
            $('#form-mpstock_document table td:nth-child(9)').css('display', 'none');
            $('#form-mpstock_document table td:nth-child(10)').css('display', 'none');
            $('#mpstock_document_form_submit_btn')
                .attr('type', 'button')
                .attr('onclick', 'javascript:loadDocument();');

            $('#form-mpstock_movement table td')
                .css('font-size', '0.8em')
                .css('white-space', 'nowrap');
            $('#form-mpstock_movement table th:nth-child(2)').css('display', 'none');
            $('#form-mpstock_movement table td:nth-child(2)').css('display', 'none');
            $('#form-mpstock_movement table td:nth-child(5)').css('white-space', 'normal');
            $('#btn-align-qty').on('click', function() {
                    if (confirm('{l s='Re-align all products quantities?' mod='mpstock'}')) {
                    $('#btn-align-pb').fadeIn();
                    $.ajax({
                        type: 'post',
                        dataType: 'json',
                        data: {
                            ajax: true,
                            action: "realignQuantities"
                        },
                        success: function(response) {
                            if (response.result) {
                                $.growl.notice({
                                    title: '{l s='Operation done.' mod='mpstock'}',
                                    message: '{l s='Products have been re-aligned.' mod='mpstock'}'
                                });
                            } else {
                                $.growl.error({
                                    title: '{l s='An error occurred.' mod='mpstock'}',
                                    message: '{l s='Unable to realign quantities.' mod='mpstock'}'
                                });
                            }
                            $('#btn-align-pb').fadeOut();
                            location.reload();
                        },
                        error: function(response) {
                            console.log(response);
                        }
                    });
                }
            });
        /**
            SET DEFAULT COMBINATION FOR ALL ACTIVE PRODUCTS
        **/
        $('#btn-default-qty').on('click', function() {
                if (confirm('{l s='Set default combination to all products?' mod='mpstock'}')) {
                $('#btn-align-pb').fadeIn();
                $.ajax({
                    type: 'post',
                    dataType: 'json',
                    data: {
                        ajax: true,
                        action: "setDefaultCombination"
                    },
                    success: function(response) {
                        if (response.result) {
                            total_products = response.id_products.length;
                            var id_products = response.id_products;
                            $.growl.notice({
                                title: '{l s='Auto default combination' mod='mpstock'}',
                                message: '{l s='Total products to process' mod='mpstock'}'+': '+total_products,
                                duration: 30000
                            });
                            setDefaultCombination(id_products);
                        }
                    },
                    error: function(response) {
                        console.log(response.responseText);
                    }
                });
            }
        });
    $(document).on('mouseover', 'span[name="expand-stock-row"]', function() {
        $(this).css({
            cursor: 'pointer'
        })
    });
    $(document).on('click', 'span[name="expand-stock-row"]', function() {
        var td_id = $(this).closest('tr').find('td:nth-child(2)').text();
        var ids = String(td_id).split('-');
        var id_product = ids[0];
        var id_attribute = ids[1];
        var row = $(this).closest('tr');
        var ico = $(this).closest('tr').find('td:nth-child(1)').find('i');

        if ($(ico).hasClass('icon-plus-circle')) {
            $.ajax({
                type: 'post',
                dataType: 'json',
                data: {
                    id_product: id_product,
                    ajax: true,
                    action: "getProductAttribute"
                },
                success: function(response) {
                    $(row).after(response.html);
                    $(ico).removeClass().addClass('icon icon-minus-circle');
                },
                error: function() {

                }
            });
        } else {
            $(ico).removeClass().addClass('icon icon-plus-circle');
            $('tr[name="' + id_product + '"]').remove();
        }
    });
    $(document).on("change", "#stock-pagination", function() {
        stock_pagination(0, $('#stock-pagination').val());
    });
    $(document).on("change", "#stock-page", function() {
    stock_pagination($('#stock-page').val(), $('#stock-pagination').val());
    });
    });

    function setDefaultCombination(id_products) {
        $.ajax({
            type: 'post',
            dataType: 'json',
            data: {
                ajax: true,
                action: 'setDefaultCombinationProducts',
                id_products: id_products,
            },
            success: function(response) {
                $('#btn-align-pb').fadeOut();
                $.growl.notice({
                    title: '{l s='Auto default combination' mod='mpstock'}',
                    message: '{l s='Operation done.' mod='mpstock'}'
                });
                setTimeout(location.reload(), 3000);
            },
            error: function(response) {
                console.log(response.responseText);
            }
        });
    }

    function stock_pagination(page, pagination) {
        $.ajax({
            type: 'post',
            dataType: 'json',
            data: {
                ajax: true,
                action: 'getStockAvailable',
                stock_pagination: pagination,
                stock_page: page
            },
            success: function(response) {
                $('#table-stock').html(response.html);
                $('#cur_rec').text(response.cur_rec);
                $('#last_rec').text(response.last_rec);
            },
            error: function(response) {
                console.log(response);
            }
        });
    }

    function saveDocument() {
        var id_document = $('#add_id_mpstock_document').val();
        var number = $('#add_number_document').val();
        var date = $('#add_date_document').val();
        var reason = $('#add_id_mpstock_mvt_reason').val();
        var sign = $('#add_id_mpstock_mvt_reason option:selected').attr('sign');
        var supplier = $('#add_id_supplier').val();
        $.ajax({
            type: "post",
            dataType: "json",
            data: {
                id_document: id_document,
                number: number,
                date: date,
                reason: reason,
                sign: sign,
                supplier: supplier,
                ajax: true,
                action: 'addDocument'
            },
            success: function(response) {
                if (response.result) {
                    $.growl.notice({
                        'title': '{l s='Operation done' mod='mpstock'}',
                        'message': '{l s='Document saved.' mod='mpstock'}'
                    });
                    setTimeout(
                        function() {
                            document.location.href="{$back_url}";
                        },
                        15000
                    );
                } else {
                    $.growl.error({
                        'title': '{l s='Error' mod='mpstock'}',
                        'message': response.message
                    });
                }
            },
            error: function(response) {
                console.log(response);
            }
        });
    }

    function toggleDocumentRow(row) {
        console.log(row);
        if ($(row).next().attr('data-type') && $(row).next().attr('data-type') == 'row-product') {
            $(row).next().fadeOut().remove();
            return false;
        } else if ($(row).next().attr('data-type')) {
            $(row).next().fadeOut().remove();
        }

        var newrow = $('<tr></tr>').attr('data-type', 'row-product');
        var newcell = $('<td></td>').attr('colspan', 16);

        //get Rows
        $.ajax({
            type: 'post',
            dataType: 'json',
            data: {
                ajax: true,
                action: 'getDocumentRows',
                id_document: Number($(row).find('td:nth-child(2)').text())
            },
            success: function(response) {
                $(newcell).html(response.htmlrow);
                $(newrow).append(newcell);
                $(row).after(newrow).fadeIn();
                bindControls();
            },
            error: function(response) {
                console.log("ajax error: ", response);
            }
        });
    }

    function bindControls() {
        $('.input-autocomplete').autocomplete({
            source: function(request, response) {
                $.ajax({
                        dataType: "json",
                        data: {
                            ajax: true,
                            action: 'getProductAutocomplete',
                            term: request.term
                        }
                    })
                    .success(function(data) {
                        response(data);
                    })
                    .fail(function() {
                        jAlert('AJAX FAIL');
                    });
            },
            minLength: 3,
            select: function(event, ui) {
                event.preventDefault();
                //console.log("ui:", ui);
                current_id_product = Number(ui.item.id_product_attribute);
                this.value = ui.item.value;
                var row = $(this).closest('.row');
                var doc = $(this).closest('tr').prev();
                var id_doc = Number($(doc).find('td:nth-child(2)').text());
                row.find('input[name="id_mp_stock_product"]').val('0');
                row.find('input[name="id_mp_stock_document"]').val(id_doc);
                row.find('input[name="id_product"]').val(ui.item.id_product);
                row.find('input[name="id_product_attribute"]').val(ui.item.id_product_attribute);
                row.find('input[name="stock_quantity_before"]').val(ui.item.stock_quantity_before);
                row.find('input[name="stock_movement"]').val('0');
                row.find('input[name="price_te_float"]').val(ui.item.price_te_float);
                row.find('input[name="price_te"]').val(ui.item.price_te);
                row.find('input[name="wholesale_price_te_float"]').val(ui.item.wholesale_price_te_float);
                row.find('input[name="wholesale_price_te"]').val(ui.item.wholesale_price_te);
                row.find('input[name="tax_rate"]').val(ui.item.tax_rate);
                row.find('input[name="tax_rate_float"]').val(ui.item.tax_rate_float);
            }
        });
    }

    function importDocument() {
        var focused = $(':focus');
        if (confirm('{l s='Are you sure you want to import old documents?' mod='mpstock'}')) {
        $(focused).find('i').removeClass('process-icon-download').addClass('process-icon-loading');
        $.ajax({
            type: 'post',
            dataType: 'json',
            data: {
                ajax: true,
                action: 'importDocument'
            },
            success: function(response) {
                $(focused).find('i').removeClass('process-icon-loading').addClass('process-icon-download');
                if (response.result) {
                    $.growl.notice({
                        title: '{l s='Operation done.' mod='mpstock'}',
                        message: '{l s='See report for better infos' mod='mpstock'}'
                    });
                    $('#info-report').text(response.message);
                    $('#info-report-row').fadeIn();
                } else {
                    $.growl.warning({
                        title: '{l s='Failed import.' mod='mpstock'}',
                        message: '{l s='See report for better infos' mod='mpstock'}'
                    });
                    $('#info-report').text(response.message);
                    $('#info-report-row').fadeIn();
                }
            },
            error: function(response) {
                $(focused).find('i').removeClass('process-icon-loading').addClass('process-icon-download');
                console.log("Ajax Call Error: ", response);
            }
        });
    }
    }

    function importConfig() {
        if (confirm('{l s='Are you sure you want to import old configuration?' mod='mpstock'}')) {
        $.ajax({
            type: 'post',
            dataType: 'json',
            data: {
                ajax: true,
                action: 'importConfig'
            },
            success: function(response) {
                if (response.result) {
                    $.growl.notice({
                        title: '{l s='Operation done.' mod='mpstock'}',
                        message: '{l s='See report for better infos' mod='mpstock'}'
                    });
                    $('#info-report').text(response.message);
                    $('#info-report-row').fadeIn();
                } else {
                    $.growl.warning({
                        title: '{l s='Failed import.' mod='mpstock'}',
                        message: '{l s='See report for better infos' mod='mpstock'}'
                    });
                    $('#info-report').text(response.message);
                    $('#info-report-row').fadeIn();
                }
            },
            error: function(response) {
                console.log("Ajax Call Error: ", response);
            }
        });
    }
    }

    function toggleSign(button) {
        var id = Number($(button).closest('tr').find('td:nth-child(2)').text());
        ajaxProcessToggle('mpstock_mvt_reason', 'sign', id, button);
    }

    function toggleTransform(button) {
        var id = Number($(button).closest('tr').find('td:nth-child(2)').text());
        ajaxProcessToggle('mpstock_mvt_reason', 'transform', id, button);
    }

    function toggleDeleted(button) {
        var id = Number($(button).closest('tr').find('td:nth-child(2)').text());
        ajaxProcessToggle('mpstock_mvt_reason', 'deleted', id, button);
    }

    function ajaxProcessToggle(tablename, field, id, button) {
        $.ajax({
            type: 'post',
            dataType: 'json',
            data: {
                ajax: true,
                action: 'toggle',
                tablename: tablename,
                field: field,
                id: id
            },
            success: function(response) {
                $(button)
                    .removeClass(response.removeClass)
                    .addClass(response.addClass)
                    .css('color', response.color);
            },
            error: function(response) {
                console.log("ajax error: ", response);
            }
        });
    }

    function loadDocument() {
        if (confirm('{l s='Load selected file?' mod='mpstock'}')) {
        var file = $('#document_filename')[0].files[0];
        var upload = new Upload(file);

        // maby check size or type here with upload.getSize() and upload.getType()

        // execute upload
        upload.doUpload();
    }
    }

    function importDocumentXML() {
        if (confirm('{l s='Import selected document?' mod='mpstock'}') == false) {
        return false;
    }
    var rows = $('#section-list .table.mpstock_movement>tbody>tr');
    var data_rows = [];
    $(rows).each(function() {
        if ($(this).find('input[type="checkbox"]').is(":checked")) {
            var row = {
                'id_supplier': Number($(this).find('td:nth-child(2)').text()),
                'ean13': String($(this).find('td:nth-child(4)').text()).trim(),
                'reference': String($(this).find('td:nth-child(5)').text()).trim(),
                'id_product': Number($(this).find('td:nth-child(6)').text()),
                'id_product_attribute': Number($(this).find('td:nth-child(7)').text()),
                'qty': String($(this).find('td:nth-child(9)').text()).trim(),
                'sign': (String($(this).find('td:nth-child(9)').text()).trim() < 0)
            };
            data_rows.push(row);
        }
    });
    $.ajax({
        type: 'post',
        dataType: 'json',
        data: {
            ajax: true,
            action: 'importFormattedDocumentXML',
            rows: data_rows,
            number: $('#number_document').val(),
            date: $('#date_document').val(),
            type: $('#id_mpstock_mvt_reason').val(),
            id_supplier: $('id_supplier').val()
        },
        success: function(response) {
            var i = 0;
            var chk = $('#section-list .table.mpstock_movement>tbody')
                .find('input[type="checkbox"]:checked')
                .closest('td');
            $(chk).each(function() {
                var span = $(this).closest('tr').find('td:nth-child(10)>span');
                if (response.rows.result[i]) {
                    $(this).css('background-color', '#72C279');
                } else {
                    $(this).css('background-color', '#E08F95');
                }
                var stock = response.rows.stock[i];
                if (stock == 0) {
                    $(span).css('color', '#555555');
                } else if (stock < 0) {
                    $(span).css('color', '#E08F95');
                } else {
                    $(span).css('color', '#72C279');
                }
                $(span).html(stock);
                i++;
            });
            $.growl.notice({
                'title': '{l s='Import document' mod='mpstock'}',
                'message': '{l s='Operation done' mod='mpstock'}'
            });
        },
        error: function(response) {
            console.log(response);
        }
    });
    }

    function saveRow() {
        var active_elem = document.activeElement;
        var row = $(active_elem).closest('.list-group-item');
        var doc = $(row).closest('tr').prev();
        var obj = {
            'id': $(row).find('input[name="id_mpstock_movement"]').val(),
            'id_document': String($(doc).find('td:nth-child(2)').text()).trim(),
            'id_movement': String($(doc).find('td:nth-child(3)').text()).trim(),
            'id_product': $(row).find('input[name="id_product"]').val(),
            'id_product_attribute': $(row).find('input[name="id_product_attribute"]').val(),
            'qty': $(row).find('input[name="stock_movement"]').val(),
            'price': $(row).find('input[name="price_te"]').val(),
            'wholesale_price': $(row).find('input[name="wholesale_price_te"]').val(),
        };
        $.ajax({
            type: 'post',
            dataType: 'json',
            data: {
                ajax: true,
                action: 'saveDocumentRow',
                row: obj
            },
            success: function(response) {
                $(doc).find('td:nth-child(8)').text(response.tot_qty);
                $(doc).find('td:nth-child(9)').text(response.tot_document_te);
                $(doc).find('td:nth-child(10)').text(response.tot_document_taxes);
                $(doc).find('td:nth-child(11)').text(response.tot_document_ti);
                $(row).closest('tr').remove();
            },
            error: function(response) {
                console.log(response);
            }
        });
    }

    function activatePane(elem = null) {
        event.preventDefault();
        if (elem == null) {

            var elem = document.activeElement;
        } else {
            //todo
        }
        //console.log('Activate pane ', elem);
        var li = $(elem).closest('li');
        var nav = $(elem).closest('.nav');
        var tabs = $(nav).closest('.row').find('.tab-content');
        var div = $(elem).attr('href');
        $(nav).find('li').removeClass('active');
        $(li).addClass('active');
        $(tabs).find('div').removeClass('active');
        $(div).addClass('active');
    }
</script>