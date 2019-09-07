setTimeout(function () {
    chrome.runtime.sendMessage({ status: STATUS_ERROR, type: STATUS_AUTH_ERROR }, function (response) {});
    var current = "<div style='background-color: rgb(250, 155, 48); padding: 10px; border-radius: 4px; margin-bottom: 10px;'>";
    current += "<h2 style='color: white;'>Mercurio Grabber</h2>";
    current += "<p style='color: white;'>You need a sales navigator account</p>";
    current += "</div>";
    current += document.getElementById("control_gen_1").innerHTML;
    document.getElementById("control_gen_1").innerHTML = current;
}, 3000);
