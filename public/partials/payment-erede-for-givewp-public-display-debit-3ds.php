<?php

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://www.linknacional.com.br/wordpress/plugins/
 * @since      1.0.0
 *
 * @package    Payment_Erede_For_Givewp
 * @subpackage Payment_Erede_For_Givewp/public/partials
 */
do_action('give_before_dc_fields', $args['form_id']);
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

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
    <input type="hidden" name="lkn_erede_debit_3ds_user_agent" />
    <input type="hidden" name="lkn_erede_debit_3ds_device_color" />
    <input type="hidden" name="lkn_erede_debit_3ds_lang" />
    <input type="hidden" name="lkn_erede_debit_3ds_device_height" />
    <input type="hidden" name="lkn_erede_debit_3ds_device_width" />
    <input type="hidden" name="lkn_erede_debit_3ds_timezone" />

    <!-- CARD NUMBER INPUT -->
    <div id="give-card-number-wrap" class="form-row form-row-two-thirds form-row-responsive give-lkn-cielo-api-cc-field-wrap">
        <label for="card_number-<?php esc_attr_e($args['form_id']); ?>" class="give-label">
            Número do cartão
            <span class="give-required-indicator">*</span>
            <span class="give-tooltip hint--top hint--medium hint--bounce" aria-label="Normalmente possui 16 digitos na frente do seu cartão de débito." rel="tooltip"><i class="give-icon give-icon-question"></i></span>
        </label>
        <input
            type="tel"
            autocomplete="off"
            name="lkn_erede_debit_3ds_card_number"
            id="card_number-<?php esc_attr_e($args['form_id']); ?>"
            class="card-number give-input required"
            placeholder="Número do cartão"
            required=""
            aria-required="true"
        />
    </div>

    <!-- CARD EXPIRY INPUT -->
    <div id="give-card-expiration-wrap" class="card-expiration form-row form-row-one-third form-row-responsive give-lkn-cielo-api-cc-field-wrap">
        <label for="give-card-expiration-field-<?php esc_attr_e($args['form_id']); ?>" class="give-label">
            Expiração
            <span class="give-required-indicator">*</span>
            <span class="give-tooltip give-icon give-icon-question"
                data-tooltip="A data de expiração do cartão de débito, geralmente na frente do cartão."></span>
        </label>
        <input
            type="tel"
            autocomplete="off"
            name="lkn_erede_debit_3ds_card_expiry"
            id="card_expiry-<?php esc_attr_e($args['form_id']); ?>"
            class="card-expiry give-input required"
            placeholder="MM / AAAA"
            required=""
            aria-required="true"
        />
    </div>

    <!-- CARD HOLDER INPUT -->
    <div id="give-card-name-wrap" class="form-row form-row-two-thirds form-row-responsive">
        <label for="give-card-name-field-<?php esc_attr_e($args['form_id']); ?>" class="give-label">
            Nome do títular do cartão
            <span class="give-required-indicator">*</span>
            <span class="give-tooltip give-icon give-icon-question"
                data-tooltip="O nome do titular da conta do cartão de débito.">
            </span>
        </label>
        <input
            type="text"
            autocomplete="off"
            id="give-card-name-field-<?php esc_attr_e($args['form_id']); ?>"
            name="lkn_erede_debit_3ds_card_name"
            class="card-name give-input required"
            placeholder="Nome do titular do cartão"
            required=""
            aria-required="true"
        />
    </div>

    <!-- CARD CVV INPUT -->
    <div id="give-card-cvc-wrap" class="form-row form-row-one-third form-row-responsive give-lkn-cielo-api-cc-field-wrap">
        <label for="give-card-cvc-field-<?php esc_attr_e($args['form_id']); ?>" class="give-label">
            CVV
            <span class="give-required-indicator">*</span>
            <span class="give-tooltip give-icon give-icon-question"
                data-tooltip="São os 3 ou 4 dígitos que estão atrás do seu cartão de débito."></span>
        </label>
        <div id="give-card-cvc-field-<?php esc_attr_e($args['form_id']); ?>" class="input empty give-lkn-cielo-api-cc-field give-lkn-cielo-api-card-cvc-field"></div>
        <input
            type="tel"
            size="4"
            maxlength="4"
            autocomplete="off"
            name="lkn_erede_debit_3ds_card_cvc"
            id="card_cvc-<?php esc_attr_e($args['form_id']); ?>"
            class="give-input required"
            placeholder="CVV"
            required=""
            aria-required="true"
        />
    </div>
<?php
do_action('give_after_dc_expiration', $args['form_id'], $args['settings']);

do_action('give_lkn_payment_erede_after_dc_expiration', $args['form_id'], $args['settings']);

// Remove Address Fields if user has option enabled.
if ('disabled' === $args['billing_details']) {
    remove_action('give_after_dc_fields', 'give_default_cc_address_fields');
}

do_action('give_after_dc_fields', $args['form_id'], $args['settings']);
?>
</fieldset>