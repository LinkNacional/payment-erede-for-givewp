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
do_action('give_before_cc_fields', $args['form_id']);
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

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
    <h1>Hello World!</h1>
<?php
do_action('give_after_cc_expiration', $args['form_id'], $args['settings']);

do_action('give_lkn_payment_erede_after_cc_expiration', $args['form_id'], $args['settings']);

// Remove Address Fields if user has option enabled.
if ('disabled' === $args['billing_details']) {
    remove_action('give_after_cc_fields', 'give_default_cc_address_fields');
}

do_action('give_after_cc_fields', $args['form_id'], $args['settings']);
?>
</fieldset>