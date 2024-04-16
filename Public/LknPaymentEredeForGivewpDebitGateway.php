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
class LknPaymentEredeForGivewpDebitGateway extends PaymentGateway {
    /**
     * @inheritDoc
     */
    public static function id(): string {
        return 'lkn_erede_debit_3ds';
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
        return __('E-Rede API - Debit Card 3DS', 'lkn_erede_debit_3ds');
    }

    /**
     * @inheritDoc
     */
    public function getPaymentMethodLabel(): string {
        return __('E-Rede - Debit Card', 'lkn_erede_debit_3ds');
    }

    /**
     * @inheritDoc
     */
    public function getLegacyFormFieldMarkup(int $formId, array $args): string {
        return $this->debit_card_form($formId, $args);
    }

    /**
     * @inheritDoc
     */
    public function createPayment(Donation $donation, $gatewayData): GatewayCommand {
        try {
            // Step 1: Validate any data passed from the gateway fields in $gatewayData.  Throw the PaymentGatewayException if the data is invalid.
            if (empty($gatewayData['example-gateway-id'])) {
                throw new PaymentGatewayException(__('Example payment ID is required.', 'example-give'));
            }

            // Step 2: Create a payment with your gateway.
            $response = $this->exampleRequest(array('transaction_id' => $gatewayData['example-gateway-id']));

            // Step 3: Return a command to complete the donation. You can alternatively return PaymentProcessing for gateways that require a webhook or similar to confirm that the payment is complete. PaymentProcessing will trigger a Payment Processing email notification, configurable in the settings.

            return new PaymentComplete($response['transaction_id']);
        } catch (Exception $e) {
            // Step 4: If an error occurs, you can update the donation status to something appropriate like failed, and finally throw the PaymentGatewayException for the framework to catch the message.
            $errorMessage = $e->getMessage();

            $donation->status = DonationStatus::FAILED();
            $donation->save();

            DonationNote::create(array(
                'donationId' => $donation->id,
                'content' => sprintf(esc_html__('Donation failed. Reason: %s', 'example-give'), $errorMessage)
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
    final public static function debit_card_form($form_id, $args) {
        $configs = LknPaymentEredeForGivewpHelper::get_configs('credit');
      
        ob_start();

        do_action('give_before_cc_fields', $form_id); ?>

        <fieldset id="give_dc_fields" class="give-do-validate">
            <legend>
                Informações de cartão de débito
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
            <!-- Secure 3DS - Erede -->
            <input type="hidden" name="lkn_erede_debit_3ds_user_agent" value="" />
            <input type="hidden" name="lkn_erede_debit_3ds_device_color" value=""/>
            <input type="hidden" name="lkn_erede_debit_3ds_lang" value=""/>
            <input type="hidden" name="lkn_erede_debit_3ds_device_height" value=""/>
            <input type="hidden" name="lkn_erede_debit_3ds_device_width" value=""/>
            <input type="hidden" name="lkn_erede_debit_3ds_timezone" value=""/>

            <script type="text/javascript">
                const language = window.navigator.language.slice(0, 2)
                const height = screen.height
                const width = screen.width
                const colorDepth = window.screen.colorDepth
                const userAgent = navigator.userAgent
                const date = new Date()
                const timezoneOffset = date.getTimezoneOffset()

                const userAgentInput = document.getElementsByName('lkn_erede_debit_3ds_user_agent')[0]
                const deviceColorInput = document.getElementsByName('lkn_erede_debit_3ds_device_color')[0]
                const langInput = document.getElementsByName('lkn_erede_debit_3ds_lang')[0]
                const heightInput = document.getElementsByName('lkn_erede_debit_3ds_device_height')[0]
                const widthInput = document.getElementsByName('lkn_erede_debit_3ds_device_width')[0]
                const timezoneInput = document.getElementsByName('lkn_erede_debit_3ds_timezone')[0]

                if (userAgentInput && deviceColorInput && langInput && heightInput && widthInput && timezoneInput) {
                    userAgentInput.value = userAgent
                    deviceColorInput.value = colorDepth
                    langInput.value = language
                    heightInput.value = height
                    widthInput.value = width
                    timezoneInput.value = timezoneOffset
                }
            </script>

            <!-- CARD NUMBER INPUT -->
            <div id="give-card-number-wrap" class="form-row form-row-two-thirds form-row-responsive give-lkn-cielo-api-cc-field-wrap">
                <label for="card_number-<?php esc_attr_e($form_id); ?>" class="give-label">
                    Número do cartão
                    <span class="give-required-indicator">*</span>
                    <span class="give-tooltip hint--top hint--medium hint--bounce" aria-label="Normalmente possui 16 digitos na frente do seu cartão de débito." rel="tooltip"><i class="give-icon give-icon-question"></i></span>
                </label>
                <input
                    type="tel"
                    autocomplete="off"
                    name="lkn_erede_debit_3ds_card_number"
                    id="card_number-<?php esc_attr_e($form_id); ?>"
                    class="card-number give-input required"
                    placeholder="Número do cartão"
                    required=""
                    aria-required="true"
                />
            </div>

            <!-- CARD EXPIRY INPUT -->
            <div id="give-card-expiration-wrap" class="card-expiration form-row form-row-one-third form-row-responsive give-lkn-cielo-api-cc-field-wrap">
                <label for="give-card-expiration-field-<?php esc_attr_e($form_id); ?>" class="give-label">
                    Expiração
                    <span class="give-required-indicator">*</span>
                    <span class="give-tooltip give-icon give-icon-question"
                        data-tooltip="A data de expiração do cartão de débito, geralmente na frente do cartão."></span>
                </label>
                <input
                    type="tel"
                    autocomplete="off"
                    name="lkn_erede_debit_3ds_card_expiry"
                    id="card_expiry-<?php esc_attr_e($form_id); ?>"
                    class="card-expiry give-input required"
                    placeholder="MM / AAAA"
                    required=""
                    aria-required="true"
                />
            </div>

            <!-- CARD HOLDER INPUT -->
            <div id="give-card-name-wrap" class="form-row form-row-two-thirds form-row-responsive">
                <label for="give-card-name-field-<?php esc_attr_e($form_id); ?>" class="give-label">
                    Nome do títular do cartão
                    <span class="give-required-indicator">*</span>
                    <span class="give-tooltip give-icon give-icon-question"
                        data-tooltip="O nome do titular da conta do cartão de débito.">
                    </span>
                </label>
                <input
                    type="text"
                    autocomplete="off"
                    id="give-card-name-field-<?php esc_attr_e($form_id); ?>"
                    name="lkn_erede_debit_3ds_card_name"
                    class="card-name give-input required"
                    placeholder="Nome do titular do cartão"
                    required=""
                    aria-required="true"
                />
            </div>

            <!-- CARD CVV INPUT -->
            <div id="give-card-cvc-wrap" class="form-row form-row-one-third form-row-responsive give-lkn-cielo-api-cc-field-wrap">
                <label for="give-card-cvc-field-<?php esc_attr_e($form_id); ?>" class="give-label">
                    CVV
                    <span class="give-required-indicator">*</span>
                    <span class="give-tooltip give-icon give-icon-question"
                        data-tooltip="São os 3 ou 4 dígitos que estão atrás do seu cartão de débito."></span>
                </label>
                <div id="give-card-cvc-field-<?php esc_attr_e($form_id); ?>" class="input empty give-lkn-cielo-api-cc-field give-lkn-cielo-api-card-cvc-field"></div>
                <input
                    type="tel"
                    size="4"
                    maxlength="4"
                    autocomplete="off"
                    name="lkn_erede_debit_3ds_card_cvc"
                    id="card_cvc-<?php esc_attr_e($form_id); ?>"
                    class="give-input required"
                    placeholder="CVV"
                    required=""
                    aria-required="true"
                />
            </div>
            <?php
            do_action('give_after_dc_expiration', $form_id, $args);

            do_action('give_lkn_payment_erede_after_dc_expiration', $form_id, $args);

            // Remove Address Fields if user has option enabled.
            if ('disabled' === $$configs['billing_details']) {
                remove_action('give_after_dc_fields', 'give_default_cc_address_fields');
            }

            do_action('give_after_dc_fields', $form_id, $args);
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
            PAYMENT_EREDE_FOR_GIVEWP_URL . 'Public/js/plugin-debit-script.js',
            array('wp-element', 'wp-i18n'),
            PAYMENT_EREDE_FOR_GIVEWP_VERSION,
            true
        );
    }
}