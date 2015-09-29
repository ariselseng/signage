<?php
$data = array();
if (isset($_GET['res'])){
  $data['res'] = $_GET['res'];
} else{
  $data['res'] = 1920;
}
if (isset($_GET['slider'])) {
  $data['slider_name'] = $_GET['slider'];
} else {
  $data['slider_name'] = "default";
}
?>
<!DOCTYPE html>
<html lang="en-GB" manifest="manifest.php?<?php echo "slider=" . $data['slider_name'] . "&res=" . $data['res'] . "&.appcache";?>">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Signage</title>
    <link rel='stylesheet' id='mission-flexslider-css'  href='css/flexslider.css' type='text/css' media='all' />
    <link rel='stylesheet' id='mission-flexslider-css'  href='css/frontend/default.css' type='text/css' media='all' />
  </head>
  <body>

<div class="sliderWrapper test2" id="mainSlider">
</div>
<script id="slidesTemplate" type="text/template">




<div id="slider" class="flexslider">
    <div class="sliderLogo">
      <a href="#">
        <img alt="" />
      </a>
    </div>
    <ul class="slides" id="mainSlider">
      
      {{#slides}}
 <li class="{{{slideClass}}}" id="slide{{index}}" data-duration="{{duration}}000">
        <a href="#">
          <img class="onlyjs" src="backend/getImage.php?id={{file_id}}&res={{res}}">
        </a>
      </li>
{{/slides}}
     
          

    </ul>
  </div>
  {{#thumbnails}}
  <div class="carouselWrapper">
    <div class="container">
      <div class="sixteen columns">
        <div id="carousel" class="flexslider">
          <ul class="slides">
          {{#slides}}
            <li class="four columns">
              <img src="backend/getImage.php?id={{file_id}}&res=220x110" alt="slide thumbnail" />
            </li>
          {{/slides}}
            
          </ul>
        </div>
      </div>
    </div>
  </div>
  {{/thumbnails}}

</script>
<script type='text/javascript' src='libs/jquery-2.1.4.min.js'></script>
<script type='text/javascript' src='libs/mustache-2.1.3.min.js'></script>
<!-- <script type='text/javascript' src='picturefill.min.js'></script> -->
<script type='text/javascript' src='libs/jquery.flexslider.js'></script>
<script type='text/javascript' src='libs/rlite-1.1.min.js'></script>
<script type='text/javascript' src='js/main.js'></script>
</body>
</html>