$(document).ready(function(){

    initUploadExcerptPhoto();
    initUploadMedia();
    $('#edcomment_save').removeAttr('disabled');
    $(document).on('click', '.js-single-click', function(){
        $(this).attr('disabled', 'disabled');
    });

    $(document).on('submit', '.js-single-submit', function(){
        var submitBtn=$(this).find(':submit');
        submitBtn.attr('disabled', 'disabled');
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
          $('#article_excerpt_photo').val('');
      });
    });

    $(document).on('click', '.js-media-object-reset', function(e)
    {
      $('.js-media-object-remove').addClass('hidden');
      $('.js-media-object').attr('src', '/bundles/edblog/img/svg/image-placeholder.svg');
      $('#article_excerpt_photo').val('');
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

    $(document).on('submit', '.js-comment-form', function(e){
        e.preventDefault();
        var form=$(this);
        var submit=form.find(':submit');
        submit.attr('disabled', 'disabled');

        $.post($(this).attr('action'),$(this).serialize() , function(data){
            $('.js-comments-content').replaceWith(data.html);
            if (data.currentComment)
            {
                $('html, body').animate({
                    scrollTop: $("#"+data.currentComment).offset().top
                }, 2000);
            }
        });
    });

    $(document).on('submit', '.ajax_media_form', function(e){
        e.preventDefault();
        $.post($(this).attr('action'), $(this).serialize(), function(data){});
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

function initUploadMedia()
{
    $('#article_media_media').fileupload({
        url: $('#article_media_media').attr('data-href'),
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
    });
}