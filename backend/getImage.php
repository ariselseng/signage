<?php
$dataFile = $_SERVER['DOCUMENT_ROOT'] . "/backend/data.json";
$data = json_decode(file_get_contents($dataFile), true);
$filesDir = $_SERVER['DOCUMENT_ROOT'] . "/files";
$srcFilesDir = $filesDir . "/src";
$resizedDir = $filesDir . "/resized";
function resizeAndCrop($originalImage,$newImage,$newWidth,$newHeight = 0){
	$im = new imagick($originalImage);
	$im->setResourceLimit(6, 1);
	$im->setImageCompression(imagick::COMPRESSION_JPEG);
	$im->setImageCompressionQuality(80);
	if($height == 0) {
		$im->scaleImage($newWidth,$newHeight);
	} else {
		$im->cropThumbnailImage($newWidth,$newHeight);
	}
	// $im->normalizeImage();
	// $im->unsharpMaskImage(0 , 0.5 , 1 , 0.05); 
	$outputDir = dirname($newImage);

	if(!is_dir($outputDir)){
		mkdir($outputDir, 0755,true);
	}
	if($im->writeImage($newImage)):
		$success = true;
	else:
		$success = false;
	endif;
	$im->clear();
	return $success;
}

if (isset($_GET['id']) && isset($_GET['res'])) {
	list($width, $height) = explode("x", $_GET['res']);
	if(is_null($height)){
		$height = 0;
	}
	if(isset($data['files'][$_GET['id']])):
		$srcFileSum = $data['files'][$_GET['id']]['md5'];
		$wantedFile = $resizedDir . "/" . $srcFileSum . "/" . $_GET['res'] . ".jpg";
		$srcFile = $srcFilesDir . "/" . $srcFileSum;
	else:
		$srcFile = $filesDir . "/static/fallback.jpg";
		$wantedFile = $resizedDir . "/fallback.jpg/" . $_GET['res'] . ".jpg";
	endif;
	if (!file_exists($wantedFile) && file_exists($srcFile)) {
		$resized = resizeAndCrop($srcFile, $wantedFile, $width, $height);
	}
	header("Cache-Control: max-age=0, must-revalidate");
	header("Content-Type: image/jpeg");
	header("Last-Modified: ".gmdate("D, d M Y H:i:s", filemtime($wantedFile))." GMT"); 
    header("Etag: " . $_GET['id']);
    //get the last-modified-date of this very file
	$lastModified=filemtime($wantedFile);
	//get a unique hash of this file (etag)
	$etagFile = md5_file($wantedFile);
	//get the HTTP_IF_MODIFIED_SINCE header if set
	$ifModifiedSince=(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? $_SERVER['HTTP_IF_MODIFIED_SINCE'] : false);
	//get the HTTP_IF_NONE_MATCH header if set (etag: unique file hash)
	$etagHeader=(isset($_SERVER['HTTP_IF_NONE_MATCH']) ? trim($_SERVER['HTTP_IF_NONE_MATCH']) : false);

	//set last-modified header
	header("Last-Modified: ".gmdate("D, d M Y H:i:s", $lastModified)." GMT");
	//set etag-header
	header("Etag: $etagFile");
	//make sure caching is turned on
	// header('Cache-Control: public');

	//check if page has changed. If not, send 304 and exit
	if (@strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE'])==$lastModified || $etagHeader == $etagFile)
	{
	       header("HTTP/1.1 304 Not Modified");
	       exit;
	}else {
		echo file_get_contents($wantedFile);
		
	}
}
?>

