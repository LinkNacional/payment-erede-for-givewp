<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.linknacional.com.br/wordpress/plugins/
 * @since      1.0.0
 *
 * @package    Payment_Erede_For_Givewp
 * @subpackage Payment_Erede_For_Givewp/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Payment_Erede_For_Givewp
 * @subpackage Payment_Erede_For_Givewp/admin
 * @author     Link Nacional <contato@linknacional.com>
 */
class Payment_Erede_For_Givewp_Admin {
    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles(): void {
        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Payment_Erede_For_Givewp_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Payment_Erede_For_Givewp_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/payment-erede-for-givewp-admin.css', array(), $this->version, 'all' );
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts(): void {
        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Payment_Erede_For_Givewp_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Payment_Erede_For_Givewp_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/payment-erede-for-givewp-admin.js', array('jquery'), $this->version, false );
    }

    public function register_gateway($gateways) {
        $gateways['lkn_erede_credit'] = array(
            'admin_label' => __('E-Rede API - Credit Card', PAYMENT_EREDE_FOR_GIVEWP_TEXT_DOMAIN),
            'checkout_label' => __('E-Rede - Credit Card', PAYMENT_EREDE_FOR_GIVEWP_TEXT_DOMAIN),
        );

        $gateways['lkn_erede_debit_3ds'] = array(
            'admin_label' => __('E-Rede API - Debit Card 3DS', PAYMENT_EREDE_FOR_GIVEWP_TEXT_DOMAIN),
            'checkout_label' => __('E-Rede - Debit Card', PAYMENT_EREDE_FOR_GIVEWP_TEXT_DOMAIN),
        );

        return $gateways;
    }

    public function add_new_setting_section($sections) :array {
        $sections['lkn-erede-credit'] = __('E-Rede API - Credit Card', PAYMENT_EREDE_FOR_GIVEWP_TEXT_DOMAIN);
        $sections['lkn-erede-debit-3ds'] = __('E-Rede API - Debit Card 3DS', PAYMENT_EREDE_FOR_GIVEWP_TEXT_DOMAIN);

        return $sections;
    }

    public function add_settings_into_section($settings) :array {
        $currentSection = give_get_current_setting_section();

        switch ($currentSection) {
            // TODO add paid options disabled freemium
            // manual capture, installments, return

            case 'lkn-erede-credit':
                $settings[] = array(
                    'type' => 'title',
                    'id' => 'lkn_erede_credit',
                );
    
                $settings[] = array(
                    'name' => __('Environment type', PAYMENT_EREDE_FOR_GIVEWP_TEXT_DOMAIN),
                    'id' => 'lkn_erede_credit_env_setting_field',
                    'desc' => __('Environment type to make transactions.', PAYMENT_EREDE_FOR_GIVEWP_TEXT_DOMAIN),
                    'type' => 'radio',
                    'default' => 'sandbox',
                    'options' => array(
                        'sandbox' => __('Homologation developer environment', PAYMENT_EREDE_FOR_GIVEWP_TEXT_DOMAIN),
                        'production' => __('Production', PAYMENT_EREDE_FOR_GIVEWP_TEXT_DOMAIN)
                    ),
                );
    
                $settings[] = array(
                    'name' => __('PV', PAYMENT_EREDE_FOR_GIVEWP_TEXT_DOMAIN),
                    'id' => 'lkn_erede_credit_pv_setting_field',
                    'desc' => __('E-Rede API credential filiation number.', PAYMENT_EREDE_FOR_GIVEWP_TEXT_DOMAIN),
                    'type' => 'api_key',
                    'default' => '',
                );
    
                $settings[] = array(
                    'name' => __('Token', PAYMENT_EREDE_FOR_GIVEWP_TEXT_DOMAIN),
                    'id' => 'lkn_erede_credit_token_setting_field',
                    'desc' => __('E-Rede API credential secret token', PAYMENT_EREDE_FOR_GIVEWP_TEXT_DOMAIN),
                    'type' => 'api_key',
                    'default' => '',
                );

                $settings[] = array(
                    'name' => __('Transaction description', PAYMENT_EREDE_FOR_GIVEWP_TEXT_DOMAIN),
                    'id' => 'lkn_erede_credit_softdescription_setting_field',
                    'desc' => __('Description that will appear on the customer\'s card statement, does not allow special characters or white space', PAYMENT_EREDE_FOR_GIVEWP_TEXT_DOMAIN),
                    'type' => 'text',
                    'default' => '',
                );
    
                $settings[] = array(
                    'name' => __('Billing fields', PAYMENT_EREDE_FOR_GIVEWP_TEXT_DOMAIN),
                    'id' => 'lkn_erede_credit_billing_fields_setting_field',
                    'desc' => __('Adds additional address fields to your donation form.', PAYMENT_EREDE_FOR_GIVEWP_TEXT_DOMAIN),
                    'type' => 'radio',
                    'default' => 'disabled',
                    'options' => array(
                        'enabled' => __('Enabled', PAYMENT_EREDE_FOR_GIVEWP_TEXT_DOMAIN),
                        'disabled' => __('Disabled', PAYMENT_EREDE_FOR_GIVEWP_TEXT_DOMAIN)
                    ),
                );
    
                $settings[] = array(
                    'name' => __('Debug mode', PAYMENT_EREDE_FOR_GIVEWP_TEXT_DOMAIN),
                    'id' => 'lkn_erede_credit_debug_setting_field',
                    'desc' => __('Saves transaction logs for testing purposes.', PAYMENT_EREDE_FOR_GIVEWP_TEXT_DOMAIN),
                    'type' => 'radio',
                    'default' => 'disabled',
                    'options' => array(
                        'enabled' => __('Enabled', PAYMENT_EREDE_FOR_GIVEWP_TEXT_DOMAIN),
                        'disabled' => __('Disabled', PAYMENT_EREDE_FOR_GIVEWP_TEXT_DOMAIN)
                    ),
                );
    
                $settings[] = array(
                    'id' => 'lkn_erede_credit',
                    'type' => 'sectionend',
                );

                break;
            case 'lkn-erede-debit-3ds':
                $settings[] = array(
                    'type' => 'title',
                    'id' => 'lkn_erede_debit_3ds',
                );
    
                $settings[] = array(
                    'name' => __('Environment type', PAYMENT_EREDE_FOR_GIVEWP_TEXT_DOMAIN),
                    'id' => 'lkn_erede_debit_3ds_env_setting_field',
                    'desc' => __('Environment type to make transactions.', PAYMENT_EREDE_FOR_GIVEWP_TEXT_DOMAIN),
                    'type' => 'radio',
                    'default' => 'sandbox',
                    'options' => array(
                        'sandbox' => __('Homologation developer environment', PAYMENT_EREDE_FOR_GIVEWP_TEXT_DOMAIN),
                        'production' => __('Production', PAYMENT_EREDE_FOR_GIVEWP_TEXT_DOMAIN)
                    ),
                );
    
                $settings[] = array(
                    'name' => __('PV', PAYMENT_EREDE_FOR_GIVEWP_TEXT_DOMAIN),
                    'id' => 'lkn_erede_debit_3ds_pv_setting_field',
                    'desc' => __('E-Rede API credential filiation number.', PAYMENT_EREDE_FOR_GIVEWP_TEXT_DOMAIN),
                    'type' => 'api_key',
                    'default' => '',
                );
    
                $settings[] = array(
                    'name' => __('Token', PAYMENT_EREDE_FOR_GIVEWP_TEXT_DOMAIN),
                    'id' => 'lkn_erede_debit_3ds_token_setting_field',
                    'desc' => __('E-Rede API credential secret token', PAYMENT_EREDE_FOR_GIVEWP_TEXT_DOMAIN),
                    'type' => 'api_key',
                    'default' => '',
                );

                $settings[] = array(
                    'name' => __('Transaction description', PAYMENT_EREDE_FOR_GIVEWP_TEXT_DOMAIN),
                    'id' => 'lkn_erede_debit_3ds_softdescription_setting_field',
                    'desc' => __('Description that will appear on the customer\'s card statement, does not allow special characters or white space', PAYMENT_EREDE_FOR_GIVEWP_TEXT_DOMAIN),
                    'type' => 'text',
                    'default' => '',
                );
    
                $settings[] = array(
                    'name' => __('Billing fields', PAYMENT_EREDE_FOR_GIVEWP_TEXT_DOMAIN),
                    'id' => 'lkn_erede_debit_3ds_billing_fields_setting_field',
                    'desc' => __('Adds additional address fields to your donation form.', PAYMENT_EREDE_FOR_GIVEWP_TEXT_DOMAIN),
                    'type' => 'radio',
                    'default' => 'disabled',
                    'options' => array(
                        'enabled' => __('Enabled', PAYMENT_EREDE_FOR_GIVEWP_TEXT_DOMAIN),
                        'disabled' => __('Disabled', PAYMENT_EREDE_FOR_GIVEWP_TEXT_DOMAIN)
                    ),
                );
    
                $settings[] = array(
                    'name' => __('Debug mode', PAYMENT_EREDE_FOR_GIVEWP_TEXT_DOMAIN),
                    'id' => 'lkn_erede_debit_3ds_debug_setting_field',
                    'desc' => __('Saves transaction logs for testing purposes.', PAYMENT_EREDE_FOR_GIVEWP_TEXT_DOMAIN),
                    'type' => 'radio',
                    'default' => 'disabled',
                    'options' => array(
                        'enabled' => __('Enabled', PAYMENT_EREDE_FOR_GIVEWP_TEXT_DOMAIN),
                        'disabled' => __('Disabled', PAYMENT_EREDE_FOR_GIVEWP_TEXT_DOMAIN)
                    ),
                );
    
                $settings[] = array(
                    'id' => 'lkn_erede_debit_3ds',
                    'type' => 'sectionend',
                );

                break;
            
            default:
                # code...
                break;
        }
        
        return $settings;
    }

    public function add_donation_details($payment_id) :void {
        load_template(
            plugin_dir_path(__FILE__) . 'partials/payment-erede-for-givewp-admin-display.php',
            true,
            array(
            )
        );
    }
}
