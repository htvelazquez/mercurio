var browserExtension = {
    tasks: [],
    windowId: 0,
    running: true,
    pauseError: false,
    pauseErrorCount: 0,
    retries: 0,
    counter: 0,
    currentDay: 0,
    user: {
        email: ''
    },
    refreshTimeout: null,
    configs: {
        enabled: null,
        appTimeout : RETRY_GET_TASKS
    },
    run: function () {
        console.log('RUN!!');

        Raven.config(SENTRY_URL).install();

        obj = this;

        storage = JSON.parse(localStorage.getItem(chrome.runtime.id));

        if (storage !== null) {
            obj.running = storage.running;
            obj.pauseError = storage.pauseError;
            obj.pauseErrorCount = storage.pauseErrorCount;
            obj.counter = storage.counter;
            obj.currentDay = storage.currentDay;
        }

        obj.refreshTimeout = setInterval(function () {
            console.log('CheckTasks');

            var d = new Date;
            var hour = d.getHours();
            var day = d.getDay(); // Day of week - 0-6 / Sunday-Saturday

            calculatedCurrentDay = parseInt(String(d.getUTCFullYear()) + ("0" + d.getUTCMonth()).slice(-2) + ("0" + d.getUTCDate()).slice(-2));

            if (obj.currentDay < calculatedCurrentDay) {
                console.log('Reset pauses');
                // Sets current day mark
                obj.currentDay = calculatedCurrentDay;

                // Reset pauses
                obj.running = true;
                obj.pauseErrorCount = 0;
                obj.pauseError = false;

                // Resets open links counter
                obj.counter = 0;
            }

            if (obj.running) {
                if (!obj.pauseError) {
                    if ((hour >= 9 && hour <= 19) && (day > 0 && day < 6)) {
                        chrome.windows.get(obj.windowId, {populate: true}, function (win) {
                            if (chrome.runtime.lastError === undefined) {
                                var tabIds = [];
                                win.tabs.forEach(function (tab) {
                                    if (tab.url !== 'chrome-extension://' + chrome.runtime.id + '/main.html') {
                                        tabIds.push(tab.id);
                                    }
                                });

                                chrome.tabs.remove(tabIds, function () {});
                            }
                        });

                        if (obj.user == null) {
                            chrome.identity.getProfileUserInfo(function(userInfo) {
                                var user = {
                                    email: userInfo.email
                                };

                                obj.user = user;
                            });
                        }

                        obj.getTasks(function () {
                            obj.openNext();
                        });
                    }
                } else {
                    obj.pauseErrorCount++;
                    if (obj.pauseErrorCount > TIMES_TO_RETRIES) {
                        obj.pauseErrorCount = 0;
                        obj.pauseError = false;
                    }
                }
            }

            var storage = {
                running: obj.running,
                pauseError: obj.pauseError,
                pauseErrorCount: obj.pauseErrorCount,
                counter: obj.counter,
                currentDay: obj.currentDay
            };

            localStorage.setItem(chrome.runtime.id, JSON.stringify(storage));

        }, obj.configs.appTimeout);

        // Listeners
        chrome.runtime.onMessage.addListener(function(request, sender, sendResponse) {
            var key;
            var task;
            var senderTabId;

            console.log('Listener');
            console.log(request);

            if (sender.tab !== undefined) {
                senderTabId = sender.tab.id;

                for (var i = 0, len = obj.tasks.length; i < len; i++) {
                    if (obj.tasks[i].tab_id === sender.tab.id) {
                        key = i;
                    }
                }
            }

            if (key !== undefined && obj.tasks[key] !== undefined) {
                // Tab messages
                task = obj.tasks[key];

                switch (request.status) {
                    case STATUS_DONE:
                        if (request.result !== false && request.result.lId !== '') {
                            obj.pauseError = false;
                            obj.retries = 0;

                            task.status = request.status;

                            if (task.result !== undefined) {
                                task.result = Object.assign(task.result, request.result);
                            } else {
                                task.result = request.result;
                            }


                            clearTimeout(obj.tasks[key].timeout);
                            obj.tasks[key].timeout = null;
                            obj.setTasks(obj.tasks[key], function (response) {
                                console.log('Save response');
                            });

                            var randTimeAfterDone = TIME_AFTER_DONE + Math.floor((Math.random() * 5000) + 1000);

                            var closeInterval = setInterval( function() {
                                    clearInterval(closeInterval);
                                    obj.tasks[key] = task;

                                    chrome.tabs.remove(senderTabId, function () {
                                        var lastError = chrome.runtime.lastError;

                                        obj.openNext();
                                    });
                                },
                                randTimeAfterDone
                            );
                        }

                        break;

                    case STATUS_ERROR:
                        obj.pauseError = true;

                        if (request.type !== undefined && request.type === STATUS_UNAVAILABLE) {
                            obj.retries++;
                            //Raven.captureMessage('UNAVAILABLE: ' + task.url);
                        }

                        if (request.type !== undefined && request.type === STATUS_AUTH_ERROR) {
                            obj.retries++;
                            //Raven.captureMessage('STATUS_AUTH_ERROR: ' + task.url);
                        }

                        obj.tasks.forEach(function(task, id) {
                            clearTimeout(task.timeout);
                            task.timeout = null;
                        });

                        if (obj.retries >= RETRIES_ERROR) {
                            obj.tasks[key].status = request.status;
                            obj.running = false;
                        }

                        chrome.tabs.update(task.tab_id, {active: true}, function (){});
                        break;

                    case CTRL_REDIRECT:
                        // request  url
                        clearTimeout(obj.tasks[key].timeout);
                        obj.tasks[key].timeout = null;
                        obj.tasks[key].url = request.url;
                        obj.tasks[key].tab_id = undefined;
                        obj.tasks[key].result = request.result;

                        chrome.tabs.remove(senderTabId, function () {
                            var lastError = chrome.runtime.lastError;
                            obj.openNext();
                        });
                        break;

                    case undefined:
                        sendResponse({action: CTRL_START});
                        break;

                    case STATUS_LIMIT_EXCEEDED:
                        Raven.captureMessage('STATUS_LIMIT_EXCEEDED: ' + obj.user.email);
                        // TO-DO add API endpoint to report it
                        obj.running = false;
                        break;
                }
            } else {
                // Control messages
                switch (request.status) {
                    case CTRL_PAUSE:
                        obj.running = false;
                        break;
                    case CTRL_RESUME:
                        obj.resume();
                        break;

                    case CTRL_GET_DATA:
                        var dataResponse = {
                            tasks: obj.tasks,
                            hasError: obj.hasError(),
                            configs: obj.configs,
                            user: {
                                email: obj.user.email
                            },
                            running: obj.running,
                            counter: obj.counter
                        };
                        sendResponse(dataResponse);
                        break;

                    case CTRL_NEW_TAB:
                        /* request
                        tab_id int (required)
                        store  bool (optional)
                        */
                        obj.tasks.forEach(function(task, id) {
                            if (task.tab_id === request.tab_id) {
                                obj.createTab(id, request.store);
                            }
                        });
                        break;
                }
            }

            obj.setBadge();
        });
    },
    isDone: function () {
        var done = true;
        this.tasks.forEach(function(task) {
            if (task.status === STATUS_CREATED && task.timeout === null ) {
                done = false;
            }
        });

        return done;
    },

    hasError: function () {
        var has = false;
        this.tasks.forEach(function(task) {
            if (task.status === STATUS_ERROR) {
                has = true;
            }
        });

        return has;
    },

    setBadge: function () {
        if (!this.hasError()) {
            chrome.browserAction.setBadgeText({text: ''});
        } else {
            chrome.browserAction.setBadgeText({text: '#'});
            chrome.browserAction.setBadgeBackgroundColor({color: 'red'});
        }
    },

    setTask: function (key, value) {
        for (var attrname in value) { this.tasks[key][attrname] = value[attrname]; }
    },

    openNext: function () {
        var obj = this;
        if (obj.running) {
            console.log('openNext');
            for (var i = 0, len = obj.tasks.length; i < len; i++) {
                if (obj.tasks[i].status === STATUS_CREATED && obj.tasks[i].tab_id === undefined) {
                    obj.createTab(i, true);
                    return;
                }
            }
        }
    },

    resume: function () {
        var obj = this;
        for (var i = 0, len = obj.tasks.length; i < len; i++) {
            if (obj.tasks[i].tab_id !== undefined) {
                chrome.tabs.remove(obj.tasks[i].tab_id, function () {
                    var lastError = chrome.runtime.lastError;
                });
            }

            obj.tasks[i].status = STATUS_CREATED;
            obj.tasks[i].tab_id = undefined;

            clearTimeout(obj.tasks[i].timeout);
            obj.tasks[i].timeout = null;
        }

        obj.running = true;
        obj.retries = 0;
        obj.openNext();
    },

    createTab: function (id, store) {
        var obj = this;
        var url = obj.tasks[id].url;

        // check if window exists
        chrome.windows.get(obj.windowId, function () {
            if (chrome.runtime.lastError !== undefined) {
                chrome.windows.create({url: 'chrome-extension://' + chrome.runtime.id + '/main.html'}, function (newWin){
                    obj.windowId = newWin.id;
                    create();
                });
            } else {
                create();
            }
        });

        function create() {
            chrome.tabs.create({ windowId: obj.windowId, url: url, active: false}, function(tab) {
                console.log('createTab url: ' + url);
                if (store) {
                    var task = {
                        url: url,
                        id: obj.tasks[id].id,
                        id_job: obj.tasks[id].id_job,
                        status: STATUS_CREATED,
                        tab_id: tab.id
                    };

                    task.timeout = setTimeout(function () {
                        clearTimeout(task.timeout);
                        task.timeout = null;
                        task.status = STATUS_NO_RESPONSE;
                        obj.setTask(id, task);

                        chrome.tabs.get(task.tab_id, function() {
                            var lastError = chrome.runtime.lastError;
                            if (lastError === undefined) {
                                chrome.tabs.remove(task.tab_id);
                            }
                        });

                        obj.setTasks(task, function (response) {
                            console.log('Save no-response');
                        });
                        //Raven.captureMessage('URL NO-RESPONSE: ' + task.url);
                        //obj.openNext();

                    }, RETRY_TIME);

                    obj.setTask(id, task);
                }
            });
        }
    },

    getTasks: function (callback) {
        var obj = this;
        if (obj.isDone()) {
            chrome.identity.getProfileUserInfo(function(userInfo) {
                if (userInfo !== undefined) {
                    console.log('getTasks');
                    Raven.setUserContext(userInfo);
                    console.log(userInfo);

                    var manifest = chrome.runtime.getManifest();
                    Raven.setExtraContext({ version: manifest.version });

                    obj.user.email = userInfo.email;
                    obj.tasks = [];
                    wsGrabber.getTasks(
                        function (tasks) {
                            const regex = /(linkedin.com\/.*)/g;
                            if (tasks !== undefined) {
                                for(i = 0; i < tasks.length; i++) {
                                    if (tasks[i] !== null) {
                                        m = regex.exec(tasks[i].url);

                                        if (m !== undefined && m !== null) {
                                            var url;
                                            url = 'https://' + m[0];
                                            var task = {
                                                id: tasks[i].jobs_contacts_id,
                                                url: url,
                                                status: STATUS_CREATED
                                            };

                                            obj.tasks.push(task);
                                        } else {
                                            console.log('Invalid url');
                                        }
                                    }
                                }
                                obj.configs.appTimeout = RETRY_GET_TASKS * tasks.length;
                            } else {
                                obj.configs.appTimeout = RETRY_GET_TASKS;
                            }

                            if (typeof callback == 'function') {
                                callback(obj);
                            }
                        }
                    )
                }
            });
        }
    },

    setTasks: function (data, callback) {
        var obj = this;
        chrome.identity.getProfileUserInfo(function (userInfo) {
            if (userInfo !== undefined) {
                console.log('setTasks');
                var user = {
                    email: userInfo.email
                };
                obj.user = user;
                obj.counter++;
                wsGrabber.setTasks(data, callback);
            }
        });
    }
};

browserExtension.run();
