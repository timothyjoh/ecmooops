	function showHideOptional(id) {
		var table = document.getElementById(id);
		var display_status = table.style.display;

		
		if(display_status == "none")
		{
			table.style.display = "block";
		}
		else
			table.style.display = "none";
	}
	function grassblade_show_lightbox(id, src, width, height) {
		if(document.getElementById("grassblade_lightbox") == null)
			jQuery("body").append("<div id='grassblade_lightbox'></div>");
		
		
			html = "<div class='grassblade_lightbox_overlay'  onClick='return grassblade_hide_lightbox();'></div><div id='" + id + "' class='grassblade_lightbox'  style='width:" + width + "; height:" + height + ";'>" + 
						"<div class='grassblade_close'><a href='#' onClick='return grassblade_hide_lightbox();'>X</a></div>" +
						"<iframe class='grassblade_lightbox_iframe' frameBorder='0' src='" + src + "'></iframe>" +
					"</div>";
				
			jQuery("#grassblade_lightbox").html(html);
			jQuery("#grassblade_lightbox").show();
			
	}
	function grassblade_hide_lightbox() {
		jQuery("#grassblade_lightbox").hide();
		jQuery("#grassblade_lightbox").html('');
		return false;
	}
	function show_xapi_content_meta_box_change() {
		var show_xapi_content = jQuery("#show_xapi_content");
		if(show_xapi_content.length == 0)
			return;

		edit_link = jQuery('#grassblade_add_to_content_edit_link'); 
		if(show_xapi_content.val() > 0) {
			edit_link.show(); 
			jQuery("body").addClass("has_xapi_content");
		}
		else {
			jQuery("body").removeClass("has_xapi_content");
			edit_link.hide();
		}
			
		jQuery("#completion_tracking_enabled").hide();
		jQuery("#completion_tracking_disabled").hide();		

		if(jQuery("#show_xapi_content option:selected").attr("completion-tracking") == "1") {
			jQuery("#completion_tracking_enabled").show();
		}
		else if(jQuery("#show_xapi_content option:selected").attr("completion-tracking") == "")
		{
			jQuery("#completion_tracking_disabled").show();			
		}
	}
	jQuery(window).load(function() {
		if(jQuery("#show_xapi_content").length > 0) {
			jQuery("#show_xapi_content").change(function() {
				show_xapi_content_meta_box_change();
			});
			show_xapi_content_meta_box_change();
		}
		if(jQuery("#grassblade_xapi_content_form").length > 0)
			grassblade_xapi_content_edit_script();
		jQuery(".grassblade_field_group > div.grassblade_field_group_label").click(function() {
			console.log(jQuery(this).parent().children("div.grassblade_field_group_fields").css("display"));
			if(jQuery(this).parent().children("div.grassblade_field_group_fields").css("display") != "none") {
				jQuery(this).parent().children("div.grassblade_field_group_label").children(".dashicons").addClass("dashicons-arrow-right-alt2");
				jQuery(this).parent().children("div.grassblade_field_group_label").children(".dashicons").removeClass("dashicons-arrow-down-alt2");
			}
			else
			{
				jQuery(this).parent().children("div.grassblade_field_group_label").children(".dashicons").removeClass("dashicons-arrow-right-alt2");
				jQuery(this).parent().children("div.grassblade_field_group_label").children(".dashicons").addClass("dashicons-arrow-down-alt2");	
			}
			jQuery(this).parent().children("div.grassblade_field_group_fields").slideToggle();
		});
		jQuery(".grassblade_field_group > div.grassblade_field_group_label").click();
		jQuery(".grassblade_field_group > div.grassblade_field_group_label:first").click();
	});

	function grassblade_xapi_content_edit_script() {
		grassblade_enable_button_selector();
		
		jQuery("h2.gb-content-selector a").click(function() {
			jQuery("h2.gb-content-selector a").removeClass("nav-tab-active");
			jQuery(this).addClass("nav-tab-active");
			if(jQuery(this).hasClass("nav-tab-content-url")) {
				jQuery("#field-src").show();
				jQuery("#field-activity_id").show();
				jQuery("#field-xapi_content").hide();
				jQuery("#field-video").hide();
				jQuery("#field-dropbox").hide();
			}
			else if(jQuery(this).hasClass("nav-tab-video")) {
				jQuery("#field-src").hide();
				jQuery("#field-activity_id").hide();
				jQuery("#field-xapi_content").hide();
				jQuery("#field-video").show();
				jQuery("#field-dropbox").hide();
			}
			else if(jQuery(this).hasClass("nav-tab-upload")) {
				jQuery("#field-src").hide();
				jQuery("#field-activity_id").show();
				jQuery("#field-xapi_content").show();
				jQuery("#field-video").hide();
				jQuery("#field-dropbox").hide();
			}
			else if(jQuery(this).hasClass("nav-tab-dropbox")) {
				jQuery("#field-src").hide();
				jQuery("#field-activity_id").show();
				jQuery("#field-xapi_content").hide();
				jQuery("#field-video").hide();
				jQuery("#field-dropbox").show();
			}
			return false;
		});

		if(jQuery("input#video").val().trim() != "")
			jQuery("a.nav-tab-video").click();
		else if(jQuery("input#src").val().trim() != "")
			jQuery("a.nav-tab-content-url").click();
		else
			jQuery("a.nav-tab-upload").click();

		jQuery("input#video").change(function() {
			jQuery("input#activity_id").val(jQuery("input#video").val());
		});

		jQuery("select#button_type").change(function() {
			if(jQuery(this).val() == "0")
			{
				jQuery("#field-text").show();
				jQuery("#field-link_button_image").hide();
			}
			else if(jQuery(this).val() == "1"){
				jQuery("#field-text").hide();
				jQuery("#field-link_button_image").show();
			}
		});		
		jQuery("select#button_type").change();
	}

	function grassblade_enable_button_selector() {
	  var _custom_media = true,
	      _orig_send_attachment = wp.media.editor.send.attachment;

	  jQuery('.gb_upload_button').click(function(e) {
	    var send_attachment_bkp = wp.media.editor.send.attachment;
	    var button = jQuery(this);
	    var id = button.attr('id');
	    _custom_media = true;
	    wp.media.editor.send.attachment = function(props, attachment){
	      if ( _custom_media ) {
	        jQuery("#"+id+"-url").val(attachment.url);
	        jQuery("#"+id+"-src").attr("src", attachment.url);
	      } else {
	        return _orig_send_attachment.apply( this, [props, attachment] );
	      };
	    }

	    wp.media.editor.open(button);
	    return false;
	  });

	  jQuery('.add_media').on('click', function(){
	    _custom_media = false;
	  });
	}
