<?php

namespace Lkn\PaymentEredeForGivewp\Includes;
use Give\Log\LogFactory;
use Datetime;

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
abstract class LknPaymentEredeForGivewpHelper {
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
                $description = give_get_option('lkn_erede_credit_softdescription_setting_field', 'Doação');
                $configs['description'] = LknPaymentEredeForGivewpHelper::format_softdescriptor_string($description);
                $configs['withoutAuth3DS'] = give_get_option('lkn_erede_credit_transaction_without_authentication');
                $configs['withoutDescription'] = give_get_option('lkn_erede_credit_enable_transaction_without_description');

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
                $description = give_get_option('lkn_erede_debit_3ds_softdescription_setting_field', 'Doação');
                $configs['description'] = LknPaymentEredeForGivewpHelper::format_softdescriptor_string($description);
                $configs['withoutDescription'] = give_get_option('lkn_erede_debit_3ds_enable_transaction_without_description');

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

    public static function regLog($logType, $category, $description, $data, $forceLog = false): void {
        if (give_get_option('lkn_getnet_debug') == 'enabled' || $forceLog) {
            $logFactory = new LogFactory();
            $log = $logFactory->make(
                $logType,
                $description,
                $category,
                'Give Getnet Payment',
                $data
            );
            $log->save();
        }
    }

    public static function format_softdescriptor_string($str) :string {
        $str = preg_replace('/[áàãâä]/ui', 'a', $str);
        $str = preg_replace('/[éèêë]/ui', 'e', $str);
        $str = preg_replace('/[íìîï]/ui', 'i', $str);
        $str = preg_replace('/[óòõôö]/ui', 'o', $str);
        $str = preg_replace('/[úùûü]/ui', 'u', $str);
        $str = preg_replace('/[ç]/ui', 'c', $str);
        $str = preg_replace('/[^a-zA-Z0-9_]/', '', $str);

        return $str;
    }
}
