/* eslint-disable no-undef */
(function ($) {
  'use strict'

  $(window).on('load', () => {
    const iframe = document.getElementsByName('give-embed-form')[0]
    const giveForm = $('.give-form')

    const language = window.navigator.language.slice(0, 2)
    const height = screen.height
    const width = screen.width
    const colorDepth = window.screen.colorDepth
    const userAgent = navigator.userAgent
    const date = new Date()
    const timezoneOffset = date.getTimezoneOffset()

    if (iframe) {
      iframe.contentDocument.getElementsByName('lkn_erede_debit_3ds_user_agent')[0].value = userAgent
      iframe.contentDocument.getElementsByName('lkn_erede_debit_3ds_device_color')[0].value = colorDepth
      iframe.contentDocument.getElementsByName('lkn_erede_debit_3ds_lang')[0].value = language
      iframe.contentDocument.getElementsByName('lkn_erede_debit_3ds_device_height')[0].value = height
      iframe.contentDocument.getElementsByName('lkn_erede_debit_3ds_device_width')[0].value = width
      iframe.contentDocument.getElementsByName('lkn_erede_debit_3ds_timezone')[0].value = timezoneOffset
    } else if (giveForm.length) {
      $('[name="lkn_erede_debit_3ds_user_agent"]').attr('value', userAgent)
      $('[name="lkn_erede_debit_3ds_device_color"]').attr('value', colorDepth)
      $('[name="lkn_erede_debit_3ds_lang"]').attr('value', language)
      $('[name="lkn_erede_debit_3ds_device_height"]').attr('value', height)
      $('[name="lkn_erede_debit_3ds_device_width"]').attr('value', width)
      $('[name="lkn_erede_debit_3ds_timezone"]').attr('value', timezoneOffset)
    }
  })
})(jQuery)
