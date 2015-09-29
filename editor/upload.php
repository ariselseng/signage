<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('max_execution_time', 90);
$root = $_SERVER['DOCUMENT_ROOT'];
// $root = dirname(dirname(dirname(getcwd())));

$imgRoot = $root . "/slides/src";
$resizedImgRoot = $root . "/slides/resized";
// $defaultResolutions = array(
// 	"veryhigh" => array(2560,array(1440,1280)),
// 	"high" => array(1440,array(800,720)),
// 	"medium" => array(1024,array(576,512)),
// 	"low" => array(640,array(360,320)),
// 	"thumb" => array(220,array(124,110)),
// 	);
$defaultResolutions = array(
	"1920" => array(1920,array(1080,720)),
	"1280" => array(1280,array(720,480)),
	"640" => array(640,array(360,320)),
	"thumb" => array(220,array(124,110)),

	);
function resizeAndCrop($originalImage,$newImage,$newWidth,$newHeight){
	var_dump($newImage);
	$im = new imagick($originalImage);
	$im->setResourceLimit(6, 1);
	$im->setImageCompression(imagick::COMPRESSION_JPEG);
	$im->setImageCompressionQuality(75);
	$im->cropThumbnailImage($newWidth,$newHeight);
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
$rootFolder = $_SERVER['DOCUMENT_ROOT'];
$slider = $_POST['slider'];
$index = $_POST['index'] + 1;
$sliderFolder =  $rootFolder . "/slides/src/" . $slider;

if(!is_dir($sliderFolder)){
	mkdir($sliderFolder, 0755,true);
}

$tmpimage = $_FILES['image']['tmp_name'];
$destinationimage = $sliderFolder . "/slide" . $index . ".jpg" ;
var_dump($slider);
var_dump($index);

if(move_uploaded_file($tmpimage, $sliderFolder . "/slide" . $index . ".jpg" )){

	$file = pathinfo($destinationimage);
	
	// $file = $file['basename'];
	$filename = $file['filename'];
	foreach ($defaultResolutions as $key => $res) {
		resizeAndCrop($destinationimage,$resizedImgRoot . "/" .$slider. "/" . $filename . "-" . $key . ".jpg",$res[0],$res[1][0]);
		resizeAndCrop($destinationimage,$resizedImgRoot . "/" .$slider. "/" . $filename . "-" . $key . "-crop.jpg",$res[0],$res[1][1]);
	}
}




// resizeAndCrop("/home/tbve/beta.tbve.no/www/images/flexslider/src/frontpage/slide04-rune.jpg","/home/tbve/beta.tbve.no/www/cache/ari/test.jpg",2560,720);
// $scanned_directory = array_diff(scandir($imgRoot), array('..', '.'));
// foreach ($scanned_directory as $slideDir) {
// 	// print_r($imgRoot . "/" . $slideDir. PHP_EOL);
// 	$images = glob($imgRoot . "/" . $slideDir . "/*.jpg");
// 	foreach ($images as $image) {

// 		$file = basename($image);
// 		$filename = basename($image, ".jpg"); 
// 		foreach ($defaultResolutions as $key => $res) {
// 			// sleep(1000);
// 			resizeAndCrop($image,$resizedImgRoot . "/" .$slideDir. "/" . $filename . "-" . $key . ".jpg",$res[0],$res[1][0]);
// 			resizeAndCrop($image,$resizedImgRoot . "/" .$slideDir. "/" . $filename . "-" . $key . "-crop.jpg",$res[0],$res[1][1]);

// 		}
// 		print_r( "finished making " . $image . PHP_EOL);
	
// 	}
// }
exit;
