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
                $description = give_get_option('lkn_erede_credit_softdescription_setting_field', __('Donation', PAYMENT_EREDE_FOR_GIVEWP_TEXT_DOMAIN));
                $configs['description'] = Payment_Erede_For_Givewp_Helper::format_softdescriptor_string($description);

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
                $description = give_get_option('lkn_erede_debit_3ds_softdescription_setting_field', __('Donation', PAYMENT_EREDE_FOR_GIVEWP_TEXT_DOMAIN));
                $configs['description'] = Payment_Erede_For_Givewp_Helper::format_softdescriptor_string($description);

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

    public static function log($message, $type) :void {
        error_log($message, 3, PAYMENT_EREDE_FOR_GIVEWP_LOG_DIR . date('d.m.Y-H.i.s') . '-' . $type . '.log');
    }

    public static function delete_old_logs() :void {
        $logsPath = PAYMENT_EREDE_FOR_GIVEWP_LOG_DIR;

        foreach (scandir($logsPath) as $logFilename) {
            if ('.' !== $logFilename && '..' !== $logFilename && 'index.php' !== $logFilename) {
                $logDate = explode('-', $logFilename)[0];
                $logDate = explode('.', $logDate);
    
                $logDay = $logDate[0];
                $logMonth = $logDate[1];
                $logYear = $logDate[2];
    
                $logDate = $logYear . '-' . $logMonth . '-' . $logDay;
    
                $logDate = new DateTime($logDate);
                $now = new DateTime(date('Y-m-d'));
    
                $interval = $logDate->diff($now);
                $logAge = $interval->format('%a');
    
                if ($logAge >= 15) {
                    unlink($logsPath . '/' . $logFilename);
                }
            }
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
