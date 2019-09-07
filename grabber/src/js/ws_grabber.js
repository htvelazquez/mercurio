var wsGrabber = {
    token: undefined,

    getToken: function (email, callback) {
        var obj_ws = this;
        var xhr = new XMLHttpRequest();
        xhr.open('POST', GRABBER_URL + '/extension/auth');
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        xhr.onload = function() {
            console.log(xhr.status);
            if (xhr.status === 200) {
                var data = JSON.parse(xhr.responseText);
                obj_ws.token = data.token;
                obj_ws.getTasks(callback);
            }
        };
        var params = "email="+email;
        xhr.send(params);
    },

    getTasks: function (callback) {
        var obj_ws = this;

        if (obj_ws.token !== undefined) {
            var xhr = new XMLHttpRequest();
            xhr.open('GET', GRABBER_URL + '/extension/job');
            xhr.setRequestHeader('Authorization', 'Bearer ' + obj_ws.token);

            xhr.onload = function() {
                if (xhr.status === 200) {
                    var data = JSON.parse(xhr.responseText);
                    //browserExtension.configs = data.configs;
                    callback(data.jobs);
                }

                if (xhr.status === 401) {
                    console.log('401');
                    obj_ws.getToken(browserExtension.user.email, callback);
                }
            };
            xhr.send();
        } else {
            obj_ws.getToken(browserExtension.user.email, callback);
        }
    },

    setTasks: function (task, callback) {
        var obj_ws = this;

        var xhr = new XMLHttpRequest();
        xhr.open('POST', GRABBER_URL + '/extension/job/' + task.id);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.setRequestHeader('Authorization', 'Bearer ' + obj_ws.token);

        xhr.onload = function() {
            if (xhr.status === 200) {
                var data = JSON.parse(xhr.responseText);
                callback(data);
            }
        };

        xhr.send(JSON.stringify(task));
    }
}
