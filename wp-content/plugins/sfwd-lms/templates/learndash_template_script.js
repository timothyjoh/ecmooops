if (typeof flip_expand_collapse == 'undefined') {
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

if (typeof flip_expand_all == 'undefined') {
	function flip_expand_all(what) {
	    jQuery( what + ' .list_arrow.flippable' ).removeClass( 'collapse' );
	    jQuery( what + ' .list_arrow.flippable' ).addClass( 'expand' );
	    jQuery( what + ' .flip' ).slideDown();
	    return false;
	}
}

if (typeof flip_collapse_all == 'undefined') {
	function flip_collapse_all(what) {
	    jQuery( what + ' .list_arrow.flippable' ).removeClass( 'expand' );
	    jQuery( what + ' .list_arrow.flippable' ).addClass( 'collapse' );
	    jQuery( what + ' .flip' ).slideUp();
	    return false;
	}
}

String.prototype.toHHMMSS = function() {
    sec_numb = parseInt( this, 10 );
    var hours = Math.floor( sec_numb / 3600 );
    var minutes = Math.floor( ( sec_numb - ( hours * 3600 ) ) / 60 );
    var seconds = sec_numb - ( hours * 3600 ) - ( minutes * 60 );
    if ( hours < 10 ) {
        hours = '0' + hours;
    }
    if ( minutes < 10 ) {
        minutes = '0' + minutes;
    }
    if ( seconds < 10 ) {
        seconds = '0' + seconds;
    }
    var time = hours + ':' + minutes + ':' + seconds;
    return time;
}

function learndash_timer() {
    document.getElementById( 'learndash_mark_complete_button' ).disabled = true;
    learndash_forced_lesson_time = learndash_forced_lesson_time - 1;
    document.getElementById( 'learndash_timer' ).innerHTML = learndash_forced_lesson_time.toString().toHHMMSS();
    if ( learndash_forced_lesson_time <= 0 ) {
        clearInterval( learndash_timer_var );
        document.getElementById( 'learndash_mark_complete_button' ).disabled = false;
        document.getElementById( 'learndash_timer' ).innerHTML = '';
    }
}

jQuery( function() {
    function force_max_12px_font_size() {
        var f1 = jQuery( '#course_navigation .learndash_navigation_lesson_topics_list a' ).css( 'font-size' );
        var f2 = jQuery( '#course_navigation .learndash_navigation_lesson_topics_list a span' ).css( 'font-size' );
        if ( f1 != undefined && f1.replace( 'px', '' ) > 12 || f2 != undefined && f2.replace( 'px', '' ) > 12 ) {
            jQuery( '#course_navigation .learndash_navigation_lesson_topics_list a, #course_navigation .learndash_navigation_lesson_topics_list a span' ).css( 'font-size', 12 );
        }
    }
    force_max_12px_font_size();
});


jQuery(document).ready(function(){
	if (typeof sfwd_data !== 'undefined') {
		if ( typeof sfwd_data.json !== 'undefined' ) {
			sfwd_data = sfwd_data.json.replace(/&quot;/g, '"');
			sfwd_data = jQuery.parseJSON( sfwd_data );
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
			url: sfwd_data.ajaxurl,
			dataType: "json",
			cache: false,
			data: post_data,
			error: function(jqXHR, textStatus, errorThrown ) {
			},
			success: function(reply_data) {

				if ( typeof reply_data.html !== 'undefined' ) {
					content.html(reply_data.html);
					jQuery('a.wpProQuiz_update', content).remove();
					jQuery('a#wpProQuiz_resetUserStatistic', content).remove();
					
					
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
