/* jshint strict:false */
/* global LinkedinUIDataExtractor:false, Promise:false */
/* exported SalesNavigatorDataExtractor */
var MicrosoftUIDataExtractor = {

    datajson : null,
    contactjson: null,

    isMicrosoftUI: function() {
        return jQueryScraper('body.render-mode-BIGPIPE').length > 0;
    },

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

/*        dataPromises.push(new Promise(function onContactInfoLoaded(resolve, reject) {
            var urlParts = window.location.pathname.replace(/^\/|\/$/gi, '').split('/');
            var publicIdentifier = decodeURIComponent(urlParts[1]);

            var settings = {
              "async": true,
              "crossDomain": true,
              "url": "https://www.linkedin.com/voyager/api/identity/profiles/"+publicIdentifier+"/profileContactInfo",
              "method": "GET",
              "headers": {
                "x-li-lang": "en_US",
                "accept-language": "en-US,en;q=0.9,es;q=0.8",
                "x-li-track": "{\"clientVersion\":\"1.5.*\",\"osName\":\"web\",\"timezoneOffset\":-3,\"deviceFormFactor\":\"DESKTOP\",\"mpName\":\"voyager-web\"}",
                "pragma": "no-cache",
                "x-li-page-instance": "urn:li:page:d_flagship3_profile_view_base_contact_details;CcBGo6CGS4y+++KZfS8qAA==",
                "accept": "application/vnd.linkedin.normalized+json+2.1",
                "cache-control": "no-cache,no-cache",
                "x-restli-protocol-version": "2.0.0",
                "authority": "www.linkedin.com",
                "csrf-token": "ajax:8646173978434193927"
              }
            }

            jQueryScraper.ajax(settings).done(function(response) {
                self.contactjson = response.data;

                console.log(self.contactjson);
                resolve({
                    email: self.getEmail(),
                    twitter: self.getTwitter(),
                    phone: self.getPhone(),
                    birthday: self.getBirthday(),
                    website: self.getWebsite()
                });
            });
        }));*/

        dataPromises.push(new Promise(function onTopCardLoaded(resolve, reject) {
            jQueryScraper('body.render-mode-BIGPIPE').waitForSelector(function() {

                self.datajson = self.getDataJson();

                if(self.datajson === false) {
                    reject('Missing Member Token, try refreshing the page.');
                }

                console.log(self.datajson);

                resolve({
                    id: self.getLinkedinToken(), //
                    title: self.getTitle(), //
                    name: self.getMemberName(), //
                    firstName: self.getFirstName(), //
                    lastName: self.getLastName(), //
                    publicProfileURL: self.getPublicProfile(), //
                    location: self.getLocation(), //
                    totalConnections: self.getTotalConnections(),
                    connectionDegree: self.getConnectionDegree(),
                    summary: self.getSummary(), //
                    experience: self.getExperience(true), //
                    fullExperience: self.getExperience(false), //
                    companyData: self.getCompanyData()
                });
            }, function() {
                resolve({
                    id: self.getLinkedinToken(), //
                    title: self.getTitle(), //
                    name: self.getMemberName(), //
                    firstName: self.getFirstName(), //
                    lastName: self.getLastName(), //
                    publicProfileURL: self.getPublicProfile(), //
                    location: self.getLocation(), //
                    totalConnections: self.getTotalConnections(),
                    connectionDegree: self.getConnectionDegree(),
                    summary: self.getSummary(),
                    experience: self.getExperience(true), //
                    fullExperience: self.getExperience(false), //
                    companyData: self.getCompanyData()
                });
            }, maxTimeout);
        }));


        Promise
            .all(dataPromises)
            .then(function(data) {
                for(var i = 0; i < data.length; i++) {
                    console.log('DATA_LINE', data[i]);
                    partialObject = Object.assign(partialObject, data[i]);
                }

                done(partialObject);
            }).catch(function(errors) {
                console.error('Failed Promises');
                console.error(errors);
                alert(JSON.stringify(errors));
            });
        },

    getDataJson: function(){
        var urlParts = window.location.pathname.replace(/^\/|\/$/gi, '').split('/');
        var publicIdentifier = decodeURIComponent(urlParts[1]);
        var $code = jQueryScraper("code:contains('\"publicIdentifier\":\""+publicIdentifier+"\"')");

        if($code.length > 0) {
            return JSON.parse($code.first().text().trim());
        }

        return false;
    },

    getCompanyId: function(){
        var companyData = this.getCompanyData();
        if(companyData.id != '')
            companyData.id

        return null;
    },

    getCompanyData: function() {
        var currentExperience = this.getExperience(true);
        if(currentExperience.length > 0){
            return {
               companyName: currentExperience[0].companyName,
               companyURL: currentExperience[0].companyLink,
               id: currentExperience[0].id
           };
        }

        return {id: '', companyName: '', companyURL: ''};
    },

    getExperience: function(currentOnly) {
        var month = new Array();
        month[1] = "Jan"; month[2] = "Feb"; month[3] = "Mar"; month[4] = "Apr"; month[5] = "May"; month[6] = "Jun";
        month[7] = "Jul"; month[8] = "Aug"; month[9] = "Sep"; month[10] = "Oct"; month[11] = "Nov"; month[12] = "Dec";

        var experience = [];

        if (this.datajson.included){
            for(var item in this.datajson.included) {
                if ( this.datajson.included[item].$type && this.datajson.included[item].$type == 'com.linkedin.voyager.identity.profile.Position' ){
                    var current = false;

                    var companyId   = (this.datajson.included[item].companyUrn)? this.datajson.included[item].companyUrn.replace('urn:li:fs_miniCompany:','') : null;
                    var jobTitle    = (this.datajson.included[item].title)? this.datajson.included[item].title : null;
                    var companyName = (this.datajson.included[item].companyName)? this.datajson.included[item].companyName : null;
                    var location    = (this.datajson.included[item].geoLocationName)? this.datajson.included[item].geoLocationName : null;

                    if (this.datajson.included[item].timePeriod && this.datajson.included[item].timePeriod.endDate){
                        var endDate = this.datajson.included[item].timePeriod.endDate;
                        var toMonth = (endDate.month)? endDate.month : 1;
                        var toYear = (endDate.year)? endDate.year : null;
                        var to = (toMonth)? month[toMonth]+' '+toYear : '';
                    }else{
                        current = true;
                    }

                    if (this.datajson.included[item].timePeriod && this.datajson.included[item].timePeriod.startDate){
                        var startDate = this.datajson.included[item].timePeriod.startDate;
                        var fromMonth = (startDate.month)? startDate.month : 1;
                        var fromYear = (startDate.year)? startDate.year : null;
                        var from = (toMonth)? month[fromMonth]+' '+fromYear : '';
                    }

                    if (!currentOnly || current){
                        if (currentOnly) {
                            experience.push({
                                id:         companyId,
                                name:       companyName,
                                jobTitle:   jobTitle,
                                companyName:companyName,
                                companyLink:(companyId)? 'https://www.linkedin.com/sales/company/'+companyId : null,
                                location: location,
                                since: from
                            });
                        }else{
                            experience.push({
                                id:         companyId,
                                name:       companyName,
                                jobTitle:   jobTitle,
                                companyName:companyName,
                                companyLink:(companyId)? 'https://www.linkedin.com/sales/company/'+companyId : null,
                                location: location,
                                from: from,
                                to: to
                            });
                        }

                    }

                }
            }
        }

        return experience;
    },

    getEmail: function(){
        if (this.contactjson.emailAddress){
            return this.contactjson.emailAddress;
        }
        return null;
    },

    getMemberName: function(){
        if (this.datajson.included){
            for(var item in this.datajson.included) {
                if ( this.datajson.included[item].firstName && this.datajson.included[item].lastName ){
                    return this.datajson.included[item].firstName+' '+this.datajson.included[item].lastName;
                }
            }
        }

        return '';
    },

    getFirstName: function(){
        if (this.datajson.included){
            for(var item in this.datajson.included) {
                if ( this.datajson.included[item].firstName ){
                    return this.datajson.included[item].firstName;
                }
            }
        }

        return '';
    },

    getLastName: function(){
        if (this.datajson.included){
            for(var item in this.datajson.included) {
                if ( this.datajson.included[item].lastName ){
                    return this.datajson.included[item].lastName;
                }
            }
        }

        return '';
    },

    getPeopleSimilar: function(){
        return [];
    },

    getLinkedinToken: function(){
        if (this.datajson.included){
            for(var item in this.datajson.included) {
                if ( this.datajson.included[item].objectUrn && this.datajson.included[item].objectUrn.match(/urn\:li\:member\:\d+/g) ){
                    return this.datajson.included[item].objectUrn.replace('urn:li:member:','');
                }
            }
        }
        return false;
    },

    getTitle: function() {
        var currentExperience = this.getExperience(true);
        if(currentExperience.length > 0)
            return currentExperience[0].jobTitle;

        for(var item in this.datajson.included) {
            if ( this.datajson.included[item].objectUrn && this.datajson.included[item].objectUrn.match(/urn\:li\:member\:\d+/g) ){
                if (this.datajson.included[item].occupation){
                    return this.datajson.included[item].occupation;
                }
            }
        }

        return '';
    },

    getPublicProfile: function(){
        for(var item in this.datajson.included) {
            if ( this.datajson.included[item].publicIdentifier ){
                return this.datajson.included[item].publicIdentifier;
            }
        }

        return false;
    },

    getLocation: function (){
        var currentExperience = this.getExperience(true);
        if(currentExperience.length > 0 && currentExperience[0].location)
            return currentExperience[0].location;

        for(var item in this.datajson.included) {
            if ( this.datajson.included[item].locationName ){
                return this.datajson.included[item].locationName;
            }
        }

        return '';
    },

    getSkills: function () {
        // For now, skills have no endorsements, so if we load the skills a push them to InAWare, we are going to replace the skills's endorsemewnts
        return [];
    },

    getTwitter: function () {
        if (this.contactjson.twitterHandles && this.contactjson.twitterHandles.length > 0 && this.contactjson.twitterHandles[0].name){
            return this.contactjson.twitterHandles[0].name;
        }
        return null;
    },

    getPhone: function () {
        if (this.contactjson.phoneNumbers && this.contactjson.phoneNumbers.length > 0 && this.contactjson.phoneNumbers[0].number){
            return this.contactjson.phoneNumbers[0].number;
        }
        return null;
    },

    getBirthday: function () {
        if (this.contactjson.birthDateOn && this.contactjson.birthDateOn.day && this.contactjson.birthDateOn.month){
            return this.contactjson.birthDateOn.day+'/'+this.contactjson.birthDateOn.month;
        }
        return null;
    },

    getWebsite: function () {
        if (this.contactjson.websites && this.contactjson.websites.length > 0 && this.contactjson.websites[0].url){
            return this.contactjson.websites[0].url;
        }
        return null;
    },

    getLanguages: function getLanguages() {
        return [];
    },

    getEducation: function getEducation() {
        // if (this.datajson.educations)
        //     return this.datajson.educations;
        return [];
    },

    getTotalConnections: function getTotalConnections() {
        // if (this.datajson.numOfConnections)
        //     return this.datajson.numOfConnections;
        return false;
    },

    getScrapedImages: function getScrapedImages() {
        return [];
    },

    getConnectionDegree: function getConnectionDegree() {
        // if (this.datajson.degree)
        //     return this.datajson.degree;
        return false;
    },

    getConnections: function getConnections() {
        return [];
    },

    getSummary: function getSummary() {
        for(var item in this.datajson.included) {
            if ( this.datajson.included[item].summary ){
                return this.datajson.included[item].summary;
            }
        }
        return '';
    }
};
