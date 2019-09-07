/* global remoteConfig:false, jQueryScraper:false, $:false, getFromLocalStorage: false, saveInLocalStorage: false*/
/* exported WebServices*/
/* jshint strict:false */

var WebServices = {

    pushContactData: function(contact, callback, errorCallback){
        this.queryWebService(
            remoteConfig.endpoints.contact.push.url,
            remoteConfig.endpoints.contact.push.method,
            remoteConfig.endpoints.contact.apikey,
            contact,
            callback,
            errorCallback
        );
    },

    queryWebService: function(endpoint, method, apikey, data, callback, errorCallback, callbackNotFound, retries) {
        var self = this;
        if(retries === null) {
            retries = 3;
        }

        console.log(endpoint);
        var jQueryHandler = null;
        if(typeof jQueryScraper !== 'undefined') {
            jQueryHandler = jQueryScraper;
        } else {
            jQueryHandler = $;
        }

        jQueryHandler.ajax({
            url: endpoint,
            cache: false,
            type: method,
            dataType: 'json',
            contentType: 'application/json',
            headers: { "x-api-key": apikey },
            data: JSON.stringify(data),
            success:  function(response){
                callback.apply(null, [response]);
            },
            error: function (xhr, textStatus, errorThrown) {
                console.log('API returned', xhr.status, textStatus, errorThrown);
                switch (xhr.status) {
                    case 404:
                        if(callbackNotFound === null) {
                            callback.apply(null, [null]);
                        } else {
                            if(callbackNotFound) {
                                callbackNotFound.apply(null, [null]);
                            }
                        }
                        break;

                    case 401:
                        errorCallback.apply(null, ['Invalid API-Key given']);
                        // deleteInLocalStorage('oauth2_' + sfdcAuth.adapterName, function() {
                        //     console.log('source_cleanup');
                        //     initOauth(function() {
                        //         console.log('initOauth::callback');
                        //         if(retries > 0) {
                        //             retries--;
                        //             self.queryWebService.apply(self, [endpoint, method, data, callback, errorCallback, callbackNotFound, retries]);
                        //         } else {
                        //             window.alert('Max re-login attemps tries');
                        //         }
                        //
                        //     });
                        // });
                        break;

                    default:
                      console.log(xhr, textStatus, errorThrown);
                      if(errorCallback !== null) {
                          errorCallback.apply(null, ['Unexpected response']);
                      }
                      break;
                }
            }
        });
    },

};
