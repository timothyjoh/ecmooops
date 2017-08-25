<%
'Copyright © 2003-2009 Rustici Software, LLC  All Rights Reserved.

Option Explicit

Const strContentUrl = "indexAPI.html"		
Const blnLogCalls = false			'do we want to record all calls made to this page to a log for debugging

dim strAiccSid
dim strAiccUrl
dim strRelayPageUrl
dim strNewAiccUrl

dim strPostTo
dim objXMLHTTP
dim strReturn


'If there is no form post, launch the AICC course but substitute parameters in for this page
'If there is a form post, pass it on to the LMS

if Request.Form.Count = 0 then
	
	'log the initial redirect call
	if blnLogCalls then
		dim x
		dim fso
		dim ts
		set fso = server.CreateObject("scripting.filesystemobject")
		set ts = fso.OpenTextFile("c:\temp\relaylog.txt", 8, true)

		call ts.writeline ("______________________________")
		
		ts.writeline ("______________________________")
		ts.writeline ("Initial Request at " & now())
		ts.writeline ("______________________________")
		ts.writeline ("______________________________")
		
		for each x in Request.Form
			ts.writeline(x & "=" & Request.Form(x) & "    " & vbcrlf)
		next
		
		for each x in Request.Querystring
			ts.writeline(x & "=" & Request.Querystring(x) & "    " & vbcrlf)
		next
		
		set ts = nothing
		set fso = nothing
	end if


	'set AiccUrl to be this page with a querystring parameter for the real aicc url
	strRelayPageUrl = GetFullPageUrl()
	strAiccSid = Request.QueryString("AICC_SID")
	strAiccUrl = Request.QueryString("AICC_URL")
	
	strNewAiccUrl = strRelayPageUrl & "?RelayTo=" & strAiccUrl
	
	Response.Redirect strContentUrl & "?AICC_URL=" & Server.URLEncode(strNewAiccUrl) & "&AICC_SID=" & Server.URLEncode (strAiccSid)
	
else


	'log the relay call
	if blnLogCalls then
		set fso = server.CreateObject("scripting.filesystemobject")
		set ts = fso.OpenTextFile("c:\temp\relaylog.txt", 8, true)

		call ts.writeline ("______________________________")
		ts.writeline ("______________________________")
		ts.writeline ("Post Request at " & now())
		ts.writeline ("______________________________")
		ts.writeline ("______________________________")
		
		for each x in Request.Form
			ts.writeline(x & "=" & Request.Form(x) & "    " & vbcrlf)
		next
		
		for each x in Request.Querystring
			ts.writeline(x & "=" & Request.Querystring(x) & "    " & vbcrlf)
		next
		
		set ts = nothing
		set fso = nothing
	end if

	'post all the incoming form variables on to the LMS using the MSXML HTTP object
	'take the response from the LMS and send it back to the content by writing it to the output stream
	
	strPostTo = Request.QueryString("RelayTo")
	  
	Set objXMLHTTP = CreateObject("MSXML2.XMLHTTP")
		
	objXMLHTTP.Open "POST", strPostTo, False
	objXMLHTTP.setRequestHeader "Content-Type", "application/x-www-form-urlencoded"

	objXMLHTTP.Send RelayPostVariables()
		
	strReturn = objXMLHTTP.ResponseText

	Set objXMLHTTP = Nothing	
	
	Response.Write strReturn
	Response.End

end if


'forms the incoming form variables into an HTTP header format for re-posting
function RelayPostVariables()

	dim strPost
	dim item
	
	strPost = ""
	
	for each item in Request.Form
		if strPost <> "" then 
			strPost = strPost & "&"
		end if
		
		strPost = strPost & item & "=" & Server.URLEncode(Request.Form(item))
	next
	
	RelayPostVariables = strPost
	
end function

'gets the fully qualified URL for the current page
function GetFullPageUrl()

	dim strHttp
	dim strServer
	dim strPage
	
	if (Request.ServerVariables("HTTPS") = "off") then
		strHttp = "http://"
	else
		strHttp = "https://"
	end if 

	strServer = Request.ServerVariables("HTTP_HOST")
	strPage = Request.ServerVariables("SCRIPT_NAME")
	
	GetFullPageUrl = strHttp & strServer & strPage
	
end function
%>