// Pop up
var popUp = {
    tasks: [],
    run: function () {
        obj = this;
        chrome.runtime.sendMessage({status: CTRL_GET_DATA}, function (response) {
            obj.tasks = response.tasks;
            obj.user = response.user;

            //obj.renderList();

            var btnResume = document.getElementById('btn_resume');
            var btnPause = document.getElementById('btn_pause');

            if (response.running) {
                btnPause.style.display = "block";
            } else {
                btnResume.style.display = "block";
            }

            var btn_resume = document.getElementById('btn_resume');
            btn_resume.addEventListener('click', function () {
                chrome.runtime.sendMessage({status: CTRL_RESUME}, function () {});
                window.close();
            }, false);

            var btn_pause = document.getElementById('btn_pause');
            btn_pause.addEventListener('click', function () {
                chrome.runtime.sendMessage({status: CTRL_PAUSE}, function () {});
                window.close();
            }, false);

            var alert = document.getElementById('user-status');
            if (response.configs.enabled === true) {
                alert.style.opacity = 1;
            } else {
                alert.style.opacity = 0.3;
            }
            alert.setAttribute("title", response.user.email);

            document.getElementById('counter').textContent = response.counter;
        });
    },

    renderList: function () {
        var tasks = this.tasks;
        
        if (tasks.length > 0) {
            var listRaw = "";

            tasks.forEach(function (task, id) {
                var statusHml = '';

                switch (task.status) {
                    case STATUS_ERROR:
                        statusHml = "<span tab_id='" + task.tab_id + "' class='task_status error tablink tablink_error'> ## Error ## </span>";
                        break;

                    case STATUS_CREATED:
                        if (task.tab_id !== undefined) {
                            statusHml = "<span tab_id='" + task.tab_id + "' class='task_status tablink created running'> ## Running ## </span>";
                        } else {
                            statusHml = "<span tab_id='" + task.tab_id + "' class='task_status tablink created'> ## Waiting ## </span>";
                        }
                        break;

                    case STATUS_DONE:
                        statusHml = "<span tab_id='" + task.tab_id + "' class='task_status tablink end'> ## End ## </span>";
                        break;

                    case STATUS_NO_RESPONSE:
                        statusHml = "<span tab_id='" + task.tab_id + "' class='task_status tablink no-response'> ## no-response ## </span>";
                        break;

                }

                var linkHtml = "<span tab_id='" + task.tab_id + "' class='task_url tablink' title='" + task.url + "'>#" + task.id + "</span>";

                listRaw += "<div class='task_item' id='task_item_" + task.tab_id + "'>";
                listRaw += linkHtml + statusHml;
                listRaw += "</div>";
            });

            var elem = document.getElementById('list');

            if (listRaw !== "" && elem != undefined) {
                elem.innerHTML = listRaw;
            }
        }
    },

    openTabMessage: function (tab_id) {
        var obj = this;

        if (tab_id !== undefined) {
            chrome.runtime.sendMessage({status: CTRL_NEW_TAB, store: false, tab_id: tab_id}, function (response) {});
        }
    }
};
// End Pop up

popUp.run();