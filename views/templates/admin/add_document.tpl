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
input[type="date"]
{
    display: block;
    width: 100%;
    height: 31px;
    padding: 6px 8px;
    font-size: 12px;
    line-height: 1.42857;
    color: #555;
    background-color: #F5F8F9;
    background-image: none;
    border: 1px solid #C7D6DB;
    border-radius: 3px;
    -webkit-transition: border-color ease-in-out 0.15s,box-shadow ease-in-out 0.15s;
    -o-transition: border-color ease-in-out 0.15s,box-shadow ease-in-out 0.15s;
    transition: border-color ease-in-out 0.15s,box-shadow ease-in-out 0.15s;
}
</style>
<form type="post" id="form-document">
    <div class="row">
        <div class="panel col-md-12">
            <div class="row">
                <div class="col-md-3 text-right">
                    <label>{l s='Id document' mod='mpstock'}</label>
                </div>
                <div class="col-md-9">
                    <input type="text" class="input fixed-width-md" id="add_id_mpstock_document" value="{$document->id}" readonly>
                </div>
            </div>
            <br>
            <div class="row">
                <div class="col-md-3 text-right">
                    <label>{l s='Number' mod='mpstock'}</label>
                </div>
                <div class="col-md-3">
                    <input type="text" class="input" id="add_number_document" value="{$document->number_document}">
                </div>
                <div class="col-md-3 text-right">
                    <label>{l s='Date' mod='mpstock'}</label>
                </div>
                <div class="col-md-3">
                    <input type="date" class="input" id="add_date_document" value="{$document->date_document_local}">
                </div>
            </div>
            <br>
            <div class="row">
                <div class="col-md-3 text-right">
                    <label>{l s='Movement reason' mod='mpstock'}</label>
                </div>
                <div class="col-md-9">
                    <select class="input chosen col-md-9" id="add_id_mpstock_mvt_reason">
                        <option value="0">{l s='Please select a movement reason' mod='mpstock'}</option>
                        {foreach $movement_reasons as $mvt}
                            <option value="{$mvt.id_mpstock_mvt_reason}" sign="{$mvt.sign}">{$mvt.name}</option>
                        {/foreach}
                    </select>
                </div>
            </div>
            <br>
            <div class="row">
                <div class="col-md-3 text-right">
                    <label>{l s='Supplier' mod='mpstock'}</label>
                </div>
                <div class="col-md-9">
                    <select class="input chosen col-md-9" id="add_id_supplier">
                        <option value="0">{l s='Please select a supplier' mod='mpstock'}</option>
                        {foreach $suppliers as $supplier}
                            <option value="{$supplier.id_supplier}">{$supplier.name}</option>
                        {/foreach}
                    </select>
                </div>
            </div>
            <br>
            <div class="panel-footer">
                <button type="button" class="btn btn-default" onclick="javascript:document.location.href='{$back_url}';">
                    <i class="process-icon-back"></i>
                    &nbsp;
                    {l s='Back' mod='mpstock'}
                </button>
                <button type="button" class="btn btn-success pull-right" onclick="javascript:saveDocument();">
                    <i class="process-icon-save"></i>
                    &nbsp;
                    {l s='Save' mod='mpstock'}
                </button>
            </div>
        </div>
    </div>
</form>