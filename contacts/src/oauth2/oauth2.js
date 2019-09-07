/* exported OAuth2 */
/*
 * Copyright 2011 Google Inc. All Rights Reserved.

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


/**
 * Constructor
 *
 * @param {String} adapterName  name of the adapter to use for this OAuth 2
 * @param {Object} config Containing clientId, clientSecret and apiScope
 * @param {String} config Alternatively, OAuth2.FINISH for the finish flow
 */
var OAuth2 = function(adapterName, config, onCreated, onRegistered) {
    this.adapterName = adapterName;
    var that = this;

    OAuth2.loadAdapter(adapterName, function() {
        that.adapter = OAuth2.adapters[adapterName];
        if (config == OAuth2.FINISH) {
          that.finishAuth(onRegistered);
        } else if (config) {
            that.getSource(function(source) {
                //console.log('updateLocalStorage::source' , source)
                if(source === null) {
                    var data = {
                        clientId: config.client_id,
                        clientSecret: config.client_secret,
                        apiScope: config.api_scope
                    };

                    console.log('UPDATE_LOCAL_STORAGE_PRE_SET_SOURCE', data);
                    that.setSource(data, function() {
                        onCreated.apply(that, [that]);
                    });
                } else {
                    onCreated.apply(that, [that]);
                }

            });
        }
    });
};

/**
 * Pass instead of config to specify the finishing OAuth flow.
 */
OAuth2.FINISH = 'finish';

/**
 * OAuth 2.0 endpoint adapters known to the library
 */
OAuth2.adapters = {};
OAuth2.adapterReverse = {};
/*OAuth2.adapterReverse = localStorage.oauth2_adapterReverse &&
    JSON.parse(localStorage.oauth2_adapterReverse) || {};
// Update the persisted adapterReverse in localStorage.
if (localStorage.adapterReverse) {
  OAuth2.adapterReverse = JSON.parse(localStorage.adapterReverse);
  // @updated (New Local Storage)
  //delete localStorage.adapterReverse;
  deleteInLocalStorage('adapterReverse')
}*/

/**
 * Consolidates the data stored in localStorage on the current adapter in to
 * a single JSON object.
 * The update should only ever happen once per adapter and will delete the old
 * obsolete entries in localStorage after copying their values.
 */
OAuth2.prototype.updateLocalStorage = function(callback) {
  // Check if update is even required.
  var context = this;
  this.getSource(function(source) {
    console.log('updateLocalStorage::source' , source)
    if(source === null) {
        //console.log('updateLocalStorage::source is null');
        var onUpdateSuccess = function(data) {

            console.log('onUpdateSuccess with', data);
            this.setSource(data, function() {
                callback.apply(this, []);
            });
        }

        var data = {};
        var variables = [
            'accessToken', 'accessTokenDate', 'apiScope', 'clientId', 'clientSecret',
            'expiresIn', 'refreshToken'
        ];

        // Check if a variable has already been persisted and then copy them.
        var key;
        var pendingVariables = variables.length;
        for (var i = 0; i < variables.length; i++) {
            key = this.adapterName + '_' + variables[i];

            if (localStorage.hasOwnProperty(key)) {
                console.log('DELETING: ', key, ' with value ', localStorage[key]);
                data[variables[i]] = localStorage[key];
        
                deleteInLocalStorage(key, function() {
                    pendingVariables--;

                    if(pendingVariables === 0) {
                        //console.log('onUpdateSuccess::preApply-1', data);
                        onUpdateSuccess.apply(context, [data]);
                    }
                });

            } else {
                pendingVariables--;
            
                if(pendingVariables === 0) {
                  //console.log('onUpdateSuccess::preApply-2', data);
                   onUpdateSuccess.apply(context, [data]);
                }
            }
        }
    } else {
        callback.apply(context, []);
    }
  });
};

/**
 * Opens up an authorization popup window. This starts the OAuth 2.0 flow.
 *
 * @param {Function} callback Method to call when the user finished auth.
 */
OAuth2.prototype.openAuthorizationCodePopup = function(callback) {
  // Store a reference to the callback so that the newly opened window can call
  // it later.
  window['oauth-callback'] = callback;
  console.log('openAuthorizationCodePopup::oauth-callback', callback.toString());

  // Create a new tab with the OAuth 2.0 prompt
  this.getConfig(function(config) {
    /*console.log('TAB_URL: ', this.adapter.authorizationCodeURL(config));*/
    chrome.tabs.create({url: this.adapter.authorizationCodeURL(config)},
    function(tab) {
        // 1. user grants permission for the application to access the OAuth 2.0
        // endpoint
        // 2. the endpoint redirects to the redirect URL.
        // 3. the extension injects a script into that redirect URL
        // 4. the injected script redirects back to oauth2.html, also passing
        // the redirect URL
        // 5. oauth2.html uses redirect URL to know what OAuth 2.0 flow to finish
        // (if there are multiple OAuth 2.0 adapters)
        // 6. Finally, the flow is finished and client code can call
        // myAuth.getAccessToken() to get a valid access token.
    });
  });
};

/**
 * Gets access and refresh (if provided by endpoint) tokens
 *
 * @param {String} authorizationCode Retrieved from the first step in the process
 * @param {Function} callback Called back with 3 params:
 *                            access token, refresh token and expiry time
 */
OAuth2.prototype.getAccessAndRefreshTokens = function(authorizationCode, callback) {
  var that = this;
  // Make an XHR to get the token
  var xhr = new XMLHttpRequest();
  xhr.addEventListener('readystatechange', function(event) {
    if (xhr.readyState == 4) {
      if (xhr.status == 200) {
        // Callback with the data (incl. tokens).
        callback(that.adapter.parseAccessToken(xhr.responseText));
      }
    }
  });

  var method = that.adapter.accessTokenMethod();

  that.getConfig(function(config) {
    var items = that.adapter.accessTokenParams(authorizationCode, config);
    var key = null;
    
    if (method == 'POST') {
        var formData = new FormData();
        
        for (key in items) {
            formData.append(key, items[key]);
        }

        xhr.open(method, that.adapter.accessTokenURL(), true);
        xhr.send(formData);

    } else if (method == 'GET') {
        var url = that.adapter.accessTokenURL();
        var params = '?';
    
        for (key in items) {
            params += encodeURIComponent(key) + '=' +
            encodeURIComponent(items[key]) + '&';
        }
    
        xhr.open(method, url + params, true);
        xhr.send();

    } else {
        throw method + ' is an unknown method';
    }
  });
};

/**
 * Refreshes the access token using the currently stored refresh token
 * Note: this only happens for the Google adapter since all other OAuth 2.0
 * endpoints don't implement refresh tokens.
 *
 * @param {String} refreshToken A valid refresh token
 * @param {Function} callback On success, called with access token and expiry time
 */
OAuth2.prototype.refreshAccessToken = function(refreshToken, callback) {
  var xhr = new XMLHttpRequest();
  xhr.onreadystatechange = function(event) {
    if (xhr.readyState == 4) {
      if(xhr.status == 200) {
        console.log(xhr.responseText);
        // Parse response with JSON
        var obj = JSON.parse(xhr.responseText);
        // Callback with the tokens
        callback(obj.access_token, obj.expires_in);
      }
    }
  };

  this.get(null, function(data) {
    var formData = new FormData();
    formData.append('client_id', data.clientId);
    formData.append('client_secret', data.clientSecret);
    formData.append('refresh_token', refreshToken);
    formData.append('grant_type', 'refresh_token');
    xhr.open('POST', this.adapter.accessTokenURL(), true);
    xhr.send(formData);
  });
};

/**
 * Extracts authorizationCode from the URL and makes a request to the last
 * leg of the OAuth 2.0 process.
*/
OAuth2.prototype.finishAuth = function(onRegistered) {
    console.log('OK-finish');
    var authorizationCode = null;
    var that = this;
    
    // Loop through existing extension views and excute any stored callbacks.
    function callback(error) {
      
        that.get(null, function(data) {
          if(onRegistered !== null) {
            onRegistered.apply(null, [data, function(err) {
            if(!err) {
                // Once we get here, close the current tab and we're good to go.
                // The following works around bug: crbug.com/84201
                window.open('', '_self', '');
                window.close();
                chrome.runtime.sendMessage({type: 'LOGIN_SUCCESS'});
              }
            }]);
          }
        });
    }

    authorizationCode = that.adapter.parseAuthorizationCode(window.location.href);
    console.log('OK-finish-2');
    console.log(authorizationCode);
    console.log('OK-finish-3');

    try {
        authorizationCode = that.adapter.parseAuthorizationCode(window.location.href);
        console.log(authorizationCode);
    } catch (e) {
        console.error(e);
        callback(e);
    }

    that.getAccessAndRefreshTokens(authorizationCode, function(response) {
        that.get(null, function(data) {
            data.accessTokenDate = new Date().valueOf();

            // Set all data returned by the OAuth 2.0 provider.
            for (var name in response) {
              if (response.hasOwnProperty(name) && response[name]) {
                data[name] = response[name];
              }
            }

            that.setSource(data, callback);
        });
    });
};

/**
 * @return True iff the current access token has expired
 */
OAuth2.prototype.isAccessTokenExpired = function(callback) {
  this.get(null, function(data) {
    var expired = (new Date().valueOf() - data.accessTokenDate) > data.expiresIn * 1000;
    callback.apply(null, [expired]);
  });
};

/**
 * Get the persisted adapter data in localStorage. Optionally, provide a
 * property name to only retrieve its value.
 *
 * @param {String} [name] The name of the property to be retrieved.
 * @return The data object or property value if name was specified.
 */
OAuth2.prototype.get = function(name, callback) {
  this.getSource(function(src) {
    //console.log('GET::' + name, src);
    var obj = src ? JSON.parse(src) : {};
    callback.apply(null, [name ? obj[name] : obj]);
  });
};

/**
 * Set the value of a named property on the persisted adapter data in
 * localStorage.
 *
 * @param {String} name The name of the property to change.
 * @param value The value to be set.
 */
OAuth2.prototype.set = function(name, value, callback) {
  this.get(null, function(obj) {
    obj[name] = value;
    this.setSource(obj, callback);
  });
};

/**
 * Clear all persisted adapter data in localStorage. Optionally, provide a
 * property name to only clear its value.
 *
 * @param {String} [name] The name of the property to clear.
 */
OAuth2.prototype.clear = function(name, callback) {
  if (name) {
    this.get(null, function(obj) {
        delete obj[name];
        this.setSource(obj, callback);
    });
  } else {
    deleteInLocalStorage('oauth2_' + this.adapterName, callback);
  }
};

/**
 * Get the JSON string for the object stored in localStorage.
 *
 * @return {String} The source JSON string.
 */
OAuth2.prototype.getSource = function(callback) {
  //return localStorage['oauth2_' + this.adapterName];
  //console.log('getSource::' + 'oauth2_' + this.adapterName);
  getFromLocalStorage('oauth2_' + this.adapterName, callback);
};

/**
 * Set the JSON string for the object stored in localStorage.
 *
 * @param {Object|String} source The new JSON string/object to be set.
 */
OAuth2.prototype.setSource = function(source, callback) {

  //console.log('setSource::', source);
  if (!source) {
    return;
  }

  if (typeof source !== 'string') {
    source = JSON.stringify(source);
  }

  //console.log('setSource::' + 'oauth2_' + this.adapterName, source);

  // @updated (New Local Storage)
  //localStorage['oauth2_' + this.adapterName] = source;
  saveInLocalStorage('oauth2_' + this.adapterName, source, callback);
};

/**
 * Get the configuration parameters to be passed to the adapter.
 *
 * @returns {Object} Contains clientId, clientSecret and apiScope.
 */
OAuth2.prototype.getConfig = function(callback) {
    var context = this;
    this.get(null, function(data) {
        //console.log('GET_CONFIG_RAW_DATA', data);
        callback.apply(context, [{
            clientId: data.clientId,
            clientSecret: data.clientSecret,
            apiScope: data.apiScope
        }]);
    });
};

/***********************************
 *
 * STATIC ADAPTER RELATED METHODS
 *
 ***********************************/

/**
 * Loads an OAuth 2.0 adapter and calls back when it's loaded
 *
 * @param adapterName {String} The name of the JS file
 * @param callback {Function} Called as soon as the adapter has been loaded
 */
OAuth2.loadAdapterCallback = null;
OAuth2.loadAdapter = function(adapterName, callback) {
  // If it's already loaded, don't load it again
  if (OAuth2.adapters[adapterName]) {
    callback();
    return;
  }
  var head = document.querySelector('head');
  var script = document.createElement('script');
  script.type = 'text/javascript';
  script.src = '/oauth2/adapters/' + adapterName + '.js';
  OAuth2.loadAdapterCallback = callback;
  script.addEventListener('load', function() {
    // When the adapter is loaded, OAuth2::adapter is called, at this point we trigger the callback
    // previously setted in OAuth2.loadAdapterCallback
  });
  head.appendChild(script);
};

/**
 * Registers an adapter with the library. This call is used by each adapter
 *
 * @param {String} name The adapter name
 * @param {Object} impl The adapter implementation
 *
 * @throws {String} If the specified adapter is invalid
 */
OAuth2.adapter = function(name, impl, registerCallback) {
  var implementing = 'authorizationCodeURL redirectURL accessTokenURL ' +
    'accessTokenMethod accessTokenParams accessToken';

  // Check for missing methods
  implementing.split(' ').forEach(function(method, index) {
    if (!method in impl) {
      throw 'Invalid adapter! Missing method: ' + method;
    }
  });

  // Save the adapter in the adapter registry
  OAuth2.adapters[name] = impl;
  // Make an entry in the adapter lookup table
  OAuth2.adapterReverse[impl.redirectURL()] = name;
  // Store the the adapter lookup table in localStorage
  // @updated (New Local Storage)
  //localStorage.oauth2_adapterReverse = JSON.stringify(OAuth2.adapterReverse);
  saveInLocalStorage('oauth2_adapterReverse', JSON.stringify(OAuth2.adapterReverse), function() {
    OAuth2.loadAdapterCallback.apply(null, []);
    OAuth2.loadAdapterCallback = null;
  });
};

/**
 * Looks up the adapter name based on the redirect URL. Used by oauth2.html
 * in the second part of the OAuth 2.0 flow.
 *
 * @param {String} url The url that called oauth2.html
 * @return The adapter for the current page
 */
OAuth2.lookupAdapterName = function(url, callback) {
    getFromLocalStorage('oauth2_adapterReverse', function(adapterReverse) {
        adapterReverse = JSON.parse(adapterReverse);
        callback.apply(null, [adapterReverse[url]]);
    });
};

/***********************************
 *
 * PUBLIC API
 *
 ***********************************/

/**
 * Authorizes the OAuth authenticator instance.
 *
 * @param {Function} callback Tries to callback when auth is successful
 *                            Note: does not callback if grant popup required
 */
OAuth2.prototype.authorize = function(callback) {
    var that = this;
    OAuth2.loadAdapter(that.adapterName, function() {
        that.adapter = OAuth2.adapters[that.adapterName];
        that.get(null, function(data) {

            //console.log('DATA', data);
            if (!data.accessToken) {
                // There's no access token yet. Start the authorizationCode flow
                that.openAuthorizationCodePopup(function() {
                  callback.apply(null, [null, true]); // true = tokenChange
                });
            } else {
                that.isAccessTokenExpired(function(isExpired) {
                    if(isExpired) {
                        // There's an existing access token but it's expired
                        if (data.refreshToken) {
                            that.refreshAccessToken(data.refreshToken, function(at, exp) {
                                that.get(null, function(newData) {
                                    newData.accessTokenDate = new Date().valueOf();
                                    newData.accessToken = at;
                                    newData.expiresIn = exp;
                              
                                    that.setSource(newData, function() {
                                        // Callback when we finish refreshing
                                        if (callback) {
                                            callback.apply(null, [null, true]); // true = tokenChange
                                        }
                                    });
                                });
                           });
                        } else {
                            // No refresh token... just do the popup thing again
                            that.openAuthorizationCodePopup(function() {
                              callback.apply(null, [null, true]); // true = tokenChange
                            });
                        }
                    } else {
                        // We have an access token, and it's not expired yet
                        if (callback) {
                            callback.apply(null, [null, false]);
                        }
                    }
                });
            }
        });
    });
};

/**
 * @returns A valid access token.
 */
OAuth2.prototype.getAccessToken = function(callback) {
  this.get('accessToken', function(accessToken) {
    callback.apply(null, [accessToken]);
  });
};

/**
 * Indicate whether or not a valid access token exists.
 *
 * @returns {Boolean} True if an access token exists; otherwise false.
 */
OAuth2.prototype.hasAccessToken = function(callback) {
  this.get('accessToken', function(accessToken) {
    callback.apply(null, [!!accessToken]);
  });
};

/**
 * Clears an access token, effectively "logging out" of the service.
 */
OAuth2.prototype.clearAccessToken = function(callback) {
  this.clear('accessToken', callback);
};
