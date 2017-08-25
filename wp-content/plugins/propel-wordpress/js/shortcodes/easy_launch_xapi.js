jQuery(document).ready(function() {
  if ( jQuery("#sfwd-mark-complete").length == 0 ) {
    jQuery('.if_topic_completed').show();
  }
});
(function() {
  var hidden = "hidden";

  // Standards:
  if (hidden in document)
    document.addEventListener("visibilitychange", onchange);
  else if ((hidden = "mozHidden") in document)
    document.addEventListener("mozvisibilitychange", onchange);
  else if ((hidden = "webkitHidden") in document)
    document.addEventListener("webkitvisibilitychange", onchange);
  else if ((hidden = "msHidden") in document)
    document.addEventListener("msvisibilitychange", onchange);
  // IE 9 and lower:
  else if ("onfocusin" in document)
    document.onfocusin = document.onfocusout = onchange;
  // All others:
  else
    window.onpageshow = window.onpagehide
    = window.onfocus = window.onblur = onchange;

  function onchange (evt) {
    var v = "visible", h = "hidden",
        evtMap = {
          focus:v, focusin:v, pageshow:v, blur:h, focusout:h, pagehide:h
        };

    evt = evt || window.event;
    if (evt.type in evtMap) {
      document.body.dataset.focusstatus = evtMap[evt.type];
    }
    else {
      document.body.dataset.focusstatus = this[hidden] ? "hidden" : "visible";
    }
    window["on"+document.body.dataset.focusstatus](); // execute a callback
  }
  window.onvisible = function() {
    console.log("window is visible, no functionality");
  };
  window.onhidden = function() {
    console.log("window is hidden, no functionality");
  };
  // set the initial state (but only if browser supports the Page Visibility API)
  if( document[hidden] !== undefined )
    onchange({type: document[hidden] ? "blur" : "focus"});
})();


jQuery(document).ready(function() {
  jQuery(".grassblade_launch_link").click(function() {
    window.onvisible = function() {
      window.location.reload(true);
    };
  });

  window.certificate_modal_hidden = function() {
    console.log("certificate_modal_hidden");
    jQuery('#header-secondary-outer').removeClass( 'hideme' );
    jQuery('#header-outer').removeClass( 'hideme' );
    jQuery('.page-header-no-bg').removeClass( 'hideme' );
    jQuery("#footer-outer").removeClass('hideme');
  };
  window.certificate_modal_shown = function() {
    console.log("certificate_modal_shown");
    jQuery('#header-secondary-outer').addClass( 'hideme' );
    jQuery('#header-outer').addClass( 'hideme' );
    jQuery('.page-header-no-bg').addClass( 'hideme' );
    jQuery("#footer-outer").addClass('hideme');
  };
});
