/*
setTimeout(function () {
  var btnUnlock = jQueryScraper("button.profile-out-of-network__upgrade-btn[data-control-name='unlock']");

  if (btnUnlock.length > 0) {
    btnUnlock.click();
    location.reload();
  }

  setTimeout(function () {
    getScraperNavigator(function (data) {
      chrome.runtime.sendMessage({
        data: data,
        type: MESSAGE_EMPLOYEE_DONE
      }, function (response) { });
    });
  }, 3000);
}, 3000);

function getScraperNavigator(done) {
  SalesNavigatorProfileExtractor.init(done);
}
*/