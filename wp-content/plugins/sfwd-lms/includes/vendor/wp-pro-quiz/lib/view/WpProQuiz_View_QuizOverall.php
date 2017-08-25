<?php

class WpProQuiz_View_QuizOverall extends WpProQuiz_View_View {
	
	public function show() {
?>
<style>
.wpProQuiz_exportList ul {
	list-style: none;
	margin: 0;
	padding: 0;
}
.wpProQuiz_exportList li {
	float: left;
	padding: 3px;
	border: 1px solid #B3B3B3;
	margin-right: 5px;
	background-color: #F3F3F3;
}
.wpProQuiz_exportList, .wpProQuiz_importList {
	padding: 20px; 
	background-color: rgb(223, 238, 255); 
	border: 1px dotted;
	margin-top: 10px;
	display: none;
}
.wpProQuiz_exportCheck {
	display: none;
}
</style>
<div class="wrap wpProQuiz_quizOverall" style="position: relative;">
	<h2><?php _e('Import/Export Associated Settings', 'learndash'); ?></h2>
	<div class="updated" style="display: none;">
		<h3><?php _e('In case of problems', 'wp-pro-quiz'); ?></h3>
		<p>
			<?php echo sprintf( _x('If %s doesn\'t work in front-end, please try following:', 'placeholders: quiz', 'wp-pro-quiz'), LearnDash_Custom_Label::label_to_lower( 'quiz' ) ); ?>
		</p>
		<p>
			[raw][LDAdvQuiz X][/raw]
		</p>
		<p>
			<?php _e('Own themes changes internal  order of filters, what causes the problems. With additional shortcode [raw] this is prevented.', 'wp-pro-quiz'); ?>
		</p>
	</div>
	<div style="margin: 8px 0px;">
		<a class="button-primary" style="font-weight: bold; display: none;" href="admin.php?page=ldAdvQuiz&module=styleManager"><?php _e('Style Manager', 'wp-pro-quiz'); ?></a>
	</div>
	
	<table class="wp-list-table widefat">
		<thead>
			<tr>
				<th scope="col" width="30px" class="wpProQuiz_exportCheck"><input type="checkbox" name="exportItemsAll" value="0"></th>
				<th scope="col" width="40px"><?php _e('ID', 'wp-pro-quiz'); ?></th>
				<th scope="col"><?php _e('Name', 'wp-pro-quiz'); ?></th>
				<th scope="col" width="180px"><?php _e('Shortcode', 'wp-pro-quiz'); ?></th>
				<th scope="col" width="180px"><?php _e('Shortcode-Leaderboard', 'wp-pro-quiz'); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php 
			if(count($this->quiz)) {
			foreach ($this->quiz as $quiz) {
			?>
			<tr>
				<th class="wpProQuiz_exportCheck"><input type="checkbox" name="exportItems" value="<?php echo $quiz->getId(); ?>"></th>
				<td><?php echo $quiz->getId(); ?></td>
				<td class="wpProQuiz_quizName">
					<strong><?php echo $quiz->getName(); ?></strong>
					<div class="row-actions">
						<span>
							<a href="admin.php?page=ldAdvQuiz&module=question&quiz_id=<?php echo $quiz->getId(); ?>&post_id=<?php echo @$_GET['post_id']; ?>"><?php _e('Questions', 'wp-pro-quiz'); ?></a> | 
						</span>
						
						<?php if(0 && current_user_can('wpProQuiz_edit_quiz')) { ?>
						<span>
							<a href="admin.php?page=ldAdvQuiz&action=addEdit&quizId=<?php echo $quiz->getId(); ?>&post_id=<?php echo @$_GET['post_id']; ?>"><?php _e('Edit', 'wp-pro-quiz'); ?></a> | 
						</span> 
						<?php } if(current_user_can('wpProQuiz_delete_quiz')) { ?>
						<span>
							<a style="color: red;" class="wpProQuiz_delete" href="admin.php?page=ldAdvQuiz&action=delete&id=<?php echo $quiz->getId(); ?>&post_id=<?php echo @$_GET['post_id']; ?>"><?php _e('Delete', 'wp-pro-quiz'); ?></a> | 
						</span>
						<?php } ?>
						<span>
							<a class="wpProQuiz_prview" href="admin.php?page=ldAdvQuiz&module=preview&id=<?php echo $quiz->getId(); ?>&post_id=<?php echo @$_GET['post_id']; ?>"><?php _e('Preview', 'wp-pro-quiz'); ?></a> | 
						</span>
						<?php if(current_user_can('wpProQuiz_show_statistics')) { ?>
						<span>
							<a href="admin.php?page=ldAdvQuiz&module=statistics&id=<?php echo $quiz->getId(); ?>&post_id=<?php echo @$_GET['post_id']; ?>"><?php _e('Statistics', 'wp-pro-quiz'); ?></a> | 
						</span>
						<?php } if(current_user_can('wpProQuiz_toplist_edit')) { ?>
						<span>
							<a href="admin.php?page=ldAdvQuiz&module=toplist&id=<?php echo $quiz->getId(); ?>&post_id=<?php echo @$_GET['post_id']; ?>"><?php _e('Leaderboard', 'wp-pro-quiz'); ?></a>
						</span>
						<?php } ?>
					</div>
				</td>
				<td>[LDAdvQuiz <?php echo $quiz->getId(); ?>]</td>
				<td>
					<?php if($quiz->isToplistActivated()) { ?>
						[LDAdvQuiz_toplist <?php echo $quiz->getId(); ?>]
					<?php } ?>
				
				</td>
			</tr>
			<?php } } else { ?>
			<tr>
				<td colspan="5" style="text-align: center; font-weight: bold; padding: 10px;"><?php _e('No data available', 'wp-pro-quiz'); ?></td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
	<p>
		<?php  if(current_user_can('wpProQuiz_import')) { ?>
		<a class="button-secondary wpProQuiz_import" href="#"><?php echo __('Import', 'wp-pro-quiz'); ?></a>
		<?php } if(current_user_can('wpProQuiz_export') && count($this->quiz)) { ?>
		<a class="button-secondary wpProQuiz_export" href="#"><?php echo __('Export', 'wp-pro-quiz'); ?></a>
		<?php } ?>
	</p>
	<div class="wpProQuiz_exportList">
		<form action="admin.php?page=ldAdvQuiz&module=importExport&action=export&noheader=true" method="POST">
			<h3 style="margin-top: 0;"><?php _e('Export', 'wp-pro-quiz'); ?></h3>
			<p><?php echo __('Choose the respective question, which you would like to export and press on "Start export"', 'wp-pro-quiz'); ?></p>
			<ul></ul>
			<div style="clear: both; margin-bottom: 10px;"></div>
			<div id="exportHidden"></div>
			<div style="margin-bottom: 15px;">
				<?php _e('Format:'); ?> 
				<label><input type="radio" name="exportType" value="wpq" checked="checked"> <?php _e('*.wpq'); ?></label>
				<?php _e('or'); ?> 
				<label><input type="radio" name="exportType" value="xml"> <?php _e('*.xml'); ?></label>
			</div>
			<input class="button-primary" name="exportStart" id="exportStart" value="<?php echo __('Start export', 'wp-pro-quiz'); ?>" type="submit">
		</form>
	</div>
	<div class="wpProQuiz_importList">
		<form action="admin.php?page=ldAdvQuiz&module=importExport&action=import" method="POST" enctype="multipart/form-data">
			<h3 style="margin-top: 0;"><?php _e('Import', 'wp-pro-quiz'); ?></h3>
			<p><?php _e('Import only *.wpq or *.xml files from known and trusted sources.', 'wp-pro-quiz'); ?></p>
			<div style="margin-bottom: 10px">
			<?php 
				$maxUpload = (int)(ini_get('upload_max_filesize'));
				$maxPost = (int)(ini_get('post_max_size'));
				$memoryLimit = (int)(ini_get('memory_limit'));
				$uploadMB = min($maxUpload, $maxPost, $memoryLimit);
			?>
				<input type="file" name="import" accept=".wpq,.xml" required="required"> <?php printf(__('Maximal %d MiB', 'wp-pro-quiz'), $uploadMB); ?>
			</div>
			<input class="button-primary" name="exportStart" id="exportStart" value="<?php _e('Start import', 'wp-pro-quiz'); ?>" type="submit">
		</form>
	</div>
</div>
		
		<?php 
	}
}