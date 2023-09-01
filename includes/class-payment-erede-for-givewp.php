<?php

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
class Payment_Erede_For_Givewp {
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
        $this->set_locale();
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

    public function verify_payment() :bool {
        $paymentsToVerify = give_get_option('lkn_erede_debit_3ds_payments_pending', '');

        if(empty($paymentsToVerify)) {
            $paymentsToVerify = [];
        }else{
            $paymentsToVerify = json_decode(base64_decode($paymentsToVerify),true);
        }

        $paymentCounter = count($paymentsToVerify);

        if($paymentCounter > 0) {
            $configs = Payment_Erede_For_Givewp_Helper::get_configs('debit-3ds');
            $authorization = base64_encode( $configs['pv'] . ':' . $configs['token'] );
            $paymentsToValidate = [];
            $type = date('d.m.Y-H.i.s') . '-debit-3ds-verification';

            $headers = array(
                'Authorization' => 'Basic ' . $authorization,
                'Content-Type' => 'application/json'
            );

            for ($c=0; $c < $paymentCounter; $c++) {
                $responseRaw = wp_remote_get($configs['api_url'] . '?reference=' . $paymentsToVerify[$c]['id'], [
                    'headers' => $headers
                ]);

                $response = json_decode(wp_remote_retrieve_body($responseRaw));

                if($configs['debug'] === 'enabled') {
                    Payment_Erede_For_Givewp_Helper::log('VERIFY PAYMENT - [Raw header]: ' . var_export(wp_remote_retrieve_headers($responseRaw) . PHP_EOL . ' [INFO]: ' . var_export($paymentsToVerify, true), true) . PHP_EOL . ' [BODY]: ' . var_export($response, true), $type);
                }

                switch ($response->returnCode) {
                    case '00':
                        give_update_payment_status($paymentsToVerify[$c]['id'], 'publish');

                        break;
                    case '78':
                        $counter = (int) ($paymentsToVerify[$c]['count']);
                        $counter++;

                        if ($counter > 5) {
                            give_update_payment_status($paymentsToVerify[$c]['id'], 'failed');
                        }else{
                            $paymentsToValidate[] = array('id' => $paymentsToVerify[$c]['id'], 'count' => $counter);
                        }

                        break;

                    default:
                        give_update_payment_status($paymentsToVerify[$c]['id'], 'failed');

                        break;
                }
            }

            if(count($paymentsToValidate) > 0) {
                $paymentsToValidate = base64_encode(json_encode($paymentsToValidate));

                give_update_option('lkn_erede_debit_3ds_payments_pending', $paymentsToValidate);
            }else{
                give_update_option('lkn_erede_debit_3ds_payments_pending', '');
            }
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
        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path( __DIR__ ) . 'includes/class-payment-erede-for-givewp-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path( __DIR__ ) . 'includes/class-payment-erede-for-givewp-i18n.php';

        /**
         * The class responsible for defining helpers functions
         */
        require_once plugin_dir_path( __DIR__ ) . 'includes/class-payment-erede-for-givewp-helper.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path( __DIR__ ) . 'admin/class-payment-erede-for-givewp-admin.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once plugin_dir_path( __DIR__ ) . 'public/class-payment-erede-for-givewp-public.php';

        $this->loader = new Payment_Erede_For_Givewp_Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Payment_Erede_For_Givewp_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale(): void {
        $plugin_i18n = new Payment_Erede_For_Givewp_i18n();

        $this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
    }

    public function process_debit_3ds_api_payment($payment_data) : void {
        // Set the configs values
        $configs = Payment_Erede_For_Givewp_Helper::get_configs('debit-3ds');
    
        // Validate nonce.
        give_validate_nonce($payment_data['gateway_nonce'], 'give-gateway');
    
        // Make sure we don't have any left over errors present.
        give_clear_errors();

        // Any errors?
        $errors = give_get_errors();

        if ($errors) {
            give_send_back_to_checkout('?payment-mode=' . $payment_data['post_data']['give-gateway'] . '&error-msg=Erro interno no processamento do pagamento, contate o suporte');

            exit;
        }

        // Setup the payment details.
        $payment_array = array(
            'price' => $payment_data['price'],
            'give_form_title' => $payment_data['post_data']['give-form-title'],
            'give_form_id' => (int) ($payment_data['post_data']['give-form-id']),
            'give_price_id' => isset($payment_data['post_data']['give-price-id']) ? $payment_data['post_data']['give-price-id'] : '',
            'date' => $payment_data['date'],
            'user_email' => $payment_data['user_email'],
            'purchase_key' => $payment_data['purchase_key'],
            'currency' => give_get_currency($payment_data['post_data']['give-form-id'], $payment_data),
            'user_info' => $payment_data['user_info'],
            'status' => 'pending',
            'gateway' => 'lkn_erede_debit_3ds',
        );

        $headers = array(
            'Authorization' => 'Basic ' . base64_encode( $configs['pv'] . ':' . $configs['token'] ),
            'Content-Type' => 'application/json'
        );

        $currencyCode = give_get_currency($payment_data['post_data']['give-form-id'], $payment_data);
        $payment_id = give_insert_payment($payment_array);
        $amount = number_format($payment_data['price'], 2, '', '');
        $userAgent = $payment_data['post_data']['lkn_erede_debit_3ds_user_agent'];
        $colorDepth = $payment_data['post_data']['lkn_erede_debit_3ds_device_color'];
        $lang = $payment_data['post_data']['lkn_erede_debit_3ds_lang'];
        $height = $payment_data['post_data']['lkn_erede_debit_3ds_device_height'];
        $width = $payment_data['post_data']['lkn_erede_debit_3ds_device_width'];
        $timezone = $payment_data['post_data']['lkn_erede_debit_3ds_timezone'];
        $type = date('d.m.Y-H.i.s') . '-debit-3ds';

        $card = array();
        $splitDate = explode('/', $payment_data['post_data']['lkn_erede_debit_3ds_card_expiry']);
        $card['expMonth'] = preg_replace('/\D/', '', sanitize_text_field($splitDate[0]));
        $card['expYear'] = preg_replace('/\D/', '', sanitize_text_field($splitDate[1]));
        $card['number'] = preg_replace('/\D/', '', sanitize_text_field($payment_data['post_data']['lkn_erede_debit_3ds_card_number']));
        $card['cvv'] = preg_replace('/\D/', '', sanitize_text_field($payment_data['post_data']['lkn_erede_debit_3ds_card_cvc']));
        $card['name'] = sanitize_text_field($payment_data['post_data']['lkn_erede_debit_3ds_card_name']);

        $body = array(
            'capture' => true,
            'kind' => 'debit',
            'reference' => $payment_id,
            'amount' => $amount,
            'cardholderName' => $card['name'],
            'cardNumber' => $card['number'],
            'expirationMonth' => $card['expMonth'],
            'expirationYear' => $card['expYear'],
            'securityCode' => $card['cvv'],
            'softDescriptor' => $configs['description'],
            'threeDSecure' => array(
                'embedded' => true,
                'onFailure' => 'decline',
                'userAgent' => $userAgent,
                'device' => array(
                    'colorDepth' => $colorDepth,
                    'deviceType3ds' => 'BROWSER',
                    'javaEnabled' => false,
                    'language' => $lang,
                    'screenHeight' => $height,
                    'screenWidth' => $width,
                    'timeZoneOffset' => $timezone
                )
            ),
            'urls' => array(
                array(
                    'kind' => 'threeDSecureSuccess',
                    'url' => give_get_success_page_uri()
                ),
                array(
                    'kind' => 'threeDSecureFailure',
                    'url' => give_get_failed_transaction_uri()
                )
            )
        );

        $body = apply_filters('lkn_erede_debit_3ds_body', $body, $currencyCode);

        $response = wp_remote_post($configs['api_url'], array(
            'headers' => $headers,
            'body' => json_encode($body)
        ));

        if ('enabled' === $configs['debug']) {
            Payment_Erede_For_Givewp_Helper::log('[Raw header]: ' . var_export(wp_remote_retrieve_headers($response), true) . PHP_EOL . ' [Raw body]: ' . var_export(wp_remote_retrieve_body($response), true), $type);
        }

        $response = json_decode(wp_remote_retrieve_body($response));

        $arrMetaData = [
            'status' => $response->returnCode,
            'message' => $response->returnMessage,
            'transaction_id' => $response->tid ?? '0'
        ];

        if('enabled' === $configs['debug']){
            $arrMetaData['log'] = $type;
        }

        switch ($response->returnCode) {
            case '200':
                give_update_payment_status($payment_id, 'publish');

                give_send_to_success_page();

                exit;
            case '220':
                $paymentsToVerify = give_get_option('lkn_erede_debit_3ds_payments_pending', '');

                if(empty($paymentsToVerify)) {
                    $paymentsToVerify = [];
                }else{
                    $paymentsToVerify = json_decode(base64_decode($paymentsToVerify),true);
                }

                $paymentsToVerify[] = ['id' => $payment_id, 'count' => '0'];
                $paymentsToVerify = base64_encode(json_encode($paymentsToVerify));
                give_update_option('lkn_erede_debit_3ds_payments_pending', $paymentsToVerify);

                wp_redirect($response->threeDSecure->url);

                exit;

            default:
                give_update_payment_status($payment_id, 'failed');

                wp_redirect(give_get_failed_transaction_uri());

                exit;
        }
    }

    public function process_credit_api_payment($payment_data): void {
        // Set the configs values
        $configs = Payment_Erede_For_Givewp_Helper::get_configs('credit');
    
        // Validate nonce.
        give_validate_nonce($payment_data['gateway_nonce'], 'give-gateway');
    
        // Make sure we don't have any left over errors present.
        give_clear_errors();

        // Any errors?
        $errors = give_get_errors();

        if ($errors) {
            give_send_back_to_checkout('?payment-mode=' . $payment_data['post_data']['give-gateway'] . '&error-msg=Erro interno no processamento do pagamento, contate o suporte');

            exit;
        }

        // Setup the payment details.
        $payment_array = array(
            'price' => $payment_data['price'],
            'give_form_title' => $payment_data['post_data']['give-form-title'],
            'give_form_id' => (int) ($payment_data['post_data']['give-form-id']),
            'give_price_id' => isset($payment_data['post_data']['give-price-id']) ? $payment_data['post_data']['give-price-id'] : '',
            'date' => $payment_data['date'],
            'user_email' => $payment_data['user_email'],
            'purchase_key' => $payment_data['purchase_key'],
            'currency' => give_get_currency($payment_data['post_data']['give-form-id'], $payment_data),
            'user_info' => $payment_data['user_info'],
            'status' => 'pending',
            'gateway' => 'lkn_erede_credit',
        );

        $headers = array(
            'Authorization' => 'Basic ' . base64_encode( $configs['pv'] . ':' . $configs['token'] ),
            'Content-Type' => 'application/json'
        );

        $currencyCode = give_get_currency($payment_data['post_data']['give-form-id'], $payment_data);
        $payment_id = give_insert_payment($payment_array);
        $amount = number_format($payment_data['price'], 2, '', '');
        $type = date('d.m.Y-H.i.s') . '-credit';

        $card = array();
        $splitDate = explode('/', $payment_data['post_data']['lkn_erede_credit_card_expiry']);
        $card['expMonth'] = preg_replace('/\D/', '', sanitize_text_field($splitDate[0]));
        $card['expYear'] = preg_replace('/\D/', '', sanitize_text_field($splitDate[1]));
        $card['number'] = preg_replace('/\D/', '', sanitize_text_field($payment_data['post_data']['lkn_erede_credit_card_number']));
        $card['cvv'] = preg_replace('/\D/', '', sanitize_text_field($payment_data['post_data']['lkn_erede_credit_card_cvc']));
        $card['name'] = sanitize_text_field($payment_data['post_data']['lkn_erede_credit_card_name']);

        $body = array(
            'capture' => true,
            'kind' => 'credit',
            'reference' => $payment_id,
            'amount' => $amount,
            'cardholderName' => $card['name'],
            'cardNumber' => $card['number'],
            'expirationMonth' => $card['expMonth'],
            'expirationYear' => $card['expYear'],
            'securityCode' => $card['cvv'],
            'softDescriptor' => $configs['description'],
            'subscription' => false,
            'origin' => 1,
            'distributorAffiliation' => 0,
            'storageCard' => '0',
            'transactionCredentials' => array(
                'credentialId' => '01'
            )
        );

        $body = apply_filters('lkn_erede_credit_body', $body, $currencyCode);

        $response = wp_remote_post($configs['api_url'], array(
            'headers' => $headers,
            'body' => json_encode($body)
        ));

        if ('enabled' === $configs['debug']) {
            Payment_Erede_For_Givewp_Helper::log('[Raw header]: ' . var_export(wp_remote_retrieve_headers($response), true) . PHP_EOL . ' [Raw body]: ' . var_export(wp_remote_retrieve_body($response), true), $type);
        }

        $response = json_decode(wp_remote_retrieve_body($response));

        $arrMetaData = [
            'status' => $response->returnCode,
            'message' => $response->returnMessage,
            'transaction_id' => $response->tid
        ];

        if('enabled' === $configs['debug']){
            $arrMetaData['log'] = $type;
        }

        switch ($response->returnCode) {
            case '00':
                give_update_payment_status($payment_id, 'publish');

                give_send_to_success_page();

                exit;

            default:
                give_update_payment_status($payment_id, 'failed');

                wp_redirect(give_get_failed_transaction_uri());

                exit;
        }
    }

    public function define_row_meta($plugin_meta, $plugin_file) :array {
        if ( ! defined(PAYMENT_EREDE_FOR_GIVEWP_BASENAME) && ! is_plugin_active(PAYMENT_EREDE_FOR_GIVEWP_BASENAME)) {
            return $plugin_meta;
        }

        $new_meta_links['setting'] = sprintf(
            '<a href="%1$s">%2$s</a>',
            admin_url('edit.php?post_type=give_forms&page=give-settings&tab=gateways'),
            __('Settings', 'give')
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
            add_action('admin_notices', array('Payment_Erede_For_Givewp', 'givewp_dependency_notice'));

            $is_deactivate_plugin = true;
        }

        $is_give_active = defined('GIVE_PLUGIN_BASENAME') ? is_plugin_active(GIVE_PLUGIN_BASENAME) : false;

        if ( ! $is_give_active) {
            add_action('admin_notices', array('Payment_Erede_For_Givewp', 'givewp_dependency_notice'));

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
            __('Activation error:', ''),
            __('You need to have', ''),
            'https://givewp.com',
            __('Give WP', ''),
            __('version', ''),
            PAYMENT_EREDE_FOR_GIVEWP_MIN_GIVE_VERSION,
            __('for the Payment Gateway E-Rede for GiveWP plugin to activate.', '')
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
        $plugin_admin = new Payment_Erede_For_Givewp_Admin( $this->get_plugin_name(), $this->get_version() );

        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
        $this->loader->add_filter( 'give_payment_gateways', $plugin_admin, 'register_gateway' );

        $this->loader->add_action('plugins_loaded', $this, 'check_environment', 999);
        $this->loader->add_filter('plugin_action_links_' . PAYMENT_EREDE_FOR_GIVEWP_BASENAME, $this, 'define_row_meta', 10, 2);
        $this->loader->add_action('lkn_payment_erede_cron_delete_logs', Payment_Erede_For_Givewp_Helper::class, 'delete_old_logs', 10, 0 );
        $this->loader->add_action('lkn_payment_erede_cron_verify_payment', $this, 'verify_payment', 10, 0 );

        $this->loader->add_filter( 'give_get_sections_gateways', $plugin_admin, 'add_new_setting_section' );
        $this->loader->add_filter( 'give_get_settings_gateways', $plugin_admin, 'add_settings_into_section' );
        $this->loader->add_action('give_view_donation_details_billing_after', $plugin_admin, 'add_donation_details');

        $this->loader->add_action( 'give_gateway_lkn_erede_credit', $this, 'process_credit_api_payment');
        $this->loader->add_action( 'give_gateway_lkn_erede_debit_3ds', $this, 'process_debit_3ds_api_payment');
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks(): void {
        $plugin_public = new Payment_Erede_For_Givewp_Public( $this->get_plugin_name(), $this->get_version() );

        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
        $this->loader->add_action('give_lkn_erede_credit_cc_form', $plugin_public, 'payment_form_credit', 10, 3);
        $this->loader->add_action('give_lkn_erede_debit_3ds_cc_form', $plugin_public, 'payment_form_debit_3ds', 10, 3);
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
}
