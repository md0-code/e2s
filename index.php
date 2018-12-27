<?php

const app = 'E2S Web Interface';
const ver = '0.1.5';

$GLOBALS["conf"] = include 'config.php';
	session_start();

function d($input) {
	echo '<pre>'.print_r($input,TRUE).'</pre>';
	return '<pre>'.print_r($input,TRUE).'</pre>';
}

function getData($command) {
	$schema = $GLOBALS["conf"]["useHTTPS"] ? 'https' : 'http';
	$opts = array(
		'http' => array(
			'method'  => 'GET',
			'header' => 'Authorization: Basic '.base64_encode($GLOBALS["conf"]["boxUser"].':'.$GLOBALS["conf"]["boxPass"]),
		),
		'ssl' => array(
			'verify_peer' => false,
			'verify_peer_name' => false,
		)
	);
	$context  = stream_context_create($opts);
	$result = @file_get_contents($schema.'://'.$GLOBALS["conf"]["boxIP"].'/api/'.$command, false, $context);
	if ($result) return json_decode($result, true);
	else return false;
}

function inStandby() {
	$powerstate = getData('powerstate');
	return $powerstate["instandby"];
}

function isTranscoding() {
	$ffProc = explode("\"",shell_exec('tasklist /NH /FO csv /FI "IMAGENAME eq ffmpeg.exe'));
	if (isset($ffProc["3"])) return str_replace('"', "", $ffProc["3"]);
	else return false;
}

function stopStream() {
	if (shell_exec('taskkill /F /T /IM ffmpeg.*')) {
		array_map('unlink', glob(getcwd().'/stream/*.*'));
		return true;
	}
	else return false;
}

function startStream ($stream) {
	$command = 'cmd /C cd '.getcwd().'\stream && ..\bin\stream.bat '.$GLOBALS["conf"]["boxIP"].' '.$stream["sRef"].' '.$stream["res"].' '.$stream["vb"].' '.$stream["ab"];
//	echo 'Command: '.$command.'<br>'; #debug
	$wshShell = new COM("WScript.Shell");
	return $oExec = $wshShell->Run($command, 0, false);
}

if (isset($_POST["confBoxIP"])) {
	$GLOBALS["conf"]["boxIP"] = $_POST["confBoxIP"];
	$GLOBALS["conf"]["boxUser"] = $_POST["confBoxUser"];
	$GLOBALS["conf"]["boxPass"] = $_POST["confBoxPass"];
	if ($_POST["confE2sPass"] !== '' && $_POST["confE2sPass"] !== '######') $GLOBALS["conf"]["e2sPass"] = md5($_POST["confE2sPass"]);
	else {
		$GLOBALS["conf"]["e2sPass"] = '';
		if (isset($_SESSION["authenticated"])) unset($_SESSION["authenticated"]);
	}
	$GLOBALS["conf"]["useHTTPS"] = isset($_POST["confUseHTTPS"]) ? true : false;
	$GLOBALS["conf"]["autoplay"] =  isset($_POST["confAutoplay"]) ? true : false;
	$GLOBALS["conf"]["wakeupBox"] =  isset($_POST["confWakeupBox"]) ? true : false;
	$GLOBALS["conf"]["zapToService"] =  isset($_POST["confZapToService"]) ? true : false;
	$GLOBALS["conf"]["showServices"] =  isset($_POST["confShowServices"]) ? true : false;
	
	file_put_contents('config.php', '<?php return '.var_export($GLOBALS["conf"], true).'; ?>');
}

if (isset($_GET["settings"]) || !$GLOBALS["conf"]["boxIP"]) {
		require_once 'tmpl/settings.html';
		die();
}

if ($GLOBALS["conf"]["e2sPass"] !== '') {

	if (isset($_GET["logout"])) {
		unset($_SESSION["authenticated"]);
		echo (json_encode(true));
		die();
	}

	if (!isset($_SESSION["authenticated"])) {
		if (isset($_POST["password"])) {
			if (md5($_POST["password"]) == $GLOBALS["conf"]["e2sPass"]) {
				$_SESSION["authenticated"] = 1;
#				header('Location: '.$_SERVER['PHP_SELF']);
#				die();
			}
			else {
				$authMessage = 'Invalid password!';
				require_once 'tmpl/auth.html';
				die();
			}
		}
		else {
			require_once 'tmpl/auth.html';
			die();
		}
	}
}


if (isset($_GET["power"])) {
	if ($_GET["power"] == 1) $result = getData('powerstate?newstate=4');
	elseif ($_GET["power"] == 0) $result = getData('powerstate?newstate=5');
	die(json_encode($result));
}

if (isset($_GET["stop"])) {
	stopStream();
}

if (isset($_GET["sRef"]) && isset($_GET["res"]) && isset($_GET["vb"]) && isset($_GET["ab"])) {
	if (isTranscoding()) stopStream();
	if ($GLOBALS["conf"]["zapToService"] == true) getData('zap?sRef='.$_GET["sRef"]);
	$info = getData('about')["info"];
	foreach ($info["tuners"] as $tuner) {
		if ($tuner["live"]) {
			$active["service"] = $tuner["live"];
			$active["tuner"] = $tuner["name"];
		}
	}
	startStream($_GET);
	if (isset($active)) echo json_encode($active);
}
else {
	$info = getData('about')["info"];
	if (sizeof($info) == 0) {
		$error = 'Cannot access the receiver. Please check the IP address and/or credentials!';
		require_once 'tmpl/error.html';
		die();
	}
	foreach ($info["tuners"] as $tuner) {
		if ($tuner["live"]) {
			$active["service"] = $tuner["live"];
			$active["tuner"] = $tuner["name"];
		}
	}
	$autoplay = ($GLOBALS["conf"]["autoplay"] == true) ? 1 : 0;
	$inStandby = inStandby() == 1 ? 1 : 0;
	if ($inStandby == 1 && $GLOBALS["conf"]["wakeupBox"]) {
		getData('powerstate?newstate=4');
		$inStandby = 0;
	}
	if ($GLOBALS["conf"]["showServices"]) {
		$satellites = getData('getsatellites?stype=tv')["satellites"];
		foreach ($satellites as $sn => $satellite) {
			$satellites[$sn]["subservices"] = getData('getservices?sRef='.urlencode($satellite["service"]))["services"];
		}
	}
	$bouquets = getData('getallservices')["services"];
	$currentService = getData('about')["service"];

	require_once 'tmpl/main.html';
}

?>