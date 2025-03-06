document.addEventListener("DOMContentLoaded", function () {
    let observer = new MutationObserver(() => {
        let iframe = document.querySelector('iframe[name="give-embed-form"]');
        if (iframe) {
            let iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
            if (iframeDoc) {
                let script = iframeDoc.createElement("script");
                script.id = "lknPaymentEredeForGivewpSetPaymentInfo";
                script.src = wpApiSettings.root.replace('/wp-json/', '/wp-content/plugins/payment-erede-for-givewp/Public/js/lknPaymentEredeForGivewpSetPaymentInfoMultiStep.js');
                script.async = true;
                iframeDoc.head.appendChild(script);
            }
        }
    });

    observer.observe(document.body, { childList: true, subtree: true });
});
