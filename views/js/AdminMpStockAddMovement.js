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
function bindControls()
{
    $('.input-quantity').on('blur', function(){
        var numb = Number(this.value);
        if (isNaN(numb)) {
            this.value = 0;
        } else {
            this.value = numb;
        }
    });
    
    $('.input-price').on('blur', function(){
        var curr = String(this.value);
        if (curr.indexOf(currency_char)!==-1) {
            curr = curr.substring(0, curr.indexOf(currency_char)-1).trim();
        }
        var numb = Number(curr);
        if (isNaN(numb) && decimal_point===',') {
            numb = String(curr).replace(thousands_sep, '');
            numb = String(numb).replace(decimal_point, '.');
            numb = Number(numb);
        }
        curr = String(Number(numb).toFixed(2)).replace('.', decimal_point) + ' ' + currency_char;
        this.value = curr;
    });
    
    $('.input-percent').on('blur', function(){
        var curr = String(this.value);
        if (curr.indexOf('%')!==-1) {
            curr = curr.substring(0, curr.indexOf('%')-1).trim();
        }
        var numb = Number(curr);
        if (isNaN(numb) && decimal_point===',') {
            numb = String(curr).replace(thousands_sep, '');
            numb = String(numb).replace(decimal_point, '.');
            numb = Number(numb);
        }
        curr = String(Number(numb).toFixed(2)).replace('.', decimal_point) + ' %';
        this.value = curr;
    });
    $('.input-float').on('blur', function(){
            var number = extractNumbers(this.value);
            this.value = Number(number).toFixed(2);
    });
    $('.input-integer').on('blur', function(){
        var number = extractNumbers(this.value);
        this.value = Number(number).toFixed(0);
    });
}
