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

<form action="{$ajax_controller}&action=saveConfig" method="post">
    <div class="panel">
        <div class="panel-heading">
            <i class="icon icon-cogs"></i>
            <span>{l s='Configurazione Modulo' mod='mpstock'}</span>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="form-group col-md-4">
                    <label>{l s='Abilita Modulo' mod='mpstock'}</label>
                    <div class="form-input">
                        <span class="switch prestashop-switch fixed-width-lg">
                            <input type="radio" name="enable" id="enable_on" value="1" {if $module_enabled} checked
                                {/if}>
                            <label for="enable_on"><i class="icon icon-2x icon-check text-success"></i></label>
                            <input type="radio" name="enable" id="enable_off" value="0" {if !$module_enabled} checked
                                {/if}>
                            <label for="enable_off"><i class="icon icon-2x icon-times text-danger"></i></label>
                            <a class="slide-button btn"></a>
                        </span>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="form-group col-md-4">
                    <label>{l s='Seleziona gli stati da non importare' mod='mpstock'}</label>
                    <div class="form-input">
                        {if !$selected_order_states}
                            {assign var='selected_order_states' value=[]}
                        {/if}
                        <select name="order_states[]" id="order_states" multiple="multiple" class="form-control chosen">
                            {foreach from=$order_states item=state}
                                <option value="{$state.id_order_state}"
                                    {if in_array($state.id_order_state, $selected_order_states)} selected {/if}>
                                    {$state.name}
                                </option>
                            {/foreach}
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="form-group col-md-4">
                    <label>{l s='Seleziona il tipo di movimento per importare gli ordini' mod='mpstock'}</label>
                    <div class="form-input">
                        <select name="order_detail_mvt_id" id="order_detail_mvt_id" class="form-control chosen">
                            {foreach from=$movement_reasons item=state}
                                <option value="{$state.id_mpstock_mvt_reason}"
                                    {if $state.id_mpstock_mvt_reason == $order_detail_mvt_id} selected {/if}>
                                    {$state.name}
                                </option>
                            {/foreach}
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="form-group">
                    <label>{l s='Modifica Tabelle' mod='mpstock'}</label>
                    <div class="form-input">
                        <textarea class="form-control" rows="5" readonly="readonly" id="tables" name="tables">
                        </textarea>
                        <button type="button" class="btn btn-default pull-right mt-2" id="btn_edit_tables">
                            <i class="icon icon-edit"></i>
                            {l s='Modifica' mod='mpstock'}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class=" panel-footer">
            <button type="submit" class="btn btn-primary pull-right">
                <i class="process-icon-save"></i>
                {l s='Salva' mod='mpstock'}
            </button>
        </div>
    </div>
</form>

<script>
    function appendTextToTextarea(text) {
        var textarea = $('#tables');
        var currentText = textarea.val();
        if (currentText.length > 0) {
            textarea.val(text + "\n" + currentText);
        } else {
            textarea.val(text);
        }
    }

    function editTables_0() {
        $.ajax({
            url: '{$ajax_controller}',
            type: 'POST',
            data: {
                ajax: true,
                action: 'edit_tables',
                step: 0
            },
            success: function(response) {
                appendTextToTextarea(response.message);
                if (response.status == 'success') {
                    editTables_1();
                }
            }
        });
    }

    function editTables_1() {
        $.ajax({
            url: '{$ajax_controller}',
            type: 'POST',
            data: {
                ajax: true,
                action: 'edit_tables',
                step: 1
            },
            success: function(response) {
                appendTextToTextarea(response.message);
                editTables_2();
            }
        });
    }

    function editTables_2() {
        $.ajax({
            url: '{$ajax_controller}',
            type: 'POST',
            data: {
                ajax: true,
                action: 'edit_tables',
                step: 2
            },
            success: function(response) {
                appendTextToTextarea(response.message);
                editTables_3();
            }
        });
    }

    function editTables_3(page = 0) {
        $.ajax({
            url: '{$ajax_controller}',
            type: 'POST',
            data: {
                ajax: true,
                action: 'edit_tables',
                step: 3,
                page: page
            },
            success: function(response) {
                if ('page' in response) {
                    appendTextToTextarea(response.message);
                    editTables_3(response.page);
                } else {
                    appendTextToTextarea(response.message);
                    alert("Operazione eseguita con successo");
                }
            }
        });
    }

    $(document).ready(function() {
        $(document).on('ajaxStart', function() {
            $('body').css('cursor', 'progress');
        });

        $(document).on('ajaxStop', function() {
            $('body').css('cursor', 'default');
        });

        $(document).on('ajaxError', function() {
            $('body').css('cursor', 'default');
        });

        $('#btn_edit_tables').click(function() {
            if(confirm("{l s='Sei sicuro di voler modificare le tabelle?' mod='mpstock'}"))
            {
                $('#tables').val('');
                editTables_0();
            }
        });
    });
</script>