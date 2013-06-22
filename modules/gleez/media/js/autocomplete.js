(function ($) {

    /**
     * Attaches the autocomplete behavior to all required fields.
     */
    $(document).on('attach.autocomplete', function (e) {
	$('input.form-autocomplete', e.target).typeahead({
	    source: function(query, process){
		Gleez.searchItems(this, query, process);
	    },
	    updater: function (item) {
		var terms = Gleez.autocompleteSplit(this.query);
		// Remove the current input.
		terms.pop();
		// Add the selected item.
		terms.push(item);

		return terms.join(", ");
	    },
	    matcher: function (item) {
		var term = Gleez.autocompleteExtractLast(this.query);
		return ~item.toLowerCase().indexOf(term.toLowerCase())
	    },
	    highlighter: function (item) {
		var term = Gleez.autocompleteExtractLast(this.query);
		var query = term.replace(/[\-\[\]{}()*+?.,\\\^$|#\s]/g, '\\$&')
		return item.replace(new RegExp('(' + query + ')', 'ig'), function ($1, match) {
		    return '<strong>' + match + '</strong>'
		})
	    }
	});
    });

    Gleez.searchItems = function(object, query, process) {
	// Get the desired term and construct the autocomplete URL for it.
        var term = Gleez.autocompleteExtractLast(query);
	
	if (!term || term.length < object.options.minLength) {
	    return object.shown ? object.hide() : object
	}
	
	//adding throbbing animation during search
	$(object.$element).addClass('throbbing');
	
	//the url from to retrive suggestions
	var url = $(object.$element).data('url') + '/' + Gleez.URLEncode(term);
	
	$.ajax({
	    url: url,
	    dataType: 'json',
	    //async: false,
	}, 300)
	.done(function(results, textStatus, jqXHR){
	    if (typeof results.status == 'undefined' || results.status != 0) {
		var items = new Array;
		// Gleez returns an object array, but we need a string array.
		$.map(results, function(data, item){
		    items.push(data);
		});
		//process the items
		process(items);
		
		//remove the throbbing class
		$(object.$element).removeClass('throbbing');
		
		//set the width of the list
		object.$menu
		.insertAfter(object.$element)
		.css({
		  width: object.$element.innerWidth() + 'px',
		})
	    }
	})
	.fail(function (jqXHR, textStatus, errorThrown) {
	    
	    //remove the throbbing class
	    $(object.$element).removeClass('throbbing');
	    
	    alert(Gleez.ajaxError(jqXHR, url));
	});
	
	return true;
    };

    Gleez.autocompleteSplit = function(val) {
	return val.split(/,\s*/);
    };
    
    Gleez.autocompleteExtractLast = function(term) {
	return Gleez.autocompleteSplit(term).pop();
    };

    Gleez.URLEncode = function (s) {
	s = encodeURIComponent (s);
	//s = s.replace(/[\-\[\]{}()*+?.,\\\^$|#\s]/g, "\\$&");
	////s = s.replace (/\~/g, '%7E').replace (/\!/g, '%21').replace (/\(/g, '%28').replace (/\)/g, '%29').replace (/\'/g, '%27');
	////s = s.replace (/%20/g, '+');
	s = s.replace (/%2F/g, '/'); //escape slash for admin/menu autocomplete
	return s;
    };
    
    Gleez.URLDecode = function (s) {
	////s = s.replace (/\+/g, '%20');
	s = s.replace (/\//g, '%2F');
	s = decodeURIComponent (s);
	return s;
    };

})(jQuery);
