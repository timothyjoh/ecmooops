<?php
class WpProQuiz_Controller_Preview extends WpProQuiz_Controller_Controller {
	
	public function route() {
		global $learndash_assets_loaded;
		
		wp_enqueue_script(
			'wpProQuiz_front_javascript', 
			plugins_url('js/wpProQuiz_front'. ( ( defined( 'LEARNDASH_SCRIPT_DEBUG' ) && LEARNDASH_SCRIPT_DEBUG ) ? '' : '.min') .'.js', WPPROQUIZ_FILE),
			array('jquery', 'jquery-ui-sortable'),
			LEARNDASH_VERSION
		);
		$learndash_assets_loaded['scripts']['wpProQuiz_front_javascript'] = __FUNCTION__;
		
		wp_localize_script('wpProQuiz_front_javascript', 'WpProQuizGlobal', array(
			'ajaxurl' => str_replace(array("http:", "https:"), array("",""), admin_url('admin-ajax.php')),
			'loadData' => __('Loading', 'wp-pro-quiz'),
			'questionNotSolved' => __('You must answer this question.', 'wp-pro-quiz'),
			'questionsNotSolved' => sprintf( _x('You must answer all questions before you can complete the %s.', 'You must answer all questions before you can complete the quiz.', 'wp-pro-quiz'), LearnDash_Custom_Label::label_to_lower( 'quiz' ) ),
			'fieldsNotFilled' => __('All fields have to be filled.', 'wp-pro-quiz')
		));
		
		wp_enqueue_style(
			'wpProQuiz_front_style', 
			plugins_url('css/wpProQuiz_front'. ( ( defined( 'LEARNDASH_SCRIPT_DEBUG' ) && ( LEARNDASH_SCRIPT_DEBUG === true ) ) ? '' : '.min') .'.css', WPPROQUIZ_FILE),
			array(),
			LEARNDASH_VERSION
		);
		$learndash_assets_loaded['styles']['wpProQuiz_front_style'] = __FUNCTION__;
		
		$this->showAction($_GET['id']);
	}
	
	public function showAction($id) {
		$view = new WpProQuiz_View_FrontQuiz();
		
		$quizMapper = new WpProQuiz_Model_QuizMapper();
		$questionMapper = new WpProQuiz_Model_QuestionMapper();
		$categoryMapper = new WpProQuiz_Model_CategoryMapper();
		$formMapper = new WpProQuiz_Model_FormMapper();
		
		$quiz = $quizMapper->fetch($id);
		
		if($quiz->isShowMaxQuestion() && $quiz->getShowMaxQuestionValue() > 0) {
				
			$value = $quiz->getShowMaxQuestionValue();
				
			if($quiz->isShowMaxQuestionPercent()) {
				$count = $questionMapper->count($id);
		
				$value = ceil($count * $value / 100);
			}
				
			$question = $questionMapper->fetchAll($id, true, $value);
				
		} else {
			$question = $questionMapper->fetchAll($id);
		}
		
		$view->quiz = $quiz;
		$view->question = $question;
		$view->category = $categoryMapper->fetchByQuiz($quiz->getId());
		$view->forms = $formMapper->fetch($quiz->getId());
		
		$view->show(true);
	}
}