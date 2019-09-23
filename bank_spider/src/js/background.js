var spiderExtension = {
	task: {
		company: {
			status: COMPANY_READY,
			data: {},
			url: '',
			tab_id: null,
			timeout: null
		},
		search: {
			status: SEARCH_READY,
			url: '',
			page: null,
			tab_id: null,
		},
		employees: [],
		/*
		Employee object
		{
			data: {},
			url: '',
			status: EMPLOYEE_READY,
			tab_id: null,
		}
		*/
		status: TASK_READY,
		search_keywords: '',
		jobs_companies_id: null
	},
	window_id: 0,
	status: APP_RUNNING,
	timeout: null,
	user: {
		email: ''
	},
	run: function () {
		console.log('Bank Spider running ...')
		obj = this;

		obj.timeout = setInterval(function () {
			var d = new Date;
			var hour = d.getHours();
			var day = d.getDay();

			if ((hour >= 9 && hour <= 18) && (day > 0 && day < 6)) {
				if (obj.status === APP_RUNNING) {
					if (obj.user.email == '') {
						chrome.identity.getProfileUserInfo(function (userInfo) {
							obj.user.email = userInfo.email
						});
					} else {
						if (obj.task == undefined) {
							obj.task = {
								status: TASK_READY,
								jobs_companies_id: null,
								search_keywords: DEFAULT_SEARCH_KEYWORDS,
								company: {
									status: COMPANY_READY,
									url: '',
									data: {},
									tab_id: null,
									timeout: null
								},
								search: {
									status: SEARCH_READY,
									url: '',
									page: 0,
									tab_id: null,
									timeout: null
								},
								employees: []
							}
						}

						console.log(obj.task.status);
						switch (obj.task.status) {
							case TASK_READY:
								obj.getTask();
								break;
							case TASK_RUNNING_COMPANY:
								obj.startCompanyFlow();
								break;
							case TASK_RUNNING_SEARCH:
								if (obj.task.search.tab_id === null) {
									obj.startSearchFlow();
								}
								break;
							case TASK_RUNNING_EMPLOYEES:
								obj.startEmployeesFlow();
								break;
							case TASK_DONE:
								obj.task.status = TASK_READY;

								wsCirenio.closeJob(obj.task.jobs_companies_id, function (data) {
									console.log('#### TASK DONE');
								});
								break;
						}
					}
				} else if (obj.status === APP_WAITING) {
					console.log(obj.status);
					wsCirenio.checkLimit(function (data) {
						if (data.run == true) {
							obj.status = APP_RUNNING;
							chrome.storage.local.get(['leadgen_storage_task'], function (result) {
								obj.task = result.leadgen_storage_task;
							});
						}
					});
				} else if (obj.status === APP_PAUSE) {
					/// ????????????
					// Cómo salimos de la pausa después del login?
				}
			}
		}, APP_TIMEOUT_LIMIT);

		// Listeners
		chrome.runtime.onMessage.addListener(function (request, sender, sendResponse) {
			if (sender.tab !== undefined) {
				switch (request.type) {
					case MESSAGE_COMPANY_DONE:
						if (sender.tab.id == obj.task.company.tab_id) {
							console.log(request.type);
							console.log(request);

							if (request.search_url !== undefined) {
								obj.task.search.url = request.search_url;
								obj.task.company.data = request.data;
								obj.task.company.status = COMPANY_DONE;
								obj.task.status = TASK_RUNNING_SEARCH;
							} else {
								obj.task.company.status = COMPANY_TIMEOUT;
								obj.task.status = TASK_DONE;
							}

							wsCirenio.sendCompany(obj.task.jobs_companies_id, obj.task.company.data, function (data) {
								console.log(data);
							});
						}
						break;

					case MESSAGE_SEARCH_CHECK:
						if (sender.tab.id == obj.task.search.tab_id) {
							console.log(request.type);
							if (obj.task.search.url.indexOf("/sales/search/people/list/employees-for-account/") > -1) {
								var responseObj = { start: true };
								console.log(responseObj);
								sendResponse(responseObj);
							} else {
								obj.task.search.url = request.url;
								obj.task.search.status = SEARCH_READY;
							}
						}
						break;

					case MESSAGE_SEARCH_DONE:
						if (sender.tab.id == obj.task.search.tab_id) {
							console.log(request.type);
							console.log(request.employees);
							obj.task.employees = [];
							if (request.employees.length > 0) {
								for (var k in request.employees) {
									var employee = {
										data: {},
										url: 'https://www.linkedin.com' + request.employees[k],
										status: EMPLOYEE_READY,
										tab_id: null,
									};

									obj.task.employees.push(employee);
								}
							}

							obj.task.search.status = SEARCH_DONE;
							obj.task.status = TASK_RUNNING_EMPLOYEES;
						}
						break;

					case MESSAGE_EMPLOYEE_DONE:
						var finish = true;
						var startedByLeadgen = false;

						for (var i in obj.task.employees) {
							if (sender.tab.id == obj.task.employees[i].tab_id) {
								console.log('#' + i);
								console.log(request.data);
								obj.task.employees[i].data = request.data;
								obj.task.employees[i].status = EMPLOYEE_DONE;
								startedByLeadgen = true;
							}

							if (obj.task.employees[i].status !== EMPLOYEE_DONE && obj.task.employees[i].status !== EMPLOYEE_TIMEOUT) {
								finish = false;
							}
						}

						if (finish && startedByLeadgen) {
							obj.task.search.status = SEARCH_READY;
							obj.task.status = TASK_RUNNING_SEARCH;

							wsCirenio.sendEmployees(obj.task.jobs_companies_id, obj.task.employees, function (data) {
								wsCirenio.checkLimit(function (data) {
									if (data.run == false) {
										obj.status = APP_WAITING;
										chrome.storage.local.set({ leadgen_storage_task: obj.task }, function () {
											console.log('leadgen_storage_task');
											console.log(obj.task);
										});
									}
								});
							});
						}
						break;

					case MESSAGE_APP_AUTH_ERROR:
						obj.status = APP_PAUSE;
						break;
				}
			}
		});
	},
	getTask: function () {
		var obj = this;

		wsCirenio.getTask(function (data) {
			obj.task.status = TASK_READY;
			obj.task.jobs_companies_id = null;
			obj.task.search_keywords = DEFAULT_SEARCH_KEYWORDS;

			obj.task.company = {
				status: COMPANY_READY,
				url: '',
				data: {},
				tab_id: null,
				timeout: null
			}

			obj.task.search = {
				status: SEARCH_READY,
				url: '',
				page: 0,
				tab_id: null,
				timeout: null
			};

			obj.task.employees = [];

			if (data.success == false) {
				obj.status = APP_WAITING;
			} else {
				console.log('#### NEW TASK');
				console.log(data.job);
				if (data.job !== null) {
					obj.task.status = TASK_RUNNING_COMPANY;
					obj.task.jobs_companies_id = data.job.jobs_companies_id;
					obj.task.search_keywords = DEFAULT_SEARCH_KEYWORDS;
					obj.task.company.url = data.job.url;
				}
			}
		});
	},
	checkWindow: function (obj, callback) {
		chrome.windows.get(obj.window_id, function () {
			if (chrome.runtime.lastError !== undefined) {
				chrome.windows.create({ url: 'chrome-extension://' + chrome.runtime.id + '/main.html' }, function (newWin) {
					obj.window_id = newWin.id;
					callback();
				});
			} else {
				callback();
			}
		});
	},
	startCompanyFlow: function () {
		var obj = this;

		if (obj.task.company.status == COMPANY_READY) {
			obj.checkWindow(obj, function () {
				var url = obj.task.company.url;
				chrome.tabs.create({ windowId: obj.window_id, url: url, active: true }, function (tab) {
					obj.task.company.status = COMPANY_RUNNING;
					obj.task.company.tab_id = tab.id;

					obj.task.company.timeout = setTimeout(function () {
						clearTimeout(obj.task.company.timeout);
						obj.task.company.timeout = null;

						chrome.tabs.remove(tab.id, function () {
							var lastError = chrome.runtime.lastError;
						});

						if (obj.task.company.status == COMPANY_RUNNING) {
							obj.task.company.status = COMPANY_TIMEOUT;
							obj.task.status = TASK_DONE;
						}
					}, COMPANY_TIMEOUT_LIMIT);
				});
			});
		}
	},
	startSearchFlow: function () {
		var obj = this;
		if (obj.task.search.status == SEARCH_READY) {
			if (obj.task.search.page > SEARCH_PAGES_LIMIT) {
				obj.task.search.status = SEARCH_DONE;
				obj.task.status = TASK_DONE;
			} else {
				obj.checkWindow(obj, function () {
					if (obj.task.search.page > 0) {
						if (obj.task.search.url.indexOf("searchSessionId") > -1) {
							var filters = '&' + obj.task.search_keywords;
						} else {
							var filters = '?' + obj.task.search_keywords;
						}

						var url = obj.task.search.url + filters + '&page=' + obj.task.search.page;
					} else {
						var url = obj.task.search.url;
					}

					obj.task.search.page++;
					console.log('#### SEARCH');
					console.log(url);

					chrome.tabs.create({ windowId: obj.window_id, url: url, active: true }, function (tab) {
						obj.task.search.status = SEARCH_RUNNING;
						obj.task.search.tab_id = tab.id;

						obj.task.search.timeout = setTimeout(function () {
							clearTimeout(obj.task.search.timeout);
							obj.task.search.timeout = null;

							chrome.tabs.remove(tab.id, function () {
								var lastError = chrome.runtime.lastError;
							});

							obj.task.search.tab_id = null;

							if (obj.task.search.status == SEARCH_RUNNING) {
								obj.task.search.status = SEARCH_TIMEOUT;
								obj.task.status = TASK_DONE;
							}
						}, SEARCH_TIMEOUT_LIMIT);
					});
				});
			}
		}
	},
	startEmployeesFlow: function () {
		var obj = this;
		obj.checkWindow(obj, function () {
			var url = '';
			var key = null;
			var allDoneOrTimeout = true;

			for (var i in obj.task.employees) {
				if (url == '' && obj.task.employees[i].status == EMPLOYEE_READY) {
					url = obj.task.employees[i].url;
					key = i;
				}

				if (obj.task.employees[i].status == EMPLOYEE_READY || obj.task.employees[i].status == EMPLOYEE_RUNNING) {
					allDoneOrTimeout = false;
				}
			}

			if (allDoneOrTimeout && obj.task.employees.length < SEARCH_ITEM_BY_PAGE) {
				obj.task.status = TASK_DONE;
			} else {
				if (url != '') {
					console.log(url);
					chrome.tabs.create({ windowId: obj.window_id, url: url, active: true }, function (tab) {
						obj.task.employees[key].status = EMPLOYEE_RUNNING;
						obj.task.employees[key].tab_id = tab.id;

						obj.task.employees[key].timeout = setTimeout(function () {
							clearTimeout(obj.task.employees[i].timeout);
							obj.task.employees[key].timeout = null;

							chrome.tabs.remove(tab.id, function () {
								var lastError = chrome.runtime.lastError;
							});

							if (obj.task.employees[key].status === EMPLOYEE_RUNNING) {
								obj.task.employees[key].status = EMPLOYEE_TIMEOUT;
							}
						}, EMPLOYEE_TIMEOUT_LIMIT);
					});
				} else {
					obj.task.status = TASK_DONE;
				}
			}
		});
	},
};
// End spiderExtension

spiderExtension.run();
