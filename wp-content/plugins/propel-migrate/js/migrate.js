jQuery(function() {
   jQuery("#working").insertAfter("#wpwrap");

   jQuery(".test-okm").on("click", function() {
      jQuery("input.test-okm").addClass("disabled");
      jQuery.ajax({
         method: 'POST',
         url: ajaxurl,
         data: {  
            action: 'validate_key',
            key: jQuery("#tenant-key").val(),
            url:  jQuery("#okm-url").val()
         }, 
         complete: function( jqXHR, textStatus ) {
            if ( jqXHR.responseJSON.success ) {
               jQuery("#okm-status").removeClass("pass").removeClass("fail");
               jQuery("#okm-status").addClass("pass");
            } else {
               jQuery("#okm-status").removeClass("pass").removeClass("fail");
               jQuery("#okm-status").addClass("fail");
            }
            jQuery("input.test-okm").removeClass("disabled");
         }
      });
   });

   jQuery("input.new-tenant").on("click", function() {
      jQuery("#okm-controls").hide();
      jQuery("div.new-tenant").show();
      jQuery("div.new-tenant input[type=text]").val("");
   });

   jQuery("input.cancel-tenant").on("click", function() {
      jQuery("div.new-tenant").hide();
      jQuery("#okm-controls").show();
   });

   jQuery("input.create-tenant").on("click", function() {
      jQuery("input.create-tenant").addClass("disabled");
      jQuery("input.cancel-tenant").addClass('disabled');
      var tgt = "prod";
      if ( jQuery(this).hasClass("stage") ) {
         tgt = "stage";
      }
      jQuery.ajax({
         method: 'POST',
         url: ajaxurl,
         data: {  
            action: 'new_tenant',
            target: tgt,
            name: jQuery("#new-name").val(),
            url:  jQuery("#new-url").val(),
            key:  jQuery("#new-key").val(),
            subaccount:  jQuery("#new-subaccount").val(),
            assign:  jQuery("#new-assign").val(),
            revoke:  jQuery("#new-revoke").val(),
            sso:  jQuery("#enable-sso").is(':checked'),
            a0name:  jQuery("#auth0-name").val()
         }, 
         complete: function( jqXHR, textStatus ) {
            if ( jqXHR.responseJSON.success ) {
               var key = jqXHR.responseJSON.data.key;
               jQuery("#tenant-key").val(key);
               jQuery("#okm-status").removeClass("pass").removeClass("fail");
               alert("Tenant created");
               jQuery("div.new-tenant").hide();
               jQuery("#okm-controls").show();
            } else {
               alert("Unable to create new tenant:\n\n"+ jqXHR.responseJSON.data.error);
            }
            jQuery("input.create-tenant").removeClass("disabled");
            jQuery("input.cancel-tenant").removeClass('disabled');
         }
      });
   });

   var genToken = function () {
      var text = "";
      var possible = "abcdefghijklmnopqrstuvwxyz0123456789";

      for( var i=0; i < 5; i++ ) {
         text += possible.charAt(Math.floor(Math.random() * possible.length));
      }
      return text;
   };

   jQuery("#do-wipe").on("click", function() {
      var t = genToken();
      var p = "WARNING:\n\n";
      p = p + "This will wipe all customer data, orders, posts, comments, courses and logs, returning this site to a clean starting state.";
      p = p = "\nOnly settings and non-customer users will be preserved.";
      p = p + "\n\nThis action cannot be reversed.";
      p = p + "\n\nNote: This process may take several minutes. Be patient."
      p = p + "\n\nTo contine, enter '"+t+"' below.";
      resp = prompt(p);
      if ( resp != t ) {
         return;
      }
      jQuery("#working").show();
      jQuery.ajax({
         method: 'POST',
         url: ajaxurl,
         data: {  
            action: 'do_wipe'
         }, 
         complete: function( jqXHR, textStatus ) {
            var out = jQuery("#migrate-msg");
            if ( jqXHR.responseJSON.success) {
               out.text("The database has been reset");
            } else {
               out.text("Database reset errors: "+jqXHR.responseJSON.data);   
            }
            out.show();
            jQuery("#working").hide();
         }
      });
   });

   jQuery("button.migrate").on("click", function() {
      var migrateTo = "stage";
      var name = "Staging";
      if ( jQuery(this).attr("id")=="do-migration-prod" ) {
         migrateTo = "prod";
         name = "Production";
      }
      resp = confirm("This will update all settings to "+name+". Are you sure?");
      if ( resp ) {
         jQuery("button.migrate").addClass("disabled");
         jQuery.ajax({
            method: 'POST',
            url: ajaxurl,
            data: {  
               action: 'do_migrate',
               target: migrateTo
            }, 
            complete: function( jqXHR, textStatus ) {
               var out = jQuery("#migrate-msg");
               if ( jqXHR.responseJSON.success) {
                  out.text("All settings were successfully migrated");
                  if ( migrateTo === "stage" ) {
                     jQuery("#tab-stage").text("Staging (Current)");
                     jQuery("#tab-prod").text("Production");
                  } else {
                     jQuery("#tab-stage").text("Staging");
                     jQuery("#tab-prod").text("Production (Current)");
                  }
               } else {
                  out.text("Migration errors: "+jqXHR.responseJSON.data);    
               }
               out.show();
               jQuery("button.migrate").removeClass("disabled");
            }
         });
      }
   });
});