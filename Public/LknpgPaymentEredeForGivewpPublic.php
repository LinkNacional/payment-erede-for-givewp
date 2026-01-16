<?php

namespace Lknpg\PaymentEredeForGivewp\PublicView;

use Give\Donations\Models\Donation;
use Give\Donations\Models\DonationNote;
use Give\Donations\ValueObjects\DonationStatus;
use Lknpg\PaymentEredeForGivewp\Includes\LknpgPaymentEredeForGivewpHelper;
use WP_REST_Request;
use WP_REST_Response;
use Exception;

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.linknacional.com.br/wordpress/plugins/
 * @since      1.0.0
 *
 * @package    Payment_Erede_For_Givewp
 * @subpackage Payment_Erede_For_Givewp/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Payment_Erede_For_Givewp
 * @subpackage Payment_Erede_For_Givewp/public
 * @author     Link Nacional <contato@linknacional.com>
 */
class LknpgPaymentEredeForGivewpPublic {
    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of the plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name . '_public';
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles(): void {
        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Payment_Erede_For_Givewp_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Payment_Erede_For_Givewp_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/lknpgPaymentEredeForGivewpPublic.css', array(), $this->version, 'all' );
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts(): void {
        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Payment_Erede_For_Givewp_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Payment_Erede_For_Givewp_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
    }

    /**
     * Register REST API routes
     * 
     * @since 1.0.0
     */
    public function register_api_routes(): void {
        // Rota para sucesso
        register_rest_route('lkn-erede/v1', '/success/', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_success_callback'),
            'permission_callback' => '__return_true'
        ));
        
        // Rota para falha
        register_rest_route('lkn-erede/v1', '/failure/', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_failure_callback'),
            'permission_callback' => '__return_true'
        ));
    }

    /**
     * Handle success callback from E-Rede
     * 
     * @since 1.0.0
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function handle_success_callback(WP_REST_Request $request): WP_REST_Response {
        try {
            $all_params = $request->get_params();
            
            // Extrair o payment_id do reference primeiro (formato: orderXXX)
            $reference = isset($all_params['reference']) ? $all_params['reference'] : '';
            if (empty($reference) || strpos($reference, 'order') !== 0) {
                wp_redirect(give_get_failed_transaction_uri());
                exit;
            }
            
            $payment_id = (int) str_replace('order', '', $reference);
            
            if (!$payment_id) {
                wp_redirect(give_get_failed_transaction_uri());
                exit;
            }

            // Obter informações da doação para determinar o gateway
            $donation = Donation::find($payment_id);
            
            if (!$donation) {
                wp_redirect(give_get_failed_transaction_uri());
                exit;
            }

            // Determinar o gateway usado baseado no meta da doação
            $gateway_id = give_get_payment_gateway($payment_id);
            $gateway_type = 'credit'; // padrão
            
            if ($gateway_id === 'lkn_erede_debit_3ds') {
                $gateway_type = 'debit-3ds';
            } elseif ($gateway_id === 'lkn_erede_credit') {
                $gateway_type = 'credit';
            }
            
            // Obter configurações do gateway correto
            $configs = LknpgPaymentEredeForGivewpHelper::get_configs($gateway_type);
            
            // Verificar se o TID foi fornecido
            $tid = $all_params['tid'] ?? '';
            if (empty($tid)) {
                wp_redirect(give_get_failed_transaction_uri());
                exit;
            }
            
            // Verificar se o TID é válido e aprovado na E-Rede
            $is_valid_payment = $this->verify_tid_with_erede($tid, $configs);
            
            if (!$is_valid_payment) {
                wp_redirect(give_get_failed_transaction_uri());
                exit;
            }
            
            // Se chegou até aqui, o pagamento é válido - continuar com a lógica original
            return $this->process_callback($request, 'success');
            
        } catch (Exception $e) {
            // Em caso de erro, redirecionar para falha
            wp_redirect(give_get_failed_transaction_uri());
            exit;
        }
    }

    /**
     * Handle failure callback from E-Rede
     * 
     * @since 1.0.0
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function handle_failure_callback(WP_REST_Request $request): WP_REST_Response {
        // Para falhas, apenas redirecionar para a página de falha do GiveWP
        // Não altera status de doação nem nada mais
        wp_redirect(give_get_failed_transaction_uri());
        exit;
    }

    /**
     * Process payment callback from E-Rede
     * 
     * @since 1.0.0
     * @param WP_REST_Request $request
     * @param string $status
     * @return WP_REST_Response
     */
    private function process_callback(WP_REST_Request $request, string $status): WP_REST_Response {
        try {
            // Obter todos os parâmetros da resposta da E-Rede
            $all_params = $request->get_params();
            
            // Extrair o payment_id do reference (já validado anteriormente)
            $reference = $all_params['reference'];
            $payment_id = (int) str_replace('order', '', $reference);
            
            // Obter informações da doação (já validada anteriormente)
            $donation = Donation::find($payment_id);
            
            // Determinar o gateway usado para obter configurações corretas
            $gateway_id = give_get_payment_gateway($payment_id);
            $gateway_type = 'credit'; // padrão
            
            if ($gateway_id === 'lkn_erede_debit_3ds') {
                $gateway_type = 'debit-3ds';
            } elseif ($gateway_id === 'lkn_erede_credit') {
                $gateway_type = 'credit';
            }
            
            // Obter configurações do gateway correto
            $configs = LknpgPaymentEredeForGivewpHelper::get_configs($gateway_type);
            
            if ('enabled' === $configs['debug']) {
                LknpgPaymentEredeForGivewpHelper::regLog(
                    'info',
                    'eredeCallback',
                    'E-Rede callback received',
                    array(
                        'status' => $status,
                        'params' => $all_params,
                        'gateway_type' => $gateway_type,
                        'headers' => $request->get_headers()
                    ),
                    true
                );
            }

            // Consultar status do pagamento na E-Rede para confirmar
            $payment_response = $this->check_payment_status($payment_id, $configs);
            
            $redirect_url = '';
            
            if ('success' === $status && $payment_response['success']) {
                // Atualizar status da doação para completa
                $donation->status = DonationStatus::COMPLETE();
                $donation->save();
                
                $redirect_url = give_get_success_page_uri() . '?donation_id=' . $payment_id;
                
                // Log de sucesso
                if ('enabled' === $configs['debug']) {
                    LknpgPaymentEredeForGivewpHelper::regLog(
                        'info',
                        'paymentSuccess',
                        'Payment completed successfully',
                        array(
                            'payment_id' => $payment_id,
                            'reference' => $reference,
                            'payment_response' => $payment_response
                        ),
                        true
                    );
                }
                
            } elseif ('failure' === $status || !$payment_response['success']) {
                // Atualizar status da doação para falha
                $donation->status = DonationStatus::FAILED();
                $donation->save();
                
                // Adicionar nota de falha
                DonationNote::create(array(
                    'donationId' => $donation->id,
                    'content' => sprintf(
                        esc_html('Falha na doação via E-Rede. Razão: %s'), 
                        $payment_response['message'] ?? 'Status de falha recebido'
                    )
                ));
                
                $redirect_url = give_get_failed_transaction_uri();
                
                // Log de falha
                if ('enabled' === $configs['debug']) {
                    LknpgPaymentEredeForGivewpHelper::regLog(
                        'warning',
                        'paymentFailure',
                        'Payment failed',
                        array(
                            'payment_id' => $payment_id,
                            'reference' => $reference,
                            'payment_response' => $payment_response,
                            'status' => $status
                        ),
                        true
                    );
                }
            }

            // Redirecionar o usuário
            if (!empty($redirect_url)) {
                wp_redirect($redirect_url);
                exit;
            }
            
            // Se chegou aqui, algo deu errado
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'No redirect URL determined'
            ), 500);

        } catch (Exception $e) {
            // Log do erro se debug estiver ativo
            if (isset($configs) && 'enabled' === $configs['debug']) {
                LknpgPaymentEredeForGivewpHelper::regLog(
                    'error',
                    'callbackError',
                    'Error processing callback',
                    array(
                        'status' => $status ?? 'unknown',
                        'error' => $e->getMessage(),
                        'params' => $all_params ?? array()
                    ),
                    true
                );
            }

            return new WP_REST_Response(array(
                'success' => false,
                'message' => $e->getMessage()
            ), 500);
        }
    }

    /**
     * Check payment status with E-Rede API
     * 
     * @since 1.0.0
     * @param int $payment_id
     * @param array $configs
     * @return array
     */
    private function check_payment_status($payment_id, $configs): array {
        try {
            // Obter token de acesso (similar ao código do gateway)
            $cached_token_data = get_option('lkn_erede_token_cache', array());
            $current_time = time();
            $access_token = '';
            
            // Verificar se precisa obter novo token
            if (empty($cached_token_data) || !isset($cached_token_data['token']) || ($current_time - $cached_token_data['timestamp']) >= 1200) {
                // Obter novo token
                $token_headers = array(
                    'Authorization' => 'Basic ' . base64_encode($configs['pv'] . ':' . $configs['token']),
                    'Content-Type' => 'application/x-www-form-urlencoded'
                );

                $token_body = array('grant_type' => 'client_credentials');
                
                $token_response = wp_remote_post($configs['api_token_url'], array(
                    'headers' => $token_headers,
                    'body' => $token_body
                ));

                if (is_wp_error($token_response)) {
                    throw new Exception('Erro ao obter token da API: ' . $token_response->get_error_message());
                }

                $token_data = json_decode(wp_remote_retrieve_body($token_response), true);
                
                if (!isset($token_data['access_token'])) {
                    throw new Exception('Token inválido ou credenciais incorretas');
                }
                
                $access_token = $token_data['access_token'];
                
                // Atualizar cache
                update_option('lkn_erede_token_cache', array(
                    'token' => $access_token,
                    'timestamp' => $current_time,
                    'credentials_hash' => md5($configs['pv'] . $configs['token'] . $configs['env'])
                ));
            } else {
                $access_token = $cached_token_data['token'];
            }

            // Consultar status do pagamento
            $headers = array(
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type' => 'application/json'
            );

            $response = wp_remote_get($configs['api_url'] . '?reference=order' . $payment_id, array(
                'headers' => $headers
            ));

            if (is_wp_error($response)) {
                throw new Exception('Erro ao consultar status do pagamento: ' . $response->get_error_message());
            }

            $response_data = json_decode(wp_remote_retrieve_body($response), true);
            
            if (!$response_data) {
                throw new Exception('Resposta inválida da API E-Rede');
            }

            $return_code = $response_data['returnCode'] ?? $response_data['authorization']['returnCode'] ?? '500';
            
            return array(
                'success' => $return_code === '00',
                'return_code' => $return_code,
                'message' => $response_data['returnMessage'] ?? $response_data['authorization']['returnMessage'] ?? 'Unknown status',
                'transaction_id' => $response_data['tid'] ?? $response_data['authorization']['tid'] ?? '0',
                'full_response' => $response_data
            );

        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => $e->getMessage(),
                'return_code' => '500'
            );
        }
    }

    /**
     * Verify TID with E-Rede API to check if payment is valid and approved
     * 
     * @since 1.0.0
     * @param string $tid
     * @param array $configs
     * @return bool
     */
    private function verify_tid_with_erede(string $tid, array $configs): bool {
        try {
            // Obter token de acesso
            $cached_token_data = get_option('lkn_erede_token_cache', array());
            $current_time = time();
            $access_token = '';
            
            // Verificar se precisa obter novo token
            if (empty($cached_token_data) || !isset($cached_token_data['token']) || ($current_time - $cached_token_data['timestamp']) >= 1200) {
                // Obter novo token
                $token_headers = array(
                    'Authorization' => 'Basic ' . base64_encode($configs['pv'] . ':' . $configs['token']),
                    'Content-Type' => 'application/x-www-form-urlencoded'
                );

                $token_body = array('grant_type' => 'client_credentials');
                
                $token_response = wp_remote_post($configs['api_token_url'], array(
                    'headers' => $token_headers,
                    'body' => $token_body
                ));

                if (is_wp_error($token_response)) {
                    return false;
                }

                $token_data = json_decode(wp_remote_retrieve_body($token_response), true);
                
                if (!isset($token_data['access_token'])) {
                    return false;
                }
                
                $access_token = $token_data['access_token'];
                
                // Atualizar cache
                update_option('lkn_erede_token_cache', array(
                    'token' => $access_token,
                    'timestamp' => $current_time,
                    'credentials_hash' => md5($configs['pv'] . $configs['token'] . $configs['env'])
                ));
            } else {
                $access_token = $cached_token_data['token'];
            }

            // Consultar pagamento pelo TID
            $headers = array(
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type' => 'application/json'
            );

            $response = wp_remote_get($configs['api_url'] . '/' . $tid, array(
                'headers' => $headers
            ));

            if (is_wp_error($response)) {
                return false;
            }

            $response_data = json_decode(wp_remote_retrieve_body($response), true);
            
            if (!$response_data) {
                return false;
            }

            // Log da verificação se debug estiver ativo
            if ('enabled' === $configs['debug']) {
                LknpgPaymentEredeForGivewpHelper::regLog(
                    'info',
                    'tidVerification',
                    'TID verification response',
                    array(
                        'tid' => $tid,
                        'response' => $response_data
                    ),
                    true
                );
            }

            // Verificar se o pagamento foi aprovado
            $return_code = $response_data['returnCode'] ?? $response_data['authorization']['returnCode'] ?? '500';
            
            // Código '00' indica aprovação na E-Rede
            return $return_code === '00';

        } catch (Exception $e) {
            // Log do erro se debug estiver ativo
            if ('enabled' === $configs['debug']) {
                LknpgPaymentEredeForGivewpHelper::regLog(
                    'error',
                    'tidVerificationError',
                    'Error verifying TID',
                    array(
                        'tid' => $tid,
                        'error' => $e->getMessage()
                    ),
                    true
                );
            }
            
            return false;
        }
    }
}
