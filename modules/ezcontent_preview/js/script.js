/**
* @file
*/

(function ($, Drupal) {

  $.fn.buildPreviewFrame = function (preview_options) {

    let options_form = '';
    if (typeof preview_options !== 'undefined' && preview_options.length > 0) {
      let options = '';
      $.each(preview_options, function(index, route){
        options += `<option value="${index}" data-value="${route.url}">${route.label}</option>`;
      });

      // hide the selector if only one preview
      let hide = "";
      if(preview_options.length === 1) hide = "style='display:none'";

      options_form += `<div class="ezcontent-preview--options" ${hide}><select class="form-select--preview-options">${options}</select></div>`;
    }

    return `<div class="ezcontent-preview--wrapper">
              <div class="ezcontent-preview__actions">
                ${options_form}
                <button class="ezcontent-preview__actions__btn-copy ezcontent-preview--btn">${Drupal.t('Copy Url')}</button>
                <button class="ezcontent-preview__actions__btn-fullscreen ezcontent-preview--btn">${Drupal.t('Fullscreen')}</button>
                <button class="ezcontent-preview__actions__btn-close ezcontent-preview--btn">${Drupal.t('Close')}</button>
              </div>
              <div class="ezcontent-preview--loading">
                <p class="ezcontent-preview--loading__loader"></p>
              </div>
              <iframe class="ezcontent-preview__iframe" width="100%" height="100%" frameborder="0" allowfullscreen=""></iframe>
            </div>`;
  }

$.fn.frameReload = function () {
  let frame = $('.ezcontent-preview__iframe');
  if (frame.length) {
    $('.ezcontent-preview--loading').show();
    frame.attr("src", function(index, attr){
      return attr;
    });
  }
}

$.fn.copyUrl = function() {
  let clipboard = new ClipboardJS('.ezcontent-preview__actions__btn-copy', {
      text: function(trigger) {
        let previewFrame = $('.ezcontent-preview__iframe');
        return previewFrame.attr('src');
      }
  });
  clipboard.on('success', function(e) {
    $(e.trigger).text("Copied!");
    e.clearSelection();
    setTimeout(function() {
      $(e.trigger).text("Copy Url");
    }, 800);
  });
}

$.fn.closePreview = function() {
  $(once('preview_close', '.ezcontent-preview__actions__btn-close')).click(function (){
    $('.ezcontent-preview--wrapper').animate({
      transform: 'translate(100%, 0)'
    }, 'slow', 'linear', function() {
      $(this).remove();
    });
    $('body').removeClass('ezcontent-preview-active');
    $('.ezcontent-preview--wrapper').removeClass('ezcontent-preview--open');
    // Change the preview button name to back to original.
    $('.ezcontent-preview--preview-btn').val("Preview");
  });
}

$.fn.fullScreen = function() {
  $(once('preview_fullscreen', '.ezcontent-preview__actions__btn-fullscreen')).click(function (){
    let el = $(this);
    if (el.text() == "Exit Fullscreen") {
    }
    el.text(function() {
      return (el.text() == "Fullscreen") ? "Exit Fullscreen" : "Fullscreen";
    });
    $('.ezcontent-preview--wrapper').toggleClass('ezcontent-preview--fullscreen');
  });
}

Drupal.AjaxCommands.prototype.previewContent = function (ajax, response, status) {

  // Update the preview frame, once we get URL from ajax submit.
  if (response.preview_options) {
    if (!$('.ezcontent-preview--wrapper').length) {
      $('body').append($.fn.buildPreviewFrame(response.preview_options)).animate({
        transform: 'translate(0, 0)'
      }, 'slow', 'linear');
      let previewFrameWrapper = $('.ezcontent-preview--wrapper');

      let previewFrame = $('.ezcontent-preview__iframe');
      let preview_selector = $('.form-select--preview-options');

      previewFrame.attr("src", $('option:selected', preview_selector).attr('data-value'));

      preview_selector.on('change', function() {
        $.fn.frameReload();
        previewFrame.attr("src", $('option:selected', preview_selector).attr('data-value'));
      });

      $('body').addClass('ezcontent-preview-active');
      previewFrameWrapper.addClass('ezcontent-preview--open');

      // Change the preview button name to refresh preview.
      $('.ezcontent-preview--preview-btn').val("Refresh Preview");
    } else {
      $.fn.frameReload();
    }

    // loading function.
    $('.ezcontent-preview__iframe').on('load', function () {
      $('.ezcontent-preview--loading').hide();
      $(this).show();
    });

    // Copy to clipboard url.
    $.fn.copyUrl();
    

    // Preview close event.
    $.fn.closePreview();

    // Preview close fullscreen.
    $.fn.fullScreen();

  }
}
})(jQuery, Drupal);
