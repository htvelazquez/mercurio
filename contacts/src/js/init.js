/*jshint strict:false */
/* global OAuth2:false */
/* exported initOauth */
var sfdcAuth; // Holds the Auth2 object

chrome.app._inject_scope = 'data_explorer_test';

function initOauth(authCallback) {
	sfdcAuth = new OAuth2(remoteConfig.oauth.driver, {
	  client_id: remoteConfig.oauth.consumer_key,
	  client_secret: remoteConfig.oauth.consumer_secret,
	  api_scope: remoteConfig.oauth.scope
	}, function(sfdcAuthInstance) {
		sfdcAuthInstance.authorize(function() {
			sfdcAuth.get(null, function(data) {
				authCallback.apply(this, [data.access_token]);
			});
		});
	});
}