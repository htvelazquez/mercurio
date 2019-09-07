/*
 * Copyright 2011 Google Inc. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

// This script serves as an intermediary between oauth2.html and oauth2.js.


// Get all query string parameters from the original URL.
var url = decodeURIComponent(window.location.href.match(/&from=([^&]+)/)[1]);
var index = url.indexOf('?');
if (index > -1) {
  url = url.substring(0, index);
}
index = url.indexOf('#');
if (index > -1) {
  url = url.substring(0, index);
}

// Derive adapter name from URI and then finish the process.
OAuth2.lookupAdapterName(url, function(adapterName) {
	new OAuth2(adapterName, OAuth2.FINISH, null, function(data, onFinish) {
		var adaptedData = {
			'access_token_date': data.accessTokenDate,
	        'access_token': data.accessToken,
	        'id': data.id,
	        'instance_url': data.instance_url,
	        'issued_at': data.issued_at,
	        'signature': data.signature,
		};

		$.ajax({
			url: remoteConfig.endpoints.account.register.url,
			cache: false,
			type: remoteConfig.endpoints.account.register.method,
			dataType: 'json',
			data: adaptedData,
			success:  function(response){
				console.log('Success:', response);
				onFinish.apply(null, [null]);
			},
			error: function (xhr, textStatus, errorThrown) {
			    switch (xhr.status) {
			        case 404: 
						alert("WebService not found (" + remoteConfig.endpoints.account.register.url + " - " + remoteConfig.endpoints.account.register.method + ")");
						onFinish.apply(null, [textStatus]);
						break;
			        default:
			        	console.log(xhr, textStatus, errorThrown);
			           	onFinish.apply(null, [textStatus]);
			           	break;
			    }
			}
		});
	});
});