<?php

namespace Lkn\PaymentEredeForGivewp\PublicView;

use Give\Donations\Models\Donation;
use Give\Donations\Models\DonationNote;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\PaymentGateways\Commands\GatewayCommand;
use Give\Framework\PaymentGateways\Commands\PaymentComplete;
use Give\Framework\PaymentGateways\Commands\PaymentRefunded;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;
use Give\Framework\PaymentGateways\PaymentGateway;
use Lkn\PaymentEredeForGivewp\Includes\LknPaymentEredeForGivewpHelper;

/**
 * @inheritDoc
 */
class LknPaymentEredeForGivewpCreditGateway extends PaymentGateway {
    /**
     * @inheritDoc
     */
    public static function id(): string {
        return 'lkn_erede_credit';
    }

    /**
     * @inheritDoc
     */
    public function getId(): string {
        return self::id();
    }

    /**
     * @inheritDoc
     */
    public function getName(): string {
        return __('E-Rede API - Credit Card', 'lkn_erede_credit');
    }

    /**
     * @inheritDoc
     */
    public function getPaymentMethodLabel(): string {
        return __('E-Rede - Credit Card', 'lkn_erede_credit');
    }

    /**
     * @inheritDoc
     */
    public function getLegacyFormFieldMarkup(int $formId, array $args): string {
        return $this->credit_card_form($formId, $args);
    }

    /**
     * @inheritDoc
     */
    public function createPayment(Donation $donation, $gatewayData): GatewayCommand {
        try {
            // Set the configs values
            $configs = LknPaymentEredeForGivewpHelper::get_configs('credit');
            $logname = date('d.m.Y-H.i.s') . '-credit';

            $donation->firstName = sanitize_text_field($donation->firstName);
            $donation->lastName = sanitize_text_field($donation->lastName);
            $donation->email = sanitize_email($donation->email);
            
            // Donation informations.
            $donPrice = $donation->amount->formatToDecimal();

            $cardNum = preg_replace('/\D/', '', sanitize_text_field($gatewayData['paymentCardNum']));
            $CardCVC = $gatewayData['paymentCardCVC'];
            $CardName = sanitize_text_field($gatewayData['paymentCardName']);
            $CardExp = $gatewayData['paymentCardExp'];
            
            //Separando mes e ano
            $expDate = explode('/', $CardExp);
            $cardExpiryMonth = trim($expDate[0]);
            $cardExpiryYear = trim($expDate[1]);

            $headers = array(
                'Authorization' => 'Basic ' . base64_encode( $configs['pv'] . ':' . $configs['token'] ),
                'Content-Type' => 'application/json'
            );

            $currencyCode = give_get_currency($donation->formId, $donation);

            $payment_id = $donation->id;
            $amount = $donPrice;
            $amount = number_format($amount, 2, '', '');

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
                'softDescriptor' => $configs['description'],
                'subscription' => false,
                'origin' => 1,
                'distributorAffiliation' => 0,
                'storageCard' => '0',
                'transactionCredentials' => array(
                    'credentialId' => '01'
                )
            );

            $body = apply_filters('lkn_erede_credit_body', $body, $currencyCode, $donation);

            $response = wp_remote_post($configs['api_url'], array(
                'headers' => $headers,
                'body' => json_encode($body)
            ));

            if ('enabled' === $configs['debug']) {
                LknPaymentEredeForGivewpHelper::log('[Raw header]: ' . var_export(wp_remote_retrieve_headers($response), true) . \PHP_EOL . ' [Raw body]: ' . var_export(json_decode(wp_remote_retrieve_body($response)), true), $logname);
            }

            $response = json_decode(wp_remote_retrieve_body($response));

            $arrMetaData = array(
                'status' => $response->returnCode ?? '500',
                'message' => $response->returnMessage ?? 'Error on processing payment',
                'transaction_id' => $response->tid ?? '0',
                'capture' => $body['capture']
            );

            if ('enabled' === $configs['debug']) {
                $arrMetaData['log'] = $logname;
            }

            give_update_payment_meta($payment_id, 'lkn_erede_response', json_encode($arrMetaData));

            switch ($response->returnCode) {
                case '00':

                    $donation->status = DonationStatus::COMPLETE();
                    $donation->save();

                    return new PaymentComplete($payment_id);
                    break;

                default:
                    $errorMessage = $response->returnMessage ?? 'Error on processing payment';

                    $donation->status = DonationStatus::FAILED();
                    $donation->save();
                
                    DonationNote::create(array(
                        'donationId' => $donation->id,
                        'content' => sprintf(esc_html('Falha na doação. Razão: %s'), $errorMessage)
                    ));
                
                    throw new PaymentGatewayException($errorMessage);
                    break;
            }
        } catch (Exception $e) {
            $errorMessage = $response->returnMessage ?? 'Error on processing payment';

            $donation->status = DonationStatus::FAILED();
            $donation->save();
                
            DonationNote::create(array(
                'donationId' => $donation->id,
                'content' => sprintf(esc_html('Falha na doação. Razão: %s'), $errorMessage)
            ));
                
            throw new PaymentGatewayException($errorMessage);
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
    final public static function credit_card_form($form_id, $args) {
        $configs = LknPaymentEredeForGivewpHelper::get_configs('credit');
      
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

	if ( ! is_ssl()) {
	    Give()->notices->print_frontend_notice(
	        sprintf(
	            '<strong>%1$s</strong> %2$s',
	            esc_html__('Erro:', 'give'),
	            esc_html__('Doação desabilitada por falta de SSL (HTTPS).', 'give')
	        )
	    );

	    exit;
	}
        ?>
	<!-- CARD NUMBER INPUT -->
	<div id="give-card-number-wrap"
		class="form-row form-row-two-thirds form-row-responsive give-lkn-cielo-api-cc-field-wrap">
		<label for="card_number-<?php esc_attr_e($form_id); ?>"
			class="give-label">
			Número do cartão
			<span class="give-required-indicator">*</span>
			<span class="give-tooltip hint--top hint--medium hint--bounce"
				aria-label="Normalmente possui 16 digitos na frente do seu cartão de crédito." rel="tooltip"><i
					class="give-icon give-icon-question"></i></span>
		</label>
		<input type="tel" autocomplete="off" name="gatewayData[paymentCardNum]"
			id="card_number-<?php esc_attr_e($form_id); ?>"
			class="card-number give-input required" placeholder="Número do cartão" required="" aria-required="true" />
	</div>

	<!-- CARD EXPIRY INPUT -->
	<div id="give-card-expiration-wrap"
		class="card-expiration form-row form-row-one-third form-row-responsive give-lkn-cielo-api-cc-field-wrap">
		<label
			for="give-card-expiration-field-<?php esc_attr_e($form_id); ?>"
			class="give-label">
			Expiração
			<span class="give-required-indicator">*</span>
			<span class="give-tooltip give-icon give-icon-question"
				data-tooltip="A data de expiração do cartão de crédito, geralmente na frente do cartão."></span>
		</label>
		<input type="tel" autocomplete="off" name="gatewayData[paymentCardExp]"
			id="card_expiry-<?php esc_attr_e($form_id); ?>"
			class="card-expiry give-input required" placeholder="MM / AAAA" required="" aria-required="true" />
	</div>

	<!-- CARD HOLDER INPUT -->
	<div id="give-card-name-wrap" class="form-row form-row-two-thirds form-row-responsive">
		<label
			for="give-card-name-field-<?php esc_attr_e($form_id); ?>"
			class="give-label">
			Nome do títular do cartão
			<span class="give-required-indicator">*</span>
			<span class="give-tooltip give-icon give-icon-question"
				data-tooltip="O nome do titular da conta do cartão de crédito.">
			</span>
		</label>
		<input type="text" autocomplete="off"
			id="give-card-name-field-<?php esc_attr_e($form_id); ?>"
			name="gatewayData[paymentCardName]" class="card-name give-input required"
			placeholder="Nome do titular do cartão" required="" aria-required="true" />
	</div>

	<!-- CARD CVV INPUT -->
	<div id="give-card-cvc-wrap"
		class="form-row form-row-one-third form-row-responsive give-lkn-cielo-api-cc-field-wrap">
		<label
			for="give-card-cvc-field-<?php esc_attr_e($form_id); ?>"
			class="give-label">
			CVV
			<span class="give-required-indicator">*</span>
			<span class="give-tooltip give-icon give-icon-question"
				data-tooltip="São os 3 ou 4 dígitos que estão atrás do seu cartão de crédito."></span>
		</label>
		<div id="give-card-cvc-field-<?php esc_attr_e($form_id); ?>"
			class="input empty give-lkn-cielo-api-cc-field give-lkn-cielo-api-card-cvc-field"></div>
		<input type="tel" size="4" maxlength="4" autocomplete="off" name="gatewayData[paymentCardCVC]"
			id="card_cvc-<?php esc_attr_e($form_id); ?>"
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

        $form = ob_get_clean();

        return $form;
    }

    /**
     * @inerhitDoc
     */
    public function refundDonation(Donation $donation): PaymentRefunded {
        // Step 1: refund the donation with your gateway.
        // Step 2: return a command to complete the refund.
        return new PaymentRefunded();
    }
    
    /**
     * // TODO needs this function to appear in v3 forms
     * @since 3.0.0
     */
    public function enqueueScript(int $formId): void {
        wp_enqueue_script(
            self::id(),
            PAYMENT_EREDE_FOR_GIVEWP_URL . 'Public/js/plugin-credit-script.js',
            array('wp-element', 'wp-i18n'),
            PAYMENT_EREDE_FOR_GIVEWP_VERSION,
            true
        );
    }
}