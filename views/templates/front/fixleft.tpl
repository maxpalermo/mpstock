{*
* 2007-2016 PrestaShop
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
*  @author Massimiliano Palermo <inpfo@mpsoft.it>
*  @copyright  2018 Digital Solutions®
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<style>
    @media only screen and (min-width: 500px) {
        .left-fixed-icon {
            position: fixed;
            top: 50%;
            left: 0;
            z-index: 999999;
            margin: 0;
            padding: 0;
            width: 64px !important;
            height: 64px !important;
            text-align: center;
            vertical-align: middle;
            background-color: #0079CC !important;
        }
        .left-fixed-title {
            position: fixed;
            top: 50%;
            left: 64px;
            z-index: 999999;
            margin: 0;
            padding: 0;
            width: auto !important;
            height: 64px;
            text-align: center;
            background-color: #0079CC !important;
        }
        .left-fixed-title div {
            color: white !important;
            text-transform: uppercase;
            text-shadow: 1px 1px 2px #222222;
            font-size: 1.5em;
            margin-top: 20px;
            padding-left: 20px;
            padding-right: 20px;
        }
    }
    @media only screen and (max-width: 500px) {
        .left-fixed-icon {
            position: fixed;
            top: 50%;
            left: calc(100% - 64px);
            z-index: 999999;
            margin: 0;
            padding: 0;
            width: 64px !important;
            height: 64px !important;
            text-align: center;
            vertical-align: middle;
            background-color: #0079CC !important;
        }
        .left-fixed-title {
            position: fixed;
            top: 50%;
            left: 64px;
            z-index: 999999;
            margin: 0;
            padding: 0;
            width: auto !important;
            height: 64px;
            text-align: center;
            background-color: #0079CC !important;
        }
        .left-fixed-title div {
            color: white !important;
            text-transform: uppercase;
            text-shadow: 1px 1px 2px #222222;
            font-size: 1.5em;
            margin-top: 20px;
            padding-left: 20px;
            padding-right: 20px;
        }
    }
        
</style>
<div class="left-fixed-icon" id="snippet-div-left">
    <i class="icon-3x icon-star-o" style="color:white; background: transparent; margin-top: 16px;"></i>
</div>
<div class="left-fixed-title" style="display: none;" id="snippet-div-title">
    <div>
        <a href="https://www.dalavoro.it/module/gsnippetsreviews/reviews?list=1" style="color:white;">
            VEDI TUTTE LE RECENSIONI
        </a>
    </div>
    
</div>
<script type="text/javascript">
    $(document).ready(function(){
        $("#snippet-div-left").on('click',function(){
            $('#snippet-div-title').toggle(
                function(){
                    $(this).animate({
                        opacity: 1,
                        left: "64",
                        height: "64"
                    });
                }
        );
        });
    });
</script>
