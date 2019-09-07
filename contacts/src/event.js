/*jshint strict:false*/
/*global remoteConfig:false*/
/* exported getProfileDetails */
//INAWAREWEBSERVICE = 'http://inaware4.insidevault.com';

//
// URL-filter to make the script run only on linkedin profile page.
//
chrome.runtime.onInstalled.addListener(function() {
    // Replace all rules ...
    chrome.declarativeContent.onPageChanged.removeRules(undefined, function() {
        // With a new rule ...
        chrome.declarativeContent.onPageChanged.addRules([
            {
                // That fires when a page's URL contains 'linkedin.com' ...
                conditions: [
                    new chrome.declarativeContent.PageStateMatcher({
                        pageUrl: { urlMatches: 'linkedin\.com((\/sales\/(profile|people)\/.+)|(\/in\/.+))' }
                    })
                ],
                // And shows the extension's page action.
                actions: [new chrome.declarativeContent.ShowPageAction()]
            }
        ]);
    });
});

//
// Data-Proxy ContentScript & Popup
//
// var bandwithImageURLRegex = remoteConfig.bandwithImageURL.replace(/^https?:\/\//, '*://') + '*';
var trackingResource = null;
var latencyTimeout = null;
var skipMessages = ['POPUP_LINKEDIN_DATA'];
var lastTabOpen = null;
var userEmail = null;

chrome.runtime.onMessage.addListener(function(request) {
    if(skipMessages.indexOf(request.type) === -1) {
        console.info('[Background Page] Message Received', request);

        chrome.identity.getProfileUserInfo(function(userInfo) {
            userEmail = userInfo.email;
        });

        switch(request.type) {
            case 'LOAD_LINKEDIN_DATA':
                chrome.tabs.query({active: true, currentWindow: true}, function(tabs) {
                    chrome.tabs.sendMessage(tabs[0].id, {type: 'CONTENT_SCRIPT_GET_LINKEDIN_DATA'});
                });
                break;

            case 'SET_LINKEDIN_DATA':
                chrome.runtime.sendMessage({type: 'POPUP_LINKEDIN_DATA', linkedinData: request.linkedinData});
                break;

            // case 'LOGIN_SUCCESS':
            //     console.log('reopen tab: ' + lastTabOpen);
            //     chrome.tabs.update(lastTabOpen, {active: true});
            //     break;

            // 'POPUP_LINKEDIN_DATA' is not in this switch, because it should be trapped on popup.js. It's inside
            // skipMessages array to avoid confusing log data.
        }
    }
});

//
// URL Change listener
//
chrome.tabs.onUpdated.addListener(function (tabId, changeInfo) {
    if (changeInfo.status === 'complete') {
        chrome.tabs.sendMessage(tabId, {type: 'CONTENT_SCRIPT_INJECT_BODY_SCRIPT'});
    }
});
