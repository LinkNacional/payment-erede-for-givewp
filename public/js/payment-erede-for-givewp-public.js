/* eslint-disable no-undef */
(function ($) {
  'use strict'

  /**
     * All of the code for your public-facing JavaScript source
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
    const language = window.navigator.language.slice(0, 2)
    const height = screen.height
    const width = screen.width
    const colorDepth = window.screen.colorDepth
    const userAgent = navigator.userAgent
    const date = new Date()
    const timezoneOffset = date.getTimezoneOffset()

    $('[name="lkn_erede_debit_3ds_user_agent"]').attr('value', userAgent)
    $('[name="lkn_erede_debit_3ds_device_color"]').attr('value', colorDepth)
    $('[name="lkn_erede_debit_3ds_lang"]').attr('value', language)
    $('[name="lkn_erede_debit_3ds_device_height"]').attr('value', height)
    $('[name="lkn_erede_debit_3ds_device_width"]').attr('value', width)
    $('[name="lkn_erede_debit_3ds_timezone"]').attr('value', timezoneOffset)
  })
})(jQuery)
