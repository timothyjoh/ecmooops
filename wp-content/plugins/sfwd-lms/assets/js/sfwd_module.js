if ( typeof sfwd_data.json !== 'undefined' ) {
	sfwd_data = sfwd_data.json.replace(/&quot;/g, '"');
	sfwd_data = jQuery.parseJSON( sfwd_data );
}


function toggleVisibility(id) {
	var e = document.getElementById(id);
	if (e.style.display == 'block')
		e.style.display = 'none';
	else
		e.style.display = 'block';
}

function countChars(field,cntfield) {
	cntfield.value = field.value.length;
}

jQuery('.sfwd_datepicker').each(function () {
	jQuery('#' + jQuery(this).attr('id')).datepicker();
});

function sfwd_do_condshow_match( index, value ) {
	if ( typeof value !== 'undefined' ) {
		matches = true;
		jQuery.each(value, function(subopt, setting) {
			cur = jQuery('[name=' + subopt + ']');
			type = cur.attr('type');
			if ( type == "checkbox" || type == "radio" )
				cur = jQuery('input[name=' + subopt + ']:checked');
			cur = cur.val();
			if ( cur != setting ) {
				matches = false;
				return false;
			}
		});
		if ( matches ) {
			jQuery('#' + index ).show();
		} else {
			jQuery('#' + index ).hide();					
		}
		return matches;
	}
	return false;
}

function sfwd_add_condshow_handlers( index, value ) {
	if ( typeof value !== 'undefined' ) {
		jQuery.each(value, function(subopt, setting) {
			jQuery('[name=' + subopt + ']').change(function() {
				sfwd_do_condshow_match( index, value );
			});
		});
	}
}

function sfwd_do_condshow( condshow ) {
	if ( typeof sfwd_data.condshow !== 'undefined' ) {
		jQuery.each(sfwd_data.condshow, function(index, value) {
			sfwd_do_condshow_match( index, value );
			sfwd_add_condshow_handlers( index, value );
		});
	}
}

function sfwd_show_pointer( handle, value ) {
	if ( typeof( jQuery( value.pointer_target ).pointer) !== 'undefined' ) {
		jQuery(value.pointer_target).pointer({
					content    : value.pointer_text,
					close  : function() {
						jQuery.post( ajaxurl, {
							pointer: handle,
							action: 'dismiss-wp-pointer'
						});
					}
				}).pointer('open');
	}
}

jQuery(document).ready(function(){
	if (typeof sfwd_data !== 'undefined') {
		if ( typeof sfwd_data.condshow !== 'undefined' ) {
			sfwd_do_condshow( sfwd_data.condshow );
		}
	}

	jQuery('a.user_statistic').click(function(e) {
		e.preventDefault();
		
		var refId 				= 	jQuery(this).data('ref_id');
		var quizId 				= 	jQuery(this).data('quiz_id');
		var userId 				= 	jQuery(this).data('user_id');
		var statistic_nonce 	= 	jQuery(this).data('statistic_nonce');
		var post_data = {
			'action': 'wp_pro_quiz_admin_ajax',
			'func': 'statisticLoadUser',
			'data': {
				'quizId': quizId,
            	'userId': userId,
            	'refId': refId,
				'statistic_nonce': statistic_nonce,
            	'avg': 0
			}
		}
		
		jQuery('#wpProQuiz_user_overlay, #wpProQuiz_loadUserData').show();
		var content = jQuery('#wpProQuiz_user_content').hide();

		jQuery.ajax({
			type: "POST",
			url: ajaxurl,
			dataType: "json",
			cache: false,
			data: post_data,
			error: function(jqXHR, textStatus, errorThrown ) {
			},
			success: function(reply_data) {

				if ( typeof reply_data.html !== 'undefined' ) {
					content.html(reply_data.html);
					jQuery('#wpProQuiz_user_content').show();

					jQuery('#wpProQuiz_loadUserData').hide();
				
					content.find('.statistic_data').click(function() {
						jQuery(this).parents('tr').next().toggle('fast');
			
						return false;
					});
				}
			}
		});
				
		jQuery('#wpProQuiz_overlay_close').click(function() {
			jQuery('#wpProQuiz_user_overlay').hide();
		});
	});
	
});

jQuery(document).ready(function() {
	var image_field;
	jQuery('.sfwd_upload_image_button').click(function() {
		window.send_to_editor = newSendToEditor;
		image_field = jQuery(this).next();
		formfield = image_field.attr('name');
		tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
		return false;
	});

	storeSendToEditor 	= window.send_to_editor;
	newSendToEditor		= function(html) {
							imgurl = jQuery('img',html).attr('src');
							image_field.val(imgurl);
							tb_remove();
							window.send_to_editor = storeSendToEditor;
						};
});

// props to commentluv for this fix
// workaround for bug that causes radio inputs to lose settings when meta box is dragged.
// http://core.trac.wordpress.org/ticket/16972
jQuery(document).ready(function(){
    // listen for drag drop of metaboxes , bind mousedown to .hndle so it only fires when starting to drag
    jQuery('.hndle').mousedown(function(){                                                               
        // set live event listener for mouse up on the content .wrap and wait a tick to give the dragged div time to settle before firing the reclick function
        jQuery('.wrap').mouseup(function(){store_radio(); setTimeout('reclick_radio();',50);});
    })
});
/**
* stores object of all radio buttons that are checked for entire form
*/
if(typeof store_radio != 'function') {
	function store_radio(){
	    var radioshack = {};
	    jQuery('input[type="radio"]').each(function(){
	        if(jQuery(this).is(':checked')){
	            radioshack[jQuery(this).attr('name')] = jQuery(this).val();
	        }
	        jQuery(document).data('radioshack',radioshack);
	    });
	}
}
/**
* detect mouseup and restore all radio buttons that were checked
*/
if(typeof reclick_radio != 'function') {
	function reclick_radio(){
	    // get object of checked radio button names and values
	    var radios = jQuery(document).data('radioshack');
	    //step thru each object element and trigger a click on it's corresponding radio button
	    for(key in radios){
	        jQuery('input[name="'+key+'"]').filter('[value="'+radios[key]+'"]').trigger('click');
	    }            
	    // unbind the event listener on .wrap  (prevents clicks on inputs from triggering function)
	    jQuery('.wrap').unbind('mouseup');
	}
}

jQuery(document).ready(function() {
		if ( typeof sfwd_data.pointers !== 'undefined' ) {
			jQuery.each(sfwd_data.pointers, function(index, value) {
				if ( value !== 'undefined' && value.pointer_text != '' ) {
					sfwd_show_pointer( index, value );				
				}
			});
		}
	
        jQuery(".sfwd_tab:not(:first)").hide();
        jQuery(".sfwd_tab:first").show();
        jQuery(".sfwd_header_tabs a").click(function(){
                stringref = jQuery(this).attr("href").split('#')[1];
                jQuery('.sfwd_tab:not(#'+stringref+')').hide();
                jQuery('.sfwd_tab#' + stringref).show();
                jQuery('.sfwd_header_tab[href!=#'+stringref+']').removeClass('active');
                jQuery('.sfwd_header_tab#[href=#' + stringref+']').addClass('active');
                return false;
        });
        


	jQuery("body.post-type-sfwd-courses #categorydiv > h3 > span, body.post-type-sfwd-lessons #categorydiv > h3 > span, body.post-type-sfwd-topic #categorydiv > h3 > span, body.post-type-sfwd-courses #categorydiv > h3 > span").html(sfwd_data.learndash_categories_lang);

	if(jQuery(".sfwd-lessons_settings").length)
		learndash_lesson_edit_page_javascript();

	if(jQuery(".sfwd-courses_settings").length)
		learndash_course_edit_page_javascript();

	if(jQuery(".sfwd-topic_settings").length) {
		learndash_topic_edit_page_javascript();
	}
	if(jQuery(".sfwd-quiz_settings").length) {
		learndash_quiz_edit_page_javascript();
	}
});

function learndash_lesson_edit_page_javascript() {

	jQuery("[name='sfwd-lessons_lesson_assignment_points_enabled']").change(function(){
		checked = jQuery("[name=sfwd-lessons_lesson_assignment_points_enabled]:checked").length;
		if(checked) {
			jQuery("#sfwd-lessons_lesson_assignment_points_amount").show();
		}
		else {
			jQuery("#sfwd-lessons_lesson_assignment_points_amount").hide();
			
			// Clear out the Points amount value
			jQuery("[name='sfwd-lessons_lesson_assignment_points_amount']").val('0'); 
		}
	});
	if(jQuery("[name='sfwd-lessons_lesson_assignment_points_enabled']").length) {
		jQuery("[name='sfwd-lessons_lesson_assignment_points_enabled']").change();
	}

	jQuery("[name='sfwd-lessons_lesson_assignment_upload']").change(function(){
		checked = jQuery("[name=sfwd-lessons_lesson_assignment_upload]:checked").length;
		if(checked) {
			jQuery("#sfwd-lessons_auto_approve_assignment").show();
			jQuery("#sfwd-lessons_lesson_assignment_points_enabled").show();
			//jQuery("#sfwd-lessons_lesson_assignment_points_amount").show();
			jQuery("[name='sfwd-lessons_lesson_assignment_points_enabled']").change();
		}
		else {
			jQuery("#sfwd-lessons_auto_approve_assignment").hide();
			jQuery("#sfwd-lessons_lesson_assignment_points_enabled").hide();
			//jQuery("#sfwd-lessons_lesson_assignment_points_amount").hide();
			jQuery("[name='sfwd-lessons_lesson_assignment_points_enabled']").change();
		}
	});

	if(jQuery("[name='sfwd-lessons_lesson_assignment_upload']").length) {
		jQuery("[name='sfwd-lessons_lesson_assignment_upload']").change();
	}



    load_datepicker();	
}
function load_datepicker() {
	
	// Wait until the #ui-datepicker-div element is added to the DOM
	jQuery(document).on('DOMNodeInserted', function(e) {
		if (e.target.id == 'ui-datepicker-div') {
			jQuery('#ui-datepicker-div').addClass('learndash-datepicker');
		}
	});

	jQuery( "input[name='sfwd-lessons_visible_after_specific_date']" ).datepicker({
		changeMonth: true,
		changeYear: true,
        dateFormat : 'MM d, yy',
        onSelect: function(dateText, inst) {
			jQuery("input[name='sfwd-lessons_visible_after']").val('0');
			jQuery("input[name='sfwd-lessons_visible_after']").prop('disabled', true);
		}
	});       
    
    jQuery("input[name='sfwd-lessons_visible_after_specific_date']").blur(function() {
		var specific_data = jQuery("input[name='sfwd-lessons_visible_after_specific_date']").val();
		if( specific_data != '') {
			jQuery("input[name='sfwd-lessons_visible_after']").val('0');
			jQuery("input[name='sfwd-lessons_visible_after']").attr("disabled", "disabled");
		} else {
			jQuery("input[name='sfwd-lessons_visible_after']").removeAttr("disabled");
		}
    });
        
	jQuery("input[name='sfwd-lessons_visible_after']").click(function() {
		var specific_data = jQuery("input[name='sfwd-lessons_visible_after_specific_date']").val();
		if( specific_data != '') {
			jQuery("input[name='sfwd-lessons_visible_after']").val('0');
			jQuery("input[name='sfwd-lessons_visible_after']").attr("disabled", "disabled");
		} else {
			jQuery("input[name='sfwd-lessons_visible_after']").removeAttr("disabled");
		}
	});
}
function learndash_course_edit_page_javascript() {
	jQuery("select[name=sfwd-courses_course_price_type]").change(function(){
		var price_type = 	jQuery("select[name=sfwd-courses_course_price_type]").val();
		if(price_type == "open" || price_type == "free") {
			jQuery("input[name=sfwd-courses_course_price]").val('');
			jQuery("#sfwd-courses_course_price").hide();
		}
		else
			jQuery("#sfwd-courses_course_price").show();

		if(price_type == "closed") 
			jQuery("#sfwd-courses_custom_button_url").show();
		else
			jQuery("#sfwd-courses_custom_button_url").hide();
			
		if(price_type == "subscribe") {
			jQuery("#sfwd-courses_course_price_billing_cycle").show();
			/*jQuery("#sfwd-courses_course_no_of_cycles").show();
			jQuery("#sfwd-courses_course_remove_access_on_subscription_end").show();*/
		}
		else {
			jQuery("#sfwd-courses_course_price_billing_cycle").hide();
			/*jQuery("#sfwd-courses_course_no_of_cycles").hide();
			jQuery("#sfwd-courses_course_remove_access_on_subscription_end").hide(); */
		}
	});
	jQuery("select[name=sfwd-courses_course_price_type]").change();
	jQuery("input[name=sfwd-courses_expire_access]").change( function() {
		if(jQuery("input[name=sfwd-courses_expire_access]:checked").val() == undefined) {
			jQuery("#sfwd-courses_expire_access_days").hide();
			jQuery("#sfwd-courses_expire_access_delete_progress").hide();
		}
		else
		{
			jQuery("#sfwd-courses_expire_access_days").show();
			jQuery("#sfwd-courses_expire_access_delete_progress").show();	
		}
	} );
	jQuery("input[name=sfwd-courses_expire_access]").change();


}
function learndash_quiz_edit_page_javascript() {
		jQuery("select[name=sfwd-quiz_quiz_pro]").change(function() {
			var quiz_pro = jQuery("select[name=sfwd-quiz_quiz_pro]").val();
			if(window['sfwd-quiz_quiz_pro'] != quiz_pro)
			{
				var html = jQuery("#sfwd-quiz_quiz_pro_html").html();
				if(html.length > 10)
					window['sfwd-quiz_quiz_pro_html'] = html;
				
				jQuery("#sfwd-quiz_quiz_pro_html").hide();
				jQuery("input[name=disable_advance_quiz_save]").val(1);


			}
			else
			{
				jQuery("#sfwd-quiz_quiz_pro_html").show();		
				jQuery("input[name=disable_advance_quiz_save]").val(0);
								
			}
			if(quiz_pro > 0)
			jQuery("#advanced_quiz_preview").attr("href",sfwd_data.advanced_quiz_preview_link + quiz_pro); 
			else
			jQuery("#advanced_quiz_preview").attr("href","#"); 
			
			jQuery.fn.wpProQuiz_preview();
		});
		var quiz_pro = jQuery("select[name=sfwd-quiz_quiz_pro]").val();
		window['sfwd-quiz_quiz_pro'] = sfwd_data.quiz_pro;
		jQuery("form#post").append("<div id='disable_advance_quiz_save'><input type='hidden' name='disable_advance_quiz_save' value='0'/></div>");
		jQuery("select[name=sfwd-quiz_quiz_pro]").change();

		jQuery("select[name=sfwd-quiz_course]").change(function() {
				if(window['sfwd_quiz_lesson'] == undefined)
				window['sfwd_quiz_lesson'] = jQuery("select[name=sfwd-quiz_lesson]").val();
				
				jQuery("select[name=sfwd-quiz_lesson]").html('<option>' + sfwd_data.loading_lang + '</option>');

				var data = {
					'action': 'select_a_lesson_or_topic',
					'course_id': jQuery(this).val()
				};

				// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
				jQuery.post(ajaxurl, data, function(json) {
					window['response'] = json;
					html  = '<option value="0">'+ sfwd_data.select_a_lesson_or_topic_lang + '</option>';
					jQuery.each(json.opt, function(i, opt) {
						if(opt.key != '' && opt.key != '0')
						{ 
							selected = (opt.key == window['sfwd_quiz_lesson'])? 'selected=selected': '';
							html += "<option value='" + opt.key + "' "+ selected +">" + opt.value + "</option>";				
						}
					});
					jQuery("select[name=sfwd-quiz_lesson]").html(html);
					//jQuery("select[name=sfwd-topic_lesson]").val(window['sfwd_topic_lesson']);
				}, "json");
		});	
		jQuery("#postimagediv").addClass("hidden_by_sfwd_lms_sfwd_module.js");
		jQuery("#postimagediv").hide(); //Hide the Featured Image Metabox
}

// Handle checkbox combination logic for Lesson Topics
function learndash_topic_edit_page_javascript() {

	jQuery('[name="sfwd-topic_lesson_assignment_upload"]').change(function(){
		checked = jQuery("[name=sfwd-topic_lesson_assignment_upload]:checked").length;
		if(checked) {
			jQuery("#sfwd-topic_auto_approve_assignment").show();
			jQuery("#sfwd-topic_lesson_assignment_points_enabled").show();
		}
		else {
			jQuery("#sfwd-topic_auto_approve_assignment").hide();
			jQuery("#sfwd-topic_lesson_assignment_points_enabled").hide();

			jQuery("[name='sfwd-topic_lesson_assignment_points_enabled']").prop('checked', false); 
			jQuery("[name='sfwd-topic_lesson_assignment_points_enabled']").change();
		}
	});
	
	if(jQuery("[name='sfwd-topic_lesson_assignment_upload']").length) {
		jQuery("[name='sfwd-topic_lesson_assignment_upload']").change();
	}
	
	jQuery('[name="sfwd-topic_lesson_assignment_points_enabled"]').change(function(){
		checked = jQuery("[name=sfwd-topic_lesson_assignment_points_enabled]:checked").length;
		if(checked) {
			jQuery("#sfwd-topic_lesson_assignment_points_amount").show();
		} else {
			jQuery("#sfwd-topic_lesson_assignment_points_amount").hide();
			
			// Clear out the Points amount value
			jQuery("[name='sfwd-topic_lesson_assignment_points_amount']").val('0'); 
			
		}
	});
	
	if(jQuery("[name='sfwd-topic_lesson_assignment_points_enabled']").length) {
		jQuery("[name='sfwd-topic_lesson_assignment_points_enabled']").change();
	}
	


		jQuery("select[name=sfwd-topic_course]").change(function() {
				if(window['sfwd_topic_lesson'] == undefined)
				window['sfwd_topic_lesson'] = jQuery("select[name=sfwd-topic_lesson]").val();
				
				jQuery("select[name=sfwd-topic_lesson]").html('<option>' + sfwd_data.loading_lang + '</option>');

				var data = {
					'action': 'select_a_lesson',
					'course_id': jQuery(this).val()
				};

				// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
				jQuery.post(ajaxurl, data, function(json) {
					window['response'] = json;
					html  = '<option value="0">'+ sfwd_data.select_a_lesson_lang + '</option>';
					jQuery.each(json, function(key, value) {
						if(key != '' && key != '0')
						{
							selected = (key == window['sfwd_topic_lesson'])? 'selected=selected': '';
							html += "<option value='" + key + "' "+ selected +">" + value + "</option>";				
						}
					});
					jQuery("select[name=sfwd-topic_lesson]").html(html);
					//jQuery("select[name=sfwd-topic_lesson]").val(window['sfwd_topic_lesson']);
				}, "json");
		});	
}

// The following functions are also found in /templates/learndash_template_script.js but as that is a template and the admin 
// can choose to remove them I copied them into this JS file loaded for admin. I couldn't take the chance the admin 
// would create a version in the theme and remove the functions. 
if (typeof flip_expand_collapse === 'undefined') {
	function flip_expand_collapse(what, id) {
	    //console.log(id + ':' + document.getElementById( 'list_arrow.flippable-'+id).className);
	    if (jQuery( what + '-' + id + ' .list_arrow.flippable' ).hasClass( 'expand' ) ) {
	        jQuery( what + '-' + id + ' .list_arrow.flippable' ).removeClass( 'expand' );
	        jQuery( what + '-' + id + ' .list_arrow.flippable' ).addClass( 'collapse' );
	        jQuery( what + '-' + id + ' .flip' ).slideUp();
	    } else {
	        jQuery( what + '-' + id + ' .list_arrow.flippable' ).removeClass( 'collapse' );
	        jQuery( what + '-' + id + ' .list_arrow.flippable' ).addClass( 'expand' );
	        jQuery( what + '-' + id + ' .flip' ).slideDown();
	    }
	    return false;
	}
}

if (typeof flip_expand_all === 'undefined') {
	function flip_expand_all(what) {
	    jQuery( what + ' .list_arrow.flippable' ).removeClass( 'collapse' );
	    jQuery( what + ' .list_arrow.flippable' ).addClass( 'expand' );
	    jQuery( what + ' .flip' ).slideDown();
	    return false;
	}
}

if (typeof flip_collapse_all === 'undefined') {
	function flip_collapse_all(what) {
	    jQuery( what + ' .list_arrow.flippable' ).removeClass( 'expand' );
	    jQuery( what + ' .list_arrow.flippable' ).addClass( 'collapse' );
	    jQuery( what + ' .flip' ).slideUp();
	    return false;
	}
}


/* Setup logic for lazy loading data for <select> options */
jQuery(document).ready(function() {

	jQuery('select[learndash_lazy_load_data]').each(function() {		
		var load_el = this;
		
		var load_data = jQuery(load_el).attr('learndash_lazy_load_data');
		if ( ( typeof load_data !== 'undefined' ) && ( load_data != '' ) ) {
			load_data = JSON.parse(load_data);
			learndash_element_lazy_loader(load_el, load_data);
		}
	})
});

function learndash_element_lazy_loader(load_el, query_data) {

	var spinner_el = jQuery(load_el).siblings('span.learndash_lazy_loading');
	if (typeof spinner === 'undefined' ) {
		jQuery(spinner_el).show();
	}
	
	if (typeof query_data.query_vars.paged === 'undefined' ) {
		query_data.query_vars.paged = 0;
	}
	
	query_data.query_vars.paged = parseInt(query_data.query_vars.paged) + 1;
	
	var post_data = {
		'action': 'learndash_element_lazy_loader',
		'query_data': query_data
	};

	jQuery.ajax({
		type: "POST",
		url: ajaxurl,
		dataType: "json",
		cache: false,
		data: post_data,
		error: function(jqXHR, textStatus, errorThrown ) {
		},
		success: function(reply_data) {
			if (typeof spinner === 'undefined' ) {
				jQuery(spinner_el).hide();
			}
			
			if ( typeof reply_data !== 'undefined' ) {
				if ( typeof reply_data['html_options'] !== 'undefined' ) {
					if ( reply_data['html_options'] != '' ) {
						jQuery(load_el).append(reply_data['html_options']);

						if ( typeof reply_data['query_data'] !== 'undefined' ) {
							if ( reply_data['query_data'] != '' ) {
								learndash_element_lazy_loader(load_el, reply_data['query_data']);
							}
						}
					} else {
						jQuery(load_el).attr('data', JSON.stringify(reply_data));
					}
				}
			}
		}
	});
}

/*
if (jQuery('#course_progress_details input[type=checkbox]').length) {
	jQuery('#course_progress_details input[type=checkbox]').click(function() {
		var checkbox_input = jQuery(this);
		var checkbox_action = jQuery(this).is(':checked');
		
		console.log('checkbox_input[%o]', checkbox_input);
		
		var type = '';
		if (checkbox_input.hasClass('learndash-mark-course-complete'))
			type = 'course';
		else if (checkbox_input.hasClass('learndash-mark-lesson-complete'))
			type = 'lesson';
		else if (checkbox_input.hasClass('learndash-mark-topic-complete'))
			type = 'topic';
		//else if (checkbox_input.hasClass('learndash-mark-quiz-complete'))
		//	type = 'quiz';
		
		//if (type == 'quiz') return false;
		
		var checkbox_data = jQuery(checkbox_input).attr('data-name');
		if (( typeof checkbox_data !== 'undefined' ) && ( checkbox_data != '' ) ) {
			checkbox_data = JSON.parse(checkbox_data);
			//console.log('2: checkbox_data[%o]', checkbox_data);
			
			update_user_course_progess_input( type, checkbox_data, checkbox_action );
		}
	});
}
*/

function update_user_course_progess_input(type, user_course_data, action) {
	//console.log('type[%o] action[%o] user_course_data[%o]', type, action, user_course_data);
	
	if (( typeof user_course_data === 'undefined' ) || ( user_course_data == '' ) ) 
		return;
	
	// Must have User ID
	if (( typeof user_course_data['user_id'] !== 'undefined' ) && ( user_course_data['user_id'] != '' ) ) {
		var user_id = user_course_data['user_id'];
	} else {
		return;
	}

	// Must have Course ID
	if (( typeof user_course_data['course_id'] !== 'undefined' ) && ( user_course_data['course_id'] != '' ) ) {
		var course_id = user_course_data['course_id'];
	} else {
		return;
	}

	var user_progress = get_user_progress_data( user_id );
	if (user_progress === false) 
		return;


	if (type == 'quiz') {
		// Must have Quiz ID
		if (( typeof user_course_data['quiz_id'] !== 'undefined' ) && ( user_course_data['quiz_id'] != '' ) ) {
			var quiz_id = user_course_data['quiz_id'];
			//console.log('in quiz logic');

			if (action == true) {
				user_progress['quiz'][quiz_id] = 1;
			} else {
				//if ( typeof user_progress['quiz'][quiz_id] !== 'undefined' ) {
				//	delete user_progress['quiz'][quiz_id];
				//}
				user_progress['quiz'][quiz_id] = 0;
			}
		}
	} else {
	
		if ( typeof user_progress['course'][course_id] === 'undefined' ) {
			var course_data = get_course_data( course_id );
			//console.log('course_data[%o]', course_data);
			if (course_data === false) 
				return;
	
			user_progress['course'][course_id] = course_data;
		}
	
		//console.log('before: user_progress[%o]', user_progress);

		// Are we changing a Topic
		if (type == 'topic') {
			if (( typeof user_course_data['topic_id'] !== 'undefined' ) && ( user_course_data['topic_id'] != '' ) ) {
				var topic_id = user_course_data['topic_id'];
		
				// Must have Lesson ID
				if (( typeof user_course_data['lesson_id'] !== 'undefined' ) && ( user_course_data['lesson_id'] != '' ) ) {
					var lesson_id = user_course_data['lesson_id'];
				} else {
					return;
				}
		
				if (action == true) {
					if ( ( typeof user_progress['course'][course_id] === 'undefined' ) || ( user_progress['course'][course_id] == null ) )
						user_progress['course'][course_id] = {};

					if ( ( typeof user_progress['course'][course_id]['topics'] === 'undefined' ) || ( user_progress['course'][course_id]['topics'] == null ) )
						user_progress['course'][course_id]['topics'] = {};

					if ( ( typeof user_progress['course'][course_id]['topics'][lesson_id] === 'undefined' ) || ( user_progress['course'][course_id]['topics'][lesson_id] == null ) )
						user_progress['course'][course_id]['topics'][lesson_id] = {};

					//if ( typeof user_course_progress[course_id]['topics'][lesson_id][topic_id] === 'undefined' )
					//	user_course_progress[course_id]['topics'][lesson_id][topic_id] = {};
			
					user_progress['course'][course_id]['topics'][lesson_id][topic_id] = 1;
			
				} else {
			
					if ( typeof user_progress['course'][course_id]['topics'][lesson_id][topic_id] !== 'undefined' ) {
						delete user_progress['course'][course_id]['topics'][lesson_id][topic_id];
				
						// If we are left with an empty lesson item remove it also. 
						if (Object.keys(user_progress['course'][course_id]['topics'][lesson_id]).length === 0) {
							delete user_progress['course'][course_id]['topics'][lesson_id];
						}
					}
				} 
			}
		} else if (type == 'lesson') {
			// Else we changing a Lesson
			// Must have Lesson ID
			if (( typeof user_course_data['lesson_id'] !== 'undefined' ) && ( user_course_data['lesson_id'] != '' ) ) {
				var lesson_id = user_course_data['lesson_id'];
			} else {
				return;
			}
		
			if (action == true) {
				if (( typeof user_progress['course'][course_id] === 'undefined' ) || ( user_progress['course'][course_id] == null ))
					user_progress['course'][course_id] = {};

				if ( ( typeof user_progress['course'][course_id]['lessons'] === 'undefined' ) || ( user_progress['course'][course_id]['lessons'] == null ) )
					user_progress['course'][course_id]['lessons'] = {};

				//if ( typeof user_course_progress[course_id]['lessons'][lesson_id] === 'undefined' )
				//	user_course_progress[course_id]['lessons'][lesson_id] = {};

				user_progress['course'][course_id]['lessons'][lesson_id] = 1;
			
			} else {
				if ( typeof user_progress['course'][course_id]['lessons'][lesson_id] !== 'undefined' ) {
					delete user_progress['course'][course_id]['lessons'][lesson_id];
				}
			}
		}
	}
	//console.log('after: user_progress[%o]', user_progress);
	
	set_user_progress_data(user_id, user_progress);		
}

function get_course_data( course_id ) {
	var course_data = false;

	if ( jQuery('#learndash-mark-course-complete-'+course_id).length ) {
		var data = jQuery('#learndash-mark-course-complete-'+course_id).attr('data-name');
		if (( typeof data !== 'undefined' ) && ( data != '' ) ) {
			data = JSON.parse(data);

			if ( typeof data['course_data'] !== 'undefined' ) {
				course_data = data['course_data'];
			}
		}
	}
	
	return course_data;
}



function get_user_progress_data(user_id) {
	var user_progress = false;

	if ( jQuery('#user-progress-'+user_id).length ) {
		// Get the JSON data value from the input field
		user_progress = jQuery('#user-progress-'+user_id).val();
		if (( typeof user_progress !== 'undefined' ) && ( user_progress != '' ) ) {
			user_progress = JSON.parse(user_progress);
		}
	}
	return user_progress;
}

function set_user_progress_data(user_id, user_progress) {
	
	// Then save it back to the input value
	if ( jQuery('#user-progress-'+user_id).length ) {
		jQuery('#user-progress-'+user_id).val(JSON.stringify(user_progress));
	}
}


//var $_suppress_notification = false;
var $_click_type = false;

jQuery(document).ready(function() {

	if (jQuery('#course_progress_details a.learndash-profile-course-details-link').length) {
		jQuery('#course_progress_details a.learndash-profile-course-details-link').click(function() {
			var clicked_el = jQuery(this);
			var clicked_div = jQuery(clicked_el).next();
			jQuery('.widget_course_return', clicked_div).hide();
			if (jQuery(clicked_div).is(':visible')) {
				jQuery(clicked_div).slideUp('fast');
			} else {
				jQuery(clicked_div).slideDown('slow');
			}
			return false;
		});
	} 

	if (jQuery('#course_progress_details .learndash-profile-course-details-container input.learndash-mark-course-complete').length) {
		jQuery('#course_progress_details .learndash-profile-course-details-container input.learndash-mark-course-complete').click(function() {

			var course_checkbox = jQuery(this);
			var course_checked 	= course_checkbox.is(':checked');
				
			if ((course_checked == true) && ($_click_type == false)) {
				var course_title = course_checkbox.attr('data-title-checked');
				if (( typeof course_title !== 'undefined' ) && (course_title != '')) {
					if ( !confirm(course_title) ) {
						return false;
					}
				}
			} 

			if ($_click_type === false) {
				$_click_type = 'course';
			}

			// If the click type is Course meaning the course checkbox was clicked we 
			// trigger the signal down to the lesson checkboxes. 
			if ($_click_type == 'course') {
				var course_navigation_container = course_checkbox.siblings('.course_navigation');
				if (jQuery('input:checkbox', course_navigation_container).length) {
					jQuery('input:checkbox', course_navigation_container).each(function () {
						if ( ( jQuery(this).hasClass('learndash-mark-lesson-complete') ) || ( jQuery(this).hasClass('learndash-mark-course-quiz-complete') ) ) {
							jQuery(this).prop('checked', course_checked).triggerHandler('click');
						}
					});
				}
			}
		
			if ($_click_type == 'course') {
				$_click_type = false;
			}
		});
	}

	if (jQuery('#course_progress_details .learndash-profile-course-details-container input.learndash-mark-lesson-complete').length) {
		jQuery('#course_progress_details .learndash-profile-course-details-container input.learndash-mark-lesson-complete').click(function() {

			var lesson_checkbox = jQuery(this);
			var lesson_checked 	= lesson_checkbox.is(':checked');
			
			if ((lesson_checked == true) && ($_click_type == false)) {
				var lesson_title = lesson_checkbox.attr('data-title-checked');
				if (( typeof lesson_title !== 'undefined' ) && (lesson_title != '')) {
					if ( !confirm(lesson_title) ) {
						return false;
					}
				}
			} 
		
			var checkbox_data = lesson_checkbox.attr('data-name');
			if (( typeof checkbox_data !== 'undefined' ) && ( checkbox_data != '' ) ) {
				checkbox_data = JSON.parse(checkbox_data);
				update_user_course_progess_input( 'lesson', checkbox_data, lesson_checked );
			}

			if ($_click_type === false) {	
				$_click_type = 'lesson';
			}				

			if (($_click_type == 'lesson') || ($_click_type == 'topic') || ($_click_type == 'quiz')) {
				update_parents(lesson_checkbox);
			}				


			if (($_click_type == 'lesson') || ($_click_type == 'course')) {
				var lesson_id = lesson_checkbox.prop('id').replace('learndash-mark-lesson-complete-', '');
				if (( typeof lesson_id !== 'undefined' ) && ( lesson_id != '' ) ) {
					if (jQuery('input:checkbox', '#learndash_topic_dots-'+lesson_id).length) {
						jQuery('input:checkbox', '#learndash_topic_dots-'+lesson_id).each(function () {
							// We only worry about children topics and quizzes
							if ((jQuery(this).hasClass('learndash-mark-topic-complete')) || ( jQuery(this).hasClass('learndash-mark-lesson-quiz-complete') )) {
								jQuery(this).prop('checked', lesson_checked).triggerHandler('click');
							}
						});
					}
				}
			}
		
			if ($_click_type == 'lesson') {	
				$_click_type = false;
			}				
		});
	}

	if (jQuery('#course_progress_details .learndash-profile-course-details-container input.learndash-mark-topic-complete').length) {
		jQuery('#course_progress_details .learndash-profile-course-details-container input.learndash-mark-topic-complete').click(function() {

			var topic_checkbox 	= jQuery(this);
			var topic_checked 	= topic_checkbox.is(':checked');

			if ((topic_checked == true) && ($_click_type == false)) {
				var topic_title = topic_checkbox.attr('data-title-checked');
				if (( typeof topic_title !== 'undefined' ) && (topic_title != '')) {
					if ( !confirm(topic_title) ) {
						return false;
					}
				}
			} 

			if ($_click_type === false) {	
				$_click_type = 'topic';
			}				

			if (($_click_type == 'topic') || ($_click_type == 'quiz')) {
				update_parents(topic_checkbox);
			}				

			var checkbox_data = topic_checkbox.attr('data-name');
			if (( typeof checkbox_data !== 'undefined' ) && ( checkbox_data != '' ) ) {
				checkbox_data = JSON.parse(checkbox_data);
				update_user_course_progess_input( 'topic', checkbox_data, topic_checked );
			}

			if ($_click_type != 'quiz') {
			
				var topic_id = topic_checkbox.prop('id').replace('learndash-mark-topic-complete-', '');

				if (jQuery('input:checkbox', '#learndash-quiz-list-'+topic_id).length) {
					jQuery('input:checkbox', '#learndash-quiz-list-'+topic_id).each(function () {
						if ( jQuery(this).hasClass('learndash-mark-topic-quiz-complete') ) {
							jQuery(this).prop('checked', topic_checked).triggerHandler('click');
						}
					});
				}
			}
		
			if ($_click_type == 'topic') {	
				$_click_type = false;
			}				
		
		});
	}

	if (jQuery('#course_progress_details .learndash-profile-course-details-container input.learndash-mark-quiz-complete').length) {
		jQuery('#course_progress_details .learndash-profile-course-details-container input.learndash-mark-quiz-complete').click(function() {

			var quiz_checkbox 	= jQuery(this);
			var quiz_checked 	= jQuery(this).is(':checked');

			if ($_click_type === false) {	
				$_click_type = 'quiz';
			}				

			if ( $_click_type == 'quiz' ) {
				update_parents(quiz_checkbox);
			}

			var checkbox_data = jQuery(this).attr('data-name');
			if (( typeof checkbox_data !== 'undefined' ) && ( checkbox_data != '' ) ) {
				checkbox_data = JSON.parse(checkbox_data);
				update_user_course_progess_input( 'quiz', checkbox_data, quiz_checked );
			}

			if ($_click_type == 'quiz') {
				$_click_type = false;
			}				
		
		});
	}

	// This function is used to mark the parent checkbox complete if all the children are complete.
	function update_parents(checkbox) {

		if ( (( $_click_type == 'quiz' ) || ( $_click_type == 'topic' )) && (( checkbox.hasClass('learndash-mark-topic-complete') )	|| ( checkbox.hasClass('learndash-mark-lesson-quiz-complete') )) ) {
			var topic_list = checkbox.parents('.learndash_topic_widget_list');
			var lesson_id = jQuery(topic_list).prop('id').replace('learndash_topic_dots-', '');
			if (( typeof lesson_id !== 'undefined' ) && ( lesson_id != '' ) ) {

				if (jQuery('#learndash-mark-lesson-complete-'+lesson_id).length) {
					var checkboxes_total 	= jQuery('input.learndash-mark-topic-complete:checkbox', topic_list).length + jQuery('input.learndash-mark-lesson-quiz-complete:checkbox', topic_list).length;
					var checkboxes_checked 	= jQuery('input.learndash-mark-topic-complete:checkbox:checked', topic_list).length + jQuery('input.learndash-mark-lesson-quiz-complete:checkbox:checked', topic_list).length;
	
					var lesson_checked = false;
					if (parseInt(checkboxes_total) == parseInt(checkboxes_checked)) {
						// Set parent Lesson checkbox to checked
						lesson_checked = true;
					} 
					jQuery('#learndash-mark-lesson-complete-'+lesson_id).prop('checked', lesson_checked).triggerHandler('click');
				}
			}
		} else if ( ( $_click_type == 'quiz' ) && ( checkbox.hasClass('learndash-mark-topic-quiz-complete') ) ) {
			var quiz_list = checkbox.parents('.learndash-quiz-list');
			var topic_id = jQuery(quiz_list).prop('id').replace('learndash-quiz-list-', '');
			if (( typeof topic_id !== 'undefined' ) && ( topic_id != '' ) ) {

				if (jQuery('#learndash-mark-topic-complete-'+topic_id).length) {
					var checkboxes_total 	= jQuery('input.learndash-mark-topic-quiz-complete:checkbox', quiz_list).length;
					var checkboxes_checked 	= jQuery('input.learndash-mark-topic-quiz-complete:checkbox:checked', quiz_list).length;

					var topic_checked = false;
					if (parseInt(checkboxes_total) == parseInt(checkboxes_checked)) {
						// Set parent Lesson checkbox to checked
						topic_checked = true;
					} 
					jQuery('#learndash-mark-topic-complete-'+topic_id).prop('checked', topic_checked).triggerHandler('click');
				}
			}
		} else if (( checkbox.hasClass('learndash-mark-lesson-complete') ) || ( checkbox.hasClass('learndash-mark-course-quiz-complete') )) {
			var lesson_list = checkbox.parents('.course_navigation');
			var course_id = jQuery(lesson_list).prop('id').replace('course_navigation-', '');
			if (( typeof course_id !== 'undefined' ) && ( course_id != '' ) ) {
				if (jQuery('#learndash-mark-course-complete-'+course_id).length) {
					var checkboxes_total 	= jQuery('input.learndash-mark-lesson-complete:checkbox', lesson_list).length + jQuery('input.learndash-mark-course-quiz-complete:checkbox', lesson_list).length;
					var checkboxes_checked 	= jQuery('input.learndash-mark-lesson-complete:checkbox:checked', lesson_list).length + jQuery('input.learndash-mark-course-quiz-complete:checkbox:checked', lesson_list).length;

					var course_checked = false;
					if (parseInt(checkboxes_total) == parseInt(checkboxes_checked)) {
						// Set parent Lesson checkbox to checked
						course_checked = true;
					} 
					jQuery('#learndash-mark-course-complete-'+course_id).prop('checked', course_checked).triggerHandler('click');
				}
			}
		}
	}
});

jQuery(document).ready(function() {
	if ( jQuery('form#posts-filter button.assignment_approve_single').length ) {
		jQuery('form#posts-filter button.assignment_approve_single').click(function(e) {
			e.preventDefault();
			var assignment_id = jQuery(this).attr('id').replace('assignment_approve_', '');
			if ( ( typeof assignment_id !== 'undefined' ) && ( assignment_id != '' ) ) {
				if (jQuery('form#posts-filter input#cb-select-'+assignment_id).length) {
					jQuery('form#posts-filter input#cb-select-'+assignment_id).prop('checked', true);
				}
				if (jQuery('form#posts-filter select#bulk-action-selector-top').length) {
					jQuery('form#posts-filter select#bulk-action-selector-top').val('approve_assignment');
				}
				jQuery('form#posts-filter input#doaction').trigger('click');
			}
		});		
	}
	
	if ( jQuery('form#posts-filter button.essay_approve_single').length ) {
		console.log('found essay buttons');
		jQuery('form#posts-filter button.essay_approve_single').click(function(e) {
			e.preventDefault();
			var essay_id = jQuery(this).attr('id').replace('essay_approve_', '');
			console.log('essay_id[%o]', essay_id);
			
			if ( ( typeof essay_id !== 'undefined' ) && ( essay_id != '' ) ) {
				if (jQuery('form#posts-filter input#cb-select-'+essay_id).length) {
					jQuery('form#posts-filter input#cb-select-'+essay_id).prop('checked', true);
				}
				if (jQuery('form#posts-filter select#bulk-action-selector-top').length) {
					jQuery('form#posts-filter select#bulk-action-selector-top').val('approve_essay');
				}
				jQuery('form#posts-filter input#doaction').trigger('click');
			}
		});		
	}
});
	
	
jQuery(document).ready(function(){
	jQuery('body.admin_page_group_admin_page table.groups a.learndash-data-group-reports-button').click(function(e) {

		e.preventDefault();

		var data_template 	= jQuery(e.target).attr('data-template');
		var data_slug 		= jQuery(e.target).attr('data-slug');
		var data_nonce 		= jQuery(e.target).attr('data-nonce');
		var data_group_id 	= jQuery(e.target).attr('data-group-id');
		var updateElement 	= jQuery('span.status', e.target);

		// disable all other buttons
		jQuery('body.admin_page_group_admin_page table.groups a.learndash-data-group-reports-button').prop('disabled', true);
		
		var post_data = {
			'action': 'learndash_data_group_reports',
			'data': {
				'init': 1,
				'nonce': data_nonce,
				'slug': data_slug,
				'group_id': data_group_id,
			}
		}
		learndash_data_group_reports_do_ajax( post_data, updateElement );
	});
});


function learndash_data_group_reports_do_ajax( post_data, updateElement ) {
	if ( ( typeof post_data === 'undefined' ) || ( post_data == '' ) ) {
		active_post_data = {};
		return false;
	}
	
	jQuery.ajax({
		type: "POST",
		url: ajaxurl,
		dataType: "json",
		cache: false,
		data: post_data,
		error: function(jqXHR, textStatus, errorThrown ) {
		},
		success: function(reply_data) {
			if ( typeof reply_data !== 'undefined' ) {

				if ( typeof reply_data['data'] !== 'undefined' ) {

					var total_count = 0;
					if ( typeof reply_data['data']['total_count'] !== 'undefined' )
						total_count = parseInt(reply_data['data']['total_count']);
					
					var result_count = 0;
					if ( typeof reply_data['data']['result_count'] !== 'undefined' ) 
						result_count = parseInt(reply_data['data']['result_count']);
					
					if ( result_count < total_count ) {
						
						// Update the progress meter
						if ( typeof updateElement !== 'undefined' ) {
							if (jQuery(updateElement).length) {
				
								if ( typeof reply_data['data']['progress_percent'] !== 'undefined' ) {
									var progress_percent = parseInt(reply_data['data']['progress_percent']);
									jQuery(updateElement).html(' '+progress_percent+'%');
								}
							}
						}
						
						post_data['data'] = reply_data['data'];
						learndash_data_group_reports_do_ajax( post_data, updateElement );
					} else {
						// Re-enable the buttons
						jQuery('body.admin_page_group_admin_page table.groups a.learndash-data-group-reports-button').prop('disabled', false);

						// Clear our update element
						jQuery(updateElement).html('');

						if (( typeof reply_data['data']['report_download_link'] !== 'undefined' ) && ( reply_data['data']['report_download_link'] != '' )) {
							window.location.href = reply_data['data']['report_download_link'];
						}
					}
				}
			}
		}
	});
}
