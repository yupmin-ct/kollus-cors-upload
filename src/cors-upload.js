/**
 * Show Instant Message.
 *
 * @param {string} type - success|info|warning|danger
 * @param {string} message
 * @param {object|null} options
 */
function showAlert(type, message, options) {
  var alertDiv = $('<div class="alert alert-' + type + '">' +
      '<button type="button" class="close" data-dismiss="alert">' +
      '&times;</button>' + message + '</div>'),
    delayDuration = 5000,
    slideUpDuration = 200;

  options = options || {};

  if ('id' in options) {
    alertDiv.attr('id', options.id);
  }

  if ('delayDuration' in options) {
    delayDuration = options.delayDuration;
  }

  if ('slideUpDuration' in options) {
    slideUpDuration = options.slideUpDuration;
  }

  if (delayDuration !== 0 && slideUpDuration !== 0) {
    alertDiv.delay(delayDuration).slideUp(slideUpDuration);
  }

  $('#alert_message').append(alertDiv);
}

/**
 * Kollus Cors Upload
 *
 * Upload event handler
 */
$(document).on('click', 'button[data-action=upload-file]', function (e) {
  e.preventDefault();
  e.stopPropagation();

  var self = this,
    closestForm = $(self).closest('form'),
    uploadFileInput = closestForm.find('input[type=file][name=upload-file]'),
    uploadFileCount = uploadFileInput.prop('files').length,
    categoryKey = closestForm.find('select[name=category_key]').first().val(),
    useEncryption = closestForm.find('input[type=checkbox][name=use_encryption]').prop('checked'),
    isAudioUpload = closestForm.find('input[type=checkbox][name=is_audio_upload]').prop('checked'),
    title = closestForm.find('input[type=text][name=title]').val(),
    apiData = {},
    progressInterval = 5000, // 5sec
    progressValue = 0,
    supportFormData = function() {
      return !!window.FormData;
    },
    supportFileAPI = function() {
      var fi = document.createElement('INPUT');
      fi.type = 'file';
      return 'files' in fi;
    },
    supportCORS = function() {
      return 'XMLHttpRequest' in window &&
        'withCredentials' in new XMLHttpRequest();
    },
    supportAjaxUploadProgress = function() {
      var xhr = new XMLHttpRequest();
      return !! (xhr && ('upload' in xhr) && ('onprogress' in xhr.upload));
    };

  if (!supportFormData() || !supportFileAPI() || !supportCORS()) {
    showAlert('warning', 'It is not supported by your browser.');
    return;
  }

  if (uploadFileCount === 0) {
    showAlert('warning', 'Please select a file to upload.');
    uploadFileInput.focus();
    return;
  }

  if (categoryKey.length > 0) {
    apiData.category_key = categoryKey;
  }
  if (useEncryption) {
    apiData.use_encryption = 1;
  }
  if (isAudioUpload) {
    apiData.is_audio_upload = 1;
  }
  if (uploadFileCount === 1 && title.length > 0) {
    apiData.title = title;
  }

  showAlert('info', 'Uploading file ...');
  $(self).attr('disabled', true);

  $.each(uploadFileInput.prop('files'), function (key, uploadFile) {
    $.post(
      closestForm.attr('action'),
      apiData,
      function (data) {
        var uploadUrl,
          progressUrl,
          uploadFileKey,
          formData = new FormData(),
          progress,
          progressBar,
          repeator;

        if (('error' in data && data.error) ||
          !('result' in data) || !('upload_url' in data.result) || !('progress_url' in data.result)) {
          showAlert('danger', ('message' in data ? data.message : 'Api response error.'));
        }

        uploadFileInput.val('').clone(true);

        uploadUrl = data.result.upload_url;
        progressUrl = data.result.progress_url;
        uploadFileKey = data.result.upload_file_key;

        progress = $('<div class="progress" />').addClass('progress-' + uploadFileKey);
        progressBar = $('<div class="progress-bar" />').attr('aria-valuenow', 0);
        progressBar.attr('role', 'progressbar')
          .attr('aria-valuenow', 0).attr('aria-valuemin', 0).attr('aria-valuenow', 0).css('min-width', '2em').text('0%');
        progress.append(progressBar);
        progress.insertBefore(uploadFileInput);

        formData.append('upload-file', uploadFile);

        $.ajax({
          url: uploadUrl,
          type: 'POST',
          data: formData,
          dataType: 'json',
          cache: false,
          contentType: false,
          processData: false,
          xhr: function () {
            var xhr = new XMLHttpRequest();

            if (supportAjaxUploadProgress()) {
              xhr.upload.addEventListener('progress', function (e) {

                if (e.lengthComputable) {
                  progressValue = Math.ceil((e.loaded / e.total) * 100);

                  if (progressValue > 0) {
                    progressBar.attr('arial-valuenow', progressValue);
                    progressBar.width(progressValue + '%');

                    if (progressValue > 10) {
                      progressBar.text(progressValue + '% - ' + uploadFile.name);
                    } else {
                      progressBar.text(progressValue + '%');
                    }
                  }
                }
              }, false);
            } else {
              repeator = setInterval(function () {

                $.get(progressUrl, function (data) {

                  if ('result' in data &&
                    'progress' in data.result) {
                    progressValue = Math.ceil(parseInt(data.result.
                      progress, 10));
                  }

                  if (progressValue > 0) {
                    progressBar.attr('arial-valuenow', progressValue);
                    progressBar.width(progressValue + '%');

                    if (progressValue > 10) {
                      progressBar.text(progressValue + '% - ' + uploadFile.name);
                    } else {
                      progressBar.text(progressValue + '%');
                    }
                  }
                }, 'json');
              }, progressInterval);
            }

            return xhr;
          }, // xhr
          success: function (data) {
            progressBar.attr('aria-valuenow', 100);
            progressBar.width('100%');
            progressBar.text(uploadFile.name + ' - 100%');

            if ('error' in data && data.error) {
              showAlert('danger', ('message' in data ? data.message : 'Api response error.'));
            } else {

              if ('message' in data) {
                showAlert('success', data.message + ' - ' + uploadFile.name);
              }
            }
          },
          error: function (jqXHR) {
            try {
              data = jqXHR.length === 0 ? {} : $.parseJSON(jqXHR.responseText);
            } catch (err) {
              data = {};
            }

            showAlert('danger', ('message' in data ? data.message : 'Ajax response error.') + ' - ' + uploadFile.name);
          },
          complete: function () {
            clearInterval(repeator);
            $(self).attr('disabled', false);

            progress.delay(2000).fadeOut(500);
          }
        });
      } // function(data)
    , 'json'); // $.post
  });
});
