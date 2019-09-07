/* jshint strict:false */
/* global LinkedinUIDataExtractor:false, Promise:false */
/* exported SalesNavigatorDataExtractor */
var SalesNavigatorDataExtractor = {

    init: function(maxTimeout, done, onlyMemberToken) {
        var partialObject = {
            id: null,
            title: null,
            name: null,
            firstName: null,
            lastName: null,
            publicProfileURL: null,
            experience: null,
            fullExperience: null,
            location: null,
            email: null,
            twitter: null,
            birthday: null,
            website: null,
            phone: null,
            totalConnections: null,
            skills: [],
            languages: null,
            education: null,
            peopleSimilar: null,
            scrapedImages: null,
            connectionDegree: null,
            connections: null,
            salesNavigator: true,
            summary: null,
            companyData: {
                id: null,
                companyName: null,
                companyURL: null
            }
        };

        var self = this;
        var dataPromises = [];
        dataPromises.push(new Promise(function onTopCardLoaded(resolve, reject) {
            jQueryScraper('#topcard th.linkedin-logo').waitForSelector(function() {
                resolve({
                    id: self.getLinkedinToken(),
                    title: self.getTitle(),
                    name: self.getMemberName(),
                    firstName: self.getFirstName(),
                    lastName: self.getLastName(),
                    publicProfileURL: self.getPublicProfile(),
                    location: self.getLocation(),
                    email: self.getEmail(),
                    twitter: self.getTwitter(),
                    phone: self.getPhone(),
                    birthday: self.getBirthday(),
                    website: self.getWebsite(),
                    totalConnections: self.getTotalConnections(),
                    connectionDegree: self.getConnectionDegree(),
                    summary: self.getSummary()
                });
            }, function() {
                resolve({
                    id: self.getLinkedinToken(),
                    title: self.getTitle(),
                    name: self.getMemberName(),
                    firstName: self.getFirstName(),
                    lastName: self.getLastName(),
                    publicProfileURL: self.getPublicProfile(),
                    location: self.getLocation(),
                    email: self.getEmail(),
                    totalConnections: self.getTotalConnections(),
                    connectionDegree: self.getConnectionDegree(),
                    summary: self.getSummary()
                });
            }, maxTimeout);
        }));

        if(onlyMemberToken === false) {
            dataPromises.push(new Promise(function onExperienceLoaded(resolve, reject) {
                jQueryScraper('#topcard').waitForSelector(function() {

                    var experience = self.getExperience(true);
                    resolve({
                        languages: self.getLanguages(),
                        education: self.getEducation(),
                        experience: self.getExperience(true),
                        fullExperience: self.getExperience(false),
                        companyData: {
                            companyName: experience.length > 0? experience[0].companyName : null,
                            companyURL: experience.length > 0? experience[0].companyLink : null,
                            id: experience.length > 0? experience[0].id : null
                        }
                    });
                }, function(){
                    reject('Experience never loaded');
                }, maxTimeout);
            }));

            // dataPromises.push(new Promise(function onPeopleSimilarLoaded(resolve) {
            //     jQueryScraper('#people-also-viewed ul.people li').waitForSelector(function() {
            //         resolve({
            //             peopleSimilar: self.getPeopleSimilar()
            //         });
            //     }, null, maxTimeout);
            // }));

            // dataPromises.push(new Promise(function onConnectionsLoaded(resolve) {
            //     jQueryScraper('#connections .connection-list li.entity .body').waitForSelector(function() {
            //         resolve({
            //             connections: self.getConnections(),
            //             scrapedImages: self.getScrapedImages()
            //         });
            //     }, function() {
            //         resolve({
            //             connections: [],
            //             scrapedImages: []
            //         });
            //     }, maxTimeout);
            // }));
        }

        Promise
            .all(dataPromises)
            .then(function(data) {
                for(var i = 0; i < data.length; i++) {
                    console.log('DATA_LINE', data[i]);
                    partialObject = Object.assign(partialObject, data[i]);
                }

                done(partialObject);
            });
        },

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
        var currentExperience = this.getExperience(true);
        if(currentExperience.length > 0) {
            return currentExperience[0];
        }

        return {id: '', name: ''};
    },

    getExperience: function(currentOnly) {
        var jobs = document.querySelectorAll('#experience li.position');
        var currentExperience = [];

        for(var i = 0; i < jobs.length; i++) {

            if(jobs[i].querySelectorAll('span.meta').length > 0) {
                if(currentOnly === false || this.isCurrentJob(jobs[i])) {
                    var linkURL = null;
                    var companyName = null;
                    var link = jobs[i].querySelector('header.position-info .company-name a.link');

                    var from = '';
                    var to = '';
                    var experienceDate = jobs[i].querySelectorAll('span.meta')[0].innerText;
                    const regex = /([a-zA-z]*\s{1}[0-9]{4})\s{1}-{1}\s{1}([a-zA-z]*(\s[0-9]{4})?)/g;
                    var fromTo = regex.exec(experienceDate);

                    if (fromTo !== null) {
                        if (fromTo[1] !== undefined) {
                            from = fromTo[1];
                        }

                        if (fromTo[2] !== undefined) {
                            to = fromTo[2];
                        }
                    }

                    if(link !== null) {
                        linkURL = link.getAttribute('href');
                        companyName = link.innerText.trim();

                        var match = linkURL.match(/\/sales\/accounts\/insights\?companyId=(\d+)/i);
                        if(match !== null) {
                            if (currentOnly) {
                                currentExperience.push({
                                    id: match[1],
                                    name:companyName,
                                    jobTitle: jobs[i].querySelector('.position-title.sub-headline').innerText.trim(),
                                    companyName: companyName,
                                    companyLink: linkURL,
                                    since: from
                                });
                            } else {
                                currentExperience.push({
                                    id: match[1],
                                    name:companyName,
                                    jobTitle: jobs[i].querySelector('.position-title.sub-headline').innerText.trim(),
                                    companyName: companyName,
                                    companyLink: linkURL,
                                    from: from,
                                    to: to
                                });
                            }
                        }
                    }
                }
            }
        }

        return currentExperience;
    },

    getEmail: function(){
        var email;
        try {
            jQueryScraper('#topcard .more-info-tray tr').each(function() {
                if(jQueryScraper(this).find('th').text().trim().toLowerCase().match(/email/gi)) {
                    email = jQueryScraper(this).find('td').text().trim();
                }
            });

        } catch(err){
            email = null;
        }

        return email;
    },

    getMemberName: function(){
        return LinkedinUIDataExtractor.getMemberName();
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

    getPeopleSimilar: function(){
        var peopleSimilar = [];
        jQueryScraper('#people-also-viewed ul.people li.person').each(function() {

            var $url = jQueryScraper(this).find('a');
            var matching = $url.attr('href').match(/\/sales\/profile\/(\d+)/i);
            if(matching !== null) {
                peopleSimilar.push({
                    // hash: urlParam('id', $url.attr('href')),
                    lId: matching[1],
                    name: jQueryScraper(this).find('.profile-info .name a').text().trim(),
                    url: $url.attr('href'),
                    desc: jQueryScraper(this).find('.profile-info .current-title').text().trim(),
                    type: 'PEOPLE_ALSO_VIEWED'
                });
            }
        });

        return peopleSimilar;
    },

    getLinkedinToken: function(){
        if(jQueryScraper('div.profile-actions input[name="memberId"]').length > 0){
                return jQueryScraper('div.profile-actions input[name="memberId"]').val()
        }
        return LinkedinUIDataExtractor.getLinkedinToken();
    },

    getTitle: function() {
        return LinkedinUIDataExtractor.getTitle();
    },

    getPublicProfile: function(){
       var elem = jQueryScraper('#topcard th.linkedin-logo');
       if (elem.length > 0) {
          return jQueryScraper('#topcard th.linkedin-logo').parent().find('td a').first().attr('href');
       } else {
          return window.location.href.split('?')[0];
       }
    },

    getLocation: function getLocation(){
        return LinkedinUIDataExtractor.getLocation();
    },

    getSkills: function getSkills() {
        return [];
    },

    getTwitter: function () {
        if (jQueryScraper('#topcard div.module-footer a[href^="https://www.twitter.com"]').length > 0) {
            return jQueryScraper('#topcard div.module-footer a[href^="https://www.twitter.com"]').text();
        }
        return null;
    },

    getPhone: function () {
        var phone = null;
        try {

            jQueryScraper('#topcard .more-info-tray tr').each(function () {
                var label = jQueryScraper(this).find('th').text().trim();
                if(label.toLowerCase().match(/phone/gi)) {
                    phone = jQueryScraper(this).find('td ul li').text().trim();
                }
            });
        } catch(err){
            phone = null;
        }

        return phone;
    },

    getBirthday: function () {
        return null;
    },

    getWebsite: function () {
        var website = null;
        try {

            jQueryScraper('#topcard .more-info-tray tr').each(function () {
                var label = jQueryScraper(this).find('th').text().trim();
                if(label.toLowerCase().match(/websites/gi)) {
                    website = jQueryScraper(this).find('td ul li a').attr('href').replace("http://", "").replace("https://", "");
                }
            });
        } catch(err){
            website = null;
        }

        return website;
    },

    getLanguages: function getLanguages() {
        var langs = [];

        if(jQueryScraper('#background #language').length > 0) {
            jQueryScraper('#background #language li.language .language-name').each(function() {
                langs.push(jQueryScraper(this).text().trim().toLowerCase());
            });
        }

        return langs;
    },

    getEducation: function getEducation() {
        var education = [];
        if(jQueryScraper('#background #education li.school').length > 0) {
            jQueryScraper('#background #education li.school').each(function() {
                var $item = jQueryScraper(this);
                var schooURL = $item.find('.school-name a').attr('href');
                education.push({
                    school: schooURL,
                    schoolName: $item.find('.school-name').text().trim(),
                    degree: $item.find('.degree').text().trim(),
                    degreeURL: null,
                    date: $item.find('.start-graduate').text().trim()
                });
            });
        }

        return education;
    },

    getTotalConnections: function getTotalConnections() {
        return jQueryScraper('#topcard .info-container .connection-info .connections-badge').text().trim();
    },

    getScrapedImages: function getScrapedImages() {
        var scrapedImages = [];

        var selector = '#top-card .module-body img[data-li-src]';
        if(jQueryScraper(selector).length > 0) {
            scrapedImages.push({
                image: jQueryScraper(selector).attr('data-li-srd'),
                name: this.getMemberName(),
                jobTitle: jQueryScraper('#topcard .profile-info li.title').text().trim(),
                location: 'PROFILE_IMAGE'
            });
        }

        selector = '#people-also-viewed .people .person';
        if(jQueryScraper(selector).length > 0) {
            jQueryScraper(selector).each(function() {
                scrapedImages.push({
                    image: jQueryScraper(this).find('.entity-card img').attr('src'),
                    name: jQueryScraper(this).find('.entity-card img').attr('alt'),
                    jobTitle: jQueryScraper(this).find('.profile-info .current-title').text().trim(),
                    location: 'PEOPLE_ALSO_VIEWED'
                });
            });
        }

        selector = '#connections .connection-list .entity';
        if(jQueryScraper(selector).length > 0) {
            jQueryScraper(selector).each(function() {
                scrapedImages.push({
                    image: jQueryScraper(this).find('.profile img').attr('src'),
                    name: jQueryScraper(this).find('.profile img').attr('alt'),
                    jobTitle: jQueryScraper(this).find('.profile .headline').text().trim(),
                    location: 'CONNECTIONS'
                });
            });
        }

        selector = '#recommendations .recommenders .recommender';
        if(jQueryScraper(selector).length > 0) {
            jQueryScraper(selector).each(function() {
                scrapedImages.push({
                    image: jQueryScraper(this).find('.recommender-image').attr('src'),
                    name: jQueryScraper(this).find('.recommender-image').attr('alt'),
                    jobTitle: jQueryScraper(this).find('.comments .headline').text().trim(),
                    location: 'RECOMMENDATION_RECEIVED'
                });
            });
        }

        return scrapedImages;
    },

    getConnectionDegree: function getConnectionDegree() {
        return jQueryScraper('#profile .info-container .degree-icon').text().trim().replace(jQueryScraper('#profile .info-container .degree-icon sup').text().trim(), '');
    },

    getConnections: function getConnections() {
        var retConnections = [];

        jQueryScraper('#connections .connection-list li.entity .body').each(function() {
            var $link = jQueryScraper(this).find('.name a');
            retConnections.push({
                hashId: null,
                name: $link.text().trim(),
                connId: null,
                degree: jQueryScraper(this).find('abbr.degree-icon').text().trim(),
            });
        });

        return retConnections;
    },

    getSummary: function getSummary() {
        return jQueryScraper('#summary .description').text().trim();
    }
};
