<?php

include_once "../lib/lti_util.php";

function line_out($output) {
	echo(htmlent_utf8($output)."<br/>\n");
    flush();
}

function error_out($output) {
	echo('<span style="color:red"><strong>'.htmlent_utf8($output)."</strong></span><br/>\n");
    flush();
}

function success_out($output) {
	echo('<span style="color:green"><strong>'.htmlent_utf8($output)."</strong></span><br/>\n");
    flush();
}

function doTop() {
echo('<html>
<head>
  <title>Automatic Web Grading Tool</title>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<script language="javascript"> 
function dataToggle(divName) {
    var ele = document.getElementById(divName);
    if(ele.style.display == "block") {
        ele.style.display = "none";
    }
    else {
        ele.style.display = "block";
    }
} 
  //]]> 
</script>
</head>
<body style="font-family:sans-serif; background-color:#add8e6">
');
}

function togglePre($title, $html) {
    global $div_id;
    $div_id = $div_id + 1;
    echo('<strong>'.htmlent_utf8($title));
    echo(' (<a href="#" onclick="dataToggle('."'".$div_id."'".');return false;">Toggle</a>)</strong>'."\n");
    echo('<pre id="'.$div_id.'" style="display:none; border: solid 1px">'."\n");
    echo(htmlent_utf8($html));
    echo("</pre><br/>\n");
}

function sendGrade($grade) {
    try {
        return sendGradeInternal($grade);
	} catch(Exception $e) {
		$msg = "Grade Exception: ".$e->getMessage();
		error_log($msg);
        return $msg;
    } 
}

function sendGradeInternal($grade) {
	if ( ! isset($_SESSION['lti']) ) {
        return "Session not set up for grade return";
    }
	$lti = $_SESSION['lti'];
	if ( ! ( isset($lti['service']) && isset($lti['sourcedid']) &&
		isset($lti['key_key']) && isset($lti['secret']) ) ) {
		error_log('Session is missing required data');
        ob_start();
        $x = $lti;
        if ( isset($x['secret']) ) $x['secret'] = MD5($x['secret']);
        var_dump($x);
        $result = ob_get_clean();
        error_log($result);
        togglePre('Internal error - please send this to Chuck',$result);
        return "Missing required data";
    }

	$method="POST";
	$content_type = "application/xml";
	$sourcedid = htmlspecialchars($lti['sourcedid']);

	$operation = 'replaceResultRequest';
	$postBody = str_replace(
		array('SOURCEDID', 'GRADE', 'OPERATION','MESSAGE'),
		array($sourcedid, $grade.'', 'replaceResultRequest', uniqid()),
		getPOXGradeRequest());

	line_out('Sending grade to '.$lti['service']);

	togglePre("Grade API Request (debug)",$postBody);
    flush();
	$response = sendOAuthBodyPOST($method, $lti['service'], $lti['key_key'], $lti['secret'], $content_type, $postBody);
	global $LastOAuthBodyBaseString;
	$lbs = $LastOAuthBodyBaseString;
	togglePre("Grade API Response (debug)",$response);
    $status = "Failure to store grade";
	try {
		$retval = parseResponse($response);
		if ( isset($retval['imsx_codeMajor']) && $retval['imsx_codeMajor'] == 'success') {
            $status = true;
		} else if ( isset($retval['imsx_description']) ) {
            $status = $retval['imsx_description'];
        }
	} catch(Exception $e) {
		$status = $e->getMessage();
	}
    $note = $status;
    if ( $note == true ) $note = 'Success';
    error_log('Grade sent '.$grade.' for '.$lti['user_displayname'].' '.$note);
    return $status;
}

