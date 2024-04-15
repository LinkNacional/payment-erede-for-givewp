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

<input type="hidden" id="lkn-erede-capture" value="<?php esc_attr_e($args['capture']); ?>">
<input type="hidden" id="lkn-erede-log" value="<?php esc_attr_e($args['log_exists']); ?>">

<div id="lkn-erede-meta-wrap" class="give-order-gateway give-admin-box-inside lkn-hidden">
    <div>
        <p>
            <strong><?php esc_html_e($args['status_label']) ?></strong> <?php esc_html_e($args['status']) ?>
            <a href="https://developer.userede.com.br/e-rede#documentacao-retornos-retornos-emissor" target="_blank">
                <?php esc_html_e($args['know_more_label']) ?>
            </a>
        </p><br>
        <p><strong><?php esc_html_e($args['message_label']) ?></strong> <?php esc_html_e($args['message']) ?></p><br>
        <p><strong><?php esc_html_e($args['transaction_label']) ?></strong> <?php esc_html_e($args['transaction_id']) ?></p><br>
    </div>
</div>

<div id="lkn-erede-log-wrap" class="give-order-gateway give-admin-box-inside lkn-hidden">
    <div>
        <p>
            <a href="data:text/log;charset=utf-8,<?php esc_attr_e($args['log_data']) ?>" download="lkn-erede.log"><?php esc_html_e($args['log_label']) ?></a>
        </p>
    </div>
</div>