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

<div class="panel-heading">
	<i class="icon-bar-chart-o" style="color: #21a3d8; margin-right: 10px;"></i>
	{l s='Add a new stock movement' mod='mpstock'}
	<span class="badge">{$tot_badge}</span>
	<span class="panel-heading-action" style="text-align: center;">
		<a id="btn-date-between" class="list-toolbar-btn" href="javascript:void(0);" onclick="$(&quot;#div-date-between&quot;).toggle();">
			<span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="Find movements" data-html="true" data-placement="top">
					<i class="icon-calendar" style="color: #2CA0FD;"></i>
			</span>
		</a>
		<a id="btn-statistics" class="list-toolbar-btn" href="javascript:void(0);" onclick="$(&quot;#div-date-between&quot;).toggle();">
			<span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="Statistics" data-html="true" data-placement="top">
					<i class="icon-bar-chart" style="color: #46a546;"></i>
			</span>
		</a>
		<a id="btn-print" class="list-toolbar-btn" href="javascript:void(0);" onclick="$(&quot;#div-date-between&quot;).toggle();">
			<span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="Print report" data-html="true" data-placement="top">
					<i class="icon-print" style="color: #f0ad4e;"></i>
			</span>
		</a>
		<a id="btn-csv" class="list-toolbar-btn" href="javascript:void(0);" onclick="exportCSV();">
			<span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="Download CSV" data-html="true" data-placement="top">
					<i class="icon-download-alt" style="color: #cdcdcd;"></i>
			</span>
		</a>
		<a id="btn-refresh" class="list-toolbar-btn" href="javascript:void(0);" onclick="resetTable();">
			<span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="Reset table" data-html="true" data-placement="top">
					<i class="icon-refresh" style="color: #090;"></i>
			</span>
		</a>
	</span>
</div>