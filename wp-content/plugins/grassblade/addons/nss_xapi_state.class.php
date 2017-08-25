<?php

class NSS_XAPI_STATE extends NSS_XAPI {
	
	function __construct($endpoint = null, $user = null, $pass = null, $version = "0.95") {
		parent::__construct($endpoint, $user, $pass, $version);
		if(!empty($endpoint)) 
		{
			$this->endpoint = $endpoint;
			$this->state_url = $endpoint."activities/state";
		}		
	}
	
	function SendState($activityId, $agent, $stateId, $data, $registration = null) {
		grassblade_debug("sendstate called");
		if(empty($activityId) || empty($agent) || empty($stateId))
		{
			grassblade_debug("[NSS_XAPI_STATE::SendState] Empty Values: ".empty($activityId).'||'.empty($agent).'||'.empty($stateId));
			return "";
		}
			$auth = $this->auth;
			
			$url = $this->state_url."?stateId=".$stateId."&activityId=".rawurlencode($activityId)."&agent=".rawurlencode(json_encode($agent));
			if(!empty($registration))
			$url .= "&registration=".$registration;
			
			grassblade_debug("[NSS_XAPI_STATE::SendState] URL: ".$url);
			
			$version = $this->version;
			
			if(empty($auth) || empty($url))
			return false;
			
			$streamopt = array(
				'ssl' => array(
					'verify-peer' => false,
				),
				'http' => array(
					'method' => 'PUT',
					'ignore_errors' => true,
					'header' =>  array(
						'Authorization: '.$auth,
						'Content-Type: application/json',
						'Accept: application/json, */*; q=0.01',
						'X-Experience-API-Version: '.$version
					),
					'content' => $data,
				),
			);

			$context = stream_context_create($streamopt);
			$stream = fopen($url, 'rb', false, $context);
			$ret = stream_get_contents($stream);
			$meta = stream_get_meta_data($stream);
			if ($ret) {
				$ret = json_decode($ret);
			}
			return array($ret, $meta);
		
	}
	function GetState($activityId, $agent, $stateId, $registration = null) {

		if(empty($activityId) || empty($agent) || empty($stateId))
		{
			grassblade_debug("[NSS_XAPI_STATE::GetState] Empty Values: ".empty($activityId).'||'.empty($agent).'||'.empty($stateId));
			return;
		}
		
		$url = $this->state_url."?stateId=".$stateId."&activityId=".rawurlencode($activityId)."&agent=".rawurlencode(json_encode($agent));
		if(!empty($registration))
		$url .= "&registration=".$registration;
		
		grassblade_debug("[NSS_XAPI_STATE::GetState] URL: ".$url);
		return $this->GetCurl($url);
	}
}
