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

<form action="{$ajax_url}" method="post">
    <input type="hidden" name="reference" value="{$reference}">
    <input type="hidden" name="action" value="submitAdjustment">

    <div class="panel">
        <div class="panel-body">
            <table class="table table-bordered table-striped table-condensed">
                <thead class="thead-light">
                    <tr>
                        <th>id</th>
                        <th>Immagine</th>
                        <th>Riferimento</th>
                        <th>Nome</th>
                        <th>Variante</th>
                        <th>Prezzo</th>
                        <th>Quantità</th>
                        <th>Allineamento</th>
                        <th>Motivo</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach $variants as $key=>$variant}
                        <tr>
                            <td>{$variant.id_product_attribute}</td>
                            <td>
                                <img src="{$variant.image}" alt="{$variant.name}" width="50" height="50"
                                    style="object-fit: contain;">
                            </td>
                            <td>{$variant.reference}</td>
                            <td>{$variant.name}</td>
                            <td>
                                <input type="hidden" name="variants[{$variant.id_product_attribute}]"
                                    value="{$variant.combination_name}">
                                {$variant.combination_name}
                            </td>
                            <td class="text-right">{Tools::displayPrice($variant.price)}</td>
                            <td class="text-right">
                                {if $variant.quantity > 0}
                                    <span class="badge badge-success">{$variant.quantity}</span>
                                {elseif $variant.quantity < 0}
                                    <span class="badge badge-danger">{$variant.quantity}</span>
                                {else}
                                    <span class="badge badge-default">{$variant.quantity}</span>
                                {/if}
                            </td>
                            <td>
                                <input type="text" class="form-control text-right fixed-width-sm"
                                    name="variant_adjustment[{$variant.id_product_attribute}]" value="{$variant.quantity}">
                            </td>
                            <td>
                                <textarea col="10" row="1" class="form-control"
                                    name="adjustment_reason[{$variant.id_product_attribute}]"></textarea>
                            </td>
                        </tr>
                    {/foreach}
                </tbody>
                <tfoot>
                    <tr>
                        <th>#</th>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="panel-footer">
            <div class="row">
                <div class="col-md-12">
                    <button class="btn btn-default pull-right" type="submit">
                        <i class="process-icon-save"></i>
                        <span>{l s='Salva' mod='mpstock'}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<script type="text/javascript">
    $(function() {
        $('input').on('focus', function() {
            $(this).select();
        });
    });
</script>