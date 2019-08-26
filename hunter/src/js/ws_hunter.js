var wsHunter = {
  token: undefined,

  getToken: function (email, callback) {
    var obj_ws = this;
    var xhr = new XMLHttpRequest();
    xhr.open('POST', HUNTER_URL + '/auth');
    xhr.setRequestHeader('Content-Type', 'application/json');

    xhr.onload = function () {
      if (xhr.status === 200) {
        var data = JSON.parse(xhr.responseText);
        obj_ws.token = data.token;
        obj_ws.getTask(callback);
      }
    };
    
    xhr.send(JSON.stringify({"email": email}));
  },

  getTask: function (callback) {
    var obj_ws = this;

    if (obj_ws.token !== undefined) {
      var xhr = new XMLHttpRequest();
      xhr.open('GET', HUNTER_URL + '/job');
      xhr.setRequestHeader('Authorization', 'Bearer ' + obj_ws.token);

      xhr.onload = function () {
        if (xhr.status === 200) {
          var data = JSON.parse(xhr.responseText);
          callback(data);
        }

        if (xhr.status === 401) {
          obj_ws.getToken(hunterExtension.user.email, callback);
        }
      };
      xhr.send();
    } else {
      obj_ws.getToken(hunterExtension.user.email, callback);
    }
  },

  sendCompany: function (jobs_companies_id, companyData, callback) {
    var obj_ws = this;

    var xhr = new XMLHttpRequest();
    xhr.open('POST', HUNTER_URL + '/job/' + jobs_companies_id + '/company');
    xhr.setRequestHeader('Content-Type', 'application/json');
    xhr.setRequestHeader('Authorization', 'Bearer ' + obj_ws.token);

    xhr.onload = function () {
      if (xhr.status === 200) {
        var data = xhr.responseText;
        callback(data);
      }
    };

    xhr.send(JSON.stringify({"status": "done", "result": companyData}));
  },

  sendEmployees: function (jobs_companies_id, employees, callback) {
    var obj_ws = this;

    var xhr = new XMLHttpRequest();
    xhr.open('POST', HUNTER_URL + '/job/' + jobs_companies_id + '/contacts');
    xhr.setRequestHeader('Content-Type', 'application/json');
    xhr.setRequestHeader('Authorization', 'Bearer ' + obj_ws.token);

    xhr.onload = function () {
      if (xhr.status === 200) {
        var data = xhr.responseText;
        callback(data);
      }
    };

    var formatedData = [];
    for (var i in employees) {
      formatedData.push(employees[i].data);
    }

    xhr.send(JSON.stringify({"status": "done", "result": formatedData}));
  },

  closeJob: function (jobs_companies_id, callback) {
    var obj_ws = this;

    var xhr = new XMLHttpRequest();
    xhr.open('PUT', HUNTER_URL + '/job/' + jobs_companies_id);
    xhr.setRequestHeader('Content-Type', 'application/json');
    xhr.setRequestHeader('Authorization', 'Bearer ' + obj_ws.token);

    xhr.onload = function () {
      if (xhr.status === 200) {
        var data = xhr.responseText;
        callback(data);
      }
    };

    xhr.send(JSON.stringify({"status": "done"}));
  },

  checkLimit: function (callback) {
    var obj_ws = this;

    var xhr = new XMLHttpRequest();
    xhr.open('GET', HUNTER_URL + '/limit');
    xhr.setRequestHeader('Content-Type', 'application/json');
    xhr.setRequestHeader('Authorization', 'Bearer ' + obj_ws.token);

    xhr.onload = function () {
      if (xhr.status === 200) {
        var data = JSON.parse(xhr.responseText);
        callback(data);
      }
    };

    xhr.send();
  }
}