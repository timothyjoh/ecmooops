jQuery(document).ready(function() {
  setTimeout(function(){ 
    findAltMarkComplete()
  }, 500); 
});

var findAltMarkComplete = function() {
  if (elementExists("#altmarkcomplete")) {
    setupAltMarkComplete();
  }
};
var setupAltMarkComplete = function() {
  jQuery("#altmarkcomplete").removeClass("mk-smooth");
  if (elementExists("#sfwd-mark-complete")) {
    replaceMarkCompleteForm();
  } else {
    replaceNextButton();
  }
};
var replaceMarkCompleteForm = function() {
  jQuery("#sfwd-mark-complete").hide();
  jQuery(document).on('click', '#altmarkcomplete', function (e) {
    e.preventDefault();
    console.log("Alt Mark Complete clicked, Form Submitted");
    jQuery("#sfwd-mark-complete").show();
    jQuery("#sfwd-mark-complete input").click();
  });
};
var replaceNextButton = function() {
  jQuery("#learndash_next_prev_link").hide();
  jQuery(document).on('click', '#altmarkcomplete', function (e) {
    e.preventDefault();
    console.log("Alt Mark Complete clicked, Next Activity Navigating");
    var url = jQuery("#learndash_next_prev_link a[rel='next']").attr("href");
    console.log(["time to go to ",url]);
    window.location.href = url;
  });
};

var elementExists = function(selector) {
  return jQuery(selector).length > 0
}

