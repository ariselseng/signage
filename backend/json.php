<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);
$returnarray = array("data" => array());
require "libs/RedBeanPHP/rb.php";
R::setup( 'sqlite:db.sqlite' );
function delTree($dir) {
   $files = array_diff(scandir($dir), array('.','..'));
    foreach ($files as $file) {
      (is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file");
    }
    return rmdir($dir);
  } 
function getUsers() {
	$users = R::findAll( 'users');
	return $users;
}
function getFiles() {
	$filesArray = array();
	$files = R::findAll( 'file' );
	foreach ($files as $key => $file) {
		$filesArray[$key] = array(
			"md5" => $file->md5,
			"title" => $file->title,
			"updated" => $file->updated,
			);
	}
	return $filesArray;
}
function getSliders() {
	$slidersArray = array();
	$sliders = R::findAll( 'slider' );
	foreach ($sliders as $key => $slider) {
		 // var_dump($sliders);
		$slidersArray[] = array(
			"id" => $key,
			"title" => $slider->title,
			"updated" => $slider->updated
			);
	}
	return $slidersArray;
}

function getSliderByTitle($id) {
	$slider = R::findOne('slider', 'title = ?', [$id]);
	if (is_null($slider)) {
		http_response_code(404);
		return null;
	}
	$sliderArray = $slider->export();
	$sliderArray['published'] = (bool)$slider->published;
	$sliderArray['thumbnails'] = (bool)$slider->thumbnails;
	$sliderArray['slides'] = array();
	foreach ($slider->xownSlideList as $key => $slide) {
		$sliderArray['slides'][] = R::findOne('slide', 'id = ?', [$key])->export();
	}
	return $sliderArray;
}
function getSliderById($id) {
	$slider = R::findOne('slider', 'id = ?', [$id]);
	if (is_null($slider)) {
		http_response_code(404);
		return null;
	}
	$sliderArray = $slider->export();
	$sliderArray['published'] = (bool)$slider->published;
	$sliderArray['thumbnails'] = (bool)$slider->thumbnails;
	$sliderArray['slides'] = array();
	foreach ($slider->xownSlideList as $key => $slide) {
		$sliderArray['slides'][] = R::findOne('slide', 'id = ?', [$key])->export();
	}
	return $sliderArray;
}

if (isset($_GET['getFiles'])) {
	$returnarray["data"]['files'] = getFiles();
}

if (isset($_GET['getSliders'])) {
	$returnarray["data"]['sliders'] = getSliders();
}

if (isset($_GET['getSlider'])) {
	if (is_numeric($_GET['getSlider'])){
		$returnarray["data"]['slider'] = getSliderById($_GET['getSlider']);
	} else {
		$returnarray["data"]['slider'] = getSliderByTitle($_GET['getSlider']);
	}
}
if (isset($_GET['getSliderByTitle'])) {
	$returnarray["data"]['slider'] = getSliderByTitle($_GET['getSliderByTitle']);
}

if (isset($_GET['uploadFiles'])) {

	$returnarray['newfiles'] = array();
	$returnarray['debug'] = $_FILES;
	$imgRoot = $_SERVER['DOCUMENT_ROOT'] . "/files/src";
	foreach ($_FILES['image']['tmp_name'] as $key => $newfile):
		$tmpimage = $newfile;
		$md5sum = md5_file($tmpimage);
		$filename_parts = pathinfo($_FILES['image']['name'][$key]);
		$title = $filename_parts['filename'];
		// $id = getNextFileID($data['files']);
		$updated = date("c");
		$newFile = R::dispense( 'file' );
		$newFile->md5 = $md5sum . ".jpg";
		$newFile->title = $title;
		$newFile->updated = $updated;
		if(move_uploaded_file($tmpimage, $imgRoot . "/" . $md5sum . ".jpg" )){
			$id = R::store($newFile);
			$returnarray['newfiles'][$id] = array("title" => $title, "updated" => $updated, 'md5' => $md5sum . ".jpg");
		}
	endforeach;
}

if (isset($_GET['saveFiles'])) {
	$newData = json_decode($_POST['data'], true);
	foreach ($newData['files'] as $key => $filedata) {
		// var_dump($key);
		$file = R::load( 'file', $key);
		// var_dump($file);
		// $file->id = $key;
		$file->updated = $filedata['updated'];
		$file->title = $filedata['title'];
		$file->md5 = $filedata['md5'];
		R::store($file);
	}
}	


if (isset($_GET['deleteFiles'])) {
	$newData = json_decode($_POST['data'], true);
	$returnarray['debug2'] = $newData;
	foreach ($newData as $key) {

		$file = R::load( 'file', $key);
		$fileIsUsed = (bool)R::count( 'slide', 'file_id = ?', [$file->id]);
		$realFileIsUsedByOtherFile = (R::count( 'file', 'md5 = ?', [$file->md5]) > 1);
		// foreach ($filesUsingSameRealFile as $key => $dupe) {
		// 	$amountOfSlidesUsingRealFile += R::count( 'slide', 'file_id = ?', [$dupe->id]);
		// }

		if (!$fileIsUsed && !$realFileIsUsedByOtherFile) {
			if(file_exists("../files/src/" . $file->md5)){
				unlink("../files/src/" . $file->md5);
			}
			if(file_exists("../files/resized/" . $file->md5)){
				$returnarray["debug4"] = "../files/resized/" . $file->md5;
				delTree("../files/resized/" . $file->md5);
			}
			R::trash( $file );
		} elseif (!$fileIsUsed) {
			R::trash( $file );
		} else {
			$returnarray['msg'] = "The file with id: " . $file->id . " is used. Not deleting.";
		}
	}
}

if (isset($_GET['deleteSliders'])) {
	$newData = json_decode($_POST['data'], true);
	$returnarray['debug2'] = $newData;
	foreach ($newData as $key) {
		$slider = R::load( 'slider', $key);
		foreach ($slider->xownSlideList as $key => $slide) {
			R::trash( $slide );
		}
		R::trash( $slider );
	}
}
if (isset($_GET['t']) && isset($_GET['slider'])) {
	$slider = getSliderById($_GET['slider']);
	if ($_GET['t'] !== $slider['updated']) {
		$response_code = 200;
	} else {
		$response_code = 304;
	}
	http_response_code($response_code);
}
if (isset($_GET['saveSlider'])) {
	$newData = json_decode($_POST['data'], true);
	if((isset($newData['slider']['orgId'])) && ($newData['slider']['orgId'] === "newSlider")):
		unset($newData['slider']['id']);
	endif;

	// if(($newData['slider']['orgId'] === "newSlider") && (isset($data['sliders'][$newData['slider']['id']]))){
	// 	$newData['slider']['id'] = $newData['slider']['id'] ."_". date("Ymd-His");
	// }

	// $newData['slider']['title'] = $newData['slider']['id'];
	// unset($newData['slider']['id']);
	$newData['slider']['_type'] = 'slider';
	// $newData['slider']['slides']['_type'] = 'slide';
	$slides = $newData['slider']['slides'];
	unset($newData['slider']['slides']);
	if($newData['slider']['orgId'] === "newSlider"){
		$newData['slider']['title'] = $newData['slider']['title'] . " copy";
	}
	$slider = R::dispense( $newData['slider'] );
	$slider->xownSlideList = array();
	foreach ($slides as $key => $value) {
		$value["_type"] = "slide";
		$slide = R::dispense($value);
		$slider->xownSlideList[] = $slide;
	}
	$id = R::store( $slider);
	// var_dump($newData['slider']);
	// $data['sliders'][$newData['slider']['id']]['updated'] = date("c");
	// unset($data['sliders'][$newData['slider']['id']]['orgId']);
	if ((int)$newData['slider']['orgId'] !== $id) {
		$returnarray['newSliderId'] = $id;
	}
	
}
if (!isset($_GET['t'])) {
	header("Content-Type:application/json");
}
echo json_encode($returnarray);