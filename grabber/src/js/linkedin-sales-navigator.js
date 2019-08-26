chrome.runtime.sendMessage({url: window.location.href}, function (response) {
    if (response !== undefined && response.action === CTRL_START) {
        var selector = document.getElementById("login");
        
        if (selector === null) {
            setTimeout(function () {
                var btnUnlock = jQueryScraper("button.profile-out-of-network__upgrade-btn[data-control-name='unlock']");
                
                if (btnUnlock.length > 0) {
                    btnUnlock.click();
                    location.reload();
                } else {
                    getScraperNavigator(function (data) {
                        chrome.runtime.sendMessage({status: STATUS_DONE, result: data}, function (response) {});
                    });   
                }
            }, 3000);
        } else {
            window.location = LOGIN_LINKEDIN_URL;
        }
    }
});

function getScraperNavigator(done) {
    SalesNavigatorDataExtractor.init(done);
}