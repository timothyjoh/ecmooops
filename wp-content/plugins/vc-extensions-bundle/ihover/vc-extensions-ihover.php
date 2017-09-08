<?php
if (!class_exists('VC_Extensions_iHover')) {

    class VC_Extensions_iHover {
        function VC_Extensions_iHover() {
          // add_shortcode('cq_vc_ihover', array(&$this, 'cq_vc_ihover_func'));
          wpb_map( array(
            "name" => __("iHover", 'vc_ihover_cq'),
            "base" => "cq_vc_ihover",
            "class" => "wpb_cq_vc_extension_ihover",
            "controls" => "full",
            "icon" => "cq_allinone_ihover",
            "category" => __('Sike Extensions', 'js_composer'),
            'description' => __('Caption with transition', 'js_composer' ),
            "params" => array(
              array(
                "type" => "attach_images",
                "heading" => __("Images (support multiple image at once)", "vc_ihover_cq"),
                "param_name" => "images",
                "value" => "",
                "description" => __("Select images from media library.", "vc_ihover_cq")
              ),
              array(
                "type" => "exploded_textarea",
                "holder" => "",
                "class" => "vc_ihover_cq",
                "heading" => __("Thumbnail title", 'vc_ihover_cq'),
                "param_name" => "thumbtitle",
                "value" => __("Thumbnail title", 'vc_ihover_cq'),
                "description" => __("Enter title for each thumbnail here. Divide each with linebreaks (Enter).", 'vc_ihover_cq')
              ),
              array(
                "type" => "exploded_textarea",
                "holder" => "",
                "class" => "vc_ihover_cq",
                "heading" => __("Thumbnail description", 'vc_ihover_cq'),
                "param_name" => "thumbdesc",
                "value" => __("Thumbnail description", 'vc_ihover_cq'),
                "description" => __("Enter description for each thumbnail here. Divide each with linebreaks (Enter).", 'vc_ihover_cq')
              ),
              array(
                  "type" => "dropdown",
                  "heading" => __("Thumbnail shape", "vc_ihover_cq"),
                  "param_name" => "shape",
                  "description" => __('Select the thumbnail shape.', 'vc_ihover_cq'),
                  "value" => array(__("square", "vc_ihover_cq") => 'square', __("circle", "vc_ihover_cq") => 'circle')
              ),
              array(
                "type" => "dropdown",
                "holder" => "",
                "class" => "vc_ihover_cq",
                "heading" => __("Effect", "vc_ihover_cq"),
                "param_name" => "effect",
                "value" => array(__("effect1", "vc_ihover_cq") => "effect1", __("effect2", "vc_ihover_cq") => "effect2", __("effect3", "vc_ihover_cq") => "effect3", __("effect4", "vc_ihover_cq") => "effect4", __("effect5", "vc_ihover_cq") => "effect5", __("effect6", "vc_ihover_cq") => "effect6", __("effect7", "vc_ihover_cq") => "effect7", __("effect8", "vc_ihover_cq") => "effect8", __("effect9", "vc_ihover_cq") => "effect9", __("effect10", "vc_ihover_cq") => "effect10", __("effect11", "vc_ihover_cq") => "effect11", __("effect12", "vc_ihover_cq") => "effect12", __("effect13", "vc_ihover_cq") => "effect13", __("effect14", "vc_ihover_cq") => "effect14", __("effect15", "vc_ihover_cq") => "effect15", __("effect16", "vc_ihover_cq") => "effect16", __("effect17", "vc_ihover_cq") => "effect17", __("effect18", "vc_ihover_cq") => "effect18", __("effect19", "vc_ihover_cq") => "effect19", __("effect20", "vc_ihover_cq") => "effect20"),
                "description" => __("Choose the hover effect.", "vc_ihover_cq")
              ),
              array(
                  "type" => "dropdown",
                  "heading" => __("Animation direction", "vc_ihover_cq"),
                  "param_name" => "direction1",
                  "description" => __('The animaion direction', 'vc_ihover_cq'),
                  "value" => array(__("left_to_right", "vc_ihover_cq") => 'left_to_right', __("right_to_left", "vc_ihover_cq") => 'right_to_left', __("top_to_bottom", "vc_ihover_cq") => 'top_to_bottom', __("bottom_to_top", "vc_ihover_cq") => 'bottom_to_top'),
                  "dependency" => Array('element' => "effect", 'value' => array('effect1', 'effect2', 'effect3', 'effect4', 'effect5', 'effect7', 'effect8', 'effect9', 'effect10', 'effect11', 'effect12', 'effect13', 'effect14', 'effect15', 'effect16', 'effect17', 'effect18', 'effect19'))
              ),
              array(
                  "type" => "dropdown",
                  "heading" => __("Animation direction", "vc_ihover_cq"),
                  "param_name" => "direction2",
                  "description" => __('The animaion direction', 'vc_ihover_cq'),
                  "value" => array(__("top_to_bottom", "vc_ihover_cq") => 'top_to_bottom', __("bottom_to_top", "vc_ihover_cq") => 'bottom_to_top'),
                  "dependency" => Array('element' => "effect", 'value' => array('effect20'))
              ),
              array(
                  "type" => "dropdown",
                  "heading" => __("Animation direction", "vc_ihover_cq"),
                  "param_name" => "direction3",
                  "description" => __('The animaion direction', 'vc_ihover_cq'),
                  "value" => array(__("from_top_and_bottom", "vc_ihover_cq") => 'from_top_and_bottom', __("from_left_and_right", "vc_ihover_cq") => 'from_left_and_right', __("top_to_bottom", "vc_ihover_cq") => 'top_to_bottom', __("bottom_to_top", "vc_ihover_cq") => 'bottom_to_top'),
                  "dependency" => Array('element' => "effect", 'value' => array('effect6'))
              ),
              array(
                "type" => "dropdown",
                "holder" => "",
                "class" => "vc_ihover_cq",
                "heading" => __("On click", "vc_ihover_cq"),
                "param_name" => "onclick",
                "value" => array(__("open large image (lightbox)", "vc_ihover_cq") => "link_image", __("Do nothing", "vc_ihover_cq") => "link_no", __("Open custom link", "vc_ihover_cq") => "custom_link"),
                "description" => __("Define action for onclick event if needed.", "vc_ihover_cq")
              ),
              array(
                "type" => "exploded_textarea",
                "heading" => __("Custom links", "vc_ihover_cq"),
                "param_name" => "custom_links",
                "description" => __('Enter links for each slide here. Divide links with linebreaks (Enter).', 'vc_ihover_cq'),
                "dependency" => Array('element' => "onclick", 'value' => array('custom_link'))
              ),
              array(
                "type" => "dropdown",
                "heading" => __("Custom link target", "vc_ihover_cq"),
                "param_name" => "custom_links_target",
                "description" => __('Select where to open  custom links.', 'vc_ihover_cq'),
                "dependency" => Array('element' => "onclick", 'value' => array('custom_link')),
                'value' => array(__("Same window", "vc_ihover_cq") => "_self", __("New window", "vc_ihover_cq") => "_blank")
              ),
              array(
                "type" => "textfield",
                "holder" => "",
                "class" => "vc_ihover_cq",
                "heading" => __("Thumbnail width", 'vc_ihover_cq'),
                "param_name" => "itemwidth",
                "value" => __("240", 'vc_ihover_cq'),
                "description" => __("Width of each thumbnail.", 'vc_ihover_cq')
              ),
              array(
                "type" => "textfield",
                "holder" => "",
                "class" => "vc_ihover_cq",
                "heading" => __("Thumbnail height", 'vc_ihover_cq'),
                "param_name" => "itemheight",
                "value" => __("160", 'vc_ihover_cq'),
                "description" => __("Height of each thumbnail.", 'vc_ihover_cq')
              ),
              array(
                "type" => "textfield",
                "holder" => "",
                "class" => "vc_ihover_cq",
                "heading" => __("Thumbnail margin", 'vc_ihover_cq'),
                "param_name" => "margin",
                "value" => __("0 16px 0 0", 'vc_ihover_cq'),
                "description" => __("Margin of each thumbnail, default is 0 16px 0 0, which means margin right for 16px.", 'vc_ihover_cq')
              ),
              array(
                "type" => "textfield",
                "holder" => "",
                "class" => "vc_ihover_cq",
                "heading" => __("Thumbnail title padding", 'vc_ihover_cq'),
                "param_name" => "thumbtitlepadding",
                "value" => __("", 'vc_ihover_cq'),
                "description" => __("Default is <strong>55px 0 0 0</strong> change to other vaule as you like.", 'vc_ihover_cq')
              ),
              array(
                "type" => "textfield",
                "holder" => "",
                "class" => "vc_ihover_cq",
                "heading" => __("Thumbnail description padding", 'vc_ihover_cq'),
                "param_name" => "thumbdescpadding",
                "value" => __("", 'vc_ihover_cq'),
                "description" => __("Default is <strong>10px 5px</strong> change to other vaule as you like.", 'vc_ihover_cq')
              ),
              array(
                "type" => "textfield",
                "holder" => "",
                "class" => "vc_ihover_cq",
                "heading" => __("Whole caption padding", 'vc_ihover_cq'),
                "param_name" => "wholecaptionpadding",
                "value" => __("", 'vc_ihover_cq'),
                "description" => __("Whole caption padding, default is 0. Sometimes you've to move the text below, for example <strong>40px 0 0 0</strong> will move the text 40px below.", 'vc_ihover_cq')
              ),
              array(
                "type" => "textfield",
                "holder" => "",
                "class" => "vc_ihover_cq",
                "heading" => __("Container width", 'vc_ihover_cq'),
                "param_name" => "gridwidth",
                "value" => __("", 'vc_ihover_cq'),
                "description" => __("Width of the whole contaier. Default is 90% and align center.", 'vc_ihover_cq')
              ),
              array(
                "type" => "textfield",
                "holder" => "",
                "class" => "vc_ihover_cq",
                "heading" => __("Container offset", 'vc_ihover_cq'),
                "param_name" => "outeroffset",
                "value" => __("0", 'vc_ihover_cq'),
                "description" => __("Offset of the whole elemetn to it's container, for example <strong>0 0 0 40px</strong> will move the element 40px from the left.", 'vc_ihover_cq')
              ),
              array(
                "type" => "textfield",
                "heading" => __("Extra class name for the thumbnail", "vc_ihover_cq"),
                "param_name" => "el_class",
                "description" => __("If you wish to style thumbnail li element differently, then use this field to add a class name and then refer to it in your css file.", "vc_ihover_cq")
              ),
              array(
                "type" => "checkbox",
                "holder" => "",
                "class" => "vc_ihover_cq",
                "heading" => __("Display in an alternative color", 'vc_ihover_cq'),
                "param_name" => "colored",
                "value" => array(__("Yes", "vc_ihover_cq") => 'on'),
                "description" => __("There is 2 color options for the hover, checked this to display the alternative one.", 'vc_ihover_cq')
              ),
              array(
                "type" => "checkbox",
                "holder" => "",
                "class" => "vc_ihover_cq",
                "heading" => __("Make the thumbnails retina?", 'vc_ihover_cq'),
                "param_name" => "retina",
                "value" => array(__("Yes", "vc_ihover_cq") => 'on'),
                "description" => __("For example a 640x480 thumbnail will display as 320x240 in retina mode.", 'vc_ihover_cq')
              )
            )
        ));

        function cq_vc_ihover_func($atts, $content=null, $tag) {
          if(version_compare(WPB_VC_VERSION,  "4.6") >= 0){
              $atts = vc_map_get_attributes($tag,$atts);
              extract($atts);
          }else{
            extract( shortcode_atts( array(
              'images' => '',
              'thumbtitle' => '',
              'thumbdesc' => '',
              'shape' => 'square',
              'effect' => 'effect1',
              // 'direction' => 'left_to_right',
              'direction1' => '',
              'direction2' => '',
              'direction3' => '',
              'itemwidth' => '240',
              'itemheight' => '240',
              'minwidth' => '240',
              'offset' => '4px',
              'thumbtitlepadding' => '',
              'wholecaptionpadding' => '',
              'thumbdescpadding' => '',
              'margin' => '0',
              'gridwidth' => '',
              'onclick' => 'link_image',
              'custom_links' => '',
              'custom_links_target' => '',
              'outeroffset' => '0',
              'background' => '#fff',
              'retina' => 'off',
              'colored' => 'off',
              'el_class' => '',
              'margintop' => '40'
            ), $atts ) );
          }

          wp_enqueue_style('cq_ihover_grid', plugins_url('css/ihover_grid.css', __FILE__));
          wp_register_style( 'vc_ihover_cq_style', plugins_url('css/ihover.css', __FILE__) );
          wp_enqueue_style( 'vc_ihover_cq_style' );
          wp_register_script('fs.boxer', plugins_url('js/jquery.fs.boxer.min.js', __FILE__), array('jquery'));
          wp_enqueue_script('fs.boxer');
          wp_enqueue_script('ihover.init', plugins_url('js/ihover.init.min.js', __FILE__), array('jquery'));
          wp_register_style('fs.boxer', plugins_url('css/jquery.fs.boxer.css', __FILE__));
          wp_enqueue_style('fs.boxer');

          $custom_links = explode( ',', $custom_links);

          global $post;
          $imagesarr = explode(',', $images);
          // $content = wpb_js_remove_wpautop($content); // fix unclosed/unwanted paragraph tags in $content


          $thumbtitles = explode( ',', $thumbtitle);
          $thumbdescs = explode( ',', $thumbdesc);

          $direction = '';
          if($effect=="effect20"){
            $direction = $direction2;
          }else if($effect=="effect6"){
            $direction = $direction3;
          }else{
            $direction = $direction1;
          }
          $offset = '';
          $img_container = '';
          $info_container = '';
          $info_text = '';
          $thumb_title = '';
          $thumb_desc = '';
          $link_start = '';
          $link_end = '';
          $shape_start = '';
          $shape_end = '';
          $output = '';
          $colored = $colored=="on"?"colored":"";
          $output .= '<div class="ihovergrid-container" style="width:'.$gridwidth.';">';
          $output .= '<ul class="ihover-container" data-width="'.$itemwidth.'" data-height="'.$itemheight.'" data-padding="'.$offset.'" data-margin="'.$margin.'" data-thumbtitlepadding="'.$thumbtitlepadding.'" data-wholecaptionpadding="'.$wholecaptionpadding.'" data-thumbdescpadding="'.$thumbdescpadding.'" data-effect="'.$effect.'" data-shape="'.$shape.'" data-outeroffset="'.$outeroffset.'">';
          $i = -1;
          $gallery_id = $post->ID.rand(0, 100);
          foreach ($imagesarr as $key => $value) {
              $i++;
              $info_container = '';
              $info_text = '';
              $img_container = '';
              $thumb_title = '';
              $thumb_desc = '';
              $shape_div = '';
              $link_start = '';
              $link_end = '';
              $shape_start = '';
              $shape_end = '';
              if($i<count($thumbtitles)){
                $thumb_title = $thumbtitles[$i];
              }else{
                $thumb_title = '';
              }
              if($i<count($thumbdescs)){
                $thumb_desc = $thumbdescs[$i];
              }else{
                $thumb_desc = '';
              }
              $output .= "<li class='".$el_class."'>";
              if(wp_get_attachment_image_src(trim($value), 'full')){
                $return_img_arr = wp_get_attachment_image_src(trim($value), 'full');
                // $return_img_height = getimagesize(aq_resize($return_img_arr[0], $itemwidth));
                if($shape=="circle"&&$effect=="effect13"){
                  if($direction=="left_to_right"||$direction=="right_to_left") $direction = "from_left_and_right";
                }
                if($shape=="circle"&&$effect=="effect1"){
                  $direction = "";
                }
                if($shape=="circle"&&$effect=="effect10"){
                  if($direction=="left_to_right") $direction = "top_to_bottom";
                  if($direction=="right_to_left") $direction = "bottom_to_top";
                }

                if($shape=="square"&&$effect=="effect1"){
                  if($direction=="left_to_right"||$direction=="right_to_left") $direction = "left_and_right";
                }
                if($shape=="square"&&$effect=="effect3"){
                  if($direction=="left_to_right") $direction = "top_to_bottom";
                  if($direction=="right_to_left") $direction = "bottom_to_top";
                }


                if($effect=="effect6" || $effect=="effect8"){
                  $shape_start .= "<div class='ih-item ".$colored." ".$shape." ".$effect." ".$direction." scale_up'>";
                }else{
                  $shape_start .= "<div class='ih-item ".$colored." ".$shape." ".$effect." ".$direction."'>";
                }
                if($effect=="effect1"){
                  if($shape=="circle") $img_container .= "<div class='spinner'></div>";
                }
                if($effect=="effect8"){
                  $img_container .= "<div class='img-container'>";
                }
                $img_container .= "<div class='img'>";
                $img_container .= "<img src='".aq_resize($return_img_arr[0], $retina=="on"?$itemwidth*2:$itemwidth, $retina=="on"?$itemheight*2:$itemheight, true, true, true)."' width='$itemwidth' height='".$itemheight."' />";
                $img_container .= "</div>";
                if($effect=="effect8"){
                  $img_container .= "</div>";
                }
                if($shape=="square"&&$effect=="effect4"){
                  $img_container .= "<div class='mask1'></div><div class='mask2'></div>";
                }

                if($thumb_title!="")$info_text .= "<h3>".$thumb_title."</h3>";
                if($thumb_desc!="")$info_text .= "<p>".$thumb_desc."</p>";
                if($shape=="circle"){
                  if($effect=="effect8"){
                    $info_container .= "<div class='info-container'>";
                    $info_container .= "<div class='info'>";
                    $info_container .= $info_text;
                    $info_container .= "</div>";
                    $info_container .= "</div>";
                  }else{
                    $info_container .= "<div class='info'>";
                    if($effect=="effect5"||$effect=="effect13"||$effect=="effect18"||$effect=="effect20"){
                      $info_container .= "<div class='info-back'>";
                      $info_container .= $info_text;
                      $info_container .= "</div>";
                    }else{
                      $info_container .= $info_text;
                    }
                    $info_container .= "</div>";
                  }
                }else{
                  // specify element for the square
                  $info_container .= "<div class='info'>";
                    if($effect=="effect9"){
                      $info_container .= "<div class='info-back'>";
                      $info_container .= $info_text;
                      $info_container .= "</div>";
                    }else{
                      $info_container .= $info_text;
                    }
                    $info_container .= "</div>";

                }

                if($onclick=='link_image'){
                  $link_start .= "<a href='".$return_img_arr[0]."' class='lightbox-link' rel='".$gallery_id."'>";
                }else if($onclick=='custom_link'){
                  if($i<count($custom_links)){
                    $link_start .= "<a href='".$custom_links[$i]."' target='".$custom_links_target."'>";
                  }
                }else{
                  $link_start .= "<a href='#' class='ihover-nothing'>";
                }
            }
            $link_end .= "</a>";
            $shape_end .= "</div>";
            $output .= $shape_start.$link_start.$img_container.$info_container.$link_end.$shape_end;
            $output .= "</li>";
          }
          $output .= '</ul>';
          $output .= '</div>';

          return $output;

        }


        add_shortcode('cq_vc_ihover', 'cq_vc_ihover_func');

      }
  }


  // copy below line to your theme's function.php
  // if(class_exists('VC_Extensions_iHover')) $vc_extensions_ihover = new VC_Extensions_iHover();
}

?>
