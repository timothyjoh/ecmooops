
jQuery(function($){
	var accountSwitch = $('input#createaccount');
	if(accountSwitch.length){
		accountSwitch.attr('checked', true).parent().hide();
	}
  jQuery(".vimeo-inplace").on("click", function(e){
    e.preventDefault();
    var loc = jQuery(this).prop('href');
    jQuery(this).after('<div class="wpb_video_widget wpb_content_element vimeo-inplace-embed"><div class="wpb_wrapper"><div class="wpb_video_wrapper"><iframe src="' + loc + '?autoplay=1" width="640" height="480" frameborder="0" title="" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe></div></div></div>');
    jQuery(this).hide();
  });

  jQuery(".vimeo-inplace").on("mouseover", function(e){
    jQuery(this).find('img').prop('src', jQuery(this).find('img').data('hover'));
  });
  jQuery(".vimeo-inplace").on("mouseout", function(e){
    jQuery(this).find('img').prop('src', jQuery(this).find('img').data('src'));
  });

  jQuery(".vimeo-inplace img").prop('src', jQuery('.vimeo-inplace img').data('src'))

  jQuery(".wpProQuiz_button").on("click", function(e) {
    jQuery(".vimeo-inplace").show();
    jQuery(".vimeo-inplace-embed").empty();
  });
});
