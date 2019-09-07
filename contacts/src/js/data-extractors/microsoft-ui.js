
/*jshint strict:false*/
/* global Promise: false, alert: false*/
/*exported MicrosoftUIDataExtractor*/
var MicrosoftUIDataExtractor = {

    isMicrosoftUI: function() {
        return jQueryScraper('body.render-mode-BIGPIPE').length > 0;
    },

    init: function(maxTimeout, done, onlyMemberToken) {
        var dataPromises = [
            this.extractRenderedDataSequenceStep(maxTimeout, onlyMemberToken)
        ];

        if(onlyMemberToken === false) {
            dataPromises.push(this.extractContactAndPersonalInfoSequenceStep(maxTimeout));
            dataPromises.push(this.extractProfileExperienceSequenceStep(maxTimeout));
            //dataPromises.push(this.extractSkillsSequenceStep(maxTimeout));
            //dataPromises.push(this.extractAccomplishmentsSequenceStep(maxTimeout));
            //dataPromises.push(this.extractEducationSequenceStep(maxTimeout));
        }

        var self = this;
        Promise
            .all(dataPromises)
            .then(function(data) {
                var partialObject = {scrapedImages: []};
                for(var i = 0; i < data.length; i++) {
                    partialObject = Object.assign(partialObject, data[i]);
                }

                if(onlyMemberToken === false) {
                    jQueryScraper('body').scrollTop(0);
                    partialObject.companyData = {
                        id: null,
                        companyName: null,
                        companyURL: null
                    };

                    if(partialObject.experience.length > 0) {
                        partialObject.companyData = partialObject.experience[0];
                        var urlMatching = partialObject.companyData.companyLink.match(/company(-beta)?\/(\d+)/i);
                        if(urlMatching.length === 3) {
                            partialObject.companyData.id = urlMatching[2]*1;
                        }
                    }

                    // Promise
                    //     .all([self.extractImagesSequenceStep(), self.extractPeopleSimilarSequenceStep()])
                    //     .then(function(finalData) {
                    //         for(var k = 0; k < finalData.length; k++) {
                    //             partialObject = Object.assign(partialObject, finalData[k]);
                    //         }
                    //
                    //         done(partialObject);
                    //     });
                } else {
                    done(partialObject);
                }
            })
            .catch(function(errors) {
                console.error('Failed Promises');
                console.error(errors);
                jQueryScraper('body').scrollTop(0);
                alert(JSON.stringify(errors));
            });
    },

    extractRenderedDataSequenceStep: function(maxTimeout, onlyMemberToken) {
        var self = this;
        return new Promise(function(resolve, reject) {

            console.debug('@@ Waiting Top-Card');
            jQueryScraper('.pv-profile-section.artdeco-container-card.pv-top-card-section').waitForSelector(function topCardLoaded() {
                var partialData = self.getLinkedinToken();

                if(partialData === false) {
                    reject('Missing Member Token, try refreshing the page or visiting the SalesNavigator profile.');
                }

                partialData.isMicrosoftUI = true;
                partialData.projects = [];
                partialData.recommendations = [];
                partialData.connections = [];
                partialData.authToken = null;

                partialData.title = jQueryScraper('.pv-top-card-section__headline').text().trim();

                partialData.location = null;
                var $location = jQueryScraper('.pv-top-card-section__body .pv-top-card-section__location');
                if($location.length > 0) {
                    partialData.location = $location.text().trim();
                }

                partialData.summary = null;
                var $summary = jQueryScraper('.pv-top-card-section__summary');
                if($summary.length > 0) {
                    partialData.summary = $summary.contents().filter(function(){
                        return this.tagName !== 'BUTTON';
                    }).text().trim();
                }

                partialData.connectionDegree = jQueryScraper('.profile-view-grid .pv-profile-section .pv-top-card-section__distance-badge .dist-value').text().trim().match(/^(\d+)/);
                if(partialData.connectionDegree !== null) {
                    partialData.connectionDegree = partialData.connectionDegree[0];
                } else {
                    partialData.connectionDegree = null;
                }

                partialData.totalConnections = null;
                var $connections = jQueryScraper('.pv-top-card-section__connections.pv-top-card-section__connections--with-separator span');
                if($connections.length > 0) {
                    partialData.totalConnections = $connections.first().text().trim();
                }

                if(onlyMemberToken === false) {
                    setTimeout(function() {
                        jQueryScraper('body').scrollTop(jQueryScraper('body').height() / 2);
                        console.debug('@@ Top-Card -> Resolve');
                        resolve(partialData);
                    }, maxTimeout / 3);
                } else {
                    console.debug('@@ Top-Card -> Resolve');
                    resolve(partialData);
                }

            }, function topCardNotLoaded() {
                reject('Top-Card not loaded');
            }, maxTimeout);
        });
    },

    extractContactAndPersonalInfoSequenceStep: function(maxTimeout) {
        var self = this;
        return new Promise(function(resolve) {
            console.debug('@@ Openning Contact info box');

            jQueryScraper('.pv-profile-section.artdeco-container-card.pv-contact-info').waitForSelector(function contactInfoLoaded() {

                var seeMoreSelector = '.pv-profile-section.artdeco-container-card.pv-contact-info button[data-control-name=contact_see_more]';
                var seeLessSelector = '.pv-profile-section.artdeco-container-card.pv-contact-info button[data-control-name=contact_see_less]';

                if(jQueryScraper(seeLessSelector).length > 0) {

                    console.debug('@@ Contact-Box -> Resolve');
                    resolve(self.getContactInfo());
                } else if(jQueryScraper(seeMoreSelector).length > 0) {
                    jQueryScraper(seeMoreSelector).trigger('click');

                    console.debug('@@ Waiting Contact info box see-less');
                    jQueryScraper(seeLessSelector).waitForSelector(function() {
                        console.debug('@@ Contact-Box -> Resolve');
                        resolve(self.getContactInfo());
                    }, function contactInfoSeeLessNeverLoaded() {
                        console.warn('@@ Contact-Box see-less not loaded');
                        console.debug('@@ Contact-Box -> Resolve');
                        resolve({});
                    }, maxTimeout);
                }
            }, function contactInfoNeverLoaded() {
                console.warn('@@ Contact-Box not loaded');
                console.debug('@@ Contact-Box -> Resolve');
                resolve({});
            }, maxTimeout);
        });
    },

    getContactInfo: function() {
        var partialData = {
            websites: [],
            email: null,
        };

        jQueryScraper('.right-rail__info_container .pv-contact-info__contact-type.ci-websites .pv-contact-info__action.ember-view').each(function() {
            var websiteType = jQueryScraper(this).parent().find('.pv-contact-info__website-type');
            if(websiteType !== null) {
                websiteType = websiteType.text().trim().replace(/^\(|\)$/g, '');
            } else {
                websiteType = 'Not Specified';
            }

            partialData.websites.push({
                site: jQueryScraper(this).attr('href'),
                type: websiteType
            });
        });

        var $email = jQueryScraper('section.pv-contact-info__contact-type.ci-email > div > a');
        if($email.length > 0) {
            partialData.email = $email.text().trim();
        }
    },

    extractProfileExperienceSequenceStep: function(maxTimeout, level) {
        var self = this;
        if(typeof level === 'undefined') { level = 0; }

        return new Promise(function(resolve, reject) {

            console.debug('@@ Waiting Experience box');

            jQueryScraper('body').scrollTop(jQueryScraper('.profile-detail').offset().top);
            jQueryScraper('.profile-detail .background-details .pv-profile-section.experience-section ul.pv-profile-section__section-info')
                .waitForSelector(function experienceBoxLoaded() {

                var experience = self.getExperience(false);

                var companyData = null;
                if(experience.length > 0) {
                    companyData = experience[0];
                    var urlMatching = companyData.companyLink.match(/company(-beta)?\/(\d+)/i);
                    if(urlMatching.length === 3) {
                        companyData.id = urlMatching[2]*1;
                    }
                }

                resolve({
                    experience: experience,
                    fullExperience: self.getExperience(true),
                    companyData: companyData
                });
            }, function experienceBoxNeverLoaded() {
                reject('Experience box never loaded');
            }, maxTimeout);
        });
    },

    getLinkedinToken: function(){
        var urlParts = window.location.pathname.replace(/^\/|\/$/gi, '').split('/');
        var urnName = decodeURIComponent(urlParts[urlParts.length - 1].split('?')[0]);

        var $code = jQueryScraper('code:contains("publicIdentifier":"' + urnName + '")');
        if($code.length > 0) {
            var codeJson = JSON.parse($code.first().text().trim());
            for(var i = 0; i < codeJson.included.length;i++) {
                var dataObject = codeJson.included[i];
                if(typeof(dataObject.publicIdentifier) !== 'undefined' && dataObject.publicIdentifier === urnName && typeof(dataObject.objectUrn) !== 'undefined') {
                    var objectId = this.__getMemberTokenFromUrn(dataObject.objectUrn);
                    if(objectId !== null) {
                        return {
                            id: objectId,
                            firstName: dataObject.firstName,
                            lastName: dataObject.lastName,
                            publicProfileURL: 'https://www.linkedin.com/in/' + dataObject.publicIdentifier,
                            name: dataObject.firstName + ' ' + dataObject.lastName
                        };
                    }
                }
            }
        } else {
            $code = jQueryScraper('code:contains("profile":"urn:li:fs_profile:)');
            if($code.length > 0) {
                var profileCodeJson = JSON.parse($code.first().text().trim());
                var linkedinHashedId = profileCodeJson.data.profile.replace('urn:li:fs_profile:', '');

                var profileData = null;
                for(var k = 0; k < profileCodeJson.included.length; k++) {
                    var profileDataObject = profileCodeJson.included[k];
                    if(typeof profileDataObject.entityUrn !== 'undefined' && profileDataObject.entityUrn.search(linkedinHashedId) !== -1 && this.__isValidStdProfileObject(profileDataObject)) {
                        profileData = {
                            id: this.__getMemberTokenFromUrn(profileDataObject.objectUrn),
                            firstName: profileDataObject.firstName,
                            lastName: profileDataObject.lastName,
                            publicProfileURL: 'https://www.linkedin.com/in/' + profileDataObject.publicIdentifier,
                            name: profileDataObject.firstName + ' ' + profileDataObject.lastName
                        };
                    }
                }

                if(profileData !== null) {
                    return profileData;
                }
            }
        }

        return false;
    },

    __getMemberTokenFromUrn: function(urnString) {
        var matching = urnString.match(/urn:li:member:(\d+)/);
        if(matching !== null) {
            return matching[1] * 1;
        }

        return false;
    },

    /*extractSkillsSequenceStep: function(maxTimeout) {
        var self = this;

        return new Promise(function(resolve, reject) {
            console.debug('@@ Waiting Skills box', 'info');
            jQueryScraper('.pv-profile-section.artdeco-container-card.pv-featured-skills-section').waitForSelector(function skillsBoxLoaded() {
                console.debug('@@ Openning Skills box', 'info');

                var expansionSelector = '.pv-profile-section.artdeco-container-card.pv-featured-skills-section #featured-skills-expanded';
                var seeMoreSelector = '.pv-profile-section.artdeco-container-card.pv-featured-skills-section button[data-control-name=skill_details]';
                if(jQueryScraper(expansionSelector).length === 0) {
                    jQueryScraper(seeMoreSelector).trigger('click');

                    jQueryScraper(expansionSelector).waitForSelector(function() {
                        resolve({skills: self.getSkills(this)});
                    }, function seeLessNeverLoaded() {
                        reject('Skills See-less never loaded');
                    }, maxTimeout);
                } else {
                    resolve({skills: self.getSkills()});
                }
            }, function skillsBoxNeverLoaded() {
                reject('Skills box never loaded');
            }, maxTimeout);
        });
    },*/

    /*getSkills: function() {
        var skills = [];
        jQueryScraper('.pv-skill-entity__header').each(function() {
            skills.push({
                id: jQueryScraper(this).find('.pv-skill-entity__skill-name').text().trim().toLowerCase().replace(/[^\w ]+/g,'').replace(/ +/g,'-'),
                endorsements: jQueryScraper(this).find('.pv-skill-entity__endorsement-count').text().trim() * 1
            });
        });

        return skills;
    },*/

    getExperience: function(fullExperience) {
        var experience = [];
        jQueryScraper('.profile-detail .background-details .pv-profile-section.experience-section ul.pv-profile-section__section-info > li').each(function() {
            // We search ONLY the actuals
            var isActual = jQueryScraper(this).find('.pv-entity__position-info .pv-entity__date-range time').length === 1;
            var isActualReg = jQueryScraper(this).html().match(/>(([a-zA-z]{3}\s{1})?[0-9]{4})\s{1}.{1}\sPresent</);
            if(fullExperience || isActual || isActualReg !== null) {
                var linkURL = null;
                var $link = jQueryScraper(this).find('a[data-control-name=background_details_company]');
                if($link.length > 0) {
                    linkURL = $link.attr('href').split('?')[0].split('#')[0];
                }

                var experienceObject = {
                    jobTitle: jQueryScraper(this).find('.pv-entity__summary-info h3').text().trim(),
                    companyName: jQueryScraper(this).find('.pv-entity__secondary-title').text().trim(),
                    companyLink: linkURL
                };

                if(isActual || isActualReg !== null) {
                    experienceObject.since = />(([a-zA-z]{3}\s{1})?[0-9]{4})\s{1}.{1}\sPresent</.exec(jQueryScraper(this).html())[1];

                } else {
                    var dateMatch = />(([a-zA-z]{3}\s{1})?[0-9]{4})\s{1}.{1}\s(([a-zA-z]{3}\s{1})?[0-9]{4})</.exec(jQueryScraper(this).html());

                    if (dateMatch !== null) {
                        experienceObject.from = dateMatch[1];
                        experienceObject.to = dateMatch[3];
                    }
                }

                experience.push(experienceObject);
            }
        });

        return experience;
    },

    /*extractAccomplishmentsSequenceStep: function(maxTimeout) {
        var self = this;
        return new Promise(function(resolve) {
            console.debug('@@ Waiting Languages & Accomplishments box');

            jQueryScraper('.pv-profile-section.accordion-panel.pv-accomplishments-block.languages')
                .waitForSelector(function languagesAccomplishmentListLoaded() {
                var languagesPromise = new Promise(function(resolveLang) {

                    console.debug('@@ Openning Languages box');

                    var seeMoreSelector = '.pv-profile-section.accordion-panel.pv-accomplishments-block.languages .pv-accomplishments-block__expand';
                    var list = '.pv-profile-section.accordion-panel.pv-accomplishments-block.languages .pv-accomplishments-block__list';
                    if(jQueryScraper(seeMoreSelector).length > 0) {

                        jQueryScraper(seeMoreSelector).trigger('click');
                        jQueryScraper(list).waitForSelector(function listLoaded() {

                            resolveLang({
                                languages: self.getLanguages.apply(this, [])
                            });

                        }, function listNeverLoaded() {
                            console.error('@@ Languages list not loaded');
                            resolveLang({});
                        }, maxTimeout);

                    } else {
                        resolveLang({
                            languages: self.getLanguages.apply(this, [])
                        });
                    }
                });


                Promise.all([languagesPromise]).then(function(data) {
                    var partialObject = {};
                    for(var i = 0; i < data.length; i++) {
                        partialObject = Object.assign(partialObject, data[i]);
                    }

                    resolve(partialObject);
                });
            },
            function() {
                console.error('@@ Languages box not loaded');
                resolve({});
            }, maxTimeout);

        });
    },*/

    /*extractEducationSequenceStep: function(maxTimeout, level) {
        var self = this;
        return new Promise(function(resolve) {
            console.debug('@@ Waiting Education box');

            if(typeof level === 'undefined') {
                level = 0;
            }

            jQueryScraper('.education-section').waitForSelector(function educationBoxLoaded() {
                resolve({
                    education: self.getEducation.apply(this, [])
                });

            }, function educationBoxNotLoaded() {
                console.debug('@@ Education box not loaded');
                resolve({education: []});
            }, maxTimeout);
        });
    },*/

    getLanguages: function() {
        var langs = [];
        jQueryScraper('#languages-accomplishment-list ul.pv-accomplishments-block__list > li').each(function() {
            langs.push(jQueryScraper(this).find('.pv-accomplishment-entity__title').contents().filter(function(){
                return this.tagName === undefined;
            }).text().trim().toLowerCase());
        });

        return langs;
    },

    getEducation: function() {
        var education = [];

        jQueryScraper('#education-section .pv-profile-section__section-info > li').each(function() {
            var degreeItems = jQueryScraper(this)
                .find('.pv-entity__summary-info .pv-entity__degree-info')
                .contents()
                .filter(function() {
                    return this.className !== undefined && this.className.search('pv-entity__secondary-title') !== -1;
                });

            var degree = [];
            degreeItems.each(function() {
                degree.push(
                    jQueryScraper(this).contents().filter(function() {
                        return this.className !== undefined && this.className.search('pv-entity__comma-item') !== -1;
                    }).text().trim()
                );
            });

            education.push({
                school: jQueryScraper(this).find('a[data-control-name=background_details_school]').attr('href'),
                schoolName: jQueryScraper(this).find('.pv-entity__summary-info .pv-entity__degree-info .pv-entity__school-name').text().trim(),
                degree: degree.join(', '),
                date: jQueryScraper(this).find('.pv-entity__summary-info .pv-entity__dates').contents().filter(function() {
                    // We only preserve the span with empty class.
                    return this.tagName === 'SPAN' && this.className === '';
                }).text().trim()
            });
        });

        return education;
    },

    extractImagesSequenceStep: function() {
        return new Promise(function(resolve) {

            console.debug('@@ Loading Images');
            var imagePromises = [
                new Promise(function(resolve) {
                    var scrapedImages = [];
                    jQueryScraper('img.pv-top-card-section__image[src]').each(function() {
                        if(jQueryScraper(this).attr('src').match(/^data:image/) === null) {
                                scrapedImages.push({
                                image: jQueryScraper(this).attr('src'),
                                name: jQueryScraper(this).attr('alt'),
                                jobTitle: jQueryScraper('.pv-top-card-section__information .pv-top-card-section__headline').text().trim(),
                                location: 'NEW_PROFILE_IMAGE'
                            });
                        }
                    });

                    resolve(scrapedImages);
                }),

                new Promise(function(resolve) {
                    var scrapedImages = [];
                    jQueryScraper('img.pv-browsemap-section__member-image[src]').each(function() {
                        if(jQueryScraper(this).attr('src').match(/^data:image/) === null) {
                            scrapedImages.push({
                                image: jQueryScraper(this).attr('src'),
                                name: jQueryScraper(this).attr('alt'),
                                jobTitle: jQueryScraper(this).parent('.pv-browsemap-section__member').find('.pv-browsemap-section__member-detail .browsemap-headline').text().trim(),
                                location: 'RELATED_PROFILES'
                            });
                        }
                    });

                    resolve(scrapedImages);
                }),

                new Promise(function(resolve) {
                    var scrapedImages = [];
                    jQueryScraper('a[data-control-name="recommendation_details_profile"] > img[src]').each(function() {
                        if(jQueryScraper(this).attr('src').match(/^data:image/) === null) {
                            scrapedImages.push({
                                image: jQueryScraper(this).attr('src'),
                                name: jQueryScraper(this).attr('alt'),
                                jobTitle: jQueryScraper(this).parent().find('.pv-recommendation-entity__detail .pv-recommendation-entity__headline').text().trim(),
                                location: 'RECOMMENDATIONS'
                            });
                        }
                    });

                    resolve(scrapedImages);
                }),

                new Promise(function(resolve) {
                    var scrapedImages = [];
                    jQueryScraper('#projects-accomplishment-list .pv-accomplishments-block__list li a[data-control-name="project_contributors"] .facepile > img[src]').each(function() {
                        if(jQueryScraper(this).attr('src').match(/^data:image/) === null) {
                            scrapedImages.push({
                                image: jQueryScraper(this).attr('src'),
                                name: jQueryScraper(this).attr('alt'),
                                jobTitle: null,
                                location: 'TEAMMATES'
                            });
                        }
                    });

                    resolve(scrapedImages);
                })
            ];

            Promise
                .all(imagePromises)
                .then(function(data) {

                    var resultSet = [];
                    for(var i = 0; i < data.length; i++) {
                        for(var k = 0; k < data[i].length; k++) {
                            resultSet.push(data[i][k]);
                        }
                    }
                    resolve({scrapedImages: resultSet});
                })
                .catch(function(errors) {
                    console.error('@@ Error Loading Images', errors);
                });
        });
    },

    __isValidStdProfileObject: function(obj) {
        return typeof obj.objectUrn !== 'undefined' && typeof obj.objectUrn.match(/^urn:li:member:(\d+)/) && typeof obj.firstName !== 'undefined' && typeof obj.lastName !== 'undefined';
    },

    extractPeopleSimilarSequenceStep: function() {
        var self = this;

        return new Promise(function(resolve) {
            var partialData = {peopleSimilar: []};

            console.debug('@@ Loading People Similar');
            jQueryScraper('code:contains("$type":"com.linkedin.voyager.identity.shared.MiniProfile")').each(function() {
                var codeBlock = JSON.parse(jQueryScraper(this).text());

                if(typeof codeBlock.included !== 'undefined') {
                    for(var i = 0; i < codeBlock.included.length; i++) {

                        if(self.__isValidStdProfileObject(codeBlock.included[i])) {
                            var relatedObject = {
                                lId: codeBlock.included[i].objectUrn.match(/^urn:li:member:(\d+)/)[1],
                                urn: codeBlock.included[i].objectUrn,
                                name: codeBlock.included[i].firstName + ' ' + codeBlock.included[i].lastName,
                                desc: null,
                                publicIdentifier: null,
                                url: null,
                                type: 'NEW_PEOPLE_SIMILAR',
                                miniProfileUrn: null,
                            };

                            if(typeof codeBlock.included[i].occupation !== 'undefined') {
                                relatedObject.desc = codeBlock.included[i].occupation;
                            }

                            if(typeof codeBlock.included[i].picture !== 'undefined') {
                                relatedObject.miniProfileUrn = codeBlock.included[i].picture['com.linkedin.voyager.common.MediaProcessorImage'].split(',')[0];
                            }

                            if(typeof codeBlock.included[i].publicIdentifier !== 'undefined') {
                                relatedObject.publicIdentifier = codeBlock.included[i].publicIdentifier;
                                relatedObject.url = 'https://www.linkedin.com/in/' + codeBlock.included[i].publicIdentifier + '/';
                            }

                            partialData.peopleSimilar.push(relatedObject);
                        }
                    }
                }
            });

            resolve(partialData);
        });
    }
};
