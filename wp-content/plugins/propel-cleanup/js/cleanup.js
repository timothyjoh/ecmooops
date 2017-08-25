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
   
   jQuery("#do-reset").on("click", function() {
      var t = genToken();
      var p = "WARNING:\n\n";
      p = p + "Clean up the PROPEL database? Depending upon your selections, this will clear out unwanted users, posts, comments, pages and lesson content.";
      p = p + "\n\nThis action cannot be reversed.";
      p = p + "\n\nNote: This process may take several minutes. Be patient."
      p = p + "\n\nTo contine, enter '"+t+"' below.";
      resp = prompt(p);
      if ( resp != t ) {
         return;
      }
      jQuery("#working").show();
      var opts = [];
      jQuery(".clean-opt").each(function() {
         if (jQuery(this).is(':checked') ) {
            opts.push( jQuery(this).val() );
         }
      });
      
      jQuery.ajax({
         method: 'POST',
         url: ajaxurl,
         data: {  
            action: 'do_reset',
            opts: opts.join(",")
         }, 
         complete: function( jqXHR, textStatus ) {
            var out = jQuery("#migrate-msg");
            if ( jqXHR.responseJSON.success) {
               out.text("The database has been reset!");
            } else {
               out.text("Database reset errors: "+jqXHR.responseJSON.data);   
            }
            out.show();
            jQuery("#working").hide();
         }
      });
   });
});