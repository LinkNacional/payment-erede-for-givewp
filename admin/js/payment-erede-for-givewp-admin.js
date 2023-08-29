/* eslint-disable no-undef */
(function ($) {
  'use strict'

  /**
     * All of the code for your admin-facing JavaScript source
     * should reside in this file.
     *
     * Note: It has been assumed you will write jQuery code here, so the
     * $ function reference has been prepared for usage within the scope
     * of this function.
     *
     * This enables you to define handlers, for when the DOM is ready:
     *
     * $(function() {
     *
     * });
     *
     * When the window is loaded:
     *
     * $( window ).load(function() {
     *
     * });
     *
     * ...and/or other possibilities.
     *
     * Ideally, it is not considered best practise to attach more than a
     * single DOM-ready or window-load handler for a particular page.
     * Although scripts in the WordPress core, Plugins and Themes may be
     * practising this, we should strive to set a better example in our own work.
     */

  $(window).on('load', () => {
    const urlParams = new URLSearchParams(window.location.search)
    const section = urlParams.get('section')
    const postType = urlParams.get('post_type')
    const page = urlParams.get('page')
    const tab = urlParams.get('tab')

    if (
      postType === 'give_forms' &&
      page === 'give-settings' &&
      tab === 'gateways'
    ) {
      switch (section) {
        case 'lkn-erede-credit': {
          const sofdescriptionInputCredit = $('#lkn_erede_credit_softdescription_setting_field')
          sofdescriptionInputCredit.attr('maxlength', '18')

          break
        }
        case 'lkn-erede-debit-3ds': {
          const sofdescriptionInputDebit = $('#lkn_erede_debit_3ds_softdescription_setting_field')
          sofdescriptionInputDebit.attr('maxlength', '18')

          break
        }

        default:
          break
      }
    }
  })
})(jQuery)
