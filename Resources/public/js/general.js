$(document).ready(function(){
    tinymce.init({
    selector: "textarea.tinymce",
    height: 300,
    theme: "modern",
    plugins: [
        "advlist autolink link lists charmap print preview hr anchor pagebreak",
        "searchreplace wordcount visualblocks visualchars insertdatetime media nonbreaking",
        "table contextmenu directionality emoticons paste textcolor"
    ],
    toolbar1: "undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | styleselect",
    toolbar2: "| link unlink anchor | forecolor backcolor  | print preview code ",
    image_advtab: true,
    relative_urls: false,
    remove_script_host: false
    });

    initUploadExcerptPhoto();
    initUploadMedia();
    initNprogress();

    $(document).on('click', 'a.js-ajax-row-update', function(e){
        e.preventDefault();
        var object = $(this);
        var row = object.parents('tr');

        $.ajax($(this).attr('href')).done(function(data){
            if(data.success)
            {
                row.replaceWith(data.html);
            }
        });
    });

    $(document).on('click', '.js-single-click', function(){
        $(this).attr('disabled', 'disabled');
    });

    $(document).on('click', '.js-add-media', function(e){
        e.preventDefault();
        $('.js-modal-add-media').modal();
        $('.js-modal-add-media').on('shown.bs.modal', function (e) {
          refreshIsotope();
        })
    });

    $(document).on('click', '.js-pick-or-upload', function(e){
        e.preventDefault();
        $('.js-modal-add-excerpt-media').modal();
        $('.js-modal-add-excerpt-media').on('shown.bs.modal', function (e) {
            refreshIsotope();
        })
    });

    $(document).on('click', '.js-trigger-upload', function(){
        $('.js-upload-input').click();
    });

    $(document).on('click', '.js-trigger-upload-medias', function(){
        $('#article_media_media').click();
    });

    $(document).on('click', '.js-trigger-upload-excerpt', function(){
        $('#article_excerpt_media').click();
    });

    $(document).on('click', '.js-media-object-remove', function(e){
      e.preventDefault();
      $.post($(this).attr('data-href'), function(){
          $('.js-media-object-remove').addClass('hidden');
          $('.js-media-object').attr('src', '/bundles/edblog/img/svg/image-placeholder.svg');
          $('#article_excerptPhoto').val('');
      });
    });

    $(document).on('click', '.js-media-object-reset', function(e)
    {
      $('.js-media-object-remove').addClass('hidden');
      $('.js-media-object').attr('src', '/bundles/edblog/img/svg/image-placeholder.svg');
      $('#article_excerptPhoto').val('');
    });

    $(document).on('click', '.js-delete-object', function(e){
      e.preventDefault();
      $('.js-delete-object-text').text($(this).attr('data-text'));
      $('.js-delete-object-title').text($(this).attr('data-title'));
      $('.js-delete-object-href').attr('href',$(this).attr('data-href'));
    });

    $(document).on('click', '.js-insert-media', function(e){
    e.preventDefault();
      $('.phototiles__iteminner.selected').each(function(){
          var caption = $(this).parents('li').find('.ajax_media_form textarea').val();

          if(caption)
          {
              tinymce.editors[0].insertContent('<div>'+ $(this).find('.js-add-media-editor').attr('data-content') +'<span class="d--b margin--halft app-grey text--mini text--italic">'+ caption +'</span></div>');
          }
          else
            tinymce.editors[0].insertContent($(this).find('.js-add-media-editor').attr('data-content'));
      });
    $('.js-close-insert-modal').click();
    });

    $(document).on('click', '.js-pagination-pager', function(e){
    e.preventDefault();
    $.post($(this).attr('href'), function(data){
      $('.js-load-more').remove();
      $('.js-media-content').after(data.pagination);

      if ($('.js-media-content').hasClass("js-noisotope")){
        $('.js-media-content').append(data.html);
      }
      else{

        // Isotope after Load more
        // Second approach
        $container = $('.isotope:visible');
        // We need jQuery object, so instead of response.photoContent we use $(response.photoContent)
        var $new_items = $(data.html);
        // imagesLoaded is independent plugin, which we use as timeout until all images in $container are fetched, so their real size can be calculated; When they all are on the page, than code within will be executed
        $container.append( $new_items );
        $container.imagesLoaded( function() {
          $container.isotope( 'appended', $new_items );
          //$container.isotope( 'prepended', $new_items );
          $container.isotope('layout');
          $('.phototiles__item').removeClass('muted--total');
          if ( $('.modal:visible').length ) {
            $('.modal:visible').each(centerModal);
          } else {
              if ( $(window).width() >= 992 ) {
                  $('html, body').animate({ scrollTop: $(document).height() }, 1200);
                  $('.dashboard-menu.alltheway').css('min-height', $('.dashboard-content').height());
              }
          }
        });
      }


    });
    });

    $(document).on('click', '.js-content-replace', function(e){
        e.preventDefault();
        element=$(this);
        $.post($(this).attr('data-href'), function(data){
            element.parent().html(data.html);
        });
    });

    // Correct display after slow page load
    $('.gl.muted--total').each(function() {
        $(this).removeClass('muted--total');
    });

    // Make modal as wide as possible
    $('.modal--full').on('shown.bs.modal', function (e) {
        recalculateModal($(this));
    });

    // Select image for insert into article
    $(document).on('click', '.js-modal-add-media .phototiles__iteminner', function(e){
        $(this).toggleClass('selected');
    });

    $(document).on('click', '.js-modal-add-excerpt-media .phototiles__iteminner', function(e){
        var item = $(this).find('.js-add-media-editor');
        $('.js-excerpt-holder').html( item.attr('data-content') );
        $('#article_excerptPhoto').val(item.attr('data-val'));
        $('.js-modal-add-excerpt-media .js-close-insert-modal').trigger('click');
    });

    $(document).on('submit', '.ajax_media_form', function(e){
        e.preventDefault();
        $.post($(this).attr('action'), $(this).serialize(), function(data){});
    });

    $(document).on('blur', '.ajax_media_form textarea', function(e){
        $(this).parents('form').submit();
    });

    $(document).on('blur', '.js-datetime-field', function(e){
        var text_item=$(this);
        console.log(text_item.attr('data-href'));
        console.log(text_item.val());
        $.post(text_item.attr('data-href'), { dataFormat : text_item.val()}, function(data) {
            var label_id=text_item.attr('id');
            $("label[for="+label_id+"]").html(data.html);
        });
    });

    $(document).on('change', "input[name='edblog_settings[date_format]']", function(e){
        var control=$(this);
        if (control.val()!="custom_date_format")
        {
            $("#edblog_settings_custom_date_format").val(control.val());
            $("label[for='edblog_settings_custom_date_format']").html($("label[for='"+control.attr('id')+"']").text());
        }
    });

    $(document).on('change', "input[name='edblog_settings[time_format]']", function(e){
        var control=$(this);
        if (control.val()!="custom_time_format")
        {
            $("#edblog_settings_custom_time_format").val(control.val());
            $("label[for='edblog_settings_custom_time_format']").html($("label[for='"+control.attr('id')+"']").text());
        }
    });

    $(document).on('click', '.js-add-prototype', function(e){
        e.preventDefault();

        var container = $(this).parents('[data-prototype]');
        var newWidget = container.attr('data-prototype');
        newWidget = newWidget.replace(/__name__/g, container.find('.row').length);
        $(this).before(newWidget);
        recalculateSidebar();
    });

    $(document).on('click', '.js-remove-prototype', function(e){
        e.preventDefault();

        if($(this).attr('data-element') != undefined)
        {
            var v = $('#article_metaExtras').val();
            $('#article_metaExtras').val( ($('#article_metaExtras').val() ? $('#article_metaExtras').val() + ':'+$(this).attr('data-element') : $(this).attr('data-element')) );
        }

        $(this).parents('.row').remove();
        recalculateSidebar();
    });

    getFancyCategories();
}); // End of $(document).ready()


// Resize or orientationchange of document
$(window).bind('resize orientationchange', function(){
  if ( $('.modal--full:visible').length ) {
    recalculateModal($('.modal--full'));
  }
});

// Calculate position for full-width modal
function recalculateModal($this) {
  mod_width = Math.floor($(window).width()*0.96);
  mod_height = Math.floor($(window).height()*0.96);
  $this.find('.modal-dialog').css('width', mod_width);
  head_foot_offset = 150;
  $this.find('.modal-body').css('max-height', mod_height - head_foot_offset);
  $this.removeClass('muted--total');
  refreshIsotope();
}

// Refresh Isotope
function refreshIsotope() {
    $container = $('.isotope');
    $container.imagesLoaded(function () {
        $container.isotope().isotope('layout');
        $('.phototiles__item').removeClass('muted--total');
        $('.modal:visible').each(centerModal);
    });
}

function initUploadExcerptPhoto()
{
    $('#form_excerptImage').fileupload({
        url: $('#form_excerptImage').attr('data-href'),
        dataType: 'json',
        maxFileSize: 20000000,
        done: function (e, response) {
                var data = response.result;
                if (data.success == 'true') {
                    $('.js-media-object').replaceWith(data.media);
                    $('.js-excerpt-photo').val(data.id);
                    $('.js-media-object-remove').removeClass('hidden');
                    $('.js-media-object-remove').attr('data-href', data.href);
                }
        },
        progressall: function (e, data) {
            var progress = parseInt(data.loaded / data.total * 100, 10);
            $('#progress .bar').css('width', progress + '%');
        }
    });
}

function displayErrorsFromArray(errors)
{
    for(error in errors)
    {
        var e = errors[error];
        new PNotify({title: 'Error while uploading', text: e.name + ' - '+  e.message, type: 'error', icon: 'fa fa-exclamation-triangle'});
    }

}

function initUploadMedia()
{
    $('#article_media_media').fileupload({
        url: $('#article_media_media').attr('data-href'),
        dataType: 'json',
        maxFileSize: 20000000,
        done: function (e, response) {
            var data = response.result;

            if(data.hasOwnProperty('errors') && data.errors.length)
            {
                displayErrorsFromArray(data.errors);
            }

            $('.js-load-more').remove();
            $('.pagination').remove();
            $('.js-media-content').replaceWith(data.html);

            refreshIsotope();
        },
        progressall: function (e, data) {
            var progress = parseInt(data.loaded / data.total * 100, 10);
            $('#progress .bar').css('width', progress + '%');
        }
    });

    $('#article_excerpt_media').fileupload({
        url: $('#article_excerpt_media').attr('data-href'),
        dataType: 'json',
        maxFileSize: 20000000,
        done: function (e, response) {
            var data = response.result;
            $('.js-load-more').remove();
            $('.pagination').remove();
            $('.js-media-content').replaceWith(data.html);
            // $('.js-trigger-upload-medias').parent().removeClass('muted--total');
            // $('.js-trigger-upload-medias').removeClass('muted--total');
            // $('.js-trigger-upload-medias').parent().css('position', 'relative');
            refreshIsotope();
        },
        progressall: function (e, data) {
            var progress = parseInt(data.loaded / data.total * 100, 10);
            $('#progress .bar').css('width', progress + '%');
        }
    });
}

function getFancyCategories()
{
    $('.js-get-pretty-categories').each(function(){
        var input = $(this);
        var url = input.attr('data-category-url');
        var selected = [];

        input.find('[checked]').each(function(){
            selected.push($(this).val());
        });
        input.find('[selected]').each(function(){
            selected.push($(this).val());
        });


        $.post(url, { 'select': selected }, function(data){
            if(data.success === true)
            {
                if(input.attr('data-empty-option') != undefined)
                {
                    input.html('<option value="">' + input.attr('data-empty-option') + '</option>');
                }
                else
                    input.html('');

                input.append(data.html).removeClass('hide');


            }
        });
    });
}

function initNprogress()
{
    $(document).ajaxStart(function(e)
    {
        if( (e.target.activeElement == undefined) || !$(e.target.activeElement).hasClass('js-skip-nprogress') )
            NProgress.start();
    }).ajaxStop(function(e){
        NProgress.done();
    }).ajaxComplete(function(e, response)
    {
        var data = response.responseJSON;
        if(data != undefined && data.hasOwnProperty("redirect") && data.hasOwnProperty("success") && data.success == false)
        {
            window.location = data.redirect;
        }
    });
}