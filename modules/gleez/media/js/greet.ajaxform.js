/*
 * This turns any form into a "post-in-place" form so it is ajaxed to save
 * without a refresh. Requires jquery form plugin @link
 *  https://github.com/gleez/greet
 *
 * @package    	Greet\AjaxForm
 * @version    	1.1
 * @requires   	jQuery v1.9 or later
 * @author     Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2005-2013 Gleez Technologies
 * @license    The MIT License (MIT)
 * @link	https://github.com/malsup/form
 */

!function ($) { "use strict";

	// GREET AJAXFROM CLASS DEFINITION
	// ======================

	var Ajaxform = function (element, options) {
		//Set the options
		options.dataType        = options.datatype
		options.beforeSerialize = options.beforeserialize
		options.beforeSubmit    = options.beforesubmit || Ajaxform.prototype.beforeSubmit
		options.success       	= options.success || Ajaxform.prototype.showResponse
		options.error       	= options.error || Ajaxform.prototype.errorResponse
		options.resetForm       = options.resetform
		options.clearForm       = options.clearform
		options.closeKeepAlive  = options.closekeepalive
		options.extraData       = options.extradata
		options.replaceTarget   = options.replacetarget
		options.includeHidden   = options.includehidden
		options.uploadProgress  = options.uploadprogress
	
		//delete unsed options
		delete options.datatype
		delete options.beforeserialize
		delete options.beforesubmit
		delete options.resetform
		delete options.clearform
		delete options.closekeepalive
		delete options.extradata
		delete options.replacetarget
		delete options.includehidden
		delete options.uploadprogress
	
		this.init(element, options)
	}

	Ajaxform.prototype.init = function(element, options) {
		this.$element   = element
		$(element).ajaxSubmit(options).removeData('jqxhr')
	}

	Ajaxform.prototype.beforeSubmit = function(formData, form, options) {
		//add submit button to form array if its from popup request
		if(options.button && options.button.length == 1){
			var subButton   = Array()
			subButton.name  = options.button.attr('name')
			subButton.value = options.button.attr('value')
			subButton.type  = options.button.attr('type')
			
			//append to form data
			formData.push(subButton)
		}
	
		// Hide any errorContainers
		$(form).find('.error-message-container').slideUp(250)
		$(options.clkbtn).attr('disabled', true).addClass('InProgress')
	
		return true
	}

	Ajaxform.prototype.showResponse = function(data, status, xhr, form) {
		if (data.FormSaved == false && data.errors){
			Ajaxform.prototype.validationErrors(data, form);
		}
		else if (data.FormSaved == true){
			var popup     = $(form).data('popup') || $(form).find('[type=submit]').data('popup')
			var dataTable = $(form).data('datatable') || false
			$(form).remove()
	
			if(dataTable){
				//redraw dataTables if its a dataTable popup or form add/edit/delete
				$(dataTable).dataTable().fnDraw()
			}
		
			//Lets check if the form is in popup window
			if(popup && typeof data.messages !== undefined && data.messages.length > 0){
				var text = '<div class="alert alert-success alert-block"><i class="icon-info-sign"></i>&nbsp'+data.messages[0].text+'</div>'
				$(popup).find('.popup-title').html(data.messages[0].type)
				$(popup).find('.popup-body').html(text)
				$(popup).find('.popup-footer').html('&nbsp')
			}
			else if(popup){
				var text = '<div class="alert alert-success alert-block"><i class="icon-info-sign"></i>&nbspSuccess</div>'
				$(popup).find('.popup-body').html(text)
				$(popup).find('.popup-footer').html('&nbsp')
			}
		}
	}

	Ajaxform.prototype.errorResponse = function(xhr, status, error, form) {
		console.log('Error Response')
		console.log(error)
	}

	Ajaxform.prototype.validationErrors = function(data, form) {
		var title = 'Error'
		  , tmpl = '<div class="alert alert-error alert-block">'
	
		tmpl += '<h4 class="alert-heading">' + title + '</h4><ul>';
			// Loop through the errors
			$.each(data.errors, function(i, value) {
			// And add the error to the list.
			tmpl += '<li>' + value + '</li>';
			// Let's guesstimate the input that gave us an error
			var $inputField = $('[name*="'+i+'"]')
	
			if ($inputField.length){
				$($inputField).parent('div.controls').parent('div.control-group').addClass('error')
			}
			});
		tmpl += '</ul></div>';
	
		// If the target block doesn't exist..
		if (!$('.error-message-container').length){
			$(form).prepend('<div class="error-message-container" style="display:none"></div>')
		}

		// Empty any previous error messages, insert the new errors and slide it in to view.
		$(form).find('.error-message-container').empty().html(tmpl).slideDown(250)
		$(form).data('clkbtn').removeAttr('disabled').removeClass('InProgress')
	}

	Ajaxform.prototype.processData = function(data, $el) {
		if (data.location) {
			window.location.href = data.location
		}
		else {
			var replace_selector = $el.attr('data-replace')
			  , replace_closest_selector = $el.attr('data-replace-closest')
			  , replace_inner_selector = $el.attr('data-replace-inner')
			  , replace_closest_inner_selector = $el.attr('data-replace-closest-inner')
			  , append_selector = $el.attr('data-append')
			  , prepend_selector = $el.attr('data-prepend')
			  , refresh_selector = $el.attr('data-refresh')
			  , refresh_closest_selector = $el.attr('data-refresh-closest')
			  , clear_selector = $el.attr('data-clear')
			  , remove_selector = $el.attr('data-remove')
			  , clear_closest_selector = $el.attr('data-clear-closest')
			  , remove_closest_selector = $el.attr('data-remove-closest')
	
			if (replace_selector) {
				$(replace_selector).replaceWith(data.html)
			}
			if (replace_closest_selector) {
				$el.closest(replace_closest_selector).replaceWith(data.html)
			}
			if (replace_inner_selector) {
				$(replace_inner_selector).html(data.html)
			}
			if (replace_closest_inner_selector) {
				$el.closest(replace_closest_inner_selector).html(data.html)
			}
			if (append_selector) {
				$(append_selector).append(data.html)
			}
			if (prepend_selector) {
				$(prepend_selector).prepend(data.html)
			}
			if (refresh_selector) {
				$.each($(refresh_selector), function(index, value) {
					$.getJSON($(value).data('refresh-url'), function(data) {
					$(value).replaceWith(data.html)
					})
				})
			}
			if (refresh_closest_selector) {
				$.each($(refresh_closest_selector), function(index, value) {
					$.getJSON($(value).data('refresh-url'), function(data) {
					$el.closest($(value)).replaceWith(data.html)
					})
				})
			}
			if (clear_selector) {
				$(clear_selector).html('')
			}
			if (remove_selector) {
				$(remove_selector).remove()
			}
			if (clear_closest_selector) {
				$el.closest(clear_closest_selector).html('')
			}
			if (remove_closest_selector) {
				$el.closest(remove_closest_selector).remove()
			}
		}
	
		if (data.fragments) {
			for (var s in data.fragments) {
				$(s).replaceWith(data.fragments[s])
			}
		}
		if (data['inner-fragments']) {
			for (var i in data['inner-fragments']) {
				$(i).html(data['inner-fragments'][i])
			}
		}
		if (data['append-fragments']) {
			for (var a in data['append-fragments']) {
				$(a).append(data['append-fragments'][a])
			}
		}
		if (data['prepend-fragments']) {
			for (var p in data['prepend-fragments']) {
				$(p).prepend(data['prepend-fragments'][p])
			}
		}
	
		$el.trigger('ajaxform.success', [data, $el])
	}

	Ajaxform.prototype.captureSubmittingElement = function(e) {
		var target = e.target
		var $el = $(target)
		
		if (!($el.is("[type=submit],[type=image]"))) {
			// is this a child element of the submit el?  (ex: a span within a button)
			var t = $el.closest('[type=submit]');
			if (t.length === 0) {
			return;
			}
			target = t[0];
		}
		
		var form = this
		form.clk = target
		if (target.type == 'image') {
			if (e.offsetX !== undefined) {
			form.clk_x = e.offsetX;
			form.clk_y = e.offsetY;
			} else if (typeof $.fn.offset == 'function') {
			var offset = $el.offset();
			form.clk_x = e.pageX - offset.left;
			form.clk_y = e.pageY - offset.top;
			} else {
			form.clk_x = e.pageX - target.offsetLeft;
			form.clk_y = e.pageY - target.offsetTop;
			}
		}
		// clear form vars
		setTimeout(function() { form.clk = form.clk_x = form.clk_y = null; }, 100)
	}

	// GREET AJAXFROM PLUGIN DEFINITION
	// =======================

	var old = $.fn.aform

	$.fn.aform = function (option) {
		return this.each(function () {
			var $this   = $(this)
			var data    = $this.data('ajaxform')
			var options = $.extend({}, $.fn.aform.defaults, $this.data(), typeof option == 'object' && option)
	
			if (!data) $this.data('ajaxform', (data = new Ajaxform(this, options)))
			if (typeof option == 'string') data[option]()
		})
	}

	$.fn.aform.defaults = {
		keyboard: true
		, loading: true
		, delegation: true
		, datatype: 'json'
		, type: 'POST'
		, beforeserialize: false
		, beforesubmit: false
		, resetform: false
		, clearform: false
		, button: false
		, clkbtn: false
		, target: false
		, success: false
		, context: false
		, error: false
		, complete: false
		, traditional: false
		, iframe: false
		, semantic: false
		, closekeepalive: false
		, extradata: false
		, replacetarget:'html'
		, includehidden: true
		, uploadprogress: false
	}

	$.fn.aform.Constructor = Ajaxform


	// GREET AJAXFROM NO CONFLICT
	// =================

	$.fn.aform.noConflict = function () {
		$.fn.aform = old
		return this
	}


   // GREET AJAXFROM DATA-API
   // ==============

	$(document).on('submit.ajaxform.data-api, click.ajaxform.data-api', '[data-toggle="ajaxform"]', function (e) {
		var $this = $(this)
		, $target = $this.data('form') || $this.parents('form')
		, option  = $.extend({}, $target.data(), $this.data())
	
		// if event has been canceled, don't proceed
		if (!e.isDefaultPrevented()) {
			e.preventDefault()
			
			option.clkbtn = $this
			$target.data('clkbtn', $this)
			$target.removeData('ajaxform').aform(option)
		}
	})

	$(document.body).on('submit.ajaxform.data-api', 'form', Ajaxform.prototype.captureSubmittingElement)
	$(document.body).on('click.ajaxform.data-api', 'form', Ajaxform.prototype.captureSubmittingElement)

}(window.jQuery);
