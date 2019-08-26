/*
setTimeout(function () {
  getScraperNavigator(function (data) {
    chrome.runtime.sendMessage({
      data: data,
      type: MESSAGE_COMPANY_DONE,
      search_url: data.employeesSearchPageUrl
    }, function (response) { });
  });
}, 3000);


function getScraperNavigator(done) {
  SalesNavigatorCompanyExtractor.init(done);
}
*/