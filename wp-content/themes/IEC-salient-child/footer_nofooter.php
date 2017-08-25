<?php 

$options = get_option('salient'); 
global $post;
$cta_link = ( !empty($options['cta-btn-link']) ) ? $options['cta-btn-link'] : '#';
$using_footer_widget_area = (!empty($options['enable-main-footer-area']) && $options['enable-main-footer-area'] == 1) ? 'true' : 'false';
$disable_footer_copyright = (!empty($options['disable-copyright-footer-area']) && $options['disable-copyright-footer-area'] == 1) ? 'true' : 'false';
$footer_reveal = (!empty($options['footer-reveal'])) ? $options['footer-reveal'] : 'false'; 
$midnight_non_reveal = ($footer_reveal != 'false') ? null : 'data-midnight="light"';

  
$exclude_pages = (!empty($options['exclude_cta_pages'])) ? $options['exclude_cta_pages'] : array(); 

?>



<?php 

$mobile_fixed = (!empty($options['header-mobile-fixed'])) ? $options['header-mobile-fixed'] : 'false';
$has_main_menu = (has_nav_menu('top_nav')) ? 'true' : 'false';

$sideWidgetArea = (!empty($options['header-slide-out-widget-area'])) ? $options['header-slide-out-widget-area'] : 'off';
$userSetSideWidgetArea = $sideWidgetArea;
if($has_main_menu == 'true' && $mobile_fixed == '1') $sideWidgetArea = '1';

$fullWidthHeader = (!empty($options['header-fullwidth']) && $options['header-fullwidth'] == '1') ? true : false;
$sideWidgetClass = (!empty($options['header-slide-out-widget-area-style'])) ? $options['header-slide-out-widget-area-style'] : 'slide-out-from-right';
$sideWidgetOverlayOpacity = (!empty($options['header-slide-out-widget-area-overlay-opacity'])) ? $options['header-slide-out-widget-area-overlay-opacity'] : 'dark';
$prependTopNavMobile = (!empty($options['header-slide-out-widget-area-top-nav-in-mobile'])) ? $options['header-slide-out-widget-area-top-nav-in-mobile'] : 'false';

?>



<?php if(!empty($options['boxed_layout']) && $options['boxed_layout'] == '1') { echo '</div>'; } ?>


<?php wp_footer(); ?> 

<script type="text/javascript">// _satellite.pageBottom();
if (typeof _satellite !== 'undefined'){ _satellite.pageBottom();}
</script>
<script>
jQuery(document).ready(function(){
  if (jQuery(".simple-banner-text").length){
    var bannerHeight2 = document.getElementById('simple-banner').offsetHeight;
    var bannerHeightPX2 =  '' + bannerHeight2 +'px';
    document.getElementById('header-secondary-outer').style.marginTop = bannerHeightPX2;
    document.getElementById('header-outer').style.marginTop = bannerHeightPX2;
    document.getElementById('ajax-content-wrap').style.marginTop = bannerHeightPX2;
  }
});
</script>
</body>
</html>
