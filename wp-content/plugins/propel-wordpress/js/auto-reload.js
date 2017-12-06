jQuery(document).ready( function() {
	// console.log('PROPEL: ready to auto-reload');
	!function(){function n(n){var o="visible",e="hidden",d={focus:o,focusin:o,pageshow:o,blur:e,focusout:e,pagehide:e};n=n||window.event,n.type in d ? document.body.setAttribute('data-focusstatus',d[n.type]):document.body.setAttribute('data-focusstatus',(this[i]?"hidden":"visible")),window["on"+document.body.getAttribute('data-focusstatus')]()}var i="hidden";i in document?document.addEventListener("visibilitychange",n):(i="mozHidden")in document?document.addEventListener("mozvisibilitychange",n):(i="webkitHidden")in document?document.addEventListener("webkitvisibilitychange",n):(i="msHidden")in document?document.addEventListener("msvisibilitychange",n):"onfocusin"in document?document.onfocusin=document.onfocusout=n:window.onpageshow=window.onpagehide=window.onfocus=window.onblur=n,window.onvisible=function(){console.log("window is visible, no functionality")},window.onhidden=function(){console.log("window is hidden, no functionality")},void 0!==document[i]&&n({type:document[i]?"blur":"focus"})}();

	window.onvisible = function() {
		window.setTimeout(function() { // wait half a second
			window.location.reload(true); // then reload
		}, 500 );
	};

	jQuery('body').on('click','.grassblade_launch_link.notcompleted', function(e) {
		// console.log('PROPEL: Clicking out on an unfinished link ...');
	})
});
