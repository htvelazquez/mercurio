/* jshint strict:false */
/* exported SalesNavigatorDataExtractor */
var SalesNavigatorDataExtractor = {

    datajson : null,


    init: function(done) {
        this.datajson = this.getDataJson();

        if(this.datajson === false) {
            done(false);
        }

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
            connections: this.getConnections(),
            companyData: this.getCompanyData()
        };

        done(partialObject);
    },

    getDataJson: function(){
        var urlParts = window.location.pathname.replace(/^\/|\/$/gi, '').split('/');
        var urnName = decodeURIComponent(urlParts[urlParts.length - 1].split('?')[0]);
        var urnCode = urnName.split(',')[0];
        var $code = jQueryScraper("code:contains('\"entityUrn\":\"urn:li:fs_salesProfile:("+urnCode+"')");

        if($code.length > 0) {
            return JSON.parse($code.first().text().trim());
        }

        if (jQueryScraper("code:contains('flagshipProfileUrl')").length > 0){
            var datatext = jQueryScraper("code:contains('flagshipProfileUrl'):first").text().trim();

            if (datatext != undefined){
                var datajson = JSON.parse(datatext);
                var uiname = this.getUIMemberName();
                if (datajson.fullName && uiname && datajson.fullName == uiname){
                    return datajson;
                }
            }
        }

        return false;
    },

    getCompanyId: function () {
        var companyData = this.getCompanyData();
        if(companyData.id != '')
            companyData.id

        return null;
    },

    getCompanyData: function () {
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

    getExperience: function (currentOnly) {
        var month = new Array();
        month[1] = "Jan"; month[2] = "Feb"; month[3] = "Mar"; month[4] = "Apr"; month[5] = "May"; month[6] = "Jun";
        month[7] = "Jul"; month[8] = "Aug"; month[9] = "Sep"; month[10] = "Oct"; month[11] = "Nov"; month[12] = "Dec";

        var experience = [];

        if (this.datajson.positions){
            var positions = this.datajson.positions;

            for(var item in positions) {

                if (!currentOnly || positions[item].current){
                    var from = ''
                    if (positions[item].startedOn !== undefined) {
                        from = (positions[item].startedOn.month && positions[item].startedOn.year)? month[positions[item].startedOn.month]+' '+positions[item].startedOn.year : (positions[item].startedOn.year)? month[1]+' '+positions[item].startedOn.year : '';
                    }

                    var companyId = (positions[item].companyUrn)? positions[item].companyUrn.replace('urn:li:fs_salesCompany:','') : null;

                    if (currentOnly) {
                        experience.push({
                            id:         companyId,
                            name:       positions[item].companyName,
                            jobTitle:   positions[item].title,
                            companyName:positions[item].companyName,
                            companyLink:(companyId)? 'https://www.linkedin.com/sales/company/'+companyId : null,
                            location: positions[item].location,
                            since: from
                        });
                    }else{
                        var to = ''
                        if (positions[item].endedOn !== undefined) {
                            to = (!positions[item].current && positions[item].endedOn.month && positions[item].endedOn.year)? month[positions[item].endedOn.month]+' '+positions[item].endedOn.year : (!positions[item].current && positions[item].endedOn.year)? month[1]+' '+positions[item].endedOn.year : '';
                        }

                        experience.push({
                            id:         companyId,
                            name:       positions[item].companyName,
                            jobTitle:   positions[item].title,
                            companyName:positions[item].companyName,
                            companyLink:(companyId)? 'https://www.linkedin.com/sales/company/'+companyId : null,
                            location: positions[item].location,
                            from: from,
                            to: to
                        });
                    }
                }
            }
        }

        return experience;
    },

    getEmail: function () {
        if (this.datajson.contactInfo !== undefined && this.datajson.contactInfo.emails){
            var emails = this.datajson.contactInfo.emails;
            if (emails.length == 1) return emails[0].emailAddress;
            for(var item in emails) {
                if (emails[item].dataSource.toLowerCase() == 'linkedin')
                    return emails[item].emailAddress;
            }
        }
        return null;
    },

    getMemberName: function () {
        if (this.datajson.fullName)
            return this.datajson.fullName;
        return '';
    },

    getUIMemberName: function(){
        var el = document.getElementsByClassName('profile-topcard-person-entity__name')[0];
        // if( !(el) ){
        //     el = document.getElementsByClassName('full-name')[0];
        // }
        if(el){
            return el.textContent.trim();
        }else{
            return null;
        }
    },

    getFirstName: function () {
        if (this.datajson.firstName)
            return this.datajson.firstName;
        return '';
    },

    getLastName: function () {
        if (this.datajson.lastName)
            return this.datajson.lastName;
        return '';
    },

    getTwitter: function () {
        if (this.datajson.contactInfo !== undefined && this.datajson.contactInfo.socialHandles){
            var socialHandles = this.datajson.contactInfo.socialHandles;
            for(var item in socialHandles) {
                if (socialHandles[item].type.toLowerCase() == 'twitter')
                    return socialHandles[item].name;
            }
        }
        return null;
    },

    getPhone: function () {
        var phone = null;

        if (this.datajson.contactInfo !== undefined && this.datajson.contactInfo.phoneNumbers){
            var phones = this.datajson.contactInfo.phoneNumbers;

            for(var item in phones) {
                if ((phone == null && phones[item].dataSource.toLowerCase() == 'linkedin') || (phones[item].dataSource.toLowerCase() == 'linkedin' && phones[item].type.toLowerCase() == 'company')){
                    phone = phones[item].number;
                }
            }
        }
        return phone;
    },

    getPeopleSimilar: function () {
        return [];
    },

    getLinkedinToken: function () {
        if (this.datajson.objectUrn)
            return this.datajson.objectUrn.replace('urn:li:member:','');
        return '';
    },

    getTitle: function () {
        var currentExperience = this.getExperience(true);
        if(currentExperience.length > 0)
            return currentExperience[0].jobTitle;

        if (this.datajson.headline)
            return this.datajson.headline;
        return '';
    },

    getLocation: function getLocation(){
        if (this.datajson != false && "defaultPosition" in this.datajson && "location" in this.datajson.defaultPosition)
            return this.datajson.defaultPosition.location;

        if (this.datajson.location)
            return this.datajson.location;
        return '';
    },

    getSkills: function getSkills() {
        return [];
    },

    getLanguages: function getLanguages() {
        return [];
    },

    getEducation: function getEducation() {
        if (this.datajson.educations)
            return this.datajson.educations;
        return [];
    },

    getTotalConnections: function getTotalConnections() {
        if (this.datajson.numOfConnections)
            return this.datajson.numOfConnections;
        return false;
    },

    getScrapedImages: function getScrapedImages() {
        return [];
    },

    getConnectionDegree: function getConnectionDegree() {
        if (this.datajson.degree)
            return this.datajson.degree;
        return false;
    },

    getConnections: function getConnections() {
        return [];
    }
};
