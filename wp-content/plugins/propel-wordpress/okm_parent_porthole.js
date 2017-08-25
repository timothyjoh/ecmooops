// JavaScript Document
var onMessage = function(messageEvent){
  var locationOrigin = function(){
    if (!window.location.origin) {
      // http://tosbourn.com/a-fix-for-window-location-origin-in-internet-explorer/
      return window.location.origin = window.location.protocol + "//" + window.location.hostname + (window.location.port ? ':' + window.location.port: '');
    } else {
      return window.location.origin;
    }
  };

  var message = Object.keys(messageEvent.data)[0];
  var parameter = messageEvent.data[message]; 

  if(message === 'scrollTop'){
    window.scrollTo(0, 0);  
  } else if(message === 'scrollToElement'){
    jQuery('html, body').animate({ scrollTop: 650 }, 500);
  } else if(message === 'navToOrderKeys'){
    window.location = locationOrigin() + parameter;
  }
};

var windowProxy;

jQuery(window).load(function(){
  // Create a proxy window to send to and receive 
  // messages from the iFrame 
  windowProxy = new Porthole.WindowProxy('vendor/porthole/proxy.html', 'okm-frame');

  // Register an event handler to receive messages;
  windowProxy.addEventListener(onMessage);
});