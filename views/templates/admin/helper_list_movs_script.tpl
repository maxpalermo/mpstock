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
    function editMovement(button)
    {
        var row = $(button).closest('tr');
        var id_movement = Number(String($(row).find('td:nth-child(1)').text()).trim());

        console.log('id_movement', id_movement);
        if (id_movement > 0) {
            var resp = confirm('{l s='Are you sure you want to edit this movement?' mod='mpstock'}');
            if (resp) {
                location.href = '{$url_edit_movement}&id_movement='+id_movement;
            }
        }
    }
    
    function deleteMovement(button)
    {
        var row = $(button).closest('tr');
        var id_movement = Number(String($(row).find('td:nth-child(1)').text()).trim());
        
        console.log('id_movement', id_movement);
        if (id_movement > 0) {
            var resp = confirm('{l s='Are you sure you want to delete this movement?' mod='mpstock'}');
            if (resp) {
                $.ajax({
                    dataType: 'json',
                    type: 'post',
                    data: 
                        {
                            ajax: true,
                            action: 'delMovement',
                            id_movement: id_movement
                        },
                    success: function(data) 
                    {
                        if (data.result===true) {
                            $.growl.notice({
                                title: '{l s='Delete Movement' mod='mpstock'}',
                                message: '{l s='Movement deleted successfully' mod='mpstock'}'
                            });
                            $(row).remove();
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
        }
    }
</script>
