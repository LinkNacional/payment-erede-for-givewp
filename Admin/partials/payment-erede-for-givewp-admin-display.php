<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://www.linknacional.com.br/wordpress/plugins/
 * @since      1.0.0
 *
 * @package    Payment_Erede_For_Givewp
 * @subpackage Payment_Erede_For_Givewp/admin/partials
 */
?>

<input type="hidden" id="lkn-erede-capture" value="<?php echo esc_attr($args['capture']); ?>">
<input type="hidden" id="lkn-erede-log" value="<?php echo esc_attr($args['log_exists']); ?>">

<div id="lkn-erede-meta-wrap" class="give-order-gateway give-admin-box-inside lkn-hidden">
    <div>
        <p>
            <strong><?php echo esc_html($args['status_label']) ?></strong> <?php echo esc_html($args['status']) ?>
            <a href="https://developer.userede.com.br/e-rede#documentacao-retornos-retornos-emissor" target="_blank">
                <?php echo esc_html($args['know_more_label']) ?>
            </a>
        </p><br>
        <p><strong><?php echo esc_html($args['message_label']) ?></strong> <?php echo esc_html($args['message']) ?></p><br>
        <p><strong><?php echo esc_html($args['transaction_label']) ?></strong> <?php echo esc_html($args['transaction_id']) ?></p><br>
    </div>
</div>

<div id="lkn-erede-log-wrap" class="give-order-gateway give-admin-box-inside lkn-hidden">
    <div>
        <p>
            <a href="data:text/log;charset=utf-8,<?php echo esc_attr($args['log_data']) ?>" download="lkn-erede.log"><?php echo esc_html($args['log_label']) ?></a>
        </p>
    </div>
</div>