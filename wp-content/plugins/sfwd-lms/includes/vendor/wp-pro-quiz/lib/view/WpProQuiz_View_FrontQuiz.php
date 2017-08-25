<?php

class WpProQuiz_View_FrontQuiz extends WpProQuiz_View_View {

	/**
	 * @var WpProQuiz_Model_Quiz
	 */
	public $quiz;

	private $_clozeTemp = array();
	private $_assessmetTemp = array();

	private function getFreeCorrect( $data ) {
		
		$t = str_replace( "\r\n", "\n", strtolower( $data->getAnswer() ) );
		$t = str_replace( "\r", "\n", $t );
		$t = explode( "\n", $t );

		//return array_values( array_filter( array_map( 'trim', $t ) ) );
		// In the consice line above we can't use the array_filter() function as
		// this will remove answer line values that are considered empty. 
		// So for example if the answr value line is 0 (zero) then array_filter
		// will consider it as equal to false. 
		// So instead we loop over the array (the hard way) and check for values equal to '' and removed.
		$t = array_map( 'trim', $t );
		foreach( $t as $idx => $item ) {
			$item = trim($item);
			if ( $item == '' ) {
				unset( $t[$idx] );
			}
		}

		return array_values( $t );
	}

	public function show( $preview = false ) {

		$question_count = count( $this->question );

		$result = $this->quiz->getResultText();

		if ( ! $this->quiz->isResultGradeEnabled() ) {
			$result = array(
				'text'    => array( $result ),
				'prozent' => array( 0 )
			);
		}

		$resultsProzent = json_encode( $result['prozent'] );
		?>
		<div class="wpProQuiz_content" id="wpProQuiz_<?php echo $this->quiz->getId(); ?>">
			<div class="wpProQuiz_spinner" style="display:none">
				<div></div>
			</div>
			<?php

			if ( ! $this->quiz->isTitleHidden() ) {
				echo '<h2>', $this->quiz->getName(), '</h2>';
			}

			LD_QuizPro::showQuizContent( $this->quiz->getID() );
			$this->showTimeLimitBox();
			$this->showCheckPageBox( $question_count );
			$this->showInfoPageBox();
			$this->showStartQuizBox();
			$this->showUserQuizStatisticsBox();
			$this->showLockBox();
			$this->showLoadQuizBox();
			$this->showStartOnlyRegisteredUserBox();
			$this->showPrerequisiteBox();
			$this->showResultBox( $result, $question_count );

			if ( $this->quiz->getToplistDataShowIn() == WpProQuiz_Model_Quiz::QUIZ_TOPLIST_SHOW_IN_BUTTON ) {
				$this->showToplistInButtonBox();
			}

			$this->showReviewBox( $question_count );
			$this->showQuizAnker();

			$quizData = $this->showQuizBox( $question_count );

			?>
		</div>
		<?php
		if ( $preview ) {
			add_action( "admin_footer", array( $this, "script_preview" ) );
		} else {
			add_action( "wp_footer", array( $this, "script" ) );
		}

	}

	public function script_preview() {
		$this->script( true );
	}

	public function script( $preview = false ) {
		global $post;
		$question_count = count( $this->question );

		$result = $this->quiz->getResultText();

		if ( ! $this->quiz->isResultGradeEnabled() ) {
			$result = array(
				'text'    => array( $result ),
				'prozent' => array( 0 )
			);
		}

		$resultsProzent = json_encode( $result['prozent'] );

		ob_start();
		$quizData = $this->showQuizBox( $question_count );
		ob_get_clean();

		foreach ( $quizData['json'] as $key => $value ) {
			foreach ( array( "points", "correct" ) as $key2 ) {
				unset( $quizData['json'][ $key ][ $key2 ] );
			}
		}
		$user_id = get_current_user_id();
		$bo      = $this->createOption( $preview );
		if ( @$post->post_type != "sfwd-quiz" ) {
			$quiz_id      = $this->quiz->getId();
			$quiz_post_id = learndash_get_quiz_id_by_pro_quiz_id( $quiz_id );
		} else {
			$quiz_post_id = (empty($post->ID))? '0':$post->ID;

			$quiz_meta = get_post_meta( $quiz_post_id, '_sfwd-quiz', true );
		}

		if ((isset($quiz_meta['sfwd-quiz_passingpercentage'])) && (!empty($quiz_meta['sfwd-quiz_passingpercentage']))){
			$quiz_meta_sfwd_quiz_passingpercentage = floatval($quiz_meta['sfwd-quiz_passingpercentage']);
		} else {
			$quiz_meta_sfwd_quiz_passingpercentage = 0;
		}
		
		$ld_script_debug = 0;
		if (isset($_GET['LD_DEBUG'])) {
			$ld_script_debug = true;
		}
				
		echo " <script type='text/javascript'>
		function load_wpProQuizFront" . $this->quiz->getId() . "() {
			jQuery('#wpProQuiz_" . $this->quiz->getId() . "').wpProQuizFront({
				quiz: " . $quiz_post_id . ",
				quizId: " . (int) $this->quiz->getId() . ",
				mode: " . (int) $this->quiz->getQuizModus() . ",
				globalPoints: " . (int) $quizData['globalPoints'] . ",
				timelimit: " . (int) $this->quiz->getTimeLimit() . ",
				timelimitcookie: " . intval($this->quiz->getTimeLimitCookie()) . ",
				resultsGrade: " . $resultsProzent . ",
				bo: " . $bo . ",
				passingpercentage: ". $quiz_meta_sfwd_quiz_passingpercentage .",
				user_id: " . $user_id . ",
				qpp: " . $this->quiz->getQuestionsPerPage() . ",
				catPoints: " . json_encode( $quizData['catPoints'] ) . ",
				formPos: " . (int) $this->quiz->getFormShowPosition() . ",
				lbn: " . json_encode( ( $this->quiz->isShowReviewQuestion() && ! $this->quiz->isQuizSummaryHide() ) ? sprintf( _x( '%s-summary', 'Quiz-summary', 'wp-pro-quiz' ), LearnDash_Custom_Label::get_label( 'quiz' ) ) : sprintf( _x( 'Finish %s', 'Finish Quiz Button Label', 'wp-pro-quiz' ), LearnDash_Custom_Label::get_label( 'quiz' ) ) ) . ",
				json: " . json_encode( $quizData['json'] ) . ",
				ld_script_debug: ". $ld_script_debug ."
			});
		}
		var loaded_wpProQuizFront" . $this->quiz->getId() . " = 0;
		jQuery(document).ready(function($) {
			load_wpProQuizFront" . $this->quiz->getId() . "();
			loaded_wpProQuizFront" . $this->quiz->getId() . " = 1;
		});
		jQuery(window).load(function($) {
			if(loaded_wpProQuizFront" . $this->quiz->getId() . " == 0)
			load_wpProQuizFront" . $this->quiz->getId() . "();
		});
		</script> ";
	}

	public function max_question_script() {
		$question_count = count( $this->question );

		$result = $this->quiz->getResultText();

		if ( ! $this->quiz->isResultGradeEnabled() ) {
			$result = array(
				'text'    => array( $result ),
				'prozent' => array( 0 )
			);
		}

		$resultsProzent = json_encode( $result['prozent'] );
		$user_id        = get_current_user_id();
		$bo             = $this->createOption( false );
		global $post;
		if ( @$post->post_type != "sfwd-quiz" ) {
			$quiz_id      = $this->quiz->getId();
			$quiz_post_id = learndash_get_quiz_id_by_pro_quiz_id( $quiz_id );
		} else {
			$quiz_post_id = (empty($post->ID))? '0':$post->ID;

			$quiz_meta = get_post_meta( $quiz_post_id, '_sfwd-quiz', true );
		}
		
		if ((isset($quiz_meta['sfwd-quiz_passingpercentage'])) && (!empty($quiz_meta['sfwd-quiz_passingpercentage']))){
			$quiz_meta_sfwd_quiz_passingpercentage = intval($quiz_meta['sfwd-quiz_passingpercentage']);
		} else {
			$quiz_meta_sfwd_quiz_passingpercentage = 0;
		}

		// If the Quiz URL contains the query string parameter 'LD_DEBUG' to turn on debug output (console.log()) in the JS 
		$ld_script_debug = 0;
		if (isset($_GET['LD_DEBUG'])) {
			$ld_script_debug = true;
		}
		
		echo "<script type='text/javascript'>
		jQuery(document).ready(function($) {
			$('#wpProQuiz_" . $this->quiz->getId() . "').wpProQuizFront({
				quiz: " . $quiz_post_id . ",
				quizId: " . (int) $this->quiz->getId() . ",
				mode: " . (int) $this->quiz->getQuizModus() . ",
				timelimit: " . (int) $this->quiz->getTimeLimit() . ",
				timelimitcookie: " . intval($this->quiz->getTimeLimitCookie()) . ",
				resultsGrade: " . $resultsProzent . ",
				bo: " . $bo . ",
				passingpercentage: ". $quiz_meta_sfwd_quiz_passingpercentage .",
				user_id: " . $user_id . ",
				qpp: " . $this->quiz->getQuestionsPerPage() . ",
				formPos: " . (int) $this->quiz->getFormShowPosition() . ",
				ld_script_debug: ". $ld_script_debug .",
				lbn: " . json_encode( ( $this->quiz->isShowReviewQuestion() && ! $this->quiz->isQuizSummaryHide() ) ? sprintf( _x( '%s-summary', 'Quiz-summary', 'wp-pro-quiz' ), LearnDash_Custom_Label::get_label( 'quiz' ) ) : sprintf( _x( 'Finish %s', 'Finish Quiz Button Label', 'wp-pro-quiz' ), LearnDash_Custom_Label::get_label( 'quiz' ) ) ) . "
			});
		});
		</script>";
	}

	private function createOption( $preview ) {
		$bo = 0;

		$bo |= ( (int) $this->quiz->isAnswerRandom() ) << 0;
		$bo |= ( (int) $this->quiz->isQuestionRandom() ) << 1;
		$bo |= ( (int) $this->quiz->isDisabledAnswerMark() ) << 2;
		$bo |= ( (int) ( $this->quiz->isQuizRunOnce() || $this->quiz->isPrerequisite() || $this->quiz->isStartOnlyRegisteredUser() ) ) << 3;
		$bo |= ( (int) $preview ) << 4;
		$bo |= ( (int) get_option( 'wpProQuiz_corsActivated' ) ) << 5;
		$bo |= ( (int) $this->quiz->isToplistDataAddAutomatic() ) << 6;
		$bo |= ( (int) $this->quiz->isShowReviewQuestion() ) << 7;
		$bo |= ( (int) $this->quiz->isQuizSummaryHide() ) << 8;
		$bo |= ( (int) ( $this->quiz->isSkipQuestion() && $this->quiz->isShowReviewQuestion() ) ) << 9;
		$bo |= ( (int) $this->quiz->isAutostart() ) << 10;
		$bo |= ( (int) $this->quiz->isForcingQuestionSolve() ) << 11;
		$bo |= ( (int) $this->quiz->isHideQuestionPositionOverview() ) << 12;
		$bo |= ( (int) $this->quiz->isFormActivated() ) << 13;
		$bo |= ( (int) $this->quiz->isShowMaxQuestion() ) << 14;
		$bo |= ( (int) $this->quiz->isSortCategories() ) << 15;

		return $bo;
	}

	public function showMaxQuestion() {
		$question_count = count( $this->question );

		$result = $this->quiz->getResultText();

		if ( ! $this->quiz->isResultGradeEnabled() ) {
			$result = array(
				'text'    => array( $result ),
				'prozent' => array( 0 )
			);
		}

		$resultsProzent = json_encode( $result['prozent'] );

		?>
		<div class="wpProQuiz_content" id="wpProQuiz_<?php echo $this->quiz->getId(); ?>">
			<?php

			if ( ! $this->quiz->isTitleHidden() ) {
				echo '<h2>', $this->quiz->getName(), '</h2>';
			}

			LD_QuizPro::showQuizContent( $this->quiz->getID() );
			$this->showTimeLimitBox();
			$this->showCheckPageBox( $question_count );
			$this->showInfoPageBox();
			$this->showStartQuizBox();
			$this->showUserQuizStatisticsBox();
			$this->showLockBox();
			$this->showLoadQuizBox();
			$this->showStartOnlyRegisteredUserBox();
			$this->showPrerequisiteBox();
			$this->showResultBox( $result, $question_count );

			if ( $this->quiz->getToplistDataShowIn() == WpProQuiz_Model_Quiz::QUIZ_TOPLIST_SHOW_IN_BUTTON ) {
				$this->showToplistInButtonBox();
			}

			$this->showReviewBox( $question_count );
			$this->showQuizAnker();
			?>
		</div>
		<?php
		add_action( "wp_footer", array( $this, "max_question_script" ) );
	}

	public function getQuizData() {
		ob_start();

		$quizData = $this->showQuizBox( count( $this->question ) );

		$quizData['content']  = ob_get_contents();
		$quizData['site_url'] = get_site_url();

		ob_end_clean();

		return $quizData;
	}

	private function showQuizAnker() {
		?>
		<div class="wpProQuiz_quizAnker" style="display: none;"></div>
		<?php
	}

	private function showAddToplist() {
		?>
		<div class="wpProQuiz_addToplist" style="display: none;">
			<span
				style="font-weight: bold;"><?php _e( 'Your result has been entered into leaderboard', 'wp-pro-quiz' ); ?></span>

			<div style="margin-top: 6px;">
				<div class="wpProQuiz_addToplistMessage"
				     style="display: none;"><?php _e( 'Loading', 'wp-pro-quiz' ); ?></div>
				<div class="wpProQuiz_addBox">
					<div>
						<span>
							<label>
								<?php _e( 'Name', 'wp-pro-quiz' ); ?>: <input type="text"
								                                              placeholder="<?php _e( 'Name', 'wp-pro-quiz' ); ?>"
								                                              name="wpProQuiz_toplistName"
								                                              maxlength="15" size="16"
								                                              style="width: 150px;">
							</label>
							<label>
								<?php _e( 'E-Mail', 'wp-pro-quiz' ); ?>: <input type="email"
								                                                placeholder="<?php _e( 'E-Mail', 'wp-pro-quiz' ); ?>"
								                                                name="wpProQuiz_toplistEmail" size="20"
								                                                style="width: 150px;">
							</label>
						</span>

						<div style="margin-top: 5px;">
							<label>
								<?php _e( 'Captcha', 'wp-pro-quiz' ); ?>: <input type="text" name="wpProQuiz_captcha"
								                                                 size="8" style="width: 50px;">
							</label>
							<input type="hidden" name="wpProQuiz_captchaPrefix" value="0">
							<img alt="captcha" src="" class="wpProQuiz_captchaImg" style="vertical-align: middle;">
						</div>
					</div>
					<input class="wpProQuiz_button2" type="submit" value="<?php _e( 'Send', 'wp-pro-quiz' ); ?>"
					       name="wpProQuiz_toplistAdd">
				</div>
			</div>
		</div>
		<?php
	}

	private function fetchCloze( $answer_text ) {
		preg_match_all( '#\{(.*?)(?:\|(\d+))?(?:[\s]+)?\}#im', $answer_text, $matches, PREG_SET_ORDER );

		$data = array();

		foreach ( $matches as $k => $v ) {
			$text    = $v[1];
			$points  = ! empty( $v[2] ) ? (int) $v[2] : 1;
			$rowText = $multiTextData = array();
			$len     = array();

			if ( preg_match_all( '#\[(.*?)\]#im', $text, $multiTextMatches ) ) {
				foreach ( $multiTextMatches[1] as $multiText ) {
					$x = mb_strtolower( trim( html_entity_decode( $multiText, ENT_QUOTES ) ) );

					$len[]           = strlen( $x );
					$multiTextData[] = $x;
					$rowText[]       = $multiText;
				}
			} else {
				$x = mb_strtolower( trim( html_entity_decode( $text, ENT_QUOTES ) ) );

				$len[]           = strlen( $x );
				$multiTextData[] = $x;
				$rowText[]       = $text;
			}

			$a = '<span class="wpProQuiz_cloze"><input data-wordlen="' . max( $len ) . '" type="text" value=""> ';
			$a .= '<span class="wpProQuiz_clozeCorrect" style="display: none;"></span></span>';

			$data['correct'][] = $multiTextData;
			$data['points'][]  = $points;
			$data['data'][]    = $a;
		}

		$data['replace'] = preg_replace( '#\{(.*?)(?:\|(\d+))?(?:[\s]+)?\}#im', '@@wpProQuizCloze@@', $answer_text );

		return $data;
	}

	private function clozeCallback( $t ) {
		$a = array_shift( $this->_clozeTemp );

		return $a === null ? '' : $a;
	}

	private function fetchAssessment( $answerText, $quizId, $questionId ) {
		preg_match_all( '#\{(.*?)\}#im', $answerText, $matches );

		$this->_assessmetTemp = array();
		$data                 = array();

		for ( $i = 0, $ci = count( $matches[1] ); $i < $ci; $i ++ ) {
			$match = $matches[1][ $i ];

			preg_match_all( '#\[([^\|\]]+)(?:\|(\d+))?\]#im', $match, $ms );

			$a = '';

			for ( $j = 0, $cj = count( $ms[1] ); $j < $cj; $j ++ ) {
				$v = $ms[1][ $j ];

				$a .= '<label>
					<input type="radio" value="' . ( $j + 1 ) . '" name="question_' . $quizId . '_' . $questionId . '_' . $i . '" class="wpProQuiz_questionInput" data-index="' . $i . '">
					' . $v . '
				</label>';

			}

			$this->_assessmetTemp[] = $a;
		}

		$data['replace'] = preg_replace( '#\{(.*?)\}#im', '@@wpProQuizAssessment@@', $answerText );

		return $data;
	}

	private function assessmentCallback( $t ) {
		$a = array_shift( $this->_assessmetTemp );

		return $a === null ? '' : $a;
	}

	private function showFormBox() {
		$info = '<div class="wpProQuiz_invalidate">' . __( 'You must fill out this field.', 'learndash' ) . '</div>';

		$validateText = array(
			WpProQuiz_Model_Form::FORM_TYPE_NUMBER => __( 'You must specify a number.', 'learndash' ),
			WpProQuiz_Model_Form::FORM_TYPE_TEXT   => __( 'You must specify a text.', 'learndash' ),
			WpProQuiz_Model_Form::FORM_TYPE_EMAIL  => __( 'You must specify an email address.', 'learndash' ),
			WpProQuiz_Model_Form::FORM_TYPE_DATE   => __( 'You must specify a date.', 'learndash' )
		);
		?>
		<div class="wpProQuiz_forms">
			<table>
				<tbody>

				<?php
				$index = 0;
				foreach ( $this->forms as $form ) {
					/* @var $form WpProQuiz_Model_Form */

					$id   = 'forms_' . $this->quiz->getId() . '_' . $index ++;
					$name = 'wpProQuiz_field_' . $form->getFormId();
					?>
					<tr>
						<td>
							<?php
							echo '<label for="' . $id . '">';
							echo esc_html( $form->getFieldname() );
							echo $form->isRequired() ? '<span class="wpProQuiz_required">*</span>' : '';
							echo '</label>';
							?>
						</td>
						<td>

							<?php
							switch ( $form->getType() ) {
								case WpProQuiz_Model_Form::FORM_TYPE_TEXT:
								case WpProQuiz_Model_Form::FORM_TYPE_EMAIL:
								case WpProQuiz_Model_Form::FORM_TYPE_NUMBER:
									echo '<input name="' . $name . '" id="' . $id . '" type="text" ',
										'data-required="' . (int) $form->isRequired() . '" data-type="' . $form->getType() . '" data-form_id="' . $form->getFormId() . '">';
									break;
								case WpProQuiz_Model_Form::FORM_TYPE_TEXTAREA:
									echo '<textarea rows="5" cols="20" name="' . $name . '" id="' . $id . '" ',
										'data-required="' . (int) $form->isRequired() . '" data-type="' . $form->getType() . '" data-form_id="' . $form->getFormId() . '"></textarea>';
									break;
								case WpProQuiz_Model_Form::FORM_TYPE_CHECKBOX:
									echo '<input name="' . $name . '" id="' . $id . '" type="checkbox" value="1"',
										'data-required="' . (int) $form->isRequired() . '" data-type="' . $form->getType() . '" data-form_id="' . $form->getFormId() . '">';
									break;
								case WpProQuiz_Model_Form::FORM_TYPE_DATE:
									echo '<div data-required="' . (int) $form->isRequired() . '" data-type="' . $form->getType() . '" class="wpProQuiz_formFields" data-form_id="' . $form->getFormId() . '">';
									echo WpProQuiz_Helper_Until::getDatePicker( get_option( 'date_format', 'j. F Y' ), $name );
									echo '</div>';
									break;
								case WpProQuiz_Model_Form::FORM_TYPE_RADIO:
									echo '<div data-required="' . (int) $form->isRequired() . '" data-type="' . $form->getType() . '" class="wpProQuiz_formFields" data-form_id="' . $form->getFormId() . '">';

									if ( $form->getData() !== null ) {
										foreach ( $form->getData() as $data ) {
											echo '<label>';
											echo '<input name="' . $name . '" type="radio" value="' . esc_attr( $data ) . '"> ',
											esc_html( $data );
											echo '</label> ';
										}
									}

									echo '</div>';

									break;
								case WpProQuiz_Model_Form::FORM_TYPE_SELECT:
									if ( $form->getData() !== null ) {
										echo '<select name="' . $name . '" id="' . $id . '" ',
											'data-required="' . (int) $form->isRequired() . '" data-type="' . $form->getType() . '" data-form_id="' . $form->getFormId() . '">';
										echo '<option value=""></option>';

										foreach ( $form->getData() as $data ) {
											echo '<option value="' . esc_attr( $data ) . '">', esc_html( $data ), '</option>';
										}

										echo '</select>';
									}
									break;
								case WpProQuiz_Model_Form::FORM_TYPE_YES_NO:
									echo '<div data-required="' . (int) $form->isRequired() . '" data-type="' . $form->getType() . '" class="wpProQuiz_formFields" data-form_id="' . $form->getFormId() . '">';
									echo '<label>';
									echo '<input name="' . $name . '" type="radio" value="1"> ',
									__( 'Yes', 'learndash' );
									echo '</label> ';

									echo '<label>';
									echo '<input name="' . $name . '" type="radio" value="0"> ',
									__( 'No', 'learndash' );
									echo '</label> ';
									echo '</div>';
									break;
							}

							if ( isset( $validateText[ $form->getType() ] ) ) {
								echo '<div class="wpProQuiz_invalidate">' . $validateText[ $form->getType() ] . '</div>';
							} else {
								echo '<div class="wpProQuiz_invalidate">' . __( 'You must fill out this field.', 'learndash' ) . '</div>';
							}
							?>
						</td>
					</tr>
				<?php } ?>
				</tbody>
			</table>

		</div>

		<?php
	}

	private function showLockBox() {
		?>
		<div style="display: none;" class="wpProQuiz_lock">
			<p>
				<?php echo sprintf( _x( 'You have already completed the %s before. Hence you can not start it again.', 'You have already completed the quiz before. Hence you can not start it again.', 'learndash' ), LearnDash_Custom_Label::label_to_lower( 'quiz' ) ); ?>
			</p>
		</div>
		<?php
	}

	private function showStartOnlyRegisteredUserBox() {
		?>
		<div style="display: none;" class="wpProQuiz_startOnlyRegisteredUser">
			<p>
				<?php echo sprintf( _x( 'You must sign in or sign up to start the %s.', 'You must sign in or sign up to start the quiz.', 'wp-pro-quiz' ), LearnDash_Custom_Label::label_to_lower( 'quiz' ) ); ?>
			</p>
		</div>
		<?php
	}

	private function showPrerequisiteBox() {
		?>
		<div style="display: none;" class="wpProQuiz_prerequisite">
			<p>
				<?php echo sprintf( _x( "You have to pass the previous Module's %s in order to start this %s", "You have to pass the previous Module's Quiz in order to start this Quiz", 'wp-pro-quiz' ), LearnDash_Custom_Label::label_to_lower( 'quiz' ), LearnDash_Custom_Label::label_to_lower( 'quiz' ) ); ?>
				<span></span>
			</p>
		</div>
		<?php
	}

	private function showCheckPageBox( $questionCount ) {
		?>
		<div class="wpProQuiz_checkPage" style="display: none;">
			<h4 class="wpProQuiz_header"><?php echo sprintf( _x( '%s-summary', 'Quiz-summary', 'wp-pro-quiz' ), LearnDash_Custom_Label::get_label( 'quiz' ) ); ?></h4>

			<p>
				<?php printf( __( '%s of %s questions completed', 'wp-pro-quiz' ), '<span>0</span>', $questionCount ); ?>
			</p>

			<p><?php _e( 'Questions', 'wp-pro-quiz' ); ?>:</p>

			<div style="margin-bottom: 20px;" class="wpProQuiz_box">
				<ol>
					<?php for ( $xy = 1; $xy <= $questionCount; $xy ++ ) { ?>
						<li><?php echo $xy; ?></li>
					<?php } ?>
				</ol>
				<div style="clear: both;"></div>
			</div>

			<?php
			if ( $this->quiz->isFormActivated() && $this->quiz->getFormShowPosition() == WpProQuiz_Model_Quiz::QUIZ_FORM_POSITION_END
			     && ( $this->quiz->isShowReviewQuestion() && ! $this->quiz->isQuizSummaryHide() )
			) {

				?>
				<h4 class="wpProQuiz_header"><?php _e( 'Information', 'wp-pro-quiz' ); ?></h4>
				<?php
				$this->showFormBox();
			}

			?>

			<input type="button" name="endQuizSummary" value="<?php echo sprintf( _x( 'Finish %s', 'Finish Quiz Button Label', 'wp-pro-quiz' ), LearnDash_Custom_Label::get_label( 'quiz' ) ); ?>"
			       class="wpProQuiz_button">
		</div>
		<?php
	}

	private function showInfoPageBox() {
		?>
		<div class="wpProQuiz_infopage" style="display: none;">
			<h4><?php _e( 'Information', 'wp-pro-quiz' ); ?></h4>

			<?php
			if ( $this->quiz->isFormActivated() && $this->quiz->getFormShowPosition() == WpProQuiz_Model_Quiz::QUIZ_FORM_POSITION_END
			     && ( ! $this->quiz->isShowReviewQuestion() || $this->quiz->isQuizSummaryHide() )
			) {
				$this->showFormBox();
			}

			?>

			<input type="button" name="endInfopage" value="<?php echo sprintf( _x( 'Finish %s', 'Finish Quiz Button Label', 'wp-pro-quiz' ), LearnDash_Custom_Label::get_label( 'quiz' ) ); ?>"
			       class="wpProQuiz_button">
		</div>
		<?php
	}

	private function showStartQuizBox() {
		?>
		<div class="wpProQuiz_text">

			<?php
			if ( $this->quiz->isFormActivated() && $this->quiz->getFormShowPosition() == WpProQuiz_Model_Quiz::QUIZ_FORM_POSITION_START ) {
				$this->showFormBox();
			}
			?>

			<div>
				<input class="wpProQuiz_button" type="button" value="<?php echo sprintf( _x( 'Start %s', 'Start Quiz Button Label', 'wp-pro-quiz' ), LearnDash_Custom_Label::get_label( 'quiz' ) ); ?>"
				       name="startQuiz">
			</div>
		</div>
		<?php
	}

	private function showUserQuizStatisticsBox() {
		
		// For now don't use. 
		return;
		
		
		global $post;
		//error_log('post<pre>'. print_r($post, true) .'</pre>');

		if ( current_user_can( 'wpProQuiz_show_statistics' ) ) {
			$user_quizzes = get_user_meta(get_current_user_id(), '_sfwd-quizzes', true);
			//error_log('user_quizzes<pre>'. print_r($user_quizzes, true) .'</pre>');
			if ( !empty( $user_quizzes ) ) {
				//krsort($user_quizzes);
				$user_quizzes = array_reverse($user_quizzes);
				//error_log('sorted: user_quizzes<pre>'. print_r($user_quizzes, true) .'</pre>');


				foreach( $user_quizzes as $user_quiz_idx => $user_quiz ) {
					if ( ( isset( $user_quiz['quiz'] ) ) && ( $user_quiz['quiz'] == $post->ID ) ) {
						if ( ( isset( $user_quiz['pro_quizid'] ) ) && ( $user_quiz['pro_quizid'] == $this->quiz->getID() ) ) {
							if ( ( isset( $user_quiz['statistic_ref_id'] ) ) && ( !empty($user_quiz['statistic_ref_id']) ) ) {
								//error_log('found idx['. $user_quiz_idx .']');
								?>
								<div class="wpProQuiz_text">
									<div>
										<input class="wpProQuiz_button" type="button" value="<?php echo sprintf( _x( 'View %s Statistics', 'Start Quiz Statistics Button Label', 'wp-pro-quiz' ), LearnDash_Custom_Label::get_label( 'quiz' ) ); ?>" name="viewUserQuizStatistics" data-quiz_id="<?php echo $user_quiz['pro_quizid'] ?>" data-ref_id="<?php echo intval( $user_quiz['statistic_ref_id'] ) ?>" />

									</div>
								</div>
								<?php
								LD_QuizPro::showModalWindow();
								return;
							}
						}
					}
				}
			}
		}
	}
	
	private function showTimeLimitBox() {
		?>
		<div style="display: none;" class="wpProQuiz_time_limit">
			<div class="time"><?php _e( 'Time limit', 'wp-pro-quiz' ); ?>: <span>0</span></div>
			<div class="wpProQuiz_progress"></div>
		</div>
		<?php
	}

	private function showReviewBox( $questionCount ) {
		?>
		<div class="wpProQuiz_reviewDiv" style="display: none;">
			<div class="wpProQuiz_reviewQuestion">
				<ol>
					<?php for ( $xy = 1; $xy <= $questionCount; $xy ++ ) { ?>
						<li><?php echo $xy; ?></li>
					<?php } ?>
				</ol>
				<div style="display: none;"></div>
			</div>
			<div class="wpProQuiz_reviewLegend">
				<ol>
					<li>
						<span class="wpProQuiz_reviewColor" style="background-color: #6CA54C;"></span>
						<span class="wpProQuiz_reviewText"><?php _e( 'Answered', 'wp-pro-quiz' ); ?></span>
					</li>
					<li>
						<span class="wpProQuiz_reviewColor" style="background-color: #FFB800;"></span>
						<span class="wpProQuiz_reviewText"><?php _e( 'Review', 'wp-pro-quiz' ); ?></span>
					</li>
				</ol>
				<div style="clear: both;"></div>
			</div>
			<div>
				<?php if ( $this->quiz->getQuizModus() != WpProQuiz_Model_Quiz::QUIZ_MODUS_SINGLE ) { ?>
					<input type="button" name="review" value="<?php _e( 'Review question', 'wp-pro-quiz' ); ?>"
					       class="wpProQuiz_button2" style="float: left; display: block;">
					<?php if ( ! $this->quiz->isQuizSummaryHide() ) { ?>
						<input type="button" name="quizSummary" value="<?php echo sprintf( _x( '%s-summary', 'Quiz-summary', 'wp-pro-quiz' ), LearnDash_Custom_Label::get_label( 'quiz' ) ); ?>"
						       class="wpProQuiz_button2" style="float: right;">
					<?php } ?>
					<div style="clear: both;"></div>
				<?php } ?>
			</div>
		</div>
		<?php
	}

	private function showResultBox( $result, $questionCount ) {
		?>
		<div style="display: none;" class="wpProQuiz_sending">
			<h4 class="wpProQuiz_header"><?php _e( 'Results', 'wp-pro-quiz' ); ?></h4>

			<p>

			<div><?php echo sprintf( _x( "%s complete. Results are being recorded.", "Quiz complete. Results are being recorded.",  "learndash" ), LearnDash_Custom_Label::get_label( 'quiz' ) ); ?></div>
			<div>
				<dd class="course_progress">
					<div class="course_progress_blue sending_progress_bar" style="width: 0%;">
					</div>
				</dd>
			</div>
			</p>
		</div>

		<div style="display: none;" class="wpProQuiz_results">
			<h4 class="wpProQuiz_header"><?php _e( 'Results', 'wp-pro-quiz' ); ?></h4>

			<?php if ( ! $this->quiz->isHideResultCorrectQuestion() ) { ?>
				<p>
					<?php printf( __( '%s of %s questions answered correctly', 'wp-pro-quiz' ), '<span class="wpProQuiz_correct_answer">0</span>', '<span>' . $questionCount . '</span>' ); ?>
				</p>
			<?php }

			if ( ! $this->quiz->isHideResultQuizTime() ) { ?>
				<p class="wpProQuiz_quiz_time">
					<?php _e( 'Your time: <span></span>', 'wp-pro-quiz' ); ?>
				</p>
			<?php } ?>

			<p class="wpProQuiz_time_limit_expired" style="display: none;">
				<?php _e( 'Time has elapsed', 'wp-pro-quiz' ); ?>
			</p>

			<?php if ( ! $this->quiz->isHideResultPoints() ) { ?>
				<p class="wpProQuiz_points">
					<?php printf( __( 'You have reached %s of %s points, (%s)', 'wp-pro-quiz' ), '<span>0</span>', '<span>0</span>', '<span>0</span>' ); ?>
				</p>
				<p class="wpProQuiz_graded_points" style="display: none;">
					<?php printf( __( 'Earned Points: %s of %s, (%s)', 'wp-pro-quiz' ), '<span>0</span>', '<span>0</span>', '<span>0</span>' ); ?><br />
					<?php printf( __( '%s Essay(s) Pending (Possible Points: %s)', 'wp-pro-quiz' ), '<span>0</span>', '<span>0</span>', '<span>0</span>' ); ?><br />
				</p>
			<?php } ?>

			<p class="wpProQuiz_certificate" style="display: none !important;">
				<?php echo LD_QuizPro::certificate_link( "" ); ?>
			</p>

			<?php if ( $this->quiz->isShowAverageResult() ) { ?>
				<div class="wpProQuiz_resultTable">
					<table>
						<tbody>
						<tr>
							<td class="wpProQuiz_resultName"><?php _e( 'Average score', 'wp-pro-quiz' ); ?></td>
							<td class="wpProQuiz_resultValue">
								<div style="background-color: #6CA54C;">&nbsp;</div>
								<span>&nbsp;</span>
							</td>
						</tr>
						<tr>
							<td class="wpProQuiz_resultName"><?php _e( 'Your score', 'wp-pro-quiz' ); ?></td>
							<td class="wpProQuiz_resultValue">
								<div style="background-color: #F79646;">&nbsp;</div>
								<span>&nbsp;</span>
							</td>
						</tr>
						</tbody>
					</table>
				</div>
			<?php } ?>

			<div class="wpProQuiz_catOverview" <?php $this->isDisplayNone( $this->quiz->isShowCategoryScore() ); ?>>
				<h4><?php _e( 'Categories', 'wp-pro-quiz' ); ?></h4>

				<div style="margin-top: 10px;">
					<ol>
						<?php foreach ( $this->category as $cat ) {
							if ( ! $cat->getCategoryId() ) {
								$cat->setCategoryName( __( 'Not categorized', 'wp-pro-quiz' ) );
							}
							?>
							<li data-category_id="<?php echo $cat->getCategoryId(); ?>">
								<span class="wpProQuiz_catName"><?php echo $cat->getCategoryName(); ?></span>
								<span class="wpProQuiz_catPercent">0%</span>
							</li>
						<?php } ?>
					</ol>
				</div>
			</div>
			<div>
				<ul class="wpProQuiz_resultsList">
					<?php foreach ( $result['text'] as $resultText ) { ?>
						<li style="display: none;">
							<div>
								<?php echo do_shortcode( apply_filters( 'comment_text', $resultText ) ); ?>
							</div>
						</li>
					<?php } ?>
				</ul>
			</div>
			<?php
			if ( $this->quiz->isToplistActivated() ) {
				if ( $this->quiz->getToplistDataShowIn() == WpProQuiz_Model_Quiz::QUIZ_TOPLIST_SHOW_IN_NORMAL ) {
					echo do_shortcode( '[LDAdvQuiz_toplist ' . $this->quiz->getId() . ' q="true"]' );
				}

				$this->showAddToplist();
			}
			?>
			<div style="margin: 10px 0px;">
				<?php
					/**
					 *	See snippet https://bitbucket.org/snippets/learndash/nMk9a
					 * @since 2.3.0.2
					 */
					$show_quiz_continue_buttom_on_fail = apply_filters( 'show_quiz_continue_buttom_on_fail', false, learndash_get_quiz_id_by_pro_quiz_id( $this->quiz->getId() ) );
				?>
				<div class='quiz_continue_link<?php if ( $show_quiz_continue_buttom_on_fail == true ) { echo ' show_quiz_continue_buttom_on_fail'; } ?>' style="margin: 10px 0px;">

				</div>
				<?php if ( ! $this->quiz->isBtnRestartQuizHidden() ) { ?>
					<input class="wpProQuiz_button" type="button" name="restartQuiz"
					       value="<?php echo sprintf( _x( 'Restart %s', 'Restart Quiz Button Label', 'wp-pro-quiz' ), LearnDash_Custom_Label::get_label( 'quiz' ) ); ?>">
				<?php }
				if ( ! $this->quiz->isBtnViewQuestionHidden() ) { ?>
					<input class="wpProQuiz_button" type="button" name="reShowQuestion"
					       value="<?php _e( 'View questions', 'wp-pro-quiz' ); ?>">
				<?php } ?>
				<?php if ( $this->quiz->isToplistActivated() && $this->quiz->getToplistDataShowIn() == WpProQuiz_Model_Quiz::QUIZ_TOPLIST_SHOW_IN_BUTTON ) { ?>
					<input class="wpProQuiz_button" type="button" name="showToplist"
					       value="<?php _e( 'Show leaderboard', 'wp-pro-quiz' ); ?>">
				<?php } ?>
			</div>
		</div>
		<?php
	}

	private function showToplistInButtonBox() {
		?>
		<div class="wpProQuiz_toplistShowInButton" style="display: none;">
			<?php echo do_shortcode( '[LDAdvQuiz_toplist ' . $this->quiz->getId() . ' q="true"]' ); ?>
		</div>
		<?php
	}

	public function showQuizBox( $questionCount ) {
		$globalPoints = 0;
		$json         = array();
		$catPoints    = array();
		?>
		<div style="display: none;" class="wpProQuiz_quiz">
			<ol class="wpProQuiz_list">
				<?php
				$index = 0;
				foreach ( $this->question as $question ) {
					$index ++;
					$answerArray = $question->getAnswerData();

					$globalPoints += $question->getPoints();


					$json[ $question->getId() ]['type']  = $question->getAnswerType();
					$json[ $question->getId() ]['id']    = (int) $question->getId();
					$json[ $question->getId() ]['catId'] = (int) $question->getCategoryId();

					if ( $question->isAnswerPointsActivated() && $question->isAnswerPointsDiffModusActivated() && $question->isDisableCorrect() ) {
						$json[ $question->getId() ]['disCorrect'] = (int) $question->isDisableCorrect();
						//$json[$question->getId()]['discoordinates'] = (int)$question->isDisableCorrect();
					}

					if ( ! isset( $catPoints[ $question->getCategoryId() ] ) ) {
						$catPoints[ $question->getCategoryId() ] = 0;
					}

					$catPoints[ $question->getCategoryId() ] += $question->getPoints();

					if ( ! $question->isAnswerPointsActivated() ) {
						$json[ $question->getId() ]['points'] = $question->getPoints();
						//$json[$question->getId()]['version'] = $question->getPoints();
						// 					$catPoints[$question->getCategoryId()] += $question->getPoints();
					}

					if ( $question->isAnswerPointsActivated() && $question->isAnswerPointsDiffModusActivated() ) {
						// 					$catPoints[$question->getCategoryId()] += $question->getPoints();
						$json[ $question->getId() ]['diffMode'] = 1;
					}

					?>
					<li class="wpProQuiz_listItem" style="display: none;" data-type="<?php echo $question->getAnswerType(); ?>">
						<div
							class="wpProQuiz_question_page" <?php $this->isDisplayNone( $this->quiz->getQuizModus() != WpProQuiz_Model_Quiz::QUIZ_MODUS_SINGLE && ! $this->quiz->isHideQuestionPositionOverview() ); ?> >
							<?php printf( __( 'Question %s of %s', 'wp-pro-quiz' ), '<span>' . $index . '</span>', '<span>' . $questionCount . '</span>' ); ?>
						</div>
						<h5 style="<?php echo $this->quiz->isHideQuestionNumbering() ? 'display: none;' : 'display: inline-block;' ?>"
						    class="wpProQuiz_header">
							<span><?php echo $index; ?></span>. <?php _e( 'Question', 'wp-pro-quiz' ); ?>
						</h5>

						<?php if ( $this->quiz->isShowPoints() ) { ?>
							<span
								style="font-weight: bold; float: right;"><?php printf( __( '%d points', 'wp-pro-quiz' ), $question->getPoints() ); ?></span>
							<div style="clear: both;"></div>
						<?php } ?>

						<?php if ( $question->getCategoryId() && $this->quiz->isShowCategory() ) { ?>
							<div style="font-weight: bold; padding-top: 5px;">
								<?php printf( __( 'Category: %s', 'wp-pro-quiz' ), esc_html( $question->getCategoryName() ) ); ?>
							</div>
						<?php } ?>
						<div class="wpProQuiz_question" style="margin: 10px 0px 0px 0px;">
							<div class="wpProQuiz_question_text">
								<?php echo do_shortcode( apply_filters( 'comment_text', $question->getQuestion() ) ); ?>
							</div>

							<?php
							/**
							 * Matrix Sort Answer
							 */
							?>
							<?php if ( $question->getAnswerType() === 'matrix_sort_answer' ) { ?>
								<div class="wpProQuiz_matrixSortString">
									<h5 class="wpProQuiz_header"><?php _e( 'Sort elements', 'wp-pro-quiz' ); ?></h5>
									<ul class="wpProQuiz_sortStringList">
										<?php
										$matrix = array();
										foreach ( $answerArray as $k => $v ) {
											$matrix[ $k ][] = $k;

											foreach ( $answerArray as $k2 => $v2 ) {
												if ( $k != $k2 ) {
													if ( $v->getAnswer() == $v2->getAnswer() ) {
														$matrix[ $k ][] = $k2;
													} else if ( $v->getSortString() == $v2->getSortString() ) {
														$matrix[ $k ][] = $k2;
													}
												}
											}
										}

										foreach ( $answerArray as $k => $v ) {
											?>
											<li class="wpProQuiz_sortStringItem" data-pos="<?php echo $k; ?>">
												<?php echo $v->isSortStringHtml() ? $v->getSortString() : esc_html( $v->getSortString() ); ?>
											</li>
										<?php } ?>
									</ul>
									<div style="clear: both;"></div>
								</div>
							<?php } ?>


							<?php
							/**
							 * Print questions in a list for all other answer types
							 */
							?>
							<ul class="wpProQuiz_questionList" data-question_id="<?php echo $question->getId(); ?>"
							    data-type="<?php echo $question->getAnswerType(); ?>">
								<?php
								$answer_index = 0;

								foreach ( $answerArray as $v ) {
									$answer_text = $v->isHtml() ? do_shortcode( $v->getAnswer() ) : esc_html( $v->getAnswer() );

									if ( $answer_text == '' && ! $v->isGraded() ) {
										continue;
									}

									if ( $question->isAnswerPointsActivated() ) {
										$json[ $question->getId() ]['points'][] = $v->getPoints();
										//$json[$question->getId()]['version'][] = $v->getPoints();
										// if(!$question->isAnswerPointsDiffModusActivated())
										// 		$catPoints[$question->getCategoryId()] += $question->getPoints();
									}

									$datapos = $answer_index;
									if ( $question->getAnswerType() === 'sort_answer' || $question->getAnswerType() === 'matrix_sort_answer' ) {
										$datapos = LD_QuizPro::datapos( $question->getId(), $answer_index );
									}
									?>

									<li class="wpProQuiz_questionListItem" data-pos="<?php echo $datapos; ?>">


										<?php
										/**
										 *  Single/Multiple
										 */
										?>
										<?php if ( $question->getAnswerType() === 'single' || $question->getAnswerType() === 'multiple' ) { ?>
											<?php $json[ $question->getId() ]['correct'][] = (int) $v->isCorrect(); ?>
											<?php /* $json[$question->getId()]['coordinates'][] = (int)$v->isCorrect(); */ ?>
											<span <?php echo $this->quiz->isNumberedAnswer() ? '' : 'style="display:none;"' ?>></span>
											<label>
												<input class="wpProQuiz_questionInput"
												       type="<?php echo $question->getAnswerType() === 'single' ? 'radio' : 'checkbox'; ?>"
												       name="question_<?php echo $this->quiz->getId(); ?>_<?php echo $question->getId(); ?>"
												       value="<?php echo( $answer_index + 1 ); ?>"> <?php echo $answer_text; ?>
											</label>


										<?php
										/**
										 *  Sort Answer
										 */
										?>
										<?php } else if ( $question->getAnswerType() === 'sort_answer' ) { ?>
											<?php $json[ $question->getId() ]['correct'][] = (int) $answer_index; ?>
											<?php /* $json[$question->getId()]['coordinates'][] = (int)$answer_index; */ ?>
											<div class="wpProQuiz_sortable">
												<?php echo $answer_text; ?>
											</div>


										<?php
										/**
										 *  Free Answer
										 */
										?>
										<?php } else if ( $question->getAnswerType() === 'free_answer' ) { ?>
											<?php $json[ $question->getId() ]['correct'] = $this->getFreeCorrect( $v ); ?>
											<?php /* $json[$question->getId()]['coordinates'] = $this->getFreeCorrect($v); */ ?>
											<label>
												<input class="wpProQuiz_questionInput" type="text"
												       name="question_<?php echo $this->quiz->getId(); ?>_<?php echo $question->getId(); ?>"
												       style="width: 300px;">
											</label>


										<?php
										/**
										 *  Matrix Sort Answer
										 */
										?>
										<?php } else if ( $question->getAnswerType() === 'matrix_sort_answer' ) { ?>
											<?php
											$json[ $question->getId() ]['correct'][] = (int) $answer_index;
											//$json[$question->getId()]['coordinates'][] = (int)$answer_index;
											$msacwValue = $question->getMatrixSortAnswerCriteriaWidth() > 0 ? $question->getMatrixSortAnswerCriteriaWidth() : 20;
											?>
											<table>
												<tbody>
												<tr class="wpProQuiz_mextrixTr">
													<td width="<?php echo $msacwValue; ?>%">
														<div
															class="wpProQuiz_maxtrixSortText"><?php echo $answer_text; ?></div>
													</td>
													<td width="<?php echo 100 - $msacwValue; ?>%">
														<ul class="wpProQuiz_maxtrixSortCriterion"></ul>
													</td>
												</tr>
												</tbody>
											</table>


										<?php
										/**
										 *  Cloze Answer
										 */
										?>
										<?php } else if ( $question->getAnswerType() === 'cloze_answer' ) {
											$clozeData = $this->fetchCloze( $v->getAnswer() );

											$this->_clozeTemp = $clozeData['data'];

											$json[ $question->getId() ]['correct'] = $clozeData['correct'];
											//$json[$question->getId()]['coordinates'] = $clozeData['correct'];

											if ( $question->isAnswerPointsActivated() ) {
												$json[ $question->getId() ]['points'] = $clozeData['points'];
												//$json[$question->getId()]['version'] = $clozeData['points'];
											}

											// Added the wpautop in LD 2.2.1 to retain line-break formatting. 
											$clozeData['replace'] = wpautop($clozeData['replace']);
											$cloze = do_shortcode( apply_filters( 'comment_text', $clozeData['replace'] ) );
											
											$cloze = $clozeData['replace'];

											echo preg_replace_callback( '#@@wpProQuizCloze@@#im', array(
												$this,
												'clozeCallback'
											), $cloze );


										/**
										 *  Assessment answer
										 */
										} else if ( $question->getAnswerType() === 'assessment_answer' ) {
											$assessmentData = $this->fetchAssessment( $v->getAnswer(), $this->quiz->getId(), $question->getId() );
											$assessment     = do_shortcode( apply_filters( 'comment_text', $assessmentData['replace'] ) );
											echo preg_replace_callback( '#@@wpProQuizAssessment@@#im', array(
												$this,
												'assessmentCallback'
											), $assessment );

										/**
										 * Essay answer
										 */
										} else if ( $question->getAnswerType() === 'essay' ) {
											?>
												<?php if ( $v->getGradedType() === 'text' ) : ?>

													<textarea class="wpProQuiz_questionEssay" rows="10" cols="40"
													          name="question_<?php echo $this->quiz->getId(); ?>_<?php echo $question->getId(); ?>"
													          id="wpProQuiz_questionEssay_question_<?php echo $this->quiz->getId(); ?>_<?php echo $question->getId(); ?>" 
															  cols="30" autocomplete="off"
													          rows="10" placeholder="<?php _e( 'Type your response here', 'learndash' ); ?>"></textarea>

												<?php elseif ( $v->getGradedType() === 'upload' ) : ?>

													<p><?php _e( 'Upload your answer to this question.', 'learndash' ); ?></p>

													<p>
														<form enctype="multipart/form-data" method="post" name="uploadEssay">
															<input type='file' name='uploadEssay[]' id='uploadEssay_<?php echo $question->getId(); ?>' size='35' class='wpProQuiz_upload_essay' />
															<input type="submit" id='uploadEssaySubmit_<?php echo $question->getId(); ?>' value="Upload" />
															<input type="hidden" id="_uploadEssay_nonce_<?php echo $question->getId(); ?>" name="_uploadEssay_nonce" value="<?php echo wp_create_nonce('learndash-upload-essay'); ?>" />
														</form>
														<input type="hidden" class="uploadEssayFile" id='uploadEssayFile_<?php echo $question->getId(); ?>' value="" />
													</p>
												<?php else : ?>
													<?php _e( 'Essay type not found', 'learndash' ); ?>
												<?php endif; ?>

												<p class="graded-disclaimer">
													<?php if ( 'graded-full' == $v->getGradingProgression() ) : ?>
														<?php _e( 'This response will be awarded full points automatically, but it can be reviewed and adjusted after submission.', 'learndash' ); ?>
													<?php elseif ( 'not-graded-full' == $v->getGradingProgression() ) : ?>
														<?php _e( 'This response will be awarded full points automatically, but it will be reviewed and possibly adjusted after submission.', 'learndash' ); ?>
													<?php elseif ( 'not-graded-none' == $v->getGradingProgression() ) : ?>
														<?php _e( 'This response will be reviewed and graded after submission.', 'learndash' ); ?>
													<?php endif; ?>
												</p>
											<?php
										}

										?>
									</li>
									<?php
									$answer_index ++;
								}
								?>
							</ul>
						</div>
						<?php if ( ! $this->quiz->isHideAnswerMessageBox() ) { ?>
							<div class="wpProQuiz_response" style="display: none;">
								<div style="display: none;" class="wpProQuiz_correct">
									<?php if ( $question->isShowPointsInBox() && $question->isAnswerPointsActivated() ) { ?>
										<div>
											<span style="float: left;"><?php _e( 'Correct', 'wp-pro-quiz' ); ?></span>
											<span style="float: right;"><?php echo $question->getPoints() . ' / ' . $question->getPoints(); ?><?php _e( 'Points', 'wp-pro-quiz' ); ?></span>
											<div style="clear: both;"></div>
										</div>
									<?php } elseif ( 'essay' == $question->getAnswerType() ) { ?>
										<?php _e( 'Grading can be reviewed and adjusted.', 'wp-pro-quiz' ); ?>
									<?php } else { ?>
										<span><?php _e( 'Correct', 'wp-pro-quiz' ); ?></span>
									<?php } ?>
									<p class="wpProQuiz_AnswerMessage">
									</p>
								</div>
								<div style="display: none;" class="wpProQuiz_incorrect">
									<?php if ( $question->isShowPointsInBox() && $question->isAnswerPointsActivated() ) { ?>
										<div>
											<span style="float: left;">
												<?php _e( 'Incorrect', 'wp-pro-quiz' ); ?>
											</span>
											<span style="float: right;"><span class="wpProQuiz_responsePoints"></span> / <?php echo $question->getPoints(); ?> <?php _e( 'Points', 'wp-pro-quiz' ); ?></span>

											<div style="clear: both;"></div>
										</div>
									<?php } elseif ( 'essay' == $question->getAnswerType() ) { ?>
										<?php _e( 'Grading can be reviewed and adjusted.', 'wp-pro-quiz' ); ?>
									<?php } else { ?>
										<span>
									<?php _e( 'Incorrect', 'wp-pro-quiz' ); ?>
								</span>
									<?php } ?>
									<p class="wpProQuiz_AnswerMessage">
									</p>
								</div>
							</div>
						<?php } ?>

						<?php if ( $question->isTipEnabled() ) { ?>
							<div class="wpProQuiz_tipp" style="display: none; position: relative;">
								<div>
									<h5 style="margin: 0px 0px 10px;"
									    class="wpProQuiz_header"><?php _e( 'Hint', 'wp-pro-quiz' ); ?></h5>
									<?php echo do_shortcode( apply_filters( 'comment_text', $question->getTipMsg() ) ); ?>
								</div>
							</div>
						<?php } ?>

						<?php if ( $this->quiz->getQuizModus() == WpProQuiz_Model_Quiz::QUIZ_MODUS_CHECK && ! $this->quiz->isSkipQuestionDisabled() && $this->quiz->isShowReviewQuestion() ) { ?>
							<input type="button" name="skip" value="<?php _e( 'Skip question', 'wp-pro-quiz' ); ?>"
							       class="wpProQuiz_button wpProQuiz_QuestionButton"
							       style="float: left; margin-right: 10px !important;">
						<?php } ?>
						<input type="button" name="back" value="<?php _e( 'Back', 'wp-pro-quiz' ); ?>"
						       class="wpProQuiz_button wpProQuiz_QuestionButton"
						       style="float: left !important; margin-right: 10px !important; display: none;">
						<?php if ( $question->isTipEnabled() ) { ?>
							<input type="button" name="tip" value="<?php _e( 'Hint', 'wp-pro-quiz' ); ?>"
							       class="wpProQuiz_button wpProQuiz_QuestionButton wpProQuiz_TipButton"
							       style="float: left !important; display: inline-block; margin-right: 10px !important;">
						<?php } ?>
						<input type="button" name="check" value="<?php _e( 'Check', 'wp-pro-quiz' ); ?>"
						       class="wpProQuiz_button wpProQuiz_QuestionButton"
						       style="float: right !important; margin-right: 10px !important; display: none;">
						<input type="button" name="next" value="<?php _e( 'Next', 'wp-pro-quiz' ); ?>"
						       class="wpProQuiz_button wpProQuiz_QuestionButton" style="float: right; display: none;">

						<div style="clear: both;"></div>

						<?php if ( $this->quiz->getQuizModus() == WpProQuiz_Model_Quiz::QUIZ_MODUS_SINGLE ) { ?>
							<div style="margin-bottom: 20px;"></div>
						<?php } ?>
					</li>

				<?php } ?>
			</ol>
			<?php if ( $this->quiz->getQuizModus() == WpProQuiz_Model_Quiz::QUIZ_MODUS_SINGLE ) { ?>
				<div>
					<input type="button" name="wpProQuiz_pageLeft"
					       data-text="<?php echo esc_attr( __( 'Page %d', 'wp-pro-quiz' ) ); ?>"
					       style="float: left; display: none;" class="wpProQuiz_button wpProQuiz_QuestionButton">
					<input type="button" name="wpProQuiz_pageRight"
					       data-text="<?php echo esc_attr( __( 'Page %d', 'wp-pro-quiz' ) ); ?>"
					       style="float: right; display: none;" class="wpProQuiz_button wpProQuiz_QuestionButton">

					<?php if ( $this->quiz->isShowReviewQuestion() && ! $this->quiz->isQuizSummaryHide() ) { ?>
						<input type="button" name="checkSingle" value="<?php echo sprintf( _x( '%s-summary', 'Quiz-summary', 'wp-pro-quiz' ), LearnDash_Custom_Label::get_label( 'quiz' ) ); ?>"
						       class="wpProQuiz_button wpProQuiz_QuestionButton" style="float: right;">
					<?php } else { ?>
						<input type="button" name="checkSingle" value="<?php echo sprintf( _x( 'Finish %s', 'Finish Quiz Button Label', 'wp-pro-quiz' ), LearnDash_Custom_Label::get_label( 'quiz' ) ); ?>"
						       class="wpProQuiz_button wpProQuiz_QuestionButton" style="float: right;">
					<?php } ?>

					<div style="clear: both;"></div>
				</div>
			<?php } ?>
		</div>
		<?php
		return array( 'globalPoints' => $globalPoints, 'json' => $json, 'catPoints' => $catPoints );
	}

	private function showLoadQuizBox() {
		?>
		<div style="display: none;" class="wpProQuiz_loadQuiz">
			<p>
				<?php printf( _x('%s is loading...', 'quiz is loading... Label', 'learndash'), LearnDash_Custom_Label::get_label( 'quiz' ) ); ?>
			</p>
		</div>
		<?php
	}
}
