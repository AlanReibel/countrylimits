<?php
/**
* 2007-2022 PrestaShop
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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2022 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class countrylimits extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'countrylimits';
        $this->tab = 'i18n_localization';
        $this->version = '1.0.0';
        $this->author = 'Alan Reibel';
        $this->need_instance = 0;
        $this->error = NULL;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Country limits');
        $this->description = $this->l('Country limitation for product selling');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        Configuration::updateValue('COUNTRY_LIMITS_LIVE_MODE', false);

        include(dirname(__FILE__).'/sql/install.php');

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('actionProductSave') &&
            $this->registerHook('actionCartSave') &&
            $this->registerHook('displayAdminProductsExtra');
    }

    public function uninstall()
    {
        include(dirname(__FILE__).'/sql/uninstall.php');

        return parent::uninstall();
    }

    public function hookActionCartSave($params)
    {
        $con = Context::getContext();
        $customer = $con->customer;
        if($customer->logged == true)
        {
            $id_address_delivery = $params["cart"]->id_address_delivery ? $params["cart"]->id_address_delivery : $con->cart->id_address_delivery;
            if ($id_address_delivery)
            {
                $address = new Address($id_address_delivery);
                $country_obj = new Country($address->id_country);
                $custommer_country = $country_obj->id;

                $cart = $params["cart"];
                $cart_products = $cart->getProducts();
                foreach ($cart_products as $product) {
                    $product_id = $product["id_product"];
                    $available_countries = $this->getCountries($product_id);
                    if($available_countries)
                    {
                        $is_product_available = array_search($custommer_country, $available_countries) !== false ? true : false;
                        if(!$is_product_available){
                            $product = new Product($product_id);
                            $product->deleteCartProducts();
                            $this->context->controller->errors[0] = $this->l('Este producto tiene limitaciones de distribución para la dirección seleccionada.');
                            
                        }
                    }
                }
            }

        }
    }
    
    public function hookActionProductSave($params)
    {
        $product_id = $params["id_product"];
        $oldCountries = $this->getCountries($product_id);
        $newCountries = Tools::getValue('countries');
        if($oldCountries && $newCountries)
        {
            $diff = array_intersect($newCountries, $oldCountries);
            if($diff)
            {
                $this->updateCountries($newCountries, $product_id);
            }
            
        }

        if($newCountries && !$oldCountries)
        {
            $this->saveCountries($newCountries, $product_id);
        }

        if(!$newCountries & $oldCountries)
        {
            $this->deleteCountries($product_id);
        }



    }

    public function hookDisplayAdminProductsExtra($params)
    {
        /* Place your code here. */
        $params = $params;
        $lang_id = $this->context->language->id;
        $countries = Country::getCountries($lang_id);
        $product_id = $params["id_product"];
        $checked = $this->getCountries($product_id);

        $this->context->smarty->assign(
            [
            'countries' => $countries,
            'checked' => $checked
            ]
           );
        return $this->display(__FILE__, 'views/templates/hook/country-list.tpl');
    }

    public function saveCountries($countries, $product_id)
    {
        $countries = json_encode($countries);
        $query = 'INSERT INTO ' . _DB_PREFIX_ . "countrylimits (countries, product_id) VALUES ('$countries', $product_id)";
        $result = Db::getInstance()->execute($query);
        return $result;
    }

    public function updateCountries($countries, $product_id)
    {
        $countries = json_encode($countries);
        $query = 'UPDATE ' . _DB_PREFIX_ . "countrylimits SET countries='$countries' WHERE product_id='$product_id'";
        $result = Db::getInstance()->execute($query);
        return $result;
    }

    public function getCountries($product_id)
    {
        $query = 'SELECT `countries` FROM ' . _DB_PREFIX_ . "countrylimits WHERE product_id='$product_id'";
        $result = Db::getInstance()->executeS($query);
        $countries = $result ? json_decode($result[0]["countries"]) : false;

        return $countries;
    }

    public function deleteCountries($product_id)
    {
        $query = 'DELETE FROM `'. _DB_PREFIX_ .'countrylimits` WHERE product_id='.$product_id;
        $result = Db::getInstance()->execute($query);

    }

}

