if(typeof checkInterval == 'undefined'){
    const checkInterval = setInterval(() => {
        userAgentInput = document.getElementsByName('gatewayData[paymentUserAgent]')[0];
    
        if (userAgentInput) {
            checkoutLanguage = window.navigator.language.slice(0, 2);
            height = screen.height;
            width = screen.width;
            colorDepth = window.screen.colorDepth;
            userAgent = navigator.userAgent;
            date = new Date();
            timezoneOffset = date.getTimezoneOffset();
    
            deviceColorInput = document.getElementsByName('gatewayData[paymentColorDepth]')[0];
            langInput = document.getElementsByName('gatewayData[paymentLanguage]')[0];
            heightInput = document.getElementsByName('gatewayData[paymentHeight]')[0];
            widthInput = document.getElementsByName('gatewayData[paymentWidth]')[0];
            timezoneInput = document.getElementsByName('gatewayData[paymentTimezoneOffset]')[0];
    
            if (deviceColorInput && langInput && heightInput && widthInput && timezoneInput) {
                userAgentInput.value = userAgent;
                deviceColorInput.value = colorDepth;
                langInput.value = checkoutLanguage;
                heightInput.value = height;
                widthInput.value = width;
                timezoneInput.value = timezoneOffset;
    
                clearInterval(checkInterval);
            }
        }
    }, 1000);
}