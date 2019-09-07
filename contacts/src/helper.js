/*jshint strict:false */
var NormalNavigator = {
    getCompanyId: function(){
        var el = document.getElementsByClassName('position-info')[0] ;
        var id;
        if( el){
            id = Number(el.getElementsByClassName('link')[0].href.replace(/[^=]+=/i, ''));
        }else{
            try{ id = Number(document.getElementsByClassName('current-position')[0].getElementsByTagName('a')[0].href.replace(/^.*\/company\/(\d+)\??.*/i,'$1')); }
            catch(err){
                id = null;
            }
        }
        return id;
    },

    getCompanyData: function() {
        var jobs = document.querySelectorAll('#background-experience .section-item');

        for(var i = 0; i < jobs.length; i++) {
            if(jobs[i].querySelectorAll('.experience-date-locale time').length === 1) {

                var linkURL = null;
                var companyName = null;
                var $link = jobs[i].querySelectorAll('header h5 span.new-miniprofile-container a');
                if($link.length > 0) {
                    linkURL = $link[0].getAttribute('href').split('?')[0].split('#')[0];
                    companyName = $link[0].innerText.trim();
                } else {

                    $link = jobs[i].querySelectorAll('header h5 a');
                    if($link.length > 0) {
                        linkURL = $link[0].getAttribute('href');
                        companyName = $link[0].innerText.trim();
                    }
                }

                var match = linkURL.match(/\/?company\/(\d+)/i);
                if(match !== null) {
                    return ({
                        id: match[1],
                        name:companyName,
                        jobTitle: jobs[i].querySelector('h4 a').innerText.trim(),
                        companyName: companyName,
                        companyLink: linkURL,
                        since: jobs[i].querySelector('.experience-date-locale time').innerText
                    });
                }
            }
        }

        return {id: '', name: ''};
    },

    getEmail: function(){
        var email;
        try{
            email = document.getElementById('email-view').getElementsByTagName('a')[0].text;
        }catch(err){
            email = null;
        }
        return email;
    },

    getMemberName: function(){
        var el = document.getElementsByClassName('member-name')[0];
        if( !(el) ){
            el = document.getElementsByClassName('full-name')[0];
        }
        if(el){
            return el.textContent;
        }else{
            return null;
        }
    },

    getLinkedinToken: function(){
        var token;
        var url = window.location.href;
        token = Number(url.replace(/https:\/\/www\.linkedin\.com\/sales\/profile\/(\d+),.*/i, '$1'));
        if(!token){
            try {
                var shareContext = JSON.parse(document.getElementById('__qmContextDataShare').text.replace('var __qmContextDataShare=', ''));
                token = shareContext.memberId;
            }
            catch (error){
                token = null;
            }
        }
        return token;
    },

    getTitle: function() {
        var el = document.getElementsByClassName('position-title')[0];
        var name;
        if (el) {
            name = el.textContent;
        } else {
            try {
                el = Array.prototype.filter.call(document.getElementsByClassName('current-position')[0].getElementsByTagName('a'), function (member) {
                    return ( (member.href.search('https://www.linkedin.com/title/') !== -1));
                })[0];
                name = el.text;
            }
            catch (err) {
                name = null;
            }
        }
        return name;
    },

    getPublicProfile: function(){
        var el = document.getElementsByClassName('view-public-profile')[0];
        if( el){
            return el.href;
        }else {
            el = document.getElementsByClassName('more-info-tray')[0].getElementsByTagName('a')[0].text;
            if( el){
                try {
                    el = Array.prototype.filter.call(el.getElementsByTagName('a'), function(el){ return ( (el.href) && (el.href.search('linkedin.com') !== -1)  && (el.href.search('@') === -1) ); })[0];
                    return el.href();
                }
                catch (err){
                    return null;
                }
            }else {
                return null;
            }
        }
        return null;
    },

    getLocation: function getLocation(){
        var el = document.getElementById('topcard');
        if( el ){
            return el.getElementsByClassName('location')[0].textContent;
        } else{
            return document.getElementById('location').getElementsByTagName('a')[0].text;
        }
    }
};

var SalesNavigator = {
    getCompanyId: function(){
        var currentJobs = this.getCurrentJobs();

        if(currentJobs.id.length !== 0) {
            return currentJobs.id;
        }

        return null;
    },

    isCurrentJob: function(job) {
        // For the current job we lookup if there is at least one span.meta that starts with a valid
        // Date and ends with and Invalid Date
        //var isCurrentJob = false;
        var jobMetas = job.querySelectorAll('span.meta');
        for(var m = 0; m < jobMetas.length; m++) {
            if(jobMetas[m].innerText) {

                var metaText = jobMetas[m].innerText.split('|')[0].trim();
                metaText = metaText.replace(/\(.+\)/i, '').trim();
                var metaTextArray = metaText.split(/ \- /g);
                if(metaTextArray.length === 2) {

                    var startDate = Date.parse(metaTextArray[0]);
                    var endDate = Date.parse(metaTextArray[1]);

                    if(!isNaN(startDate) && isNaN(endDate)) {
                        return true;
                    }
                }
            }
        }

        return false;
    },

    getCompanyData: function() {
        var jobs = document.querySelectorAll('#experience li.position');
        //var currentJobs = [];

        for(var i = 0; i < jobs.length; i++) {

            if(jobs[i].querySelectorAll('span.meta').length > 0) {
                if(this.isCurrentJob(jobs[i])) {
                    var linkURL = null;
                    var companyName = null;
                    var link = jobs[i].querySelector('header.position-info .company-name a.link');

                    if(link !== null) {
                        linkURL = link.getAttribute('href');
                        companyName = link.innerText.trim();

                        var match = linkURL.match(/\/sales\/accounts\/insights\?companyId=(\d+)/i);
                        if(match !== null) {
                            return {
                                id: match[1],
                                name:companyName,
                                jobTitle: jobs[i].querySelector('.position-title.sub-headline').innerText.trim(),
                                companyName: companyName,
                                companyLink: linkURL
                            };
                        }
                    }
                }
            }
        }

        return {id: '', name: ''};
    },

    getEmail: function(){
        return NormalNavigator.getEmail();
    },

    getMemberName: function(){
        return NormalNavigator.getMemberName();
    },

    getLinkedinToken: function(){
        return NormalNavigator.getLinkedinToken();
    },

    getTitle: function() {
        return NormalNavigator.getTitle();
    },

    getPublicProfile: function(){
       return NormalNavigator.getPublicProfile();
    },

    getLocation: function getLocation(){
        return NormalNavigator.getLocation();
    }
};

var currentNavigator = NormalNavigator;
if(window.location.href.match(/^https:\/\/(www\.)?linkedin.com\/sales\/profile/) !== null) {
    currentNavigator = SalesNavigator;
}

// Send a message containing the page details back to the event page
chrome.runtime.sendMessage({
    'action' : 'getPopUpInfo',
    'memberName': currentNavigator.getMemberName(),
    'title': currentNavigator.getTitle(),
    'linkedinToken' : currentNavigator.getLinkedinToken(),
    'companyData' : currentNavigator.getCompanyData(),
    'email' : currentNavigator.getEmail(),
    'publicProfile' : currentNavigator.getPublicProfile(),
    'location' : currentNavigator.getLocation()
});
