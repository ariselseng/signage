/*global
$, window, Mustache, Rlite, alert, confirm, signageViewerApp
*/
var r = new Rlite();
// Default route
r.add('params', function (r) {
  var data = {slider_id: "default", res: 1920, transition: "slide", animationSpeed: 2000};
  if (r.params.slider !== undefined) {
    data.slider_id = r.params.slider;
  }
  if (r.params.transition !== undefined) {
    data.transition = r.params.transition;
  }

  if (r.params.res !== undefined) {
    data.res = r.params.res;
  }
  if (r.params.animationSpeed !== undefined) {
    data.animationSpeed = r.params.animationSpeed;
  }
  signageViewerApp.navigate(data);
});

var signageViewerApp = (function () {
  var self = {};
  var templates = {};
  var lastUpdate = new Date();
  var lastCacheUpdate = new Date();
  var slider;
  var retrieveTemplates = function () {
    templates.slides = $("#slidesTemplate").html();

  };

  var getBasePath = function () {
    return "";
  };

  var retrieveData = function (sliderId, callback) {
    $.ajax({
      async: true,
      url: getBasePath() + 'backend/json.php?getSlider=' + sliderId,
      type: 'GET'
    }).done(function (data) {
      slider = data.data.slider;
      if (callback !== undefined) {
        callback()
      }
    });

  };

  self.getData = function () {
    return slider.data;
  };
  self.reload = function () {
    // window.applicationCache.swapCache();
    console.log("UPDATE: reloading...");
    $("body").fadeOut(700);
    window.location.reload(true);
  };
  self.checkForUpdate = function (force) {
    var now = new Date();
    if (now.getTime() - lastUpdate.getTime() >=  30 * 1000 || force) {
      console.log("checkForUpdate, last update: " + (now.getTime() - lastUpdate.getTime()));
      lastUpdate = new Date();
      $.ajax({
        async: true,
        url: getBasePath() + 'backend/json.php?slider=' + slider.id + '&t=' + new Date(slider.updated).getTime(),
        complete: function (e) {
          if (e.status === 200) {
            self.updateCache(true);
          }
        }
      });
    }

  };

  self.updateCache = function (force) {
    var now = new Date();
    if (now.getTime() - lastCacheUpdate.getTime() >= 2 * 60 * 1000 || force) {
      if (window.applicationCache.status !== window.applicationCache.UNCACHED) {
        lastCacheUpdate = new Date();
        window.applicationCache.update();
      }
    }
  };
  var loadContent = function (params) {
    if (params.transition === "none") {
      params.transition = "fade";
      params.animationSpeed = 2000;
    }
    var i = 0;
    var starterSlide = 0;
    if (slider.slides.length > 0) {
      if (window.localStorage !== undefined && window.localStorage !== null) {
        starterSlide = JSON.parse(window.localStorage.getItem("currentSlide-" + params.slider_id));
        if (starterSlide === null) {
          starterSlide = 0;
        } else if (!slider.slides.hasOwnProperty(starterSlide)) {
          starterSlide = 0;
        }
      }
      var duration = slider.slides[starterSlide].duration * 1000;
      for (i = 0; i < slider.slides.length; i++) {
        slider.slides[i].index = i + 1;
      }
      var markup = Mustache.to_html(templates.slides, {
        slides: slider.slides,
        slider_id: params.slider_id,
        res: params.res,
        thumbnails: slider.thumbnails,
        getTime: function () {
          return new Date(this.updated).getTime();
        },
        largeTitleInLines: function () {
          var array = [];
          for (i = 0; i < this.largeTitle.length; i++) {
            array.push({line: this.largeTitle[i]});
          }
          return array;
        }
      });
      if (slider.published) {
        $("#mainSlider").html(markup);
        if (slider.thumbnails) {
          $('#carousel').flexslider({
            animation: "slide",
            controlNav: false,
            animationLoop: false,
            slideshow: false,
            itemWidth: 215,
            itemMargin: 20,
            asNavFor: '#slider',
            useCSS: true,

          });
          $('#slider').flexslider({
            animation: params.transition,
            animationSpeed: params.animationSpeed,
            controlNav: false,
            animationLoop: true,
            startAt: starterSlide,
            easing: "linear",
            slideshowSpeed: duration,
            slideshow: true,
            sync: '#carousel',
            useCSS: true,
            pauseOnAction: false,
            start: function () {
              $('body').removeClass('loading');
            },
            after: function (slider) {
              // clears the interval
              slider.stop();
              if (window.localStorage !== undefined && window.localStorage !== null) {
                window.localStorage.setItem('currentSlide-' + params.slider_id, slider.currentSlide);
              }
              self.checkForUpdate();
              // grab the duration to show this slide
              slider.vars.slideshowSpeed = $(slider.slides[slider.currentSlide]).data('duration');
              // start the interval
              slider.play();
            },
            end: function () {
              if (window.localStorage !== undefined && window.localStorage !== null) {
                window.localStorage.setItem('currentSlide-' + params.slider_id, 0);
              }
            }
          });
        } else {
          $('#slider').flexslider({
            animation: params.transition,
            animationSpeed: params.animationSpeed,
            controlNav: false,
            startAt: starterSlide,
            easing: "linear",
            initDelay: 1000,
            animationLoop: true,
            slideshowSpeed: duration,
            slideshow: true,
            useCSS: true,
            pauseOnAction: false,
            start: function () {
              $('body').removeClass('loading');
            },
            after: function (slider) {
              // clears the interval
              slider.stop();
              self.checkForUpdate();
              if (window.localStorage !== undefined && window.localStorage !== null) {
                window.localStorage.setItem('currentSlide-' + params.slider_id, slider.currentSlide);
              }
              // grab the duration to show this slide
              slider.vars.slideshowSpeed = $(slider.slides[slider.currentSlide]).data('duration');
              // start the interval
              slider.play();
            },
            end: function () {
              if (window.localStorage !== undefined && window.localStorage !== null) {
                window.localStorage.setItem('currentSlide-' + params.slider_id, 0);
              }
            }

          });
        }
        window.flexSlider = $('#slider').data('flexslider');
        setTimeout(function () {
            if (!flexSlider.playing) {
              $('#slider').flexslider("pause")
              $('#slider').flexslider("play")
            }
        }, 2500)
      }
    }
  };
  self.navigate = function (params) {
    retrieveData(params.slider_id, function() {
      if (params.transition === "none") {
        $("body").show()
      } else {
        $("body").fadeIn()
        console.log("transition: " + params.transition)
      }
      loadContent(params);
    });
  };
  self.processPath = function () {
    r.run("params" + window.location.search);
  };
  self.start = function () {
    retrieveTemplates();
    self.processPath();
    // $("body").fadeIn();
    window.applicationCache.removeEventListener('noupdate', self.start);
    setTimeout(function () {
      signageViewerApp.checkForUpdate();
    }, 10 * 1000);

  };
  return self;

}());
$("body").removeClass("no-js");
if (window.applicationCache !== undefined) {
  if (window.applicationCache.status === window.applicationCache.UNCACHED) {
    console.log("uncached");
    signageViewerApp.start();
  }
  window.applicationCache.addEventListener('updateready', signageViewerApp.reload);
  window.applicationCache.addEventListener('noupdate', signageViewerApp.start);
  window.applicationCache.addEventListener('cached', signageViewerApp.start);
  window.applicationCache.addEventListener('error', signageViewerApp.start);
} else {
  clearTimeout(window.noAppCacheTimeout);
  signageViewerApp.start();
  alert("No offline support.");
}

// setInterval(function () {
//   signageViewerApp.updateCache();
// }, 12 * 60 * 60 * 1000);
console.log("VERSION: 19");