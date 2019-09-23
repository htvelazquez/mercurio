/*jshint strict:false */
/* exported urlParam, getScraperNavigator*/
/* global LinkedinUIDataExtractor:false, SalesNavigatorDataExtractor:false, MicrosoftUIDataExtractor:false */
// Common Functions
function urlParam(variable, from) {
    if(typeof from === 'undefined' || from === null) {
        console.log('ERROR::urlParam -> Requesting "' + variable + ' over null "from"');
        return false;
    }

    var query = from.split('?', 2)[1];
    var result = {};
    query.split('&').forEach(function(part) {
        var item = part.split('=');
        result[item[0]] = decodeURIComponent(item[1]);
    });

    if(typeof result[variable] !== 'undefined') {
        return result[variable];
    }

    return false;
}


function getScraperNavigator(maxTimeout, done, onlyMemberToken) {

    if(typeof onlyMemberToken === 'undefined') {
        onlyMemberToken = false;
    }

    if(MicrosoftUIDataExtractor.isMicrosoftUI()) {
        console.info('[UI] Microsoft UI');
        MicrosoftUIDataExtractor.init(maxTimeout, done, onlyMemberToken);

    // } else if(isSalesNavigator()) {
    //     console.info('[UI] SalesNavigator');
    //     SalesNavigatorDataExtractor.init(maxTimeout, done, onlyMemberToken);

    } else if(isSalesNavigatorMS()) {
        console.info('[UI] SalesNavigator Microsoft v1');
        SalesNavigatorMS1DataExtractor.init(maxTimeout, done, onlyMemberToken);

    } else {
        console.info('[UI] Old UI');
        LinkedinUIDataExtractor.init(done);
    }
}

function isSalesNavigator() {
    return window.location.href.match(/^https:\/\/(www\.)?linkedin.com\/sales\/profile/) !== null;
}

function isSalesNavigatorMS() {
    return window.location.href.match(/^https:\/\/(www\.)?linkedin.com\/sales\/people/) !== null;
}
