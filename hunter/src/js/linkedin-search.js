setTimeout(function () {
  chrome.runtime.sendMessage({
    url: window.location.href,
    type: MESSAGE_SEARCH_CHECK
  }, function (response) {
    /*
    if (response.start === true) {
      window.scrollTo(0,document.body.scrollHeight);
      setTimeout(function () {
        getScraperNavigator(function (data) {
          chrome.runtime.sendMessage({
            employees: data.employees,
            type: MESSAGE_SEARCH_DONE
          }, function (response) {});
        });
      }, 3000);
    }
    */
  });
}, 4000);

/*
function getScraperNavigator(done) {
  SalesNavigatorEmployeesExtractor.init(done);
}
*/