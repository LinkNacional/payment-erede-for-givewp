<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 *
 * This file may be updated more in future version of the Boilerplate; however, this is the
 * general skeleton and outline for how the file should work.
 *
 * For more information, see the following discussion:
 * https://github.com/tommcfarlin/WordPress-Plugin-Boilerplate/pull/123#issuecomment-28541913
 *
 * @link       https://www.linknacional.com.br/wordpress/plugins/
 * @since      1.0.0
 *
 * @package    Payment_Erede_For_Givewp
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

wp_unschedule_hook( 'lkn_payment_erede_cron_delete_logs' );
wp_unschedule_hook( 'lkn_payment_erede_cron_verify_payment' );

$lkn_erede_opt = give_get_settings();

$lkn_erede_opt = array_filter($lkn_erede_opt, function ($key) {
    return strpos($key, 'lkn_erede_') === 0;
}, \ARRAY_FILTER_USE_KEY);
$lkn_erede_opt = array_keys($lkn_erede_opt);

if (count($lkn_erede_opt) > 0) {
    for ($c = 0; $c < count($lkn_erede_opt); $c++) {
        give_delete_option($lkn_erede_opt[$c]);
    }
}