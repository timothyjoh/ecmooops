<?php
if (!class_exists('VC_Extensions_VideoCover')) {
    class VC_Extensions_VideoCover{
        function VC_Extensions_VideoCover() {
          if(version_compare(WPB_VC_VERSION,  "4.4")>= 0){
            wpb_map(array(
            "name" => __("Video Cover", 'vc_videocover_cq'),
            "base" => "cq_vc_videocover",
            "class" => "wpb_cq_vc_extension_videocover",
            // "as_parent" => array('only' => 'cq_vc_videocover_item'),
            "icon" => "cq_allinone_videocover",
            "category" => __('Sike Extensions', 'js_composer'),
            // "content_element" => false,
            // "show_settings_on_create" => false,
            'description' => __('Lightbox video', 'js_composer'),
            "params" => array(
              array(
                "type" => "attach_image",
                "heading" => __("Image", "vc_videocover_cq"),
                "param_name" => "videoimage",
                "value" => "",
                "description" => __("Select image from media library.", "vc_videocover_cq")
              ),
              array(
                "type" => "dropdown",
                "holder" => "",
                "class" => "vc_videocover_cq",
                "heading" => __("Image shape", "vc_videocover_cq"),
                "param_name" => "imageshape",
                "value" => array("square", "rounded (small)" => "roundsmall", "rounded (large)" => "roundlarge", "ellipse (or circle with square image)" => "ellipse"),
                "std" => "no",
                "description" => __("", "vc_videocover_cq")
              ),
              array(
                "type" => "dropdown",
                "holder" => "",
                "class" => "vc_videocover_cq",
                "heading" => __("Resize the image?", "vc_videocover_cq"),
                "param_name" => "resizecoverimage",
                "value" => array("no", "yes (specify the image width below)"=>"yes"),
                "std" => "no",
                "description" => __("", "vc_videocover_cq")
              ),
              array(
                "type" => "textfield",
                "heading" => __("Resize image to this width", "vc_videocover_cq"),
                "param_name" => "coverimagewidth",
                "value" => "",
                "dependency" => Array('element' => "resizecoverimage", 'value' => array('yes')),
                "description" => __("Default we will use the original image, specify a width here. For example, 800 will resize the image to width 800.", "vc_videocover_cq")
              ),
              array(
                "type" => "textfield",
                "heading" => __("Tooltip for the image(optional)", "vc_videocover_cq"),
                "param_name" => "imagetooltip",
                "value" => "",
                "description" => __("", "vc_videocover_cq")
              ),
              array(
                "type" => "dropdown",
                "holder" => "",
                "class" => "vc_videocover_cq",
                "heading" => __("Overlay button with:", "vc_videocover_cq"),
                "param_name" => "overlaytype",
                "value" => array("Icon (select the icon below)" => "icon", "Text (customize the button text below)" => "text"),
                "std" => "no",
                "description" => __("", "vc_videocover_cq")
              ),
              array(
                'type' => 'dropdown',
                'heading' => __( 'Icon library', 'js_composer' ),
                'value' => array(
                  __( 'Font Awesome', 'js_composer' ) => 'fontawesome',
                  __( 'Open Iconic', 'js_composer' ) => 'openiconic',
                  __( 'Typicons', 'js_composer' ) => 'typicons',
                  __( 'Entypo', 'js_composer' ) => 'entypo',
                  __( 'Linecons', 'js_composer' ) => 'linecons',
                ),
                'admin_label' => true,
                'param_name' => 'covericon',
                'dependency' => array('element' => 'overlaytype', 'value' => 'icon',
                ),
                'description' => __( 'Select icon library.', 'js_composer' ),
              ),
              array(
                'type' => 'iconpicker',
                'heading' => __( 'Icon', 'js_composer' ),
                'param_name' => 'icon_fontawesome',
                'value' => 'fa fa-youtube-play', // default value to backend editor admin_label
                'settings' => array(
                  'emptyIcon' => false, // default true, display an "EMPTY" icon?
                  'iconsPerPage' => 4000, // default 100, how many icons per/page to display, we use (big number) to display all icons in single page
                ),
                'dependency' => array(
                  'element' => 'covericon',
                  'value' => 'fontawesome',
                ),
                'description' => __( 'Select icon from library.', 'js_composer' ),
              ),
              array(
                'type' => 'iconpicker',
                'heading' => __( 'Icon', 'js_composer' ),
                'param_name' => 'icon_openiconic',
                'value' => 'vc-oi vc-oi-dial', // default value to backend editor admin_label
                'settings' => array(
                  'emptyIcon' => false, // default true, display an "EMPTY" icon?
                  'type' => 'openiconic',
                  'iconsPerPage' => 4000, // default 100, how many icons per/page to display
                ),
                'dependency' => array(
                  'element' => 'covericon',
                  'value' => 'openiconic',
                ),
                'description' => __( 'Select icon from library.', 'js_composer' ),
              ),
              array(
                'type' => 'iconpicker',
                'heading' => __( 'Icon', 'js_composer' ),
                'param_name' => 'icon_typicons',
                'value' => 'typcn typcn-adjust-brightness', // default value to backend editor admin_label
                'settings' => array(
                  'emptyIcon' => false, // default true, display an "EMPTY" icon?
                  'type' => 'typicons',
                  'iconsPerPage' => 4000, // default 100, how many icons per/page to display
                ),
                'dependency' => array(
                  'element' => 'covericon',
                  'value' => 'typicons',
                ),
                'description' => __( 'Select icon from library.', 'js_composer' ),
              ),
              array(
                'type' => 'iconpicker',
                'heading' => __( 'Icon', 'js_composer' ),
                'param_name' => 'icon_entypo',
                'value' => 'entypo-icon entypo-icon-note', // default value to backend editor admin_label
                'settings' => array(
                  'emptyIcon' => false, // default true, display an "EMPTY" icon?
                  'type' => 'entypo',
                  'iconsPerPage' => 4000, // default 100, how many icons per/page to display
                ),
                'dependency' => array(
                  'element' => 'covericon',
                  'value' => 'entypo',
                ),
              ),
              array(
                'type' => 'iconpicker',
                'heading' => __( 'Icon', 'js_composer' ),
                'param_name' => 'icon_linecons',
                'value' => 'vc_li vc_li-heart', // default value to backend editor admin_label
                'settings' => array(
                  'emptyIcon' => false, // default true, display an "EMPTY" icon?
                  'type' => 'linecons',
                  'iconsPerPage' => 4000, // default 100, how many icons per/page to display
                ),
                'dependency' => array(
                  'element' => 'covericon',
                  'value' => 'linecons',
                ),
                'description' => __( 'Select icon from library.', 'js_composer' ),
              ),
              array(
                "type" => "textfield",
                "heading" => __("Button text", "vc_videocover_cq"),
                "param_name" => "buttonlabel",
                "value" => "PLAY",
                'dependency' => array('element' => 'overlaytype', 'value' => 'text', ),
                "description" => __("", "vc_videocover_cq")
              ),
              array(
                "type" => "textfield",
                "heading" => __("Size of the button text", "vc_videocover_cq"),
                "param_name" => "iconsize",
                "value" => "",
                "description" => __("The icon default is <strong>2em</strong>, the button text default is <strong>1em</strong>. Specify other value as you like here.", "vc_videocover_cq")
              ),
              array(
                "type" => "dropdown",
                "holder" => "",
                "class" => "vc_videocover_cq",
                "heading" => __("Icon (or text) background shape", "vc_videocover_cq"),
                "param_name" => "iconshape",
                "value" => array("circle", "rounded (small)" => "roundsmall", "rounded (large)" => "roundlarge", "square"),
                "std" => "no",
                "description" => __("", "vc_videocover_cq")
              ),
              array(
                "type" => "textfield",
                "heading" => __("Size of the button text background", "vc_videocover_cq"),
                "param_name" => "iconbgsize",
                "value" => "",
                "description" => __("The icon default is <strong>64</strong> (in pixel). Specify other value as you like here, like <strong>80</strong>.", "vc_videocover_cq")
              ),
              array(
                "type" => "colorpicker",
                "holder" => "div",
                "class" => "",
                "heading" => __("Icon (or text) color", 'vc_videocover_cq'),
                "param_name" => "iconcolor",
                "value" => '',
                "description" => __("Default is white.", 'vc_videocover_cq')
              ),
              array(
                "type" => "colorpicker",
                "holder" => "div",
                "class" => "",
                "heading" => __("Icon (or text) background color", 'vc_videocover_cq'),
                "param_name" => "iconbgcolor",
                "value" => '',
                "description" => __("Default is transparent black.", 'vc_videocover_cq')
              ),
              array(
                "type" => "dropdown",
                "holder" => "",
                "class" => "vc_videocover_cq",
                "heading" => __("Clicking the image, open as", "vc_videocover_cq"),
                "param_name" => "linktype",
                "value" => array("lightbox (video, Youtube or Vimeo)" => "video", /* , "lightbox (image)" => "image", */ "link"),
                "std" => "video",
                'group' => 'Link',
                "description" => __("", "vc_videocover_cq")
              ),
              array(
                'type' => 'vc_link',
                'heading' => __( 'URL (Optional link for the header)', 'vc_videocover_cq' ),
                'dependency' => array('element' => 'linktype', 'value' => 'link',
                ),
                'param_name' => 'normallink',
                'group' => 'Link',
                'description' => __( '', 'vc_videocover_cq' )
              ),
              array(
                "type" => "textfield",
                "heading" => __("Video link", "vc_videocover_cq"),
                "param_name" => "videolink",
                "value" => "",
                'dependency' => array('element' => 'linktype', 'value' => 'video', ),
                'group' => 'Link',
                "description" => __("Just copy and paste the page URL of the <strong>YouTube</strong> or <strong>Vimeo</strong> video, something like <strong>https://www.youtube.com/watch?v=pNSKQ9Qp36M</strong> or <strong>https://vimeo.com/127081676</strong>.", "vc_videocover_cq")
              ),
              array(
                "type" => "textfield",
                "heading" => __("Video width", "vc_videocover_cq"),
                "param_name" => "videowidth",
                "value" => "",
                'dependency' => array('element' => 'linktype', 'value' => 'video', ),
                'group' => 'Link',
                "description" => __("The width of lightbox video. Default is <strong>800</strong>. You can specify other value here.", "vc_videocover_cq")
              ),
              array(
                "type" => "textarea",
                "heading" => __("Optional caption under the video in the lightbox", "vc_videocover_cq"),
                "param_name" => "videocaption",
                "value" => "",
                'dependency' => array('element' => 'linktype', 'value' => 'video', ),
                'group' => 'Link',
                "description" => __("", "vc_videocover_cq")
              ),
              array(
                "type" => "textfield",
                "heading" => __("Display the lightbox in this gallery:", "vc_videocover_cq"),
                "param_name" => "gallery",
                "value" => "",
                'group' => 'Link',
                'dependency' => array('element' => 'linktype', 'value' => 'video', ),
                "description" => __("If you wish to open the video lightbox as a gallery, you can specify a unique gallery string for each one here. For example, <strong>video_gallery_1</strong>.", "vc_videocover_cq")
              ),
              array(
                "type" => "textfield",
                "heading" => __("Extra class name", "vc_videocover_cq"),
                "param_name" => "extraclass",
                "value" => "",
                "description" => __("If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", "vc_videocover_cq")
              )

           )
        ));

        }else{

          wpb_map(array(
            "name" => __("Video Cover", 'vc_videocover_cq'),
            "base" => "cq_vc_videocover",
            "class" => "wpb_cq_vc_extension_videocover",
            // "as_parent" => array('only' => 'cq_vc_videocover_item'),
            "icon" => "cq_allinone_videocover",
            "category" => __('Sike Extensions', 'js_composer'),
            // "content_element" => false,
            // "show_settings_on_create" => false,
            'description' => __('Lightbox video', 'js_composer'),
            "params" => array(
              array(
                "type" => "attach_image",
                "heading" => __("Image", "vc_videocover_cq"),
                "param_name" => "videoimage",
                "value" => "",
                "description" => __("Select image from media library.", "vc_videocover_cq")
              ),
              array(
                "type" => "dropdown",
                "holder" => "",
                "class" => "vc_videocover_cq",
                "heading" => __("Image shape", "vc_videocover_cq"),
                "param_name" => "imageshape",
                "value" => array("square", "rounded (small)" => "roundsmall", "rounded (large)" => "roundlarge", "ellipse (or circle with square image)" => "ellipse"),
                "std" => "no",
                "description" => __("", "vc_videocover_cq")
              ),
              array(
                "type" => "dropdown",
                "holder" => "",
                "class" => "vc_videocover_cq",
                "heading" => __("Resize the image?", "vc_videocover_cq"),
                "param_name" => "resizecoverimage",
                "value" => array("no", "yes (specify the image width below)"=>"yes"),
                "std" => "no",
                "description" => __("", "vc_videocover_cq")
              ),
              array(
                "type" => "textfield",
                "heading" => __("Resize image to this width", "vc_videocover_cq"),
                "param_name" => "coverimagewidth",
                "value" => "",
                "dependency" => Array('element' => "resizecoverimage", 'value' => array('yes')),
                "description" => __("Default we will use the original image, specify a width here. For example, 800 will resize the image to width 800.", "vc_videocover_cq")
              ),
              array(
                "type" => "textfield",
                "heading" => __("Tooltip for the image(optional)", "vc_videocover_cq"),
                "param_name" => "imagetooltip",
                "value" => "",
                "description" => __("", "vc_videocover_cq")
              ),
              array(
                "type" => "textfield",
                "heading" => __("Button text", "vc_videocover_cq"),
                "param_name" => "buttonlabel",
                "value" => "PLAY",
                'dependency' => array('element' => 'overlaytype', 'value' => 'text', ),
                "description" => __("", "vc_videocover_cq")
              ),
              array(
                "type" => "textfield",
                "heading" => __("Size of the button text", "vc_videocover_cq"),
                "param_name" => "iconsize",
                "value" => "",
                "description" => __("The icon default is <strong>2em</strong>, the button text default is <strong>1em</strong>. Specify other value as you like here.", "vc_videocover_cq")
              ),
              array(
                "type" => "dropdown",
                "holder" => "",
                "class" => "vc_videocover_cq",
                "heading" => __("Button background shape", "vc_videocover_cq"),
                "param_name" => "iconshape",
                "value" => array("circle", "rounded (small)" => "roundsmall", "rounded (large)" => "roundlarge", "square"),
                "std" => "no",
                "description" => __("", "vc_videocover_cq")
              ),
              array(
                "type" => "textfield",
                "heading" => __("Size of the button text background", "vc_videocover_cq"),
                "param_name" => "iconbgsize",
                "value" => "",
                "description" => __("The icon default is <strong>64</strong> (in pixel). Specify other value as you like here, like <strong>80</strong>.", "vc_videocover_cq")
              ),
              array(
                "type" => "colorpicker",
                "holder" => "div",
                "class" => "",
                "heading" => __("Button text color", 'vc_videocover_cq'),
                "param_name" => "iconcolor",
                "value" => '',
                "description" => __("Default is white.", 'vc_videocover_cq')
              ),
              array(
                "type" => "colorpicker",
                "holder" => "div",
                "class" => "",
                "heading" => __("Button text background color", 'vc_videocover_cq'),
                "param_name" => "iconbgcolor",
                "value" => '',
                "description" => __("Default is transparent black.", 'vc_videocover_cq')
              ),
              array(
                "type" => "dropdown",
                "holder" => "",
                "class" => "vc_videocover_cq",
                "heading" => __("Clicking the image, open as", "vc_videocover_cq"),
                "param_name" => "linktype",
                "value" => array("lightbox (video, Youtube or Vimeo)" => "video", /* , "lightbox (image)" => "image", */ "link"),
                "std" => "video",
                'group' => 'Link',
                "description" => __("", "vc_videocover_cq")
              ),
              array(
                'type' => 'vc_link',
                'heading' => __( 'URL (Optional link for the header)', 'vc_videocover_cq' ),
                'dependency' => array('element' => 'linktype', 'value' => 'link',
                ),
                'param_name' => 'normallink',
                'group' => 'Link',
                'description' => __( '', 'vc_videocover_cq' )
              ),
              array(
                "type" => "textfield",
                "heading" => __("Video link", "vc_videocover_cq"),
                "param_name" => "videolink",
                "value" => "",
                'dependency' => array('element' => 'linktype', 'value' => 'video', ),
                'group' => 'Link',
                "description" => __("Just copy and paste the page URL of the <strong>YouTube</strong> or <strong>Vimeo</strong> video, something like <strong>https://www.youtube.com/watch?v=pNSKQ9Qp36M</strong> or <strong>https://vimeo.com/127081676</strong>.", "vc_videocover_cq")
              ),
              array(
                "type" => "textfield",
                "heading" => __("Video width", "vc_videocover_cq"),
                "param_name" => "videowidth",
                "value" => "",
                'dependency' => array('element' => 'linktype', 'value' => 'video', ),
                'group' => 'Link',
                "description" => __("The width of lightbox video. Default is <strong>800</strong>. You can specify other value here.", "vc_videocover_cq")
              ),
              array(
                "type" => "textarea",
                "heading" => __("Optional caption under the video in the lightbox", "vc_videocover_cq"),
                "param_name" => "videocaption",
                "value" => "",
                'dependency' => array('element' => 'linktype', 'value' => 'video', ),
                'group' => 'Link',
                "description" => __("", "vc_videocover_cq")
              ),
              array(
                "type" => "textfield",
                "heading" => __("Display the lightbox in this gallery:", "vc_videocover_cq"),
                "param_name" => "gallery",
                "value" => "",
                'group' => 'Link',
                'dependency' => array('element' => 'linktype', 'value' => 'video', ),
                "description" => __("If you wish to open the video lightbox as a gallery, you can specify a unique gallery string for each one here. For example, <strong>video_gallery_1</strong>.", "vc_videocover_cq")
              ),
              array(
                "type" => "textfield",
                "heading" => __("Extra class name", "vc_videocover_cq"),
                "param_name" => "extraclass",
                "value" => "",
                "description" => __("If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", "vc_videocover_cq")
              )

           )
        ));



        }


        function cq_vc_videocover_func($atts, $content=null, $tag) {
          $covericon = $icon_fontawesome = $icon_openiconic = $icon_typicons = $icon_entypo = $icon_linecons = '';
          if(version_compare(WPB_VC_VERSION,  "4.6") >= 0){
              $atts = vc_map_get_attributes($tag,$atts);
              extract($atts);
          }else{
            extract(shortcode_atts(array(
              "icon_fontawesome" => '',
              "icon_openiconic" => '',
              "icon_typicons" => '',
              "icon_entypo" => '',
              "icon_linecons" => '',
              "videoimage" => '',
              "coverimagewidth" => '',
              "resizecoverimage" => 'no',
              "covericon" => '',
              "imageshape" => '',
              "iconshape" => '',
              "iconsize" => '',
              "iconbgsize" => '',
              "iconcolor" => '',
              "iconbgcolor" => '',
              "videolink" => '',
              "videocaption" => '',
              "overlaytype" => '',
              "buttonlabel" => '',
              "linktype" => '',
              "normallink" => '',
              "headerheight" => '',
              "gallery" => '',
              "videowidth" => '',
              "imagetooltip" => '',
              "extraclass" => ""
            ), $atts));
          }


          if(version_compare(WPB_VC_VERSION,  "4.4")>= 0){
            vc_icon_element_fonts_enqueue($covericon);
          }else{
            // wp_register_style( 'font-awesome', plugins_url('../faanimation/css/font-awesome.min.css', __FILE__) );
            // wp_enqueue_style( 'font-awesome' );
          }


          $content = wpb_js_remove_wpautop($content); // fix unclosed/unwanted paragraph tags in $content
          $output = '';

          $normallink = vc_build_link($normallink);


          wp_register_style('tooltipster', plugins_url('../appmockup/css/tooltipster.css', __FILE__));
          wp_enqueue_style('tooltipster');
          wp_register_style('formstone-lightbox', plugins_url('css/lightbox.css', __FILE__));
          wp_enqueue_style('formstone-lightbox');
          wp_register_script('tooltipster', plugins_url('../appmockup/js/jquery.tooltipster.min.js', __FILE__), array('jquery'));
          wp_enqueue_script('tooltipster');
          wp_register_script('formstone-lightbox', plugins_url('js/lightbox.js', __FILE__));
          wp_enqueue_script('formstone-lightbox');

          wp_register_style( 'vc-extensions-videocover-style', plugins_url('css/style.css', __FILE__) );
          wp_enqueue_style( 'vc-extensions-videocover-style' );
          wp_enqueue_script('vc-extensions-videocover-script');
          wp_register_script('vc-extensions-videocover-script', plugins_url('js/init.min.js', __FILE__), array("jquery", "tooltipster", "formstone-lightbox"));
          wp_enqueue_script('vc-extensions-videocover-script');


          $videoimage = wp_get_attachment_image_src($videoimage, 'full');
          $i = -1;
          $output = '';
          $link_str = '';
          if($linktype=="video"){
              $output .= '<a href="'.$videolink.'" class="cq-videocover-lightbox" data-lightbox-gallery="'.$gallery.'" data-videowidth="'.$videowidth.'" title="'.htmlspecialchars($videocaption).'">';
          }elseif ($linktype=="image") {
              // $output .= '<a href="http://wp.cq.com/wp-content/uploads/2014/04/4667092966_ef6b46bb27_b_d.jpg" class="cq-videocover-imglightbox" title="hello image" data-lightbox-gallery="video_gallery">';
          }else{
              $output .= '<a href="'.$normallink["url"].'" title="'.$normallink["title"].'" target="'.$normallink["target"].'">';
          }
          $output .= '<div class="cq-videocover '.$extraclass.'" data-iconsize="'.$iconsize.'" data-iconbgsize="'.$iconbgsize.'" data-iconcolor="'.$iconcolor.'" data-iconbgcolor="'.$iconbgcolor.'" data-tooltip="'.$imagetooltip.'">';
          if($videoimage[0]!="") {
              if($resizecoverimage=="yes"&&$coverimagewidth!=""){
                  $output .= '<img src="'.aq_resize($videoimage[0], $coverimagewidth, null, true, true, true).'" class="cq-videocover-img '.$imageshape.'"  />';
              }else{
                $output .= '<img src="'.$videoimage[0].'" class="cq-videocover-img '.$imageshape.'"  />';
              }
          }
          if(version_compare(WPB_VC_VERSION,  "4.4")>=0){
            if($overlaytype=="icon"){
                  if(version_compare(WPB_VC_VERSION,  "4.4")>=0&&isset(${'icon_' . $covericon})){
                    $output .= '<div class="cq-videocover-iconcontainer '.$iconshape.'">';
                    $output .= '<i class="cq-videocover-icon '.esc_attr(${'icon_' . $covericon}).'"></i>';
                    $output .= '</div>';
                  }
            }else{
                  if($buttonlabel!=""){
                    $output .= '<div class="cq-videocover-iconcontainer '.$iconshape.'">';
                    $output .= '<span class="cq-videocover-label">'.$buttonlabel.'</span>';
                    $output .= '</div>';
                  }
            }

          }else{
              if($buttonlabel!=""){
                $output .= '<div class="cq-videocover-iconcontainer '.$iconshape.'">';
                $output .= '<span class="cq-videocover-label">'.$buttonlabel.'</span>';
                $output .= '</div>';
              }

          }




          $output .= '</div>';
          $output .= '</a>';
          return $output;

        }

        add_shortcode('cq_vc_videocover', 'cq_vc_videocover_func');

      }
  }

}

?>
