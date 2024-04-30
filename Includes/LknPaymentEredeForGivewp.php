<?php

namespace Lkn\PaymentEredeForGivewp\Includes;

use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Lkn\PaymentEredeForGivewp\Admin\LknPaymentEredeForGivewpAdmin;
use Lkn\PaymentEredeForGivewp\PublicView\LknPaymentEredeForGivewpPublic;
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
class LknPaymentEredeForGivewp {
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
        if ( ! wp_next_scheduled( 'lkn_payment_erede_cron_delete_logs' ) ) {
            wp_schedule_event( time() + 604800, 'weekly', 'lkn_payment_erede_cron_delete_logs' );
        }

        if ( ! wp_next_scheduled( 'lkn_payment_erede_cron_verify_payment' ) ) {
            wp_schedule_event( time() + 60, 'every_minute', 'lkn_payment_erede_cron_verify_payment' );
        }
    }

    public function verify_payment() : bool {
        $paymentsToVerify = give_get_option('lkn_erede_debit_3ds_payments_pending', '');
        $paymentsToVerify = json_decode(base64_decode($paymentsToVerify, true) ?: '[]', true);
        $logname = date('d.m.Y-H.i.s') . '-debit-3ds-verification';
    
        if (is_array($paymentsToVerify) && ! empty($paymentsToVerify)) {
            $configs = LknPaymentEredeForGivewpHelper::get_configs('debit-3ds');
            $authorization = base64_encode($configs['pv'] . ':' . $configs['token']);
            $paymentsToValidate = array();
            $logname = date('d.m.Y-H.i.s') . '-debit-3ds-verification';
            $headers = array(
                'Authorization' => 'Basic ' . $authorization,
                'Content-Type' => 'application/json'
            );

            foreach ($paymentsToVerify as $payment) {
                $donation_payment = Donation::find($payment['id']);
                
                $responseRaw = wp_remote_get($configs['api_url'] . '?reference=' . 'order' . $payment['id'], array('headers' => $headers));
                $response = json_decode(wp_remote_retrieve_body($responseRaw));
    
                if ('enabled' === $configs['debug']) {
                    // Realizar o logging da informação relevante
                    $rawHeaders = wp_remote_retrieve_headers($responseRaw);
                    $logMessage = 'VERIFY PAYMENT - [Raw header]: ' . var_export($rawHeaders, true) . \PHP_EOL .
                                ' [INFO]: ' . var_export($payment, true) . \PHP_EOL .
                                ' [BODY]: ' . var_export($response, true);

                    LknPaymentEredeForGivewpHelper::log($logMessage, $logname);
                }
    
                if ($response && isset($response->authorization) && isset($response->authorization->returnCode)) {
                    $returnCode = $response->authorization->returnCode;

                } elseif ($response && isset($response->returnCode)) {
                    $returnCode = $response->returnCode;

                } else {
                    $donation_payment->status = DonationStatus::FAILED();
                    $donation_payment->save();
                    continue; 
                }

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
    
            $pendingPayments = ! empty($paymentsToValidate) ? base64_encode(json_encode($paymentsToValidate)) : '';
            give_update_option('lkn_erede_debit_3ds_payments_pending', $pendingPayments);
        } else {
            give_update_option('lkn_erede_debit_3ds_payments_pending', '');
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
        require_once __DIR__ . '/plugin-updater/plugin-update-checker.php';
        $this->loader = new LknPaymentEredeForGivewpLoader();
    }

    public function define_row_meta($plugin_meta, $plugin_file) :array {
        if ( ! defined(PAYMENT_EREDE_FOR_GIVEWP_BASENAME) && ! is_plugin_active(PAYMENT_EREDE_FOR_GIVEWP_BASENAME)) {
            return $plugin_meta;
        }

        $new_meta_links['setting'] = sprintf(
            '<a href="%1$s">%2$s</a>',
            admin_url('edit.php?post_type=give_forms&page=give-settings&tab=gateways'),
            'Settings',
        );
    
        return array_merge($plugin_meta, $new_meta_links);
    }

    public function check_environment() : bool {
        // Flag to check whether deactivate plugin or not.
        $is_deactivate_plugin = false;

        // Load plugin helper functions.
        if ( ! function_exists('deactivate_plugins') || ! function_exists('is_plugin_active')) {
            require_once ABSPATH . '/wp-admin/includes/plugin.php';
        }

        if (
            defined('GIVE_VERSION')
            && version_compare(GIVE_VERSION, PAYMENT_EREDE_FOR_GIVEWP_MIN_GIVE_VERSION, '<')
        ) {
            // Min. Give. plugin version.

            // Show admin notice.
            add_action('admin_notices', array($this, 'givewp_dependency_notice'));

            $is_deactivate_plugin = true;
        }

        $is_give_active = defined('GIVE_PLUGIN_BASENAME') ? is_plugin_active(GIVE_PLUGIN_BASENAME) : false;

        if ( ! $is_give_active) {
            add_action('admin_notices', array($this, 'givewp_dependency_notice'));

            $is_deactivate_plugin = true;
        }

        // Don't let this plugin activate.
        if ($is_deactivate_plugin) {
            // Deactivate plugin.
            deactivate_plugins(PAYMENT_EREDE_FOR_GIVEWP_BASENAME);

            if (isset($_GET['activate'])) {
                unset($_GET['activate']);
            }
            return false;
        }

        return true;
    }

    public static function givewp_dependency_notice(): void {
        // Admin notice.
        $message = sprintf(
            '<div class="notice notice-error"><p><strong>%1$s</strong> %2$s <a href="%3$s" target="_blank">%4$s</a>  %5$s %6$s+ %7$s.</p></div>',
            'Activation error:',
            'You need to have',
            'https://givewp.com',
            'Give WP',
            'version',
            PAYMENT_EREDE_FOR_GIVEWP_MIN_GIVE_VERSION,
            'for the Payment Gateway E-Rede for GiveWP plugin to activate.',
        );

        echo $message;
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks(): void {
        $plugin_admin = new LknPaymentEredeForGivewpAdmin( $this->get_plugin_name(), $this->get_version() );
        $this->loader->add_action('give_init', $this, 'updater_init');

        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

        $this->loader->add_action('plugins_loaded', $this, 'check_environment', 999);
        $this->loader->add_filter('plugin_action_links_' . PAYMENT_EREDE_FOR_GIVEWP_BASENAME, $this, 'define_row_meta', 10, 2);
        $this->loader->add_action('lkn_payment_erede_cron_delete_logs', 'Lkn\PaymentEredeForGivewp\Includes\LknPaymentEredeForGivewpHelper', 'delete_old_logs', 10, 0 );
        $this->loader->add_action('lkn_payment_erede_cron_verify_payment', $this, 'verify_payment', 10, 0 );

        $this->loader->add_filter( 'give_get_settings_gateways', $plugin_admin, 'add_settings_into_section' );
        $this->loader->add_filter('give_get_sections_gateways', $plugin_admin, 'new_setting_section');
        $this->loader->add_action('give_view_donation_details_billing_after', $plugin_admin, 'add_donation_details');
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks(): void {
        $plugin_public = new LknPaymentEredeForGivewpPublic( $this->get_plugin_name(), $this->get_version() );

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
        $paymentGatewayRegister->registerGateway('Lkn\PaymentEredeForGivewp\PublicView\LknPaymentEredeForGivewpDebitGateway');
        $paymentGatewayRegister->registerGateway('Lkn\PaymentEredeForGivewp\PublicView\LknPaymentEredeForGivewpCreditGateway');
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
        if (class_exists('Lkn_Puc_Plugin_UpdateChecker')) {
            return new Lkn_Puc_Plugin_UpdateChecker(
                'https://api.linknacional.com.br/v2/u/?slug=payment-erede-for-givewp',
                PAYMENT_EREDE_FOR_GIVEWP_FILE,
                'payment-erede-for-givewp'
            );
        }
    }
}
