<?php
if (!class_exists('VC_Extensions_Tabs')) {

    class VC_Extensions_Tabs {
        function VC_Extensions_Tabs() {
          wpb_map( array(
            "name" => __("Tabs", 'vc_tabs_cq'),
            "base" => "cq_vc_tabs",
            "class" => "wpb_cq_vc_extension_tab",
            "controls" => "full",
            "icon" => "cq_allinone_tab",
            "category" => __('Sike Extensions', 'js_composer'),
            'description' => __( 'Tabbed content', 'js_composer' ),
            "params" => array(
              array(
                "type" => "textarea_html",
                "holder" => "div",
                "heading" => __("Tabs content, divide(wrap) each one with [tabitem][/tabitem], please edit in text mode:", "vc_tabs_cq"),
                "param_name" => "content",
                "value" => __("[tabitem]
                  You have to wrap each tabs block with <strong>tabitem</strong>.
                  So you can put anything in it, like a image, video and other shortcode.
                  [/tabitem]
                  [tabitem]
                    Tab content 2, please edit it in the text editor.
                  [/tabitem]
                  [tabitem]
                    Tab content 3, please edit it in the text editor.
                    <a href='http://codecanyon.net/user/sike?ref=sike'>Visit my profile</a> for more works.
                  [/tabitem]
                  [tabitem]
                    Yet another content, please edit it in the text editor.
                  [/tabitem]", "vc_tabs_cq"), "description" => __("Please try to edit in the <strong>Text</strong> mode.", "vc_tabs_cq") ),
              array(
                "type" => "colorpicker",
                "holder" => "div",
                "class" => "",
                "heading" => __("Content font color", 'vc_tabs_cq'),
                "param_name" => "contentcolor1",
                "value" => '',
                "dependency" => Array('element' => "tabsstyle", 'value' => array('style1', 'style3')),
                "description" => __("The color of tabs content.", 'vc_tabs_cq')
              ),
              array(
                "type" => "colorpicker",
                "holder" => "div",
                "class" => "",
                "heading" => __("Content font color", 'vc_tabs_cq'),
                "param_name" => "contentcolor2",
                "value" => '',
                "dependency" => Array('element' => "tabsstyle", 'value' => array('style2')),
                "description" => __("The color of tabs content.", 'vc_tabs_cq')
              ),
              array(
                "type" => "colorpicker",
                "holder" => "div",
                "class" => "",
                "heading" => __("Content background color", 'vc_tabs_cq'),
                "param_name" => "contentbg1",
                "dependency" => Array('element' => "tabsstyle", 'value' => array('style1','style3')),
                "value" => '',
                "description" => __("The background color of tabs content.", 'vc_tabs_cq')
              ),
              array(
                "type" => "colorpicker",
                "holder" => "div",
                "class" => "",
                "heading" => __("Content background color", 'vc_tabs_cq'),
                "param_name" => "contentbg2",
                "dependency" => Array('element' => "tabsstyle", 'value' => array('style2')),
                "value" => '',
                "description" => __("The background color of tabs content.", 'vc_tabs_cq')
              ),
              array(
                "type" => "dropdown",
                "holder" => "",
                "class" => "vc_tabs_cq",
                "heading" => __("Select tabs style", "vc_tabs_cq"),
                "param_name" => "tabsstyle",
                "value" => array(__("style 1", "vc_tabs_cq") => "style1", __("style 2", "vc_tabs_cq") => "style2", __("style 3", "vc_tabs_cq") => "style3"),
                "description" => __("", "vc_tabs_cq")
              ),
              array(
                "type" => "exploded_textarea",
                "holder" => "",
                "class" => "vc_tabs_cq",
                "heading" => __("Tab menu", 'vc_tabs_cq'),
                "param_name" => "tabstitle",
                "value" => __("Tab 1,Tab 2,Tab 3,Another Tab", 'vc_tabs_cq'),
                "description" => __("Menu title for each tabs, divide with linebreak (Enter).", 'vc_tabs_cq')
              ),
              array(
                "type" => "dropdown",
                "holder" => "",
                "class" => "vc_tabs_cq",
                "heading" => __("Menu support Font Awesome icon?", "vc_tabs_cq"),
                "param_name" => "iconsupport",
                "value" => array(__("yes", "vc_tabs_cq") => "yes", __("no", "vc_tabs_cq") => "no"),
                "description" => __("", "vc_tabs_cq")
              ),
              array(
                "type" => "exploded_textarea",
                "holder" => "",
                "class" => "vc_tabs_cq",
                "heading" => __("Icon for each tab menu", 'vc_tabs_cq'),
                "param_name" => "tabsicon",
                "value" => __("fa-cloud,fa-image,fa-coffee,fa-comment", 'vc_tabs_cq'),
                "dependency" => Array('element' => "iconsupport", 'value' => array('yes')),
                "description" => __("Put the <a href='http://fortawesome.github.io/Font-Awesome/icons/' target='_blank'>Font Awesome icon</a> here, divide with linebreak (Enter).", 'vc_tabs_cq')
              ),
              array(
                "type" => "colorpicker",
                "holder" => "div",
                "class" => "",
                "heading" => __("Tab menu color", 'vc_tabs_cq'),
                "param_name" => "titlecolor",
                "value" => '',
                "description" => __("The font color of tab in normal mode.", 'vc_tabs_cq')
              ),
              array(
                "type" => "colorpicker",
                "holder" => "div",
                "class" => "",
                "heading" => __("Tab menu background color", 'vc_tabs_cq'),
                "param_name" => "titlebg",
                "value" => '',
                "description" => __("The background color of tab in normal mode.", 'vc_tabs_cq')
              ),
              array(
                "type" => "colorpicker",
                "holder" => "div",
                "class" => "",
                "heading" => __("Tab menu hover font color", 'vc_tabs_cq'),
                "param_name" => "titlehovercolor",
                // "dependency" => Array('element' => "tabsstyle", 'value' => array('style2')),
                "value" => '',
                "description" => __("The font color of tab when user hover or in current mode.", 'vc_tabs_cq')
              ),
              array(
                "type" => "colorpicker",
                "holder" => "div",
                "class" => "",
                "heading" => __("Tab menu background hover color", 'vc_tabs_cq'),
                "param_name" => "titlehoverbg",
                // "dependency" => Array('element' => "tabsstyle", 'value' => array('style2')),
                "value" => '',
                "description" => __("The background color of tab when user hover or in current mode.", 'vc_tabs_cq')
              ),
              array(
                "type" => "dropdown",
                "holder" => "",
                "class" => "vc_tabs_cq",
                "heading" => __("Auto rotate tabs", "vc_tabs_cq"),
                "param_name" => "rotatetabs",
                'value' => array( 3, 5, 10, 15, __( 'Disable', 'vc_tabs_cq' ) => 0 ),
                'std' => 0,
                "description" => __("Auto rotate tabs each X seconds.", "vc_tabs_cq")
              ),
              array(
                "type" => "textfield",
                "heading" => __("Container width", "vc_tabs_cq"),
                "param_name" => "contaienrwidth",
                "value" => "",
                "description" => __("The width of the whole contaienr, default is 100%. You can specify it with a smaller value, like 80%, and it will align center automatically.", "vc_tabs_cq")
              ),
              array(
                "type" => "textfield",
                "heading" => __("Extra class name for the container", "vc_tabs_cq"),
                "param_name" => "extra_class",
                "description" => __("If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", "vc_tabs_cq")
              )

            )
        ));

        function cq_vc_tabs_func($atts, $content=null, $tag) {
          if(version_compare(WPB_VC_VERSION,  "4.6") >= 0){
              $atts = vc_map_get_attributes($tag,$atts);
              extract($atts);
          }else{
            extract( shortcode_atts( array(
              'tabsstyle' => 'style1',
              'titlecolor' => '',
              'titlebg' => '',
              'titlehoverbg' => '',
              'titlehovercolor' => '',
              'tabstitle' => '',
              'tabstitlesize2' => '',
              'contentcolor1' => '',
              'contentbg1' => '',
              'contentcolor2' => '',
              'contentbg2' => '',
              'contaienrwidth' => '',
              'rotatetabs' => '',
              'tabsicon' => '',
              'iconsupport' => 'yes',
              'extra_class' => ''
            ), $atts ) );
          }

          if($iconsupport=="yes"){
            wp_register_style( 'font-awesome', plugins_url('../faanimation/css/font-awesome.min.css', __FILE__) );
            wp_enqueue_style( 'font-awesome' );
          }

          wp_register_style( 'vc_tabs_cq_style', plugins_url('css/style.css', __FILE__));
          wp_enqueue_style( 'vc_tabs_cq_style' );

          wp_register_script('vc_tabs_cq_script', plugins_url('js/script.min.js', __FILE__), array("jquery"));
          wp_enqueue_script('vc_tabs_cq_script');


          $i = -1;
          if($tabsstyle=="style2"){
            $contentcolor = $contentcolor2;
            $contentbg = $contentbg2;
          }else{
            $contentcolor = $contentcolor1;
            $contentbg = $contentbg1;
          }


          $content = wpb_js_remove_wpautop($content); // fix unclosed/unwanted paragraph tags in $content

          $content = preg_replace( '#<p>\s*</p>#', '', $content );
          if(strpos($content, '[/tabitem]')===false){
              $content = str_replace('</div>', '', trim($content));
              $contentarr = explode('<div class="tabs-content">', trim($content));
          }else{
              $content = str_replace('[/tabitem]', '', trim($content));
              $contentarr = explode('[tabitem]', trim($content));
          }
          array_shift($contentarr);
          $tabstitle = explode(',', $tabstitle);
          $tabsicon = explode(',', $tabsicon);
          $output = '';
          $all_start = $all_end = '';
          $menu_start = $menu_content = $menu_end = '';
          $container_start = $container_content = $container_end = '';

          $all_start .= '<div class="cq-tabs '.$extra_class.'" style="width:'.$contaienrwidth.'" data-tabsstyle="'.$tabsstyle.'" data-titlebg="'.$titlebg.'" data-titlecolor="'.$titlecolor.'" data-titlehoverbg="'.$titlehoverbg.'" data-titlehovercolor="'.$titlehovercolor.'" data-rotatetabs="'.$rotatetabs.'">';
          $all_end .= '</div>';
          if($tabsstyle=="style1"){
              $menu_start .= '<ul class="cq-tabmenu '.$tabsstyle.'" style="background-color:'.$titlebg.';border-bottom-color:'.$titlehoverbg.';">';
          }else if($tabsstyle=="style2"){
              $menu_start .= '<ul class="cq-tabmenu '.$tabsstyle.'">';
          }else{
              $menu_start .= '<ul class="cq-tabmenu '.$tabsstyle.'">';
          }
          $menu_end .= '</ul>';

          $container_start .= '<div class="cq-tabcontent '.$tabsstyle.'" style="background:'.$contentbg.';">';
          $container_end .= '</div>';

          foreach ($contentarr as $key => $thecontent) {
              $i++;
              if(!isset($tabstitle[$i])) $tabstitle[$i] = 'Tab '.($i+1);
              if(!isset($tabsicon[$i])) $tabsicon[$i] = '';
              if($tabsstyle=="style3"){
                  $menu_content .= '<li style="background-color:'.$titlebg.';">';
                  $menu_content .= '<a href="#" style="color:'.$titlecolor.';">';
                  $menu_content .= '<span>';
                  if($tabsicon[$i]!="")$menu_content .= '<i class="fa pull-left fa-1x '.$tabsicon[$i].'"></i>';
                  $menu_content .= $tabstitle[$i];
                  $menu_content .= '</span>';
                  $menu_content .= '</a>';
                  $menu_content .= '</li>';
              }else if($tabsstyle=="style2"){
                  $menu_content .= '<li>';
                  $menu_content .= '<a href="#" style="background-color:'.$titlebg.';color:'.$titlecolor.';">';
                  if($tabsicon[$i]!="")$menu_content .= '<i class="fa pull-left fa-1x '.$tabsicon[$i].'"></i>';
                  $menu_content .= $tabstitle[$i];
                  $menu_content .= '</a>';
                  $menu_content .= '</li>';
              }else{
                  $menu_content .= '<li style="background-color:'.$titlebg.';">';
                  $menu_content .= '<a href="#" style="color:'.$titlecolor.';">';
                  if($tabsicon[$i]!="")$menu_content .= '<i class="fa pull-left fa-1x '.$tabsicon[$i].'"></i>';
                  $menu_content .= $tabstitle[$i];
                  $menu_content .= '</a>';
                  $menu_content .= '</li>';

              }
              $container_content .= '<div class="cq-tabitem" style="color:'.$contentcolor.';">';
              // $container_content .= ltrim($thecontent, '<br />');
              $thecontent = preg_replace('/^(<br \/>)*/', "", $thecontent);
              $container_content .= $thecontent;
              $container_content .= '</div>';

          }
          $output .= $all_start.$menu_start.$menu_content.$menu_end.$container_start.$container_content.$container_end.$all_end;

          return $output;

        }

        add_shortcode('cq_vc_tabs', 'cq_vc_tabs_func');

      }
  }

}

?>
