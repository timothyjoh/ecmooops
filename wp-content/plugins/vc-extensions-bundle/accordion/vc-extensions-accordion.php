<?php
if (!class_exists('VC_Extensions_Accordion')) {

    class VC_Extensions_Accordion {
        function VC_Extensions_Accordion() {
          wpb_map( array(
            "name" => __("Accordion", 'vc_accordion_cq'),
            "base" => "cq_vc_accordion",
            "class" => "wpb_cq_vc_extension_accordion",
            "controls" => "full",
            "icon" => "cq_allinone_accordion",
            "category" => __('Sike Extensions', 'js_composer'),
            'description' => __( 'CSS3 accordion', 'js_composer' ),
            "params" => array(
              array(
                "type" => "textarea_html",
                "holder" => "div",
                "heading" => __("Accordion content, divide(wrap) each one with [accordionitem][/accordionitem], please edit in text mode:", "vc_accordion_cq"),
                "param_name" => "content",
                "value" => __("[accordionitem]
                  You have to wrap each accordion block in <strong>accordionitem</strong>.
                  So you can put anything in it, like a image, video or other shortcode.
                  [/accordionitem]
                  [accordionitem]
                  Hello accordion 2
                  You can customize the title, color, font-size, accordion content etc in the backend.
                  Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
                  [/accordionitem]
                  [accordionitem]
                  Hello accordion 3
                  Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
                  [/accordionitem]
                  [accordionitem]
                  Yet another accordion.
                  Hi amco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.
                  <a href='http://codecanyon.net/user/sike?ref=sike'>Visit my profile</a> for more works.
                  [/accordionitem]", "vc_accordion_cq"), "description" => __("Please try to edit in the <strong>Text</strong> mode.", "vc_accordion_cq") ),
              array(
                "type" => "dropdown",
                "holder" => "",
                "class" => "vc_accordion_cq",
                "heading" => __("Select accordion style", "vc_accordion_cq"),
                "param_name" => "accordionstyle",
                "value" => array(__("style 1", "vc_accordion_cq") => "style1", __("style 2", "vc_accordion_cq") => "style2"),
                "description" => __("", "vc_accordion_cq")
              ),
              array(
                "type" => "colorpicker",
                "holder" => "div",
                "class" => "",
                "heading" => __("Content font color", 'vc_accordion_cq'),
                "param_name" => "contentcolor",
                "value" => '#333',
                "description" => __("The color of accordion content.", 'vc_accordion_cq')
              ),
              array(
                "type" => "colorpicker",
                "holder" => "div",
                "class" => "",
                "heading" => __("Content background color", 'vc_accordion_cq'),
                "param_name" => "contentbg",
                // "dependency" => Array('element' => "accordionstyle", 'value' => array('style2')),
                "value" => '',
                "description" => __("The background color of accordion content.", 'vc_accordion_cq')
              ),
              array(
                "type" => "textfield",
                "heading" => __("Title for whole accordion container(optional)", "vc_accordion_cq"),
                "param_name" => "title",
                "value" => "",
                "dependency" => Array('element' => "accordionstyle", 'value' => array('style1')),
                "description" => __("The title of the accordion.", "vc_accordion_cq")
              ),
              array(
                "type" => "textfield",
                "heading" => __("Container title size", "vc_accordion_cq"),
                "param_name" => "titlesize",
                "value" => "",
                "dependency" => Array('element' => "accordionstyle", 'value' => array('style1')),
                "description" => __("The size of the container title. Default is 1.4em.", "vc_accordion_cq")
              ),
              array(
                "type" => "exploded_textarea",
                "holder" => "",
                "class" => "vc_accordion_cq",
                "heading" => __("Menu(title) for each accordion", 'vc_accordion_cq'),
                "param_name" => "accordiontitle",
                "value" => __("Accordion title 1,Accordion title 2,Accordion title 3", 'vc_accordion_cq'),
                "description" => __("Menu title for each accordion, divide with linebreak (Enter). Leave it to be blank and it will fetch text from content automatically.", 'vc_accordion_cq')
              ),
              array(
                "type" => "textfield",
                "heading" => __("Menu title font size", "vc_accordion_cq"),
                "param_name" => "accordiontitlesize1",
                "value" => "",
                "dependency" => Array('element' => "accordionstyle", 'value' => array('style1')),
                "description" => __("The font size of each accordion title. Default is 1.3em.", "vc_accordion_cq")
              ),
              // array(
              //   "type" => "textfield",
              //   "heading" => __("margin-top of the plus/close icon", "vc_accordion_cq"),
              //   "param_name" => "iconmargintop",
              //   "value" => "",
              //   "dependency" => Array('element' => "accordionstyle", 'value' => array('style1')),
              //   "description" => __("The margin-top of the plus/close icon, default is -8px, you may have to specify with other value if you change the title size/padding.", "vc_accordion_cq")
              // ),
              array(
                "type" => "textfield",
                "heading" => __("Accordion content font size", "vc_accordion_cq"),
                "param_name" => "accordioncontentsize1",
                "value" => "",
                "dependency" => Array('element' => "accordionstyle", 'value' => array('style1')),
                "description" => __("The font size of each accordion content. Default is 1em.", "vc_accordion_cq")
              ),
              array(
                "type" => "textfield",
                "heading" => __("Menu title font size", "vc_accordion_cq"),
                "param_name" => "accordiontitlesize2",
                "value" => "",
                "dependency" => Array('element' => "accordionstyle", 'value' => array('style2')),
                "description" => __("The font size of each accordion title. Default is 20px.", "vc_accordion_cq")
              ),
              array(
                "type" => "textfield",
                "heading" => __("Accordion content font size", "vc_accordion_cq"),
                "param_name" => "accordioncontentsize2",
                "value" => "",
                "dependency" => Array('element' => "accordionstyle", 'value' => array('style2')),
                "description" => __("The font size of each accordion content. Default is 1em.", "vc_accordion_cq")
              ),
              array(
                "type" => "textfield",
                "heading" => __("CSS padding of the accordion title", "vc_accordion_cq"),
                "param_name" => "titlepadding1",
                "value" => "",
                "dependency" => Array('element' => "accordionstyle", 'value' => array('style1')),
                "description" => __("The CSS padding of the accordion title. Default is 18px 0, which stand for padding-top and padding-bottom is 18px.", "vc_accordion_cq")
              ),
              array(
                "type" => "textfield",
                "heading" => __("CSS padding of the accordion title", "vc_accordion_cq"),
                "param_name" => "titlepadding2",
                "value" => "",
                "dependency" => Array('element' => "accordionstyle", 'value' => array('style2')),
                "description" => __("The CSS padding of the accordion title. Default is 1em.", "vc_accordion_cq")
              ),
              array(
                "type" => "attach_image",
                "heading" => __("Optional repeat pattern for the accordion menu", "vc_accordion_cq"),
                "param_name" => "pattern",
                "value" => "",
                "dependency" => Array('element' => "accordionstyle", 'value' => array('style2')),
                "description" => __("Select image pattern from media library.", "vc_accordion_cq")
              ),
              array(
                "type" => "colorpicker",
                "holder" => "div",
                "class" => "",
                "heading" => __("Menu title color", 'vc_accordion_cq'),
                "param_name" => "titlecolor",
                "value" => '',
                "description" => __("The color of each accordion title.", 'vc_accordion_cq')
              ),
              array(
                "type" => "colorpicker",
                "holder" => "div",
                "class" => "",
                "heading" => __("Menu title background color", 'vc_accordion_cq'),
                "param_name" => "titlebg",
                "dependency" => Array('element' => "accordionstyle", 'value' => array('style2')),
                "value" => '',
                "description" => __("", 'vc_accordion_cq')
              ),
              array(
                "type" => "colorpicker",
                "holder" => "div",
                "class" => "",
                "heading" => __("Menu title hover font color", 'vc_accordion_cq'),
                "param_name" => "titlehovercolor",
                "dependency" => Array('element' => "accordionstyle", 'value' => array('style2')),
                "value" => '#fff',
                "description" => __("", 'vc_accordion_cq')
              ),
              array(
                "type" => "colorpicker",
                "holder" => "div",
                "class" => "",
                "heading" => __("Menu title background hover color", 'vc_accordion_cq'),
                "param_name" => "titlehoverbg",
                "dependency" => Array('element' => "accordionstyle", 'value' => array('style2')),
                "value" => '#00ACED',
                "description" => __("", 'vc_accordion_cq')
              ),
              array(
                "type" => "dropdown",
                "holder" => "",
                "class" => "vc_accordion_cq",
                "heading" => __("Border under each accordion menu?", "vc_accordion_cq"),
                "param_name" => "withborder",
                "value" => array(__("no", "vc_accordion_cq") => "", __("yes", "vc_accordion_cq") => "withBorder"),
                "dependency" => Array('element' => "accordionstyle", 'value' => array('style2')),
                "description" => __("", "vc_accordion_cq")
              ),
              array(
                "type" => "colorpicker",
                "holder" => "div",
                "class" => "",
                "heading" => __("Color of border under each accordion menu", 'vc_accordion_cq'),
                "param_name" => "withbordercolor",
                "dependency" => Array('element' => "withborder", 'value' => array('withBorder')),
                "value" => '',
                "description" => __("", 'vc_accordion_cq')
              ),
              array(
                "type" => "dropdown",
                "holder" => "",
                "class" => "vc_accordion_cq",
                "heading" => __("Display extra border under whole accordion?", "vc_accordion_cq"),
                "param_name" => "extraborder",
                "value" => array(__("no", "vc_accordion_cq") => "no", __("yes", "vc_accordion_cq") => "yes"),
                "dependency" => Array('element' => "accordionstyle", 'value' => array('style2')),
                "description" => __("", "vc_accordion_cq")
              ),
              array(
                "type" => "colorpicker",
                "holder" => "div",
                "class" => "",
                "heading" => __("Extra border color", 'vc_accordion_cq'),
                "param_name" => "extrabordercolor",
                "dependency" => Array('element' => "extraborder", 'value' => array('yes')),
                "value" => '',
                "description" => __("", 'vc_accordion_cq')
              ),
              array(
                "type" => "dropdown",
                "holder" => "",
                "class" => "vc_accordion_cq",
                "heading" => __("Select arrow color", "vc_accordion_cq"),
                "param_name" => "arrowcolor",
                "value" => array(__("Default", "vc_accordion_cq") => "", __("red", "vc_accordion_cq") => "red", __("green", "vc_accordion_cq") => "green", __("yellow", "vc_accordion_cq") => "yellow", __("blue", "vc_accordion_cq") => "blue", __("orange", "vc_accordion_cq") => "orange", __("purple", "vc_accordion_cq") => "purple"),
                "dependency" => Array('element' => "accordionstyle", 'value' => array('style1')),
                "description" => __("You can select the arrow color here, default is gray.", "vc_accordion_cq")
              ),
              array(
                "type" => "textfield",
                "heading" => __("Display how many words form the content if you don't specify the title", "vc_accordion_cq"),
                "param_name" => "titlewords",
                "value" => "4",
                "description" => __("We will fetch the words from the content if you don't specify title for the accordion. Default will fetch 4 words.", "vc_accordion_cq")
              ),
              array(
                "type" => "textfield",
                "heading" => __("Container width", "vc_accordion_cq"),
                "param_name" => "contaienrwidth",
                "value" => "",
                "description" => __("The width of the whole contaienr, default is 100%. You can specify it with a smaller value, like 80%, and it will align center automatically.", "vc_accordion_cq")
              ),
              array(
                "type" => "checkbox",
                "holder" => "",
                "class" => "vc_accordion_cq",
                "heading" => __("Display first accordion by default?", 'vc_accordion_cq'),
                "param_name" => "displayfirst",
                "value" => array(__("Yes, display first accordion", "vc_accordion_cq") => 'on'),
                "description" => __("", 'vc_accordion_cq')
              ),
              array(
                "type" => "textfield",
                "heading" => __("Extra class name for the container", "vc_accordion_cq"),
                "param_name" => "extra_class",
                "description" => __("If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", "vc_accordion_cq")
              )

            )
        ));

        function cq_vc_accordion_func($atts, $content=null, $tag) {
          if(version_compare(WPB_VC_VERSION,  "4.6") >= 0){
              $atts = vc_map_get_attributes($tag,$atts);
              extract($atts);
          }else{
            extract( shortcode_atts( array(
              'accordionstyle' => 'style1',
              'title' => '',
              'titlebg' => '',
              'pattern' => '',
              'titlehoverbg' => '',
              'titlehovercolor' => '',
              'titlesize' => '',
              'accordiontitle' => '',
              'accordiontitlesize1' => '',
              'accordiontitlesize2' => '',
              'accordioncontentsize1' => '',
              'accordioncontentsize2' => '',
              'titlecolor' => '',
              'contentcolor' => '',
              'contentbg' => '',
              'arrowcolor' => '',
              'titlepadding1' => '',
              'titlepadding2' => '',
              'titlewords' => '4',
              'extraborder' => '',
              'withborder' => '',
              'withbordercolor' => '',
              'extrabordercolor' => '',
              'contaienrwidth' => '',
              'displayfirst' => '',
              'extra_class' => ''
            ), $atts ) );
          }


          wp_register_style( 'vc_accordion_cq_style', plugins_url('css/style.min.css', __FILE__));
          wp_enqueue_style( 'vc_accordion_cq_style' );

          wp_register_script('vc_accordion_cq_script', plugins_url('js/script.min.js', __FILE__), array("jquery"));
          wp_enqueue_script('vc_accordion_cq_script');


          $i = -1;
          $content = wpb_js_remove_wpautop($content); // fix unclosed/unwanted paragraph tags in $content

          $pattern = wp_get_attachment_image_src($pattern, 'full');
          $content = preg_replace( '#<p>\s*</p>#', '', $content );
          if(strpos($content, '[/accordionitem]')===false){
              $content = str_replace('</div>', '', trim($content));
              $contentarr = explode('<div class="accordion-content">', trim($content));
          }else{
              $content = str_replace('[/accordionitem]', '', trim($content));
              $contentarr = explode('[accordionitem]', trim($content));
          }

          array_shift($contentarr);
          $accordiontitle = explode(',', $accordiontitle);
          $output = '';
          $container_start = $container_content = $container_end = '';
          if($accordionstyle=="style1"){
              $container_start = '<div class="cq-accordion '.$extra_class.'" style="width:'.$contaienrwidth.';" data-displayfirst="'.$displayfirst.'">';
              if($title!=""){
                $container_start .= '<h3 style="color:'.$titlecolor.';font-size:'.$titlesize.';">';
                $container_start .= $title;
                $container_start .= '</h3>';
              }
              $container_start .= '<ul>';

              $container_end .= '</ul>';
              $container_end .= '</div>';
          }else{
              $container_start .= '<div class="cq-accordion2 '.$extra_class.'" style="width:'.$contaienrwidth.';" data-titlecolor="'.$titlecolor.'" data-titlebg="'.$titlebg.'" data-titlehoverbg="'.$titlehoverbg.'" data-titlehovercolor="'.$titlehovercolor.'" data-displayfirst="'.$displayfirst.'">';
              $container_start .= '<dl>';
              $container_end .= '</dl>';
              if($extraborder=="yes"){
                  $container_end .= '<div class="extraborder" style="background-color:'.$extrabordercolor.';"></div>';
              }
              $container_end .= '</div>';
          }
          foreach ($contentarr as $key => $thecontent) {
              $i++;
              $thetitle = '';
              if(!isset($accordiontitle[$i])||$accordiontitle[$i]==""){
                  $thetitle = preg_replace("/<img[^>]+\>/i", "", trim($thecontent));
                  $thetitle = strip_tags(implode(' ', array_slice(explode(' ', $thetitle), 0, $titlewords)));
              }else{
                 $thetitle = $accordiontitle[$i];
              }
              if($accordionstyle=="style1"){
                  $container_content .= '<li>';
                  $container_content .= '<input type="checkbox" checked>';
                  $container_content .= '<i class="'.$arrowcolor.'"></i>';
                  $container_content .= '<h4 style="color:'.$titlecolor.';font-size:'.$accordiontitlesize1.';padding:'.$titlepadding1.';">'.$thetitle.'</h4>';
                  $container_content .= '<div class="accordion-content" style="background-color:'.$contentbg.';color:'.$contentcolor.';font-size:'.$accordioncontentsize1.';">';
                  $container_content .= do_shortcode($thecontent);
                  $container_content .= '</div>';
                  $container_content .= '</li>';
              }else{
                  $container_content .= '<dt>';
                  if(!isset($pattern[0])) $pattern[0] = '';
                  $container_content .= '<a class="accordionTitle '.$withborder.'" style="background-color:'.$titlebg.';background-image:url('.$pattern[0].');padding:'.$titlepadding2.';color:'.$titlecolor.';font-size:'.$accordiontitlesize2.';border-color:'.$withbordercolor.';" href="#">';
                  $container_content .= '<i class="accordion-icon">+</i>';
                  $container_content .= $thetitle;
                  $container_content .= '</a>';

                  $container_content .= '</dt>';
                  $container_content .= '<dd class="accordionItem accordionItemCollapsed">';
                  $container_content .= '<div class="accordion-content" style="background-color:'.$contentbg.';color:'.$contentcolor.';font-size:'.$accordioncontentsize2.';">';
                  $container_content .= do_shortcode($thecontent);
                  $container_content .= '</div>';
                  $container_content .= '</dd>';

              }

          }
          $output .= $container_start.$container_content.$container_end;

          return $output;

        }

        add_shortcode('cq_vc_accordion', 'cq_vc_accordion_func');

      }
  }


}

?>
