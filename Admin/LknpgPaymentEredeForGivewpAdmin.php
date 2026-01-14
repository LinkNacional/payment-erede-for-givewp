<?php

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

namespace Lknpg\PaymentEredeForGivewp\Admin;

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
class LknpgPaymentEredeForGivewpAdmin {
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
        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/lknpgPaymentEredeForGivewpAdmin.css', array(), $this->version, 'all' );
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
        wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/lknpgPaymentEredeForGivewpAdmin.js', array('jquery'), $this->version, false );
        
        $noticeDesc = sprintf(
            ' %1$s %2$s %3$s %4$s',
            __('Get new features with', 'payment-gateway-e-rede-for-givewp'),
            '<a href="https://www.linknacional.com.br/wordpress/" target="_blank">',
            __('Payment E-Rede for GiveWP PRO.', 'payment-gateway-e-rede-for-givewp'),
            '</a>',
        );

        $currencyExchangeLabel = sprintf(
            '%1$s %2$s %3$s %4$s',
            __('Calculate exchange rates automatically for international currencies, we have full compatibility with the', 'payment-gateway-e-rede-for-givewp'),
            '<a href="https://www.linknacional.com.br/wordpress/givewp/multimoeda/" target="_blank">',
            __('Multicurrency plugin for GiveWP by Link Nacional.', 'payment-gateway-e-rede-for-givewp'),
            '</a>',
        );

        wp_localize_script($this->plugin_name, 'lknEredePaymentAdmin', array(
            'notice' => esc_html($noticeDesc),
            'captureLabelTitle' => esc_html__('Manual capture your transactions', 'payment-gateway-e-rede-for-givewp'),
            'captureLabelDesc' => esc_html__('Capture your transactions manually to avoid chargeback and card testing.', 'payment-gateway-e-rede-for-givewp'),
            'returnLabelTitle' => esc_html__('Refund your transactions', 'payment-gateway-e-rede-for-givewp'),
            'returnLabelDesc' => esc_html__('Option to refund transaction amount integrated into GiveWP donation details.', 'payment-gateway-e-rede-for-givewp'),
            'installmentLabelTitle' => esc_html__('Donations in installments', 'payment-gateway-e-rede-for-givewp'),
            'installmentLabelDesc' => esc_html__('Option for your donor to pay the donation in installments.', 'payment-gateway-e-rede-for-givewp'),
            'currencyExchangeLabelTitle' => esc_html__('International currency exchange', 'payment-gateway-e-rede-for-givewp'),
            'currencyExchangeLabelDesc' => $currencyExchangeLabel,
        ));
    }

    /**
     * Add new section to "General" setting tab
     *
     * @param $sections
     *
     * @return array
     */
    public function new_setting_section($sections) {
        $sections['lkn-erede-credit'] = __('E-Rede API - Credit Card', 'payment-gateway-e-rede-for-givewp');
        $sections['lkn-erede-debit-3ds'] = __('E-Rede API - Debit Card 3DS', 'payment-gateway-e-rede-for-givewp');

        return $sections;
    }

    public function add_settings_into_section($settings) :array {
        $currentSection = give_get_current_setting_section();

        switch ($currentSection) {
            case 'lkn-erede-credit':
                $settings[] = array(
                    'type' => 'title',
                    'id' => 'lkn_erede_credit',
                );
    
                $settings[] = array(
                    'name' => __('Environment type', 'payment-gateway-e-rede-for-givewp'),
                    'id' => 'lkn_erede_credit_env_setting_field',
                    'desc' => __('Environment type to make transactions.', 'payment-gateway-e-rede-for-givewp'),
                    'type' => 'radio',
                    'default' => 'sandbox',
                    'options' => array(
                        'sandbox' => __('Homologation environment for developer', 'payment-gateway-e-rede-for-givewp'),
                        'production' => __('Production', 'payment-gateway-e-rede-for-givewp')
                    ),
                );
    
                $settings[] = array(
                    'name' => __('PV', 'payment-gateway-e-rede-for-givewp'),
                    'id' => 'lkn_erede_credit_pv_setting_field',
                    'desc' => __('E-Rede API credential filiation number.', 'payment-gateway-e-rede-for-givewp'),
                    'type' => 'api_key',
                    'default' => '',
                );
    
                $settings[] = array(
                    'name' => __('Token', 'payment-gateway-e-rede-for-givewp'),
                    'id' => 'lkn_erede_credit_token_setting_field',
                    'desc' => __('E-Rede API credential secret token.', 'payment-gateway-e-rede-for-givewp'),
                    'type' => 'api_key',
                    'default' => '',
                );

                //TODO traduzir textos
                $settings[] = array(
                    'name' => __('Permitir doação sem descrição de transação', 'payment-gateway-e-rede-for-givewp'),
                    'id' => 'lkn_erede_credit_enable_transaction_without_description',
                    'desc' => __('Habilitar esta opção permite que doações sejam feitas sem incluir uma descrição na transação.', 'payment-gateway-e-rede-for-givewp'),
                    'type' => 'radio',
                    'default' => 'disabled',
                    'options' => array(
                        'enabled' => __('Enabled', 'payment-gateway-e-rede-for-givewp'),
                        'disabled' => __('Disabled', 'payment-gateway-e-rede-for-givewp')
                    ),
                );

                if (give_get_option('lkn_erede_credit_enable_transaction_without_description', 'disabled') === 'disabled') {
                    $settings[] = array(
                        'name' => __('Transaction description', 'payment-gateway-e-rede-for-givewp'),
                        'id' => 'lkn_erede_credit_softdescription_setting_field',
                        'desc' => __('Description that will appear on the customer card statement, does not allow special characters or white space.', 'payment-gateway-e-rede-for-givewp'),
                        'type' => 'text',
                        'default' => '',
                    );
                }
    
                $settings[] = array(
                    'name' => __('Billing fields', 'payment-gateway-e-rede-for-givewp'),
                    'id' => 'lkn_erede_credit_billing_fields_setting_field',
                    'desc' => __('Adds additional address fields to your donation form.', 'payment-gateway-e-rede-for-givewp'),
                    'type' => 'radio',
                    'default' => 'disabled',
                    'options' => array(
                        'enabled' => __('Enabled', 'payment-gateway-e-rede-for-givewp'),
                        'disabled' => __('Disabled', 'payment-gateway-e-rede-for-givewp')
                    ),
                );

                $settings[] = array(
                    'name' => 'Seguir transação sem autenticação Erede 3DS 2.0',
                    'id' => 'lkn_erede_credit_transaction_without_authentication',
                    'desc' => __('If the option is enabled, the transaction continues without Erede 3DS 2.0 authentication.', 'payment-gateway-e-rede-for-givewp'),
                    'type' => 'radio',
                    'default' => 'disabled',
                    'options' => array(
                        'enabled' => 'Habilitar',
                        'disabled' => 'Desabilitar',
                    ),
                );
    
                $settings[] = array(
                    'name' => __('Debug mode', 'payment-gateway-e-rede-for-givewp'),
                    'id' => 'lkn_erede_credit_debug_setting_field',
                    'desc' => sprintf(
                        /* translators: 1: HTML tag 2: HTML tag end */
                        __( 'Saves transaction logs for testing purposes. %1$sSee logs.%2$s', 'payment-gateway-e-rede-for-givewp'),
                        '<a target="_blank" href="' . home_url('wp-admin/edit.php?post_type=give_forms&page=give-tools&tab=logs') . '">',
                        '</a>'
                    ),
                    'type' => 'radio',
                    'default' => 'disabled',
                    'options' => array(
                        'enabled' => __('Enabled', 'payment-gateway-e-rede-for-givewp'),
                        'disabled' => __('Disabled', 'payment-gateway-e-rede-for-givewp')
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
                    'name' => __('Environment type', 'payment-gateway-e-rede-for-givewp'),
                    'id' => 'lkn_erede_debit_3ds_env_setting_field',
                    'desc' => __('Environment type to make transactions.', 'payment-gateway-e-rede-for-givewp'),
                    'type' => 'radio',
                    'default' => 'sandbox',
                    'options' => array(
                        'sandbox' => __('Homologation environment for developer', 'payment-gateway-e-rede-for-givewp'),
                        'production' => __('Production', 'payment-gateway-e-rede-for-givewp')
                    ),
                );
    
                $settings[] = array(
                    'name' => __('PV', 'payment-gateway-e-rede-for-givewp'),
                    'id' => 'lkn_erede_debit_3ds_pv_setting_field',
                    'desc' => __('E-Rede API credential filiation number.', 'payment-gateway-e-rede-for-givewp'),
                    'type' => 'api_key',
                    'default' => '',
                );
    
                $settings[] = array(
                    'name' => __('Token', 'payment-gateway-e-rede-for-givewp'),
                    'id' => 'lkn_erede_debit_3ds_token_setting_field',
                    'desc' => __('E-Rede API credential secret token.', 'payment-gateway-e-rede-for-givewp'),
                    'type' => 'api_key',
                    'default' => '',
                );

                //TODO traduzir textos
                $settings[] = array(
                    'name' => __('Permitir doação sem descrição de transação', 'payment-gateway-e-rede-for-givewp'),
                    'id' => 'lkn_erede_debit_3ds_enable_transaction_without_description',
                    'desc' => __('Habilitar esta opção permite que doações sejam feitas sem incluir uma descrição na transação.', 'payment-gateway-e-rede-for-givewp'),
                    'type' => 'radio',
                    'default' => 'disabled',
                    'options' => array(
                        'enabled' => __('Enabled', 'payment-gateway-e-rede-for-givewp'),
                        'disabled' => __('Disabled', 'payment-gateway-e-rede-for-givewp')
                    ),
                );

                if (give_get_option('lkn_erede_debit_3ds_enable_transaction_without_description', 'disabled') === 'disabled') {
                    $settings[] = array(
                        'name' => __('Transaction description', 'payment-gateway-e-rede-for-givewp'),
                        'id' => 'lkn_erede_debit_3ds_softdescription_setting_field',
                        'desc' => __('Description that will appear on the customer card statement, does not allow special characters or white space.', 'payment-gateway-e-rede-for-givewp'),
                        'type' => 'text',
                        'default' => '',
                    );
                }
    
                $settings[] = array(
                    'name' => __('Billing fields', 'payment-gateway-e-rede-for-givewp'),
                    'id' => 'lkn_erede_debit_3ds_billing_fields_setting_field',
                    'desc' => __('Adds additional address fields to your donation form.', 'payment-gateway-e-rede-for-givewp'),
                    'type' => 'radio',
                    'default' => 'disabled',
                    'options' => array(
                        'enabled' => __('Enabled', 'payment-gateway-e-rede-for-givewp'),
                        'disabled' => __('Disabled', 'payment-gateway-e-rede-for-givewp')
                    ),
                );
    
                $settings[] = array(
                    'name' => __('Debug mode', 'payment-gateway-e-rede-for-givewp'),
                    'id' => 'lkn_erede_debit_3ds_debug_setting_field',
                    'desc' => sprintf(
                        /* translators: 1: HTML tag 2: HTML tag end */
                        __( 'Saves transaction logs for testing purposes. %1$sSee logs.%2$s', 'payment-gateway-e-rede-for-givewp'),                        '<a target="_blank" href="' . home_url('wp-admin/edit.php?post_type=give_forms&page=give-tools&tab=logs') . '">',
                        '</a>'
                    ),
                    'type' => 'radio',
                    'default' => 'disabled',
                    'options' => array(
                        'enabled' => __('Enabled', 'payment-gateway-e-rede-for-givewp'),
                        'disabled' => __('Disabled', 'payment-gateway-e-rede-for-givewp')
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
        $metaOpt = json_decode(give_get_meta($payment_id, 'lkn_erede_response', true));

        if (isset($metaOpt->status)) {
            load_template(
                plugin_dir_path(__FILE__) . 'partials/lknpgPaymentEredeForGivewpAdminDisplay.php',
                true,
                array(
                    'status' => $metaOpt->status,
                    'message' => $metaOpt->message,
                    'transaction_id' => $metaOpt->transaction_id,
                    'capture' => $metaOpt->capture,
                    'log_exists' => file_exists(PAYMENT_EREDE_FOR_GIVEWP_LOG_DIR . $metaOpt->log . '.log'),
                    'log_data' => base64_encode(file_get_contents(PAYMENT_EREDE_FOR_GIVEWP_LOG_DIR . $metaOpt->log . '.log')),
                    'status_label' => __('Return code:', 'payment-gateway-e-rede-for-givewp'),
                    'message_label' => __('Return message:', 'payment-gateway-e-rede-for-givewp'),
                    'transaction_label' => __('Transaction ID:', 'payment-gateway-e-rede-for-givewp'),
                    'log_label' => __('Transaction log in base64', 'payment-gateway-e-rede-for-givewp'),
                    'know_more_label' => __('Know more', 'payment-gateway-e-rede-for-givewp')
                )
            );
        }
    }
}
