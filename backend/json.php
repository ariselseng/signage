<?php
date_default_timezone_set("UTC");
$dataFile = "data.json";
$data = json_decode(file_get_contents($dataFile), true);
$returnarray = array("data" => array());
$response_code = 200;

function getNextFileID($files) {
	ksort($files);
	return end(array_keys($files)) + 1;
}

function getData($data){
	return $data;
}
function getSliders($sliders){
	$slidersList = array();
	foreach ($sliders as $key => $slider) {
		$slidersList[] = array("id" => $key, "updated" => $slider['updated']);
	}
	return $slidersList;
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
if(isset($_GET['getFiles'])):
	$returnarray["data"]['files'] = $data['files'];
endif;
if (isset($_GET['t']) && isset($_GET['slider']) && isset($data['sliders'][$_GET['slider']])):
	if ((int)$_GET['t'] !== $data['sliders'][$_GET['slider']]['updated']) {
		$response_code = 200;
	} else {
		$response_code = 304;
	}
	http_response_code($response_code);

elseif(isset($_GET['getData'])):
	$returnarray["data"] = getData($data);
elseif(isset($_GET['deleteSliders'])):
	$sliderIdsToDelete = json_decode($_POST['data'], true);
	$returnarray["debug"] = $sliderIdsToDelete;
	foreach ($sliderIdsToDelete as $key => $id) {
		if(isset($data['sliders'][$id])){
			unset($data['sliders'][$id]);
		}
	}
	if(count($sliderIdsToDelete) > 0):

		if(file_put_contents($dataFile, json_encode($data))){
			http_response_code(200);
			$returnarray['status'] = true;
		} else {
			http_response_code(500);
			$returnarray['status'] = false;
		}
	
	endif;
elseif(isset($_GET['saveSlider'])):
	$newData = json_decode($_POST['data'], true);
	if((isset($newData['slider']['orgId'])) && (strlen($newData['slider']['orgId']) > 0 ) && ($newData['slider']['orgId'] !== "newSlider")):
		unset($data['sliders'][$newData['slider']['orgId']]);
	endif;
	if(($newData['slider']['orgId'] === "newSlider") && (isset($data['sliders'][$newData['slider']['id']]))){
		$newData['slider']['id'] = $newData['slider']['id'] ."_". date("Ymd-His");
	}
	$returnarray['debug2'] = $newData['slider']['id'];
	$data['sliders'][$newData['slider']['id']] = $newData['slider'];
	// var_dump($newData['slider']);
	// $data['sliders'][$newData['slider']['id']]['updated'] = date("c");
	unset($data['sliders'][$newData['slider']['id']]['orgId']);
	if(file_put_contents($dataFile, json_encode($data))){
		http_response_code(200);
		$returnarray['status'] = true;
		if ($newData['slider']['orgId'] !== $newData['slider']['id']) {
			$returnarray['newSliderId'] = $newData['slider']['id'];
		}
	}else{
		http_response_code(500);
		$returnarray['status'] = false;
	}
elseif(isset($_GET['saveFiles'])):
	$newData = json_decode($_POST['data'], true);
	$data['files'] = $newData['files'];
	if(file_put_contents($dataFile, json_encode($data))){
		http_response_code(200);
		$returnarray['status'] = true;
	}else{
		http_response_code(500);
		$returnarray['status'] = false;
	}
elseif(isset($_GET['uploadFiles'])):
	$returnarray['newfiles'] = array();
	$returnarray['debug'] = $_FILES;
	$imgRoot = $_SERVER['DOCUMENT_ROOT'] . "/files/src";
	foreach ($_FILES['image']['tmp_name'] as $key => $newfile):
		$tmpimage = $newfile;
		$md5sum = md5_file($tmpimage);
		$filename_parts = pathinfo($_FILES['image']['name'][$key]);
		$title = $filename_parts['filename'];
		$id = getNextFileID($data['files']);
		$updated = date("c");
		if(move_uploaded_file($tmpimage, $imgRoot . "/" . $md5sum . ".jpg" )){
			$data['files'][$id] = array("title" => $title, "updated" => $updated, 'md5' => $md5sum . ".jpg");
			$returnarray['newfiles'][$id] = array("title" => $title, "updated" => $updated, 'md5' => $md5sum . ".jpg");
		}
	endforeach;
	if(file_put_contents($dataFile, json_encode($data))){
		http_response_code(200);
		$returnarray['status'] = true;
	}else{
		http_response_code(500);
		$returnarray['status'] = false;
	}

elseif(isset($_GET['getSlider'])):
	$returnarray["data"]['slider'] = getSlider($data, $_GET['getSlider']);
elseif(isset($_GET['getSliders'])):
	$returnarray["data"]['sliders'] = getSliders($data['sliders']);
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