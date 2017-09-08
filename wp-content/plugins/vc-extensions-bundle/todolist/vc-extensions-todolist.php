<?php
if (!class_exists('VC_Extensions_ToDoList')) {

    class VC_Extensions_ToDoList {
        function VC_Extensions_ToDoList() {
          wpb_map( array(
            "name" => __("To Do List", 'vc_todolist_cq'),
            "base" => "cq_vc_todolist",
            "class" => "wpb_cq_vc_extension_todolist",
            "controls" => "full",
            "icon" => "cq_allinone_todolist",
            "category" => __('Sike Extensions', 'js_composer'),
            'description' => __('To Do List or Price Table', 'js_composer' ),
            "params" => array(
              array(
                "type" => "textfield",
                "heading" => __("Header text", "vc_todolist_cq"),
                "param_name" => "header",
                "value" => __("To Do List", 'vc_todolist_cq'),
                "description" => __("", "vc_todolist_cq")
              ),
              array(
                "type" => "colorpicker",
                "holder" => "div",
                "class" => "",
                "heading" => __("Header text color", 'vc_todolist_cq'),
                "param_name" => "headercolor",
                "value" => '#FFFFFF',
                "description" => __("", 'vc_todolist_cq')
              ),
              array(
                "type" => "colorpicker",
                "holder" => "div",
                "class" => "",
                "heading" => __("Header text background", 'vc_todolist_cq'),
                "param_name" => "headerbackground",
                "value" => '#663399',
                "description" => __("", 'vc_todolist_cq')
              ),
              array(
                "type" => "attach_image",
                "heading" => __("Optional repeat pattern for the header", "vc_hotspot_cq"),
                "param_name" => "headerpattern",
                "value" => "",
                "description" => __("Select image pattern from media library.", "vc_hotspot_cq")
              ),
              array(
                "type" => "textarea_html",
                "holder" => "div",
                "heading" => __("List item, divide each one with &lt;div class=&#039;cqlist-item&#039;&gt;&lt;/div&gt;, please edit in text mode:", "vc_todolist_cq"),
                "param_name" => "content",
                "value" => __("<div class='cqlist-item'>You have to wrap each item text in a div with class <strong>cqlist-item</strong>. Something like:
                &lt;div class='cqlist-item'&gt;Buy plane tickets&lt;/div&gt;
                </div>
                <div class='cqlist-item'>
                  Buy plane tickets
                </div>
                <div class='cqlist-item'>
                  Plan Birthday Party
                </div>
                <div class='cqlist-item'>
                Water the plants, <a href='http://codecanyon.net/user/sike?ref=sike'>Visit my profile</a> for more works. </div>
                <div class='cqlist-item'>
                  Make dinner reservation
                </div>
                <div class='cqlist-item'>Pay electricity bill</div>", "vc_todolist_cq"),
                "description" => __("Enter content for each block here. Divide each with paragraph (Enter).", "vc_todolist_cq") ),
              array(
                "type" => "exploded_textarea",
                "holder" => "",
                "class" => "vc_todolist_cq",
                "heading" => __("Icon for each list item", 'vc_todolist_cq'),
                "param_name" => "icon",
                "value" => __("fa-square-o,fa-plane,fa-birthday-cake,fa-square-o,fa-cutlery,fa-cc-visa", 'vc_todolist_cq'),
                "description" => __("You can find all the available <a href='http://fortawesome.github.io/Font-Awesome/icons/'>Font Awesome icon</a>, leave it to be blank if you do not need it.", 'vc_todolist_cq')
              ),
              array(
                "type" => "exploded_textarea",
                "holder" => "",
                "class" => "vc_todolist_cq",
                "heading" => __("Optional label divider", 'vc_todolist_cq'),
                "param_name" => "datedivider",
                "value" => __("", 'vc_todolist_cq'),
                "description" => __("You can insert an optional (date)label to the item list, divide with linebreak (Enter), it will appear as the line position.", 'vc_todolist_cq')
              ),
              array(
                "type" => "attach_image",
                "heading" => __("Optional repeat pattern for the label divider", "vc_hotspot_cq"),
                "param_name" => "labelpattern",
                "value" => "",
                "description" => __("Select image pattern from media library.", "vc_hotspot_cq")
              ),
              array(
                "type" => "exploded_textarea",
                "holder" => "",
                "class" => "vc_todolist_cq",
                "heading" => __("Color for each icon", 'vc_todolist_cq'),
                "param_name" => "iconcolor",
                "value" => __("#333333,#4682B4,#FF6347", 'vc_todolist_cq'),
                "description" => __("You can specify color for each icon, default (leave it to be blank) will be in black.", 'vc_todolist_cq')
              ),
              array(
                "type" => "dropdown",
                "heading" => __("Make the list interactive?", "vc_todolist_cq"),
                "param_name" => "isclickable",
                "description" => __('Make the to do list clickable or not. Note, the interactive only work in front-end without saving data, which means everytime you reload the page the list will be reset.', 'vc_todolist_cq'),
                'value' => array(__("yes", "vc_todolist_cq") => "yes", __("no", "vc_todolist_cq") => "no")
              ),
              array(
                "type" => "textfield",
                "heading" => __("Change the icon to below one after clicking:", "vc_todolist_cq"),
                "param_name" => "clickedicon",
                "value" => __("fa-check-square-o", 'vc_todolist_cq'),
                "dependency" => Array('element' => "isclickable", 'value' => array('yes')),
                "description" => __("Change the icon to this one when user mark the item as done.", "vc_todolist_cq")
              ),
              array(
                "type" => "dropdown",
                "holder" => "",
                "class" => "vc_todolist_cq",
                "heading" => __("Display sign up button below the list", "vc_todolist_cq"),
                "param_name" => "issignup",
                "value" => array(__("no", "vc_todolist_cq") => "no", __("yes", "vc_todolist_cq") => "yes"),
                "description" => __("Append a button to the end of the list, you can use this as a Price Table.", "vc_todolist_cq")
              ),
              array(
                "type" => "textfield",
                "heading" => __("Sign up button text", "vc_todolist_cq"),
                "param_name" => "signuptext",
                "value" => __("Sign Up", 'vc_todolist_cq'),
                "dependency" => Array('element' => "issignup", 'value' => array('yes')),
                "description" => __("", "vc_todolist_cq")
              ),
              array(
                "type" => "textfield",
                "heading" => __("Link of the sign up button", "vc_todolist_cq"),
                "param_name" => "signuplink",
                "value" => __("", 'vc_todolist_cq'),
                "dependency" => Array('element' => "issignup", 'value' => array('yes')),
                "description" => __("", "vc_todolist_cq")
              ),
              array(
                "type" => "dropdown",
                "heading" => __("How to open the sign up button link", "vc_todolist_cq"),
                "param_name" => "custom_links_target",
                "description" => __('Select how to open custom links.', 'vc_todolist_cq'),
                "dependency" => Array('element' => "onclick", 'value' => array('custom_link')),
                'value' => array(__("Same window", "vc_todolist_cq") => "_self", __("New window", "vc_todolist_cq") => "_blank")
              ),
              array(
                "type" => "textfield",
                "heading" => __("CSS padding of the sign up button", "vc_todolist_cq"),
                "param_name" => "signuppadding",
                "value" => __("6px 8px", 'vc_todolist_cq'),
                "dependency" => Array('element' => "issignup", 'value' => array('yes')),
                "description" => __("", "vc_todolist_cq")
              ),
              array(
                "type" => "textfield",
                "heading" => __("max-width of the sign up button", "vc_todolist_cq"),
                "param_name" => "signupmaxwidth",
                "value" => __("120px", 'vc_todolist_cq'),
                "dependency" => Array('element' => "issignup", 'value' => array('yes')),
                "description" => __("", "vc_todolist_cq")
              ),
              array(
                "type" => "colorpicker",
                "holder" => "div",
                "class" => "",
                "heading" => __("Sign up button text color", 'vc_todolist_cq'),
                "param_name" => "signupcolor",
                "value" => '#FFFFFF',
                "dependency" => Array('element' => "issignup", 'value' => array('yes')),
                "description" => __("", 'vc_todolist_cq')
              ),
              array(
                "type" => "colorpicker",
                "holder" => "div",
                "class" => "",
                "heading" => __("Sign up button background", 'vc_todolist_cq'),
                "param_name" => "signupbackground",
                "value" => '#663399',
                "dependency" => Array('element' => "issignup", 'value' => array('yes')),
                "description" => __("", 'vc_todolist_cq')
              ),
              array(
                "type" => "colorpicker",
                "holder" => "div",
                "class" => "",
                "heading" => __("Sign up button hover background", 'vc_todolist_cq'),
                "param_name" => "signuphoverbackground",
                "value" => '#6495ED',
                "dependency" => Array('element' => "issignup", 'value' => array('yes')),
                "description" => __("", 'vc_todolist_cq')
              ),
              array(
                "type" => "dropdown",
                "heading" => __("Divide each item with", "vc_todolist_cq"),
                "param_name" => "itembg",
                "description" => __('You can choose how to divide each item list, by different background or border only.', 'vc_todolist_cq'),
                'value' => array(__("Background", "vc_todolist_cq") => "background", __("Border", "vc_todolist_cq") => "border")
              ),
              array(
                "type" => "textfield",
                "heading" => __("Container width", "vc_todolist_cq"),
                "param_name" => "containerwidth",
                "description" => __("Default is 100%, you can specify it with a smaller value like 60%, and will be align center automatically.", "vc_todolist_cq")
              ),
              array(
                "type" => "textfield",
                "heading" => __("Extra class name for the container", "vc_todolist_cq"),
                "param_name" => "extra_class",
                "description" => __("If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", "vc_todolist_cq")
              )

            )
        ));

        function cq_vc_todolist_func($atts, $content=null, $tag) {
          if(version_compare(WPB_VC_VERSION,  "4.6") >= 0){
              $atts = vc_map_get_attributes($tag,$atts);
              extract($atts);
          }else{
            extract( shortcode_atts( array(
              'header' => '',
              'width' => '',
              'color' => '',
              'icon' => '',
              'iconcolor' => '',
              'headerbackground' => '#663399',
              'headercolor' => '#FFF',
              'signupbackground' => '#663399',
              'signuphoverbackground' => '#6495ED',
              'signupcolor' => '#FFF',
              'issignup' => '',
              'isclickable' => '',
              'clickedicon' => '',
              'signuptext' => 'Sign Up',
              'signupmaxwidth' => '120px',
              'datedivider' => '',
              'signuppadding' => '',
              'signuplink' => '',
              'itembg' => '',
              'headerpattern' => '',
              'labelpattern' => '',
              'maxwidth' => '',
              'containerwidth' => '',
              'custom_links_target' => '',
              'extra_class' => ''
            ), $atts ) );
          }


          wp_register_style( 'vc_todolist_cq_style', plugins_url('css/style.css', __FILE__));
          wp_enqueue_style( 'vc_todolist_cq_style' );

          wp_register_style( 'font-awesome', plugins_url('../faanimation/css/font-awesome.min.css', __FILE__) );
          wp_enqueue_style( 'font-awesome' );

          wp_register_script('vc_todolist_cq_script', plugins_url('js/script.min.js', __FILE__), array("jquery"));
          wp_enqueue_script('vc_todolist_cq_script');


          $icon = explode(',', $icon);
          // $color = explode(',', $color);
          $iconcolor = explode(',', $iconcolor);
          $datedivider = explode(',', $datedivider);
          $headerpattern = wp_get_attachment_image_src($headerpattern, 'full');
          $labelpattern = wp_get_attachment_image_src($labelpattern, 'full');
          $i = -1;
          $content = wpb_js_remove_wpautop($content); // fix unclosed/unwanted paragraph tags in $content
          $content = str_replace('</div>', '', trim($content));
          $contentarr = explode('<div class="cqlist-item">', $content);
          array_shift($contentarr);
          $output = '';
          $output .= '<div class="cqlist-container '.$extra_class.'" data-isclickable="'.$isclickable.'" data-clickedicon="'.$clickedicon.'" style="width:'.$containerwidth.';">';
          $output .= '<div class="cqlist '.$itembg.'">';
          if($header!=""){
                if($headerpattern[0]!=""){
                  $output .= '<h3 style="color:'.$headercolor.';background-color:'.$headerbackground.';background-image:url('.$headerpattern[0].');">'.$header.'</h3>';
                }else{
                  $output .= '<h3 style="color:'.$headercolor.';background-color:'.$headerbackground.';">'.$header.'</h3>';
                }
          }
          $output .= '<ul>';
          foreach ($contentarr as $key => $thecontent) {
             $i++;
             if(!isset($icon[$i])) $icon[$i] = '';
             if(!isset($iconcolor[$i])) $iconcolor[$i] = '';
             if(!isset($datedivider[$i])) $datedivider[$i] = '';
             if($datedivider[$i]!=""){
                if($labelpattern[0]!=""){
                  $output .= '<span class="cqlist-label" style="background-image:url('.$labelpattern[0].');">'.$datedivider[$i].'</span>';
                }else{
                  $output .= '<span class="cqlist-label">'.$datedivider[$i].'</span>';
                }
             }
             $output .= '<li>';
             if($icon[$i]!='') {$output .= '<a href="#" class="todolist-btn"> <div class="fa '.$icon[$i].'"  style="color:'.$iconcolor[$i].';" data-icon="'.$icon[$i].'"></div> </a>';
                 $output .= '<span class="todolist-content">'.$thecontent.'</span>';
             }else{
                 $output .= '<span class="no-icon">'.$thecontent.'</span>';
             }
             $output .= '</li>';
          }
          $output .= '</ul>';
          $output .= '</div>';
          $output .= '</div>';
          if($issignup=="yes"){
              $output .= '<div class="cqlist-signup">';
              $output .= '<a href="'.$signuplink.'" target="'.$custom_links_target.'" style="padding:'.$signuppadding.';max-width:'.$signupmaxwidth.';color:'.$signupcolor.';background:'.$signupbackground.';" data-signupbackground="'.$signupbackground.'" data-signuphoverbackground="'.$signuphoverbackground.'">'.$signuptext.'</a>';
              $output .= '</div>';
          }

          return $output;

        }

        add_shortcode('cq_vc_todolist', 'cq_vc_todolist_func');

      }
  }

}

?>
