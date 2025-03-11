<?php

namespace Lknpg\PaymentEredeForGivewp\Includes;

use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Lknpg\PaymentEredeForGivewp\Admin\LknpgPaymentEredeForGivewpAdmin;
use Lknpg\PaymentEredeForGivewp\PublicView\LknpgPaymentEredeForGivewpPublic;
use Lkn_Puc_Plugin_UpdateChecker;

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.linknacional.com.br/wordpress/plugins/
 * @since      1.0.0
 *
 * @package    Payment_Erede_For_Givewp
 * @subpackage Payment_Erede_For_Givewp/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Payment_Erede_For_Givewp
 * @subpackage Payment_Erede_For_Givewp/includes
 * @author     Link Nacional <contato@linknacional.com>
 */
class LknpgPaymentEredeForGivewp {
    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Payment_Erede_For_Givewp_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct() {
        if ( defined( 'PAYMENT_EREDE_FOR_GIVEWP_VERSION' ) ) {
            $this->version = PAYMENT_EREDE_FOR_GIVEWP_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'payment-erede-for-givewp';

        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->schedule_events();
    }

    public function schedule_events() : void {
        if ( ! wp_next_scheduled( 'lknpg_payment_erede_cron_verify_payment' ) ) {
            wp_schedule_event( time() + 60, 'every_minute', 'lknpg_payment_erede_cron_verify_payment' );
        } else {
            wp_schedule_event( time() + 60, 'every_minute', 'lknpg_payment_erede_cron_verify_payment' );
        }
    }

    public function verify_payment() : bool {
        $paymentsToVerify = give_get_option('lkn_erede_3ds_payments_pending', '');
        $paymentsToVerify = json_decode(base64_decode($paymentsToVerify, true) ?: '[]', true);
        $logname = gmdate('d.m.Y-H.i.s') . '-3ds-verification';
    
        if (is_array($paymentsToVerify) && ! empty($paymentsToVerify)) {
            $configs = LknpgPaymentEredeForGivewpHelper::get_configs('debit-3ds');
            $authorization = base64_encode($configs['pv'] . ':' . $configs['token']);
            $paymentsToValidate = array();
            $logname = gmdate('d.m.Y-H.i.s') . '-3ds-verification';
            $headers = array(
                'Authorization' => 'Basic ' . $authorization,
                'Content-Type' => 'application/json'
            );

            foreach ($paymentsToVerify as $payment) {
                $donation_payment = Donation::find($payment['id']);
                
                $responseRaw = wp_remote_get($configs['api_url'] . '?reference=' . 'order' . $payment['id'], array('headers' => $headers));
                $response = json_decode(wp_remote_retrieve_body($responseRaw));
                
                if ('enabled' === $configs['debug']) {
                    LknpgPaymentEredeForGivewpHelper::regLog(
                        'info', // logType
                        'verify_payment', // category
                        'Verificação de pagamento', // description
                        array(
                            'response' => $response,
                            'payment' => $payment
                        ), // data
                        true // forceLog
                    );
                }
    
                if ($response && isset($response->authorization) && isset($response->authorization->returnCode)) {
                    $returnCode = $response->authorization->returnCode;

                    $arrMetaData = array(
                        'status' => $response->authorization->returnCode ?? '500',
                        'message' => $response->authorization->returnMessage ?? 'Error on processing payment',
                        'transaction_id' => $response->authorization->tid ?? '0',
                        'capture' => false
                    );
                } elseif ($response && isset($response->returnCode)) {
                    $returnCode = $response->returnCode;

                    $arrMetaData = array(
                        'status' => $response->returnCode ?? '500',
                        'message' => $response->returnMessage ?? 'Error on processing payment',
                        'transaction_id' => $response->tid ?? '0',
                        'capture' => false
                    );
                } else {
                    $donation_payment->status = DonationStatus::FAILED();
                    $donation_payment->save();
                    continue;
                }

                if ('enabled' === $configs['debug']) {
                    $arrMetaData['log'] = $logname;
                }

                give_update_payment_meta($payment['id'], 'lkn_erede_response', wp_json_encode($arrMetaData));

                // Atualizar o status da doação com base no código de retorno
                switch ($returnCode) {
                    case '00':
                        // Transação aprovada, atualizar o status da doação para COMPLETE
                        $donation_payment->status = DonationStatus::COMPLETE();
                        $donation_payment->save();
                        break;
                    case '78':
                        $counter = isset($payment['count']) ? (int) $payment['count'] + 1 : 1;

                        if ($counter > 5) {
                            $donation_payment->status = DonationStatus::FAILED();
                            $donation_payment->save();
                        } else {
                            $payment['count'] = $counter;
                            $paymentsToValidate[] = $payment;
                        }
                        break;
                    default:
                        // Outro código de retorno não esperado, marcar a doação como FAILED
                        $donation_payment->status = DonationStatus::FAILED();
                        $donation_payment->save();
                        break;
                }
            }
    
            $pendingPayments = ! empty($paymentsToValidate) ? base64_encode(wp_json_encode($paymentsToValidate)) : '';
            give_update_option('lkn_erede_3ds_payments_pending', $pendingPayments);
        } else {
            give_update_option('lkn_erede_3ds_payments_pending', '');
        }
    
        return true;
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Payment_Erede_For_Givewp_Loader. Orchestrates the hooks of the plugin.
     * - Payment_Erede_For_Givewp_i18n. Defines internationalization functionality.
     * - Payment_Erede_For_Givewp_Admin. Defines all hooks for the admin area.
     * - Payment_Erede_For_Givewp_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies(): void {
        $this->loader = new LknpgPaymentEredeForGivewpLoader();
    }

    public function define_row_meta($plugin_meta, $plugin_file) :array {
        if ( ! defined(PAYMENT_EREDE_FOR_GIVEWP_BASENAME) && ! is_plugin_active(PAYMENT_EREDE_FOR_GIVEWP_BASENAME)) {
            return $plugin_meta;
        }

        $new_meta_links['setting'] = sprintf(
            '<a href="%1$s">%2$s</a>',
            admin_url('edit.php?post_type=give_forms&page=give-settings&tab=gateways'),
            __('Settings', 'payment-gateway-e-rede-for-givewp'),
        );
    
        return array_merge($plugin_meta, $new_meta_links);
    }

    public static function givewp_dependency_notice(): void {
        // Admin notice.
        $message = sprintf(
            '<div class="notice notice-error"><p><strong>%1$s</strong> %2$s <a href="%3$s" target="_blank">%4$s</a>  %5$s %6$s+ %7$s.</p></div>',
            __('Activation error:', 'payment-gateway-e-rede-for-givewp'),
            __('You need to have', 'payment-gateway-e-rede-for-givewp'),
            'https://givewp.com',
            __('Give WP', 'payment-gateway-e-rede-for-givewp'),
            __('version', 'payment-gateway-e-rede-for-givewp'),
            PAYMENT_EREDE_FOR_GIVEWP_MIN_GIVE_VERSION,
            __('for the Payment Gateway E-Rede for GiveWP plugin to activate.', 'payment-gateway-e-rede-for-givewp')
        );
        
        echo wp_kses_post($message);
    }

    public function custom_check_redirect_params(): void {
        if ( is_front_page() ) {
            $doacao_id = isset( $_GET['doacao_id'] ) ? (int) sanitize_text_field(wp_unslash(( $_GET['doacao_id'] ))) : 0;
            $status = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash($_GET['status']) ) : '';
    
            if ( $doacao_id && ( 'success' === $status || 'failure' === $status ) ) {
                $redirect_url = '';

                // Determinar a página de destino com base no status
                if ( 'success' === $status ) {
                    // Obter a URL de sucesso do GiveWP
                    $redirect_url = give_get_success_page_uri() . '?donation_id=' . $doacao_id;
                } elseif ( 'failure' === $status ) {
                    // Obter a URL de falha do GiveWP
                    $redirect_url = give_get_failed_transaction_uri();
                }

                // Adicionar o script de redirecionamento ao cabesçalho se a URL de destino for encontrada
                if ( ! empty( $redirect_url ) ) {
                    wp_redirect( $redirect_url );
                    exit;
                }
            }
        }
    }
    
    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks(): void {
        $plugin_admin = new LknpgPaymentEredeForGivewpAdmin( $this->get_plugin_name(), $this->get_version() );

        $this->loader->add_action( 'template_redirect', $this, 'custom_check_redirect_params' );

        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

        $this->loader->add_filter('plugin_action_links_' . PAYMENT_EREDE_FOR_GIVEWP_BASENAME, $this, 'define_row_meta', 10, 2);
        $this->loader->add_action('lknpg_payment_erede_cron_verify_payment', $this, 'verify_payment', 10, 0 );

        $this->loader->add_filter( 'give_get_settings_gateways', $plugin_admin, 'add_settings_into_section' );
        $this->loader->add_filter('give_get_sections_gateways', $plugin_admin, 'new_setting_section');
        $this->loader->add_action('give_view_donation_details_billing_after', $plugin_admin, 'add_donation_details');
        $this->loader->add_action('give_init', $this, 'updater_init');
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks(): void {
        $plugin_public = new LknpgPaymentEredeForGivewpPublic( $this->get_plugin_name(), $this->get_version() );

        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
        $this->loader->add_action('givewp_register_payment_gateway', $this, 'new_gateway_register', 999);
    }

    /**
     * Register gateway to new GiveWP v3
     *
     * @since 3.0.0
     *
     * @param  PaymentGatewayRegister $paymentGatewayRegister 
     *
     * @return void
     */
    final public function new_gateway_register($paymentGatewayRegister): void {
        $paymentGatewayRegister->registerGateway('Lknpg\PaymentEredeForGivewp\PublicView\LknpgPaymentEredeForGivewpDebitGateway');
        $paymentGatewayRegister->registerGateway('Lknpg\PaymentEredeForGivewp\PublicView\LknpgPaymentEredeForGivewpCreditGateway');
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run(): void {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    Payment_Erede_For_Givewp_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }

    public function updater_init() {
        return new Lkn_Puc_Plugin_UpdateChecker(
            'https://api.linknacional.com/v2/u/?slug=payment-erede-for-givewp',
            PAYMENT_EREDE_FOR_GIVEWP_FILE,//(caso o plugin não precise de compatibilidade com ioncube utilize: __FILE__), //Full path to the main plugin file or functions.php.
            'payment-erede-for-givewp'
        );
    }
}
