<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('max_execution_time', 90);
header("Content-Type: application/json");
$returnarray = array('newfiles' => array());
$imgRoot = $_SERVER['DOCUMENT_ROOT'] . "/files/src";


$returnarray['debug'] = $_FILES;
foreach ($_FILES['image']['tmp_name'] as $key => $newfile):
	$tmpimage = $newfile;
	$md5sum = md5_file($tmpimage);
	$filename_parts = pathinfo($_FILES['image']['name'][$key]);
	$title = $filename_parts['filename'];
	if(move_uploaded_file($tmpimage, $imgRoot . "/" . $md5sum . ".jpg" )){
		$returnarray['newfiles'][] = array("title" => $title, 'md5' => $md5sum . ".jpg");
	}
endforeach;
echo json_encode($returnarray);

exit;
