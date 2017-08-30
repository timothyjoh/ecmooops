jQuery(document).ready(function($) {

  if (userName !== '') {
    userengage('event.Register', {
      'userName': '"' + userName + '"',
      'email': '"' + userEmail + '"'
    });
  }
  $('.add_to_cart_button').on('click', function() {

    $this = $(this);
    var productId = $(this).attr('data-product_id');
    var Data = 'ajax=1&product=' + productId + '';
    request = $.ajax({
      url: templateUrl,
      type: "get",
      data: Data,
      success: function(data, textStatus, jqXHR) {
        var object1 = jQuery.parseJSON(data);
        var object2 = {
          image_url: $this.parents('li').find('.product_thumbnail img').attr('src')
        };
        var obj = $.extend(object1, object2);
        userengage('event.AddCart', obj);
        return true;
      }
    });

  });

});
