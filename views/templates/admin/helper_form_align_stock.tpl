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
    function alignStock()
    {
        if (!confirm('{l s='Start stock alignment?' mod='mpstock'}')) {
            return false;
        }
        $.ajax({
            dataType: 'json',
            type: 'post',
            data: 
                {
                    ajax: true,
                    action: 'alignStock',
                },
            success: function(data) 
            {
                if (data.result===true) {
                    $.growl.notice({
                        title: '{l s='Align Stock' mod='mpstock'}',
                        message: '{l s='Stock movements aligned.' mod='mpstock'}'
                    });
                } else {
                    $.growl.error({
                        title: '{l s='Error' mod='mpstock'}',
                        message: data.message
                    });
                }
            },
            error: function()
            {
                $.growl.error({
                    title: '{l s='Error' mod='mpstock'}',
                    message: '{l s='Ajax error' mod='mpstock'}'
                });
            }
        });
    }
</script>
