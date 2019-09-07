/* jshint strict:false */
/* exported SalesNavigatorDataExtractor */
var SalesNavigatorDataExtractor = {

    init: function(done) {
        var partialObject = {
            lId: this.getLinkedinToken(),
            title: this.getTitle(),
            name: this.getMemberName(),
            firstName: this.getFirstName(),
            lastName: this.getLastName(),
            experience: this.getExperience(true),
            fullExperience: this.getExperience(false),
            location: this.getLocation(),
            email: this.getEmail(),
            twitter: this.getTwitter(),
            phone: this.getPhone(),
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

    getCompanyId: function () {
        var currentJobs = this.getCurrentJobs();

        if(currentJobs.id.length !== 0) {
            return currentJobs.id;
        }

        return null;
    },

    isCurrentJob: function (job) {
        // For the current job we lookup if there is at least one span.meta that starts with a valid
        // Date and ends with and Invalid Date
        //var isCurrentJob = false;

        var jobMetas = job.querySelectorAll('span.meta');
        for (var m = 0; m < jobMetas.length; m++) {
            if (jobMetas[m].innerText.length > 0) {
                var metaText = jobMetas[m].innerText.split('|')[0].trim();
                metaText = metaText.replace(/\(.+\)/i, '').trim();
                var metaTextArray = metaText.split(/ \- /g);

                if (metaTextArray.length === 2) {
                    var startDate = Date.parse(metaTextArray[0]);
                    var endDate = Date.parse(metaTextArray[1]);

                    if (!isNaN(startDate) && isNaN(endDate)) {
                        return true;
                    }
                }
            }
        }

        return false;
    },

    getCompanyData: function () {
        var currentExperience = this.getExperience(true);
        if (currentExperience.length > 0) {
            return currentExperience[0];
        }

        return {id: '', name: ''};
    },

    getExperience: function (currentOnly) {
        var jobs = document.querySelectorAll('#experience li.position');
        var currentExperience = [];

        for (var i = 0; i < jobs.length; i++) {
            if (jobs[i].querySelectorAll('span.meta').length > 0) {
                var isCurrentJob = this.isCurrentJob(jobs[i]);
                if (currentOnly === false || (currentOnly === true && isCurrentJob)) {
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

                    if (link !== null) {
                        linkURL = link.getAttribute('href');
                        companyName = link.innerText.trim();

                        var match = linkURL.match(/\/sales\/accounts\/insights\?companyId=(\d+)/i);
                        if (match !== null) {
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

    getEmail: function () {
        var email;
        try {
            jQueryScraper('#topcard .more-info-tray tr').each(function () {
                if(jQueryScraper(this).find('th').text().trim().toLowerCase().match(/email/gi)) {
                    email = jQueryScraper(this).find('td').text().trim();
                }
            });

        } catch(err){
            email = null;
        }

        return email;
    },

    getMemberName: function () {
        var el = document.getElementsByClassName('member-name')[0];
        if (!(el)) {
            el = document.getElementsByClassName('full-name')[0];
        }

        if (el) {
            return el.textContent;
        } else {
            return null;
        }
    },

    getFirstName: function () {
        var arrName = this.getMemberName().split(' ');
        if (arrName.length < 4) {
            return arrName[0];
        } else {
            return arrName[0] + ' ' + arrName[1];
        }
    },

    getLastName: function () {
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

    getTwitter: function () {
        if (jQueryScraper('#topcard div.module-footer a[href^="https://www.twitter.com"]').length > 0) {
            return jQueryScraper('#topcard div.module-footer a[href^="https://www.twitter.com"]').text();
        }
        return null;
    },

    getPhone: function () {
        var phone;
        try {
            jQueryScraper('#topcard .more-info-tray tr').each(function () {
                if(jQueryScraper(this).find('th').text().trim().toLowerCase().match(/phone/gi)) {
                    phone = jQueryScraper(this).find('td').text().trim();
                }
            });

        } catch(err){
            phone = null;
        }

        return phone;
    },

    getPeopleSimilar: function () {
        var peopleSimilar = [];
        jQueryScraper('#people-also-viewed ul.people li.person').each(function () {

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

    getLinkedinToken: function () {
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

    getTitle: function () {
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

    getLocation: function getLocation() {
        var el = document.getElementById('topcard');
        if (el){
            return el.getElementsByClassName('location')[0].textContent;
        } else{
            return document.getElementById('location').getElementsByTagName('a')[0].text;
        }
    },

    getSkills: function getSkills() {
        var skills = [];

        if (jQueryScraper('#skills ul.skills li.skill').length > 0) {
            jQueryScraper('#skills ul.skills li.skill').each(function (index) {
                skills.push({
                    id: jQueryScraper(this).text().trim().toLowerCase().replace(/[^\w ]+/g,'').replace(/ +/g,'-'),
                    endorsements: null
                });
            });
        }

        return skills;
    },

    getLanguages: function getLanguages() {
        var langs = [];

        if(jQueryScraper('#language').length > 0) {
            jQueryScraper('#language ul li h2').each(function() {
                langs.push(jQueryScraper(this).text().trim().toLowerCase());
            });
        }

        return langs;
    },

    getEducation: function getEducation() {
        var education = [];

        if(jQueryScraper('#education ol li.school').length > 0) {
            jQueryScraper('#education ol li.school').each(function () {
                var $item = jQueryScraper(this);
                var schooURL = $item.find('h2.school-name a').attr('href');

                education.push({
                    school: schooURL,
                    schoolName: $item.find('h2.school-name').text().trim(),
                    degree: $item.find('h3.degree').text().trim(),
                    degreeURL: $item.find('h3.degree a').length > 0 ? $item.find('h3.degree a').attr('href') : null,
                    date: $item.find('.start-graduate').text().trim()
                });
            });
        }

        return education;
    },

    getTotalConnections: function getTotalConnections() {
        var connections = jQueryScraper('.connection-info .connections-badge');
        if (connections.length > 0) {
            return connections.text().trim();
        }

        return null;
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
            jQueryScraper(selector + ' li').each(function () {
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
            jQueryScraper(selector + ' li').each(function () {
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
            jQueryScraper(selector + ' .endorsement-full').each(function () {
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
        return jQueryScraper('.profile-info .degree-icon').text().replace(jQueryScraper('.profile-info .degree-icon sup').text(), '').trim();
    },

    getConnections: function getConnections() {
        var retConnections = [];

        jQueryScraper('#connections .connection-list li.entity').each(function () {
            var $link = jQueryScraper(this).find('h3.name a');
            var $image = jQueryScraper(this).find('a img.image');
            
            retConnections.push({
                name: $link.text().trim(),
                salesNavigatorUrl: $link.attr('href'),
                imageUrl: $image.attr('src'),
                imageBasename: $image.attr('src').split('/').reverse()[0],
                degree: jQueryScraper(this).find('abbr.degree-icon').text().trim(),
                jobTitle: jQueryScraper(this).find('p.headline').text().trim()
            });
        });

        return retConnections;
    }
};
