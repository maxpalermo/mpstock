{**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    Massimiliano Palermo <maxx.palermo@gmail.com>
 * @copyright Since 2016 Massimiliano Palermo
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 *}

<style type="text/css">
    .ui-autocomplete-row {
        padding: 8px;
        background-color: #f4f4f4;
        border-bottom: 1px solid #ccc;
        font-weight: bold;
    }

    .ui-autocomplete-row:hover {
        background-color: #ddd;
    }
</style>

<form action="{Context::getContext()->link->getAdminLink('AdminMpStockMovements')}&action=setDocument" method="post">
    <div class="panel">
        <div class="panel-heading">
            <i class="icon icon-cogs"></i>
            <span>Imposta Documento</span>
        </div>
        <div class="panel-body">
            <fieldset>
                <legend style="padding-bottom: 1rem;">
                    <span>Imposta Documento</span>
                </legend>
                <div class="row">
                    <div class="form-group col-md-4">
                        <label for="document">{l s='Numero Documento'}<small class="text-danger"> ({l s="Inserire numero documento" mod='mpstock'})</small></label>
                        <input type="text" id="document" name="document_number" class="form-control" value="{if isset($document)}{$document['document_number']}{/if}">
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-4">
                        <label for="document">{l s='Data Documento'}<small class="text-danger"> ({l s="Inserire data documento" mod='mpstock'})</small></label>
                        <input type="date" id="document" name="document_date" class="form-control" value="{if isset($document)}{$document['document_date']}{/if}">
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-4">
                        <label for="document">{l s='Fornitore'}<small class="text-danger"> ({l s="Seleziona un fornitore" mod='mpstock'})</small></label>
                        <select name="id_supplier" id="id_supplier" class="form-select chosen">
                            <option value="">{l s='Seleziona un fornitore'}</option>
                            {foreach from=$suppliers item=supplier}
                                <option value="{$supplier.id_supplier}" {if isset($document) && $document['document_supplier_id'] == $supplier.id_supplier} selected {/if}>{$supplier.name}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>
            </fieldset>
        </div>
        <div class="panel-footer text-center">
            <a class="btn btn-default" href="{$back_url}">
                <i class="process-icon-back"></i>
                <span>{l s='Indietro' mod='mpstock'}</span>
            </a>
            <button class="btn btn-default" type="submit" value="removeDocument" name="submitRemoveDocument">
                <i class="process-icon-trash"></i>
                <span>Rimuovi</span>
            </button>
            <button class="btn btn-default" type="submit" value="addDocument" name="submitAddDocument">
                <i class="process-icon-ok"></i>
                <span>Imposta</span>
            </button>
        </div>
    </div>
</form>

{if isset($document) && $document['document_number']}
    <div class="panel">
        <div class="panel-heading">
            <i class="icon icon-cogs"></i>
            <span>Documento</span>
        </div>
        <div class="panel-body">
            <table class="table table-condensed table-striped">
                <thead>
                    <tr>
                        <th>{l s='#' mod='mpstock'}</th>
                        <th>{l s='Prodotto' mod='mpstock'}</th>
                        <th>{l s='Quantità' mod='mpstock'}</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach $document_movements as $dmov}
                        <tr>
                            <td>
                                <a href="{$ajax_controller}&id_mpstock_movement={$dmov['id_mpstock_movement']}&updatempstock_movement">
                                    {$dmov['id_mpstock_movement']}
                                </a>
                            </td>
                            <td>{$dmov['product_name']} - {$dmov['product_variant']}</td>
                            <td>{$dmov['stock_movement']}</td>
                        </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>
    </div>
{/if}

<form action="{Context::getContext()->link->getAdminLink('AdminMpStockMovements')}&action=addMovement" method="post">
    <div class="panel">
        <div class="panel-heading">
            <i class="icon icon-plus"></i>
            <span>{l s='Aggiungi movimento' mod='mpstock'}</span>
        </div>
        <div class="panel-body">
            <fieldset>
                <legend style="padding-bottom: 1rem;">
                    {if isset($movement) && isset($movement->id) && $movement->id > 0}
                        <span>{l s='Modifica Movimento' mod='mpstock'}: </span><span class="badge badge-info">{$movement->id}</span>
                    {else}
                        <span>{l s='Inserisci un nuovo movimento' mod='mpstock'}</span>
                    {/if}
                </legend>
                <input type="hidden" name="id_mpstock_movement" value="{if isset($movement->id)}{$movement->id}{/if}">
                <div class="row">
                    <div class="form-group col-md-6">
                        <label for="id_order">{l s='Ordine n.'}<small class="text-danger"> ({l s="Inserire il codice dell\'ordine se il movimento appartiene a un ordine esistente" mod='mpstock'})</small></label>
                        <input type="text" id="id_order" name="id_order" class="form-control fixed-width-lg text-right" value="{if isset($movement->id_order)}{$movement->id_order}{/if}">
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-6">
                        <label for="id_mpstock_mvt_reason">{l s='Tipo di movimento'}</label>
                        <select name="id_mpstock_mvt_reason" id="id_mpstock_mvt_reason" class="form-select chosen">
                            <option value="">{l s='Seleziona un tipo di movimento'}</option>
                            {foreach from=$reasons item=reason}
                                <option value="{$reason.id_mpstock_mvt_reason}" {if isset($movement->id_mpstock_mvt_reason) && $movement->id_mpstock_mvt_reason == $reason.id_mpstock_mvt_reason} selected {/if}>{$reason.name}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-6">
                        <label for="id_product">{l s='Prodotto'}</label>
                        <input type="hidden" id="product_id" name="product_id" value="{if isset($movement->id_product)}{$movement->id_product}{/if}">
                        <input id="id_product" type="text" class="form-control autocomplete" placeholder="{l s='Inserisci il nome del prodotto o il riferimento'}" value="{if isset($movement->product_name)}{$movement->product_name}{/if}">
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-6">
                        <label for="id_product_attribute">{l s='Variante'}</label>
                        <select name="id_product_attribute" id="id_product_attribute" class="form-select chosen">
                            <option value="">{l s='Seleziona una variante'}</option>
                            {if isset($variants)}
                                {foreach from=$variants item=variant}
                                    <option value="{$variant.id}" {if isset($movement->id_product_attribute) && $movement->id_product_attribute == $variant.id} selected {/if}>{$variant.variant}</option>
                                {/foreach}
                            {/if}
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="pull-left mr-4">
                            <div class="form-group">
                                <label for="product_qty">{l s='Quantità' mod='mpstock'} <small class="text-danger">({l s='Sempre positiva' mod='mpstock'})</small></label>
                                <div class="input-group fixed-width-lg">
                                    <input id="product_qty" class="form-control text-right" type="text" name="product_qty" value="{if isset($movement->stock_movement)}{$movement->stock_movement}{else}0{/if}">
                                    <span class="input-group-addon"><i class="icon icon-tag"></i></span>
                                </div>
                            </div>
                        </div>
                        <div class="pull-left mr-4">
                            <div class="form-group">
                                <label for="product_wholesale_price_ti">{l s='Prezzo d\'acquisto' mod='mpstock'} <small class="text-danger">({l s='Iva inclusa' mod='mpstock'})</small></label>
                                <div class="input-group fixed-width-md">
                                    <input id="product_wholesale_price_ti" class="form-control fixed-width-md text-right" type="text" name="product_wholesale_price_ti" value="{if isset($movement->wholesale_price_ti)}{$movement->wholesale_price_ti}{else}0.00{/if}">
                                    <span class="input-group-addon">{Context::getContext()->currency->iso_code}</span>
                                </div>
                            </div>
                        </div>
                        <div class="pull-left mr-4">
                            <div class="form-group">
                                <label for="product_price_ti">{l s='Prezzo di vendita' mod='mpstock'} <small class="text-danger">({l s='Iva inclusa' mod='mpstock'})</small></label>
                                <div class="input-group fixed-width-md">
                                    <input id="product_price_ti" class="form-control fixed-width-md text-right" type="text" name="product_price_ti" value="{if isset($movement->price_ti)}{$movement->price_ti}{else}0.00{/if}">
                                    <span class="input-group-addon">{Context::getContext()->currency->iso_code}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="reason">{l s='Descrizione movimento' mod='mpstock'}<small class="text-danger"> ({l s='Inserisci il  motivo di questo movimento' mod='mpstock'})</small></label>
                            <textarea id="reason" class="form-control" id="mvt_reason" name="mvt_reason" cols="80" rows="10">{if isset($movement->mvt_reason)}{$movement->mvt_reason}{/if}</textarea>
                        </div>
                    </div>
                </div>
            </fieldset>
        </div>
        <div class="panel-footer text-center">
            <a class="btn btn-default" href="{$back_url}">
                <i class="process-icon-back"></i>
                <span>{l s='Indietro' mod='mpstock'}</span>
            </a>
            <button class="btn btn-default" type="submit" name="saveMovementAndStay">
                <i class="process-icon-save"></i>
                <span>{l s='Salva e continua' mod='mpstock'}</span>
            </button>
            <button class="btn btn-default" type="submit" name="saveMovement">
                <i class="process-icon-save"></i>
                <span>{l s='Salva' mod='mpstock'}</span>
            </button>
        </div>
    </div>
</form>

<script type="text/javascript">
    $(document).ready(
        function() {
            document.querySelectorAll('[data-dynamic-select]').forEach(select => new DynamicSelect(select));

            $('.chosen').chosen();

            $('input').focus(function() {
                $(this).select();
            });

            $('textarea').focus(function() {
                $(this).select();
            });

            $('.autocomplete').autocomplete({
                minLength: 2,
                source: function(request, response) {
                    $.ajax({
                        url: "{$ajax_controller}",
                        dataType: "json",
                        type: "POST",
                        minLength: 3,
                        data: {
                            ajax: true,
                            action: 'searchProduct',
                            q: request.term
                        },
                        success: function(data) {
                            response(data);
                        }
                    });
                },
                select: function(evt, ui) {
                    evt.stopImmediatePropagation();
                    evt.preventDefault();
                    console.log("SELECT", ui.item);
                    $('#id_product').val(ui.item.name)
                    $('#product_id').val(ui.item.id);
                    $.ajax({
                        url: '{$ajax_controller}',
                        type: 'post',
                        dataType: 'json',
                        data: {
                            ajax: true,
                            action: 'searchProductAttribute',
                            id_product: ui.item.id
                        },
                        success: function(response) {
                            $('#id_product_attribute').empty();
                            $('#id_product_attribute').append('<option value="">{l s="Seleziona una variante"}</option>');
                            $.each(response, function(key, value) {
                                $('#id_product_attribute')
                                    .append(
                                        $("<option>", {
                                            value: value.id,
                                            text: value.variant
                                        })
                                    );
                            });
                            $('#id_product_attribute').trigger("chosen:updated");
                        },
                        error: function(response) {
                            console.log(response);
                        }
                    });
                },
            }).data('ui-autocomplete')._renderItem = function(ul, item) {
                return $("<li class='ui-menu-item' role='presentation'></li>")
                    .data("item.autocomplete", item)
                    .append(item.label)
                    .appendTo(ul);
            };
        });
</script>