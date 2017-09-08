jQuery(document).ready(function($) {
	var touch = Modernizr.touch;
	$('.cq-parallaxcontainer').each(function(index) {
		var _this = $(this);
		var _speed = $(this).data('speed') || 0.2;
		var _coverratio = $(this).data('coverratio') || 0.75;
		var _holderminheight = $(this).data('holderminheight') || 200;
		var _holdermaxheight = $(this).data('holdermaxheight') == "" ? null : parseInt($(this).data('holdermaxheight'));
		var _extraheight = $(this).data('extraheight') == "" ? 0 : parseInt($(this).data('extraheight'));
		// console.log('cover, min, max, extra', _coverratio, _holderminheight, _holdermaxheight, _extraheight);
		$(this).find('.cq-parallaximage').imageScroll({
		  container: _this,
		  speed: _speed,
		  coverRatio: _coverratio,
		  holderMinHeight: _holderminheight,
          holderMaxHeight: _holdermaxheight,
          extraHeight: _extraheight,
		  coverRatio: _coverratio,
		  holderClass: 'cq-parallaximgholder',
		  imageAttribute: (touch === true) ? 'image-mobile' : 'image',
		  touch: touch
		});

		// $(window).on('resize', function(event) {
		// 	_this.find('.cq-parallaximgholder').css({
		// 		'width': _this.width()
		// 	});
		// });
		// $(window).trigger('resize');
	});

	$('.cq-parallaximgholder').each(function(index) {
		$(this).on('click', function(event) {
			var _target = $(this).data('target');
			var _link = $(this).data('link');
			if(_link&&_link!=""){
				if(_target=="_blank"){
					window.open(_link);
				}else{
					window.location = _link;
				}
			}

		});
	});

});
