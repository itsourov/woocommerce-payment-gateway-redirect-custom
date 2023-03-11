<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://itsourov.com
 * @since             1.0.1
 * @package           Spg
 *
 * @wordpress-plugin
 * Plugin Name:       Payment gateway by sourov
 * Plugin URI:        https://itsourov.com
 * Description:       amar banano gateway te redirect korar jonno plugin
 * Version:           1.0.0
 * Author:            Sourov Biswas
 * Author URI:        https://itsourov.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       spg
 * Domain Path:       /languages
 */


/*
 * This action hook registers our PHP class as a WooCommerce payment gateway
 */
add_filter('woocommerce_payment_gateways', 'spg_add_gateway_class');
function spg_add_gateway_class($gateways)
{
    $gateways[] = 'WC_spg_Gateway'; // your class name is here
    return $gateways;
}

/*
 * The class itself, please note that it is inside plugins_loaded action hook
 */
add_action('plugins_loaded', 'spg_init_gateway_class');

function spg_init_gateway_class()
{

    class WC_spg_Gateway extends WC_Payment_Gateway
    {

        /**
         * Class constructor, more about it in Step 3
         */
        public function __construct()
        {
            $this->id = 'spg'; // payment gateway plugin ID
            $this->icon = ''; // URL of the icon that will be displayed on checkout page near your gateway name
            $this->has_fields = true; // in case you need a custom credit card form
            $this->method_title = 'spg Gateway';
            $this->method_description = 'Description of spg payment gateway'; // will be displayed on the options page

            // gateways can support subscriptions, refunds, saved payment methods,
            // but in this tutorial we begin with simple payments
            $this->supports = array(
                'products'
            );

            // Method with all the options fields
            $this->init_form_fields();

            // Load the settings.
            $this->init_settings();
            $this->title = $this->get_option('title');
            $this->description = $this->get_option('description');
            $this->enabled = $this->get_option('enabled');

            $this->gateway_url =  $this->get_option('gateway_url');
            $this->icon_url =  $this->get_option('icon_url');
            $this->icon_url_in_gateway =  $this->get_option('icon_url_in_gateway');
            $this->icon = $this->icon_url;


            // This action hook saves the settings
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));


            // // You can also register a webhook here
            // // add_action( 'woocommerce_api_{webhook name}', array( $this, 'webhook' ) );

        }

        /**
         * Plugin options, we deal with it in Step 3 too
         */
        public function init_form_fields()
        {

            $this->form_fields = array(
                'enabled' => array(
                    'title'       => 'Enable/Disable',
                    'label'       => 'Enable spg Gateway',
                    'type'        => 'checkbox',
                    'description' => '',
                    'default'     => 'no'
                ),
                'title' => array(
                    'title'       => 'Title',
                    'type'        => 'text',
                    'description' => 'This controls the title which the user sees during checkout.',
                    'default'     => 'Credit Card',
                    'desc_tip'    => true,
                ),
                'description' => array(
                    'title'       => 'Description',
                    'type'        => 'textarea',
                    'description' => 'This controls the description which the user sees during checkout.',
                    'default'     => 'Pay with your credit card via our super-cool payment gateway.',
                ),

                'gateway_url' => array(
                    'title'       => 'Gateway Url',
                    'description' => 'The payment gateway that sourov built',
                    'type'        => 'url'
                ),
                'icon_url' => array(
                    'title'       => 'Icon Url',
                    'type'        => 'url'
                ),
                'icon_url_in_gateway' => array(
                    'title'       => 'Icon Url in gateway',
                    'type'        => 'url'
                ),

            );
        }

        /**
         * You will need it if you want your custom credit card form, Step 4 is about it
         */
        public function payment_fields()
        {
            echo $this->description;
        }

        /*
		 * Custom CSS and JS, in most cases required only when you decided to go with a custom credit card form
		 */
        public function payment_scripts()
        {
        }

        /*
 		 * Fields validation, more in Step 5
		 */
        public function validate_fields()
        {
        }

        /*
		 * We're processing the payments here, everything about it is in Step 5
		 */
        public function process_payment($order_id)
        {
            global $woocommerce;

            // we need it to get any order detailes
            $order = wc_get_order($order_id);

            if (empty($this->gateway_url)) {
                wc_add_notice('Gateway url not set', 'error');
                return;
            }
            return array(
                'result' => 'success',
                'redirect' => $this->gateway_url . '?invoice=' . urlencode($order_id) . '&email=' . urlencode($order->get_billing_email()) . '&price=' . urlencode($order->get_total()) . '&fallbackUrl=' . urlencode(wc_get_checkout_url()) . '&icon=' . urlencode($this->icon_url_in_gateway), // ???
            );
        }

        /*
		 * In case you need a webhook, like PayPal IPN etc
		 */
        public function webhook()
        {
        }
    }
}
