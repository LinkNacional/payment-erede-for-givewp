<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.linknacional.com.br/wordpress/plugins/
 * @since             1.0.0
 * @package           Payment_Erede_For_Givewp
 *
 * @wordpress-plugin
 * Plugin Name:       Payment E-Rede for GiveWP
 * Plugin URI:        https://www.linknacional.com.br/wordpress/plugins/
 * Description:       Pagamento de cartão de crédito e débito utilizando E-Rede
 * Version:           1.0.0
 * Author:            Link Nacional
 * Author URI:        https://www.linknacional.com.br/wordpress/plugins/
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       payment-erede-for-givewp
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'PAYMENT_EREDE_FOR_GIVEWP_VERSION', '1.0.0' );
define( 'PAYMENT_EREDE_FOR_GIVEWP_MIN_GIVE_VERSION', '2.31.0' );
define( 'PAYMENT_EREDE_FOR_GIVEWP_TEXT_DOMAIN', 'payment-erede-for-givewp' );
define( 'PAYMENT_EREDE_FOR_GIVEWP_BASENAME', plugin_basename(__FILE__) );
define( 'PAYMENT_EREDE_FOR_GIVEWP_LOG_DIR', __DIR__ . '/includes/logs/' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-payment-erede-for-givewp-activator.php
 */
function activate_payment_erede_for_givewp(): void {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-payment-erede-for-givewp-activator.php';
    Payment_Erede_For_Givewp_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-payment-erede-for-givewp-deactivator.php
 */
function deactivate_payment_erede_for_givewp(): void {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-payment-erede-for-givewp-deactivator.php';
    Payment_Erede_For_Givewp_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_payment_erede_for_givewp' );
register_deactivation_hook( __FILE__, 'deactivate_payment_erede_for_givewp' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-payment-erede-for-givewp.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_payment_erede_for_givewp(): void {
    $plugin = new Payment_Erede_For_Givewp();
    $plugin->run();
}
run_payment_erede_for_givewp();
