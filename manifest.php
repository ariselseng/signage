<?php
header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() - (60 * 60)));
date_default_timezone_set("Europe/Oslo");
header("Content-Type: text/cache-manifest");

$dataFile = $_SERVER['DOCUMENT_ROOT'] . "/backend/data.json";
$data = array("res" => 1920, "manifest_files" => array(), "manifest_images" => array());
$data['version'] = filemtime($dataFile) . filemtime($_SERVER['DOCUMENT_ROOT'] . "/index.html") . filemtime($_SERVER['DOCUMENT_ROOT'] . "/js/main.js");
$content = json_decode(file_get_contents($dataFile), true);

if (isset($_GET['res'])){
	$data['res'] = $_GET['res'];
}
if (isset($_GET['slider'])) {
	$data['slider_name'] = $_GET['slider'];
} else {
	$data['slider_name'] = "default";
}
$data['slider_folder'] = "slides/resized/" . $data['slider_name'];
$data['slider_content'] = $content['sliders'][$data['slider_name']];
$data['slider_slides_length'] = count($data['slider_content']['slides']);

foreach ($data['slider_content']['slides'] as $key => $slide) {
	$data['manifest_images'][] = "backend/getImage.php?id=" . $slide['file_id'] . "&res=" . $data['res'];
}
// foreach
// 	// $time = strtotime($data['slider_content']['slides'][$i]['updated']);
// 	// $data['manifest_images'][] = $data['slider_folder'] . "/slide" .  ($i + 1) . "-" . $data['res'] . ".jpg" . "?t=" . $time;
// 	// $data['manifest_images'][] = $data['slider_folder'] . "/slide" .  ($i + 1) . "-thumb-crop.jpg" . "?t=" . $time;
// 	// $data['manifest_images'][] = $data['slider_folder'] . "/slide" .  ($i + 1) . "-" . $data['res'] . ".jpg";;
// 	// $data['manifest_images'][] = $data['slider_folder'] . "/slide" .  ($i + 1) . "-thumb-crop.jpg";
// }
$data['manifest_files'][] = "js/main.js";
$data['manifest_files'][] = "libs/jquery-2.1.4.min.js";
$data['manifest_files'][] = "libs/mustache-2.1.3.min.js";
$data['manifest_files'][] = "libs/rlite-1.1.min.js";
$data['manifest_files'][] = "libs/jquery.flexslider.js";
$data['manifest_files'][] = "css/frontend/default.css";
$data['manifest_files'][] = "css/flexslider.css";

?>
CACHE MANIFEST
# <?php echo $data['version'];?>


CACHE:
<?php
foreach ($data['manifest_files'] as $key => $path) {
	// echo $path . "?t=" .filemtime($path). PHP_EOL;
	echo $path . PHP_EOL;
}
foreach ($data['manifest_images'] as $key => $path) {
	echo $path . PHP_EOL;
}
echo "backend/json.php?getSlider=" . $data['slider_name'] . PHP_EOL;
?>

NETWORK:
*


FALLBACK:
