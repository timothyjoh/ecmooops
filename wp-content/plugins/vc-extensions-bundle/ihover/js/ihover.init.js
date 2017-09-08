jQuery(document).ready(function($) {
  $('.ihover-container').each(function() {
  	var _this = $(this);
  	var _effect = _this.data('effect');
  	var _shape = _this.data('shape');
    _this.css({
      'margin': _this.data('outeroffset'),
      'display': 'inline-block'
    });
  	$(this).find('li').each(function(index) {
  		$(this).css({
  			'width': _this.data('width'),
  			'height': _this.data('height'),
  			// 'padding': _this.data('padding'),
  			'margin': _this.data('margin')
  		});
  		if(_effect=="effect1"&&_shape=="square"){
	  		$(this).find('.ih-item').css({
	  			'width': _this.data('width'),
	  			'height': _this.data('height')
	  		});
  		}
  		$(this).find('.mask1, .mask2').css({
  			'position': 'absolute',
  			'width': _this.data('width'),
  			'height': _this.data('height')
  		});
  		$(this).find('.mask1').css({
			left: 'auto',
			right: 0,
			top: 0
  		});
  		$(this).find('.mask2').css({
		  	// '-webkit-transform': 'rotate(56.5deg) translateX( '+_this.data("width")*.5+'px)',
			// '-moz-transform': 'rotate(56.5deg) translateX( '+_this.data("width")*.5+'px)',
			// '-ms-transform': 'rotate(56.5deg) translateX( '+_this.data("width")*.5+'px)',
			// '-o-transform': 'rotate(56.5deg) translateX( '+_this.data("width")*.5+'px)',
			// 'transform': 'rotate(56.5deg) translateX( '+_this.data("width")*.5+'px)',
			top: 'auto',
			bottom: 0,
			left: 0
  		});

  		if(_this.data("shapge")=="square"&&_this.data("effect")=="effect4"){
  			$(this).find('.info').css({
	  			// 'width': _this.data('width')
  			});
  		}


  		$(this).find('img').css({
  			'width': _this.data('width'),
  			'height': _this.data('height')
  			// 'display': 'none'
  		});


  		$(this).find('.spinner').css({
  			'border-radius': '50%',
  			'width': _this.data('width')+10,
  			'height': _this.data('height')+10
  		});
  		$(this).find('.info').css({
  			'padding': _this.data('wholecaptionpadding')
  		});
  		$(this).find('h3').css({
  			'padding': _this.data('thumbtitlepadding')
  		});
  		$(this).find('p').css({
  			'padding': _this.data('thumbdescpadding')
  		});
		$(this).find('a.lightbox-link').boxer({
		    // minWidth: _minWidth,
		    fixed : true
		    // fixed: _container.data('fixed')=='on'?true:false
		});
		$(this).find('a.ihover-nothing').on('click', function(event) {
			event.preventDefault();
		});

  	});
  })
});
