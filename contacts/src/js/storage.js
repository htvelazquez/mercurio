/*jshint strict:false */
/* jshint ignore:start */
function getFromLocalStorage(key, callback) {
	chrome.storage.sync.get(key, function(items) {
		var returnValue = null;
		if(items.hasOwnProperty(key))
			returnValue = items[key];

		callback.apply(null, [returnValue]);
	});
}

function saveInLocalStorage(key, value, callback) {
	var saveObject = {};
	saveObject[key] = value;

	chrome.storage.sync.set(saveObject, function() {
		callback();
	});
}

function deleteInLocalStorage(key, callback) {
	chrome.storage.sync.remove(key, callback);
}
/* jshint ignore:end */