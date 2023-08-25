<?php

/**
 * Helper plugin class
 *
 * This is used to define functions that are called
 * in general by this plugin
 *
 * @since      1.0.0
 * @package    Payment_Erede_For_Givewp_Helper
 * @subpackage Payment_Erede_For_Givewp_Helper/includes
 * @author     Link Nacional <contato@linknacional.com>
 */
abstract class Payment_Erede_For_Givewp_Helper {
    /**
     * Get all paymethod config options
     *
     * @since 1.0.0
     *
     * @param  string $type 
     *
     * @return array
     */
    public static function get_configs($type) :array {
        $configs = array();

        switch ($type) {
            case 'credit':
                $configs['env'] = give_get_option('lkn_erede_credit_env_setting_field', 'sandbox');
                $configs['pv'] = give_get_option('lkn_erede_credit_pv_setting_field', '');
                $configs['token'] = give_get_option('lkn_erede_credit_token_setting_field', '');
                $configs['billing_fields'] = give_get_option('lkn_erede_credit_billing_fields_setting_field', 'disabled');
                $configs['debug'] = give_get_option('lkn_erede_credit_debug_setting_field', 'disabled');

                if ('production' === $configs['env']) {
                    $configs['api_url'] = 'https://api.userede.com.br/erede/v1/transactions';
                } else {
                    $configs['api_url'] = 'https://sandbox-erede.useredecloud.com.br/v1/transactions';
                }

                break;
            case 'debit-3ds':
                $configs['env'] = give_get_option('lkn_erede_debit_3ds_env_setting_field', 'sandbox');
                $configs['pv'] = give_get_option('lkn_erede_debit_3ds_pv_setting_field', '');
                $configs['token'] = give_get_option('lkn_erede_debit_3ds_token_setting_field', '');
                $configs['billing_fields'] = give_get_option('lkn_erede_debit_3ds_billing_fields_setting_field', 'disabled');
                $configs['debug'] = give_get_option('lkn_erede_debit_3ds_debug_setting_field', 'disabled');

                if ('production' === $configs['env']) {
                    $configs['api_url'] = 'https://api.userede.com.br/erede/v1/transactions';
                } else {
                    $configs['api_url'] = 'https://sandbox-erede.useredecloud.com.br/v1/transactions';
                }

                break;
            
            default:
                # code...
                break;
        }

        return $configs;
    }

    /**
     * Get billing fields option
     *
     * @since 1.0.0
     *
     * @return string
     */
    public static function get_billing_fields_opt() :string {
        return give_get_option('lkn_erede_credit_billing_fields_setting_field', 'disabled');
    }

    public static function log($message) :void {
        error_log(PHP_EOL . date('d-m-Y') . ' - ' . $message, 3, __DIR__ . '/error.log');
    }
}
