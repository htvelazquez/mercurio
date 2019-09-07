/* global jQuery:false */
/* exported jQueryScraper*/
var jQueryScraper = jQuery.noConflict();

;(function ($, window) {
'use strict';
var intervals = {};
var RETRY_INTERVAL = 250;
var removeListener = function(selector) {

	if (intervals[selector]) {
		window.clearInterval(intervals[selector]);
		intervals[selector] = null;
	}
};
// var found = 'waitForSelector.found';

/**
 * https://gist.github.com/PizzaBrandon/5709010
 * @function
 * @property {object} jQuery plugin which runs handler function once specified
 *           element is inserted into the DOM
 * @param {function|string} handler 
 *            A function to execute at the time when the element is inserted or 
 *            string "remove" to remove the listener from the given selector
 * @example jQuery(selector).waitForSelector(function);
 */

$.fn.unWaitAllSelector = function() {
    for (var key in intervals) {
        if(intervals[key] !== null) {
            clearInterval(intervals[key]);
        }
    }

    intervals = {};
};

$.fn.waitForSelector = function(handler, timeoutHandler, maxTimeout, isFirstRun, intervalKey) {

	var selector = this.selector;
	var $this = $(selector);
	// var $elements = $this.not(function() { return $(this).data(found); });

    var timeout = 2500;
    if(typeof maxTimeout !== 'undefined') {
        timeout = maxTimeout;
    }

    if(typeof isFirstRun === 'undefined') {
        isFirstRun = true;
    }

    if(typeof intervalKey === 'undefined') {
        intervalKey = selector + '-' + new Date().getTime();
    }

    if(isFirstRun) {
        if($this.length > 0 ) {
            handler.apply(null, []);
        } else {
            intervals[intervalKey] = window.setInterval(function () {
                timeout -= RETRY_INTERVAL;
                $this.waitForSelector(handler, timeoutHandler, timeout, false, intervalKey);
            }, RETRY_INTERVAL);
        }

    } else {
        if(timeout > 0) {
            // console.info('TICK ', {selector: selector, exists: $this.length, counter: timeout});
            if($this.length > 0) {
                removeListener(intervalKey);
                handler.apply(null, []);
            } else {
                // Tick
                // console.info('TICK-Finish ', {selector: selector, exists: $this.length, counter: timeout});
            }
        } else {
            // console.info('TICK-Finish ', {selector: selector, exists: $this.length, counter: timeout});
            removeListener(intervalKey);    
            if(typeof timeoutHandler === 'function') {
                timeoutHandler.apply(null, []);
            } else {
                throw 'Timeout waiting: ' + selector;
            }
        }
    }

	return $this;
};
 
}(jQueryScraper, window));