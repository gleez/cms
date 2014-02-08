/*
 * This is a highly modified version of the bootstrap modal dialog.
 * https://github.com/gleez/greet
 * 
 * @package    Greet\Popup
 * @version    2.0
 * @requires   jQuery v1.9 or later
 * @author     Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2005-2014 Gleez Technologies
 * @license    The MIT License (MIT)
 *
 */

+function ($) { 'use strict';

	// GREET POPUP CLASS DEFINITION
	// ======================

	var Popup = function (element, options) {
		this.init(element, options)
	}

	Popup.prototype.init = function (element, options) {
		this.options     = options
		this.$element  	 = $(element)
		this.$backdrop 	 =
		this.isShown   	 = null
		this.modalParent = null
		this.forms 		 = false
		
		this.windowWidth	= $(window).width()
		this.windowHeight	= $(window).height()

		this.options.loading && this.loading()
		this.local()
		this.remote()
	}

	Popup.prototype.local = function () {
		//not implemented yet
	}

	Popup.prototype.remote = function () {
		if (this.options.remote){
			var that = this
		
			//do the ajax call
			$.ajax({
				url: this.options.remote,
				type: "GET",
				dataType: this.options.type,
				cache: this.options.cache,
			beforeSend: function ( xhr ) {}
			}, 300)
			.done(function(data, textStatus, jqXHR){
				that.show()
				that.reveal(data, that, jqXHR)
			})
			.fail(function (jqXHR, textStatus, errorThrown) {
				that.show()
				that.$element.find('.popup-title').text(textStatus)
				that.$element.find('.popup-body').text(errorThrown)
			})
		}
	}

	Popup.prototype.reveal = function (data, popup, jqXHR) {
		var json = false

		this.dataTable()
		
		// First see if we've retrieved json or something else
		try {
			json = $.parseJSON(jqXHR.responseText)
		} catch (e) {
			json = false
			console.log(e)
		}

		if (json && typeof json.Body !== undefined) {
			data = $.base64Decode(json.Body)

			if (typeof json.title !== undefined){
				this.options.title = json.title || this.options.title
			}
		}

		var $data = $($.parseHTML(data))

		// Now, if there are any forms in the popup, hijack them if necessary.
		// Pass the popup object to the form as data() to handle popup events from form
		if (data && this.options.consumeform) {
			this.forms = $data.filter('form')
			
			//if only one form, remove and create custom buttons in popup footer
			if(this.forms.length == 1){
				this.singleForm($data)
			}
			else if(this.forms.length > 1){
				this.multipleForms($data)
			}
		}

		// Prevent blank popups
		if (data && (!this.forms || typeof this.forms !== undefined)) {
			this.$element.find('.popup-title').html(this.options.title)
			this.$element.find('.popup-body').html($data)
		}

		// Trigger an event then plugins can attach to when popups are revealed.
		this.$element.trigger('reveal.popup')
	}

	Popup.prototype.singleForm = function (response) {
		var sButton
		,   cButton
		,   sText
		,   cText
		,   sAttr
		,   cAttr
		,   submitBtn = $('<a>Save changes</a>')
		,   closeBtn  = $('<a>Close</a>')
		,   e         = $.Event('form.show.popup')

		this.$element.trigger(e)
		
		//Do auto valid submit button detection
		sButton = response.find('[type=submit]')
				 .not('input[name^="no"]')
				 .not('input[name^="cancel"]')
				 .not('input[name$="no"]')
				 .not('input[name$="cancel"]')
				 .first()

		//Do auto valid cancel button detection
		cButton = response
				.find('[type=submit]input[name^="no"],[type=submit]input[name^="cancel"],[type=submit]input[name$="no"],[type=submit]input[name$="cancel"]')
				.first()

		//hide all buttons in popup body
		response.find('[type=submit]').hide()
		response.find('[type=button]').hide()
		response.find('.form-actions').hide()
		response.find('.form-group').hide()

		//add the popup element to form data
		$(this.forms).attr('data-popup', 'true')
					.data('popup',	 this.$element)
					.data('datatable',	 this.options.table)

		//Generate a valid buttons text
		sText = $(sButton).val() || 'Save changes'
		cText = $(cButton).val() || 'Close'
		sAttr = $(sButton).attr('class') || 'btn btn-primary'
		cAttr = $(cButton).attr('class') || 'btn'
		
		//create submit and cancel buttons in popup footer
		$(submitBtn).attr('data-toggle', 'ajaxform')
				.attr('class',  sAttr)
				.attr('href',   '#')
				.data('popup',  this.$element)
				.data('form',   this.forms)
				.data('button', sButton)
				.text(sText)
		
		$(closeBtn).attr('class',  cAttr)
				.attr('href', '#')
				.attr('data-dismiss', 'popup')
				.text(cText)
				.delegate('[data-dismiss="popup"]', 'click.dismiss.popup', $.proxy(this.hide, this))

		//Add the content and title to popup and buttons to footer
		this.$element.find('.popup-title').html(this.options.title)
		this.$element.find('.popup-body').html(response)
		this.$element.find('.popup-footer').html(closeBtn).append(submitBtn)
		
		this.$element.trigger('form.shown.popup')
	}

	Popup.prototype.multipleForms = function (response) {
		var e = $.Event('forms.show.popup')
		this.$element.trigger(e)
		
		//the valid submit buttons
		response.find('[type=submit]')
			.not('input[name^="no"]')
			.not('input[name^="cancel"]')
			.not('input[name$="no"]')
			.not('input[name$="cancel"]')
			.attr('data-toggle', 'ajaxform')
			.data('popup', popup.$element)

		//add close handler to no/cancel buttons
		var buttons = response.find('[type=submit]input[name^="no"], [type=submit]input[name^="cancel"]')
		$(buttons).attr('data-dismiss', 'popup')
				.delegate('[data-dismiss="popup"]', 'click.dismiss.popup', $.proxy(this.hide, this))
		
		//add the content and title to popup
		this.$element.find('.popup-title').html(this.options.title)
		this.$element.find('.popup-body').html(response)
	}

	Popup.prototype.dataTable = function () {
		if (this.options.consumedt && this.options.table){
			var dTable = $(this.options.table)
			this.options.table = false
			if(dTable.length > 0) this.options.table = dTable
		}
		else if (this.options.consumedt && !this.options.table){
			var dTable = $(this.options.click).closest('table.dataTable')
			if(dTable.length > 0) this.options.table = dTable
		}
	}

	Popup.prototype.tab = function () {
		var that = this;

		if (this.isShown && this.options.consumetab) {
			this.$element.on('keydown.tabindex.popup', '[data-tabindex]', function (e) {
				if (e.keyCode && e.keyCode == 9){
					var $next = $(this)
					,   $rollover = $(this)
					
					that.$element.find('[data-tabindex]:enabled:not([readonly])').each(function (e) {
						if (!e.shiftKey){
							$next = $next.data('tabindex') < $(this).data('tabindex') ?
								$next = $(this) :
								$rollover = $(this);
						} else {
							$next = $next.data('tabindex') > $(this).data('tabindex') ?
								$next = $(this) :
								$rollover = $(this);
						}
					})
					
					$next[0] !== $(this)[0] ? $next.focus() : $rollover.focus()
					
					e.preventDefault()
				}
			})
		} else if (!this.isShown) {
			this.$element.off('keydown.tabindex.popup')
		}
	}

	Popup.prototype.removeLoading = function () {
		this.$loading.remove()
		this.$loading = null
		this.isLoading = false
	}

	Popup.prototype.loading = function (callback) {
		callback = callback || function () {}

		var animate = this.$element.hasClass('fade') ? 'fade' : '';

		if (!this.isLoading) {
			var doAnimate = $.support.transition && animate;

			this.$loading = $('<div class="loading-spinner ' + animate + '">')
				.append(this.options.spinner)
				.appendTo(document.body)

			if (doAnimate) this.$loading[0].offsetWidth; // force reflow

			this.$loading.addClass('in')

			this.isLoading = true

			doAnimate ?
				this.$loading.one($.support.transition.end, callback) :
				callback();
		} else if (this.isLoading && this.$loading) {
			this.$loading.removeClass('in');

			var that = this;
			$.support.transition && this.$element.hasClass('fade')?
				this.$loading.one($.support.transition.end, function () { that.removeLoading() }) :
				this.removeLoading();

		} else if (callback) {
			callback(this.isLoading)
		}
	}

	Popup.prototype.layout = function () {
		if (this.options.width && this.windowWidth > 768){
			this.$element.find('.popup-dialog').css('width', this.options.width)
			var that = this
			this.$element.find('.popup-dialog').css('margin-left', function () {
				if (/%/ig.test(that.options.width)){
					return -(parseInt(that.options.width) / 2) + '%'
				} else {
					return -($(this).width() / 2) + 'px'
				}
			})
		}
		else if (this.options.width && this.windowWidth < 769){
			this.$element.find('.popup-dialog').css('width', 'auto')
			this.$element.find('.popup-dialog').css('margin-left', '0')
		}
		
		if (this.options.height){
			var prop = this.options.height ? 'height' : 'max-height'
			, value  = this.options.height || this.options.maxHeight
			
			if (value){
				this.$element.find('.popup-body')
				.css('overflow', 'auto')
				.css(prop, value)
			}
		}

		if (this.options.modaloverflow){
			var modalOverflow = $(window).height() - 10 < this.$element.height()
			if (modalOverflow || this.options.modaloverflow) {
				this.$element
					.css('margin-top', 0)
					.addClass('popup-overflow')
			}
		}
	}

	Popup.prototype.modalResize = function () {
		if (!this.isShown) return
		if (!this.options.width && !this.options.height) return
		
		this.windowWidth  = $(window).width()
		this.windowHeight = $(window).height()
		
		this.layout()
	}

	Popup.prototype.toggle = function (_relatedTarget) {
		return this[!this.isShown ? 'show' : 'hide'](_relatedTarget)
	}

	Popup.prototype.show = function (_relatedTarget) {
		var that = this
		var e    = $.Event('show.popup', { relatedTarget: _relatedTarget })

		this.$element.trigger(e)

		if (this.isShown || e.isDefaultPrevented()) return

		this.isShown = true

		this.keys()

		this.tab()

		this.options.loading && this.loading()
		$(window).on('resize.popup.data-api', $.proxy(this.modalResize, this))

		this.$element.on('click.dismiss.popup', '[data-dismiss="popup"]', $.proxy(this.hide, this))

		this.backdrop(function () {
			var transition = $.support.transition && that.$element.hasClass('fade')
	  
			if (!that.$element.parent().length) {
				that.layout()
				that.$element.appendTo(that.options.manager) //don't move modals dom position
			}

			if (that.findModalParent()) {
				// put parentModal behind the backdrop
				that.modalParent.$element.css('z-index', 0)

				that.modalParent.$element.off('keyup.dismiss.popup')
				$(document).off('focusin.popup')

				that.modalParent.$element.on('hidden.popup.submodal', function(e) {
					if (e.target === that.modalParent.$element[0]) {
						that.modalParent = null
					}
				})
			}

			that.$element.show()
	  
			if (transition) {
				that.$element[0].offsetWidth // force reflow
			}
	  
			that.$element
			.addClass('in')
			.attr('aria-hidden', false)
	  
			that.enforceFocus()

			var e = $.Event('shown.popup', { relatedTarget: _relatedTarget })

			transition ?
				that.$element.find('.popup-dialog') // wait for modal to slide in
				.one($.support.transition.end, function () {
					that.$element.focus().trigger(e)
				})
				.emulateTransitionEnd(300) :
				that.$element.focus().trigger(e)
		})
	}

	Popup.prototype.hide = function (e) {
		if (e) e.preventDefault()

		e = $.Event('hide.popup')

		this.$element.trigger(e)

		if (!this.isShown || e.isDefaultPrevented()) return

		this.isShown = false

		this.keys()

		this.tab()

		this.isLoading && this.loading();

		$(document).off('focusin.popup')

		this.$element
		  .removeClass('in')
		  .attr('aria-hidden', true)

		$.support.transition && this.$element.hasClass('fade') ?
		  this.hideWithTransition() :
		  this.hidePopup()
	}

	Popup.prototype.enforceFocus = function () {
		var that = this
		$(document)
		.off('focusin.popup') // guard against infinite focus loop
		.on('focusin.popup', function (e) {
			if (that.$element[0] !== e.target && !$.contains(that.$element[0], e.target)) {
				that.$element.focus()
			}
		})
	}

	Popup.prototype.escape = function () {
		var that = this
		if (this.isShown && this.options.keyboard) {
			this.$element.on('keyup.dismiss.popup', function (e) {
				e.which == 27 && that.hide()
			})
		} else if (!this.isShown) {
			this.$element.off('keyup.dismiss.popup')
		}
	}

	Popup.prototype.keys = function () {
		var that = this
		if (this.isShown && this.options.keyboard) {
			if (!this.$element.attr('tabindex')) this.$element.attr('tabindex', -1);

			this.$element.on('keyup.dismiss.popup', function (e) {
				e.which == 27 && that.hide();
			});
		} else if (!this.isShown) {
			this.$element.off('keyup.dismiss.popup')
		}
	}

	Popup.prototype.hideWithTransition = function () {
		var that    = this
		var timeout = setTimeout(function () {
		  that.$element.off($.support.transition.end)
		  that.hidePopup()
		}, 500)

		this.$element.one($.support.transition.end, function () {
		  clearTimeout(timeout)
		  that.hidePopup()
		})
	}

	Popup.prototype.hidePopup = function () {
		var that  = this
		var prop  = this.options.height ? 'height' : 'max-height';
		var value = this.options.height || this.options.maxHeight;

		if (value){
			this.$element.find('.popup-body')
				.css('overflow', '')
				.css(prop, '');
		}

		this.$element.hide()
		this.backdrop(function () {
			if (that.modalParent) {
				that.modalParent.$element.css('z-index', '')
				that.modalParent.$element.off('hidden.popup.submodal')
				that.modalParent.escape()
				that.modalParent.enforceFocus()
				that.modalParent.$element.focus()
				that.modalParent = null
			}

			that.$element.trigger('hidden.popup')

			//destroy the popup for remote modals
			if(that.options.remote) that.$element.remove()
		})
	}

	Popup.prototype.removeBackdrop = function () {
		this.$backdrop && this.$backdrop.remove()
		this.$backdrop = null
		$(document.body).removeClass('popup-open')
	}

	Popup.prototype.backdrop = function (callback) {
		var that    = this
		var animate = this.$element.hasClass('fade') ? 'fade' : ''
		var count   = 0

		if (this.isShown && this.options.backdrop) {
			var $inBackdrops = $('.popup-backdrop.in')

			if ($inBackdrops.length) {
				//skip adding new backdrop, reuse the existing
				this.$backdrop = $inBackdrops.first()
				count = this.$backdrop.data('popup.refcount') || 0
				this.$backdrop.data('popup.refcount', ++count)

				callback && callback()
				return
			}

			var doAnimate = $.support.transition && animate

			$(document.body).addClass('popup-open')

			this.$backdrop = $('<div class="popup-backdrop ' + animate + '" />')
			.appendTo(document.body)

			this.$element.on('click.dismiss.popup', function(e) {
				if (e.target !== e.currentTarget) return
				if (that.options.backdrop !== 'static') that.hide()
			})

			if (doAnimate) this.$backdrop[0].offsetWidth // force reflow

			this.$backdrop.addClass('in')

			if (!callback) return

			doAnimate ?
			  this.$backdrop.one($.support.transition.end, callback) :
			  callback()
		} else if (!this.isShown && this.$backdrop) {
			count = this.$backdrop.data('popup.refcount')
			if (count) {
				this.$backdrop.data('popup.refcount', --count)
				callback && callback()
				return
			}

			this.$backdrop.removeClass('in')

			if ($.support.transition && this.$element.hasClass('fade')) {
				this.$backdrop
				.one($.support.transition.end, function() {
					that.removeBackdrop()
					callback && callback()
				})
				.emulateTransitionEnd(150)
			} else {
				this.removeBackdrop()
				callback && callback()
			}
		} else if (callback) {
			callback()
		}
	}

	/**
	* Try to find the modal dialog, that was shown before this one.
	* updates the this.modalParent property.
	*/
	Popup.prototype.findModalParent = function() {
		var that = this
		$('.popup.in').each(function() {
			var $elem = $(this)
			var data = $elem.data('popup')
			if (data && data.isShown) {
				if (!that.modalParent || $elem.css('z-index') > that.modalParent.$element.css('z-index')) {
					that.modalParent = data
				}
			}
		})

		return this.modalParent ? true : false
	}

	// GREET POPUP PLUGIN DEFINITION
	// =======================

	var old = $.fn.popup

	$.fn.popup = function (option) {
		return this.each(function () {
			var $this   = $(this)
			var data    = $this.data('popup')
			var options = $.extend({}, $.fn.popup.defaults, $this.data(), typeof option == 'object' && option)

			if (!data) $this.data('popup', (data = new Popup(this, options)))
			if (typeof option == 'string') data[option](_relatedTarget)
			else if (options.show) data.show(_relatedTarget)
		})
	}

	$.fn.popup.defaults = {
	  backdrop: true
	  , keyboard: true
	  , loading: true
	  , show: false
	  , cache: false
	  , width : false
	  , height : false
	  , minWidth : 100
	  , minHeight : 100
	  , maxWidth : 9999
	  , maxHeight : 9999
	  , modaloverflow: false
	  , consumetab: true
	  , consumeform: true
	  , consumedt: true
	  , table: false
	  , focusOn: false
	  , replace: false
	  , resize: false
	  , click: false
	  , type: 'json'
	  , manager: 'body'
	  , icon: false
	  , title: '&nbsp;'
	  , spinner:  '<div class="InProgress">&nbsp</div>'
	  , template: '<div id="{popup.id}" class="popup fade" tabIndex="-1" role="dialog"><div class="popup-dialog"><div class="popup-content"><div class="popup-header"><button type="button" class="close" data-dismiss="popup">&times;</button><h4 class="popup-title">&nbsp;</h4></div><div class="popup-body"></div><div class="popup-footer"></div></div></div></div>'
	}

	$.fn.popup.Constructor = Popup


	// GREET POPUP NO CONFLICT
	// =================

	$.fn.popup.noConflict = function () {
		$.fn.popup = old
		return this
	}

	// GREET POPUP DATA-API
	// ==============

	$(document).on('click.popup.data-api', '[data-toggle="popup"]', function (e) {
		var $this   = $(this)
		var href    = $this.attr('href')
		var $target = $($.fn.popup.defaults.template.replace('{popup.id}', 'popup-'+ new Date().getTime()))
		var option  = $.extend({ remote:!/#/.test(href) && href }, $target.data(), $this.data())

		e.preventDefault()

		//reference the click handler for further use
		option.click = $this

		$target
			.popup(option)
			.one('hide', function () {
			$this.focus()
		})
	})

}(jQuery);