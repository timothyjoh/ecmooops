<?php
class NSS_XAPI_Verbs {
	public $verbs = array(
						"answered" => array(
							"id" => "http://adlnet.gov/expapi/verbs/answered",
							"display" => array("en-US" => "answered")
						),
						"asked" => array(
							"id" => "http://adlnet.gov/expapi/verbs/asked",
							"display" => array("en-US" => "asked")
						),
						"attempted" => array(
							"id" => "http://adlnet.gov/expapi/verbs/attempted",
							"display" => array("en-US" => "attempted")
						),
						"attended" => array(
							"id" => "http://adlnet.gov/expapi/verbs/attended",
							"display" => array("en-US" => "attended")
						),
						"commented" => array(
							"id" => "http://adlnet.gov/expapi/verbs/commented",
							"display" => array("en-US" => "commented")
						),
						"completed" => array(
							"id" => "http://adlnet.gov/expapi/verbs/completed",
							"display" => array("en-US" => "completed")
						),
						"exited" => array(
							"id" => "http://adlnet.gov/expapi/verbs/exited",
							"display" => array("en-US" => "exited")
						),
						"experienced" => array(
							"id" => "http://adlnet.gov/expapi/verbs/experienced",
							"display" => array("en-US" => "experienced")
						),
						"failed" => array(
							"id" => "http://adlnet.gov/expapi/verbs/failed",
							"display" => array("en-US" => "failed")
						),
						"imported" => array(
							"id" => "http://adlnet.gov/expapi/verbs/imported",
							"display" => array("en-US" => "imported")
						),
						"initialized" => array(
							"id" => "http://adlnet.gov/expapi/verbs/initialized",
							"display" => array("en-US" => "initialized")
						),
						"interacted" => array(
							"id" => "http://adlnet.gov/expapi/verbs/interacted",
							"display" => array("en-US" => "interacted")
						),
						"launched" => array(
							"id" => "http://adlnet.gov/expapi/verbs/launched",
							"display" => array("en-US" => "launched")
						),
						"mastered" => array(
							"id" => "http://adlnet.gov/expapi/verbs/mastered",
							"display" => array("en-US" => "mastered")
						),
						"passed" => array(
							"id" => "http://adlnet.gov/expapi/verbs/passed",
							"display" => array("en-US" => "passed")
						),
						"preferred" => array(
							"id" => "http://adlnet.gov/expapi/verbs/preferred",
							"display" => array("en-US" => "preferred")
						),
						"progressed" => array(
							"id" => "http://adlnet.gov/expapi/verbs/progressed",
							"display" => array("en-US" => "progressed")
						),
						"registered" => array(
							"id" => "http://adlnet.gov/expapi/verbs/registered",
							"display" => array("en-US" => "registered")
						),
						"responded" => array(
							"id" => "http://adlnet.gov/expapi/verbs/responded",
							"display" => array("en-US" => "responded")
						),
						"resumed" => array(
							"id" => "http://adlnet.gov/expapi/verbs/resumed",
							"display" => array("en-US" => "resumed")
						),
						"scored" => array(
							"id" => "http://adlnet.gov/expapi/verbs/scored",
							"display" => array("en-US" => "scored")
						),
						"shared" => array(
							"id" => "http://adlnet.gov/expapi/verbs/shared",
							"display" => array("en-US" => "shared")
						),
						"suspended" => array(
							"id" => "http://adlnet.gov/expapi/verbs/suspended",
							"display" => array("en-US" => "suspended")
						),
						"terminated" => array(
							"id" => "http://adlnet.gov/expapi/verbs/terminated",
							"display" => array("en-US" => "terminated")
						),
						"voided" => array(
							"id" => "http://adlnet.gov/expapi/verbs/voided",
							"display" => array("en-US" => "voided")
						)
					);
					
					
	public function __construct(/* args */)
	{
		$this->verbs = apply_filters("grassblade_verbs", $this->verbs);
		// constructor code
	}
	public function add_verbs($verbs) {
		foreach ($verbs as $key => $verb) {
			if(!isset($this->verbs[$key]))
				$this->verbs[$key] = $verb;
		}
	}
	public function get_verb($verb) {
		if(isset($this->verbs[$verb]))
		return $this->verbs[$verb];
		else
		return null;
	}
}