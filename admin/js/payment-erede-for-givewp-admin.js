/* eslint-disable no-undef */
(function ($) {
  'use strict'

  $(window).on('load', () => {
    const urlParams = new URLSearchParams(window.location.search)
    const section = urlParams.get('section')
    const postType = urlParams.get('post_type')
    const page = urlParams.get('page')
    const tab = urlParams.get('tab')
    const id = urlParams.get('id')
    const view = urlParams.get('view')

    if (
      postType === 'give_forms' &&
      page === 'give-settings' &&
      tab === 'gateways'
    ) {
      switch (section) {
        case 'lkn-erede-credit': {
          const sofdescriptionInputCredit = $('#lkn_erede_credit_softdescription_setting_field')
          sofdescriptionInputCredit.attr('maxlength', '18')

          // Notice to sell the plugin
          lknMakeNotice()
          // Add support for collapsibles
          lknInitCollapsibles()

          break
        }
        case 'lkn-erede-debit-3ds': {
          const sofdescriptionInputDebit = $('#lkn_erede_debit_3ds_softdescription_setting_field')
          sofdescriptionInputDebit.attr('maxlength', '18')

          // Notice to sell the plugin
          lknMakeNotice()
          // Add support for collapsibles
          lknInitCollapsibles()

          break
        }

        default:
          break
      }
    }

    if (
      postType === 'give_forms' &&
      page === 'give-payment-history' &&
      view === 'view-payment-details' &&
      id
    ) {
      const metadataBox = document.getElementById('give-order-details')
      const lknMetadataWrap = document.getElementById('lkn-erede-meta-wrap')
      const lknMetaLogWrap = document.getElementById('lkn-erede-log-wrap')
      const lknLogExists = document.getElementById('lkn-erede-log')

      if (lknMetadataWrap) {
        metadataBox.append(lknMetadataWrap)
        lknMetadataWrap.classList.remove('lkn-hidden')
      }

      if (lknMetaLogWrap && lknLogExists && lknLogExists.value === '1') {
        metadataBox.append(lknMetaLogWrap)
        lknMetaLogWrap.classList.remove('lkn-hidden')
      }
    }
  })

  function lknMakeNotice () {
    const noticeDiv = document.createElement('div')
    noticeDiv.setAttribute('id', 'lkn-payment-erede-notice')
    noticeDiv.innerHTML = lknEredePaymentAdmin.notice +
    '<ul id="lkn-list-collapsible">' +
    '<li class="lkn-collapsible">' + lknEredePaymentAdmin.captureLabelTitle + '</li>' +
    '<div class="lkn-content">' +
    '  <p>' + lknEredePaymentAdmin.captureLabelDesc + '</p>' +
    '</div>' +
    '<li class="lkn-collapsible">' + lknEredePaymentAdmin.returnLabelTitle + '</li>' +
    '<div class="lkn-content">' +
    '  <p>' + lknEredePaymentAdmin.returnLabelDesc + '</p>' +
    '</div>' +
    '<li class="lkn-collapsible">' + lknEredePaymentAdmin.installmentLabelTitle + '</li>' +
    '<div class="lkn-content">' +
    '  <p>' + lknEredePaymentAdmin.installmentLabelDesc + '</p>' +
    '</div>' +
    '<li class="lkn-collapsible">' + lknEredePaymentAdmin.currencyExchangeLabelTitle + '</li>' +
    '<div class="lkn-content">' +
    '  <p>' + lknEredePaymentAdmin.currencyExchangeLabelDesc + '</p>' +
    '</div>' +
    '</ul>'

    const formSubmit = document.getElementsByClassName('give-submit-wrap')[0]
    formSubmit.before(noticeDiv)
  }

  function lknInitCollapsibles () {
    const coll = document.getElementsByClassName('lkn-collapsible')
    let i

    for (i = 0; i < coll.length; i++) {
      coll[i].addEventListener('click', function () {
        this.classList.toggle('lkn-active')
        const content = this.nextElementSibling
        if (content.style.maxHeight) {
          content.style.maxHeight = null
          this.style = 'list-style: disclosure-closed;'
        } else {
          content.style.maxHeight = content.scrollHeight + 'px'
          this.style = 'list-style: disclosure-open;'
        }
      })
    }
  }
})(jQuery)
