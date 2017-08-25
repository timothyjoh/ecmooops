jQuery(function() {
   jQuery("#working").insertAfter("#wpwrap");
   
   var genToken = function () {
      var text = "";
      var possible = "abcdefghijklmnopqrstuvwxyz0123456789";

      for( var i=0; i < 5; i++ ) {
         text += possible.charAt(Math.floor(Math.random() * possible.length));
      }
      return text;
   };
   
   jQuery("#do-autocomplete").on("click", function() {
      var course = jQuery("#course option:selected").text();
      var t = genToken();
      var p = "WARNING:\n\n";
      p = p + "Mark all listed users as 100% Complete for:\n\n   '"+course+"''?";
      p = p + "\n\nThis action cannot be reversed.";
      p = p + "\n\nTo contine, enter '"+t+"' below.";
      
      var courseId = jQuery('#course').val();
      var emails = jQuery('#user-emails').val();
      if (emails.length == 0 ) {
         alert("At least one user email is required!");
         return;
      }
      
      resp = prompt(p);
      if ( resp != t ) {
         return;
      }
         
      jQuery("#working").show();
      jQuery.ajax({
         method: 'POST',
         url: ajaxurl,
         data: {  
            action: 'do_autocomplete',
            course: courseId,
            emails: emails
         }, 
         complete: function( jqXHR, textStatus ) {
            if ( jqXHR.responseJSON &&  jqXHR.responseJSON.data) {
               alert(  jqXHR.responseJSON.data );   
            } else {
               alert("All users have been marked complete");
            }
            jQuery('#user-emails').val("");
            jQuery("#working").hide();
         }
      });
   });
});