<?php

use Lknpg\PaymentEredeForGivewp\Includes\LknpgPaymentEredeForGivewpActivator;
use Lknpg\PaymentEredeForGivewp\Includes\LknpgPaymentEredeForGivewpDeactivator;
use Lknpg\PaymentEredeForGivewp\Includes\LknpgPaymentEredeForGivewp;

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
 * Plugin Name:       Payment Gateway E-Rede for GiveWP
 * Plugin URI:        https://www.linknacional.com.br/wordpress/plugins/
 * Description:       Credit and debit card payment using E-Rede
 * Version:           2.0.3
 * Author:            Link Nacional
 * Author URI:        https://www.linknacional.com.br/
 * License:           GPL-3.0+
 * Requires Plugins: give
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       payment-gateway-e-rede-for-givewp
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Includes/plugin-updater/plugin-update-checker.php';

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'PAYMENT_EREDE_FOR_GIVEWP_VERSION', '2.0.3' );

define( 'PAYMENT_EREDE_FOR_GIVEWP_MIN_GIVE_VERSION', '2.31.0' );

define( 'PAYMENT_EREDE_FOR_GIVEWP_BASENAME', plugin_basename(__FILE__));

define( 'PAYMENT_EREDE_FOR_GIVEWP_FILE', __FILE__);

define( 'PAYMENT_EREDE_FOR_GIVEWP_LOG_DIR', __DIR__ . '/includes/logs/' );

define('PAYMENT_EREDE_FOR_GIVEWP_URL', plugin_dir_url(__FILE__));

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-payment-erede-for-givewp-activator.php
 */
function payment_erede_for_givewp_activate(): void {
    LknpgPaymentEredeForGivewpActivator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-payment-erede-for-givewp-deactivator.php
 */
function payment_erede_for_givewp_deactivate(): void {
    LknpgPaymentEredeForGivewpDeactivator::deactivate();
}

register_activation_hook( __FILE__, 'payment_erede_for_givewp_activate' );
register_deactivation_hook( __FILE__, 'payment_erede_for_givewp_deactivate' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function payment_erede_for_givewp_run(): void {
    $plugin = new LknpgPaymentEredeForGivewp();
    $plugin->run();
}
payment_erede_for_givewp_run();
