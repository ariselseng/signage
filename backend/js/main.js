/*global
$, window, Mustache, Rlite, alert, confirm, signageEditorApp, FormData, moment
*/
var signageEditorApp = (function () {
  var self = {};
  var r = new Rlite();
  var data = {};
  var prop;
  var templates = (function () {
    var tpls = {};
    tpls.files = $("#filesTemplate").html();
    tpls.slider = $("#sliderTemplate").html();
    tpls.sliders = $("#slidersTemplate").html();
    tpls.slide = $("#slideTemplate").html();
    return tpls;
  }());
  // parse templates, should increase speed
  for (prop in templates) {
    if (templates.hasOwnProperty(prop)) {
      Mustache.parse(templates[prop]);
    }
  }

  var getBasePath = function () {
    return "";
  };

  var getData = function (params) {
    var restSliderArray = [];
    var response;
    var i;
    for (i = 0; i < params.length; i++) {
      if (params[i].type === "slider") {
        restSliderArray.push('getSlider=' + params[i].id);
      } else if (params[i].type === "sliders") {
        restSliderArray.push('getSliders');
      } else if (params[i].type === "files") {
        restSliderArray.push('getFiles');
      }
    }
    var restOfUrl = restSliderArray.join('&');
    response = $.ajax({
      async: false,
      url: getBasePath() + 'json.php?' + restOfUrl,
      type: 'GET'
    });
    data = response.responseJSON.data;
    return response.responseJSON.data;

  };
  self.getStoredData = function () {
    return data;
  };
  self.updateSlideImageInDom = function (el, file_id) {
    if (file_id.length > 0) {
      var imageEl = $(el).parent().parent().find("img.preview")[0];
      var url = $(imageEl).attr('src');
      url = url.replace(url.match("id=[0-9]*"), "id=" + file_id);
      $(imageEl).attr('src', url);
    }
  };
  var getArrayOfFiles = function (files, selected_id) {
    var filesArray = [];
    var propt, selected;
    for (propt in files) {
      if (files.hasOwnProperty(propt)) {
        if (selected_id === propt) {
          selected = true;
        } else {
          selected = false;
        }
        filesArray.push({id: propt, selected: selected, updated: files[propt].updated, md5: files[propt].md5, title: files[propt].title});
      }
    }
    return filesArray;
  };
  var updateIndexNumbers = function () {
    $("tbody tr").each(function (index, el) {
      $(el).find(".index").text((index + 1) + ".");
    });
  };
  self.addSlide = function () {
    var slide = {file_id: 0, updated: new Date().toJSON(), duration: 8};
    var markup = Mustache.to_html(templates.slide, {
      files: getArrayOfFiles(data.files, slide.file_id),
      orginalStringifiedSlide: JSON.stringify(slide),
      slide: slide
    });
    var checked = $("input[name=checker]:checked");
    if (checked.length === 1) {
      $(checked).first().parent().parent().after(markup);
    } else {
      if ($("tbody tr").length > 0) {
        $("tbody tr").last().after(markup);
      } else {
        $("tbody").append(markup);
      }
    }
    $("select.fileselect").not("select.fileselect.selectized").selectize({
      sortField: {
        field: 'text',
        direction: 'asc'
      }
    });
    updateIndexNumbers();
    $(".container table").tableDnDUpdate();
  };
  self.addFile = function () {
    console.log("addFile");
  };
  self.removeRow = function () {
    $("input[name=checker]:checked").each(function () {
      $(this).parent().parent().remove();
    });
  };
  var getSliderFromDom = function () {
    var slider = {};
    slider.id = $("#sliderId").val();
    slider.title = $("#title").val();
    slider.orgId = $("#originalSliderId").val();
    slider.published = $("input#published").is(":checked");
    slider.thumbnails = $("input#thumbnails").is(":checked");
    slider.updated = new Date().toJSON();
    slider.slides = [];
    var originalSlide;
    $("tbody tr").each(function () {
      var slide = {};
      originalSlide = JSON.parse($(this).find("input[name=orginalStringifiedSlide]").val());
      slide.duration = $(this).find("input[name=duration]").val();
      slide.file_id = $(this).find(".fileselect").val();
      slide.updated = $(this).find("input[name=updated]").val();
      if ((originalSlide.duration !== slide.duration) || (originalSlide.file_id !== slide.file_id)) {
        slide.updated = new Date().toJSON();
      }
      slider.slides.push(slide);
    });
    return slider;
  };
  self.getSliderFromDom = getSliderFromDom;
  var getFilesFromDom = function () {
    var files = {};
    $("tbody tr").each(function () {
      var file = {};
      var originalFile = JSON.parse($(this).find("input[name=originalFile]").val());
      var id;
      // originalSlide = JSON.parse($(this).find("input[name=orginalStringifiedSlide]").val());
      id = $(this).find("input[name=id]").val();
      file.md5 = $(this).find("input[name=md5]").val();
      file.title = $(this).find("input[name=title]").val();
      console.log(originalFile.title);
      console.log(file.title);

      file.updated = $(this).find("input[name=updated]").val();
      files[id] = file;
      if (originalFile.title !== file.title) {
        file.updated = new Date().toJSON();
      }
      // slider.slides.push(slide);
    });
    return files;
  };
  self.saveFiles = function () {
    var files = getFilesFromDom();
    var willContinue = confirm("Do you really want this?");
    if (willContinue === true) {
      console.log(files);
      $.ajax({
        async: true,
        url: getBasePath() + 'json.php?saveFiles',
        type: 'POST',
        data: {data: JSON.stringify({files: files})},
      }).success(function (data) {
        console.log(data);
        // window.location.reload();
      }).error(function () {
        alert("There was a problem somewhere. Probably smart to reload.");
      });
    }
  };
  var getSelectedIdsFromDom = function () {
    var ids = [];
    $("input[name=checker]:checked").each(function () {
      ids.push($(this).parent().parent().find("input[name=id]").val());
    });
    return ids;
  };
  self.deleteFiles = function () {
    var fileIds = getSelectedIdsFromDom();
    console.log(fileIds);
    var willContinue = confirm("Do you really want this?");
    if (willContinue === true) {
      console.log(fileIds);
      $.ajax({
        async: true,
        url: getBasePath() + 'json.php?deleteFiles',
        type: 'POST',
        data: {data: JSON.stringify(fileIds)},
      }).success(function (data) {
        if (data.msg !== undefined) {
          alert(data.msg);
        }
        window.location.reload();
        console.log(data);
      }).error(function () {
        alert("There was a problem somewhere. Probably smart to reload.");
      });
    }
  };
  self.deleteSliders = function () {
    var sliderIds = getSelectedIdsFromDom();
    var willContinue = confirm("Do you really want this?");
    if (willContinue === true) {
      console.log(sliderIds);
      $.ajax({
        async: true,
        url: getBasePath() + 'json.php?deleteSliders',
        type: 'POST',
        data: {data: JSON.stringify(sliderIds)},
      }).success(function () {
        window.location.reload();
      }).error(function () {
        alert("There was a problem somewhere. Probably smart to reload.");
      });
    }
  };
  self.uploadFiles = function () {
    $("input[type=file]").each(function () {
      if (this.value.length !== 0) {
        var formData = new FormData($(this).parent()[0]);
        $.ajax({
          url: 'json.php?uploadFiles',  //Server script to process data
          type: 'POST',
          xhr: function () {  // Custom XMLHttpRequest
            var myXhr = $.ajaxSettings.xhr();
            return myXhr;
          },
          //Ajax events
          // beforeSend: beforeSendHandler,
          success: function (data) {
            console.log(data);
            window.location.reload();
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
  };
  self.saveSlider = function (customId) {
    var slider = getSliderFromDom();
    var willContinue = false;
    if (customId !== undefined) {
      slider.orgId = "newSlider";
    }
    if (slider.title.length > 0 && slider.id === "new") {
      alert("Need a title");
    } else if (slider.title.length > 0) {
      willContinue = confirm("Do you really want this?");
    } else {
      alert("You need a title for your slider.");
    }

    if (willContinue === true) {
      $.ajax({
        async: true,
        url: getBasePath() + 'json.php?saveSlider',
        type: 'POST',
        data: {data: JSON.stringify({slider: slider})},
      }).success(function (data) {
        if (data.newSliderId !== undefined) {
          window.location.href = window.location.pathname + "#/sliders/" + data.newSliderId;
        } else {
          window.location.reload();
        }
      }).error(function () {
        alert("There was a problem somewhere. Probably smart to reload.");
      });
    }
  };

  var loadSlider = function (params) {
    var content = {};
    if (params.id === "new") {
      content = getData([{type: "files"}]);
      content.slider = {};
    } else {
      content = getData([{type: "slider", id: params.id}, {type: "files"}]);
    }
    var index = 1;
    var markup = Mustache.to_html(templates.slider, {
      slider: content.slider,
      slidemarkup: function () {
        this.idx = index++;
        this.updated_humanized = moment(this.updated).fromNow();
        return Mustache.to_html(templates.slide, {
          files: getArrayOfFiles(content.files, this.file_id),
          orginalStringifiedSlide: JSON.stringify(this),
          slide: this,

        });
      }
    });
    $(".container:last").html(markup);
    $(".fileselect").selectize({
      sortField: {
        field: 'text',
        direction: 'asc'
      }
    });
    $(".container table").tableDnD({onDrop: updateIndexNumbers});
  };
  var loadSliders = function () {
    var content = getData([{type: "sliders"}, {type: "files"}]);
    var markup = Mustache.to_html(templates.sliders, {
      sliders: content.sliders,
      updated_humanized: function () {
        return moment(this.updated).fromNow();
      }
    });
    $(".container:last").html(markup);

  };
  var loadFiles = function () {
    var content = getData([{type: "files"}]);
    var filesArray = getArrayOfFiles(content.files);
    var markup = Mustache.to_html(templates.files, {
      files: filesArray.reverse(),
      originalFile: function () {
        return JSON.stringify(this);
      },
      updated_humanized: function () {
        return moment(this.updated).fromNow();
      }
    });
    $(".container:last").html(markup);

  };
  self.processHash = function () {
    var hash = window.location.hash || '#';
    r.run(hash.slice(1));
  };
  self.start = function () {
    self.processHash();
    $("body").fadeIn();
  };
  r.add('sliders', function () {
    $("nav li.active").removeClass("active");
    $("#linkSliders").addClass("active");
    loadSliders();
  });
  r.add('sliders/:id', function (r) {
    $("nav li.active").removeClass("active");
    $("#linkSliders").addClass("active");
    loadSlider({id: r.params.id});
  });
  r.add('files', function () {
    $("nav li.active").removeClass("active");
    $("#linkFiles").addClass("active");
    loadFiles();
  });
  return self;

}());

$(function () {
  signageEditorApp.processHash();
  window.addEventListener('hashchange', signageEditorApp.processHash);
});