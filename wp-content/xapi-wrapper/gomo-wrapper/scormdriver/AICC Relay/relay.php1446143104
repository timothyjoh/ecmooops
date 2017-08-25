<?php
$strContentUrl = "indexAPI.html";		
$blnLogCalls = false;			// do we want to record all calls made to this page to a log for debugging

$strAiccSid = "";
$strAiccUrl = "";
$strRelayPageUrl = "";
$strNewAiccUrl = "";

$strPostTo = "";
$objXMLHTTP = NULL;
$strReturn = "";


// If there is no form post, launch the AICC course but substitute parameters in for this page
// If there is a form post, pass it on to the LMS
if (count($_POST) == 0) {
	// log the initial redirect call
/*
	if ($blnLogCalls) {
		LogIt("Initial Request");
	}
*/

	// set AiccUrl to be this page with a querystring parameter for the real aicc url
	try {
		foreach ($_GET as $key => $value) {
			if (strtolower($key) == 'aicc_url') {
				$strAiccUrl = $value;
			}
		}
		if (!$strAiccUrl) {
			throw new Exception('No AICC URL received from LMS.');
		}
		
		$strRelayPageUrl = GetFullPageUrl();
		$strNewAiccUrl = $strRelayPageUrl . "?RelayTo=" . $strAiccUrl;
		
		$otherParams = "";
		foreach ($_GET as $key => $value) {
			if (strtolower($key) != 'aicc_url') {
				$otherParams .= "&$key=" .urlencode($value);
			}
		}
		header("Location: " . $strContentUrl . "?AICC_URL=" . urlencode($strNewAiccUrl) . $otherParams);
	}
	catch (Exception $e) {
		error_log($e->getMessage());
	}
} else {
	// log the relay call
/*
	if ($blnLogCalls) {
		LogIt("Post Request");
	}
*/

	// post all the incoming form variables using CURL
	// take the response from the LMS and send it back to the content by writing it to the output stream
	
	$strPostTo = $_GET['RelayTo'];
	
	// Re-post the data and then echo the response
	$request = curl_init($strPostTo);
	curl_setopt($request, CURLOPT_HEADER, TRUE);							
	curl_setopt($request, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); 	
	curl_setopt($request, CURLOPT_POST, TRUE);
	curl_setopt($request, CURLOPT_POSTFIELDS, RelayPostVariables());
	curl_setopt($request, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($request, CURLOPT_RETURNTRANSFER, TRUE);
	$strReturn = curl_exec($request);
	echo $strReturn;
}

// Log the transaction
/*
function LogIt($descrip) {
	$fp = fopen("../../_community_uploads_Z6jM31/relaylog.txt", "a");
	
	fwrite($fp, "______________________________\n");
	fwrite($fp, "______________________________\n");
	fwrite($fp, "$descrip at " . date("Y-m-d h:i:s") . "\n");
	fwrite($fp, "______________________________\n");
	fwrite($fp, "______________________________\n");
	
	foreach ($_POST as $key=>$value) {
		fwrite($fp, "POST: $key=$value\n");		
	}
	foreach ($_GET as $key=>$value) {
		fwrite($fp, "GET:  $key=$value\n");		
	}
	fclose($fp);
}
*/

// forms the incoming form variables into an HTTP header format for re-posting
function RelayPostVariables() {
	$strPost = "";
	
	foreach ($_POST as $key => $value) {
		if ($strPost != "") $strPost .= "&";
		
		$strPost .= $key . "=" . urlencode($value);
	}
	
	return $strPost;
}

// gets the fully qualified URL for the current page
function GetFullPageUrl() {
	if ($_SERVER['HTTPS'] == "") {
		$strHttp = "http://";
	} else {
		$strHttp = "https://";
	} 

	$strServer = $_SERVER['HTTP_HOST'];
	$strPage = $_SERVER['SCRIPT_NAME'];
	
	return $strHttp . $strServer . $strPage;
}
?>