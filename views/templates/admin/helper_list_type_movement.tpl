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
        $('a[name="editMovement[]"]').on('click', function(event){
            event.preventDefault();
            editMovement(this);
        });
        $('a[name="deleteMovement[]"]').on('click', function(event){
            event.preventDefault();
            deleteMovement(this);
        });
    });
    function editMovement(button)
    {
        if (!confirm('{l s='Edit selected movement?' mod='mpstock'}')) {
            return false;
        }
        var id = Number(String($(button).closest('tr').find('td:nth-child(2)').text()).trim());
        var location = '{$module_url}&editMovement=' + id;
        console.log("location:", location);
        window.open(location, "_self", false);
        return false;
    }

    function deleteMovement(button)
    {
        if (!confirm('{l s='Delete selected movement?' mod='mpstock'}')) {
            return false;
        }
        var id = Number(String($(button).closest('tr').find('td:nth-child(2)').text()).trim());
        window.location.href = '{$module_url}&deleteMovement=' + id;
    }
</script>
