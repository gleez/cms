/* =================================================
 * user.js v1.2
 * http://github.com/gleez/cms
 *
 * Copyright 2011-2013 Gleez Technologies
 * Gleez CMS License http://gleezcms.org/license
 * ============================================== */
(function ($) {

	/**
	 * Wait until the DOM has loaded before querying the document
	 */
	$(document).ready(function(){
			scan_modals();
	});

	/**
	 * Scan modal windows
	 */
	function scan_modals()
	{
		$('a#add-pic').click(function(e){
			$.get('/user/photo', function(data){
				$('#upload-photo').modal({show: true});
				$('#upload-photo').find('.modal-data').html(data);
				picture_upload();
			});

			e.preventDefault();
		});
	}

	/**
	 * Wait until the DOM has loaded before querying the document
	 */
	function picture_upload()
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
			}
		});
	}
})(jQuery);