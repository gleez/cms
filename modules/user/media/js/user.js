(function ($) {
        
        // Wait until the DOM has loaded before querying the document
        $(document).ready(function(){
                profile_photo();
        });

        function profile_photo()
        {
                $('a#add-pic').click(function(e){
                        var top = Math.max($(window).height() - $('.modal').outerHeight(), 0) / 2;
                        
                        $('.modal').css({
				//top: top + $(window).scrollTop()
			});
                        
                        $.get('/user/photo', function(data){
                                $('#upload-photo').modal('show');
                                $('#upload-photo').find('.modal-data').html(data);
                                profile_upload();
                        });
      
                        e.preventDefault();
                });  
        }
        
        function profile_upload()
        {
                var bar = $('.bar');
                var percent = $('.percent');
                var status = $('#status');
   
                $('form').ajaxForm({
                        beforeSend: function() {
                                status.empty();
                                $('.progress').show();
                                var percentVal = '0%';
                                bar.width(percentVal);
                        },
                        uploadProgress: function(event, position, total, percentComplete) {
                                var percentVal = percentComplete + '%';
                                bar.width(percentVal);
                        },
                        complete: function(xhr) {
                                location.reload();
                                //status.html(xhr.responseText);
                        }
                });
        }
        
})(jQuery);