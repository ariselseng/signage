<?php
if(isset($flexsliderFolder)){


if(is_dir("images/flexslider/resized/".$flexsliderFolder)){


$imagesEdited = filemtime("images/flexslider/resized/".$flexsliderFolder."/.");
function check_user_agent_flex($type = NULL){
        $user_agent = strtolower ( $_SERVER['HTTP_USER_AGENT'] );
        if ( $type == 'bot' ) {
                // matches popular bots
                if ( preg_match ( "/googlebot|adsbot|yahooseeker|yahoobot|msnbot|watchmouse|pingdom\.com|feedfetcher-google/", $user_agent ) ) {
                        return true;
                        // watchmouse|pingdom\.com are "uptime services"
                }
        } else if ( $type == 'browser' ) {
                // matches core browser types
                if ( preg_match ( "/mozilla\/|opera\//", $user_agent ) ) {
                        return true;
                }
        } else if ( $type == 'mobile' ) {
                // matches popular mobile devices that have small screens and/or touch inputs
                // mobile devices have regional trends; some of these will have varying popularity in Europe, Asia, and America
                // detailed demographics are unknown, and South America, the Pacific Islands, and Africa trends might not be represented, here
                if ( preg_match ( "/phone|iphone|itouch|ipod|symbian|android|htc_|htc-|palmos|blackberry|opera mini|iemobile|windows ce|nokia|fennec|hiptop|kindle|mot |mot-|webos\/|samsung|sonyericsson|^sie-|nintendo/", $user_agent ) ) {
                        // these are the most common
                        return true;
                } else if ( preg_match ( "/mobile|pda;|avantgo|eudoraweb|minimo|netfront|brew|teleca|lg;|lge |wap;| wap /", $user_agent ) ) {
                        // these are less common, and might not be worth checking
                        return true;
                }
        }
        return false;
}
$dataFile = "libraries/flexslider/editor/data.json";
$sliders = json_decode(file_get_contents($dataFile),true);
$data = $sliders['sliders'][$flexsliderFolder];
$slides = $data["slides"];
$sliderthumbs = $data["thumbnails"];
// var_dump($slides);
if(!isset($sliderthumbs)):
$sliderthumbs = true;
$imgSuffix = "";
endif;
$ismobile = check_user_agent_flex('mobile');
if($ismobile):
  $imgSuffix = "-crop";
  $sliderthumbs = false;
endif;


// file_put_contents("images/flexslider/src/frontpage/data.json", json_encode(array("thumbnails" => $sliderthumbs,"slides" => array($slides))));

?>
<link rel='stylesheet' id='mission-flexslider-css'  href='/libraries/flexslider/flexslider.css' type='text/css' media='all' />
<style>
li.right .flex-caption div{
  left:initial;
  right:0;
}
li.yellow h1{
  color: #000; background: #F5F219;
}
li.orange h1{
  color: #ffffff; background: #d96f4e;
}
li.blue h1{
  color: #fff; background: #428cbc;
}
li.green h1{
  color: #ffffff; background: #43bd3d;
}
.sliderLogo{display: none;}
#slider span {
    line-height: 24px;
    -webkit-font-smoothing: subpixel-antialiased;
    -moz-font-smoothing: subpixel-antialiased;
    font-smoothing: subpixel-antialiased;
  float: left;
}
.container:after {
    clear: both;
    content: " ";
    display: block;
    height: 0;
    visibility: hidden;
}
.carouselWrapper .container {
    padding-left: 15px;
}
.container {
    margin: 0 auto;
    padding: 0;
    position: relative;
    width: 960px;
}
.sliderLogo img {
    margin-top: 1px;
}
  
.flex-caption div.left.top {
    top: 9%;
}
.flex-caption div.left.bottom {
    top: initial;
    bottom:9%
}
.flex-caption div.left.middle {
    top: 30%;
}  

.flex-caption a:hover h1 {
    color: #fff !important;
    background: #555 !important;
}
a.smallTitle h1{line-height:1.2em;font-size:1.2em !important;padding:2px;}
body.no-js img.onlyjs{display:none !important;}
@media only screen and (min-width: 960px) and (max-width: 1300px) {
  .flex-caption h1 { font-size: 35px !important; line-height: 42px !important; }
  .caption-btn li a { font-size: 11px; }
  #slider span { font-size: 13px; }
  .flex-caption p { margin: 0 0 15px 0 !important;}
  .flex-caption-decription { padding: 10px !important; }
  .sliderLogo { top: 6% !important; }
}
@media only screen and (min-width: 960px) and (max-width: 1040px) {
  .flex-caption{max-width:860px;}

}
@media only screen and (min-width: 768px) and (max-width: 959px) {
  
  .container {width: 768px;}
  .caption-btn { display: none; }
  .caption-btn li a { font-size: 11px; }
  #slider p { font-size: 13px; }
  
  .flex-caption h1 { font-size: 35px !important; line-height: 42px !important; }
  #slider .flex-caption-decription { display: none !important; }
  #slider .flex-caption { max-width: 690px !important; }
  .flex-caption div {top: 50% !important;}
  .pageContent .container { margin-top: 0; }
}
@media only screen and (max-width: 767px) {
  .flex-caption div{top:initial;bottom:3%;}
  a.largeTitle{display:none;}
  .carouselWrapper {display: none;}
  .sliderWrapper{margin-bottom:0;}
  #slider .flex-caption p, #slider .caption-btn, #slider .flex-caption .flex-caption-decription, #slider .flex-direction-nav { display: none; }
  .sliderLogo {top: 33% !important;}
}
@media only screen and (min-width: 768px) {
  a.smallTitle{display:none;}
}
@media only screen and (min-width: 480px) and (max-width: 767px) {
  /*.flex-holder { z-index: -1; }*/
}

@media only screen and (max-width: 479px) {
  .sliderLogo {
    top: 20% !important;
  }
}
img {
-webkit-user-select: none;
-khtml-user-select: none;
-moz-user-select: none;
-o-user-select: none;
user-select: none;
}
</style>
<script type='text/javascript' src='/libraries/flexslider/picturefill.min.js'></script>
<script type='text/javascript' src='/libraries/flexslider/jquery.flexslider.js'></script>
<div class="sliderWrapper test2">
  <div id="slider" class="flexslider">
    <div class="sliderLogo">
      <a href="#">
        <img alt="" />
      </a>
    </div>

    <ul class="slides">
    <?php
    foreach ($slides as $key => $slide):
      $imgAlt = strip_tags(implode(" ",$slide["smallTitle"]));
      $imgKey = ($key + 1);

    ?>
      
    
      <li class="<?php echo $slide['slideClass'];?>">
        <a href="/arrangementer/sommerstevnet">
          <noscript><img src="/images/flexslider/resized/<?php echo $flexsliderFolder;?>/slide<?php echo $imgKey;?>-medium<?php echo $imgSuffix;?>.jpg?<?php echo $imagesEdited;?>?noscript&?<?php echo $imagesEdited;?>" alt="<?php echo $imgAlt;?>" /></noscript>
          <img class="onlyjs" test="123" srcset="/images/flexslider/resized/<?php echo $flexsliderFolder;?>/slide<?php echo $imgKey;?>-veryhigh<?php echo $imgSuffix;?>.jpg?<?php echo $imagesEdited;?> 2560w, /images/flexslider/resized/<?php echo $flexsliderFolder;?>/slide<?php echo $imgKey;?>-high<?php echo $imgSuffix;?>.jpg?<?php echo $imagesEdited;?> 1440w, /images/flexslider/resized/<?php echo $flexsliderFolder;?>/slide<?php echo $imgKey;?>-medium<?php echo $imgSuffix;?>.jpg?<?php echo $imagesEdited;?> 1024w,/images/flexslider/resized/<?php echo $flexsliderFolder;?>/slide<?php echo $imgKey;?>-low<?php echo $imgSuffix;?>.jpg?<?php echo $imagesEdited;?> 640w, /images/flexslider/resized/<?php echo $flexsliderFolder;?>/slide<?php echo $imgKey;?>-thumb<?php echo $imgSuffix;?>.jpg?<?php echo $imagesEdited;?> 220w" alt="<?php echo $imgAlt;?>">
        </a>
        <div class="flex-holder">
          <div class="flex-caption">
            <div>
              <a class="largeTitle" href="<?php echo $slide['link'];?>">
                <?php foreach ($slide["largeTitle"] as $part):?>            
                <h1><?php echo $part;?></h1><br />
                <?php endforeach;?>
              </a>
              <a class="smallTitle" href="<?php echo $slide['link'];?>">
                <?php foreach ($slide["smallTitle"] as $part):?>            
                <h1><?php echo $part;?></h1>
                <?php endforeach;?>
              </a>
              <span class="flex-caption-decription"><?php echo $slide['description'];?></span>
              <ul class="caption-btn">
                <li>
                  <a href="<?php echo $slide['link'];?>"><?php echo $slide['linkText'];?>  &rarr; </a>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </li>
<?php endforeach;?>
    </ul>

  </div>
  <?php if($sliderthumbs):?>
  <div class="carouselWrapper">
    <div class="container">
      <div class="sixteen columns">
        <div id="carousel" class="flexslider">
          <ul class="slides">
          <?php foreach ($slides as $key => $slide):
            $imgKey = ($key + 1);
          ?>
            <li class="four columns">
              <img src="/images/flexslider/resized/<?php echo $flexsliderFolder;?>/slide<?php echo $imgKey;?>-thumb-crop.jpg" alt="slide thumbnail" />
            </li>
          <?php endforeach;?>  
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>
<?php endif;?>
  <script type="text/javascript">
  jQuery("body").removeClass("no-js");
  jQuery(window).load(function() {
   <?php if($sliderthumbs):?> 
    jQuery('#carousel').flexslider({
      animation: "slide",
      controlNav: false,
      animationLoop: false,
      slideshow: false,
      itemWidth: 215,
      itemMargin: 20,
      asNavFor: '#slider'
    });
    <?php endif;?>
    jQuery('#slider').flexslider({
      animation: "slide",
      controlNav: false,
      animationLoop: true,
      slideshow: false,
      <?php if($sliderthumbs):?>
      sync: "#carousel",
      <?php endif;?>
      start: function(slider){
        jQuery('body').removeClass('loading');
      }
    });
  });
  </script>
<?php
  }
}
?>