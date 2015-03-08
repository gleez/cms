/*
 * This is a highly modified version of the bootstrap Typeahead.
 * https://github.com/gleez/greet
 * 
 * @package    Greet\Typeahead
 * @version    2.0
 * @requires   jQuery v1.9 or later
 * @author     Sandeep Sangamreddi - Gleez
 * @copyright  (c) 2005-2015 Gleez Technologies
 * @license    The MIT License (MIT)
 *
 */

+function ($) {
	'use strict';

	/* GREET TYPEAHEAD PUBLIC CLASS DEFINITION
	 * ================================= */

	var Typeahead = function (element, options) {
		this.$element = $(element)
		this.options  = $.extend({}, $.fn.typeahead.defaults, options)
		this.$menu    = $(this.options.menu)
		this.shown    = false
		this.results  = {}
		
		this.matcher     = this.options.matcher || this.matcher
		this.sorter      = this.options.sorter  || this.sorter
		this.updater     = this.options.updater || this.updater
		this.source      = this.options.source  || this.source
		this.grepper     = this.options.grepper || this.grepper
		this.render      = this.options.render  || this.render
		this.select      = this.options.select  || this.select
		this.highlighter = this.options.highlighter || this.highlighter
		
		this.hiddenElementID  = this.options.field || false
	  
		if (!this.source.length) {
		  this.ajax = $.extend({}, $.fn.typeahead.defaults, { url: this.options.url })
		}
	  
		this.listen()
	}

	Typeahead.prototype.select = function () {
		var val = this.$menu.find('.active').attr('data-value')
		this.$element
		  .val(this.updater(val))
		  .change()
		return this.hide()
	}
  
	Typeahead.prototype.hiddenElement = function (label, value) {
	  //set value in hidden element if its set
	  if(label && value){
		$('input[name='+label+']').val(value)
	  }
	}
  
	Typeahead.prototype.updater = function (item) {
	  var terms = this.autocompleteSplit(this.query)
  
	  if(typeof this.results[item] !== 'undefined'){
		var data  = this.results[item]
		
		if (data.hasOwnProperty(this.options.value)) {
	  var value = data[this.options.value]
	  this.hiddenElement(this.hiddenElementID, value)
		}
	  }
	  
	  // Remove the current input.
	  terms.pop()
	  
	  // Add the selected item.
	  terms.push(item)
  
	  return terms.join(", ")
	}

	Typeahead.prototype.show = function () {
	  var pos = $.extend({}, this.$element.position(), {
		height: this.$element[0].offsetHeight
	  })
  
	  this.$menu
		.insertAfter(this.$element)
		.css({
	  top: pos.top + pos.height
		, left: pos.left
		, width: this.$element.innerWidth() + 'px'
		})
		.show()
  
	  this.shown = true
	  return this
	}
  
	Typeahead.prototype.hide = function () {
	  this.$menu.hide()
	  this.shown = false
	  return this
	}
  
	Typeahead.prototype.lookup = function (event) {
	  var items
	  if (this.ajax) {
		this.ajaxer()
	  }
	  else {
		this.query = this.$element.val()
	
		if (!this.query || this.query.length < this.options.minLength) {
	  return this.shown ? this.hide() : this
		}
	
		items = $.isFunction(this.source) ? this.source(this.query, $.proxy(this.process, this)) : this.source
	
		return items ? this.process(items) : this
	  }
	}

	Typeahead.prototype.process = function (items) {
	  var that = this
  
	  items = $.grep(items, function (item) {
		return that.matcher(item)
	  })
  
	  items = this.sorter(items)
  
	  if (!items.length) {
		return this.shown ? this.hide() : this
	  }
  
	  return this.render(items.slice(0, this.options.items)).show()
	}
  
	Typeahead.prototype.matcher = function (item) {
	  var term = this.autocompleteExtractLast(this.query);
	  return ~item.toLowerCase().indexOf(term.toLowerCase())
	}

	// Filters relevent results
	Typeahead.prototype.grepper = function(data) {
		var that = this
  
		if (data && data.length && !data[0].hasOwnProperty(that.options.display)) {
		  return null
		}
		
		var items = [];
		// Gleez cms returns an object array, but we need a string array.
		$.map(data, function(result, item){
		  var label
		  ,	  save = false
		  
		  //check if the result is object or string
		  if(typeof result == 'string' || result instanceof String){
		label = result
		  }
		  else if (result.hasOwnProperty(that.options.display)) {
		label = result[that.options.display]
		save =  true
		  }
		  
		  //for each item returned, if the display name is already included 
		  //(e.g. multiple "John Smith" records) then add a unique value to the end
		  //so that the user can tell them apart.
		  if(that.in_array(label, that.results)){
			label = label + ' #' + new Date().getTime();
		  }
		  
		  items.push(label)
		  
		  //also store a mapping to get from label back to object
		  if(save){
			that.results[label] = result;
		  }
		})
	
		items = $.grep(items, function (item) {
		  return that.matcher(item)
		})
	
		return this.sorter(items)
	}
  
	Typeahead.prototype.sorter = function (items) {
		var beginswith = []
		, caseSensitive = []
		, caseInsensitive = []
		, item
	
		while (item = items.shift()) {
			if (!item.toLowerCase().indexOf(this.query.toLowerCase())) beginswith.push(item)
			else if (~item.indexOf(this.query)) caseSensitive.push(item)
			else caseInsensitive.push(item)
		}
	
		return beginswith.concat(caseSensitive, caseInsensitive)
	}

	Typeahead.prototype.smartHighlighter = function (item) {
		var data = this.results[item]
		, markup = "<div class='typeahead_wrapper'>"
		
		if (data.image !== undefined) {
		  markup += "<img class='typeahead_photo' src='" + data.image + "' />";
		}
		
		if (data.name !== undefined) {
		  markup += "<div class='typeahead_primary'>" + data.name + "</div>";
		}
		
		if (data.group !== undefined) {
		  markup += "<div class='typeahead_secondary'>" + data.group + "</div>";
		}
		
		markup +="</div>";
		
		return markup
	}
  
	Typeahead.prototype.highlighter = function (item) {
		var term = this.autocompleteExtractLast(this.query);
		var query = term.replace(/[\-\[\]{}()*+?.,\\\^$|#\s]/g, '\\$&')
		return item.replace(new RegExp('(' + query + ')', 'ig'), function ($1, match) {
			return '<strong>' + match + '</strong>'
		})
	}
	
	Typeahead.prototype.render = function (items) {
		var that = this
	
		items = $(items).map(function (i, item) {
			i = $(that.options.item).attr('data-value', item)
			i.find('a').html(that.highlighter(item))
			return i[0]
		})
	
		items.first().addClass('active')
		this.$menu.html(items)
		return this
	}
  
	Typeahead.prototype.next = function (event) {
		var active = this.$menu.find('.active').removeClass('active')
		  , next = active.next()
	
		if (!next.length) {
			next = $(this.$menu.find('li')[0])
		}
	
		next.addClass('active')
	}
  
	Typeahead.prototype.prev = function (event) {
		var active = this.$menu.find('.active').removeClass('active')
		  , prev = active.prev()
	
		if (!prev.length) {
			prev = this.$menu.find('li').last()
		}
	
		prev.addClass('active')
	}

	Typeahead.prototype.listen = function () {
		this.$element
		  .on('focus',    $.proxy(this.focus, this))
		  .on('blur',     $.proxy(this.blur, this))
		  .on('keypress', $.proxy(this.keypress, this))
		  .on('keyup',    $.proxy(this.keyup, this))
	
		if (this.eventSupported('keydown')) {
			this.$element.on('keydown', $.proxy(this.keydown, this))
		}
	
		this.$menu
		  .on('click', $.proxy(this.click, this))
		  .on('mouseenter', 'li', $.proxy(this.mouseenter, this))
		  .on('mouseleave', 'li', $.proxy(this.mouseleave, this))
	}
  
	Typeahead.prototype.eventSupported = function(eventName) {
		var isSupported = eventName in this.$element
		if (!isSupported) {
			this.$element.setAttribute(eventName, 'return;')
			isSupported = typeof this.$element[eventName] === 'function'
		}
		return isSupported
	}

	Typeahead.prototype.move = function (e) {
		if (!this.shown) return
	
		switch(e.keyCode) {
		  case 9: // tab
		  case 13: // enter
		  case 27: // escape
			e.preventDefault()
		break
	
		  case 38: // up arrow
			e.preventDefault()
			this.prev()
		break
	
		  case 40: // down arrow
			e.preventDefault()
			this.next()
		break
		}
	
		e.stopPropagation()
	}
  
	Typeahead.prototype.keydown = function (e) {
		this.suppressKeyPressRepeat = ~$.inArray(e.keyCode, [40,38,9,13,27])
		this.move(e)
	}

	Typeahead.prototype.keypress = function (e) {
		if (this.suppressKeyPressRepeat) return
		this.move(e)
	}

	Typeahead.prototype.keyup = function (e) {
		switch(e.keyCode) {
		  case 40: // down arrow
		  case 38: // up arrow
		  case 16: // shift
		  case 17: // ctrl
		  case 18: // alt
		break
	
		  case 9: // tab
		  case 13: // enter
			  if (!this.shown) return
			  this.select()
		break
	
		  case 27: // escape
			  if (!this.shown) return
			  this.hide()
		break
	
		  default:
			  this.lookup()
		}
  
		e.stopPropagation()
		e.preventDefault()
	}

	Typeahead.prototype.focus = function (e) {
		this.focused = true
	}
  
	Typeahead.prototype.blur = function (e) {
		this.focused = false
		if (!this.mousedover && this.shown) this.hide()
	}

	Typeahead.prototype.click = function (e) {
		e.stopPropagation()
		e.preventDefault()
		this.select()
		this.$element.focus()
	}

	Typeahead.prototype.mouseenter = function (e) {
		this.mousedover = true
		this.$menu.find('.active').removeClass('active')
		$(e.currentTarget).addClass('active')
	}

	Typeahead.prototype.mouseleave = function (e) {
		this.mousedover = false
		if (!this.focused && this.shown) this.hide()
	}

	// Handle AJAX source
	Typeahead.prototype.ajaxer = function () {
	  var that = this
	  , query = that.$element.val()
	  
	  if (query === that.query) {
		return that
	  }
  
	  // Query changed
	  that.query = query
	  this.results  = {}
	  this.hiddenElement(this.hiddenElementID, '0')
  
	  // Cancel last timer if set
	  if (that.ajax.timerId) {
		clearTimeout(that.ajax.timerId)
		that.ajax.timerId = null
	  }
	  
	  if (!query || query.length < that.ajax.triggerLength) {
		// Cancel the ajax callback if in progress
		if (that.ajax.xhr) {
			that.ajax.xhr.abort()
			that.ajax.xhr = null
			that.ajaxToggleLoadClass(false)
		}
  
		return that.shown ? that.hide() : that
	  }
	  
	  // Query is good to send, set a timer
	  that.ajax.timerId = setTimeout(function() {
		$.proxy(that.ajaxExecute(query), that)
	  }, that.ajax.timeout)
	
	  return that
	}

	// Execute an AJAX request
	Typeahead.prototype.ajaxExecute = function(query) {
		var that = this
		
		this.ajaxToggleLoadClass(true)
		
		// Cancel last call if already in progress
		if (this.ajax.xhr) this.ajax.xhr.abort()
		
		// Get the desired term and construct the autocomplete URL for it.
		var term = this.autocompleteExtractLast(query)
		
		var params = this.ajax.preDispatch ? this.ajax.preDispatch(query) : ''
		var jAjax  = (this.ajax.method === "post") ? $.post : $.get
		var url    = this.ajax.url+'/'+ this.URLEncode(term)
		
		this.ajax.xhr = jAjax(url, params, $.proxy(this.ajaxLookup, this))
		
		this.ajax.xhr.fail(function (jqXHR, textStatus, errorThrown) {
			//remove the throbbing class
			that.ajaxToggleLoadClass(false)
			alert(errorThrown)
		})
		
		this.ajax.timerId = null
	}

	// Perform a lookup in the AJAX results
	Typeahead.prototype.ajaxLookup = function (data) {
		var items
		
		this.ajaxToggleLoadClass(false)
	  
		if (!this.ajax.xhr) return
		
		if (this.ajax.preProcess) {
			data = this.ajax.preProcess(data)
		}
	  
		// Save for selection retrevial
		this.ajax.data = data
	  
		items = this.grepper(this.ajax.data)
	  
		if (!items || !items.length) {
			return this.shown ? this.hide() : this
		}
	  
		this.ajax.xhr = null
	
		return this.render(items.slice(0, this.options.items)).show()
	}

  // Toggle the loading class
	Typeahead.prototype.ajaxToggleLoadClass = function (enable) {
		if (!this.ajax.loadingClass) return
		this.$element.toggleClass(this.ajax.loadingClass, enable)
	}
	
	Typeahead.prototype.in_array = function (needle, haystack) {
		for(var i in haystack) {
		  if(haystack[i] == needle) return true
		}
		return false
	}
  
	Typeahead.prototype.autocompleteSplit = function(val) {
		return val.split(/,\s*/)
	}
	
	Typeahead.prototype.autocompleteExtractLast = function(term) {
		return this.autocompleteSplit(term).pop()
	}

	Typeahead.prototype.URLEncode = function (s) {
		s = encodeURIComponent (s);
		//s = s.replace(/[\-\[\]{}()*+?.,\\\^$|#\s]/g, "\\$&");
		////s = s.replace (/\~/g, '%7E').replace (/\!/g, '%21').replace (/\(/g, '%28').replace (/\)/g, '%29').replace (/\'/g, '%27');
		////s = s.replace (/%20/g, '+');
		s = s.replace (/%2F/g, '/'); //escape slash for admin/menu autocomplete
		return s
	}
  
	Typeahead.prototype.URLDecode = function (s) {
	  ////s = s.replace (/\+/g, '%20');
	  s = s.replace (/\//g, '%2F')
	  s = decodeURIComponent (s)
	  return s;
	}

	/* GREET TYPEAHEAD PLUGIN DEFINITION
	 * =========================== */
  
	var old = $.fn.typeahead
  
	$.fn.typeahead = function (option) {
		return this.each(function () {
		  var $this = $(this)
		, data = $this.data('typeahead')
		, options = typeof option == 'object' && option
		  if (!data) $this.data('typeahead', (data = new Typeahead(this, options)))
		  if (typeof option == 'string') data[option]()
		})
	}

	$.fn.typeahead.defaults = {
	  source: []
	  , items: 8
	  , menu: '<ul class="typeahead dropdown-menu"></ul>'
	  , item: '<li><a href="#"></a></li>'
	  , minLength: 3
	  , display: 'name'
	  , field: false
	  , value: false
	  , loadingClass: 'throbbing'
	  , preDispatch: null
	  , preProcess: null
	  , url: null
	  , timeout: 300
	  , method: 'get'
	  , itemSelected: function () { }
	}
  
	$.fn.typeahead.Constructor = Typeahead


	/* GREET TYPEAHEAD NO CONFLICT
	 * =================== */
   
	$.fn.typeahead.noConflict = function () {
		$.fn.typeahead = old
		return this
	}
   
   
	/* GREET TYPEAHEAD DATA-API
	 * ================== */
   
	$(document).on('focus.typeahead.data-api', '[data-provide="typeahead"]', function (e) {
		var $this = $(this)
		if ($this.data('typeahead')) return
		$this.typeahead($this.data())
	})

}(jQuery);
