<?php
if (!class_exists('VC_Extensions_BeforeAfter')) {
    class VC_Extensions_BeforeAfter{
        function VC_Extensions_BeforeAfter() {
          if(version_compare(WPB_VC_VERSION,  "4.4")>= 0){
            wpb_map(array(
            "name" => __("Before & After", 'vc_beforeafter_cq'),
            "base" => "cq_vc_beforeafter",
            "class" => "wpb_cq_vc_extension_beforeafter",
            // "as_parent" => array('only' => 'cq_vc_beforeafter_item'),
            "icon" => "cq_allinone_beforeafter",
            "category" => __('Sike Extensions', 'js_composer'),
            // "content_element" => false,
            // "show_settings_on_create" => false,
            'description' => __('Image comparison slider', 'js_composer'),
            "params" => array(
              array(
                "type" => "attach_image",
                "heading" => __("Before image", "vc_beforeafter_cq"),
                "param_name" => "beforeimage",
                "value" => "",
                "description" => __("Select image from media library.", "vc_beforeafter_cq")
              ),
              array(
                "type" => "attach_image",
                "heading" => __("After image", "vc_beforeafter_cq"),
                "param_name" => "afterimage",
                "value" => "",
                "description" => __("Select image from media library.", "vc_beforeafter_cq")
              ),
              array(
                "type" => "dropdown",
                "holder" => "",
                "class" => "vc_beforeafter_cq",
                "heading" => __("Auto slide the handle", "vc_beforeafter_cq"),
                "param_name" => "autoslide",
                'value' => array(1, 2, 3, 4, 5, 6, 8, 10, __( 'Disable', 'vc_beforeafter_cq' ) => 0 ),
                'std' => 0,
                "description" => __("Auto slide the handle in each X seconds.", "vc_beforeafter_cq")
              ),
              array(
                "type" => "dropdown",
                "holder" => "",
                "class" => "vc_beforeafter_cq",
                "heading" => __("Handle color style:", "vc_beforeafter_cq"),
                "param_name" => "handlestyle",
                "value" => array("Orange" => "", "Grape Fruit" => "grapefruit", "Grass" => "grass", "Aqua" => "aqua", "Lavender" => "lavender", "Pink Rose" => "pinkrose", "Light Gray" => "lightgray", "Dark Gray" => "darkgray"),
                'std' => 'lightgray',
                "group" => "Handle",
                "description" => __("", "vc_beforeafter_cq")
              ),
              array(
                'type' => 'dropdown',
                'heading' => __( 'Select icon for the handle, Icon library:', 'js_composer' ),
                'value' => array(
                  __( 'Font Awesome', 'js_composer' ) => 'fontawesome',
                  __( 'Open Iconic', 'js_composer' ) => 'openiconic',
                  __( 'Typicons', 'js_composer' ) => 'typicons',
                  __( 'Entypo', 'js_composer' ) => 'entypo',
                  __( 'Linecons', 'js_composer' ) => 'linecons',
                ),
                'admin_label' => true,
                'param_name' => 'handleicon',
                "group" => "Handle",
                'description' => __( 'Select icon library.', 'js_composer' ),
              ),
              array(
                'type' => 'iconpicker',
                'heading' => __( 'Icon', 'js_composer' ),
                'param_name' => 'icon_fontawesome',
                'value' => 'fa fa-arrows-h', // default value to backend editor admin_label
                'settings' => array(
                  'emptyIcon' => false, // default true, display an "EMPTY" icon?
                  'iconsPerPage' => 4000, // default 100, how many icons per/page to display, we use (big number) to display all icons in single page
                ),
                'dependency' => array(
                  'element' => 'handleicon',
                  'value' => 'fontawesome',
                ),
                "group" => "Handle",
                'description' => __( 'Select icon from library.', 'js_composer' ),
              ),
              array(
                'type' => 'iconpicker',
                'heading' => __( 'Icon', 'js_composer' ),
                'param_name' => 'icon_openiconic',
                'value' => 'vc-oi vc-oi-dial', // default value to backend editor admin_label
                'settings' => array(
                  'emptyIcon' => true, // default true, display an "EMPTY" icon?
                  'type' => 'openiconic',
                  'iconsPerPage' => 4000, // default 100, how many icons per/page to display
                ),
                'dependency' => array(
                  'element' => 'handleicon',
                  'value' => 'openiconic',
                ),
                "group" => "Handle",
                'description' => __( 'Select icon from library.', 'js_composer' ),
              ),
              array(
                'type' => 'iconpicker',
                'heading' => __( 'Icon', 'js_composer' ),
                'param_name' => 'icon_typicons',
                'value' => 'typcn typcn-adjust-brightness', // default value to backend editor admin_label
                'settings' => array(
                  'emptyIcon' => true, // default true, display an "EMPTY" icon?
                  'type' => 'typicons',
                  'iconsPerPage' => 4000, // default 100, how many icons per/page to display
                ),
                'dependency' => array(
                  'element' => 'handleicon',
                  'value' => 'typicons',
                ),
                "group" => "Handle",
                'description' => __( 'Select icon from library.', 'js_composer' ),
              ),
              array(
                'type' => 'iconpicker',
                'heading' => __( 'Icon', 'js_composer' ),
                'param_name' => 'icon_entypo',
                'value' => 'entypo-icon entypo-icon-note', // default value to backend editor admin_label
                'settings' => array(
                  'emptyIcon' => true, // default true, display an "EMPTY" icon?
                  'type' => 'entypo',
                  'iconsPerPage' => 4000, // default 100, how many icons per/page to display
                ),
                "group" => "Handle",
                'dependency' => array(
                  'element' => 'handleicon',
                  'value' => 'entypo',
                ),
              ),
              array(
                'type' => 'iconpicker',
                'heading' => __( 'Icon', 'js_composer' ),
                'param_name' => 'icon_linecons',
                'value' => 'vc_li vc_li-heart', // default value to backend editor admin_label
                'settings' => array(
                  'emptyIcon' => true, // default true, display an "EMPTY" icon?
                  'type' => 'linecons',
                  'iconsPerPage' => 4000, // default 100, how many icons per/page to display
                ),
                'dependency' => array(
                  'element' => 'handleicon',
                  'value' => 'linecons',
                ),
                "group" => "Handle",
                'description' => __( 'Select icon from library.', 'js_composer' ),
              ),
              array(
                "type" => "textfield",
                "class" => "",
                "heading" => __("Tooltip for the handle (optional)", "vc_beforeafter_cq"),
                "param_name" => "handletooltip",
                "value" => "",
                "group" => "Handle",
                "description" => __("", "vc_beforeafter_cq")
              ),
              array(
                'type' => 'vc_link',
                'heading' => __( 'URL (Optional link for the whole element)', 'vc_beforeafter_cq' ),
                'param_name' => 'link',
                "group" => "Link",
                'description' => __("Note: You'd better setup a link with the auto slide enabled. Otherwise it will open this link when you drag the handle.", 'vc_beforeafter_cq' )
              ),
              array(
                "type" => "colorpicker",
                "holder" => "div",
                "class" => "",
                "heading" => __("Icon color", 'vc_beforeafter_cq'),
                "param_name" => "iconcolor",
                "value" => '',
                "group" => "Handle",
                "description" => __("", 'vc_beforeafter_cq')
              ),
              array(
                "type" => "textfield",
                "heading" => __("Extra class name", "vc_beforeafter_cq"),
                "param_name" => "extraclass",
                "value" => "",
                "description" => __("If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", "vc_beforeafter_cq")
              )

           )
        ));


        }else{

          wpb_map(array(
            "name" => __("Before & After", 'vc_beforeafter_cq'),
            "base" => "cq_vc_beforeafter",
            "class" => "wpb_cq_vc_extension_beforeafter",
            // "as_parent" => array('only' => 'cq_vc_beforeafter_item'),
            "icon" => "cq_allinone_beforeafter",
            "category" => __('Sike Extensions', 'js_composer'),
            // "content_element" => false,
            // "show_settings_on_create" => false,
            'description' => __('Image comparison slider', 'js_composer'),
            "params" => array(
              array(
                "type" => "attach_image",
                "heading" => __("Before image", "vc_beforeafter_cq"),
                "param_name" => "beforeimage",
                "value" => "",
                "description" => __("Select image from media library.", "vc_beforeafter_cq")
              ),
              array(
                "type" => "attach_image",
                "heading" => __("After image", "vc_beforeafter_cq"),
                "param_name" => "afterimage",
                "value" => "",
                "description" => __("Select image from media library.", "vc_beforeafter_cq")
              ),
              array(
                "type" => "dropdown",
                "holder" => "",
                "class" => "vc_beforeafter_cq",
                "heading" => __("Auto slide the handle", "vc_beforeafter_cq"),
                "param_name" => "autoslide",
                'value' => array(1, 2, 3, 4, 5, 6, 8, 10, __( 'Disable', 'vc_beforeafter_cq' ) => 0 ),
                'std' => 0,
                "description" => __("Auto slide the handle in each X seconds.", "vc_beforeafter_cq")
              ),
              array(
                "type" => "dropdown",
                "holder" => "",
                "class" => "vc_beforeafter_cq",
                "heading" => __("Handle color style:", "vc_beforeafter_cq"),
                "param_name" => "handlestyle",
                "value" => array("Orange" => "", "Grape Fruit" => "grapefruit", "Grass" => "grass", "Aqua" => "aqua", "Lavender" => "lavender", "Pink Rose" => "pinkrose", "Light Gray" => "lightgray", "Dark Gray" => "darkgray"),
                'std' => 'lightgray',
                "group" => "Handle",
                "description" => __("", "vc_beforeafter_cq")
              ),
              array(
                "type" => "textfield",
                "heading" => __("Handle icon:", "vc_beforeafter_cq"),
                "param_name" => "handleicon",
                "value" => "",
                "group" => "Handle",
                "description" => __("Support <a href='http://fortawesome.github.io/Font-Awesome/icons/' target='_blank'>Font Awesome icon</a> here. For example fa-twitter will insert a Twitter icon</a>", "vc_beforeafter_cq")
              ),
              array(
                "type" => "textfield",
                "class" => "",
                "heading" => __("Tooltip for the handle (optional)", "vc_beforeafter_cq"),
                "param_name" => "handletooltip",
                "value" => "",
                "group" => "Handle",
                "description" => __("", "vc_beforeafter_cq")
              ),
              array(
                'type' => 'vc_link',
                'heading' => __( 'URL (Optional link for the whole element)', 'vc_beforeafter_cq' ),
                'param_name' => 'link',
                "group" => "Link",
                'description' => __("Note: You'd better setup a link with the auto slide enabled. Otherwise it will open this link when you drag the handle.", 'vc_beforeafter_cq' )
              ),
              array(
                "type" => "colorpicker",
                "holder" => "div",
                "class" => "",
                "heading" => __("Icon color", 'vc_beforeafter_cq'),
                "param_name" => "iconcolor",
                "value" => '',
                "group" => "Handle",
                "description" => __("", 'vc_beforeafter_cq')
              ),
              array(
                "type" => "textfield",
                "heading" => __("Extra class name", "vc_beforeafter_cq"),
                "param_name" => "extraclass",
                "value" => "",
                "description" => __("If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", "vc_beforeafter_cq")
              )

           )
        ));


        }


        function cq_vc_beforeafter_func($atts, $content=null, $tag) {
          $handleicon = $icon_fontawesome = $icon_openiconic = $icon_typicons = $icon_entypo = $icon_linecons = '';
          if(version_compare(WPB_VC_VERSION,  "4.6") >= 0){
              $atts = vc_map_get_attributes($tag,$atts);
              extract($atts);
          }else{
            extract(shortcode_atts(array(
              "handleicon" => '',
              "icon_fontawesome" => '',
              "icon_openiconic" => '',
              "icon_typicons" => '',
              "icon_entypo" => '',
              "icon_linecons" => '',
              "beforeimage" => '',
              "afterimage" => '',
              "handletooltip" => '',
              "link" => '',
              "autoslide" => '',
              "handlestyle" => 'lightgray',
              "iconcolor" => '',
              "extraclass" => ""
            ), $atts));
          }

          $content = wpb_js_remove_wpautop($content); // fix unclosed/unwanted paragraph tags in $content
          $output = '';

          if(version_compare(WPB_VC_VERSION,  "4.4")>= 0){
            vc_icon_element_fonts_enqueue($handleicon);
          }else{
            wp_register_style( 'font-awesome', plugins_url('../faanimation/css/font-awesome.min.css', __FILE__) );
            wp_enqueue_style( 'font-awesome' );
          }

          $link = vc_build_link($link);

          wp_register_style('tooltipster', plugins_url('../appmockup/css/tooltipster.css', __FILE__));
          wp_enqueue_style('tooltipster');
          wp_register_script('tooltipster', plugins_url('../appmockup/js/jquery.tooltipster.min.js', __FILE__), array('jquery'));
          wp_enqueue_script('tooltipster');

          wp_register_style( 'vc-extensions-beforeafter-style', plugins_url('css/style.css', __FILE__) );
          wp_enqueue_style( 'vc-extensions-beforeafter-style' );
          wp_register_script('jquery.mobile.touch', plugins_url('js/jquery.mobile.custom.min.js', __FILE__));
          wp_enqueue_script('jquery.mobile.touch');
          wp_register_script('vc-extensions-beforeafter-script', plugins_url('js/init.min.js', __FILE__), array("jquery", "jquery.mobile.touch", "tooltipster"));
          wp_enqueue_script('vc-extensions-beforeafter-script');
          $beforeimage = wp_get_attachment_image_src($beforeimage, 'full');
          $afterimage = wp_get_attachment_image_src($afterimage, 'full');
          $output = '';
          if($link["url"]!=="") $output .= '<a href="'.$link["url"].'" title="'.$link["title"].'" target="'.$link["target"].'" class="cq-beforeafter-link">';
          $output .= '<div class="cq-beforeafter '.$extraclass.'" data-autoslide="'.$autoslide.'" data-iconcolor="'.$iconcolor.'">';
          $output .= '<img class="cq-beforeafter-img" src="'.$beforeimage[0].'" />';
          $output .= '<div class="cq-beforeafter-resize">';
          $output .= '<img class="cq-beforeafter-img" src="'.$afterimage[0].'" />';
          $output .= '</div>';
          $output .= '<span class="cq-beforeafter-handle '.$handlestyle.'">';
          if(version_compare(WPB_VC_VERSION,  "4.4")>=0&&isset(${'icon_' . $handleicon})){
              $output .= '<i class="'.esc_attr(${'icon_' . $handleicon}).'" title="'.$handletooltip.'"></i> ';
          }else{
              $output .= '<i class="fa '.$handleicon.'" title="'.$handletooltip.'"></i>';
          }
          $output .= '</span>';
          $output .= '</div>';
          if($link["url"]!=="") $output .= '</a>';


          return $output;

        }

        add_shortcode('cq_vc_beforeafter', 'cq_vc_beforeafter_func');

      }
  }

}

?>
