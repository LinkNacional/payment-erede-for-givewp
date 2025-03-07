document.addEventListener('DOMContentLoaded', function() {
    const language = window.navigator.language.slice(0, 2)
    const height = screen.height
    const width = screen.width
    const colorDepth = window.screen.colorDepth
    const userAgent = navigator.userAgent
    const date = new Date()
    const timezoneOffset = date.getTimezoneOffset()

    const userAgentInput = document.getElementsByName('gatewayData[paymentUserAgent]')[0]
    const deviceColorInput = document.getElementsByName('gatewayData[paymentColorDepth]')[0]
    const langInput = document.getElementsByName('gatewayData[paymentLanguage]')[0]
    const heightInput = document.getElementsByName('gatewayData[paymentHeight]')[0]
    const widthInput = document.getElementsByName('gatewayData[paymentWidth]')[0]
    const timezoneInput = document.getElementsByName('gatewayData[paymentTimezoneOffset]')[0]

    if (userAgentInput && deviceColorInput && langInput && heightInput && widthInput && timezoneInput) {
        userAgentInput.value = userAgent
        deviceColorInput.value = colorDepth
        langInput.value = language
        heightInput.value = height
        widthInput.value = width
        timezoneInput.value = timezoneOffset
    }
});