<?php
if (!class_exists( 'VC_Extensions_DepthModal' ) ) {

    class VC_Extensions_DepthModal {
        function VC_Extensions_DepthModal() {
            add_shortcode( 'cq_vc_modal', array( &$this, 'cq_vc_modal_func') );
            add_shortcode( 'cq_vc_notify', array( &$this, 'cq_vc_notify_func') );
            add_shortcode( 'cq_vc_gallery', array( &$this, 'cq_vc_gallery_func') );
            wpb_map( array(
              "name" => __("Depth Modal", 'vc_modal_cq'),
              "base" => "cq_vc_modal",
              "class" => "wpb_cq_vc_extension_depthmodal",
              "controls" => "full",
              "icon" => "cq_allinone_depthmodal",
              "category" => __('Sike Extensions', 'js_composer'),
              'description' => __( 'Popup modal', 'js_composer' ),
              // 'admin_enqueue_js' => array(plugins_url('vc_modal_cq_admin.js', __FILE__)),
              // 'admin_enqueue_css' => array(plugins_url('css/vc_extensions_cq_admin.css', __FILE__)),
              "params" => array(
                array(
                  "type" => "textarea_raw_html",
                  "holder" => "div",
                  "class" => "", // vc_modal_cq_textarea_raw
                  "heading" => __("Link label", 'vc_modal_cq'),
                  "param_name" => "buttontext",
                  "value" => __("SGVsbG8lMjBCdXR0b24=", 'vc_modal_cq'),
                  "description" => __("The link user click to open the modal, support HTML and other shortcode.", 'vc_modal_cq')
                ),
                array(
                  "type" => "textarea_html",
                  "holder" => "div",
                  "class" => "",
                  "heading" => __("Popup content", 'vc_modal_cq'),
                  "param_name" => "content",
                  "value" => __("<p>I am test text block. Click edit button to change this text.</p>", 'vc_modal_cq'),
                  "description" => __("", 'vc_modal_cq')
                ),
                array(
                  "type" => "colorpicker",
                  "holder" => "div",
                  "class" => "",
                  "heading" => __("Popup text color", 'vc_extend'),
                  "param_name" => "textcolor",
                  "value" => '#333',
                  "description" => __("", 'vc_extend')
                ),
                array(
                  "type" => "colorpicker",
                  "holder" => "div",
                  "class" => "",
                  "heading" => __("Popup background", 'vc_extend'),
                  "param_name" => "background",
                  "value" => '#fff',
                  "description" => __("", 'vc_extend')
                ),
                array(
                  "type" => "textfield",
                  "holder" => "",
                  "class" => "vc_modal_cq_tiny_text",
                  "heading" => __("Popup width", 'vc_modal_cq'),
                  "param_name" => "width",
                  "value" => __("640", 'vc_modal_cq'),
                  "description" => __("A fixed value like 640, or a (responsive) percent value like 60%.", 'vc_modal_cq')
                ),
                // array(
                //   "type" => "textfield",
                //   "holder" => "",
                //   "class" => "vc_modal_cq_tiny_text",
                //   "heading" => __("Popup padding", 'vc_modal_cq'),
                //   "param_name" => "padding",
                //   "value" => __("20px 20px 20px 20px", 'vc_modal_cq'),
                //   "description" => __("", 'vc_modal_cq')
                // ),
                array(
                  "type" => "textfield",
                  "holder" => "",
                  "class" => "vc_modal_cq_tiny_text",
                  "heading" => __("Popup margin top", 'vc_modal_cq'),
                  "param_name" => "margintop",
                  "value" => __("40", 'vc_modal_cq'),
                  "description" => __("", 'vc_modal_cq')
                ),
                array(
                "type" => "dropdown",
                "holder" => "",
                "class" => "vc_modal_cq",
                "heading" => __("Display the Popup in:", "vc_modal_cq"),
                "param_name" => "popupposition",
                "value" => array("fixed" => "fixed", "absolute (work better for long contnet)" => "absolute"),
                "description" => __("CSS position value for the Popup.", "vc_modal_cq")
              ),
                array(
                  "type" => "checkbox",
                  "holder" => "",
                  "class" => "vc_modal_cq",
                  "heading" => __("Do not hide the popup content when page is loaded", 'vc_modal_cq'),
                  "param_name" => "loadedvisible",
                  "value" => array(__("Yes, set the popup content visible by default", "vc_modal_cq") => 'on'),
                  "description" => __("Sometime you have to display the popup content when page is loaded, for example my hotspot plugin need it's container to be visible when loaded.", 'vc_modal_cq')
                )

              )
            ) );


            wpb_map( array(
              "name" => __("Scrolling Notification", 'vc_notify_cq'),
              "base" => "cq_vc_notify",
              "class" => "wpb_cq_vc_extension_scrollnotification",
              "controls" => "full",
              "icon" => "cq_allinone_scrollnotification",
              "category" => __('Sike Extensions', 'js_composer'),
              'description' => __( 'Popup notification', 'js_composer' ),
              'admin_enqueue_js' => array(plugins_url('js/vc_notify_cq_admin.js', __FILE__)),
              // 'admin_enqueue_css' => array(plugins_url('css/vc_notify_cq_admin.css', __FILE__)),
              "params" => array(
                array(
                  "type" => "textarea_html",
                  "holder" => "div",
                  "class" => "",
                  "heading" => __("Notification content", 'vc_notify_cq'),
                  "param_name" => "content",
                  "value" => __("<p>I am test text block. Click edit button to change this text.</p>", 'vc_notify_cq'),
                  "description" => __("", 'vc_notify_cq')
                ),
                array(
                  "type" => "textfield",
                  "holder" => "",
                  "class" => "vc_notify_cq",
                  "heading" => __("opacity", 'vc_notify_cq'),
                  "param_name" => "opacity",
                  "value" => __("0.8", 'vc_notify_cq'),
                  "description" => __("", 'vc_notify_cq')
                ),
                array(
                  "type" => "dropdown",
                  "holder" => "",
                  "class" => "vc_notify_cq",
                  "heading" => __("easein", "vc_notify_cq"),
                  "param_name" => "easein",
                  "value" => array(__("random", "vc_notify_cq") => 'random', __("fadeIn", "vc_notify_cq") => "fadeIn", __("wobble", "vc_notify_cq") => "wobble", __("tada", "vc_notify_cq") => "tada", __("shake", "vc_notify_cq") => "shake", __("swing", "vc_notify_cq") => "swing", __("pulse", "vc_notify_cq") => "pulse", __("fadeInLeft", "vc_notify_cq") => "fadeInLeft", __("fadeInRight", "vc_notify_cq") => "fadeInRight", __("fadeInUp", "vc_notify_cq") => "fadeInUp", __("fadeInDown", "vc_notify_cq") => "fadeInDown", __("fadeInLeftBig", "vc_notify_cq") => "fadeInLeftBig", __("fadeInRightBig", "vc_notify_cq") => "fadeInRightBig", __("fadeInUpBig", "vc_notify_cq") => "fadeInUpBig", __("fadeInDownBig", "vc_notify_cq") => "fadeInDownBig", __("bounceInLeft", "vc_notify_cq") => "bounceInLeft", __("bounceInRight", "vc_notify_cq") => "bounceInRight", __("bounce", "vc_notify_cq") => "bounce", __("bounceInUp", "vc_notify_cq") => "bounceInUp", __("bounceInDown", "vc_notify_cq") => "bounceInDown", __("rollIn", "vc_notify_cq") => "rollIn", __("rotateIn", "vc_notify_cq") => "rotateIn", __("rotateInDownLeft", "vc_notify_cq") => "rotateInDownLeft", __("rotateInDownRight", "vc_notify_cq") => "rotateInDownRight", __("rotateInUpLeft", "vc_notify_cq") => "rotateInUpLeft", __("rotateInUpRight", "vc_notify_cq") => "rotateInUpRight", __("flipInX", "vc_notify_cq") => "flipInX", __("flipInY", "vc_notify_cq") => "flipInY", __("lightSpeedIn", "vc_notify_cq") => "lightSpeedIn"),
                  "description" => __("Select easin in animation type. Note: Works only in modern browsers.", "vc_notify_cq")
                ),
                array(
                  "type" => "dropdown",
                  "holder" => "",
                  "class" => "vc_notify_cq",
                  "heading" => __("easeout", "vc_notify_cq"),
                  "param_name" => "easeout",
                  "value" => array(__("random", "vc_notify_cq") => 'random', __("fadeOut", "vc_notify_cq") => "fadeOut", __("fadeOutLeft", "vc_notify_cq") => "fadeOutLeft", __("fadeOutRight", "vc_notify_cq") => "fadeOutRight", __("fadeOutUp", "vc_notify_cq") => "fadeOutUp", __("fadeOutDown", "vc_notify_cq") => "fadeOutDown", __("fadeOutLeftBig", "vc_notify_cq") => "fadeOutLeftBig", __("fadeOutRightBig", "vc_notify_cq") => "fadeOutRightBig", __("fadeOutUpBig", "vc_notify_cq") => "fadeOutUpBig", __("fadeOutDownBig", "vc_notify_cq") => "fadeOutDownBig", __("bounceOutLeft", "vc_notify_cq") => "bounceOutLeft", __("bounceOutRight", "vc_notify_cq") => "bounceOutRight", __("bounceOutUp", "vc_notify_cq") => "bounceOutUp", __("bounceOutDown", "vc_notify_cq") => "bounceOutDown", __("rollOut", "vc_notify_cq") => "rollOut", __("rotateOut", "vc_notify_cq") => "rotateOut", __("rotateOutDownLeft", "vc_notify_cq") => "rotateOutDownLeft", __("rotateOutDownRight", "vc_notify_cq") => "rotateOutDownRight", __("rotateOutUpLeft", "vc_notify_cq") => "rotateOutUpLeft", __("rotateOutUpRight", "vc_notify_cq") => "rotateOutUpRight", __("flipOutX", "vc_notify_cq") => "flipOutX", __("flipOutY", "vc_notify_cq") => "flipOutY", __("lightSpeedOut", "vc_notify_cq") => "lightSpeedOut"),
                  "description" => __("Select easout in animation type. Note: Works only in modern browsers.", "vc_notify_cq")
                ),
                array(
                  "type" => "dropdown",
                  "holder" => "",
                  "class" => "vc_notify_cq",
                  "heading" => __("Display the notification", "vc_notify_cq"),
                  "param_name" => "displaywhen",
                  "value" => array( __("hidden by default, visible only when user scrolling", "vc_notify_cq") => "scrolling", __("always visible", "vc_notify_cq") => "loaded", __("visible by default, hidden when user scrolling", "vc_notify_cq") => "scrollhidden"),
                  "description" => __("Choose when to display the notification.", "vc_notify_cq")
                ),
                array(
                  "type" => "dropdown",
                  "holder" => "",
                  "class" => "vc_notify_cq",
                  "heading" => __("Put the close button on the", "vc_notify_cq"),
                  "param_name" => "closeposition",
                  "value" => array(__("left", "vc_notify_cq") => "left", __("right", "vc_notify_cq") => "right"),
                  "description" => __("", "vc_notify_cq")
                ),
                array(
                  "type" => "textfield",
                  "holder" => "",
                  "class" => "vc_notify_cq",
                  "heading" => __("width", 'vc_notify_cq'),
                  "param_name" => "width",
                  "value" => __("240", 'vc_notify_cq'),
                  "description" => __("A fixed value like 640, or a percent value like 60%, or leave it to be blank equal to auto.", 'vc_notify_cq')
                ),
                array(
                  "type" => "textfield",
                  "holder" => "",
                  "class" => "vc_notify_cq",
                  "heading" => __("height", 'vc_notify_cq'),
                  "param_name" => "height",
                  "value" => __("auto", 'vc_notify_cq'),
                  "description" => __("A fixed value like 640, or a percent value like 60%, or leave it to be blank equal to auto.", 'vc_notify_cq')
                ),
                array(
                  "type" => "colorpicker",
                  "holder" => "div",
                  "class" => "",
                  "heading" => __("Notification text color", 'vc_extend'),
                  "param_name" => "textcolor",
                  "value" => '#333',
                  "description" => __("", 'vc_extend')
                ),
                array(
                  "type" => "colorpicker",
                  "holder" => "div",
                  "class" => "",
                  "heading" => __("Notification background", 'vc_extend'),
                  "param_name" => "background",
                  "value" => '#fff',
                  "description" => __("", 'vc_extend')
                ),
                array(
                  "type" => "textfield",
                  "holder" => "",
                  "class" => "vc_notify_cq",
                  "heading" => __("top", 'vc_notify_cq'),
                  "param_name" => "top",
                  "value" => __("", 'vc_notify_cq'),
                  "description" => __("", 'vc_notify_cq')
                ),
                array(
                  "type" => "textfield",
                  "holder" => "",
                  "class" => "vc_notify_cq",
                  "heading" => __("right", 'vc_notify_cq'),
                  "param_name" => "right",
                  "value" => __("10", 'vc_notify_cq'),
                  "description" => __("", 'vc_notify_cq')
                ),
                array(
                  "type" => "textfield",
                  "holder" => "",
                  "class" => "vc_notify_cq",
                  "heading" => __("bottom", 'vc_notify_cq'),
                  "param_name" => "bottom",
                  "value" => __("10", 'vc_notify_cq'),
                  "description" => __("", 'vc_notify_cq')
                ),
                array(
                  "type" => "textfield",
                  "holder" => "",
                  "class" => "vc_notify_cq",
                  "heading" => __("left", 'vc_notify_cq'),
                  "param_name" => "left",
                  "value" => __("", 'vc_notify_cq'),
                  "description" => __("", 'vc_notify_cq')
                ),
                array(
                  "type" => "textfield",
                  "holder" => "",
                  "class" => "vc_notify_cq",
                  "heading" => __("Auto hide delay", 'vc_notify_cq'),
                  "param_name" => "autohidedelay",
                  "value" => __("", 'vc_notify_cq'),
                  "description" => __("For example, 5000 stand for 5 seconds, leave it to blank if you do not want it", 'vc_notify_cq')
                ),
                array(
                  "type" => "checkbox",
                  "holder" => "",
                  "class" => "vc_notify_cq",
                  "heading" => __("After close, store it in cookie", 'vc_notify_cq'),
                  "param_name" => "cookie",
                  "value" => array(__("yes", "vc_notify_cq") => 'on'),
                  "description" => __("", 'vc_notify_cq')
                ),
                // array(
                //   "type" => "checkbox",
                //   "holder" => "",
                //   "class" => "vc_notify_cq",
                //   "heading" => __("Display be default, hiden when user scrolling.", 'vc_notify_cq'),
                //   "param_name" => "displaybydefault",
                //   "value" => array(__("yes", "vc_notify_cq") => 'on'),
                //   "description" => __("", 'vc_notify_cq')
                // ),
                array(
                  "type" => "textfield",
                  "holder" => "",
                  "class" => "vc_notify_cq",
                  "heading" => __("After close, do not show the notification again for days", 'vc_notify_cq'),
                  "param_name" => "days",
                  "value" => __("10", 'vc_notify_cq'),
                  "description" => __("You have to enable the store in cookie", 'vc_notify_cq')
                )
                // array(
                //   "type" => "checkbox",
                //   "holder" => "",
                //   "class" => "vc_notify_cq",
                //   "heading" => __("display globally", 'vc_notify_cq'),
                //   "param_name" => "displayglobally",
                //   "value" => array(__("yes", "vc_notify_cq") => 'true'),
                //   "description" => __("Check this if you want to display a unique notification only whole site", 'vc_notify_cq')
                // )

              )
            ) );


            // gallery part
            wpb_map( array(
              "name" => __("Masonry Gallery", 'vc_gallery_cq'),
              "base" => "cq_vc_gallery",
              "class" => "wpb_cq_vc_extension_masonry",
              "controls" => "full",
              "icon" => "cq_allinone_masonry",
              "category" => __('Sike Extensions', 'js_composer'),
              'description' => __( 'Responsive grid gallery', 'js_composer' ),
              // 'admin_enqueue_js' => array(plugins_url('vc_modal_cq_admin.js', __FILE__)),
              // 'admin_enqueue_css' => array(plugins_url('css/vc_gallery_cq_admin.css', __FILE__)),
              "params" => array(
                array(
                  "type" => "attach_images",
                  "heading" => __("Images", "vc_gallery_cq"),
                  "param_name" => "images",
                  "value" => "",
                  "description" => __("Select images from media library.", "vc_gallery_cq")
                ),
                array(
                  "type" => "dropdown",
                  "holder" => "",
                  "class" => "vc_gallery_cq",
                  "heading" => __("On click", "vc_gallery_cq"),
                  "param_name" => "onclick",
                  "value" => array(__("open large image (lightbox)", "vc_gallery_cq") => "link_image", __("Do nothing", "vc_gallery_cq") => "link_no", __("Open custom link", "vc_gallery_cq") => "custom_link"),
                  "description" => __("Define action for onclick event if needed.", "vc_gallery_cq")
                ),
                array(
                  "type" => "exploded_textarea",
                  "heading" => __("Custom links", "vc_gallery_cq"),
                  "param_name" => "custom_links",
                  "description" => __('Enter links for each slide here. Divide links with linebreaks (Enter).', 'vc_gallery_cq'),
                  "dependency" => Array('element' => "onclick", 'value' => array('custom_link'))
                ),
                array(
                  "type" => "dropdown",
                  "heading" => __("Custom link target", "vc_gallery_cq"),
                  "param_name" => "custom_links_target",
                  "description" => __('Select where to open custom links.', 'vc_gallery_cq'),
                  "dependency" => Array('element' => "onclick", 'value' => array('custom_link')),
                  'value' => array(__("Same window", "vc_gallery_cq") => "_self", __("New window", "vc_gallery_cq") => "_blank")
                ),
                array(
                  "type" => "textfield",
                  "holder" => "",
                  "class" => "vc_gallery_cq",
                  "heading" => __("Thumbnail width", 'vc_gallery_cq'),
                  "param_name" => "itemwidth",
                  "value" => __("240", 'vc_gallery_cq'),
                  "description" => __("Width of each thumbnail in the masonry gallery.", 'vc_gallery_cq')
                ),
                array(
                  "type" => "textfield",
                  "holder" => "",
                  "class" => "vc_gallery_cq",
                  "heading" => __("Thumbnail padding", 'vc_gallery_cq'),
                  "param_name" => "offset",
                  "value" => __("4", 'vc_gallery_cq'),
                  "description" => __("Padding between each thumbnail.", 'vc_gallery_cq')
                ),
                array(
                  "type" => "textfield",
                  "holder" => "",
                  "class" => "vc_gallery_cq",
                  "heading" => __("Container offset", 'vc_gallery_cq'),
                  "param_name" => "outeroffset",
                  "value" => __("0", 'vc_gallery_cq'),
                  "description" => __("Offset of the whole gallery to it's container.", 'vc_gallery_cq')
                ),
                array(
                  "type" => "textfield",
                  "holder" => "",
                  "class" => "vc_gallery_cq",
                  "heading" => __("minWidth", 'vc_gallery_cq'),
                  "param_name" => "minwidth",
                  "value" => __("240", 'vc_gallery_cq'),
                  "description" => __("Minimal width of the lightbox image.", 'vc_gallery_cq')
                ),
                array(
                  "type" => "checkbox",
                  "holder" => "",
                  "class" => "vc_gallery_cq",
                  "heading" => __("Make the thumbnails retina?", 'vc_gallery_cq'),
                  "param_name" => "retina",
                  "value" => array(__("Yes", "vc_gallery_cq") => 'on'),
                  "description" => __("For example a 640x480 thumbnail will display as 320x240 in retina mode.", 'vc_gallery_cq')
                ),
                array(
                "type" => "checkbox",
                "holder" => "",
                "class" => "vc_gallery_cq",
                "heading" => __("Layout before all images are loaded?", 'vc_gallery_cq'),
                "param_name" => "imagesload",
                "value" => array(__("Yes", "vc_gallery_cq") => 'on'),
                "description" => __("Defalut the masonry layout is generated after images are all loaded, you can check this if your theme support instant layout.<br />Note: this will break the layout and make the images stacked in some theme, so check it carefully.", 'vc_gallery_cq')
              )



                /* ,
                array(
                  "type" => "textarea_html",
                  "holder" => "div",
                  "class" => "",
                  "heading" => __("popup content", 'vc_gallery_cq'),
                  "param_name" => "content",
                  "value" => __("<p>I am test text block. Click edit button to change this text.</p>", 'vc_gallery_cq'),
                  "description" => __("", 'vc_gallery_cq')
                )
                */
              )
            ));


        }

        function cq_vc_modal_func( $atts, $content=null, $tag) {
          if(version_compare(WPB_VC_VERSION,  "4.6") >= 0){
              $atts = vc_map_get_attributes($tag,$atts);
              extract($atts);
          }else{
            extract( shortcode_atts( array(
              'buttontext' => 'link label',
              'width' => '640',
              'textcolor' => '#333',
              'background' => '#fff',
              'margintop' => '40',
              'padding' => '0',
              'popupposition' => '',
              'loadedvisible' => 'off'
            ), $atts ) );
          }
          wp_register_style( 'vc_modal_cq_style', plugins_url('css/avgrund.css', __FILE__) );
          wp_enqueue_style( 'vc_modal_cq_style' );
          wp_enqueue_script( 'vc_modal_cq_js', plugins_url('js/jquery.avgrund.min.js', __FILE__), array('jquery') );

          $content = wpb_js_remove_wpautop($content); // fix unclosed/unwanted paragraph tags in $content
          return "<div class='avgrund-container' data-width='${width}' data-textcolor='${textcolor}' data-background='${background}' data-loadedvisible='${loadedvisible}' data-margintop='${margintop}' data-popupposition='${popupposition}'><div class='avgrund-popup'>
              <div class='avgrund-content'>
                {$content}
              </div>
              <a href='#' class='avgrund-close'><img width='24' height='24' src='".plugins_url('img/close.png', __FILE__)."' alt='close' /></a>
            </div><div class='avgrund-cover'></div><a href='#' class='avgrund-btn'>".do_shortcode(urldecode(base64_decode($buttontext)))."</a></div>";
        }

        function cq_vc_notify_func( $atts, $content=null, $tag) {
          if(version_compare(WPB_VC_VERSION,  "4.6") >= 0){
              $atts = vc_map_get_attributes($tag,$atts);
              extract($atts);
          }else{
            extract( shortcode_atts( array(
              'width' => '240',
              'height' => '140',
              'textcolor' => '#333',
              'background' => '#fff',
              'easein' => 'fadeInLeft',
              'easeout' => 'fadeOutRight',
              'cookie' => 'false',
              'autohidedelay' => '',
              'days' => '10',
              'top' => '',
              'right' => '10',
              'bottom' => '10',
              'left' => '',
              'opacity' => '0.8',
              'displaywhen' => 'scrolling',
              // 'displaybydefault' => '',
              'closeposition' => 'left'
              // 'displayglobally' => 'no'
            ), $atts ) );
          }

          wp_register_style( 'vc_notify_cq_style', plugins_url('css/jquery.scroll-notify.css', __FILE__) );
          wp_enqueue_style( 'vc_notify_cq_style' );
          wp_register_style( 'animate', plugins_url('css/animate.min.css', __FILE__) );
          wp_enqueue_style( 'animate' );
          wp_register_script('modernizr_css3', plugins_url('js/modernizr.custom.49511.js', __FILE__), array("jquery"));
          wp_enqueue_script('modernizr_css3');
          wp_enqueue_script('jquery-cookie', plugins_url('js/jquery.cookie.js', __FILE__), array('jquery'));
          wp_enqueue_script( 'vc_notify_cq_js', plugins_url('js/jquery.scroll-notify.min.js', __FILE__), array('jquery') );
          $content = wpb_js_remove_wpautop($content); // fix unclosed/unwanted paragraph tags in $content
          $output = '';
            if(is_single()||is_page()){
              if($displaywhen=="scrollhidden"){
                return "<div id='cq-scroll-notification' data-width='${width}' data-height='${height}' data-textcolor='${textcolor}' data-background='${background}' data-easein='${easein}' data-easeout='${easeout}' data-positiontop='${top}' data-positionright='${right}' data-positionbottom='${bottom}' data-positionleft='${left}' data-cookie='${cookie}' data-days='${days}' data-autohidedelay='${autohidedelay}' data-displaywhen='loaded' data-opacity='${opacity}' data-from='0' data-to='all' data-closebutton='true' data-displaybydefault='on' data-closeposition='${closeposition}' class='cq-scroll-notification'> {$content} </div>";
              }else{
                return "<div id='cq-scroll-notification' data-width='${width}' data-height='${height}' data-textcolor='${textcolor}' data-background='${background}' data-easein='${easein}' data-easeout='${easeout}' data-positiontop='${top}' data-positionright='${right}' data-positionbottom='${bottom}' data-positionleft='${left}' data-cookie='${cookie}' data-days='${days}' data-autohidedelay='${autohidedelay}' data-displaywhen='${displaywhen}' data-opacity='${opacity}' data-from='0' data-to='all' data-closebutton='true' data-closeposition='${closeposition}' class='cq-scroll-notification' style='display:none'> {$content} </div>";

              }
            }
        }


        // the gallery shortcode
        function cq_vc_gallery_func( $atts, $content=null, $tag) {
          global $post;
          if(version_compare(WPB_VC_VERSION,  "4.6") >= 0){
              $atts = vc_map_get_attributes($tag,$atts);
              extract($atts);
          }else{
            extract( shortcode_atts( array(
              'images' => '',
              'itemwidth' => '240',
              'minwidth' => '240',
              'offset' => '4',
              'onclick' => 'link_image',
              'custom_links' => '',
              'custom_links_target' => '',
              'outeroffset' => '0',
              'background' => '#fff',
              'retina' => 'off',
              'imagesload' => 'off',
              'margintop' => '40'
            ), $atts ) );
          }

          wp_enqueue_style('cq_pinterest_style', plugins_url('css/jquery.pinterest.css', __FILE__));
          wp_register_script('imagesload', plugins_url('js/imagesloaded.pkgd.min.js', __FILE__), array('jquery'));
          wp_enqueue_script('imagesload');
          wp_register_script('wookmark', plugins_url('js/jquery.wookmark.min.js', __FILE__), array('jquery', 'imagesload'));
          wp_enqueue_script('wookmark');

          if($onclick=='link_image'){
            wp_register_script('fs.boxer', plugins_url('js/jquery.fs.boxer.min.js', __FILE__), array('jquery'));
            wp_enqueue_script('fs.boxer');
            wp_register_style('fs.boxer', plugins_url('css/jquery.fs.boxer.css', __FILE__));
            wp_enqueue_style('fs.boxer');
          }else if($onclick=="custom_link"){
            $custom_links = explode( ',', $custom_links);
          }

          $imagesarr = explode(',', $images);
          $output = '';
          $output .= '<ul class="pinterest-container" data-onclick="'.$onclick.'" data-itemwidth="'.$itemwidth.'" data-minwidth="'.$minwidth.'" data-offset="'.$offset.'" data-outeroffset="'.$outeroffset.'" data-id="'.$post->ID.rand(0, 100).'" data-imagesload="'.$imagesload.'">';
          $i = -1;
          foreach ($imagesarr as $key => $value) {
              $i++;
              $output .= "<li style='list-style:none;display:none'>";
              if(wp_get_attachment_image_src(trim($value), 'full')){
                $return_img_arr = wp_get_attachment_image_src(trim($value), 'full');
                // $return_img_height = getimagesize(aq_resize($return_img_arr[0], $itemwidth));
                $return_img_height = aq_resize($return_img_arr[0], $itemwidth, null, null, false);
                if($onclick=='link_image'){
                  $output .= "<a href='".$return_img_arr[0]."' class='lightbox-link' rel='cq-pinterst-".$post->ID."'>";
                  $output .= "<img src='".aq_resize($return_img_arr[0], $retina=="on"?$itemwidth*2:$itemwidth)."' width='$itemwidth' height='".$return_img_height[1]."' />";
                  $output .= "</a>";
                }else if($onclick=='custom_link'){
                  if($i<count($custom_links)){
                    $output .= "<a href='".$custom_links[$i]."' target='".$custom_links_target."'>";
                    $output .= "<img src='".aq_resize($return_img_arr[0], $retina=="on"?$itemwidth*2:$itemwidth)."' width='$itemwidth' height='".$return_img_height[1]."' />";
                    $output .= "</a>";
                  }else{
                    $output .= "<img src='".aq_resize($return_img_arr[0], $retina=="on"?$itemwidth*2:$itemwidth)."' width='$itemwidth' height='".$return_img_height[1]."' />";
                  }
                }else{
                  $output .= "<img src='".aq_resize($return_img_arr[0], $retina=="on"?$itemwidth*2:$itemwidth)."' width='$itemwidth' height='".$return_img_height[1]."' />";
                }
              }
              $output .= "</li>";
          }
          $output .= '</ul>';

          return $output;

        }

    }

    // if(!function_exists('aq_resize')) require_once('aq_resizer.php');

    // if(class_exists('VC_Extensions_DepthModal'))$vc_extensions_depthmodal = new VC_Extensions_DepthModal();
}

?>
