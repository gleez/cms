/* 
 * Pretty time-series line, bar and donut graphs
 * Requires morris js @link
 * https://github.com/gleez/greet
 * 
 * @package    Greet\Charts
 * @version    1.0
 * @requires   jQuery v1.9 or later
 * @author     Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2005-2015 Gleez Technologies
 * @license    The MIT License (MIT)
 * @link       https://github.com/morrisjs/morris.js
 *
 */

+function ($) {
	'use strict';

	// GREET CHART CLASS DEFINITION
	// ======================

	var Chart = function(element, options) {
		this.init(element, options)
	}

	Chart.prototype.init = function (element, options) {
		this.options   = options
		this.$element  = $(element).delegate('[data-dismiss="chart"]', 'click.dismiss.chart', $.proxy(this.stop, this))
		this.fire      = true
		this.loading   = false
		this.win 	   = $(window)
		this.doc       = $(document)
		this.chart     =

		this.start()
	}

	Chart.prototype.start = function () {
		this.fire    = true

		if( this.options.chart == 'donut'){
			this.donut()
		}
		
		if( this.options.chart == 'line'){
			//this.line()
		}
		
		if( this.options.chart == 'bar'){
			//this.bar()
		}
		
		this.getJson()
	}

	Chart.prototype.stop = function () {
		this.fire    = false
	}

	Chart.prototype.getJson = function(){
		//exit if no url
		if(this.options.url == false) return

		// set lock and Add loading indicator
		this.loading = true;
		this.$element.append('<div class="Loading">&#160;</div>')
		var that = this
		
		
		//do the ajax call
		$.ajax({
			url: this.options.url,
			type: "GET",
			async: false,
			dataType: 'json',
			cache: this.options.cache,
			beforeSend: function ( xhr ) {}
		}, 300)
		.done(function(data, textStatus, jqXHR){
			that.successHandler(data, that, jqXHR)
		})
		.fail(function (jqXHR, textStatus, errorThrown) {
			
		})
		.always(function () {
		    // No matter if request is successful or not, stop the spinner
			that.loading = false
			that.$element.find('.Loading').remove()
		});
	}

	Chart.prototype.successHandler = function (json, that, jqXHR){
		if( this.options.chart == 'donut'){
			this.chart.setData(json.data)
		}
		if( this.options.chart == 'line'){
			//this.chart.setData(json.data)
			this.line(json)
		}
		if( this.options.chart == 'bar'){
			//this.chart.setData(json.data)
			this.bar(json)
		}
	}

	Chart.prototype.donut = function(){
		this.chart = Morris.Donut({
			element: $(this.$element),
			// Set initial data (ideally you would provide an array of default data)
			data: [0, 0],
			resize: true
		})
		.on('click', function(i, row){
			console.log(i, row)
		})
	}

	Chart.prototype.line = function(json){
		
		this.chart = Morris.Line({
			element: $(this.$element),
			// Set initial data (ideally you would provide an array of default data)
			data: json.data,
			xkey: json.xkey,
			ykeys: json.ykeys,
			labels: json.labels,
			hideHover: 'auto',
			lineColors: (json.lineColors) ? json.lineColors : ['#428bca', 'grey'],
			preUnits: (json.preUnits) ? json.preUnits : '',
			resize: true
		})
		.on('click', function(i, row){
			console.log(i, row)
		})
	}
	
	Chart.prototype.bar = function(json){
		this.chart = Morris.Bar({
			element: $(this.$element),
			// Set initial data (ideally you would provide an array of default data)
			data: json.data,
			xkey: json.xkey,
			ykeys: json.ykeys,
			labels: json.labels,
			horizontal: true,
			hideHover: 'auto',
			resize: true
		})
		.on('click', function(i, row){
			console.log(i, row)
		})
	}
	
	Chart.DEFAULTS = {
		selector: false
		, url: false
		, cache: false
		, target: false
		, chart: 'donut'
	}

	// GREET CHART PLUGIN DEFINITION
	// =======================

	var old = $.fn.gchart

	$.fn.gchart = function (option) {
		return this.each(function () {
			var $this   = $(this)
			var data    = $this.data('gchart')
			var options = $.extend({}, Chart.DEFAULTS, $this.data(), typeof option == 'object' && option)
			
			if (!data) $this.data('gchart', (data = new Chart(this, options)))
			if (typeof option == 'string') data[option]()
		})
	}

	$.fn.gchart.Constructor = Chart

	// GREET CHART NO CONFLICT
	// =================

	$.fn.gchart.noConflict = function () {
		$.fn.gchart = old
		return this
	}

	// GREET CHART DATA-API
	// ==============

	$(window).on('load.gchart.data-api', function () {
		$('[data-toggle="chart"]').each(function () {
			var $target = $(this)
			$target.gchart($target.data())
		})
	})

	// Added pajax and jquery mobile support
	$(document).on('pjax:complete pagecontainerchange', function () {
		$('[data-toggle="chart"]').each(function () {
			var $target = $(this)
			$target.gchart($target.data())
		})
	})

}(jQuery);