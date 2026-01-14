<?php

namespace Lknpg\PaymentEredeForGivewp\PublicView;

use Give\Donations\Models\Donation;
use Give\Donations\Models\DonationNote;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\PaymentGateways\Commands\GatewayCommand;
use Give\Framework\PaymentGateways\Commands\PaymentComplete;
use Give\Framework\PaymentGateways\Commands\PaymentRefunded;
use Give\Framework\PaymentGateways\Commands\RedirectOffsite;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;
use Give\Framework\PaymentGateways\PaymentGateway;
use Lknpg\PaymentEredeForGivewp\Includes\LknpgPaymentEredeForGivewpHelper;

/**
 * @inheritDoc
 */
class LknpgPaymentEredeForGivewpCreditGateway extends PaymentGateway
{
    /**
     * @inheritDoc
     */
    public static function id(): string
    {
        return 'lkn_erede_credit';
    }

    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return self::id();
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return __('E-Rede API - Credit Card', 'payment-gateway-e-rede-for-givewp');
    }

    /**s
     * @inheritDoc
     */
    public function getPaymentMethodLabel(): string
    {
        return __('E-Rede - Credit Card', 'payment-gateway-e-rede-for-givewp');
    }

    /**
     * @inheritDoc
     */
    public function getLegacyFormFieldMarkup(int $formId, array $args): string
    {
        return $this->credit_card_form($formId, $args);
    }

    /**
     * @inheritDoc
     */
    public function createPayment(Donation $donation, $gatewayData): GatewayCommand
    {
        try {
            // Set the configs values
            $configs = LknpgPaymentEredeForGivewpHelper::get_configs('credit');
            $logname = gmdate('d.m.Y-H.i.s') . '-credit';
            $withoutAuth3DS = $configs['withoutAuth3DS'];

            $donation->firstName = sanitize_text_field($donation->firstName);
            $donation->lastName = sanitize_text_field($donation->lastName);
            $donation->email = sanitize_email($donation->email);

            // Donation informations.
            $donPrice = $donation->amount->formatToDecimal();

            $cardNum = preg_replace('/\D/', '', sanitize_text_field($gatewayData['paymentCardNum']));
            $CardCVC = $gatewayData['paymentCardCVC'];
            $CardName = sanitize_text_field($gatewayData['paymentCardName']);
            $CardExp = $gatewayData['paymentCardExp'];

            // 3DS.
            $userAgent = $gatewayData['paymentUserAgent'];
            $colorDepth = $gatewayData['paymentColorDepth'];
            $lang = $gatewayData['paymentLanguage'];
            $height = $gatewayData['paymentHeight'];
            $width = $gatewayData['paymentWidth'];
            $timezone = $gatewayData['paymentTimezoneOffset'];

            //Separando mes e ano
            $expDate = explode('/', $CardExp);
            $cardExpiryMonth = trim($expDate[0]);
            $cardExpiryYear = trim($expDate[1]);

            // Verificar se precisa obter novo token da API E-Rede
            $cached_token_data = get_option('lkn_erede_token_cache', array());
            $current_time = time();
            $token_expired = false;
            $access_token = '';

            // Verificar se existe cache e se já passaram 20 minutos (1200 segundos)
            if (empty($cached_token_data) || !isset($cached_token_data['timestamp']) || !isset($cached_token_data['token'])) {
                $token_expired = true;
            } else {
                $time_diff = $current_time - $cached_token_data['timestamp'];
                if ($time_diff >= 1200) { // 20 minutos = 1200 segundos
                    $token_expired = true;
                } else {
                    $access_token = $cached_token_data['token'];
                }
            }

            // Fazer requisição do token apenas se necessário
            if ($token_expired) {
                $token_headers = array(
                    'Authorization' => 'Basic ' . base64_encode($configs['pv'] . ':' . $configs['token']),
                    'Content-Type' => 'application/x-www-form-urlencoded'
                );

                $token_body = array(
                    'grant_type' => 'client_credentials'
                );

                $token_response = wp_remote_post($configs['api_token_url'], array(
                    'headers' => $token_headers,
                    'body' => $token_body
                ));

                // Verificar se a requisição do token foi bem-sucedida
                if (is_wp_error($token_response)) {
                    $errorMessage = 'Erro ao obter token da API: ' . $token_response->get_error_message();
                    
                    if ('enabled' === $configs['debug']) {
                        LknpgPaymentEredeForGivewpHelper::regLog(
                            'error',
                            'tokenRequest',
                            $errorMessage,
                            array(
                                'url' => $configs['api_token_url'],
                                'headers' => $token_headers,
                                'body' => $token_body
                            ),
                            true
                        );
                    }
                    
                    throw new PaymentGatewayException($errorMessage);
                }

                $token_response_body = json_decode(wp_remote_retrieve_body($token_response), true);
                $access_token = isset($token_response_body['access_token']) ? $token_response_body['access_token'] : '';

                // Armazenar o token com timestamp no cache
                $token_cache_data = array(
                    'token' => $access_token,
                    'timestamp' => $current_time,
                    'full_response' => $token_response_body
                );
                
                update_option('lkn_erede_token_cache', $token_cache_data);
            }

            $headers = array(
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type' => 'application/json'
            );

            $currencyCode = give_get_currency($donation->formId, $donation);

            $payment_id = $donation->id;
            $amount = $donPrice;
            $amount = number_format($amount, 2, '', '');
            $nonce = wp_create_nonce('lknNonceEredeForGivewp');

            // Construir a URL com parâmetros
            $redirect_url_sucess = add_query_arg(
                array(
                    'doacao_id' => $payment_id,
                    'status' => 'success',
                    'nonce' => $nonce
                ),
                home_url()
            );

            $redirect_url_fail = add_query_arg(
                array(
                    'doacao_id' => $payment_id,
                    'status' => 'failure',
                    'nonce' => $nonce
                ),
                home_url()
            );

            if ('enabled' == $withoutAuth3DS) {
                $body = array(
                    'capture' => true,
                    'kind' => 'credit',
                    'reference' => $payment_id,
                    'amount' => $amount,
                    'cardholderName' => $CardName,
                    'cardNumber' => $cardNum,
                    'expirationMonth' => $cardExpiryMonth,
                    'expirationYear' => $cardExpiryYear,
                    'securityCode' => $CardCVC,
                    'subscription' => false,
                    'origin' => 1,
                    'distributorAffiliation' => 0,
                    'storageCard' => '0',
                    'transactionCredentials' => array(
                        'credentialId' => '01'
                    )
                );
            } else {
                $body = array(
                    'capture' => false,
                    'kind' => 'credit',
                    'reference' => 'order' . $payment_id,
                    'amount' => $amount,
                    'cardholderName' => $CardName,
                    'cardNumber' => $cardNum,
                    'expirationMonth' => $cardExpiryMonth,
                    'expirationYear' => $cardExpiryYear,
                    'securityCode' => $CardCVC,
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
                            'url' => $redirect_url_sucess
                        ),
                        array(
                            'kind' => 'threeDSecureFailure',
                            'url' => $redirect_url_fail
                        )
                    )
                );
            }

            // Adicione o softDescriptor apenas se withoutDescription for disabled
            if ('disabled' === $configs['withoutDescription']) {
                $body['softDescriptor'] = $configs['description'];
            }

            $body = apply_filters('lknpg_erede_credit_body', $body, $currencyCode);

            $response = wp_remote_post($configs['api_url'], array(
                'headers' => $headers,
                'body' => wp_json_encode($body)
            ));

            $response = json_decode(wp_remote_retrieve_body($response));

            if ('enabled' === $configs['debug']) {
                LknpgPaymentEredeForGivewpHelper::regLog(
                    'info', // logType
                    'createPayment', // category
                    __('Credit card payment', 'payment-gateway-e-rede-for-givewp'), // description
                    array(
                        'url' => $configs['api_url'],
                        'headers' => $headers,
                        'body' => $body,
                        'response' => $response
                    ), // data
                    true // forceLog
                );
            }

            $arrMetaData = array(
                'status' => $response->returnCode ?? '500',
                'message' => $response->returnMessage ?? 'Error on processing payment',
                'transaction_id' => $response->tid ?? '0',
                'capture' => $body['capture']
            );

            give_update_payment_meta($payment_id, 'lkn_erede_response', wp_json_encode($arrMetaData));

            switch ($response->returnCode) {
                case '00':

                    $donation->status = DonationStatus::COMPLETE();
                    $donation->save();

                    return new PaymentComplete($payment_id);
                    exit;

                case '220':

                    $paymentsToVerify = give_get_option('lkn_erede_3ds_payments_pending', '');

                    if (empty($paymentsToVerify)) {
                        $paymentsToVerify = array();
                    } else {
                        $paymentsToVerify = json_decode(base64_decode($paymentsToVerify, true), true);
                    }

                    $paymentsToVerify[] = array('id' => $payment_id, 'count' => '0');
                    $paymentsToVerify = base64_encode(wp_json_encode($paymentsToVerify));
                    give_update_option('lkn_erede_3ds_payments_pending', $paymentsToVerify);

                    $donation->status = DonationStatus::PENDING();
                    $donation->save();

                    return new RedirectOffsite($response->threeDSecure->url);
                    exit;

                default:
                    $errorMessage = $response->returnMessage ?? 'Error on processing payment';

                    $donation->status = DonationStatus::FAILED();
                    $donation->save();

                    DonationNote::create(array(
                        'donationId' => $donation->id,
                        'content' => sprintf(esc_html('Falha na doação. Razão: %s'), $errorMessage)
                    ));

                    throw new PaymentGatewayException($errorMessage);
            }
        } catch (Exception $e) {
            $errorMessage = $e->getMessage() ?? 'Error on processing payment';

            // Log de debug para erros se o modo debug estiver ativo
            if (isset($configs) && 'enabled' === $configs['debug']) {
                LknpgPaymentEredeForGivewpHelper::regLog(
                    'error', // logType
                    'createPaymentError', // category
                    __('Error processing payment', 'payment-gateway-e-rede-for-givewp'), // description
                    array(
                        'url' => $configs['api_url'] ?? 'N/A',
                        'headers' => $headers ?? 'N/A',
                        'body' => $body ?? 'N/A',
                        'response' => $response ?? 'N/A',
                        'exception' => $e->getMessage(),
                        'errorMessage' => $errorMessage
                    ), // data
                    true // forceLog
                );
            }

            $donation->status = DonationStatus::FAILED();
            $donation->save();

            DonationNote::create(array(
                'donationId' => $donation->id,
                'content' => sprintf(esc_html('Falha na doação. Razão: %s'), $errorMessage)
            ));

            throw new PaymentGatewayException(esc_html($errorMessage));
        }
    }

    /* ========== FORM RENDER ========== */

    /**
     * Function that build the donation form
     *
     * @param int $form_id - the form identificator
     *
     * @param array $args - list of additional arguments
     *
     * @return mixed
     */
    final public static function credit_card_form($form_id, $args)
    {
        $configs = LknpgPaymentEredeForGivewpHelper::get_configs('credit');

        ob_start();

        do_action('give_before_cc_fields', $form_id); ?>

        <fieldset id="give_cc_fields" class="give-do-validate">
            <legend>
                Informações de cartão de crédito
            </legend>

            <?php if (is_ssl()) { ?>
                <div id="give_secure_site_wrapper">
                    <span class="give-icon padlock"></span>
                    <span>
                        Doação Segura por Criptografia SSL
                    </span>
                </div>
            <?php }

            if (! is_ssl()) {
                Give()->notices->print_frontend_notice(
                    sprintf(
                        '<strong>%1$s</strong> %2$s',
                        esc_html__('Erro:', 'payment-gateway-e-rede-for-givewp'),
                        esc_html__('Doação desabilitada por falta de SSL (HTTPS).', 'payment-gateway-e-rede-for-givewp')
                    )
                );

                exit;
            }

            ?>

            <!-- Secure 3DS - Erede -->
            <input type="hidden" name="gatewayData[paymentUserAgent]" value="" />
            <input type="hidden" name="gatewayData[paymentColorDepth]" value="" />
            <input type="hidden" name="gatewayData[paymentLanguage]" value="" />
            <input type="hidden" name="gatewayData[paymentHeight]" value="" />
            <input type="hidden" name="gatewayData[paymentWidth]" value="" />
            <input type="hidden" name="gatewayData[paymentTimezoneOffset]" value="" />

            <!-- CARD NUMBER INPUT -->
            <div id="give-card-number-wrap"
                class="form-row form-row-two-thirds form-row-responsive give-lkn-cielo-api-cc-field-wrap">
                <label for="card_number-<?php echo esc_attr($form_id); ?>"
                    class="give-label">
                    Número do cartão
                    <span class="give-required-indicator">*</span>
                    <span class="give-tooltip hint--top hint--medium hint--bounce"
                        aria-label="Normalmente possui 16 digitos na frente do seu cartão de crédito." rel="tooltip"><i
                            class="give-icon give-icon-question"></i></span>
                </label>
                <input type="tel" autocomplete="off" name="gatewayData[paymentCardNum]"
                    id="card_number-<?php echo esc_attr($form_id); ?>"
                    class="card-number give-input required" placeholder="Número do cartão" required="" aria-required="true" />
            </div>

            <!-- CARD EXPIRY INPUT -->
            <div id="give-card-expiration-wrap"
                class="card-expiration form-row form-row-one-third form-row-responsive give-lkn-cielo-api-cc-field-wrap">
                <label
                    for="give-card-expiration-field-<?php echo esc_attr($form_id); ?>"
                    class="give-label">
                    Expiração
                    <span class="give-required-indicator">*</span>
                    <span class="give-tooltip give-icon give-icon-question"
                        data-tooltip="A data de expiração do cartão de crédito, geralmente na frente do cartão."></span>
                </label>
                <input type="tel" autocomplete="off" name="gatewayData[paymentCardExp]"
                    id="card_expiry-<?php echo esc_attr($form_id); ?>"
                    class="card-expiry give-input required" placeholder="MM / AAAA" required="" aria-required="true" />
            </div>

            <!-- CARD HOLDER INPUT -->
            <div id="give-card-name-wrap" class="form-row form-row-two-thirds form-row-responsive">
                <label
                    for="give-card-name-field-<?php echo esc_attr($form_id); ?>"
                    class="give-label">
                    Nome do títular do cartão
                    <span class="give-required-indicator">*</span>
                    <span class="give-tooltip give-icon give-icon-question"
                        data-tooltip="O nome do titular da conta do cartão de crédito.">
                    </span>
                </label>
                <input type="text" autocomplete="off"
                    id="give-card-name-field-<?php echo esc_attr($form_id); ?>"
                    name="gatewayData[paymentCardName]" class="card-name give-input required"
                    placeholder="Nome do titular do cartão" required="" aria-required="true" />
            </div>

            <!-- CARD CVV INPUT -->
            <div id="give-card-cvc-wrap"
                class="form-row form-row-one-third form-row-responsive give-lkn-cielo-api-cc-field-wrap">
                <label
                    for="give-card-cvc-field-<?php echo esc_attr($form_id); ?>"
                    class="give-label">
                    CVV
                    <span class="give-required-indicator">*</span>
                    <span class="give-tooltip give-icon give-icon-question"
                        data-tooltip="São os 3 ou 4 dígitos que estão atrás do seu cartão de crédito."></span>
                </label>
                <div id="give-card-cvc-field-<?php echo esc_attr($form_id); ?>"
                    class="input empty give-lkn-cielo-api-cc-field give-lkn-cielo-api-card-cvc-field"></div>
                <input type="tel" size="4" maxlength="4" autocomplete="off" name="gatewayData[paymentCardCVC]"
                    id="card_cvc-<?php echo esc_attr($form_id); ?>"
                    class="give-input required" placeholder="CVV" required="" aria-required="true" />
            </div>
            <?php
            do_action('give_after_cc_expiration', $form_id, $args);

            do_action('give_lkn_payment_erede_after_cc_expiration', $form_id, $args);

            // Remove Address Fields if user has option enabled.
            if ('disabled' === $configs['billing_fields']) {
                remove_action('give_after_cc_fields', 'give_default_cc_address_fields');
            }

            do_action('give_after_cc_fields', $form_id, $args);
            ?>
        </fieldset>

<?php
        wp_enqueue_script('lknPaymentEredeForGivewpSetPaymentInfo', PAYMENT_EREDE_FOR_GIVEWP_URL . 'Public/js/lknpgPaymentEredeForGivewpSetPaymentInfo.js', array('jquery'), PAYMENT_EREDE_FOR_GIVEWP_VERSION, false);

        $form = ob_get_clean();

        return $form;
    }

    /**
     * @inerhitDoc
     */
    public function refundDonation(Donation $donation): PaymentRefunded
    {
        // Step 1: refund the donation with your gateway.
        // Step 2: return a command to complete the refund.
        return new PaymentRefunded();
    }

    /**
     * // needs this function to appear in v3 forms
     * @since 3.0.0
     */
    public function enqueueScript(int $formId): void
    {
        wp_enqueue_script(
            self::id(),
            PAYMENT_EREDE_FOR_GIVEWP_URL . 'Public/js/plugin-credit-script.js',
            array('wp-element', 'wp-i18n'),
            PAYMENT_EREDE_FOR_GIVEWP_VERSION,
            true
        );
    }
}
