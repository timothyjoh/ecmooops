jQuery(document).ready( function(){    
  jQuery('.propel-popup-BG').appendTo(document.body);
  jQuery('.launch-propel-popup').on('click',function(e){
    var clickedButton = this.name;
    jQuery('#' + clickedButton).show();
    jQuery(window).scrollTop(0);
  });
  
  //close if X is click
  jQuery('.propel-popup-body .propel-close').on('click',propel_close_popup);

  // close if ESC is pressed
  jQuery(window).keydown(function(e) {
      if(e.which === 27){
        propel_close_popup();
      }
   });

  //close if background is clicked
  jQuery('.propel-popup-BG').on('click',function(e){
    //don't close-popup submit button is clicked
    if(isSubmitButton(e)){
      return undefined;
    } 
    propel_close_popup();
  });  
  
  //don't close if the form is clicked
  jQuery('.propel-popup-body').on('click',function(e){
    //don't prevent sumbission if the sumbit button is clicked
    if(isSubmitButton(e)){
      return undefined;
    } 
    e.preventDefault();
    return false;
  })

  // HELPERS!  
  function propel_close_popup(){
    jQuery('.propel-popup-BG').hide();
  }
  
  function isSubmitButton(e){
    var gsb = "gform_submit_button_";
    if(e.target.id.slice(0, gsb.length) === gsb){
      return true; /*allows form sumbit*/
    }  
  }
  
})