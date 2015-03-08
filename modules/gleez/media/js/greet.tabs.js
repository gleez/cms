 /*
 * Loading remote data into tab.
 * When your tabs do not fit in a single row.
 * https://github.com/gleez/greet
 * 
 * @package    Greet\Tabs
 * @version    2.1
 * @requires   jQuery v1.9 or later
 * @author     Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2005-2015 Gleez Technologies
 * @license    The MIT License (MIT)
 *
 */

+function ($) {
  'use strict';

	// TABS CLASS DEFINITION
	// ====================

	var Tabs = function (element, options) {
		this.$element  = $(element)
		this.options  = options
		this.collection
		this.dropDown
		this.tabTimer
		this.resizeTimer
	}

	Tabs.DEFAULTS = {
		  text      : '<i class="fa fa-align-justify"></i>'
		, delay     : 100
		, type      : 'json'
		, cache     : false
		, remote    : false
		, container : false
		, tabdelay  : false
		, tabreload : true
		, tabreshow : true
	}

	// Load remote data into tab content
	Tabs.prototype.ajax = function () {
		var href = this.$element.attr('href')

		if(href && /#/.test(href)){
			this.options.container = $('#' + href.split('#')[1])
		}

		if(href && !/#/.test(href)){
			this.options.remote = href
		}

		this.tabTimer()
	}

	Tabs.prototype.remote = function () {
		if (this.options.remote && this.options.tabreload){
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
				that.reveal(data, that, jqXHR)
			})
			.fail(function (jqXHR, textStatus, errorThrown) {
				//that.show()
			})
		}
	}

	Tabs.prototype.reveal = function (data, tabs, jqXHR) {
		var json = false

		// First see if we've retrieved json or something else
		try {
			json = $.parseJSON(jqXHR.responseText)
		} catch (e) {
			json = false
			console.log(e)
		}

		if (json && typeof json.Body !== undefined) {
			data = $.base64Decode(json.Body)
		}

		var $data = $($.parseHTML(data))

		if (data && this.options.container) {
			this.options.container.empty().html($data)
		}

		// Trigger an event then plugins can attach to when tabs are shown.
		this.$element.trigger('reveal.gt.tabs')
	}

	// Move Tabs to dropdown if tabs do not fit in a single row
	Tabs.prototype.drop = function () {
		this.dropDown = this.$element.find('>li.tabdrop').not('.no-tabdrop')
		this.collection = []

		if(this.dropDown.length == 0){
			this.dropDown = $('<li class="dropdown hide pull-right tabdrop"><a class="dropdown-toggle" data-toggle="dropdown" href="#">'+this.options.text+' <b class="caret"></b></a><ul class="dropdown-menu"></ul></li>')
							.prependTo(this.$element)
		}

		$(window).on('resize.tabs.data-api', $.proxy(this.resizeTimer, this))
		this.resizeLayout()
	}

	Tabs.prototype.resizeLayout = function () {
		var that = this
		this.dropDown.removeClass('hide')

		// Find the extra tabs to push to dropdown
		this.$element.find('>li')
			.not('.tabdrop')
			.each(function(){
				if(this.offsetTop > 0) {
					that.collection.push(this)
				}
			})

		if (this.collection.length > 0) {
			this.dropDown
					.find('ul.dropdown-menu')
					.empty()
					.append(this.collection)
		}
		else if(this.dropDown.find('ul.dropdown-menu>li').length == 0){
			this.dropDown.addClass('hide')
		}
	}

	// Delay the tab remote ajax cal with 100
	Tabs.prototype.tabTimer = function () {
		// delay the json call if it has been given a value
		if(this.options.tabdelay) {
			var that = this
			clearTimeout(this.tabTimer)

			this.tabTimer = setTimeout(function(){that.remote()}, this.options.tabdelay)
		} else {
			this.remote()
		}
	}
	
	// Delay the resize layout with 100
	Tabs.prototype.resizeTimer = function () {
		if(this.options.delay) {
			var that = this
			clearTimeout(this.resizeTimer)
			
			this.resizeTimer = setTimeout(function(){that.resizeLayout()}, this.options.delay)
		}
	}

	// Try to navigate to the tab/accordion last given in the URL
	Tabs.prototype.reShow = function () {
		var hash       = document.location.hash
		, hasTab       = false
		, hasAccordion = false

		if (hash && this.options.tabreshow) {
			hasTab       = $('[data-toggle=tab][href='+hash+']')
			hasAccordion = $('[data-toggle=collapse][href='+hash+']')

			if (hasTab) {
				hasTab.tab('show')
			}

			if (hasAccordion) {	
				// for some reason we cannot execute the 'show' event for an accordion properly, so here's a workaround
				if (hasAccordion[0] != $('[data-toggle=collapse]:first')[0]) {
					hasAccordion.click()
				}
			}
		}
	}

	// GREET TABS PLUGIN DEFINITION
	// =======================

	var old = $.fn.tabs

	$.fn.tabs = function (option) {
		return this.each(function () {
			var $this   = $(this)
			var data    = $this.data('tabs')
			var options = $.extend({}, Tabs.DEFAULTS, $this.data(), typeof option == 'object' && option)

			if (!data) $this.data('tabs', (data = new Tabs(this, options)))
			if (typeof option == 'string') data[option]()
		})
	}

	$.fn.tabs.Constructor = Tabs

	// GREET TABS NO CONFLICT
	// =================

	$.fn.tabs.noConflict = function () {
		$.fn.tabs = old
		return this
	}

	// GREET TABS DATA-API
	// ==============

	// Load remote data into tab content
	$(document).on('click.tabs.data-api', '[data-provider="tabs"]', function (e) {
		e.preventDefault()
		$(this).tabs('ajax')
	})

	// Move Tabs to dropdown if tabs do not fit in a single row
	$(window).on('load.tabs.data-api', function (e) {
		$('[data-toggle="tabdrop"]').each(function () {
			$(this).tabs('drop')
		})
	})

	// Added pajax and jquery mobile support
	$(document).on('pjax:complete pagecontainerchange', function (e) {
		$('[data-toggle="tabdrop"]').each(function () {
			$(this).tabs('drop')
		})
	})

}(jQuery);