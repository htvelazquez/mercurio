/* global LI:false, remoteConfig:false */
(function () {
	'use strict';
	if(typeof LI !== 'undefined' && typeof LI.Profile !== 'undefined') {
		var qmContext = document.createElement('script');
		qmContext.setAttribute('id', '__qmContextDataShare');
		qmContext.type = 'text/javascript';
		qmContext.text = 'var __qmContextDataShare=' + JSON.stringify({
			memberId: LI.Profile.data.memberId
		});

		document.head.appendChild(qmContext);
	}

	var images = document.querySelectorAll('#__bandwith-fixed-image');
	if(images.length > 0) {
		for(var i = 0; i < images.length;i++) {
			images[i].remove();
		}
	}

	var bandwithFixedImage = document.createElement('img');
	bandwithFixedImage.style = 'display: none;';
	bandwithFixedImage.src = remoteConfig.bandwithImageURL + '?t=' + (new Date()).getTime();
	bandwithFixedImage.id = '__bandwith-fixed-image';
	document.body.appendChild(bandwithFixedImage);
}());