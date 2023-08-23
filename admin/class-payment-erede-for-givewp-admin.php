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
        $gateways['lkn_payment_erede'] = array(
            'admin_label' => __('Payment E-Rede for GiveWP', PAYMENT_EREDE_FOR_GIVEWP_TEXT_DOMAIN),
            'checkout_label' => __('Payment E-Rede for GiveWP', PAYMENT_EREDE_FOR_GIVEWP_TEXT_DOMAIN),
        );

        return $gateways;
    }

    public function add_new_setting_section($sections) :array {
        $sections['lkn-payment-erede'] = __('Payment E-Rede for GiveWP', PAYMENT_EREDE_FOR_GIVEWP_TEXT_DOMAIN);

        return $sections;
    }

    public function add_settings_into_section($settings) :array {
        $currentSection = give_get_current_setting_section();

        if ('lkn-payment-erede' === $currentSection) {
            $settings[] = array(
                'type' => 'title',
                'id' => 'lkn_payment_erede',
            );

            $settings[] = array(
                'name' => __('Payment E-Rede', PAYMENT_EREDE_FOR_GIVEWP_TEXT_DOMAIN),
                'id' => 'lkn_payment_erede_setting_field',
                'desc' => __('Enable or disable the option to add the payment fee amount for the donor.', PAYMENT_EREDE_FOR_GIVEWP_TEXT_DOMAIN),
                'type' => 'radio',
                'default' => 'disabled',
                'options' => array(
                    'enabled' => __('Enabled', PAYMENT_EREDE_FOR_GIVEWP_TEXT_DOMAIN),
                    'disabled' => __('Disabled', PAYMENT_EREDE_FOR_GIVEWP_TEXT_DOMAIN)
                ),
            );

            $settings[] = array(
                'id' => 'lkn_payment_erede',
                'type' => 'sectionend',
            );
        }
        
        return $settings;
    }
}
