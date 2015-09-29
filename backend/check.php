<?php
if (isset($_GET['slider']) && isset($_GET['t'])) {
	$dataFile = "../editor/data.json";
	$data = json_decode(file_get_contents($dataFile), true);
	if ((int)$_GET['t'] !== $data['updated']) {
		$response_code = 200;
	} else {
		$response_code = 304;
	}
} else{
	$response_code = 400;
}
http_response_code($response_code);
?>