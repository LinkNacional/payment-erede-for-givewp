<?php

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

namespace Lknpg\PaymentEredeForGivewp\Includes;

/**
 * Fired during plugin deactivation
 *
 * @link       https://www.linknacional.com.br/wordpress/plugins/
 * @since      1.0.0
 *
 * @package    Payment_Erede_For_Givewp
 * @subpackage Payment_Erede_For_Givewp/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Payment_Erede_For_Givewp
 * @subpackage Payment_Erede_For_Givewp/includes
 * @author     Link Nacional <contato@linknacional.com>
 */
class LknpgPaymentEredeForGivewpDeactivator {
    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function deactivate(): void {
        wp_unschedule_hook( 'lknpg_payment_erede_cron_verify_payment' );
    }
}
