<?php
require_once(dirname(__FILE__)."/nss_xapi_verbs.class.php");
class NSS_XAPI {
	public $version = "0.95";
	public $endpoint;
	public $iron_endpoint;	// LFF -- new endpoint for Iron.io
	public $user;
	public $pass;
	public $auth;
	public $statement_url;
	
	public $actor = null;
	public $verb = null;
	public $result = null;
	public $context = null;
	public $object = null;
	public $parent = null;
	public $grouping = null;
	public $context_extensions = null;
	public $verbs = null;
	public $debug = false;
	
	// LFF -- Accept the new Iron.io endpoint in the c'tor params and set it in member data
	function __construct($endpoint = null, $user = null, $pass = null, $iron_endpoint = null, $version = "0.95") {
		if(!empty($endpoint)) 
		{
			$this->endpoint = $endpoint;
			$this->statement_url = $endpoint."statements";
		}
		if(!empty($user)) $this->user = $user;
		if(!empty($pass)) $this->pass = $pass;
		if(!empty($user) && !empty($pass)) $this->auth = $this->getBasicAuth($user, $pass);
		if(!empty($iron_endpoint)) $this->iron_endpoint = $iron_endpoint;

		$this->version = $version;
		$this->new_statement();
	}
	public function getBasicAuth($user, $pass)
	{
		return "Basic ".base64_encode(trim($user).":".trim($pass));
	}
	
	public function SendStatements($data)
	{		
		$data = $this->upgradeStatement($data);
		if(function_exists('curl_version')) {
			return $this->SendStatementsCurl($data);
		} else {
			return $this->SendStatementsFOpen($data);
		}
	}
	public function upgradeStatement($data) {
		$version = $this->version;
		if(empty($data["verb"])) {
			foreach ($data as $key => $statement) {
				$data[$key] = $this->upgradeStatement($statement);
			}
			return $data;
		}
		else
		{
			if(empty($data["version"]) && $version >= "1.0") {
				$data["version"] = (string) "1.0.0";
			}
			return $data;
		}
	}
	public function SendStatementsFOpen($data)
	{
		$auth = $this->auth;
		$url = $this->statement_url;
		$version = $this->version;
		
		if(empty($auth) || empty($url))
		return false;
		
		$streamopt = array(
			'ssl' => array(
				'verify-peer' => false,
			),
			'http' => array(
				'method' => 'POST',
				'ignore_errors' => true,
				'header' =>  array(
					'Authorization: '.$auth,
					'Content-Type: application/json',
					'Accept: application/json, */*; q=0.01',
					'X-Experience-API-Version: '.$version
				),
				'content' => json_encode($data),
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
	function SendStatementsCurl($data) {
		$auth = $this->auth;
		$url = $this->statement_url;
		$version = $this->version;	 
		$username = $this->user;	 
		$password = $this->pass;	 
		$content = json_encode($data);
		
		$ch = curl_init ();
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch,CURLOPT_USERPWD,"$username:$password");
		curl_setopt($ch,CURLOPT_USERAGENT,$_SERVER['HTTP_USER_AGENT']);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_TIMEOUT, 15); //times out after 15s
		//curl_setopt($ch,CURLOPT_FOLLOWLOCATION,1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		  	'Authorization: '.$auth,
		  	'Content-Type: application/json',
		  	'Accept: application/json, */*; q=0.01',
		  	'X-Experience-API-Version: '.$version
		  	));		 
		if (!empty($_SERVER['HTTP_REFERER']))
			curl_setopt($ch,CURLOPT_REFERER, $_SERVER['HTTP_REFERER']);

		curl_setopt($ch,CURLOPT_POST,1);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$content);	
		//error_log("SENDING TO LRS endpont ".$url." U/P: $username:$password AUTH $auth");	 
		$result = curl_exec ($ch);
		if(curl_errno($ch)) {
			return array('error' => curl_error($ch));
		}

		// LFF -- Send same message to Iron.io if the endpoint is set
		if(!empty($this->iron_endpoint)) {
			//error_log("SENDING TO IRON.IO endpont ".$this->iron_endpoint);
			curl_setopt($ch,CURLOPT_URL, $this->iron_endpoint);	
			$iron_res = curl_exec ($ch);
			if ( curl_errno($iron_res)) {
				error_log("Curl to Iron.io FAILED: ".curl_error($ch));
			}
		}

		curl_close($ch); 

		return $result;
	}
	function GetStatements($filters = array(), $count = 1000000000){
		$url = $this->statement_url;
		$filter = "";
		//$filters['limit'] = isset($filters['limit'])? $filters['limit']:($count < 200? $count:200);
		
		if(is_array($filters))
		foreach($filters as $k=>$v) {
			$filter .= (empty($filter)? "?":"&");
			if(!is_string($v))
			{
				//$v = str_replace("\/", "/", json_encode($v));
				$v = json_encode($v);
				$filter .= $k."=".urlencode($v);
			}
			else
			$filter .= $k."=".urlencode($v);
		}
		$url .= $filter;
		$return = array('statements' => array());
		for ($i=1; $i<=$count; $i++)
		  {
			$returned_content = $this->GetCurl($url);
			if(is_string($returned_content))
			$json = json_decode($returned_content);
			else {
			$this->debug($returned_content);
			break;
			}

			$returned_array = (array)$json;
			//print_r($json);
			//print_r($returned_array);
			if(empty($returned_array['statements']))
			$returned_array['statements'] = array();
			
			$more = !empty($returned_array['more'])? $returned_array['more']:"";
			$return['statements'] = array_merge($return['statements'], $returned_array['statements']);
			$return['more'] = $more;
			$this->debug(count($returned_array['statements'])." Total:". count($return['statements'])." More:".$more." URL:".$url);
			
			if(count($return['statements']) >= $count)
			break;
			
			if(!empty($more)){
				$parsed_url = parse_url($url);
				$url = $parsed_url['scheme']."://".$parsed_url['host'].$more;
				$this->debug($url);
			}
			else{
				break;
			}
		  }
		  return $return;
	}

	function GetCurl($url){
		$ch = curl_init();
		$timeout = 15;
		$version = $this->version;	 
		$username = $this->user;	 
		$password = $this->pass;
		$auth = $this->auth;
		
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
						'Authorization: '.$auth,
						'Content-Type: application/json',
						'Accept: application/json, */*; q=0.01',
						'X-Experience-API-Version: '.$version
					));		 
		$data = curl_exec($ch);
		if(curl_errno($ch))
		{
				$data = array('error' => curl_error($ch));
		}		
		curl_close($ch);
		return $data;
	}	
	public function hasError($retVal)
	{
		if((!empty($retVal[1]) && !empty($retVal[1]['wrapper_data']) && !empty($retVal[1]['wrapper_data'][0]) && $retVal[1]['wrapper_data'][0] == "HTTP/1.1 401 Unauthorized")   || is_array($retVal) && !empty($retVal['error']))
			return 1; 
		
		return 0;
	}

	public function set_actor_by_object($actor){
		if(empty($actor))
		return null;
		
		$this->actor = $actor;
		return $this->actor;
	}	
	public function set_actor($name, $email, $version = "0.95"){
		if(empty($name) || empty($email))
		return null;
		
		if($version == "0.90" || $version == "0.9")
		$this->actor = array("name" => array($name), 
							"mbox" => array("mailto:".$email),
							'objectType' => 'Agent');
		else
		$this->actor = array("name" => $name, 
							"mbox" => "mailto:".$email,
							'objectType' => 'Agent');
		
		return $this->actor;
	}
	public function set_verb($verb) {
		if(empty($verb))
			return null;
			
		if(!is_string($verb))
			$this->verb = $verb;
		else
		{
			$this->verb = $this->load_verb($verb);
		}
		return $this->verb;
	}
	
	public function build_object($id, $name, $description, $type = null, $objectType = 'Activity') {
		if(!is_array($name))
		$name = array ('en-US' => $name);
		
		if(!is_array($description))
		$description = array ('en-US' => $description);
		
		
		$object  = array(	'id' => $id,
					'definition' => array(
						'name' => $name,
						'description' => $description,
						'type' => $type
					),
				'objectType' => $objectType
				);
		foreach($object["definition"] as $key => $val) {
			if(empty($val))
				unset($object["definition"][$key]);
		}
		return $object;
	}
	public function set_parent($id, $name, $description, $type, $objectType = 'Activity') {
		$this->{'parent'} = $this->build_object($id, $name, $description, $type, $objectType);
		return $this->{'parent'};
	}
	public function set_parent_by_object($parent) {
		if(is_array($parent))
		$this->{'parent'} = $parent;
		return $this->{'parent'};
	}
	public function set_grouping($id, $name, $description, $type, $objectType = 'Activity') {
		$this->{'grouping'} = $this->build_object($id, $name, $description, $type, $objectType);
		return $this->{'grouping'};
	}
	public function set_grouping_object($grouping) {
		if(is_array($grouping))
		$this->{'grouping'} = $grouping;
		return $this->{'grouping'};
	}
	public function set_context_extensions($context_extensions) {
		if(is_array($context_extensions))
		$this->context_extensions = $context_extensions;
		return $this->context_extensions;
	}	
	public function set_object($id, $name, $description, $type, $objectType = 'Activity') {
		$this->{'object'} = $this->build_object($id, $name, $description, $type, $objectType);
		return $this->{'object'};
	}
	public function set_object_by_object($object) {
		if(is_array($object))
		$this->{'object'} = $object;
		return $this->{'object'};
	}	
	public function set_grouping_by_object($grouping) {
		if(is_array($grouping))
		$this->{'grouping'} = $grouping;
		return $this->{'grouping'};
	}	
	public function build_context($context = null,$enrollmentKey) {
		if(is_array($context))
		$this->context = $context;
		else
		{
			error_log("TinCan xAPI build_context ->");

			if(!empty($this->{'grouping'}) || !empty($this->{'parent'})) {
				$this->context['contextActivities'] = array ();
				if(!empty($this->{'parent'}))
				$this->context['contextActivities']['parent'] = $this->{'parent'};
				
				if(!empty($this->{'grouping'})) {
					$this->context['contextActivities']['grouping'] = $this->{'grouping'};
					$this->context['contextActivities']['grouping']['definition']['extensions'] = array();
					$this->context['contextActivities']['grouping']['definition']['extensions']['key-progress'] = get_key_curriculum_stats( $enrollmentKey );
				}
			}

			$this->context['registration'] = $enrollmentKey;
			error_log( "context: " . json_encode($this->context));

			if(!empty($this->context_extensions)) {
				$this->context['extensions'] = $this->context_extensions;
			}
		}
		return $this->context;
	}
	public function set_result_by_object($result) {
		if(is_array($result))
		$this->{'result'} = $result;
		return $this->{'result'};
	}	
	public function load_verb($verb) {
	
		if(!class_exists('NSS_XAPI_Verbs'))
		return null;
		
		if(is_null($this->verbs))
		$this->verbs = new NSS_XAPI_Verbs();
		
		return $this->verbs->get_verb($verb);
	}
	public function build_statement($enrollmentKey = 'NOT-A-REAL-KEY') {
	
		if(empty($this->context))
		$this->build_context(null, $enrollmentKey);	
		
		if(empty($this->actor) || empty($this->verb) || empty($this->verb) || empty($this->{'object'}) || empty($this->context) )
		return;
		
		$statement = array();
		$statement['actor'] = $this->actor;
		$statement['verb'] = $this->verb;
		
		if(!empty($this->result))
		$statement['result'] = $this->result;
		
		$statement['object'] = $this->{'object'};
		$statement['context'] = $this->context;
		
		$this->statement = $statement;
		$this->statements[] = $statement;
		return $statement;
	}
	public function new_statement() {
		$this->actor = null;
		$this->verb = null;
		$this->result = null;
		$this->{'parent'} = null;
		$this->{'grouping'} = null;
		$this->context = null;
		$this->{'object'} = null;
	}
	public function json_print($json) {
		$json = str_replace('\\/', '/',$json);
		$result      = '';
		$pos         = 0;
		$strLen      = strlen($json);
		$indentStr   = '  ';
		$newLine     = "\n";
		$prevChar    = '';
		$outOfQuotes = true;

		for ($i=0; $i<=$strLen; $i++) {

			// Grab the next character in the string.
			$char = substr($json, $i, 1);

			// Are we inside a quoted string?
			if ($char == '"' && $prevChar != '\\') {
				$outOfQuotes = !$outOfQuotes;

			// If this character is the end of an element,
			// output a new line and indent the next line.
			} else if(($char == '}' || $char == ']') && $outOfQuotes) {
				$result .= $newLine;
				$pos --;
				for ($j=0; $j<$pos; $j++) {
					$result .= $indentStr;
				}
			}

			// Add the character to the result string.
			$result .= $char;

			// If the last character was the beginning of an element,
			// output a new line and indent the next line.
			if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
				$result .= $newLine;
				if ($char == '{' || $char == '[') {
					$pos ++;
				}

				for ($j = 0; $j < $pos; $j++) {
					$result .= $indentStr;
				}
			}

			$prevChar = $char;
		}

		return $result;
	}
	public function gen_uuid() {
		return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			// 32 bits for "time_low"
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

			// 16 bits for "time_mid"
			mt_rand( 0, 0xffff ),

			// 16 bits for "time_hi_and_version",
			// four most significant bits holds version number 4
			mt_rand( 0, 0x0fff ) | 0x4000,

			// 16 bits, 8 bits for "clk_seq_hi_res",
			// 8 bits for "clk_seq_low",
			// two most significant bits holds zero and one for variant DCE1.1
			mt_rand( 0, 0x3fff ) | 0x8000,

			// 48 bits for "node"
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
		);
	}
	public function debug($msg) {
		$original_log_errors = ini_get('log_errors');
		$original_error_log = ini_get('error_log');
		ini_set('log_errors', true);
		ini_set('error_log', dirname(__FILE__).DIRECTORY_SEPARATOR.'debug.log');
		
		global $ld_sf_processing_id;
		if(empty($ld_sf_processing_id))
		$ld_sf_processing_id	= time();
		
		if(isset($_GET['debug']) || !empty($this->debug))
		error_log("[$ld_sf_processing_id] ".print_r($msg, true)); //Comment This line to stop logging debug messages.
		
		ini_set('log_errors', $original_log_errors);
		ini_set('error_log', $original_error_log);		
	}	
}

