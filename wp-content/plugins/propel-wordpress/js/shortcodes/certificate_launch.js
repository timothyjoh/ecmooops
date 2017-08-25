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