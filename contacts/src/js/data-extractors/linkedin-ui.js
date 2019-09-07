/*jshint strict:false */
/* global urlParam: false */
/*exported LinkedinUIDataExtractor*/
var LinkedinUIDataExtractor = {

    init: function(done) {
        var partialObject = {
            id: this.getLinkedinToken(),
            title: this.getTitle(),
            name: this.getMemberName(),
            firstName: this.getFirstName(),
            lastName: this.getLastName(),
            publicProfileURL: this.getPublicProfile(),
            experience: this.getExperience(true),
            fullExperience: this.getExperience(false),
            getLocation: this.getLocation(),
            email: this.getEmail(),
            skills: this.getSkills(),
            languages: this.getLanguages(),
            education: this.getEducation(),
            totalConnections: this.getTotalConnections(),
            peopleSimilar: this.getPeopleSimilar(),
            scrapedImages: this.getScrapedImages(),
            connectionDegree: this.getConnectionDegree(),
            connections: this.getConnections()
        };

        partialObject.companyData = {
            id: null,
            companyName: null,
            companyURL: null
        };

        if(partialObject.experience.length > 0) {
            partialObject.companyData.companyName = partialObject.experience[0].companyName;
            partialObject.companyData.companyURL = partialObject.experience[0].companyLink;
            partialObject.companyData.id = partialObject.experience[0].id;
        }

        done(partialObject);
    },

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
        var currentExperience = this.getExperience(true);
        if(currentExperience.length > 0) {
            return currentExperience[0];
        }

        return {id: '', name: ''};
    },

    getExperience: function(currentOnly) {
        var jobs = document.querySelectorAll('#background-experience .section-item');
        var currentExperience = [];

        for(var i = 0; i < jobs.length; i++) {
            if(currentOnly === false || (currentOnly === true && jobs[i].querySelectorAll('.experience-date-locale time').length === 1)) {

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
                    currentExperience.push({
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

        return currentExperience;
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

    getFirstName: function(){
        var arrName = this.getMemberName().split(' ');
        if (arrName.length < 4){
            return arrName[0];
        } else {
            return arrName[0] + ' ' + arrName[1];
        }
    },

    getLastName: function(){
        var arrName = this.getMemberName().split(' ');
        if (arrName.length < 4){
            arrName.shift();
            return arrName.join(' ');
        } else {
            arrName.shift();
            arrName.shift();
            return arrName.join(' ');
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
    },

    getSkills: function getSkills() {
        var skills = [];
        var endorsersJSONText = jQueryScraper('*:contains("EndorserIds"):last').text().replace(/.+facePileEndorserIds: ?(\[\[([^\]]+])+]).+/i, '$1');

        if(endorsersJSONText.length > 0) {
            var endorsersIndex = JSON.parse(endorsersJSONText);
            if(jQueryScraper('#background-skills #profile-skills li[data-endorsed-item-name]').length > 0) {
                jQueryScraper('#background-skills #profile-skills li[data-endorsed-item-name]').each(function(index) {
                    var skillURL = jQueryScraper(this).find('.endorse-item-name a').attr('href');

                    if(typeof skillURL !== 'undefined') {
                        skills.push({
                            id: jQueryScraper(this).find('.endorse-item-name a').text().trim() .toLowerCase().replace(/[^\w ]+/g,'').replace(/ +/g,'-'),
                            endorsements: jQueryScraper(this).find('.num-endorsements').text().trim() * 1,
                            url: skillURL,
                            from: (typeof endorsersIndex[index] !== 'undefined') ? endorsersIndex[index] : []
                        });
                    }
                });
            }
        }

        return skills;
    },

    getLanguages: function getLanguages() {
        var langs = [];

        if(jQueryScraper('#background-languages #languages').length > 0) {
            jQueryScraper('#background-languages #languages ol li h4 span').each(function() {
                langs.push(jQueryScraper(this).text().trim().toLowerCase());
            });
        }

        return langs;
    },

    getEducation: function getEducation() {
        var education = [];
        if(jQueryScraper('#background-education > div').length > 0) {
            jQueryScraper('#background-education > div').each(function() {
                var $item = jQueryScraper(this).find('div[id^="education-"][id$="-view"] div header');
                var schooURL = $item.find('h4.summary a').attr('href');
                education.push({
                    school: schooURL,
                    schoolName: $item.find('h4.summary a').text().trim(),
                    degree: $item.find('h5 span').text().trim(),
                    degreeURL: $item.find('h5 span a').length > 0 ? $item.find('h5 span a').attr('href') : null,
                    date: $item.parent().find('.education-date').text().trim()
                });
            });
        }

        return education;
    },

    getTotalConnections: function getTotalConnections() {
        var connections = jQueryScraper('#top-card .member-connections a.connections-link');
        if(connections.length > 0) {
            return connections.text().trim();
        } else {

            connections = jQueryScraper('#top-card  .member-connections strong');
            if(connections.length > 0 ) {
                return connections.text().trim();
            }
        }

        return null;
    },

    getPeopleSimilar: function getPeopleSimilar() {
        var peopleSimilar = [];
        jQueryScraper('.discovery-panel ol.discovery-results li .discovery-detail').each(function() {

            var $url = jQueryScraper(this).find('dt > a');
            peopleSimilar.push({
                hash: urlParam('id', $url.attr('href')),
                lId: urlParam('key', jQueryScraper(this).find('.take-action a.connect').attr('href')),
                name: $url.text().trim(),
                url: $url.attr('href'),
                desc: jQueryScraper(this).find('.take-action-headline').text().trim(),
                type: 'PEOPLE_SIMILAR'
            });
        });

        return peopleSimilar;
    },

    getScrapedImages: function getScrapedImages() {
        var scrapedImages = [];

        var selector = '#top-card .profile-card.vcard .profile-picture a img';
        if(jQueryScraper(selector).length > 0) {
            scrapedImages.push({
                image: jQueryScraper('#top-card .profile-card.vcard .profile-picture a img').attr('src'),
                name: jQueryScraper('#top-card .profile-card.vcard .profile-picture a img').attr('alt'),
                jobTitle: jQueryScraper('#headline-container').text().trim(),
                location: 'PROFILE_IMAGE'
            });
        }

        selector = '.insights-browse-map ul';
        if(jQueryScraper(selector).length > 0) {
            jQueryScraper(selector + ' li').each(function() {
                scrapedImages.push({
                    image: jQueryScraper(this).find('a.browse-map-photo img').data('li-src'),
                    name: jQueryScraper(this).find('a.browse-map-photo img').attr('alt'),
                    jobTitle: jQueryScraper(this).find('p.browse-map-title').text(),
                    location: 'PEOPLE_ALSO_VIEWED'
                });
            });
        }

        selector = '.discovery-panel .discovery-results';
        if(jQueryScraper(selector).length > 0) {
            jQueryScraper(selector + ' li').each(function() {
                scrapedImages.push({
                    image: jQueryScraper(this).find('a.discovery-photo img').data('li-src'),
                    name: jQueryScraper(this).find('.discovery-detail dt a').text().trim(),
                    jobTitle: jQueryScraper(this).find('.discovery-detail dd').attr('title'),
                    location: 'PEOPLE_SIMILAR'
                });
            });
        }

        selector = '#endorsements .endorsements-container .endorsements-received';
        if(jQueryScraper(selector).length > 0) {
            jQueryScraper(selector + ' .endorsement-full').each(function() {
                scrapedImages.push({
                    image: jQueryScraper(this).find('.endorsement-picture a img').attr('src'),
                    name: jQueryScraper(this).find('.endorsement-picture a img').attr('alt'),
                    jobTitle: jQueryScraper(this).find('.endorsement-info hgroup h6').text().trim(),
                    location: 'ENDORSEMENTS_RECEIVED'
                });
            });
        }

        return scrapedImages;
    },

    getConnectionDegree: function getConnectionDegree() {
        return jQueryScraper('.account-icons .degree-icon').text().replace(jQueryScraper('.account-icons .degree-icon sup').text(), '').trim();
    },

    getConnections: function getConnections() {
        var retConnections = [];

        jQueryScraper('#connections-view .cardstack-container ul li').each(function() {
            var $link = jQueryScraper(this).find('a.connections-name');
            retConnections.push({
                hashId: urlParam('id', $link.attr('href')),
                name: $link.text().trim(),
                connId: jQueryScraper(this).attr('id').replace(/^connection\-/i, ''),
                degree: jQueryScraper(this).find('abbr.degree-icon').text().trim(),
            });
        });

        return retConnections;
    }
};
