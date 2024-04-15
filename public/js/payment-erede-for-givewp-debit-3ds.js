/* eslint-disable no-undef */
(function ($) {
  'use strict'

  $(window).on('load', () => lknLoadEredeDebit3DS())

  function lknLoadEredeDebit3DS () {
    const iframe = document.getElementsByName('give-embed-form')[0]
    const giveForm = $('.give-form')

    if (iframe) {
      lknSetInputsEredeDebit3DS('iframe')
      const gatewayList = iframe.contentDocument.getElementById('give-gateway-radio-list')
      if (gatewayList) {
        gatewayList.addEventListener('click', () => lknSetInputsEredeDebit3DS('iframe'))
      }
    } else if (giveForm.length) {
      lknSetInputsEredeDebit3DS('legacy')
      const gatewayList = $('#give-gateway-radio-list')
      if (gatewayList.length) {
        gatewayList.on('click', () => lknSetInputsEredeDebit3DS('legacy'))
      }
    }
  }

  function lknSetInputsEredeDebit3DS (typeForm, count = 0) {
    count++

    const iframe = document.getElementsByName('give-embed-form')[0]

    const language = window.navigator.language.slice(0, 2)
    const height = screen.height
    const width = screen.width
    const colorDepth = window.screen.colorDepth
    const userAgent = navigator.userAgent
    const date = new Date()
    const timezoneOffset = date.getTimezoneOffset()

    if (typeForm === 'iframe') {
      const userAgentInput = iframe.contentDocument.getElementsByName('lkn_erede_debit_3ds_user_agent')[0]
      const deviceColorInput = iframe.contentDocument.getElementsByName('lkn_erede_debit_3ds_device_color')[0]
      const langInput = iframe.contentDocument.getElementsByName('lkn_erede_debit_3ds_lang')[0]
      const heightInput = iframe.contentDocument.getElementsByName('lkn_erede_debit_3ds_device_height')[0]
      const widthInput = iframe.contentDocument.getElementsByName('lkn_erede_debit_3ds_device_width')[0]
      const timezoneInput = iframe.contentDocument.getElementsByName('lkn_erede_debit_3ds_timezone')[0]

      if (
        userAgentInput &&
        deviceColorInput &&
        langInput &&
        heightInput &&
        widthInput &&
        timezoneInput
      ) {
        userAgentInput.value = userAgent
        deviceColorInput.value = colorDepth
        langInput.value = language
        heightInput.value = height
        widthInput.value = width
        timezoneInput.value = timezoneOffset

        return true
      }
    } else {
      const userAgentInput = $('[name="lkn_erede_debit_3ds_user_agent"]')
      const deviceColorInput = $('[name="lkn_erede_debit_3ds_device_color"]')
      const langInput = $('[name="lkn_erede_debit_3ds_lang"]')
      const heightInput = $('[name="lkn_erede_debit_3ds_device_height"]')
      const widthInput = $('[name="lkn_erede_debit_3ds_device_width"]')
      const timezoneInput = $('[name="lkn_erede_debit_3ds_timezone"]')

      if (
        userAgentInput.length &&
        deviceColorInput.length &&
        langInput.length &&
        heightInput.length &&
        widthInput.length &&
        timezoneInput.length
      ) {
        userAgentInput.attr('value', userAgent)
        deviceColorInput.attr('value', colorDepth)
        langInput.attr('value', language)
        heightInput.attr('value', height)
        widthInput.attr('value', width)
        timezoneInput.attr('value', timezoneOffset)

        return true
      }
    }

    // Only run 4 times
    if (count > 4) {
      return false
    }

    // Run again if inputs are not found
    setTimeout(() => lknSetInputsEredeDebit3DS(typeForm, count), 1000)
  }
})(jQuery)
