setTimeout(function () {
  makeLogin(function () {
    console.log('test');
    chrome.runtime.sendMessage({
      type: MESSAGE_LOGIN_DONE
    }, function (response) { });
  });
}, 3000);

function makeLogin(done) {
  SantanderLoginHanlder.init(done);
}
