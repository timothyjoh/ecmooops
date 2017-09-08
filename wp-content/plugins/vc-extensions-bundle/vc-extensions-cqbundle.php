<?php
/*
Plugin Name: Visual Composer Extensions All In One
Description: Add 40+ new elements to Visual Composer, includes: Draggable Timeline, Metro Carousel and Tile, Zooma or Magnify, Carousel & Gallery, Tabs, Accordion, Image Hotspot with Tooltip, Parallax, Medium Gallery, Stack Gallery, Testimonial Carousel, iHover, Scrolling Notification and Masonry Gallery etc.
Author: Sike
Version: 3.4.1
Author URI: http://codecanyon.net/user/sike?ref=sike
*/

require_once( 'faanimation/vc-extensions-faanimation.php' );
require_once( 'dagallery/vc-extensions-dagallery.php' );
require_once( 'appmockup/vc-extensions-appmockup.php' );
require_once( 'depthmodal/vc-extensions-depthmodal.php' );
require_once( 'ihover/vc-extensions-ihover.php' );
require_once( 'profilecard/vc-extensions-profilecard.php' );
require_once( 'testimonialcarousel/vc-extensions-testimonialcarousel.php' );
require_once( 'stackgallery/vc-extensions-stackgallery.php' );
// require_once( 'animatetext/vc-extensions-animatetext.php' );
require_once( 'figurenav/vc-extensions-figurenav.php' );
// require_once( 'timeline/vc-extensions-timeline.php' );
require_once( 'ribbon/vc-extensions-ribbon.php' );
require_once( 'mediumgallery/vc-extensions-mediumgallery.php' );
require_once( 'productcover/vc-extensions-productcover.php' );
require_once( 'imagewitharrow/vc-extensions-imagewitharrow.php' );
require_once( 'parallax/vc-extensions-parallax.php' );
require_once( 'buttons/vc-extensions-cqbutton.php' );
require_once( 'hotspot/vc-extensions-hotspot.php' );
require_once( 'todolist/vc-extensions-todolist.php' );
require_once( 'accordion/vc-extensions-accordion.php' );
require_once( 'tabs/vc-extensions-tabs.php' );
require_once( 'carousel/vc-extensions-carousel.php' );
require_once( 'zoomimage/vc-extensions-zoomimage.php' );
require_once( 'metrocarousel/vc-extensions-metrocarousel.php' );
require_once( 'draggabletimeline/vc-extensions-draggabletimeline.php' );
require_once( 'thumbnailcaption/vc-extensions-thumbnailcaption.php' );
require_once( 'fullscreenintro/vc-extensions-fullscreenintro.php' );
require_once( 'pagetransition/vc-extensions-pagetransition.php' );
require_once( 'separator/vc-extensions-separator.php' );
require_once( 'materialcard/vc-extensions-materialcard.php' );
require_once( 'cubebox/vc-extensions-cubebox.php' );
require_once( 'sidebyside/vc-extensions-sidebyside.php' );
require_once( 'typewriter/vc-extensions-typewriter.php' );
require_once( 'sticker/vc-extensions-sticker.php' );
require_once( 'imageoverlay/vc-extensions-imageoverlay.php' );
require_once( 'flipbox/vc-extensions-flipbox.php' );
require_once( 'bannerblock/vc-extensions-bannerblock.php' );
require_once( 'beforeafter/vc-extensions-beforeafter.php' );
require_once( 'compareslider/vc-extensions-compareslider.php' );
require_once( 'imageoverlay2/vc-extensions-imageoverlay2.php' );
require_once( 'imageaccordion/vc-extensions-imageaccordion.php' );
require_once( 'profilepanel/vc-extensions-profilepanel.php' );
require_once( 'videocover/vc-extensions-videocover.php' );
require_once( 'stackblock/vc-extensions-stackblock.php' );
require_once( 'gradientbox/vc-extensions-gradientbox.php' );
require_once( 'vectorcard/vc-extensions-vectorcard.php' );
require_once( 'avatarwithpopup/vc-extensions-avatarwithpopup.php' );

if (!class_exists('VC_Extensions_CQBundle')) {
    class VC_Extensions_CQBundle {
        function VC_Extensions_CQBundle() {
            // function vc_extensions_cqbundle_map_fucn(){
              if(!function_exists('cq_vc_animationfw_func')){
                  $vc_extensions_faanimation = new VC_Extensions_FAanimation();
              }
              if(!function_exists('cq_vc_dagallery_func')) $vc_extensions_dagallery = new VC_Extensions_DAGallery();
              if(!function_exists('cq_vc_appmockup_func')) $vc_extensions_appmockup = new VC_Extensions_AppMockup();
              if(!function_exists('cq_vc_depthmodal_func'))$vc_extensions_depthmodal = new VC_Extensions_DepthModal();
              if(!function_exists('cq_vc_ihover_func')) $vc_extensions_ihover = new VC_Extensions_iHover();
              if(!function_exists('cq_vc_profilecard_func')) $vc_extensions_profilecard = new VC_Extensions_ProfileCard();
              if(!function_exists('cq_vc_testimonialcarousel_func')) $vc_extensions_testimonialcarousel = new VC_Extensions_TestimonialCarousel();
              if(!function_exists('cq_vc_stackgallery_func')) $vc_extensions_stackgallery = new VC_Extensions_StackGallery();
              // if(!function_exists('cq_vc_animatetext_func')) $vc_extensions_animatetext = new VC_Extensions_AnimateText();
              if(!function_exists('cq_vc_figurenav_func')) $vc_extensions_figurenav = new VC_Extensions_FigureNav();
              // if(!function_exists('cq_vc_timeline_func')) $vc_extensions_timeline = new VC_Extensions_Timeline();
              if(!function_exists('cq_vc_ribbon_func')) $vc_extensions_ribbon = new VC_Extensions_Ribbon();
              if(!function_exists('cq_vc_mediumgallery_func')) $vc_extensions_mediumgallery = new VC_Extensions_MediumGallery();
              if(!function_exists('cq_vc_productcover_func')) $vc_extensions_productcover = new VC_Extensions_ProductCover();
              if(!function_exists('cq_vc_imagewitharrow_func')) $vc_extensions_imagewitharrow = new VC_Extensions_ImageWithArrow();
              if(!function_exists('cq_vc_parallax_func')) $vc_extensions_parallax = new VC_Extensions_Parallax();
              if(!function_exists('cq_vc_cqbutton_func')) $vc_extensions_cqbutton = new VC_Extensions_CQButton();
              if(!function_exists('cq_vc_hotspot_func')) $vc_extensions_hotspot = new VC_Extensions_HotSpot();
              if(!function_exists('cq_vc_todolist_func')) $vc_extensions_todolist = new VC_Extensions_ToDoList();
              if(!function_exists('cq_vc_accordion_func')) $vc_extensions_accordion = new VC_Extensions_Accordion();
              if(!function_exists('cq_vc_tabs_func')) $vc_extensions_tabs = new VC_Extensions_Tabs();
              if(!function_exists('cq_vc_cqcarousel_func')) $vc_extensions_cqcarousel = new VC_Extensions_CQCarousel();
              if(!function_exists('cq_vc_zoomimage_func')) $vc_extensions_zoomimage = new VC_Extensions_ZoomImage();
              if(!function_exists('cq_vc_metrocarousel_func')) $vc_extensions_metrocarousel = new VC_Extensions_MetroCarousel();
              if(!function_exists('cq_vc_draggabletimeline_func')) $vc_extensions_draggabletimeline = new VC_Extensions_DraggableTimeline();
              if(!function_exists('cq_vc_thumbnailcaption_func')) $vc_extensions_thumbnailcaption = new VC_Extensions_CQThumbnailCaption();
              if(!function_exists('cq_vc_fullscreenintro_func')) $vc_extensions_fullscreenintro = new VC_Extensions_FullscreenIntro();
              if(!function_exists('cq_vc_pagetransition_func')) $vc_extensions_pagetransition = new VC_Extensions_PageTransition();
              if(!function_exists('cq_vc_separator_func')) $vc_extensions_separator = new VC_Extensions_Separator();
              if(!function_exists('cq_vc_materialcard_func')) $vc_extensions_materialcard = new VC_Extensions_MaterialCard();
              if(!function_exists('cq_vc_cubebox_func')) $vc_extensions_cubebox = new VC_Extensions_CubeBox();
              if(!function_exists('cq_vc_sidebyside_func')) $vc_extensions_sidebyside = new VC_Extensions_SideBySide();
              if(!function_exists('cq_vc_typewriter_func')) $vc_extensions_typewriter = new VC_Extensions_TypeWriter();
              if(!function_exists('cq_vc_sticker_func')) $vc_extensions_sticker = new VC_Extensions_Sticker();
              if(!function_exists('cq_vc_imageoverlay_func')) $vc_extensions_imageoverlay = new VC_Extensions_ImageOverlay();
              if(!function_exists('cq_vc_flipbox_func')) $vc_extensions_flipbox = new VC_Extensions_FlipBox();
              if(!function_exists('cq_vc_bannerblock_func')) $vc_extensions_bannerblock = new VC_Extensions_BannerBlock();
              if(!function_exists('cq_vc_beforeafter_func')) $vc_extensions_beforeafter = new VC_Extensions_BeforeAfter();
              if(!function_exists('cq_vc_compareslider_func')) $vc_extensions_compareslider = new VC_Extensions_CompareSlider();
              if(!function_exists('cq_vc_imageoverlay2_func')) $vc_extensions_imageoverlay2 = new VC_Extensions_ImageOverlay2();
              if(!function_exists('cq_vc_imageaccordion_func')) $vc_extensions_imageaccordion = new VC_Extensions_ImageAccordion();
              if(!function_exists('cq_vc_profilepanel_func')) $vc_extensions_profilepanel = new VC_Extensions_ProfilePanel();
              if(!function_exists('cq_vc_videocover_func')) $vc_extensions_videocover = new VC_Extensions_VideoCover();
              if(!function_exists('cq_vc_stackblock_func')) $vc_extensions_stackblock = new VC_Extensions_StackBlock();
              if(!function_exists('cq_vc_gradientbox_func')) $vc_extensions_gradientbox = new VC_Extensions_GradientBox();
              if(!function_exists('cq_vc_vectorcard_func')) $vc_extensions_vectorcard = new VC_Extensions_VectorCard();
              if(!function_exists('cq_vc_avatarwithpopup_func')) $vc_extensions_avatarwithpopup = new VC_Extensions_AvatarWithPopup();
            // }

            // if(version_compare(WPB_VC_VERSION,  "4.2") >= 0) {
            //   add_action('init', 'vc_extensions_cqbundle_map_fucn');
            // }else{
            //   vc_extensions_cqbundle_map_fucn();
            // }

        }
  }

  function vc_addons_cq_notice(){
    $plugin_data = get_plugin_data(__FILE__);
    echo '
    <div class="updated">
      <p>'.sprintf(__('<strong>%s</strong> requires <strong><a href="http://codecanyon.net/item/visual-composer-page-builder-for-wordpress/242431?ref=sike" target="_blank">Visual Composer</a></strong> plugin to be installed and activated on your site.', 'vc_addons_cq'), $plugin_data['Name']).'</p>
    </div>';
  }
  if (!defined('ABSPATH')) die('-1');


  function vc_extensions_cqbundle_init(){
    if (!defined('WPB_VC_VERSION')) {add_action('admin_notices', 'vc_addons_cq_notice'); return;}
    if(!function_exists('aq_resize')) require_once('aq_resizer.php');
    wp_register_style( 'vc_extensions_cqbundle_adminicon', plugins_url('css/admin_icon.min.css', __FILE__) );
    wp_enqueue_style( 'vc_extensions_cqbundle_adminicon' );
    if(class_exists('VC_Extensions_CQBundle')) $vc_extensions_cqbundle = new VC_Extensions_CQBundle();
  }

  add_action('init', 'vc_extensions_cqbundle_init');


}

?>
