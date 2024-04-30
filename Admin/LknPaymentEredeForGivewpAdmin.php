<?php

namespace Lkn\PaymentEredeForGivewp\Admin;

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
class LknPaymentEredeForGivewpAdmin {
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
        
        $noticeDesc = sprintf(
            ' %1$s %2$s %3$s %4$s',
            'Get new features with',
            '<a href="https://www.linknacional.com.br/wordpress/" target="_blank">',
            'Payment E-Rede for GiveWP PRO.',
            '</a>',
        );

        $currencyExchangeLabel = sprintf(
            '%1$s %2$s %3$s %4$s',
            'Calculate exchange rates automatically for international currencies, we have full compatibility with the',
            '<a href="https://www.linknacional.com.br/wordpress/givewp/multimoeda/" target="_blank">',
            'Multicurrency plugin for GiveWP by Link Nacional.',
            '</a>',
        );

        wp_localize_script($this->plugin_name, 'lknEredePaymentAdmin', array(
            'notice' => esc_html($noticeDesc),
            'captureLabelTitle' => esc_html('Manual capture your transactions'),
            'captureLabelDesc' => esc_html('Capture your transactions manually to avoid chargeback and card testing.'),
            'returnLabelTitle' => esc_html('Refund your transactions'),
            'returnLabelDesc' => esc_html('Option to refund transaction amount integrated into GiveWP donation details.'),
            'installmentLabelTitle' => esc_html('Donations in installments'),
            'installmentLabelDesc' => esc_html('Option for your donor to pay the donation in installments.'),
            'currencyExchangeLabelTitle' => esc_html('International currency exchange'),
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
        $sections['lkn-erede-credit'] = 'E-Rede API - Credit Card';
        $sections['lkn-erede-debit-3ds'] = 'E-Rede API - Debit Card 3DS';

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
                    'name' => 'Environment type',
                    'id' => 'lkn_erede_credit_env_setting_field',
                    'desc' => 'Environment type to make transactions.',
                    'type' => 'radio',
                    'default' => 'sandbox',
                    'options' => array(
                        'sandbox' => 'Homologation environment for developer',
                        'production' => 'Production'
                    ),
                );
    
                $settings[] = array(
                    'name' => 'PV',
                    'id' => 'lkn_erede_credit_pv_setting_field',
                    'desc' => 'E-Rede API credential filiation number.',
                    'type' => 'api_key',
                    'default' => '',
                );
    
                $settings[] = array(
                    'name' => 'Token',
                    'id' => 'lkn_erede_credit_token_setting_field',
                    'desc' => 'E-Rede API credential secret token.',
                    'type' => 'api_key',
                    'default' => '',
                );

                $settings[] = array(
                    'name' => 'Transaction description',
                    'id' => 'lkn_erede_credit_softdescription_setting_field',
                    'desc' => 'Description that will appear on the customer card statement, does not allow special characters or white space.',
                    'type' => 'text',
                    'default' => '',
                );
    
                $settings[] = array(
                    'name' => 'Billing fields',
                    'id' => 'lkn_erede_credit_billing_fields_setting_field',
                    'desc' => 'Adds additional address fields to your donation form.',
                    'type' => 'radio',
                    'default' => 'disabled',
                    'options' => array(
                        'enabled' => 'Enabled',
                        'disabled' => 'Disabled'
                    ),
                );

                $settings[] = array(
                    'name' => 'Seguir transação sem autenticação Erede 3DS 2.0',
                    'id' => 'lkn_erede_credit_transaction_without_authentication',
                    'desc' => 'Caso esteja com a opção habilitada segue a transação sem autenticação do Erede 3DS 2.0.',
                    'type' => 'radio',
                    'default' => 'disabled',
                    'options' => array(
                        'enabled' => 'Habilitar',
                        'disabled' => 'Desabilitar',
                    ),
                );
    
                $settings[] = array(
                    'name' => 'Debug mode',
                    'id' => 'lkn_erede_credit_debug_setting_field',
                    'desc' => 'Saves transaction logs for testing purposes.',
                    'type' => 'radio',
                    'default' => 'disabled',
                    'options' => array(
                        'enabled' => 'Enabled',
                        'disabled' => 'Disabled'
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
                    'name' => 'Environment type',
                    'id' => 'lkn_erede_debit_3ds_env_setting_field',
                    'desc' => 'Environment type to make transactions.',
                    'type' => 'radio',
                    'default' => 'sandbox',
                    'options' => array(
                        'sandbox' => 'Homologation environment for developer',
                        'production' => 'Production'
                    ),
                );
    
                $settings[] = array(
                    'name' => 'PV',
                    'id' => 'lkn_erede_debit_3ds_pv_setting_field',
                    'desc' => 'E-Rede API credential filiation number.',
                    'type' => 'api_key',
                    'default' => '',
                );
    
                $settings[] = array(
                    'name' => 'Token',
                    'id' => 'lkn_erede_debit_3ds_token_setting_field',
                    'desc' => 'E-Rede API credential secret token.',
                    'type' => 'api_key',
                    'default' => '',
                );

                $settings[] = array(
                    'name' => 'Transaction description',
                    'id' => 'lkn_erede_debit_3ds_softdescription_setting_field',
                    'desc' => 'Description that will appear on the customer card statement, does not allow special characters or white space.',
                    'type' => 'text',
                    'default' => '',
                );
    
                $settings[] = array(
                    'name' => 'Billing fields',
                    'id' => 'lkn_erede_debit_3ds_billing_fields_setting_field',
                    'desc' => 'Adds additional address fields to your donation form.',
                    'type' => 'radio',
                    'default' => 'disabled',
                    'options' => array(
                        'enabled' => 'Enabled',
                        'disabled' => 'Disabled'
                    ),
                );
    
                $settings[] = array(
                    'name' => 'Debug mode',
                    'id' => 'lkn_erede_debit_3ds_debug_setting_field',
                    'desc' => 'Saves transaction logs for testing purposes.',
                    'type' => 'radio',
                    'default' => 'disabled',
                    'options' => array(
                        'enabled' => 'Enabled',
                        'disabled' => 'Disabled'
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
                plugin_dir_path(__FILE__) . 'partials/payment-erede-for-givewp-admin-display.php',
                true,
                array(
                    'status' => $metaOpt->status,
                    'message' => $metaOpt->message,
                    'transaction_id' => $metaOpt->transaction_id,
                    'capture' => $metaOpt->capture,
                    'log_exists' => file_exists(PAYMENT_EREDE_FOR_GIVEWP_LOG_DIR . $metaOpt->log . '.log'),
                    'log_data' => base64_encode(file_get_contents(PAYMENT_EREDE_FOR_GIVEWP_LOG_DIR . $metaOpt->log . '.log')),
                    'status_label' => 'Return code:',
                    'message_label' => 'Return message:',
                    'transaction_label' => 'Transaction ID:',
                    'log_label' => 'Transaction log in base64',
                    'know_more_label' => 'Know more'
                )
            );
        }
    }
}
