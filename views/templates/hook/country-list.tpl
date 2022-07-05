{*
 * 2007-2015 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
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
 *  @author PrestaShop SA <contact@prestashop.com>
 *  @copyright  2007-2015 PrestaShop SA
 *  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 *
 *
 * @author    Arnaud Merigeau <contact@arnaud-merigeau.fr>
 * @copyright  Copyright (c) 2009-2018 Arnaud Merigeau - https://www.arnaud-merigeau.fr
 * @license    You only can use module, nothing more!
*}

<div class="m-b-1 m-t-1">
    <h2>{l s='Paises de venta exclusiva' mod='countrylimits'}</h2>
    <fieldset class="form-group">
        {* <div class="col-md-12"> *}
    {foreach from=$countries item=country}
                {foreach from=$checked item=item}
                    {if $item eq $country.id_country}
                        {assign var=isChecked value=$country.id_country}
                    {/if}
                {/foreach}
            <input {if isset($isChecked) && $isChecked eq $country.id_country} checked{/if} type="checkbox" id="{$country.iso_code}" name="countries[]" value="{$country.id_country}" >
            <label for="{$country.iso_code}" class="form-control-label">{$country.name}</label>

    {/foreach}
        {* </div> *}
    </fieldset>
    <div class="clearfix"></div>
</div>


<style>
#module_countrylimits input{
    float: left;
    margin-right: 5px;
    margin-top: 3px;
}
.form-group .form-control-label {
  display: -webkit-box;
  display: -ms-flexbox;
  display: flex;
  -webkit-box-align: start;
  -ms-flex-align: start;
  align-items: flex-start;
}
</style>
