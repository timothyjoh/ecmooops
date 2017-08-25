<?php get_header(); ?>

<?php nectar_page_header($post->ID); ?>

<div class="container-wrap">
	
	<div class="container main-content">
		
		<div class="row">
			
			<?php 

			//breadcrumbs
			if ( function_exists( 'yoast_breadcrumb' ) && !is_home() && !is_front_page() ){ yoast_breadcrumb('<p id="breadcrumbs">','</p>'); } 

			 //buddypress
			 global $bp; 
			 if($bp && !bp_is_blog_page()) echo '<h1>' . get_the_title() . '</h1>'; ?>
			
			<?php if(have_posts()) : while(have_posts()) : the_post(); ?>
				
				<?php the_content(); ?>
	
			<?php endwhile; endif; ?>
				
	
		</div><!--/row-->
		
	</div><!--/container-->
	
</div>
<?php get_footer(); ?>