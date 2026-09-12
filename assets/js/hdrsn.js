/**
 * HDWebmobile Recent Sales Notifications.
 *
 * This script never fetches anything from the server -- window.hdrsnData is a fixed,
 * already-safe payload built server-side (see class-hdrsn-frontend.php) and printed as
 * inline JSON before this file loads. Every notification is written into the page with
 * textContent only; this file never assigns innerHTML and never inserts markup.
 */
(function () {
	'use strict';

	var data = window.hdrsnData;
	if (!data || !data.items || !data.items.length) { return; }

	var el = document.getElementById('hdrsn-popup');
	if (!el) { return; }

	var i = 0;

	function showNext() {
		var item = data.items[i % data.items.length];
		i++;

		el.textContent = '';

		var link = document.createElement('a');
		link.href = item.url;
		link.textContent = item.text;
		el.appendChild(link);

		el.hidden = false;
		el.classList.add('is-visible');

		window.setTimeout(function () {
			el.classList.remove('is-visible');
			window.setTimeout(function () { el.hidden = true; }, 400);
		}, data.displayMs);
	}

	window.setTimeout(showNext, 1500);
	window.setInterval(showNext, data.intervalMs);
})();
