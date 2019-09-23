/* jshint strict:false */
/* global $:false, WebServices:false */
var LinkedinProfileData;

function renderEmailDiv(LinkedinProfileData){

    $('#emailguess').show();
    if (LinkedinProfileData.email != null){
        $('#emailguess').val(LinkedinProfileData.email);
    }
    // $('#email-div .glyphicon-question-sign').show();
    $.LoadingOverlay('hide');

    $('#email-tips span').click(function(){
        //$('#email-options').toggle();
        if(!$('#email-options').is(':visible')) {
            $('#email-options p[method="nothing"]').hide();
            $('#email-options p[method="loading"]').show();
            $('#email-options').show();

            var executeAfterLoading = [];

            executeAfterLoading.push(function() {
                $('#email-options p[method="loading"]').hide();
            });

            if(LinkedinProfileData !== null && LinkedinProfileData.email) {
                $('#email-options p[method="linkedin"]').html('');
                $('#email-options p[method="linkedin"]').append('<span class="email-link"> Linkedin Email: ' + LinkedinProfileData.email + '</span>');
                $('#email-options p[method="linkedin"]').append(' <span style="color: yellowgreen;" class="glyphicon glyphicon-ok" hidden=""></span>');

                executeAfterLoading.push(function() {
                    $('#email-options p[method="linkedin"]').show();
                });

                //$('#email-options p[method="linkedin"]').addClass('list-group-item');
                $('#email-options p[method="linkedin"] .email-link').click(function() {
                    $('#emailguess').val(LinkedinProfileData.email);
                    $('#newemail').val(true);

                    executeAfterLoading.push(function() {
                        $('#email-options').hide();
                    });
                });
            }

            executeAfterLoading.push(function() {
                $('#email-options p[method="guess"]').show();
            });

            var suggestedEmail = null;
            var predeterminedEmailFormat = null;

            if($('#email-options').attr('predeterminedEmailFormat')) {
                predeterminedEmailFormat = $('#email-options').attr('predeterminedEmailFormat');
            }

            if(predeterminedEmailFormat !== null && predeterminedEmailFormat !== ''){
                var pattern = predeterminedEmailFormat.toLowerCase();
                if( pattern !== 'no pattern' && pattern !== 'other pattern') {

                    var firstName = LinkedinProfileData.firstName.split(' ');
                    firstName = firstName[0].replace('.', '').toLowerCase();

                    var lastName = LinkedinProfileData.lastName.split(' ');
                    lastName = lastName[0].toLowerCase();

                    firstName = replaceSpecialChars(firstName);
                    lastName = replaceSpecialChars(lastName);

                    firstName = cleanSpecialChars(firstName);
                    lastName = cleanSpecialChars(lastName);

                    suggestedEmail =  predeterminedEmailFormat
                        .replace('{first.name}', firstName)
                        .replace('{last.name}', lastName)
                        .replace('{first.initial}', firstName[0])
                        .replace('{last.initial}', lastName[0]);
                }
            }

            if(suggestedEmail !== null) {

                $('#email-options p[method="suggested"]').html('');
                $('#email-options p[method="suggested"]').append('<span class="email-link">Suggested Email: ' + suggestedEmail + '</span>');

                executeAfterLoading.push(function() {
                    $('#email-options p[method="suggested"]').show();
                });

                var attachClickAction = function() {
                    $('#email-options p[method="suggested"] .email-link').click(function(){
                        $('#emailguess').val(suggestedEmail);
                        $('#newemail').val(true);

                        executeAfterLoading.push(function() {
                            $('#email-options').hide();
                        });
                    });
                };

            } else {

                var visible = false;
                $('#email-options p[method]').each(function() {
                    if($(this).attr('method') !== 'loading') {
                        visible = visible || $(this).is(':visible');
                    }
                });

                if(!visible) {
                    $('#email-options p[method="nothing"]').show();
                }

                applyCallbackArray(executeAfterLoading);
            }


        } else {
            $('#email-options').hide();
        }
    });
}

function applyCallbackArray(callbacks, context, params) {
    if(typeof context === 'undefined') {
        context = null;
    }

    if(typeof params === 'undefined') {
        params = [];
    }

    for(var i = 0; i < callbacks.length;i++) {
        callbacks[i].apply(context, params);
    }
}

function cleanSpecialChars(str){
    if (typeof(str) == 'undefined') return '';
    return str.replace(/[^a-zA-Z]/ig,'');
}

function replaceSpecialChars(str){

    var replaceChars = {'\u00e0':'a','\u00e8':'e','\u00ec':'i','\u00f2':'o','\u00f9':'u','\u00f1':'n',
                        '\u00e1':'a','\u00e9':'e','\u00ed':'i','\u00f3':'o','\u00fa':'u','\u00e7':'c',
                        '\u00e2':'a','\u00ea':'e','\u00ee':'i','\u00f4':'o','\u00fb':'u',
                        '\u00e3':'a','\u00eb':'e','\u00ef':'i','\u00f5':'o','\u00fc':'u',
                        '\u00e4':'a',                          '\u00f6':'o',
                        '\u00e5':'a',

                        '\u00c0':'A',
                        '\u00c1':'A','\u00c8':'E','\u00cc':'I','\u00d2':'O','\u00d9':'U','\u00d1':'N',
                        '\u00c2':'A','\u00c9':'E','\u00cd':'I','\u00d3':'O','\u00da':'U','\u00c7':'C',
                        '\u00c3':'A','\u00ca':'E','\u00ce':'I','\u00d4':'O','\u00db':'U',
                        '\u00c4':'A','\u00cb':'E','\u00cf':'I','\u00d5':'O','\u00dc':'U',
                        '\u00c5':'A',                          '\u00d6':'O'
                        };

    for (var x in replaceChars) {

        console.debug(x + ' -> ' + replaceChars[x] );

        str = str.replace(new RegExp(x, 'g'), replaceChars[x]);
    }

    return str;
}

function renderStatus(statusText, alertType) {
    typeClass = (alertType === null)? 'alert-warning' : 'alert-'+alertType;
    // $('#status').html(statusText).removeClass('alert-warning','alert-success','alert-danger').addClass(typeClass).how();

    var status = document.getElementById('status');
    status.textContent = statusText;
    status.hidden = false;
    status.className = typeClass;
    $.LoadingOverlay('hide');
}

function appendStatus(text) {
    var status = document.getElementById('status');
    status.textContent = status.textContent + text;
    status.hidden = false;
    $.LoadingOverlay('hide');
}


function uploadContactButtonAction(){
    event.preventDefault();
    $('#upload-button').prop('disabled', true);

    renderStatus('Checking data...','warning');
    if ($("#emailguess").val() != '' && LinkedinProfileData.email != $("#emailguess").val()){
      LinkedinProfileData.email = $("#emailguess").val();
    }

    if ($("#website").val() != '' && LinkedinProfileData.website != $("#website").val()){
      LinkedinProfileData.website = $("#website").val();
    }

    renderStatus('Saving to Cirenio...','warning');

    WebServices.pushContactData(LinkedinProfileData, function (response) {
            $('#upload-button').addClass('hidden');
            renderStatus('Contact pushed','success');
        },
        function(error){
            $('#upload-button').prop('disabled', false);
            renderStatus(error,'danger');
        }
    );
}

function trimTwitterHandle( twitter ){
    if (typeof(twitter) == 'undefined' || twitter == null || twitter == '') return '';
    var re = /^(https?:\/\/)?(www\.)?(twitter\.com\/)?(\w+)$/gs;
    var match = re.exec(twitter);
    if (typeof(match) == 'undefined' || match == null || match == '') return '';
    return match[4].toLowerCase();
}

// When the popup HTML has loaded
window.addEventListener('load', function() {
    // Get the event page

    $(document).ready(function() {
        $.LoadingOverlaySetup({image: 'images/overlay_loader.gif'});
        $.LoadingOverlay('show');
        chrome.runtime.getBackgroundPage(function() {
            chrome.tabs.query({active: true, currentWindow: true}, function() {
                chrome.runtime.sendMessage({type: 'LOAD_LINKEDIN_DATA'});
                chrome.runtime.onMessage.addListener(function(request){
                    console.info('[Popup] Message Received', request);
                    switch(request.type) {
                        case 'POPUP_LINKEDIN_DATA':
                            hydrateContactForm(request.linkedinData);
                            break;
                    }
                });
            });
        });
    });
});

function hydrateContactForm(linkedinProfileData) {
    $("#upload-button").click(uploadContactButtonAction);

    LinkedinProfileData = linkedinProfileData;

    $('#twitter').on('blur', function(){
        $('#twitter').val(trimTwitterHandle( $('#twitter').val() ));
    })

    var linkedinToken = linkedinProfileData.id;
    if(!linkedinToken) {
        renderStatus('Cannot find Linkedin Token in the current page','danger');
        return;
    }

    $('#name-div .help-block').hide();

    $('#firstname').prop('disabled', false);
    $('#lastname').prop('disabled', false);
    $('#accountname').prop('disabled', false);

    $('#firstname').val(linkedinProfileData.firstName);
    $('#lastname').val(linkedinProfileData.lastName);

    $('#twitter').val(linkedinProfileData.twitter);
    $('#website').val(linkedinProfileData.website);
    $('#birthday').val(linkedinProfileData.birthday);
    $('#phone').val(linkedinProfileData.phone);

    $('#title').val(linkedinProfileData.title);
    $('#contactName').val(linkedinProfileData.name);

    $('#upload-button').removeClass('hidden');

    // If the first company has Linkedin Company Token
    if(linkedinProfileData.companyData.id !== '' && linkedinProfileData.companyData.id !== null) {
        $('#account-div .help-block').hide();
        $('#accountname').val(linkedinProfileData.companyData.companyName);

        renderEmailDiv(linkedinProfileData);
    } else {
        // errorCallback.apply(null, ['Unable to load Current Company from Linkedin']);
    }

    $('#contact-form').show();
    $.LoadingOverlay('hide');
}
