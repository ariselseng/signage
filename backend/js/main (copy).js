/*global
$, window, Mustache, Rlite, FormData, alert, data
*/
var authtoken;
if (typeof window.localStorage.getItem("authtoken") === "string") {
  authtoken = window.localStorage.getItem("authtoken");
} else {
  authtoken = window.prompt("Hva er passordet?");
}

function getSlideRowMarkup(imageurl, slide, files) {
  var slideTemplate = $("#slideTemplate").html();
  var filesArray = [];
  var propt, selected;
  for (propt in files) {
    if (files.hasOwnProperty(propt)) {
      if (slide.file_id === propt) {
        selected = true;
      } else {
        selected = false;
      }
      filesArray.push({id: propt, selected: selected, title: files[propt].title});
    }
  }
  var markup = Mustache.to_html(slideTemplate, {imageurl: imageurl, slide: slide, files: filesArray});

  // var markup = ;
  // markup += '<tr><td><div class="input-group><span class="input-group-addon"><input name="checker" type="checkbox"></input></span></div><img width="256px" src="' + imageurl + '" /></td><td><input value="' + slide.duration + '" type="number" name="duration" placeholder="Seconds"/></td><td><input type="hidden" name="id" value="' + slide.id + '" /><input type="hidden" name="updated" value="' + slide.updated + '" />' + slide.updated + '</td><td><div class="input-group"><span class="btn btn-default btn-file"><span>Browse</span> <form enctype="multipart/form-data"><input data-hasinput="false" onchange="$(this).parent().parent().find(\'span\').text(this.value);$(this).attr(\'data-hasinput\',true)" type="file" class="form-control" name="image"></form></span></div></td></tr>';
  return markup;
}
var substringMatcher = function (strs) {
  return function findMatches(q, cb) {
    var matches, substrRegex;

    // an array that will be populated with substring matches
    matches = [];

    // regex used to determine if a string contains the substring `q`
    substrRegex = new RegExp(q, 'i');

    // iterate through the pool of strings and for any string that
    // contains the substring `q`, add it to the `matches` array
    $.each(strs, function (i, str) {
      if (substrRegex.test(str)) {
        console.log(i);
        matches.push(str);
      }
    });

    cb(matches);
  };
};
function getSliderMarkup(key, data) {
  var markup = '';
  var i;
  for (i = 0; i < data.sliders[key].slides.length; i++) {
    markup += getSlideRowMarkup("/backend/getImage.php?id=" + data.sliders[key].slides[i].file_id + "&res=64x36", data.sliders[key].slides[i], data.files);
  }
  return markup;
}
function loadSlider(key, data) {
  var slidesMarkup = getSliderMarkup(key, data);
  var sliderTemplate = $("#sliderTemplate").html();
  var markup = Mustache.to_html(sliderTemplate, {slider:data.sliders[key],slidesMarkup: slidesMarkup});

 $(".container").html(markup);
}
function loadData(data) {
  var propt = "default";
  window.data = data;
  console.log(data);
  $("ul#sliders").empty();
  // for (propt in data.sliders) {
    // if (data.sliders.hasOwnProperty(propt)) {
  loadSlider(propt, data);
    // }
  // }
}
function getData() {
  $.ajax({
    url: 'json.php?getData',
    type: 'POST',
    dataType: 'jsonp',
    data: {auth: authtoken},
    success: function (data) {
      loadData(data.data);
      if (window.localStorage !== undefined) {
        window.localStorage.setItem('authtoken', window.authtoken);
      }
    }
  });
}
function pad(n, width, z) {
  z = z || '0';
  n = String(n);
  return n.length >= width ? n : [width - n.length + 1].join(z) + n;
}
function uploadFiles() {
  $("input[type=file]").each(function () {
    if (this.value.length !== 0) {
      var formData = new FormData($(this).parent()[0]);
      formData.append("slider", $(this).parentsUntil("ul").last().find("input#id").val());
      formData.append("index", $(this).parentsUntil("tr").last().parent().index());
      $.ajax({
        url: 'upload.php',  //Server script to process data
        type: 'POST',
        xhr: function () {  // Custom XMLHttpRequest
          var myXhr = $.ajaxSettings.xhr();
          // if(myXhr.upload){ // Check if upload property exists
          //     myXhr.upload.addEventListener('progress',progressHandlingFunction, false); // For handling the progress of the upload
          // }
          return myXhr;
        },
        //Ajax events
        // beforeSend: beforeSendHandler,
        success: function () {
          console.log(this);
        },
        error: function (jqXHR, textStatus, errorThrown) {
          console.log(jqXHR);
          console.log(textStatus);
          console.log(errorThrown);
        },
        async: false,
        // Form data
        data: formData,
        //Options to tell jQuery not to process data or worry about content-type.
        cache: false,
        contentType: false,
        processData: false
      });
    }
  });
}
function basename(path) {
  return path.split('/').reverse()[0];
}
function saveAll() {
  var newData = {};
  $("ul#sliders li").each(function () {
    var slides = [];
    var id = $(this).find("input#id").val();
    var thumbnails = $(this).find("input#thumbnails").is(":checked");
    var published = $(this).find("input#published").is(":checked");
    // var smallTitle, largeTitle, slideClass, description, link, linkText;
    var updated, duration, filepath, filename;
    $(this).find("tbody tr").each(function () {

      // smallTitle = $(this).find("textarea[name=smallTitle]").val().split(";");
      // largeTitle = $(this).find("textarea[name=largeTitle]").val().split(";");
      // slideClass = $(this).find("input[name=slideClass]").val();
      // description = $(this).find("textarea[name=description]").val();
      duration = Number($(this).find("input[name=duration]").val());
      if (duration < 1) {
        duration = 8;
      }
      filepath = $(this).find("input[type=file]").val();
      if (filepath.length > 0) {
        filename = basename(filepath);
        updated = new Date().toJSON();
      } else {
        updated = $(this).find("input[name=updated]").val();
        filename = $(this).find("input[name=id]").val();
      }
      slides.push({id: filename, updated: updated, duration: duration});
    });
    newData[id] = {id: id, published: published, thumbnails: thumbnails, slides: slides};
  });
  console.log({sliders: newData});
  uploadFiles();
  $.ajax({
    url: 'json.php?saveData&callback=?',
    jsonp: "callback",
    type: "POST",
    // Tell jQuery we're expecting JSONP
    dataType: "jsonp",
    data: {auth: authtoken, data: JSON.stringify({sliders: newData})},
    success: function (data) {
      console.log(data);
      if (data.status) {
        loadData(data.data);
        alert("Saved");
      } else {
        alert("Error saving.");
      }
    }
  });

}

function addSlide(el) {
  var markup = getSlideRowMarkup("", {});
  var checked = $(el).parent().parent().find("input[name=checker]:checked");
  if (checked.length === 1) {
    $(el).parent().parent().find("input[name=checker]:checked").first().parent().parent().parent().parent().after(markup);
  } else if (checked.length > 1) {
    alert("You need to have only one checked in a slider to insert new row");
  } else {
    // $(el).parent().find("tr").after(markup)
    $(el).parent().parent().find("tr").last().after(markup);
  }

}

function removeSlide(el) {
  $(el).parent().parent().find("input[name=checker]:checked").each(function () {
    $(this).parent().parent().parent().remove();
  });
}
function destroySlider(el) {
  $(el).parentsUntil("li").last().parent().remove();
}
function createSlider() {
  var key = new Date().getTime();
  var slidesMarkup = getSlideRowMarkup({smallTitle: ["mobiltittel"], largeTitle: ["Desktoptittel"], slideClass: "left middle yellow", description: "Beskrivelse", link: "/stottoss", linkText: "Les mer"});
  var sliderTemplate = $("#sliderTemplate").html();
  var markup = Mustache.to_html(sliderTemplate, {slidesMarkup: slidesMarkup});
  $("ul#sliders").append('<li id="slider' + key + '">' + markup + '</li>');
}




// Hash-based routing
function processHash() {
  var hash = window.location.hash || '#';
  r.run(hash.slice(1));
}
$(function () {
  window.r = new Rlite();

  window.r.add('', function () {
    getData();
  });
  processHash();
});
window.addEventListener('hashchange', processHash);