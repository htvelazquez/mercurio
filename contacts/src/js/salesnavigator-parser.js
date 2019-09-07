var SalesNavigatorParser = {
    datajson : null,

    init: function(dataJson) {
		var self = this;
		self.datajson = dataJson;

		var dataResult = {
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
			birthday: null,
			website: self.getWebsite(),
			totalConnections: self.getTotalConnections(),
			connectionDegree: self.getConnectionDegree(),
			summary: self.getSummary(),
			experience: self.getExperience(true),
			fullExperience: self.getExperience(false),
			companyData: self.getCompanyData()
		};
		
		return dataResult;
	},
	
	getLinkedinToken: function(){
        if (this.datajson.objectUrn)
            return this.datajson.objectUrn.replace('urn:li:member:','');
        return false;
	},
	
	getTitle: function() {
        var currentExperience = this.getExperience(true);
        if(currentExperience.length > 0)
            return currentExperience[0].jobTitle;

        if (this.datajson.headline)
            return this.datajson.headline;
        return '';
    },

	getMemberName: function(){
        if (this.datajson.fullName)
            return this.datajson.fullName;
        return '';
	},
	
	getFirstName: function(){
        if (this.datajson.firstName)
            return this.datajson.firstName;
        return '';
    },

    getLastName: function(){
        if (this.datajson.lastName)
            return this.datajson.lastName;
        return '';
	},

	getPublicProfile: function(){
        if (this.datajson.flagshipProfileUrl)
            return this.datajson.flagshipProfileUrl;
        return false;
    },

	getLocation: function(){
        if ("location" in this.datajson.defaultPosition)
            return this.datajson.defaultPosition.location;

        if (this.datajson.location)
            return this.datajson.location;
        return '';
	},
	
	getEmail: function(){
        if (this.datajson.contactInfo.emails){
            var emails = this.datajson.contactInfo.emails;
            if (emails.length == 1) return emails[0].emailAddress;
            for(var item in emails) {
                if (emails[item].dataSource.toLowerCase() == 'linkedin')
                    return emails[item].emailAddress;
            }
        }
        return null;
	},
	
	getTwitter: function(){
        if (this.datajson.contactInfo.socialHandles){
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

        if (this.datajson.contactInfo.phoneNumbers){
            var phones = this.datajson.contactInfo.phoneNumbers;

            for(var item in phones) {
                if ((phone == null && phones[item].dataSource.toLowerCase() == 'linkedin') || (phones[item].dataSource.toLowerCase() == 'linkedin' && phones[item].type.toLowerCase() == 'company')){
                    phone = phones[item].number;
                }
            }
        }
        return phone;
	},
	
	getWebsite: function () {
        var website = null;
        if (this.datajson.contactInfo.websites){
            var websites = this.datajson.contactInfo.websites;

            for(var item in websites) {
                if (website == null || (website == null && websites[item].category.toLowerCase() == 'personal') || (websites[item].category.toLowerCase() == 'personal' && websites[item].dataSource.toLowerCase() == "linkedin")){
                    website = websites[item].url.replace("http://", "").replace("https://", "");
                }
            }
        }
        return website;
	},
	
	getTotalConnections: function getTotalConnections() {
        if (this.datajson.numOfConnections)
            return this.datajson.numOfConnections;
        return false;
    },

	getConnectionDegree: function getConnectionDegree() {
        if (this.datajson.degree)
            return this.datajson.degree;
        return false;
	},

	getSummary: function getSummary() {
        if (this.datajson.summary)
            return this.datajson.summary;
        return '';
	},

	getExperience: function(currentOnly) {
        var month = new Array();
        month[1] = "Jan"; month[2] = "Feb"; month[3] = "Mar"; month[4] = "Apr"; month[5] = "May"; month[6] = "Jun";
        month[7] = "Jul"; month[8] = "Aug"; month[9] = "Sep"; month[10] = "Oct"; month[11] = "Nov"; month[12] = "Dec";

        var experience = [];

        if (this.datajson.positions){
            var positions = this.datajson.positions;

            for(var item in positions) {

                if (!currentOnly || positions[item].current){
                    if (positions[item].startedOn){
                        var from = (positions[item].startedOn.month && positions[item].startedOn.year)? month[positions[item].startedOn.month]+' '+positions[item].startedOn.year : (positions[item].startedOn.year)? positions[item].startedOn.year : '';
                    }else{
                        var from = '';
                    }

                    var companyId = (positions[item].companyUrn)? positions[item].companyUrn.replace('urn:li:fs_salesCompany:','') : null;

                    if (currentOnly) {
                        experience.push({
                            id:         companyId,
                            name:       positions[item].companyName,
                            jobTitle:   positions[item].title,
                            companyName:positions[item].companyName,
                            companyLink:(companyId)? 'https://www.linkedin.com/sales/company/'+companyId : null,
                            since: from
                        });
                    }else{
                        var to = (!positions[item].current && positions[item].endedOn.month && positions[item].endedOn.year)? month[positions[item].endedOn.month]+' '+positions[item].endedOn.year : (!positions[item].current && positions[item].endedOn.year)? positions[item].endedOn.year : '';
                        experience.push({
                            id:         companyId,
                            name:       positions[item].companyName,
                            jobTitle:   positions[item].title,
                            companyName:positions[item].companyName,
                            companyLink:(companyId)? 'https://www.linkedin.com/sales/company/'+companyId : null,
                            from: from,
                            to: to
                        });
                    }
                }
            }
        }

        return experience;
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
    }
};
