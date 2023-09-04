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
})(jQuery)
