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

    public function process_api_payment($payment_data): void {
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

        $payment_id = give_insert_payment($payment_array);
        $amount = number_format($payment_data['price'], 2, '', '');

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

        $response = wp_remote_post($configs['api_url'], array(
            'headers' => $headers,
            'body' => json_encode($body)
        ));

        Payment_Erede_For_Givewp_Helper::log('[Raw Response]: ' . var_export($response, true));

        $response = json_decode(wp_remote_retrieve_body($response));

        Payment_Erede_For_Givewp_Helper::log('[Response decoded]: ' . var_export($response, true));

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
        $this->loader->add_filter( 'give_get_sections_gateways', $plugin_admin, 'add_new_setting_section' );
        $this->loader->add_filter( 'give_get_settings_gateways', $plugin_admin, 'add_settings_into_section' );
        $this->loader->add_filter( 'give_payment_gateways', $plugin_admin, 'register_gateway' );
        $this->loader->add_action( 'give_gateway_lkn_erede_credit', $this, 'process_api_payment');
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
