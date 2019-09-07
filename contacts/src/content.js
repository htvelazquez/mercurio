/*jshint strict:false */
/* global getScraperNavigator:false, isSalesNavigator: false, WebServices: false*/

chrome.runtime.onMessage.addListener(function(request, sender) {
    console.info('[Content Script] Message Received', request);
    switch(request.type) {
        case 'CONTENT_SCRIPT_GET_LINKEDIN_DATA':

            getScraperNavigator(request.maxTimeout, function(data) {
                chrome.runtime.sendMessage({type: 'SET_LINKEDIN_DATA', linkedinData: data, extensionId: sender.id});
            }, false);
            break;

         case 'CONTENT_SCRIPT_DYNAMIC_CONFIG':
            // Event triggered once after injecting the image to measure the Network Latency
            if(request.config === 'latencyTimeout') {

                if(isSalesNavigator()) {
                    console.info('Using SalesNavigator');

                } else {
                    console.info('Using MicrosoftUI ');
                }
            }
            break;

        case 'CONTENT_SCRIPT_INJECT_BODY_SCRIPT':
            // Event triggered once on URL Change (from background page).
            jQueryScraper(document).unWaitAllSelector();
            jQueryScraper.getScript(chrome.extension.getURL('/js/remoteConfig.js'), function() {
                jQueryScraper.getScript(chrome.extension.getURL('/js/qm_content_script.js'));
            });
            break;

    }
});

jQueryScraper(document).unWaitAllSelector();
jQueryScraper.getScript(chrome.extension.getURL('/js/remoteConfig.js'), function() {
    jQueryScraper.getScript(chrome.extension.getURL('/js/qm_content_script.js'));
});
