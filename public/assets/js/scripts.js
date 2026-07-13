function googleTranslateElementInit() {
  new google.translate.TranslateElement({
    pageLanguage: 'en',
    includedLanguages: 'ar,en',
    autoDisplay: true
  }, 'google_translate_element');
}

function removeAjaxMsgs() {
  let errorDiv = $('.ajax-error-div');
  let errorMsgElm = $('.ajax-error-msg');
  $('.text-danger').remove();
  $('.text-success').remove();

  errorDiv.css('display', 'none');
  errorMsgElm.html('');
}

function formSuccessAction(form_elm, modal_elm, datatable_elm) {
  form_elm.before('<p class="text-success">Form Submitted Successfully!</p>');
  setTimeout(function() {
    if (form_elm.length) {
      modal_elm.modal('hide');
      form_elm[0].reset();
    }
  }, 1500);

  setTimeout(function() {
    datatable_elm.DataTable().ajax.reload();
  }, 2000);

}

function showFormErrorMsgs(errs) {
    //console.log(errs.responseJSON);
  var response = errs.responseJSON;
  if(Array.isArray(response.message)){
      $.each(response.message, function(key, value) {

        // let msg = value[0].replace(" id", "");
        // key = key.replace(/\./g, '_');
        $("#errorMsg").append('<p class="text-danger">' + value + '</p>');
        $("#errorMsg").removeClass("d-none");
      });
  }else{
    $("#errorMsg").append('<p class="text-danger">' + response.message + '</p>');
    $("#errorMsg").removeClass("d-none");
  }
}

function showErrorMsgs(errs) {
  var response = errs.responseJSON;
  console.log(response);
  $.each(response.errors, function(key, value) {

    let msg = value[0].replace(" id", "");
    key = key.replace(/\./g, '_');
    console.log(key)
    $("#" + key).parent().append('<p class="text-danger">' + msg + '</p>');
  });
}

function deleteRow(id, module, elm) {
  Swal.fire({
    title: 'Are you sure?',
    text: "You won't be able to revert this!",
    type: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Yes, delete it!'
  }).then((result) => {
    if (result.value) {
      let request_url = base_url + '/' + module + '-delete/' + id;

      removeAjaxMsgs();
      $.get(request_url).done(function() {

        let table = elm.closest('table').DataTable();
        table.ajax.reload(null, false);

        // Swal.fire(
        //   'Deleted!',
        //   'Your data has been deleted.',
        //   'success'
        // )
        window.location.reload()
      }).fail(function(err) {
        var error = err.responseJSON.message.replace(/\\/g, '')
        Swal.fire(
          'Error!',
          error,
          'error'
        )

      });
    }
  })
}
$.fn.extend({
  toggleText: function(a, b) {
    return this.text(this.text() == b ? a : b);
  }
});
$("#change_password").on('click', function() {
  $(this).toggleText('Enable Password Field', 'Disable Password Field');
});

$('#subscription_img').on('change', function() {
  var reader = new FileReader();
  reader.readAsDataURL($(this)[0].files[0]);
  //Read the contents of Image File.
  reader.onload = function(e) {
    //Initiate the JavaScript Image object.
    var image = new Image();

    //Set the Base64 string return from FileReader as source.
    image.src = e.target.result;

    //Validate the File Height and Width.
    image.onload = function() {
      var height = this.height;
      var width = this.width;
      if (height > 400 || width > 400) {
        //console.error("Height and Width must not exceed 400px.");
        $('#subscription_img').val(null);

        $('.img_error').addClass('text-danger').text('Image dimensions are( '+width+'px '+height+'px' +') these must not exceed 400px x 400px');
      } else {
        $('.img_error').removeClass('text-danger').html('<i class="fas fa-check" style="color:green"></i> Accepted'+' '+'<i style="color:green">( '+width+'px'+'   '+height+'px )</i>');
      }
    };
  };

});

$(document).ready(function() {
  $("#subscription_type").on("change", function() {
    var value = $(this).val();
    var per_user_amount = $('input[name="per_user_amount"]').data('amount') == undefined ? 1 : $('input[name="per_user_amount"]').data('amount');
    var register_limit = $('input[name="register_limit"]').data('amount') == undefined ? 1 : $('input[name="register_limit"]').data('amount');
    console.log(per_user_amount, register_limit);
    switch (value) {
      case '2':
        $('input[name="per_user_amount"]').removeAttr("readonly").val(per_user_amount)
        $('input[name="register_limit"]').removeAttr("readonly").val(register_limit)
        break;
      default:
        $('input[name="per_user_amount"]').prop("readonly", true).val(0)
        $('input[name="register_limit"]').prop("readonly", true).val(0)
    }
  })
})

$(document).on('submit', '#addForm', function(event) {

  event.preventDefault();
  var classes = $(this).attr("class");
  var roles = $(this).attr("data-module");
  if (roles == "user_roles") {
    var checkboxes = document.querySelectorAll('input[type="checkbox"].access_modules');
    var checkedOne = Array.prototype.slice.call(checkboxes).some(x => x.checked);
    if (!checkedOne) {
      if ($(".additional").length < 1) {
        $("#errorMsg").append('<p class="text-danger additional">Select atleast one module</p>');
        $("#errorMsg").removeClass("d-none");
        return false;
      }

    }
  }

  removeAjaxMsgs();
  let form = $(this);
  let btn = form.find('.btn');
  btn.attr("disabled", true);
  btn.addClass('loader');
  var formData = new FormData(this);

  $.ajax({
    type: 'POST',
    url: form.attr('action'),
    data: formData,
    cache: false,
    contentType: false,
    processData: false,
    success: function(data) {
      setTimeout(function(){
          if (classes == "addModal") {
            window.location.reload();
          }
          else {
            window.location.assign(document.referrer);

          }
      },1000);
      

    },
    error: function(err) {
      console.log(err);
      btn.attr("disabled", false);
      btn.removeClass('loader');
      showFormErrorMsgs(err);
    }
  });
});

$(document).on('submit', '#editForm', function(event) {
  event.preventDefault();
  var roles = $(this).attr("data-module");
  if (roles == "user_roles") {
    var checkboxes = document.querySelectorAll('input[type="checkbox"]');
    var checkedOne = Array.prototype.slice.call(checkboxes).some(x => x.checked);
    if (!checkedOne) {
      if ($(".additional").length < 1) {
        $("#errorMsg").append('<p class="text-danger additional">Select atleast one module</p>');
        $("#errorMsg").removeClass("d-none");
        return false;
      }

    }
  }
  removeAjaxMsgs();
  let form = $(this);
  let btn = form.find('.btn');
  btn.attr("disabled", true);
  btn.addClass('loader');
  var formData = new FormData(this);

  $.ajax({
    type: 'POST',
    url: form.attr('action'),
    data: formData,
    cache: false,
    contentType: false,
    processData: false,
    success: function(data) {
      // history.go(-1);
            window.location.assign(document.referrer);

    },
    error: function(err) {
      btn.attr("disabled", false);
      btn.removeClass('loader');
      showErrorMsgs(err);
      showFormErrorMsgs(err);
    }
  });
});

$('#addModal').on('hidden.bs.modal', function() {
  removeAjaxMsgs();
});

let search_elm = $('#global_search');
let search_results_elm = $('#search_results');


search_elm.on('keypress', function(e) {
  if (e.which == 13) {
    search_results_elm.html('');
    e.preventDefault();

    let url = base_url + '/search';
    let value = $(this).val();

    $.get(url, { query: value })
      .then((data) => {
        search_results_elm.html(data);
      });
  }
});

function ReplaceNumberWithCommas(yourNumber, elm) {

  let number = yourNumber.replace('$', '');

  number = parseInt(number.replace(/\D/g, ''));

  if (!$.isNumeric(number)) {
    return;
  }

  let value = number.toLocaleString();

  elm.val('$' + value);

}

/*
setInterval(function () {
    getNotificationCount()
}, 5000);
*/

function getNotifications(count) {

  $.get(base_url + '/get-notifications/' + count)
    .done(function(data) {
      if (count) {
        $('.be-notifications').html(data);
      }
    })
    .fail(function(err) {
      console.log(err);
    })

}

function getNotificationCount() {
  $('.indicator').remove();
  $.get(base_url + '/get-notification-count')
    .done(function(data) {
      console.log(data)
      if (data > 0) {
        $('.mdi-notifications').html('<span class="indicator">' + data + '</span>');
        ion.sound.play("button_tiny");
      }
    })
    .fail(function(err) {
      console.log(err)
    })
}

function printDiv(divID) {
  $('#' + divID).printThis();
}

$("#videoType").change(function() {
  $('#videoTitle').show();
  $('#videoThumbnail').show();
  $('#videoVideo').show();
  $('#videoEmbeddedurlField').prop("required", false)
  $('#videoEmbeddedurl').hide();
  if ($("#videoType").val() == 'manual') {
    $('#videoTitle').show();
    $('#videoThumbnail').show();
    $('#videoVideo').show();
    $('#videoEmbeddedurlField').prop("required", false)
    $('#videoEmbeddedurl').hide();
  } else {
    $('#videoEmbeddedurlField').prop("required", true)
    $('#videoTitleField').prop("required", false)
    $('#videoThumbnailField').prop("required", false)
    $('#videoVideoField').prop("required", false)
    $('#videoTitle').hide();
    $('#videoThumbnail').hide();
    $('#videoVideo').hide();
    $('#videoEmbeddedurl').show();
  }
});
