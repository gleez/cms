(function ($) {

    /**
     * Attaches the autocomplete behavior to all required fields.
     */
    $(document).on('attach.autocomplete', function (e) {
	$('input.form-autocomplete', e.target).typeahead({
	    source: function(query, process){
		Gleez.searchItems(this.$element, query, process);
	    },
	});
    });

    Gleez.searchItems = function(element, query, process) {
	// Get the desired term and construct the autocomplete URL for it.
        var term = Gleez.autocompleteExtractLast(query);
	$(element).addClass('throbbing');
	
	$.ajax({
	    url: $(element).data('autocompletePath') + '/' + encodeURIComponent(term),
	    dataType: 'json',
	    //async: false,
	    success: function(results){
		if (typeof results.status == 'undefined' || results.status != 0) {
		    var items = new Array;
		    // Gleez returns an object array, but we need a string array.
		    $.map(results, function(data, item){
			items.push(data);
		    });
		    process(items);
		    $(element).removeClass('throbbing'); 
		}
	    }
	}, 300);
    };

    Gleez.autocompleteSplit = function(val) {
	return val.split(/,\s*/);
    };
    
    Gleez.autocompleteExtractLast = function(term) {
	return Gleez.autocompleteSplit(term).pop();
    };

})(jQuery);
