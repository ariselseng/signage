<?php

$dataFile = "data.json";
$data = json_decode(file_get_contents($dataFile), true);
$returnarray = array();
$response_code = 200;

function getData($data){
	return $data;
}

function getSlider($data,$sliderid){
	if(isset($data['sliders'][$sliderid])){
		$data['sliders'][$sliderid]['updated'] = $data['updated'];
		return $data['sliders'][$sliderid];
	} else {
		http_response_code(404);
		return array("msg" => "No such slider.");
	}
}

function saveData($newdata, $dataFile){
	$newdata['updated'] = time(); //needs to on updated slider in the future
	if(file_put_contents($dataFile, json_encode($newdata))){
		return true;
	}else{
		return true;
	}
}

if (isset($_GET['t']) && isset($_GET['slider'])):
	if ((int)$_GET['t'] !== $data['updated']) {
		$response_code = 200;
	} else {
		$response_code = 304;
	}
	http_response_code($response_code);

elseif(isset($_GET['getData'])):
	$returnarray["data"] = getData($data);

elseif(isset($_GET['getSlider'])):
	$returnarray["data"] = getSlider($data, $_GET['getSlider']);

elseif(isset($_GET['saveData'])):
	$returnarray["status"] = saveData(json_decode($_POST['data'],true),$dataFile);
	$returnarray["data"] = json_decode($_POST['data'],true);
endif;

if(!isset($_GET['t'])){
	header("Content-Type:application/json");
	if(isset($_GET['callback'])):
		echo $_GET['callback'] . '(' . json_encode($returnarray) . ')';
	else:
		echo json_encode($returnarray);
	endif;
}


?>